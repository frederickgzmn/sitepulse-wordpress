<?php
/**
 * SitePulse remote management recovery
 *
 * Undoes a safe update from the SitePulse dashboard: restores the plugin or theme folder and
 * the database from the backup UpdraftPlus made before the update, through UpdraftPlus's own
 * restorer, or reinstalls the version that was installed from WordPress.org. It runs in
 * WordPress cron and is answered to the API when it is done. WordPress's own automatic updates
 * then leave the undone version alone.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Remote_Recovery
 */
class Sitepulse_Remote_Recovery {

	/** Dashboard commands this class runs. */
	const COMMANDS = array( 'restore_backup', 'rollback_item' );

	/** Cron hook that runs the recovery. */
	const JOB_HOOK = 'sitepulse_remote_recovery_job';

	/** The recovery being run. */
	const JOB_OPTION = 'sitepulse_remote_recovery_job';

	/** Versions recoveries undid, which WordPress's automatic updates must not install again. */
	const UNDONE_OPTION = 'sitepulse_remote_undone';

	/** Seconds an undone version stays held back (90 days), unless a newer one is installed first. */
	const UNDONE_SECONDS = 7776000;

	/** Held while a request runs the recovery. */
	const LOCK = 'sitepulse_remote_recovery_lock';

	/** Seconds before trying again while another request runs the recovery, or a backup. */
	const RETRY_SECONDS = 60;

	/** Seconds a request may take to run it. */
	const TIME_LIMIT = 1800;

	/**
	 * Seconds after it was received within which a recovery must start; later, it answers
	 * expired. The API waits for its result for hours, and a restore that started that late
	 * would take the whole database back to the backup without anyone expecting it.
	 */
	const START_SECONDS = 3600;

	/** Seconds after it was received during which a restore waits for an UpdraftPlus backup to end. */
	const BACKUP_WAIT_SECONDS = 1800;

	/** Where WordPress.org keeps every released version of its plugins and themes. */
	const PACKAGES = 'https://downloads.wordpress.org/';

	/** A version, as the dashboard sends it. */
	const VERSION_PATTERN = '/^[0-9A-Za-z._+-]{1,64}$/';

	/**
	 * SitePulse's own settings, which restoring the database must not take back to the time of
	 * the backup: the API would no longer accept the site's key, queued events and results would
	 * be lost, and versions undone since would be installed again.
	 */
	const KEPT_OPTIONS = array(
		'sitepulse_settings',
		'sitepulse_license_key',
		Sitepulse_Remote_Management::STATE_OPTION,
		Sitepulse_Remote_Management::KEY_OPTION,
		Sitepulse_Remote_Management::EVENTS_OPTION,
		Sitepulse_Remote_Management::LOG_OPTION,
		Sitepulse_Remote_Management::CONNECTION_OPTION,
		Sitepulse_Remote_Updates::JOB_OPTION,
		Sitepulse_Remote_Activity::OPTION,
		self::JOB_OPTION,
		self::UNDONE_OPTION,
	);

	/**
	 * Run the recovery in cron, and keep it scheduled. WordPress's automatic updates, and the
	 * auto-update column of its Plugins and Themes screens, leave undone versions alone.
	 */
	public static function init() {
		add_action( self::JOB_HOOK, array( __CLASS__, 'run_job' ) );
		// Last, so that no other setting turns the automatic update of an undone version back on.
		add_filter( 'auto_update_plugin', array( __CLASS__, 'hold_plugin' ), PHP_INT_MAX, 2 );
		add_filter( 'auto_update_theme', array( __CLASS__, 'hold_theme' ), PHP_INT_MAX, 2 );

		if ( ( is_admin() || wp_doing_cron() ) && self::running() && ! wp_next_scheduled( self::JOB_HOOK ) ) {
			self::schedule( 0 );
		}
	}

	/**
	 * Start a command; it is answered when it is done.
	 *
	 * @param string $id   Command id.
	 * @param string $type restore_backup or rollback_item.
	 * @param array  $args Command arguments.
	 * @return array|null status, result and error for the API; null while it runs.
	 */
	public static function run_command( $id, $type, array $args ) {
		$kind  = $args['kind'] ?? null;
		$item  = $args['item'] ?? null;
		$valid = in_array( $kind, array( 'plugin', 'theme' ), true ) && is_string( $item ) && '' !== $item && ( 'restore_backup' === $type
			// The version installed before the safe update, which the restore is checked against, may be unknown: none, or empty.
			? 'updraftplus' === ( $args['provider'] ?? null ) && is_string( $args['job'] ?? null ) && 1 === preg_match( '/^[0-9a-f]{1,64}$/', $args['job'] ) && ( in_array( $args['version'] ?? null, array( null, '' ), true ) || self::is_version( $args['version'] ) )
			: self::is_version( $args['version'] ?? null ) );
		if ( ! $valid ) {
			return self::failed( 'invalid_arguments' );
		}
		if ( self::running() || Sitepulse_Remote_Updates::running() ) {
			return self::failed( 'update_running' );
		}

		Sitepulse_Remote_Updates::load_admin_api();
		if ( ! wp_is_file_mod_allowed( 'sitepulse_remote_updates' ) || 'direct' !== get_filesystem_method() ) {
			return self::failed( 'cannot_update' );
		}
		if ( ! Sitepulse_Remote_Updates::enough_disk() ) {
			return self::failed( 'not_enough_disk' );
		}
		// A backup that runs meanwhile would save a database half restored.
		if ( 'restore_backup' === $type && Sitepulse_Remote_Backups::updraftplus_busy() ) {
			return self::failed( 'backup_running' );
		}

		self::save_job(
			array(
				'command'     => $id,
				'type'        => $type,
				'args'        => $args,
				'started'     => false,
				'received_at' => time(),
			)
		);
		self::schedule( 0 );

		return null;
	}

	/**
	 * Run the recovery, once: a request that ended before it finished is not run again.
	 */
	public static function run_job() {
		$job = self::job();
		if ( null === $job ) {
			return;
		}
		// Another request is running it.
		if ( get_transient( self::LOCK ) ) {
			self::schedule( self::RETRY_SECONDS );
			return;
		}
		if ( $job['started'] ) {
			self::finish( $job, self::failed( 'interrupted' ) );
			return;
		}
		// A recovery received by an older version of the plugin has no time of receipt: it is too old.
		$waited = time() - (int) ( $job['received_at'] ?? 0 );
		if ( $waited > self::START_SECONDS ) {
			self::finish( $job, self::failed( 'expired' ) );
			return;
		}
		// An UpdraftPlus backup that started since the command arrived is waited for, for a while.
		if ( 'restore_backup' === $job['type'] && Sitepulse_Remote_Backups::updraftplus_busy() ) {
			if ( $waited >= self::BACKUP_WAIT_SECONDS ) {
				self::finish( $job, self::failed( 'backup_running' ) );
				return;
			}
			self::schedule( self::RETRY_SECONDS );
			return;
		}

		self::lock();
		$job['started'] = true;
		// The version this recovery undoes, which WordPress's automatic updates must not install again.
		$job['undoing'] = Sitepulse_Remote_Events::installed_version( $job['args']['kind'], $job['args']['item'] );
		self::save_job( $job );
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( self::TIME_LIMIT );
		}
		Sitepulse_Remote_Updates::load_admin_api();
		self::report_start( $job );

		try {
			$result = 'restore_backup' === $job['type'] ? self::restore( $job['args'] ) : self::roll_back( $job['args'] );
		} finally {
			delete_transient( self::LOCK );
		}
		self::finish( $job, $result );
	}

	/**
	 * @return bool Whether a recovery is being run.
	 */
	public static function running() {
		return null !== self::job();
	}

	/**
	 * Stop running a recovery; the plugin is being deactivated.
	 */
	public static function unschedule() {
		wp_clear_scheduled_hook( self::JOB_HOOK );
	}

	/**
	 * WordPress does not update a plugin automatically to a version a recovery undid.
	 *
	 * @param bool|null $update Whether WordPress updates the plugin automatically.
	 * @param mixed     $offer  The update WordPress offers: plugin and new_version.
	 * @return bool|null
	 */
	public static function hold_plugin( $update, $offer ) {
		return self::hold( 'plugin', $update, $offer );
	}

	/**
	 * WordPress does not update a theme automatically to a version a recovery undid.
	 *
	 * @param bool|null $update Whether WordPress updates the theme automatically.
	 * @param mixed     $offer  The update WordPress offers: theme and new_version.
	 * @return bool|null
	 */
	public static function hold_theme( $update, $offer ) {
		return self::hold( 'theme', $update, $offer );
	}

	/**
	 * @param string    $kind   plugin or theme.
	 * @param bool|null $update Whether WordPress updates the item automatically.
	 * @param mixed     $offer  The update WordPress offers.
	 * @return bool|null false for the version a recovery undid; otherwise $update.
	 */
	private static function hold( $kind, $update, $offer ) {
		$offer  = (array) $offer;
		$undone = self::undone()[ $kind . ':' . (string) ( $offer[ $kind ] ?? '' ) ] ?? null;

		return null !== $undone && (string) ( $offer['new_version'] ?? '' ) === (string) $undone['version'] ? false : $update;
	}

	/**
	 * @return array The versions recoveries undid that are still held back, by "kind:item".
	 */
	private static function undone() {
		$stored = get_option( self::UNDONE_OPTION, array() );
		$undone = array_filter( is_array( $stored ) ? $stored : array(), array( __CLASS__, 'held' ) );
		if ( $undone !== $stored ) {
			self::save_undone( $undone );
		}

		return $undone;
	}

	/**
	 * @param mixed $entry A version a recovery undid: kind, item, version and when.
	 * @return bool Whether it is still held back: for 90 days, until a newer version is installed.
	 */
	private static function held( $entry ) {
		if ( ! is_array( $entry ) || ! in_array( $entry['kind'] ?? null, array( 'plugin', 'theme' ), true ) || ! isset( $entry['item'], $entry['version'], $entry['at'] ) ) {
			return false;
		}

		return time() - (int) $entry['at'] < self::UNDONE_SECONDS
			&& ! version_compare( Sitepulse_Remote_Events::installed_version( $entry['kind'], (string) $entry['item'] ), (string) $entry['version'], '>' );
	}

	/**
	 * Remember a version a recovery undid; a later recovery of the same item replaces it.
	 *
	 * @param string $kind    plugin or theme.
	 * @param string $item    Plugin file or theme stylesheet.
	 * @param string $version The version that was installed before the recovery.
	 */
	private static function remember_undone( $kind, $item, $version ) {
		$undone                        = self::undone();
		$undone[ $kind . ':' . $item ] = array(
			'kind'    => $kind,
			'item'    => $item,
			'version' => $version,
			'at'      => time(),
		);
		self::save_undone( $undone );
	}

	/**
	 * @param array $undone Versions held back, by "kind:item".
	 */
	private static function save_undone( array $undone ) {
		if ( $undone ) {
			update_option( self::UNDONE_OPTION, $undone, false );
		} else {
			delete_option( self::UNDONE_OPTION );
		}
	}

	/**
	 * Tell the dashboard the recovery is under way; its result says the rest.
	 *
	 * @param array $job The recovery.
	 */
	private static function report_start( array $job ) {
		$client = Sitepulse_Remote_Management::client();
		if ( null === $client ) {
			return;
		}

		$client->post(
			'command-progress',
			array(
				'id'       => $job['command'],
				'progress' => array(
					'phase'   => 'restore_backup' === $job['type'] ? 'restoring' : 'installing',
					'percent' => null,
					'backup'  => null,
					'done'    => 0,
					'total'   => 1,
					'item'    => $job['args']['item'],
				),
			)
		);
	}

	/**
	 * Restore the item's folder and the database from the backup UpdraftPlus made before the
	 * update, the way UpdraftPlus's own restore does, and keep SitePulse's settings as they are.
	 *
	 * @param array $args kind, item, job (the backup's nonce) and, when known, the version the item must be back to.
	 * @return array The command's outcome.
	 */
	private static function restore( array $args ) {
		$updraftplus = $GLOBALS['updraftplus'] ?? null;
		$supported   = array_filter(
			Sitepulse_Remote_Backups::providers(),
			static function ( $provider ) {
				return 'updraftplus' === $provider['id'] && $provider['supported'];
			}
		);
		if ( ! $supported ) {
			return self::failed( 'restore_unsupported' );
		}

		$part = $args['kind'] . 's';
		$set  = self::backup_set( $args['job'] );
		if ( null === $set || ! isset( $set[ $part ], $set['db'] ) ) {
			return self::failed( 'backup_not_found' );
		}

		$folder   = 'plugin' === $args['kind'] && '.' !== dirname( $args['item'] ) ? dirname( $args['item'] ) : $args['item'];
		$entities = array(
			$part => $part,
			'db'   => 'db',
		);
		$options  = array(
			'include_unspecified_' . $part            => false,
			$part . '_to_restore'                     => array( $folder ),
			$part . '_to_skip'                        => array(),
			'include_unspecified_tables'              => true,
			'tables_to_restore'                       => array(),
			'tables_to_skip'                          => array(),
			'updraft_encryptionphrase'                => (string) UpdraftPlus_Options::get_updraft_option( 'updraft_encryptionphrase', '' ),
			'updraft_restorer_wpcore_includewpconfig' => false,
			'updraft_incremental_restore_point'       => -1,
		);

		// The restored database holds SitePulse's settings of the time of the backup. UpdraftPlus
		// restores the options table first and announces it: put the current ones back then, so no
		// other request acts on the old ones meanwhile, such as an update the backup caught running.
		// That table no longer holds this request's lock either, and UpdraftPlus empties the object
		// cache once the database is back: take the lock again, so no other request takes this
		// recovery for an interrupted one while UpdraftPlus restores the folder.
		$kept     = self::kept_settings();
		$relock   = static function () {
			self::lock();
		};
		$restored = static function ( $table, $prefix ) use ( $kept, $relock ) {
			if ( $prefix . 'options' === $table ) {
				self::put_back( $kept );
				$relock();
			}
		};
		// Put them back once more at the end, even when a PHP time limit ends the request meanwhile.
		$keep = static function () use ( $kept ) {
			self::put_back( $kept );
			delete_transient( self::LOCK );
		};
		add_action( 'updraftplus_restored_db_table', $restored, 10, 2 );
		add_action( 'updraftplus_restored_db', $relock, 10, 0 );
		add_action( 'shutdown', $keep, 1 );
		// Problems UpdraftPlus recorded before, such as in another task of this cron request, are not the restore's.
		$recorded = count( (array) $updraftplus->errors );
		// UpdraftPlus prints its progress for a browser.
		ob_start();
		try {
			$outcome = self::updraftplus_restore( $updraftplus, $set, $entities, $options );
		} catch ( Throwable $e ) {
			$outcome = new WP_Error( 'restore_failed', $e->getMessage() );
		} finally {
			ob_end_clean();
			remove_action( 'updraftplus_restored_db_table', $restored, 10 );
			remove_action( 'updraftplus_restored_db', $relock, 10 );
			remove_action( 'shutdown', $keep, 1 );
			self::put_back( $kept );
		}

		// An error UpdraftPlus recorded fails the restore; a warning does not.
		$problems = Sitepulse_Remote_Backups::updraftplus_problems( array_slice( (array) $updraftplus->errors, $recorded ) );
		if ( true !== $outcome || $problems['errors'] > 0 ) {
			return Sitepulse_Remote_Management::command_result(
				'failed',
				array(
					'message' => is_wp_error( $outcome )
						? $outcome->get_error_message()
						: sprintf( 'UpdraftPlus could not restore the backup; its log of restore %s has the details.', (string) $updraftplus->nonce ),
				),
				'restore_failed'
			);
		}

		$version = (string) ( $args['version'] ?? '' );
		if ( '' !== $version ) {
			// The restored folder must hold the version installed before the safe update.
			'plugin' === $args['kind'] ? wp_clean_plugins_cache() : wp_clean_themes_cache();
			$installed = Sitepulse_Remote_Events::installed_version( $args['kind'], $args['item'] );
			if ( $installed !== $version ) {
				return Sitepulse_Remote_Management::command_result(
					'failed',
					array( 'message' => sprintf( 'UpdraftPlus restored the backup, but %1$s is not back to version %2$s: %3$s.', $args['item'], $version, '' === $installed ? 'it is not installed' : 'version ' . $installed . ' is installed' ) ),
					'restore_incomplete'
				);
			}
		}

		return Sitepulse_Remote_Management::command_result( 'done', array( 'restored' => array_keys( $entities ) ), null );
	}

	/**
	 * Run UpdraftPlus's restorer as its own restore does after the backup is chosen.
	 *
	 * @param object $updraftplus UpdraftPlus.
	 * @param array  $set         The backup.
	 * @param array  $entities    What to restore.
	 * @param array  $options     How to restore it.
	 * @return bool|WP_Error true when restored.
	 */
	private static function updraftplus_restore( $updraftplus, array $set, array $entities, array $options ) {
		$updraftplus->initiate_restore_job();
		$updraftplus->jobdata_set_multi(
			array(
				'updraft_restore'                  => array_keys( $entities ),
				'backup_timestamp'                 => $set['timestamp'],
				'updraft_restorer_restore_options' => $options,
				'restore_options'                  => $options,
			)
		);
		// UpdraftPlus keeps this marker while it restores, and offers to resume a restore that stopped.
		update_site_option( 'updraft_restore_in_progress', $updraftplus->nonce );
		WP_Filesystem();

		$restorer                        = new Updraft_Restorer( new Updraft_Restorer_Skin(), $set, false, $options, null );
		$GLOBALS['updraftplus_restorer'] = $restorer;
		// UpdraftPlus logs PHP errors while it restores; its clean-up restores the handler.
		set_error_handler( array( $updraftplus, 'php_error' ), E_ALL ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- UpdraftPlus's own restore sets it the same way.
		try {
			$outcome = $restorer->perform_restore( $entities, $options );
		} catch ( Throwable $e ) {
			$outcome = new WP_Error( 'restore_failed', $e->getMessage() );
		}
		$restorer->post_restore_clean_up( $outcome, false );

		if ( true === $outcome ) {
			// UpdraftPlus lists the backups the restored database did not know about yet.
			UpdraftPlus_Backup_History::rebuild();
		}

		return $outcome;
	}

	/**
	 * @param string $nonce The backup's nonce.
	 * @return array|null The backup with its timestamp, from UpdraftPlus's history.
	 */
	private static function backup_set( $nonce ) {
		$set = Sitepulse_Remote_Backups::updraftplus_backup( $nonce );
		if ( null === $set ) {
			// A database restored since lost the backups made after it; UpdraftPlus finds them again in its folder.
			UpdraftPlus_Backup_History::rebuild();
			$set = Sitepulse_Remote_Backups::updraftplus_backup( $nonce );
		}

		return $set;
	}

	/**
	 * @return array SitePulse's own settings as they are now (null for those not set), and whether
	 *               WordPress loads each one on every request.
	 */
	private static function kept_settings() {
		$autoloaded = wp_load_alloptions();
		$kept       = array();
		foreach ( self::KEPT_OPTIONS as $option ) {
			$kept[ $option ] = array(
				'value'    => get_option( $option, null ),
				'autoload' => array_key_exists( $option, $autoloaded ),
			);
		}

		return $kept;
	}

	/**
	 * Write SitePulse's own settings back over the restored ones, each loaded on every request or
	 * not, as before. WordPress caches options, so the cache is emptied first to compare with what
	 * the database holds now.
	 *
	 * @param array $kept Settings from kept_settings().
	 */
	private static function put_back( array $kept ) {
		wp_cache_flush();
		$autoloaded = wp_load_alloptions();
		foreach ( $kept as $option => $setting ) {
			if ( null === $setting['value'] ) {
				delete_option( $option );
				continue;
			}
			// WordPress leaves an unchanged value as it is, with the restored row's autoload.
			if ( $setting['autoload'] !== array_key_exists( $option, $autoloaded ) ) {
				delete_option( $option );
			}
			update_option( $option, $setting['value'], $setting['autoload'] );
		}
	}

	/**
	 * Reinstall the version that was installed before the update, from WordPress.org, through
	 * WordPress's own upgrader.
	 *
	 * @param array $args kind, item and version.
	 * @return array The command's outcome.
	 */
	private static function roll_back( array $args ) {
		$kind = $args['kind'];
		$id   = $args['item'];
		$from = Sitepulse_Remote_Events::installed_version( $kind, $id );
		if ( '' === $from ) {
			return self::failed( 'not_installed' );
		}
		$slug = self::wordpress_org_slug( $kind, $id );
		if ( '' === $slug ) {
			return self::failed( 'no_package' );
		}
		$package = self::PACKAGES . $kind . '/' . rawurlencode( $slug . '.' . $args['version'] ) . '.zip';
		// WordPress.org answers 404 for a version it does not have: the site is left as it is. Any
		// other answer, or none, leaves it to the install.
		if ( 404 === (int) wp_remote_retrieve_response_code( wp_remote_head( $package, array( 'timeout' => 10 ) ) ) ) {
			return self::failed( 'no_package' );
		}

		$upgrader = 'plugin' === $kind ? new Plugin_Upgrader( new Automatic_Upgrader_Skin() ) : new Theme_Upgrader( new Automatic_Upgrader_Skin() );
		$release  = static function () use ( $upgrader ) {
			$upgrader->maintenance_mode( false );
			Sitepulse_Remote_Events::$updating = false;
		};
		// A PHP time limit or crash can end this request while WordPress installs the package.
		// WordPress still runs its shutdown actions then: leave maintenance mode, and free the
		// recovery for the next request, which answers that it was interrupted.
		$ending = static function () use ( $release ) {
			$release();
			delete_transient( self::LOCK );
		};
		add_action( 'shutdown', $ending, 1 );
		// SitePulse reports this change itself, as the command's result.
		Sitepulse_Remote_Events::$updating = true;
		$upgrader->maintenance_mode( true );
		try {
			$outcome = $upgrader->install( $package, array( 'overwrite_package' => true ) );
		} finally {
			remove_action( 'shutdown', $ending, 1 );
			$release();
		}
		'plugin' === $kind ? wp_clean_plugins_cache() : wp_clean_themes_cache();

		$to     = Sitepulse_Remote_Events::installed_version( $kind, $id );
		$result = array(
			'kind' => $kind,
			'id'   => $id,
			'from' => $from,
			'to'   => '' === $to ? null : $to,
		);

		return true === $outcome && $to === $args['version']
			? Sitepulse_Remote_Management::command_result( 'done', $result, null )
			: Sitepulse_Remote_Management::command_result( 'failed', $result, 'install_failed' );
	}

	/**
	 * WordPress knows which installed plugins and themes come from WordPress.org: they are in its
	 * update check with a WordPress.org package. Others, such as premium plugins, have no
	 * versions there. A plugin of a single file is not in a folder like WordPress.org's packages.
	 *
	 * @param string $kind plugin or theme.
	 * @param string $id   Plugin file or theme stylesheet.
	 * @return string The item's WordPress.org slug; empty when it does not come from there.
	 */
	private static function wordpress_org_slug( $kind, $id ) {
		$updates = get_site_transient( 'update_' . $kind . 's' );
		foreach ( array( 'response', 'no_update' ) as $list ) {
			$entry = is_object( $updates ) && isset( $updates->{$list}[ $id ] ) ? (array) $updates->{$list}[ $id ] : array();
			if ( 0 !== strpos( (string) ( $entry['package'] ?? '' ), self::PACKAGES . $kind . '/' ) ) {
				continue;
			}
			if ( 'theme' === $kind ) {
				return $id;
			}

			return '.' === dirname( $id ) ? '' : (string) ( $entry['slug'] ?? '' );
		}

		return '';
	}

	/**
	 * Answer the command. A recovery that is done no longer has the effect of the update it undid
	 * measured, keeps WordPress's automatic updates from installing the undone version again, and
	 * has WordPress check for updates afresh, as after the dashboard's updates.
	 *
	 * @param array $job    The recovery.
	 * @param array $result Its outcome.
	 */
	private static function finish( array $job, array $result ) {
		delete_option( self::JOB_OPTION );
		self::unschedule();
		if ( 'done' === $result['status'] ) {
			$kind = $job['args']['kind'];
			$item = $job['args']['item'];
			Sitepulse_Remote_Activity::unwatch( $kind, $item );
			if ( '' !== $job['undoing'] ) {
				self::remember_undone( $kind, $item, $job['undoing'] );
			}
			if ( 'plugin' === $kind ) {
				wp_clean_plugins_cache();
				wp_update_plugins();
			} else {
				wp_clean_themes_cache();
				wp_update_themes();
			}
		}

		Sitepulse_Remote_Management::finish_command( $job['command'], $job['type'], $job['args'], $result );
	}

	/**
	 * Hold the recovery's lock, for as long as a request may run it.
	 */
	private static function lock() {
		set_transient( self::LOCK, 1, self::TIME_LIMIT );
	}

	/**
	 * @param mixed $version A version from the dashboard.
	 * @return bool Whether it is one.
	 */
	private static function is_version( $version ) {
		return is_string( $version ) && 1 === preg_match( self::VERSION_PATTERN, $version );
	}

	/**
	 * @param string $error Error code.
	 * @return array A failed command.
	 */
	private static function failed( $error ) {
		return Sitepulse_Remote_Management::command_result( 'failed', null, $error );
	}

	/**
	 * @return array|null The recovery being run.
	 */
	private static function job() {
		$job = get_option( self::JOB_OPTION, null );
		return is_array( $job ) && isset( $job['command'], $job['type'], $job['args'] ) ? $job : null;
	}

	/**
	 * @param array $job The recovery.
	 */
	private static function save_job( array $job ) {
		update_option( self::JOB_OPTION, $job, false );
	}

	/**
	 * @param int $seconds Seconds until a request runs the recovery.
	 */
	private static function schedule( $seconds ) {
		wp_clear_scheduled_hook( self::JOB_HOOK );
		wp_schedule_single_event( time() + $seconds, self::JOB_HOOK );
	}
}

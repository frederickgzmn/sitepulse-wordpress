<?php
/**
 * SitePulse remote management recovery
 *
 * Undoes a safe update from the SitePulse dashboard: restores the plugin or theme folder and
 * the database from the backup UpdraftPlus made before the update, through UpdraftPlus's own
 * restorer, or reinstalls the version that was installed from WordPress.org. It runs in
 * WordPress cron and is answered to the API when it is done.
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

	/** Held while a request runs the recovery. */
	const LOCK = 'sitepulse_remote_recovery_lock';

	/** Seconds before trying again while another request runs the recovery. */
	const RETRY_SECONDS = 60;

	/** Seconds a request may take to run it. */
	const TIME_LIMIT = 1800;

	/** Where WordPress.org keeps every released version of its plugins and themes. */
	const PACKAGES = 'https://downloads.wordpress.org/';

	/**
	 * SitePulse's own settings, which restoring the database must not take back to the time of
	 * the backup: the API would no longer accept the site's key, and queued events and results
	 * would be lost.
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
	);

	/**
	 * Run the recovery in cron, and keep it scheduled.
	 */
	public static function init() {
		add_action( self::JOB_HOOK, array( __CLASS__, 'run_job' ) );

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
			? 'updraftplus' === ( $args['provider'] ?? null ) && is_string( $args['job'] ?? null ) && 1 === preg_match( '/^[0-9a-f]{1,64}$/', $args['job'] )
			: is_string( $args['version'] ?? null ) && 1 === preg_match( '/^[0-9A-Za-z._+-]{1,64}$/', $args['version'] ) );
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

		self::save_job(
			array(
				'command' => $id,
				'type'    => $type,
				'args'    => $args,
				'started' => false,
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

		set_transient( self::LOCK, 1, self::TIME_LIMIT );
		$job['started'] = true;
		self::save_job( $job );
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( self::TIME_LIMIT );
		}
		Sitepulse_Remote_Updates::load_admin_api();

		$result = 'restore_backup' === $job['type'] ? self::restore( $job['args'] ) : self::roll_back( $job['args'] );
		delete_transient( self::LOCK );
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
	 * Restore the item's folder and the database from the backup UpdraftPlus made before the
	 * update, the way UpdraftPlus's own restore does, and keep SitePulse's settings as they are.
	 *
	 * @param array $args kind, item and job (the backup's nonce).
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

		// The restored database holds SitePulse's settings of the time of the backup. Put the
		// current ones back afterwards, even when a PHP time limit ends the request meanwhile.
		$kept = self::kept_settings();
		$keep = static function () use ( $kept ) {
			self::put_back( $kept );
			delete_transient( self::LOCK );
		};
		add_action( 'shutdown', $keep, 1 );
		// UpdraftPlus prints its progress for a browser.
		ob_start();
		try {
			$outcome = self::updraftplus_restore( $updraftplus, $set, $entities, $options );
		} catch ( Throwable $e ) {
			$outcome = new WP_Error( 'restore_failed', $e->getMessage() );
		} finally {
			ob_end_clean();
			remove_action( 'shutdown', $keep, 1 );
			self::put_back( $kept );
		}

		if ( true !== $outcome || ! empty( $updraftplus->errors ) ) {
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
		$set = self::find_backup( $nonce );
		if ( null === $set ) {
			// A database restored since lost the backups made after it; UpdraftPlus finds them again in its folder.
			UpdraftPlus_Backup_History::rebuild();
			$set = self::find_backup( $nonce );
		}

		return $set;
	}

	/**
	 * @param string $nonce The backup's nonce.
	 * @return array|null The backup in UpdraftPlus's history, with its timestamp.
	 */
	private static function find_backup( $nonce ) {
		foreach ( (array) UpdraftPlus_Backup_History::get_history() as $timestamp => $set ) {
			if ( is_array( $set ) && $nonce === ( $set['nonce'] ?? null ) ) {
				$set['timestamp'] = (int) $timestamp;
				return $set;
			}
		}

		return null;
	}

	/**
	 * @return array SitePulse's own settings as they are now; null for those not set.
	 */
	private static function kept_settings() {
		$kept = array();
		foreach ( self::KEPT_OPTIONS as $option ) {
			$kept[ $option ] = get_option( $option, null );
		}

		return $kept;
	}

	/**
	 * Write SitePulse's own settings back over the restored ones. WordPress caches options, so
	 * the cache is emptied first to compare with what the database holds now.
	 *
	 * @param array $kept Settings from kept_settings().
	 */
	private static function put_back( array $kept ) {
		wp_cache_flush();
		foreach ( $kept as $option => $value ) {
			if ( null === $value ) {
				delete_option( $option );
			} else {
				update_option( $option, $value );
			}
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

		$upgrader = 'plugin' === $kind ? new Plugin_Upgrader( new Automatic_Upgrader_Skin() ) : new Theme_Upgrader( new Automatic_Upgrader_Skin() );
		// SitePulse reports this change itself, as the command's result.
		Sitepulse_Remote_Events::$updating = true;
		$upgrader->maintenance_mode( true );
		$outcome = $upgrader->install( self::PACKAGES . $kind . '/' . rawurlencode( $slug . '.' . $args['version'] ) . '.zip', array( 'overwrite_package' => true ) );
		$upgrader->maintenance_mode( false );
		Sitepulse_Remote_Events::$updating = false;
		'plugin' === $kind ? wp_clean_plugins_cache() : wp_clean_themes_cache();

		// WordPress.org answers 404 for a version it does not have.
		if ( is_wp_error( $outcome ) && 'download_failed' === $outcome->get_error_code() ) {
			return self::failed( 'no_package' );
		}
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
	 * Answer the command and stop measuring the effect of the update it undid.
	 *
	 * @param array $job    The recovery.
	 * @param array $result Its outcome.
	 */
	private static function finish( array $job, array $result ) {
		delete_option( self::JOB_OPTION );
		self::unschedule();
		if ( 'done' === $result['status'] ) {
			Sitepulse_Remote_Activity::unwatch( $job['args']['kind'], $job['args']['item'] );
		}

		Sitepulse_Remote_Management::finish_command( $job['command'], $job['type'], $job['args'], $result );
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

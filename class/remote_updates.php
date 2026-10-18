<?php
/**
 * SitePulse remote management updates
 *
 * Runs the dashboard's plugin, theme and WordPress updates through WordPress's own
 * upgrader: a backup first (with UpdraftPlus or BackWPup), for a safe update the API's
 * visual scan of the site, a health baseline, then one item at a time, each checked and
 * rolled back when it breaks the site. An update runs across WordPress cron requests and
 * is answered to the API when it is done.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Remote_Updates
 */
class Sitepulse_Remote_Updates {

	/** Dashboard commands this class runs. */
	const COMMANDS = array( 'update_plugins', 'update_themes', 'update_core', 'set_auto_updates' );

	/** What each update command updates, and the argument that lists it. */
	const KINDS = array(
		'update_plugins' => array( 'plugin', 'plugins' ),
		'update_themes'  => array( 'theme', 'themes' ),
		'update_core'    => array( 'core', 'version' ),
	);

	/** Cron hook that runs the update. */
	const JOB_HOOK = 'sitepulse_remote_update_job';

	/** The update being run. */
	const JOB_OPTION = 'sitepulse_remote_update_job';

	/** Held while a request runs the update. */
	const LOCK = 'sitepulse_remote_update_lock';

	/** Free space an update needs in wp-content. */
	const MIN_FREE_BYTES = 104857600;

	/** Seconds a request works on the update before the next request continues it. */
	const STEP_SECONDS = 30;

	/** Seconds between checks while a backup runs. */
	const POLL_SECONDS = 60;

	/** Seconds between the questions to the API while it scans the site before a safe update. */
	const VISUAL_POLL_SECONDS = 30;

	/** Seconds a safe update waits for that scan. */
	const VISUAL_WAIT_SECONDS = 1800;

	/**
	 * Seconds after it was received within which an update must take its first step; later, it
	 * answers expired instead of starting so long after it was asked for.
	 */
	const START_SECONDS = 3600;

	/** The folder a safe update backs up with the database, by command (backup_parts). */
	const BACKUP_FOLDERS = array(
		'update_plugins' => 'plugins',
		'update_themes'  => 'themes',
	);

	/** Most items per command. */
	const MAX_ITEMS = 50;

	/** Item errors that do not stop the other items. */
	const SKIPPABLE = array( 'up_to_date', 'not_installed', 'requirements', 'no_package' );

	/**
	 * Seconds to wait after an update before checking the site. Like WordPress's own automatic
	 * updates, this lets PHP's opcode cache pick up the new files (it rechecks them every 2 seconds
	 * by default). Filter: sitepulse_remote_health_check_delay.
	 */
	const HEALTH_CHECK_DELAY = 2;

	/** Item errors of items WordPress tried to update. */
	const ATTEMPTED = array( null, 'install_failed', 'health_check_failed', 'interrupted' );

	/**
	 * Run the update in cron, and keep it scheduled.
	 */
	public static function init() {
		add_action( self::JOB_HOOK, array( __CLASS__, 'run_job' ) );

		if ( ( is_admin() || wp_doing_cron() ) && null !== self::job() && ! wp_next_scheduled( self::JOB_HOOK ) ) {
			self::schedule( 0 );
		}
	}

	/**
	 * Start a command. set_auto_updates is answered at once; an update when it is done.
	 *
	 * @param string $id   Command id.
	 * @param string $type Command type.
	 * @param array  $args Command arguments.
	 * @return array|null status, result and error for the API; null while the update runs.
	 */
	public static function run_command( $id, $type, array $args ) {
		if ( 'set_auto_updates' === $type ) {
			return self::set_auto_updates( $args );
		}

		$kind  = self::KINDS[ $type ][0];
		$items = 'core' === $kind ? self::strings( array( $args['version'] ?? null ) ) : self::strings( $args[ self::KINDS[ $type ][1] ] ?? null );
		$wait  = $args['backup_wait'] ?? 60;
		$what  = self::backup_what( $args['backup_parts'] ?? null, $type );
		$safe  = $args['safe_update'] ?? null;
		if ( ! $items || count( $items ) > self::MAX_ITEMS || ! is_int( $wait ) || $wait < 5 || $wait > 240 || null === $what || ! ( null === $safe || ( is_string( $safe ) && '' !== $safe ) ) ) {
			return self::failed( 'invalid_arguments' );
		}
		if ( self::running() || Sitepulse_Remote_Recovery::running() ) {
			return self::failed( 'update_running' );
		}

		self::load_admin_api();
		if ( ! wp_is_file_mod_allowed( 'sitepulse_remote_updates' ) || 'direct' !== get_filesystem_method() ) {
			return self::failed( 'cannot_update' );
		}
		if ( ! self::enough_disk() ) {
			return self::failed( 'not_enough_disk' );
		}

		// Updates do not need a backup plugin: without a supported one, the update skips the backup.
		$wanted       = false !== ( $args['backup_first'] ?? true );
		$provider     = Sitepulse_Remote_Backups::provider();
		$skipped      = $wanted && ( null === $provider || ! $provider['supported'] ) ? 'no_backup_plugin' : null;
		$backup_first = $wanted && null === $skipped;
		self::save_job(
			array(
				'command'        => $id,
				'type'           => $type,
				'args'           => $args,
				'kind'           => $kind,
				'items'          => $items,
				'backup_first'   => $backup_first,
				'backup_wait'    => $wait,
				'backup_what'    => $what,
				'safe_update'    => $safe,
				'phase'          => $backup_first ? 'backup' : self::after_backup( $safe ),
				'backup'         => null,
				'backup_skipped' => $skipped,
				'visual_since'   => null,
				'baseline'       => null,
				'current'        => null,
				'results'        => array(),
				'started_at'     => time(),
				'begun'          => false,
				'finished'       => false,
				'error'          => null,
			)
		);
		self::schedule( 0 );

		return null;
	}

	/**
	 * @return bool Whether wp-content has the free space an update needs; true when the server does not tell.
	 */
	public static function enough_disk() {
		$free = function_exists( 'disk_free_space' ) ? @disk_free_space( WP_CONTENT_DIR ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- WordPress core reads it the same way before updates.

		return false === $free || $free >= self::MIN_FREE_BYTES;
	}

	/**
	 * Continue the update for a while, then let the next cron request go on.
	 */
	public static function run_job() {
		$job = self::job();
		if ( null === $job ) {
			return;
		}
		// Another request is working on it.
		if ( get_transient( self::LOCK ) ) {
			self::schedule( self::POLL_SECONDS );
			return;
		}
		self::load_admin_api();
		// An update that could not start within an hour is not run that late. One received by an
		// older version of the plugin has no "begun": it has.
		if ( false === ( $job['begun'] ?? true ) ) {
			if ( time() - (int) $job['started_at'] > self::START_SECONDS ) {
				self::finish( self::stop( $job, 'expired' ) );
				return;
			}
			$job['begun'] = true;
		}

		set_transient( self::LOCK, 1, 10 * MINUTE_IN_SECONDS );
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 10 * MINUTE_IN_SECONDS );
		}

		$started = time();
		do {
			// Saved before the progress report, which can take a while: a request that ends while
			// it reports keeps the step it made.
			$job = self::step( $job );
			self::save_job( $job );
			$reported = self::report_progress( $job );
			if ( $reported !== $job ) {
				$job = $reported;
				self::save_job( $job );
			}
		} while ( ! $job['finished'] && ! isset( $job['wait'] ) && time() - $started < self::STEP_SECONDS );
		delete_transient( self::LOCK );

		if ( $job['finished'] ) {
			self::finish( $job );
			return;
		}
		self::schedule( $job['wait'] ?? 0 );
	}

	/**
	 * @return bool Whether an update is being run.
	 */
	public static function running() {
		return null !== self::job();
	}

	/**
	 * @return array|null Progress of the update for the inventory.
	 */
	public static function job_status() {
		$job = self::job();
		if ( null === $job ) {
			return null;
		}

		return array(
			'command'    => $job['command'],
			'kind'       => $job['kind'],
			'phase'      => $job['phase'],
			'done'       => count( $job['results'] ),
			'total'      => count( $job['items'] ),
			'started_at' => gmdate( 'Y-m-d\TH:i:s\Z', (int) $job['started_at'] ),
		);
	}

	/**
	 * Stop running updates; the plugin is being deactivated.
	 */
	public static function unschedule() {
		wp_clear_scheduled_hook( self::JOB_HOOK );
	}

	/**
	 * @param array $job The update.
	 * @return array The update after one step: a backup check, the baseline or one item.
	 */
	private static function step( array $job ) {
		unset( $job['wait'] );
		if ( Sitepulse_Remote_Management::is_blocked() ) {
			return self::stop( $job, 'blocked' );
		}
		if ( 'backup' === $job['phase'] ) {
			return self::backup_step( $job );
		}
		if ( 'visual' === $job['phase'] ) {
			return self::visual_step( $job );
		}
		if ( 'baseline' === $job['phase'] ) {
			$job['baseline'] = Sitepulse_Remote_Health::check();
			$error           = Sitepulse_Remote_Health::baseline_error( $job['baseline'] );
			$job['phase']    = 'updating';
			return null === $error ? $job : self::stop( $job, $error );
		}

		return self::update_step( $job );
	}

	/**
	 * Start a backup with the managed backup plugin and wait until it is done: a full one, or
	 * for a safe update the folder it updates and the database.
	 *
	 * @param array $job The update.
	 * @return array
	 */
	private static function backup_step( array $job ) {
		// An update started by an older version of the plugin has no backup_what.
		$what = $job['backup_what'] ?? 'full';
		if ( null === $job['backup'] ) {
			$requested = time();
			$result    = Sitepulse_Remote_Backups::run_command( 'backup_now', array( 'what' => $what ), true );
			if ( 'done' === $result['status'] ) {
				$job['backup']          = array(
					'provider'     => $result['result']['provider'],
					'job'          => $result['result']['job'],
					'requested_at' => $requested,
				);
				$job['backup_progress'] = array(
					'percent' => 0,
					'stage'   => 'queued',
				);
				$job['wait']            = self::POLL_SECONDS;
				return $job;
			}
			// A backup that is already running is waited for, then ours starts.
			if ( 'backup_running' === $result['error'] && time() - $job['started_at'] <= $job['backup_wait'] * MINUTE_IN_SECONDS ) {
				$job['wait'] = self::POLL_SECONDS;
				return $job;
			}
			return self::stop( $job, array( 'backup_running' => 'backup_timeout', 'no_backup_plugin' => 'no_backup_plugin' )[ $result['error'] ] ?? 'backup_failed' );
		}

		$status      = Sitepulse_Remote_Backups::status();
		$running     = is_array( $status['running'] ?? null ) ? $status['running'] : null;
		$updraftplus = 'updraftplus' === $job['backup']['provider'];
		if ( $updraftplus ) {
			// UpdraftPlus names each backup: only ours ends the wait, not another that finished first.
			$last = Sitepulse_Remote_Backups::updraftplus_result( $job['backup']['job'] );
		} else {
			// BackWPup's runs have no name: once none runs, its last run is ours if it ended since the request.
			$last = null === $running && is_array( $status['last'] ?? null ) ? $status['last'] : null;
		}
		$finished = null !== $last && strtotime( (string) $last['time'] ) >= $job['backup']['requested_at'];
		if ( null !== $running ) {
			$job['backup_progress'] = array(
				'percent' => is_int( $running['progress'] ) ? max( 0, min( 100, $running['progress'] ) ) : null,
				'stage'   => '' === $running['stage'] ? null : substr( $running['stage'], 0, 64 ),
			);
		}
		if ( $finished ) {
			$saved = true === $last['ok'] && ( ! $updraftplus || self::backed_up( $job['backup']['job'], $what ) );
			if ( ! $saved ) {
				return self::stop( $job, 'backup_failed' );
			}
			$job['phase'] = self::after_backup( $job['safe_update'] ?? null );
			return $job;
		}
		if ( time() - $job['backup']['requested_at'] > $job['backup_wait'] * MINUTE_IN_SECONDS ) {
			return self::stop( $job, 'backup_timeout' );
		}

		$job['wait'] = self::POLL_SECONDS;
		return $job;
	}

	/**
	 * Tell the dashboard how far the update is when that changed: its phase, the backup's
	 * progress, and the item it updates. A report that does not arrive is sent again with the
	 * next change; the result says the rest.
	 *
	 * @param array $job The update after a step.
	 * @return array The update, with what was reported.
	 */
	private static function report_progress( array $job ) {
		$client = Sitepulse_Remote_Management::client();
		if ( $job['finished'] || null === $client ) {
			return $job;
		}

		$total    = count( $job['items'] );
		$done     = count( $job['results'] );
		$updating = 'updating' === $job['phase'];
		$progress = array(
			'phase'   => $job['phase'],
			// The baseline and each item; the backup and the visual check have bars of their own.
			'percent' => $updating ? 10 + intdiv( 90 * $done, $total ) : ( 'baseline' === $job['phase'] ? 5 : 0 ),
			'backup'  => 'backup' === $job['phase'] ? ( $job['backup_progress'] ?? array(
				'percent' => 0,
				'stage'   => null,
			) ) : null,
			'done'    => $done,
			'total'   => $total,
			'item'    => $updating ? $job['items'][ $done ] : null,
		);
		if ( ( $job['reported'] ?? null ) === $progress ) {
			return $job;
		}

		if ( ! is_wp_error(
			$client->post(
				'command-progress',
				array(
					'id'       => $job['command'],
					'progress' => $progress,
				)
			)
		) ) {
			$job['reported'] = $progress;
		}

		return $job;
	}

	/**
	 * A safe update updates once the API has scanned the site's pages as they are before the
	 * update, which it does while the backup runs: ask the API until it says to go on or stop.
	 *
	 * @param array $job The update.
	 * @return array
	 */
	private static function visual_step( array $job ) {
		if ( empty( $job['visual_since'] ) ) {
			$job['visual_since'] = time();
		}

		$client = Sitepulse_Remote_Management::client();
		$answer = null === $client ? null : $client->post( 'safe-update', array( 'command' => $job['command'] ) );
		if ( is_array( $answer ) && true === ( $answer['stop'] ?? null ) ) {
			return self::stop( $job, 'visual_check_failed' );
		}
		if ( is_array( $answer ) && true === ( $answer['proceed'] ?? null ) ) {
			$job['phase'] = 'baseline';
			return $job;
		}
		// An API that cannot be reached is asked again, until the scan's time is up.
		if ( time() - $job['visual_since'] >= self::VISUAL_WAIT_SECONDS ) {
			return self::stop( $job, 'visual_check_timeout' );
		}

		$job['wait'] = self::VISUAL_POLL_SECONDS;
		return $job;
	}

	/**
	 * @param string|null $safe_update The safe update the command belongs to, if any.
	 * @return string The phase after the backup: the visual check of a safe update, or the baseline.
	 */
	private static function after_backup( $safe_update ) {
		return null === $safe_update ? 'baseline' : 'visual';
	}

	/**
	 * @param mixed  $parts The command's backup_parts: none, or the folder a safe update backs up.
	 * @param string $type  The command, which updates that folder: plugins or themes, not WordPress.
	 * @return string|null What the backup saves: full, plugins or themes; null when not valid.
	 */
	private static function backup_what( $parts, $type ) {
		if ( null === $parts ) {
			return 'full';
		}
		$folder = self::BACKUP_FOLDERS[ $type ] ?? null;

		return null !== $folder && array( $folder ) === $parts ? $folder : null;
	}

	/**
	 * Whether UpdraftPlus's history has the backup with what it was to save: the database, and
	 * for a safe update the folder it updates.
	 *
	 * @param string $nonce The backup's nonce.
	 * @param string $what  full, plugins or themes.
	 * @return bool
	 */
	private static function backed_up( $nonce, $what ) {
		$set = Sitepulse_Remote_Backups::updraftplus_backup( $nonce );

		return null !== $set && ! array_diff( 'full' === $what ? array( 'db' ) : array( 'db', $what ), array_keys( array_filter( $set ) ) );
	}

	/**
	 * Update the next item and check the site.
	 *
	 * @param array $job The update.
	 * @return array
	 */
	private static function update_step( array $job ) {
		$id = $job['items'][ count( $job['results'] ) ];
		// The request that updated this item stopped before it finished.
		if ( null !== $job['current'] ) {
			$rolled_back = $job['current']['rolled_back'] ?? null;
			if ( null === $rolled_back ) {
				// The request did not even end normally (it was killed): its maintenance file is
				// still there, and WordPress still has the item's previous files; put them back.
				wp_delete_file( ABSPATH . '.maintenance' );
				WP_Filesystem();
				$rolled_back = self::roll_back( self::upgrader( $job['kind'] ), $job['kind'], $id );
			}
			$job['results'][] = self::result( $id, $job['current']['from'], Sitepulse_Remote_Events::installed_version( $job['kind'], $id ), 'interrupted', false, $rolled_back );
			$job['current']   = null;
			return self::stop( $job, 'update_failed' );
		}

		$from           = Sitepulse_Remote_Events::installed_version( $job['kind'], $id );
		$job['current'] = array( 'from' => $from );
		self::save_job( $job );

		$result           = self::update_item( $job, $id, $from );
		$job['current']   = null;
		$job['results'][] = $result;
		if ( ! $result['ok'] && ! in_array( $result['error'], self::SKIPPABLE, true ) ) {
			return self::stop( $job, 'update_failed' );
		}
		if ( count( $job['results'] ) === count( $job['items'] ) ) {
			$failed = in_array( false, array_column( $job['results'], 'ok' ), true );
			return self::stop( $job, $failed ? 'update_failed' : null );
		}

		return $job;
	}

	/**
	 * Update one item with WordPress's upgrader, check the site and roll back a broken update.
	 *
	 * @param array  $job  The update.
	 * @param string $id   Plugin file, theme stylesheet or WordPress version.
	 * @param string $from Installed version.
	 * @return array The item's result.
	 */
	private static function update_item( array $job, $id, $from ) {
		$kind = $job['kind'];
		if ( '' === $from ) {
			return self::result( $id, null, null, 'not_installed' );
		}
		$offer = self::offer( $kind, $id );
		if ( null === $offer ) {
			return self::result( $id, $from, $from, 'up_to_date', 'core' !== $kind || version_compare( $from, $id, '>=' ) );
		}
		if ( ! self::meets_requirements( $offer ) ) {
			return self::result( $id, $from, null, 'requirements' );
		}
		if ( empty( $offer['package'] ) ) {
			return self::result( $id, $from, null, 'no_package' );
		}

		$upgrader = self::upgrader( $kind );
		// A PHP time limit or crash can end this request in the middle of the update. WordPress
		// still runs its shutdown actions then: leave maintenance mode, put the previous version
		// back before WordPress deletes its copy (at priority 100), and free the job for its next step.
		$recover = static function () use ( $upgrader, $kind, $id ) {
			$upgrader->maintenance_mode( false );
			$job = self::job();
			if ( null !== $job && null !== $job['current'] ) {
				$job['current']['rolled_back'] = self::roll_back( $upgrader, $kind, $id );
				self::save_job( $job );
			}
			delete_transient( self::LOCK );
		};
		add_action( 'shutdown', $recover, 1 );
		try {
			return self::install_and_check( $job, $upgrader, $offer, $id, $from );
		} finally {
			remove_action( 'shutdown', $recover, 1 );
		}
	}

	/**
	 * Install an item's update, check the site and roll back a broken update.
	 *
	 * @param array  $job      The update.
	 * @param object $upgrader WordPress's upgrader for the item.
	 * @param array  $offer    The update WordPress offers.
	 * @param string $id       Plugin file, theme stylesheet or WordPress version.
	 * @param string $from     Installed version.
	 * @return array The item's result.
	 */
	private static function install_and_check( array $job, $upgrader, array $offer, $id, $from ) {
		$kind  = $job['kind'];
		$since = (int) current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- SitePulse's error log stores this time.
		// SitePulse reports this update itself, with its health check.
		Sitepulse_Remote_Events::$updating = true;
		// A background update, as WordPress calls one that has no browser to reactivate the plugin:
		// WordPress then keeps an active plugin active while it replaces its files.
		add_filter( 'wp_doing_cron', '__return_true' );
		$upgrader->maintenance_mode( true );
		// Like WordPress's bulk updates, keep the offered updates for the next items until the end.
		$outcome = 'core' === $kind ? $upgrader->upgrade( $offer['offer'], array( 'attempt_rollback' => true ) ) : $upgrader->upgrade( $id, array( 'clear_update_cache' => false ) );
		$upgrader->maintenance_mode( false );
		remove_filter( 'wp_doing_cron', '__return_true' );
		Sitepulse_Remote_Events::$updating = false;

		$to = Sitepulse_Remote_Events::installed_version( $kind, $id );
		if ( ! $outcome || is_wp_error( $outcome ) || $to === $from ) {
			return self::result( $id, $from, $to, 'install_failed' );
		}

		sleep( (int) apply_filters( 'sitepulse_remote_health_check_delay', self::HEALTH_CHECK_DELAY ) );
		$problems = Sitepulse_Remote_Health::problems( $job['baseline'], Sitepulse_Remote_Health::check() );
		if ( ! $problems ) {
			// Fatal errors in the updated code that the checked pages did not show, such as on pages visitors loaded meanwhile.
			$problems = Sitepulse_Remote_Health::logged_fatal_errors( self::path( $kind, $id ), $since );
		}
		if ( ! $problems ) {
			return self::result( $id, $from, $to, null, true );
		}

		$rolled_back = self::roll_back( $upgrader, $kind, $id );
		$result      = self::result( $id, $from, $rolled_back ? Sitepulse_Remote_Events::installed_version( $kind, $id ) : $to, 'health_check_failed', false, $rolled_back );

		$result['problems'] = array_slice( $problems, 0, 5 );
		return $result;
	}

	/**
	 * The update WordPress offers for an item.
	 *
	 * @param string $kind plugin, theme or core.
	 * @param string $id   Plugin file, theme stylesheet or WordPress version.
	 * @return array|null version, package, requires_wp, requires_php and the offer itself.
	 */
	private static function offer( $kind, $id ) {
		if ( 'core' === $kind ) {
			foreach ( (array) get_core_updates() as $update ) {
				if ( is_object( $update ) && 'upgrade' === ( $update->response ?? '' ) && $id === ( $update->current ?? '' ) ) {
					return array(
						'version'      => $id,
						'package'      => $update->packages->full ?? $update->download ?? '',
						'requires_wp'  => null,
						'requires_php' => $update->php_version ?? null,
						'offer'        => $update,
					);
				}
			}
			return null;
		}

		$updates = get_site_transient( 'update_' . $kind . 's' );
		$offer   = is_object( $updates ) && isset( $updates->response[ $id ] ) ? (array) $updates->response[ $id ] : null;
		if ( null === $offer ) {
			return null;
		}

		return array(
			'version'      => (string) ( $offer['new_version'] ?? '' ),
			'package'      => (string) ( $offer['package'] ?? '' ),
			'requires_wp'  => $offer['requires'] ?? null,
			'requires_php' => $offer['requires_php'] ?? null,
			'offer'        => $offer,
		);
	}

	/**
	 * @param array $offer Update offer.
	 * @return bool Whether this site's PHP and WordPress can run the update.
	 */
	private static function meets_requirements( array $offer ) {
		return ( empty( $offer['requires_php'] ) || version_compare( PHP_VERSION, (string) $offer['requires_php'], '>=' ) )
			&& ( empty( $offer['requires_wp'] ) || version_compare( (string) get_bloginfo( 'version' ), (string) $offer['requires_wp'], '>=' ) );
	}

	/**
	 * @param string $kind plugin, theme or core.
	 * @return object WordPress's upgrader, quiet as in its automatic updates.
	 */
	private static function upgrader( $kind ) {
		$skin    = new Automatic_Upgrader_Skin();
		$classes = array(
			'plugin' => 'Plugin_Upgrader',
			'theme'  => 'Theme_Upgrader',
			'core'   => 'Core_Upgrader',
		);

		return new $classes[ $kind ]( $skin );
	}

	/**
	 * Put the previous version back from the copy WordPress (6.3 and later) keeps during an update.
	 *
	 * @param object $upgrader The upgrader that updated the item.
	 * @param string $kind     plugin or theme; WordPress core is not rolled back.
	 * @param string $id       Plugin file or theme stylesheet.
	 * @return bool Whether the previous version is back.
	 */
	private static function roll_back( $upgrader, $kind, $id ) {
		if ( 'core' === $kind || ! method_exists( $upgrader, 'restore_temp_backup' ) ) {
			return false;
		}

		$backup = array(
			array(
				'dir'  => $kind . 's',
				'slug' => 'plugin' === $kind ? dirname( $id ) : $id,
				'src'  => 'plugin' === $kind ? WP_PLUGIN_DIR : get_theme_root( $id ),
			),
		);
		if ( true !== $upgrader->restore_temp_backup( $backup ) ) {
			return false;
		}

		$upgrader->delete_temp_backup( $backup );
		'plugin' === $kind ? wp_clean_plugins_cache() : wp_clean_themes_cache();
		return true;
	}

	/**
	 * @param string $kind plugin, theme or core.
	 * @param string $id   Plugin file, theme stylesheet or WordPress version.
	 * @return string Where the item's code is, for fatal errors in SitePulse's error log.
	 */
	private static function path( $kind, $id ) {
		if ( 'plugin' === $kind ) {
			return WP_PLUGIN_DIR . '/' . ( '.' === dirname( $id ) ? $id : dirname( $id ) . '/' );
		}

		return 'theme' === $kind ? get_theme_root( $id ) . '/' . $id . '/' : ABSPATH . WPINC . '/';
	}

	/**
	 * Stop the update; items not tried yet are skipped.
	 *
	 * @param array       $job   The update.
	 * @param string|null $error Why it stopped, or null when every item is up to date.
	 * @return array
	 */
	private static function stop( array $job, $error ) {
		foreach ( array_slice( $job['items'], count( $job['results'] ) ) as $id ) {
			$job['results'][] = self::result( $id, null, null, 'skipped' );
		}
		$job['finished'] = true;
		$job['error']    = $error;

		return $job;
	}

	/**
	 * Report the update to the dashboard and the event history.
	 *
	 * @param array $job The finished update.
	 */
	private static function finish( array $job ) {
		delete_option( self::JOB_OPTION );
		self::unschedule();
		// WordPress offers updates afresh, as after its own bulk updates.
		if ( 'plugin' === $job['kind'] ) {
			wp_clean_plugins_cache();
			wp_update_plugins();
		} elseif ( 'theme' === $job['kind'] ) {
			wp_clean_themes_cache();
			wp_update_themes();
		}

		$attempted = array_values(
			array_filter(
				$job['results'],
				static function ( $item ) {
					return in_array( $item['error'], self::ATTEMPTED, true );
				}
			)
		);
		foreach ( $attempted as $item ) {
			if ( $item['ok'] && ! $item['rolled_back'] ) {
				Sitepulse_Remote_Activity::watch( $job['kind'], 'core' === $job['kind'] ? 'wordpress' : $item['id'], $item['from'], $item['to'] );
			}
		}
		if ( $attempted ) {
			Sitepulse_Remote_Management::record_event(
				'update.completed',
				array(
					'kind'      => $job['kind'],
					'initiator' => 'sitepulse',
					'items'     => array_map(
						static function ( $item ) {
							return array_intersect_key( $item, array_flip( array( 'id', 'from', 'to', 'ok', 'rolled_back' ) ) );
						},
						$attempted
					),
				)
			);
		}

		Sitepulse_Remote_Management::finish_command(
			$job['command'],
			$job['type'],
			$job['args'],
			Sitepulse_Remote_Management::command_result(
				null === $job['error'] ? 'done' : 'failed',
				array_merge(
					array(
						'backup' => null === $job['backup'] ? null : array_intersect_key( $job['backup'], array_flip( array( 'provider', 'job' ) ) ),
						'items'  => $job['results'],
					),
					// An update started by an older version of the plugin has no backup_skipped.
					empty( $job['backup_skipped'] ) ? array() : array( 'backup_skipped' => $job['backup_skipped'] )
				),
				$job['error']
			)
		);
	}

	/**
	 * Turn WordPress's own automatic updates on or off for installed plugins or themes.
	 *
	 * @param array $args kind, items and enabled.
	 * @return array status, result and error for the API.
	 */
	private static function set_auto_updates( array $args ) {
		$kind    = $args['kind'] ?? null;
		$items   = self::strings( $args['items'] ?? null );
		$enabled = $args['enabled'] ?? null;
		if ( ! in_array( $kind, array( 'plugin', 'theme' ), true ) || ! $items || ! is_bool( $enabled ) ) {
			return self::failed( 'invalid_arguments' );
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$installed = array_map( 'strval', array_keys( 'plugin' === $kind ? get_plugins() : wp_get_themes() ) );
		$option    = 'auto_update_' . $kind . 's';
		$current   = array_map( 'strval', (array) get_site_option( $option, array() ) );
		$items     = array_values( array_intersect( $items, $installed ) );
		$changed   = $enabled ? array_diff( $items, $current ) : array_intersect( $items, $current );
		$settings  = $enabled ? array_merge( $current, $changed ) : array_diff( $current, $changed );
		update_site_option( $option, array_values( $settings ) );

		return Sitepulse_Remote_Management::command_result(
			'done',
			array(
				'kind'    => $kind,
				'enabled' => $enabled,
				'items'   => array_values( $changed ),
			),
			null
		);
	}

	/**
	 * @param string      $id          Item.
	 * @param string|null $from        Version before.
	 * @param string|null $to          Version after.
	 * @param string|null $error       Item error.
	 * @param bool        $ok          Whether the item is up to date afterwards.
	 * @param bool        $rolled_back Whether the previous version was put back.
	 * @return array
	 */
	private static function result( $id, $from, $to, $error, $ok = false, $rolled_back = false ) {
		return array(
			'id'          => $id,
			'from'        => '' === $from ? null : $from,
			'to'          => '' === $to ? null : $to,
			'ok'          => $ok,
			'error'       => $error,
			'rolled_back' => $rolled_back,
		);
	}

	/**
	 * @param string $error Error code.
	 * @return array A failed command.
	 */
	private static function failed( $error ) {
		return Sitepulse_Remote_Management::command_result( 'failed', null, $error );
	}

	/**
	 * @param mixed $value A list from the command.
	 * @return array Its distinct strings; empty when it is not a list of non-empty strings.
	 */
	private static function strings( $value ) {
		$value = is_array( $value ) ? array_values( $value ) : array();
		foreach ( $value as $item ) {
			if ( ! is_string( $item ) || '' === $item ) {
				return array();
			}
		}

		return array_values( array_unique( $value ) );
	}

	/**
	 * @return array|null The update being run.
	 */
	private static function job() {
		$job = get_option( self::JOB_OPTION, null );
		return is_array( $job ) && isset( $job['command'], $job['items'], $job['results'] ) ? $job : null;
	}

	/**
	 * @param array $job The update.
	 */
	private static function save_job( array $job ) {
		update_option( self::JOB_OPTION, $job, false );
	}

	/**
	 * @param int $seconds Seconds until the next request continues the update.
	 */
	private static function schedule( $seconds ) {
		wp_clear_scheduled_hook( self::JOB_HOOK );
		wp_schedule_single_event( time() + $seconds, self::JOB_HOOK );
	}

	/**
	 * Cron requests do not load WordPress's admin API or its upgrader.
	 */
	public static function load_admin_api() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/update.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	}
}

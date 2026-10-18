<?php
/**
 * SitePulse remote management backups
 *
 * Detects UpdraftPlus and BackWPup, reads their schedules, running job and recent
 * backups for the SitePulse dashboard, reports each finished backup, and runs the
 * dashboard's backup commands through each plugin's own code.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Remote_Backups
 */
class Sitepulse_Remote_Backups {

	/** Oldest UpdraftPlus release remote management works with. */
	const UPDRAFTPLUS_MIN_VERSION = '1.26';

	/** BackWPup major release remote management works with. */
	const BACKWPUP_MAJOR = '5';

	/** Recent backups listed in the inventory. */
	const RECENT_LIMIT = 10;

	/** Keys of the file archives in an UpdraftPlus backup; database keys are db, db1, db2... */
	const UPDRAFTPLUS_FILE_ENTITIES = array( 'plugins', 'themes', 'uploads', 'mu-plugins', 'others', 'wpcore', 'more' );

	/** What each BackWPup job type saves. */
	const BACKWPUP_CONTENTS = array(
		'DBDUMP'   => 'database',
		'FILE'     => 'files',
		'WPEXP'    => 'content_export',
		'WPPLUGIN' => 'plugin_list',
		'DBCHECK'  => 'database_check',
	);

	/** Dashboard commands this class runs. */
	const COMMANDS = array( 'backup_now', 'cancel_backup', 'apply_backup_preset' );

	/** UpdraftPlus's "Backup Now" hooks, by what the backup saves. */
	const UPDRAFTPLUS_BACKUP_NOW = array(
		'full'     => 'updraft_backupnow_backup_all',
		'files'    => 'updraft_backupnow_backup',
		'database' => 'updraft_backupnow_backup_database',
		'plugins'  => 'updraft_backupnow_backup_all',
		'themes'   => 'updraft_backupnow_backup_all',
	);

	/** Backups of one folder, which also save the database (a safe update backs up what it updates). */
	const FOLDER_BACKUPS = array( 'plugins', 'themes' );

	/** UpdraftPlus settings of the parts a files backup includes. */
	const UPDRAFTPLUS_PARTS = array(
		'plugins' => 'updraft_include_plugins',
		'themes'  => 'updraft_include_themes',
		'uploads' => 'updraft_include_uploads',
		'other'   => 'updraft_include_others',
	);

	/** BackWPup job settings of the same parts. */
	const BACKWPUP_PARTS = array(
		'plugins' => 'backupplugins',
		'themes'  => 'backupthemes',
		'uploads' => 'backupuploads',
		'other'   => 'backupcontent',
	);

	/** BackWPup job settings of every folder a files backup can save. */
	const BACKWPUP_FOLDERS = array( 'backuproot', 'backupcontent', 'backupplugins', 'backupthemes', 'backupuploads', 'backupspecialfiles' );

	/** Preset frequencies from least to most frequent; manual turns a schedule off. */
	const PRESET_FREQUENCIES = array( 'manual', 'monthly', 'weekly', 'daily' );

	/** Most backups a preset may keep. */
	const PRESET_MAX_KEEP = 100;

	/** Names of the BackWPup jobs SitePulse creates, by what they save. */
	const BACKWPUP_JOB_NAMES = array(
		'files'          => 'Files (SitePulse)',
		'database'       => 'Database (SitePulse)',
		'files,database' => 'Files and database (SitePulse)',
	);

	/**
	 * Report finished backups.
	 */
	public static function init() {
		add_filter( 'updraftplus_save_last_backup', array( __CLASS__, 'updraftplus_finished' ) );
		add_action( 'backwpup_end_job', array( __CLASS__, 'backwpup_finished' ), 10, 3 );
	}

	/**
	 * The backup plugin SitePulse manages: the one picked in the dashboard while it is
	 * active and supported; otherwise the supported one with the most recent successful
	 * backup, UpdraftPlus on a tie; an unsupported one only when no other is active.
	 *
	 * @return array|null id, version and supported; null when neither plugin is active.
	 */
	public static function provider() {
		$providers = self::providers();
		$supported = array_values(
			array_filter(
				$providers,
				static function ( $provider ) {
					return $provider['supported'];
				}
			)
		);
		if ( ! $supported ) {
			return $providers ? $providers[0] : null;
		}

		$preferred = Sitepulse_Remote_Management::state()['preferred_provider'];
		foreach ( $supported as $provider ) {
			if ( $provider['id'] === $preferred ) {
				return $provider;
			}
		}

		return isset( $supported[1] ) && self::last_success( 'backwpup' ) > self::last_success( 'updraftplus' ) ? $supported[1] : $supported[0];
	}

	/**
	 * @return array Every active backup plugin, UpdraftPlus first.
	 */
	public static function providers() {
		return array_values( array_filter( array( self::updraftplus(), self::backwpup() ) ) );
	}

	/**
	 * Run a backup command from the dashboard with the managed backup plugin.
	 *
	 * @param string $type backup_now, cancel_backup or apply_backup_preset.
	 * @param array  $args Command arguments.
	 * @return array status, result and error for the API.
	 */
	public static function run_command( $type, array $args ) {
		$provider = self::provider();
		if ( null === $provider || ! $provider['supported'] ) {
			return Sitepulse_Remote_Management::command_result( 'failed', null, 'no_backup_plugin' );
		}

		$updraftplus = 'updraftplus' === $provider['id'];
		try {
			if ( 'backup_now' === $type ) {
				$what    = $args['what'] ?? 'full';
				$outcome = is_string( $what ) && isset( self::UPDRAFTPLUS_BACKUP_NOW[ $what ] )
					? ( $updraftplus ? self::updraftplus_backup_now( $what ) : self::backwpup_backup_now( $what ) )
					: 'invalid_arguments';
			} elseif ( 'cancel_backup' === $type ) {
				$outcome = array( 'cancelled' => $updraftplus ? self::updraftplus_cancel() : self::backwpup_cancel() );
			} else {
				$preset  = self::preset( $args['preset'] ?? null );
				$outcome = null === $preset ? 'invalid_preset' : array(
					'preset'  => is_string( $args['name'] ?? null ) ? $args['name'] : '',
					'created' => $updraftplus ? self::updraftplus_apply( $preset ) : self::backwpup_apply( $preset ),
				);
			}
		} catch ( Throwable $e ) {
			$outcome = 'backup_plugin_error';
		}

		if ( is_string( $outcome ) ) {
			return Sitepulse_Remote_Management::command_result( 'failed', null, $outcome );
		}
		if ( 'apply_backup_preset' === $type ) {
			$outcome['schedules'] = self::status()['schedules'] ?? array();
		}

		return Sitepulse_Remote_Management::command_result( 'done', array( 'provider' => $provider['id'] ) + $outcome, null );
	}

	/**
	 * Backup status for the inventory.
	 *
	 * @return array|null Null when no backup plugin is active.
	 */
	public static function status() {
		$provider = self::provider();
		if ( null === $provider || ! $provider['supported'] ) {
			return null === $provider ? null : array( 'provider' => $provider );
		}

		try {
			$status = 'updraftplus' === $provider['id'] ? self::updraftplus_status() : self::backwpup_status();
		} catch ( Throwable $e ) {
			// A failing backup plugin must not stop the rest of the inventory.
			$status = array( 'error' => 'status_unavailable' );
		}

		return array_merge( array( 'provider' => $provider ), $status );
	}

	/**
	 * Report a finished UpdraftPlus backup; UpdraftPlus saves its result through this filter.
	 *
	 * @param mixed $last_backup UpdraftPlus's record of the backup.
	 * @return mixed The record, unchanged.
	 */
	public static function updraftplus_finished( $last_backup ) {
		if ( is_array( $last_backup ) && ! self::updraftplus_resuming( $last_backup ) ) {
			$problems = self::updraftplus_problems( $last_backup['errors'] ?? array() );
			Sitepulse_Remote_Management::record_event(
				'backup.completed',
				array(
					'provider' => 'updraftplus',
					'ok'       => ! empty( $last_backup['success'] ),
					'errors'   => $problems['errors'],
					'warnings' => $problems['warnings'],
					'size'     => self::updraftplus_size( (array) ( $last_backup['backup_array'] ?? array() ) ),
					'time'     => self::time( $last_backup['backup_time'] ?? time() ),
				)
			);
		}

		return $last_backup;
	}

	/**
	 * Whether UpdraftPlus saved the record of a run that had errors but will try again: it saves
	 * one at the end of every run, and only the last run of a backup finishes it.
	 *
	 * @param array $last_backup UpdraftPlus's record of the run.
	 * @return bool
	 */
	private static function updraftplus_resuming( array $last_backup ) {
		$updraftplus = $GLOBALS['updraftplus'] ?? null;
		if ( ! empty( $last_backup['success'] ) || ! is_object( $updraftplus ) || empty( $updraftplus->newresumption_scheduled ) ) {
			return false;
		}

		// UpdraftPlus also schedules a resumption that only reports the failure ("fail_on_resume").
		return (int) $updraftplus->current_resumption + 1 !== (int) $updraftplus->jobdata_get( 'fail_on_resume' );
	}

	/**
	 * Report a finished BackWPup job.
	 *
	 * @param array  $job         Job settings.
	 * @param string $backup_file Archive name.
	 * @param object $job_object  The finished BackWPup_Job.
	 */
	public static function backwpup_finished( $job, $backup_file, $job_object ) {
		Sitepulse_Remote_Management::record_event(
			'backup.completed',
			array(
				'provider' => 'backwpup',
				'ok'       => 0 === (int) $job_object->errors,
				'errors'   => (int) $job_object->errors,
				'warnings' => (int) $job_object->warnings,
				'size'     => (int) $job_object->backup_filesize,
				'time'     => self::time( time() ),
			)
		);
	}

	/**
	 * @return array|null UpdraftPlus as a provider, when it is active.
	 */
	private static function updraftplus() {
		$plugin  = $GLOBALS['updraftplus'] ?? null;
		$version = is_object( $plugin ) && class_exists( 'UpdraftPlus_Options', false ) ? (string) ( $plugin->version ?? '' ) : '';
		if ( '' === $version ) {
			return null;
		}

		return array(
			'id'        => 'updraftplus',
			'version'   => $version,
			'supported' => version_compare( $version, self::UPDRAFTPLUS_MIN_VERSION, '>=' ),
		);
	}

	/**
	 * @return array|null BackWPup as a provider, when it is active.
	 */
	private static function backwpup() {
		$version = class_exists( 'BackWPup', false ) ? (string) BackWPup::get_plugin_data( 'version' ) : '';
		if ( '' === $version ) {
			return null;
		}

		return array(
			'id'        => 'backwpup',
			'version'   => $version,
			'supported' => self::BACKWPUP_MAJOR === explode( '.', $version )[0],
		);
	}

	/**
	 * UpdraftPlus keeps one schedule for files and one for the database.
	 *
	 * @return array
	 */
	private static function updraftplus_status() {
		$files_frequency = (string) UpdraftPlus_Options::get_updraft_option( 'updraft_interval', 'manual' );
		$files_keep      = (int) UpdraftPlus_Options::get_updraft_option( 'updraft_retain', 2 );
		$destinations    = self::updraftplus_destinations( UpdraftPlus_Options::get_updraft_option( 'updraft_service', array() ) );
		$next_files      = wp_next_scheduled( 'updraft_backup' );
		$next_database   = wp_next_scheduled( 'updraft_backup_database' );
		$next            = array_filter( array( $next_files, $next_database ) );

		return array(
			'schedules' => array(
				array(
					'name'         => 'Files',
					'contents'     => array( 'files' ),
					'frequency'    => $files_frequency,
					'next'         => self::time( $next_files ),
					'keep'         => $files_keep,
					'destinations' => $destinations,
				),
				array(
					'name'         => 'Database',
					'contents'     => array( 'database' ),
					'frequency'    => (string) UpdraftPlus_Options::get_updraft_option( 'updraft_interval_database', $files_frequency ),
					'next'         => self::time( $next_database ),
					'keep'         => (int) UpdraftPlus_Options::get_updraft_option( 'updraft_retain_db', $files_keep ),
					'destinations' => $destinations,
				),
			),
			'running'   => self::updraftplus_running(),
			'last'      => self::updraftplus_last( UpdraftPlus_Options::get_updraft_option( 'updraft_last_backup', array() ) ),
			'next'      => $next ? self::time( min( $next ) ) : null,
			'recent'    => self::updraftplus_recent(),
		);
	}

	/**
	 * @return array|null The UpdraftPlus job that is running.
	 */
	private static function updraftplus_running() {
		$jobs = self::updraftplus_jobs();
		$job  = current( $jobs );
		if ( false === $job ) {
			return null;
		}

		return array(
			'name'     => (string) ( $job['label'] ?? '' ),
			'progress' => self::updraftplus_progress( $job ),
			'stage'    => (string) ( $job['jobstatus'] ?? '' ),
		);
	}

	/**
	 * How far an UpdraftPlus backup is, as UpdraftPlus's own progress bar counts it: six stages
	 * (file archives, database, upload, pruning), with the progress UpdraftPlus records in each.
	 *
	 * @param array $job UpdraftPlus's job data.
	 * @return int Percent.
	 */
	private static function updraftplus_progress( array $job ) {
		$status   = (string) ( $job['jobstatus'] ?? '' );
		$fraction = static function ( $substatus, $done, $total ) {
			$substatus = is_array( $substatus ) ? $substatus : array();
			return min( 1, (float) ( $substatus[ $done ] ?? 0 ) / max( 1, (float) ( $substatus[ $total ] ?? 1 ) ) );
		};
		$fixed    = array(
			'filescreated' => 2,
			'pruning'      => 5,
			'finished'     => 6,
		);

		if ( isset( $fixed[ $status ] ) ) {
			$stage = $fixed[ $status ];
		} elseif ( 'filescreating' === $status ) {
			$stage = 1 + $fraction( $job['filecreating_substatus'] ?? null, 'i', 't' );
		} elseif ( 0 === strpos( $status, 'dbcreating' ) ) {
			$stage = 2 + $fraction( $job['dbcreating_substatus'] ?? null, 'i', 'a' );
		} elseif ( 0 === strpos( $status, 'dbencrypting' ) ) {
			$stage = 3;
		} elseif ( 0 === strpos( $status, 'db' ) ) {
			$stage = 4;
		} elseif ( 'clouduploading' === $status ) {
			$stage = 4 + $fraction( $job['uploading_substatus'] ?? null, 'i', 't' );
		} else {
			$stage = 0;
		}

		return (int) min( 100, ceil( 100 / 6 * $stage ) );
	}

	/**
	 * A running UpdraftPlus job keeps its next resumption scheduled.
	 *
	 * @return array Job data of the running jobs, keyed by their nonce.
	 */
	private static function updraftplus_jobs() {
		$jobs = array();
		foreach ( (array) _get_cron_array() as $hooks ) {
			foreach ( (array) ( $hooks['updraft_backup_resume'] ?? array() ) as $event ) {
				$nonce = (string) ( $event['args'][1] ?? '' );
				$job   = get_site_option( 'updraft_jobdata_' . $nonce, array() );
				if ( is_array( $job ) && $job && 'finished' !== ( $job['jobstatus'] ?? '' ) ) {
					$jobs[ $nonce ] = $job;
				}
			}
		}

		return $jobs;
	}

	/**
	 * @return array "Backup Now" events SitePulse queued that have not started: timestamp, hook and args.
	 */
	private static function updraftplus_queued() {
		$queued = array();
		foreach ( (array) _get_cron_array() as $timestamp => $hooks ) {
			foreach ( array_unique( self::UPDRAFTPLUS_BACKUP_NOW ) as $hook ) {
				foreach ( (array) ( $hooks[ $hook ] ?? array() ) as $event ) {
					$queued[] = array( $timestamp, $hook, $event['args'] ?? array() );
				}
			}
		}

		return $queued;
	}

	/**
	 * Queue UpdraftPlus's own "Backup Now" in WordPress cron, so the check-in is not held up
	 * while the backup runs. It uses the storage set up in UpdraftPlus.
	 *
	 * @param string $what full, files, database, plugins or themes.
	 * @return array|string Result, or an error code.
	 */
	private static function updraftplus_backup_now( $what ) {
		if ( self::updraftplus_jobs() || self::updraftplus_queued() ) {
			return 'backup_running';
		}

		$nonce   = $GLOBALS['updraftplus']->backup_time_nonce();
		$options = array(
			'nocloud'     => false,
			'use_nonce'   => $nonce,
			'label'       => 'SitePulse',
			'always_keep' => false,
		);
		// Like UpdraftPlus's "Backup Now" with only that folder ticked, with the database.
		if ( in_array( $what, self::FOLDER_BACKUPS, true ) ) {
			$options['restrict_files_to_override'] = array( $what );
		}
		wp_schedule_single_event( time(), self::UPDRAFTPLUS_BACKUP_NOW[ $what ], array( $options ) );

		return array(
			'what' => $what,
			'job'  => $nonce,
		);
	}

	/**
	 * Remove queued backups and stop running ones, as UpdraftPlus's own "Delete" link does.
	 *
	 * @return int Backups stopped.
	 */
	private static function updraftplus_cancel() {
		$cancelled = 0;
		foreach ( self::updraftplus_queued() as $event ) {
			wp_unschedule_event( $event[0], $event[1], $event[2] );
			++$cancelled;
		}

		$jobs = self::updraftplus_jobs();
		if ( $jobs ) {
			// UpdraftPlus keeps this in its admin code, which it loads the same way for its own remote commands.
			updraft_try_include_file( 'admin.php', 'include_once' );
			foreach ( array_keys( $jobs ) as $nonce ) {
				$result     = $GLOBALS['updraftplus_admin']->activejobs_delete( (string) $nonce );
				$cancelled += 'deleted' === ( $result['c'] ?? '' ) ? 1 : 0;
			}
		}

		return $cancelled;
	}

	/**
	 * Apply a preset. UpdraftPlus reschedules backups when its settings screen saves, so
	 * its schedulers are called directly before the settings change.
	 *
	 * @param array $preset Validated preset.
	 * @return array Jobs created: none for UpdraftPlus.
	 */
	private static function updraftplus_apply( array $preset ) {
		$updraftplus = $GLOBALS['updraftplus'];
		if ( isset( $preset['files'] ) ) {
			UpdraftPlus_Options::update_updraft_option( 'updraft_interval', $updraftplus->schedule_backup( $preset['files']['frequency'] ) );
			UpdraftPlus_Options::update_updraft_option( 'updraft_retain', $preset['files']['keep'] );
			foreach ( isset( $preset['files']['include'] ) ? self::UPDRAFTPLUS_PARTS : array() as $part => $option ) {
				UpdraftPlus_Options::update_updraft_option( $option, in_array( $part, $preset['files']['include'], true ) ? 1 : 0 );
			}
		}
		if ( isset( $preset['database'] ) ) {
			UpdraftPlus_Options::update_updraft_option( 'updraft_interval_database', $updraftplus->schedule_backup_database( $preset['database']['frequency'] ) );
			UpdraftPlus_Options::update_updraft_option( 'updraft_retain_db', $preset['database']['keep'] );
		}

		return array();
	}

	/**
	 * @param mixed $last UpdraftPlus's record of its last backup.
	 * @return array|null
	 */
	private static function updraftplus_last( $last ) {
		if ( ! is_array( $last ) || empty( $last['backup_time'] ) ) {
			return null;
		}

		$problems = self::updraftplus_problems( $last['errors'] ?? array() );
		return array(
			'time'     => self::time( $last['backup_time'] ),
			'ok'       => ! empty( $last['success'] ),
			'errors'   => $problems['errors'],
			'warnings' => $problems['warnings'],
		);
	}

	/**
	 * @return array The newest backups in UpdraftPlus's history.
	 */
	private static function updraftplus_recent() {
		// UpdraftPlus keys each backup by its time.
		$history = UpdraftPlus_Options::get_updraft_option( 'updraft_backup_history', array() );
		$history = array_filter( is_array( $history ) ? $history : array(), 'is_int', ARRAY_FILTER_USE_KEY );
		krsort( $history );

		$recent = array();
		foreach ( array_slice( $history, 0, self::RECENT_LIMIT, true ) as $time => $backup ) {
			$backup   = (array) $backup;
			$recent[] = array(
				'time'         => self::time( $time ),
				'contents'     => self::updraftplus_contents( $backup ),
				'size'         => self::updraftplus_size( $backup ),
				'destinations' => self::updraftplus_destinations( $backup['service'] ?? array() ),
				'label'        => (string) ( $backup['label'] ?? '' ),
			);
		}

		return $recent;
	}

	/**
	 * @param array $backup An UpdraftPlus backup.
	 * @return array database and/or files.
	 */
	private static function updraftplus_contents( array $backup ) {
		$keys     = array_map( 'strval', array_keys( $backup ) );
		$contents = array();
		if ( preg_grep( '/^db\d*$/', $keys ) ) {
			$contents[] = 'database';
		}
		if ( array_intersect( self::UPDRAFTPLUS_FILE_ENTITIES, $keys ) ) {
			$contents[] = 'files';
		}

		return $contents;
	}

	/**
	 * @param array $backup An UpdraftPlus backup; each archive has an "<archive>-size" entry.
	 * @return int Total size in bytes.
	 */
	private static function updraftplus_size( array $backup ) {
		$size = 0;
		foreach ( $backup as $key => $value ) {
			if ( '-size' === substr( (string) $key, -5 ) && is_numeric( $value ) ) {
				$size += (int) $value;
			}
		}

		return $size;
	}

	/**
	 * @param mixed $service UpdraftPlus storage setting: a method, a list of methods or "none".
	 * @return array Remote storage methods; empty when backups stay on this server.
	 */
	private static function updraftplus_destinations( $service ) {
		$services = array_map( 'strtolower', array_filter( (array) $service, 'is_string' ) );
		return array_values( array_unique( array_diff( $services, array( '', 'none' ) ) ) );
	}

	/**
	 * UpdraftPlus records errors as strings and warnings as arrays with a level.
	 *
	 * @param mixed $errors UpdraftPlus's errors of one backup.
	 * @return array errors and warnings counts.
	 */
	private static function updraftplus_problems( $errors ) {
		$counts = array(
			'errors'   => 0,
			'warnings' => 0,
		);
		foreach ( (array) $errors as $error ) {
			$warning = is_array( $error ) && 'warning' === ( $error['level'] ?? '' );
			$counts[ $warning ? 'warnings' : 'errors' ]++;
		}

		return $counts;
	}

	/**
	 * BackWPup runs jobs, each with its own contents, schedule and destinations.
	 *
	 * @return array
	 */
	private static function backwpup_status() {
		$jobs      = self::backwpup_jobs();
		$schedules = array();
		$recent    = array();
		$next      = array();

		foreach ( $jobs as $job_id => $job ) {
			$contents     = self::backwpup_contents( $job['type'] ?? array() );
			$destinations = self::backwpup_job_destinations( $job );
			$scheduled    = in_array( $job['activetype'] ?? '', array( 'wpcron', 'easycron' ), true );
			$job_next     = $scheduled ? wp_next_scheduled( 'backwpup_cron', array( 'arg' => $job_id ) ) : false;
			// BackWPup's "Backup Now" job is not a schedule, but its archives are backups.
			if ( empty( $job['backup_now'] ) ) {
				$schedules[] = array(
					'name'         => (string) ( $job['name'] ?? '' ),
					'contents'     => $contents,
					'frequency'    => $scheduled ? (string) ( $job['frequency'] ?? 'custom' ) : 'manual',
					'next'         => self::time( $job_next ),
					'keep'         => isset( $job['maxbackups'] ) ? (int) $job['maxbackups'] : null,
					'destinations' => array_map( 'strtolower', $destinations ),
				);
			}
			if ( $job_next ) {
				$next[] = $job_next;
			}

			foreach ( $destinations as $destination ) {
				$recent = array_merge( $recent, self::backwpup_backups( $job_id, $job, $destination, $contents ) );
			}
		}

		// Jobs that share a folder list the same archives.
		$recent = array_values( array_column( $recent, null, 'key' ) );
		usort(
			$recent,
			static function ( $a, $b ) {
				return $b['time'] <=> $a['time'];
			}
		);

		return array(
			'schedules' => $schedules,
			'running'   => self::backwpup_running(),
			'last'      => self::backwpup_last( self::backwpup_last_run( $jobs ) ),
			'next'      => $next ? self::time( min( $next ) ) : null,
			'recent'    => array_map(
				static function ( $backup ) {
					unset( $backup['key'] );
					$backup['time'] = self::time( $backup['time'] );
					return $backup;
				},
				array_slice( $recent, 0, self::RECENT_LIMIT )
			),
		);
	}

	/**
	 * @param int    $job_id      BackWPup job id.
	 * @param array  $job         Job settings.
	 * @param string $destination Destination key, such as FOLDER or S3.
	 * @param array  $contents    What the job saves.
	 * @return array Archives the destination holds for the job.
	 */
	private static function backwpup_backups( $job_id, array $job, $destination, array $contents ) {
		$handler = BackWPup::get_destination( $destination );
		if ( ! is_object( $handler ) ) {
			return array();
		}

		$backups = array();
		foreach ( (array) $handler->file_get_list( $job_id . '_' . $destination ) as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$backups[] = array(
				'key'          => $destination . '|' . ( $item['file'] ?? '' ),
				'time'         => (int) ( $item['time'] ?? 0 ),
				'contents'     => $contents,
				'size'         => (int) ( $item['filesize'] ?? 0 ),
				'destinations' => array( strtolower( $destination ) ),
				'label'        => (string) ( $job['name'] ?? '' ),
			);
		}

		return $backups;
	}

	/**
	 * @return array|null The job BackWPup is running.
	 */
	private static function backwpup_running() {
		$job = BackWPup_Job::get_working_data();
		if ( ! is_object( $job ) ) {
			return null;
		}

		return array(
			'name'     => (string) ( $job->job['name'] ?? '' ),
			'progress' => (int) $job->step_percent,
			'stage'    => (string) $job->step_working,
		);
	}

	/**
	 * @param array|null $last Time and log file of the job that ran last.
	 * @return array|null
	 */
	private static function backwpup_last( $last ) {
		if ( null === $last ) {
			return null;
		}

		// The result is in the header of the job's log.
		$header = '' === $last['logfile'] ? array() : (array) BackWPup_Job::read_logheader( $last['logfile'] );
		$known  = is_numeric( $header['errors'] ?? null );
		return array(
			'time'     => self::time( $last['ran'] ),
			'ok'       => $known ? 0 === (int) $header['errors'] : null,
			'errors'   => $known ? (int) $header['errors'] : null,
			'warnings' => $known ? (int) ( $header['warnings'] ?? 0 ) : null,
		);
	}

	/**
	 * @return array BackWPup jobs by id, without the temporary jobs it runs once.
	 */
	private static function backwpup_jobs() {
		$jobs = array();
		foreach ( (array) BackWPup_Option::get_job_ids() as $job_id ) {
			$job = BackWPup_Option::get_job( $job_id );
			if ( is_array( $job ) && empty( $job['tempjob'] ) ) {
				$jobs[ $job_id ] = $job;
			}
		}

		return $jobs;
	}

	/**
	 * @param array $job Job settings.
	 * @return array The job's storage keys, uppercase like BackWPup's.
	 */
	private static function backwpup_job_destinations( array $job ) {
		return array_values( array_map( 'strtoupper', array_filter( (array) ( $job['destinations'] ?? array() ), 'is_string' ) ) );
	}

	/**
	 * @param array $jobs BackWPup jobs by id.
	 * @return array|null Time (UTC) and log file of the job that ran last.
	 */
	private static function backwpup_last_run( array $jobs ) {
		// BackWPup stores job run times in the site's local time.
		$offset = (int) ( (float) get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
		$last   = null;
		foreach ( $jobs as $job ) {
			$ran = (int) ( $job['lastrun'] ?? 0 ) - $offset;
			if ( ! empty( $job['lastrun'] ) && ( null === $last || $ran > $last['ran'] ) ) {
				$last = array(
					'ran'     => $ran,
					'logfile' => (string) ( $job['logfile'] ?? '' ),
				);
			}
		}

		return $last;
	}

	/**
	 * Run BackWPup's own "Backup Now" job, which keeps the archive on this server like its button.
	 *
	 * @param string $what full, files, database, plugins or themes.
	 * @return array|string Result, or an error code.
	 */
	private static function backwpup_backup_now( $what ) {
		if ( is_object( BackWPup_Job::get_working_data() ) ) {
			return 'backup_running';
		}

		// The job writes this file once it runs; BackWPup checks it the same way after its own "Backup Now".
		$started = trailingslashit( BackWPup::get_plugin_data( 'TEMP' ) ) . '.backwpup_job_started';
		if ( file_exists( $started ) ) {
			wp_delete_file( $started );
		}

		$job_id = self::backwpup_backup_now_job( $what );
		BackWPup_Job::get_jobrun_url( 'runnow', $job_id );
		for ( $wait = 0; $wait < 10 && ! file_exists( $started ); $wait++ ) {
			usleep( 100000 );
		}
		if ( ! file_exists( $started ) ) {
			return 'start_failed';
		}
		wp_delete_file( $started );

		return array(
			'what' => $what,
			'job'  => $job_id,
		);
	}

	/**
	 * Prepare BackWPup's "Backup Now" job with the settings its own button uses.
	 *
	 * @param string $what full, files, database, plugins or themes.
	 * @return int Job id.
	 */
	private static function backwpup_backup_now_job( $what ) {
		$job_id = (int) get_site_option( 'backwpup_backup_now_job_id', 0 );
		if ( $job_id < 1 ) {
			$job_id = (int) BackWPup_Option::next_job_id();
			update_site_option( 'backwpup_backup_now_job_id', $job_id );
		}

		$types    = array(
			'full'     => BackWPup_JobTypes::$type_job_both,
			'files'    => BackWPup_JobTypes::$type_job_files,
			'database' => BackWPup_JobTypes::$type_job_database,
			'plugins'  => BackWPup_JobTypes::$type_job_both,
			'themes'   => BackWPup_JobTypes::$type_job_both,
		);
		$settings = array_merge(
			BackWPup_Option::defaults_job(),
			BackWPup::get_destination( 'FOLDER' )->option_defaults(),
			array(
				'jobid'              => $job_id,
				'name'               => 'Backup Now',
				'type'               => $types[ $what ],
				'destinations'       => array( 'FOLDER' ),
				'activetype'         => '',
				'backupsyncnodelete' => false,
				'tempjob'            => false,
				'backup_now'         => true,
			)
		);
		// A folder backup saves only that folder with the database; the others save every folder.
		foreach ( self::BACKWPUP_FOLDERS as $option ) {
			$settings[ $option ] = ! in_array( $what, self::FOLDER_BACKUPS, true ) || self::BACKWPUP_PARTS[ $what ] === $option;
		}
		foreach ( $settings as $key => $value ) {
			BackWPup_Option::update( $job_id, $key, $value );
		}

		return $job_id;
	}

	/**
	 * @return int 1 when the running job was aborted, 0 when none was running.
	 */
	private static function backwpup_cancel() {
		if ( ! is_object( BackWPup_Job::get_working_data() ) ) {
			return 0;
		}

		BackWPup_Job::user_abort();
		return 1;
	}

	/**
	 * Apply a preset to the jobs that save files or the database, and create a job for
	 * what no job saves yet, with the storage of the existing jobs.
	 *
	 * @param array $preset Validated preset.
	 * @return array Names of the jobs created.
	 */
	private static function backwpup_apply( array $preset ) {
		$jobs  = array_filter(
			self::backwpup_jobs(),
			static function ( $job ) {
				return empty( $job['backup_now'] );
			}
		);
		$saved = array();
		foreach ( $jobs as $job_id => $job ) {
			$kinds = self::backwpup_kinds( $job );
			$saved = array_merge( $saved, $kinds );
			if ( array_intersect_key( $preset, array_flip( $kinds ) ) ) {
				self::backwpup_configure( $job_id, $job, $preset, $kinds );
			}
		}

		$missing = array();
		foreach ( $preset as $kind => $settings ) {
			if ( ! in_array( $kind, $saved, true ) && 'manual' !== $settings['frequency'] ) {
				$missing[] = $kind;
			}
		}
		// One job for both, as BackWPup's own setup does, when they share a schedule.
		$groups = 2 === count( $missing ) && $preset['files']['frequency'] === $preset['database']['frequency']
			? array( $missing )
			: array_chunk( $missing, 1 );

		$created = array();
		foreach ( $groups as $kinds ) {
			$name  = self::BACKWPUP_JOB_NAMES[ implode( ',', $kinds ) ];
			$types = 2 === count( $kinds ) ? BackWPup_JobTypes::$type_job_both : ( 'files' === $kinds[0] ? BackWPup_JobTypes::$type_job_files : BackWPup_JobTypes::$type_job_database );
			$id    = BackWPup_Option::create_default_jobs( $name, $types );
			BackWPup_Option::update( $id, 'destinations', self::backwpup_storage( $jobs ) );
			self::backwpup_configure( $id, (array) BackWPup_Option::get_job( $id ), $preset, $kinds );
			$created[] = $name;
		}

		return $created;
	}

	/**
	 * Schedule a job, set how many backups each of its storages keeps and what it includes.
	 * A job that saves both files and the database gets the more frequent schedule and the
	 * larger retention.
	 *
	 * @param int   $job_id Job id.
	 * @param array $job    Job settings.
	 * @param array $preset Validated preset.
	 * @param array $kinds  files and/or database: what the job saves.
	 */
	private static function backwpup_configure( $job_id, array $job, array $preset, array $kinds ) {
		$settings  = array_intersect_key( $preset, array_flip( $kinds ) );
		$frequency = 'manual';
		foreach ( $settings as $part ) {
			if ( array_search( $part['frequency'], self::PRESET_FREQUENCIES, true ) > array_search( $frequency, self::PRESET_FREQUENCIES, true ) ) {
				$frequency = $part['frequency'];
			}
		}
		$keep = max( array_column( $settings, 'keep' ) );
		$arg  = array( 'arg' => (int) $job_id );

		wp_clear_scheduled_hook( 'backwpup_cron', $arg );
		if ( 'manual' === $frequency ) {
			BackWPup_Option::update( $job_id, 'activetype', '' );
		} else {
			// Keep the job's time of day when its frequency does not change.
			$cron = ( $job['frequency'] ?? '' ) === $frequency && ! empty( $job['cron'] ) ? (string) $job['cron'] : BackWPup_Cron::get_basic_cron_expression( $frequency );
			foreach ( array( 'frequency' => $frequency, 'cronselect' => 'basic', 'cron' => $cron, 'activetype' => 'wpcron' ) as $key => $value ) {
				BackWPup_Option::update( $job_id, $key, $value );
			}
			wp_schedule_single_event( BackWPup_Cron::cron_next( $cron ), 'backwpup_cron', $arg );
		}

		foreach ( self::backwpup_job_destinations( (array) BackWPup_Option::get_job( $job_id ) ) as $destination ) {
			$handler = BackWPup::get_destination( $destination );
			foreach ( is_object( $handler ) ? array_keys( $handler->option_defaults() ) : array() as $option ) {
				if ( 'maxbackups' === substr( $option, -10 ) ) {
					BackWPup_Option::update( $job_id, $option, $keep );
				}
			}
		}

		foreach ( isset( $settings['files']['include'] ) ? self::BACKWPUP_PARTS : array() as $part => $option ) {
			BackWPup_Option::update( $job_id, $option, in_array( $part, $settings['files']['include'], true ) );
		}
	}

	/**
	 * @param array $job Job settings.
	 * @return array files and/or database: what the job saves.
	 */
	private static function backwpup_kinds( array $job ) {
		$types = (array) ( $job['type'] ?? array() );
		return array_keys(
			array_filter(
				array(
					'files'    => in_array( 'FILE', $types, true ),
					'database' => in_array( 'DBDUMP', $types, true ),
				)
			)
		);
	}

	/**
	 * @param array $jobs BackWPup jobs by id.
	 * @return array Storage for a new job: that of the existing jobs, or this server.
	 */
	private static function backwpup_storage( array $jobs ) {
		$storage = array();
		foreach ( $jobs as $job ) {
			$storage = array_merge( $storage, self::backwpup_job_destinations( $job ) );
		}

		return $storage ? array_values( array_unique( $storage ) ) : array( 'FOLDER' );
	}

	/**
	 * When the last successful backup of a backup plugin happened.
	 *
	 * @param string $id updraftplus or backwpup.
	 * @return int Unix time, 0 when unknown.
	 */
	private static function last_success( $id ) {
		try {
			$last = 'updraftplus' === $id
				? self::updraftplus_last( UpdraftPlus_Options::get_updraft_option( 'updraft_last_backup', array() ) )
				: self::backwpup_last( self::backwpup_last_run( self::backwpup_jobs() ) );
		} catch ( Throwable $e ) {
			return 0;
		}

		return null !== $last && true === $last['ok'] ? (int) strtotime( $last['time'] ) : 0;
	}

	/**
	 * Validate a preset from the dashboard and keep only the preset format.
	 *
	 * @param mixed $preset Preset from the command.
	 * @return array|null Null when it is not a valid preset.
	 */
	private static function preset( $preset ) {
		if ( ! is_array( $preset ) ) {
			return null;
		}

		$valid = array();
		foreach ( array( 'files', 'database' ) as $kind ) {
			if ( ! isset( $preset[ $kind ] ) ) {
				continue;
			}
			$settings = $preset[ $kind ];
			$keep     = is_array( $settings ) ? ( $settings['keep'] ?? null ) : null;
			if ( ! in_array( $settings['frequency'] ?? null, self::PRESET_FREQUENCIES, true ) || ! is_int( $keep ) || $keep < 1 || $keep > self::PRESET_MAX_KEEP ) {
				return null;
			}
			$valid[ $kind ] = array(
				'frequency' => $settings['frequency'],
				'keep'      => $keep,
			);
		}

		if ( isset( $preset['files']['include'] ) ) {
			$include = $preset['files']['include'];
			if ( ! is_array( $include ) || array_diff( $include, array_keys( self::UPDRAFTPLUS_PARTS ) ) ) {
				return null;
			}
			$valid['files']['include'] = array_values( array_unique( $include ) );
		}

		return $valid ? $valid : null;
	}

	/**
	 * @param mixed $types BackWPup job types.
	 * @return array What the job saves.
	 */
	private static function backwpup_contents( $types ) {
		return array_values(
			array_map(
				static function ( $type ) {
					return self::BACKWPUP_CONTENTS[ (string) $type ] ?? strtolower( (string) $type );
				},
				(array) $types
			)
		);
	}

	/**
	 * @param mixed $timestamp Unix time, or a false value for none.
	 * @return string|null ISO 8601 time in UTC.
	 */
	private static function time( $timestamp ) {
		return $timestamp ? gmdate( 'Y-m-d\TH:i:s\Z', (int) $timestamp ) : null;
	}
}

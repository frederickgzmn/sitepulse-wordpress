<?php
/**
 * SitePulse remote management backups
 *
 * Detects UpdraftPlus and BackWPup, reads their schedules, running job and recent
 * backups for the SitePulse dashboard, and reports each finished backup.
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

	/**
	 * Report finished backups.
	 */
	public static function init() {
		add_filter( 'updraftplus_save_last_backup', array( __CLASS__, 'updraftplus_finished' ) );
		add_action( 'backwpup_end_job', array( __CLASS__, 'backwpup_finished' ), 10, 3 );
	}

	/**
	 * The active backup plugin: a supported one first, UpdraftPlus before BackWPup.
	 *
	 * @return array|null id, version and supported; null when neither plugin is active.
	 */
	public static function provider() {
		$found = array_filter( array( self::updraftplus(), self::backwpup() ) );
		foreach ( $found as $provider ) {
			if ( $provider['supported'] ) {
				return $provider;
			}
		}

		return $found ? reset( $found ) : null;
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
		if ( is_array( $last_backup ) ) {
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
	 * A running UpdraftPlus job keeps its next resumption scheduled.
	 *
	 * @return array|null
	 */
	private static function updraftplus_running() {
		foreach ( (array) _get_cron_array() as $hooks ) {
			foreach ( (array) ( $hooks['updraft_backup_resume'] ?? array() ) as $event ) {
				$job = get_site_option( 'updraft_jobdata_' . ( $event['args'][1] ?? '' ), array() );
				if ( is_array( $job ) && $job && 'finished' !== ( $job['jobstatus'] ?? '' ) ) {
					return array(
						'name'     => (string) ( $job['label'] ?? '' ),
						'progress' => null,
						'stage'    => (string) ( $job['jobstatus'] ?? '' ),
					);
				}
			}
		}

		return null;
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
		// BackWPup stores job run times in the site's local time.
		$offset    = (int) ( (float) get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
		$schedules = array();
		$recent    = array();
		$next      = array();
		$last      = null;

		foreach ( (array) BackWPup_Option::get_job_ids() as $job_id ) {
			$job = BackWPup_Option::get_job( $job_id );
			// "Backup now" runs a temporary job.
			if ( ! is_array( $job ) || ! empty( $job['tempjob'] ) ) {
				continue;
			}

			$contents     = self::backwpup_contents( $job['type'] ?? array() );
			$destinations = array_map( 'strtoupper', array_filter( (array) ( $job['destinations'] ?? array() ), 'is_string' ) );
			$scheduled    = in_array( $job['activetype'] ?? '', array( 'wpcron', 'easycron' ), true );
			$job_next     = $scheduled ? wp_next_scheduled( 'backwpup_cron', array( 'arg' => $job_id ) ) : false;
			$schedules[]  = array(
				'name'         => (string) ( $job['name'] ?? '' ),
				'contents'     => $contents,
				'frequency'    => $scheduled ? (string) ( $job['frequency'] ?? 'custom' ) : 'manual',
				'next'         => self::time( $job_next ),
				'keep'         => isset( $job['maxbackups'] ) ? (int) $job['maxbackups'] : null,
				'destinations' => array_map( 'strtolower', array_values( $destinations ) ),
			);
			if ( $job_next ) {
				$next[] = $job_next;
			}

			$ran = (int) ( $job['lastrun'] ?? 0 ) - $offset;
			if ( ! empty( $job['lastrun'] ) && ( null === $last || $ran > $last['ran'] ) ) {
				$last = array(
					'ran'     => $ran,
					'logfile' => (string) ( $job['logfile'] ?? '' ),
				);
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
			'last'      => self::backwpup_last( $last ),
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

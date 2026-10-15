<?php
/**
 * SitePulse remote management activity
 *
 * What the SitePulse dashboard's timeline adds to the site's events: how each update
 * changed page speed, each day's page speed, and new PHP fatal errors. Speed figures
 * come from SitePulse's profiler, which keeps each callback's time per UTC day.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Remote_Activity
 */
class Sitepulse_Remote_Activity {

	/** Pending update measurements, the last day reported and how far the error log was read. */
	const OPTION = 'sitepulse_remote_activity';

	/** Page loads a day needs before its figures say anything about an update. */
	const MIN_LOADS = 20;

	/** Updates measured at once. */
	const MAX_WATCHES = 50;

	/** Fatal errors sent per check-in. */
	const MAX_ERRORS = 10;

	/** Characters of an error message sent. */
	const MESSAGE_LENGTH = 300;

	/** Sources listed in a day's figures. */
	const TOP_SOURCES = 5;

	const DEFAULTS = array(
		'watches'      => array(),
		'daily_day'    => 0,
		'errors_until' => 0,
	);

	/**
	 * Start measuring how an update changes page speed: the figures of the full UTC day
	 * before it now, those of the full day after it at the first check-in two days later.
	 *
	 * @param string      $kind plugin, theme or core.
	 * @param string      $id   Plugin file, theme stylesheet or "wordpress".
	 * @param string|null $from Version before the update.
	 * @param string|null $to   Version after it.
	 */
	public static function watch( $kind, $id, $from, $to ) {
		$measurable = in_array( $kind, array( 'plugin', 'theme', 'core' ), true ) && plugin_basename( SITEPULSE_PLUGIN_FILE ) !== $id;
		if ( ! $measurable || null === $from || null === $to || $from === $to || ! Sitepulse_Remote_Management::is_active() ) {
			return;
		}

		$state = self::state();
		$day   = self::today();
		$key   = $kind . ':' . $id;
		// A later update of the same item replaces its pending measurement.
		unset( $state['watches'][ $key ] );
		$state['watches'][ $key ] = array(
			'kind'       => $kind,
			'id'         => $id,
			'name'       => self::name( $kind, $id ),
			'from'       => $from,
			'to'         => $to,
			'updated_at' => gmdate( 'Y-m-d\TH:i:s\Z' ),
			'day'        => $day,
			'before'     => self::figures( self::day_rows( $day - 1 ), $kind, $id, $day - 1 ),
		);
		$state['watches'] = array_slice( $state['watches'], -self::MAX_WATCHES, null, true );
		self::save( $state );
	}

	/**
	 * Events due at this check-in: finished update measurements, yesterday's figures and new fatal errors.
	 *
	 * @return array Events, each an array of its type and data.
	 */
	public static function collect() {
		$state  = self::state();
		$today  = self::today();
		$events = array();

		foreach ( $state['watches'] as $key => $watch ) {
			if ( $today < $watch['day'] + 2 ) {
				continue;
			}
			// The day after the update can be read only on the following day, while the profiler keeps it.
			$after    = $today === $watch['day'] + 2 ? self::figures( self::day_rows( $watch['day'] + 1 ), $watch['kind'], $watch['id'], $watch['day'] + 1 ) : null;
			$events[] = array( 'update.impact', self::impact( $watch, $after ) );
			unset( $state['watches'][ $key ] );
		}

		if ( $state['daily_day'] < $today - 1 ) {
			$state['daily_day'] = $today - 1;
			$daily              = self::daily( $today - 1 );
			if ( null !== $daily ) {
				$events[] = array( 'performance.daily', $daily );
			}
		}

		$now = (int) current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- SitePulse's error log stores this time.
		// The first check-in after remote management turns on only starts reading the log.
		if ( $state['errors_until'] > 0 ) {
			foreach ( self::fatal_errors( $state['errors_until'], $now ) as $error ) {
				$events[] = array( 'error.fatal', $error );
			}
		}
		$state['errors_until'] = $now;

		self::save( $state );
		return $events;
	}

	/**
	 * Forget pending measurements and where reading stopped, when remote management turns off.
	 */
	public static function reset() {
		delete_option( self::OPTION );
	}

	/**
	 * @param array      $watch A pending measurement.
	 * @param array|null $after Figures of the day after the update, or null when they could not be read.
	 * @return array An update.impact event.
	 */
	private static function impact( array $watch, $after ) {
		$measured = null !== $after && $watch['before']['loads'] >= self::MIN_LOADS && $after['loads'] >= self::MIN_LOADS;
		$reason   = null === $after ? 'missed' : 'not_enough_traffic';

		return array(
			'kind'       => $watch['kind'],
			'id'         => $watch['id'],
			'name'       => $watch['name'],
			'from'       => $watch['from'],
			'to'         => $watch['to'],
			'updated_at' => $watch['updated_at'],
			'before'     => $watch['before'],
			'after'      => $after,
			'measured'   => $measured,
			'reason'     => $measured ? null : $reason,
		);
	}

	/**
	 * Figures of all code on one day, with the sources taking the most time.
	 *
	 * @param int $day UTC day number.
	 * @return array|null A performance.daily event, or null without page loads that day.
	 */
	private static function daily( $day ) {
		$rows = self::day_rows( $day );
		if ( ! $rows ) {
			return null;
		}

		$sources = array();
		foreach ( $rows as $row ) {
			$source = self::source( $row['fileline'] );
			if ( 'other' === $source['kind'] ) {
				continue;
			}
			$key = $source['kind'] . ':' . $source['id'];
			if ( ! isset( $sources[ $key ] ) ) {
				$sources[ $key ] = array(
					'kind' => $source['kind'],
					'id'   => $source['id'] ?? 'core',
					'ms'   => 0.0,
				);
			}
			$sources[ $key ]['ms'] += $row['ms'];
		}
		usort(
			$sources,
			static function ( $a, $b ) {
				return $b['ms'] <=> $a['ms'];
			}
		);

		$all = self::figures( $rows, null, null, $day );
		return array(
			'day'   => $all['day'],
			'ms'    => $all['ms'],
			'loads' => $all['loads'],
			'top'   => array_map(
				static function ( $source ) {
					$source['ms'] = round( $source['ms'], 1 );
					return $source;
				},
				array_slice( $sources, 0, self::TOP_SOURCES )
			),
		);
	}

	/**
	 * Own time per page load of some code on one day: the sum of each of its callbacks'
	 * time per page load it ran on, and the most page loads any of them ran on.
	 *
	 * @param array       $rows The day's profiler rows.
	 * @param string|null $kind plugin, theme or core; null for all code.
	 * @param string|null $id   Plugin file or theme stylesheet.
	 * @param int         $day  UTC day number.
	 * @return array day, ms and loads.
	 */
	private static function figures( array $rows, $kind, $id, $day ) {
		$ms    = 0.0;
		$loads = 0;
		foreach ( $rows as $row ) {
			if ( null === $kind || self::covers( $row['fileline'], $kind, $id ) ) {
				$ms   += $row['ms'];
				$loads = max( $loads, $row['loads'] );
			}
		}

		return array(
			'day'   => gmdate( 'Y-m-d', $day * DAY_IN_SECONDS ),
			'ms'    => round( $ms, 1 ),
			'loads' => $loads,
		);
	}

	/**
	 * The profiler's figures of one UTC day, without SitePulse's own code.
	 *
	 * @param int $day UTC day number.
	 * @return array Rows with fileline, ms (own time per page load) and loads.
	 */
	private static function day_rows( $day ) {
		$own  = 'wp-content/plugins/' . dirname( plugin_basename( SITEPULSE_PLUGIN_FILE ) ) . '/';
		$rows = array();
		foreach ( Sitepulse_Profiler::get_stored_stats() as $stat ) {
			// The profiler keeps the current day (window_) and the one before it (previous_).
			$window = (int) ( $stat['window'] ?? 0 );
			if ( $window !== $day && $window !== $day + 1 ) {
				continue;
			}
			$prefix   = $window === $day ? 'window_' : 'previous_';
			$loads    = (float) ( $stat[ $prefix . 'loads' ] ?? 0 );
			$fileline = (string) $stat['fileline'];
			if ( $loads <= 0 || 0 === strpos( $fileline, $own ) ) {
				continue;
			}
			$rows[] = array(
				'fileline' => $fileline,
				'ms'       => (float) ( $stat[ $prefix . 'self_ms' ] ?? 0 ) / $loads,
				'loads'    => (int) round( $loads ),
			);
		}

		return $rows;
	}

	/**
	 * Whether a profiler row's file is part of a plugin, a theme or WordPress core.
	 *
	 * @param string $fileline Path relative to the WordPress folder, with the line after a colon.
	 * @param string $kind     plugin, theme or core.
	 * @param string $id       Plugin file or theme stylesheet.
	 * @return bool
	 */
	private static function covers( $fileline, $kind, $id ) {
		if ( 'core' === $kind ) {
			return 0 === strpos( $fileline, 'wp-includes/' ) || 0 === strpos( $fileline, 'wp-admin/' );
		}
		if ( 'theme' === $kind ) {
			return 0 === strpos( $fileline, 'wp-content/themes/' . $id . '/' );
		}
		if ( '.' !== dirname( $id ) ) {
			return 0 === strpos( $fileline, 'wp-content/plugins/' . dirname( $id ) . '/' );
		}

		// A single-file plugin is that file only.
		$file = 'wp-content/plugins/' . $id;
		return $fileline === $file || 0 === strpos( $fileline, $file . ':' );
	}

	/**
	 * The plugin, theme or core code a file belongs to.
	 *
	 * @param string $file Path relative to the WordPress folder, possibly with a line after a colon.
	 * @return array kind (plugin, theme, core or other) and id (plugin folder, theme stylesheet or null).
	 */
	private static function source( $file ) {
		if ( preg_match( '#^wp-content/(plugins|themes)/([^/:]+)#', $file, $match ) ) {
			return array(
				'kind' => 'plugins' === $match[1] ? 'plugin' : 'theme',
				// A single-file plugin is named after its file, as SitePulse's profiler names it.
				'id'   => preg_replace( '/\.php$/', '', $match[2] ),
			);
		}

		return array(
			'kind' => 0 === strpos( $file, 'wp-includes/' ) || 0 === strpos( $file, 'wp-admin/' ) ? 'core' : 'other',
			'id'   => null,
		);
	}

	/**
	 * Fatal errors SitePulse's error log recorded in a period, grouped by message, file and line.
	 *
	 * @param int $since Local time the last check-in read the log up to.
	 * @param int $until Local time now.
	 * @return array error.fatal events, oldest first.
	 */
	private static function fatal_errors( $since, $until ) {
		$offset = (int) ( (float) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$groups = array();
		foreach ( Sitepulse_Remote_Health::error_log_entries() as $error ) {
			$time = (int) ( $error['timestamp'] ?? 0 );
			if ( empty( $error['is_fatal'] ) || $time <= $since || $time > $until ) {
				continue;
			}

			$file    = Sitepulse_Utils::shortpath( (string) ( $error['file'] ?? '' ) );
			$line    = (int) ( $error['line'] ?? 0 );
			$message = substr( Sitepulse_Remote_Health::first_line( (string) ( $error['message'] ?? '' ) ), 0, self::MESSAGE_LENGTH );
			$at      = gmdate( 'Y-m-d\TH:i:s\Z', $time - $offset );
			$key     = md5( $message . '|' . $file . '|' . $line );
			if ( ! isset( $groups[ $key ] ) ) {
				$groups[ $key ] = array(
					'message'  => $message,
					'file'     => $file,
					'line'     => $line,
					'source'   => self::source( $file ),
					'count'    => 0,
					'first_at' => $at,
				);
			}
			++$groups[ $key ]['count'];
			$groups[ $key ]['last_at'] = $at;
		}

		return array_slice( array_values( $groups ), 0, self::MAX_ERRORS );
	}

	/**
	 * @param string $kind plugin, theme or core.
	 * @param string $id   Plugin file or theme stylesheet.
	 * @return string The name WordPress shows for it.
	 */
	private static function name( $kind, $id ) {
		if ( 'plugin' === $kind ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			return (string) ( get_plugins()[ $id ]['Name'] ?? $id );
		}

		$name = 'theme' === $kind ? (string) wp_get_theme( $id )->get( 'Name' ) : 'WordPress';
		return '' === $name ? $id : $name;
	}

	/**
	 * @return int Number of the current UTC day, as SitePulse's profiler counts its windows.
	 */
	private static function today() {
		return (int) floor( time() / DAY_IN_SECONDS );
	}

	/**
	 * @return array The stored state, with every field valid.
	 */
	private static function state() {
		$stored = get_option( self::OPTION, array() );
		$state  = array_merge( self::DEFAULTS, is_array( $stored ) ? $stored : array() );

		$state['watches'] = array_filter(
			is_array( $state['watches'] ) ? $state['watches'] : array(),
			static function ( $watch ) {
				return is_array( $watch ) && isset( $watch['kind'], $watch['id'], $watch['name'], $watch['from'], $watch['to'], $watch['updated_at'], $watch['day'], $watch['before']['loads'] );
			}
		);
		$state['daily_day']    = (int) $state['daily_day'];
		$state['errors_until'] = (int) $state['errors_until'];

		return $state;
	}

	/**
	 * @param array $state The state to store.
	 */
	private static function save( array $state ) {
		update_option( self::OPTION, $state, false );
	}
}

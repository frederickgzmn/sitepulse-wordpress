<?php
/**
 * Privacy-safe, local product journey tracking.
 *
 * Events are stored in the WordPress database and are never transmitted
 * automatically. A compact summary may be included when an administrator
 * explicitly submits deactivation feedback.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sitepulse_Product_Analytics {

	const EVENTS_OPTION_KEY = 'sitepulse_product_events';
	const ONCE_OPTION_KEY   = 'sitepulse_product_events_once';
	const MAX_EVENTS        = 50;

	/**
	 * Events accepted from the SitePulse UI.
	 *
	 * @return string[]
	 */
	private static function get_allowed_events() {
		return array(
			'plugin_activated',
			'onboarding_viewed',
			'onboarding_step_viewed',
			'onboarding_collection_started',
			'onboarding_task_completed',
			'onboarding_task_failed',
			'onboarding_collection_completed',
			'onboarding_completed',
			'onboarding_skipped',
			'dashboard_viewed',
			'first_useful_finding',
		);
	}

	/**
	 * Record a local product event.
	 *
	 * @param string $event   Event name.
	 * @param array  $context Optional non-sensitive context.
	 * @return bool
	 */
	public static function track( $event, $context = array() ) {
		$event = sanitize_key( $event );

		if ( ! in_array( $event, self::get_allowed_events(), true ) ) {
			return false;
		}

		$events = get_option( self::EVENTS_OPTION_KEY, array() );
		if ( ! is_array( $events ) ) {
			$events = array();
		}

		$events[] = array(
			'event'          => $event,
			'timestamp'      => time(),
			'plugin_version' => defined( 'SITEPULSE_VERSION' ) ? SITEPULSE_VERSION : '',
			'context'        => self::sanitize_context( $context ),
		);

		if ( count( $events ) > self::MAX_EVENTS ) {
			$events = array_slice( $events, -self::MAX_EVENTS );
		}

		return (bool) update_option( self::EVENTS_OPTION_KEY, $events, false );
	}

	/**
	 * Record an event once per installation.
	 *
	 * @param string $event   Event name.
	 * @param array  $context Optional non-sensitive context.
	 * @return bool
	 */
	public static function track_once( $event, $context = array() ) {
		$event = sanitize_key( $event );

		if ( ! in_array( $event, self::get_allowed_events(), true ) ) {
			return false;
		}

		$tracked = get_option( self::ONCE_OPTION_KEY, array() );
		if ( ! is_array( $tracked ) ) {
			$tracked = array();
		}

		if ( ! empty( $tracked[ $event ] ) ) {
			return false;
		}

		$recorded = self::track( $event, $context );
		if ( $recorded ) {
			$tracked[ $event ] = time();
			update_option( self::ONCE_OPTION_KEY, $tracked, false );
		}

		return $recorded;
	}

	/**
	 * Return a compact summary suitable for explicit feedback submission.
	 *
	 * @return array
	 */
	public static function get_summary() {
		$events = get_option( self::EVENTS_OPTION_KEY, array() );
		if ( ! is_array( $events ) || empty( $events ) ) {
			return array(
				'event_count' => 0,
				'counts'      => array(),
			);
		}

		$counts          = array();
		$first_timestamp = null;
		$last_timestamp  = null;
		$last_event      = '';

		foreach ( $events as $item ) {
			if ( ! is_array( $item ) || empty( $item['event'] ) ) {
				continue;
			}

			$event = sanitize_key( $item['event'] );
			if ( ! in_array( $event, self::get_allowed_events(), true ) ) {
				continue;
			}

			$timestamp = isset( $item['timestamp'] ) ? absint( $item['timestamp'] ) : 0;
			$counts[ $event ] = isset( $counts[ $event ] ) ? $counts[ $event ] + 1 : 1;

			if ( $timestamp && ( null === $first_timestamp || $timestamp < $first_timestamp ) ) {
				$first_timestamp = $timestamp;
			}
			if ( $timestamp >= (int) $last_timestamp ) {
				$last_timestamp = $timestamp;
				$last_event     = $event;
			}
		}

		return array(
			'event_count'           => array_sum( $counts ),
			'counts'                => $counts,
			'first_event_timestamp' => $first_timestamp,
			'last_event_timestamp'  => $last_timestamp,
			'last_event'            => $last_event,
			'onboarding_completed'  => ! empty( $counts['onboarding_completed'] ),
			'useful_finding_seen'   => ! empty( $counts['first_useful_finding'] ),
		);
	}

	/**
	 * Keep context small and free of URLs or nested arbitrary payloads.
	 *
	 * @param mixed $context Event context.
	 * @return array
	 */
	private static function sanitize_context( $context ) {
		if ( ! is_array( $context ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( array_slice( $context, 0, 12, true ) as $key => $value ) {
			if ( ! is_scalar( $value ) && null !== $value ) {
				continue;
			}

			$key = sanitize_key( $key );
			if ( '' === $key ) {
				continue;
			}

			if ( is_bool( $value ) ) {
				$sanitized[ $key ] = $value;
				continue;
			}

			if ( is_int( $value ) || is_float( $value ) ) {
				$sanitized[ $key ] = $value;
				continue;
			}

			$sanitized[ $key ] = substr( sanitize_text_field( (string) $value ), 0, 200 );
		}

		return $sanitized;
	}
}

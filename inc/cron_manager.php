<?php
/**
 * SitePulse Cron Manager
 * 
 * Manages scheduled cron jobs for the SitePulse plugin.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Cron_Manager
 */
class Sitepulse_Cron_Manager {

	/**
	 * Cron hook name for daily API request
	 *
	 * @var string
	 */
	private const DAILY_API_HOOK = 'sitepulse_daily_api_request';

	/**
	 * Initialize the cron manager
	 */
	public static function init() {
		// Check if external API is enabled in settings
		$sp_all_settings = get_option( 'sitepulse_settings', array() );
		$sp_external_api_enabled = isset( $sp_all_settings['external_api_enabled'] ) ? (bool) $sp_all_settings['external_api_enabled'] : false;
		if ( ! $sp_external_api_enabled ) {
			return;
		}

		// Register the cron hook callback
		add_action( self::DAILY_API_HOOK, array( __CLASS__, 'send_daily_api_request' ) );

		// Ensure cron is scheduled (in case activation hook didn't fire)
		if ( ! wp_next_scheduled( self::DAILY_API_HOOK ) ) {
			self::schedule_daily_api_request();
		}
	}

	/**
	 * Schedule the daily API request cron job at 1am
	 */
	public static function schedule_daily_api_request() {
		// Clear any existing scheduled event
		$timestamp = wp_next_scheduled( self::DAILY_API_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::DAILY_API_HOOK );
		}

		// Calculate next 1am timestamp
		$next_1am = self::get_next_1am_timestamp();

		// Schedule the event
		wp_schedule_event( $next_1am, 'daily', self::DAILY_API_HOOK );
	}

	/**
	 * Get the next 1am timestamp
	 *
	 * @return int Unix timestamp for next 1am
	 */
	private static function get_next_1am_timestamp() {
		// WordPress schedules in UTC. We need to compute "next 1:00 AM local" then
		// express it as a UTC timestamp.
		$tz_string = wp_timezone_string();
		try {
			$tz = new DateTimeZone( $tz_string );
		} catch ( Exception $e ) {
			$tz = new DateTimeZone( 'UTC' );
		}

		$now_local   = new DateTime( 'now', $tz );
		$target      = new DateTime( 'today 01:00', $tz );

		// If it's already past 1 AM local today, aim for tomorrow.
		if ( $now_local >= $target ) {
			$target->modify( '+1 day' );
		}

		return $target->getTimestamp(); // Returns UTC unix timestamp.
	}

	/**
	 * Send daily API request
	 * This is the callback function for the cron hook
	 */
	public static function send_daily_api_request() {
		// Load the Settings class if not already loaded
		if ( ! class_exists( 'Sitepulse_Settings' ) ) {
			require_once SITEPULSE_CLASS_PATH . 'settings.php';
		}

		// Check if external API is enabled
		if ( class_exists( 'Sitepulse_Settings' ) ) {
			$settings = Sitepulse_Settings::getInstance();
			if ( ! $settings->is_external_api_enabled() ) {
				// Log that API call was skipped
				return array(
					'success' => false,
					'message' => __( 'External API data collection is disabled', 'sitepulse' ),
				);
			}
		}

		$result = null;

		if ( SITEPULSE_PRO_IS_ACTIVE ) {
			// Pro library
			// Load the API service class if not already loaded
			if ( class_exists( 'Sitepulse_Pro_Api_Service' ) ) {
				// Send the request
				$result = Sitepulse_Pro_Api_Service::send_website_data();
			}
		} else {
			// Free library
			// Load the API service class if not already loaded
			if ( ! class_exists( 'Sitepulse_Api_Service' ) ) {
				require_once SITEPULSE_CLASS_PATH . 'api_service.php';
			}

			// Send the request
			$result = Sitepulse_Api_Service::send_website_data();
		}

		return $result;
	}

	/**
	 * Unschedule the daily API request cron job
	 * Called on plugin deactivation
	 */
	public static function unschedule_daily_api_request() {
		$timestamp = wp_next_scheduled( self::DAILY_API_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::DAILY_API_HOOK );
		}
	}

	/**
	 * Get the next scheduled time for the daily API request
	 *
	 * @return int|false Unix timestamp of next scheduled run, or false if not scheduled
	 */
	public static function get_next_scheduled_time() {
		return wp_next_scheduled( self::DAILY_API_HOOK );
	}
}

// Initialize the cron manager
Sitepulse_Cron_Manager::init();

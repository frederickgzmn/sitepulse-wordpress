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
	 * Legacy hook name retained for compatibility with existing integrations.
	 *
	 * @var string
	 */
	private const DAILY_API_HOOK = 'sitepulse_daily_api_request';

	/** Custom schedule for sending site status every six hours. */
	private const INTERVAL_NAME = 'sitepulse_every_six_hours';

	/** Seconds between site status reports. */
	public const API_INTERVAL_SECONDS = 6 * HOUR_IN_SECONDS;

	/**
	 * Initialize the cron manager
	 */
	public static function init() {
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_interval' ) );

		// Check if external API is enabled in settings
		$sp_all_settings = get_option( 'sitepulse_settings', array() );
		$sp_external_api_enabled = isset( $sp_all_settings['external_api_enabled'] ) ? (bool) $sp_all_settings['external_api_enabled'] : true;
		if ( ! $sp_external_api_enabled ) {
			return;
		}

		// Register the cron hook callback
		add_action( self::DAILY_API_HOOK, array( __CLASS__, 'send_daily_api_request' ) );

		// Schedule missing events and migrate existing daily events automatically.
		$event = wp_get_scheduled_event( self::DAILY_API_HOOK );
		if ( ! $event || self::INTERVAL_NAME !== $event->schedule || self::API_INTERVAL_SECONDS !== $event->interval ) {
			self::schedule_daily_api_request();
		}
	}

	/**
	 * Register the six-hour cron interval.
	 *
	 * @param array $schedules Existing cron schedules.
	 * @return array Updated cron schedules.
	 */
	public static function add_cron_interval( $schedules ) {
		$schedules[ self::INTERVAL_NAME ] = array(
			'interval' => self::API_INTERVAL_SECONDS,
			'display'  => __( 'Every Six Hours (SitePulse)', 'sitepulse' ),
		);
		return $schedules;
	}

	/**
	 * Schedule site status reports every six hours, starting six hours from now.
	 * The legacy method name is retained for compatibility.
	 */
	public static function schedule_daily_api_request() {
		// Clear all old events, including any duplicate daily schedules.
		self::unschedule_daily_api_request();

		wp_schedule_event( time() + self::API_INTERVAL_SECONDS, self::INTERVAL_NAME, self::DAILY_API_HOOK );
	}

	/**
	 * Send the site status to the API
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

		if ( ! SITEPULSE_PRO_IS_ACTIVE ) {
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
	 * Unschedule site status reports
	 * Called on plugin deactivation
	 */
	public static function unschedule_daily_api_request() {
		wp_clear_scheduled_hook( self::DAILY_API_HOOK );
	}

	/**
	 * Get the next scheduled time for the site status report
	 *
	 * @return int|false Unix timestamp of next scheduled run, or false if not scheduled
	 */
	public static function get_next_scheduled_time() {
		return wp_next_scheduled( self::DAILY_API_HOOK );
	}
}

// Initialize the cron manager
Sitepulse_Cron_Manager::init();

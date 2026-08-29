<?php
/**
 * SitePulse AI Diagnostic Cron Manager
 * 
 * Manages scheduled cron jobs for checking AI diagnostic status.
 * Only runs when an AI diagnostic is pending or processing.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_AI_Diagnostic_Cron
 */
class Sitepulse_AI_Diagnostic_Cron {

	/**
	 * Cron hook name for AI diagnostic status check
	 *
	 * @var string
	 */
	private const CRON_HOOK = 'sitepulse_check_ai_diagnostic_status';

	/**
	 * Interval name for 1-minute schedule
	 *
	 * @var string
	 */
	private const INTERVAL_NAME = 'sitepulse_every_minute';

	/**
	 * Initialize the cron manager
	 */
	public static function init() {
		// Add custom cron interval (every minute)
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_interval' ) );

		// Register the cron hook callback
		add_action( self::CRON_HOOK, array( __CLASS__, 'check_status_callback' ) );

		// Check if we need to schedule on init (in case diagnostic is pending)
		add_action( 'init', array( __CLASS__, 'maybe_schedule_on_init' ), 20 );
	}

	/**
	 * Add custom cron interval for every minute
	 *
	 * @param array $schedules Existing schedules
	 * @return array Modified schedules
	 */
	public static function add_cron_interval( $schedules ) {
		$schedules[ self::INTERVAL_NAME ] = array(
			'interval' => 60, // 60 seconds = 1 minute
			'display'  => __( 'Every Minute (SitePulse AI Diagnostic)', 'sitepulse' ),
		);
		return $schedules;
	}

	/**
	 * Check if we should schedule cron on init (for page reloads when diagnostic is pending)
	 */
	public static function maybe_schedule_on_init() {
		// Check if external API is enabled
		$sp_all_settings = get_option( 'sitepulse_settings', array() );
		$sp_external_api_enabled = isset( $sp_all_settings['external_api_enabled'] ) ? (bool) $sp_all_settings['external_api_enabled'] : true;
		if ( ! $sp_external_api_enabled ) {
			return;
		}

		$status = get_option( 'sitepulse_ai_diagnostic_status', null );

		// If pending or processing, ensure cron is scheduled
		if ( in_array( $status, array( 'pending', 'processing' ), true ) ) {
			if ( ! self::is_scheduled() ) {
				self::schedule_status_check();
			}
		} else {
			// If completed/failed/none, ensure cron is NOT scheduled
			if ( self::is_scheduled() ) {
				self::unschedule();
			}
		}
	}

	/**
	 * Schedule the AI diagnostic status check cron job
	 */
	public static function schedule_status_check() {
		// Clear any existing scheduled event first
		self::unschedule();

		// Schedule to run every minute
		wp_schedule_event( time() + 60, self::INTERVAL_NAME, self::CRON_HOOK );
	}

	/**
	 * Callback for the cron hook - checks AI diagnostic status
	 */
	public static function check_status_callback() {
		// Check if external API is enabled
		$sp_all_settings = get_option( 'sitepulse_settings', array() );
		$sp_external_api_enabled = isset( $sp_all_settings['external_api_enabled'] ) ? (bool) $sp_all_settings['external_api_enabled'] : true;
		if ( ! $sp_external_api_enabled ) {
			self::unschedule();
			return;
		}

		// Check current status before making API call
		$current_status = get_option( 'sitepulse_ai_diagnostic_status', null );

		// If not pending or processing, unschedule and return
		if ( ! in_array( $current_status, array( 'pending', 'processing' ), true ) ) {
			self::unschedule();
			return;
		}

		// Load the API service class if needed
		if ( ! SITEPULSE_PRO_IS_ACTIVE ) {
			if ( class_exists( 'Sitepulse_Api_Service' ) ) {
				$response = Sitepulse_Api_Service::check_ai_diagnostic_status();
			}
		}

		// If status is no longer pending/processing, or if there's an error, unschedule
		$is_successful = isset( $response['success'] ) && $response['success'];
		$new_status = isset( $response['status'] ) ? $response['status'] : null;
		$is_terminal_status = in_array( $new_status, array( 'completed', 'failed', 'none' ), true );

		// Unschedule if the API call failed or if the diagnostic has reached a terminal state.
		if ( ! $is_successful || $is_terminal_status ) {
		    self::unschedule();
		}
	}

	/**
	 * Unschedule the AI diagnostic status check cron job
	 */
	public static function unschedule() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}

		// Also clear all events with this hook (in case multiple were scheduled)
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Check if the cron job is scheduled
	 *
	 * @return bool True if scheduled, false otherwise
	 */
	public static function is_scheduled() {
		return wp_next_scheduled( self::CRON_HOOK ) !== false;
	}

	/**
	 * Get the next scheduled time for the status check
	 *
	 * @return int|false Unix timestamp of next scheduled run, or false if not scheduled
	 */
	public static function get_next_scheduled_time() {
		return wp_next_scheduled( self::CRON_HOOK );
	}
}

// Initialize the AI diagnostic cron manager
Sitepulse_AI_Diagnostic_Cron::init();

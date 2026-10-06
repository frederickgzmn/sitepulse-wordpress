<?php
/**
 * SitePulse Monitoring Class
 * Pauses and resumes performance monitoring as a whole.
 *
 * Pausing switches off every tracker that runs on page loads, so SitePulse adds
 * no profiling overhead, while settings and collected data are kept. It gives
 * people who only need SitePulse occasionally an alternative to deactivating.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Monitoring
 */
class Sitepulse_Monitoring {

	/**
	 * Option recording when monitoring was paused
	 */
	const PAUSED_OPTION = 'sitepulse_monitoring_paused';

	/**
	 * admin-post action and nonce action of the toolbar toggle
	 */
	const TOGGLE_ACTION = 'sitepulse_toggle_monitoring';

	/**
	 * Options of the trackers that run on page loads
	 */
	const TRACKER_OPTIONS = array( 'sitepulse_profiler_enabled', 'sitepulse_curl_api_enabled', 'sitepulse_plugins_profiler_enabled' );

	/**
	 * Register the toggle handler.
	 */
	public static function init() {
		add_action( 'admin_post_' . self::TOGGLE_ACTION, array( __CLASS__, 'handle_toggle' ) );
	}

	/**
	 * Whether monitoring is paused. Switching a tracker back on by hand ends the pause.
	 *
	 * @return bool
	 */
	public static function is_paused() {
		return (bool) get_option( self::PAUSED_OPTION, false )
			&& ! get_option( 'sitepulse_profiler_enabled' )
			&& ! get_option( 'sitepulse_curl_api_enabled' );
	}

	/**
	 * Switch every page-load tracker off, keeping settings and data.
	 */
	public static function pause() {
		foreach ( self::TRACKER_OPTIONS as $option ) {
			update_option( $option, false, false );
		}
		update_option( self::PAUSED_OPTION, time() );
	}

	/**
	 * Switch every page-load tracker back on.
	 */
	public static function resume() {
		foreach ( self::TRACKER_OPTIONS as $option ) {
			update_option( $option, true, false );
		}
		delete_option( self::PAUSED_OPTION );

		// Let the "trackers disabled" notice appear again if a tracker is switched off later.
		delete_metadata( 'user', 0, 'sitepulse_trackers_disabled_notice_dismissed', '', true );
	}

	/**
	 * Link that pauses or resumes monitoring and returns to the current page.
	 *
	 * The return address travels in the link, so it works even when the
	 * browser does not send a Referer header.
	 *
	 * @return string
	 */
	public static function toggle_url() {
		$url = admin_url( 'admin-post.php?action=' . self::TOGGLE_ACTION );

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$url = add_query_arg( '_wp_http_referer', rawurlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $url );
		}

		return wp_nonce_url( $url, self::TOGGLE_ACTION );
	}

	/**
	 * Handle the toolbar toggle.
	 */
	public static function handle_toggle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sitepulse' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::TOGGLE_ACTION );

		if ( self::is_paused() ) {
			self::resume();
		} else {
			self::pause();
		}

		$referer = wp_get_referer();
		wp_safe_redirect( $referer ? $referer : admin_url( 'admin.php?page=wpsp_sitepulse' ) );
		exit;
	}
}

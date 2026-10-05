<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper function to register SitePulse REST API routes
 * Reduces code duplication for route registration
 *
 * @param string   $endpoint    REST endpoint path (without namespace)
 * @param callable $callback    Callback function to handle the request
 * @param string   $methods     HTTP methods (default: 'POST')
 * @param array    $extra_args  Additional arguments for the route
 */
function sitepulse_register_route( $endpoint, $callback, $methods = 'POST', $extra_args = [] ) {
	$default_args = [
		'_wpnonce' => [
			'required' => true,
			'description' => 'WP REST API nonce for authentication',
			'type' => 'string',
		],
	];

	$args = array_merge( $default_args, $extra_args );

	register_rest_route( 'sitepulse/v1', $endpoint, [
		'methods' => $methods,
		'callback' => $callback,
		'permission_callback' => function () {
			return current_user_can( 'manage_options' );
		},
		'args' => $args,
	] );
}

/**
 * Enforce permissions for SitePulse REST routes.
 * Non-GET methods require 'manage_options' capability.
 *
 * This uses rest_pre_dispatch so it applies even if register_rest_route does not include
 * a permission_callback.
 */
add_filter( 'rest_pre_dispatch', 'sitepulse_rest_pre_dispatch', 10, 3 );
function sitepulse_rest_pre_dispatch( $result, $server, $request ) {
	// Only target your plugin's routes to avoid interfering with other plugins
	$route = method_exists( $request, 'get_route' ) ? $request->get_route() : '';
	if ( empty( $route ) ) {
		return $result;
	}

	// Adjust this prefix if your plugin uses a different namespace
	if ( strpos( $route, '/sitepulse/' ) === 0 || strpos( $route, '/sitepulse/v1/' ) === 0 ) {
		$method = $request->get_method();
		// Allow GET (read) requests for users as-is; require manage_options for mutating requests
		if ( strtoupper( $method ) !== 'GET' && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'sitepulse_rest_forbidden', __( 'You do not have permission to perform this action.', 'sitepulse' ), array( 'status' => 403 ) );
		}
	}

	return $result;
}

/**
 * Basic sanitization helper for REST handlers.
 * Use appropriate WordPress sanitizers in each handler for the expected data type.
 */
function sitepulse_sanitize_param( $value, $type = 'text' ) {
	if ( is_array( $value ) ) {
		return array_map( 'sitepulse_sanitize_param', $value );
	}
	switch ( $type ) {
		case 'int':
			return absint( $value );
		case 'url':
			return esc_url_raw( $value );
		case 'html':
			// Allow some HTML but sanitize it
			return wp_kses_post( $value );
		case 'text':
		default:
			return sanitize_text_field( $value );
	}
}

/**
 * Register all SitePulse REST API routes
 */
add_action( 'rest_api_init', function () {
	// Core functionality routes
	sitepulse_register_route( '/save_queries/enable', 'sitepulse_enable_save_queries' );
	sitepulse_register_route( '/clear_load_events/clear', 'sitepulse_enable_clear_load_events' );
	sitepulse_register_route( '/clear_curl_api_events/clear', 'sitepulse_enable_clear_curl_api_events' );
	sitepulse_register_route( '/memory_checker/check', 'sitepulse_check_memory' );

	// Toggle/settings routes
	sitepulse_register_route( '/wpslowhttp/set_active', 'sitepulse_set_wpslowhttp' );
	sitepulse_register_route( '/sp_profiler/set_active', 'sitepulse_set_sp_profiler' );
	sitepulse_register_route( '/wpspageloadhttp/set_active', 'sitepulse_set_wpspageloadhttp' );
	sitepulse_register_route( '/sp_report_mode/set_active', 'sitepulse_sp_report_mode' );

	// Real-time tracking routes
	sitepulse_register_route( '/wpsprealtimemode/set_active', 'sitepulse_realtime_mode' );
	sitepulse_register_route( '/wpsprealtimemode/get_active', 'sitepulse_get_realtime_mode' );

	// Notice dismissal routes
	sitepulse_register_route( '/trackers_disabled_notice/dismiss', 'sitepulse_set_trackers_disabled_notice' );
	sitepulse_register_route( '/onboarding_notice/dismiss', 'sitepulse_dismiss_onboarding_notice' );

	// Onboarding routes
	sitepulse_register_route( '/onboarding/complete', 'sitepulse_complete_onboarding' );
	sitepulse_register_route( '/onboarding/dismiss', 'sitepulse_dismiss_onboarding' );
	sitepulse_register_route( '/onboarding/reset', 'sitepulse_reset_onboarding' );

	// Settings routes
	sitepulse_register_route( '/settings/update', 'sitepulse_update_settings' );

	// Stats routes
	sitepulse_register_route( '/profiler_stats', 'sitepulse_get_profiler_stats' );
	sitepulse_register_route( '/curl_stats', 'sitepulse_get_curl_stats' );
	sitepulse_register_route( '/plugin_profiler_stats', 'sitepulse_get_plugin_profiler_stats' );
	sitepulse_register_route( '/plugin_profiler/collect', 'sitepulse_collect_plugin_data' );
	sitepulse_register_route( '/memory_info', 'sitepulse_get_memory_info' );

	// Vulnerability route
	sitepulse_register_route( '/vulnerability/check', 'sitepulse_check_vulnerabilities_api' );

	// AI Diagnostic routes
	sitepulse_register_route( '/ai_diagnostic/request', 'sitepulse_request_ai_diagnostic_api' );
	sitepulse_register_route( '/ai_diagnostic/status', 'sitepulse_check_ai_diagnostic_status_api' );

	// Autoload options routes
	sitepulse_register_route( '/autoload_options', 'sitepulse_get_autoload_options', 'POST' );
	sitepulse_register_route( '/autoload_options/update', 'sitepulse_update_autoload_option' );
} );

// sitepulse_get_realtime_mode
function sitepulse_get_realtime_mode( WP_REST_Request $request ) {
	$is_active = get_transient( "sitepulse_realtime_tracking" );

	return rest_ensure_response( [
		'success' => true,
		'real_time_status' => $is_active ? true : false
	] );
}

function sitepulse_realtime_mode( WP_REST_Request $request ) {
	// Sanitization $request['real_time_status']
	$real_time_status = isset( $request['real_time_status'] ) ? sanitize_text_field( $request['real_time_status'] ) : '';
	$result = false;
	if ( isset( $request['real_time_status'] ) && $request['real_time_status'] ) {
		set_transient( "sitepulse_realtime_tracking", true, SITEPULSE_REALTIME_TRACKING_TIME );
		$result = true;
	} else {
		delete_transient( "sitepulse_realtime_tracking" );
	}

	return rest_ensure_response( [
		'success' => $result,
	] );
}

function sitepulse_enable_clear_curl_api_events( WP_REST_Request $request ) {
	$sitepulse_CurLoader = new Sitepulse_CurLoader();
	$sitepulse_CurLoader->clear_events();

	return rest_ensure_response( [
		'success' => true,
	] );
}

function sitepulse_enable_clear_load_events( WP_REST_Request $request ) {
	$sitepulse_Profiler = new Sitepulse_Profiler();
	$sitepulse_Profiler->clear_events();

	// Move sitepulse to first position in the active plugins list
	sitepulse_move_to_first_position();

	return rest_ensure_response( [
		'success' => true,
	] );
}

/**
 * Enables the save queries feature.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_enable_save_queries( WP_REST_Request $request ) {
	$sitepulse_Plugin = new Sitepulse_Plugin();
	$set_savequeries = $sitepulse_Plugin->sp_set_savequeries( true );

	if ( isset( $set_savequeries['success'] ) && $set_savequeries['success'] ) {
		return rest_ensure_response( [
			'success' => true,
		] );
	}

	return rest_ensure_response( [
		'success' => false,
	] );
}

function sitepulse_sp_report_mode( WP_REST_Request $request ) {
	$params = $request->get_json_params();
	$report_mode = isset( $params['report_mode'] ) ? sanitize_text_field( $params['report_mode'] ) : '';
	if ( $report_mode === '' ) {
		return new WP_Error( 'missing_report_mode', 'report_mode is required', [ 'status' => 400 ] );
	}

	update_option( "sitepulse_report_mode_active", $report_mode, false );

	return rest_ensure_response( [
		'success' => true,
		'report_mode' => $report_mode
	] );
}

function sitepulse_set_wpspageloadhttp( WP_REST_Request $request ) {
	$params = $request->get_json_params();

	// Sanitazion to each variable: $params['page_id'], $params['curlSwitch'], $params['loadSwitch']
	$page_id = isset( $params['page_id'] ) ? sanitize_text_field( $params['page_id'] ) : '';
	$curlSwitch = isset( $params['curlSwitch'] ) ? sanitize_text_field( $params['curlSwitch'] ) : '';
	$loadSwitch = isset( $params['loadSwitch'] ) ? sanitize_text_field( $params['loadSwitch'] ) : '';

	// Validate required parameters
	if ( ! isset( $params['page_id'] ) || ! isset( $params['curlSwitch'] ) || ! isset( $params['loadSwitch'] ) ) {
		return new WP_Error( 'missing_params', 'page_id, curlSwitch, and loadSwitch are required', [ 'status' => 400 ] );
	}

	$page_id = absint( $params['page_id'] );
	$curlSwitch = absint( $params['curlSwitch'] );
	$loadSwitch = absint( $params['loadSwitch'] );

	// Persist as option (example). Adapt storage to your plugin.
	update_option( "sitepulse_pageloadhttp_curlstatus", $curlSwitch, false );
	update_option( "sitepulse_pageloadhttp_loadstatus", $loadSwitch, false );
	update_option( "sitepulse_current_tracked_pageid", $page_id, false );

	return rest_ensure_response( [
		'success' => true,
		'page_id' => $page_id,
		'curlSwitch' => $curlSwitch,
		'loadSwitch' => $loadSwitch
	] );
}

//trackers_disabled_notice/dismiss
function sitepulse_set_trackers_disabled_notice( WP_REST_Request $request ) {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return new WP_Error( 'unauthenticated', 'User not authenticated', [ 'status' => 401 ] );
	}

	// Mark the notice as dismissed for the current user
	update_user_meta( $user_id, 'sitepulse_trackers_disabled_notice_dismissed', true );

	return rest_ensure_response( [
		'success' => true,
	] );
}

/**
 * Sets the active status of the external requests tracker (curl/API).
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_set_wpslowhttp( WP_REST_Request $request ) {
	$params = $request->get_json_params();
	$wpslowhttp = isset( $params['wpslowhttp'] ) ? sanitize_text_field( $params['wpslowhttp'] ) : '';
	if ( $wpslowhttp === '' ) {
		return new WP_Error( 'missing_wpslowhttp', 'wpslowhttp is required', [ 'status' => 400 ] );
	}

	if ( $wpslowhttp == 'enabled' ) {
		$wpslowhttp = true;
	}

	// persist as option (example). adapt storage to your plugin.
	update_option( "sitepulse_curl_api_enabled", $wpslowhttp, false );

	// Reset the dismissal of the trackers disabled notice
	$user_id = get_current_user_id();
	if ( $user_id ) {
		update_user_meta( $user_id, 'sitepulse_trackers_disabled_notice_dismissed', false );
	}

	return rest_ensure_response( [
		'success' => true,
		'wpslowhttp' => $wpslowhttp
	] );
}

/**
 * Sets the active status of the SP Profiler.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_set_sp_profiler( WP_REST_Request $request ) {
	$params = $request->get_json_params();
	$sitepulse_profiler_enabled = isset( $params['sitepulse_profiler_enabled'] ) ? sanitize_text_field( $params['sitepulse_profiler_enabled'] ) : '';
	if ( $sitepulse_profiler_enabled === '' ) {
		return new WP_Error( 'missing_profiler_enabled', 'sitepulse_profiler_enabled is required', [ 'status' => 400 ] );
	}

	if ( $sitepulse_profiler_enabled == 'enabled' ) {
		$sitepulse_profiler_enabled = true;
	} else {
		$sitepulse_profiler_enabled = false;
	}

	// Persist the enabled state
	$sitepulse_Profiler = new Sitepulse_Profiler();
	$sitepulse_Profiler::$enabled = $sitepulse_profiler_enabled;

	add_action( 'shutdown', function () use ($sitepulse_profiler_enabled) {
		update_option( "sitepulse_profiler_enabled", $sitepulse_profiler_enabled, false );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}
		update_user_meta( $user_id, 'sitepulse_trackers_disabled_notice_dismissed', false );
	} );

	return rest_ensure_response( [
		'success' => true,
		'sitepulse_profiler_enabled' => $sitepulse_profiler_enabled
	] );
}

/**
 * Checks system memory usage and limit.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_check_memory( WP_REST_Request $request ) {
	// Suppress any output that might interfere with JSON response
	if ( ob_get_level() ) {
		ob_clean();
	}

	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	// Check class existence before instantiation
	if ( ! class_exists( 'Sitepulse_Plugin' ) ) {
		return new WP_Error( 'missing_class', 'Sitepulse_Plugin class not found', [ 'status' => 500 ] );
	}

	$capture_buffer_level = ob_get_level();
	try {
		$sitepulse_Plugin = new Sitepulse_Plugin();

		// Capture any output that might be generated
		ob_start();
		$mem = $sitepulse_Plugin->sp_get_memory_info();
		$output = ob_get_clean();

		return rest_ensure_response( [
			'success' => true,
			'memory' => $mem
		] );

	} catch (Exception $e) {
		if ( ob_get_level() > $capture_buffer_level ) {
			ob_end_clean();
		}
		return new WP_Error( 'memory_check_error', 'Error checking memory: ' . $e->getMessage(), [ 'status' => 500 ] );
	}
}

/**
 * Mark onboarding as completed
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_complete_onboarding( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	require_once SITEPULSE_CLASS_PATH . 'onboarding.php';
	$onboarding = Sitepulse_Onboarding::getInstance();
	$onboarding->mark_onboarding_completed();

	return rest_ensure_response( [
		'success' => true,
		'message' => 'Onboarding completed successfully'
	] );
}

/**
 * Dismiss onboarding
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_dismiss_onboarding( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	require_once SITEPULSE_CLASS_PATH . 'onboarding.php';
	$onboarding = Sitepulse_Onboarding::getInstance();
	$onboarding->mark_onboarding_dismissed();

	return rest_ensure_response( [
		'success' => true,
		'message' => 'Onboarding dismissed successfully'
	] );
}

/**
 * Update SitePulse settings
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_update_settings( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	$params = $request->get_json_params();

	require_once SITEPULSE_CLASS_PATH . 'settings.php';
	$settings = Sitepulse_Settings::getInstance();

	$updated_settings = [];

	// Email blocking settings
	if ( isset( $params['email_blocking_enabled'] ) ) {
		$updated_settings['email_blocking_enabled'] = (bool) $params['email_blocking_enabled'];
	}

	if ( isset( $params['email_blocking_mode'] ) ) {
		$updated_settings['email_blocking_mode'] = sanitize_text_field( $params['email_blocking_mode'] );
	}

	// Cron settings
	if ( isset( $params['cron_disabled'] ) ) {
		$updated_settings['cron_disabled'] = (bool) $params['cron_disabled'];
	}

	// Update settings
	if ( ! empty( $updated_settings ) ) {
		$result = $settings->update_settings( $updated_settings );

		if ( $result ) {
			return rest_ensure_response( [
				'success' => true,
				'message' => 'Settings updated successfully'
			] );
		}
	}

	return rest_ensure_response( [
		'success' => false,
		'message' => 'No settings were updated'
	] );
}

/**
 * Dismiss onboarding notice
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_dismiss_onboarding_notice( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return new WP_Error( 'unauthenticated', 'User not authenticated', [ 'status' => 401 ] );
	}

	// Mark the notice as dismissed for the current user
	update_user_meta( $user_id, 'sitepulse_onboarding_notice_dismissed', true );

	return rest_ensure_response( [
		'success' => true,
		'message' => 'Onboarding notice dismissed successfully'
	] );
}

/**
 * Reset onboarding state
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_reset_onboarding( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	require_once SITEPULSE_CLASS_PATH . 'onboarding.php';
	$onboarding = Sitepulse_Onboarding::getInstance();
	$onboarding->reset_onboarding();

	// Also clear the notice dismissal for current user
	$user_id = get_current_user_id();
	if ( $user_id ) {
		delete_user_meta( $user_id, 'sitepulse_onboarding_notice_dismissed' );
	}

	return rest_ensure_response( [
		'success' => true,
		'message' => 'Onboarding has been reset successfully'
	] );
}

/**
 * Get profiler stats for onboarding
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_get_profiler_stats( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	// Get profiler stats from option
	$profiler_stats = get_option( SITEPULSE_PROFILER_KEY, [] );

	// Convert to array format
	$stats_array = [];
	if ( is_array( $profiler_stats ) && ! empty( $profiler_stats ) ) {
		foreach ( $profiler_stats as $hook => $data ) {
			$stats_array[] = [
				'hook' => $hook,
				'data' => $data
			];
		}
	}

	return rest_ensure_response( [
		'success' => true,
		'data' => $stats_array,
		'count' => count( $stats_array )
	] );
}

/**
 * Get CURL/HTTP stats for onboarding
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_get_curl_stats( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	// Get CURL events from option
	$curl_events = get_option( SITEPULSE_CURL_API_KEY, [] );

	if ( ! is_array( $curl_events ) ) {
		$curl_events = [];
	}

	return rest_ensure_response( [
		'success' => true,
		'events' => $curl_events,
		'count' => count( $curl_events )
	] );
}

/**
 * Get memory info for onboarding
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_get_memory_info( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	if ( ! class_exists( 'Sitepulse_Plugin' ) ) {
		return new WP_Error( 'missing_class', 'Sitepulse_Plugin class not found', [ 'status' => 500 ] );
	}

	$plugin = new Sitepulse_Plugin();
	$mem    = $plugin->sp_get_memory_info();

	$memory_mb     = round( (int) $mem['allocated'] / 1024 / 1024, 2 );
	$limit_mb      = (int) $mem['limit_bytes'] > 0 ? round( (int) $mem['limit_bytes'] / 1024 / 1024, 2 ) : 0;
	$usage_percent = is_numeric( rtrim( (string) $mem['percent'], '%' ) ) ? (float) rtrim( (string) $mem['percent'], '%' ) : 0;

	return rest_ensure_response( [
		'success'       => true,
		'memory'        => $memory_mb,
		'limit'         => $limit_mb,
		'usage_percent' => $usage_percent,
		'formatted'     => $mem['formatted'],
	] );
}

/**
 * Get plugin profiler stats
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_get_plugin_profiler_stats( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	if ( ! class_exists( 'Sitepulse_Plugin_Profiler' ) ) {
		return new WP_Error( 'missing_class', 'Sitepulse_Plugin_Profiler class not found', [ 'status' => 500 ] );
	}

	$stats = Sitepulse_Plugin_Profiler::get_stats();
	$current_time = get_option( Sitepulse_Plugin_Profiler::OPTION_CURRENT_TIME, null );

	return rest_ensure_response( [
		'success' => true,
		'stats' => $stats,
		'count' => count( $stats ),
		'last_updated' => $current_time,
	] );
}

/**
 * Manually collect plugin profiler data
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_collect_plugin_data( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	if ( ! class_exists( 'Sitepulse_Plugin_Profiler' ) ) {
		return new WP_Error( 'missing_class', 'Sitepulse_Plugin_Profiler class not found', [ 'status' => 500 ] );
	}

	// Enable data collection for this request using global variable
	Sitepulse_Plugin_Profiler::enable_data_collection();

	Sitepulse_Plugin_Profiler::capture_baseline();
	Sitepulse_Plugin_Profiler::track_plugins();
	Sitepulse_Plugin_Profiler::persist();

	$stats = Sitepulse_Plugin_Profiler::get_stats();
	$total_time = Sitepulse_Plugin_Profiler::get_total_load_time();

	return rest_ensure_response( [
		'success' => true,
		'message' => __( 'Plugin data collected successfully', 'sitepulse' ),
		'stats' => $stats,
		'count' => count( $stats ),
		'total_time' => $total_time,
		'collected_at' => current_time( 'mysql' ),
	] );
}

/**
 * Check for vulnerabilities via REST API
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_check_vulnerabilities_api( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	if ( ! SITEPULSE_PRO_IS_ACTIVE ) {
		if ( ! class_exists( 'Sitepulse_Api_Service' ) ) {
			return new WP_Error( 'service_unavailable', __( 'API Service not found.', 'sitepulse' ), [ 'status' => 500 ] );
		}

		$response = Sitepulse_Api_Service::check_vulnerabilities();
	}

	if ( $response['success'] ) {
		$vuln_wait = isset( $response['vuln_wait'] ) ? $response['vuln_wait'] : false;

		if ( $vuln_wait ) {
			return rest_ensure_response( array(
				'success' => true,
				'data' => array(
					'message' => $response['message'],
					'vuln_wait' => true
				)
			) );
		} else {
			// Normalize vulnerabilities data for simple display
			$raw_vulns = isset( $response['vulnerabilities'] ) ? $response['vulnerabilities'] : [];
			$formatted_vulns = [];

			// Helper to process arrays
			$process_vulns = function ( $items, $prefix = '' ) use ( &$formatted_vulns ) {
				if ( is_array( $items ) ) {
					foreach ( $items as $item ) {
						$formatted_vulns[] = array(
							'name' => $prefix . ( isset( $item['title'] ) ? $item['title'] : 'Unknown Vulnerability' ),
							'version' => isset( $item['severity'] ) ? $item['severity'] : 'Unknown',
						);
					}
				}
			};

			// Process Core vulnerabilities
			if ( isset( $raw_vulns['wordpress_core'] ) && is_array( $raw_vulns['wordpress_core'] ) ) {
				$process_vulns( $raw_vulns['wordpress_core'], 'WP Core: ' );
			}

			// Process Plugin vulnerabilities (new nested format)
			if ( isset( $raw_vulns['plugins'] ) && is_array( $raw_vulns['plugins'] ) ) {
				foreach ( $raw_vulns['plugins'] as $slug => $plugin_data ) {
					if ( ! is_array( $plugin_data ) ) {
						continue;
					}

					$plugin_name = isset( $plugin_data['name'] ) ? (string) $plugin_data['name'] : (string) $slug;
					if ( isset( $plugin_data['vulnerabilities'] ) && is_array( $plugin_data['vulnerabilities'] ) ) {
						$process_vulns( $plugin_data['vulnerabilities'], 'Plugin (' . $plugin_name . '): ' );
					}
				}
			}

			// Process Theme vulnerabilities
			if ( isset( $raw_vulns['theme'] ) && is_array( $raw_vulns['theme'] ) ) {
				if ( isset( $raw_vulns['theme']['vulnerabilities'] ) && is_array( $raw_vulns['theme']['vulnerabilities'] ) ) {
					// Single theme object format
					$theme_name = isset( $raw_vulns['theme']['name'] ) ? (string) $raw_vulns['theme']['name'] : 'Theme';
					$process_vulns( $raw_vulns['theme']['vulnerabilities'], 'Theme (' . $theme_name . '): ' );
				} else {
					// Themes can be a flat array or a keyed object like plugins
					if ( ! empty( $raw_vulns['theme'] ) ) {
						reset( $raw_vulns['theme'] );
						$first_key = key( $raw_vulns['theme'] );

						$is_keyed_theme_object = (
							is_string( $first_key )
							&& isset( $raw_vulns['theme'][ $first_key ] )
							&& is_array( $raw_vulns['theme'][ $first_key ] )
							&& isset( $raw_vulns['theme'][ $first_key ]['vulnerabilities'] )
						);

						if ( $is_keyed_theme_object ) {
							foreach ( $raw_vulns['theme'] as $slug => $theme_data ) {
								if ( ! is_array( $theme_data ) ) {
									continue;
								}
								$theme_name = isset( $theme_data['name'] ) ? (string) $theme_data['name'] : (string) $slug;
								if ( isset( $theme_data['vulnerabilities'] ) && is_array( $theme_data['vulnerabilities'] ) ) {
									$process_vulns( $theme_data['vulnerabilities'], 'Theme (' . $theme_name . '): ' );
								}
							}
						} else {
							// Legacy flat array
							$process_vulns( $raw_vulns['theme'], 'Theme: ' );
						}
					}
				}
			}

			// Handle legacy or flat array format
			if ( empty( $formatted_vulns ) && is_array( $raw_vulns ) && ! empty( $raw_vulns ) && ! isset( $raw_vulns['wordpress_core'] ) ) {
				foreach ( $raw_vulns as $k => $v ) {
					if ( is_array( $v ) ) {
						$formatted_vulns[] = array(
							'name' => isset( $v['name'] ) ? $v['name'] : ( isset( $v['title'] ) ? $v['title'] : 'Unknown' ),
							'version' => isset( $v['version'] ) ? $v['version'] : ( isset( $v['severity'] ) ? $v['severity'] : '' ),
						);
					}
				}
			}

			update_option( 'sitepulse_vulnerabilities_list', $formatted_vulns, false );
			update_option( 'sitepulse_last_vulnerability_check', current_time( 'mysql' ), false );

			return rest_ensure_response( array(
				'success' => true,
				'data' => array(
					'message' => $response['message'],
					'vulnerabilities' => $formatted_vulns,
					'timestamp' => current_time( 'g:i A' ),
					'vuln_wait' => false
				)
			) );
		}
	} else {
		// Return success=false in JSON body handled by frontend, or HTTP error.
		// Frontend expects response.success = false
		return rest_ensure_response( array(
			'success' => false,
			'data' => array( 'message' => $response['message'] )
		) );
	}
}

/**
 * Request AI diagnostic via REST API
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_request_ai_diagnostic_api( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	// Get force_new parameter
	$params = $request->get_json_params();
	$force_new = isset( $params['force_new'] ) ? (bool) $params['force_new'] : false;

	if ( ! SITEPULSE_PRO_IS_ACTIVE ) {
		if ( ! class_exists( 'Sitepulse_Api_Service' ) ) {
			return new WP_Error( 'service_unavailable', __( 'API Service not found.', 'sitepulse' ), [ 'status' => 500 ] );
		}

		$response = Sitepulse_Api_Service::request_ai_diagnostic( $force_new );
	}

	// Schedule cron for polling if status is pending or processing
	if ( $response['success'] && isset( $response['status'] ) ) {
		$status = $response['status'];
		if ( in_array( $status, array( 'pending', 'processing' ), true ) ) {
			if ( class_exists( 'Sitepulse_AI_Diagnostic_Cron' ) ) {
				Sitepulse_AI_Diagnostic_Cron::schedule_status_check();
			}
		}
	}

	return rest_ensure_response( array(
		'success' => $response['success'],
		'data' => $response,
	) );
}

/**
 * Check AI diagnostic status via REST API
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_check_ai_diagnostic_status_api( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	if ( ! SITEPULSE_PRO_IS_ACTIVE ) {
		if ( ! class_exists( 'Sitepulse_Api_Service' ) ) {
			return new WP_Error( 'service_unavailable', __( 'API Service not found.', 'sitepulse' ), [ 'status' => 500 ] );
		}

		$response = Sitepulse_Api_Service::check_ai_diagnostic_status();
	}

	// If status changed to completed or failed, unschedule cron
	if ( isset( $response['status'] ) && in_array( $response['status'], array( 'completed', 'failed' ), true ) ) {
		if ( class_exists( 'Sitepulse_AI_Diagnostic_Cron' ) ) {
			Sitepulse_AI_Diagnostic_Cron::unschedule();
		}
	}

	return rest_ensure_response( array(
		'success' => isset( $response['success'] ) ? $response['success'] : true,
		'data' => $response,
	) );
}

/**
 * Get the canonical list of protected WP option keys that must not be modified.
 *
 * Used by both the autoload-options GET query (to exclude them) and the update
 * endpoint (to reject modifications).
 *
 * @return string[] Protected option names.
 */
function sitepulse_get_protected_options() {
	return array(
		'siteurl', 'home', 'blogname', 'blogdescription', 'admin_email',
		'users_can_register', 'start_of_week', 'use_balanceTags', 'use_smilies',
		'require_name_email', 'comments_notify', 'posts_per_rss', 'rss_use_excerpt',
		'mailserver_url', 'mailserver_login', 'mailserver_pass', 'mailserver_port',
		'default_category', 'default_comment_status', 'default_ping_status',
		'default_pingback_flag', 'posts_per_page', 'date_format', 'time_format',
		'links_updated_date_format', 'comment_moderation', 'moderation_notify',
		'rewrite_rules', 'active_plugins', 'template', 'stylesheet',
		'current_theme', 'db_version', 'initial_db_version', 'wp_user_roles',
		'WPLANG', 'cron', 'auto_core_update_notified',
	);
}

/**
 * Get autoloaded options from wp_options table
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_get_autoload_options( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	global $wpdb;

	// Core WP options that should not be modified
	$protected_options = sitepulse_get_protected_options();
	$protected_list    = "'" . implode( "','", array_map( 'esc_sql', $protected_options ) ) . "'";

	// WP 6.6+ uses 'on'/'off'/'auto-on'/'auto-off' instead of 'yes'/'no'
	$autoload_on_values = "'yes','on','auto-on'";

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT option_id, option_name, autoload, LENGTH(option_value) AS data_size
			FROM {$wpdb->options}
			WHERE autoload IN ({$autoload_on_values})
			AND option_name NOT IN ({$protected_list})
			AND option_name NOT LIKE %s
			ORDER BY data_size DESC
			LIMIT 20",
			'\_transient%'
		),
		ARRAY_A
	);

	if ( $results === null ) {
		return new WP_Error( 'db_error', 'Database query failed', [ 'status' => 500 ] );
	}

	// Format data sizes and normalize autoload values
	foreach ( $results as &$row ) {
		$row['option_id'] = (int) $row['option_id'];
		// Normalize autoload to 'on'/'off' for frontend consistency
		$row['autoload'] = in_array( $row['autoload'], array( 'yes', 'on', 'auto-on' ), true ) ? 'on' : 'off';
		$size = (int) $row['data_size'];
		if ( $size >= 1048576 ) {
			$row['data_size_formatted'] = number_format( $size / 1048576, 2 ) . ' MB';
		} elseif ( $size >= 1024 ) {
			$row['data_size_formatted'] = number_format( $size / 1024, 2 ) . ' KB';
		} else {
			$row['data_size_formatted'] = $size . ' B';
		}
	}

	// Also get total count of autoload=yes
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	$total_autoload_size = $wpdb->get_var(
		"SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload IN ('yes','on','auto-on')"
	);

	return rest_ensure_response( array(
		'success' => true,
		'data'    => $results,
		'count'   => count( $results ),
		'total_autoload_size' => $total_autoload_size ? (int) $total_autoload_size : 0,
		'total_autoload_size_formatted' => $total_autoload_size
			? ( $total_autoload_size >= 1048576
				? number_format( $total_autoload_size / 1048576, 2 ) . ' MB'
				: number_format( $total_autoload_size / 1024, 2 ) . ' KB' )
			: '0 B',
	) );
}

/**
 * Update autoload value for a specific option
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
function sitepulse_update_autoload_option( WP_REST_Request $request ) {
	// Validate nonce for extra security
	$nonce = $request->get_param( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	$params = $request->get_json_params();

	if ( ! isset( $params['option_id'] ) || ! isset( $params['autoload'] ) ) {
		return new WP_Error( 'missing_params', 'option_id and autoload are required', [ 'status' => 400 ] );
	}

	$option_id = absint( $params['option_id'] );
	$autoload  = sanitize_text_field( $params['autoload'] );

	// WP 6.6+ uses 'on'/'off'; also accept legacy 'yes'/'no'
	$valid_values = array( 'yes', 'no', 'on', 'off' );
	if ( ! in_array( $autoload, $valid_values, true ) ) {
		return new WP_Error( 'invalid_autoload', 'autoload must be "on" or "off"', [ 'status' => 400 ] );
	}

	// Normalize to WP 6.6+ format
	if ( $autoload === 'yes' ) {
		$autoload = 'on';
	} elseif ( $autoload === 'no' ) {
		$autoload = 'off';
	}

	global $wpdb;

	// Verify the option exists
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	$option = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT option_id, option_name FROM {$wpdb->options} WHERE option_id = %d",
			$option_id
		),
		ARRAY_A
	);

	if ( ! $option ) {
		return new WP_Error( 'option_not_found', 'Option not found', [ 'status' => 404 ] );
	}

	// Protect critical WP options
	if ( in_array( $option['option_name'], sitepulse_get_protected_options(), true ) ) {
		return new WP_Error( 'protected_option', 'This option is protected and cannot be modified', [ 'status' => 403 ] );
	}

	// Update the autoload value
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->update(
		$wpdb->options,
		array( 'autoload' => $autoload ),
		array( 'option_id' => $option_id ),
		array( '%s' ),
		array( '%d' )
	);

	if ( $result === false ) {
		return new WP_Error( 'update_failed', 'Failed to update option', [ 'status' => 500 ] );
	}

	// Clear the alloptions cache so WP picks up the change
	wp_cache_delete( 'alloptions', 'options' );

	return rest_ensure_response( array(
		'success'     => true,
		'option_id'   => $option_id,
		'option_name' => $option['option_name'],
		'autoload'    => $autoload,
		'message'     => sprintf(
			/* translators: 1: option name, 2: autoload value */
			__( 'Option "%1$s" autoload set to "%2$s"', 'sitepulse' ),
			$option['option_name'],
			$autoload
		),
	) );
}

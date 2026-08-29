<?php
/**
 * SitePulse API Service
 * 
 * Handles communication with the SitePulse API endpoint for sending
 * resource and API request data and retrieving license information.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Api_Service
 */
class Sitepulse_Api_Service {

	/**
	 * API endpoint URL
	 *
	 * @var string
	 */
	private const API_ENDPOINT = 'https://api.sitepulse.me/api/website/website-request';

	/**
	 * API endpoint Vulnerabilities URL
	 *
	 * @var string
	 */
	private const API_ENDPOINT_VULNERABILITIES = 'https://api.sitepulse.me/api/website/website-vulnerabilities';

	/**
	 * API endpoint for AI diagnostic
	 *
	 * @var string
	 */
	private const API_ENDPOINT_AI_DIAGNOSTIC = 'https://api.sitepulse.me/api/ai-diagnostic';

	/**
	 * API endpoint for deactivation feedback
	 *
	 * @var string
	 */
	private const API_ENDPOINT_DEACTIVATION_FEEDBACK = 'https://api.sitepulse.me/api/feedback/deactivation';

	/**
	 * Option key for storing license number
	 *
	 * @var string
	 */
	private const LICENSE_OPTION_KEY = 'sitepulse_license_key';

	/**
	 * Option key for storing last API request timestamp
	 *
	 * @var string
	 */
	private const LAST_REQUEST_OPTION_KEY = 'sitepulse_last_api_request';

	/**
	 * Option key for storing pagespeed report
	 *
	 * @var string
	 */
	private const PAGESPEED_REPORT_OPTION_KEY = 'sitepulse_pagespeed_report';

	/**
	 * Option key for storing AI diagnostic report
	 *
	 * @var string
	 */
	private const AI_DIAGNOSTIC_REPORT_OPTION_KEY = 'sitepulse_ai_diagnostic_report';

	/**
	 * Option key for storing AI diagnostic request status
	 *
	 * @var string
	 */
	private const AI_DIAGNOSTIC_STATUS_KEY = 'sitepulse_ai_diagnostic_status';

	/**
	 * Option key for storing AI diagnostic report ID
	 *
	 * @var string
	 */
	private const AI_DIAGNOSTIC_REPORT_ID_KEY = 'sitepulse_ai_diagnostic_report_id';

	/**
	 * Option key for storing AI diagnostic request timestamp
	 *
	 * @var string
	 */
	private const AI_DIAGNOSTIC_REQUESTED_AT_KEY = 'sitepulse_ai_diagnostic_requested_at';

	/**
	 * Send website data to the API endpoint
	 *
	 * @return array Response array with success status, message, and license number if available
	 */
	public static function send_website_data() {
		try {
			// Collect resources data (profiler stats)
			$resources = self::get_resources_data();

			// Collect API/Request data (curl events) - limit size to prevent timeouts
			$api_requests = self::get_api_requests_data();

			// Limit API requests to prevent large payloads that could cause timeouts
			// Keep only the most recent 10 requests
			if ( is_array( $api_requests ) && count( $api_requests ) > 10 ) {
				$api_requests = array_slice( $api_requests, -10 );
			}

			// Get stored license key if available
			$license_key = self::get_license_key();

			// Collect website info
			$website_info = self::get_website_info();

			// Prepare payload
			$payload = array(
				'resources' => $resources,
				'api_requests' => $api_requests,
				'domain' => home_url(),
				'site_name' => get_bloginfo( 'name' ),
				'website_info' => $website_info,
				'ip_address' => self::get_host_ip_address(),
				'timestamp' => current_time( 'mysql' ),
			);

			// Include license_key in payload if available
			if ( ! empty( $license_key ) ) {
				$payload['license_key'] = $license_key;
			}

			// Check payload size to prevent issues
			$payload_json = wp_json_encode( $payload );
			$payload_size = strlen( $payload_json );

			// Limit payload to 2MB to prevent timeouts (adjust if needed)
			$max_payload_size = 2 * 1024 * 1024; // 2MB
			if ( $payload_size > $max_payload_size ) {
				// Reduce data size by limiting resources and api_requests
				if ( is_array( $payload['resources'] ) && count( $payload['resources'] ) > 50 ) {
					$payload['resources'] = array_slice( $payload['resources'], -50 );
				}
				if ( is_array( $payload['api_requests'] ) && count( $payload['api_requests'] ) > 50 ) {
					$payload['api_requests'] = array_slice( $payload['api_requests'], -50 );
				}
				$payload_json = wp_json_encode( $payload );
			}

			// Send request to API
			$response = self::make_api_request( $payload );

			// Update last request timestamp
			update_option( self::LAST_REQUEST_OPTION_KEY, current_time( 'timestamp' ), false );

			return $response;
		} catch (Exception $e) {
			return array(
				'success' => false,
				'message' => __( 'An error occurred while preparing the request', 'sitepulse' ),
				'license_key' => null,
			);
		}
	}

	/**
	 * Get resources data from profiler
	 *
	 * @return array Resources data
	 */
	private static function get_resources_data() {
		$resources = get_option( SITEPULSE_PROFILER_KEY, array() );

		// Ensure we return an array
		if ( ! is_array( $resources ) ) {
			return array();
		}

		return $resources;
	}

	/**
	 * Get API/Request data from curl loader
	 *
	 * @return array API requests data
	 */
	private static function get_api_requests_data() {
		$api_requests = get_option( SITEPULSE_CURL_API_KEY, array() );

		// Ensure we return an array
		if ( ! is_array( $api_requests ) ) {
			return array();
		}

		return $api_requests;
	}

	/**
	 * Make API request to the endpoint
	 *
	 * @param array $payload Request payload data
	 * @return array Response array with success status, message, and license number
	 */
	private static function make_api_request( array $payload ) {
		// Check for testing endpoint override (defined in wp-config.php for testing purposes)
		$endpoint = defined( 'SITEPULSE_TESTING_API_ENDPOINT' ) && ! empty( SITEPULSE_TESTING_API_ENDPOINT )
			? SITEPULSE_TESTING_API_ENDPOINT
			: self::API_ENDPOINT;

		// Validate endpoint URL
		if ( empty( $endpoint ) || ! filter_var( $endpoint, FILTER_VALIDATE_URL ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid API endpoint URL', 'sitepulse' ),
				'license_key' => null,
			);
		}

		// Encode payload with error handling
		$payload_json = wp_json_encode( $payload );
		if ( false === $payload_json ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to encode request payload', 'sitepulse' ),
				'license_key' => null,
			);
		}

		// Set reasonable timeout based on payload size
		$timeout = 30; // Default 30 seconds
		$payload_size = strlen( $payload_json );

		// Increase timeout for larger payloads, but cap at 60 seconds
		if ( $payload_size > 500000 ) { // > 500KB
			$timeout = 45;
		}
		if ( $payload_size > 1000000 ) { // > 1MB
			$timeout = 60;
		}

		// Make the request with error handling
		$response = wp_remote_post(
			$endpoint,
			array(
				'timeout' => $timeout,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => true,
				'sslverify' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
					'User-Agent' => 'SitePulse-WordPress/' . SITEPULSE_VERSION,
				),
				'body' => $payload_json,
				'cookies' => array(),
			)
		);

		// Check for WP_Error
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();

			// Provide more specific error messages
			if ( strpos( $error_message, 'timeout' ) !== false || strpos( $error_message, 'timed out' ) !== false ) {
				$error_message = __( 'Request timed out. The server may be slow or the payload is too large.', 'sitepulse' );
			} elseif ( strpos( $error_message, 'resolve' ) !== false || strpos( $error_message, 'DNS' ) !== false ) {
				$error_message = __( 'Could not connect to API server. Please check your internet connection.', 'sitepulse' );
			} elseif ( strpos( $error_message, 'SSL' ) !== false || strpos( $error_message, 'certificate' ) !== false ) {
				$error_message = __( 'SSL certificate error. Please contact your hosting provider.', 'sitepulse' );
			}

			return array(
				'success' => false,
				'message' => $error_message,
				'license_key' => null,
			);
		}

		// Get response code
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Check if request was successful
		if ( $response_code !== 200 ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %d: HTTP response code */
					__( 'API request failed with status code: %d', 'sitepulse' ),
					$response_code
				),
				'license_key' => null,
			);
		}

		// Decode JSON response
		$decoded_response = json_decode( $response_body, true );

		// Check if JSON decoding was successful
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to decode API response', 'sitepulse' ),
				'license_key' => null,
			);
		}

		// Extract license key from response
		// Priority: new_license object means a new license key must be stored
		$license_key = null;

		// Handle new_license object - this indicates a new license key needs to be stored
		if ( isset( $decoded_response['new_license'] ) && is_array( $decoded_response['new_license'] ) ) {
			$new_license = $decoded_response['new_license'];
			if ( isset( $new_license['license_key'] ) && ! empty( $new_license['license_key'] ) ) {
				$license_key = sanitize_text_field( $new_license['license_key'] );

				// Store the new license key (this replaces any existing license key)
				update_option( self::LICENSE_OPTION_KEY, $license_key, false );

				// Store additional license information from new_license
				if ( isset( $new_license['type'] ) ) {
					update_option( 'sitepulse_license_type', sanitize_text_field( $new_license['type'] ), false );
				}
				if ( isset( $new_license['status'] ) ) {
					update_option( 'sitepulse_license_status', sanitize_text_field( $new_license['status'] ), false );
				}
				if ( isset( $new_license['generated_at'] ) ) {
					update_option( 'sitepulse_license_generated_at', sanitize_text_field( $new_license['generated_at'] ), false );
				}
			}
		} elseif ( isset( $decoded_response['license_key'] ) ) {
			// Direct license_key in response (update existing if present)
			$license_key = sanitize_text_field( $decoded_response['license_key'] );
			if ( ! empty( $license_key ) ) {
				update_option( self::LICENSE_OPTION_KEY, $license_key, false );
			}
		} elseif ( isset( $decoded_response['new_license'] ) ) {
			// Fallback: license field (update existing if present)
			$license_key = sanitize_text_field( $decoded_response['new_license'] );
			if ( ! empty( $license_key ) ) {
				update_option( self::LICENSE_OPTION_KEY, $license_key, false );
			}
		} elseif ( isset( $decoded_response['license'] ) ) {
			// Fallback: license field (update existing if present)
			$license_key = sanitize_text_field( $decoded_response['license'] );
			if ( ! empty( $license_key ) ) {
				update_option( self::LICENSE_OPTION_KEY, $license_key, false );
			}
		}

		// Extract and store pagespeed_report if provided and not empty
		if ( isset( $decoded_response['pagespeed_report'] ) && ! empty( $decoded_response['pagespeed_report'] ) ) {
			// Store pagespeed report (can be array, object, or string)
			$pagespeed_report = $decoded_response['pagespeed_report'];
			// If it's an array or object, encode it; otherwise store as-is
			if ( is_array( $pagespeed_report ) || is_object( $pagespeed_report ) ) {
				$pagespeed_report = wp_json_encode( $pagespeed_report );
			}
			update_option( self::PAGESPEED_REPORT_OPTION_KEY, $pagespeed_report, false );
		}

		// Extract and store ai_diagnostic_report if provided and not empty
		if ( isset( $decoded_response['ai_diagnostic_report'] ) && ! empty( $decoded_response['ai_diagnostic_report'] ) ) {
			// Store AI diagnostic report (can be array, object, or string)
			$ai_diagnostic_report = $decoded_response['ai_diagnostic_report'];
			// If it's an array or object, encode it; otherwise store as-is
			if ( is_array( $ai_diagnostic_report ) || is_object( $ai_diagnostic_report ) ) {
				$ai_diagnostic_report = wp_json_encode( $ai_diagnostic_report );
			}
			update_option( self::AI_DIAGNOSTIC_REPORT_OPTION_KEY, $ai_diagnostic_report, false );
		}

		// Extract and store or clear whitelabel data
		if ( class_exists( 'Sitepulse_Whitelabel_Service' ) ) {
			if ( isset( $decoded_response['whitelabel'] ) && is_array( $decoded_response['whitelabel'] ) ) {
				if ( ! empty( $decoded_response['whitelabel']['enabled'] ) ) {
					Sitepulse_Whitelabel_Service::store_whitelabel_data( $decoded_response['whitelabel'] );
				} else {
					Sitepulse_Whitelabel_Service::clear_whitelabel_data();
				}
			} else {
				// Prevent stale whitelabel branding when API stops sending it.
				Sitepulse_Whitelabel_Service::clear_whitelabel_data();
			}
		}

		return array(
			'success' => true,
			'message' => __( 'Data sent successfully to API', 'sitepulse' ),
			'license_key' => $license_key,
			'response' => $decoded_response,
		);
	}

	/**
	 * Get stored license number
	 *
	 * @return string|null License number or null if not set
	 */
	public static function get_license_key() {
		return get_option( self::LICENSE_OPTION_KEY, null );
	}

	/**
	 * Get last API request timestamp
	 *
	 * @return int|null Timestamp or null if never requested
	 */
	public static function get_last_request_timestamp() {
		return get_option( self::LAST_REQUEST_OPTION_KEY, null );
	}

	/**
	 * Get stored pagespeed report
	 *
	 * @param bool $decode_json Whether to decode JSON if stored as JSON string
	 * @return mixed Pagespeed report data or null if not set
	 */
	public static function get_pagespeed_report( $decode_json = true ) {
		$report = get_option( self::PAGESPEED_REPORT_OPTION_KEY, null );

		if ( $report === null ) {
			return null;
		}

		// If decode_json is true and the value is a JSON string, decode it
		if ( $decode_json && is_string( $report ) ) {
			$decoded = json_decode( $report, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return $decoded;
			}
		}

		return $report;
	}

	/**
	 * Get stored AI diagnostic report
	 *
	 * @param bool $decode_json Whether to decode JSON if stored as JSON string
	 * @return mixed AI diagnostic report data or null if not set
	 */
	public static function get_ai_diagnostic_report( $decode_json = true ) {
		$report = get_option( self::AI_DIAGNOSTIC_REPORT_OPTION_KEY, null );

		if ( $report === null ) {
			return null;
		}

		// If decode_json is true and the value is a JSON string, decode it
		if ( $decode_json && is_string( $report ) ) {
			$decoded = json_decode( $report, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return $decoded;
			}
		}

		return $report;
	}
	/**
	 * Get website info (WP version, active plugins, active theme)
	 *
	 * @return array Website info
	 */
	private static function get_website_info() {
		global $wp_version;

		// Ensure plugin functions are available
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Get active plugins
		$active_plugins = get_option( 'active_plugins' );
		$plugins_data = array();

		if ( is_array( $active_plugins ) ) {
			foreach ( $active_plugins as $plugin_file ) {
				$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
				if ( file_exists( $plugin_path ) ) {
					$data = get_plugin_data( $plugin_path );
					$slug = dirname( $plugin_file );
					if ( '.' === $slug ) {
						$slug = basename( $plugin_file, '.php' );
					}

					$plugins_data[] = array(
						'name' => $data['Name'],
						'version' => $data['Version'],
						'slug' => $slug,
					);
				}
			}
		}

		// Get active theme
		$theme = wp_get_theme();
		$theme_data = array(
			'name' => $theme->get( 'Name' ),
			'version' => $theme->get( 'Version' ),
			'slug' => $theme->get_stylesheet(),
		);

		// If child theme, maybe get parent? For now just active theme is fine.

		// Get cron failure info
		$cron_failure_info = array();
		if ( class_exists( 'Sitepulse_Settings' ) ) {
			$settings = Sitepulse_Settings::getInstance();
			$cron_failure = $settings->get_cron_failure_info();
			if ( $cron_failure['failing'] ) {
				$cron_failure_info = array(
					'failing'     => true,
					'description' => $cron_failure['description'],
				);
			}
		}

		// Get error log count
		$error_log_count = 0;
		if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
			$error_stats = Sitepulse_Error_Handler::get_error_log_stats();
			$error_log_count = isset( $error_stats['error_count'] ) ? (int) $error_stats['error_count'] : 0;
		}

		return array(
			'wp_version'      => $wp_version,
			'plugins'         => $plugins_data,
			'theme'           => $theme_data,
			'cron_failure'    => $cron_failure_info,
			'error_log_count' => $error_log_count,
		);
	}

	/**
	 * Get the host/server IP address
	 *
	 * Attempts to resolve the IP address of the host server.
	 * Uses multiple methods as fallback.
	 *
	 * @return string|null IP address or null if not found
	 */
	private static function get_host_ip_address() {
		// Method 1: Get IP from $_SERVER if available
		if ( ! empty( $_SERVER['SERVER_ADDR'] ) ) {
			return sanitize_text_field( $_SERVER['SERVER_ADDR'] );
		}

		// Method 2: Try gethostbyname on the domain
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( ! empty( $domain ) ) {
			$ip = gethostbyname( $domain );
			// gethostbyname returns the hostname if it fails, so check if it's a valid IP
			if ( $ip !== $domain && filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}

		// Method 3: Try to get local IP using gethostname
		$hostname = gethostname();
		if ( $hostname ) {
			$ip = gethostbyname( $hostname );
			if ( $ip !== $hostname && filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}

		// Method 4: Fallback to LOCAL_ADDR (IIS specific)
		if ( ! empty( $_SERVER['LOCAL_ADDR'] ) ) {
			return sanitize_text_field( $_SERVER['LOCAL_ADDR'] );
		}

		return null;
	}

	/**
	 * Check for website vulnerabilities
	 *
	 * @return array Response array with success status, message, and vulnerabilities list
	 */
	public static function check_vulnerabilities() {
		try {
			// Get stored license key if available
			$license_key = self::get_license_key();

			// If it's the first time (no last request timestamp), send website data first
			if ( self::get_last_request_timestamp() === null ) {
				self::send_website_data();
			}

			// Collect website info
			$website_info = self::get_website_info();

			// Prepare payload
			$payload = array(
				'domain' => home_url(),
				'site_name' => get_bloginfo( 'name' ),
				'website_info' => $website_info,
				'timestamp' => current_time( 'mysql' ),
				'license_key' => $license_key,
			);

			// Send request to API
			return self::make_vulnerability_api_request( $payload );

		} catch (Exception $e) {
			return array(
				'success' => false,
				'message' => __( 'An error occurred while preparing the vulnerability check', 'sitepulse' ),
			);
		}
	}

	/**
	 * Make API request to the vulnerabilities endpoint
	 *
	 * @param array $payload Request payload data
	 * @param bool  $retry Whether this is a retry attempt
	 * @return array Response array with success status, message, and vulnerabilities
	 */
	private static function make_vulnerability_api_request( array $payload, $retry = false ) {
		// Check if external API is enabled in settings
		$sp_all_settings = get_option( 'sitepulse_settings', array() );
		$sp_external_api_enabled = isset( $sp_all_settings['external_api_enabled'] ) ? (bool) $sp_all_settings['external_api_enabled'] : true;
		if ( ! $sp_external_api_enabled ) {
			return array(
				'success' => false,
				'message' => __( 'External API data collection is disabled. If you want to enable it, please update your settings.', 'sitepulse' ),
			);
		}

		// Use the defined vulnerability endpoint
		$endpoint = self::API_ENDPOINT_VULNERABILITIES;

		// Validate endpoint URL
		if ( empty( $endpoint ) || ! filter_var( $endpoint, FILTER_VALIDATE_URL ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid API endpoint URL', 'sitepulse' ),
			);
		}

		// Encode payload
		$payload_json = wp_json_encode( $payload );
		if ( false === $payload_json ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to encode request payload', 'sitepulse' ),
			);
		}

		// Make the request
		$response = wp_remote_post(
			$endpoint,
			array(
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => true,
				'sslverify' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
					'User-Agent' => 'SitePulse-WordPress/' . SITEPULSE_VERSION,
				),
				'body' => $payload_json,
				'cookies' => array(),
			)
		);

		// Check for WP_Error
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => __( 'Could not connect to API server. Please try again later.', 'sitepulse' ),
			);
		}

		// Get response code
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Decode JSON response
		$decoded_response = json_decode( $response_body, true );

		// Handle Site Not Registered / Invalid License (401, 404, or specific message)
		$is_invalid_license = false;
		if ( isset( $decoded_response['message'] ) && stripos( $decoded_response['message'], 'Invalid license key' ) !== false ) {
			$is_invalid_license = true;
		}

		if ( ( 401 === $response_code || 404 === $response_code || $is_invalid_license ) && ! $retry ) {
			// Register site first
			self::send_website_data();
			
			// Refresh license key
			$new_license_key = self::get_license_key();
			
			// Update payload with new license
			$payload['license_key'] = $new_license_key;
			
			// Retry request once
			return self::make_vulnerability_api_request( $payload, true );
		}

		// Handle Rate Limiting
		if ( 429 === $response_code ) {
			return array(
				'success' => false,
				'message' => __( 'Core: Too many requests. Please wait trying again later.', 'sitepulse' ),
			);
		}

		// Check if request was successful
		if ( 200 !== $response_code ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %d: HTTP response code */
					__( 'Free scan requests exhausted with status code: %d. Please try again tomorrow.', 'sitepulse' ),
					$response_code
				),
			);
		}

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to decode API response', 'sitepulse' ),
			);
		}

		// Check for new license and update if present
		if ( isset( $decoded_response['new_license'] ) ) {
			$new_license_key = null;

			if ( is_array( $decoded_response['new_license'] ) && isset( $decoded_response['new_license']['license_key'] ) ) {
				$new_license_key = sanitize_text_field( $decoded_response['new_license']['license_key'] );

				// Store additional info if available
				if ( isset( $decoded_response['new_license']['type'] ) ) {
					update_option( 'sitepulse_license_type', sanitize_text_field( $decoded_response['new_license']['type'] ), false );
				}
				if ( isset( $decoded_response['new_license']['status'] ) ) {
					update_option( 'sitepulse_license_status', sanitize_text_field( $decoded_response['new_license']['status'] ), false );
				}
			} elseif ( is_string( $decoded_response['new_license'] ) ) {
				$new_license_key = sanitize_text_field( $decoded_response['new_license'] );
			}

			if ( ! empty( $new_license_key ) ) {
				update_option( self::LICENSE_OPTION_KEY, $new_license_key, false );
			}
		}

		// Parse vulnerabilities
		$vulnerabilities = isset( $decoded_response['vulnerabilities'] ) ? $decoded_response['vulnerabilities'] : array();

		return array(
			'success' => true,
			'message' => empty( $vulnerabilities ) ? __( 'No vulnerabilities found. The site is scheduled for a re-scan; please check back later.', 'sitepulse' ) : __( 'Vulnerabilities detected.', 'sitepulse' ),
			'vulnerabilities' => $vulnerabilities,
		);
	}

	/**
	 * Request AI diagnostic from the API
	 *
	 * @param bool $force_new Force a new diagnostic even if one exists
	 * @return array Response array with status, message, and report_id
	 */
	public static function request_ai_diagnostic( $force_new = false ) {
		// Check if external API is enabled
		$sp_all_settings = get_option( 'sitepulse_settings', array() );
		$sp_external_api_enabled = isset( $sp_all_settings['external_api_enabled'] ) ? (bool) $sp_all_settings['external_api_enabled'] : true;
		if ( ! $sp_external_api_enabled ) {
			return array(
				'success' => false,
				'message' => __( 'External API is disabled. Enable it in Settings to use AI diagnostics.', 'sitepulse' ),
			);
		}

		try {
			// If it's the first time (no last request timestamp), send website data first
			if ( self::get_last_request_timestamp() === null ) {
				self::send_website_data();
			}

			// Get (possibly newly issued) license key
			$license_key = self::get_license_key();

			// Collect metrics data
			$resources = self::get_resources_data();
			$api_requests = self::get_api_requests_data();
			$website_info = self::get_website_info();

			// Get vulnerabilities if available
			$vulnerabilities = get_option( 'sitepulse_vulnerabilities_list', array() );

			// Build metrics payload matching the expected API format
			$metrics = array(
				'hooks' => $resources,
				'api_requests' => is_array( $api_requests ) ? array_slice( $api_requests, -20 ) : array(),
				'website_info' => $website_info,
				'vulnerabilities' => is_array( $vulnerabilities ) ? $vulnerabilities : array(),
			);

			// Prepare payload
			$payload = array(
				'domain' => wp_parse_url( home_url(), PHP_URL_HOST ),
				'license_key' => $license_key,
				'force_new' => $force_new,
				'metrics' => $metrics,
			);

			// Make API request
			$response = self::make_ai_diagnostic_api_request( $payload );

			// Store request state if successful
			if ( $response['success'] ) {
				update_option( self::AI_DIAGNOSTIC_STATUS_KEY, $response['status'], false );
				update_option( self::AI_DIAGNOSTIC_REQUESTED_AT_KEY, current_time( 'mysql' ), false );

				if ( isset( $response['ai_diagnostic_report_id'] ) ) {
					update_option( self::AI_DIAGNOSTIC_REPORT_ID_KEY, $response['ai_diagnostic_report_id'], false );
				}

				// Store queue position as transient (expires in 10 minutes)
				if ( isset( $response['queue_position'] ) ) {
					set_transient( 'sitepulse_ai_diagnostic_queue_position', $response['queue_position'], 600 );
				}

				// If completed, store the report and clear queue position
				if ( $response['status'] === 'completed' && isset( $response['diagnostic_result'] ) ) {
					self::store_ai_diagnostic_report( $response['diagnostic_result'] );
					delete_transient( 'sitepulse_ai_diagnostic_queue_position' );
				}
			}

			return $response;

		} catch ( Exception $e ) {
			return array(
				'success' => false,
				'message' => __( 'An error occurred while requesting the AI diagnostic', 'sitepulse' ),
			);
		}
	}

	/**
	 * Check AI diagnostic status
	 *
	 * @return array Response array with status and data
	 */
	public static function check_ai_diagnostic_status() {
		// Check if external API is enabled
		$sp_all_settings = get_option( 'sitepulse_settings', array() );
		$sp_external_api_enabled = isset( $sp_all_settings['external_api_enabled'] ) ? (bool) $sp_all_settings['external_api_enabled'] : true;
		if ( ! $sp_external_api_enabled ) {
			return array(
				'success' => false,
				'message' => __( 'External API is disabled.', 'sitepulse' ),
			);
		}

		$current_status = get_option( self::AI_DIAGNOSTIC_STATUS_KEY, null );
		$report_id = get_option( self::AI_DIAGNOSTIC_REPORT_ID_KEY, null );
		$requested_at = get_option( self::AI_DIAGNOSTIC_REQUESTED_AT_KEY, null );

		// If no request has been made, return empty state
		if ( empty( $current_status ) || empty( $report_id ) ) {
			$existing_report = self::get_ai_diagnostic_report( true );
			if ( ! empty( $existing_report ) ) {
				return array(
					'success' => true,
					'status' => 'completed',
					'message' => __( 'Previous AI diagnostic available.', 'sitepulse' ),
					'diagnostic_result' => $existing_report,
					'requested_at' => $requested_at,
				);
			}

			return array(
				'success' => true,
				'status' => 'none',
				'message' => __( 'No AI diagnostic has been requested yet.', 'sitepulse' ),
			);
		}

		// If already completed or failed, return stored data
		if ( $current_status === 'completed' ) {
			$report = self::get_ai_diagnostic_report( true );
			return array(
				'success' => true,
				'status' => 'completed',
				'message' => __( 'AI diagnostic completed.', 'sitepulse' ),
				'diagnostic_result' => $report,
				'requested_at' => $requested_at,
			);
		}

		if ( $current_status === 'failed' ) {
			return array(
				'success' => false,
				'status' => 'failed',
				'message' => __( 'Previous AI diagnostic failed. Request a new one.', 'sitepulse' ),
				'requested_at' => $requested_at,
			);
		}

		// If pending or processing, check API for updated status
		$license_key = self::get_license_key();
		$payload = array(
			'domain' => wp_parse_url( home_url(), PHP_URL_HOST ),
			'license_key' => $license_key,
			'force_new' => false,
		);

		$response = self::make_ai_diagnostic_api_request( $payload );

		// Update stored status
		if ( $response['success'] && isset( $response['status'] ) ) {
			update_option( self::AI_DIAGNOSTIC_STATUS_KEY, $response['status'], false );

			// Update queue position transient
			if ( isset( $response['queue_position'] ) ) {
				set_transient( 'sitepulse_ai_diagnostic_queue_position', $response['queue_position'], 600 );
			}

			// If completed, store the report and clear queue position
			if ( $response['status'] === 'completed' && isset( $response['diagnostic_result'] ) ) {
				self::store_ai_diagnostic_report( $response['diagnostic_result'] );
				delete_transient( 'sitepulse_ai_diagnostic_queue_position' );
			}
		}

		$response['requested_at'] = $requested_at;
		return $response;
	}

	/**
	 * Get current AI diagnostic status without making API call
	 *
	 * @return array Status information
	 */
	public static function get_ai_diagnostic_status() {
		return array(
			'status' => get_option( self::AI_DIAGNOSTIC_STATUS_KEY, null ),
			'report_id' => get_option( self::AI_DIAGNOSTIC_REPORT_ID_KEY, null ),
			'requested_at' => get_option( self::AI_DIAGNOSTIC_REQUESTED_AT_KEY, null ),
		);
	}

	/**
	 * Clear AI diagnostic request state
	 */
	public static function clear_ai_diagnostic_request() {
		delete_option( self::AI_DIAGNOSTIC_STATUS_KEY );
		delete_option( self::AI_DIAGNOSTIC_REPORT_ID_KEY );
		delete_option( self::AI_DIAGNOSTIC_REQUESTED_AT_KEY );
	}

	/**
	 * Store AI diagnostic report
	 *
	 * @param array|object $report The diagnostic report
	 */
	private static function store_ai_diagnostic_report( $report ) {
		$encoded = is_array( $report ) || is_object( $report ) ? wp_json_encode( $report ) : $report;
		update_option( self::AI_DIAGNOSTIC_REPORT_OPTION_KEY, $encoded, false );
	}

	/**
	 * Make API request to the AI diagnostic endpoint
	 *
	 * @param array $payload Request payload data
	 * @param bool  $retry Whether this is a retry attempt
	 * @return array Response array with success status, message, and data
	 */
	private static function make_ai_diagnostic_api_request( array $payload, $retry = false ) {
		$endpoint = self::API_ENDPOINT_AI_DIAGNOSTIC;

		// Validate endpoint URL
		if ( empty( $endpoint ) || ! filter_var( $endpoint, FILTER_VALIDATE_URL ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid API endpoint URL', 'sitepulse' ),
			);
		}

		// Encode payload
		$payload_json = wp_json_encode( $payload );
		if ( false === $payload_json ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to encode request payload', 'sitepulse' ),
			);
		}

		// Make the request
		$response = wp_remote_post(
			$endpoint,
			array(
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => true,
				'sslverify' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
					'User-Agent' => 'SitePulse-WordPress/' . SITEPULSE_VERSION,
				),
				'body' => $payload_json,
				'cookies' => array(),
			)
		);

		// Check for WP_Error (includes Cloudflare or connection errors)
		if ( is_wp_error( $response ) ) {
			// Do not update status to 'failed' on transient errors to allow polling to retry.
			$error_message = $response->get_error_message();
			return array(
				'success' => false,
				// Preserve current status so cron/UI polling doesn't stop on transient failures.
				'status'  => get_option( self::AI_DIAGNOSTIC_STATUS_KEY, 'pending' ),
				'message' => __( 'Could not connect to API server: ', 'sitepulse' ) . $error_message,
			);
		}

		// Get response code
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Decode JSON response early to check for messages
		$decoded_response = json_decode( $response_body, true );

		// Handle Site Not Registered / Invalid License (401, 404, or specific message)
		$is_invalid_license = false;
		if ( isset( $decoded_response['message'] ) && stripos( $decoded_response['message'], 'Invalid license key' ) !== false ) {
			$is_invalid_license = true;
		}

		if ( ( 401 === $response_code || 404 === $response_code || $is_invalid_license ) && ! $retry ) {
			// Register site first
			self::send_website_data();
			
			// Refresh license key
			$new_license_key = self::get_license_key();
			
			// Update payload with new license
			$payload['license_key'] = $new_license_key;
			
			// Retry request once
			return self::make_ai_diagnostic_api_request( $payload, true );
		}

		// Check for Cloudflare or server errors (5xx)
		if ( $response_code >= 500 ) {
			update_option( self::AI_DIAGNOSTIC_STATUS_KEY, 'failed', false );

			return array(
				'success' => false,
				'status' => 'failed',
				'message' => sprintf(
					/* translators: %d: HTTP response code */
					__( 'Server error (HTTP %d). Please try again later.', 'sitepulse' ),
					$response_code
				),
			);
		}

		// Handle rate limiting
		if ( 429 === $response_code ) {
			return array(
				'success' => false,
				'status'  => get_option( self::AI_DIAGNOSTIC_STATUS_KEY, 'pending' ),
				'message' => __( 'Too many requests. Please wait before trying again.', 'sitepulse' ),
			);
		}

		// Check for other client errors
		if ( $response_code >= 400 && $response_code < 500 ) {
			$message = isset( $decoded_response['message'] ) ? $decoded_response['message'] : __( 'Request failed', 'sitepulse' );

			return array(
				'success' => false,
				'status'  => get_option( self::AI_DIAGNOSTIC_STATUS_KEY, 'pending' ),
				'message' => $message,
			);
		}

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			update_option( self::AI_DIAGNOSTIC_STATUS_KEY, 'failed', false );

			return array(
				'success' => false,
				'status' => 'failed',
				'message' => __( 'Failed to decode API response', 'sitepulse' ),
			);
		}

		// Build response based on API status
		$status = isset( $decoded_response['status'] ) ? $decoded_response['status'] : 'unknown';
		$success = isset( $decoded_response['success'] ) ? $decoded_response['success'] : true;
		$message = isset( $decoded_response['message'] ) ? $decoded_response['message'] : '';

		$result = array(
			'success' => $success,
			'status' => $status,
			'message' => $message,
		);

		// Include report ID if present
		if ( isset( $decoded_response['ai_diagnostic_report_id'] ) ) {
			$result['ai_diagnostic_report_id'] = $decoded_response['ai_diagnostic_report_id'];
		}

		// Include queue position if pending
		if ( isset( $decoded_response['queue_position'] ) ) {
			$result['queue_position'] = $decoded_response['queue_position'];
		}

		// Include diagnostic result if completed
		if ( $status === 'completed' && isset( $decoded_response['diagnostic_result'] ) ) {
			$result['diagnostic_result'] = $decoded_response['diagnostic_result'];
		}

		// Include error info if failed
		if ( $status === 'failed' ) {
			if ( isset( $decoded_response['error_message'] ) ) {
				$result['error_message'] = $decoded_response['error_message'];
			}
			if ( isset( $decoded_response['can_retry'] ) ) {
				$result['can_retry'] = $decoded_response['can_retry'];
			}
		}

		return $result;
	}
}


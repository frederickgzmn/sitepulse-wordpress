<?php
/**
 * Easy Mode — the default SitePulse dashboard experience.
 *
 * Controls template routing, user preference storage, and asset loading
 * for the modern UI layer. The classic dashboard remains available as the
 * "Advanced view". All data-gathering logic is reused from Sitepulse_Backend;
 * this class only handles presentation.
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sitepulse_Easy_Mode {

	/** User meta key that stores the preference (boolean). */
	const META_KEY = 'sitepulse_easy_mode';

	/** User meta key for color scheme (dark / light). */
	const THEME_META_KEY = 'sitepulse_easy_mode_theme';

	/** User meta key for the classic dashboard view (basic / developer). */
	const DASHBOARD_VIEW_META_KEY = 'sitepulse_dashboard_view';

	/** Base path for Easy Mode templates. */
	private static $template_base = '';

	/**
	 * Boot the Easy Mode system.
	 * Called from loader.php after all classes are available.
	 */
	public static function init() {
		self::$template_base = SITEPULSE_PATH . 'templates/easy/';

		// AJAX handlers for toggling mode and theme
		add_action( 'wp_ajax_sitepulse_toggle_easy_mode', array( __CLASS__, 'ajax_toggle_mode' ) );
		add_action( 'wp_ajax_sitepulse_toggle_theme', array( __CLASS__, 'ajax_toggle_theme' ) );
		add_action( 'wp_ajax_sitepulse_clear_error_log', array( __CLASS__, 'ajax_clear_error_log' ) );
		add_action( 'wp_ajax_sitepulse_enable_savequeries', array( __CLASS__, 'ajax_enable_savequeries' ) );
	}

	/**
	 * Check whether the current user uses Easy Mode.
	 *
	 * Easy Mode is the default; only an explicit choice of the advanced
	 * (classic) view turns it off.
	 *
	 * @return bool
	 */
	public static function is_enabled(): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user_id = get_current_user_id();
		$value   = get_user_meta( $user_id, self::META_KEY, true );

		if ( '' === $value && ! metadata_exists( 'user', $user_id, self::META_KEY ) ) {
			return true;
		}

		return (bool) $value;
	}

	/**
	 * Store the current user's choice between Easy Mode and the advanced view.
	 *
	 * @param bool $enabled True for Easy Mode
	 */
	public static function set_enabled( bool $enabled ) {
		update_user_meta( get_current_user_id(), self::META_KEY, $enabled ? '1' : '0' );
	}

	/**
	 * The classic dashboard view the current user prefers.
	 *
	 * @return string "basic" or "developer"
	 */
	public static function get_dashboard_view(): string {
		$view = get_user_meta( get_current_user_id(), self::DASHBOARD_VIEW_META_KEY, true );

		return 'basic' === $view ? 'basic' : 'developer';
	}

	/**
	 * Store the classic dashboard view the current user prefers.
	 *
	 * @param string $view "basic" or "developer"
	 * @return bool False for an unknown view
	 */
	public static function set_dashboard_view( string $view ): bool {
		if ( ! in_array( $view, array( 'basic', 'developer' ), true ) ) {
			return false;
		}

		update_user_meta( get_current_user_id(), self::DASHBOARD_VIEW_META_KEY, $view );
		return true;
	}

	/**
	 * Get the current user's preferred color scheme.
	 *
	 * @return string "dark" or "light"
	 */
	public static function get_theme(): string {
		if ( ! is_user_logged_in() ) {
			return 'dark';
		}
		$theme = get_user_meta( get_current_user_id(), self::THEME_META_KEY, true );
		return in_array( $theme, array( 'dark', 'light' ), true ) ? $theme : 'dark';
	}

	/**
	 * AJAX: toggle Easy Mode on or off for the current user.
	 */
	public static function ajax_toggle_mode() {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$new_val = ! self::is_enabled();
		self::set_enabled( $new_val );

		wp_send_json_success( array(
			'enabled' => $new_val,
			'message' => $new_val
				? __( 'Simple view enabled. Refreshing…', 'sitepulse' )
				: __( 'Advanced view enabled. Refreshing…', 'sitepulse' ),
		) );
	}

	/**
	 * AJAX: switch between dark and light themes.
	 */
	public static function ajax_toggle_theme() {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$requested = isset( $_POST['theme'] ) ? sanitize_text_field( wp_unslash( $_POST['theme'] ) ) : '';
		if ( ! in_array( $requested, array( 'dark', 'light' ), true ) ) {
			$requested = 'dark';
		}

		update_user_meta( get_current_user_id(), self::THEME_META_KEY, $requested );

		wp_send_json_success( array(
			'theme'   => $requested,
			'message' => sprintf(
				/* translators: %s: theme name */
				__( 'Switched to %s mode.', 'sitepulse' ),
				ucfirst( $requested )
			),
		) );
	}

	/**
	 * Enqueue Easy Mode assets (CSS + JS).
	 * Called from Sitepulse_Backend::enqueue_styles_and_scripts() when Easy Mode is active.
	 */
	public static function enqueue_assets() {
		$css_path = SITEPULSE_ADMIN_ASSETS_CSS_PATH . 'easy-mode.css';
		$js_path  = SITEPULSE_ADMIN_ASSETS_JS_PATH . 'easy-mode.js';

		// Google Fonts: Inter
		wp_enqueue_style(
			'sitepulse-font-inter',
			'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
			array(),
			null
		);

		wp_enqueue_style(
			'sitepulse-easy-mode-style',
			SITEPULSE_ADMIN_ASSETS_CSS_URL . 'easy-mode.css',
			array( 'dashicons' ),
			file_exists( $css_path ) ? filemtime( $css_path ) : SITEPULSE_VERSION,
			'all'
		);

		wp_enqueue_script(
			'sitepulse-easy-mode-script',
			SITEPULSE_ADMIN_ASSETS_JS_URL . 'easy-mode.js',
			array( 'jquery' ),
			file_exists( $js_path ) ? filemtime( $js_path ) : SITEPULSE_VERSION,
			true
		);

		wp_localize_script( 'sitepulse-easy-mode-script', 'SitePulseEasy', array(
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
			'rest_url'  => esc_url_raw( rest_url() ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'theme'     => self::get_theme(),
			'i18n'      => array(
				'confirm_clear'    => __( 'Clear all event and load data?', 'sitepulse' ),
				'confirm_cron'     => __( 'Are you sure you want to disable WordPress cron?', 'sitepulse' ),
				'confirm_email'    => __( 'Are you sure you want to block all outgoing emails?', 'sitepulse' ),
				'confirm_clear_errors' => __( 'Clear all PHP error logs?', 'sitepulse' ),
				'switching'        => __( 'Switching…', 'sitepulse' ),
				'saved'            => __( 'Saved!', 'sitepulse' ),
				'clearing'         => __( 'Clearing…', 'sitepulse' ),
				'installing'       => __( 'Installing…', 'sitepulse' ),
				'installed'        => __( 'Installed!', 'sitepulse' ),
				'error'            => __( 'An error occurred.', 'sitepulse' ),
				'working'          => __( 'Working…', 'sitepulse' ),
			),
		) );

		// Backend.js for interactive features (diagnostics, comparisons, profiler, etc.)
		$backend_js_path = SITEPULSE_ADMIN_ASSETS_JS_PATH . 'backend.js';
		wp_enqueue_script(
			'sitepulse-backend-script',
			SITEPULSE_ADMIN_ASSETS_JS_URL . 'backend.js',
			array( 'jquery' ),
			file_exists( $backend_js_path ) ? filemtime( $backend_js_path ) : SITEPULSE_VERSION,
			true
		);

		wp_localize_script( 'sitepulse-backend-script', 'SitePulse', array(
			'rest_url'                        => esc_url_raw( rest_url() ),
			'nonce'                           => wp_create_nonce( 'wp_rest' ),
			'cron_confirm_message'            => esc_js( __( 'Are you sure you want to disable WordPress cron? This will stop all scheduled tasks including updates, backups, and other automated processes.', 'sitepulse' ) ),
			'email_blocking_confirm_message'  => esc_js( __( 'Are you sure you want to block all outgoing emails? This will prevent password resets, notifications, and other important emails from being sent.', 'sitepulse' ) ),
		) );

		// Bootstrap 5
		wp_enqueue_style( 'sitepulse_bootstrap_css', SITEPULSE_ADMIN_ASSETS_URL . 'externals/bootstrap-5.3.8-dist/css/bootstrap.min.css', array(), '5.3.8', 'all' );
		wp_enqueue_script( 'sitepulse_bootstrap_js', SITEPULSE_ADMIN_ASSETS_URL . 'externals/bootstrap-5.3.8-dist/js/bootstrap.min.js', array(), '5.3.8', true );
	}

	/**
	 * Render the Easy Mode layout for a given view.
	 *
	 * @param string $view     The view slug (home, performance, security, insights, api-monitor, system, settings).
	 * @param array  $data     Template variables to extract into scope.
	 */
	public static function render( string $view, array $data = array() ) {
		$view_file = self::$template_base . 'views/' . $view . '.php';
		if ( ! file_exists( $view_file ) ) {
			$view_file = self::$template_base . 'views/home.php';
			$view      = 'home';
		}

		Sitepulse_Getting_Started::mark_visited( $view );

		// Make data available to templates
		$easy_view  = $view;
		$easy_theme = self::get_theme();

		// Explicitly assign known template variables from $data instead of extract().
		// This prevents arbitrary variable injection while keeping templates working.
		$sp_allowed_vars = array(
			'stats', 'curl_events', 'mem', 'overall_score', 'speed_score',
			'resource_score', 'error_log_score', 'total_load_time',
			'active_plugins_count', 'error_count', 'ai_diagnostic_report',
			'has_ai_report', 'all_slow_items', 'slow_api_requests', 'slow_queries',
			'show_performance_warnings', 'has_valid_pagespeed',
			'ps_performance_score', 'ps_data', 'ps_strategy',
			'snapshot_time', 'sitepulse_lowhttp_enabled',
			'sitepulse_profiler_enabled', 'weekly_trends',
			'vulnerabilities', 'error_log_stats',
			'sp_ai_external_api_enabled', 'ai_diagnostic_status',
			'ai_diagnostic_requested_at', 'ai_diagnostic_queue_position',
			'is_ai_pending', 'is_ai_failed', 'plugins_score', 'memory_score',
			'plugin_profiler_stats', 'total_time', 'autoload_metrics',
			'sp_settings_saved', 'sitepulse_settings', 'current_settings',
			'status_info', 'active_plugins',
			'loadsentinel_score', 'api_score', 'load_time_score',
			'system_php_version',
			'sql_queries', 'sql_statistics', 'sql_recent',
			'single_curl_events', 'single_load_events',
			'history', 'error_log', 'fatal_count', 'warning_count',
			'last_vulnerability_check', 'mem_usage_percent',
			'plugin_percent', 'disk_write',
			'analysis_suggestions', 'analysis_recent', 'analysis_request',
			'getting_started_steps', 'show_getting_started',
		);
		foreach ( $sp_allowed_vars as $sp_var_name ) {
			if ( array_key_exists( $sp_var_name, $data ) ) {
				$$sp_var_name = $data[ $sp_var_name ];
			}
		}
		unset( $sp_allowed_vars, $sp_var_name );

		require_once self::$template_base . 'layout.php';
	}

	/**
	 * Get navigation items for the sidebar.
	 *
	 * @return array
	 */
	public static function get_nav_items(): array {
		$base_url = admin_url( 'admin.php?page=wpsp_sitepulse' );

		$items = array(
			array(
				'slug'  => 'home',
				'label' => __( 'Dashboard', 'sitepulse' ),
				'icon'  => 'dashicons-dashboard',
				'url'   => $base_url,
			),
			array(
				'slug'  => 'page-analysis',
				'label' => __( 'Page Analysis', 'sitepulse' ),
				'icon'  => 'dashicons-search',
				'url'   => Sitepulse_Page_Analysis::admin_link(),
			),
			array(
				'slug'  => 'performance',
				'label' => __( 'Performance', 'sitepulse' ),
				'icon'  => 'dashicons-performance',
				'url'   => $base_url . '&sp_view=performance',
			),
			array(
				'slug'  => 'resource-load',
				'label' => __( 'Plugin Activity', 'sitepulse' ),
				'icon'  => 'dashicons-admin-plugins',
				'url'   => $base_url . '&sp_view=resource-load',
			),
			array(
				'slug'  => 'api-monitor',
				'label' => __( 'External Requests', 'sitepulse' ),
				'icon'  => 'dashicons-rest-api',
				'url'   => $base_url . '&sp_view=api-monitor',
			),
			array(
				'slug'  => 'insights',
				'label' => __( 'PageSpeed', 'sitepulse' ),
				'icon'  => 'dashicons-chart-area',
				'url'   => $base_url . '&sp_view=insights',
			),
			array(
				'slug'  => 'security',
				'label' => __( 'Security', 'sitepulse' ),
				'icon'  => 'dashicons-shield',
				'url'   => $base_url . '&sp_view=security',
			),
			array(
				'slug'  => 'system',
				'label' => __( 'Site Info', 'sitepulse' ),
				'icon'  => 'dashicons-info-outline',
				'url'   => $base_url . '&sp_view=system',
			),
			array(
				'slug'  => 'settings',
				'label' => __( 'Settings', 'sitepulse' ),
				'icon'  => 'dashicons-admin-generic',
				'url'   => $base_url . '&sp_view=settings',
			),
		);

		return $items;
	}

	/**
	 * Determine the current Easy Mode view based on admin page and query parameters.
	 *
	 * @return string The view slug.
	 */
	public static function get_current_view(): string {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return 'home';
		}

		$page    = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		$sp_view = isset( $_GET['sp_view'] ) ? sanitize_text_field( wp_unslash( $_GET['sp_view'] ) ) : '';

		// Dashboard sub-views via query param (Primary Routing)
		if ( in_array( $sp_view, array( 'performance', 'security', 'system', 'resource-load', 'insights', 'api-monitor', 'settings' ), true ) ) {
			return $sp_view;
		}

		// Legacy admin page mapping fallback
		if ( strpos( $page, 'page_analysis' ) !== false ) {
			return 'page-analysis';
		}
		if ( strpos( $page, 'resource_load' ) !== false ) {
			return 'resource-load';
		}
		if ( strpos( $page, 'curl_api' ) !== false ) {
			return 'api-monitor';
		}
		if ( strpos( $page, '_settings' ) !== false ) {
			return 'settings';
		}

		return 'home';
	}

	/**
	 * Clear Error Log via AJAX
	 */
	public static function ajax_clear_error_log() {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'sitepulse' ) ) );
		}

		if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
			Sitepulse_Error_Handler::clear_error_log();
			wp_send_json_success();
		}

		wp_send_json_error( array( 'message' => __( 'Error handler not found', 'sitepulse' ) ) );
	}

	/**
	 * AJAX: Enable SAVEQUERIES in wp-config.php
	 */
	public static function ajax_enable_savequeries() {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'sitepulse' ) ) );
		}

		// Find the main plugin instance
		if ( ! class_exists( 'Sitepulse_Plugin' ) ) {
			wp_send_json_error( array( 'message' => __( 'Core plugin not found', 'sitepulse' ) ) );
		}

		// Initialize file system
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		$plugin = new Sitepulse_Plugin();
		$result = $plugin->sp_set_savequeries( true );

		if ( ! empty( $result['success'] ) ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( array( 'message' => isset( $result['message'] ) ? $result['message'] : __( 'Unable to update wp-config.php.', 'sitepulse' ) ) );
		}
	}
}

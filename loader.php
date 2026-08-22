<?php
/**
 * Plugin Name:       SitePulse - See What’s Powering (or Slowing) Your Site
 * Description:       SitePulse gives you real-time insights into your WordPress site’s performance, slow queries, and bottlenecks - so you can keep your site fast, healthy, and optimized.
 * Version:           1.4.1
 * Author:            Frederic Guzman
 * Author URI:        https://www.nilbug.com
 * Text Domain:       sitepulse
 * Domain Path:       /languages
 * License:           GPL-2.0+
 *
 */

// Prevent to access the file from outside of WordPress
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// SitePulse defines. He.
define( 'SITEPULSE_PLUGIN_FILE', __FILE__ );
if ( ! defined( 'SITEPULSE_DEBUG' ) ) {
	define( 'SITEPULSE_DEBUG', false );
}

if ( ! defined( 'SITEPULSE_STRESS_MODE' ) ) {
	define( 'SITEPULSE_STRESS_MODE', false );
}

define( 'SITEPULSE_VERSION',               '1.4.1' );
define( 'SITEPULSE_NAME',                  'SitePulse' );
define( 'SITEPULSE_SLUG',                  'sitepulse' );
define( 'SITEPULSE_PREFIX',                'wpsp' );
define( 'SITEPULSE_PREFIX_SEPARATOR',		 '_' );
define( 'SITEPULSE_PATH',                  plugin_dir_path( __FILE__ ) . '/' );
define( 'SITEPULSE_TEXT_DOMAIN_PATH',      SITEPULSE_PATH . 'languages/' . '/' );
define( 'SITEPULSE_INC_PATH',              SITEPULSE_PATH . 'inc/' );
define( 'SITEPULSE_FRONT_PATH',            SITEPULSE_PATH . 'front/' );
define( 'SITEPULSE_ADMIN_PATH',            SITEPULSE_PATH . 'admin/' );
define( 'SITEPULSE_ADDONS_PATH',           SITEPULSE_PATH . 'addons/' );
define( 'SITEPULSE_CLASS_PATH',            SITEPULSE_PATH . 'class/' );
define( 'SITEPULSE_LOGS_PATH',             wp_upload_dir()['basedir'] . '/sitepulse/sitepulse-logs' );
define( 'SITEPULSE_WRITE_TEST_PATH',       wp_upload_dir()['basedir'] );
define( 'SITEPULSE_VENDORS_PATH',          SITEPULSE_PATH . 'inc/vendors' );
define( 'SITEPULSE_URL',        		   plugin_dir_url( __FILE__ ) );
define( 'SITEPULSE_INC_URL',               SITEPULSE_URL . 'inc/' );
define( 'SITEPULSE_ADMIN_URL',             SITEPULSE_INC_URL . 'admin/' );
define( 'SITEPULSE_ADMIN_ASSETS_URL',      SITEPULSE_URL . 'assets/' );
define( 'SITEPULSE_ADMIN_ASSETS_PATH',     SITEPULSE_PATH . 'assets/' );
define( 'SITEPULSE_ADMIN_ASSETS_JS_URL',   SITEPULSE_ADMIN_ASSETS_URL . 'js/' );
define( 'SITEPULSE_ADMIN_ASSETS_JS_PATH',  SITEPULSE_ADMIN_ASSETS_PATH . 'js/' );
define( 'SITEPULSE_ADMIN_ASSETS_CSS_URL',  SITEPULSE_ADMIN_ASSETS_URL . 'css/' );
define( 'SITEPULSE_ADMIN_ASSETS_CSS_PATH', SITEPULSE_ADMIN_ASSETS_PATH . 'css/' );
define( 'SITEPULSE_ADMIN_ASSETS_IMG_URL',  SITEPULSE_ADMIN_ASSETS_URL . 'img/' );

// Addons Constants
// Profiler constants
define( 'SITEPULSE_PROFILER_KEY',           	 'sitepulse_profiler_stats' );
define( 'SITEPULSE_PROFILER_ENABLED',          'sitepulse_profiler_enabled' );
define( 'SITEPULSE_PROFILER_CURRENT_TIME',     'sitepulse_profiler_current_time' );
define( 'SITEPULSE_PROFILER_SLUG',     		 'resource_load' );

// CURL/API constants
define( 'SITEPULSE_CURL_API_KEY',               'sitepulse_curl_api_events' );
define( 'SITEPULSE_CURL_API_ENABLED',       	  'sitepulse_curl_api_enabled' );
define( 'SITEPULSE_CURL_API_SLUG',          	  'curl_api' );
define( 'SITEPULSE_CURL_API_DEFAULT_THRESHOLD', 1 );

// Real time tracking constants
define( 'SITEPULSE_REALTIME_TRACKING_TIME', 30 ); // In seconds

// Load error handler FIRST to catch any errors during plugin loading
require_once SITEPULSE_CLASS_PATH . 'error_handler.php';

// Initialize error handler immediately after loading (before other plugins)
if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
	Sitepulse_Error_Handler::init();
}

// Load utility classes (shared helpers)
require_once SITEPULSE_CLASS_PATH . 'utils.php';
require_once SITEPULSE_CLASS_PATH . 'page_tracker.php';
require_once SITEPULSE_CLASS_PATH . 'product_analytics.php';

// Load profiler class
require_once SITEPULSE_CLASS_PATH . 'profiler.php';
require_once SITEPULSE_CLASS_PATH . 'curloader.php';
require_once SITEPULSE_CLASS_PATH . 'plugin_profiler.php';
require_once SITEPULSE_PATH . 'inc/api_backend.php';
require_once SITEPULSE_CLASS_PATH . '/setup.php';
require_once SITEPULSE_CLASS_PATH . '/plugin.php';
require_once SITEPULSE_CLASS_PATH . 'onboarding.php';
require_once SITEPULSE_CLASS_PATH . 'whitelabel_service.php';

// Constant for pro detection - set early for performance
// This is checked by core to conditionally load/skip widgets when pro is active
if ( ! defined( 'SITEPULSE_PRO_IS_ACTIVE' ) ) {
	define( 'SITEPULSE_PRO_IS_ACTIVE', Sitepulse_Setup::is_pro_active() );
}


// Load Easy Mode only for the free runtime (avoid class conflicts with pro plugin).
if ( ! SITEPULSE_PRO_IS_ACTIVE ) {
	require_once SITEPULSE_CLASS_PATH . 'easy_mode.php';
}

// Only load api_service, cron_manager, and cron_fallback when pro is not active.
// Pro version has its own implementation of these components.
if ( ! SITEPULSE_PRO_IS_ACTIVE ) {
	require_once SITEPULSE_CLASS_PATH . 'api_service.php';
	require_once SITEPULSE_PATH . 'inc/cron_manager.php';
	// Cron fallback runner — catches stale cron tasks and executes them non-blocking.
	require_once SITEPULSE_PATH . 'inc/cron_fallback.php';
}

// Load AI diagnostic cron manager (works with both free and pro)
require_once SITEPULSE_PATH . 'inc/ai_diagnostic_cron.php';

class Sitepulse_Loader {
	// Constructor
	public function __construct() {

		// Nothing to do if autosave.
		if ( defined( 'DOING_AUTOSAVE' ) ) {
			return;
		}

		$profiler = new Sitepulse_Profiler();

		if ( class_exists('Sitepulse_Profiler') ) {
			$profiler::init();
		}

		$curLoader = new Sitepulse_CurLoader();
		if ( class_exists('Sitepulse_CurLoader') ) {
			$curLoader::init();
		}

		$plugin_profiler = new Sitepulse_Plugin_Profiler();
		if ( class_exists('Sitepulse_Plugin_Profiler') ) {
			$plugin_profiler::init();
		}

		// Error handler is already initialized at the top of loader.php (before this constructor)

		add_action( 'init', [ $this, 'load_plugin_textdomain' ] );
		self::init();
	}

	/**
	 * Initialize the plugin
	 */
	private function init() {
		// Allow pro version or other extensions to initialize before core plugin
		do_action( 'sitepulse_before_init' );
		
		new Sitepulse_Plugin();

		// Initialize onboarding
		Sitepulse_Onboarding::getInstance();

		// Initialize Easy Mode (toggleable dashboard experience)
		if ( class_exists( 'Sitepulse_Easy_Mode' ) ) {
			Sitepulse_Easy_Mode::init();
		}

		// Error handler is already initialized in constructor (moved earlier for better error catching)

		register_activation_hook(SITEPULSE_PLUGIN_FILE, [ $this, 'activate']);
		register_deactivation_hook(SITEPULSE_PLUGIN_FILE, [ $this, 'deactivate']);
		// Admin dismissable notice when trackers are disabled
		add_action( 'admin_notices', [ $this, 'admin_notice_trackers_disabled' ] );
		// Admin notice for onboarding
		add_action( 'admin_notices', [ $this, 'admin_notice_onboarding' ] );
		
		// Allow pro version or other extensions to hook after initialization
		do_action( 'sitepulse_after_init' );
	}

	/**
	 * Initialize internationalization (i18n) for this plugin.
	 * For testing change your WordPress language to the translated one
	 * in the WordPress Admin, under Settings/General/Site Language
	 *
	 * Resources:
	 * http://codex.wordpress.org/I18n_for_WordPress_Developers
	 */
	function load_plugin_textdomain() {
		$domain			= 'sitepulse';
		$plugin_folder	= SITEPULSE_URL;
		$lang_folder	= $plugin_folder . '/' . SITEPULSE_TEXT_DOMAIN_PATH;
		$mo_file 		= $lang_folder . '/' . SITEPULSE_SLUG . '-' . get_locale() . '.mo';

		load_textdomain('sitepulse', $mo_file);
		// Deprecated.
		// load_plugin_textdomain('sitepulse', false, SITEPULSE_TEXT_DOMAIN_PATH . '/'); 
	}

	/**
	 * Deactivates the plugin and cleans up scheduled cron jobs.
	 */
	public function deactivate() {
		// Unschedule the daily API request cron job
		if ( class_exists( 'Sitepulse_Cron_Manager' ) ) {
			Sitepulse_Cron_Manager::unschedule_daily_api_request();
		}

		// Clean up fallback cron hooks (legacy and current).
		$fallback_hooks = array(
			'sitepulse_fallback_cron_check',
			'sitepulse_cron_fallback_immediate',
		);
		foreach ( $fallback_hooks as $hook ) {
			$ts = wp_next_scheduled( $hook );
			if ( $ts ) {
				wp_unschedule_event( $ts, $hook );
			}
		}

		// Unschedule AI diagnostic cron job
		if ( class_exists( 'Sitepulse_AI_Diagnostic_Cron' ) ) {
			Sitepulse_AI_Diagnostic_Cron::unschedule();
		}
	}

	/**
	 * Activates the plugin enable the trackers.
	 */
	public function activate() {
		update_option( SITEPULSE_PROFILER_ENABLED, true );
		update_option( SITEPULSE_CURL_API_ENABLED, true );
		update_option( 'sitepulse_plugins_profiler_enabled', true );
		Sitepulse_Product_Analytics::track_once( 'plugin_activated' );
		
		// Set transient to trigger onboarding redirect
		set_transient( 'sitepulse_activation_redirect', true, 30 );

		// Schedule the daily API request cron job
		if ( class_exists( 'Sitepulse_Cron_Manager' ) ) {
			Sitepulse_Cron_Manager::schedule_daily_api_request();
		}
	}

	// Admin dismissable notice when trackers are disabled
	public function admin_notice_trackers_disabled() {
		$profiler_enabled = ( get_option( SITEPULSE_PROFILER_ENABLED ) == '1' );
		$curl_enabled     = ( get_option( SITEPULSE_CURL_API_ENABLED ) == '1' );

		if ( $profiler_enabled && $curl_enabled ) {
			return;
		}

		// check if notice was dismissed
		$dismissed = get_user_meta( get_current_user_id(), 'sitepulse_trackers_disabled_notice_dismissed', true );
		if ( $dismissed ) {
			return;
		}

		$class = 'notice notice-warning is-dismissible';
		if ( ! $profiler_enabled && ! $curl_enabled ) {
			$message = esc_html__( 'SitePulse monitoring is paused. Both the Performance Monitor and External Requests tracker are disabled. Please enable them in the SitePulse dashboard to start tracking performance.', 'sitepulse' );
		} elseif ( ! $profiler_enabled ) {
			$message = esc_html__( 'SitePulse Performance Monitor is disabled. Please enable it in the SitePulse dashboard to start tracking performance.', 'sitepulse' );
		} else {
			$message = esc_html__( 'SitePulse External Requests tracker is disabled. Please enable it in the SitePulse dashboard to start tracking external connections.', 'sitepulse' );
		}

		printf( '<div id="sitepulse-trackers-disabled-notice" class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
	}

	/**
	 * Admin notice to run onboarding wizard
	 */
	public function admin_notice_onboarding() {
		// Only show on SitePulse pages
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'sitepulse' ) === false ) {
			return;
		}

		$onboarding = Sitepulse_Onboarding::getInstance();
		
		// Don't show if completed
		if ( $onboarding->is_onboarding_completed() ) {
			return;
		}

		// Only show if dismissed
		if ( ! $onboarding->is_onboarding_dismissed() ) {
			return;
		}

		// check if notice was dismissed
		$dismissed = get_user_meta( get_current_user_id(), 'sitepulse_onboarding_notice_dismissed', true );
		if ( $dismissed ) {
			return;
		}

		$class = 'notice notice-info is-dismissible';
		$onboarding_url = admin_url( 'admin.php?page=wpsp_sitepulse_onboarding' );
		$message = sprintf(
			/* translators: %s: URL to onboarding page */
			esc_html__( 'Want to get started with SitePulse? Run the %s to configure the plugin.', 'sitepulse' ),
			'<a href="' . esc_url( $onboarding_url ) . '">' . esc_html__( 'setup wizard', 'sitepulse' ) . '</a>'
		);

		printf( '<div id="sitepulse-onboarding-notice" class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) ); 
	}
}

$sitepulse_loader = new Sitepulse_Loader();

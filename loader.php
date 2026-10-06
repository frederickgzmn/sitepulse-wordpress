<?php
/**
 * Plugin Name:       SitePulse - Performance Monitor and AI Diagnostics
 * Description:       SitePulse gives you real-time insights into your WordPress site’s performance, slow queries, and bottlenecks - so you can keep your site fast, healthy, and optimized.
 * Version:           1.4.5
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

// Prevent to load this plugin if Pro version is active
if ( defined( 'SITEPULSE_PRO_IS_ACTIVE' ) && true === SITEPULSE_PRO_IS_ACTIVE ) {
	// admin notice if SitePulse Pro is already active
	add_action( 'admin_notices', function() {
		?>
		<div class="notice notice-error">
			<p><?php printf( esc_html__( 'SitePulse Pro is already active. SitePulse Free need to be deactivated.', 'sitepulse' ) ); ?></p>
		</div>
		<?php
	} );

	return;
}

// SitePulse defines. He.
define( 'SITEPULSE_PLUGIN_FILE', __FILE__ );
if ( ! defined( 'SITEPULSE_DEBUG' ) ) {
	define( 'SITEPULSE_DEBUG', false );
}

if ( ! defined( 'SITEPULSE_STRESS_MODE' ) ) {
	define( 'SITEPULSE_STRESS_MODE', false );
}

define( 'SITEPULSE_VERSION',               '1.4.5' );
define( 'SITEPULSE_NAME',                  'SitePulse' );
define( 'SITEPULSE_SLUG',                  'sitepulse' );
define( 'SITEPULSE_PREFIX',                'wpsp' );
define( 'SITEPULSE_PREFIX_SEPARATOR',		 '_' );
define( 'SITEPULSE_PATH',                  plugin_dir_path( __FILE__ ) . '/' );
define( 'SITEPULSE_TEXT_DOMAIN_PATH',      SITEPULSE_PATH . 'languages/' . '/' );
define( 'SITEPULSE_CLASS_PATH',            SITEPULSE_PATH . 'class/' );
define( 'SITEPULSE_URL',        		   plugin_dir_url( __FILE__ ) );
define( 'SITEPULSE_ADMIN_ASSETS_URL',      SITEPULSE_URL . 'assets/' );
define( 'SITEPULSE_ADMIN_ASSETS_PATH',     SITEPULSE_PATH . 'assets/' );
define( 'SITEPULSE_ADMIN_ASSETS_JS_URL',   SITEPULSE_ADMIN_ASSETS_URL . 'js/' );
define( 'SITEPULSE_ADMIN_ASSETS_JS_PATH',  SITEPULSE_ADMIN_ASSETS_PATH . 'js/' );
define( 'SITEPULSE_ADMIN_ASSETS_CSS_URL',  SITEPULSE_ADMIN_ASSETS_URL . 'css/' );
define( 'SITEPULSE_ADMIN_ASSETS_CSS_PATH', SITEPULSE_ADMIN_ASSETS_PATH . 'css/' );
define( 'SITEPULSE_ADMIN_ASSETS_IMG_URL',  SITEPULSE_ADMIN_ASSETS_URL . 'img/' );

// Plugin settings
define( 'SITEPULSE_SETTINGS_PROFILER_INACTIVE_AFTER_SECONDS',  60 ); // 60 seconds


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

// Move SitePulse to first position in the active plugins list
function sitepulse_move_to_first_position(): void {
	if ( sitepulse_is_first_plugin() ) {
		return;
	}

	$sitepulse = plugin_basename( SITEPULSE_PLUGIN_FILE );
	$plugins   = (array) get_option( 'active_plugins', array() );
	$position  = array_search( $sitepulse, $plugins, true );

	if ( false === $position || 0 === $position ) {
		return;
	}
	unset( $plugins[ $position] );
	$plugins = array_values( $plugins );
	array_unshift( $plugins, $sitepulse );

	update_option( 'active_plugins', $plugins );
}

/**
 * Detect another plugin displacing SitePulse from position zero.
 *
 * @return string|null Plugin currently occupying position zero, or null.
 */
function sitepulse_detect_first_position_conflict(): ?string {
    $sitepulse = plugin_basename( SITEPULSE_PLUGIN_FILE );
    $plugins   = array_values(
        (array) get_option( 'active_plugins', array() )
    );

    $position = array_search( $sitepulse, $plugins, true );

    if ( false === $position || 0 === $position ) {
        return null;
    }

    return $plugins[0] ?? null;
}

// Conflict detector between SitePulse and other plugins
define( 'SITEPULSE_CONFLICTING_PLUGIN', sitepulse_detect_first_position_conflict() );

/**
 * Move SitePulse to the first position in an active-plugins value.
 *
 * This is intentionally in-memory only; the active_plugins option is never
 * updated from this callback.
 *
 * @param mixed $plugins Active plugin basenames.
 * @return mixed
 */
function sitepulse_reorder_active_plugins_for_conflict( $pre_option ) {
	$plugins = $pre_option;

	if ( ! is_array( $plugins ) ) {
		$all_options = wp_load_alloptions();
		$plugins     = $all_options['active_plugins'] ?? $pre_option;
	}

	if ( ! is_array( $plugins ) ) {
		return $pre_option;
	}

	$sitepulse = plugin_basename( SITEPULSE_PLUGIN_FILE );
	$position  = array_search( $sitepulse, $plugins, true );

	if ( false !== $position && 0 !== $position ) {
		unset( $plugins[ $position ] );
		array_unshift( $plugins, $sitepulse );
	}

	return array_values( $plugins );
}

if ( null !== SITEPULSE_CONFLICTING_PLUGIN ) {
	add_filter( 'pre_option_active_plugins', 'sitepulse_reorder_active_plugins_for_conflict' );
}

// Check if SitePulse is the first plugin in the active plugins list
function sitepulse_is_first_plugin(): bool {
    $sitepulse = plugin_basename( SITEPULSE_PLUGIN_FILE );
    $plugins   = (array) get_option( 'active_plugins', array() );

    return isset( $plugins[0] ) && $plugins[0] === $sitepulse;
}

// Runs after WordPress has saved the activation list.
add_action( 'activated_plugin', 'sitepulse_move_to_first_position', PHP_INT_MAX );

// Initialize error handler immediately after loading (before other plugins)
if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
	Sitepulse_Error_Handler::init();
}

// Load utility classes (shared helpers)
require_once SITEPULSE_CLASS_PATH . 'utils.php';
require_once SITEPULSE_CLASS_PATH . 'page_tracker.php';

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

require_once SITEPULSE_CLASS_PATH . 'easy_mode.php';

// Only load api_service, cron_manager, and cron_fallback when pro is not active.
require_once SITEPULSE_CLASS_PATH . 'api_service.php';
require_once SITEPULSE_PATH . 'inc/cron_manager.php';
// Cron fallback runner — catches stale cron tasks and executes them non-blocking.
require_once SITEPULSE_PATH . 'inc/cron_fallback.php';

// Load AI diagnostic cron manager (works with both free and pro)
require_once SITEPULSE_PATH . 'inc/ai_diagnostic_cron.php';

class Sitepulse_Loader {
	// Constructor
	public function __construct() {

		// Nothing to do if autosave.
		if ( defined( 'DOING_AUTOSAVE' ) ) {
			return;
		}

		if ( class_exists( 'Sitepulse_Profiler' ) ) {
			Sitepulse_Profiler::init();
		}

		if ( class_exists( 'Sitepulse_CurLoader' ) ) {
			Sitepulse_CurLoader::init();
		}

		if ( class_exists( 'Sitepulse_Plugin_Profiler' ) ) {
			Sitepulse_Plugin_Profiler::init();
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
		$mo_file = rtrim( SITEPULSE_TEXT_DOMAIN_PATH, '/' ) . '/' . SITEPULSE_SLUG . '-' . get_locale() . '.mo';
		load_textdomain( 'sitepulse', $mo_file );
		// Deprecated.
		// load_plugin_textdomain('sitepulse', false, SITEPULSE_TEXT_DOMAIN_PATH . '/'); 
	}

	/**
	 * Deactivates the plugin and cleans up scheduled cron jobs.
	 */
	public function deactivate() {
		// Unschedule site status reports
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
		
		// Set transient to trigger onboarding redirect
		set_transient( 'sitepulse_activation_redirect', true, 30 );

		// Schedule site status reports every six hours
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

<?php
/**
 * SitePulse Onboarding Class
 * Handles the first-time user experience after plugin installation
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once SITEPULSE_CLASS_PATH . 'setup.php';

class Sitepulse_Onboarding extends Sitepulse_Setup {
	
	const ONBOARDING_COMPLETED_KEY = 'sitepulse_onboarding_completed';
	const ONBOARDING_DISMISSED_KEY = 'sitepulse_onboarding_dismissed';
	const ONBOARDING_STEP_KEY = 'sitepulse_onboarding_current_step';
	
	private static $instance = null;
	
	/**
	 * Get singleton instance
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor - Register hooks
	 */
	public function __construct() {
		// Check if we should show onboarding
		add_action( 'admin_init', [ $this, 'maybe_redirect_to_onboarding' ] );
		add_action( 'admin_menu', [ $this, 'register_onboarding_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_onboarding_assets' ] );
		add_filter( 'admin_body_class', [ $this, 'add_onboarding_body_class' ] );
		add_filter( 'admin_title', [ $this, 'set_onboarding_title' ], 10, 2 );
		// Set title early, before admin-header.php is included
		add_action( 'load-admin_page_wpsp_sitepulse_onboarding', [ $this, 'set_page_title_early' ] );
	}
	
	/**
	 * Check if onboarding has been completed
	 */
	public function is_onboarding_completed() {
		return (bool) get_option( self::ONBOARDING_COMPLETED_KEY, false );
	}
	
	/**
	 * Check if onboarding has been dismissed
	 */
	public function is_onboarding_dismissed() {
		return (bool) get_option( self::ONBOARDING_DISMISSED_KEY, false );
	}
	
	/**
	 * Mark onboarding as completed
	 */
	public function mark_onboarding_completed() {
		update_option( self::ONBOARDING_COMPLETED_KEY, true );
		delete_option( self::ONBOARDING_STEP_KEY );
	}
	
	/**
	 * Mark onboarding as dismissed
	 */
	public function mark_onboarding_dismissed() {
		update_option( self::ONBOARDING_DISMISSED_KEY, true );
	}
	
	/**
	 * Get current onboarding step
	 */
	public function get_current_step() {
		return (int) get_option( self::ONBOARDING_STEP_KEY, 1 );
	}
	
	/**
	 * Set current onboarding step
	 */
	public function set_current_step( $step ) {
		update_option( self::ONBOARDING_STEP_KEY, (int) $step, false );
	}
	
	/**
	 * Maybe redirect to onboarding after activation
	 */
	public function maybe_redirect_to_onboarding() {
		// Only redirect on activation
		$should_redirect = get_transient( 'sitepulse_activation_redirect' );
		
		if ( ! $should_redirect ) {
			return;
		}
		
		// Delete the transient so we don't redirect again
		delete_transient( 'sitepulse_activation_redirect' );
		
		// Don't redirect if user can't manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// Don't redirect if already completed or dismissed
		if ( $this->is_onboarding_completed() || $this->is_onboarding_dismissed() ) {
			return;
		}

		// Redirect to onboarding page
		wp_safe_redirect( admin_url( 'admin.php?page=wpsp_sitepulse_onboarding' ) );
		exit;
	}
	
	/**
	 * Register hidden onboarding page
	 */
	public function register_onboarding_page() {
		// Only show if onboarding is not completed or dismissed
		// if ( $this->is_onboarding_completed() || $this->is_onboarding_dismissed() ) {
		// 	return;
		// }
		
		// Use proper WordPress function signature
		$page_hook = add_submenu_page(
			'', // Empty string instead of null to prevent deprecated warnings
			'SitePulse Onboarding', // No translation needed for page_title when parent is empty
			'SitePulse Onboarding', // No translation needed for menu_title when parent is empty
			'manage_options',
			$this->setPrefix( 'sitepulse_onboarding' ),
			[ $this, 'render_onboarding_page' ]
		);
	}
	
	/**
	 * Add body class to onboarding page
	 */
	public function add_onboarding_body_class( $classes ) {
		$screen = get_current_screen();
		
		// Check if we're on the onboarding page
		if ( $screen && $screen->id === 'admin_page_' . $this->setPrefix( 'sitepulse_onboarding' ) ) {
			$classes .= ' sitepulse_css_class ';
		}
		
		return $classes;
	}
	
	/**
	 * Set admin title for onboarding page to prevent strip_tags warning
	 */
	public function set_onboarding_title( $admin_title, $title ) {
		$screen = get_current_screen();
		
		// Check if we're on the onboarding page
		if ( $screen && $screen->id === 'admin_page_' . $this->setPrefix( 'sitepulse_onboarding' ) ) {
			// Return a proper title to prevent null being passed to strip_tags
			return 'SitePulse Onboarding' . $admin_title;
		}
		
		return $admin_title;
	}
	
	/**
	 * Set page title early - before admin-header.php is loaded
	 * This hook fires after admin_menu but before the page is rendered
	 */
	public function set_page_title_early() {
		global $title, $parent_file, $submenu_file;
		
		// Set the global title variable that admin-header.php uses
		$title = 'SitePulse Onboarding';
		
		// Also set parent and submenu file to avoid other potential issues
		$parent_file = '';
		$submenu_file = $this->setPrefix( 'sitepulse_onboarding' );
	}
	
	/**
	 * Enqueue onboarding assets
	 */
	public function enqueue_onboarding_assets( $hook ) {
		// Only load on onboarding page
		if ( $hook !== 'admin_page_wpsp_sitepulse_onboarding' ) {
			return;
		}
		
		// Enqueue dashicons (WordPress icons)
		wp_enqueue_style( 'dashicons' );
		
		// Enqueue backend global styles (includes sitepulse_css_class styles)
		wp_enqueue_style(
			$this->setPrefix( 'backend_global_style' ),
			SITEPULSE_ADMIN_ASSETS_CSS_URL . 'backend_global.css',
			[],
			filemtime( SITEPULSE_ADMIN_ASSETS_CSS_PATH . 'backend_global.css' ),
			'all'
		);
		
		// Enqueue backend styles (includes main SitePulse design)
		wp_enqueue_style(
			$this->setPrefix( 'backend_style' ),
			SITEPULSE_ADMIN_ASSETS_CSS_URL . 'backend.css',
			[],
			filemtime( SITEPULSE_ADMIN_ASSETS_CSS_PATH . 'backend.css' ),
			'all'
		);
		
		// Enqueue Bootstrap
		wp_enqueue_style(
			'sitepulse_bootstrap_css',
			SITEPULSE_ADMIN_ASSETS_URL . 'externals/bootstrap-5.3.8-dist/css/bootstrap.min.css',
			[],
			'5.3.8',
			'all'
		);
		
		// Enqueue onboarding-specific styles (last, so it can override if needed)
		wp_enqueue_style(
			$this->setPrefix( 'onboarding_style' ),
			SITEPULSE_ADMIN_ASSETS_CSS_URL . 'onboarding.css',
			[ $this->setPrefix( 'backend_style' ) ], // Depends on backend styles
			filemtime( SITEPULSE_ADMIN_ASSETS_CSS_PATH . 'onboarding.css' ),
			'all'
		);
		
		// Enqueue scripts
		wp_enqueue_script( 'jquery' );
		
		wp_enqueue_script(
			'sitepulse_bootstrap_js',
			SITEPULSE_ADMIN_ASSETS_URL . 'externals/bootstrap-5.3.8-dist/js/bootstrap.min.js',
			[],
			'5.3.8',
			true
		);
		
		wp_enqueue_script(
			$this->setPrefix( 'onboarding_script' ),
			SITEPULSE_ADMIN_ASSETS_JS_URL . 'onboarding.js',
			[ 'jquery', 'sitepulse_bootstrap_js' ],
			filemtime( SITEPULSE_ADMIN_ASSETS_JS_PATH . 'onboarding.js' ),
			true
		);
		
		// Localize script
		wp_localize_script(
			$this->setPrefix( 'onboarding_script' ),
			'SitePulseOnboarding',
			[
				'rest_url' => esc_url_raw( rest_url() ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'admin_url' => admin_url( 'admin.php?page=wpsp_sitepulse' ),
			]
		);
	}
	
	/**
	 * Render onboarding page
	 */
	public function render_onboarding_page() {
		// Ensure user has permission
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sitepulse' ) );
		}
		
		// Set the page title to prevent strip_tags warning in admin-header.php
		global $title;
		$title = 'SitePulse Onboarding';
		
		// Include the onboarding template
		require_once SITEPULSE_PATH . 'templates/backend/onboarding.php';
	}
	
	/**
	 * Reset onboarding state (for testing purposes)
	 */
	public function reset_onboarding() {
		delete_option( self::ONBOARDING_COMPLETED_KEY );
		delete_option( self::ONBOARDING_DISMISSED_KEY );
		delete_option( self::ONBOARDING_STEP_KEY );
	}
}


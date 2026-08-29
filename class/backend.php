<?php
/**
 * The backend(admin)-specific functionality of the plugin.
 * - admin menus
 * - admin pages
 * - stylesheets
 * - scripts
 */
if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly
class Sitepulse_Backend {
	private $plugin;
	private $settings;
	private $isSettingsPage = false;

	private $pages = [];
	public function __construct( $instance ) {
		$this->plugin = $instance;

		add_action( 'admin_enqueue_scripts', [ &$this, 'enqueue_styles_and_scripts' ] );
		add_filter( 'admin_body_class', [ &$this, 'sitepulse_body_class' ] );

		if ( current_user_can( 'manage_options' ) )
			add_action( 'admin_menu', [ &$this, 'register_admin_menu' ] );


		add_action( 'admin_init', [ &$this, 'register_settings' ] );

		add_action( 'current_screen', [ $this, 'wpdocs_this_screen' ] );

		// Hide third-party admin notices on SitePulse pages in Easy Mode
		add_action( 'admin_head', [ $this, 'suppress_admin_notices' ], 1 );

		// Filter plugin name in Plugins list for whitelabel
		add_filter( 'all_plugins', [ &$this, 'filter_plugin_name_whitelabel' ] );
	}

	// Register stylesheets and scripts for the admin area. Always.
	public function enqueue_styles_and_scripts() {
		// Enqueue dashicons for icons
		wp_enqueue_style( 'dashicons' );

		// Global for all backend dashboard
		wp_enqueue_style( $this->plugin->setPrefix( '_wpsp_backend_style_global' ), SITEPULSE_ADMIN_ASSETS_CSS_URL . 'backend_global.css', [], filemtime( SITEPULSE_ADMIN_ASSETS_CSS_PATH . 'backend_global.css' ), 'all' );
		wp_enqueue_script( $this->plugin->setPrefix( SITEPULSE_PREFIX . '_global_script' ), SITEPULSE_ADMIN_ASSETS_JS_URL . 'sitepulse_global.js', [ 'jquery' ], filemtime( SITEPULSE_ADMIN_ASSETS_JS_PATH . 'sitepulse_global.js' ), false );
		wp_localize_script( $this->plugin->setPrefix( SITEPULSE_PREFIX . '_global_script' ), 'SitePulse', [
			'rest_url' => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		] );

		// Deactivation feedback modal — only on the plugins page
		$current_screen = get_current_screen();
		if ( $current_screen && $current_screen->id === 'plugins' ) {
			wp_enqueue_style(
				'sitepulse-deactivation-modal',
				SITEPULSE_ADMIN_ASSETS_CSS_URL . 'deactivation-modal.css',
				[],
				filemtime( SITEPULSE_ADMIN_ASSETS_CSS_PATH . 'deactivation-modal.css' ),
				'all'
			);
			wp_enqueue_script(
				'sitepulse-deactivation-modal',
				SITEPULSE_ADMIN_ASSETS_JS_URL . 'deactivation-modal.js',
				[ 'jquery' ],
				filemtime( SITEPULSE_ADMIN_ASSETS_JS_PATH . 'deactivation-modal.js' ),
				true
			);
			wp_localize_script( 'sitepulse-deactivation-modal', 'SitePulseDeactivationData', [
				'api_endpoint'   => 'https://api.sitepulse.me/api/feedback/deactivation',
				'domain'         => esc_url_raw( home_url() ),
				'plugin_version' => defined( 'SITEPULSE_VERSION' ) ? SITEPULSE_VERSION : '',
				'wp_version'     => get_bloginfo( 'version' ),
			] );
		}

		// If Easy Mode is active, load its assets instead of classic for all SitePulse pages
		if ( class_exists( 'Sitepulse_Easy_Mode' ) && Sitepulse_Easy_Mode::is_enabled() ) {
			if ( $current_screen && strpos( $current_screen->id, 'sitepulse' ) !== false ) {
				Sitepulse_Easy_Mode::enqueue_assets();
				return;
			}
		}

		if ( ! $this->isSettingsPage ) {
			return;
		}

		// CSS sitepulse
		wp_enqueue_style( $this->plugin->setPrefix( SITEPULSE_PREFIX . '_backend_style' ), SITEPULSE_ADMIN_ASSETS_CSS_URL . 'backend.css', [], filemtime( SITEPULSE_ADMIN_ASSETS_CSS_PATH . 'backend.css' ), 'all' );
		// JS sitepulse
		wp_enqueue_script( $this->plugin->setPrefix( SITEPULSE_PREFIX . '_backend_script' ), SITEPULSE_ADMIN_ASSETS_JS_URL . 'backend.js', [ 'jquery' ], filemtime( SITEPULSE_ADMIN_ASSETS_JS_PATH . 'backend.js' ), true );
		wp_localize_script( $this->plugin->setPrefix( SITEPULSE_PREFIX . '_backend_script' ), 'SitePulse', [
			'cron_confirm_message' => esc_js( __( 'Are you sure you want to disable WordPress cron? This will stop all scheduled tasks including updates, backups, and other automated processes.', 'sitepulse' ) ),
			'email_blocking_confirm_message' => esc_js( __( 'Are you sure you want to block all outgoing emails? This will prevent password resets, notifications, and other important emails from being sent.', 'sitepulse' ) ),
			'rest_url' => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		] );

		wp_enqueue_style( 'sitepulse_bootstrap_css', SITEPULSE_ADMIN_ASSETS_URL . '/externals/bootstrap-5.3.8-dist/css/bootstrap.min.css', [], '5.3.8', 'all' );
		wp_enqueue_script( 'sitepulse_bootstrap_js', SITEPULSE_ADMIN_ASSETS_URL . '/externals/bootstrap-5.3.8-dist/js/bootstrap.min.js', [], '5.3.8', true );
	}

	public function wpdocs_this_screen() {
		$current_screen = get_current_screen();

		$this->isSettingsPage = in_array( $current_screen->id, $this->pages );

		// Also enqueue scripts for dashboard page (main sitepulse page)
		if ( ! $this->isSettingsPage && $current_screen && strpos( $current_screen->id, 'sitepulse' ) !== false ) {
			// Skip Bootstrap if Easy Mode is active
			if ( class_exists( 'Sitepulse_Easy_Mode' ) && Sitepulse_Easy_Mode::is_enabled() ) {
				return;
			}

			// Enqueue Bootstrap for dashboard
			wp_enqueue_style( 'sitepulse_bootstrap_css', SITEPULSE_ADMIN_ASSETS_URL . '/externals/bootstrap-5.3.8-dist/css/bootstrap.min.css', [], '5.3.8', 'all' );
			wp_enqueue_script( 'sitepulse_bootstrap_js', SITEPULSE_ADMIN_ASSETS_URL . '/externals/bootstrap-5.3.8-dist/js/bootstrap.min.js', [], '5.3.8', true );
		}
	}

	/**
	 * Remove third-party admin notices on SitePulse screens when Easy Mode is enabled.
	 */
	public function suppress_admin_notices() {
		if ( ! class_exists( 'Sitepulse_Easy_Mode' ) || ! Sitepulse_Easy_Mode::is_enabled() ) {
			return;
		}

		$current_screen = get_current_screen();
		if ( ! $current_screen || strpos( $current_screen->id, 'sitepulse' ) === false ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
	}
	/**
	 * Register admin menu.
	 */
	public function register_admin_menu() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sitepulse' ) );
		}

		/**
		 * Add a top-level menu page.
		 * https://developer.wordpress.org/reference/functions/add_menu_page/
		 *
		 * Arguments:
		 * 1. Page Title
		 * 2. Menu Title
		 * 3. Capability
		 * 4. Menu Slug (you will overwrite that menu item if specified slug is already exists)
		 * 5. Function to display page
		 * 6. Icon URL
		 * 7. Position (https://developer.wordpress.org/reference/functions/add_menu_page/#menu-structure)
		 */
		$page = add_menu_page(
			Sitepulse_Whitelabel_Service::get_plugin_name(),
			Sitepulse_Whitelabel_Service::get_plugin_name(),
			"manage_options",
			$this->plugin->setPrefix( "sitepulse" ),
			[ &$this, "render_page_sitepulse" ],
			Sitepulse_Whitelabel_Service::get_logo_url( 'menu' ),
			80
		);

		array_push( $this->pages, $page );

		// Change the first submenu to "Dashboard" and make it open the main page
		add_submenu_page(
			$this->plugin->setPrefix( "sitepulse" ),
			__( "Dashboard", 'sitepulse' ),
			__( "Dashboard", 'sitepulse' ),
			"manage_options",
			$this->plugin->setPrefix( "sitepulse" ),
			[ &$this, "render_page_sitepulse" ],
			1
		);

		// Add a submenu for the LoadSentinel
		$page_resource_load = add_submenu_page(
			$this->plugin->setPrefix( "sitepulse" ),
			__( 'Insights', 'sitepulse' ),
			__( 'Insights', 'sitepulse' ),
			'manage_options',
			$this->plugin->setPrefix( "sitepulse" ) . '_' . SITEPULSE_PROFILER_SLUG,
			[ &$this, 'render_resource_load' ],
			2
		);

		array_push( $this->pages, $page_resource_load );

		// Add a submenu for the cURL API
		$page_curl_api = add_submenu_page(
			$this->plugin->setPrefix( "sitepulse" ),
			__( 'APIMonitor', 'sitepulse' ),
			__( 'APIMonitor', 'sitepulse' ),
			'manage_options',
			$this->plugin->setPrefix( "sitepulse" ) . '_' . SITEPULSE_CURL_API_SLUG,
			[ &$this, 'render_curl_api' ],
			3
		);

		array_push( $this->pages, $page_curl_api );

		// Add a submenu for settings page
		$page_settings = add_submenu_page(
			$this->plugin->setPrefix( "sitepulse" ),
			__( 'Settings', 'sitepulse' ),
			__( 'Settings', 'sitepulse' ),
			'manage_options',
			$this->plugin->setPrefix( "sitepulse" ) . '_settings',
			[ &$this, 'render_page_sitepulse_settings' ],
			10
		);

		array_push( $this->pages, $page_settings );
	}

	public function sitepulse_body_class( $classes ) {
		return $this->plugin->add_sitepulse_body_class( $classes, $this->pages );
	}

	/**
	 * Filter plugin name in Plugins list for whitelabel
	 *
	 * @param array $plugins Array of plugin data
	 * @return array Modified plugins array
	 */
	public function filter_plugin_name_whitelabel( $plugins ) {
		// Only modify if whitelabel is enabled
		if ( ! class_exists( 'Sitepulse_Whitelabel_Service' ) || ! Sitepulse_Whitelabel_Service::is_whitelabel_enabled() ) {
			return $plugins;
		}

		// Find the SitePulse plugin in the list
		$plugin_file = plugin_basename( SITEPULSE_PLUGIN_FILE );
		if ( isset( $plugins[ $plugin_file ] ) ) {
			$whitelabel_name = Sitepulse_Whitelabel_Service::get_plugin_name();
			$company_name = Sitepulse_Whitelabel_Service::get_company_name();

			// Update plugin name
			$plugins[ $plugin_file ]['Name'] = $whitelabel_name;
			$plugins[ $plugin_file ]['Title'] = $whitelabel_name;

			// Update author if company name is set
			if ( ! empty( $company_name ) ) {
				$plugins[ $plugin_file ]['Author'] = $company_name;
				$plugins[ $plugin_file ]['AuthorName'] = $company_name;
			}
		}

		return $plugins;
	}

	/**
	 * Register settings page(s), sections and fields.
	 */
	public function register_settings() {
		$this->settings = (array) $this->plugin->getOption( 'fields' );

		$sitepulse = $this->plugin->setPrefix( "sitepulse" );
		$http_load = $this->plugin->setPrefix( "http-load" );

		$menu1_section1 = $this->plugin->setPrefix( "dashboard" );
		add_settings_section(
			$menu1_section1,
			false,
			[ &$this, "render_dashboard_section" ],
			$sitepulse
		);
	}
	/**
	 * Display the settings page for the menu(s) that have created.
	 */
	public function render_page_sitepulse() {
		// Render all sections of the page
		do_settings_sections( $this->plugin->setPrefix( "sitepulse" ) );
	}

	/**
	 * Calculate performance scores for dashboard metrics
	 * 
	 * @param array $stats Hook performance stats
	 * @param array $curl_events API request events
	 * @param array $mem Memory information
	 * @param int $active_plugins_count Number of active plugins
	 * @param array $error_log_stats Error log statistics
	 * @return array Calculated scores
	 */
	private function calculate_performance_scores( $stats, $curl_events, $mem, $active_plugins_count, $error_log_stats ) {
		// Calculate scores for each metric (0-100 scale)
		$loadsentinel_score = count( $stats ) > 0 ? 100 - count( $stats ) : 0;
		$api_score = count( $curl_events ) > 0 ? 100 - count( $curl_events ) : 0;

		$api_score = $api_score > 0 ? $api_score : 100;
		$loadsentinel_score = $loadsentinel_score > 0 ? $loadsentinel_score : 100;

		// Memory score (inverse - lower usage = higher score)
		$mem_percent = floatval( str_replace( [ '%', 'MB', ' ' ], '', $mem['formatted'] ) );
		if ( strpos( $mem['formatted'], '%' ) !== false ) {
			$memory_score = max( 0, min( 100, round( 100 - $mem_percent ) ) );
		} else {
			$memory_score = 75; // Default if can't parse
		}

		// Plugins score (fewer plugins = higher score)
		$plugins_score = min( 100, max( 0, 100 - ( $active_plugins_count * 2 ) ) );

		// Error Log score
		$error_count = isset( $error_log_stats['error_count'] ) ? (int) $error_log_stats['error_count'] : 0;
		$error_log_score = max( 0, min( 100, 100 - ( $error_count * 10 ) ) );
		if ( $error_count === 0 ) {
			$error_log_score = 100;
		}

		return array(
			'loadsentinel_score' => $loadsentinel_score,
			'api_score' => $api_score,
			'memory_score' => $memory_score,
			'plugins_score' => $plugins_score,
			'error_log_score' => $error_log_score,
		);
	}

	/**
	 * Calculate slow plugins and themes from performance data
	 * 
	 * @param array $stats Hook performance stats
	 * @param array $plugin_profiler_stats Plugin profiler stats
	 * @return array Slow items array
	 */
	private function calculate_slow_items( $stats, $plugin_profiler_stats ) {
		$slow_plugins_themes = array();
		if ( ! empty( $stats ) && is_array( $stats ) ) {
			foreach ( $stats as $stat ) {
				if ( ! empty( $stat ) && isset( $stat['total'] ) ) {
					$time_ms = $stat['total'] * 1000;
					if ( $time_ms > 500 ) {
						$plugin_or_theme = str_replace( '-', ' ', $stat['source'] );
						$slow_plugins_themes[] = array(
							'name' => ucfirst( $plugin_or_theme ),
							'time_ms' => $time_ms,
							'date_time' => isset( $stat['date_time'] ) ? $stat['date_time'] : 0,
							'severity' => $time_ms > 1000 ? 'critical' : 'warning'
						);
					}
				}
			}
		}

		$slow_plugins_profiler = array();
		if ( ! empty( $plugin_profiler_stats ) && is_array( $plugin_profiler_stats ) ) {
			foreach ( $plugin_profiler_stats as $plugin_stat ) {
				if ( isset( $plugin_stat['load_time_ms'] ) ) {
					$time_ms = floatval( $plugin_stat['load_time_ms'] );
					if ( $time_ms > 500 ) {
						$slow_plugins_profiler[] = array(
							'name' => isset( $plugin_stat['plugin_name'] ) ? $plugin_stat['plugin_name'] : 'Unknown Plugin',
							'time_ms' => $time_ms,
							'date_time' => isset( $plugin_stat['date_time'] ) ? $plugin_stat['date_time'] : 0,
							'severity' => $time_ms > 1000 ? 'critical' : 'warning'
						);
					}
				}
			}
		}

		// Merge and deduplicate
		$all_slow_items = array();
		$seen_names = array();

		foreach ( $slow_plugins_profiler as $item ) {
			$key = strtolower( $item['name'] );
			if ( ! isset( $seen_names[ $key ] ) ) {
				$all_slow_items[] = $item;
				$seen_names[ $key ] = true;
			}
		}

		foreach ( $slow_plugins_themes as $item ) {
			$key = strtolower( $item['name'] );
			if ( ! isset( $seen_names[ $key ] ) ) {
				$all_slow_items[] = $item;
				$seen_names[ $key ] = true;
			}
		}

		usort( $all_slow_items, function ( $a, $b ) {
			return $b['time_ms'] <=> $a['time_ms'];
		} );

		return $all_slow_items;
	}

	/**
	 * Find top slowest item from stats
	 * 
	 * @param array $stats Hook performance stats
	 * @return array|null Top slow item or null
	 */
	private function get_top_slow_item( $stats ) {
		if ( empty( $stats ) || ! is_array( $stats ) ) {
			return null;
		}

		$top_slow = null;
		$top_slow_time = 0;
		foreach ( $stats as $stat ) {
			if ( ! empty( $stat ) && isset( $stat['total'] ) ) {
				$time_ms = $stat['total'] * 1000;
				if ( $time_ms > $top_slow_time ) {
					$top_slow_time = $time_ms;
					$top_slow = $stat;
				}
			}
		}

		if ( $top_slow ) {
			$plugin_or_theme = str_replace( '-', ' ', $top_slow['source'] );
			return array(
				'name' => ucfirst( $plugin_or_theme ),
				'time_ms' => $top_slow_time,
				'hook' => isset( $top_slow['hook'] ) ? $top_slow['hook'] : '',
				'fileline' => isset( $top_slow['fileline'] ) ? $top_slow['fileline'] : '',
				'type' => 'plugin_or_theme'
			);
		}

		return null;
	}

	/**
	 * Calculate slow API requests
	 * 
	 * @param array $curl_events API request events
	 * @return array Slow API requests
	 */
	private function calculate_slow_api_requests( $curl_events ) {
		$slow_api_requests = array();
		if ( ! empty( $curl_events ) && is_array( $curl_events ) ) {
			foreach ( $curl_events as $event ) {
				if ( isset( $event['elapsed'] ) ) {
					$time_ms = $event['elapsed'] * 1000;
					if ( $time_ms > 500 ) {
						$slow_api_requests[] = array(
							'url' => isset( $event['url'] ) ? $event['url'] : 'Unknown URL',
							'time_ms' => $time_ms,
							'origin' => isset( $event['origin'] ) ? $event['origin'] : 'Unknown',
							'code' => isset( $event['code'] ) ? $event['code'] : '',
							'severity' => $time_ms > 1000 ? 'critical' : 'warning'
						);
					}
				}
			}
			usort( $slow_api_requests, function ( $a, $b ) {
				return $b['time_ms'] <=> $a['time_ms'];
			} );
		}

		return $slow_api_requests;
	}

	/**
	 * Calculate total load time
	 * 
	 * @param array $stats Hook performance stats
	 * @return float Total load time in milliseconds
	 */
	private function calculate_total_load_time( $stats ) {
		$total_load_time = 0;
		$time_load_in_ms = null;

		if ( class_exists( 'Sitepulse_Frontend' ) ) {
			$time_load_in_ms = Sitepulse_Frontend::get_current_load_time();
			if ( $time_load_in_ms !== null ) {
				$total_load_time = (float) $time_load_in_ms;
			}
		}

		if ( $total_load_time == 0 && class_exists( 'Sitepulse_Plugin_Profiler' ) ) {
			$total_load_time = (float) Sitepulse_Plugin_Profiler::get_total_load_time();
		}

		if ( $total_load_time == 0 && ! empty( $stats ) ) {
			foreach ( $stats as $stat ) {
				if ( ! empty( $stat ) && isset( $stat['total'] ) ) {
					$total_load_time += (float) $stat['total'] * 1000;
				}
			}
		}

		return $total_load_time;
	}

	/**
	 * Calculate load time score based on total load time
	 * 
	 * @param float $total_load_time Total load time in milliseconds
	 * @return int Load time score (0-100)
	 */
	private function calculate_load_time_score( $total_load_time ) {
		$load_time_score = 100;
		if ( $total_load_time > 0 ) {
			if ( $total_load_time >= 5000 ) {
				$load_time_score = max( 0, 20 - ( ( $total_load_time - 5000 ) / 5000 * 20 ) );
			} elseif ( $total_load_time >= 2000 ) {
				$load_time_score = max( 20, 40 - ( ( $total_load_time - 2000 ) / 3000 * 20 ) );
			} elseif ( $total_load_time >= 1500 ) {
				$load_time_score = max( 40, 60 - ( ( $total_load_time - 1500 ) / 500 * 20 ) );
			} elseif ( $total_load_time >= 1000 ) {
				$load_time_score = max( 60, 90 - ( ( $total_load_time - 1000 ) / 500 * 30 ) );
			} else {
				$load_time_score = 100;
			}
			$load_time_score = round( $load_time_score );
		}

		return $load_time_score;
	}

	/**
	 * Calculate overall, speed, and resource scores
	 * 
	 * @param array $scores Performance scores array
	 * @param float $total_load_time Total load time in milliseconds
	 * @param int $load_time_score Load time score
	 * @return array Overall scores
	 */
	private function calculate_overall_scores( $scores, $total_load_time, $load_time_score ) {
		// Calculate overall score including load time score
		if ( $load_time_score > 0 && $total_load_time > 0 ) {
			$overall_score = round( ( $scores['loadsentinel_score'] + $scores['api_score'] + $scores['memory_score'] + $scores['plugins_score'] + $scores['error_log_score'] + $load_time_score ) / 6 );
		} else {
			$overall_score = round( ( $scores['loadsentinel_score'] + $scores['api_score'] + $scores['memory_score'] + $scores['plugins_score'] + $scores['error_log_score'] ) / 5 );
		}

		// Calculate speed score including load time score
		if ( $load_time_score > 0 && $total_load_time > 0 ) {
			$speed_score = round( ( $scores['loadsentinel_score'] + $scores['api_score'] + $load_time_score ) / 3 );
		} else {
			$speed_score = round( ( $scores['loadsentinel_score'] + $scores['api_score'] ) / 2 );
		}

		$resource_score = round( ( $scores['memory_score'] + $scores['plugins_score'] ) / 2 );

		return array(
			'overall_score' => $overall_score,
			'speed_score' => $speed_score,
			'resource_score' => $resource_score,
		);
	}

	/**
	 * Calculate memory usage percentage for progress bar display
	 * 
	 * @param array $mem Memory information
	 * @return float Memory usage percentage (0-100)
	 */
	private function calculate_memory_percentage( $mem ) {
		// Extract percentage from $mem['percent'] which is in format "7%" or "—"
		if ( isset( $mem['percent'] ) && $mem['percent'] !== '—' && $mem['percent'] !== '' ) {
			// Remove % sign and convert to float
			$percent_value = floatval( str_replace( '%', '', trim( $mem['percent'] ) ) );
			if ( ! is_nan( $percent_value ) && $percent_value >= 0 ) {
				return min( 100, max( 0, $percent_value ) );
			}
		}
		// Fallback: try to parse from formatted string if percent is not available
		if ( isset( $mem['formatted'] ) && strpos( $mem['formatted'], '%' ) !== false ) {
			// Try to extract percentage from formatted string like "Memory: 7.5 MB ... — 7%"
			preg_match( '/(\d+(?:\.\d+)?)\s*%/', $mem['formatted'], $matches );
			if ( ! empty( $matches[1] ) ) {
				$percent_value = floatval( $matches[1] );
				return min( 100, max( 0, $percent_value ) );
			}
		}
		return 0; // Default if can't parse
	}

	/**
	 * Calculate plugin count percentage
	 * 
	 * @param int $active_plugins_count Number of active plugins
	 * @return float Plugin count percentage
	 */
	private function calculate_plugin_percentage( $active_plugins_count ) {
		return min( 100, ( $active_plugins_count / 50 ) * 100 );
	}

	/**
	 * Process SitePulse settings form submission.
	 *
	 * @return bool True when settings were saved.
	 */
	private function process_settings_form() {
		if ( ! isset( $_POST['submit'], $_POST['sitepulse_settings_nonce'] ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sitepulse_settings_nonce'] ) ), 'sitepulse_settings_action' ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		require_once SITEPULSE_CLASS_PATH . 'settings.php';
		$sitepulse_settings = Sitepulse_Settings::getInstance();
		$updated_settings   = array();

		$updated_settings['email_blocking_enabled'] = isset( $_POST['sitepulse_email_blocking_enabled'] ) ? 1 : 0;
		if ( isset( $_POST['sitepulse_email_blocking_mode'] ) ) {
			$updated_settings['email_blocking_mode'] = sanitize_text_field( wp_unslash( $_POST['sitepulse_email_blocking_mode'] ) );
		}

		$updated_settings['cron_disabled']        = isset( $_POST['sitepulse_cron_disabled'] ) ? 1 : 0;
		$updated_settings['external_api_enabled'] = isset( $_POST['sitepulse_external_api_enabled'] ) ? 1 : 0;

		if ( isset( $_POST['sitepulse_recovery_mode_email_addresses'] ) ) {
			$recovery_email = sanitize_email( wp_unslash( $_POST['sitepulse_recovery_mode_email_addresses'] ) );
			$updated_settings['recovery_mode_email_addresses'] = $recovery_email;
			if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
				Sitepulse_Error_Handler::set_recovery_email( $recovery_email );
			}
		}

		return (bool) $sitepulse_settings->update_settings( $updated_settings );
	}

	/**
	 * Display sections
	 */
	public function render_dashboard_section() {
		$stats = get_option( SITEPULSE_PROFILER_KEY, [] );
		$snapshot_time = get_option( SITEPULSE_PROFILER_CURRENT_TIME, null );
		$loadstatus = get_option( "sitepulse_pageloadhttp_loadstatus" );
		$curlstatus = get_option( "sitepulse_pageloadhttp_curlstatus" );
		$sitepulse_current_tracked_pageid = get_option( "sitepulse_current_tracked_pageid" );

		// List of activated plugins - use cached utility method
		$active_plugins_count = class_exists( 'Sitepulse_Utils' )
			? Sitepulse_Utils::get_active_plugins_count()
			: count( get_option( 'active_plugins', [] ) );

		// Memory ram used
		$mem = $this->plugin->sp_get_memory_info();
		$disk_write = $this->plugin->sitepulse_disk_write_test( 1048576, 3 );

		// Testing with Stress mode
		if ( SITEPULSE_STRESS_MODE ) {
			$this->plugin->sp_db_delay_test( 6.0, false );
		}

		// Skip slow queries calculation when pro is active (pro has its own SQL Monitor)
		$slow_queries = [];
		$slow_queries = $this->plugin->sp_get_slow_queries( 100.0, 50 );
		$curLoader = new Sitepulse_CurLoader();
		$curl_events = array_reverse( $curLoader::get_events() );

		// Get plugin profiler stats
		$plugin_profiler_stats = [];
		if ( class_exists( 'Sitepulse_Plugin_Profiler' ) ) {
			$plugin_profiler_stats = Sitepulse_Plugin_Profiler::get_stats();
		}

		// Get error log statistics
		$error_log_stats = array(
			'error_count' => 0,
			'total_count' => 0,
			'last_error_date' => null,
		);
		if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
			$error_log_stats = Sitepulse_Error_Handler::get_error_log_stats();
		}

		// Calculate all performance metrics
		$scores = $this->calculate_performance_scores( $stats, $curl_events, $mem, $active_plugins_count, $error_log_stats );
		$all_slow_items = $this->calculate_slow_items( $stats, $plugin_profiler_stats );
		$top_slow_item = $this->get_top_slow_item( $stats );
		$slow_api_requests = $this->calculate_slow_api_requests( $curl_events );
		$total_load_time = $this->calculate_total_load_time( $stats );
		$load_time_score = $this->calculate_load_time_score( $total_load_time );
		$overall_scores = $this->calculate_overall_scores( $scores, $total_load_time, $load_time_score );

		// Extract error counts
		$error_count = isset( $error_log_stats['error_count'] ) ? (int) $error_log_stats['error_count'] : 0;
		$fatal_count = isset( $error_log_stats['fatal_count'] ) ? (int) $error_log_stats['fatal_count'] : 0;
		$warning_count = isset( $error_log_stats['warning_count'] ) ? (int) $error_log_stats['warning_count'] : 0;

		// Determine if we should show performance warnings
		$is_overall_slow = ( $scores['loadsentinel_score'] < 60 );
		$show_performance_warnings = ! empty( $all_slow_items ) || $total_load_time > 2000 || $is_overall_slow;

		// Calculate display percentages
		$mem_usage_percent = $this->calculate_memory_percentage( $mem );
		$plugin_percent = $this->calculate_plugin_percentage( $active_plugins_count );

		// Make all calculated data available to template
		$loadsentinel_score = $scores['loadsentinel_score'];
		$api_score = $scores['api_score'];
		$memory_score = $scores['memory_score'];
		$plugins_score = $scores['plugins_score'];
		$error_log_score = $scores['error_log_score'];
		$overall_score = $overall_scores['overall_score'];
		$speed_score = $overall_scores['speed_score'];
		$resource_score = $overall_scores['resource_score'];

		// Get PageSpeed Insights report data
		$pagespeed_report = null;

		if ( ! SITEPULSE_PRO_IS_ACTIVE ) {
			// Free
			if ( class_exists( 'Sitepulse_Api_Service' ) ) {
				$pagespeed_report = Sitepulse_Api_Service::get_pagespeed_report( true );
			}
		}

		// Validate PageSpeed report data - supports new structure with desktop/mobile keys
		$has_valid_pagespeed = false;
		$ps_performance_score = 0;
		$ps_data = null;
		$ps_strategy = 'desktop';

		// Get vulnerabilities list
		$vulnerabilities = get_option( 'sitepulse_vulnerabilities_list', [] );
		$last_vulnerability_check = get_option( 'sitepulse_last_vulnerability_check', '' );

		if ( ! empty( $pagespeed_report ) && is_array( $pagespeed_report ) && ! empty( $pagespeed_report['has_data'] ) ) {
			// Check for desktop data first, then mobile
			if ( isset( $pagespeed_report['desktop'] ) && is_array( $pagespeed_report['desktop'] ) ) {
				$ps_data = $pagespeed_report['desktop'];
				$ps_strategy = 'desktop';
			} elseif ( isset( $pagespeed_report['mobile'] ) && is_array( $pagespeed_report['mobile'] ) ) {
				$ps_data = $pagespeed_report['mobile'];
				$ps_strategy = 'mobile';
			}

			// Validate scores exist
			if ( $ps_data && isset( $ps_data['scores'] ) && is_array( $ps_data['scores'] ) ) {
				if ( isset( $ps_data['scores']['performance'] ) && is_numeric( $ps_data['scores']['performance'] ) ) {
					$perf_value = floatval( $ps_data['scores']['performance'] );
					// Handle both 0-1 and 0-100 formats
					$ps_performance_score = $perf_value <= 1 ? round( $perf_value * 100 ) : round( $perf_value );
					$has_valid_pagespeed = true;
				}
			}
		}

		// Allow pro version to add/modify dashboard data before rendering
		do_action( 'sitepulse_before_dashboard_render', $stats, $curl_events, $mem );

		$sitepulse_lowhttp_enabled = get_option( 'sitepulse_curl_api_enabled', false );
		$sitepulse_profiler_enabled = get_option( 'sitepulse_profiler_enabled', null );

		// AI Diagnostic shared variables - loaded once for both views
		$sp_ai_settings = get_option( 'sitepulse_settings', array() );
		$sp_ai_external_api_enabled = isset( $sp_ai_settings['external_api_enabled'] ) ? (bool) $sp_ai_settings['external_api_enabled'] : true;
		$ai_diagnostic_status = null;
		$ai_diagnostic_requested_at = null;
		$ai_diagnostic_queue_position = null;
		$ai_diagnostic_report = null;

		if ( $sp_ai_external_api_enabled ) {
			$ai_diagnostic_status = get_option( 'sitepulse_ai_diagnostic_status', null );
			$ai_diagnostic_requested_at = get_option( 'sitepulse_ai_diagnostic_requested_at', null );
			$ai_diagnostic_queue_position = get_transient( 'sitepulse_ai_diagnostic_queue_position' );
			
			// Get report if completed or no status yet
			if ( $ai_diagnostic_status === 'completed' || empty( $ai_diagnostic_status ) ) {
				if ( ! SITEPULSE_PRO_IS_ACTIVE ) {
					$ai_diagnostic_report = Sitepulse_Api_Service::get_ai_diagnostic_report( true );
				}
			}
		}

		// Helper flags for AI diagnostic display
		$has_ai_report = ! empty( $ai_diagnostic_report ) && is_array( $ai_diagnostic_report );
		$is_ai_pending = in_array( $ai_diagnostic_status, array( 'pending', 'processing' ), true );
		$is_ai_failed = $ai_diagnostic_status === 'failed';

		// Easy Mode dashboard/views
		if ( class_exists( 'Sitepulse_Easy_Mode' ) && Sitepulse_Easy_Mode::is_enabled() ) {
			$easy_view_name = Sitepulse_Easy_Mode::get_current_view();
			$total_time = class_exists( 'Sitepulse_Plugin_Profiler' ) ? Sitepulse_Plugin_Profiler::get_total_load_time() : 0;
			$autoload_metrics = ( class_exists( 'Sitepulse_Utils' ) && method_exists( 'Sitepulse_Utils', 'get_autoload_metrics' ) )
				? Sitepulse_Utils::get_autoload_metrics()
				: array();
			$weekly_trends = array();

			$sp_settings_saved = false;
			$sitepulse_settings = null;
			$current_settings = array();
			$status_info = array();
			$active_plugins = array();

			if ( in_array( $easy_view_name, array( 'settings', 'system' ), true ) ) {
				require_once SITEPULSE_CLASS_PATH . 'settings.php';
				$sitepulse_settings = Sitepulse_Settings::getInstance();

				if ( 'settings' === $easy_view_name ) {
					$sp_settings_saved = $this->process_settings_form();
				}

				$current_settings = $sitepulse_settings->get_all_settings();
				$status_info = $sitepulse_settings->get_status_info();
				$active_plugins = get_option( 'active_plugins', array() );
			}

			$single_curl_events = false;
			$single_load_events = false;
			$error_log = $error_log_stats;

			Sitepulse_Easy_Mode::render( $easy_view_name, compact(
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
				'single_curl_events', 'single_load_events',
				'error_log', 'fatal_count', 'warning_count',
				'last_vulnerability_check', 'mem_usage_percent',
				'plugin_percent', 'disk_write'
			) );
			return;
		}

		// Dashboard template.
		require_once SITEPULSE_PATH . 'templates/backend/dashboard.php';
	}


	/** Admin: resource_load */
	public function render_resource_load() {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		// full report mode
		$sitepulse_report_mode_active = get_option( "sitepulse_report_mode_active" );
		$sitepulse_current_tracked_pageid = get_option( "sitepulse_current_tracked_pageid" );
		$stats = get_option( SITEPULSE_PROFILER_KEY, [] );

		$single_load_events = false;
		if ( $sitepulse_report_mode_active && $sitepulse_current_tracked_pageid ) {
			$sitepulse_load_single_page_id = sanitize_text_field( wp_unslash( $sitepulse_current_tracked_pageid ) );

			if ( ! empty( $sitepulse_load_single_page_id ) && is_numeric( $sitepulse_load_single_page_id ) ) {
				$sitepulse_load_single_page_id = $sitepulse_load_single_page_id;

				$post_load_events = get_transient( 'sitepulse_load_single_page_' . $sitepulse_load_single_page_id );
				if ( $post_load_events ) {
					$stats = $post_load_events;
					$single_load_events = true;
				}
			}
		}

		$snapshot_time = get_option( SITEPULSE_PROFILER_CURRENT_TIME, null );

		// List of activated plugins - use cached utility method
		$active_plugins_count = class_exists( 'Sitepulse_Utils' )
			? Sitepulse_Utils::get_active_plugins_count()
			: count( get_option( 'active_plugins', [] ) );

		// Memory ram used
		$mem = $this->plugin->sp_get_memory_info();

		if ( class_exists( 'Sitepulse_Easy_Mode' ) && Sitepulse_Easy_Mode::is_enabled() ) {
			Sitepulse_Easy_Mode::render( 'resource-load', compact(
				'stats', 'snapshot_time', 'active_plugins_count',
				'mem', 'single_load_events'
			) );
			return;
		}

		// LoadSentinel template.
		require_once SITEPULSE_PATH . 'templates/backend/' . SITEPULSE_PROFILER_SLUG . '.php';
	}

	public function render_page_sitepulse_settings() {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		// Memory ram used
		$mem = $this->plugin->sp_get_memory_info();

		// Initialize settings class
		require_once SITEPULSE_CLASS_PATH . 'settings.php';
		$sitepulse_settings = Sitepulse_Settings::getInstance();

		if ( class_exists( 'Sitepulse_Easy_Mode' ) && Sitepulse_Easy_Mode::is_enabled() ) {
			$sp_settings_saved = $this->process_settings_form();
			$active_plugins = get_option( 'active_plugins', array() );
			$current_settings = $sitepulse_settings->get_all_settings();
			$status_info = $sitepulse_settings->get_status_info();

			Sitepulse_Easy_Mode::render( 'settings', compact(
				'mem', 'sitepulse_settings', 'current_settings',
				'status_info', 'active_plugins', 'sp_settings_saved'
			) );
			return;
		}

		// Handle form submission for SitePulse settings
		if ( isset( $_POST['submit'] ) && isset( $_POST['sitepulse_settings_nonce'] ) ) {
			// Verify user has permission to manage options
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sitepulse' ) );
			}

			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sitepulse_settings_nonce'] ) ), 'sitepulse_settings_action' ) ) {
				$updated_settings = array();

				// Email blocking settings
				$updated_settings['email_blocking_enabled'] = isset( $_POST['sitepulse_email_blocking_enabled'] ) ? 1 : 0;
				if ( isset( $_POST['sitepulse_email_blocking_mode'] ) ) {
					$updated_settings['email_blocking_mode'] = sanitize_text_field( wp_unslash( $_POST['sitepulse_email_blocking_mode'] ) );
				}

				// Cron settings
				$updated_settings['cron_disabled'] = isset( $_POST['sitepulse_cron_disabled'] ) ? 1 : 0;

				// External API settings
				$updated_settings['external_api_enabled'] = isset( $_POST['sitepulse_external_api_enabled'] ) ? 1 : 0;

				// Recovery mode email address
				if ( isset( $_POST['sitepulse_recovery_mode_email_addresses'] ) ) {
					$recovery_email = sanitize_email( wp_unslash( $_POST['sitepulse_recovery_mode_email_addresses'] ) );
					$updated_settings['recovery_mode_email_addresses'] = $recovery_email;

					// Also update the error handler directly
					if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
						Sitepulse_Error_Handler::set_recovery_email( $recovery_email );
					}
				}

				// Update settings
				if ( $sitepulse_settings->update_settings( $updated_settings ) ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'SitePulse settings updated successfully!', 'sitepulse' ) . '</p></div>';
				} else {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to update settings.', 'sitepulse' ) . '</p></div>';
				}
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Security check failed. Please try again.', 'sitepulse' ) . '</p></div>';
			}
		}

		// List of activated plugins
		$active_plugins = get_option( 'active_plugins', [] );

		// Get current settings
		$current_settings = $sitepulse_settings->get_all_settings();
		$status_info = $sitepulse_settings->get_status_info();

		// Render all sections of the page
		require_once SITEPULSE_PATH . 'templates/backend/settings.php';
	}

	public function render_curl_api() {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		// full report mode
		$sitepulse_report_mode_active = get_option( "sitepulse_report_mode_active" );
		$sitepulse_current_tracked_pageid = get_option( "sitepulse_current_tracked_pageid" );

		// Main Method
		$curLoader = new Sitepulse_CurLoader();
		$curl_events = $curLoader::get_events();

		$single_curl_events = false;
		if ( $sitepulse_report_mode_active && $sitepulse_current_tracked_pageid ) {
			$safewpsp_sinpage_id = sanitize_text_field( wp_unslash( $sitepulse_current_tracked_pageid ) );
			if ( isset( $safewpsp_sinpage_id ) && ! empty( $safewpsp_sinpage_id ) && is_numeric( $safewpsp_sinpage_id ) ) {
				$wpsp_sinpage_id = $safewpsp_sinpage_id;

				$post_curl_events = get_transient( 'sitepulse_single_page_' . $wpsp_sinpage_id );
				if ( $post_curl_events ) {
					$curl_events = $post_curl_events;
					$single_curl_events = true;
				}
			}
		}

		// Snapshot time
		$snapshot_time = get_option( SITEPULSE_PROFILER_CURRENT_TIME, null );

		// List of activated plugins - use cached utility method
		$active_plugins_count = class_exists( 'Sitepulse_Utils' )
			? Sitepulse_Utils::get_active_plugins_count()
			: count( get_option( 'active_plugins', [] ) );

		$curl_events = array_reverse( $curl_events );
		// Memory ram used
		$mem = $this->plugin->sp_get_memory_info();

		if ( class_exists( 'Sitepulse_Easy_Mode' ) && Sitepulse_Easy_Mode::is_enabled() ) {
			Sitepulse_Easy_Mode::render( 'api-monitor', compact(
				'curl_events', 'snapshot_time', 'active_plugins_count',
				'mem', 'single_curl_events'
			) );
			return;
		}

		// LoadSentinel template.
		require_once SITEPULSE_PATH . 'templates/backend/' . SITEPULSE_CURL_API_SLUG . '.php';
	}
}

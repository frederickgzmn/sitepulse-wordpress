<?php
/**
 * The frontend(public facing)-specific functionality of the plugin.
 * - stylesheets
 * - scripts
 * - shortcodes
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Sitepulse_Frontend {
	private $plugin;

	public function __construct($instance) {
		$this->plugin = $instance;
		add_action('wp_enqueue_scripts', [&$this, 'enqueue_styles_and_scripts']);

		// Register admin bar node on both admin and front-end (method checks capabilities)
		add_action('admin_bar_menu', [&$this, 'admin_bar_node'], 90);

        // Add to footer (before </body>)
        add_action('wp_footer', [ &$this, 'sitepulse_modal_html'], 100 );
	}

    // Frontend modal HTML output
    public static function sitepulse_modal_html() {
        $sitepulse_current_tracked_pageid = get_option( "sitepulse_current_tracked_pageid" );
        $realtime_tracking = get_transient( "sitepulse_realtime_tracking" );
        $tracking_status = 'stopped';

        if ( $realtime_tracking ) {
            // Ensure is_logging() is defined or replace with appropriate check
            if ( current_user_can( 'manage_options' ) ) {
                $tracking_status = 'tracking';
            }
        }

        require_once SITEPULSE_PATH . 'templates/frontend/frontend.php';
    }

    public static function admin_bar_node($wp_admin_bar) {
        $curLoader = new Sitepulse_CurLoader();
        $stats = get_option(SITEPULSE_PROFILER_KEY, [] );

        $sitepulse_stats_count = count( $stats );

        if ( ! current_user_can('manage_options' ) ) return;
        $events = $curLoader::get_events();
        $count = count($events);

        $sitepulse_current_tracked_pageid = get_option( "sitepulse_current_tracked_pageid" );
        // Count current tracked page Load events
        $count_current_load_page = 0;
        if ( $sitepulse_current_tracked_pageid ) {
            $post_load_events = get_transient( 'sitepulse_load_single_page_' . $sitepulse_current_tracked_pageid );
            
            if ( $post_load_events ) {
                $count_current_load_page = count( $post_load_events );
            }
        }

        // Count current tracked page cURL events
        $count_current_curl_page = 0;
        if ( $sitepulse_current_tracked_pageid ) {
            $post_curl_events = get_transient( 'sitepulse_single_page_' . $sitepulse_current_tracked_pageid );
            
            if ( $post_curl_events ) {
                $count_current_curl_page = count( $post_curl_events );
            }
        }

        // Safely derive current request URI (used only to check for an existing query string)
        $current_request_uri = '';
        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            // Unsplash first (WP may have added slashes), then sanitize
            $current_request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
        }
        $has_query = ( $current_request_uri && false !== strpos( $current_request_uri, '?' ) );

        $left_icon = '<span class="wshp-left-icon" aria-hidden="true">'
            . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="3" width="18" height="18" rx="4" fill="transparent"/><path d="M4 12h4l2-5 3 10 2-6h5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>'
            . '</span>';

        $time_load = $time_load_in_ms = self::get_current_load_time();

        // $time_load seconds, minutes or hours formatting can be added here if needed
        $time_format_siteload = 'ms';
        if ( $time_load >= 3600000 ) {
            $time_load /= 3600000;
            $time_format_siteload = 'hr';
        } elseif ( $time_load >= 60000 ) {
            $time_load /= 60000;
            $time_format_siteload = 'min';
        } elseif ( $time_load >= 1000 ) {
            $time_load /= 1000;
            $time_format_siteload = 's';
        }

        // Telemetry
        if ( $time_load_in_ms ) {
            if ( $time_load_in_ms < 1000 ) {
                $time_load = '<span class="wshp-site-load-time green" aria-hidden="true"> ' . __('Load Time', 'sitepulse') . ': ' . number_format($time_load, 1) . ' ' . $time_format_siteload . '</span>';
            } elseif ( $time_load_in_ms >= 1000 && $time_load_in_ms < 3000 ) {
                $time_load = '<span class="wshp-site-load-time yellow" aria-hidden="true"> ' . __('Load Time', 'sitepulse') . ': ' . number_format($time_load, 1) . ' ' . $time_format_siteload . '</span>';
            } else {
                $time_load = '<span class="wshp-site-load-time red" aria-hidden="true"> ' . __('Load Time', 'sitepulse') . ': ' . number_format($time_load, 1) . ' ' . $time_format_siteload . '</span>';
            }
        }

        $badge = $count ? '<span class="wshp-count wpsp-stats" aria-hidden="true">API: ' . intval($count) . '</span>' : '';
        $badge .= $sitepulse_stats_count ? '<span class="wshp-count" aria-hidden="true">Load: ' . intval($sitepulse_stats_count) . '</span>' : '';
        $badge .= $time_load ? $time_load : '';

        $title_html = '<span class="wshp-pill">'
            . $left_icon
            . '<span class="wshp-label">SitePulse ' . $badge . '</span>'
            . '</span>';

        $wp_admin_bar->add_node([
            'id'    => SITEPULSE_PREFIX,
            'title' => $title_html,
            'href'  => admin_url('admin.php?page=' . SITEPULSE_PREFIX . SITEPULSE_PREFIX_SEPARATOR . SITEPULSE_SLUG),
            'meta'  => false,
        ]);

		$wp_admin_bar->add_node([
			'id'     => SITEPULSE_PREFIX . '_dashboard',
			'title'  => __('Dashboard', 'sitepulse'),
			'parent' => SITEPULSE_PREFIX,
			'href'   => admin_url('admin.php?page=' . SITEPULSE_PREFIX . SITEPULSE_PREFIX_SEPARATOR . SITEPULSE_SLUG),
			'meta'   => false,
		]);

        // Easy Mode toggle in admin bar
        if ( class_exists( 'Sitepulse_Easy_Mode' ) ) {
            $easy_enabled = Sitepulse_Easy_Mode::is_enabled();
            $wp_admin_bar->add_node([
                'id'     => SITEPULSE_PREFIX . '_easy_mode',
                'title'  => $easy_enabled
                    ? __( 'Switch to Classic View', 'sitepulse' )
                    : __( 'Switch to New Experience', 'sitepulse' ),
                'parent' => SITEPULSE_PREFIX,
                'href'   => '#',
                'meta'   => [
                    'onclick' => 'fetch("' . esc_url( admin_url( 'admin-ajax.php' ) ) . '",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:"action=sitepulse_toggle_easy_mode&nonce=' . wp_create_nonce( 'wp_rest' ) . '"}).then(function(){location.reload()});return false;',
                ],
            ]);
        }

		if ( is_singular( ['post', 'page'] ) || is_home() ) {
            
			$admin_permalink_apiload = admin_url('admin.php?page=' . SITEPULSE_PREFIX . SITEPULSE_PREFIX_SEPARATOR . SITEPULSE_SLUG . '_' . SITEPULSE_CURL_API_SLUG);

			// Track API/CURL requests page/post
			$wp_admin_bar->add_node([
                'id'     => SITEPULSE_PREFIX . '_api_tracker',
                'title'  => __('View Page Request Report', 'sitepulse'),
                'parent' => SITEPULSE_PREFIX,
                'href'   => $admin_permalink_apiload,
                'meta'   => false,
            ]);

            $admin_permalink_profileload = admin_url('admin.php?page=' . SITEPULSE_PREFIX . SITEPULSE_PREFIX_SEPARATOR . SITEPULSE_SLUG . '_' . SITEPULSE_PROFILER_SLUG);

			// Track Load requests page/post
			$wp_admin_bar->add_node([
                'id'     => SITEPULSE_PREFIX . '_load_tracker',
                'title'  => __('View Page Activity Report', 'sitepulse'),
                'parent' => SITEPULSE_PREFIX,
                'href'   => $admin_permalink_profileload,
                'meta'   => false,
            ]);

            $sitepulse_pageloadhttp_loadstatus = get_option( "sitepulse_pageloadhttp_loadstatus" );
            $sitepulse_pageloadhttp_curlstatus = get_option( "sitepulse_pageloadhttp_curlstatus" );

            // display page or post view
            $sitepulse_pagelhttp_view = 'display: none';

            // checked status loadstatus
            $sitepulse_pagelhttp_rain = '';
            if ( $sitepulse_pageloadhttp_loadstatus ) {
                $sitepulse_pagelhttp_rain = 'rainbow-border';
                $sitepulse_pagelhttp_view = 'display: block';
            }

            // checked status curlstatus
            $sitepulse_pagechttp_rain = '';
            if ( $sitepulse_pageloadhttp_curlstatus ) {
                $sitepulse_pagechttp_rain = 'rainbow-border';
                $sitepulse_pagelhttp_view = 'display: block';
            }

            $page_post_title = get_the_title();
            if (strlen($page_post_title) > 20) {
                $page_post_title = mb_substr($page_post_title, 0, 20) . '...';
            }

			$wp_admin_bar->add_node([
                'id'     => SITEPULSE_PREFIX . '_view_report',
                'parent' => SITEPULSE_PREFIX,
                'title'  => '
                    <div class="switch-container">
                        <div class="switch-item ' . $sitepulse_pagechttp_rain . '">
                            <span class="switch-label"> - Track Page Activity - </span>
                            <label class="switch">
                                <input type="checkbox" ' . checked( $sitepulse_pageloadhttp_curlstatus, true, false ) . ' id="curlSwitch" data-sitepulse-page-id="' . get_the_ID() . '">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="sitepulse-container">
                        <div class="sitepulse-ontracking" style="' . $sitepulse_pagelhttp_view . '">Tracking page: ' . $page_post_title . '<span class="wshp-count wpsp-stats">API: ' . $count_current_curl_page . '</span><span class="wshp-count">Load: ' . $count_current_load_page . '</span></div>
                    </div>
                    <div class="sitepulse-container">
                        <div class="sitepulse-realtime-tracking">
                            <button id="realtime-tracking-btn" class="sitepulse-btn sitepulse-btn-primary">
                                <span class="sitepulse-btn-icon">📊</span>
                                Start Real-Time Tracking
                            </button>
                        </div>
                    </div>
                ',
                'meta'   => [
                    'html' => false,
                    'class' => 'sitepulse-switches'
                ],
                'href'   => false,
            ]);
            // Warning for unsupported page types and not show on admin pages
		} elseif ( ! is_admin() ) {
            $wp_admin_bar->add_node([
                'id'     => SITEPULSE_PREFIX . '_unsupported_page',
                'title'  => '<span class="sitepulse-warning-label"><span class="sitepulse-warning-icon" aria-hidden="true">⚠</span> ' . __('Tracking not supported on this page', 'sitepulse') . '</span>',
                'parent' => SITEPULSE_PREFIX,
                'href'   => false,
                'meta'   => [
                    'html'  => false,
                    'class' => 'sitepulse-unsupported',
                    'title' => __('Tracking is not available for this page type.', 'sitepulse'),
                ],
            ]);
        }
    }

	// Register stylesheets and scripts for the frontend.
	public function enqueue_styles_and_scripts() {
		wp_enqueue_style($this->plugin->setPrefix(  SITEPULSE_PREFIX . '_frontend_style'), SITEPULSE_ADMIN_ASSETS_CSS_URL . 'frontend.css', [], filemtime(SITEPULSE_ADMIN_ASSETS_CSS_PATH . 'frontend.css'), 'all');
		wp_enqueue_script(
			$this->plugin->setPrefix(SITEPULSE_PREFIX . '_global_script'),
			SITEPULSE_ADMIN_ASSETS_JS_URL . 'sitepulse_global.js',
			['jquery'],
			filemtime(SITEPULSE_ADMIN_ASSETS_JS_PATH . 'sitepulse_global.js'),
			true // Load in footer
		);

        // Pass $post_load_events to frontend.js
        $sitepulse_current_tracked_pageid = get_option( "sitepulse_current_tracked_pageid" );
        $post_load_events = [];
        if ( $sitepulse_current_tracked_pageid ) {
            $transient = get_transient( 'sitepulse_load_single_page_' . $sitepulse_current_tracked_pageid );
            if ( $transient ) {
                $post_load_events = $transient;
            }
        }

        wp_enqueue_script(
            $this->plugin->setPrefix(SITEPULSE_PREFIX . '_frontend_script'),
            SITEPULSE_ADMIN_ASSETS_JS_URL . 'frontend.js',
            ['jquery'],
            filemtime(SITEPULSE_ADMIN_ASSETS_JS_PATH . 'frontend.js'),
            true // Load in footer
        );

        $post_load_events = array_values($post_load_events);
        usort($post_load_events, function($a, $b) {
            return $b['total_ms'] <=> $a['total_ms'];
        });

        wp_localize_script(
            $this->plugin->setPrefix( SITEPULSE_PREFIX . '_frontend_script'),
            'SitePulse', [
                'rest_url' => esc_url_raw( rest_url() ),
                'nonce'    => wp_create_nonce( 'wp_rest' ),
                'post_load_events' => array_slice($post_load_events, 0, 5),
                'post_load_events_count' => count( $post_load_events ),
            ]
        );
	}

    /**
     * Get current page load time in milliseconds
     */
    public static function get_current_load_time() {        
        global $timestart;

        if ( ! defined( 'SITEPULSE_SITELOAD_START' ) ) {
            $start = $timestart;
        } else {
            $start = SITEPULSE_SITELOAD_START;
        }

        if ( ! $start ) return null;
        
        return (microtime(true) - $start) * 1000;
    }
}

<?php
/**
 * The toolbar (admin bar) functionality of the plugin, shown on the front end
 * and in the admin for administrators:
 * - live load time and event counts
 * - "Analyze this page" for any front-end URL
 * - pause / resume monitoring
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Sitepulse_Frontend {
	private $plugin;

	public function __construct($instance) {
		$this->plugin = $instance;

		// Register admin bar node on both admin and front-end (method checks capabilities)
		add_action('admin_bar_menu', [&$this, 'admin_bar_node'], 90);
	}

    public static function admin_bar_node($wp_admin_bar) {
        if ( ! current_user_can('manage_options' ) ) return;

        $stats = Sitepulse_Profiler::get_stored_stats();
        $sitepulse_stats_count = count( $stats );
        $count = count( Sitepulse_CurLoader::get_events() );
        $paused = Sitepulse_Monitoring::is_paused();

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
        $badge .= $paused ? '<span class="wshp-paused">' . esc_html__( 'Paused', 'sitepulse' ) . '</span>' : '';

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

        // Every front-end URL can be analyzed: pages, posts, products, archives, search results.
        $wp_admin_bar->add_node([
            'id'     => SITEPULSE_PREFIX . '_analyze_page',
            'title'  => is_admin() ? __( 'Analyze a page', 'sitepulse' ) : __( 'Analyze this page', 'sitepulse' ),
            'parent' => SITEPULSE_PREFIX,
            'href'   => is_admin() ? Sitepulse_Page_Analysis::admin_link() : Sitepulse_Page_Analysis::admin_link( self::current_url(), true ),
            'meta'   => [
                'class' => 'wshp-analyze',
                'title' => __( 'See exactly what makes this page slow', 'sitepulse' ),
            ],
        ]);

        $wp_admin_bar->add_node([
            'id'     => SITEPULSE_PREFIX . '_monitoring',
            'title'  => $paused ? __( 'Resume monitoring', 'sitepulse' ) : __( 'Pause monitoring', 'sitepulse' ),
            'parent' => SITEPULSE_PREFIX,
            'href'   => Sitepulse_Monitoring::toggle_url(),
            'meta'   => [
                'title' => $paused
                    ? __( 'Start collecting performance data again', 'sitepulse' )
                    : __( 'Stop collecting performance data without deactivating SitePulse. Settings and history are kept.', 'sitepulse' ),
            ],
        ]);
    }

    /**
     * Address of the front-end page being viewed, on this site's home host.
     *
     * @return string
     */
    private static function current_url() {
        $home = wp_parse_url( home_url( '/' ) );
        $uri  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';

        return remove_query_arg(
            Sitepulse_Page_Tracker::QUERY_VAR,
            $home['scheme'] . '://' . $home['host'] . ( isset( $home['port'] ) ? ':' . $home['port'] : '' ) . $uri
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

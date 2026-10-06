<?php
final class Sitepulse_Test_Admin_Bar {
    public $nodes = array();
    public function add_node($node) { $this->nodes[$node['id']] = $node; }
}
final class FrontendAdminBarTest extends Sitepulse_Test_Case {
    /** @dataProvider durations */
    public function test_load_duration_uses_the_largest_applicable_unit_and_threshold_color($seconds, $display, $color): void {
        $GLOBALS['timestart'] = microtime(true) - $seconds;
        $bar = new Sitepulse_Test_Admin_Bar(); Sitepulse_Frontend::admin_bar_node($bar);
        $this->assertStringContainsString($display, $bar->nodes['wpsp']['title']);
        $this->assertStringContainsString('wshp-site-load-time ' . $color, $bar->nodes['wpsp']['title']);
    }
    public static function durations(): array { return array(array(.1, ' ms', 'green'), array(2, '2.0 s', 'yellow'), array(5, '5.0 s', 'red'), array(70, '1.2 min', 'red'), array(4000, '1.1 hr', 'red')); }
    public function test_menu_counts_events_and_offers_dashboard_analysis_and_pause(): void {
        update_option('sitepulse_profiler_stats', array(array('total_ms' => 2), array('total_ms' => 3))); update_option('sitepulse_curl_api_events', array(array('elapsed' => 2)));
        $GLOBALS['timestart'] = null; $bar = new Sitepulse_Test_Admin_Bar(); Sitepulse_Frontend::admin_bar_node($bar);
        $this->assertStringContainsString('API: 1', $bar->nodes['wpsp']['title']); $this->assertStringContainsString('Load: 2', $bar->nodes['wpsp']['title']); $this->assertStringNotContainsString('Paused', $bar->nodes['wpsp']['title']);
        $this->assertSame(array('wpsp', 'wpsp_dashboard', 'wpsp_analyze_page', 'wpsp_monitoring'), array_keys($bar->nodes));
        $this->assertSame('Analyze a page', $bar->nodes['wpsp_analyze_page']['title']); $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_page_analysis', $bar->nodes['wpsp_analyze_page']['href']);
        $this->assertSame('Pause monitoring', $bar->nodes['wpsp_monitoring']['title']);
        $this->assertSame('https://example.test/wp-admin/admin-post.php?action=sitepulse_toggle_monitoring&_wp_http_referer=%2F&_wpnonce=nonce-sitepulse_toggle_monitoring', $bar->nodes['wpsp_monitoring']['href']);
    }
    /** @dataProvider frontEndAddresses */
    public function test_every_front_end_page_can_be_analyzed_from_the_toolbar($request_uri, $expected_url): void {
        Sitepulse_Test_WP::$is_admin = false; $GLOBALS['timestart'] = null; $_SERVER['REQUEST_URI'] = $request_uri;
        $bar = new Sitepulse_Test_Admin_Bar(); Sitepulse_Frontend::admin_bar_node($bar);
        $this->assertSame('Analyze this page', $bar->nodes['wpsp_analyze_page']['title']);
        $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_page_analysis&url=' . rawurlencode($expected_url) . '&autorun=1', $bar->nodes['wpsp_analyze_page']['href']);
    }
    public static function frontEndAddresses(): array {
        return array(
            'product' => array('/product/blue-shirt/', 'https://example.test/product/blue-shirt/'), 'archive' => array('/category/news/page/2/', 'https://example.test/category/news/page/2/'),
            'search with query' => array('/?s=shoes&post_type=product', 'https://example.test/?s=shoes&post_type=product'),
            'analysis token removed' => array('/shop/?sitepulse_analyze=abcdefghijklmnopqrstuvwxyz012345', 'https://example.test/shop/'),
        );
    }
    public function test_front_end_link_keeps_port_and_falls_back_to_home_when_request_uri_is_missing(): void {
        Sitepulse_Test_WP::$is_admin = false; Sitepulse_Test_WP::$home_url = 'http://localhost:8080'; unset($_SERVER['REQUEST_URI']); $GLOBALS['timestart'] = null;
        $bar = new Sitepulse_Test_Admin_Bar(); Sitepulse_Frontend::admin_bar_node($bar);
        $this->assertStringEndsWith('&url=' . rawurlencode('http://localhost:8080/') . '&autorun=1', $bar->nodes['wpsp_analyze_page']['href']);
    }
    public function test_paused_monitoring_is_visible_and_offers_resume(): void {
        Sitepulse_Monitoring::pause(); $GLOBALS['timestart'] = null; $bar = new Sitepulse_Test_Admin_Bar(); Sitepulse_Frontend::admin_bar_node($bar);
        $this->assertStringContainsString('<span class="wshp-paused">Paused</span>', $bar->nodes['wpsp']['title']);
        $this->assertSame('Resume monitoring', $bar->nodes['wpsp_monitoring']['title']); $this->assertSame('Start collecting performance data again', $bar->nodes['wpsp_monitoring']['meta']['title']);
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_explicit_start_constant_takes_precedence_over_wordpress_start(): void {
        define('SITEPULSE_SITELOAD_START', microtime(true) - 2); $GLOBALS['timestart'] = microtime(true) - 50;
        $this->assertGreaterThanOrEqual(2000, Sitepulse_Frontend::get_current_load_time()); $this->assertLessThan(3000, Sitepulse_Frontend::get_current_load_time());
    }
}

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
    public function test_singular_page_menu_counts_global_and_page_events_and_shows_active_tracking(): void {
        Sitepulse_Test_WP::$is_singular = true; Sitepulse_Test_WP::$post_id = 42;
        Sitepulse_Test_WP::$posts[42] = array('post_title' => 'A page title longer than twenty characters');
        Sitepulse_Test_WP::$posts[0] = Sitepulse_Test_WP::$posts[42];
        update_option('sitepulse_current_tracked_pageid', 42); update_option('sitepulse_pageloadhttp_loadstatus', true); update_option('sitepulse_pageloadhttp_curlstatus', true);
        update_option('sitepulse_profiler_stats', array(array('total_ms' => 2), array('total_ms' => 3))); update_option('sitepulse_curl_api_events', array(array('elapsed' => 2)));
        set_transient('sitepulse_load_single_page_42', array(1, 2, 3)); set_transient('sitepulse_single_page_42', array(1, 2));
        update_user_meta(1, 'sitepulse_easy_mode', true); $GLOBALS['timestart'] = null;
        $bar = new Sitepulse_Test_Admin_Bar(); Sitepulse_Frontend::admin_bar_node($bar);
        $this->assertStringContainsString('API: 1', $bar->nodes['wpsp']['title']); $this->assertStringContainsString('Load: 2', $bar->nodes['wpsp']['title']);
        $page = $bar->nodes['wpsp_view_report']['title'];
        $this->assertStringContainsString('data-sitepulse-page-id="42"', $page); $this->assertStringContainsString('API: 2', $page); $this->assertStringContainsString('Load: 3', $page);
        $this->assertStringContainsString('A page title longer ...', $page); $this->assertStringContainsString('checked="checked"', $page); $this->assertStringContainsString('rainbow-border', $page); $this->assertStringContainsString('display: block', $page);
        $this->assertSame('Switch to Classic View', $bar->nodes['wpsp_easy_mode']['title']);
        $this->assertStringContainsString('wpsp_sitepulse_curl_api', $bar->nodes['wpsp_api_tracker']['href']); $this->assertStringContainsString('wpsp_sitepulse_resource_load', $bar->nodes['wpsp_load_tracker']['href']);
    }
    public function test_unsupported_frontend_page_has_notice_while_admin_page_does_not(): void {
        Sitepulse_Test_WP::$is_admin = false; $GLOBALS['timestart'] = null;
        $bar = new Sitepulse_Test_Admin_Bar(); Sitepulse_Frontend::admin_bar_node($bar);
        $this->assertArrayHasKey('wpsp_unsupported_page', $bar->nodes); $this->assertArrayNotHasKey('wpsp_view_report', $bar->nodes);
        Sitepulse_Test_WP::$is_admin = true; $bar = new Sitepulse_Test_Admin_Bar(); Sitepulse_Frontend::admin_bar_node($bar);
        $this->assertArrayNotHasKey('wpsp_unsupported_page', $bar->nodes); $this->assertSame('Switch to New Experience', $bar->nodes['wpsp_easy_mode']['title']);
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

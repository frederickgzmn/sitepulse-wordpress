<?php

final class FrontendTest extends Sitepulse_Test_Case {
    public function test_visitors_pages_get_no_sitepulse_scripts_or_footer_markup(): void {
        $plugin = new Sitepulse_Plugin(); $before = $GLOBALS['wp_filter']['wp_enqueue_scripts']->callbacks;
        $frontend = new Sitepulse_Frontend($plugin);
        $this->assertSame($before, $GLOBALS['wp_filter']['wp_enqueue_scripts']->callbacks); $this->assertFalse(has_action('wp_footer'));
        $this->assertSame(90, has_action('admin_bar_menu', array($frontend, 'admin_bar_node')));
    }

    public function test_admin_bar_is_hidden_from_unprivileged_users(): void {
        Sitepulse_Test_WP::$capabilities['manage_options'] = false;
        $bar = new class {
            public $nodes = array();
            public function add_node($node) { $this->nodes[] = $node; }
        };
        Sitepulse_Frontend::admin_bar_node($bar);
        $this->assertSame(array(), $bar->nodes);
    }

    public function test_current_load_time_reports_milliseconds_and_unknown_start(): void {
        $GLOBALS['timestart'] = null;
        $this->assertNull(Sitepulse_Frontend::get_current_load_time());
        $GLOBALS['timestart'] = microtime(true) - 2;
        $this->assertGreaterThanOrEqual(2000, Sitepulse_Frontend::get_current_load_time());
        unset($GLOBALS['timestart']);
    }
}

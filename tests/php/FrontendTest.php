<?php

final class FrontendTest extends Sitepulse_Test_Case {
    private function frontendHandle(): string {
        foreach (Sitepulse_Test_WP::$scripts as $handle => $script) {
            if ($script['src'] === SITEPULSE_ADMIN_ASSETS_JS_URL . 'frontend.js') { return $handle; }
        }
        $this->fail('The frontend tracking script must be enqueued.');
    }

    public function test_localized_page_events_are_ranked_limited_and_include_total_count(): void {
        update_option('sitepulse_current_tracked_pageid', 42);
        $events = array(
            'one' => array('source' => 'one', 'total_ms' => 10),
            'two' => array('source' => 'two', 'total_ms' => 50),
            'three' => array('source' => 'three', 'total_ms' => 30),
            'four' => array('source' => 'four', 'total_ms' => 20),
            'five' => array('source' => 'five', 'total_ms' => 70),
            'six' => array('source' => 'six', 'total_ms' => 60),
        );
        set_transient('sitepulse_load_single_page_42', $events, 60);
        (new Sitepulse_Frontend(new Sitepulse_Plugin()))->enqueue_styles_and_scripts();
        $handle = $this->frontendHandle();
        $data = Sitepulse_Test_WP::$localized[$handle]['SitePulse'];
        $this->assertSame(array('five', 'six', 'two', 'three', 'four'), array_column($data['post_load_events'], 'source'));
        $this->assertSame(6, $data['post_load_events_count']);
        $this->assertSame('nonce-wp_rest', $data['nonce']);
        $this->assertSame('https://example.test/wp-json/', $data['rest_url']);
        $this->assertSame(array('jquery'), Sitepulse_Test_WP::$scripts[$handle]['deps']);
    }

    public function test_no_tracked_page_localizes_an_empty_list(): void {
        (new Sitepulse_Frontend(new Sitepulse_Plugin()))->enqueue_styles_and_scripts();
        $data = Sitepulse_Test_WP::$localized[$this->frontendHandle()]['SitePulse'];
        $this->assertSame(array(), $data['post_load_events']);
        $this->assertSame(0, $data['post_load_events_count']);
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

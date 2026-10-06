<?php

final class BackendCoverageTest extends Sitepulse_Test_Case {
    private function invoke($backend, $method, ...$args) {
        $reflection = new ReflectionMethod($backend, $method);
        if (PHP_VERSION_ID < 80100) { $reflection->setAccessible(true); }
        return $reflection->invokeArgs($backend, $args);
    }

    public function test_admin_assets_follow_screen_and_mode_and_notices_are_suppressed_only_on_plugin_pages(): void {
        $backend = new Sitepulse_Backend(new Sitepulse_Plugin());
        Sitepulse_Test_WP::$screen = (object) array('id' => 'plugins');
        $backend->enqueue_styles_and_scripts();
        $this->assertArrayHasKey('sitepulse-deactivation-modal', Sitepulse_Test_WP::$scripts);
        $backend->register_admin_menu();
        Sitepulse_Test_WP::$screen->id = 'toplevel_page_wpsp_sitepulse';
        $backend->wpdocs_this_screen();
        $backend->enqueue_styles_and_scripts();
        $this->assertArrayHasKey('sitepulse_bootstrap_js', Sitepulse_Test_WP::$scripts);
        $this->assertStringContainsString('sitepulse', $backend->sitepulse_body_class('base'));
        Sitepulse_Test_WP::$screen->id = 'sitepulse_extension';
        $backend->wpdocs_this_screen();
        $backend->suppress_admin_notices();
        update_user_meta(1, 'sitepulse_easy_mode', true);
        $backend->wpdocs_this_screen();
        $backend->enqueue_styles_and_scripts();
        $this->assertArrayHasKey('sitepulse-easy-mode-script', Sitepulse_Test_WP::$scripts);
        add_action('admin_notices', '__return_true');
        add_action('all_admin_notices', '__return_true');
        $backend->suppress_admin_notices();
        $this->assertFalse(has_filter('admin_notices'));
        Sitepulse_Test_WP::$screen = null;
        $backend->suppress_admin_notices();
    }

    public function test_menu_and_rendering_capabilities_are_enforced(): void {
        Sitepulse_Test_WP::$capabilities = array();
        $backend = new Sitepulse_Backend(new Sitepulse_Plugin());
        foreach (array('render_resource_load', 'render_curl_api', 'render_page_sitepulse_settings') as $method) {
            ob_start(); $backend->$method(); $this->assertSame('', ob_get_clean());
        }
        $this->expectException(Sitepulse_Test_Die::class);
        $backend->register_admin_menu();
    }

    public function test_settings_form_requires_nonce_and_capability_and_saves_sanitized_values(): void {
        $backend = new Sitepulse_Backend(new Sitepulse_Plugin());
        $this->assertFalse($this->invoke($backend, 'process_settings_form'));
        $_POST = array('submit' => 'Save', 'sitepulse_settings_nonce' => 'bad');
        Sitepulse_Test_WP::$nonce_valid = false;
        $this->assertFalse($this->invoke($backend, 'process_settings_form'));
        Sitepulse_Test_WP::$nonce_valid = true;
        $_POST['sitepulse_settings_nonce'] = wp_create_nonce('sitepulse_settings_action');
        Sitepulse_Test_WP::$capabilities = array();
        $this->assertFalse($this->invoke($backend, 'process_settings_form'));
        Sitepulse_Test_WP::$capabilities = array('manage_options' => true);
        $_POST += array('sitepulse_email_blocking_enabled' => 'on', 'sitepulse_email_blocking_mode' => 'all', 'sitepulse_cron_disabled' => 'on', 'sitepulse_recovery_mode_email_addresses' => 'alerts@example.test');
        $this->assertTrue($this->invoke($backend, 'process_settings_form'));
        $settings = Sitepulse_Settings::getInstance()->get_all_settings();
        $this->assertTrue($settings['email_blocking_enabled']);
        $this->assertSame('alerts@example.test', $settings['recovery_mode_email_addresses']);
        $this->assertFalse($settings['external_api_enabled']);
    }

    public function test_calculations_use_real_request_time_then_profiler_then_hook_fallback(): void {
        $backend = new Sitepulse_Backend(new Sitepulse_Plugin());
        $GLOBALS['timestart'] = microtime(true) - .5;
        $this->assertGreaterThanOrEqual(500, $this->invoke($backend, 'calculate_total_load_time', array()));
        $GLOBALS['timestart'] = null;
        $this->assertSame(700.0, $this->invoke($backend, 'calculate_total_load_time', array(array('total' => .7), array())));
        $this->assertNull($this->invoke($backend, 'get_top_slow_item', array(array('total' => 0))));
        $scores = $this->invoke($backend, 'calculate_performance_scores', array(), array(), array('formatted' => '20 MB'), 60, array('error_count' => 12));
        $this->assertSame(75, $scores['memory_score']);
        $this->assertSame(0, $scores['plugins_score']);
        $this->assertSame(0, $scores['error_log_score']);
        $this->assertSame(array('other' => array()), $backend->filter_plugin_name_whitelabel(array('other' => array())));
    }

    /** @dataProvider memoryPercentageScores */
    public function test_memory_score_inverts_percentage_rounds_and_clamps_to_zero_through_one_hundred($formatted_memory, $expected_score): void {
        $backend = new Sitepulse_Backend(new Sitepulse_Plugin());
        $scores = $this->invoke($backend, 'calculate_performance_scores', array(), array(), array('formatted' => $formatted_memory), 0, array());

        $this->assertEquals($expected_score, $scores['memory_score']);
    }

    public static function memoryPercentageScores(): array {
        return array(
            'normal utilization' => array('25%', 75),
            'fractional utilization is rounded' => array('37.5 %', 63),
            'zero utilization' => array('0%', 100),
            'full utilization' => array('100%', 0),
            'negative utilization is clamped' => array('-10%', 100),
            'over-limit utilization is clamped' => array('125%', 0),
        );
    }
}

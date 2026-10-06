<?php

require_once SITEPULSE_PATH . 'inc/api_backend.php';
require_once __DIR__ . '/Support/LocalFilesystem.php';

final class RestCoverageTest extends Sitepulse_Test_Case {
    private function request(array $params = array()): WP_REST_Request {
        $request = new WP_REST_Request('POST', '/sitepulse/v1/test');
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(json_encode($params));
        $request->set_param('_wpnonce', wp_create_nonce('wp_rest'));
        return $request;
    }

    /** @dataProvider sanitizedParameters */
    public function test_parameter_sanitizer_removes_unsafe_content_for_each_supported_type($value, string $type, $expected): void {
        $this->assertSame($expected, sitepulse_sanitize_param($value, $type));
    }

    public static function sanitizedParameters(): array {
        return array(
            'text' => array(" <b>Hello</b>\n world ", 'text', 'Hello world'),
            'unknown defaults to text' => array('<b>Hello</b>', 'unknown', 'Hello'),
            'positive integer' => array('-12', 'int', 12),
            'zero integer' => array('', 'int', 0),
            'unsafe URL' => array('javascript:alert(1)', 'url', ''),
            'safe URL' => array('https://example.test/report', 'url', 'https://example.test/report'),
            'limited HTML' => array('<b>Keep</b><script>drop-tag</script>', 'html', '<b>Keep</b>drop-tag'),
            'nested array' => array(array('title' => '<b>Title</b>', 'items' => array('<i>one</i>')), 'text', array('title' => 'Title', 'items' => array('one'))),
        );
    }

    public function test_enable_savequeries_writes_configuration_through_the_real_plugin(): void {
        $GLOBALS['wp_filesystem'] = new Sitepulse_Test_Local_Filesystem();
        $config = ABSPATH . 'wp-config.php';
        file_put_contents($config, "<?php\ndefine('SAVEQUERIES', false);\n");
        try {
            $this->assertTrue(sitepulse_enable_save_queries($this->request())->get_data()['success']);
            $this->assertStringContainsString("define('SAVEQUERIES', true);", file_get_contents($config));
        } finally {
            unlink($config);
            foreach (glob(wp_upload_dir()['basedir'] . '/sitepulse/wp-config*') ?: array() as $file) {
                if (is_file($file)) { unlink($file); }
            }
        }
    }

    public function test_enable_savequeries_returns_failure_when_current_user_cannot_write_settings(): void {
        Sitepulse_Test_WP::$capabilities['manage_options'] = false;
        $this->assertFalse(sitepulse_enable_save_queries($this->request())->get_data()['success']);
    }

    public function test_profiler_state_is_persisted_without_creating_guest_user_metadata(): void {
        Sitepulse_Test_WP::$user_id = 0;
        sitepulse_set_sp_profiler($this->request(array('sitepulse_profiler_enabled' => 'disabled')));
        do_action('shutdown');
        $this->assertFalse(get_option('sitepulse_profiler_enabled'));
        $this->assertSame(array(), Sitepulse_Test_WP::$user_meta);
    }

    public function test_memory_check_returns_real_memory_data_without_incidental_output(): void {
        ob_start();
        try {
            echo 'incidental output';
            $result = sitepulse_check_memory($this->request())->get_data();
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        $this->assertSame('', $output);
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['memory']['allocated']);
        $this->assertArrayHasKey('percent', $result['memory']);
        $this->assertNotEmpty($result['memory']['formatted']);
    }

    public function test_memory_check_rejects_invalid_nonce_before_collecting_metrics(): void {
        $request = $this->request();
        $request->set_param('_wpnonce', 'invalid');
        ob_start();
        try {
            $result = sitepulse_check_memory($request);
        } finally {
            ob_end_clean();
        }
        $this->assertSame('invalid_nonce', $result->get_error_code());
        $this->assertSame(403, $result->get_error_data()['status']);
    }

    public function test_settings_endpoint_persists_cron_opt_out(): void {
        $this->assertTrue(sitepulse_update_settings($this->request(array('cron_disabled' => true)))->get_data()['success']);
        $this->assertTrue(Sitepulse_Settings::getInstance()->is_cron_disabled());
    }

    public function test_dismissing_onboarding_persists_the_dismissed_state(): void {
        $this->assertTrue(sitepulse_dismiss_onboarding($this->request())->get_data()['success']);
        $this->assertTrue(Sitepulse_Onboarding::getInstance()->is_onboarding_dismissed());
    }

    public function test_plugin_profiler_stats_expose_persisted_rows_and_last_update(): void {
        $stats = array('plugin-a' => array('plugin_name' => 'Plugin A', 'avg_time' => 15.0, 'avg_memory' => 1024));
        update_option('sitepulse_plugin_profiler_stats', $stats);
        update_option('sitepulse_plugin_profiler_enabled', false);
        update_option('sitepulse_plugin_profiler_current_time', '2026-01-01 11:00:00');
        Sitepulse_Plugin_Profiler::init();
        $result = sitepulse_get_plugin_profiler_stats($this->request())->get_data();
        $this->assertSame($stats, $result['stats']);
        $this->assertSame(1, $result['count']);
        $this->assertSame('2026-01-01 11:00:00', $result['last_updated']);
    }

    public function test_manual_plugin_collection_maps_actual_profiler_measurements_and_persists_them(): void {
        Sitepulse_Test_WP::$plugins = array('fixture/plugin.php' => array('Name' => 'Fixture Plugin', 'Version' => '1.0'));
        update_option('active_plugins', array('fixture/plugin.php'));
        update_option(SITEPULSE_PROFILER_ENABLED, false);
        update_option(SITEPULSE_PROFILER_KEY, array('bootstrap' => array(
            'hook' => 'plugin_bootstrap', 'fileline' => 'wp-content/plugins/fixture/plugin.php', 'calls' => 1,
            'total_ms' => 12.5, 'avg_ms' => 12.5, 'max_ms' => 12.5, 'current_load_time' => 12.5,
        )));
        Sitepulse_Profiler::init();
        $result = sitepulse_collect_plugin_data($this->request())->get_data();
        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['count']);
        $this->assertSame(12.5, $result['total_time']);
        $row = reset($result['stats']);
        $this->assertSame('Fixture Plugin', $row['plugin_name']);
        $this->assertSame(12.5, $row['current_load_time']);
        $this->assertSame($result['stats'], get_option('sitepulse_plugin_profiler_stats'));
        $this->assertFalse(Sitepulse_Plugin_Profiler::should_collect_data());
    }

    public function test_vulnerability_normalization_skips_malformed_plugin_and_theme_entries(): void {
        update_option('sitepulse_last_api_request', 1700000000);
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('vulnerabilities' => array(
            'plugins' => array('invalid-plugin' => 'malformed', 'valid-plugin' => array('name' => 'Good Plugin', 'vulnerabilities' => array(array('title' => 'Plugin XSS', 'severity' => 'high')))),
            'theme' => array('valid-theme' => array('name' => 'Good Theme', 'vulnerabilities' => array(array('title' => 'Theme XSS', 'severity' => 'medium'))), 'invalid-theme' => 'malformed'),
        )));
        $result = sitepulse_check_vulnerabilities_api($this->request())->get_data();
        $this->assertSame(array(
            array('name' => 'Plugin (Good Plugin): Plugin XSS', 'version' => 'high'),
            array('name' => 'Theme (Good Theme): Theme XSS', 'version' => 'medium'),
        ), $result['data']['vulnerabilities']);
    }

    public function test_vulnerability_scan_wait_preserves_previous_findings_and_last_success_time(): void {
        update_option('sitepulse_last_api_request', 1700000000);
        update_option('sitepulse_vulnerabilities_list', array(array('name' => 'Previous finding', 'version' => 'high')));
        update_option('sitepulse_last_vulnerability_check', '2026-01-01 09:00:00');
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('vuln_wait' => true, 'message' => 'Your scan is queued'));
        $result = sitepulse_check_vulnerabilities_api($this->request())->get_data();
        $this->assertSame(array('success' => true, 'data' => array('message' => 'Your scan is queued', 'vuln_wait' => true)), $result);
        $this->assertSame(array(array('name' => 'Previous finding', 'version' => 'high')), get_option('sitepulse_vulnerabilities_list'));
        $this->assertSame('2026-01-01 09:00:00', get_option('sitepulse_last_vulnerability_check'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider unavailableHandlers
     */
    public function test_rest_handlers_report_unavailable_dependencies_without_fatal_errors(string $handler, string $error): void {
        $autoloaders = spl_autoload_functions();
        foreach ($autoloaders as $autoloader) { spl_autoload_unregister($autoloader); }
        ob_start();
        try {
            $result = $handler($this->request());
        } finally {
            ob_end_clean();
            foreach ($autoloaders as $autoloader) { spl_autoload_register($autoloader); }
        }
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertSame($error, $result->get_error_code());
        $this->assertSame(500, $result->get_error_data()['status']);
    }

    public static function unavailableHandlers(): array {
        return array(
            'memory checker' => array('sitepulse_check_memory', 'missing_class'),
            'memory information' => array('sitepulse_get_memory_info', 'missing_class'),
            'plugin stats' => array('sitepulse_get_plugin_profiler_stats', 'missing_class'),
            'plugin collection' => array('sitepulse_collect_plugin_data', 'missing_class'),
            'vulnerability service' => array('sitepulse_check_vulnerabilities_api', 'service_unavailable'),
            'AI request service' => array('sitepulse_request_ai_diagnostic_api', 'service_unavailable'),
            'AI status service' => array('sitepulse_check_ai_diagnostic_status_api', 'service_unavailable'),
        );
    }
}

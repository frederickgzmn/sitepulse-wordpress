<?php

/** Additional real service behaviors, including failure and legacy-format boundaries. */
final class ApiCoverageTest extends Sitepulse_Test_Case {
    private function payload(int $index = 0): array {
        return json_decode(Sitepulse_Test_WP::$http_requests[$index]['args']['body'], true);
    }

    public function test_oversized_site_report_keeps_the_fifty_most_recent_resource_entries(): void {
        $resources = array();
        for ($i = 0; $i < 60; $i++) {
            $resources[] = array('ordinal' => $i, 'details' => str_repeat('x', 40000));
        }
        update_option(SITEPULSE_PROFILER_KEY, $resources);
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array());
        $this->assertTrue(Sitepulse_Api_Service::send_website_data()['success']);
        $payload = $this->payload();
        $this->assertCount(50, $payload['resources']);
        $this->assertSame(10, $payload['resources'][0]['ordinal']);
        $this->assertSame(59, $payload['resources'][49]['ordinal']);
        $this->assertCount(0, $payload['api_requests']);
    }

    public function test_unencodable_resource_data_fails_without_outbound_request(): void {
        $resource = fopen('php://memory', 'r+');
        try {
            update_option(SITEPULSE_PROFILER_KEY, array('invalid' => $resource));
            $result = Sitepulse_Api_Service::send_website_data();
            $this->assertFalse($result['success']);
            $this->assertStringContainsString('encode', $result['message']);
            $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
        } finally {
            fclose($resource);
        }
    }

    /** @dataProvider preparationFailures */
    public function test_preparation_exception_returns_failure_without_sending(string $method, string $option): void {
        add_filter('pre_option_' . $option, static function () { throw new RuntimeException('Storage unavailable'); });
        $result = Sitepulse_Api_Service::$method();
        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['message']);
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public static function preparationFailures(): array {
        return array(
            'site metrics' => array('send_website_data', 'sitepulse_profiler_stats'),
            'vulnerability license' => array('check_vulnerabilities', 'sitepulse_license_key'),
            'diagnostic state' => array('request_ai_diagnostic', 'sitepulse_last_api_request'),
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_invalid_testing_endpoint_is_rejected_before_network(): void {
        define('SITEPULSE_TESTING_API_ENDPOINT', 'not-a-valid-url');
        $result = Sitepulse_Api_Service::send_website_data();
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('endpoint', $result['message']);
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_testing_endpoint_override_receives_site_report(): void {
        define('SITEPULSE_TESTING_API_ENDPOINT', 'https://fixture.example.test/site-report');
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('license_key' => 'fixture-license'));
        $this->assertTrue(Sitepulse_Api_Service::send_website_data()['success']);
        $this->assertSame('https://fixture.example.test/site-report', Sitepulse_Test_WP::$http_requests[0]['url']);
        $this->assertSame('fixture-license', get_option('sitepulse_license_key'));
    }

    public function test_website_metadata_includes_folder_and_single_file_plugins_but_omits_missing_files(): void {
        $folder = WP_PLUGIN_DIR . '/api-fixture';
        mkdir($folder);
        file_put_contents($folder . '/plugin.php', '<?php');
        file_put_contents(WP_PLUGIN_DIR . '/api-single.php', '<?php');
        try {
            update_option('active_plugins', array('api-fixture/plugin.php', 'api-single.php', 'missing/missing.php'));
            Sitepulse_Test_WP::$plugins = array(
                'api-fixture/plugin.php' => array('Name' => 'Folder Plugin', 'Version' => '2.5'),
                'api-single.php' => array('Name' => 'Single Plugin', 'Version' => '1.2'),
            );
            Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array());
            $this->assertTrue(Sitepulse_Api_Service::send_website_data()['success']);
            $this->assertSame(array(
                array('name' => 'Folder Plugin', 'version' => '2.5', 'slug' => 'api-fixture'),
                array('name' => 'Single Plugin', 'version' => '1.2', 'slug' => 'api-single'),
            ), $this->payload()['website_info']['plugins']);
        } finally {
            unlink($folder . '/plugin.php');
            rmdir($folder);
            unlink(WP_PLUGIN_DIR . '/api-single.php');
        }
    }

    public function test_website_metadata_includes_systemic_cron_failure_and_retained_error_count(): void {
        Sitepulse_Test_WP::$cron = array(time() - 7200 => array('overdue_hook' => array()));
        update_option('sitepulse_error_log', array(
            'errors' => array(array('message' => 'Recent warning', 'timestamp' => current_time('timestamp') - 30, 'is_fatal' => false)),
            'total_count' => 1, 'last_error_date' => current_time('mysql'),
        ));
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array());
        Sitepulse_Api_Service::send_website_data();
        $info = $this->payload()['website_info'];
        $this->assertSame(1, $info['error_log_count']);
        $this->assertTrue($info['cron_failure']['failing']);
        $this->assertStringContainsString('2 hours', $info['cron_failure']['description']);
    }

    public function test_host_ip_uses_local_site_resolution_when_server_address_is_unavailable(): void {
        unset($_SERVER['SERVER_ADDR']);
        Sitepulse_Test_WP::$home_url = 'http://localhost';
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array());
        $this->assertTrue(Sitepulse_Api_Service::send_website_data()['success']);
        $this->assertSame('127.0.0.1', $this->payload()['ip_address']);
    }

    public function test_internal_vulnerability_retry_respects_external_api_opt_out(): void {
        update_option('sitepulse_settings', array('external_api_enabled' => false));
        $method = new ReflectionMethod(Sitepulse_Api_Service::class, 'make_vulnerability_api_request');
        if (PHP_VERSION_ID < 80100) { $method->setAccessible(true); }
        $result = $method->invoke(null, array('domain' => 'https://example.test'), true);
        $this->assertFalse($result['success']);
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    /** @dataProvider whitelabelResponses */
    public function test_report_response_updates_or_clears_remote_branding(array $body, bool $enabled): void {
        Sitepulse_Whitelabel_Service::store_whitelabel_data(array('enabled' => true, 'plugin_name' => 'Old Branding'));
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response($body);
        $this->assertTrue(Sitepulse_Api_Service::send_website_data()['success']);
        $this->assertSame($enabled, Sitepulse_Whitelabel_Service::is_whitelabel_enabled());
        if ($enabled) {
            $this->assertSame('Agency Monitor', Sitepulse_Whitelabel_Service::get_plugin_name());
        }
    }

    public static function whitelabelResponses(): array {
        return array(
            'enabled' => array(array('whitelabel' => array('enabled' => true, 'plugin_name' => 'Agency Monitor')), true),
            'disabled' => array(array('whitelabel' => array('enabled' => false)), false),
            'omitted' => array(array(), false),
        );
    }

    public function test_missing_reports_return_null_instead_of_false_or_invalid_json(): void {
        $this->assertNull(Sitepulse_Api_Service::get_pagespeed_report());
        $this->assertNull(Sitepulse_Api_Service::get_ai_diagnostic_report());
    }

    public function test_empty_reports_from_server_do_not_erase_previous_results(): void {
        update_option('sitepulse_pagespeed_report', 'previous-pagespeed');
        update_option('sitepulse_ai_diagnostic_report', 'previous-diagnostic');
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('pagespeed_report' => array(), 'ai_diagnostic_report' => '', 'license_key' => ''));
        $this->assertTrue(Sitepulse_Api_Service::send_website_data()['success']);
        $this->assertSame('previous-pagespeed', Sitepulse_Api_Service::get_pagespeed_report());
        $this->assertSame('previous-diagnostic', Sitepulse_Api_Service::get_ai_diagnostic_report());
    }

    public function test_plain_text_reports_are_preserved_without_double_encoding(): void {
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('pagespeed_report' => 'Page report', 'ai_diagnostic_report' => 'AI report'));
        Sitepulse_Api_Service::send_website_data();
        $this->assertSame('Page report', Sitepulse_Api_Service::get_pagespeed_report(false));
        $this->assertSame('AI report', Sitepulse_Api_Service::get_ai_diagnostic_report(false));
    }

    /** @dataProvider vulnerabilityFailures */
    public function test_failed_vulnerability_response_is_not_reported_as_clean_scan($response): void {
        update_option('sitepulse_last_api_request', 1700000000);
        Sitepulse_Test_WP::$http_queue[] = $response;
        $result = Sitepulse_Api_Service::check_vulnerabilities();
        $this->assertFalse($result['success']);
        $this->assertArrayNotHasKey('vulnerabilities', $result);
        $this->assertCount(1, Sitepulse_Test_WP::$http_requests);
    }

    public static function vulnerabilityFailures(): array {
        return array(
            'transport' => array(new WP_Error('http_request_failed', 'Connection failed')),
            'unavailable server' => array(Sitepulse_Test_WP::response(array('message' => 'Unavailable'), 503)),
            'invalid JSON' => array(Sitepulse_Test_WP::response('<html>Unexpected content</html>')),
        );
    }

    /** @dataProvider vulnerabilityLicenseFormats */
    public function test_vulnerability_scan_rotates_legacy_and_full_license_metadata($license, array $expected): void {
        update_option('sitepulse_last_api_request', 1700000000);
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('new_license' => $license, 'vulnerabilities' => array()));
        $this->assertTrue(Sitepulse_Api_Service::check_vulnerabilities()['success']);
        foreach ($expected as $key => $value) {
            $this->assertSame($value, get_option($key));
        }
    }

    public function test_queued_vulnerability_scan_without_message_returns_a_useful_wait_state(): void {
        update_option('sitepulse_last_api_request', 1700000000);
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('vuln_wait' => true));
        $result = Sitepulse_Api_Service::check_vulnerabilities();
        $this->assertTrue($result['success']);
        $this->assertTrue($result['vuln_wait']);
        $this->assertStringContainsString('pending', $result['message']);
        $this->assertArrayNotHasKey('vulnerabilities', $result);
    }

    public static function vulnerabilityLicenseFormats(): array {
        return array(
            'legacy string' => array('<b>new-key</b>', array('sitepulse_license_key' => 'new-key')),
            'object metadata' => array(array('license_key' => 'new-key', 'type' => '<b>free</b>', 'status' => 'active'), array('sitepulse_license_key' => 'new-key', 'sitepulse_license_type' => 'free', 'sitepulse_license_status' => 'active')),
        );
    }

    /** @dataProvider unencodableRequests */
    public function test_unencodable_license_prevents_scan_and_diagnostic_requests(string $method): void {
        update_option('sitepulse_last_api_request', 1700000000);
        $resource = fopen('php://memory', 'r+');
        try {
            update_option('sitepulse_license_key', $resource);
            $result = Sitepulse_Api_Service::$method();
            $this->assertFalse($result['success']);
            $this->assertStringContainsString('encode', $result['message']);
            $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
        } finally {
            fclose($resource);
        }
    }

    public static function unencodableRequests(): array {
        return array('vulnerabilities' => array('check_vulnerabilities'), 'AI diagnostic' => array('request_ai_diagnostic'));
    }

    public function test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list(): void {
        update_option('sitepulse_last_api_request', 1700000000);
        update_option('sitepulse_vulnerabilities_list', 'invalid');
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('status' => 'pending'));
        Sitepulse_Api_Service::request_ai_diagnostic();
        $this->assertSame(array(), $this->payload()['metrics']['vulnerabilities']);
    }

    public function test_ai_poll_refreshes_queue_position_while_remaining_pending(): void {
        update_option('sitepulse_ai_diagnostic_status', 'pending');
        update_option('sitepulse_ai_diagnostic_report_id', 'report-42');
        set_transient('sitepulse_ai_diagnostic_queue_position', 8, 600);
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('success' => true, 'status' => 'pending', 'queue_position' => 3));
        $this->assertSame('pending', Sitepulse_Api_Service::check_ai_diagnostic_status()['status']);
        $this->assertSame(3, get_transient('sitepulse_ai_diagnostic_queue_position'));
        $this->assertSame(600, Sitepulse_Test_WP::$transient_expirations['sitepulse_ai_diagnostic_queue_position']);
    }

    public function test_failed_ai_response_preserves_error_details_and_retry_permission(): void {
        update_option('sitepulse_last_api_request', 1700000000);
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('success' => false, 'status' => 'failed', 'message' => 'Diagnostic failed', 'error_message' => 'No metrics supplied', 'can_retry' => true));
        $result = Sitepulse_Api_Service::request_ai_diagnostic();
        $this->assertFalse($result['success']);
        $this->assertSame('failed', $result['status']);
        $this->assertSame('No metrics supplied', $result['error_message']);
        $this->assertTrue($result['can_retry']);
        $this->assertFalse(get_option('sitepulse_ai_diagnostic_requested_at'));
    }

    public function test_ai_server_error_is_returned_without_replacing_stored_report(): void {
        update_option('sitepulse_last_api_request', 1700000000);
        update_option('sitepulse_ai_diagnostic_report', 'previous-report');
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('message' => 'Unavailable'), 503);
        $result = Sitepulse_Api_Service::request_ai_diagnostic();
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('503', $result['message']);
        $this->assertSame('previous-report', get_option('sitepulse_ai_diagnostic_report'));
    }

    public function test_ai_request_status_snapshot_does_not_make_network_request(): void {
        update_option('sitepulse_ai_diagnostic_status', 'processing');
        update_option('sitepulse_ai_diagnostic_report_id', 'report-42');
        update_option('sitepulse_ai_diagnostic_requested_at', '2026-01-01 12:00:00');
        $this->assertSame(array('status' => 'processing', 'report_id' => 'report-42', 'requested_at' => '2026-01-01 12:00:00'), Sitepulse_Api_Service::get_ai_diagnostic_status());
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }
}

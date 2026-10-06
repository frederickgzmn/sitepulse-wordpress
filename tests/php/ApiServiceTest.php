<?php

/** Exercises the real API service; only WordPress HTTP and storage are doubled. */
final class ApiServiceTest extends Sitepulse_Test_Case {
    protected function setUp(): void {
        parent::setUp();
        $_SERVER['SERVER_ADDR'] = '192.0.2.25';
    }

    private function queue(array $body, int $status = 200): void {
        Sitepulse_Test_WP::$http_queue[] = array(
            'headers' => array('content-type' => 'application/json'),
            'body' => json_encode($body),
            'response' => array('code' => $status, 'message' => 'Fixture response'),
            'cookies' => array(), 'filename' => null,
        );
    }

    private function payload(int $index = 0): array {
        return json_decode(Sitepulse_Test_WP::$http_requests[$index]['args']['body'], true);
    }

    private function registered(): void {
        update_option('sitepulse_last_api_request', 1700000000);
        update_option('sitepulse_license_key', 'existing-license');
    }

    private function pending(): void {
        $this->registered();
        update_option('sitepulse_ai_diagnostic_status', 'pending');
        update_option('sitepulse_ai_diagnostic_report_id', 'report-42');
        update_option('sitepulse_ai_diagnostic_requested_at', '2026-10-01 10:15:00');
    }

    public function test_site_report_sends_identity_metrics_and_only_ten_recent_http_events(): void {
        update_option(SITEPULSE_PROFILER_KEY, array('init' => array('elapsed' => 0.25)));
        update_option(SITEPULSE_CURL_API_KEY, range(1, 14));
        update_option('sitepulse_license_key', 'existing-license');
        $this->queue(array('license_key' => 'existing-license'));

        $result = Sitepulse_Api_Service::send_website_data();

        $this->assertTrue($result['success']);
        $request = Sitepulse_Test_WP::$http_requests[0];
        $this->assertSame('https://api.sitepulse.me/api/website/website-request', $request['url']);
        $this->assertSame('application/json', $request['args']['headers']['Content-Type']);
        $this->assertTrue($request['args']['sslverify']);
        $this->assertSame(30, $request['args']['timeout']);
        $payload = $this->payload();
        $this->assertSame(array('init' => array('elapsed' => 0.25)), $payload['resources']);
        $this->assertSame(array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14), $payload['api_requests']);
        $this->assertSame('existing-license', $payload['license_key']);
        $this->assertSame('192.0.2.25', $payload['ip_address']);
        $this->assertSame(home_url(), $payload['domain']);
        $this->assertArrayHasKey('theme', $payload['website_info']);
        $this->assertArrayHasKey('wp_version', $payload['website_info']);
        $this->assertSame(current_time('timestamp'), get_option('sitepulse_last_api_request'));
    }

    public function test_corrupt_metrics_become_empty_collections_and_unlicensed_payload_omits_license(): void {
        update_option(SITEPULSE_PROFILER_KEY, 'corrupt');
        update_option(SITEPULSE_CURL_API_KEY, false);
        $this->queue(array());
        $this->assertTrue(Sitepulse_Api_Service::send_website_data()['success']);
        $this->assertSame(array(), $this->payload()['resources']);
        $this->assertSame(array(), $this->payload()['api_requests']);
        $this->assertArrayNotHasKey('license_key', $this->payload());
    }

    /** @dataProvider licenseResponses */
    public function test_license_response_formats_replace_stored_key(array $body, string $expected): void {
        update_option('sitepulse_license_key', 'old-key');
        $this->queue($body);
        $result = Sitepulse_Api_Service::send_website_data();
        $this->assertTrue($result['success']);
        $this->assertSame($expected, get_option('sitepulse_license_key'));
        $this->assertSame($expected, $result['license_key']);
    }

    public static function licenseResponses(): array {
        return array(
            'new object takes precedence' => array(array('new_license' => array('license_key' => '<b>new-key</b>'), 'license_key' => 'stale'), 'new-key'),
            'direct key' => array(array('license_key' => 'direct-key'), 'direct-key'),
            'legacy new license string' => array(array('new_license' => 'legacy-new'), 'legacy-new'),
            'legacy license string' => array(array('license' => 'legacy-key'), 'legacy-key'),
        );
    }

    public function test_success_persists_license_metadata_and_decodable_reports(): void {
        $this->queue(array(
            'new_license' => array('license_key' => 'new-key', 'type' => '<b>free</b>', 'status' => 'active', 'generated_at' => '2026-10-01'),
            'pagespeed_report' => array('score' => 88),
            'ai_diagnostic_report' => array('summary' => 'Reduce requests'),
        ));
        Sitepulse_Api_Service::send_website_data();
        $this->assertSame('free', get_option('sitepulse_license_type'));
        $this->assertSame('active', get_option('sitepulse_license_status'));
        $this->assertSame('2026-10-01', get_option('sitepulse_license_generated_at'));
        $this->assertSame(array('score' => 88), Sitepulse_Api_Service::get_pagespeed_report());
        $this->assertSame(array('summary' => 'Reduce requests'), Sitepulse_Api_Service::get_ai_diagnostic_report());
        $this->assertSame('{"score":88}', Sitepulse_Api_Service::get_pagespeed_report(false));
    }

    /** @dataProvider reportValues */
    public function test_report_readers_preserve_legacy_text_and_decode_json($stored, $expected): void {
        update_option('sitepulse_pagespeed_report', $stored);
        update_option('sitepulse_ai_diagnostic_report', $stored);
        $this->assertSame($expected, Sitepulse_Api_Service::get_pagespeed_report());
        $this->assertSame($expected, Sitepulse_Api_Service::get_ai_diagnostic_report());
    }

    public static function reportValues(): array {
        return array('plain text' => array('Legacy report', 'Legacy report'), 'array' => array(array('score' => 9), array('score' => 9)), 'json' => array('{"score":9}', array('score' => 9)));
    }

    /** @dataProvider transportErrors */
    public function test_site_report_transport_errors_do_not_replace_existing_license(string $message, string $fragment): void {
        update_option('sitepulse_license_key', 'keep-me');
        Sitepulse_Test_WP::$http_queue[] = new WP_Error('http_request_failed', $message);
        $result = Sitepulse_Api_Service::send_website_data();
        $this->assertFalse($result['success']);
        $this->assertStringContainsString($fragment, $result['message']);
        $this->assertSame('keep-me', get_option('sitepulse_license_key'));
    }

    public static function transportErrors(): array {
        return array('timeout' => array('Operation timed out', 'timed out'), 'dns' => array('Could not resolve host', 'connect'), 'tls' => array('SSL certificate expired', 'SSL'), 'other' => array('Connection reset', 'Connection reset'));
    }

    public function test_site_report_rejects_server_errors_and_invalid_json_without_overwriting_reports(): void {
        update_option('sitepulse_pagespeed_report', 'keep-report');
        $this->queue(array('pagespeed_report' => 'bad-report'), 503);
        $this->assertFalse(Sitepulse_Api_Service::send_website_data()['success']);
        $this->queue(array());
        Sitepulse_Test_WP::$http_queue[0]['body'] = '<html>Proxy error</html>';
        $this->assertFalse(Sitepulse_Api_Service::send_website_data()['success']);
        $this->assertSame('keep-report', get_option('sitepulse_pagespeed_report'));
    }

    /** @dataProvider largePayloads */
    public function test_large_reports_receive_bounded_longer_timeouts(int $bytes, int $timeout): void {
        update_option(SITEPULSE_PROFILER_KEY, array('init' => str_repeat('x', $bytes)));
        $this->queue(array());
        Sitepulse_Api_Service::send_website_data();
        $this->assertSame($timeout, Sitepulse_Test_WP::$http_requests[0]['args']['timeout']);
    }

    public static function largePayloads(): array {
        return array('over half MB' => array(550000, 45), 'over MB' => array(1100000, 60));
    }

    public function test_vulnerability_scan_includes_registered_license_and_returns_findings(): void {
        $this->registered();
        $findings = array('wordpress_core' => array(array('title' => 'Stored XSS', 'severity' => 'high')));
        $this->queue(array('vulnerabilities' => $findings, 'new_license' => array('license_key' => 'rotated', 'type' => 'free')));
        $result = Sitepulse_Api_Service::check_vulnerabilities();
        $this->assertTrue($result['success']);
        $this->assertSame($findings, $result['vulnerabilities']);
        $this->assertSame('existing-license', $this->payload()['license_key']);
        $this->assertSame('rotated', get_option('sitepulse_license_key'));
        $this->assertSame('https://api.sitepulse.me/api/website/website-vulnerabilities', Sitepulse_Test_WP::$http_requests[0]['url']);
    }

    public function test_vulnerability_invalid_license_registers_and_retries_once_with_new_license(): void {
        $this->registered();
        $this->queue(array('message' => 'Invalid license key'), 401);
        $this->queue(array('new_license' => array('license_key' => 'refreshed')));
        $this->queue(array('vulnerabilities' => array()));
        $this->assertTrue(Sitepulse_Api_Service::check_vulnerabilities()['success']);
        $this->assertCount(3, Sitepulse_Test_WP::$http_requests);
        $this->assertSame('existing-license', $this->payload(0)['license_key']);
        $this->assertSame('refreshed', $this->payload(2)['license_key']);
    }

    public function test_vulnerability_retry_does_not_loop_when_refreshed_license_is_rejected(): void {
        $this->registered();
        $this->queue(array(), 401);
        $this->queue(array('license_key' => 'refreshed'));
        $this->queue(array(), 401);
        $this->assertFalse(Sitepulse_Api_Service::check_vulnerabilities()['success']);
        $this->assertCount(3, Sitepulse_Test_WP::$http_requests);
    }

    public function test_disabled_external_api_prevents_registered_vulnerability_and_ai_requests(): void {
        $this->registered();
        update_option('sitepulse_settings', array('external_api_enabled' => false));
        $this->assertFalse(Sitepulse_Api_Service::check_vulnerabilities()['success']);
        $this->assertFalse(Sitepulse_Api_Service::request_ai_diagnostic()['success']);
        $this->assertFalse(Sitepulse_Api_Service::check_ai_diagnostic_status()['success']);
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public function test_vulnerability_rate_limit_does_not_trigger_registration_retry(): void {
        $this->registered();
        $this->queue(array('message' => 'Rate limit'), 429);
        $this->assertFalse(Sitepulse_Api_Service::check_vulnerabilities()['success']);
        $this->assertCount(1, Sitepulse_Test_WP::$http_requests);
    }

    public function test_ai_request_registers_first_and_persists_pending_state_and_queue_position(): void {
        update_option(SITEPULSE_CURL_API_KEY, range(1, 25));
        update_option(SITEPULSE_PROFILER_KEY, array('init' => array('elapsed' => 0.1)));
        update_option('sitepulse_vulnerabilities_list', array(array('name' => 'XSS', 'version' => 'high')));
        $this->queue(array('license_key' => 'issued-license'));
        $this->queue(array('success' => true, 'status' => 'pending', 'ai_diagnostic_report_id' => 'report-42', 'queue_position' => 7));
        $result = Sitepulse_Api_Service::request_ai_diagnostic(true);
        $this->assertTrue($result['success']);
        $payload = $this->payload(1);
        $this->assertSame('issued-license', $payload['license_key']);
        $this->assertTrue($payload['force_new']);
        $this->assertStringNotContainsString('://', $payload['domain']);
        $this->assertSame(array(6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25), $payload['metrics']['api_requests']);
        $this->assertSame(array('init' => array('elapsed' => 0.1)), $payload['metrics']['hooks']);
        $this->assertSame(array(array('name' => 'XSS', 'version' => 'high')), $payload['metrics']['vulnerabilities']);
        $this->assertSame('pending', get_option('sitepulse_ai_diagnostic_status'));
        $this->assertSame('report-42', get_option('sitepulse_ai_diagnostic_report_id'));
        $this->assertSame(current_time('mysql'), get_option('sitepulse_ai_diagnostic_requested_at'));
        $this->assertSame(7, get_transient('sitepulse_ai_diagnostic_queue_position'));
    }

    public function test_completed_ai_poll_stores_report_clears_queue_and_retains_request_time(): void {
        $this->pending();
        set_transient('sitepulse_ai_diagnostic_queue_position', 7, 600);
        $this->queue(array('success' => true, 'status' => 'completed', 'diagnostic_result' => array('summary' => 'Use caching')));
        $result = Sitepulse_Api_Service::check_ai_diagnostic_status();
        $this->assertSame('completed', get_option('sitepulse_ai_diagnostic_status'));
        $this->assertSame(array('summary' => 'Use caching'), Sitepulse_Api_Service::get_ai_diagnostic_report());
        $this->assertFalse(get_transient('sitepulse_ai_diagnostic_queue_position'));
        $this->assertSame('2026-10-01 10:15:00', $result['requested_at']);
        $this->assertFalse($this->payload()['force_new']);
        $this->assertArrayNotHasKey('metrics', $this->payload());
    }

    public function test_completed_ai_request_stores_report_without_leaving_queue_position(): void {
        $this->registered();
        $this->queue(array('status' => 'completed', 'queue_position' => 1, 'diagnostic_result' => array('summary' => 'Done')));
        $this->assertTrue(Sitepulse_Api_Service::request_ai_diagnostic()['success']);
        $this->assertSame(array('summary' => 'Done'), Sitepulse_Api_Service::get_ai_diagnostic_report());
        $this->assertFalse(get_transient('sitepulse_ai_diagnostic_queue_position'));
    }

    /** @dataProvider cachedDiagnosticStates */
    public function test_terminal_and_empty_ai_states_are_served_without_network($status, $report, string $expected, bool $success): void {
        if ($status !== null) {
            update_option('sitepulse_ai_diagnostic_status', $status);
            update_option('sitepulse_ai_diagnostic_report_id', 'report-42');
        }
        if ($report !== null) {
            update_option('sitepulse_ai_diagnostic_report', json_encode($report));
        }
        $result = Sitepulse_Api_Service::check_ai_diagnostic_status();
        $this->assertSame($expected, $result['status']);
        $this->assertSame($success, $result['success']);
        if ($expected === 'completed') {
            $this->assertSame($report, $result['diagnostic_result']);
        }
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public static function cachedDiagnosticStates(): array {
        return array('empty' => array(null, null, 'none', true), 'legacy report' => array(null, array('score' => 8), 'completed', true), 'completed' => array('completed', array('score' => 9), 'completed', true), 'failed' => array('failed', null, 'failed', false));
    }

    public function test_transient_ai_transport_failure_preserves_pending_state_for_retry(): void {
        $this->pending();
        Sitepulse_Test_WP::$http_queue[] = new WP_Error('http_request_failed', 'Connection timed out');
        $result = Sitepulse_Api_Service::check_ai_diagnostic_status();
        $this->assertFalse($result['success']);
        $this->assertSame('pending', $result['status']);
        $this->assertSame('pending', get_option('sitepulse_ai_diagnostic_status'));
    }

    /** @dataProvider aiClientErrors */
    public function test_ai_client_error_preserves_processing_state(int $status): void {
        $this->pending();
        update_option('sitepulse_ai_diagnostic_status', 'processing');
        $this->queue(array('message' => 'Try later'), $status);
        $result = Sitepulse_Api_Service::check_ai_diagnostic_status();
        $this->assertFalse($result['success']);
        $this->assertSame('processing', $result['status']);
        $this->assertSame('processing', get_option('sitepulse_ai_diagnostic_status'));
    }

    public static function aiClientErrors(): array {
        return array('rate limit' => array(429), 'bad request' => array(400), 'forbidden' => array(403));
    }

    public function test_ai_invalid_license_refreshes_and_retries_with_new_key(): void {
        $this->pending();
        $this->queue(array('message' => 'Invalid license key'), 401);
        $this->queue(array('license_key' => 'refreshed'));
        $this->queue(array('status' => 'processing', 'success' => true));
        $result = Sitepulse_Api_Service::check_ai_diagnostic_status();
        $this->assertSame('processing', $result['status']);
        $this->assertSame('refreshed', $this->payload(2)['license_key']);
        $this->assertCount(3, Sitepulse_Test_WP::$http_requests);
    }

    public function test_ai_invalid_json_fails_without_replacing_a_previous_report(): void {
        $this->pending();
        update_option('sitepulse_ai_diagnostic_report', 'previous');
        $this->queue(array());
        Sitepulse_Test_WP::$http_queue[0]['body'] = 'invalid-json';
        $result = Sitepulse_Api_Service::check_ai_diagnostic_status();
        $this->assertFalse($result['success']);
        $this->assertSame('previous', get_option('sitepulse_ai_diagnostic_report'));
    }

    public function test_clearing_ai_request_keeps_the_completed_report_available(): void {
        $this->pending();
        update_option('sitepulse_ai_diagnostic_report', '{"summary":"previous"}');
        Sitepulse_Api_Service::clear_ai_diagnostic_request();
        $this->assertFalse(get_option('sitepulse_ai_diagnostic_status'));
        $this->assertFalse(get_option('sitepulse_ai_diagnostic_report_id'));
        $this->assertFalse(get_option('sitepulse_ai_diagnostic_requested_at'));
        $this->assertSame(array('summary' => 'previous'), Sitepulse_Api_Service::check_ai_diagnostic_status()['diagnostic_result']);
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }
}

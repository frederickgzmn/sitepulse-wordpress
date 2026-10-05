<?php

/** Regressions for report retries, first registration, and external API opt-out. */
require_once SITEPULSE_PATH . 'inc/ai_diagnostic_cron.php';

final class ApiRegressionTest extends Sitepulse_Test_Case {
    public function test_failed_site_report_must_not_mark_last_successful_request_time(): void {
        Sitepulse_Test_WP::$http_queue[] = new WP_Error('http_request_failed', 'Connection timed out');

        $result = Sitepulse_Api_Service::send_website_data();

        $this->assertFalse($result['success']);
        $this->assertFalse(get_option('sitepulse_last_api_request'), 'Failed reports must not delay the cron fallback by recording a successful send time.');
    }

    public function test_first_vulnerability_scan_must_use_newly_issued_registration_license(): void {
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('license_key' => 'newly-issued-license'));
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('vulnerabilities' => array()));

        Sitepulse_Api_Service::check_vulnerabilities();

        $this->assertCount(2, Sitepulse_Test_WP::$http_requests);
        $scan = json_decode(Sitepulse_Test_WP::$http_requests[1]['args']['body'], true);
        $this->assertSame('newly-issued-license', $scan['license_key'], 'The initial scan must use the key returned by the immediately preceding registration.');
    }

    public function test_temporary_ai_transport_failure_must_preserve_pending_polling_schedule(): void {
        update_option('sitepulse_ai_diagnostic_status', 'pending');
        update_option('sitepulse_ai_diagnostic_report_id', 'report-1');
        Sitepulse_AI_Diagnostic_Cron::schedule_status_check();
        Sitepulse_Test_WP::$http_queue[] = new WP_Error('http_request_failed', 'Connection timed out');

        Sitepulse_AI_Diagnostic_Cron::check_status_callback();

        $this->assertSame('pending', get_option('sitepulse_ai_diagnostic_status'));
        $this->assertTrue(Sitepulse_AI_Diagnostic_Cron::is_scheduled(), 'A transient connection failure must allow the next scheduled poll to retry.');
    }

    public function test_external_api_opt_out_must_prevent_registration_on_first_vulnerability_scan(): void {
        update_option('sitepulse_settings', array('external_api_enabled' => false));
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('license_key' => 'unexpected-registration'));

        $result = Sitepulse_Api_Service::check_vulnerabilities();

        $this->assertFalse($result['success']);
        $this->assertCount(0, Sitepulse_Test_WP::$http_requests, 'Opting out of external API collection must prevent every outbound request, including first-time registration.');
    }
}

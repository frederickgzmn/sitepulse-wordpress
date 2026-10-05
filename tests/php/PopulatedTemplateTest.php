<?php
require_once __DIR__ . '/Support/TemplateData.php';
require_once __DIR__ . '/Support/Database.php';
/** @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class PopulatedTemplateTest extends Sitepulse_Test_Case {
    private function render($file, array $data): string {
        extract($data, EXTR_SKIP); ob_start();
        try { require SITEPULSE_PATH . 'templates/' . $file . '.php'; return ob_get_contents(); }
        finally { ob_end_clean(); }
    }
    public static function activityViews(): array {
        return array(array('backend/resource_load', 'Sample 0'), array('backend/curl_api', 'SitePulse (API)'), array('easy/views/resource-load', 'sample-0'), array('easy/views/api-monitor', 'api.example.test'), array('easy/views/performance', 'Fixture Plugin 10'));
    }
    /** @dataProvider activityViews */
    public function test_populated_activity_views_show_samples_and_measurements($view, $expected): void {
        $data = sitepulse_test_seed_template_options();
        $html = $this->render($view, $data);
        $this->assertStringContainsString($expected, $html);
        $this->assertStringContainsString('ms', $html);
        if ($view === 'easy/views/api-monitor') { $this->assertStringNotContainsString('token=secret', $html); }
        if ($view === 'easy/views/performance') { $this->assertStringNotContainsString('Fixture Plugin 11', $html); }
    }
    public function test_resource_view_accepts_old_records_without_activity_identity(): void {
        $data = sitepulse_test_seed_template_options(); unset($data['stats'][0]['key']);
        $html = $this->render('easy/views/resource-load', $data);
        $this->assertStringContainsString('Unknown', $html);
        $this->assertStringContainsString('Inactive since', $html);
    }
    public function test_active_record_and_sidebar_error_badge_reflect_current_state(): void {
        $data = sitepulse_test_seed_template_options();
        $this->setStaticProperty(Sitepulse_Profiler::class, 'stats', array_column($data['stats'], null, 'key'));
        $html = $this->render('easy/views/resource-load', $data);
        $this->assertStringContainsString('sp-badge-success', $html);
        $this->assertMatchesRegularExpression('/sp-badge-success[^>]*>\s*Active/', $html);
        update_option('sitepulse_error_log', array('errors' => array(array('message' => 'Fixture error', 'timestamp' => time(), 'date' => gmdate('Y-m-d H:i:s'))), 'total_count' => 1));
        $html = $this->render('easy/partials/sidebar', $data);
        $this->assertStringContainsString('sp-nav-badge">1', $html);
        $this->assertStringContainsString('only-logo.png', $html);
    }
    public function test_security_view_renders_severities_remediation_and_escaped_stack_frames(): void {
        $data = sitepulse_test_seed_template_options();
        $data['fatal_count'] = 1;
        $data['vulnerabilities'] = array(
            array('title' => 'Unsafe <script>', 'severity' => 'critical', 'plugin_name' => 'Fixture', 'cve' => 'CVE-TEST', 'cvss_score' => '9.9', 'description' => str_repeat('description ', 20), 'patched' => true, 'patched_versions' => array('2.0'), 'references' => array('https://example.test/advisory')),
            array('title' => 'Upgrade needed', 'severity' => 'medium', 'remediation' => str_repeat('Update now ', 20)), array('title' => 'Low warning', 'severity' => 'low'), array('title' => 'High warning', 'severity' => 'high'));
        $data['last_vulnerability_check'] = '2026-01-01';
        $error = array('type' => 'Fatal Error', 'is_fatal' => true, 'message' => '<script>failure</script>', 'file' => WP_PLUGIN_DIR . '/fixture/main.php', 'line' => 12, 'date' => gmdate('Y-m-d H:i:s'), 'timestamp' => time(), 'call_stack' => array(array('class' => 'Fixture', 'type' => '::', 'function' => 'run', 'file' => WP_PLUGIN_DIR . '/fixture/main.php', 'line' => 9), array('function' => 'include')));
        update_option('sitepulse_error_log', array('errors' => array($error), 'total_count' => 1, 'last_error_date' => $error['date']));
        $html = $this->render('easy/views/security', $data);
        $this->assertStringContainsString('CVE-TEST', $html);
        $this->assertStringContainsString('2.0', $html);
        $this->assertStringContainsString('Fixture::', $html);
        $this->assertStringNotContainsString('<script>failure', $html);
    }
    public static function diskStates(): array { return array(array(null, "hasn&#039;t run"), array(false, 'FAIL'), array(true, 'Average Write Speed')); }
    /** @dataProvider diskStates */
    public function test_system_view_displays_storage_outcomes_and_branding($success, $expected): void {
        $data = sitepulse_test_seed_template_options();
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
        if ($success === null) { unset($data['disk_write']); }
        else { $data['disk_write']['success'] = $success; $data['disk_write']['error'] = 'Read-only disk'; }
        Sitepulse_Whitelabel_Service::store_whitelabel_data(array('enabled' => true, 'plugin_name' => 'Agency Monitor'));
        $html = $this->render('easy/views/system', $data);
        $this->assertStringContainsString($expected, $html);
        $this->assertStringContainsString('Agency Monitor', $html);
        $this->assertStringContainsString('Fixture 6', $html);
    }
    public function test_performance_small_duration_uses_milliseconds(): void {
        $data = sitepulse_test_template_data(); $data['total_load_time'] = 250;
        $this->assertStringContainsString('250ms', $this->render('easy/views/performance', $data));
    }
    public static function diskAverages(): array { return array(array(75), array(20)); }
    /** @dataProvider diskAverages */
    public function test_classic_resource_disk_summary_displays_each_speed_tier($average): void {
        $data = sitepulse_test_seed_template_options(); $data['disk_write']['avg_ms'] = $average;
        $html = $this->render('backend/resource_load', $data);
        $this->assertStringContainsString(number_format($average, 1), $html);
        $this->assertStringContainsString('Disk Write Test', $html);
    }

    public static function onboardingStates(): array { return array(array('completed'), array('dismissed')); }
    /** @dataProvider onboardingStates */
    public function test_settings_show_saved_controls_cron_errors_and_custom_branding($state): void {
        $data = sitepulse_test_seed_template_options();
        update_option('sitepulse_onboarding_' . $state, true);
        Sitepulse_Whitelabel_Service::store_whitelabel_data(array('enabled' => true, 'plugin_name' => 'Agency Monitor', 'company_name' => 'Example Agency', 'logo_url' => 'https://example.test/logo.png', 'updated_at' => '2026-01-01'));
        Sitepulse_Settings::getInstance()->update_settings(array('cron_disabled' => true, 'external_api_enabled' => false));
        $data['sitepulse_settings'] = Sitepulse_Settings::getInstance();
        $data['current_settings'] = $data['sitepulse_settings']->get_all_settings();
        $data['status_info'] = $data['sitepulse_settings']->get_status_info();
        $data['status_info']['cron_events_count'] = 5;
        $data['status_info']['error_log_count'] = 1;
        $data['status_info']['error_log_total_count'] = 8;
        $data['status_info']['error_log_last_date'] = '2026-01-01';
        $data['status_info']['cron_failing'] = true;
        $data['status_info']['cron_failure_data'] = array('message' => 'Loopback failed', 'last_failure' => time(), 'timestamp' => time());
        $data['active_plugins'] = array_keys(Sitepulse_Test_WP::$plugins);
        update_option('sitepulse_error_log', array('errors' => array(array('message' => 'Fixture error', 'type' => 'Warning', 'file' => '/tmp/fixture.php', 'line' => 12, 'timestamp' => time(), 'date' => gmdate('Y-m-d H:i:s'))), 'total_count' => 8));
        $html = $this->render('backend/settings', $data);
        $this->assertStringContainsString($state === 'completed' ? 'Completed' : 'Skipped', $html);
        $this->assertStringContainsString('Example Agency', $html);
        $this->assertStringContainsString('5 scheduled events', $html);
        $this->assertStringContainsString('Fixture error', $html);
        $this->assertStringContainsString('and 1 more', $html);
    }
}

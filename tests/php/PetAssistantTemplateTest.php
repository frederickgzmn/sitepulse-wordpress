<?php
require_once __DIR__ . '/Support/TemplateData.php';
/** @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class PetAssistantTemplateTest extends Sitepulse_Test_Case {
    private function render(array $data): string {
        extract($data, EXTR_SKIP); ob_start();
        try { require SITEPULSE_PATH . 'templates/easy/partials/pet-assistant.php'; return ob_get_contents(); }
        finally { ob_end_clean(); }
    }
    public static function recommendations(): array {
        return array(
            array('home', array('overall_score' => 40, 'total_load_time' => 4000, 'mem' => array('percent' => '90%'), 'error_count' => 10, 'active_plugins_count' => 40, 'speed_score' => 30, 'plugins_score' => 30, 'memory_score' => 30, 'show_performance_warnings' => true, 'weekly_trends' => array(array('score' => 90), array('score' => 20)), 'has_valid_pagespeed' => true, 'ps_performance_score' => 40), 'Speed score: 30'),
            array('home', array('overall_score' => 65, 'total_load_time' => 2000, 'mem' => array('percent' => '70%'), 'error_count' => 2, 'weekly_trends' => array(array('score' => 20), array('score' => 90)), 'has_valid_pagespeed' => true, 'ps_performance_score' => 70), 'Up 70 pts'),
            array('home', array('overall_score' => 95, 'total_load_time' => 500, 'error_count' => 0), 'Great shape!'),
            array('performance', array('all_slow_items' => array(array('name' => 'SitePulse'), array('name' => 'Nilbug'), array('name' => 'External', 'time' => 1500), array('name' => 'Theme'), array('name' => 'Shop'), array('name' => 'Gallery'))), 'External'),
            array('resource-load', array(), 'Single-page profile active'),
            array('security', array('vulnerabilities' => array(array('vulnerabilities' => array('CVE-TEST'))), 'error_log' => array('fatal_count' => 2, 'warning_count' => 5)), 'vulnerability(ies) found'),
            array('security', array('vulnerabilities' => array(array('vulnerabilities' => array()))), 'No known vulnerabilities'),
            array('security', array('vulnerabilities' => array(), 'error_count' => 0), 'All clear!'),
            array('insights', array('has_valid_pagespeed' => true, 'ps_data' => array('scores' => array('performance' => .3), 'core_web_vitals' => array('lcp' => array('score' => .1, 'display_value' => '5s'), 'tbt' => array('score' => .1)), 'opportunities' => array(array('title' => 'Compress images')))), 'Score: 30/100'),
            array('insights', array('has_valid_pagespeed' => true, 'ps_data' => array('scores' => array('performance' => .7))), 'Score: 70/100'),
            array('insights', array('has_valid_pagespeed' => true, 'ps_data' => array('scores' => array('performance' => .95))), 'Score: 95/100'),
            array('api-monitor', array(), 'api.example.test'),
            array('api-monitor', array('slow_api_requests' => array()), 'All connections healthy'),
            array('system', array(), 'Share this info'),
            array('settings', array('sp_ai_external_api_enabled' => false, 'current_settings' => array('alerts_enabled' => false)), 'Alerts disabled'),
        );
    }
    /** @dataProvider recommendations */
    public function test_assistant_recommends_actions_for_observed_metrics($view, $overrides, $expected): void {
        $_SERVER['HTTPS'] = 'on';
        $data = array_replace(sitepulse_test_template_data(), $overrides, array('easy_view' => $view));
        $data['ai_diagnostic_report'] = array('summary' => 'Review performance', 'status' => 'critical', 'metrics_summary' => array('overall_health_score' => 40), 'quick_wins' => array(array('title' => 'Compress images', 'fix' => 'Use WebP')), 'action_items' => array(array('title' => 'Update shop', 'priority' => 'critical', 'fix' => 'Upgrade plugin'), array('title' => 'Reduce scripts', 'priority' => 'low', 'problem' => 'Heavy scripts')));
        $data['plugin_profiler_stats'][] = array('plugin_name' => 'SitePulse', 'total_time' => 2000);
        $html = $this->render($data);
        $this->assertStringContainsString($expected, $html);
        $this->assertStringContainsString('Compress images', $html);
    }
    public function test_healthy_site_has_no_prescriptions(): void {
        $_SERVER['HTTPS'] = 'on';
        $html = $this->render(array('overall_score' => 99, 'total_load_time' => 500));
        $this->assertStringContainsString('data-issues="0"', $html);
        $this->assertStringNotContainsString('sp-dr-rx-organ', $html);
    }
    public static function tlsFailures(): array { return array(array(true, 'SSL mismatch:'), array(false, 'SSL loopback failing')); }
    /** @dataProvider tlsFailures */
    public function test_optional_ssl_integration_explains_internal_and_external_failures($mismatch, $expected): void {
        require __DIR__ . '/fixtures/ssl-health-boundary.php';
        Sitepulse_SSL_Health::$result = array('loopback_ok' => false, 'external_ok' => false, 'cn_mismatch' => $mismatch, 'loopback_cert_cn' => 'wrong.example.test', 'expected_domain' => 'example.test');
        $html = $this->render(array('easy_view' => 'system'));
        $this->assertStringContainsString($expected, $html);
        $this->assertStringContainsString('Outbound SSL failing', $html);
        $this->assertStringContainsString('Outbound Security', $html);
    }
    public static function hostVersions(): array { return array(array('7.4.33', 'outdated'), array('8.1.32', '8.2+ available'), array('8.5.0', 'latest and fastest')); }
    /** @dataProvider hostVersions */
    public function test_host_version_recommendation_uses_the_supplied_environment($version, $expected): void {
        $html = $this->render(array('easy_view' => 'system', 'system_php_version' => $version));
        $this->assertStringContainsString($version, $html);
        $this->assertStringContainsString($expected, $html);
    }
}

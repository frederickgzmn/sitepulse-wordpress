<?php
require_once __DIR__ . '/Support/SubprocessCoverage.php';
final class CoreVariantsTest extends Sitepulse_Test_Case {
    private function runVariant(array $input): array {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/core-variants.php', $input);
        $this->assertSame(0, $result['status'], $result['error']);
        $this->assertSame('', $result['error']);
        $decoded = json_decode($result['output'], true);
        $this->assertIsArray($decoded, $result['output']);
        return $decoded;
    }
    /** @dataProvider guardedFiles */
    public function test_direct_requests_stop_before_accessing_wordpress($file): void {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/core-variants.php', array('mode' => 'guard', 'file' => $file));
        $this->assertSame(0, $result['status']); $this->assertSame('', $result['output']); $this->assertSame('', $result['error']);
    }
    public static function guardedFiles(): array { return array_map(static function ($file) { return array($file); }, array('settings', 'setup', 'utils', 'onboarding', 'whitelabel_service', 'product_analytics', 'easy_mode', 'page_tracker', 'profiler', 'plugin_profiler', 'curloader')); }
    /** @dataProvider traceSummaries */
    public function test_debug_trace_handles_available_missing_and_malformed_wordpress_summaries($trace_api, $summary, $expected): void {
        $this->assertSame($expected, $this->runVariant(array('mode' => 'debug_trace', 'trace_api' => $trace_api, 'summary' => $summary)));
    }
    public static function traceSummaries(): array {
        return array(
            array(false, '', array()), array(true, '', array()), array(true, 'unparseable WordPress stack', array()),
            array(true, "1. {ABSPATH}wp-content/plugins/demo/main.php:42\n2. {ABSPATH}slow-http-profiler.php:7\ninvalid", array(array('file' => 'wp-content/plugins/demo/main.php', 'line' => 42, 'function' => 'wp_trace'))),
        );
    }
    /** @dataProvider absentIntegrations */
    public function test_ajax_missing_optional_classes_returns_actionable_failure($handler, $class, $message): void {
        $result = $this->runVariant(array('mode' => 'missing_class', 'exclude_classes' => array($class), 'handler' => $handler));
        $this->assertFalse($result['success']); $this->assertSame($message, $result['data']['message']);
    }
    public static function absentIntegrations(): array { return array(array('ajax_clear_error_log', 'Sitepulse_Error_Handler', 'Error handler not found'), array('ajax_enable_savequeries', 'Sitepulse_Plugin', 'Core plugin not found')); }
    public function test_plugin_profiler_gracefully_handles_missing_timing_engine(): void { $this->assertSame(array(), $this->runVariant(array('mode' => 'missing_profiler', 'exclude_classes' => array('Sitepulse_Profiler')))); }
    public function test_plugin_inventory_api_is_loaded_on_demand_before_collection(): void {
        $result = $this->runVariant(array('mode' => 'plugin_inventory_lazy_load', 'exclude_functions' => array('get_plugins')));
        $this->assertSame('Demo', $result['plugin_name']); $this->assertSame(0, $result['calls']);
    }
    public function test_onboarding_redirect_terminates_request_after_consuming_activation_flag(): void {
        $result = $this->runVariant(array('mode' => 'redirect_exit'));
        $this->assertFalse($result['pending']); $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_onboarding', $result['redirects'][0]['url']);
    }
    public function test_theme_bootstrap_without_wordpress_theme_helpers_keeps_unknown_attribution(): void {
        $rows = $this->runVariant(array('mode' => 'theme_functions_missing', 'exclude_functions' => array('get_stylesheet', 'get_template')));
        $this->assertSame('(unknown)', $rows[0]['source']); $this->assertSame('(unknown)', $rows[0]['fileline']); $this->assertSame('functions.php', $rows[0]['sig']);
    }
}

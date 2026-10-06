<?php
require_once __DIR__ . '/Support/SubprocessCoverage.php';
final class OptionalViewIntegrationTest extends Sitepulse_Test_Case {
    private function renderVariant(array $input): string {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/optional-view-integrations.php', $input);
        $this->assertSame(0, $result['status'], $result['error']); $this->assertSame('', $result['error']);
        $data = json_decode($result['output'], true); $this->assertIsArray($data, substr($result['output'], 0, 600));
        return $data['html'];
    }
    /** @dataProvider backendViews */
    public function test_backend_views_can_count_active_plugins_when_utility_class_is_unavailable($method, $expected, $plugin_count_pattern): void {
        $html = $this->renderVariant(array('mode' => 'backend', 'method' => $method, 'exclude_classes' => array('Sitepulse_Utils')));
        $this->assertStringContainsString($expected, $html);
        $this->assertMatchesRegularExpression($plugin_count_pattern, preg_replace('/\s+/', ' ', strip_tags($html)));
    }
    public static function backendViews(): array {
        return array(
            array('render_dashboard_section', 'SitePulse', '/\b2 Active Plugins\b/'),
            array('render_resource_load', 'Plugin Activity', '/\bActive Plugins 2\b/'),
            array('render_curl_api', 'External Requests', '/\b2 Active Plugins\b/'),
        );
    }
    public function test_performance_view_formats_plugin_timings_without_optional_profiler_adapter(): void {
        $html = $this->renderVariant(array('mode' => 'view', 'view' => 'performance', 'exclude_classes' => array('Sitepulse_Plugin_Profiler')));
        $this->assertStringContainsString('Fixture Plugin 1', $html); $this->assertStringContainsString('120.00 ms', $html); $this->assertStringContainsString('150.00 ms', $html);
    }
    public function test_security_view_treats_corrupt_error_collection_as_empty(): void {
        $html = $this->renderVariant(array('mode' => 'view', 'view' => 'security'));
        $this->assertStringContainsString('Error Log', $html); $this->assertStringNotContainsString('legacy-corruption', $html); $this->assertStringContainsString('No errors', $html);
    }
    public function test_system_view_loads_wordpress_plugin_metadata_api_when_needed(): void {
        $html = $this->renderVariant(array('mode' => 'view', 'view' => 'system', 'exclude_functions' => array('get_plugin_data')));
        $this->assertStringContainsString('Fixture 1', $html); $this->assertStringContainsString('Fixture 6', $html);
    }
}

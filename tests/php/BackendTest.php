<?php

final class BackendTest extends Sitepulse_Test_Case {
    private function calculate($method, ...$arguments) {
        // Exercise the existing calculation without rendering the full WordPress admin.
        $backend = new Sitepulse_Backend(new Sitepulse_Plugin());
        $reflection = new ReflectionMethod($backend, $method);
        if (PHP_VERSION_ID < 80100) { $reflection->setAccessible(true); }
        return $reflection->invokeArgs($backend, $arguments);
    }

    public static function loadTimeScores(): array {
        return array(array(0, 100), array(999, 100), array(1000, 90), array(1250, 75), array(1500, 60), array(2000, 40), array(3500, 30), array(5000, 20), array(7500, 10), array(10000, 0), array(12000, 0));
    }

    /** @dataProvider loadTimeScores */
    public function test_load_time_scores_follow_thresholds_and_never_go_negative($milliseconds, $expected): void {
        $this->assertEquals($expected, $this->calculate('calculate_load_time_score', $milliseconds));
    }

    public function test_slow_items_merge_deduplicate_case_insensitively_and_rank_by_time(): void {
        $hooks = array(
            array('source' => 'shop', 'total' => 0.8),
            array('source' => 'slow-theme', 'total' => 1.5),
            array('source' => 'boundary', 'total' => 0.5),
        );
        $plugins = array(array('plugin_name' => 'SHOP', 'load_time_ms' => 700), array('plugin_name' => 'Fast', 'load_time_ms' => 20));
        $this->assertSame(array(
            array('name' => 'Slow theme', 'time_ms' => 1500.0, 'date_time' => 0, 'severity' => 'critical'),
            array('name' => 'SHOP', 'time_ms' => 700.0, 'date_time' => 0, 'severity' => 'warning'),
        ), $this->calculate('calculate_slow_items', $hooks, $plugins));
        $this->assertSame(array(), $this->calculate('calculate_slow_items', array(), array()));
    }

    public function test_slow_requests_apply_threshold_sort_and_tolerate_missing_metadata(): void {
        $result = $this->calculate('calculate_slow_api_requests', array(
            array('elapsed' => 0.5, 'url' => 'https://boundary.test'),
            array('elapsed' => 0.6, 'url' => 'https://slow.test', 'origin' => 'shop', 'code' => 200),
            array('elapsed' => 1.2),
            array('url' => 'missing-elapsed'),
        ));
        $this->assertSame(array(
            array('url' => 'Unknown URL', 'time_ms' => 1200.0, 'origin' => 'Unknown', 'code' => '', 'severity' => 'critical'),
            array('url' => 'https://slow.test', 'time_ms' => 600.0, 'origin' => 'shop', 'code' => 200, 'severity' => 'warning'),
        ), $result);
    }

    public function test_top_slow_item_ignores_missing_timings_and_formats_source(): void {
        $this->assertNull($this->calculate('get_top_slow_item', array()));
        $this->assertSame(array('name' => 'Slow plugin', 'time_ms' => 900.0, 'hook' => 'init', 'fileline' => 'plugin.php:9', 'type' => 'plugin_or_theme'), $this->calculate('get_top_slow_item', array(
            array('source' => 'missing'), array('source' => 'fast', 'total' => 0.01),
            array('source' => 'slow-plugin', 'total' => 0.9, 'hook' => 'init', 'fileline' => 'plugin.php:9'),
        )));
    }

    public static function memoryPercentages(): array {
        return array(
            array(array('percent' => '7.5%'), 7.5),
            array(array('percent' => '150%'), 100),
            array(array('percent' => '—', 'formatted' => 'Memory: 5 MB — 25%'), 25),
            array(array('percent' => ''), 0),
        );
    }

    /** @dataProvider memoryPercentages */
    public function test_memory_progress_uses_percentage_and_clamps_to_one_hundred($memory, $expected): void {
        $this->assertEquals($expected, $this->calculate('calculate_memory_percentage', $memory));
    }

    public function test_overall_scores_include_available_load_measurement(): void {
        $scores = array('loadsentinel_score' => 80, 'api_score' => 60, 'memory_score' => 40, 'plugins_score' => 100, 'error_log_score' => 20);
        $this->assertSame(array('overall_score' => 60.0, 'speed_score' => 70.0, 'resource_score' => 70.0), $this->calculate('calculate_overall_scores', $scores, 0, 100));
        $this->assertSame(array('overall_score' => 65.0, 'speed_score' => 77.0, 'resource_score' => 70.0), $this->calculate('calculate_overall_scores', $scores, 1000, 90));
    }

    public function test_whitelabel_changes_only_sitepulse_plugin_metadata(): void {
        Sitepulse_Whitelabel_Service::store_whitelabel_data(array('enabled' => true, 'plugin_name' => 'Agency Monitor', 'company_name' => 'Example Agency'));
        $own = plugin_basename(SITEPULSE_PLUGIN_FILE);
        $plugins = array($own => array('Name' => 'SitePulse', 'Version' => '1.0'), 'other/loader.php' => array('Name' => 'Other'));
        $result = (new Sitepulse_Backend(new Sitepulse_Plugin()))->filter_plugin_name_whitelabel($plugins);
        $this->assertSame('Agency Monitor', $result[$own]['Name']);
        $this->assertSame('Example Agency', $result[$own]['Author']);
        $this->assertSame('1.0', $result[$own]['Version']);
        $this->assertSame($plugins['other/loader.php'], $result['other/loader.php']);
    }
}

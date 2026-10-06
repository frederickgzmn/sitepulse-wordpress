<?php
final class ProfilerTest extends Sitepulse_Test_Case {
    public function test_samples_accumulate_calls_average_peak_and_current_duration_without_url_query_secrets(): void {
        $_SERVER['REQUEST_URI'] = '/checkout?token=private'; Sitepulse_Profiler::init();
        $callback = static function () {};
        Sitepulse_Profiler::record('render', 10, 'id', $callback, .01); Sitepulse_Profiler::record('render', 10, 'id', $callback, .03);
        $rows = Sitepulse_Profiler::get_stats_by_hook('render'); $this->assertCount(1, $rows); $row = reset($rows);
        $this->assertSame(2, $row['calls']); $this->assertSame(40.0, $row['total_ms']); $this->assertSame(20.0, $row['avg_ms']); $this->assertSame(30.0, $row['max_ms']); $this->assertSame(30.0, $row['current_load_time']); $this->assertSame('/checkout', $row['current_url']); $this->assertTrue(Sitepulse_Profiler::is_active($row['key']));
        $formatted = Sitepulse_Profiler::get_formatted_stats()[$row['key']]; $this->assertSame('40.00 ms', $formatted['total_formatted']); $this->assertSame('20.00 ms', $formatted['avg_formatted']); $this->assertSame('30.00 ms', $formatted['max_formatted']);
        Sitepulse_Profiler::persist(); $this->assertSame($rows, get_option('sitepulse_profiler_stats'));
    }
    public function test_activity_rejects_unknown_expired_and_future_samples(): void {
        $this->setStaticProperty('Sitepulse_Profiler', 'stats', array('old' => array('last_seen_at' => microtime(true) - 120), 'future' => array('last_seen_at' => microtime(true) + 120), 'recent' => array('last_seen_at' => microtime(true) - 1)));
        $this->assertFalse(Sitepulse_Profiler::is_active('missing')); $this->assertFalse(Sitepulse_Profiler::is_active('old')); $this->assertFalse(Sitepulse_Profiler::is_active('future')); $this->assertTrue(Sitepulse_Profiler::is_active('recent'));
    }
    public function test_wrapped_callback_preserves_arguments_return_value_and_records_once_when_wrapped_twice(): void {
        add_filter('expensive_filter', static function ($value, $suffix) { usleep(2000); return $value . $suffix; }, 12, 2);
        Sitepulse_Profiler::wrap_all_hooks(); Sitepulse_Profiler::wrap_all_hooks();
        $this->assertSame('hello world', apply_filters('expensive_filter', 'hello', ' world'));
        $rows = Sitepulse_Profiler::get_stats_by_hook('expensive_filter'); $this->assertCount(1, $rows); $row = reset($rows); $this->assertSame(1, $row['calls']); $this->assertSame(12, $row['priority']); $this->assertGreaterThan(0, $row['total_ms']);
    }
    public function test_disabled_profiler_does_not_wrap_callbacks_or_register_measurement_hooks(): void {
        update_option('sitepulse_profiler_enabled', false); Sitepulse_Profiler::init();
        add_filter('filter', static function ($value) { usleep(1000); return $value; }); Sitepulse_Profiler::wrap_all_hooks(); apply_filters('filter', 1);
        $this->assertSame(array(), Sitepulse_Profiler::get_stats_by_hook('filter')); $this->assertFalse(has_action('plugin_loaded', array('Sitepulse_Profiler', 'capture_plugin_load_time')));
    }
    public function test_plugin_intervals_ignore_initial_baseline_and_attribute_following_plugin(): void {
        Sitepulse_Profiler::capture_plugin_load_time(WP_PLUGIN_DIR . '/sitepulse/loader.php'); $this->assertSame(array(), Sitepulse_Profiler::get_stats_by_hook('plugin_bootstrap'));
        Sitepulse_Profiler::mark_plugin_load_complete(); Sitepulse_Profiler::capture_plugin_load_time(WP_PLUGIN_DIR . '/demo/main.php');
        $row = array_values(Sitepulse_Profiler::get_stats_by_hook('plugin_bootstrap'))[0]; $this->assertSame('wp-content/plugins/demo/main.php', $row['fileline']); $this->assertSame('demo', $row['source']); $this->assertSame('include', $row['sig']); $this->assertSame(1, $row['calls']);
    }
    public function test_theme_bootstrap_tracks_child_parent_once_and_requires_start(): void {
        Sitepulse_Test_WP::$theme['stylesheet'] = 'child'; Sitepulse_Test_WP::$theme['template'] = 'parent';
        Sitepulse_Profiler::capture_theme_bootstrap(); $this->assertSame(array(), Sitepulse_Profiler::get_stats_by_hook('theme_bootstrap'));
        Sitepulse_Profiler::start_theme_bootstrap(); Sitepulse_Profiler::capture_theme_bootstrap(); Sitepulse_Profiler::capture_theme_bootstrap();
        $row = array_values(Sitepulse_Profiler::get_stats_by_hook('theme_bootstrap'))[0]; $this->assertSame('functions.php (child + parent)', $row['sig']); $this->assertSame('wp-content/themes/child/functions.php', $row['fileline']); $this->assertSame(1, $row['calls']);
    }
    public function test_nested_theme_templates_match_each_frame_and_exclude_plugin_templates(): void {
        $outer = ABSPATH . 'wp-content/themes/test-theme/index.php'; $inner = ABSPATH . 'wp-content/themes/test-theme/parts/item.php'; $plugin = WP_PLUGIN_DIR . '/demo/view.php';
        Sitepulse_Profiler::start_theme_template_load($outer); Sitepulse_Profiler::start_theme_template_load($inner); Sitepulse_Profiler::start_theme_template_load($plugin);
        Sitepulse_Profiler::capture_theme_template_load($inner); Sitepulse_Profiler::capture_theme_template_load($outer); Sitepulse_Profiler::capture_theme_template_load($plugin); Sitepulse_Profiler::capture_theme_template_load($outer);
        $rows = array_values(Sitepulse_Profiler::get_stats_by_hook('theme_template')); $this->assertCount(2, $rows); $this->assertSame('wp-content/themes/test-theme/parts/item.php', $rows[0]['fileline']); $this->assertSame('wp-content/themes/test-theme/index.php', $rows[1]['fileline']); $this->assertSame(1, $rows[1]['calls']);
    }
    public function test_single_page_persistence_keeps_global_snapshot_unchanged(): void {
        update_option('sitepulse_profiler_stats', array('old' => array('hook' => 'old'))); Sitepulse_Profiler::init();
        update_option('sitepulse_pageloadhttp_loadstatus', true); update_option('sitepulse_current_tracked_pageid', 42); Sitepulse_Test_WP::$posts[42] = array('post_status' => 'publish');
        Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .002); Sitepulse_Profiler::persist();
        $this->assertCount(2, get_transient('sitepulse_load_single_page_42')); $this->assertSame(array('old' => array('hook' => 'old')), get_option('sitepulse_profiler_stats'));
    }
    public function test_clear_events_removes_persisted_samples(): void {
        Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .001); (new Sitepulse_Profiler())->clear_events(); $this->assertSame(array(), get_option('sitepulse_profiler_stats')); $this->assertSame(array(), Sitepulse_Profiler::get_formatted_stats());
    }
}

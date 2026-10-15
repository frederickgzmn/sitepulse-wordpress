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
    public function test_analysis_request_profiles_in_isolation_and_never_touches_the_site_snapshot(): void {
        update_option('sitepulse_profiler_stats', array('old' => array('hook' => 'old'))); update_option('sitepulse_profiler_enabled', false);
        $this->startAnalysisRequest(); Sitepulse_Profiler::init();
        $this->assertTrue(Sitepulse_Profiler::$enabled); $this->assertSame(array(), Sitepulse_Profiler::get_stats());
        Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .002); Sitepulse_Profiler::persist();
        $this->assertCount(1, Sitepulse_Profiler::get_stats()); $this->assertSame(array('old' => array('hook' => 'old')), get_option('sitepulse_profiler_stats')); $this->assertFalse(get_option('sitepulse_profiler_current_time'));
    }
    public function test_nested_callbacks_record_their_own_time_separately_from_children(): void {
        add_filter('inner_filter', static function ($value) { usleep(4000); return $value; });
        add_filter('outer_filter', static function ($value) { usleep(2000); return apply_filters('inner_filter', $value) . '!'; });
        Sitepulse_Profiler::wrap_all_hooks();
        $this->assertSame('done!', apply_filters('outer_filter', 'done'));
        $outer = array_values(Sitepulse_Profiler::get_stats_by_hook('outer_filter'))[0]; $inner = array_values(Sitepulse_Profiler::get_stats_by_hook('inner_filter'))[0];
        $this->assertSame($inner['total_ms'], $inner['self_total_ms']); $this->assertGreaterThanOrEqual(4.0, $inner['total_ms']);
        $this->assertGreaterThanOrEqual($inner['total_ms'] + 2.0, $outer['total_ms']); $this->assertGreaterThanOrEqual(2.0, $outer['self_total_ms']);
        $this->assertEqualsWithDelta($outer['total_ms'] - $inner['total_ms'], $outer['self_total_ms'], 0.5);
    }
    public function test_theme_template_own_time_excludes_callbacks_running_inside_it(): void {
        add_action('inside_template', static function () { usleep(4000); }); Sitepulse_Profiler::wrap_all_hooks();
        $template = ABSPATH . 'wp-content/themes/test-theme/single.php';
        Sitepulse_Profiler::start_theme_template_load($template); usleep(1000); do_action('inside_template'); Sitepulse_Profiler::capture_theme_template_load($template);
        $row = array_values(Sitepulse_Profiler::get_stats_by_hook('theme_template'))[0]; $inside = array_values(Sitepulse_Profiler::get_stats_by_hook('inside_template'))[0];
        $this->assertGreaterThanOrEqual(5.0, $row['total_ms']); $this->assertEqualsWithDelta($row['total_ms'] - $inside['total_ms'], $row['self_total_ms'], 0.5);
    }
    public function test_profiler_and_tracker_callbacks_are_never_wrapped(): void {
        $this->startAnalysisRequest(); Sitepulse_Profiler::init(); Sitepulse_Profiler::wrap_all_hooks();
        foreach (array('plugin_loaded' => PHP_INT_MIN, 'shutdown' => PHP_INT_MAX) as $hook => $priority) {
            foreach ($GLOBALS['wp_filter'][$hook]->callbacks[$priority] as $callback) { $this->assertIsArray($callback['function']); }
        }
    }
    public function test_exception_in_wrapped_callback_propagates_and_later_timings_stay_exclusive(): void {
        add_filter('failing_filter', static function () { throw new RuntimeException('callback failed'); });
        add_filter('after_failure', static function ($value) { usleep(2000); return $value; });
        Sitepulse_Profiler::wrap_all_hooks();
        try { apply_filters('failing_filter', 1); $this->fail('The callback exception must propagate.'); } catch (RuntimeException $error) { $this->assertSame('callback failed', $error->getMessage()); }
        apply_filters('after_failure', 1); $row = array_values(Sitepulse_Profiler::get_stats_by_hook('after_failure'))[0];
        $this->assertSame($row['total_ms'], $row['self_total_ms']);
    }
    public function test_samples_persisted_before_exclusive_timing_keep_accumulating(): void {
        Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .001);
        $legacy = Sitepulse_Profiler::get_stats(); foreach ($legacy as &$row) { unset($row['self_total_ms']); } unset($row); $this->setStaticProperty('Sitepulse_Profiler', 'stats', $legacy);
        Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .003, .002);
        $row = array_values(Sitepulse_Profiler::get_stats_by_hook('render'))[0]; $this->assertSame(2, $row['calls']); $this->assertEqualsWithDelta(2.0, $row['self_total_ms'], 0.0001);
    }
    public function test_single_file_plugins_are_named_after_their_file(): void {
        file_put_contents(WP_PLUGIN_DIR . '/hello-single.php', '<?php function sitepulse_test_hello_single() {}');
        require_once WP_PLUGIN_DIR . '/hello-single.php';
        Sitepulse_Profiler::record('wp_head', 10, 'hello', 'sitepulse_test_hello_single', .002);
        $row = array_values(Sitepulse_Profiler::get_stats_by_hook('wp_head'))[0];
        $this->assertSame('hello-single', $row['source']); $this->assertSame('wp-content/plugins/hello-single.php:1', $row['fileline']);
    }
    public function test_window_counts_each_page_load_once_alongside_lifetime_totals(): void {
        Sitepulse_Profiler::init(); Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .002, .001); Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .006, .004); Sitepulse_Profiler::persist();
        Sitepulse_Profiler::init(); Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .004, .004);
        $row = array_values(Sitepulse_Profiler::get_stats_by_hook('render'))[0];
        $this->assertSame((int) floor(microtime(true) / Sitepulse_Profiler::RECENT_SECONDS), $row['window']); $this->assertSame(3, $row['calls']);
        $this->assertSame(3, $row['window_calls']); $this->assertSame(2, $row['window_loads'], 'Two page loads, however often the callback ran in each.');
        $this->assertEqualsWithDelta(12.0, $row['window_ms'], 1e-9); $this->assertEqualsWithDelta(9.0, $row['window_self_ms'], 1e-9); $this->assertEqualsWithDelta(6.0, $row['window_max_ms'], 1e-9);
        $this->assertSame(0, $row['previous_calls']); $this->assertSame(0.0, $row['previous_ms']);
    }
    public function test_a_new_window_keeps_the_one_that_just_ended_and_drops_older_figures(): void {
        Sitepulse_Profiler::record('yesterday', 10, 'id', 'strlen', .002); Sitepulse_Profiler::record('last_week', 10, 'id', 'strlen', .002);
        $window = (int) floor(microtime(true) / Sitepulse_Profiler::RECENT_SECONDS); $stats = Sitepulse_Profiler::get_stats();
        foreach ($stats as $key => $row) {
            $stats[$key] = array_merge($row, array('window' => 'yesterday' === $row['hook'] ? $window - 1 : $window - 7, 'window_calls' => 7, 'window_loads' => 3, 'window_ms' => 70.0, 'window_self_ms' => 50.0, 'window_max_ms' => 30.0, 'previous_calls' => 99, 'previous_loads' => 9, 'previous_ms' => 990.0));
        }
        // A later request records both callbacks again.
        $this->setStaticProperty('Sitepulse_Profiler', 'stats', $stats); $this->setStaticProperty('Sitepulse_Profiler', 'counted_loads', array());
        Sitepulse_Profiler::record('yesterday', 10, 'id', 'strlen', .004); Sitepulse_Profiler::record('last_week', 10, 'id', 'strlen', .004);
        $yesterday = array_values(Sitepulse_Profiler::get_stats_by_hook('yesterday'))[0]; $last_week = array_values(Sitepulse_Profiler::get_stats_by_hook('last_week'))[0];
        $this->assertSame(array($window, 7, 3, 70.0, 50.0, 30.0), array($yesterday['window'], $yesterday['previous_calls'], $yesterday['previous_loads'], $yesterday['previous_ms'], $yesterday['previous_self_ms'], $yesterday['previous_max_ms']));
        $this->assertSame(array(1, 1), array($yesterday['window_calls'], $yesterday['window_loads'])); $this->assertEqualsWithDelta(4.0, $yesterday['window_ms'], 1e-9);
        $this->assertSame(array($window, 0, 0, 0.0, 0.0, 0.0), array($last_week['window'], $last_week['previous_calls'], $last_week['previous_loads'], $last_week['previous_ms'], $last_week['previous_self_ms'], $last_week['previous_max_ms']));
        $this->assertSame(array(1, 1), array($last_week['window_calls'], $last_week['window_loads']));
    }
    public function test_saving_forgets_code_that_has_not_run_for_two_days(): void {
        $this->setStaticProperty('Sitepulse_Profiler', 'stats', array(
            'today' => array('hook' => 'init', 'last_seen_at' => microtime(true) - HOUR_IN_SECONDS), 'yesterday' => array('hook' => 'init', 'last_seen_at' => microtime(true) - 47 * HOUR_IN_SECONDS),
            'retired' => array('hook' => 'init', 'last_seen_at' => microtime(true) - 49 * HOUR_IN_SECONDS), 'legacy' => array('hook' => 'init', 'total_ms' => 90000.0),
        ));
        Sitepulse_Profiler::persist();
        $this->assertSame(array('today', 'yesterday'), array_keys(get_option('sitepulse_profiler_stats')));
    }
    public function test_clear_events_removes_persisted_samples(): void {
        Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .001); (new Sitepulse_Profiler())->clear_events(); $this->assertSame(array(), get_option('sitepulse_profiler_stats')); $this->assertSame(array(), Sitepulse_Profiler::get_formatted_stats());
    }
}

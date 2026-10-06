<?php
require_once SITEPULSE_PATH . 'inc/api_backend.php';

/**
 * Code that runs on page loads, in cron and over REST must cope with stale,
 * partial or corrupted stored data. PHPUnit turns any warning into a failure.
 */
final class StoredDataWritePathsTest extends Sitepulse_Test_Case {
    private function invokeStatic($class, $method, ...$args) {
        $reflection = new ReflectionMethod($class, $method);
        if (PHP_VERSION_ID < 80100) { $reflection->setAccessible(true); }
        return $reflection->invokeArgs(null, $args);
    }

    public function test_stored_lists_keep_valid_records_fill_missing_fields_and_drop_everything_else(): void {
        update_option('sitepulse_profiler_stats', 'corrupt');
        $this->assertSame(array(), Sitepulse_Profiler::get_stored_stats());
        update_option('sitepulse_profiler_stats', array('a' => array('hook' => 'init', 'extra' => true), 'b' => 'scalar'));
        $this->assertSame(array('a' => array_merge(Sitepulse_Profiler::STAT_DEFAULTS, array('hook' => 'init', 'extra' => true))), Sitepulse_Profiler::get_stored_stats());
        update_option('sitepulse_curl_api_events', array(array('url' => 'https://api.test/x'), 7));
        $this->assertSame(array(array_merge(Sitepulse_CurLoader::EVENT_DEFAULTS, array('url' => 'https://api.test/x'))), Sitepulse_CurLoader::get_events());
    }

    public function test_http_tracker_records_requests_when_the_stored_list_is_corrupt(): void {
        update_option('sitepulse_curl_api_events', 'corrupt');
        Sitepulse_CurLoader::init();
        Sitepulse_CurLoader::capture(Sitepulse_Test_WP::response(array()), 'response', 'Requests', array('_wshp_start' => microtime(true) - 2, '_wshp_trace' => array()), 'https://api.test/slow');
        $events = Sitepulse_CurLoader::get_events();
        $this->assertCount(1, $events); $this->assertSame('https://api.test/slow', $events[0]['url']);
    }

    public function test_profiler_keeps_counting_rows_stored_in_an_older_shape(): void {
        Sitepulse_Profiler::record('init', 10, 'cb', 'strlen', .002);
        $key = array_keys(Sitepulse_Profiler::get_stats())[0];
        update_option('sitepulse_profiler_stats', array($key => array('hook' => 'init', 'sig' => 'strlen'), 'scalar' => 'not-an-array'));
        update_option('sitepulse_profiler_enabled', true);
        Sitepulse_Profiler::init();
        Sitepulse_Profiler::record('init', 10, 'cb', 'strlen', .003);
        Sitepulse_Profiler::persist();
        $stored = get_option('sitepulse_profiler_stats');
        $this->assertSame(array($key), array_keys($stored)); $this->assertSame(1, $stored[$key]['calls']); $this->assertEqualsWithDelta(3.0, $stored[$key]['total_ms'], 0.0001);
    }

    public function test_error_handler_logs_when_the_stored_log_is_broken_and_completes_old_entries(): void {
        update_option('sitepulse_error_log', array('errors' => 'corrupt'));
        Sitepulse_Error_Handler::handle_error(E_WARNING, 'Something broke', '/var/www/file.php', 12);
        $log = Sitepulse_Error_Handler::get_error_log();
        $this->assertCount(1, $log['errors']); $this->assertSame('Something broke', $log['errors'][0]['message']);

        update_option('sitepulse_error_log', array('errors' => array(array('timestamp' => current_time('timestamp'), 'message' => 'Old entry'), 'not-an-array'), 'total_count' => 2));
        $entry = Sitepulse_Error_Handler::get_error_log()['errors'][0];
        $this->assertSame(array('type' => 'Unknown', 'message' => 'Old entry', 'file' => '', 'line' => 0, 'date' => '', 'timestamp' => current_time('timestamp'), 'is_fatal' => false, 'call_stack' => array()), $entry);
        $this->assertCount(1, Sitepulse_Error_Handler::get_error_log()['errors']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_admin_error_cleanup_leaves_a_broken_entry_list_for_the_log_reader_to_repair(): void {
        update_option('sitepulse_error_log', array('errors' => 'corrupt', 'total_count' => 4));
        Sitepulse_Test_WP::$is_admin = true;
        Sitepulse_Error_Handler::cleanup_old_errors_on_init();
        $this->assertSame(array('errors' => array(), 'total_count' => 4, 'last_error_date' => null), Sitepulse_Error_Handler::get_error_log());
    }

    public function test_cron_report_and_rest_endpoints_read_corrupt_stats_safely(): void {
        foreach (array('sitepulse_profiler_stats', 'sitepulse_curl_api_events', 'sitepulse_plugin_profiler_stats') as $option) { update_option($option, 'corrupt'); }
        $this->assertSame(array(), $this->invokeStatic('Sitepulse_Api_Service', 'get_resources_data'));
        $this->assertSame(array(), $this->invokeStatic('Sitepulse_Api_Service', 'get_api_requests_data'));
        Sitepulse_Plugin_Profiler::init();
        $request = new WP_REST_Request('POST', '/sitepulse/v1/test'); $request->set_param('_wpnonce', wp_create_nonce('wp_rest'));
        $this->assertSame(0, sitepulse_get_profiler_stats($request)->get_data()['count']);
        $this->assertSame(0, sitepulse_get_curl_stats($request)->get_data()['count']);
        $this->assertSame(0, sitepulse_get_plugin_profiler_stats($request)->get_data()['count']);
    }

    public function test_plugin_stats_keep_rows_from_older_versions_and_drop_broken_ones(): void {
        update_option('sitepulse_plugin_profiler_stats', array('old' => array('plugin_name' => 'Old Plugin', 'avg_time' => 0.4, 'loads' => 3), 'scalar' => 'not-an-array'));
        Sitepulse_Plugin_Profiler::init();
        $this->assertSame(array('old' => array('plugin_name' => 'Old Plugin', 'avg_time' => 0.4, 'loads' => 3)), Sitepulse_Plugin_Profiler::get_stats());
    }
}

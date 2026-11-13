<?php
require_once __DIR__ . '/Support/SubprocessCoverage.php';

/** Saving measurements once the visitor has the page, so the write no longer delays it. */
final class FinishResponseTest extends Sitepulse_Test_Case {
    private function variant(string $mode): array {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/finish-response-variants.php', array('mode' => $mode));
        $this->assertSame(0, $result['status'], $result['error']); $this->assertSame('', $result['error']);
        $data = json_decode($result['output'], true); $this->assertIsArray($data, $result['output']);
        $this->assertFalse($data['fastcgi']);
        return $data;
    }
    public function test_php_fpm_sends_the_response_before_the_work_that_follows(): void {
        Sitepulse_Utils::finish_response();
        $this->assertSame(1, Sitepulse_Test_WP::$finished_requests);
    }
    public function test_litespeed_sends_the_response_and_keeps_running_if_the_visitor_leaves(): void {
        $data = $this->variant('litespeed');
        $this->assertSame(1, $data['litespeed']); $this->assertTrue($data['keeps_running']);
    }
    public function test_other_servers_leave_the_response_as_it_was(): void {
        $data = $this->variant('none');
        $this->assertSame(0, $data['litespeed']); $this->assertFalse($data['keeps_running']);
    }
    public function test_profiler_stats_are_saved_once_the_page_is_sent(): void {
        update_option(SITEPULSE_PROFILER_ENABLED, true); Sitepulse_Test_WP::$option_writes = array();
        Sitepulse_Profiler::init(); Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .002);
        do_action('shutdown');
        $this->assertSame(1, Sitepulse_Test_WP::$finished_requests);
        $this->assertCount(1, get_option(SITEPULSE_PROFILER_KEY));
        $this->assertSame(array(SITEPULSE_PROFILER_CURRENT_TIME, SITEPULSE_PROFILER_KEY), array_column(Sitepulse_Test_WP::$option_writes, 'key'));
    }
    public function test_a_page_analysis_response_is_left_whole_for_the_page_tracker(): void {
        $this->startAnalysisRequest();
        Sitepulse_Profiler::init(); Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .002);
        Sitepulse_Profiler::persist_after_response();
        $this->assertSame(0, Sitepulse_Test_WP::$finished_requests);
        $this->assertFalse(get_option(SITEPULSE_PROFILER_KEY));
    }
    public function test_plugin_profiler_stats_are_saved_once_the_page_is_sent(): void {
        Sitepulse_Plugin_Profiler::init();
        $this->assertSame(PHP_INT_MAX, has_action('shutdown', array('Sitepulse_Plugin_Profiler', 'persist_after_response')));
        Sitepulse_Plugin_Profiler::persist_after_response();
        $this->assertSame(1, Sitepulse_Test_WP::$finished_requests);
        $this->assertNotFalse(get_option(Sitepulse_Plugin_Profiler::OPTION_CURRENT_TIME));
    }
    public function test_clearing_profiler_stats_from_the_api_does_not_end_its_response(): void {
        Sitepulse_Profiler::record('render', 10, 'id', 'strlen', .002);
        (new Sitepulse_Profiler())->clear_events();
        $this->assertSame(0, Sitepulse_Test_WP::$finished_requests, 'The API answer is sent after this.');
        $this->assertSame(array(), get_option(SITEPULSE_PROFILER_KEY));
    }
}

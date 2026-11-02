<?php
require_once __DIR__ . '/Support/RecentActivity.php';

/** Rankings, warnings and reports describe the last 24 hours, so code that has since changed is no longer blamed. */
final class RecentActivityTest extends Sitepulse_Test_Case {
    /** 06:00 into window 20000: three quarters of window 19999 are still within the last 24 hours. */
    private const NOW = 20000.25 * 86400;

    private static function stat(int $window, array $fields = array()): array {
        return array_merge(array(
            'source' => 'shop', 'calls' => 5000, 'total_ms' => 900000.0, 'avg_ms' => 180.0, 'last_seen_at' => self::NOW - 10, 'window' => $window,
            'window_calls' => 4, 'window_loads' => 2, 'window_ms' => 400.0, 'window_self_ms' => 300.0, 'window_max_ms' => 150.0,
            'previous_calls' => 8, 'previous_loads' => 4, 'previous_ms' => 1600.0, 'previous_self_ms' => 1200.0, 'previous_max_ms' => 900.0,
        ), $fields);
    }

    public function test_figures_combine_this_window_with_the_part_of_the_previous_one_inside_the_last_24_hours(): void {
        $recent = Sitepulse_Profiler::recent_stat(self::stat(20000), self::NOW);
        $this->assertSame(array(10, 5), array($recent['calls'], $recent['loads']));
        $this->assertSame(array(1600.0, 1.6, 1200.0, 160.0, 900.0, 0.9), array($recent['total_ms'], $recent['total'], $recent['self_total_ms'], $recent['avg_ms'], $recent['max_ms'], $recent['max']));
        $this->assertSame(240.0, $recent['load_ms'], 'Own time per page load: 1200 ms over 5 page loads.');
        $this->assertSame('shop', $recent['source']);
        $this->assertSame(array(), array_intersect_key($recent, array_flip(array('window', 'window_calls', 'window_loads', 'window_ms', 'window_self_ms', 'window_max_ms', 'previous_calls', 'previous_loads', 'previous_ms', 'previous_self_ms', 'previous_max_ms'))));
    }

    public function test_a_window_that_just_ended_counts_for_the_part_still_inside_the_last_24_hours(): void {
        // Ran last at 20:00 yesterday; the stored previous window is two days old and no longer counts.
        $recent = Sitepulse_Profiler::recent_stat(self::stat(19999, array('last_seen_at' => self::NOW - 10 * HOUR_IN_SECONDS, 'window_calls' => 8, 'window_loads' => 4, 'window_ms' => 1600.0, 'window_self_ms' => 1200.0, 'window_max_ms' => 900.0, 'previous_max_ms' => 5000.0)), self::NOW);
        $this->assertSame(array(6, 3, 1200.0, 900.0, 200.0, 900.0, 300.0), array($recent['calls'], $recent['loads'], $recent['total_ms'], $recent['self_total_ms'], $recent['avg_ms'], $recent['max_ms'], $recent['load_ms']));
    }

    /** @dataProvider retiredStats */
    public function test_code_that_did_not_run_in_the_last_24_hours_has_no_recent_figures(array $stat): void {
        $this->assertNull(Sitepulse_Profiler::recent_stat($stat, self::NOW));
    }

    public static function retiredStats(): array {
        return array(
            'last ran 30 hours ago' => array(self::stat(19999, array('last_seen_at' => self::NOW - 30 * HOUR_IN_SECONDS))),
            'last ran two windows ago' => array(self::stat(19998, array('last_seen_at' => self::NOW - 50 * HOUR_IN_SECONDS))),
            'stored by an older version' => array(array_merge(Sitepulse_Profiler::STAT_DEFAULTS, array('total_ms' => 900000.0, 'last_seen_at' => self::NOW - 10))),
            'counters without page loads' => array(self::stat(20000, array('window_loads' => 0, 'previous_loads' => 0))),
            'window from a clock set later' => array(self::stat(20001)),
        );
    }

    public function test_counts_of_code_that_ran_never_show_zero_and_averages_keep_their_scale(): void {
        // 18:00 into window 20000: a quarter of window 19999 still counts, so a single run there counts as a quarter.
        $now = 20000.75 * 86400;
        $stat = self::stat(19999, array('last_seen_at' => $now - 20 * HOUR_IN_SECONDS, 'window_calls' => 1, 'window_loads' => 1, 'window_ms' => 800.0, 'window_self_ms' => 800.0, 'window_max_ms' => 800.0));
        $recent = Sitepulse_Profiler::recent_stat($stat, $now);
        $this->assertSame(array(1, 1, 200.0, 800.0, 800.0), array($recent['calls'], $recent['loads'], $recent['total_ms'], $recent['avg_ms'], $recent['load_ms']));
    }

    public function test_damaged_counters_without_calls_give_no_average_instead_of_failing(): void {
        $recent = Sitepulse_Profiler::recent_stat(self::stat(20000, array('window_calls' => 0, 'previous_calls' => 0)), self::NOW);
        $this->assertSame(0.0, $recent['avg_ms']); $this->assertSame(1, $recent['calls']); $this->assertSame(240.0, $recent['load_ms']);
    }

    public function test_site_wide_activity_lists_recent_code_slowest_first_with_stored_keys(): void {
        update_option('sitepulse_profiler_stats', array(
            'fast' => sitepulse_test_recent_stat(array('key' => 'fast'), 5.0),
            'retired' => sitepulse_test_recent_stat(array('key' => 'retired'), 4000.0, 3, 3 * DAY_IN_SECONDS),
            'slow' => sitepulse_test_recent_stat(array('key' => 'slow'), 1200.0, 2),
            'legacy' => array('hook' => 'init', 'total_ms' => 99999.0, 'last_seen_at' => microtime(true)),
            'corrupt' => 'not-an-array',
        ));
        $recent = Sitepulse_Profiler::get_recent_stats();
        $this->assertSame(array('slow', 'fast'), array_keys($recent));
        $this->assertSame(array(1200.0, 2, 2400.0), array($recent['slow']['load_ms'], $recent['slow']['loads'], $recent['slow']['total_ms']));
        $this->assertArrayNotHasKey('window_ms', $recent['fast']);
    }

    public function test_only_http_requests_of_the_last_24_hours_are_recent(): void {
        update_option('sitepulse_curl_api_events', array(
            sitepulse_test_http_event(array('url' => 'https://old.test/'), 2 * DAY_IN_SECONDS), sitepulse_test_http_event(array('url' => 'https://first.test/'), 3 * HOUR_IN_SECONDS),
            array('url' => 'https://legacy.test/'), 'corrupt', sitepulse_test_http_event(array('url' => 'https://latest.test/')),
        ));
        $recent = Sitepulse_CurLoader::get_recent_events();
        $this->assertSame(array('https://first.test/', 'https://latest.test/'), array_column($recent, 'url'));
        $this->assertSame(array(0, 1), array_keys($recent));
    }
}

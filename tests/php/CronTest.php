<?php

require_once SITEPULSE_PATH . 'inc/cron_manager.php';
require_once SITEPULSE_PATH . 'inc/cron_fallback.php';
require_once SITEPULSE_PATH . 'inc/ai_diagnostic_cron.php';

final class CronTest extends Sitepulse_Test_Case {
    public function test_initialization_schedules_six_hour_reports_and_keeps_other_intervals(): void {
        $before = time();
        Sitepulse_Cron_Manager::init();
        $event = wp_get_scheduled_event('sitepulse_daily_api_request');
        $this->assertSame('sitepulse_every_six_hours', $event->schedule);
        $this->assertSame(21600, $event->interval);
        $this->assertGreaterThanOrEqual($before + 21600, $event->timestamp);
        $this->assertLessThanOrEqual(time() + 21600, $event->timestamp);
        $this->assertSame(86400, wp_get_schedules()['daily']['interval']);
        $this->assertNotFalse(has_action('sitepulse_daily_api_request', array('Sitepulse_Cron_Manager', 'send_daily_api_request')));
    }

    public function test_legacy_daily_and_duplicate_events_are_replaced_by_one_six_hour_event(): void {
        wp_schedule_event(time() + 100, 'daily', 'sitepulse_daily_api_request');
        wp_schedule_event(time() + 200, 'daily', 'sitepulse_daily_api_request');
        wp_schedule_event(time() + 300, 'daily', 'another_plugin');
        Sitepulse_Cron_Manager::init();
        $events = array_values(array_filter(Sitepulse_Test_WP::$scheduled, static function ($event) { return $event->hook === 'sitepulse_daily_api_request'; }));
        $this->assertCount(1, $events);
        $this->assertSame(21600, $events[0]->interval);
        $this->assertNotFalse(wp_next_scheduled('another_plugin'));
    }

    public function test_reinitializing_correct_schedule_preserves_next_run(): void {
        Sitepulse_Cron_Manager::init();
        $timestamp = Sitepulse_Cron_Manager::get_next_scheduled_time();
        Sitepulse_Cron_Manager::init();
        $this->assertSame($timestamp, Sitepulse_Cron_Manager::get_next_scheduled_time());
        $this->assertCount(1, Sitepulse_Test_WP::$scheduled);
    }

    public function test_external_api_opt_out_prevents_scheduling_and_sending(): void {
        update_option('sitepulse_settings', array('external_api_enabled' => false));
        Sitepulse_Cron_Manager::init();
        $this->assertFalse(Sitepulse_Cron_Manager::get_next_scheduled_time());
        $this->assertFalse(has_action('sitepulse_daily_api_request'));
        $this->assertFalse(Sitepulse_Cron_Manager::send_daily_api_request()['success']);
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public function test_scheduled_callback_sends_actual_site_data_and_unschedule_clears_duplicates(): void {
        Sitepulse_Cron_Manager::init();
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('license_key' => 'cron-license'));
        do_action('sitepulse_daily_api_request');
        $this->assertSame('cron-license', get_option('sitepulse_license_key'));
        $this->assertCount(1, Sitepulse_Test_WP::$http_requests);
        wp_schedule_event(time() + 300, 'daily', 'sitepulse_daily_api_request');
        Sitepulse_Cron_Manager::unschedule_daily_api_request();
        $this->assertFalse(wp_next_scheduled('sitepulse_daily_api_request'));
    }

    private function overdue(int $seconds = 172900): void {
        wp_schedule_event(time() - $seconds, 'daily', 'sitepulse_daily_api_request');
    }

    public function test_overdue_fallback_acquires_lock_and_defers_network_to_shutdown(): void {
        $this->overdue();
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        $this->assertSame(1, get_transient('sitepulse_cron_fallback_daily_lock'));
        $this->assertSame(600, Sitepulse_Test_WP::$transient_expirations['sitepulse_cron_fallback_daily_lock']);
        $this->assertNotFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
        $this->assertSame(0, Sitepulse_Test_WP::$spawn_cron_calls);
    }

    /** @dataProvider suppressedFallbacks */
    public function test_admin_fallback_does_not_run_for_ineligible_requests(string $state): void {
        $this->overdue();
        if ($state === 'non_admin') { Sitepulse_Test_WP::$is_admin = false; }
        if ($state === 'no_capability') { Sitepulse_Test_WP::$capabilities['manage_options'] = false; }
        if ($state === 'ajax') { Sitepulse_Test_WP::$doing_ajax = true; }
        if ($state === 'cron') { Sitepulse_Test_WP::$doing_cron = true; }
        if ($state === 'disabled') { update_option('sitepulse_settings', array('cron_disabled' => true)); }
        if ($state === 'filter') { add_filter('sitepulse_enable_cron_fallback', '__return_false'); }
        if ($state === 'healthy_cache') { set_transient('sitepulse_cron_healthy', 1, 300); }
        if ($state === 'locked') { set_transient('sitepulse_cron_fallback_daily_lock', 1, 600); }
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        $this->assertFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public static function suppressedFallbacks(): array {
        return array_map(static function ($state) { return array($state); }, array('non_admin', 'no_capability', 'ajax', 'cron', 'disabled', 'filter', 'healthy_cache', 'locked'));
    }

    public function test_recent_success_prevents_duplicate_fallback_even_when_event_is_overdue(): void {
        $this->overdue();
        update_option('sitepulse_last_api_request', current_time('timestamp') - 21500);
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        $this->assertFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
        $this->assertSame(1, get_transient('sitepulse_cron_healthy'));
        $this->assertSame(300, Sitepulse_Test_WP::$transient_expirations['sitepulse_cron_healthy']);
    }

    public function test_missing_event_is_rescheduled_without_sending_inline(): void {
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        $this->assertGreaterThan(time(), Sitepulse_Cron_Manager::get_next_scheduled_time());
        $this->assertFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
    }

    public function test_healthy_upcoming_event_is_cached(): void {
        wp_schedule_event(time() + 1000, 'daily', 'sitepulse_daily_api_request');
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        $this->assertSame(1, get_transient('sitepulse_cron_healthy'));
        $this->assertFalse(get_transient('sitepulse_cron_fallback_daily_lock'));
    }

    public function test_systemic_stale_cron_triggers_early_fallback_for_overdue_sitepulse_event(): void {
        $this->overdue(700);
        Sitepulse_Test_WP::$cron = array(time() - 3700 => array('another_plugin' => array()));
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        $this->assertNotFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
    }

    public function test_systemic_stale_cron_does_not_trigger_before_sitepulse_grace_period(): void {
        $this->overdue(500);
        Sitepulse_Test_WP::$cron = array(time() - 3700 => array('another_plugin' => array()));
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        $this->assertFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
    }

    public function test_frontend_fallback_runs_without_admin_capability_and_throttles_for_an_hour(): void {
        Sitepulse_Test_WP::$is_admin = false;
        Sitepulse_Test_WP::$capabilities['manage_options'] = false;
        $this->overdue();
        Sitepulse_Cron_Fallback::maybe_run_fallback_frontend();
        $this->assertSame(1, get_transient('sitepulse_cron_fallback_frontend_check'));
        $this->assertSame(3600, Sitepulse_Test_WP::$transient_expirations['sitepulse_cron_fallback_frontend_check']);
        $this->assertNotFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
    }

    public function test_frontend_throttle_avoids_rechecking_an_overdue_event(): void {
        $this->overdue();
        set_transient('sitepulse_cron_fallback_frontend_check', 1, 3600);
        Sitepulse_Cron_Fallback::maybe_run_fallback_frontend();
        $this->assertFalse(get_transient('sitepulse_cron_fallback_daily_lock'));
        $this->assertFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
    }

    public function test_fallback_checks_only_once_per_request_even_after_cache_is_removed(): void {
        $this->overdue();
        set_transient('sitepulse_cron_healthy', 1, 300);
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        delete_transient('sitepulse_cron_healthy');
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        $this->assertFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
    }

    private function executeFallback(): void {
        $ignore_abort = ignore_user_abort();
        $execution_limit = (int) ini_get('max_execution_time');
        try {
            Sitepulse_Cron_Fallback::execute_fallback_task();
        } finally {
            ignore_user_abort((bool) $ignore_abort);
            set_time_limit($execution_limit);
        }
    }

    public function test_successful_fallback_releases_browser_before_http_reschedules_and_unlocks(): void {
        Sitepulse_Cron_Manager::init();
        Sitepulse_Cron_Manager::unschedule_daily_api_request();
        $this->overdue();
        set_transient('sitepulse_cron_fallback_daily_lock', 1, 600);
        Sitepulse_Test_WP::$http_queue[] = function ($url, $args) {
            $this->assertSame(1, Sitepulse_Test_WP::$finished_requests, 'The browser connection must close before the blocking API call.');
            return Sitepulse_Test_WP::response(array('license_key' => 'fallback-license'));
        };
        $before = time();
        $this->executeFallback();
        $event = wp_get_scheduled_event('sitepulse_daily_api_request');
        $this->assertGreaterThanOrEqual($before + 21600, $event->timestamp);
        $this->assertSame(21600, $event->interval);
        $this->assertCount(1, Sitepulse_Test_WP::$scheduled);
        $this->assertSame('fallback-license', get_option('sitepulse_license_key'));
        $this->assertFalse(get_transient('sitepulse_cron_fallback_daily_lock'));
    }

    public function test_failed_fallback_keeps_overdue_schedule_and_releases_lock(): void {
        $this->overdue();
        $original = wp_next_scheduled('sitepulse_daily_api_request');
        set_transient('sitepulse_cron_fallback_daily_lock', 1, 600);
        Sitepulse_Test_WP::$http_queue[] = new WP_Error('http_request_failed', 'timeout');
        $this->executeFallback();
        $this->assertSame($original, wp_next_scheduled('sitepulse_daily_api_request'));
        $this->assertFalse(get_transient('sitepulse_cron_fallback_daily_lock'));
        $this->assertCount(1, Sitepulse_Test_WP::$http_requests);
    }

    /** @dataProvider diagnosticScheduling */
    public function test_ai_polling_schedule_follows_diagnostic_state($status, bool $expected): void {
        Sitepulse_AI_Diagnostic_Cron::init();
        update_option('sitepulse_ai_diagnostic_status', $status);
        if (!$expected) { Sitepulse_AI_Diagnostic_Cron::schedule_status_check(); }
        Sitepulse_AI_Diagnostic_Cron::maybe_schedule_on_init();
        $this->assertSame($expected, Sitepulse_AI_Diagnostic_Cron::is_scheduled());
        if ($expected) {
            $event = wp_get_scheduled_event('sitepulse_check_ai_diagnostic_status');
            $this->assertSame(60, $event->interval);
            $this->assertSame('sitepulse_every_minute', $event->schedule);
            Sitepulse_AI_Diagnostic_Cron::maybe_schedule_on_init();
            $this->assertCount(1, Sitepulse_Test_WP::$scheduled);
        }
    }

    public static function diagnosticScheduling(): array {
        return array('pending' => array('pending', true), 'processing' => array('processing', true), 'completed' => array('completed', false), 'failed' => array('failed', false), 'empty' => array(null, false));
    }

    public function test_disabled_ai_api_stops_scheduled_polling_without_network(): void {
        update_option('sitepulse_settings', array('external_api_enabled' => false));
        update_option('sitepulse_ai_diagnostic_status', 'pending');
        Sitepulse_AI_Diagnostic_Cron::schedule_status_check();
        Sitepulse_AI_Diagnostic_Cron::check_status_callback();
        $this->assertFalse(Sitepulse_AI_Diagnostic_Cron::is_scheduled());
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    /** @dataProvider polledDiagnosticStates */
    public function test_ai_callback_persists_remote_state_and_stops_only_for_terminal_success(string $status, bool $scheduled): void {
        update_option('sitepulse_ai_diagnostic_status', 'pending');
        update_option('sitepulse_ai_diagnostic_report_id', 'report-1');
        Sitepulse_AI_Diagnostic_Cron::schedule_status_check();
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('success' => true, 'status' => $status, 'diagnostic_result' => array('summary' => 'Done')));
        Sitepulse_AI_Diagnostic_Cron::check_status_callback();
        $this->assertSame($status, get_option('sitepulse_ai_diagnostic_status'));
        $this->assertSame($scheduled, Sitepulse_AI_Diagnostic_Cron::is_scheduled());
        $this->assertCount(1, Sitepulse_Test_WP::$http_requests);
    }

    public static function polledDiagnosticStates(): array {
        return array('processing' => array('processing', true), 'completed' => array('completed', false), 'failed' => array('failed', false), 'none' => array('none', false));
    }
}

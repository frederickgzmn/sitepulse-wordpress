<?php

require_once SITEPULSE_PATH . 'inc/cron_fallback.php';

final class CronCoverageTest extends Sitepulse_Test_Case {
    private function overdue(): void {
        wp_schedule_event(time() - 900, 'daily', 'sitepulse_daily_api_request');
    }

    public function test_fallback_initialization_registers_admin_frontend_and_legacy_entry_points(): void {
        Sitepulse_Cron_Fallback::init();
        $this->assertSame(20, has_action('admin_init', array('Sitepulse_Cron_Fallback', 'maybe_run_fallback')));
        $this->assertSame(10, has_action('template_redirect', array('Sitepulse_Cron_Fallback', 'maybe_run_fallback_frontend')));
        $this->assertSame(10, has_action('sitepulse_cron_fallback_immediate', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_cron_request_constant_prevents_admin_and_frontend_fallback(): void {
        define('DOING_CRON', true);
        $this->overdue();
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        Sitepulse_Cron_Fallback::maybe_run_fallback_frontend();
        $this->assertFalse(get_transient('sitepulse_cron_fallback_frontend_check'));
        $this->assertFalse(get_transient('sitepulse_cron_fallback_daily_lock'));
        $this->assertFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
    }

    public function test_frontend_does_not_recheck_after_an_admin_check_in_the_same_request(): void {
        $this->overdue();
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        Sitepulse_Cron_Fallback::maybe_run_fallback_frontend();
        $this->assertFalse(get_transient('sitepulse_cron_fallback_frontend_check'));
    }

    public function test_frontend_skips_wordpress_cron_requests(): void {
        Sitepulse_Test_WP::$doing_cron = true;
        Sitepulse_Cron_Fallback::maybe_run_fallback_frontend();
        $this->assertFalse(get_transient('sitepulse_cron_fallback_frontend_check'));
        $this->assertFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
    }

    /** @dataProvider healthyCronQueues */
    public function test_recently_overdue_event_does_not_trigger_fallback_when_global_cron_is_healthy(array $cron): void {
        $this->overdue();
        Sitepulse_Test_WP::$cron = $cron ? array(time() + 3600 => array('other_hook' => array())) : array();
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        $this->assertFalse(get_transient('sitepulse_cron_fallback_daily_lock'));
        $this->assertSame(1, get_transient('sitepulse_cron_healthy'));
    }

    public static function healthyCronQueues(): array {
        return array('empty queue' => array(array()), 'upcoming other jobs' => array(array('future')));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider schedulerStates
     */
    public function test_action_scheduler_backlog_controls_early_fallback(array $ids, bool $throws, bool $expected): void {
        require_once __DIR__ . '/Support/ActionScheduler.php';
        class_exists('Sitepulse_Cron_Manager');
        Sitepulse_Cron_Manager::unschedule_daily_api_request();
        $this->overdue();
        ActionScheduler_Store::$past_due_ids = $ids;
        ActionScheduler_Store::$throw_on_query = $throws;
        Sitepulse_Cron_Fallback::maybe_run_fallback();
        $this->assertSame($expected, has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')) !== false);
        $this->assertCount(1, ActionScheduler_Store::$queries);
        $query = ActionScheduler_Store::$queries[0];
        $this->assertSame('ids', $query['type']);
        $this->assertSame('pending', $query['query']['status']);
        $this->assertSame('<=', $query['query']['date_compare']);
        $this->assertSame(11, $query['query']['per_page']);
        $this->assertSame('UTC', $query['query']['date']->getTimezone()->getName());
    }

    public static function schedulerStates(): array {
        return array('substantial backlog' => array(range(1, 11), false, true), 'small backlog' => array(array(1, 2), false, false), 'store exception' => array(array(), true, false));
    }

    private function withoutAutoload(callable $callback) {
        $autoloaders = spl_autoload_functions();
        foreach ($autoloaders as $autoloader) { spl_autoload_unregister($autoloader); }
        try { return $callback(); }
        finally { foreach ($autoloaders as $autoloader) { spl_autoload_register($autoloader); } }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_cron_callback_loads_settings_and_api_service_when_no_autoloader_is_available(): void {
        class_exists('Sitepulse_Setup');
        require_once SITEPULSE_PATH . 'inc/cron_manager.php';
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array('license_key' => 'lazy-license'));
        $result = $this->withoutAutoload(static function () { return Sitepulse_Cron_Manager::send_daily_api_request(); });
        $this->assertTrue($result['success']);
        $this->assertSame('lazy-license', get_option('sitepulse_license_key'));
        $this->assertCount(1, Sitepulse_Test_WP::$http_requests);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_fallback_loads_settings_then_safely_skips_an_unavailable_cron_manager(): void {
        class_exists('Sitepulse_Setup');
        $this->withoutAutoload(static function () { Sitepulse_Cron_Fallback::maybe_run_fallback(); });
        $this->assertTrue(class_exists('Sitepulse_Settings', false));
        $this->assertFalse(has_action('shutdown', array('Sitepulse_Cron_Fallback', 'execute_fallback_task')));
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_fallback_executor_leaves_connection_open_when_cron_manager_is_unavailable(): void {
        $this->withoutAutoload(static function () { Sitepulse_Cron_Fallback::execute_fallback_task(); });
        $this->assertSame(0, Sitepulse_Test_WP::$finished_requests);
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public function test_disabled_external_api_does_not_start_ai_polling_on_init(): void {
        update_option('sitepulse_settings', array('external_api_enabled' => false));
        update_option('sitepulse_ai_diagnostic_status', 'pending');
        Sitepulse_AI_Diagnostic_Cron::maybe_schedule_on_init();
        $this->assertFalse(Sitepulse_AI_Diagnostic_Cron::is_scheduled());
    }

    public function test_ai_callback_cleans_up_stale_schedule_after_completion_without_http(): void {
        update_option('sitepulse_ai_diagnostic_status', 'completed');
        Sitepulse_AI_Diagnostic_Cron::schedule_status_check();
        $this->assertGreaterThan(time(), Sitepulse_AI_Diagnostic_Cron::get_next_scheduled_time());
        Sitepulse_AI_Diagnostic_Cron::check_status_callback();
        $this->assertFalse(Sitepulse_AI_Diagnostic_Cron::get_next_scheduled_time());
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }
}

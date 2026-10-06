<?php
final class ActionSchedulerTest extends Sitepulse_Test_Case {
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider pendingCounts
     */
    public function test_only_systemic_past_due_backlog_is_reported($count, $expected): void {
        require_once __DIR__ . '/Support/ActionScheduler.php';
        ActionScheduler_Store::$past_due_ids = $count ? range(1, $count) : array();
        $result = (new Sitepulse_Settings())->get_cron_failure_info();
        $this->assertSame($expected, $result['failing']);
        if ($expected) { $this->assertSame('action_scheduler', $result['source']); $this->assertSame(11, $result['past_due_count']); }
        $query = ActionScheduler_Store::$queries[0];
        $this->assertSame('ids', $query['type']); $this->assertSame('pending', $query['query']['status']); $this->assertSame('<=', $query['query']['date_compare']); $this->assertSame(11, $query['query']['per_page']);
        $this->assertSame('UTC', $query['query']['date']->getTimezone()->getName());
    }
    public static function pendingCounts(): array { return array(array(0, false), array(10, false), array(11, true)); }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_scheduler_failure_does_not_crash_settings_or_claim_a_backlog(): void {
        require_once __DIR__ . '/Support/ActionScheduler.php'; ActionScheduler_Store::$throw_on_query = true;
        $this->assertSame(array('failing' => false), (new Sitepulse_Settings())->get_cron_failure_info());
    }
}

<?php
final class GettingStartedTest extends Sitepulse_Test_Case {
    public function test_steps_complete_as_features_are_used(): void {
        $steps = Sitepulse_Getting_Started::get_steps(array('ai_enabled' => true));
        $this->assertSame(array('analyze', 'plugins', 'requests', 'ai'), array_column($steps, 'id')); $this->assertSame(array(false, false, false, false), array_column($steps, 'done'));
        $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_page_analysis&url=https%3A%2F%2Fexample.test%2F&autorun=1', $steps[0]['url']);
        $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse&sp_view=resource-load', $steps[1]['url']); $this->assertSame('#sp-ai-card', $steps[3]['url']);
        $this->assertTrue(Sitepulse_Getting_Started::should_show($steps));

        update_option('sitepulse_page_analyses', array(array('id' => 'a', 'url' => 'u', 'created_at' => 1, 'samples' => array(array('server_ms' => 1)), 'pending_tokens' => array())));
        Sitepulse_Getting_Started::mark_visited('resource-load'); Sitepulse_Getting_Started::mark_visited('api-monitor');
        $steps = Sitepulse_Getting_Started::get_steps(array('ai_enabled' => true, 'is_ai_pending' => true));
        $this->assertSame(array(true, true, true, true), array_column($steps, 'done')); $this->assertFalse(Sitepulse_Getting_Started::should_show($steps));
        $this->assertTrue(Sitepulse_Getting_Started::get_steps(array('ai_enabled' => true, 'has_ai_report' => true))[3]['done']);
    }
    public function test_ai_step_is_left_out_when_external_reports_are_disabled(): void {
        $this->assertSame(array('analyze', 'plugins', 'requests'), array_column(Sitepulse_Getting_Started::get_steps(array()), 'id'));
    }
    public function test_only_tracked_screens_are_remembered_once_per_user(): void {
        Sitepulse_Getting_Started::mark_visited('settings'); $this->assertSame('', get_user_meta(1, 'sitepulse_getting_started_visited', true));
        Sitepulse_Getting_Started::mark_visited('api-monitor'); $writes = Sitepulse_Test_WP::$user_meta; Sitepulse_Getting_Started::mark_visited('api-monitor');
        $this->assertSame(array('api-monitor'), get_user_meta(1, 'sitepulse_getting_started_visited', true)); $this->assertSame($writes, Sitepulse_Test_WP::$user_meta);
        Sitepulse_Test_WP::$user_id = 2; $this->assertFalse(Sitepulse_Getting_Started::get_steps(array())[2]['done']);
        update_user_meta(2, 'sitepulse_getting_started_visited', 'corrupt'); $this->assertFalse(Sitepulse_Getting_Started::get_steps(array())[2]['done']);
    }
    public function test_dismissed_checklist_stays_hidden_for_that_user_only(): void {
        $steps = Sitepulse_Getting_Started::get_steps(array());
        Sitepulse_Getting_Started::dismiss(); $this->assertFalse(Sitepulse_Getting_Started::should_show($steps));
        Sitepulse_Test_WP::$user_id = 2; $this->assertTrue(Sitepulse_Getting_Started::should_show($steps));
    }
}

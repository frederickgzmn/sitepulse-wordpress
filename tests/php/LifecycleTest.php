<?php
require_once __DIR__ . '/Support/SubprocessCoverage.php';

final class LifecycleTest extends Sitepulse_Test_Case {
    private function runLifecycle(array $input): array {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/lifecycle.php', $input);
        $output = $result['output'];
        $error = $result['error'];
        $this->assertSame(0, $result['status'], $error . $output);
        $this->assertSame('', $error);
        $this->assertJson($output);
        return json_decode($output, true);
    }

    public function test_real_loader_reorders_plugins_without_mutating_option_in_filter(): void {
        $original = array('first/first.php', 'sitepulse/loader.php', 'third/third.php');
        $result = $this->runLifecycle(array('mode' => 'order', 'plugins' => $original));
        $this->assertSame('first/first.php', $result['conflict']);
        $this->assertSame($original, $result['before']);
        $this->assertSame(array('sitepulse/loader.php', 'first/first.php', 'third/third.php'), $result['reordered']);
        $this->assertSame($result['reordered'], $result['after']);
        $this->assertTrue($result['is_first']);
    }

    public function test_loader_does_not_insert_an_inactive_plugin(): void {
        $result = $this->runLifecycle(array('mode' => 'order', 'plugins' => array('other/other.php')));
        $this->assertNull($result['conflict']);
        $this->assertSame(array('other/other.php'), $result['after']);
        $this->assertFalse($result['is_first']);
    }

    public function test_pro_plugin_prevents_free_loader_initialization_and_displays_notice(): void {
        $result = $this->runLifecycle(array('mode' => 'pro'));
        $this->assertFalse($result['loader_initialized']);
        $this->assertStringContainsString('SitePulse Pro is already active', $result['notice']);
    }

    public function test_activation_enables_trackers_and_deactivation_removes_cron_tasks(): void {
        $result = $this->runLifecycle(array('mode' => 'activation'));
        $this->assertTrue($result['activated']['profiler']);
        $this->assertTrue($result['activated']['curl']);
        $this->assertTrue($result['activated']['plugins']);
        $this->assertTrue($result['activated']['redirect']);
        $this->assertNotEmpty($result['activated']['scheduled']);
        $this->assertSame(array(), $result['remaining']);
    }

    public function test_disabled_notice_is_hidden_after_dismissal_or_enabling_trackers(): void {
        $visible = $this->runLifecycle(array('mode' => 'notice', 'enabled' => false, 'dismissed' => false));
        $this->assertStringContainsString('monitoring is paused', $visible['notice']);
        $this->assertSame('', $this->runLifecycle(array('mode' => 'notice', 'enabled' => false, 'dismissed' => true))['notice']);
        $this->assertSame('', $this->runLifecycle(array('mode' => 'notice', 'enabled' => true, 'dismissed' => false))['notice']);
    }

    public function test_uninstall_removes_settings_and_dismissals_but_preserves_other_plugins(): void {
        $result = $this->runLifecycle(array('mode' => 'uninstall', 'options' => array('sitepulse_settings' => array('cron_disabled' => true), 'sitepulse_profiler_stats' => array('data'), 'sitepulse_curl_api_events' => array('data'), 'sitepulse_onboarding_completed' => true, 'unrelated' => 'keep')));
        $this->assertSame(array('unrelated' => 'keep'), $result['options']);
        $this->assertSame(array('unrelated' => 'keep'), $result['user_meta'][1]);
        $this->assertFalse($result['redirect']);
    }
    public function test_loader_direct_access_guard_terminates_without_initializing_wordpress(): void {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/lifecycle.php', array('mode' => 'direct_access'));
        $this->assertSame(0, $result['status']); $this->assertSame('', $result['output']);
    }
    public function test_autosave_skips_loader_initialization(): void {
        $result = $this->runLifecycle(array('mode' => 'autosave'));
        $this->assertSame(0, $result['before_init']); $this->assertFalse($result['activation']);
    }
    public function test_first_plugin_order_is_unchanged_and_invalid_option_does_not_override_core(): void {
        $result = $this->runLifecycle(array('mode' => 'order', 'plugins' => array('sitepulse/loader.php', 'other/main.php')));
        $this->assertTrue($result['is_first']); $this->assertSame($result['before'], $result['after']);
        $result = $this->runLifecycle(array('mode' => 'invalid_option'));
        $this->assertFalse($result['result']); $this->assertSame('corrupt', $result['stored']);
    }
    /** @dataProvider oneDisabledTracker */
    public function test_disabled_tracker_notice_identifies_the_disabled_feature($profiler, $curl, $message): void {
        $result = $this->runLifecycle(array('mode' => 'notice', 'enabled' => $profiler, 'curl_enabled' => $curl, 'dismissed' => false));
        $this->assertStringContainsString($message, $result['notice']);
    }
    public static function oneDisabledTracker(): array { return array(array(false, true, 'Performance Monitor is disabled'), array(true, false, 'External Requests tracker is disabled')); }
    /** @dataProvider onboardingNoticeCases */
    public function test_onboarding_notice_respects_page_completion_and_dismissal_state($screen, $completed, $dismissed, $notice_dismissed, $visible): void {
        $result = $this->runLifecycle(array('mode' => 'onboarding_notice', 'screen' => $screen, 'completed' => $completed, 'dismissed' => $dismissed, 'notice_dismissed' => $notice_dismissed));
        if ($visible) { $this->assertStringContainsString('setup wizard', $result['notice']); $this->assertStringContainsString('admin.php?page=wpsp_sitepulse_onboarding', $result['notice']); }
        else { $this->assertSame('', $result['notice']); }
    }
    public static function onboardingNoticeCases(): array { return array(array(null, false, true, false, false), array('dashboard', false, true, false, false), array('toplevel_page_wpsp_sitepulse', true, true, false, false), array('toplevel_page_wpsp_sitepulse', false, false, false, false), array('toplevel_page_wpsp_sitepulse', false, true, true, false), array('toplevel_page_wpsp_sitepulse', false, true, false, true)); }
    public function test_translations_load_from_plugin_language_directory_on_disk(): void {
        $result = $this->runLifecycle(array('mode' => 'translation'));
        $this->assertSame('sitepulse', $result['domains'][0][0]);
        $this->assertSame($result['expected'], $result['domains'][0][1]);
        $this->assertStringNotContainsString('https://', $result['domains'][0][1]);
    }
    public function test_uninstall_requires_wordpress_explicit_uninstall_flag(): void {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/lifecycle.php', array('mode' => 'uninstall_guard'));
        $this->assertSame(0, $result['status']); $this->assertSame('', $result['output']);
    }
}

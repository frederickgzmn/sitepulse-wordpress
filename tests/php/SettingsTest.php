<?php
final class SettingsTest extends Sitepulse_Test_Case {
    public function test_defaults_preserve_email_and_cron_and_allow_external_api(): void {
        $settings = new Sitepulse_Settings();
        $this->assertFalse($settings->is_email_blocking_enabled()); $this->assertFalse($settings->is_cron_disabled()); $this->assertTrue($settings->is_external_api_enabled());
        $settings->maybe_apply_settings();
        $this->assertSame(array('subject' => 'hello'), apply_filters('wp_mail', array('subject' => 'hello')));
        $this->assertSame(array('url' => 'cron'), apply_filters('cron_request', array('url' => 'cron')));
    }
    /** @dataProvider emailModes */
    public function test_email_mode_selectively_installs_and_removes_blocking_hooks($mode, $blocks_mail, $blocks_smtp): void {
        $settings = new Sitepulse_Settings();
        $settings->set_email_blocking_mode($mode); $settings->set_email_blocking_enabled(true); $settings->maybe_apply_settings();
        $this->assertSame($blocks_mail ? false : 'mail', apply_filters('wp_mail', 'mail'));
        $this->assertSame($blocks_smtp ? false : 'smtp', apply_filters('wp_mail_smtp_get_mailer', 'smtp'));
        $this->assertSame($blocks_smtp ? array() : array('host' => 'smtp.test'), apply_filters('wp_mail_smtp_providers_smtp_get_options', array('host' => 'smtp.test')));
        $settings->set_email_blocking_enabled(false);
        $this->assertSame('mail', apply_filters('wp_mail', 'mail')); $this->assertSame('smtp', apply_filters('wp_mail_smtp_get_mailer', 'smtp'));
    }
    public static function emailModes(): array { return array(array('sendmail_block', true, false), array('smtp_block', false, true), array('both', true, true)); }
    public function test_switching_mode_removes_previous_hooks_and_invalid_mode_preserves_settings(): void {
        $settings = new Sitepulse_Settings(); $settings->set_email_blocking_enabled(true); $settings->maybe_apply_settings();
        $settings->set_email_blocking_mode('smtp_block');
        $this->assertSame('mail', apply_filters('wp_mail', 'mail')); $this->assertFalse(apply_filters('wp_mail_smtp_get_mailer', 'smtp'));
        $this->assertFalse($settings->set_email_blocking_mode('invalid')); $this->assertSame('smtp_block', $settings->get_email_blocking_mode());
    }
    public function test_cron_toggle_blocks_requests_and_disabling_restores_them(): void {
        $settings = new Sitepulse_Settings(); $settings->maybe_apply_settings(); $settings->set_cron_disabled(true);
        $this->assertFalse(apply_filters('cron_request', array('url' => 'cron')));
        $settings->set_cron_disabled(false); $this->assertSame(array('url' => 'cron'), apply_filters('cron_request', array('url' => 'cron')));
    }
    public function test_batch_update_sanitizes_and_ignores_unknown_keys_without_discarding_other_settings(): void {
        update_option('sitepulse_settings', array('custom_extension' => 'preserved'));
        $settings = new Sitepulse_Settings();
        $this->assertTrue($settings->update_settings(array('email_blocking_enabled' => 1, 'email_blocking_mode' => 'both', 'external_api_enabled' => 0, 'recovery_mode_email_addresses' => '<b>admin@example.test</b>', 'unknown' => 'ignored')));
        $stored = get_option('sitepulse_settings');
        $this->assertTrue($stored['email_blocking_enabled']); $this->assertFalse($stored['external_api_enabled']);
        $this->assertSame('admin@example.test', $stored['recovery_mode_email_addresses']); $this->assertSame('preserved', $stored['custom_extension']); $this->assertArrayNotHasKey('unknown', $stored);
        $this->assertFalse($settings->update_settings(array('unknown' => true, 'email_blocking_mode' => 'bad')));
    }
    public function test_reset_deactivate_and_uninstall_release_hooks_and_handle_persisted_settings(): void {
        $settings = new Sitepulse_Settings(); $settings->update_settings(array('email_blocking_enabled' => true, 'cron_disabled' => true)); $settings->maybe_apply_settings();
        $settings->cleanup_on_deactivate();
        $this->assertFalse($settings->is_email_blocking_enabled()); $this->assertFalse($settings->is_cron_disabled()); $this->assertSame('mail', apply_filters('wp_mail', 'mail'));
        $settings->set_external_api_enabled(false); $settings->reset_all_settings(); $this->assertTrue($settings->is_external_api_enabled());
        $settings->cleanup_on_uninstall(); $this->assertArrayNotHasKey('sitepulse_settings', Sitepulse_Test_WP::$options);
    }
    /** @dataProvider overdueCases */
    public function test_cron_failure_reports_the_oldest_overdue_event($age, $failing, $fragment): void {
        Sitepulse_Test_WP::$cron = array(time() - $age => array('job' => array()));
        $info = (new Sitepulse_Settings())->get_cron_failure_info();
        $this->assertSame($failing, $info['failing']);
        if ($failing) { $this->assertSame('wp_cron', $info['source']); $this->assertStringContainsString($fragment, $info['description']); }
    }
    public static function overdueCases(): array { return array(array(-300, false, ''), array(300, false, ''), array(900, true, '15 minutes'), array(7200, true, '2 hours'), array(172800, true, '2 days')); }
    public function test_intentionally_disabled_cron_is_not_reported_as_failed(): void {
        Sitepulse_Test_WP::$cron = array(time() - 90000 => array('job' => array())); $settings = new Sitepulse_Settings(); $settings->set_cron_disabled(true);
        $this->assertSame(array('failing' => false), $settings->get_cron_failure_info());
    }
}

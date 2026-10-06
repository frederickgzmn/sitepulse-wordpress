<?php

final class ErrorHandlerTest extends Sitepulse_Test_Case {
    public function test_warning_is_logged_without_consuming_normal_error_handling(): void {
        $this->assertFalse(Sitepulse_Error_Handler::handle_error(E_WARNING, 'Test warning', ABSPATH . 'example.php', 42));
        $log = Sitepulse_Error_Handler::get_error_log();
        $this->assertSame(1, $log['total_count']);
        $this->assertCount(1, $log['errors']);
        $this->assertSame('Test warning', $log['errors'][0]['message']);
        $this->assertSame(42, $log['errors'][0]['line']);
        $this->assertFalse($log['errors'][0]['is_fatal']);
        $this->assertNotEmpty($log['errors'][0]['call_stack']);
    }

    public function test_notices_and_filtered_warnings_are_not_logged(): void {
        Sitepulse_Error_Handler::handle_error(E_NOTICE, 'Notice', __FILE__, 1);
        $previous = error_reporting(E_ERROR);
        try { Sitepulse_Error_Handler::handle_error(E_WARNING, 'Hidden warning', __FILE__, 1); }
        finally { error_reporting($previous); }
        $this->assertSame(array(), Sitepulse_Error_Handler::get_error_log()['errors']);
    }

    public function test_retention_keeps_recent_warnings_and_two_days_of_fatal_errors(): void {
        $now = current_time('timestamp');
        $entries = array(
            array('message' => 'old warning', 'timestamp' => $now - 3700, 'is_fatal' => false),
            array('message' => 'recent warning', 'timestamp' => $now - 3500, 'is_fatal' => false),
            array('message' => 'old fatal', 'timestamp' => $now - 173000, 'is_fatal' => true),
            array('message' => 'recent fatal', 'timestamp' => $now - 80000, 'is_fatal' => true, 'date' => '2026-10-03 10:00:00'),
            array('message' => 'missing timestamp'),
            array('message' => 'invalid date', 'date' => 'not a date'),
        );
        update_option('sitepulse_error_log', array('errors' => $entries, 'total_count' => 10, 'last_error_date' => null));
        $log = Sitepulse_Error_Handler::get_error_log();
        $this->assertSame(array('recent warning', 'recent fatal'), array_column($log['errors'], 'message'));
        $this->assertSame(10, $log['total_count']);
        $this->assertSame('2026-10-03 10:00:00', $log['last_error_date']);
        $stats = Sitepulse_Error_Handler::get_error_log_stats();
        $this->assertSame(2, $stats['error_count']);
        $this->assertSame(1, $stats['fatal_count']);
        $this->assertSame(1, $stats['warning_count']);
        $this->assertSame($log, get_option('sitepulse_error_log'));
    }

    public function test_corrupt_log_is_recovered_and_can_be_cleared(): void {
        update_option('sitepulse_error_log', 'invalid data');
        $this->assertSame(array('errors' => array(), 'total_count' => 0, 'last_error_date' => null), Sitepulse_Error_Handler::get_error_log());
        Sitepulse_Error_Handler::handle_error(E_WARNING, 'Recovered', __FILE__, 1);
        $this->assertSame(1, Sitepulse_Error_Handler::get_error_log_stats()['error_count']);
        $this->assertTrue(Sitepulse_Error_Handler::clear_error_log());
        $this->assertSame(0, Sitepulse_Error_Handler::get_error_log_stats()['error_count']);
    }

    public function test_recovery_email_validation_synchronization_and_clear(): void {
        $this->assertFalse(Sitepulse_Error_Handler::set_recovery_email('not-an-email'));
        $this->assertNull(Sitepulse_Error_Handler::get_recovery_email());
        $this->assertTrue(Sitepulse_Error_Handler::set_recovery_email('recovery@example.test'));
        $this->assertSame('recovery@example.test', get_option('sitepulse_recovery_mode_email_addresses'));
        $this->assertSame('recovery@example.test', Sitepulse_Settings::getInstance()->get_recovery_mode_email_addresses());
        $this->assertSame('recovery@example.test', Sitepulse_Error_Handler::get_recovery_email());
        $this->assertTrue(Sitepulse_Error_Handler::set_recovery_email(''));
        $this->assertNull(Sitepulse_Error_Handler::get_recovery_email());
    }

    public function test_recovery_email_prefers_settings_and_falls_back_to_legacy_option(): void {
        update_option('sitepulse_recovery_mode_email_addresses', 'legacy@example.test');
        $this->assertSame('legacy@example.test', Sitepulse_Error_Handler::get_recovery_email());
        update_option('sitepulse_settings', array('recovery_mode_email_addresses' => 'settings@example.test'));
        $this->assertSame('settings@example.test', Sitepulse_Error_Handler::get_recovery_email());
    }

    public function test_display_escapes_error_and_stack_content_and_shortens_paths(): void {
        $output = Sitepulse_Error_Handler::format_error_for_display(array(
            'type' => '<script>type</script>', 'message' => '<img src=x onerror=alert(1)>',
            'file' => ABSPATH . 'example.php', 'line' => 12,
            'call_stack' => array(array('class' => '<b>Demo</b>', 'type' => '::', 'function' => 'run', 'file' => ABSPATH . 'caller.php', 'line' => 7)),
        ));
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringNotContainsString('<img ', $output);
        $this->assertStringNotContainsString('<b>', $output);
        $this->assertStringNotContainsString(ABSPATH, $output);
        $this->assertStringContainsString('&lt;img', $output);
        $this->assertStringContainsString('example.php:12', $output);
        $this->assertStringContainsString('caller.php:7', $output);
    }

    public function test_display_accepts_empty_or_invalid_inputs(): void {
        $this->assertSame('', Sitepulse_Error_Handler::format_error_for_display(null));
        $this->assertSame('', Sitepulse_Error_Handler::format_call_stack_for_display(array()));
        $this->assertSame('', Sitepulse_Error_Handler::format_call_stack_for_display('invalid'));
    }

    public function test_fatal_notification_is_blocked_but_ordinary_mail_is_preserved(): void {
        $this->assertFalse(Sitepulse_Error_Handler::prevent_wordpress_fatal_error_wp_mail(array('subject' => 'Your site has a CRITICAL ERROR')));
        $ordinary = array('to' => 'customer@example.test', 'subject' => 'Your receipt', 'message' => 'Thank you.');
        $this->assertSame($ordinary, Sitepulse_Error_Handler::prevent_wordpress_fatal_error_wp_mail($ordinary));
        update_option('admin_email', 'admin@example.test');
        $this->assertFalse(Sitepulse_Error_Handler::prevent_wordpress_fatal_error_wp_mail(array('to' => array('admin@example.test'), 'message' => 'fatal exception occurred')));
    }
}

<?php
final class ErrorHandlerRecoveryTest extends Sitepulse_Test_Case {
    public function test_fatal_errors_notify_recovery_address_once_per_hour_and_keep_logging_during_cooldown(): void {
        Sitepulse_Error_Handler::set_recovery_email('recovery@example.test'); update_option('admin_email', 'admin@example.test');
        Sitepulse_Error_Handler::handle_error(E_USER_ERROR, 'Payment worker failed', ABSPATH . 'worker.php', 12);
        $this->assertCount(1, Sitepulse_Test_WP::$mail);
        $mail = Sitepulse_Test_WP::$mail[0];
        $this->assertSame('recovery@example.test', $mail['to']);
        $this->assertSame("A PHP Error has been detected on your WordPress site.\n\nSite: Test Site (https://example.test)\nError Type: PHP Error\nError Message: Payment worker failed\nFile: " . ABSPATH . "worker.php\nLine: 12\nTimestamp: 2026-01-01 12:00:00\n\nThis is an automated notification from SitePulse Error Handler.\nPlease investigate and resolve this issue as soon as possible.\n", $mail['message']); $this->assertStringContainsString('PHP Error', $mail['subject']); $this->assertStringContainsString('Payment worker failed', $mail['message']); $this->assertStringContainsString('worker.php', $mail['message']); $this->assertContains('From: Test Site <admin@example.test>', $mail['headers']);
        Sitepulse_Error_Handler::handle_error(E_USER_ERROR, 'Second worker failed', ABSPATH . 'worker.php', 13);
        $this->assertCount(1, Sitepulse_Test_WP::$mail); $this->assertCount(2, Sitepulse_Error_Handler::get_error_log()['errors']);
        update_option('sitepulse_last_error_notification', current_time('timestamp') - 3600);
        Sitepulse_Error_Handler::handle_error(E_USER_ERROR, 'Third worker failed', ABSPATH . 'worker.php', 14);
        $this->assertCount(2, Sitepulse_Test_WP::$mail); $this->assertSame(current_time('timestamp'), get_option('sitepulse_last_error_notification'));
    }
    public function test_exception_logs_fatal_trace_and_sends_exception_notification(): void {
        Sitepulse_Error_Handler::set_recovery_email('recovery@example.test');
        $exception = $this->buildException();
        Sitepulse_Error_Handler::handle_exception($exception);
        $error = Sitepulse_Error_Handler::get_error_log()['errors'][0];
        $this->assertTrue($error['is_fatal']); $this->assertSame('Uncaught Exception', $error['type']); $this->assertSame('Checkout failed', $error['message']);
        $this->assertNotEmpty($error['call_stack']); $this->assertSame('buildException', $error['call_stack'][0]['function']);
        $this->assertStringContainsString('PHP Exception', Sitepulse_Test_WP::$mail[0]['subject']); $this->assertStringContainsString('Stack Trace:', Sitepulse_Test_WP::$mail[0]['message']);
    }
    private function buildException(): RuntimeException { return new RuntimeException('Checkout failed'); }
    public function test_warning_ring_buffer_keeps_last_hundred_entries_and_lifetime_count(): void {
        for ($index = 0; $index < 103; $index++) { Sitepulse_Error_Handler::handle_error(E_WARNING, 'warning-' . $index, __FILE__, $index); }
        $log = Sitepulse_Error_Handler::get_error_log();
        $this->assertCount(100, $log['errors']); $this->assertSame('warning-3', $log['errors'][0]['message']); $this->assertSame('warning-102', $log['errors'][99]['message']); $this->assertSame(103, $log['total_count']);
        $this->assertSame(array(), Sitepulse_Test_WP::$mail);
    }
    public function test_corrupt_error_collection_is_reported_as_empty_statistics_without_crashing(): void {
        update_option('sitepulse_error_log', array('errors' => 'broken', 'total_count' => 7, 'last_error_date' => null));
        $stats = Sitepulse_Error_Handler::get_error_log_stats(); $this->assertSame(0, $stats['error_count']); $this->assertSame(7, $stats['total_count']);
    }
    public function test_external_file_and_minimal_stack_render_without_leaking_markup(): void {
        $html = Sitepulse_Error_Handler::format_error_for_display(array('message' => 'Example', 'file' => '/external/file.php', 'call_stack' => array(array('function' => 'example'), array('file' => '/external/file.php'), array())));
        $this->assertStringContainsString('Unknown Error:', $html); $this->assertStringContainsString('/external/file.php', $html); $this->assertStringContainsString('>example</code>', $html);
    }
    public function test_wordpress_fatal_email_address_is_disabled(): void { $this->assertSame('', Sitepulse_Error_Handler::prevent_wordpress_fatal_error_email('admin@example.test')); }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_admin_cleanup_removes_old_entries_once_and_frontend_leaves_data_untouched(): void {
        $log = array('errors' => array(array('message' => 'expired', 'timestamp' => current_time('timestamp') - 4000)), 'total_count' => 1, 'last_error_date' => null);
        update_option('sitepulse_error_log', $log); Sitepulse_Test_WP::$is_admin = false;
        Sitepulse_Error_Handler::cleanup_old_errors_on_init(); $this->assertSame($log, get_option('sitepulse_error_log'));
        Sitepulse_Test_WP::$is_admin = true; Sitepulse_Error_Handler::cleanup_old_errors_on_init(); $this->assertSame(array(), get_option('sitepulse_error_log')['errors']);
        update_option('sitepulse_error_log', $log); Sitepulse_Error_Handler::cleanup_old_errors_on_init(); $this->assertSame($log, get_option('sitepulse_error_log'));
    }
    public function test_fatal_without_recovery_email_is_logged_without_sending_mail(): void {
        Sitepulse_Error_Handler::handle_error(E_USER_ERROR, 'No recovery address', __FILE__, 1);
        $this->assertSame(array(), Sitepulse_Test_WP::$mail);
        $this->assertTrue(Sitepulse_Error_Handler::get_error_log()['errors'][0]['is_fatal']);
    }
    public function test_own_notification_exception_requires_exact_subject_and_recovery_recipient(): void {
        Sitepulse_Error_Handler::set_recovery_email('recovery@example.test');
        $own = array('to' => array('recovery@example.test'), 'subject' => '[Test Site] SitePulse: Fatal Error Detected');
        $this->assertSame($own, Sitepulse_Error_Handler::prevent_wordpress_fatal_error_wp_mail($own));
        $own['to'] = array('somebody@example.test');
        $this->assertFalse(Sitepulse_Error_Handler::prevent_wordpress_fatal_error_wp_mail($own));
        $own['to'] = array('recovery@example.test'); $own['subject'] = 'WordPress fatal error';
        $this->assertFalse(Sitepulse_Error_Handler::prevent_wordpress_fatal_error_wp_mail($own));
        $own['subject'] = '[Another site] SitePulse: Fatal Error Detected';
        $this->assertFalse(Sitepulse_Error_Handler::prevent_wordpress_fatal_error_wp_mail($own));
    }
}

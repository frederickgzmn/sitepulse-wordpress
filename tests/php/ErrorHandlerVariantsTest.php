<?php
require_once __DIR__ . '/Support/SubprocessCoverage.php';
final class ErrorHandlerVariantsTest extends Sitepulse_Test_Case {
    private function variant($mode): array { return sitepulse_test_subprocess($this, __DIR__ . '/fixtures/error-handler-variants.php', array('mode' => $mode)); }
    public function test_error_handler_does_not_execute_without_wordpress(): void { $result = $this->variant('guard'); $this->assertSame(0, $result['status']); $this->assertSame('', $result['output']); }
    public function test_init_preserves_previous_handlers_and_is_idempotent(): void {
        $result = $this->variant('previous_handlers'); $this->assertSame(0, $result['status'], $result['error']); $data = json_decode($result['output'], true); $this->assertIsArray($data, $result['output']);
        $this->assertCount(1, $data['errors']); $this->assertSame('Chained warning', $data['errors'][0]['message']); $this->assertSame(array('Chained exception'), $data['exceptions']); $this->assertCount(1, $data['mail']); $this->assertFalse($data['wp_enabled']);
    }
    public function test_fatal_shutdown_logs_actual_engine_error_and_notifies_recovery_address(): void {
        $result = $this->variant('fatal_shutdown'); $this->assertSame(255, $result['status'], $result['error']); $data = json_decode($result['output'], true); $this->assertIsArray($data, $result['output']);
        $this->assertSame(1, $data['log']['total_count']); $this->assertTrue($data['log']['errors'][0]['is_fatal']); $this->assertStringContainsString('Cannot redeclare', $data['log']['errors'][0]['message']);
        $this->assertSame('recovery@example.test', $data['mail'][0]['to']);
        Sitepulse_Error_Handler::set_recovery_email('recovery@example.test');
        $this->assertSame($data['mail'][0], Sitepulse_Error_Handler::prevent_wordpress_fatal_error_wp_mail($data['mail'][0]), 'The plugin must not block its own generated recovery notification.'); $this->assertStringContainsString('Fatal Error', $data['mail'][0]['subject']);
    }
    public function test_shutdown_without_error_does_not_log_or_notify(): void {
        $result = $this->variant('shutdown_without_error'); $this->assertSame(0, $result['status']); $data = json_decode($result['output'], true); $this->assertFalse($data['log']); $this->assertSame(array(), $data['mail']);
    }
}

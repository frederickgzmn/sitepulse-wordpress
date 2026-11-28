<?php
require_once __DIR__ . '/Support/SubprocessCoverage.php';
final class ErrorHandlerVariantsTest extends Sitepulse_Test_Case {
    private function variant($mode): array { return sitepulse_test_subprocess($this, __DIR__ . '/fixtures/error-handler-variants.php', array('mode' => $mode)); }
    public function test_error_handler_does_not_execute_without_wordpress(): void { $result = $this->variant('guard'); $this->assertSame(0, $result['status']); $this->assertSame('', $result['output']); }
    public function test_init_preserves_previous_handlers_and_is_idempotent(): void {
        $result = $this->variant('previous_handlers'); $this->assertSame(0, $result['status'], $result['error']); $data = json_decode($result['output'], true); $this->assertIsArray($data, $result['output']);
        $this->assertCount(1, $data['errors']); $this->assertSame('Chained warning', $data['errors'][0]['message']); $this->assertSame(array('Chained exception'), $data['exceptions']); $this->assertCount(1, $data['mail']); $this->assertSame(array('wp_fatal_error_handler_enabled' => false, 'wp_fatal_error_handler_email' => false, 'wp_mail' => false), $data['hooks'], 'WordPress keeps its own fatal error page and recovery mail.');
    }
    public function test_fatal_shutdown_logs_actual_engine_error_and_notifies_recovery_address(): void {
        $result = $this->variant('fatal_shutdown'); $this->assertSame(255, $result['status'], $result['error']); $data = json_decode($result['output'], true); $this->assertIsArray($data, $result['output']);
        $this->assertSame(1, $data['log']['total_count']); $this->assertTrue($data['log']['errors'][0]['is_fatal']); $this->assertStringContainsString('Cannot redeclare', $data['log']['errors'][0]['message']);
        $this->assertSame('recovery@example.test', $data['mail'][0]['to']);
        Sitepulse_Error_Handler::set_recovery_email('recovery@example.test');
        $this->assertStringContainsString('Fatal Error', $data['mail'][0]['subject']);
    }
    public function test_exceptions_reach_wordpress_fatal_error_check_and_are_logged_once(): void {
        $result = $this->variant('scraping_exception'); $this->assertSame(255, $result['status'], $result['error']); $data = json_decode($result['output'], true); $this->assertIsArray($data, $result['output']);
        $this->assertSame(E_ERROR, $data['last']['type'], 'WordPress reads the error PHP reports at shutdown.'); $this->assertStringContainsString('Uncaught Error: Call to undefined function sitepulse_removed_function()', $data['last']['message']);
        $this->assertSame(1, $data['log']['total_count']); $this->assertSame('Fatal Error', $data['log']['errors'][0]['type']); $this->assertTrue($data['log']['errors'][0]['is_fatal']); $this->assertCount(1, $data['mail']);
    }
    public function test_uncaught_exception_stays_a_fatal_error_for_wordpress_and_is_logged_once_with_its_trace(): void {
        $result = $this->variant('uncaught_exception'); $this->assertSame(255, $result['status'], $result['error']); $data = json_decode($result['output'], true); $this->assertIsArray($data, $result['output']);
        $this->assertSame(E_ERROR, $data['last']['type'], 'Without a fatal error at shutdown WordPress shows a blank page instead of its critical error page.'); $this->assertStringContainsString('Uncaught Error: Call to undefined function sitepulse_removed_function()', $data['last']['message']);
        $this->assertSame(1, $data['log']['total_count']); $this->assertCount(1, $data['log']['errors']); $this->assertSame('Uncaught Exception', $data['log']['errors'][0]['type']); $this->assertTrue($data['log']['errors'][0]['is_fatal']);
        $this->assertCount(1, $data['mail']); $this->assertStringContainsString('PHP Exception', $data['mail'][0]['subject']);
    }
    public function test_syntax_error_in_an_included_file_reaches_wordpress_at_that_file_and_is_logged_once(): void {
        $result = $this->variant('uncaught_parse_error'); $this->assertSame(255, $result['status'], $result['error']); $data = json_decode($result['output'], true); $this->assertIsArray($data, $result['output']);
        $this->assertIsArray($data['last'], 'PHP 7 reported nothing it could show when the syntax error was thrown again as it was.');
        $this->assertContains($data['last']['type'], array(E_ERROR, E_PARSE)); $this->assertStringContainsString('syntax error', $data['last']['message']);
        $this->assertSame($data['file'], $data['last']['file'], 'WordPress pauses the plugin or theme that owns this file in recovery mode.');
        $this->assertSame(1, $data['log']['total_count']); $this->assertSame('Uncaught Exception', $data['log']['errors'][0]['type']); $this->assertCount(1, $data['mail']);
    }
    public function test_syntax_error_on_a_page_wordpress_checks_is_reported_at_its_file_on_php_7_too(): void {
        $result = $this->variant('scraping_parse_error'); $this->assertSame(255, $result['status'], $result['error']); $data = json_decode($result['output'], true); $this->assertIsArray($data, $result['output']);
        $this->assertStringContainsString('syntax error', $data['last']['message'], 'PHP 7 reported "Exception thrown without a stack frame" when the syntax error was thrown again as it was.');
        $this->assertSame($data['file'], $data['last']['file']); $this->assertSame(1, $data['log']['total_count']);
    }
    public function test_exception_an_earlier_handler_gives_back_to_php_is_logged_once(): void {
        $result = $this->variant('earlier_handler_rethrows'); $this->assertSame(255, $result['status'], $result['error']); $data = json_decode($result['output'], true); $this->assertIsArray($data, $result['output']);
        $this->assertStringContainsString('Uncaught RuntimeException: Given back', $data['last']['message']);
        $this->assertSame(1, $data['log']['total_count']); $this->assertSame('Uncaught Exception', $data['log']['errors'][0]['type']); $this->assertCount(1, $data['mail']);
    }
    public function test_shutdown_without_error_does_not_log_or_notify(): void {
        $result = $this->variant('shutdown_without_error'); $this->assertSame(0, $result['status']); $data = json_decode($result['output'], true); $this->assertFalse($data['log']); $this->assertSame(array(), $data['mail']);
    }
}

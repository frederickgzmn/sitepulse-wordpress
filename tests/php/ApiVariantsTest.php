<?php

require_once __DIR__ . '/Support/SubprocessCoverage.php';

final class ApiVariantsTest extends Sitepulse_Test_Case {
    private function runVariant(array $input): array {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/api-variants.php', $input);
        $this->assertSame(0, $result['status'], $result['error']);
        $this->assertSame('', $result['error']);
        return $result;
    }

    /** @dataProvider guardedFiles */
    public function test_direct_access_stops_before_wordpress_dependencies_are_used(string $file): void {
        $this->assertSame('', $this->runVariant(array('mode' => 'guard', 'file' => $file))['output']);
    }

    public static function guardedFiles(): array {
        return array_map(static function ($file) { return array($file); }, array('class/api_service.php', 'inc/api_backend.php', 'inc/cron_manager.php', 'inc/cron_fallback.php', 'inc/ai_diagnostic_cron.php'));
    }

    public function test_plugin_metadata_api_is_loaded_when_wordpress_has_not_loaded_it_yet(): void {
        $result = $this->runVariant(array('mode' => 'plugin_metadata_lazy_load', 'exclude_functions' => array('get_plugin_data')));
        $this->assertSame(array(array('name' => 'Lazy Plugin', 'version' => '2.0', 'slug' => 'lazy')), json_decode($result['output'], true));
    }

    public function test_debug_fallback_logs_trigger_and_failure_then_releases_lock(): void {
        $result = json_decode($this->runVariant(array('mode' => 'debug_fallback'))['output'], true);
        $this->assertFalse($result['lock']);
        $this->assertStringContainsString('Cron fallback triggered (overdue)', $result['errors']);
        $this->assertStringContainsString('Cron fallback API call failed', $result['errors']);
        $this->assertLessThan(time(), $result['next_scheduled']);
    }

    public function test_non_fpm_fallback_flushes_browser_output_before_finishing_background_request(): void {
        $output = $this->runVariant(array('mode' => 'native_flush', 'exclude_functions' => array('fastcgi_finish_request')))['output'];
        $prefix = 'browser response\\n';
        $this->assertStringStartsWith($prefix, $output);
        $result = json_decode(substr($output, strlen($prefix)), true);
        $this->assertSame(0, $result['buffer_level']);
        $this->assertSame('background-license', $result['license']);
        $this->assertFalse($result['lock']);
        $this->assertGreaterThan(time(), $result['next_scheduled']);
    }

    public function test_clear_load_endpoint_clears_real_profiler_and_moves_plugin_first(): void {
        $result = json_decode($this->runVariant(array('mode' => 'clear_load'))['output'], true);
        $this->assertSame(array('success' => true), $result['response']);
        $this->assertSame(array(), $result['events']);
        $this->assertSame(array('sitepulse/loader.php', 'other/plugin.php'), $result['plugins']);
    }

    public function test_memory_dependency_exception_returns_error_and_closes_its_output_buffer(): void {
        $result = json_decode($this->runVariant(array('mode' => 'memory_error'))['output'], true);
        $this->assertSame('memory_check_error', $result['code']);
        $this->assertSame(500, $result['status']);
        $this->assertStringContainsString('Memory conversion unavailable', $result['message']);
        $this->assertSame(0, $result['buffer_level']);
    }

    /** @dataProvider hostAddresses */
    public function test_host_address_fallback_uses_correct_lookup_precedence(array $input, $expected, array $queries, int $hostname_calls): void {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('PHP 8+ allows isolated replacement of disabled external DNS functions; PHP 7.4 retains its native definitions.');
        }
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/api-variants.php', array_merge(array('mode' => 'host_resolution'), $input), array('disable_functions' => 'gethostname,gethostbyname'));
        $this->assertSame(0, $result['status'], $result['error']);
        $this->assertSame('', $result['error']);
        $data = json_decode($result['output'], true);
        $this->assertSame($expected, $data['ip_address']);
        $this->assertSame($queries, $data['queries']);
        $this->assertSame($hostname_calls, $data['hostname_calls']);
    }

    public static function hostAddresses(): array {
        return array(
            'server address takes precedence' => array(array('server_addr' => '192.0.2.1', 'home_url' => 'https://site.test', 'hostname' => 'host.test', 'addresses' => array(), 'local_addr' => '192.0.2.4'), '192.0.2.1', array(), 0),
            'site domain resolves' => array(array('server_addr' => '', 'home_url' => 'https://site.test', 'hostname' => 'host.test', 'addresses' => array('site.test' => '192.0.2.2'), 'local_addr' => ''), '192.0.2.2', array('site.test'), 0),
            'hostname resolves after site failure' => array(array('server_addr' => '', 'home_url' => 'https://site.test', 'hostname' => 'host.test', 'addresses' => array('host.test' => '192.0.2.3'), 'local_addr' => ''), '192.0.2.3', array('site.test', 'host.test'), 1),
            'IIS address after both lookups fail' => array(array('server_addr' => '', 'home_url' => 'https://site.test', 'hostname' => 'host.test', 'addresses' => array(), 'local_addr' => '192.0.2.4'), '192.0.2.4', array('site.test', 'host.test'), 1),
            'no domain or machine hostname' => array(array('server_addr' => '', 'home_url' => '/', 'hostname' => false, 'addresses' => array(), 'local_addr' => ''), null, array(), 1),
            'invalid resolver answer is ignored' => array(array('server_addr' => '', 'home_url' => 'https://site.test', 'hostname' => 'host.test', 'addresses' => array('site.test' => 'invalid-ip', 'host.test' => 'invalid-ip'), 'local_addr' => ''), null, array('site.test', 'host.test'), 1),
        );
    }
}

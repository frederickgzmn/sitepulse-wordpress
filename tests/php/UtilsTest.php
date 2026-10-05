<?php
final class UtilsTest extends Sitepulse_Test_Case {
    /** @dataProvider timeCases */
    public function test_time_format_handles_units_rounding_negative_values_and_cap($time, $unit, $expected): void {
        $this->assertSame($expected, Sitepulse_Utils::format_time($time, $unit));
    }
    public static function timeCases(): array {
        return array('zero' => array(0, 'seconds', '0.00 ms'), 'negative' => array(-1, 'seconds', '0 ms'), 'fraction' => array(.1255, 'seconds', '125.50 ms'), 'below second' => array(999, 'ms', '999.00 ms'), 'second' => array(1000, 'ms', '1.00 s'), 'rounding' => array(2.345, 'seconds', '2.35 s'), 'cap' => array(60001, 'ms', '60.00 s'));
    }
    /** @dataProvider memoryCases */
    public function test_memory_units_use_binary_boundaries($bytes, $expected): void { $this->assertSame($expected, Sitepulse_Utils::format_memory($bytes)); }
    public static function memoryCases(): array { return array(array(0, '0 B'), array(1023, '1023 B'), array(1024, '1.00 KB'), array(1536, '1.50 KB'), array(1048576, '1.00 MB')); }
    public function test_shortpath_removes_only_the_wordpress_root_and_normalizes_windows_separators(): void {
        $this->assertSame('wp-content/plugins/demo.php', Sitepulse_Utils::shortpath(ABSPATH . 'wp-content/plugins/demo.php'));
        $this->assertSame('/outside/config.php', Sitepulse_Utils::shortpath('/outside/config.php'));
        $this->assertSame('C:/outside/config.php', Sitepulse_Utils::shortpath('C:\\outside\\config.php'));
        $this->assertSame('(unknown)', Sitepulse_Utils::shortpath(''));
    }
    /** @dataProvider redactionCases */
    public function test_url_redaction_masks_sensitive_keys_and_preserves_public_query_and_fragment($url, $expected): void { $this->assertSame($expected, Sitepulse_Utils::redact_url_query($url)); }
    public static function redactionCases(): array {
        return array(
            array('https://api.test:8443/v1?q=hello&API_KEY=secret#detail', 'https://api.test:8443/v1?q=hello&API_KEY=%2A%2A%2A%2A%2A%2A%2A%2A#detail'),
            array('/query?email=private%40test.test&page=2', '/query?email=%2A%2A%2A%2A%2A%2A%2A%2A&page=2'),
            array('https://example.test/?foo=a%20b', 'https://example.test/?foo=a%20b'), array('http://example.test:bad/', 'http://example.test:bad/'), array('', ''), array(null, null),
        );
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_active_plugin_helpers_cache_the_request_inventory(): void {
        update_option('active_plugins', array('one/one.php', 'two/two.php'));
        $this->assertSame(2, Sitepulse_Utils::get_active_plugins_count());
        $this->assertSame(array('one/one.php', 'two/two.php'), Sitepulse_Utils::get_active_plugins());
        update_option('active_plugins', array('three/three.php'));
        $this->assertSame(2, Sitepulse_Utils::get_active_plugins_count());
        $this->assertSame(array('one/one.php', 'two/two.php'), Sitepulse_Utils::get_active_plugins());
    }
}

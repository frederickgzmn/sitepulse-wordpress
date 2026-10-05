<?php
require_once __DIR__ . '/Support/LocalFilesystem.php';
final class PluginAdditionalBehaviorTest extends Sitepulse_Test_Case {
    public function test_initialization_installs_frontend_and_admin_hooks_only_in_admin_context(): void {
        $plugin = new Sitepulse_Plugin(); $plugin->init();
        $this->assertNotFalse(has_action('admin_menu')); $this->assertNotFalse(has_action('wp_footer')); $this->assertNotFalse(has_action('admin_bar_menu'));
        $plugin->enqueue_styles_and_scripts(); $this->assertArrayHasKey('jquery', Sitepulse_Test_WP::$scripts); $this->assertSame(SITEPULSE_ADMIN_ASSETS_CSS_URL . 'sitepulse_general.css', Sitepulse_Test_WP::$styles['wpsp_admin_bar']['src']);
        Sitepulse_Test_WP::reset(); Sitepulse_Test_WP::$is_admin = false; (new Sitepulse_Plugin())->init();
        $this->assertFalse(has_action('admin_menu')); $this->assertNotFalse(has_action('wp_footer'));
    }
    public function test_disk_benchmark_reports_unavailable_filesystem_without_writing(): void {
        $GLOBALS['wp_filesystem'] = null; Sitepulse_Test_WP::$filesystem_factory = static function () { return null; };
        $this->assertSame(array('success' => false, 'error' => 'wp_filesystem_unavailable'), (new Sitepulse_Plugin())->sitepulse_disk_write_test());
    }
    public function test_disk_benchmark_initializes_filesystem_and_reports_unwritable_directory(): void {
        $GLOBALS['wp_filesystem'] = null; Sitepulse_Test_WP::$filesystem_factory = static function () { $fs = new Sitepulse_Test_Local_Filesystem(); $fs->failure = 'not_writable'; return $fs; };
        $result = (new Sitepulse_Plugin())->sitepulse_disk_write_test(16);
        $this->assertFalse($result['success']); $this->assertSame('dir_not_writable', $result['error']); $this->assertSame(array(), $GLOBALS['wp_filesystem']->writes);
    }
    public function test_savequeries_initializes_filesystem_and_supports_config_without_opening_php_tag(): void {
        $GLOBALS['wp_filesystem'] = null; Sitepulse_Test_WP::$filesystem_factory = static function () { return new Sitepulse_Test_Local_Filesystem(); };
        file_put_contents(ABSPATH . 'wp-config.php', '// legacy empty configuration');
        try {
            $result = (new Sitepulse_Plugin())->sp_set_savequeries(true);
            $this->assertTrue($result['success']); $this->assertStringStartsWith("<?php\ndefine('SAVEQUERIES', true);", file_get_contents(ABSPATH . 'wp-config.php')); $this->assertSame('// legacy empty configuration', file_get_contents($result['backup']));
        } finally { foreach (glob(ABSPATH . 'wp-config*') ?: array() as $file) { unlink($file); } }
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider memoryUsageLevels
     */
    public function test_memory_badges_follow_actual_allocated_fraction($target_fraction, $expected_badge): void {
        // Keep headroom above PHP's allocated pages while exercising the high-usage thresholds.
        $buffer = str_repeat('x', 20 * 1048576);
        $old = ini_get('memory_limit');
        try {
            $limit = (int) ceil(memory_get_usage(true) / $target_fraction);
            ini_set('memory_limit', (string) $limit);
            $info = (new Sitepulse_Plugin())->sp_get_memory_info();
            $this->assertSame($limit, $info['limit_bytes']); $this->assertSame($expected_badge, $info['percent_class']);
        } finally { ini_set('memory_limit', $old); unset($buffer); }
    }
    public static function memoryUsageLevels(): array { return array(array(.6, 'bg-warning text-dark'), array(.8, 'bg-warning text-dark'), array(.95, 'bg-danger'), array(.3, 'bg-secondary')); }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_memory_report_uses_wordpress_unit_conversion_when_available(): void {
        function wp_convert_hr_to_bytes($value) { return (int) $value * 1048576; }
        $old = ini_get('memory_limit');
        try { ini_set('memory_limit', '256M'); $this->assertSame(268435456, (new Sitepulse_Plugin())->sp_get_memory_info()['limit_bytes']); }
        finally { ini_set('memory_limit', $old); }
    }
    public function test_savequeries_regex_failure_returns_error_and_preserves_configuration(): void {
        $GLOBALS['wp_filesystem'] = new Sitepulse_Test_Local_Filesystem();
        $original = "<?php\ndefine('SAVEQUERIES', false);\n";
        file_put_contents(ABSPATH . 'wp-config.php', $original);
        $old = ini_get('pcre.backtrack_limit');
        try {
            ini_set('pcre.backtrack_limit', '0');
            $result = (new Sitepulse_Plugin())->sp_set_savequeries(true);
        } finally { ini_set('pcre.backtrack_limit', $old); }
        try {
            $this->assertFalse($result['success']);
            $this->assertSame('compose_failed', $result['error']);
            $this->assertSame($original, file_get_contents(ABSPATH . 'wp-config.php'));
            $this->assertSame(array(), $GLOBALS['wp_filesystem']->writes);
        } finally { foreach (glob(ABSPATH . 'wp-config*') ?: array() as $file) { unlink($file); } }
    }
}

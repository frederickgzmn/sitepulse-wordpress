<?php

require_once __DIR__ . '/Support/LocalFilesystem.php';

final class PluginTest extends Sitepulse_Test_Case {
    private $plugin;

    protected function setUp(): void {
        parent::setUp();
        $this->plugin = new Sitepulse_Plugin();
        $GLOBALS['wp_filesystem'] = new Sitepulse_Test_Local_Filesystem();
    }

    protected function tearDown(): void {
        foreach (glob(ABSPATH . 'wp-config*') ?: array() as $file) { unlink($file); }
        $uploads = wp_upload_dir();
        foreach (glob($uploads['basedir'] . '/sitepulse/*') ?: array() as $file) {
            if (is_file($file)) { unlink($file); }
        }
        unset($GLOBALS['wp_filesystem']);
        parent::tearDown();
    }

    public function test_savequeries_requires_administrator_permission(): void {
        Sitepulse_Test_WP::$capabilities['manage_options'] = false;
        $this->assertSame('forbidden', $this->plugin->sp_set_savequeries(true)['error']);
    }

    public function test_savequeries_reports_missing_config_without_creating_one(): void {
        $this->assertSame('missing_file', $this->plugin->sp_set_savequeries(true)['error']);
        $this->assertFileDoesNotExist(ABSPATH . 'wp-config.php');
    }

    public static function configExamples(): array {
        return array(
            'replace existing false' => array("<?php\ndefine('SAVEQUERIES', false);\n", true, false),
            'disable existing true' => array("<?php\ndefine(\"SAVEQUERIES\", TRUE);\n", false, true),
            'insert before settings' => array("<?php\nrequire_once(ABSPATH . 'wp-settings.php');\n", true, null),
            'fallback after PHP tag' => array("<?php\n// A configuration\n", true, null),
        );
    }

    /** @dataProvider configExamples */
    public function test_savequeries_updates_only_temporary_config_and_preserves_backup($original, $enable, $previous): void {
        file_put_contents(ABSPATH . 'wp-config.php', $original);
        $result = $this->plugin->sp_set_savequeries($enable);
        $this->assertTrue($result['success']);
        $this->assertTrue($result['changed']);
        $this->assertSame($previous, $result['previous']);
        $this->assertSame($original, file_get_contents($result['backup']));
        $updated = file_get_contents(ABSPATH . 'wp-config.php');
        $this->assertStringContainsString($enable ? "define('SAVEQUERIES', true);" : "define('SAVEQUERIES', false);", $updated);
        $this->assertSame(1, substr_count($updated, 'SAVEQUERIES'));
        $this->assertSame(array(), glob(wp_upload_dir()['basedir'] . '/sitepulse/wp-config.tmp.*'));
    }

    public function test_savequeries_identical_value_is_noop_until_forced(): void {
        $original = "<?php\ndefine('SAVEQUERIES', true);\n";
        file_put_contents(ABSPATH . 'wp-config.php', $original);
        $result = $this->plugin->sp_set_savequeries(true);
        $this->assertFalse($result['changed']);
        $this->assertNull($result['backup']);
        $this->assertSame(array(), $GLOBALS['wp_filesystem']->writes);
        $this->assertTrue($this->plugin->sp_set_savequeries(true, true)['changed']);
    }

    public static function filesystemFailures(): array {
        return array_map(function ($error) { return array($error); }, array('not_readable', 'not_writable', 'read_failed', 'backup_failed', 'write_failed', 'rename_failed'));
    }

    /** @dataProvider filesystemFailures */
    public function test_savequeries_failure_preserves_original_and_removes_partial_files($error): void {
        $original = "<?php\n// retain this\n";
        file_put_contents(ABSPATH . 'wp-config.php', $original);
        $GLOBALS['wp_filesystem']->failure = $error;
        $result = $this->plugin->sp_set_savequeries(true);
        $this->assertFalse($result['success']);
        $this->assertSame($error, $result['error']);
        $this->assertSame($original, file_get_contents(ABSPATH . 'wp-config.php'));
        $this->assertSame(array(), glob(wp_upload_dir()['basedir'] . '/sitepulse/wp-config.tmp.*'));
    }

    public function test_disk_benchmark_clamps_iterations_and_cleans_up_files(): void {
        $result = $this->plugin->sitepulse_disk_write_test(17, 20);
        $this->assertTrue($result['success']);
        $this->assertSame(5, $result['iterations']);
        $this->assertSame(85, $result['total_written_bytes']);
        $this->assertCount(5, $result['times_ms']);
        $this->assertGreaterThanOrEqual(0, $result['min_ms']);
        $this->assertSame(array(), glob($result['dir'] . 'spdisk_*'));
    }

    public function test_disk_benchmark_clamps_nonpositive_inputs(): void {
        $result = $this->plugin->sitepulse_disk_write_test(0, -1);
        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['size_bytes']);
        $this->assertSame(1, $result['iterations']);
        $this->assertSame(1, $result['total_written_bytes']);
    }

    public function test_disk_benchmark_cleans_up_partial_write(): void {
        $GLOBALS['wp_filesystem']->failure = 'write_failed';
        $result = $this->plugin->sitepulse_disk_write_test(16);
        $this->assertSame('put_contents_failed', $result['error']);
        $this->assertFileDoesNotExist($result['file']);
    }

    public function test_memory_info_handles_unlimited_memory(): void {
        $original = ini_get('memory_limit');
        try {
            ini_set('memory_limit', '-1');
            $info = $this->plugin->sp_get_memory_info();
            $this->assertSame(-1, $info['limit_bytes']);
            $this->assertSame('—', $info['percent']);
            $this->assertStringContainsString('unlimited', $info['formatted']);
            $this->assertGreaterThan(0, $info['allocated']);
        } finally { ini_set('memory_limit', $original); }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_slow_queries_reports_disabled_collection(): void {
        $this->assertSame('savequeries_disabled', $this->plugin->sp_get_slow_queries()['error']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_slow_queries_filters_converts_sorts_and_limits(): void {
        define('SAVEQUERIES', true);
        $GLOBALS['wpdb'] = new stdClass();
        $GLOBALS['wpdb']->queries = array(
            array('SELECT fast', 0.02, 'fast'),
            array('SELECT boundary', 0.1, 'boundary'),
            array('SELECT slowest', 0.75, 'slow'),
            'invalid entry',
            array('SELECT middle', 0.2),
        );
        $this->assertSame(array(
            'success' => true,
            'results' => array(
                array('sql' => 'SELECT slowest', 'time_ms' => 750.0, 'caller' => 'slow'),
                array('sql' => 'SELECT middle', 'time_ms' => 200.0, 'caller' => null),
            ),
        ), $this->plugin->sp_get_slow_queries(100, 2));
        $this->assertCount(3, $this->plugin->sp_get_slow_queries(100, 0)['results']);
        $GLOBALS['wpdb']->queries = array();
        $this->assertSame(array('success' => true, 'results' => array()), $this->plugin->sp_get_slow_queries());
    }
}

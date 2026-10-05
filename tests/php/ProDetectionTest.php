<?php
final class ProDetectionTest extends Sitepulse_Test_Case {
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider pluginInventories
     */
    public function test_pro_detection_requires_exact_plugin_directory_and_caches_result($plugins, $expected): void {
        update_option('active_plugins', $plugins);
        $this->assertSame($expected, Sitepulse_Setup::is_pro_active());
        update_option('active_plugins', $expected ? array() : array('sitepulse-pro/loader.php'));
        $this->assertSame($expected, Sitepulse_Setup::is_pro_active());
    }
    public static function pluginInventories(): array {
        return array(array(array(), false), array(array('another-sitepulse-pro/loader.php'), false), array(array('sitepulse-pro/loader.php'), true));
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_pro_version_constant_signals_an_active_external_pro_plugin(): void {
        define('SITEPULSE_PRO_VERSION', '1.0');
        $this->assertTrue(Sitepulse_Setup::is_pro_active());
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_loaded_external_pro_loader_signals_active_pro_plugin(): void {
        // Only the external Pro plugin's class-presence boundary is needed here.
        // No class implemented by this repository is replaced.
        require __DIR__ . '/fixtures/pro-loader-boundary.php';
        $this->assertTrue(Sitepulse_Setup::is_pro_active());
    }
}

<?php
require_once __DIR__ . '/Support/LocalFilesystem.php';
final class EasyModeSaveQueriesTest extends Sitepulse_Test_Case {
    protected function setUp(): void {
        parent::setUp(); $_POST['nonce'] = 'nonce-wp_rest';
        Sitepulse_Test_WP::$filesystem_factory = static function () { return new Sitepulse_Test_Local_Filesystem(); };
    }
    protected function tearDown(): void {
        foreach (glob(ABSPATH . 'wp-config*') ?: array() as $file) { unlink($file); }
        parent::tearDown();
    }
    public function test_savequeries_ajax_writes_real_disposable_config_and_returns_success(): void {
        file_put_contents(ABSPATH . 'wp-config.php', "<?php\ndefine('SAVEQUERIES', false);\n");
        $response = $this->ajaxResponse(array('Sitepulse_Easy_Mode', 'ajax_enable_savequeries'));
        $this->assertTrue($response->success); $this->assertTrue($response->data['changed']);
        $this->assertStringContainsString("define('SAVEQUERIES', true);", file_get_contents(ABSPATH . 'wp-config.php'));
        $this->assertFileExists($response->data['backup']);
    }
    public function test_savequeries_ajax_returns_missing_configuration_error(): void {
        $response = $this->ajaxResponse(array('Sitepulse_Easy_Mode', 'ajax_enable_savequeries'));
        $this->assertFalse($response->success); $this->assertStringContainsString('wp-config.php', $response->data['message']);
        $this->assertFileDoesNotExist(ABSPATH . 'wp-config.php');
    }
}

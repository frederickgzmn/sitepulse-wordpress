<?php
require_once __DIR__ . '/Support/SubprocessCoverage.php';

final class RemoteVariantsTest extends Sitepulse_Test_Case {
    private function runVariant(array $input): array {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/remote-variants.php', $input);
        $this->assertSame(0, $result['status'], $result['error']);
        $this->assertSame('', $result['error']);
        $decoded = json_decode($result['output'], true);
        $this->assertIsArray($decoded, $result['output']);
        return $decoded;
    }

    public function test_development_api_base_changes_the_host_but_not_the_signed_path(): void {
        $result = $this->runVariant(array('mode' => 'api_base', 'constants' => array('SITEPULSE_REMOTE_API_BASE' => 'http://localhost:8000/')));
        $this->assertSame('http://localhost:8000/api/website/remote/check-in', $result['url']);
        $this->assertTrue($result['signed_path']);
        $this->assertSame(array('success' => true), $result['result']);
    }

    /** @dataProvider wordpressConstants */
    public function test_wordpress_constants_decide_core_auto_updates_and_file_changes(array $constants, ?string $reason, string $auto_update): void {
        $this->assertSame(array('reason' => $reason, 'auto_update' => $auto_update), $this->runVariant(array('mode' => 'inventory', 'constants' => $constants)));
    }

    public static function wordpressConstants(): array {
        return array(
            'all core releases' => array(array('WP_AUTO_UPDATE_CORE' => true), null, 'major'),
            'beta releases include majors' => array(array('WP_AUTO_UPDATE_CORE' => 'beta'), null, 'major'),
            'minor releases only' => array(array('WP_AUTO_UPDATE_CORE' => 'minor'), null, 'minor'),
            'core updates off' => array(array('WP_AUTO_UPDATE_CORE' => false), null, 'disabled'),
            'automatic updater off' => array(array('AUTOMATIC_UPDATER_DISABLED' => true), null, 'disabled'),
            'file changes disallowed' => array(array('DISALLOW_FILE_MODS' => true), 'file_mods_disabled', 'disabled'),
        );
    }

    public function test_hosts_that_manage_updates_are_recognized(): void {
        $constants = array('WPE_APIKEY' => 'k', 'KINSTAMU_VERSION' => '2.0', 'PANTHEON_ENVIRONMENT' => 'live', 'IS_ATOMIC' => true, 'GD_SYSTEM_PLUGIN_DIR' => '/gd', 'FLYWHEEL_CONFIG_DIR' => '/fw');
        $this->assertSame(array('wpengine', 'kinsta', 'pantheon', 'wordpress-com', 'godaddy', 'flywheel'), array_column($this->runVariant(array('mode' => 'hosts', 'constants' => $constants)), 'id'));
    }

    /** @dataProvider diskSpace */
    public function test_updates_need_free_disk_space(array $input, array $expected): void {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('PHP 8+ allows isolated replacement of the disabled disk space function; PHP 7.4 retains its native definition.');
        }
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/remote-variants.php', array('mode' => 'disk') + $input, array('disable_functions' => 'disk_free_space'));
        $this->assertSame(0, $result['status'], $result['error']);
        $this->assertSame($expected, json_decode($result['output'], true));
    }

    public static function diskSpace(): array {
        return array(
            'almost full' => array(array('disk_free' => 1000), array('disk_free' => 1000, 'update' => array('status' => 'failed', 'result' => null, 'error' => 'not_enough_disk'))),
            'enough' => array(array('disk_free' => 104857600), array('disk_free' => 104857600, 'update' => null)),
            'the server does not tell' => array(array(), array('disk_free' => null, 'update' => null)),
        );
    }

    public function test_sitepulse_pro_keeps_remote_management_to_itself(): void {
        $this->assertSame(array('can_connect' => false, 'html' => ''), $this->runVariant(array('mode' => 'pro', 'constants' => array('SITEPULSE_PRO_IS_ACTIVE' => true))));
    }

    public function test_block_switch_stops_the_request_after_redirecting(): void {
        $result = $this->runVariant(array('mode' => 'block_exit'));
        $this->assertSame(array(array('url' => 'https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_settings', 'status' => 302)), $result['redirects']);
        $this->assertTrue($result['blocked']);
    }
}

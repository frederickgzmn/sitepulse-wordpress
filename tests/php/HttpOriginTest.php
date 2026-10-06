<?php
final class HttpOriginTest extends Sitepulse_Test_Case {
    /** @dataProvider origins */
    public function test_slow_requests_resolve_mu_theme_upload_and_custom_origins($path_type, $expected): void {
        Sitepulse_Test_WP::$plugins['sitepulse/loader.php'] = array('Name' => 'SitePulse');
        $paths = array('mu' => WPMU_PLUGIN_DIR . '/custom-loader/main.php', 'theme' => get_theme_root() . '/test-theme/inc/service.php', 'uploads' => WP_CONTENT_DIR . '/uploads/snippet.php', 'custom' => ABSPATH . 'custom.php', 'unknown_plugin' => WP_PLUGIN_DIR . '/unregistered/main.php');
        Sitepulse_CurLoader::init();
        Sitepulse_CurLoader::capture(Sitepulse_Test_WP::response(), 'response', 'Requests', array('_wshp_start' => microtime(true) - 2, '_wshp_trace' => array(array('function' => 'no_file'), array('file' => $paths[$path_type], 'line' => 9))), '/relative-url');
        $event = Sitepulse_CurLoader::get_events()[0];
        $this->assertSame($expected, $event['origin']); $this->assertSame('', $event['host']); $this->assertSame(9, $event['line']);
    }
    public static function origins(): array { return array(array('mu', 'MU Plugin: custom-loader'), array('theme', 'Theme: Test Theme'), array('uploads', 'Custom code (wp-content)'), array('custom', 'Custom code'), array('unknown_plugin', 'Plugin: unregistered')); }
}

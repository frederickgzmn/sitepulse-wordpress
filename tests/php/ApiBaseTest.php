<?php
require_once __DIR__ . '/Support/RemoteManagement.php';

/**
 * SITEPULSE_API_BASE points every request of the plugin at another SitePulse API, such as a
 * local one during development. Constants cannot be undefined, so each case runs in its own process.
 */
final class ApiBaseTest extends Sitepulse_Test_Case {
    private function queue(array $body): void {
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response($body);
    }

    private function origin(string $url) {
        $method = new ReflectionMethod('Sitepulse_CurLoader', 'identify_origin_by_url');
        if (PHP_VERSION_ID < 80100) { $method->setAccessible(true); }
        return $method->invoke(null, $url);
    }

    public function test_without_the_setting_the_plugin_talks_to_api_sitepulse_me(): void {
        $this->assertSame('https://api.sitepulse.me', Sitepulse_Api_Service::api_base());
        $this->assertSame('https://api.sitepulse.me/api/ai-diagnostic', Sitepulse_Api_Service::api_url('/api/ai-diagnostic'));
        $this->assertSame('api.sitepulse.me', Sitepulse_Api_Service::api_host());
        $this->assertSame('SitePulse (API)', $this->origin('https://api.sitepulse.me/api/website/website-request'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_every_request_goes_to_the_api_the_setting_names(): void {
        define('SITEPULSE_API_BASE', ' http://api.sitepulse.lndo.site/ ');
        update_option('sitepulse_last_api_request', 1700000000);
        update_option('sitepulse_license_key', 'existing-license');
        $this->queue(array('license_key' => 'existing-license'));
        $this->queue(array('vulnerabilities' => array()));
        $this->queue(array('status' => 'completed', 'diagnostic_result' => array('summary' => 'Done')));
        Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response(array('success' => true));

        Sitepulse_Api_Service::send_website_data();
        Sitepulse_Api_Service::check_vulnerabilities();
        Sitepulse_Api_Service::request_ai_diagnostic();
        (new Sitepulse_Remote_Client(SITEPULSE_TEST_LICENSE))->post('events', array('events' => array()));

        $this->assertSame(array(
            'http://api.sitepulse.lndo.site/api/website/website-request',
            'http://api.sitepulse.lndo.site/api/website/website-vulnerabilities',
            'http://api.sitepulse.lndo.site/api/ai-diagnostic',
            'http://api.sitepulse.lndo.site/api/website/remote/events',
        ), array_column(Sitepulse_Test_WP::$http_requests, 'url'));
        $this->assertSame('api.sitepulse.lndo.site', Sitepulse_Api_Service::api_host());
        $this->assertSame('SitePulse (API)', $this->origin('http://API.sitepulse.lndo.site/api/ai-diagnostic'));

        update_user_meta(1, 'sitepulse_easy_mode', '0');
        Sitepulse_Test_WP::$screen = (object) array('id' => 'plugins');
        (new Sitepulse_Backend(new Sitepulse_Plugin()))->enqueue_styles_and_scripts();
        $this->assertSame('http://api.sitepulse.lndo.site/api/feedback/deactivation', Sitepulse_Test_WP::$localized['sitepulse-deactivation-modal']['SitePulseDeactivationData']['api_endpoint']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_the_remote_management_setting_still_wins_for_remote_management(): void {
        define('SITEPULSE_API_BASE', 'http://api.sitepulse.lndo.site');
        define('SITEPULSE_REMOTE_API_BASE', 'http://127.0.0.1:8099/');
        Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response(array('success' => true));

        (new Sitepulse_Remote_Client(SITEPULSE_TEST_LICENSE))->post('events', array('events' => array()));

        $this->assertSame('http://127.0.0.1:8099/api/website/remote/events', Sitepulse_Test_WP::$http_requests[0]['url']);
        $this->assertSame('http://api.sitepulse.lndo.site', Sitepulse_Api_Service::api_base());
    }

    /**
     * @dataProvider unusableValues
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_values_that_are_not_an_http_address_keep_api_sitepulse_me($value): void {
        define('SITEPULSE_API_BASE', $value);
        $this->assertSame('https://api.sitepulse.me', Sitepulse_Api_Service::api_base());
    }

    public static function unusableValues(): array {
        return array(
            'empty' => array(''),
            'not a URL' => array('local api'),
            'another scheme' => array('ftp://api.sitepulse.lndo.site'),
            'no host' => array('http://'),
            'not a string' => array(8099),
        );
    }
}

<?php
require_once __DIR__ . '/Support/RemoteManagement.php';

final class RemoteClientTest extends Sitepulse_Test_Case {
    private function client(): Sitepulse_Remote_Client { return new Sitepulse_Remote_Client(SITEPULSE_TEST_LICENSE); }

    public function test_signatures_match_the_test_vector_shared_with_the_api(): void {
        $this->assertSame('633c6b913e4b6653592e94e7980dd55c8e66bc4834fb4abd32e7739c1ada1603', Sitepulse_Remote_Client::request_signature('POST', '/api/website/remote/check-in', 'example.com', '1760544000', '5f0c2b1e9a7d4c3b8e6f1a2d3c4b5a69', '{"schema":1}', SITEPULSE_TEST_LICENSE));
        $this->assertSame('e5be8c72903694dda6265dd3fa13f7d3929d34662b8e8fd748fcd8f3487e1b14', Sitepulse_Remote_Client::response_signature('5f0c2b1e9a7d4c3b8e6f1a2d3c4b5a69', '1760544060', '{"success":true}', SITEPULSE_TEST_LICENSE));
    }

    /** @dataProvider homeUrls */
    public function test_domain_is_the_lowercase_host_without_www_port_or_path(string $home, string $domain): void {
        Sitepulse_Test_WP::$home_url = $home;
        $this->assertSame($domain, Sitepulse_Remote_Client::domain());
    }

    public static function homeUrls(): array {
        return array(
            array('https://example.test', 'example.test'),
            array('https://WWW.Example.TEST:8443/blog', 'example.test'),
            array('http://shop.example.test/', 'shop.example.test'),
            array('https://wwwexample.test', 'wwwexample.test'),
        );
    }

    public function test_signed_request_carries_no_license_and_accepts_the_signed_answer(): void {
        Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response(array('success' => true, 'accepted' => 2));

        $this->assertSame(array('success' => true, 'accepted' => 2), $this->client()->post('events', array('events' => array())));

        $request = Sitepulse_Test_WP::$http_requests[0];
        $headers = $request['args']['headers'];
        $this->assertSame('https://api.sitepulse.me/api/website/remote/events', $request['url']);
        $this->assertSame('POST', $request['args']['method']);
        $this->assertSame('{"events":[]}', $request['args']['body']);
        $this->assertSame('example.test', $headers['X-SitePulse-Domain']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $headers['X-SitePulse-Nonce']);
        $this->assertEqualsWithDelta(time(), (int) $headers['X-SitePulse-Timestamp'], 2);
        $this->assertSame(Sitepulse_Remote_Client::request_signature('POST', '/api/website/remote/events', 'example.test', $headers['X-SitePulse-Timestamp'], $headers['X-SitePulse-Nonce'], '{"events":[]}', SITEPULSE_TEST_LICENSE), $headers['X-SitePulse-Signature']);
        $this->assertSame('application/json', $headers['Accept']);
        $this->assertStringNotContainsString(SITEPULSE_TEST_LICENSE, json_encode($request));
    }

    public function test_each_request_uses_a_new_nonce(): void {
        Sitepulse_Test_WP::$http_queue = array(sitepulse_test_remote_response(), sitepulse_test_remote_response());
        $this->client()->post('check-in', array());
        $this->client()->post('check-in', array());
        $this->assertNotSame(Sitepulse_Test_WP::$http_requests[0]['args']['headers']['X-SitePulse-Nonce'], Sitepulse_Test_WP::$http_requests[1]['args']['headers']['X-SitePulse-Nonce']);
    }

    /** @dataProvider untrustedResponses */
    public function test_untrusted_or_failed_responses_are_errors(callable $response, string $code): void {
        Sitepulse_Test_WP::$http_queue[] = $response;
        $result = $this->client()->post('check-in', array('schema' => 1));
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertSame($code, $result->get_error_code());
    }

    public static function untrustedResponses(): array {
        return array(
            'network failure' => array(static function () { return new WP_Error('http_request_failed', 'cURL error 28'); }, 'unreachable'),
            'rejected signature' => array(sitepulse_test_remote_response(array('success' => false), 401), 'http_401'),
            'server error' => array(sitepulse_test_remote_response('<html>', 500), 'http_500'),
            'unsigned' => array(static function () { return Sitepulse_Test_WP::response(array('success' => true)); }, 'bad_signature'),
            'signed with another license' => array(sitepulse_test_remote_response(array('success' => true), 200, array('license' => 'sk_other')), 'bad_signature'),
            'replayed answer to another request' => array(sitepulse_test_remote_response(array('success' => true), 200, array('nonce' => str_repeat('a', 32))), 'bad_signature'),
            'stale' => array(sitepulse_test_remote_response(array('success' => true), 200, array('timestamp' => time() - 3600)), 'bad_signature'),
            'from the future' => array(sitepulse_test_remote_response(array('success' => true), 200, array('timestamp' => time() + 3600)), 'bad_signature'),
            'malformed timestamp' => array(sitepulse_test_remote_response(array('success' => true), 200, array('timestamp' => '1e9')), 'bad_signature'),
            'not JSON' => array(sitepulse_test_remote_response('not json'), 'bad_response'),
            'not successful' => array(sitepulse_test_remote_response(array('success' => false)), 'bad_response'),
        );
    }

    public function test_an_http_error_carries_the_reason_the_api_gives(): void {
        Sitepulse_Test_WP::$http_queue = array(
            Sitepulse_Test_WP::response(array('success' => false, 'error' => 'invalid_signature', 'message' => 'Invalid signature.'), 401),
            // The sentence is kept to 200 characters, here two-byte ones.
            Sitepulse_Test_WP::response(array('success' => false, 'error' => 'site_blocked', 'message' => str_repeat('é', 250)), 403),
            Sitepulse_Test_WP::response('<html>Bad gateway</html>', 502),
            Sitepulse_Test_WP::response('"Unauthorized"', 401),
            Sitepulse_Test_WP::response(array('success' => false, 'error' => array('invalid_signature'), 'message' => 401), 401),
            Sitepulse_Test_WP::response(array('success' => false, 'error' => '', 'message' => ''), 429),
        );
        $error = function (): array {
            $error = $this->client()->post('check-in', array());
            return array($error->get_error_code(), $error->get_error_message(), $error->get_error_data());
        };
        $none = array('error' => null, 'message' => null);

        $this->assertSame(array('http_401', 'HTTP 401', array('error' => 'invalid_signature', 'message' => 'Invalid signature.')), $error());
        $this->assertSame(array('http_403', 'HTTP 403', array('error' => 'site_blocked', 'message' => str_repeat('é', 200))), $error());
        $this->assertSame(array('http_502', 'HTTP 502', $none), $error(), 'Not JSON.');
        $this->assertSame(array('http_401', 'HTTP 401', $none), $error(), 'Not an object.');
        $this->assertSame(array('http_401', 'HTTP 401', $none), $error(), 'Not strings.');
        $this->assertSame(array('http_429', 'HTTP 429', $none), $error(), 'Empty.');
    }

    public function test_network_failure_keeps_the_transport_message(): void {
        Sitepulse_Test_WP::$http_queue[] = new WP_Error('http_request_failed', 'cURL error 28: timed out');
        $this->assertSame('cURL error 28: timed out', $this->client()->post('check-in', array())->get_error_message());
    }
}

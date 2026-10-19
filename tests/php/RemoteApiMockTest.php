<?php
require_once __DIR__ . '/Support/RemoteManagement.php';
require_once __DIR__ . '/Support/Database.php';

/**
 * scripts/remote-api-mock.php, the development stand-in for the API, run with PHP's
 * built-in server and spoken to by the plugin's own client.
 */
final class RemoteApiMockTest extends Sitepulse_Test_Case {
    private $server;
    private $base;
    private $stateFile;

    protected function setUp(): void {
        parent::setUp();
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);
        $this->base = 'http://127.0.0.1:' . $port;
        $this->stateFile = tempnam(sys_get_temp_dir(), 'sitepulse-mock-');
        unlink($this->stateFile);
        $this->server = proc_open(
            array(PHP_BINARY, '-S', '127.0.0.1:' . $port, dirname(__DIR__, 2) . '/scripts/remote-api-mock.php'),
            array(1 => array('file', '/dev/null', 'w'), 2 => array('file', '/dev/null', 'w')),
            $pipes,
            null,
            array('SITEPULSE_MOCK_LICENSE' => SITEPULSE_TEST_LICENSE, 'SITEPULSE_MOCK_STATE' => $this->stateFile) + getenv()
        );
        for ($i = 0; $i < 100 && !@fsockopen('127.0.0.1', $port); $i++) { usleep(50000); }

        // The plugin's requests go to the mock instead of api.sitepulse.me.
        Sitepulse_Test_WP::$http_responder = function ($url, $args) {
            $headers = array();
            foreach ($args['headers'] as $name => $value) { $headers[] = "$name: $value"; }
            $body = file_get_contents($this->base . parse_url($url, PHP_URL_PATH), false, stream_context_create(array('http' => array('method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $args['body'], 'ignore_errors' => true))));
            $status = (int) explode(' ', $http_response_header[0])[1];
            $received = array();
            foreach (array_slice($http_response_header, 1) as $line) {
                $parts = explode(':', $line, 2);
                $received[trim($parts[0])] = trim($parts[1] ?? '');
            }
            return Sitepulse_Test_WP::response((string) $body, $status, $received);
        };
        update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
        Sitepulse_Test_Backups::reset();
        Sitepulse_Test_Backups::updraftplus();
    }

    protected function tearDown(): void {
        proc_terminate($this->server);
        proc_close($this->server);
        if (is_file($this->stateFile)) { unlink($this->stateFile); }
        Sitepulse_Test_Backups::reset();
        unset($GLOBALS['wpdb']);
        parent::tearDown();
    }

    private function mock(string $path): array { return json_decode((string) file_get_contents($this->base . $path), true); }

    /** POST a check-in to the mock with these headers: its status and decoded body. */
    private function raw(array $headers, string $body = '{}'): array {
        $lines = array('Content-Type: application/json');
        foreach ($headers as $name => $value) { $lines[] = "$name: $value"; }
        $answer = file_get_contents($this->base . '/api/website/remote/check-in', false, stream_context_create(array('http' => array('method' => 'POST', 'header' => implode("\r\n", $lines), 'content' => $body, 'ignore_errors' => true))));
        return array((int) explode(' ', $http_response_header[0])[1], json_decode((string) $answer, true));
    }

    /** Headers of a check-in signed with the license at this time and nonce. */
    private function signed(int $timestamp, string $nonce, string $body = '{}'): array {
        return array('X-SitePulse-Domain' => 'example.test', 'X-SitePulse-Timestamp' => (string) $timestamp, 'X-SitePulse-Nonce' => $nonce, 'X-SitePulse-Signature' => Sitepulse_Remote_Client::request_signature('POST', '/api/website/remote/check-in', 'example.test', (string) $timestamp, $nonce, $body, SITEPULSE_TEST_LICENSE));
    }

    public function test_the_plugin_checks_in_runs_queued_commands_and_reports_to_the_mock(): void {
        $command = $this->mock('/mock/queue?type=refresh_inventory&args=' . rawurlencode('{}'));
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $command['id']);
        update_option(Sitepulse_Remote_Management::EVENTS_OPTION, array(array('id' => '6f1d0c3e-6c0a-4a43-9a52-0d8f3a6b2c11', 'type' => 'plugin.activated', 'occurred_at' => '2026-10-15T18:00:00Z', 'data' => array())));

        Sitepulse_Remote_Management::check_in();

        $state = $this->mock('/mock/state');
        $this->assertTrue(Sitepulse_Remote_Management::state()['active']);
        $this->assertSame(array(SITEPULSE_VERSION, false), array($state['check_ins'][0]['plugin_version'], $state['check_ins'][0]['blocked']));
        $this->assertSame(array(array('id' => $command['id'], 'status' => 'done', 'result' => array('inventory_sent' => true), 'error' => null)), $state['results']);
        $this->assertSame('updraftplus', $state['inventory']['backups']['provider']['id']);
        $this->assertSame(array('plugin.activated'), array_column($state['events'], 'type'));
        $this->assertSame(array(), $state['queue'], 'A command is delivered once.');
    }

    public function test_the_mock_delivers_one_update_at_a_time(): void {
        $plugins = $this->mock('/mock/queue?type=update_plugins&args=' . rawurlencode('{"plugins":["seo/seo.php"]}'));
        $themes = $this->mock('/mock/queue?type=update_themes&args=' . rawurlencode('{"themes":["astra"]}'));
        $refresh = $this->mock('/mock/queue?type=refresh_inventory&args=' . rawurlencode('{}'));
        $client = new Sitepulse_Remote_Client(SITEPULSE_TEST_LICENSE);
        $delivered = static function () use ($client): array { return array_column($client->post('check-in', array('schema' => 1))['commands'], 'id'); };

        $this->assertSame(array($plugins['id'], $refresh['id']), $delivered());
        $this->assertSame(array(), $delivered(), 'The next update waits for a result.');
        $client->post('command-result', array('id' => $plugins['id'], 'status' => 'running', 'result' => null, 'error' => null));
        $client->post('command-result', array('id' => $refresh['id'], 'status' => 'done', 'result' => null, 'error' => null));
        $this->assertSame(array(), $delivered());
        $client->post('command-result', array('id' => $plugins['id'], 'status' => 'failed', 'result' => null, 'error' => 'update_failed'));
        $this->assertSame(array($themes['id']), $delivered());
        $this->assertSame($themes['id'], $this->mock('/mock/state')['updating']);
    }

    public function test_the_mock_refuses_what_the_api_refuses(): void {
        $refused = (new Sitepulse_Remote_Client('sk_some_other_license'))->post('check-in', array('schema' => 1));
        $this->assertSame(array('http_401', array('error' => 'invalid_signature', 'message' => 'Invalid signature.')), array($refused->get_error_code(), $refused->get_error_data()));
        $this->assertSame('http_409', (new Sitepulse_Remote_Client(SITEPULSE_TEST_LICENSE))->post('key', array('schema' => 1))->get_error_code(), 'No rotation is pending.');
        $this->assertSame('http_404', (new Sitepulse_Remote_Client(SITEPULSE_TEST_LICENSE))->post('unknown', array())->get_error_code());
        $this->assertFalse(@file_get_contents($this->base . '/api/website/remote/check-in'), 'Only signed POST requests reach the endpoints.');

        $client = new Sitepulse_Remote_Client(SITEPULSE_TEST_LICENSE);
        $event = array('id' => '6f1d0c3e-6c0a-4a43-9a52-0d8f3a6b2c11', 'type' => 'plugin.activated', 'occurred_at' => '2026-10-15T18:00:00Z', 'data' => array());
        $this->assertSame(1, $client->post('events', array('events' => array($event, 'not an event')))['accepted']);
        $this->assertSame(0, $client->post('events', array('events' => array($event)))['accepted'], 'A retried event is not stored twice.');
    }

    public function test_the_mock_says_why_it_refuses_a_request_as_the_api_does(): void {
        $refused = static function (string $error, string $message): array { return array(401, array('success' => false, 'error' => $error, 'message' => $message)); };
        $nonce = str_repeat('a', 32);

        $this->assertSame($refused('malformed_headers', 'Missing or malformed signature headers.'), $this->raw(array()));
        $this->assertSame($refused('malformed_headers', 'Missing or malformed signature headers.'), $this->raw(array('X-SitePulse-Signature' => 'not hex') + $this->signed(time(), $nonce)));
        $this->assertSame($refused('request_expired', 'Request expired.'), $this->raw($this->signed(time() - 600, $nonce)));
        $this->assertSame($refused('request_expired', 'Request expired.'), $this->raw(array('X-SitePulse-Timestamp' => '') + $this->signed(time(), $nonce)));
        $this->assertSame($refused('invalid_signature', 'Invalid signature.'), $this->raw($this->signed(time(), $nonce), '{"schema":1}'));
        $this->assertSame(200, $this->raw($this->signed(time(), $nonce))[0]);
        $this->assertSame($refused('request_replayed', 'This request was already received.'), $this->raw($this->signed(time(), $nonce)));

        // A site whose license the mock does not know is told to enter the right one.
        update_option('sitepulse_license_key', 'sk_some_other_license');
        Sitepulse_Remote_Management::check_in();
        $state = Sitepulse_Remote_Management::state();
        $this->assertSame(array('http_401', 'invalid_signature', sitepulse_test_remote_refusals()['invalid_signature'][1]), array($state['last_error'], $state['last_refusal'], $state['last_error_message']));
    }

    public function test_a_key_rotated_in_the_mock_is_confirmed_and_kept(): void {
        $key = 'rk_' . str_repeat('a1B2', 10);
        $this->mock('/mock/rotate?key=' . $key);

        Sitepulse_Remote_Management::check_in();
        Sitepulse_Remote_Management::check_in();

        $this->assertSame($key, get_option(Sitepulse_Remote_Management::KEY_OPTION));
        $state = $this->mock('/mock/state');
        $this->assertSame(array($key, null), array($state['key'], $state['pending_key']));
        $this->assertCount(2, $state['check_ins'], 'The second check-in is signed with the new key.');
        $this->assertSame(array('key' => SITEPULSE_TEST_LICENSE, 'queue' => array(), 'updating' => null), array_intersect_key($this->mock('/mock/reset'), array('key' => 1, 'queue' => 1, 'updating' => 1)));
    }
}

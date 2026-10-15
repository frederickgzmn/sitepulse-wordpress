<?php
$input = json_decode($argv[1], true);
$root = dirname(__DIR__, 3);
// WordPress configuration constants cannot change inside one process.
foreach ($input['constants'] ?? array() as $name => $value) { define($name, $value); }
require $root . '/vendor/autoload.php';
require $root . '/tests/php/bootstrap.php';
require $root . '/tests/php/Support/RemoteManagement.php';
require $root . '/tests/php/Support/Database.php';
$GLOBALS['wpdb'] = new Sitepulse_Test_Database();
update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
if ($input['mode'] === 'api_base') {
    Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response();
    $result = (new Sitepulse_Remote_Client(SITEPULSE_TEST_LICENSE))->post('check-in', array('schema' => 1));
    $headers = Sitepulse_Test_WP::$http_requests[0]['args']['headers'];
    $expected = Sitepulse_Remote_Client::request_signature('POST', '/api/website/remote/check-in', 'example.test', $headers['X-SitePulse-Timestamp'], $headers['X-SitePulse-Nonce'], '{"schema":1}', SITEPULSE_TEST_LICENSE);
    echo json_encode(array('url' => Sitepulse_Test_WP::$http_requests[0]['url'], 'signed_path' => $expected === $headers['X-SitePulse-Signature'], 'result' => $result));
} elseif ($input['mode'] === 'inventory') {
    $updates = Sitepulse_Remote_Inventory::collect()['updates'];
    echo json_encode(array('reason' => $updates['reason'], 'auto_update' => $updates['core']['auto_update']));
} elseif ($input['mode'] === 'pro') {
    ob_start();
    Sitepulse_Remote_Management::render_settings();
    echo json_encode(array('can_connect' => Sitepulse_Remote_Management::can_connect(), 'html' => ob_get_clean()));
} elseif ($input['mode'] === 'block_exit') {
    Sitepulse_Test_WP::$redirect_throws = false;
    $_REQUEST['_wpnonce'] = 'nonce-sitepulse_remote_management';
    $_POST['sitepulse_remote_blocked'] = '1';
    register_shutdown_function(static function () { echo json_encode(array('redirects' => Sitepulse_Test_WP::$redirects, 'blocked' => Sitepulse_Remote_Management::is_blocked())); });
    Sitepulse_Remote_Management::handle_block_form();
    echo 'unexpected continuation';
}

<?php
// A server without PHP-FPM's fastcgi_finish_request(): LiteSpeed's litespeed_finish_request(), or neither (Apache's mod_php, the CLI).
$input = json_decode($argv[1], true);
$root = dirname(__DIR__, 3);
$GLOBALS['sitepulse_test_omitted_wp_functions'] = array('fastcgi_finish_request');
require $root . '/vendor/autoload.php';
require $root . '/tests/php/bootstrap.php';
if ($input['mode'] === 'litespeed') {
    function litespeed_finish_request() { $GLOBALS['litespeed_finished'] = ($GLOBALS['litespeed_finished'] ?? 0) + 1; return true; }
}
Sitepulse_Utils::finish_response();
echo json_encode(array('fastcgi' => function_exists('fastcgi_finish_request'), 'litespeed' => $GLOBALS['litespeed_finished'] ?? 0, 'keeps_running' => (bool) ignore_user_abort()));

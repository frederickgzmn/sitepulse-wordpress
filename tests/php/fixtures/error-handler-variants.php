<?php
$input = json_decode($argv[1], true);
$root = dirname(__DIR__, 3);
if ($input['mode'] === 'guard') { require $root . '/class/error_handler.php'; echo 'unexpected access'; exit(1); }
require $root . '/vendor/autoload.php';
require $root . '/tests/php/bootstrap.php';
Sitepulse_Error_Handler::set_recovery_email('recovery@example.test');
if ($input['mode'] === 'previous_handlers') {
    $GLOBALS['previous_errors'] = array(); $GLOBALS['previous_exceptions'] = array();
    set_error_handler(static function ($code, $message, $file, $line) { $GLOBALS['previous_errors'][] = compact('code', 'message', 'file', 'line'); return true; });
    set_exception_handler(static function ($exception) { $GLOBALS['previous_exceptions'][] = $exception->getMessage(); });
    Sitepulse_Error_Handler::init(); Sitepulse_Error_Handler::init();
    $handled = Sitepulse_Error_Handler::handle_error(E_WARNING, 'Chained warning', __FILE__, 15);
    Sitepulse_Error_Handler::handle_exception(new RuntimeException('Chained exception'));
    echo json_encode(array('errors' => $GLOBALS['previous_errors'], 'handled' => $handled, 'exceptions' => $GLOBALS['previous_exceptions'], 'log' => Sitepulse_Error_Handler::get_error_log(), 'mail' => Sitepulse_Test_WP::$mail, 'wp_enabled' => apply_filters('wp_fatal_error_handler_enabled', true), 'mail_filtered' => has_filter('wp_mail')));
    restore_error_handler(); restore_exception_handler();
} elseif ($input['mode'] === 'fatal_shutdown') {
    ini_set('display_errors', '0'); ini_set('log_errors', '0');
    Sitepulse_Error_Handler::init();
    register_shutdown_function(static function () { echo json_encode(array('log' => get_option('sitepulse_error_log'), 'mail' => Sitepulse_Test_WP::$mail)); });
    $file = ABSPATH . 'fatal-fixture.php';
    file_put_contents($file, '<?php function sitepulse_duplicate_function() {} function sitepulse_duplicate_function() {}');
    require $file;
} elseif ($input['mode'] === 'scraping_exception') {
    ini_set('display_errors', '0'); ini_set('log_errors', '0');
    define('WP_SANDBOX_SCRAPING', true);
    Sitepulse_Error_Handler::init();
    register_shutdown_function(static function () { echo json_encode(array('last' => error_get_last(), 'log' => get_option('sitepulse_error_log'), 'mail' => Sitepulse_Test_WP::$mail)); });
    throw new Error('Call to undefined function sitepulse_removed_function()');
} elseif ($input['mode'] === 'uncaught_exception') {
    // An ordinary page whose code calls a function that no longer exists: PHP 7+ throws it as an Error.
    ini_set('display_errors', '0'); ini_set('log_errors', '0');
    Sitepulse_Error_Handler::init();
    register_shutdown_function(static function () { echo json_encode(array('last' => error_get_last(), 'log' => get_option('sitepulse_error_log'), 'mail' => Sitepulse_Test_WP::$mail)); });
    trigger_error('Warning before the fatal error', E_USER_WARNING);
    throw new Error('Call to undefined function sitepulse_removed_function()');
} elseif ($input['mode'] === 'shutdown_without_error') {
    error_clear_last(); Sitepulse_Error_Handler::handle_shutdown(); echo json_encode(array('log' => get_option('sitepulse_error_log'), 'mail' => Sitepulse_Test_WP::$mail));
}

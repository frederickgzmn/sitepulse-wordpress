<?php

$input = json_decode($argv[1], true);
$root = dirname(__DIR__, 3);
if ($input['mode'] === 'guard') {
    require $root . '/' . $input['file'];
    echo 'unexpected execution after direct access guard';
    exit(1);
}

if ($input['mode'] === 'clear_load') {
    define('SITEPULSE_TEST_WP_ROOT', sys_get_temp_dir() . '/sitepulse-api-loader-' . getmypid());
    define('ABSPATH', SITEPULSE_TEST_WP_ROOT . '/');
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
    define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');
    define('WPINC', 'wp-includes');
    define('HOUR_IN_SECONDS', 3600);
    define('DAY_IN_SECONDS', 86400);
    define('MINUTE_IN_SECONDS', 60);
    define('WP_DEBUG', false);
    require $root . '/tests/php/Support/WordPress.php';
    function plugin_dir_path($file) { return dirname($file) . '/'; }
    function plugin_dir_url($file) { return 'https://example.test/wp-content/plugins/sitepulse/'; }
    function register_activation_hook($file, $callback) { add_action('activate_' . plugin_basename($file), $callback); }
    function register_deactivation_hook($file, $callback) { add_action('deactivate_' . plugin_basename($file), $callback); }
    Sitepulse_Test_WP::reset();
    update_option('active_plugins', array('other/plugin.php', 'sitepulse/loader.php'));
    update_option('sitepulse_profiler_stats', array('init' => array('total' => 0.5)));
    require $root . '/loader.php';
    restore_error_handler();
    restore_exception_handler();
    remove_filter('pre_option_active_plugins', 'sitepulse_reorder_active_plugins_for_conflict');
    $request = new WP_REST_Request('POST');
    $result = sitepulse_enable_clear_load_events($request);
    echo json_encode(array('response' => $result->get_data(), 'events' => get_option(SITEPULSE_PROFILER_KEY), 'plugins' => Sitepulse_Test_WP::$options['active_plugins']));
    exit;
}

$GLOBALS['sitepulse_test_omitted_wp_functions'] = $input['exclude_functions'] ?? array();
if ($input['mode'] === 'debug_fallback') { define('WP_DEBUG', true); }
if ($input['mode'] === 'host_resolution') {
    // DNS and machine identity are external boundaries. Disable only these two
    // native functions in this child so every lookup is deterministic and offline.
    $GLOBALS['sitepulse_dns_fixture'] = $input;
    $GLOBALS['sitepulse_dns_queries'] = array();
    $GLOBALS['sitepulse_hostname_calls'] = 0;
    function gethostname() {
        $GLOBALS['sitepulse_hostname_calls']++;
        return $GLOBALS['sitepulse_dns_fixture']['hostname'];
    }
    function gethostbyname($hostname) {
        $GLOBALS['sitepulse_dns_queries'][] = $hostname;
        return $GLOBALS['sitepulse_dns_fixture']['addresses'][$hostname] ?? $hostname;
    }
}
if ($input['mode'] === 'memory_error') {
    function wp_convert_hr_to_bytes($value) { throw new RuntimeException('Memory conversion unavailable'); }
}
require $root . '/vendor/autoload.php';
require $root . '/tests/php/bootstrap.php';

if ($input['mode'] === 'host_resolution') {
    $_SERVER['SERVER_ADDR'] = $input['server_addr'];
    $_SERVER['LOCAL_ADDR'] = $input['local_addr'];
    Sitepulse_Test_WP::$home_url = $input['home_url'];
    Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array());
    Sitepulse_Api_Service::send_website_data();
    $payload = json_decode(Sitepulse_Test_WP::$http_requests[0]['args']['body'], true);
    echo json_encode(array('ip_address' => $payload['ip_address'], 'queries' => $GLOBALS['sitepulse_dns_queries'], 'hostname_calls' => $GLOBALS['sitepulse_hostname_calls']));
} elseif ($input['mode'] === 'memory_error') {
    require $root . '/inc/api_backend.php';
    $request = new WP_REST_Request('POST');
    $request->set_param('_wpnonce', wp_create_nonce('wp_rest'));
    $result = sitepulse_check_memory($request);
    echo json_encode(array('code' => $result->get_error_code(), 'status' => $result->get_error_data()['status'], 'message' => $result->get_error_message(), 'buffer_level' => ob_get_level()));
} elseif ($input['mode'] === 'plugin_metadata_lazy_load') {
    file_put_contents(ABSPATH . 'wp-admin/includes/plugin.php', '<?php function get_plugin_data($file) { return array("Name" => "Lazy Plugin", "Version" => "2.0"); }');
    file_put_contents(WP_PLUGIN_DIR . '/lazy.php', '<?php');
    update_option('active_plugins', array('lazy.php'));
    Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(array());
    Sitepulse_Api_Service::send_website_data();
    $payload = json_decode(Sitepulse_Test_WP::$http_requests[0]['args']['body'], true);
    echo json_encode($payload['website_info']['plugins']);
} elseif ($input['mode'] === 'debug_fallback' || $input['mode'] === 'native_flush') {
    class_exists('Sitepulse_Cron_Manager');
    Sitepulse_Cron_Manager::unschedule_daily_api_request();
    wp_schedule_event(time() - 172900, 'daily', 'sitepulse_daily_api_request');
    $error_log = ABSPATH . 'fallback-errors.log';
    ini_set('error_log', $error_log);
    Sitepulse_Test_WP::$http_queue[] = $input['mode'] === 'debug_fallback'
        ? new WP_Error('http_request_failed', 'timeout')
        : Sitepulse_Test_WP::response(array('license_key' => 'background-license'));
    Sitepulse_Cron_Fallback::maybe_run_fallback();
    ob_start();
    if ($input['mode'] === 'native_flush') { echo 'browser response\n'; }
    Sitepulse_Cron_Fallback::execute_fallback_task();
    if ($input['mode'] === 'debug_fallback') { ob_end_clean(); }
    echo json_encode(array(
        'lock' => get_transient('sitepulse_cron_fallback_daily_lock'),
        'license' => get_option('sitepulse_license_key'),
        'buffer_level' => ob_get_level(),
        'next_scheduled' => Sitepulse_Cron_Manager::get_next_scheduled_time(),
        'errors' => file_exists($error_log) ? file_get_contents($error_log) : '',
    ));
}

<?php
$input = json_decode($argv[1], true);
$root = dirname(__DIR__, 3);
if ($input['mode'] === 'guard') {
    require $root . '/class/' . $input['file'] . '.php';
    echo 'unexpected execution after access guard';
    exit(1);
}
$GLOBALS['sitepulse_test_autoload_exclusions'] = $input['exclude_classes'] ?? array();
$GLOBALS['sitepulse_test_omitted_wp_functions'] = $input['exclude_functions'] ?? array();
if ($input['mode'] === 'debug_trace') {
    define('WP_DEBUG', true);
    if (($input['trace_api'] ?? false) === true) {
        function _wp_call_all_hook() {}
        function wp_debug_backtrace_summary() { return $GLOBALS['trace_summary']; }
    }
}
require $root . '/vendor/autoload.php';
require $root . '/tests/php/bootstrap.php';
if ($input['mode'] === 'debug_trace') {
    $GLOBALS['trace_summary'] = str_replace('{ABSPATH}', ABSPATH, $input['summary'] ?? '');
    Sitepulse_CurLoader::init();
    $args = Sitepulse_CurLoader::tag_start_time(array(), 'https://api.test');
    foreach ($args['_wshp_trace'] as &$frame) { $frame['file'] = str_replace(ABSPATH, '', $frame['file']); }
    unset($frame);
    echo json_encode($args['_wshp_trace']);
} elseif ($input['mode'] === 'missing_class') {
    $_POST['nonce'] = 'nonce-wp_rest';
    try { call_user_func(array('Sitepulse_Easy_Mode', $input['handler'])); }
    catch (Sitepulse_Test_Json_Response $response) { echo json_encode(array('success' => $response->success, 'data' => $response->data)); }
} elseif ($input['mode'] === 'missing_profiler') {
    Sitepulse_Plugin_Profiler::init();
    Sitepulse_Plugin_Profiler::enable_data_collection();
    Sitepulse_Plugin_Profiler::track_plugins();
    echo json_encode(Sitepulse_Plugin_Profiler::get_stats());
} elseif ($input['mode'] === 'plugin_inventory_lazy_load') {
    file_put_contents(ABSPATH . 'wp-admin/includes/plugin.php', '<?php function get_plugins() { return Sitepulse_Test_WP::$plugins; }');
    Sitepulse_Test_WP::$plugins = array('demo/main.php' => array('Name' => 'Demo'));
    update_option('active_plugins', array('demo/main.php'));
    Sitepulse_Plugin_Profiler::enable_data_collection();
    Sitepulse_Plugin_Profiler::track_plugins();
    echo json_encode(Sitepulse_Plugin_Profiler::get_plugin_stats('demo/main.php'));
} elseif ($input['mode'] === 'redirect_exit') {
    Sitepulse_Test_WP::$redirect_throws = false;
    set_transient('sitepulse_activation_redirect', true, 30);
    register_shutdown_function(static function () { echo json_encode(array('redirects' => Sitepulse_Test_WP::$redirects, 'pending' => get_transient('sitepulse_activation_redirect'))); });
    (new Sitepulse_Onboarding())->maybe_redirect_to_onboarding();
    echo 'unexpected continuation';
} elseif ($input['mode'] === 'theme_functions_missing') {
    Sitepulse_Profiler::start_theme_bootstrap();
    Sitepulse_Profiler::capture_theme_bootstrap();
    echo json_encode(array_values(Sitepulse_Profiler::get_stats_by_hook('theme_bootstrap')));
}

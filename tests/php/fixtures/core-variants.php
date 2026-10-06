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
} elseif ($input['mode'] === 'tracker_without_profiler') {
    set_transient('sitepulse_pa_token_abcdefghijklmnopqrstuvwxyz012345', 'analysis1');
    $_GET['sitepulse_analyze'] = 'abcdefghijklmnopqrstuvwxyz012345';
    Sitepulse_Page_Tracker::init();
    Sitepulse_Page_Tracker::store_sample();
    echo json_encode(get_transient('sitepulse_pa_sample_abcdefghijklmnopqrstuvwxyz012345'));
} elseif ($input['mode'] === 'analysis_plugin_names') {
    file_put_contents(ABSPATH . 'wp-admin/includes/plugin.php', '<?php function get_plugins() { return array("lazy/lazy.php" => array("Name" => "Lazy Plugin")); }');
    $sample = array('server_ms' => 100.0, 'memory_peak' => 1, 'queries' => 1, 'status' => 200, 'page' => array('title' => 'T', 'kind' => 'Page'), 'callbacks' => array(), 'http' => array(),
        'sources' => array(array('source' => 'lazy', 'type' => 'plugin', 'self_ms' => 10.0, 'calls' => 1)));
    $report = Sitepulse_Page_Analysis::build_report(array('id' => 'a', 'url' => 'https://example.test/', 'created_at' => 1, 'pending_tokens' => array(), 'samples' => array($sample)));
    echo json_encode($report['sources'][0]['name']);
} elseif ($input['mode'] === 'monitoring_toggle_exit') {
    Sitepulse_Test_WP::$redirect_throws = false;
    $_REQUEST['_wpnonce'] = 'nonce-sitepulse_toggle_monitoring';
    register_shutdown_function(static function () { echo json_encode(array('redirects' => Sitepulse_Test_WP::$redirects, 'paused' => Sitepulse_Monitoring::is_paused())); });
    Sitepulse_Monitoring::handle_toggle();
    echo 'unexpected continuation';
}

<?php
$input = json_decode($argv[1], true);
$root = dirname(__DIR__, 3);
$GLOBALS['sitepulse_test_autoload_exclusions'] = $input['exclude_classes'] ?? array();
$GLOBALS['sitepulse_test_omitted_wp_functions'] = $input['exclude_functions'] ?? array();
require $root . '/vendor/autoload.php';
require $root . '/tests/php/bootstrap.php';
require $root . '/tests/php/Support/Database.php';
require $root . '/tests/php/Support/LocalFilesystem.php';
require $root . '/tests/php/Support/TemplateData.php';
$GLOBALS['wpdb'] = new Sitepulse_Test_Database();
$GLOBALS['wp_filesystem'] = new Sitepulse_Test_Local_Filesystem();
if ($input['mode'] === 'backend') {
    Sitepulse_Test_WP::$screen = (object) array('id' => 'toplevel_page_wpsp_sitepulse');
    update_option('active_plugins', array('demo/main.php', 'second/main.php'));
    Sitepulse_Easy_Mode::init();
    update_user_meta(1, Sitepulse_Easy_Mode::META_KEY, '1');
    $backend = new Sitepulse_Backend(new Sitepulse_Plugin());
    ob_start(); $backend->{$input['method']}(); $html = ob_get_clean();
} else {
    $data = sitepulse_test_seed_template_options();
    if ($input['view'] === 'security') { update_option('sitepulse_error_log', array('errors' => 'legacy-corruption', 'total_count' => 0, 'last_error_date' => null)); }
    if ($input['view'] === 'system') {
        file_put_contents(ABSPATH . 'wp-admin/includes/plugin.php', '<?php function get_plugin_data($file, $markup = true, $translate = true) { return Sitepulse_Test_WP::$plugins[plugin_basename($file)]; }');
    }
    extract($data, EXTR_SKIP);
    ob_start(); require $root . '/templates/easy/views/' . $input['view'] . '.php'; $html = ob_get_clean();
}
echo json_encode(array('html' => $html));

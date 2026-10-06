<?php
$input = json_decode($argv[1], true);
$root = dirname(__DIR__, 3);
if ($input['mode'] === 'direct_access') { require $root . '/loader.php'; echo 'unexpected direct access'; exit(1); }
// A fresh PHP process lets the real loader own its constants and global handlers.
define('SITEPULSE_TEST_WP_ROOT', sys_get_temp_dir() . '/sitepulse-lifecycle-' . getmypid());
define('ABSPATH', SITEPULSE_TEST_WP_ROOT . '/');
define('WP_CONTENT_DIR', SITEPULSE_TEST_WP_ROOT . '/wp-content');
define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');
define('WPINC', 'wp-includes');
define('HOUR_IN_SECONDS', 3600);
define('DAY_IN_SECONDS', 86400);
define('MINUTE_IN_SECONDS', 60);
define('WP_DEBUG', false);
require dirname(__DIR__) . '/Support/WordPress.php';
function plugin_dir_path($file) { return dirname($file) . '/'; }
function plugin_dir_url($file) { return 'https://example.test/wp-content/plugins/sitepulse/'; }
function register_activation_hook($file, $callback) { add_action('activate_' . plugin_basename($file), $callback); }
function register_deactivation_hook($file, $callback) { add_action('deactivate_' . plugin_basename($file), $callback); }

Sitepulse_Test_WP::reset();
$input = json_decode($argv[1], true);
$root = dirname(__DIR__, 3);
if ($input['mode'] === 'uninstall_guard') { require $root . '/uninstall.php'; echo 'unexpected uninstall'; exit(1); }
if ($input['mode'] === 'uninstall') {
    define('WP_UNINSTALL_PLUGIN', 'sitepulse/loader.php');
    Sitepulse_Test_WP::$options = $input['options'];
    Sitepulse_Test_WP::$user_meta = array(1 => array('sitepulse_trackers_disabled_notice_dismissed' => true, 'sitepulse_onboarding_notice_dismissed' => true, 'unrelated' => 'keep'));
    set_transient('sitepulse_activation_redirect', true, 30);
    require $root . '/uninstall.php';
    echo json_encode(array('options' => Sitepulse_Test_WP::$options, 'user_meta' => Sitepulse_Test_WP::$user_meta, 'redirect' => get_transient('sitepulse_activation_redirect')));
    exit;
}
Sitepulse_Test_WP::$options['active_plugins'] = $input['plugins'] ?? array('sitepulse/loader.php');
if ($input['mode'] === 'pro') { define('SITEPULSE_PRO_IS_ACTIVE', true); }
if ($input['mode'] === 'autosave') { define('DOING_AUTOSAVE', true); }
require $root . '/loader.php';
if ($input['mode'] === 'pro') {
    ob_start(); do_action('admin_notices'); $notice = ob_get_clean();
    echo json_encode(array('loader_initialized' => isset($sitepulse_loader), 'notice' => $notice));
    exit;
}
// The real plugin installs process-wide handlers; restore them before test operations.
restore_error_handler();
restore_exception_handler();
if ($input['mode'] === 'order') {
    remove_filter('pre_option_active_plugins', 'sitepulse_reorder_active_plugins_for_conflict');
    $conflict = sitepulse_detect_first_position_conflict();
    $reordered = sitepulse_reorder_active_plugins_for_conflict($input['plugins']);
    $before = Sitepulse_Test_WP::$options['active_plugins'];
    sitepulse_move_to_first_position();
    echo json_encode(array('conflict' => $conflict, 'reordered' => $reordered, 'before' => $before, 'after' => get_option('active_plugins'), 'is_first' => sitepulse_is_first_plugin()));
} elseif ($input['mode'] === 'activation') {
    $sitepulse_loader->activate();
    $activated = array('profiler' => get_option(SITEPULSE_PROFILER_ENABLED), 'curl' => get_option(SITEPULSE_CURL_API_ENABLED), 'plugins' => get_option('sitepulse_plugins_profiler_enabled'), 'redirect' => get_transient('sitepulse_activation_redirect'), 'scheduled' => array_column(Sitepulse_Test_WP::$scheduled, 'hook'));
    wp_schedule_event(time() + 30, 'hourly', 'sitepulse_fallback_cron_check');
    wp_schedule_event(time() + 30, 'hourly', 'sitepulse_cron_fallback_immediate');
    wp_schedule_event(time() + 30, 'hourly', 'sitepulse_check_ai_diagnostic_status');
    $sitepulse_loader->deactivate();
    echo json_encode(array('activated' => $activated, 'remaining' => array_column(Sitepulse_Test_WP::$scheduled, 'hook')));
} elseif ($input['mode'] === 'notice') {
    update_option(SITEPULSE_PROFILER_ENABLED, $input['enabled']);
    update_option(SITEPULSE_CURL_API_ENABLED, $input['curl_enabled'] ?? $input['enabled']);
    if ($input['dismissed']) { update_user_meta(1, 'sitepulse_trackers_disabled_notice_dismissed', true); }
    ob_start(); $sitepulse_loader->admin_notice_trackers_disabled(); $notice = ob_get_clean();
    echo json_encode(array('notice' => $notice));
}
 elseif ($input['mode'] === 'onboarding_notice') {
    Sitepulse_Test_WP::$screen = isset($input['screen']) ? (object) array('id' => $input['screen']) : null;
    update_option('sitepulse_onboarding_completed', $input['completed'] ?? false);
    update_option('sitepulse_onboarding_dismissed', $input['dismissed'] ?? false);
    update_user_meta(1, 'sitepulse_onboarding_notice_dismissed', $input['notice_dismissed'] ?? false);
    ob_start(); $sitepulse_loader->admin_notice_onboarding(); $notice = ob_get_clean();
    echo json_encode(array('notice' => $notice));
} elseif ($input['mode'] === 'autosave') {
    echo json_encode(array('before_init' => did_action('sitepulse_before_init'), 'activation' => has_action('activate_sitepulse/loader.php')));
} elseif ($input['mode'] === 'invalid_option') {
    remove_filter('pre_option_active_plugins', 'sitepulse_reorder_active_plugins_for_conflict');
    Sitepulse_Test_WP::$options['active_plugins'] = 'corrupt';
    echo json_encode(array('result' => sitepulse_reorder_active_plugins_for_conflict(false), 'stored' => Sitepulse_Test_WP::$options['active_plugins']));
} elseif ($input['mode'] === 'translation') {
    $sitepulse_loader->load_plugin_textdomain();
    echo json_encode(array('domains' => Sitepulse_Test_WP::$textdomains, 'expected' => rtrim(SITEPULSE_TEXT_DOMAIN_PATH, '/') . '/sitepulse-en_US.mo'));
}

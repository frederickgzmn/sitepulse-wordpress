<?php
/** Isolated PHP harness: no WordPress installation, database, or network is loaded. */
define('SITEPULSE_TEST_ROOT', dirname(__DIR__, 2));
define('SITEPULSE_TEST_WP_ROOT', sys_get_temp_dir() . '/sitepulse-php-tests-' . getmypid() . '-' . bin2hex(random_bytes(6)));
foreach (array('/wp-admin/includes', '/wp-content/plugins', '/wp-content/themes', '/wp-content/uploads', '/wp-includes') as $directory) {
    if (!is_dir(SITEPULSE_TEST_WP_ROOT . $directory)) {
        mkdir(SITEPULSE_TEST_WP_ROOT . $directory, 0777, true);
    }
}
foreach (array('file', 'plugin', 'update') as $fixture) {
    file_put_contents(SITEPULSE_TEST_WP_ROOT . '/wp-admin/includes/' . $fixture . '.php', "<?php // Intentionally empty WordPress boundary fixture.\n");
}
$constants = array(
    'ABSPATH' => SITEPULSE_TEST_WP_ROOT . '/', 'WP_CONTENT_DIR' => SITEPULSE_TEST_WP_ROOT . '/wp-content',
    'WP_PLUGIN_DIR' => SITEPULSE_TEST_WP_ROOT . '/wp-content/plugins', 'WPMU_PLUGIN_DIR' => SITEPULSE_TEST_WP_ROOT . '/wp-content/mu-plugins',
    'WPINC' => 'wp-includes', 'WP_DEBUG' => false, 'WP_DEBUG_LOG' => false, 'WP_DEBUG_DISPLAY' => false,
    'WP_MEMORY_LIMIT' => '256M', 'WP_MAX_MEMORY_LIMIT' => '512M',
    'SITEPULSE_PATH' => SITEPULSE_TEST_ROOT . '/', 'SITEPULSE_CLASS_PATH' => SITEPULSE_TEST_ROOT . '/class/',
    'SITEPULSE_PLUGIN_FILE' => SITEPULSE_TEST_ROOT . '/loader.php', 'SITEPULSE_VERSION' => '1.4.5',
    'SITEPULSE_PREFIX' => 'wpsp', 'SITEPULSE_PREFIX_SEPARATOR' => '_', 'SITEPULSE_NAME' => 'SitePulse',
    'SITEPULSE_SLUG' => 'sitepulse', 'SITEPULSE_DEBUG' => false, 'SITEPULSE_STRESS_MODE' => false,
    'SITEPULSE_URL' => 'https://example.test/wp-content/plugins/sitepulse/',
    'SITEPULSE_PROFILER_KEY' => 'sitepulse_profiler_stats', 'SITEPULSE_PROFILER_ENABLED' => 'sitepulse_profiler_enabled',
    'SITEPULSE_PROFILER_CURRENT_TIME' => 'sitepulse_profiler_current_time', 'SITEPULSE_PROFILER_SLUG' => 'resource_load',
    'SITEPULSE_CURL_API_KEY' => 'sitepulse_curl_api_events', 'SITEPULSE_CURL_API_ENABLED' => 'sitepulse_curl_api_enabled',
    'SITEPULSE_CURL_API_SLUG' => 'curl_api', 'SITEPULSE_CURL_API_DEFAULT_THRESHOLD' => 1,
    'SITEPULSE_SETTINGS_PROFILER_INACTIVE_AFTER_SECONDS' => 60, 'SITEPULSE_REALTIME_TRACKING_TIME' => 30,
    'SECOND_IN_SECONDS' => 1, 'MINUTE_IN_SECONDS' => 60, 'HOUR_IN_SECONDS' => 3600, 'DAY_IN_SECONDS' => 86400,
    'WEEK_IN_SECONDS' => 604800, 'MONTH_IN_SECONDS' => 2592000, 'YEAR_IN_SECONDS' => 31536000,
    'KB_IN_BYTES' => 1024, 'MB_IN_BYTES' => 1048576, 'GB_IN_BYTES' => 1073741824,
    'FS_CHMOD_DIR' => 0755, 'SITEPULSE_PRO_IS_ACTIVE' => false, 'ARRAY_A' => 'ARRAY_A', 'OBJECT' => 'OBJECT', 'FS_CHMOD_FILE' => 0644,
);
foreach ($constants as $name => $value) { if (!defined($name)) { define($name, $value); } }
define('SITEPULSE_ADMIN_ASSETS_PATH', SITEPULSE_PATH . 'assets/');
define('SITEPULSE_ADMIN_ASSETS_URL', SITEPULSE_URL . 'assets/');
foreach (array('JS' => 'js', 'CSS' => 'css', 'IMG' => 'img') as $suffix => $directory) {
    define('SITEPULSE_ADMIN_ASSETS_' . $suffix . '_PATH', SITEPULSE_ADMIN_ASSETS_PATH . $directory . '/');
    define('SITEPULSE_ADMIN_ASSETS_' . $suffix . '_URL', SITEPULSE_ADMIN_ASSETS_URL . $directory . '/');
}
require_once __DIR__ . '/Support/WordPress.php';
spl_autoload_register(static function ($class) {
    if (in_array($class, $GLOBALS['sitepulse_test_autoload_exclusions'] ?? array(), true)) { return; }
    if (strpos($class, 'Sitepulse_') !== 0 || strpos($class, 'Sitepulse_Test_') === 0) { return; }
    $file = strtolower(substr($class, strlen('Sitepulse_'))) . '.php';
    foreach (array(SITEPULSE_CLASS_PATH, SITEPULSE_PATH . 'inc/') as $directory) {
        if (is_file($directory . $file)) { require_once $directory . $file; return; }
    }
});
require_once __DIR__ . '/Support/TestCase.php';
Sitepulse_Test_WP::reset();
register_shutdown_function(static function () {
    // Only remove the process-specific disposable fixture tree.
    if (!is_dir(SITEPULSE_TEST_WP_ROOT)) { return; }
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(SITEPULSE_TEST_WP_ROOT, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) { $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname()); }
    rmdir(SITEPULSE_TEST_WP_ROOT);
});

<?php
/** Minimal boundary doubles, deliberately restricted to WordPress calls exercised by tests. */
final class Sitepulse_Test_WP {
    public static $filesystem_factory = null;
    public static $mail = array();
    public static $mail_result = true;
    public static $textdomains = array();
    public static $home_url = 'https://example.test';
    public static $options = array();
    public static $site_options = array();
    public static $transients = array();
    public static $transient_expirations = array();
    public static $hooks = array();
    public static $actions = array();
    public static $http_queue = array();
    public static $http_requests = array();
    public static $capabilities = array('manage_options' => true);
    public static $user_id = 1;
    public static $user_meta = array();
    public static $screen = null;
    public static $post_id = 0;
    public static $posts = array();
    public static $plugins = array();
    public static $theme = array('Name' => 'Test Theme', 'Version' => '1.0', 'stylesheet' => 'test-theme', 'template' => 'test-theme');
    public static $cron = array();
    public static $scheduled = array();
    public static $routes = array();
    public static $styles = array();
    public static $scripts = array();
    public static $localized = array();
    public static $menus = array();
    public static $redirects = array();
    public static $redirect_throws = true;
    public static $option_writes = array();
    public static $cache_deletes = array();
    public static $is_singular = false;
    public static $is_home = false;
    public static $is_admin = true;
    public static $multisite = false;
    public static $doing_ajax = false;
    public static $doing_cron = false;
    public static $nonce_valid = true;
    public static $spawn_cron_calls = 0;
    public static $finished_requests = 0;
    public static $now = '2026-01-01 12:00:00';
    public static $passwords = array();
    public static $num_queries = 0;
    public static $is_404 = false;
    public static $is_front_page = false;
    public static $is_search = false;
    public static $is_archive = false;
    public static $post_type = '';
    public static $post_types = array();
    public static $posts_by_type = array();
    public static $post_queries = array();
    public static $document_title = '';
    public static $referer = false;
    public static $admin_bar_showing = true;
    public static function rememberClassDefaults($class) {
        // PHP 7.4 reflection returns the current static values, not their
        // declared defaults. Capture each class before tests can mutate it.
        static $defaults = array();
        if (!array_key_exists($class, $defaults)) {
            $defaults[$class] = (new ReflectionClass($class))->getDefaultProperties();
        }
        return $defaults[$class];
    }
    public static function reset() {
        foreach (self::rememberClassDefaults(__CLASS__) as $name => $value) { self::${$name} = $value; }
        foreach (get_declared_classes() as $class) {
            if (strpos($class, 'Sitepulse_') !== 0 || strpos($class, 'Sitepulse_Test_') === 0) { continue; }
            $rc = new ReflectionClass($class);
            $defaults = self::rememberClassDefaults($class);
            foreach ($rc->getProperties(ReflectionProperty::IS_STATIC) as $property) {
                if ($property->getDeclaringClass()->getName() !== $class) { continue; }
                if (array_key_exists($property->getName(), $defaults)) {
                    if (PHP_VERSION_ID < 80100) { $property->setAccessible(true); }
                    $property->setValue(null, $defaults[$property->getName()]);
                }
            }
        }
        $GLOBALS['wp_filter'] = array();
        $GLOBALS['wp_filesystem'] = new Sitepulse_Test_Filesystem();
        $_GET = $_POST = $_REQUEST = array();
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_ADDR'] = '192.0.2.1';
        $GLOBALS['wp_version'] = '6.8';
    }
    public static function response($body = array(), $status = 200, $headers = array()) {
        return array('headers' => $headers, 'body' => is_string($body) ? $body : json_encode($body), 'response' => array('code' => $status, 'message' => $status === 200 ? 'OK' : 'Error'), 'cookies' => array(), 'filename' => null);
    }
    public static function http($url, $args) {
        self::$http_requests[] = array('url' => $url, 'args' => $args);
        if (!self::$http_queue) { throw new RuntimeException('Unexpected HTTP request: ' . $url); }
        $response = array_shift(self::$http_queue);
        return is_callable($response) ? $response($url, $args) : $response;
    }
}
Sitepulse_Test_WP::rememberClassDefaults(Sitepulse_Test_WP::class);
class Sitepulse_Test_Hook { public $callbacks = array(); }
class Sitepulse_Test_Json_Response extends RuntimeException {
    public $success; public $data; public $status;
    public function __construct($success, $data, $status) { parent::__construct('WordPress JSON response'); $this->success = $success; $this->data = $data; $this->status = $status; }
}
class Sitepulse_Test_Die extends RuntimeException {}
class Sitepulse_Test_Redirect extends RuntimeException {}
class Sitepulse_Test_Filesystem {
    public $method = 'direct';
    private function safe($path) { if (strpos($path, SITEPULSE_TEST_WP_ROOT . '/') !== 0 || in_array('..', explode('/', wp_normalize_path($path)), true)) { throw new RuntimeException('File write outside disposable WordPress fixture: ' . $path); } }
    public function exists($path) { return file_exists($path); }
    public function is_writable($path) { $this->safe($path); return is_writable($path); }
    public function get_contents($path) { $this->safe($path); return file_exists($path) ? file_get_contents($path) : false; }
    public function put_contents($path, $contents, $mode = false) { $this->safe($path); return file_put_contents($path, $contents) !== false; }
    public function delete($path, $recursive = false, $type = false) { $this->safe($path); return is_file($path) ? unlink($path) : false; }
    public function size($path) { $this->safe($path); return filesize($path); }
}
class WP_Error {
    public $errors = array(); public $error_data = array();
    public function __construct($code = '', $message = '', $data = '') { if ($code !== '') { $this->errors[$code] = array($message); if ($data !== '') { $this->error_data[$code] = $data; } } }
    public function get_error_code() { return key($this->errors) ?: ''; }
    public function get_error_message($code = '') { $code = $code ?: $this->get_error_code(); return $this->errors[$code][0] ?? ''; }
    public function get_error_data($code = '') { return $this->error_data[$code ?: $this->get_error_code()] ?? null; }
}
class WP_REST_Response {
    protected $data; protected $status; protected $headers;
    public function __construct($data = null, $status = 200, $headers = array()) { $this->data = $data; $this->status = $status; $this->headers = $headers; }
    public function get_data() { return $this->data; }
    public function set_data($data) { $this->data = $data; }
    public function get_status() { return $this->status; }
    public function set_status($status) { $this->status = $status; }
    public function get_headers() { return $this->headers; }
}
class WP_REST_Request implements ArrayAccess {
    private $method; private $route; private $params = array(); private $json = null; private $headers = array();
    public function __construct($method = 'GET', $route = '') { $this->method = $method; $this->route = $route; }
    public function get_method() { return $this->method; }
    public function set_method($method) { $this->method = $method; }
    public function get_route() { return $this->route; }
    public function set_route($route) { $this->route = $route; }
    public function set_param($key, $value) { $this->params[$key] = $value; }
    public function get_param($key) { return $this->json[$key] ?? $this->params[$key] ?? null; }
    public function get_params() { return array_merge($this->params, $this->json ?: array()); }
    public function set_body_params($params) { $this->params = $params; }
    public function set_query_params($params) { $this->params = $params; }
    public function set_body($body) { $this->json = json_decode($body, true); }
    public function set_header($key, $value) { $this->headers[strtolower($key)] = $value; }
    public function get_header($key) { return $this->headers[strtolower($key)] ?? ''; }
    public function get_json_params() { return $this->json; }
    public function offsetExists($offset): bool { return $this->get_param($offset) !== null; }
    #[\ReturnTypeWillChange]
    public function offsetGet($offset) { return $this->get_param($offset); }
    public function offsetSet($offset, $value): void { $this->set_param($offset, $value); }
    public function offsetUnset($offset): void { unset($this->params[$offset], $this->json[$offset]); }
}
function sitepulse_test_callback_id($callback) {
    if (is_string($callback)) { return $callback; }
    if (is_object($callback)) { return spl_object_hash($callback); }
    return (is_object($callback[0]) ? spl_object_hash($callback[0]) : $callback[0]) . '::' . $callback[1];
}
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
    global $wp_filter;
    if (!isset($wp_filter[$hook])) { $wp_filter[$hook] = new Sitepulse_Test_Hook(); }
    $wp_filter[$hook]->callbacks[$priority][sitepulse_test_callback_id($callback)] = array('function' => $callback, 'accepted_args' => $accepted_args);
    Sitepulse_Test_WP::$hooks = $wp_filter;
    return true;
}
function add_action($hook, $callback, $priority = 10, $accepted_args = 1) { return add_filter($hook, $callback, $priority, $accepted_args); }
function remove_filter($hook, $callback, $priority = 10) {
    global $wp_filter; $id = sitepulse_test_callback_id($callback); $found = isset($wp_filter[$hook]->callbacks[$priority][$id]);
    if ($found) { unset($wp_filter[$hook]->callbacks[$priority][$id]); }
    return $found;
}
function remove_action($hook, $callback, $priority = 10) { return remove_filter($hook, $callback, $priority); }
function has_filter($hook, $callback = false) {
    global $wp_filter;
    foreach (($wp_filter[$hook]->callbacks ?? array()) as $priority => $callbacks) {
        if ($callback === false && $callbacks) { return true; }
        if ($callback !== false && isset($callbacks[sitepulse_test_callback_id($callback)])) { return $priority; }
    }
    return false;
}
function has_action($hook, $callback = false) { return has_filter($hook, $callback); }
function apply_filters($hook, $value, ...$args) {
    global $wp_filter; $priorities = $wp_filter[$hook]->callbacks ?? array(); ksort($priorities);
    foreach ($priorities as $callbacks) { foreach ($callbacks as $entry) { $value = ($entry['function'])(...array_slice(array_merge(array($value), $args), 0, $entry['accepted_args'])); } }
    return $value;
}
function do_action($hook, ...$args) {
    global $wp_filter; Sitepulse_Test_WP::$actions[$hook] = (Sitepulse_Test_WP::$actions[$hook] ?? 0) + 1;
    $priorities = $wp_filter[$hook]->callbacks ?? array(); ksort($priorities);
    foreach ($priorities as $callbacks) { foreach ($callbacks as $entry) { ($entry['function'])(...array_slice($args, 0, $entry['accepted_args'])); } }
}
function did_action($hook) { return Sitepulse_Test_WP::$actions[$hook] ?? 0; }
function __return_false() { return false; }
function __return_true() { return true; }
function __return_empty_array() { return array(); }
function get_option($key, $default = false) { $pre = apply_filters('pre_option_' . $key, false, $key, $default); return $pre !== false ? $pre : (array_key_exists($key, Sitepulse_Test_WP::$options) ? Sitepulse_Test_WP::$options[$key] : $default); }
function update_option($key, $value, $autoload = null) { $old = get_option($key); Sitepulse_Test_WP::$options[$key] = $value; Sitepulse_Test_WP::$option_writes[] = array('key' => $key, 'value' => $value, 'autoload' => $autoload); return $old !== $value; }
function add_option($key, $value = '', $deprecated = '', $autoload = true) { if (array_key_exists($key, Sitepulse_Test_WP::$options)) { return false; } return update_option($key, $value, $autoload); }
function delete_option($key) { $found = array_key_exists($key, Sitepulse_Test_WP::$options); unset(Sitepulse_Test_WP::$options[$key]); return $found; }
function get_site_option($key, $default = false) { return Sitepulse_Test_WP::$site_options[$key] ?? $default; }
function wp_load_alloptions() { return Sitepulse_Test_WP::$options; }
function set_transient($key, $value, $expiration = 0) { Sitepulse_Test_WP::$transients[$key] = $value; Sitepulse_Test_WP::$transient_expirations[$key] = $expiration; return true; }
function get_transient($key) { return Sitepulse_Test_WP::$transients[$key] ?? false; }
function delete_transient($key) { $found = isset(Sitepulse_Test_WP::$transients[$key]); unset(Sitepulse_Test_WP::$transients[$key], Sitepulse_Test_WP::$transient_expirations[$key]); return $found; }
function get_user_meta($id, $key, $single = false) { return Sitepulse_Test_WP::$user_meta[$id][$key] ?? ($single ? '' : array()); }
function update_user_meta($id, $key, $value) { Sitepulse_Test_WP::$user_meta[$id][$key] = $value; return true; }
function delete_user_meta($id, $key) { unset(Sitepulse_Test_WP::$user_meta[$id][$key]); return true; }
function get_current_user_id() { return Sitepulse_Test_WP::$user_id; }
function is_user_logged_in() { return Sitepulse_Test_WP::$user_id > 0; }
function current_user_can($capability, ...$args) { return Sitepulse_Test_WP::$capabilities[$capability] ?? false; }
function get_current_screen() { return Sitepulse_Test_WP::$screen; }
function is_admin() { return Sitepulse_Test_WP::$is_admin; }
function is_multisite() { return Sitepulse_Test_WP::$multisite; }
function wp_doing_ajax() { return Sitepulse_Test_WP::$doing_ajax; }
function wp_doing_cron() { return Sitepulse_Test_WP::$doing_cron; }
function get_the_ID() { return Sitepulse_Test_WP::$post_id; }
function get_post_status($id) { return Sitepulse_Test_WP::$posts[$id]['post_status'] ?? false; }
if (!in_array('get_plugins', $GLOBALS['sitepulse_test_omitted_wp_functions'] ?? array(), true)) { function get_plugins() { return Sitepulse_Test_WP::$plugins; } }
if (!in_array('get_plugin_data', $GLOBALS['sitepulse_test_omitted_wp_functions'] ?? array(), true)) { function get_plugin_data($file, $markup = true, $translate = true) { return Sitepulse_Test_WP::$plugins[plugin_basename($file)] ?? array('Name' => '', 'Version' => '', 'TextDomain' => ''); } }
function plugin_basename($file) { $file = wp_normalize_path($file); if (defined('SITEPULSE_PLUGIN_FILE') && $file === SITEPULSE_PLUGIN_FILE) { return 'sitepulse/loader.php'; } return ltrim(str_replace(WP_PLUGIN_DIR . '/', '', $file), '/'); }
function get_theme_root() { return WP_CONTENT_DIR . '/themes'; }
if (!in_array('get_stylesheet', $GLOBALS['sitepulse_test_omitted_wp_functions'] ?? array(), true)) { function get_stylesheet() { return Sitepulse_Test_WP::$theme['stylesheet']; } }
if (!in_array('get_template', $GLOBALS['sitepulse_test_omitted_wp_functions'] ?? array(), true)) { function get_template() { return Sitepulse_Test_WP::$theme['template']; } }
class Sitepulse_Test_Theme { public function get_stylesheet() { return get_stylesheet(); }  public function get($key) { return Sitepulse_Test_WP::$theme[$key] ?? ''; } public function exists() { return true; } }
function wp_get_theme($slug = '') { return new Sitepulse_Test_Theme(); }
function wp_upload_dir() { return array('basedir' => WP_CONTENT_DIR . '/uploads', 'baseurl' => 'https://example.test/wp-content/uploads', 'path' => WP_CONTENT_DIR . '/uploads', 'url' => 'https://example.test/wp-content/uploads', 'subdir' => '', 'error' => false); }
function wp_parse_args($args, $defaults = array()) { if (is_object($args)) { $args = get_object_vars($args); } elseif (!is_array($args)) { parse_str((string) $args, $args); } return array_merge($defaults, $args); }
function wp_normalize_path($path) { return str_replace('\\', '/', (string) $path); }
function wp_parse_url($url, $component = -1) { return parse_url($url, $component); }
function wp_unslash($value) { return is_array($value) ? array_map('wp_unslash', $value) : stripslashes((string) $value); }
function sanitize_text_field($value) { if (is_array($value) || is_object($value)) { return ''; } return trim(preg_replace('/[\r\n\t ]+/', ' ', strip_tags((string) $value))); }
function sanitize_key($value) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value)); }
function sanitize_hex_color($value) { return $value === '' || preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i', $value) ? $value : null; }
function sanitize_email($value) { return filter_var($value, FILTER_SANITIZE_EMAIL); }
function absint($value) { return abs((int) $value); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return esc_html($value); }
function esc_url_raw($value) { $value = trim(strip_tags((string) $value)); return preg_match('/^(?:javascript|data|vbscript):/i', $value) ? '' : $value; }
function esc_url($value) { return esc_html(esc_url_raw($value)); }
function esc_js($value) { return addslashes((string) $value); }
function __($value, $domain = null) { return $value; }
function esc_html__($value, $domain = null) { return esc_html($value); }
function esc_attr__($value, $domain = null) { return esc_attr($value); }
function _e($value, $domain = null) { echo $value; }
function esc_html_e($value, $domain = null) { echo esc_html($value); }
function wp_kses_post($value) { return strip_tags($value, '<a><b><strong><em><p><br><span>'); }
function wp_json_encode($value, $flags = 0, $depth = 512) { return json_encode($value, $flags, $depth); }
function current_time($type, $gmt = 0) { return $type === 'mysql' ? Sitepulse_Test_WP::$now : ($type === 'timestamp' || $type === 'U' ? strtotime(Sitepulse_Test_WP::$now . ' UTC') : gmdate($type, strtotime(Sitepulse_Test_WP::$now . ' UTC'))); }
function get_bloginfo($show = '', $filter = 'raw') { return array('version' => '6.8', 'name' => 'Test Site', 'admin_email' => 'admin@example.test', 'language' => 'en-US', 'url' => 'https://example.test')[$show] ?? ''; }
function home_url($path = '', $scheme = null) { return Sitepulse_Test_WP::$home_url . ($path === '' ? '' : '/' . ltrim($path, '/')); }
function site_url($path = '', $scheme = null) { return home_url($path, $scheme); }
function admin_url($path = '', $scheme = 'admin') { return home_url('/wp-admin/' . ltrim($path, '/')); }
function rest_url($path = '', $scheme = 'rest') { return home_url('/wp-json/' . ltrim($path, '/')); }
function wp_create_nonce($action = -1) { return 'nonce-' . $action; }
function wp_verify_nonce($nonce, $action = -1) { return Sitepulse_Test_WP::$nonce_valid && $nonce === wp_create_nonce($action) ? 1 : false; }
function check_ajax_referer($action = -1, $query_arg = false, $stop = true) { $nonce = $_REQUEST[$query_arg ?: '_ajax_nonce'] ?? $_POST[$query_arg ?: '_ajax_nonce'] ?? ''; $valid = wp_verify_nonce($nonce, $action); if (!$valid && $stop) { throw new Sitepulse_Test_Die('-1'); } return $valid; }
function wp_send_json_success($data = null, $status = null) { throw new Sitepulse_Test_Json_Response(true, $data, $status); }
function wp_send_json_error($data = null, $status = null) { throw new Sitepulse_Test_Json_Response(false, $data, $status); }
function wp_die($message = '', $title = '', $args = array()) { throw new Sitepulse_Test_Die((string) $message); }
function wp_safe_redirect($url, $status = 302) { Sitepulse_Test_WP::$redirects[] = array('url' => $url, 'status' => $status); if (Sitepulse_Test_WP::$redirect_throws) { throw new Sitepulse_Test_Redirect($url, $status); } return true; }
function wp_remote_post($url, $args = array()) { return Sitepulse_Test_WP::http($url, array_merge(array('method' => 'POST'), $args)); }
function wp_remote_get($url, $args = array()) { return Sitepulse_Test_WP::http($url, array_merge(array('method' => 'GET'), $args)); }
function wp_remote_request($url, $args = array()) { return Sitepulse_Test_WP::http($url, $args); }
function wp_remote_retrieve_response_code($response) { return is_wp_error($response) ? '' : ($response['response']['code'] ?? ''); }
function wp_remote_retrieve_body($response) { return is_wp_error($response) ? '' : ($response['body'] ?? ''); }
function is_wp_error($value) { return $value instanceof WP_Error; }
function rest_ensure_response($value) { return $value instanceof WP_REST_Response || is_wp_error($value) ? $value : new WP_REST_Response($value); }
function register_rest_route($namespace, $route, $args, $override = false) { Sitepulse_Test_WP::$routes['/' . trim($namespace, '/') . '/' . ltrim($route, '/')] = $args; return true; }
function _get_cron_array() { return Sitepulse_Test_WP::$cron; }
function wp_next_scheduled($hook, $args = array()) { $event = wp_get_scheduled_event($hook, $args); return $event ? $event->timestamp : false; }
function wp_get_scheduled_event($hook, $args = array(), $timestamp = null) { foreach (Sitepulse_Test_WP::$scheduled as $event) { if ($event->hook === $hook && $event->args === $args && ($timestamp === null || $timestamp === $event->timestamp)) { return $event; } } return false; }
function wp_schedule_event($timestamp, $recurrence, $hook, $args = array(), $wp_error = false) { Sitepulse_Test_WP::$scheduled[] = (object) array('timestamp' => $timestamp, 'schedule' => $recurrence, 'hook' => $hook, 'args' => $args, 'interval' => wp_get_schedules()[$recurrence]['interval'] ?? 0); return true; }
function wp_unschedule_event($timestamp, $hook, $args = array(), $wp_error = false) { Sitepulse_Test_WP::$scheduled = array_values(array_filter(Sitepulse_Test_WP::$scheduled, static function ($event) use ($timestamp, $hook, $args) { return !($event->hook === $hook && $event->timestamp === $timestamp && $event->args === $args); })); return true; }
function wp_clear_scheduled_hook($hook, $args = array(), $wp_error = false) { $count = 0; foreach (Sitepulse_Test_WP::$scheduled as $event) { if ($event->hook === $hook && $event->args === $args) { wp_unschedule_event($event->timestamp, $hook, $args); $count++; } } return $count; }
function wp_get_schedules() { return apply_filters('cron_schedules', array('hourly' => array('interval' => 3600, 'display' => 'Hourly'), 'daily' => array('interval' => 86400, 'display' => 'Daily'))); }
function spawn_cron($gmt_time = 0) { Sitepulse_Test_WP::$spawn_cron_calls++; return true; }
function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') { Sitepulse_Test_WP::$styles[$handle] = compact('src', 'deps', 'ver', 'media'); }
function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $args = false) { Sitepulse_Test_WP::$scripts[$handle] = compact('src', 'deps', 'ver', 'args'); }
function wp_localize_script($handle, $object_name, $l10n) { Sitepulse_Test_WP::$localized[$handle][$object_name] = $l10n; return true; }
function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '', $position = null) { Sitepulse_Test_WP::$menus[$menu_slug] = compact('parent_slug', 'page_title', 'menu_title', 'capability', 'callback'); return 'admin_page_' . $menu_slug; }
function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null) { Sitepulse_Test_WP::$menus[$menu_slug] = compact('page_title', 'menu_title', 'capability', 'callback', 'icon_url', 'position'); return 'toplevel_page_' . $menu_slug; }
function WP_Filesystem($args = false, $context = false, $allow_relaxed_file_ownership = false) { $GLOBALS['wp_filesystem'] = Sitepulse_Test_WP::$filesystem_factory ? call_user_func(Sitepulse_Test_WP::$filesystem_factory) : new Sitepulse_Test_Filesystem(); return (bool) $GLOBALS['wp_filesystem']; }
function wp_cache_delete($key, $group = '') { Sitepulse_Test_WP::$cache_deletes[] = array($key, $group); return true; }

function is_email($email) { return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false; }
function delete_metadata($meta_type, $object_id, $meta_key, $meta_value = '', $delete_all = false) {
    if ($meta_type !== 'user') { throw new RuntimeException('Unsupported test metadata type: ' . $meta_type); }
    foreach (Sitepulse_Test_WP::$user_meta as $id => $metadata) {
        if (($delete_all || $id == $object_id) && array_key_exists($meta_key, $metadata) && ($meta_value === '' || $metadata[$meta_key] === $meta_value)) { unset(Sitepulse_Test_WP::$user_meta[$id][$meta_key]); }
    }
    return true;
}
function esc_sql($value) { return is_array($value) ? array_map('esc_sql', $value) : addslashes((string) $value); }
if (!function_exists('fastcgi_finish_request') && !in_array('fastcgi_finish_request', $GLOBALS['sitepulse_test_omitted_wp_functions'] ?? array(), true)) { function fastcgi_finish_request() { Sitepulse_Test_WP::$finished_requests++; return true; } }

require_once __DIR__ . '/PresentationWordPress.php';

function is_singular($post_types = '') { return Sitepulse_Test_WP::$is_singular; }
function is_home() { return Sitepulse_Test_WP::$is_home; }

/** URL helpers with WordPress semantics: new values are added as given, existing ones re-encoded, false removes. */
function add_query_arg(...$args) {
    if (is_array($args[0])) { $params = $args[0]; $url = $args[1] ?? ($_SERVER['REQUEST_URI'] ?? ''); }
    else { $params = array($args[0] => $args[1]); $url = $args[2] ?? ($_SERVER['REQUEST_URI'] ?? ''); }
    $fragment = ''; $hash = strpos($url, '#'); if ($hash !== false) { $fragment = substr($url, $hash); $url = substr($url, 0, $hash); }
    $base = $url; $query = ''; $mark = strpos($url, '?'); if ($mark !== false) { $base = substr($url, 0, $mark); $query = substr($url, $mark + 1); }
    parse_str($query, $existing); $existing = array_map('urlencode', $existing);
    foreach ($params as $key => $value) { if ($value === false) { unset($existing[$key]); } else { $existing[$key] = $value; } }
    $pairs = array(); foreach ($existing as $key => $value) { $pairs[] = $key . '=' . $value; }
    return $base . ($pairs ? '?' . implode('&', $pairs) : '') . $fragment;
}
function remove_query_arg($key, $query = false) { foreach ((array) $key as $name) { $query = add_query_arg($name, false, $query); } return $query; }
function metadata_exists($meta_type, $object_id, $meta_key) { return array_key_exists($meta_key, Sitepulse_Test_WP::$user_meta[$object_id] ?? array()); }
function wp_generate_password($length = 12, $special_chars = true, $extra_special_chars = false) { return Sitepulse_Test_WP::$passwords ? array_shift(Sitepulse_Test_WP::$passwords) : substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $length)), 0, $length); }
if (!in_array('get_num_queries', $GLOBALS['sitepulse_test_omitted_wp_functions'] ?? array(), true)) { function get_num_queries() { return Sitepulse_Test_WP::$num_queries; } }
function is_404() { return Sitepulse_Test_WP::$is_404; }
function is_front_page() { return Sitepulse_Test_WP::$is_front_page; }
function is_search() { return Sitepulse_Test_WP::$is_search; }
function is_archive() { return Sitepulse_Test_WP::$is_archive; }
function get_post_type($post = null) { return Sitepulse_Test_WP::$post_type; }
function get_post_type_object($post_type) { return Sitepulse_Test_WP::$post_types[$post_type] ?? null; }
function get_post_types($args = array(), $output = 'names') { return Sitepulse_Test_WP::$post_types; }
function get_posts($args = array()) { Sitepulse_Test_WP::$post_queries[] = $args; return Sitepulse_Test_WP::$posts_by_type[$args['post_type']] ?? array(); }
if (!in_array('wp_get_document_title', $GLOBALS['sitepulse_test_omitted_wp_functions'] ?? array(), true)) { function wp_get_document_title() { return Sitepulse_Test_WP::$document_title; } }
function wp_nonce_url($actionurl, $action = -1, $name = '_wpnonce') { return add_query_arg($name, wp_create_nonce($action), $actionurl); }
function check_admin_referer($action = -1, $query_arg = '_wpnonce') { if (!wp_verify_nonce($_REQUEST[$query_arg] ?? '', $action)) { throw new Sitepulse_Test_Die('The link you followed has expired.'); } return 1; }
function wp_get_referer() { return !empty($_REQUEST['_wp_http_referer']) ? wp_unslash($_REQUEST['_wp_http_referer']) : Sitepulse_Test_WP::$referer; }
/** Public post type double with WordPress's object shape. */
function sitepulse_test_post_type($name, $singular) { return (object) array('name' => $name, 'labels' => (object) array('singular_name' => $singular)); }
function is_admin_bar_showing() { return Sitepulse_Test_WP::$admin_bar_showing; }
function untrailingslashit($value) { return rtrim((string) $value, '/\\'); }

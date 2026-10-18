<?php
/**
 * Remote management boundaries: the SitePulse API answers with signed responses, and
 * UpdraftPlus and BackWPup expose the same calls their real classes provide.
 */

const SITEPULSE_TEST_LICENSE = 'sk_test_license_0123456789';

/** Configuration of the backup plugin stand-ins; Sitepulse_Test_Backups::reset() restores it. */
final class Sitepulse_Test_Backups {
    public static $backwpup_version = '';
    /** BackWPup jobs: id => settings. */
    public static $jobs = array();
    /** Registered BackWPup destinations: KEY => array("<jobid>_<KEY>" => file list). */
    public static $destinations = array();
    /** Running BackWPup job, or false. */
    public static $working = false;
    /** BackWPup log headers: path => header. */
    public static $log_headers = array();
    /** Make the backup plugins fail while they are read. */
    public static $throw = false;
    /** The nonce UpdraftPlus reserves for its next backup. */
    public static $nonce = 'abc123def456';
    /** What UpdraftPlus's "Delete" answers per job nonce; other jobs are not found. */
    public static $deletable = array();
    /** Whether a BackWPup job launched with get_jobrun_url() starts. */
    public static $job_starts = true;
    /** Default settings of BackWPup storages: KEY => settings. */
    public static $destination_defaults = array('FOLDER' => array('maxbackups' => 15, 'backupdir' => 'uploads/backwpup'), 'S3' => array('s3maxbackups' => 15, 's3region' => 'us-east-1'));
    /** What UpdraftPlus's restorer does: a callable returning its result; null restores. */
    public static $restore = null;
    /** Backups UpdraftPlus finds in its folder when it rebuilds its history: time => backup. */
    public static $found = array();
    /** Calls the backup plugins received, in order. */
    public static $calls = array();
    public static function reset() {
        foreach (Sitepulse_Test_WP::rememberClassDefaults(__CLASS__) as $name => $value) { self::${$name} = $value; }
        unset($GLOBALS['updraftplus'], $GLOBALS['updraftplus_admin'], $GLOBALS['updraftplus_restorer']);
        wp_delete_file(self::backwpup_started());
    }
    /** The file BackWPup's job writes once it runs. */
    public static function backwpup_started() { return BackWPup::get_plugin_data('TEMP') . '/.backwpup_job_started'; }
    /** Make UpdraftPlus active, as its main file does with the global $updraftplus. */
    public static function updraftplus($version = '1.26.9') { $GLOBALS['updraftplus'] = new UpdraftPlus($version); }
}
Sitepulse_Test_WP::rememberClassDefaults(Sitepulse_Test_Backups::class);

/** UpdraftPlus's schedulers return the interval they scheduled, 'daily' for one WordPress does not know. */
class UpdraftPlus {
    public $version;
    /** The run's state when it saves its record: a later resumption, and the one that only reports failure. */
    public $newresumption_scheduled = false;
    public $current_resumption = 0;
    public $jobdata = array();
    /** The job being run, and the errors it logged. */
    public $nonce = '';
    public $errors = array();
    public function __construct($version) { $this->version = $version; }
    public function jobdata_get($key, $default = null) { return $this->jobdata[$key] ?? $default; }
    public function jobdata_set_multi() { Sitepulse_Test_Backups::$calls[] = array('jobdata_set_multi', func_get_args()); }
    public function initiate_restore_job($nonce = false) { Sitepulse_Test_Backups::$calls[] = array('initiate_restore_job'); $this->nonce = 'restore00001'; }
    public function php_error($errno, $errstr, $errfile, $errline) { return false; }
    public function backup_time_nonce() { Sitepulse_Test_Backups::$calls[] = array('backup_time_nonce'); return Sitepulse_Test_Backups::$nonce; }
    public function schedule_backup($interval) { Sitepulse_Test_Backups::$calls[] = array('schedule_backup', $interval, get_option('updraft_interval')); return $interval; }
    public function schedule_backup_database($interval) { Sitepulse_Test_Backups::$calls[] = array('schedule_backup_database', $interval, get_option('updraft_interval_database')); return $interval; }
}
class UpdraftPlus_Options {
    public static function get_updraft_option($option, $default = null) {
        if (Sitepulse_Test_Backups::$throw) { throw new RuntimeException('UpdraftPlus is broken'); }
        return get_option($option, $default);
    }
    public static function update_updraft_option($option, $value, $use_cache = true, $autoload = 'yes') { return update_option($option, $value, $autoload); }
}
/** UpdraftPlus's history of backups, keyed by time. */
final class UpdraftPlus_Backup_History {
    public static function get_history($timestamp = false) { $history = (array) get_option('updraft_backup_history', array()); krsort($history); return $history; }
    public static function rebuild($remote_scan = false, $only_add_this_file = false, $debug = false) {
        Sitepulse_Test_Backups::$calls[] = array('rebuild');
        update_option('updraft_backup_history', Sitepulse_Test_Backups::$found + (array) get_option('updraft_backup_history', array()));
    }
}
class Updraft_Restorer_Skin {}
/** UpdraftPlus's restorer prints its progress; its clean-up restores the error handler UpdraftPlus set. */
class Updraft_Restorer {
    public function __construct($skin = null, $backup_set = null, $short_init = false, $restore_options = array(), $continuation_data = null) {
        Sitepulse_Test_Backups::$calls[] = array('restorer', get_class($skin), $backup_set, $restore_options, $continuation_data);
    }
    public function perform_restore($entities_to_restore, $restore_options) {
        Sitepulse_Test_Backups::$calls[] = array('perform_restore', $entities_to_restore);
        echo '<h2>Database</h2>';
        return Sitepulse_Test_Backups::$restore ? (Sitepulse_Test_Backups::$restore)() : true;
    }
    public function post_restore_clean_up($successful = true, $browser_context = true) {
        Sitepulse_Test_Backups::$calls[] = array('post_restore_clean_up', is_wp_error($successful) ? $successful->get_error_code() : $successful, $browser_context);
        restore_error_handler();
    }
}
/** UpdraftPlus loads its admin code, which creates the global $updraftplus_admin. */
function updraft_try_include_file($path, $method = 'include') { Sitepulse_Test_Backups::$calls[] = array('include', $path, $method); $GLOBALS['updraftplus_admin'] = new Sitepulse_Test_Updraft_Admin(); }
final class Sitepulse_Test_Updraft_Admin {
    public function activejobs_delete($job_id) {
        Sitepulse_Test_Backups::$calls[] = array('activejobs_delete', $job_id);
        return in_array($job_id, Sitepulse_Test_Backups::$deletable, true) ? array('ok' => 'Y', 'c' => 'deleted') : array('ok' => 'N', 'c' => 'not_found');
    }
}
final class BackWPup {
    public static function get_plugin_data($name = null) {
        $name = strtolower((string) $name);
        return 'version' === $name ? Sitepulse_Test_Backups::$backwpup_version : ('temp' === $name ? WP_CONTENT_DIR . '/uploads' : '');
    }
    public static function get_destination($key) { $key = strtoupper($key); return array_key_exists($key, Sitepulse_Test_Backups::$destinations) ? new Sitepulse_Test_Backwpup_Destination($key) : null; }
}
final class Sitepulse_Test_Backwpup_Destination {
    private $key;
    public function __construct($key) { $this->key = $key; }
    public function file_get_list($jobdest) { return Sitepulse_Test_Backups::$destinations[$this->key][$jobdest] ?? array(); }
    public function option_defaults() { return Sitepulse_Test_Backups::$destination_defaults[$this->key] ?? array(); }
}
final class BackWPup_Option {
    public static function get_job_ids($key = null, $value = false) {
        if (Sitepulse_Test_Backups::$throw) { throw new RuntimeException('BackWPup is broken'); }
        return array_keys(Sitepulse_Test_Backups::$jobs);
    }
    public static function get_job($id, $use_cache = true) { return Sitepulse_Test_Backups::$jobs[$id] ?? false; }
    public static function next_job_id() { return Sitepulse_Test_Backups::$jobs ? max(array_keys(Sitepulse_Test_Backups::$jobs)) + 1 : 1; }
    public static function defaults_job() { return array('type' => BackWPup_JobTypes::$type_job_both, 'destinations' => array('FOLDER'), 'name' => 'New Job', 'activetype' => 'wpcron', 'cronselect' => 'basic', 'cron' => '0 0 1 * *', 'frequency' => 'monthly', 'tempjob' => false, 'backup_now' => false); }
    public static function update($id, $key, $value) { Sitepulse_Test_Backups::$jobs[$id][$key] = $value; return true; }
    public static function create_default_jobs($job_name, array $job_type) {
        $id = self::next_job_id();
        Sitepulse_Test_Backups::$jobs[$id] = array_merge(self::defaults_job(), array('jobid' => $id, 'type' => $job_type, 'name' => $job_name));
        return $id;
    }
}
final class BackWPup_JobTypes {
    public static $type_job_files = array('FILE', 'WPPLUGIN');
    public static $type_job_database = array('DBDUMP');
    public static $type_job_both = array('FILE', 'DBDUMP', 'WPPLUGIN');
}
/** BackWPup's own cron helpers: basic expressions at midnight, and a fixed next run. */
final class BackWPup_Cron {
    public static function get_basic_cron_expression(string $basic_expression, $hours = 0, int $minutes = 0, int $day_of_week = 0, string $day_of_month = '1'): string {
        return array('monthly' => "$minutes $hours $day_of_month * *", 'weekly' => "$minutes $hours * * $day_of_week", 'daily' => "$minutes $hours * * *")[$basic_expression] ?? '';
    }
    public static function cron_next($cronstring) { return 1760572800 + strlen((string) $cronstring); }
}
class BackWPup_Job {
    public $job = array(); public $step_percent = 1; public $step_working = 'CREATE'; public $errors = 0; public $warnings = 0; public $backup_filesize = 0;
    public static function get_working_data() { return Sitepulse_Test_Backups::$working; }
    /** BackWPup returns empty header fields for a log it cannot read. */
    public static function read_logheader($logfile) { return Sitepulse_Test_Backups::$log_headers[$logfile] ?? array('errors' => '', 'warnings' => ''); }
    /** Launching a job: its request writes the start file once it runs. */
    public static function get_jobrun_url($starttype, $jobid = 0) {
        Sitepulse_Test_Backups::$calls[] = array('get_jobrun_url', $starttype, $jobid);
        if (Sitepulse_Test_Backups::$job_starts) { touch(Sitepulse_Test_Backups::backwpup_started()); }
        return array();
    }
    public static function user_abort() { Sitepulse_Test_Backups::$calls[] = array('user_abort'); }
}

/**
 * An API response signed for the request it answers, as api.sitepulse.me signs it.
 *
 * @param array|string $body   Response body.
 * @param array        $tamper Replace the timestamp, nonce or license used for the signature.
 */
function sitepulse_test_remote_response($body = array('success' => true), $status = 200, array $tamper = array()) {
    return static function ($url, $args) use ($body, $status, $tamper) {
        $raw = is_string($body) ? $body : json_encode($body);
        $timestamp = (string) ($tamper['timestamp'] ?? time());
        $nonce = $tamper['nonce'] ?? $args['headers']['X-SitePulse-Nonce'];
        $signature = Sitepulse_Remote_Client::response_signature($nonce, $timestamp, $raw, $tamper['license'] ?? SITEPULSE_TEST_LICENSE);
        return Sitepulse_Test_WP::response($raw, $status, array('X-SitePulse-Timestamp' => $timestamp, 'X-SitePulse-Signature' => $signature));
    };
}

/** Decoded body of the nth request sent to the API. */
function sitepulse_test_remote_request_body($index) { return json_decode(Sitepulse_Test_WP::$http_requests[$index]['args']['body'], true); }

/** Path of the endpoint the nth request went to. */
function sitepulse_test_remote_endpoint($index) { return substr(parse_url(Sitepulse_Test_WP::$http_requests[$index]['url'], PHP_URL_PATH), strlen('/api/website/remote/')); }

/** WordPress's upgrader as remote management uses it, and the site's own pages; Sitepulse_Test_Upgrades::reset() restores them. */
final class Sitepulse_Test_Upgrades {
    /** What upgrade() does per "kind:id": a callable returning the upgrader's result. */
    public static $outcomes = array();
    /** What restore_temp_backup() returns, and what it does when it succeeds. */
    public static $restore = true;
    public static $on_restore = null;
    /** The site's pages for health checks, by URL: status (0: no answer) and fatal. */
    public static $pages = array();
    /** WooCommerce's cart and checkout links; empty without WooCommerce pages. */
    public static $woocommerce = array();
    /** Progress the site reported (command-progress), and whether the API cannot be reached for it. */
    public static $progress = array();
    public static $progress_unreachable = false;
    public static $calls = array();
    public static function reset() {
        foreach (Sitepulse_Test_WP::rememberClassDefaults(__CLASS__) as $name => $value) { self::${$name} = $value; }
        Sitepulse_Test_WP::$http_responder = 'sitepulse_test_site_pages';
    }
}
Sitepulse_Test_WP::rememberClassDefaults(Sitepulse_Test_Upgrades::class);

class Automatic_Upgrader_Skin {}
abstract class Sitepulse_Test_Upgrader {
    public $skin;
    public function __construct($skin = null) { $this->skin = $skin; }
    public function maintenance_mode($enable = false) { Sitepulse_Test_Upgrades::$calls[] = array('maintenance', $enable); }
    public function restore_temp_backup(array $temp_backups = array()) {
        Sitepulse_Test_Upgrades::$calls[] = array('restore', $temp_backups);
        if (true === Sitepulse_Test_Upgrades::$restore && Sitepulse_Test_Upgrades::$on_restore) { (Sitepulse_Test_Upgrades::$on_restore)(); }
        return Sitepulse_Test_Upgrades::$restore;
    }
    public function delete_temp_backup(array $temp_backups = array()) { Sitepulse_Test_Upgrades::$calls[] = array('delete_temp_backup', $temp_backups); return true; }
    /** Installing a package: what Sitepulse_Test_Upgrades::$outcomes["install:<package>"] does, or a download WordPress.org refuses. */
    public function install($package, $args = array()) {
        Sitepulse_Test_Upgrades::$calls[] = array('install', $package, $args);
        $outcome = Sitepulse_Test_Upgrades::$outcomes['install:' . $package] ?? null;
        return $outcome ? $outcome() : new WP_Error('download_failed', 'Not Found');
    }
    protected function run($kind, $id, array $args = array()) {
        Sitepulse_Test_Upgrades::$calls[] = array('upgrade', $kind, $id);
        $outcome = Sitepulse_Test_Upgrades::$outcomes[$kind . ':' . $id] ?? null;
        $result = $outcome ? $outcome() : false;
        // Like WordPress, an update clears the offered updates unless asked not to.
        if (false !== ($args['clear_update_cache'] ?? true)) { unset(Sitepulse_Test_WP::$site_transients['update_' . $kind . 's']); }
        return $result;
    }
}
class Plugin_Upgrader extends Sitepulse_Test_Upgrader { public function upgrade($plugin, $args = array()) { return $this->run('plugin', $plugin, $args); } }
class Theme_Upgrader extends Sitepulse_Test_Upgrader { public function upgrade($theme, $args = array()) { return $this->run('theme', $theme, $args); } }
class Core_Upgrader extends Sitepulse_Test_Upgrader { public function upgrade($current, $args = array()) { Sitepulse_Test_Upgrades::$calls[] = array('core_args', $args); return $this->run('core', $current->current); } }
function wc_get_page_permalink($page) { return Sitepulse_Test_Upgrades::$woocommerce[$page] ?? ''; }

/**
 * The site's own pages answer WordPress's fatal-error check: healthy unless Sitepulse_Test_Upgrades::$pages
 * says otherwise, and the API acknowledges progress reports. Other requests (the SitePulse API) use the queue.
 */
function sitepulse_test_site_pages($url, $args) {
    // The API acknowledges the progress the site reports while it updates, signed like its other answers.
    if (false !== strpos($url, '/api/website/remote/command-progress')) {
        Sitepulse_Test_Upgrades::$progress[] = json_decode($args['body'], true)['progress'];
        $key = get_option(Sitepulse_Remote_Management::KEY_OPTION, '');
        return Sitepulse_Test_Upgrades::$progress_unreachable
            ? new WP_Error('http_request_failed', 'Connection refused')
            : call_user_func(sitepulse_test_remote_response(array('success' => true), 200, array('license' => is_string($key) && '' !== $key ? $key : SITEPULSE_TEST_LICENSE)), $url, $args);
    }
    if (0 !== strpos($url, home_url('/'))) { return null; }
    $page = strtok($url, '?');
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
    $state = Sitepulse_Test_Upgrades::$pages[$page] ?? array();
    if (0 === ($state['status'] ?? 200)) { return new WP_Error('http_request_failed', 'Connection refused'); }
    // A request that dies without running shutdown functions answers nothing at all.
    if (!empty($state['silent'])) { return Sitepulse_Test_WP::response('', 200); }
    $key = $query['wp_scrape_key'] ?? '';
    // Like WordPress, a request whose key and nonce do not match the transient is refused.
    $result = get_transient('scrape_key_' . $key) !== ($query['wp_scrape_nonce'] ?? null)
        ? array('code' => 'scrape_nonce_failure', 'message' => 'Scrape key check failed. Please try again.')
        : (isset($state['fatal']) ? array('type' => 1, 'message' => $state['fatal'], 'file' => 'wp-content/plugins/x.php', 'line' => 3) : true);
    $body = "<html>page</html>\n###### wp_scraping_result_start:$key ######\n" . json_encode($result) . "\n###### wp_scraping_result_end:$key ######\n";
    return Sitepulse_Test_WP::response($body, $state['status'] ?? 200);
}

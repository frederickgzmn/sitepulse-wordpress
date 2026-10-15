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
    /** Calls the backup plugins received, in order. */
    public static $calls = array();
    public static function reset() {
        foreach (Sitepulse_Test_WP::rememberClassDefaults(__CLASS__) as $name => $value) { self::${$name} = $value; }
        unset($GLOBALS['updraftplus'], $GLOBALS['updraftplus_admin']);
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
    public function __construct($version) { $this->version = $version; }
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

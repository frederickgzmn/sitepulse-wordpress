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
    public static function reset() {
        foreach (Sitepulse_Test_WP::rememberClassDefaults(__CLASS__) as $name => $value) { self::${$name} = $value; }
        unset($GLOBALS['updraftplus']);
    }
    /** Make UpdraftPlus active, as its main file does with the global $updraftplus. */
    public static function updraftplus($version = '1.26.9') { $GLOBALS['updraftplus'] = new UpdraftPlus($version); }
}
Sitepulse_Test_WP::rememberClassDefaults(Sitepulse_Test_Backups::class);

class UpdraftPlus { public $version; public function __construct($version) { $this->version = $version; } }
class UpdraftPlus_Options {
    public static function get_updraft_option($option, $default = null) {
        if (Sitepulse_Test_Backups::$throw) { throw new RuntimeException('UpdraftPlus is broken'); }
        return get_option($option, $default);
    }
}
final class BackWPup {
    public static function get_plugin_data($name = null) { return 'version' === strtolower((string) $name) ? Sitepulse_Test_Backups::$backwpup_version : ''; }
    public static function get_destination($key) { $key = strtoupper($key); return array_key_exists($key, Sitepulse_Test_Backups::$destinations) ? new Sitepulse_Test_Backwpup_Destination($key) : null; }
}
final class Sitepulse_Test_Backwpup_Destination {
    private $key;
    public function __construct($key) { $this->key = $key; }
    public function file_get_list($jobdest) { return Sitepulse_Test_Backups::$destinations[$this->key][$jobdest] ?? array(); }
}
final class BackWPup_Option {
    public static function get_job_ids($key = null, $value = false) {
        if (Sitepulse_Test_Backups::$throw) { throw new RuntimeException('BackWPup is broken'); }
        return array_keys(Sitepulse_Test_Backups::$jobs);
    }
    public static function get_job($id, $use_cache = true) { return Sitepulse_Test_Backups::$jobs[$id] ?? false; }
}
class BackWPup_Job {
    public $job = array(); public $step_percent = 1; public $step_working = 'CREATE'; public $errors = 0; public $warnings = 0; public $backup_filesize = 0;
    public static function get_working_data() { return Sitepulse_Test_Backups::$working; }
    /** BackWPup returns empty header fields for a log it cannot read. */
    public static function read_logheader($logfile) { return Sitepulse_Test_Backups::$log_headers[$logfile] ?? array('errors' => '', 'warnings' => ''); }
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

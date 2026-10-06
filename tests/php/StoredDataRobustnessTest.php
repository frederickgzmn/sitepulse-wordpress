<?php
require_once __DIR__ . '/Support/LocalFilesystem.php';
require_once __DIR__ . '/Support/Database.php';
require_once __DIR__ . '/Support/PresentationWordPress.php';

/**
 * Sites keep whatever earlier versions stored. Every screen must render stale,
 * partial and corrupted stored data without a single PHP warning, notice or error.
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class StoredDataRobustnessTest extends Sitepulse_Test_Case {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['wp_filesystem'] = new Sitepulse_Test_Local_Filesystem();
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
        $GLOBALS['timestart'] = null;
        Sitepulse_Test_WP::$screen = (object) array('id' => 'toplevel_page_wpsp_sitepulse');
        update_option('admin_email', 'admin@example.test');
        update_option('date_format', 'Y-m-d'); update_option('time_format', 'H:i');
        Sitepulse_Easy_Mode::init();
    }

    /** Stored values as older versions, interrupted writes or other plugins may leave them. */
    private static function storedData(string $scenario): array {
        if ($scenario === 'corrupt') {
            return array_fill_keys(array(
                'sitepulse_profiler_stats', 'sitepulse_curl_api_events', 'sitepulse_plugin_profiler_stats', 'sitepulse_error_log',
                'sitepulse_vulnerabilities_list', 'sitepulse_pagespeed_report', 'sitepulse_ai_diagnostic_report', 'sitepulse_settings',
                'sitepulse_whitelabel', 'sitepulse_page_analyses', 'sitepulse_profiler_current_time', 'sitepulse_last_vulnerability_check',
                'sitepulse_plugin_profiler_total_time',
            ), 'corrupt') + array('sitepulse_ai_diagnostic_status' => 'completed');
        }
        if ($scenario === 'nested') {
            return array(
                'sitepulse_profiler_stats' => array('null-fields' => array_fill_keys(array_keys(Sitepulse_Profiler::STAT_DEFAULTS), null)),
                'sitepulse_curl_api_events' => array(array_fill_keys(array_keys(Sitepulse_CurLoader::EVENT_DEFAULTS), null)),
                'sitepulse_plugin_profiler_stats' => array('null-fields' => array_fill_keys(array_keys(Sitepulse_Plugin_Profiler::STAT_DEFAULTS), null)),
                'sitepulse_error_log' => array('errors' => 'corrupt', 'total_count' => 3, 'last_error_date' => 'corrupt'),
                'sitepulse_vulnerabilities_list' => array(array('name' => null, 'version' => null)),
                'sitepulse_pagespeed_report' => array('has_data' => true, 'desktop' => array('scores' => 'corrupt', 'metrics' => 'corrupt'), 'mobile' => 'corrupt'),
                'sitepulse_ai_diagnostic_status' => 'completed',
                'sitepulse_ai_diagnostic_report' => json_encode(array('summary' => null, 'recommendations' => 'corrupt')),
                'sitepulse_settings' => array('external_api_enabled' => null),
                'sitepulse_whitelabel' => array('enabled' => true, 'plugin_name' => null),
                'sitepulse_page_analyses' => array(array('id' => null, 'url' => null, 'created_at' => null, 'samples' => 'corrupt')),
            );
        }
        return array(
            'sitepulse_profiler_stats' => array('old-a' => array('hook' => 'init', 'total' => 0.002), 'old-b' => array(), 'old-c' => 'not-an-array'),
            'sitepulse_profiler_current_time' => 'not-a-date',
            'sitepulse_curl_api_events' => array(array('url' => 'https://api.example.test/x'), array(), 'not-an-array'),
            'sitepulse_plugin_profiler_stats' => array(
                md5('old/old.php') => array('plugin_name' => 'Old Plugin', 'avg_time' => 0.4, 'loads' => 3, 'total_time' => 1.2, 'avg_memory' => 2048),
                'empty' => array(), 'scalar' => 'not-an-array',
            ),
            'sitepulse_error_log' => array('errors' => array(array('message' => 'Legacy entry'), array(), 'not-an-array'), 'total_count' => 3),
            'sitepulse_vulnerabilities_list' => array(array('name' => 'Old'), array(), 'not-an-array'),
            'sitepulse_last_vulnerability_check' => 'not-a-date',
            'sitepulse_pagespeed_report' => array('has_data' => true, 'desktop' => array('scores' => array('performance' => 0.7))),
            'sitepulse_ai_diagnostic_status' => 'completed',
            'sitepulse_ai_diagnostic_report' => json_encode(array('summary' => 'Legacy summary', 'recommendations' => array('high' => array('Do one thing')))),
            'sitepulse_settings' => array('legacy_key' => 1),
            'sitepulse_whitelabel' => array('enabled' => true),
            'sitepulse_page_analyses' => array(array('id' => 'broken')),
        );
    }

    public static function screens(): array {
        $rows = array();
        foreach (array('legacy', 'corrupt', 'nested') as $scenario) {
            foreach (array('home', 'performance', 'resource-load', 'security', 'insights', 'api-monitor', 'system', 'settings') as $view) {
                $rows["$scenario simple $view"] = array($scenario, true, 'render_dashboard_section', $view);
            }
            foreach (array('render_dashboard_section', 'render_resource_load', 'render_curl_api', 'render_page_sitepulse_settings', 'render_page_analysis') as $method) {
                $rows["$scenario advanced $method"] = array($scenario, false, $method, 'home');
                $rows["$scenario simple $method"] = array($scenario, true, $method, 'home');
            }
            $rows["$scenario toolbar"] = array($scenario, false, 'toolbar', 'home');
        }
        return $rows;
    }

    /** @dataProvider screens */
    public function test_screen_renders_stored_data_without_php_warnings_or_errors($scenario, $easy, $method, $view): void {
        $this->seed($scenario);
        update_user_meta(1, 'sitepulse_easy_mode', $easy ? '1' : '0');
        $_GET = array('page' => 'wpsp_sitepulse', 'sp_view' => $view);

        $bar = new class { public $nodes = array(); public function add_node($node) { $this->nodes[] = $node['id']; } };
        list($problems, $html) = $this->run_collecting_problems(static function () use ($method, $bar) {
            Sitepulse_Plugin_Profiler::init();
            if ($method === 'toolbar') {
                Sitepulse_Frontend::admin_bar_node($bar);
            } else {
                (new Sitepulse_Backend(new Sitepulse_Plugin()))->$method();
            }
        });

        $this->assertSame(array(), $problems);
        if ($method === 'toolbar') { $this->assertSame(array('wpsp', 'wpsp_dashboard', 'wpsp_analyze_page', 'wpsp_monitoring'), $bar->nodes); }
        else { $this->assertNotSame('', trim($html)); }
    }

    public static function scenarios(): array {
        return array('legacy' => array('legacy'), 'corrupt' => array('corrupt'), 'nested' => array('nested'));
    }

    /** @dataProvider scenarios */
    public function test_every_request_reads_and_updates_stored_data_without_php_warnings_or_errors($scenario): void {
        $this->seed($scenario);
        Sitepulse_Test_WP::$is_admin = true;
        update_option('sitepulse_profiler_enabled', true);

        list($problems) = $this->run_collecting_problems(static function () {
            Sitepulse_Error_Handler::cleanup_old_errors_on_init();
            Sitepulse_Error_Handler::handle_error(E_USER_WARNING, 'Probe warning', '/var/www/probe.php', 7);
            Sitepulse_Error_Handler::get_error_log_stats();
            Sitepulse_Profiler::init();
            Sitepulse_Profiler::record('init', 10, 'cb', 'strlen', .002);
            Sitepulse_Profiler::persist();
            Sitepulse_CurLoader::init();
            Sitepulse_CurLoader::capture(Sitepulse_Test_WP::response(array()), 'response', 'Requests', array('_wshp_start' => microtime(true) - 2, '_wshp_trace' => array()), 'https://api.test/slow');
            Sitepulse_Plugin_Profiler::init();
            Sitepulse_Plugin_Profiler::persist();
        });

        $this->assertSame(array(), $problems);
        $this->assertSame('Probe warning', end(Sitepulse_Error_Handler::get_error_log()['errors'])['message']);
    }

    private function seed(string $scenario): void {
        foreach (self::storedData($scenario) as $option => $value) { update_option($option, $value); }
        Sitepulse_Test_WP::$plugins = array('old/old.php' => array('Name' => 'Old Plugin', 'Version' => '1.0'));
        update_option('active_plugins', array('old/old.php'));
    }

    /** Runs the callback and returns every PHP warning, notice or uncaught error it raised, plus its output. */
    private function run_collecting_problems(callable $callback): array {
        $problems = array();
        set_error_handler(static function ($errno, $message, $file, $line) use (&$problems) {
            $problems[] = str_replace(SITEPULSE_PATH, '', $file) . ':' . $line . ' ' . $message;
            return true;
        });
        ob_start();
        try {
            $callback();
        } catch (Throwable $error) {
            $problems[] = get_class($error) . ': ' . $error->getMessage() . ' at ' . str_replace(SITEPULSE_PATH, '', $error->getFile()) . ':' . $error->getLine();
        } finally {
            $html = ob_get_clean();
            restore_error_handler();
        }
        return array(array_values(array_unique($problems)), $html);
    }
}

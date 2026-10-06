<?php

require_once SITEPULSE_PATH . 'inc/api_backend.php';
require_once __DIR__ . '/Support/Database.php';

final class AutoloadRestApiTest extends Sitepulse_Test_Case {
    private $previous_database;

    protected function setUp(): void {
        parent::setUp();
        $this->previous_database = $GLOBALS['wpdb'] ?? null;
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
    }

    protected function tearDown(): void {
        $GLOBALS['wpdb'] = $this->previous_database;
        parent::tearDown();
    }

    private function request(array $params = array()): WP_REST_Request {
        $request = new WP_REST_Request('POST', '/sitepulse/v1/autoload_options');
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(json_encode($params));
        $request->set_param('_wpnonce', wp_create_nonce('wp_rest'));
        return $request;
    }

    public function test_autoload_list_normalizes_legacy_values_and_formats_sizes(): void {
        $GLOBALS['wpdb']->results = array(
            array('option_id' => '4', 'option_name' => 'plugin_big', 'autoload' => 'yes', 'data_size' => '1048576'),
            array('option_id' => '5', 'option_name' => 'plugin_medium', 'autoload' => 'auto-on', 'data_size' => '2048'),
            array('option_id' => '6', 'option_name' => 'plugin_small', 'autoload' => 'on', 'data_size' => '17'),
        );
        $GLOBALS['wpdb']->var = '1050641';
        $result = sitepulse_get_autoload_options($this->request())->get_data();
        $this->assertTrue($result['success']);
        $this->assertSame(3, $result['count']);
        $this->assertSame(array(4, 5, 6), array_column($result['data'], 'option_id'));
        $this->assertSame(array('on', 'on', 'on'), array_column($result['data'], 'autoload'));
        $this->assertSame(array('1.00 MB', '2.00 KB', '17 B'), array_column($result['data'], 'data_size_formatted'));
        $this->assertSame(1050641, $result['total_autoload_size']);
        $this->assertSame('1.00 MB', $result['total_autoload_size_formatted']);
    }

    public function test_autoload_query_excludes_transients_and_core_options_and_limits_largest_results(): void {
        sitepulse_get_autoload_options($this->request());
        $query = $GLOBALS['wpdb']->queries[0];
        $this->assertStringContainsString("autoload IN ('yes','on','auto-on')", $query);
        $this->assertStringContainsString('option_name NOT IN', $query);
        $this->assertStringContainsString("'siteurl'", $query);
        $this->assertStringContainsString("'active_plugins'", $query);
        $this->assertStringContainsString("option_name NOT LIKE '\\_transient%'", $query);
        $this->assertStringContainsString('ORDER BY data_size DESC', $query);
        $this->assertStringContainsString('LIMIT 20', $query);
        $this->assertStringContainsString("autoload IN ('yes','on','auto-on')", $GLOBALS['wpdb']->queries[1]);
    }

    public function test_empty_autoload_list_has_zero_totals(): void {
        $result = sitepulse_get_autoload_options($this->request())->get_data();
        $this->assertSame(array(), $result['data']);
        $this->assertSame(0, $result['count']);
        $this->assertSame(0, $result['total_autoload_size']);
        $this->assertSame('0 B', $result['total_autoload_size_formatted']);
    }

    public function test_database_read_failure_returns_server_error(): void {
        $GLOBALS['wpdb']->results = null;
        $error = sitepulse_get_autoload_options($this->request());
        $this->assertInstanceOf(WP_Error::class, $error);
        $this->assertSame('db_error', $error->get_error_code());
        $this->assertSame(500, $error->get_error_data()['status']);
    }

    /** @dataProvider autoloadValues */
    public function test_autoload_update_normalizes_values_targets_one_id_and_invalidates_cache(string $input, string $expected): void {
        $GLOBALS['wpdb']->row = array('option_id' => 24, 'option_name' => 'sample_plugin_settings');
        $result = sitepulse_update_autoload_option($this->request(array('option_id' => '24', 'autoload' => $input)))->get_data();
        $this->assertTrue($result['success']);
        $this->assertSame($expected, $result['autoload']);
        $this->assertSame(24, $result['option_id']);
        $this->assertSame(array(array('table' => 'wp_options', 'data' => array('autoload' => $expected), 'where' => array('option_id' => 24), 'format' => array('%s'), 'where_format' => array('%d'))), $GLOBALS['wpdb']->updates);
        $this->assertSame(array(array('alloptions', 'options')), Sitepulse_Test_WP::$cache_deletes);
        $this->assertStringContainsString('WHERE option_id = 24', $GLOBALS['wpdb']->queries[0]);
    }

    public static function autoloadValues(): array {
        return array('legacy yes' => array('yes', 'on'), 'legacy no' => array('no', 'off'), 'modern on' => array('on', 'on'), 'modern off' => array('off', 'off'));
    }

    /** @dataProvider protectedOptions */
    public function test_critical_wordpress_options_cannot_be_modified(string $option): void {
        $GLOBALS['wpdb']->row = array('option_id' => 1, 'option_name' => $option);
        $error = sitepulse_update_autoload_option($this->request(array('option_id' => 1, 'autoload' => 'off')));
        $this->assertInstanceOf(WP_Error::class, $error);
        $this->assertSame('protected_option', $error->get_error_code());
        $this->assertSame(403, $error->get_error_data()['status']);
        $this->assertSame(array(), $GLOBALS['wpdb']->updates);
        $this->assertSame(array(), Sitepulse_Test_WP::$cache_deletes);
    }

    public static function protectedOptions(): array {
        return array('site URL' => array('siteurl'), 'home URL' => array('home'), 'plugins' => array('active_plugins'), 'cron' => array('cron'), 'roles' => array('wp_user_roles'), 'theme' => array('stylesheet'));
    }

    public function test_missing_option_does_not_issue_update(): void {
        $error = sitepulse_update_autoload_option($this->request(array('option_id' => 404, 'autoload' => 'off')));
        $this->assertSame('option_not_found', $error->get_error_code());
        $this->assertSame(404, $error->get_error_data()['status']);
        $this->assertSame(array(), $GLOBALS['wpdb']->updates);
    }

    public function test_failed_database_update_preserves_cache_and_reports_server_error(): void {
        $GLOBALS['wpdb']->row = array('option_id' => 1, 'option_name' => 'sample_plugin_settings');
        $GLOBALS['wpdb']->update_result = false;
        $error = sitepulse_update_autoload_option($this->request(array('option_id' => 1, 'autoload' => 'off')));
        $this->assertSame('update_failed', $error->get_error_code());
        $this->assertSame(500, $error->get_error_data()['status']);
        $this->assertSame(array(), Sitepulse_Test_WP::$cache_deletes);
    }

    public function test_idempotent_update_with_zero_changed_rows_still_succeeds(): void {
        $GLOBALS['wpdb']->row = array('option_id' => 1, 'option_name' => 'sample_plugin_settings');
        $GLOBALS['wpdb']->update_result = 0;
        $this->assertTrue(sitepulse_update_autoload_option($this->request(array('option_id' => 1, 'autoload' => 'off')))->get_data()['success']);
    }
}

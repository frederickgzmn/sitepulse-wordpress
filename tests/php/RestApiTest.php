<?php

require_once SITEPULSE_PATH . 'inc/api_backend.php';

final class RestApiTest extends Sitepulse_Test_Case {
    private static $route_initializer;

    public static function captureRouteRegistration(): void {
        foreach ($GLOBALS['wp_filter']['rest_api_init']->callbacks as $callbacks) {
            foreach ($callbacks as $entry) {
                if ($entry['function'] instanceof Closure) {
                    self::$route_initializer = $entry['function'];
                }
            }
        }
    }

    public function test_all_registered_routes_require_admin_capability_and_nonce_argument(): void {
        (self::$route_initializer)();
        foreach (array(
            '/sitepulse/v1/settings/update' => 'sitepulse_update_settings',
            '/sitepulse/v1/ai_diagnostic/request' => 'sitepulse_request_ai_diagnostic_api',
            '/sitepulse/v1/vulnerability/check' => 'sitepulse_check_vulnerabilities_api',
            '/sitepulse/v1/autoload_options/update' => 'sitepulse_update_autoload_option',
        ) as $path => $handler) {
            $this->assertArrayHasKey($path, Sitepulse_Test_WP::$routes);
            $this->assertSame($handler, Sitepulse_Test_WP::$routes[$path]['callback']);
        }
        foreach (Sitepulse_Test_WP::$routes as $route => $definition) {
            $this->assertTrue(is_callable($definition['callback']), $route);
            $this->assertTrue($definition['args']['_wpnonce']['required'], $route);
            Sitepulse_Test_WP::$capabilities['manage_options'] = false;
            $this->assertFalse($definition['permission_callback'](), $route);
            Sitepulse_Test_WP::$capabilities['manage_options'] = true;
            $this->assertTrue($definition['permission_callback'](), $route);
        }
        $this->assertSame('sitepulse_update_settings', Sitepulse_Test_WP::$routes['/sitepulse/v1/settings/update']['callback']);
        $this->assertSame('POST', Sitepulse_Test_WP::$routes['/sitepulse/v1/autoload_options/update']['methods']);
    }

    public function test_registration_helper_retains_extra_argument_validation(): void {
        sitepulse_register_route('/example', 'sitepulse_pause_monitoring', 'GET', array('page_id' => array('type' => 'integer', 'required' => true)));
        $route = Sitepulse_Test_WP::$routes['/sitepulse/v1/example'];
        $this->assertSame('GET', $route['methods']);
        $this->assertSame(array('type' => 'integer', 'required' => true), $route['args']['page_id']);
        $this->assertTrue($route['args']['_wpnonce']['required']);
    }
    private function request(array $params = array(), bool $valid_nonce = true): WP_REST_Request {
        $request = new WP_REST_Request('POST', '/sitepulse/v1/test');
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(json_encode($params));
        $request->set_param('_wpnonce', $valid_nonce ? wp_create_nonce('wp_rest') : 'invalid');
        return $request;
    }

    private function assertError($response, string $code, int $status): void {
        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertSame($code, $response->get_error_code());
        $this->assertSame($status, $response->get_error_data()['status']);
    }

    private function queue(array $body, int $status = 200): void {
        Sitepulse_Test_WP::$http_queue[] = array('headers' => array('content-type' => 'application/json'), 'body' => json_encode($body), 'response' => array('code' => $status, 'message' => 'Fixture response'), 'cookies' => array(), 'filename' => null);
    }

    /** @dataProvider protectedHandlers */
    public function test_explicit_nonce_checks_reject_invalid_nonce_before_side_effects(string $handler): void {
        $before = Sitepulse_Test_WP::$options;
        $this->assertError($handler($this->request(array(), false)), 'invalid_nonce', 403);
        $this->assertSame($before, Sitepulse_Test_WP::$options);
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public static function protectedHandlers(): array {
        return array_map(static function ($handler) { return array($handler); }, array(
            'sitepulse_complete_onboarding', 'sitepulse_dismiss_onboarding', 'sitepulse_update_settings',
            'sitepulse_dismiss_onboarding_notice', 'sitepulse_reset_onboarding', 'sitepulse_get_profiler_stats',
            'sitepulse_get_curl_stats', 'sitepulse_get_memory_info', 'sitepulse_get_plugin_profiler_stats',
            'sitepulse_collect_plugin_data', 'sitepulse_check_vulnerabilities_api', 'sitepulse_request_ai_diagnostic_api',
            'sitepulse_check_ai_diagnostic_status_api', 'sitepulse_get_autoload_options', 'sitepulse_update_autoload_option',
        ));
    }

    /** @dataProvider dispatchPermissions */
    public function test_pre_dispatch_protects_mutations_without_interfering_with_other_routes(string $route, string $method, bool $admin, bool $denied): void {
        Sitepulse_Test_WP::$capabilities['manage_options'] = $admin;
        $request = new WP_REST_Request($method, $route);
        $sentinel = new stdClass();
        $result = sitepulse_rest_pre_dispatch($sentinel, null, $request);
        if ($denied) {
            $this->assertError($result, 'sitepulse_rest_forbidden', 403);
        } else {
            $this->assertSame($sentinel, $result);
        }
    }

    public static function dispatchPermissions(): array {
        return array(
            'unauthorized post' => array('/sitepulse/v1/settings/update', 'POST', false, true),
            'unauthorized delete' => array('/sitepulse/v1/settings/update', 'DELETE', false, true),
            'authorized post' => array('/sitepulse/v1/settings/update', 'POST', true, false),
            'read preserved' => array('/sitepulse/v1/settings/update', 'GET', false, false),
            'other plugin' => array('/other/v1/settings/update', 'POST', false, false),
            'similar prefix' => array('/sitepulse-other/v1/test', 'POST', false, false),
            'empty route' => array('', 'POST', false, false),
        );
    }

    /** @dataProvider missingParameters */
    public function test_required_settings_parameters_are_rejected_without_mutation(string $handler, array $params, string $code): void {
        $before = Sitepulse_Test_WP::$options;
        $this->assertError($handler($this->request($params)), $code, 400);
        $this->assertSame($before, Sitepulse_Test_WP::$options);
    }

    public static function missingParameters(): array {
        return array(
            'http tracker missing' => array('sitepulse_set_wpslowhttp', array(), 'missing_wpslowhttp'),
            'profiler missing' => array('sitepulse_set_sp_profiler', array(), 'missing_profiler_enabled'),
            'dashboard view missing' => array('sitepulse_set_dashboard_view', array(), 'sitepulse_invalid_view'),
            'dashboard view unknown' => array('sitepulse_set_dashboard_view', array('view' => 'expert'), 'sitepulse_invalid_view'),
            'analysis url missing' => array('sitepulse_page_analysis_start', array(), 'sitepulse_missing_url'),
            'analysis of another site' => array('sitepulse_page_analysis_start', array('url' => 'https://elsewhere.test/'), 'sitepulse_external_url'),
            'autoload missing' => array('sitepulse_update_autoload_option', array('option_id' => 4), 'missing_params'),
            'autoload invalid' => array('sitepulse_update_autoload_option', array('option_id' => 4, 'autoload' => 'invalid'), 'invalid_autoload'),
        );
    }

    public function test_retired_single_page_and_simulated_realtime_routes_are_gone(): void {
        (self::$route_initializer)();
        foreach (array('wpspageloadhttp/set_active', 'sp_report_mode/set_active', 'wpsprealtimemode/set_active', 'wpsprealtimemode/get_active') as $route) {
            $this->assertArrayNotHasKey('/sitepulse/v1/' . $route, Sitepulse_Test_WP::$routes);
        }
        foreach (array('page_analysis/start' => 'sitepulse_page_analysis_start', 'page_analysis/sample' => 'sitepulse_page_analysis_sample', 'page_analysis/collect' => 'sitepulse_page_analysis_collect',
            'page_analysis/report' => 'sitepulse_page_analysis_report', 'page_analysis/delete' => 'sitepulse_page_analysis_delete', 'monitoring/pause' => 'sitepulse_pause_monitoring',
            'monitoring/resume' => 'sitepulse_resume_monitoring', 'dashboard_view' => 'sitepulse_set_dashboard_view', 'getting_started/dismiss' => 'sitepulse_dismiss_getting_started') as $route => $handler) {
            $this->assertSame($handler, Sitepulse_Test_WP::$routes['/sitepulse/v1/' . $route]['callback']);
        }
    }

    public function test_page_analysis_routes_start_report_and_remove_an_analysis(): void {
        Sitepulse_Test_WP::$passwords = array('Analysis01AB');
        $started = sitepulse_page_analysis_start($this->request(array('url' => '/shop/')))->get_data();
        $this->assertSame(array('id' => 'analysis01ab', 'url' => 'https://example.test/shop/', 'samples' => 3), $started);
        $this->assertSame(array('ok' => true, 'collected' => 0, 'samples' => 0), sitepulse_page_analysis_collect($this->request(array('id' => 'analysis01ab')))->get_data());
        $this->assertFalse(sitepulse_page_analysis_report($this->request(array('id' => 'ANALYSIS01AB')))->get_data()['complete']);
        $this->assertTrue(sitepulse_page_analysis_delete($this->request(array('id' => 'analysis01ab')))->get_data()['success']);
        $this->assertFalse(sitepulse_page_analysis_delete($this->request(array('id' => 'analysis01ab')))->get_data()['success']);
        $this->assertError(sitepulse_page_analysis_sample($this->request(array('id' => 'analysis01ab'))), 'sitepulse_analysis_not_found', 404);
        $this->assertError(sitepulse_page_analysis_report($this->request()), 'sitepulse_analysis_not_found', 404);
    }

    public function test_monitoring_can_be_paused_and_resumed_over_rest(): void {
        $this->assertSame(array('success' => true, 'paused' => true), sitepulse_pause_monitoring($this->request())->get_data());
        $this->assertTrue(Sitepulse_Monitoring::is_paused());
        $this->assertSame(array('success' => true, 'paused' => false), sitepulse_resume_monitoring($this->request())->get_data());
        $this->assertFalse(Sitepulse_Monitoring::is_paused());
    }

    public function test_dashboard_view_and_getting_started_preferences_are_saved_per_user(): void {
        $this->assertSame(array('success' => true, 'view' => 'basic'), sitepulse_set_dashboard_view($this->request(array('view' => 'BASIC')))->get_data());
        $this->assertSame('basic', get_user_meta(1, 'sitepulse_dashboard_view', true));
        $this->assertSame(array('success' => true), sitepulse_dismiss_getting_started($this->request())->get_data());
        $this->assertSame(1, get_user_meta(1, 'sitepulse_getting_started_dismissed', true));
    }

    public function test_disabling_http_tracker_stores_a_real_false_that_stops_tracking(): void {
        $result = sitepulse_set_wpslowhttp($this->request(array('wpslowhttp' => 'disabled')))->get_data();
        $this->assertFalse($result['wpslowhttp']); $this->assertFalse(get_option('sitepulse_curl_api_enabled'));
        Sitepulse_CurLoader::init();
        $this->assertSame(array('timeout' => 5), Sitepulse_CurLoader::tag_start_time(array('timeout' => 5), 'https://api.test'));
    }

    public function test_enabling_http_tracker_resets_current_users_dismissal(): void {
        update_user_meta(get_current_user_id(), 'sitepulse_trackers_disabled_notice_dismissed', true);
        $result = sitepulse_set_wpslowhttp($this->request(array('wpslowhttp' => 'enabled')))->get_data();
        $this->assertTrue($result['wpslowhttp']);
        $this->assertTrue(get_option('sitepulse_curl_api_enabled'));
        $this->assertFalse(get_user_meta(get_current_user_id(), 'sitepulse_trackers_disabled_notice_dismissed', true));
    }

    /** @dataProvider profilerSwitches */
    public function test_profiler_switch_updates_runtime_state_and_persists_at_shutdown(string $input, bool $expected): void {
        $result = sitepulse_set_sp_profiler($this->request(array('sitepulse_profiler_enabled' => $input)))->get_data();
        $this->assertSame($expected, $result['sitepulse_profiler_enabled']);
        $this->assertSame($expected, Sitepulse_Profiler::$enabled);
        do_action('shutdown');
        $this->assertSame($expected, get_option('sitepulse_profiler_enabled'));
        $this->assertFalse(get_user_meta(get_current_user_id(), 'sitepulse_trackers_disabled_notice_dismissed', true));
    }

    public static function profilerSwitches(): array {
        return array('enable' => array('enabled', true), 'disable' => array('disabled', false));
    }

    public function test_settings_update_only_changes_supported_keys(): void {
        update_option('sitepulse_settings', array('external_api_enabled' => true, 'email_blocking_enabled' => false));
        $result = sitepulse_update_settings($this->request(array('email_blocking_enabled' => true, 'email_blocking_mode' => 'smtp_block', 'unknown' => 'value')))->get_data();
        $this->assertTrue($result['success']);
        $settings = get_option('sitepulse_settings');
        $this->assertTrue($settings['email_blocking_enabled']);
        $this->assertSame('smtp_block', $settings['email_blocking_mode']);
        $this->assertArrayNotHasKey('unknown', $settings);
    }

    public function test_empty_settings_request_reports_no_update(): void {
        $this->assertFalse(sitepulse_update_settings($this->request())->get_data()['success']);
    }

    public function test_completing_onboarding_persists_completion_and_clears_resume_step(): void {
        update_option('sitepulse_onboarding_current_step', 3);
        $this->assertTrue(sitepulse_complete_onboarding($this->request())->get_data()['success']);
        $this->assertTrue(get_option('sitepulse_onboarding_completed'));
        $this->assertFalse(get_option('sitepulse_onboarding_current_step'));
        $this->assertSame(array(), Sitepulse_Test_WP::$user_meta[1] ?? array());
    }

    /** @dataProvider onboardingInterfaces */
    public function test_onboarding_saves_the_chosen_interface($interface, $easy_mode, $view): void {
        sitepulse_complete_onboarding($this->request(array('interface' => $interface)));
        $this->assertSame($easy_mode, Sitepulse_Easy_Mode::is_enabled()); $this->assertSame($view, Sitepulse_Easy_Mode::get_dashboard_view());
        $this->assertSame($easy_mode ? '1' : '0', get_user_meta(1, 'sitepulse_easy_mode', true));
    }

    public static function onboardingInterfaces(): array {
        return array('simple' => array('simple', true, 'basic'), 'advanced' => array('advanced', false, 'developer'));
    }

    public function test_resetting_onboarding_clears_all_progress_and_current_users_notice(): void {
        update_option('sitepulse_onboarding_completed', true);
        update_option('sitepulse_onboarding_dismissed', true);
        update_option('sitepulse_onboarding_current_step', 3);
        update_user_meta(1, 'sitepulse_onboarding_notice_dismissed', true);
        update_user_meta(2, 'sitepulse_onboarding_notice_dismissed', true);
        $this->assertTrue(sitepulse_reset_onboarding($this->request())->get_data()['success']);
        $this->assertFalse(get_option('sitepulse_onboarding_completed'));
        $this->assertFalse(get_option('sitepulse_onboarding_dismissed'));
        $this->assertFalse(get_option('sitepulse_onboarding_current_step'));
        $this->assertSame('', get_user_meta(1, 'sitepulse_onboarding_notice_dismissed', true));
        $this->assertTrue(get_user_meta(2, 'sitepulse_onboarding_notice_dismissed', true));
    }

    /** @dataProvider noticeHandlers */
    public function test_notice_dismissal_is_scoped_to_current_user(string $handler, string $key): void {
        Sitepulse_Test_WP::$user_id = 7;
        $this->assertTrue($handler($this->request())->get_data()['success']);
        $this->assertTrue(get_user_meta(7, $key, true));
        $this->assertSame('', get_user_meta(8, $key, true));
    }

    /** @dataProvider noticeHandlers */
    public function test_notice_dismissal_requires_authenticated_user(string $handler, string $key): void {
        Sitepulse_Test_WP::$user_id = 0;
        $this->assertError($handler($this->request()), 'unauthenticated', 401);
        $this->assertSame(array(), Sitepulse_Test_WP::$user_meta);
    }

    public static function noticeHandlers(): array {
        return array('tracker notice' => array('sitepulse_set_trackers_disabled_notice', 'sitepulse_trackers_disabled_notice_dismissed'), 'onboarding notice' => array('sitepulse_dismiss_onboarding_notice', 'sitepulse_onboarding_notice_dismissed'));
    }

    public function test_clearing_http_events_removes_persisted_events(): void {
        update_option(SITEPULSE_CURL_API_KEY, array(array('url' => 'https://example.test')));
        $this->assertTrue(sitepulse_enable_clear_curl_api_events($this->request())->get_data()['success']);
        $this->assertSame(array(), get_option(SITEPULSE_CURL_API_KEY));
    }

    public function test_memory_info_returns_numeric_megabytes_and_usage_for_a_bounded_limit(): void {
        $original = ini_get('memory_limit');
        try {
            $this->assertNotFalse(ini_set('memory_limit', '1G'));
            $result = sitepulse_get_memory_info($this->request())->get_data();
            $this->assertTrue($result['success']);
            $this->assertGreaterThan(0, $result['memory']);
            $this->assertSame(1024.0, $result['limit']);
            $this->assertIsFloat($result['usage_percent']);
            $this->assertGreaterThan(0, $result['usage_percent']);
            $this->assertLessThanOrEqual(100, $result['usage_percent']);
            $this->assertStringContainsString('limit 1,024.00 MB', $result['formatted']);
        } finally {
            ini_set('memory_limit', $original);
        }
    }

    public function test_memory_info_reports_zero_limit_and_usage_when_memory_is_unlimited(): void {
        $original = ini_get('memory_limit');
        try {
            $this->assertNotFalse(ini_set('memory_limit', '-1'));
            $result = sitepulse_get_memory_info($this->request())->get_data();
            $this->assertTrue($result['success']);
            $this->assertGreaterThan(0, $result['memory']);
            $this->assertSame(0, $result['limit']);
            $this->assertSame(0, $result['usage_percent']);
            $this->assertStringContainsString('limit unlimited', $result['formatted']);
        } finally {
            ini_set('memory_limit', $original);
        }
    }

    public function test_profiler_stats_expose_hook_names_and_http_stats_count_events(): void {
        update_option(SITEPULSE_PROFILER_KEY, array('init' => array('elapsed' => 1.5), 'shutdown' => array('elapsed' => 0.2)));
        $result = sitepulse_get_profiler_stats($this->request())->get_data();
        $this->assertSame(array(array('hook' => 'init', 'data' => array('elapsed' => 1.5)), array('hook' => 'shutdown', 'data' => array('elapsed' => 0.2))), $result['data']);
        $this->assertSame(2, $result['count']);
        update_option(SITEPULSE_CURL_API_KEY, array(array('url' => 'https://api.example.test', 'elapsed' => 1.1)));
        $result = sitepulse_get_curl_stats($this->request())->get_data();
        $this->assertSame(1, $result['count']);
        $this->assertSame('https://api.example.test', $result['events'][0]['url']);
    }

    public function test_corrupt_stats_return_empty_collections(): void {
        update_option(SITEPULSE_PROFILER_KEY, 'corrupt');
        update_option(SITEPULSE_CURL_API_KEY, 'corrupt');
        $this->assertSame(array('success' => true, 'data' => array(), 'count' => 0), sitepulse_get_profiler_stats($this->request())->get_data());
        $this->assertSame(array('success' => true, 'events' => array(), 'count' => 0), sitepulse_get_curl_stats($this->request())->get_data());
    }

    /** @dataProvider vulnerabilityFormats */
    public function test_vulnerability_results_are_normalized_and_persisted(array $raw, array $expected): void {
        update_option('sitepulse_last_api_request', 1700000000);
        $this->queue(array('vulnerabilities' => $raw));
        $result = sitepulse_check_vulnerabilities_api($this->request())->get_data();
        $this->assertTrue($result['success']);
        $this->assertSame($expected, $result['data']['vulnerabilities']);
        $this->assertSame($expected, get_option('sitepulse_vulnerabilities_list'));
        $this->assertSame(current_time('mysql'), get_option('sitepulse_last_vulnerability_check'));
    }

    public static function vulnerabilityFormats(): array {
        return array(
            'core and plugin' => array(array('wordpress_core' => array(array('title' => 'XSS', 'severity' => 'high')), 'plugins' => array('sample' => array('name' => 'Sample', 'vulnerabilities' => array(array('title' => 'SQL injection', 'severity' => 'critical'))))), array(array('name' => 'WP Core: XSS', 'version' => 'high'), array('name' => 'Plugin (Sample): SQL injection', 'version' => 'critical'))),
            'single theme' => array(array('theme' => array('name' => 'Theme One', 'vulnerabilities' => array(array('title' => 'XSS', 'severity' => 'medium')))), array(array('name' => 'Theme (Theme One): XSS', 'version' => 'medium'))),
            'keyed themes' => array(array('theme' => array('theme-one' => array('vulnerabilities' => array(array('title' => 'XSS', 'severity' => 'low'))))), array(array('name' => 'Theme (theme-one): XSS', 'version' => 'low'))),
            'legacy theme' => array(array('theme' => array(array('title' => 'XSS', 'severity' => 'high'))), array(array('name' => 'Theme: XSS', 'version' => 'high'))),
            'legacy flat' => array(array(array('name' => 'Old finding', 'version' => '2.0')), array(array('name' => 'Old finding', 'version' => '2.0'))),
            'empty scan' => array(array(), array()),
        );
    }

    public function test_failed_vulnerability_scan_preserves_previous_findings(): void {
        update_option('sitepulse_last_api_request', 1700000000);
        update_option('sitepulse_vulnerabilities_list', array(array('name' => 'Previous', 'version' => 'high')));
        $this->queue(array('message' => 'Rate limited'), 429);
        $result = sitepulse_check_vulnerabilities_api($this->request())->get_data();
        $this->assertFalse($result['success']);
        $this->assertSame(array(array('name' => 'Previous', 'version' => 'high')), get_option('sitepulse_vulnerabilities_list'));
        $this->assertFalse(get_option('sitepulse_last_vulnerability_check'));
    }

    public function test_pending_ai_request_schedules_polling_and_forwards_force_new(): void {
        update_option('sitepulse_last_api_request', 1700000000);
        $this->queue(array('success' => true, 'status' => 'pending', 'ai_diagnostic_report_id' => 'report-7'));
        $result = sitepulse_request_ai_diagnostic_api($this->request(array('force_new' => true)))->get_data();
        $this->assertTrue($result['success']);
        $this->assertSame('pending', $result['data']['status']);
        $this->assertTrue(Sitepulse_AI_Diagnostic_Cron::is_scheduled());
        $payload = json_decode(Sitepulse_Test_WP::$http_requests[0]['args']['body'], true);
        $this->assertTrue($payload['force_new']);
    }

    public function test_completed_ai_status_stops_polling_and_serves_stored_report(): void {
        update_option('sitepulse_ai_diagnostic_status', 'completed');
        update_option('sitepulse_ai_diagnostic_report_id', 'report-7');
        update_option('sitepulse_ai_diagnostic_report', '{"summary":"Done"}');
        Sitepulse_AI_Diagnostic_Cron::schedule_status_check();
        $result = sitepulse_check_ai_diagnostic_status_api($this->request())->get_data();
        $this->assertSame(array('summary' => 'Done'), $result['data']['diagnostic_result']);
        $this->assertFalse(Sitepulse_AI_Diagnostic_Cron::is_scheduled());
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }
}

RestApiTest::captureRouteRegistration();

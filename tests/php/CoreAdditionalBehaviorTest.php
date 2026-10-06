<?php
/** Additional configuration, recovery, and compatibility paths exercised through real plugin methods. */
final class CoreAdditionalBehaviorTest extends Sitepulse_Test_Case {
    public function test_status_combines_error_counts_with_cron_health_without_changing_event_totals(): void {
        update_option('sitepulse_settings', array('email_blocking_enabled' => true, 'email_blocking_mode' => 'both'));
        update_option('sitepulse_error_log', array(array('type' => E_ERROR, 'message' => 'fatal', 'timestamp' => '2026-01-01 11:00:00')));
        Sitepulse_Test_WP::$cron = array(time() - 7200 => array('job' => array()));
        $status = Sitepulse_Settings::getInstance()->get_status_info();
        $this->assertSame('Both Blocked', $status['email_status']);
        $this->assertSame('Enabled', $status['cron_status']);
        $this->assertTrue($status['cron_failing']);
        $this->assertSame(1, $status['cron_events_count']);
        $this->assertSame(1, $status['error_log_count'] - $status['error_log_total_count']);
        $this->assertSame('wp_cron', $status['cron_failure_data']['source']);
    }
    public function test_direct_recovery_email_changes_are_sanitized_and_persisted_with_other_settings(): void {
        $settings = new Sitepulse_Settings();
        $settings->set_cron_disabled(true);
        $this->assertTrue($settings->set_recovery_mode_email_addresses(" <b>admin@example.test</b>\n second@example.test "));
        $this->assertSame('admin@example.test second@example.test', $settings->get_recovery_mode_email_addresses());
        $this->assertTrue($settings->get_all_settings()['cron_disabled']);
        $settings->maybe_apply_settings();
        $settings->update_settings(array('email_blocking_enabled' => true));
        $this->assertFalse(apply_filters('wp_mail', array('to' => 'somebody@example.test')));
        $settings->maybe_apply_settings();
        $this->assertFalse(apply_filters('cron_request', array('url' => 'cron')));
    }
    public function test_onboarding_registers_hidden_admin_page_and_scopes_titles_and_body_classes(): void {
        $onboarding = new Sitepulse_Onboarding();
        $onboarding->register_onboarding_page();
        $menu = Sitepulse_Test_WP::$menus['wpsp_sitepulse_onboarding'];
        $this->assertSame('', $menu['parent_slug']);
        $this->assertSame('manage_options', $menu['capability']);
        $this->assertSame(array($onboarding, 'render_onboarding_page'), $menu['callback']);
        $this->assertSame('body', $onboarding->add_onboarding_body_class('body'));
        $this->assertSame('Existing', $onboarding->set_onboarding_title('Existing', ''));
        Sitepulse_Test_WP::$screen = (object) array('id' => 'admin_page_wpsp_sitepulse_onboarding');
        $this->assertStringContainsString('sitepulse_css_class', $onboarding->add_onboarding_body_class('body'));
        $this->assertSame('SitePulse OnboardingExisting', $onboarding->set_onboarding_title('Existing', ''));
        $this->assertSame('SitePulse Onboarding ‹ Demo Shop', $onboarding->set_onboarding_title('SitePulse Onboarding ‹ Demo Shop', 'SitePulse Onboarding'));
        $onboarding->set_page_title_early();
        $this->assertSame('SitePulse Onboarding', $GLOBALS['title']);
        $this->assertSame('wpsp_sitepulse_onboarding', $GLOBALS['submenu_file']);
        $this->assertSame('', $GLOBALS['parent_file']);
    }
    public function test_analytics_corrupt_once_index_is_recovered_and_nonscalar_context_is_omitted(): void {
        update_option('sitepulse_product_events_once', 'broken');
        $this->assertTrue(Sitepulse_Product_Analytics::track_once('plugin_activated', 'invalid-context'));
        $this->assertSame(array(), get_option('sitepulse_product_events')[0]['context']);
        $this->assertArrayHasKey('plugin_activated', get_option('sitepulse_product_events_once'));
        update_option('sitepulse_product_events', 'broken');
        $this->assertSame(array('event_count' => 0, 'counts' => array()), Sitepulse_Product_Analytics::get_summary());
    }
    public function test_analytics_limits_context_to_twelve_fields_and_supports_empty_values(): void {
        $context = array('empty' => null);
        for ($index = 1; $index < 15; $index++) { $context['key_' . $index] = $index; }
        Sitepulse_Product_Analytics::track('dashboard_viewed', $context);
        $stored = get_option('sitepulse_product_events')[0]['context'];
        $this->assertSame('', $stored['empty']);
        $this->assertCount(12, $stored);
        $this->assertSame(11, $stored['key_11']);
        $this->assertArrayNotHasKey('key_12', $stored);
    }
    public function test_easy_mode_without_screen_defaults_to_dashboard(): void {
        $_GET['sp_view'] = 'security';
        $this->assertSame('home', Sitepulse_Easy_Mode::get_current_view());
    }
    public function test_easy_mode_error_log_clear_erases_persisted_errors(): void {
        $_POST['nonce'] = 'nonce-wp_rest';
        update_option('sitepulse_error_log', array(array('message' => 'error')));
        $response = $this->ajaxResponse(array('Sitepulse_Easy_Mode', 'ajax_clear_error_log'));
        $this->assertTrue($response->success);
        $this->assertFalse(get_option('sitepulse_error_log'));
    }
    public function test_plugin_profiler_disabled_initialization_and_helpers_preserve_existing_measurements(): void {
        update_option('sitepulse_plugin_profiler_enabled', false);
        update_option('sitepulse_plugin_profiler_stats', array('old' => array('avg_time' => 2000)));
        $GLOBALS['wp_filesystem'] = null;
        Sitepulse_Plugin_Profiler::init();
        Sitepulse_Plugin_Profiler::capture_baseline();
        Sitepulse_Plugin_Profiler::setup_plugin_tracking();
        $this->assertFalse(has_action('plugins_loaded', array('Sitepulse_Plugin_Profiler', 'track_plugins')));
        $this->assertSame(array('old' => array('avg_time' => 2000)), Sitepulse_Plugin_Profiler::get_stats());
        $this->assertSame('2.00 s', Sitepulse_Plugin_Profiler::format_time(2000));
        $this->assertSame('1.00 MB', Sitepulse_Plugin_Profiler::format_memory(1048576));
    }
    public function test_plugin_profiler_discards_unattributable_rows_while_retaining_unmeasured_plugins(): void {
        Sitepulse_Test_WP::$plugins = array('one/main.php' => array('Name' => 'One'));
        update_option('active_plugins', array('one/main.php'));
        $this->setStaticProperty('Sitepulse_Profiler', 'stats', array(
            array('hook' => 'plugin_bootstrap', 'fileline' => 'wp-includes/core.php', 'calls' => 3),
            array('hook' => 'plugin_bootstrap', 'fileline' => 'wp-content/plugins/', 'calls' => 1),
            array('hook' => 'plugin_bootstrap', 'fileline' => '', 'calls' => 2),
        ));
        Sitepulse_Plugin_Profiler::init();
        Sitepulse_Plugin_Profiler::enable_data_collection();
        Sitepulse_Plugin_Profiler::track_plugins();
        $this->assertSame(0, Sitepulse_Plugin_Profiler::get_plugin_stats('one/main.php')['calls']);
        $this->assertSame(0.0, Sitepulse_Plugin_Profiler::get_total_load_time());
    }
    public function test_http_event_labels_bound_the_display_width_without_changing_short_values(): void {
        $this->assertSame('abc', Sitepulse_CurLoader::truncate('abc', 3));
        $this->assertSame('abc…', Sitepulse_CurLoader::truncate('abcdef', 4));
    }
    public function test_setup_lifecycle_noops_preserve_installed_options(): void {
        update_option('wpsp_version', '1.4.5');
        $setup = new Sitepulse_Setup();
        $setup->deactivate(); $setup->uninstall();
        $this->assertSame('1.4.5', $setup->getInstalledVersion());
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_corrupt_plugin_inventory_is_normalized_to_an_empty_list(): void {
        update_option('active_plugins', 'broken');
        $this->assertSame(array(), Sitepulse_Utils::get_active_plugins());
        $this->assertSame(0, Sitepulse_Utils::get_active_plugins_count());
        $this->assertSame('https://example.test/path', Sitepulse_Utils::redact_url_query('https://example.test/path'));
    }
}

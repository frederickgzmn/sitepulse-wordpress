<?php
require_once __DIR__ . '/Support/LocalFilesystem.php';
require_once __DIR__ . '/Support/Database.php';
require_once __DIR__ . '/Support/PresentationWordPress.php';
require_once __DIR__ . '/Support/TemplateData.php';

/**
 * Real templates/controllers render in fresh requests because they use require_once.
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class TemplateRenderingTest extends Sitepulse_Test_Case {
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

    private function render(callable $render): string {
        ob_start();
        try { $render(); return ob_get_contents(); }
        finally { ob_end_clean(); }
    }

    public static function screens(): array {
        $rows = array();
        foreach (array('home', 'performance', 'security', 'system', 'resource-load', 'insights', 'api-monitor', 'settings') as $view) {
            $rows['easy ' . $view] = array(true, $view, 'render_dashboard_section');
        }
        foreach (array('render_dashboard_section', 'render_resource_load', 'render_curl_api', 'render_page_sitepulse_settings', 'render_page_analysis') as $method) {
            $rows['classic ' . $method] = array(false, 'home', $method);
        }
        $rows['easy page analysis'] = array(true, 'home', 'render_page_analysis');
        return $rows;
    }

    /** @dataProvider screens */
    public function test_real_admin_views_render_empty_state_without_warnings($easy, $view, $method): void {
        update_user_meta(1, 'sitepulse_easy_mode', $easy);
        $_GET = array('page' => 'wpsp_sitepulse', 'sp_view' => $view);
        $html = $this->render(array(new Sitepulse_Backend(new Sitepulse_Plugin()), $method));
        $this->assertStringContainsString('SitePulse', $html);
        
        $this->assertNotEmpty(trim(strip_tags($html)));
        if ($easy) { $this->assertStringContainsString('sp-easy-shell', $html); }
        else { $this->assertStringContainsString('sp-dashboard', $html); }
    }

    public function test_onboarding_render_outputs_wizard_and_configuration_controls(): void {
        $html = $this->render(array(new Sitepulse_Onboarding(), 'render_onboarding_page'));
        $this->assertStringContainsString('sitepulse-onboarding', $html);
        $this->assertStringContainsString('data-view="advanced"', $html); $this->assertStringContainsString('class="onboarding-checkup"', $html);
        $this->assertStringContainsString('Performance Monitor', $html); $this->assertStringNotContainsString('LoadSentinel', $html);
    }

    public static function focusedViews(): array {
        return array(array(false, 'render_resource_load'), array(true, 'render_resource_load'), array(false, 'render_curl_api'), array(true, 'render_curl_api'));
    }
    /** @dataProvider focusedViews */
    public function test_site_wide_lists_show_collected_records_and_point_to_page_analysis($easy, $method): void {
        $data = sitepulse_test_seed_template_options(); update_option('sitepulse_curl_api_events', $data['curl_events']);
        update_user_meta(1, 'sitepulse_easy_mode', $easy);
        $html = $this->render(array(new Sitepulse_Backend(new Sitepulse_Plugin()), $method));
        $this->assertStringContainsString('href="https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_page_analysis"', $html);
        $this->assertStringContainsString('sample', strtolower($html)); $this->assertStringNotContainsString('Single Mode', $html);
        $this->assertSame(array($method === 'render_curl_api' ? 'api-monitor' : 'resource-load'), get_user_meta(1, 'sitepulse_getting_started_visited', true));
    }

    public static function settingsSubmissions(): array { return array(array(false, false), array(false, true), array(true, true)); }
    /** @dataProvider settingsSubmissions */
    public function test_settings_controller_reports_form_submission_outcome($easy, $valid): void {
        update_user_meta(1, 'sitepulse_easy_mode', $easy);
        $_POST = array('submit' => 'Save', 'sitepulse_settings_nonce' => $valid ? wp_create_nonce('sitepulse_settings_action') : 'invalid', 'sitepulse_external_api_enabled' => 'on');
        $html = $this->render(array(new Sitepulse_Backend(new Sitepulse_Plugin()), 'render_page_sitepulse_settings'));
        $this->assertStringContainsString($valid ? 'success' : 'Failed to update settings', $html);
    }

    public function test_invalid_view_falls_back_to_home_and_settings_api_can_render_registered_dashboard(): void {
        $html = $this->render(static function () { Sitepulse_Easy_Mode::render('nonexistent'); });
        $this->assertStringContainsString('sp-easy-shell', $html);
        update_user_meta(1, 'sitepulse_easy_mode', '0');
        $backend = new Sitepulse_Backend(new Sitepulse_Plugin());
        $backend->register_settings();
        $html = $this->render(array($backend, 'render_page_sitepulse'));
        $this->assertStringContainsString('sp-dashboard', $html);
    }

    public static function pageSpeedStrategies(): array { return array(array('desktop', .8), array('mobile', 75)); }
    /** @dataProvider pageSpeedStrategies */
    public function test_dashboard_reads_stored_pagespeed_strategy_and_score($strategy, $score): void {
        update_user_meta(1, 'sitepulse_easy_mode', true);
        $_GET = array('page' => 'wpsp_sitepulse', 'sp_view' => 'insights');
        update_option('sitepulse_pagespeed_report', array('has_data' => true, $strategy => array('scores' => array('performance' => $score))));
        $html = $this->render(array(new Sitepulse_Backend(new Sitepulse_Plugin()), 'render_dashboard_section'));
        $this->assertStringContainsString('data-score="' . ($score <= 1 ? round($score * 100) : $score) . '"', $html);
    }
}

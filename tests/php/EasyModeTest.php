<?php
final class EasyModeTest extends Sitepulse_Test_Case {
    public function test_preferences_are_per_user_and_anonymous_users_keep_defaults(): void {
        update_user_meta(1, 'sitepulse_easy_mode', '0'); update_user_meta(1, 'sitepulse_easy_mode_theme', 'light');
        $this->assertFalse(Sitepulse_Easy_Mode::is_enabled()); $this->assertSame('light', Sitepulse_Easy_Mode::get_theme());
        Sitepulse_Test_WP::$user_id = 2; $this->assertTrue(Sitepulse_Easy_Mode::is_enabled()); $this->assertSame('dark', Sitepulse_Easy_Mode::get_theme());
        update_user_meta(2, 'sitepulse_easy_mode_theme', 'invalid'); $this->assertSame('dark', Sitepulse_Easy_Mode::get_theme());
        Sitepulse_Test_WP::$user_id = 0; $this->assertFalse(Sitepulse_Easy_Mode::is_enabled()); $this->assertSame('dark', Sitepulse_Easy_Mode::get_theme());
    }
    public function test_easy_mode_is_the_default_until_someone_chooses_the_advanced_view(): void {
        $this->assertTrue(Sitepulse_Easy_Mode::is_enabled());
        update_user_meta(1, 'sitepulse_easy_mode', ''); $this->assertFalse(Sitepulse_Easy_Mode::is_enabled(), 'An explicit classic choice stored by earlier versions is kept.');
        Sitepulse_Easy_Mode::set_enabled(true); $this->assertSame('1', get_user_meta(1, 'sitepulse_easy_mode', true)); $this->assertTrue(Sitepulse_Easy_Mode::is_enabled());
    }
    public function test_classic_dashboard_view_defaults_to_developer_and_accepts_only_known_views(): void {
        $this->assertSame('developer', Sitepulse_Easy_Mode::get_dashboard_view());
        $this->assertTrue(Sitepulse_Easy_Mode::set_dashboard_view('basic')); $this->assertSame('basic', Sitepulse_Easy_Mode::get_dashboard_view());
        $this->assertFalse(Sitepulse_Easy_Mode::set_dashboard_view('expert')); $this->assertSame('basic', Sitepulse_Easy_Mode::get_dashboard_view());
        update_user_meta(1, 'sitepulse_dashboard_view', 'tampered'); $this->assertSame('developer', Sitepulse_Easy_Mode::get_dashboard_view());
    }
    public function test_ajax_toggle_persists_both_directions_for_current_user(): void {
        $_POST['nonce'] = 'nonce-wp_rest';
        $response = $this->ajaxResponse(array('Sitepulse_Easy_Mode', 'ajax_toggle_mode'));
        $this->assertTrue($response->success); $this->assertFalse($response->data['enabled']); $this->assertSame('0', get_user_meta(1, 'sitepulse_easy_mode', true)); $this->assertSame('Advanced view enabled. Refreshing…', $response->data['message']);
        $response = $this->ajaxResponse(array('Sitepulse_Easy_Mode', 'ajax_toggle_mode'));
        $this->assertTrue($response->data['enabled']); $this->assertSame('1', get_user_meta(1, 'sitepulse_easy_mode', true)); $this->assertSame('Simple view enabled. Refreshing…', $response->data['message']);
    }
    /** @dataProvider themes */
    public function test_ajax_theme_allows_known_values_and_defaults_invalid_input($input, $expected): void {
        $_POST = array('nonce' => 'nonce-wp_rest', 'theme' => $input);
        $response = $this->ajaxResponse(array('Sitepulse_Easy_Mode', 'ajax_toggle_theme'));
        $this->assertTrue($response->success); $this->assertSame($expected, $response->data['theme']); $this->assertSame($expected, get_user_meta(1, 'sitepulse_easy_mode_theme', true));
    }
    public static function themes(): array { return array(array('light', 'light'), array('dark', 'dark'), array('<b>light</b>', 'light'), array('unexpected', 'dark')); }
    /** @dataProvider mutatingHandlers */
    public function test_ajax_preferences_reject_users_without_admin_permission($handler): void {
        $_POST['nonce'] = 'nonce-wp_rest'; Sitepulse_Test_WP::$capabilities['manage_options'] = false;
        $response = $this->ajaxResponse(array('Sitepulse_Easy_Mode', $handler)); $this->assertFalse($response->success); $this->assertSame(array(), Sitepulse_Test_WP::$user_meta);
    }
    public static function mutatingHandlers(): array { return array(array('ajax_toggle_mode'), array('ajax_toggle_theme'), array('ajax_clear_error_log'), array('ajax_enable_savequeries')); }
    public function test_invalid_nonce_prevents_preference_writes(): void {
        $_POST['nonce'] = 'invalid';
        try { Sitepulse_Easy_Mode::ajax_toggle_mode(); $this->fail('Expected nonce rejection'); } catch (Sitepulse_Test_Die $error) { $this->assertSame(array(), Sitepulse_Test_WP::$user_meta); }
    }
    /** @dataProvider views */
    public function test_routing_prefers_valid_subviews_then_legacy_pages_and_defaults_unknown_input($page, $view, $expected): void {
        Sitepulse_Test_WP::$screen = (object) array('id' => 'toplevel_page_wpsp_sitepulse'); $_GET = array('page' => $page, 'sp_view' => $view);
        $this->assertSame($expected, Sitepulse_Easy_Mode::get_current_view());
    }
    public static function views(): array { return array(array('wpsp_sitepulse', 'security', 'security'), array('wpsp_curl_api', 'performance', 'performance'), array('wpsp_resource_load', '', 'resource-load'), array('wpsp_curl_api', '', 'api-monitor'), array('wpsp_settings', '', 'settings'), array('wpsp_sitepulse', '../../private', 'home')); }
    public function test_assets_expose_current_user_theme_and_nonce_with_jquery_dependency(): void {
        update_user_meta(1, 'sitepulse_easy_mode_theme', 'light'); Sitepulse_Easy_Mode::enqueue_assets();
        $config = Sitepulse_Test_WP::$localized['sitepulse-easy-mode-script']['SitePulseEasy'];
        $this->assertSame('light', $config['theme']); $this->assertSame('nonce-wp_rest', $config['nonce']); $this->assertSame('https://example.test/wp-admin/admin-ajax.php', $config['ajax_url']);
        $this->assertContains('jquery', Sitepulse_Test_WP::$scripts['sitepulse-easy-mode-script']['deps']);
        $this->assertSame('https://example.test/wp-json/', Sitepulse_Test_WP::$localized['sitepulse-backend-script']['SitePulse']['rest_url']);
    }
    public function test_navigation_urls_resolve_to_the_declared_views(): void {
        Sitepulse_Test_WP::$screen = (object) array('id' => 'toplevel_page_wpsp_sitepulse');
        foreach (Sitepulse_Easy_Mode::get_nav_items() as $item) { parse_str(parse_url($item['url'], PHP_URL_QUERY), $_GET); $this->assertSame($item['slug'], Sitepulse_Easy_Mode::get_current_view()); }
    }
}

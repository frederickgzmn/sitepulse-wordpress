<?php
require_once __DIR__ . '/Support/SubprocessCoverage.php';

final class MonitoringTest extends Sitepulse_Test_Case {
    public function test_pause_switches_every_page_load_tracker_off_and_resume_restores_them(): void {
        update_option('sitepulse_profiler_enabled', true); update_option('sitepulse_curl_api_enabled', true); update_option('sitepulse_plugins_profiler_enabled', true);
        $this->assertFalse(Sitepulse_Monitoring::is_paused());
        Sitepulse_Monitoring::pause();
        $this->assertTrue(Sitepulse_Monitoring::is_paused());
        foreach (array('sitepulse_profiler_enabled', 'sitepulse_curl_api_enabled', 'sitepulse_plugins_profiler_enabled') as $option) { $this->assertFalse(get_option($option), $option); }
        $this->assertSame(time(), get_option('sitepulse_monitoring_paused'));
        update_user_meta(1, 'sitepulse_trackers_disabled_notice_dismissed', true); update_user_meta(2, 'sitepulse_trackers_disabled_notice_dismissed', true);
        Sitepulse_Monitoring::resume();
        $this->assertFalse(Sitepulse_Monitoring::is_paused()); $this->assertFalse(get_option('sitepulse_monitoring_paused'));
        foreach (array('sitepulse_profiler_enabled', 'sitepulse_curl_api_enabled', 'sitepulse_plugins_profiler_enabled') as $option) { $this->assertTrue(get_option($option), $option); }
        $this->assertSame('', get_user_meta(1, 'sitepulse_trackers_disabled_notice_dismissed', true)); $this->assertSame('', get_user_meta(2, 'sitepulse_trackers_disabled_notice_dismissed', true));
    }
    public function test_turning_a_tracker_back_on_by_hand_ends_the_pause(): void {
        Sitepulse_Monitoring::pause(); update_option('sitepulse_curl_api_enabled', true);
        $this->assertFalse(Sitepulse_Monitoring::is_paused());
        update_option('sitepulse_curl_api_enabled', false); update_option('sitepulse_profiler_enabled', true);
        $this->assertFalse(Sitepulse_Monitoring::is_paused());
    }
    public function test_toggle_is_registered_as_a_nonce_protected_admin_post_action(): void {
        Sitepulse_Monitoring::init();
        $this->assertNotFalse(has_action('admin_post_sitepulse_toggle_monitoring', array('Sitepulse_Monitoring', 'handle_toggle')));
        $this->assertSame('https://example.test/wp-admin/admin-post.php?action=sitepulse_toggle_monitoring&_wp_http_referer=%2F&_wpnonce=nonce-sitepulse_toggle_monitoring', Sitepulse_Monitoring::toggle_url());
    }
    public function test_toggle_pauses_then_resumes_and_returns_to_the_page_it_came_from(): void {
        $_REQUEST['_wpnonce'] = 'nonce-sitepulse_toggle_monitoring'; Sitepulse_Test_WP::$referer = 'https://example.test/shop/';
        try { Sitepulse_Monitoring::handle_toggle(); $this->fail('Expected a redirect.'); } catch (Sitepulse_Test_Redirect $redirect) { $this->assertSame('https://example.test/shop/', $redirect->getMessage()); }
        $this->assertTrue(Sitepulse_Monitoring::is_paused());
        Sitepulse_Test_WP::$referer = false;
        try { Sitepulse_Monitoring::handle_toggle(); $this->fail('Expected a redirect.'); } catch (Sitepulse_Test_Redirect $redirect) { $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse', $redirect->getMessage()); }
        $this->assertFalse(Sitepulse_Monitoring::is_paused());
    }
    public function test_toggle_link_carries_the_page_to_return_to_even_without_a_referer_header(): void {
        $_SERVER['REQUEST_URI'] = '/product/blue-shirt/?color=red';
        $this->assertSame('https://example.test/wp-admin/admin-post.php?action=sitepulse_toggle_monitoring&_wp_http_referer=%2Fproduct%2Fblue-shirt%2F%3Fcolor%3Dred&_wpnonce=nonce-sitepulse_toggle_monitoring', Sitepulse_Monitoring::toggle_url());
        $_REQUEST = array('_wpnonce' => 'nonce-sitepulse_toggle_monitoring', '_wp_http_referer' => '/product/blue-shirt/?color=red');
        try { Sitepulse_Monitoring::handle_toggle(); $this->fail('Expected a redirect.'); } catch (Sitepulse_Test_Redirect $redirect) { $this->assertSame('/product/blue-shirt/?color=red', $redirect->getMessage()); }
        unset($_SERVER['REQUEST_URI']);
        $this->assertSame('https://example.test/wp-admin/admin-post.php?action=sitepulse_toggle_monitoring&_wpnonce=nonce-sitepulse_toggle_monitoring', Sitepulse_Monitoring::toggle_url());
    }
    public function test_toggle_rejects_users_without_permission_and_forged_links(): void {
        Sitepulse_Test_WP::$capabilities['manage_options'] = false; $_REQUEST['_wpnonce'] = 'nonce-sitepulse_toggle_monitoring';
        try { Sitepulse_Monitoring::handle_toggle(); $this->fail('Expected wp_die.'); } catch (Sitepulse_Test_Die $die) { $this->assertStringContainsString('sufficient permissions', $die->getMessage()); }
        Sitepulse_Test_WP::$capabilities['manage_options'] = true; $_REQUEST['_wpnonce'] = 'forged';
        try { Sitepulse_Monitoring::handle_toggle(); $this->fail('Expected wp_die.'); } catch (Sitepulse_Test_Die $die) { $this->assertStringContainsString('expired', $die->getMessage()); }
        $this->assertFalse(Sitepulse_Monitoring::is_paused());
    }
    public function test_toggle_stops_the_request_after_redirecting(): void {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/core-variants.php', array('mode' => 'monitoring_toggle_exit'));
        $this->assertSame(0, $result['status'], $result['error']);
        $this->assertSame(array('redirects' => array(array('url' => 'https://example.test/wp-admin/admin.php?page=wpsp_sitepulse', 'status' => 302)), 'paused' => true), json_decode($result['output'], true));
    }
}

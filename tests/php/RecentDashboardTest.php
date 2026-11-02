<?php
require_once __DIR__ . '/Support/LocalFilesystem.php';
require_once __DIR__ . '/Support/Database.php';
require_once __DIR__ . '/Support/PresentationWordPress.php';
require_once __DIR__ . '/Support/RecentActivity.php';

/**
 * A site monitored for weeks: screens flag what slows pages down now, not what was slow before.
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class RecentDashboardTest extends Sitepulse_Test_Case {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['wp_filesystem'] = new Sitepulse_Test_Local_Filesystem();
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
        $GLOBALS['timestart'] = null;
        Sitepulse_Test_WP::$screen = (object) array('id' => 'toplevel_page_wpsp_sitepulse');
        update_option('admin_email', 'admin@example.test');
        update_option('date_format', 'Y-m-d'); update_option('time_format', 'H:i');
        Sitepulse_Easy_Mode::init();
        update_option('sitepulse_profiler_stats', array(
            // Slow for weeks until it was fixed three days ago: a huge lifetime total and nothing since.
            'fixed' => sitepulse_test_recent_stat(array('key' => 'fixed', 'source' => 'fixed-gallery', 'calls' => 40000, 'total' => 9000.0, 'total_ms' => 9000000.0), 2500.0, 3, 3 * DAY_IN_SECONDS),
            // Runs on every page and has added up to minutes, but costs each page load 3 ms.
            'busy' => sitepulse_test_recent_stat(array('key' => 'busy', 'source' => 'busy-cache', 'calls' => 90000, 'total' => 270.0, 'total_ms' => 270000.0), 3.0, 900),
            // Slows down pages today.
            'slow' => sitepulse_test_recent_stat(array('key' => 'slow', 'source' => 'slow-shop', 'hook' => 'wp_head', 'fileline' => 'wp-content/plugins/slow-shop/front.php:40'), 1300.0, 12),
        ));
        update_option('sitepulse_curl_api_events', array(
            sitepulse_test_http_event(array('origin' => 'Plugin: Old Gateway', 'url' => 'https://old-gateway.test/charge', 'elapsed' => 9.0), 5 * DAY_IN_SECONDS),
            sitepulse_test_http_event(array('origin' => 'Plugin: Live Rates', 'url' => 'https://rates.test/v2', 'elapsed' => 2.0)),
        ));
    }

    private function render(bool $easy, string $method, string $view = 'home'): string {
        update_user_meta(1, 'sitepulse_easy_mode', $easy ? '1' : '0');
        $_GET = array('page' => 'wpsp_sitepulse', 'sp_view' => $view);
        ob_start();
        try { (new Sitepulse_Backend(new Sitepulse_Plugin()))->$method(); return ob_get_contents(); }
        finally { ob_end_clean(); }
    }

    public function test_classic_dashboard_flags_only_code_that_is_slow_in_the_last_24_hours(): void {
        $html = $this->render(false, 'render_dashboard_section');
        $flagged = strstr(strstr($html, 'Slow items in the last 24 hours:'), '</ul>', true);
        $this->assertStringContainsString('Slow shop', $flagged); $this->assertStringNotContainsString('Busy cache', $flagged);
        $this->assertStringContainsString('1 Critical Issue Found', $html);
        $this->assertStringContainsString('1,300 ms', $html); $this->assertStringContainsString('own time per page load, last 24 hours', $html);
        $this->assertStringContainsString('Live Rates', $html);
        foreach (array('Fixed gallery', 'Old Gateway', 'old-gateway.test') as $retired) { $this->assertStringNotContainsString($retired, $html); }
    }

    public function test_simple_dashboard_speed_warnings_name_only_current_slowdowns(): void {
        $html = $this->render(true, 'render_dashboard_section');
        $this->assertStringContainsString('Plugins and themes that slowed down page loads in the last 24 hours', $html);
        $this->assertStringContainsString('Slow shop', $html);
        foreach (array('Fixed gallery', 'Busy cache') as $not_slow) { $this->assertStringNotContainsString($not_slow, $html); }
    }

    /** @dataProvider activityScreens */
    public function test_plugin_activity_ranks_recent_code_by_its_own_time_per_page_load($easy, $slow, $busy, $retired): void {
        $html = $this->render($easy, 'render_resource_load');
        $this->assertGreaterThan(0, strpos($html, $slow)); $this->assertGreaterThan(strpos($html, $slow), strpos($html, $busy));
        $this->assertStringNotContainsString($retired, $html);
    }

    public static function activityScreens(): array {
        return array('classic' => array(false, 'Slow shop', 'Busy cache', 'Fixed gallery'), 'simple' => array(true, '>slow-shop<', '>busy-cache<', 'fixed-gallery'));
    }

    public function test_external_requests_screen_keeps_the_full_log(): void {
        $html = $this->render(false, 'render_curl_api');
        $this->assertStringContainsString('Old Gateway', $html); $this->assertStringContainsString('Live Rates', $html);
    }
}

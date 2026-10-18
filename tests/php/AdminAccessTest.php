<?php
require_once __DIR__ . '/Support/RemoteManagement.php';

/**
 * The site's own server checks that it can reach wp-admin: /wp-admin/ and the login page,
 * loaded the way WordPress checks an edited plugin for fatal errors.
 */
final class AdminAccessTest extends Sitepulse_Test_Case {
    private const ADMIN = 'https://example.test/wp-admin/';
    private const LOGIN = 'https://example.test/wp-login.php';
    private const TIMED_OUT = 'cURL error 28: Operation timed out after 10001 milliseconds with 0 bytes received';

    protected function setUp(): void {
        parent::setUp();
        // The site's own pages answer WordPress's fatal-error check; wp-admin sends a visitor to the login page.
        Sitepulse_Test_Upgrades::reset();
        Sitepulse_Test_Upgrades::$pages[self::ADMIN] = array('status' => 302);
    }

    protected function tearDown(): void { Sitepulse_Test_Upgrades::reset(); Sitepulse_Test_WP::$http_responder = null; parent::tearDown(); }

    /**
     * The site's own pages, by URL: how WordPress's check answers (status, fatal), or a transport error message.
     *
     * @param array $pages URL => page state or error message.
     */
    private function site(array $pages): void {
        $errors = array();
        foreach ($pages as $url => $page) {
            if (is_string($page)) { $errors[$url] = $page; } else { Sitepulse_Test_Upgrades::$pages[$url] = $page; }
        }
        Sitepulse_Test_WP::$http_responder = static function ($url, $args) use ($errors) {
            $page = strtok($url, '?');
            return isset($errors[$page]) ? new WP_Error('http_request_failed', $errors[$page]) : sitepulse_test_site_pages($url, $args);
        };
    }

    /** Pages the check requested, in order. */
    private function requested(): array {
        return array_map(static function ($request) { return strtok($request['url'], '?'); }, Sitepulse_Test_WP::$http_requests);
    }

    /** A stored result, checked some seconds ago. */
    private static function result(bool $reachable, int $ago, array $fields = array()): array {
        return array_merge(array('reachable' => $reachable, 'status' => $reachable ? 302 : 503, 'login_status' => 200, 'ms' => 184, 'error' => $reachable ? null : 'http_503', 'checked_at' => gmdate('Y-m-d\TH:i:s\Z', time() - $ago)), $fields);
    }

    private function store(array $last, ?array $previous = null): void {
        update_option(SITEPULSE_ADMIN_ACCESS_OPTION, array('last' => $last, 'previous' => $previous), false);
    }

    private function render(string $context = 'advanced'): string {
        ob_start();
        try { Sitepulse_Admin_Access::render($context); return ob_get_contents(); }
        finally { ob_end_clean(); }
    }

    public function test_init_registers_the_hourly_check_and_the_check_now_button(): void {
        Sitepulse_Admin_Access::init();
        $this->assertNotFalse(has_action('sitepulse_admin_access_check', array('Sitepulse_Admin_Access', 'check')));
        $this->assertNotFalse(has_action('init', array('Sitepulse_Admin_Access', 'ensure_schedule')));
        $this->assertNotFalse(has_action('wp_ajax_sitepulse_admin_access_check', array('Sitepulse_Admin_Access', 'ajax_check')));
    }

    public function test_the_hourly_check_is_scheduled_once_and_cleared(): void {
        Sitepulse_Admin_Access::ensure_schedule();
        Sitepulse_Admin_Access::ensure_schedule();
        $this->assertCount(1, Sitepulse_Test_WP::$scheduled);
        $this->assertSame('hourly', Sitepulse_Test_WP::$scheduled[0]->schedule);
        $this->assertEqualsWithDelta(time(), wp_next_scheduled('sitepulse_admin_access_check'), 2, 'The first check runs at the next cron run.');

        Sitepulse_Admin_Access::unschedule();
        $this->assertFalse(wp_next_scheduled('sitepulse_admin_access_check'));
    }

    public function test_a_working_wp_admin_is_reachable_and_the_result_is_stored(): void {
        $lock = null;
        Sitepulse_Test_WP::$http_responder = static function ($url, $args) use (&$lock) {
            $lock = array(get_transient('sitepulse_admin_access_lock'), Sitepulse_Test_WP::$transient_expirations['sitepulse_admin_access_lock']);
            return sitepulse_test_site_pages($url, $args);
        };

        $result = Sitepulse_Admin_Access::check();

        $this->assertSame(array(self::ADMIN, self::LOGIN), $this->requested());
        $this->assertSame(array('reachable', 'status', 'login_status', 'ms', 'error', 'checked_at'), array_keys($result), 'No message without an error.');
        $this->assertSame(array(true, 302, 200, null), array($result['reachable'], $result['status'], $result['login_status'], $result['error']));
        $this->assertIsInt($result['ms']); $this->assertGreaterThanOrEqual(0, $result['ms']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ$/', $result['checked_at']);
        $this->assertEqualsWithDelta(time(), strtotime($result['checked_at']), 2);
        foreach (Sitepulse_Test_WP::$http_requests as $request) {
            $this->assertStringContainsString('wp_scrape_key=', $request['url']);
            $this->assertSame(array(10, 0), array($request['args']['timeout'], $request['args']['redirection']));
        }
        $this->assertSame(array(1, 30), $lock, 'A short lock is held while the pages load.');
        $this->assertSame(array(), Sitepulse_Test_WP::$transients, 'The lock and the error-check keys are gone afterwards.');
        $this->assertSame($result, Sitepulse_Admin_Access::last());
        $this->assertSame(array('last' => $result, 'previous' => null), get_option('sitepulse_admin_access'));
        $this->assertFalse(Sitepulse_Test_WP::$autoload['sitepulse_admin_access'], 'Pages that do not show it never load it.');
    }

    /** @dataProvider outcomes */
    public function test_the_result_says_why_wp_admin_cannot_be_reached($admin, $login, array $expected, $message): void {
        $this->site(array(self::ADMIN => $admin, self::LOGIN => $login));

        $result = Sitepulse_Admin_Access::check();

        $this->assertSame($expected, array($result['reachable'], $result['status'], $result['login_status'], $result['error']));
        $this->assertSame($message, $result['message'] ?? null);
        $this->assertSame(array(self::ADMIN, self::LOGIN), $this->requested(), 'Both pages are requested.');
    }

    public static function outcomes(): array {
        $fatal = 'Uncaught Error: Call to undefined function boom() in ' . ABSPATH . "wp-content/plugins/x/x.php:3\nStack trace:\n#0 {main}";
        return array(
            'a page' => array(array('status' => 200), array(), array(true, 200, 200, null), null),
            'wp-admin times out' => array(self::TIMED_OUT, array(), array(false, 0, 200, 'timeout'), self::TIMED_OUT),
            'no connection' => array(array('status' => 0), array('status' => 0), array(false, 0, 0, 'connection_failed'), 'Connection refused'),
            'wp-admin unavailable' => array(array('status' => 503), array(), array(false, 503, 200, 'http_503'), null),
            'wp-admin not found' => array(array('status' => 404), array(), array(false, 404, 200, 'http_404'), null),
            'wp-admin fatal error' => array(array('status' => 500, 'fatal' => $fatal), array(), array(false, 500, 200, 'fatal_error'), 'Uncaught Error: Call to undefined function boom() in wp-content/plugins/x/x.php:3'),
            'login page missing' => array(array('status' => 302), array('status' => 404), array(false, 302, 404, 'http_404'), null),
            'login page redirects' => array(array('status' => 302), array('status' => 302), array(false, 302, 302, 'http_302'), null),
            'login page fatal error' => array(array('status' => 302), array('status' => 200, 'fatal' => 'boom'), array(false, 302, 200, 'fatal_error'), 'boom'),
            'login page times out' => array(array('status' => 302), self::TIMED_OUT, array(false, 302, 0, 'timeout'), self::TIMED_OUT),
            'wp-admin first' => array(array('status' => 503), array('status' => 404), array(false, 503, 404, 'http_503'), null),
        );
    }

    /** @dataProvider transportErrors */
    public function test_only_a_timeout_is_told_apart_from_other_transport_errors(string $message, string $error): void {
        $this->site(array(self::ADMIN => $message));
        $this->assertSame($error, Sitepulse_Admin_Access::check()['error']);
    }

    public static function transportErrors(): array {
        return array(
            array('cURL error 28: Connection timeout after 10001 ms', 'timeout'), array('cURL error 28: Resolving timed out after 10000 milliseconds', 'timeout'),
            array('fsocket timed out', 'timeout'), array('cURL error 6: Could not resolve host: example.test', 'connection_failed'),
            array('cURL error 7: Failed to connect to example.test port 443: Connection refused', 'connection_failed'), array('cURL error 60: SSL certificate problem: self-signed certificate', 'connection_failed'),
        );
    }

    public function test_a_long_message_is_cut_to_200_characters_without_breaking_a_character(): void {
        $this->site(array(self::ADMIN => array('status' => 500, 'fatal' => str_repeat('é', 300))));
        $this->assertSame(str_repeat('é', 200), Sitepulse_Admin_Access::check()['message']);
    }

    public function test_the_last_result_that_found_the_opposite_is_kept(): void {
        $first = Sitepulse_Admin_Access::check();
        Sitepulse_Test_WP::$http_requests = array();
        $this->site(array(self::ADMIN => array('status' => 503)));
        $down = Sitepulse_Admin_Access::check();
        $this->assertSame(array('last' => $down, 'previous' => $first), get_option('sitepulse_admin_access'), 'Reachable until the first check.');

        $still = Sitepulse_Admin_Access::check();
        $this->assertSame(array('last' => $still, 'previous' => $first), get_option('sitepulse_admin_access'), 'It stays the last reachable check while it fails.');

        Sitepulse_Test_Upgrades::$pages[self::ADMIN] = array('status' => 302);
        $back = Sitepulse_Admin_Access::check();
        $this->assertSame(array('last' => $back, 'previous' => $still), get_option('sitepulse_admin_access'), 'Then the last failed check.');
    }

    public function test_a_check_does_not_start_while_another_runs(): void {
        set_transient('sitepulse_admin_access_lock', 1, 30);
        $this->assertNull(Sitepulse_Admin_Access::check());
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
        $this->assertFalse(get_option('sitepulse_admin_access'));
    }

    public function test_a_check_in_reports_a_recent_result_without_checking_again(): void {
        $this->assertNull(Sitepulse_Admin_Access::report(), 'Nothing before the first check.');
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);

        $recent = self::result(true, 14 * 60);
        $this->store($recent);
        $this->assertSame($recent, Sitepulse_Admin_Access::report());
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public function test_a_check_in_checks_again_first_when_the_result_is_older_than_15_minutes(): void {
        $this->store(self::result(false, 16 * 60));

        $report = Sitepulse_Admin_Access::report();

        $this->assertSame(array(self::ADMIN, self::LOGIN), $this->requested());
        $this->assertTrue($report['reachable']);
        $this->assertSame($report, Sitepulse_Admin_Access::last());
    }

    public function test_a_check_in_reports_the_older_result_while_another_check_runs(): void {
        $stale = self::result(true, 3600);
        $this->store($stale);
        set_transient('sitepulse_admin_access_lock', 1, 30);
        $this->assertSame($stale, Sitepulse_Admin_Access::report());
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    /** @dataProvider storedValues */
    public function test_unreadable_stored_results_count_as_no_result($stored): void {
        update_option('sitepulse_admin_access', $stored);
        $this->assertNull(Sitepulse_Admin_Access::last());
        $this->assertSame('Not checked yet', Sitepulse_Admin_Access::view()['label']);
    }

    public static function storedValues(): array {
        return array(
            'scalar' => array('corrupt'), 'no last' => array(array('previous' => null)), 'not an array' => array(array('last' => 'corrupt')),
            'reachable is not a boolean' => array(array('last' => array('reachable' => 'yes', 'checked_at' => '2026-10-18T22:10:00Z'))),
            'no time' => array(array('last' => array('reachable' => true))), 'unreadable time' => array(array('last' => array('reachable' => true, 'checked_at' => 'not-a-date'))),
        );
    }

    public function test_a_partly_stored_result_gets_every_field(): void {
        update_option('sitepulse_admin_access', array('last' => array('reachable' => false, 'checked_at' => '2026-10-18T22:10:00Z', 'error' => array('x'), 'message' => ''), 'previous' => 'corrupt'));
        $this->assertSame(array('reachable' => false, 'status' => 0, 'login_status' => 0, 'ms' => 0, 'error' => null, 'checked_at' => '2026-10-18T22:10:00Z'), Sitepulse_Admin_Access::last());
        $this->assertSame('wp-admin answered HTTP 0.', Sitepulse_Admin_Access::view()['detail']);
    }

    public function test_the_pages_say_when_it_was_not_checked_yet(): void {
        $this->assertSame(array('label' => 'Not checked yet', 'tone' => 'neutral', 'detail' => 'SitePulse checks every hour whether this server can reach its own wp-admin.', 'message' => '', 'note' => ''), Sitepulse_Admin_Access::view());
    }

    public function test_a_reachable_wp_admin_shows_its_status_time_and_age(): void {
        $this->store(self::result(true, 300));
        $this->assertSame(array('label' => 'Reachable', 'tone' => 'success', 'detail' => 'HTTP 302 → login · 184 ms · checked 5 mins ago', 'message' => '', 'note' => ''), Sitepulse_Admin_Access::view());

        $this->store(self::result(true, 60, array('status' => 200, 'ms' => 1234)), self::result(false, 3 * 3600));
        $view = Sitepulse_Admin_Access::view();
        $this->assertSame('HTTP 200 · 1,234 ms · checked 1 min ago', $view['detail']);
        $this->assertSame('Was not reachable until 3 hours ago.', $view['note'], 'A problem of the last day is mentioned.');

        $this->store(self::result(true, 60), self::result(false, 2 * 86400));
        $this->assertSame('', Sitepulse_Admin_Access::view()['note'], 'An older one is not.');
    }

    /** @dataProvider unreachable */
    public function test_an_unreachable_wp_admin_says_why_in_plain_words(array $fields, string $detail): void {
        $this->store(self::result(false, 300, $fields));
        $view = Sitepulse_Admin_Access::view();
        $this->assertSame(array('Not reachable', 'danger', $detail), array($view['label'], $view['tone'], $view['detail']));
        $this->assertSame('Checked 5 mins ago.', $view['note']);
    }

    public static function unreachable(): array {
        return array(
            'timeout' => array(array('status' => 0, 'error' => 'timeout'), 'wp-admin did not answer the server\'s own request within 10 seconds.'),
            'connection' => array(array('status' => 0, 'error' => 'connection_failed'), 'The server could not connect to its own wp-admin (loopback requests may be blocked by the host).'),
            'fatal error' => array(array('status' => 500, 'error' => 'fatal_error'), 'A PHP fatal error stopped wp-admin or its login page.'),
            'wp-admin status' => array(array('status' => 503, 'error' => 'http_503'), 'wp-admin answered HTTP 503.'),
            'login status' => array(array('status' => 302, 'login_status' => 404, 'error' => 'http_404'), 'The login page answered HTTP 404 instead of 200.'),
        );
    }

    public function test_an_unreachable_wp_admin_shows_the_error_and_since_when(): void {
        $this->store(self::result(false, 300, array('status' => 500, 'error' => 'fatal_error', 'message' => 'Uncaught Error: boom')), self::result(true, 2 * 3600));
        $view = Sitepulse_Admin_Access::view();
        $this->assertSame('Uncaught Error: boom', $view['message']);
        $this->assertSame('Checked 5 mins ago. Was reachable until 2 hours ago.', $view['note']);
    }

    public function test_check_now_needs_an_administrator_and_a_valid_nonce(): void {
        Sitepulse_Test_WP::$capabilities = array();
        $_REQUEST['nonce'] = 'nonce-sitepulse_admin_access_check';
        $response = $this->ajaxResponse(array('Sitepulse_Admin_Access', 'ajax_check'));
        $this->assertSame(array(false, null, 403), array($response->success, $response->data, $response->status));

        Sitepulse_Test_WP::$capabilities = array('manage_options' => true);
        $_REQUEST['nonce'] = 'nonce-other';
        $response = $this->ajaxResponse(array('Sitepulse_Admin_Access', 'ajax_check'));
        $this->assertSame(array(false, 403), array($response->success, $response->status));
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests, 'Nothing was checked.');
    }

    public function test_check_now_checks_and_answers_what_the_pages_show(): void {
        $_REQUEST['nonce'] = 'nonce-sitepulse_admin_access_check';
        $response = $this->ajaxResponse(array('Sitepulse_Admin_Access', 'ajax_check'));
        $this->assertTrue($response->success);
        $this->assertSame(array(self::ADMIN, self::LOGIN), $this->requested());
        $this->assertSame(Sitepulse_Admin_Access::view(), $response->data);
        $this->assertSame('Reachable', $response->data['label']);
        $this->assertStringStartsWith('HTTP 302 → login · ', $response->data['detail']);
    }

    public function test_check_now_says_when_a_check_is_already_running(): void {
        $_REQUEST['nonce'] = 'nonce-sitepulse_admin_access_check';
        set_transient('sitepulse_admin_access_lock', 1, 30);
        $response = $this->ajaxResponse(array('Sitepulse_Admin_Access', 'ajax_check'));
        $this->assertSame(array(false, array('message' => 'A check is already running. Try again in a moment.'), 409), array($response->success, $response->data, $response->status));
    }

    public function test_the_advanced_dashboard_shows_the_result_and_the_check_now_button(): void {
        $this->store(self::result(false, 300, array('status' => 500, 'error' => 'fatal_error', 'message' => '<b>boom</b>')), self::result(true, 7200));

        $html = $this->render();

        $this->assertStringContainsString('<div class="sp-detail-item" id="sitepulse-wp-admin" data-sitepulse-admin-access data-url="https://example.test/wp-admin/admin-ajax.php" data-action="sitepulse_admin_access_check" data-nonce="nonce-sitepulse_admin_access_check">', $html);
        $this->assertStringContainsString('<span class="badge bg-danger" data-admin-access-badge data-success="badge bg-success" data-danger="badge bg-danger" data-neutral="badge bg-secondary">Not reachable</span>', $html);
        $this->assertStringContainsString('<div class="sp-detail-meta" data-admin-access-detail>A PHP fatal error stopped wp-admin or its login page.</div>', $html);
        $this->assertStringContainsString('<div class="sp-detail-meta font-monospace" data-admin-access-message>&lt;b&gt;boom&lt;/b&gt;</div>', $html);
        $this->assertStringContainsString('<div class="sp-detail-meta" data-admin-access-note>Checked 5 mins ago. Was reachable until 2 hours ago.</div>', $html);
        $this->assertStringContainsString('<div class="sp-detail-meta text-danger" data-admin-access-error hidden></div>', $html);
        $this->assertStringContainsString('data-checking="Checking…" data-failed="The check could not run. Reload the page and try again.">Check now</button>', $html);
        $this->assertSame(array('src' => 'https://example.test/wp-content/plugins/sitepulse/assets/js/admin-access.js', 'deps' => array(), 'ver' => SITEPULSE_VERSION, 'args' => true), Sitepulse_Test_WP::$scripts['sitepulse-admin-access']);
    }

    public function test_the_simple_dashboard_shows_the_result_and_hides_empty_lines(): void {
        $this->store(self::result(true, 300));

        $html = $this->render('easy');

        $this->assertStringContainsString('<div class="sp-card sp-mb-16" id="sitepulse-wp-admin" data-sitepulse-admin-access data-url="https://example.test/wp-admin/admin-ajax.php"', $html);
        $this->assertStringContainsString('wp-admin Access</h3>', $html);
        $this->assertStringContainsString('<span class="sp-badge sp-badge-success" data-admin-access-badge data-success="sp-badge sp-badge-success" data-danger="sp-badge sp-badge-danger" data-neutral="sp-badge sp-badge-neutral">Reachable</span>', $html);
        $this->assertStringContainsString('<p class="sp-text-sm sp-mb-8" data-admin-access-detail>HTTP 302 → login · 184 ms · checked 5 mins ago</p>', $html);
        $this->assertStringContainsString('<p class="sp-text-xs sp-mono sp-mb-8" data-admin-access-message hidden></p>', $html);
        $this->assertStringContainsString('<p class="sp-text-xs sp-text-muted sp-mb-8" data-admin-access-note hidden></p>', $html);
        $this->assertStringContainsString('class="sp-btn sp-btn-outline sp-btn-sm" data-admin-access-check', $html);
    }

    public function test_only_administrators_see_it(): void {
        Sitepulse_Test_WP::$capabilities = array();
        $this->assertSame('', $this->render());
        $this->assertArrayNotHasKey('sitepulse-admin-access', Sitepulse_Test_WP::$scripts);
    }

    public function test_the_toolbar_links_to_the_page_that_shows_it(): void {
        $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse&sp_view=system#sitepulse-wp-admin', Sitepulse_Admin_Access::page_url(), 'Site Information on the Simple dashboard.');
        update_user_meta(1, 'sitepulse_easy_mode', '0');
        $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_settings#sitepulse-wp-admin', Sitepulse_Admin_Access::page_url(), 'The settings on the Advanced one.');
    }
}

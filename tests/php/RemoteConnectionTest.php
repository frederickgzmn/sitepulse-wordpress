<?php
require_once __DIR__ . '/Support/RemoteManagement.php';
require_once __DIR__ . '/Support/Database.php';

/**
 * Connecting the site to a SitePulse dashboard with a connection key, from the remote management settings.
 */
final class RemoteConnectionTest extends Sitepulse_Test_Case {
    private const NONCE = 'nonce-sitepulse_remote_connection';

    protected function setUp(): void {
        parent::setUp();
        Sitepulse_Test_Backups::reset();
        // An active site's check-in sends the inventory, which reads the database version.
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
        Sitepulse_Test_WP::$capabilities['manage_options'] = true;
        Sitepulse_Test_WP::$referer = 'https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_settings';
        $_REQUEST['_wpnonce'] = self::NONCE;
    }

    protected function tearDown(): void {
        Sitepulse_Test_Backups::reset();
        unset($_POST['sitepulse_connection'], $_POST['sitepulse_license'], $_REQUEST['nonce'], $GLOBALS['wpdb']);
        parent::tearDown();
    }

    private function connect(): void { update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE); }

    /** Press one of the connection buttons; the form always redirects back. */
    private function press(string $operation): void {
        $_POST['sitepulse_connection'] = $operation;
        try { Sitepulse_Remote_Management::handle_connection_form(); $this->fail('Expected a redirect.'); }
        catch (Sitepulse_Test_Redirect $redirect) { $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_settings', $redirect->getMessage()); }
    }

    private function answer(array $data): void {
        Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response(array('success' => true, 'data' => $data));
    }

    /** The API's answer to a check-in. */
    private function checkedIn(bool $active = false): void {
        Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response(array('success' => true, 'remote_management' => array('enabled' => $active, 'active' => $active, 'check_in_seconds' => $active ? 300 : 3600), 'commands' => array()));
    }

    private function endpoints(): array { return array_map('sitepulse_test_remote_endpoint', array_keys(Sitepulse_Test_WP::$http_requests)); }

    private function stored(): array { return get_option(Sitepulse_Remote_Management::CONNECTION_OPTION); }

    private function render(string $context = 'advanced'): string {
        ob_start(); Sitepulse_Remote_Management::render_settings($context); return ob_get_clean();
    }

    /** Ask, as the settings page does while it shows a key, whether the key was used. */
    private function poll(string $nonce = 'nonce-sitepulse_remote_connection_poll'): Sitepulse_Test_Json_Response {
        $_REQUEST['nonce'] = $nonce;
        try { Sitepulse_Remote_Management::ajax_connection_poll(); } catch (Sitepulse_Test_Json_Response $response) { return $response; }
        $this->fail('Expected a JSON answer.');
    }

    public function test_init_registers_the_buttons_the_poll_the_late_check_in_and_the_plugins_link(): void {
        Sitepulse_Remote_Management::init();
        $this->assertNotFalse(has_action('admin_post_sitepulse_remote_connection', array('Sitepulse_Remote_Management', 'handle_connection_form')));
        $this->assertNotFalse(has_action('wp_ajax_sitepulse_remote_connection_poll', array('Sitepulse_Remote_Management', 'ajax_connection_poll')));
        $this->assertNotFalse(has_action('admin_init', array('Sitepulse_Remote_Management', 'check_in_when_late')));
        $this->assertNotFalse(has_filter('plugin_action_links_sitepulse/loader.php', array('Sitepulse_Remote_Management', 'plugin_links')));
    }

    public function test_a_key_comes_after_a_check_in_and_the_page_waits_for_the_dashboard_to_use_it(): void {
        $this->connect();
        $this->checkedIn();
        $this->answer(array('key' => 'spc_first_key', 'connectable' => true, 'has_key' => true, 'connections' => array()));

        $this->press('key');

        // The check-in tells the API where to wake the site once the key is used.
        $this->assertSame(array('check-in', 'connection/key'), $this->endpoints());
        $this->assertSame('[]', Sitepulse_Test_WP::$http_requests[1]['args']['body']);
        $this->assertSame('spc_first_key', $this->stored()['key']);
        $html = $this->render();
        $this->assertStringContainsString('value="spc_first_key"', $html);
        $this->assertStringContainsString('<button type="button" class="button" data-copy="sitepulse-connection-key" data-copied="Copied">Copy</button>', $html);
        $this->assertStringContainsString('Enter this site&#039;s address (example.test) and the key.', $html);
        $this->assertStringContainsString('data-poll-url="https://example.test/wp-admin/admin-ajax.php" data-poll-action="sitepulse_remote_connection_poll" data-poll-nonce="nonce-sitepulse_remote_connection_poll"', $html);
        $this->assertStringContainsString('Waiting for your SitePulse dashboard.', $html);
        $this->assertStringContainsString('Get a new key', $html);
        $this->assertStringContainsString('name="_wpnonce" value="nonce-sitepulse_remote_connection"', $html);
        $this->assertSame('https://example.test/wp-content/plugins/sitepulse/assets/js/remote-connection.js', Sitepulse_Test_WP::$scripts['sitepulse-remote-connection']['src']);
        // The Simple dashboard's buttons.
        $this->assertStringContainsString('class="sp-btn sp-btn-outline sp-btn-sm" data-copy="sitepulse-connection-key"', $this->render('easy'));
    }

    public function test_a_site_without_a_license_registers_before_it_shows_a_key(): void {
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(json_encode(array('license_key' => SITEPULSE_TEST_LICENSE)));
        $this->checkedIn();
        $this->answer(array('key' => 'spc_new_site', 'connectable' => true, 'has_key' => true, 'connections' => array()));

        $this->press('key');

        $this->assertSame('https://api.sitepulse.me/api/website/website-request', Sitepulse_Test_WP::$http_requests[0]['url']);
        $this->assertSame(SITEPULSE_TEST_LICENSE, get_option('sitepulse_license_key'));
        $this->assertSame(array('check-in', 'connection/key'), array_slice($this->endpoints(), 1));
        $this->assertSame('spc_new_site', $this->stored()['key']);
    }

    public function test_says_why_a_site_that_cannot_register_gets_no_key(): void {
        Sitepulse_Test_WP::$http_queue[] = Sitepulse_Test_WP::response(json_encode(array('success' => false, 'message' => 'Maintenance')), 503);

        $this->press('key');

        $this->assertCount(1, Sitepulse_Test_WP::$http_requests, 'Only the registration was tried.');
        $this->assertStringStartsWith('This site is not registered with SitePulse yet: ', $this->stored()['error']);
        $this->assertStringContainsString('This site is not registered with SitePulse yet: ', $this->render());
    }

    public function test_checking_the_connection_checks_in_and_shows_the_accounts_once_the_key_was_used(): void {
        $this->connect();
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('key' => 'spc_first_key'));

        // Still waiting: the key stays.
        $this->answer(array('connectable' => true, 'has_key' => true, 'connections' => array()));
        $this->checkedIn();
        $this->press('status');
        $this->assertSame('spc_first_key', $this->stored()['key']);
        $this->assertSame(array('connection', 'check-in'), $this->endpoints());

        // The dashboard used the key: it is gone, the account shows and the site is managed.
        $this->answer(array('connectable' => true, 'has_key' => false, 'connections' => array(
            array('account' => 'o***@agency.example', 'plan' => 'Agency', 'connected_at' => '2026-10-20T10:00:00+00:00'),
            array('account' => null, 'plan' => 'Starter', 'connected_at' => 'not a date'),
            'not a connection',
        )));
        $this->checkedIn(true);
        $this->answer(array('success' => true));
        $this->press('status');

        $this->assertSame('', $this->stored()['key']);
        $this->assertTrue(Sitepulse_Remote_Management::is_active());
        foreach (array('advanced', 'easy') as $context) {
            $html = $this->render($context);
            $this->assertStringContainsString('Connected to o***@agency.example (Agency plan) since 2026-10-20 10:00.', $html);
            $this->assertStringContainsString('Connected to a SitePulse account (Starter plan) by SitePulse since .', $html);
            $this->assertStringContainsString('Disconnect from SitePulse accounts', $html);
            $this->assertStringNotContainsString('to manage this site from there', $html, 'A managed site is not asked to get a key.');
            $this->assertStringContainsString('Get connection key', $html);
            $this->assertStringNotContainsString('data-poll-url', $html);
            $this->assertStringContainsString('Checked 1 min ago.', $html);
        }
    }

    public function test_a_site_connected_by_sitepulse_offers_a_key_to_be_managed(): void {
        $this->connect();
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('connections' => array(array('account' => '', 'plan' => 'Agency', 'connected_at' => '2026-07-19T17:00:00+00:00'))));

        $html = $this->render();

        $this->assertStringContainsString('Connected to a SitePulse account (Agency plan) by SitePulse since 2026-07-19 17:00.', $html);
        $this->assertStringContainsString('Get connection key', $html);
        $this->assertStringContainsString('Get a connection key and enter it in your SitePulse dashboard to manage this site from there', $html);
    }

    public function test_disconnects_the_site_from_its_accounts(): void {
        $this->connect();
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('connections' => array(array('account' => 'o***@agency.example', 'plan' => 'Agency', 'connected_at' => ''))));
        $this->answer(array('connectable' => true, 'has_key' => false, 'connections' => array()));
        $this->checkedIn();

        $this->press('disconnect');

        $this->assertSame(array('connection/disconnect', 'check-in'), $this->endpoints());
        $this->assertSame(array(), $this->stored()['connections']);
        $this->assertSame(array('This site was disconnected from its SitePulse accounts.'), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
        $this->assertStringContainsString('Get connection key', $this->render());
        $this->assertStringNotContainsString('Disconnect from SitePulse accounts', $this->render());
    }

    /**
     * @dataProvider failures
     * @param WP_Error|callable $response What the request to SitePulse got.
     */
    public function test_keeps_the_key_and_says_why_sitepulse_did_not_answer($response, string $error, string $shown): void {
        $this->connect();
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('key' => 'spc_kept'));
        Sitepulse_Test_WP::$http_queue[] = $response;

        $this->press('status');

        $this->assertSame(array('connection'), $this->endpoints());
        $this->assertSame('spc_kept', $this->stored()['key']);
        $this->assertSame($error, $this->stored()['error']);
        foreach (array('advanced', 'easy') as $context) {
            $this->assertStringContainsString('<p style="color: #b32d2e; margin: 6px 0 0;">' . $shown . '</p>', $this->render($context));
        }
    }

    public static function failures(): array {
        // WordPress's reason is cut to 160 characters, here just after a two-byte character.
        $kept = 'cURL error 28: <b>Operation</b> timed out & ' . str_repeat('x', 115) . 'é';
        $refused = static function (int $status): array {
            return array(
                sitepulse_test_remote_response(array('success' => false), $status),
                "SitePulse refused this site's request (HTTP $status). Check the server's date and time, and that Cloud Services are on; if the site's address or license changed, disconnect and connect again.",
                "SitePulse refused this site&#039;s request (HTTP $status). Check the server&#039;s date and time, and that Cloud Services are on; if the site&#039;s address or license changed, disconnect and connect again.",
            );
        };

        $failures = array(
            'this server cannot reach SitePulse' => array(
                new WP_Error('http_request_failed', $kept . ' and the rest of a long reason'),
                'SitePulse could not be reached from this server (' . $kept . '). Check that the server can reach api.sitepulse.me, then try again.',
                'SitePulse could not be reached from this server (cURL error 28: &lt;b&gt;Operation&lt;/b&gt; timed out &amp; ' . str_repeat('x', 115) . 'é). Check that the server can reach api.sitepulse.me, then try again.',
            ),
            'SitePulse fails' => array(
                sitepulse_test_remote_response(array('success' => false), 500),
                'SitePulse is having trouble right now (HTTP 500). Please try again in a few minutes.',
                'SitePulse is having trouble right now (HTTP 500). Please try again in a few minutes.',
            ),
            'SitePulse is unavailable' => array(
                sitepulse_test_remote_response(array('success' => false), 503),
                'SitePulse is having trouble right now (HTTP 503). Please try again in a few minutes.',
                'SitePulse is having trouble right now (HTTP 503). Please try again in a few minutes.',
            ),
            'signature refused' => $refused(401),
            'license refused' => $refused(403),
            'too many requests' => array(
                sitepulse_test_remote_response(array('success' => false), 429),
                'SitePulse asked this site to slow down (HTTP 429). Please try again in a minute.',
                'SitePulse asked this site to slow down (HTTP 429). Please try again in a minute.',
            ),
            'another status' => array(
                sitepulse_test_remote_response(array('success' => false), 404),
                'SitePulse answered HTTP 404. Please try again in a moment.',
                'SitePulse answered HTTP 404. Please try again in a moment.',
            ),
            'an answer signed for another site' => array(
                sitepulse_test_remote_response(array('success' => true), 200, array('license' => 'sk_other')),
                "SitePulse answered, but the answer was not signed for this site. Check the server's date and time, then try again.",
                'SitePulse answered, but the answer was not signed for this site. Check the server&#039;s date and time, then try again.',
            ),
            'an answer that is not understood' => array(
                sitepulse_test_remote_response('not json'),
                "SitePulse's answer could not be understood. Please try again in a moment.",
                'SitePulse&#039;s answer could not be understood. Please try again in a moment.',
            ),
            // Without the API's reason, a refusal keeps the general sentence.
            'a refusal that is not JSON' => array_merge(array(Sitepulse_Test_WP::response('<html>401 Unauthorized</html>', 401)), array_slice($refused(401), 1)),
            'a refusal code this version does not know' => array_merge(array(Sitepulse_Test_WP::response(array('success' => false, 'error' => 'license_on_fire', 'message' => 'Something new.'), 403)), array_slice($refused(403), 1)),
        );
        // The API says why it refused the site: the settings say what to fix.
        foreach (sitepulse_test_remote_refusals() as $code => list($response, $sentence)) {
            $failures["refused: $code"] = array($response, $sentence, str_replace("'", '&#039;', $sentence));
        }
        return $failures;
    }

    public function test_a_failure_the_client_does_not_report_keeps_the_general_sentence(): void {
        $message = new ReflectionMethod(Sitepulse_Remote_Management::class, 'connection_error_message');
        if (PHP_VERSION_ID < 80100) { $message->setAccessible(true); }

        $this->assertSame('SitePulse could not be reached. Please try again in a moment.', $message->invoke(null, new WP_Error('http_request_failed', 'timeout')));
        $this->assertSame('SitePulse could not be reached. Please try again in a moment.', $message->invoke(null, new WP_Error('http_', 'HTTP')));
    }

    private const LICENSE = '1A2B-3C4D-5E6F-7A8B-9C0D';
    private const CLOSED = '<details style="margin: 8px 0 0;">';
    private const OPEN = '<details style="margin: 8px 0 0;" open>';

    /** Enter a license in the connection box and save it. */
    private function saveLicense(?string $license): void {
        if (null !== $license) { $_POST['sitepulse_license'] = $license; }
        $this->press('license');
    }

    /** A site that works with its license and a key the dashboard rotated, as SitePulse last saw it. */
    private function rotated(): array {
        $previous = array(
            'sitepulse_license_key' => SITEPULSE_TEST_LICENSE,
            Sitepulse_Remote_Management::KEY_OPTION => 'rk_' . str_repeat('k', 40),
            Sitepulse_Remote_Management::STATE_OPTION => array('active' => false, 'last_check_in' => 5, 'last_error' => ''),
        );
        foreach ($previous as $option => $value) { update_option($option, $value); }
        return $previous;
    }

    public function test_the_license_form_is_closed_until_sitepulse_refuses_the_license(): void {
        $this->connect();
        foreach (array('advanced', 'easy') as $context) {
            $html = $this->render($context);
            $this->assertStringContainsString(self::CLOSED . '<summary>Change the license this site uses</summary>', preg_replace('/>\s+</', '><', $html));
            $this->assertStringContainsString('<label for="sitepulse-license" style="display: block; margin: 0 0 4px;">Set the site&#039;s SitePulse license</label>', $html);
            $this->assertStringContainsString('<input type="text" id="sitepulse-license" name="sitepulse_license" class="regular-text code" required pattern="[0-9A-Fa-f]{4}(?:-[0-9A-Fa-f]{4}){4}" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX" autocomplete="off" spellcheck="false" />', $html);
            $this->assertStringContainsString('value="license" class="' . ('easy' === $context ? 'sp-btn sp-btn-outline sp-btn-sm' : 'button') . '">Save</button>', $html);
            $this->assertStringNotContainsString(SITEPULSE_TEST_LICENSE, $html, 'The license is never shown.');
        }

        // SitePulse refuses the site's key: the form opens on both dashboards.
        Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_refusals()['invalid_signature'][0];
        $this->press('status');
        $this->assertSame('invalid_signature', $this->stored()['error_code']);
        foreach (array('advanced', 'easy') as $context) {
            $html = $this->render($context);
            $this->assertStringContainsString(self::OPEN, $html);
            $this->assertStringContainsString('Enter the license your SitePulse dashboard shows for this site below, then press Check connection.', $html);
        }

        // Another refusal does not ask for the license.
        Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_refusals()['request_expired'][0];
        $this->press('status');
        $this->assertSame('request_expired', $this->stored()['error_code']);
        $this->assertStringContainsString(self::CLOSED, $this->render());

        // Neither does a site that cannot connect, which has no form.
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('connectable' => false, 'error_code' => 'invalid_signature'));
        $this->assertStringNotContainsString('sitepulse_license', $this->render());
    }

    public function test_a_license_sitepulse_accepts_replaces_the_old_one_and_its_rotated_key(): void {
        $this->rotated();
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('error' => 'Refused.', 'error_code' => 'invalid_signature', 'key' => 'spc_kept'));
        Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response(array('success' => true, 'remote_management' => array('enabled' => false, 'active' => false), 'commands' => array()), 200, array('license' => self::LICENSE));

        $this->saveLicense(' 1a2b-3c4d-5e6f-7a8b-9c0d ');

        // The check-in was signed with the new license, not the rotated key.
        $this->assertSame(array('check-in'), $this->endpoints());
        $headers = Sitepulse_Test_WP::$http_requests[0]['args']['headers'];
        $this->assertSame(Sitepulse_Remote_Client::request_signature('POST', '/api/website/remote/check-in', 'example.test', $headers['X-SitePulse-Timestamp'], $headers['X-SitePulse-Nonce'], Sitepulse_Test_WP::$http_requests[0]['args']['body'], self::LICENSE), $headers['X-SitePulse-Signature']);
        $this->assertSame(array(self::LICENSE, false), array(get_option('sitepulse_license_key'), get_option(Sitepulse_Remote_Management::KEY_OPTION)));
        $this->assertSame(array('', '', 'The license was saved and the site checked in.', 'spc_kept'), array($this->stored()['error'], $this->stored()['error_code'], $this->stored()['notice'], $this->stored()['key']));
        $this->assertSame(array('The SitePulse license of this site was changed in its settings.'), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
        $this->assertEqualsWithDelta(time(), Sitepulse_Remote_Management::state()['last_check_in'], 2);
        foreach (array('advanced', 'easy') as $context) {
            $html = $this->render($context);
            $this->assertStringContainsString('<p style="color: #00a32a; margin: 6px 0 0;">The license was saved and the site checked in.</p>', $html);
            $this->assertStringContainsString(self::CLOSED, $html);
            $this->assertStringNotContainsString('color: #b32d2e', $html);
        }

        // The next button replaces the notice, here with an error.
        Sitepulse_Test_WP::$http_queue[] = new WP_Error('http_request_failed', 'timeout');
        $this->press('status');
        $this->assertSame(array('', 'SitePulse could not be reached from this server (timeout). Check that the server can reach api.sitepulse.me, then try again.'), array($this->stored()['notice'], $this->stored()['error']));
        $this->assertStringNotContainsString('The license was saved', $this->render());
    }

    /**
     * @dataProvider refusedLicenses
     * @param WP_Error|array|callable $response SitePulse's answer to the check-in with the new license.
     */
    public function test_a_license_sitepulse_does_not_accept_is_not_kept($response, string $error): void {
        $previous = $this->rotated();
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('notice' => 'The license was saved and the site checked in.'));
        Sitepulse_Test_WP::$http_queue[] = $response;

        $this->saveLicense(self::LICENSE);

        // The site keeps the license, key and state it worked with.
        $this->assertSame(array('check-in'), $this->endpoints());
        foreach ($previous as $option => $value) { $this->assertSame($value, get_option($option), $option); }
        $this->assertSame(array($error, 'license', ''), array($this->stored()['error'], $this->stored()['error_code'], $this->stored()['notice']));
        $this->assertStringStartsWith('Check-in with SitePulse failed: ', Sitepulse_Remote_Management::log_entries()[0]['message']);
        foreach (array('advanced', 'easy') as $context) {
            $html = $this->render($context);
            $this->assertStringContainsString('<p style="color: #b32d2e; margin: 6px 0 0;">' . esc_html($error) . '</p>', $html);
            $this->assertStringContainsString(self::OPEN, $html, 'The form stays open to try again.');
            $this->assertStringNotContainsString('color: #00a32a', $html);
        }
    }

    public static function refusedLicenses(): array {
        return array(
            'the license does not match' => array(sitepulse_test_remote_refusals()['invalid_signature'][0], 'SitePulse does not accept that license for this site, so it was not saved. Enter the license your SitePulse dashboard shows for this site.'),
            'SitePulse cannot be reached' => array(new WP_Error('http_request_failed', 'cURL error 28'), 'The license was not saved: SitePulse could not check it. SitePulse could not be reached from this server (cURL error 28). Check that the server can reach api.sitepulse.me, then try again.'),
            'the clock is off' => array(sitepulse_test_remote_refusals()['request_expired'][0], "The license was not saved: SitePulse could not check it. SitePulse rejected the request because this server's clock is more than 5 minutes off. Fix the server's date and time, then try again."),
        );
    }

    public function test_a_site_without_a_license_stays_without_one_when_sitepulse_refuses_it(): void {
        Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_refusals()['invalid_signature'][0];

        $this->saveLicense(self::LICENSE);

        $this->assertSame(array(false, false, false), array(get_option('sitepulse_license_key'), get_option(Sitepulse_Remote_Management::KEY_OPTION), get_option(Sitepulse_Remote_Management::STATE_OPTION)));
        $this->assertSame('license', $this->stored()['error_code']);
    }

    /** @dataProvider malformedLicenses */
    public function test_a_license_in_another_format_is_refused_without_asking_sitepulse(?string $license): void {
        $previous = $this->rotated();

        $this->saveLicense($license);

        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
        foreach ($previous as $option => $value) { $this->assertSame($value, get_option($option), $option); }
        $error = 'That is not a SitePulse license. A license is five groups of four characters (0 to 9 and A to F) separated by dashes, such as 1A2B-3C4D-5E6F-7A8B-9C0D.';
        $this->assertSame(array($error, 'license'), array($this->stored()['error'], $this->stored()['error_code']));
        foreach (array('advanced', 'easy') as $context) {
            $html = $this->render($context);
            $this->assertStringContainsString('<p style="color: #b32d2e; margin: 6px 0 0;">' . $error . '</p>', $html);
            $this->assertStringContainsString(self::OPEN, $html);
        }
    }

    public static function malformedLicenses(): array {
        return array(
            'no field' => array(null),
            'empty' => array(''),
            'four groups' => array('1A2B-3C4D-5E6F-7A8B'),
            'not hexadecimal' => array('1A2B-3C4D-5E6F-7A8B-9C0G'),
            'six groups' => array('1A2B-3C4D-5E6F-7A8B-9C0D-1234'),
            'a test license' => array(SITEPULSE_TEST_LICENSE),
        );
    }

    public function test_only_administrators_with_a_valid_nonce_and_cloud_services_may_set_the_license(): void {
        $previous = $this->rotated();
        $_POST['sitepulse_license'] = self::LICENSE;
        $_POST['sitepulse_connection'] = 'license';

        Sitepulse_Test_WP::$capabilities['manage_options'] = false;
        try { Sitepulse_Remote_Management::handle_connection_form(); $this->fail('Expected wp_die.'); } catch (Sitepulse_Test_Die $die) { $this->assertStringContainsString('sufficient permissions', $die->getMessage()); }
        Sitepulse_Test_WP::$capabilities['manage_options'] = true;
        $_REQUEST['_wpnonce'] = 'forged';
        try { Sitepulse_Remote_Management::handle_connection_form(); $this->fail('Expected wp_die.'); } catch (Sitepulse_Test_Die $die) { $this->assertStringContainsString('expired', $die->getMessage()); }
        $_REQUEST['_wpnonce'] = self::NONCE;
        update_option('sitepulse_settings', array('external_api_enabled' => false));
        $previous['sitepulse_settings'] = get_option('sitepulse_settings');
        $this->press('license');

        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
        foreach ($previous as $option => $value) { $this->assertSame($value, get_option($option), $option); }
        $this->assertFalse(get_option(Sitepulse_Remote_Management::CONNECTION_OPTION));
    }

    public function test_explains_a_pro_site_and_a_site_that_cannot_connect(): void {
        $this->connect();
        $this->answer(array('connectable' => false, 'reason' => 'pro_site', 'has_key' => false, 'connections' => 'none'));
        $this->checkedIn();
        $this->press('status');
        $this->assertStringContainsString('This site has a SitePulse Pro license, so it already appears in the account that owns the license.', $this->render());

        update_option('sitepulse_settings', array('external_api_enabled' => false));
        $this->assertStringContainsString('Turn on Cloud Services so this site can connect to your SitePulse dashboard.', $this->render());
        $this->assertFalse(Sitepulse_Remote_Management::view_data()['connection']['available']);
    }

    public function test_sends_nothing_for_an_unknown_button_or_without_cloud_services(): void {
        $this->connect();
        $this->press('delete-everything');
        unset($_POST['sitepulse_connection']);
        try { Sitepulse_Remote_Management::handle_connection_form(); } catch (Sitepulse_Test_Redirect $redirect) {}
        update_option('sitepulse_settings', array('external_api_enabled' => false));
        $this->press('key');
        // Without a license, only asking for a key does something: it registers the site.
        update_option('sitepulse_settings', array());
        delete_option('sitepulse_license_key');
        $this->press('status');
        $this->press('disconnect');

        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
        $this->assertFalse(get_option(Sitepulse_Remote_Management::CONNECTION_OPTION));
    }

    public function test_redirects_to_the_settings_without_a_referer(): void {
        Sitepulse_Test_WP::$referer = false;
        $_POST['sitepulse_connection'] = 'status';
        update_option('sitepulse_settings', array('external_api_enabled' => false));
        try { Sitepulse_Remote_Management::handle_connection_form(); $this->fail('Expected a redirect.'); }
        catch (Sitepulse_Test_Redirect $redirect) { $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_settings', $redirect->getMessage()); }
    }

    public function test_only_administrators_with_a_valid_nonce_may_press_the_buttons(): void {
        $this->connect();
        Sitepulse_Test_WP::$capabilities['manage_options'] = false;
        try { Sitepulse_Remote_Management::handle_connection_form(); $this->fail('Expected wp_die.'); } catch (Sitepulse_Test_Die $die) { $this->assertStringContainsString('sufficient permissions', $die->getMessage()); }

        Sitepulse_Test_WP::$capabilities['manage_options'] = true;
        $_REQUEST['_wpnonce'] = 'forged';
        try { Sitepulse_Remote_Management::handle_connection_form(); $this->fail('Expected wp_die.'); } catch (Sitepulse_Test_Die $die) { $this->assertStringContainsString('expired', $die->getMessage()); }

        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public function test_the_page_learns_when_the_key_was_used_and_the_site_checks_in_at_once(): void {
        $this->connect();
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('key' => 'spc_waiting'));

        // Not used yet.
        $this->answer(array('connectable' => true, 'has_key' => true, 'connections' => array()));
        $response = $this->poll();
        $this->assertSame(array(true, array('used' => false)), array($response->success, $response->data));
        $this->assertSame(array('connection'), $this->endpoints());

        // SitePulse cannot be reached: asked again later.
        Sitepulse_Test_WP::$http_queue[] = new WP_Error('http_request_failed', 'timeout');
        $this->assertSame(array('used' => false), $this->poll()->data);
        $this->assertSame('spc_waiting', $this->stored()['key']);

        // Used: the site checks in now, without waiting for WP-Cron.
        $this->answer(array('connectable' => true, 'has_key' => false, 'connections' => array(array('account' => 'o***@agency.example', 'plan' => 'Agency', 'connected_at' => '2026-10-20T10:00:00+00:00'))));
        $this->checkedIn(true);
        $this->answer(array('success' => true));
        $this->assertSame(array('used' => true), $this->poll()->data);
        $this->assertSame(array('connection', 'connection', 'connection', 'check-in', 'inventory'), $this->endpoints());
        $this->assertSame('', $this->stored()['key']);
        $this->assertTrue(Sitepulse_Remote_Management::is_active());

        // Nothing waits any more: the page reloads without asking SitePulse.
        $this->assertSame(array('used' => true), $this->poll()->data);
        $this->assertCount(5, Sitepulse_Test_WP::$http_requests);
    }

    public function test_the_poll_needs_an_administrator_and_its_nonce_and_a_site_that_can_connect(): void {
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('key' => 'spc_waiting'));
        $this->assertSame(array(false, 403), array($this->poll('forged')->success, $this->poll('forged')->status));
        Sitepulse_Test_WP::$capabilities['manage_options'] = false;
        $this->assertSame(403, $this->poll()->status);

        Sitepulse_Test_WP::$capabilities['manage_options'] = true;
        $this->assertSame(array('used' => false), $this->poll()->data, 'Without a license, the key cannot have been used.');
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public function test_the_plugins_screen_links_to_the_connection_key(): void {
        $links = Sitepulse_Remote_Management::plugin_links(array('deactivate' => '<a>Deactivate</a>'));
        $this->assertSame(array('sitepulse-connection', 'deactivate'), array_keys($links));
        $this->assertSame('<a href="https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_settings#sitepulse-remote-connection">Connection key</a>', $links['sitepulse-connection']);

        Sitepulse_Test_WP::$capabilities['manage_options'] = false;
        $this->assertSame(array('deactivate' => '<a>Deactivate</a>'), Sitepulse_Remote_Management::plugin_links(array('deactivate' => '<a>Deactivate</a>')));
        Sitepulse_Test_WP::$capabilities['manage_options'] = true;
        Sitepulse_Test_WP::$multisite = true;
        $this->assertSame(array(), Sitepulse_Remote_Management::plugin_links(array()));
    }

    public function test_an_administrator_page_checks_in_after_it_is_sent_when_wp_cron_does_not_run(): void {
        $this->connect();
        // Not late yet.
        wp_schedule_single_event(time() - 300, Sitepulse_Remote_Management::CHECK_IN_HOOK);
        Sitepulse_Remote_Management::check_in_when_late();
        $this->assertFalse(has_action('shutdown', array('Sitepulse_Remote_Management', 'check_in_after_response')));

        // Ten minutes late: once the page is sent, it checks in.
        wp_clear_scheduled_hook(Sitepulse_Remote_Management::CHECK_IN_HOOK);
        wp_schedule_single_event(time() - 700, Sitepulse_Remote_Management::CHECK_IN_HOOK);
        Sitepulse_Remote_Management::check_in_when_late();
        $this->assertNotFalse(has_action('shutdown', array('Sitepulse_Remote_Management', 'check_in_after_response')));
        $this->assertSame(600, Sitepulse_Test_WP::$transient_expirations['sitepulse_remote_late_check_in']);

        $this->checkedIn();
        $finished = Sitepulse_Test_WP::$finished_requests;
        Sitepulse_Remote_Management::check_in_after_response();
        $this->assertSame($finished + 1, Sitepulse_Test_WP::$finished_requests, 'The page was sent first.');
        $this->assertSame(array('check-in'), $this->endpoints());
        $this->assertEqualsWithDelta(time() + 3600, wp_next_scheduled(Sitepulse_Remote_Management::CHECK_IN_HOOK), 2);
    }

    /** @dataProvider requestsThatDoNotCheckInLate */
    public function test_only_late_check_ins_of_administrator_pages_check_in(callable $setup): void {
        $this->connect();
        wp_schedule_single_event(time() - 700, Sitepulse_Remote_Management::CHECK_IN_HOOK);
        $setup();

        Sitepulse_Remote_Management::check_in_when_late();

        $this->assertFalse(has_action('shutdown', array('Sitepulse_Remote_Management', 'check_in_after_response')));
    }

    public static function requestsThatDoNotCheckInLate(): array {
        return array(
            'an AJAX request' => array(static function () { Sitepulse_Test_WP::$doing_ajax = true; }),
            'a cron request' => array(static function () { Sitepulse_Test_WP::$doing_cron = true; }),
            'not an administrator' => array(static function () { Sitepulse_Test_WP::$capabilities['manage_options'] = false; }),
            'a site that cannot connect' => array(static function () { delete_option('sitepulse_license_key'); }),
            'no check-in scheduled' => array(static function () { wp_clear_scheduled_hook(Sitepulse_Remote_Management::CHECK_IN_HOOK); }),
            'another page checks in' => array(static function () { set_transient('sitepulse_remote_late_check_in', 1, 600); }),
        );
    }
}

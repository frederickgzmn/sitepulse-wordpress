<?php
require_once __DIR__ . '/Support/RemoteManagement.php';

/**
 * Connecting the site to a SitePulse account with a connection key, from the remote management settings.
 */
final class RemoteConnectionTest extends Sitepulse_Test_Case {
    private const NONCE = 'nonce-sitepulse_remote_connection';

    protected function setUp(): void {
        parent::setUp();
        Sitepulse_Test_Backups::reset();
        Sitepulse_Test_WP::$capabilities['manage_options'] = true;
        Sitepulse_Test_WP::$referer = 'https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_settings';
        $_REQUEST['_wpnonce'] = self::NONCE;
    }

    protected function tearDown(): void { Sitepulse_Test_Backups::reset(); unset($_POST['sitepulse_connection']); parent::tearDown(); }

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

    private function stored(): array { return get_option(Sitepulse_Remote_Management::CONNECTION_OPTION); }

    private function render(string $context = 'advanced'): string {
        ob_start(); Sitepulse_Remote_Management::render_settings($context); return ob_get_clean();
    }

    public function test_init_registers_the_connection_buttons(): void {
        Sitepulse_Remote_Management::init();
        $this->assertNotFalse(has_action('admin_post_sitepulse_remote_connection', array('Sitepulse_Remote_Management', 'handle_connection_form')));
    }

    public function test_shows_a_connection_key_until_it_is_used_and_then_the_account(): void {
        $this->connect();
        $this->answer(array('key' => 'spc_first_key', 'connectable' => true, 'has_key' => true, 'connections' => array()));
        $this->press('key');

        $this->assertSame('connection/key', sitepulse_test_remote_endpoint(0));
        $this->assertSame('[]', Sitepulse_Test_WP::$http_requests[0]['args']['body']);
        $this->assertSame('spc_first_key', $this->stored()['key']);
        $html = $this->render();
        $this->assertStringContainsString('value="spc_first_key"', $html);
        $this->assertStringContainsString('Create a new key', $html);
        $this->assertStringContainsString('name="action" value="sitepulse_remote_connection"', $html);
        $this->assertStringContainsString('name="_wpnonce" value="nonce-sitepulse_remote_connection"', $html);

        // Still waiting: the key stays.
        $this->answer(array('connectable' => true, 'has_key' => true, 'connections' => array()));
        $this->press('status');
        $this->assertSame('spc_first_key', $this->stored()['key']);

        // The dashboard used the key: it is gone, and the account shows.
        $this->answer(array('connectable' => true, 'has_key' => false, 'connections' => array(
            array('account' => 'o***@agency.example', 'plan' => 'Agency', 'connected_at' => '2026-10-20T10:00:00+00:00'),
            array('account' => null, 'plan' => 'Starter', 'connected_at' => 'not a date'),
            'not a connection',
        )));
        $this->press('status');

        $this->assertSame('connection', sitepulse_test_remote_endpoint(2));
        $this->assertSame('', $this->stored()['key']);
        foreach (array('advanced', 'easy') as $context) {
            $html = $this->render($context);
            $this->assertStringContainsString('Connected to o***@agency.example (Agency plan) since 2026-10-20 10:00.', $html);
            $this->assertStringContainsString('Connected to SitePulse (Starter plan) since .', $html);
            $this->assertStringContainsString('Disconnect from SitePulse accounts', $html);
            $this->assertStringNotContainsString('Show connection key', $html);
            $this->assertStringContainsString('Checked 1 min ago.', $html);
        }
    }

    public function test_disconnects_the_site_from_its_accounts(): void {
        $this->connect();
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('connections' => array(array('account' => 'o***@agency.example', 'plan' => 'Agency', 'connected_at' => ''))));
        $this->answer(array('connectable' => true, 'has_key' => false, 'connections' => array()));

        $this->press('disconnect');

        $this->assertSame('connection/disconnect', sitepulse_test_remote_endpoint(0));
        $this->assertSame(array(), $this->stored()['connections']);
        $this->assertSame(array('This site was disconnected from its SitePulse accounts.'), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
        $this->assertStringContainsString('Show connection key', $this->render());
        $this->assertStringContainsString('Not connected yet.', $this->render());
    }

    public function test_keeps_the_key_and_shows_an_error_when_sitepulse_cannot_be_reached(): void {
        $this->connect();
        update_option(Sitepulse_Remote_Management::CONNECTION_OPTION, array('key' => 'spc_kept'));
        Sitepulse_Test_WP::$http_queue[] = new WP_Error('http_request_failed', 'timeout');

        $this->press('status');

        $this->assertSame('spc_kept', $this->stored()['key']);
        $this->assertStringContainsString('SitePulse could not be reached. Please try again in a moment.', $this->render('easy'));
    }

    public function test_explains_a_pro_site_and_a_site_that_cannot_connect(): void {
        $this->connect();
        $this->answer(array('connectable' => false, 'reason' => 'pro_site', 'has_key' => false, 'connections' => 'none'));
        $this->press('status');
        $this->assertStringContainsString('This site has a SitePulse Pro license, so it already appears in the account that owns the license.', $this->render());

        delete_option('sitepulse_license_key');
        $this->assertStringContainsString('Turn on Cloud Services so this site can connect to your SitePulse account.', $this->render());
        $this->assertFalse(Sitepulse_Remote_Management::view_data()['connection']['available']);
    }

    public function test_sends_nothing_for_an_unknown_button_or_an_unlicensed_site(): void {
        $this->connect();
        $this->press('delete-everything');
        unset($_POST['sitepulse_connection']);
        try { Sitepulse_Remote_Management::handle_connection_form(); } catch (Sitepulse_Test_Redirect $redirect) {}
        delete_option('sitepulse_license_key');
        $this->press('key');

        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
        $this->assertFalse(get_option(Sitepulse_Remote_Management::CONNECTION_OPTION));
    }

    public function test_redirects_to_the_settings_without_a_referer(): void {
        Sitepulse_Test_WP::$referer = false;
        $_POST['sitepulse_connection'] = 'status';
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
}

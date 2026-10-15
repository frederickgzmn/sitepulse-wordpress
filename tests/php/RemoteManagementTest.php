<?php
require_once __DIR__ . '/Support/RemoteManagement.php';
require_once __DIR__ . '/Support/Database.php';

final class RemoteManagementTest extends Sitepulse_Test_Case {
    private const ACTIVE = array('enabled' => true, 'active' => true, 'check_in_seconds' => 300, 'inventory_seconds' => 21600);
    private const IDLE = array('enabled' => false, 'active' => false, 'check_in_seconds' => 3600, 'inventory_seconds' => 21600);

    protected function setUp(): void {
        parent::setUp();
        Sitepulse_Test_Backups::reset();
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
    }

    protected function tearDown(): void { Sitepulse_Test_Backups::reset(); unset($GLOBALS['wpdb']); parent::tearDown(); }

    private function connect(): void { update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE); }

    /** A site the dashboard manages: licensed, with UpdraftPlus, a recent inventory and the given state. */
    private function manage(array $state = array()): void {
        $this->connect();
        Sitepulse_Test_Backups::updraftplus();
        update_option(Sitepulse_Remote_Management::STATE_OPTION, $state + array('active' => true, 'inventory_sent_at' => time(), 'provider' => Sitepulse_Remote_Backups::provider()));
    }

    private function answer(array $remote, array $commands = array()): callable {
        return sitepulse_test_remote_response(array('success' => true, 'remote_management' => $remote, 'commands' => $commands));
    }

    private function queue(...$responses): void { Sitepulse_Test_WP::$http_queue = array_merge(Sitepulse_Test_WP::$http_queue, $responses); }

    private function endpoints(): array { return array_map('sitepulse_test_remote_endpoint', array_keys(Sitepulse_Test_WP::$http_requests)); }

    private function messages(): array { return array_column(Sitepulse_Remote_Management::log_entries(), 'message'); }

    private function nextCheckIn() { return wp_next_scheduled(Sitepulse_Remote_Management::CHECK_IN_HOOK); }

    private function queueEvents(int $count): void {
        $events = array();
        for ($i = 0; $i < $count; $i++) { $events[] = array('id' => sprintf('00000000-0000-4000-8000-%012d', $i), 'type' => 'theme.switched', 'occurred_at' => '2026-10-15T10:00:00Z', 'data' => array()); }
        update_option(Sitepulse_Remote_Management::EVENTS_OPTION, $events);
    }

    public function test_init_registers_the_check_in_settings_and_listeners(): void {
        Sitepulse_Remote_Management::init();
        $this->assertNotFalse(has_action('sitepulse_remote_check_in', array('Sitepulse_Remote_Management', 'check_in')));
        $this->assertNotFalse(has_action('admin_post_sitepulse_remote_management', array('Sitepulse_Remote_Management', 'handle_block_form')));
        $this->assertNotFalse(has_action('sitepulse_settings_after_form', array('Sitepulse_Remote_Management', 'render_settings')));
        $this->assertNotFalse(has_action('activated_plugin', array('Sitepulse_Remote_Events', 'plugin_activated')));
        $this->assertNotFalse(has_action('backwpup_end_job', array('Sitepulse_Remote_Backups', 'backwpup_finished')));
    }

    public function test_dashboard_and_cron_requests_schedule_the_first_check_in_once(): void {
        $this->connect();
        Sitepulse_Remote_Management::init();
        $this->assertEqualsWithDelta(time() + 60, $this->nextCheckIn(), 2);

        Sitepulse_Test_WP::$is_admin = false; Sitepulse_Test_WP::$doing_cron = true;
        Sitepulse_Remote_Management::init();
        $this->assertCount(1, Sitepulse_Test_WP::$scheduled);
    }

    public function test_page_views_do_not_touch_the_schedule(): void {
        $this->connect();
        Sitepulse_Test_WP::$is_admin = false;
        Sitepulse_Remote_Management::init();
        $this->assertFalse($this->nextCheckIn());
    }

    public function test_the_check_in_stops_when_the_site_can_no_longer_connect(): void {
        wp_schedule_single_event(time() + 300, Sitepulse_Remote_Management::CHECK_IN_HOOK);
        Sitepulse_Remote_Management::ensure_schedule();
        $this->assertFalse($this->nextCheckIn());
        Sitepulse_Remote_Management::ensure_schedule();
        $this->assertSame(array(), Sitepulse_Test_WP::$scheduled);
    }

    /** @dataProvider connections */
    public function test_the_site_connects_only_with_a_license_cloud_services_and_a_single_site(callable $configure, bool $expected): void {
        $configure();
        $this->assertSame($expected, Sitepulse_Remote_Management::can_connect());
    }

    public static function connections(): array {
        $license = static function () { update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE); };
        return array(
            'licensed' => array($license, true),
            'no license yet' => array(static function () {}, false),
            'Cloud Services off' => array(static function () use ($license) { $license(); update_option('sitepulse_settings', array('external_api_enabled' => false)); }, false),
            'Cloud Services on' => array(static function () use ($license) { $license(); update_option('sitepulse_settings', array('external_api_enabled' => 1)); }, true),
            'corrupt settings' => array(static function () use ($license) { $license(); update_option('sitepulse_settings', 'corrupt'); }, true),
            'multisite' => array(static function () use ($license) { $license(); Sitepulse_Test_WP::$multisite = true; }, false),
        );
    }

    public function test_check_in_without_a_connection_unschedules_itself(): void {
        wp_schedule_single_event(time(), Sitepulse_Remote_Management::CHECK_IN_HOOK);
        Sitepulse_Remote_Management::check_in();
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
        $this->assertFalse($this->nextCheckIn());
    }

    public function test_idle_site_reports_itself_hourly_and_does_nothing_else(): void {
        $this->connect();
        $this->queue($this->answer(self::IDLE, array(array('id' => '01JABCDEF0123456789ABCDEFG', 'type' => 'refresh_inventory'))));

        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('check-in'), $this->endpoints());
        $this->assertSame(array('schema' => 1, 'plugin_version' => SITEPULSE_VERSION, 'wp_version' => '6.8', 'blocked' => false, 'backup_provider' => null, 'backup_providers' => array()), sitepulse_test_remote_request_body(0));
        $state = Sitepulse_Remote_Management::state();
        $this->assertFalse($state['enabled']);
        $this->assertFalse($state['active']);
        $this->assertEqualsWithDelta(time(), $state['last_check_in'], 2);
        $this->assertSame(array(), $state['done_commands'], 'Commands run only while the dashboard manages the site.');
        $this->assertEqualsWithDelta(time() + 3600, $this->nextCheckIn(), 2);
        $this->assertSame(array(), $this->messages());
    }

    public function test_turning_on_sends_the_inventory_and_checks_in_every_five_minutes(): void {
        $this->connect();
        Sitepulse_Test_Backups::updraftplus();
        $this->queue($this->answer(self::ACTIVE), sitepulse_test_remote_response());

        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('check-in', 'inventory'), $this->endpoints());
        $this->assertSame(array('id' => 'updraftplus', 'version' => '1.26.9', 'supported' => true), sitepulse_test_remote_request_body(0)['backup_provider']);
        $inventory = sitepulse_test_remote_request_body(1);
        $this->assertSame(array('schema', 'collected_at', 'site', 'updates', 'backups'), array_keys($inventory));
        $this->assertSame('updraftplus', $inventory['backups']['provider']['id']);
        $state = Sitepulse_Remote_Management::state();
        $this->assertTrue($state['active']);
        $this->assertEqualsWithDelta(time(), $state['inventory_sent_at'], 2);
        $this->assertEqualsWithDelta(time() + 300, $this->nextCheckIn(), 2);
        $this->assertSame(array('Remote management turned on from the SitePulse dashboard.'), $this->messages());
    }

    /** @dataProvider inventoryTiming */
    public function test_inventory_is_sent_when_due_or_after_a_change(array $state, bool $sent): void {
        $this->manage($state);
        $this->queue($this->answer(self::ACTIVE), sitepulse_test_remote_response());
        if (!$sent) { array_pop(Sitepulse_Test_WP::$http_queue); }

        Sitepulse_Remote_Management::check_in();

        $this->assertSame($sent ? array('check-in', 'inventory') : array('check-in'), $this->endpoints());
        $this->assertFalse(Sitepulse_Remote_Management::state()['inventory_due']);
    }

    public static function inventoryTiming(): array {
        return array(
            'recent' => array(array(), false),
            'six hours old' => array(array('inventory_sent_at' => time() - 21600), true),
            'something changed' => array(array('inventory_due' => true), true),
        );
    }

    public function test_a_failed_inventory_is_retried_at_the_next_check_in(): void {
        $this->manage(array('inventory_due' => true, 'inventory_sent_at' => 5));
        $this->queue($this->answer(self::ACTIVE), sitepulse_test_remote_response(array('success' => false), 413));

        Sitepulse_Remote_Management::check_in();

        $state = Sitepulse_Remote_Management::state();
        $this->assertTrue($state['inventory_due']);
        $this->assertSame(5, $state['inventory_sent_at']);
    }

    public function test_commands_run_once_each_and_report_their_result(): void {
        $this->manage(array('done_commands' => array('01JDONE0000000000000000000')));
        $commands = array(
            array('id' => '01JREFRESH0000000000000000', 'type' => 'refresh_inventory', 'args' => array(), 'expires_at' => gmdate('Y-m-d\TH:i:s\Z', time() + 900)),
            array('id' => '01JREFRESH0000000000000000', 'type' => 'refresh_inventory'),
            array('id' => '01JDONE0000000000000000000', 'type' => 'refresh_inventory'),
            array('id' => '01JUNKNOWN0000000000000000', 'type' => 'delete_everything'),
            array('id' => '01JEXPIRED0000000000000000', 'type' => 'refresh_inventory', 'expires_at' => gmdate('Y-m-d\TH:i:s\Z', time() - 60)),
            array('id' => '01JNOTYPE00000000000000000', 'type' => array('refresh_inventory')),
            array('id' => 42, 'type' => 'refresh_inventory'),
            'not a command',
        );
        $this->queue($this->answer(self::ACTIVE, $commands));
        for ($i = 0; $i < 5; $i++) { $this->queue(sitepulse_test_remote_response()); }

        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('check-in', 'inventory', 'command-result', 'command-result', 'command-result', 'command-result'), $this->endpoints());
        $this->assertSame(array('id' => '01JREFRESH0000000000000000', 'status' => 'done', 'result' => array('inventory_sent' => true), 'error' => null), sitepulse_test_remote_request_body(2));
        $this->assertSame(array('id' => '01JUNKNOWN0000000000000000', 'status' => 'failed', 'result' => null, 'error' => 'unsupported_command'), sitepulse_test_remote_request_body(3));
        $this->assertSame(array('id' => '01JEXPIRED0000000000000000', 'status' => 'failed', 'result' => null, 'error' => 'expired'), sitepulse_test_remote_request_body(4));
        $this->assertSame('unsupported_command', sitepulse_test_remote_request_body(5)['error']);
        $this->assertSame(array('01JDONE0000000000000000000', '01JREFRESH0000000000000000', '01JUNKNOWN0000000000000000', '01JEXPIRED0000000000000000', '01JNOTYPE00000000000000000'), Sitepulse_Remote_Management::state()['done_commands']);
        $this->assertSame(array(
            'Dashboard request done: refresh the site inventory.',
            'Dashboard request failed: delete_everything (unsupported_command).',
            'Dashboard request failed: refresh the site inventory (expired).',
            'Dashboard request failed:  (unsupported_command).',
        ), $this->messages());
    }

    public function test_refresh_fails_when_the_api_refuses_the_inventory(): void {
        $this->manage();
        $this->queue($this->answer(self::ACTIVE, array(array('id' => '01JREFRESH0000000000000000', 'type' => 'refresh_inventory'))), sitepulse_test_remote_response(array('success' => false), 500), sitepulse_test_remote_response());

        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('id' => '01JREFRESH0000000000000000', 'status' => 'failed', 'result' => null, 'error' => 'inventory_failed'), sitepulse_test_remote_request_body(2));
    }

    public function test_a_check_in_runs_at_most_twenty_commands_and_remembers_the_last_fifty(): void {
        $done = array();
        for ($i = 0; $i < 50; $i++) { $done[] = sprintf('01JOLD%020d', $i); }
        $this->manage(array('done_commands' => $done));
        $commands = array();
        for ($i = 0; $i < 25; $i++) { $commands[] = array('id' => sprintf('01JNEW%020d', $i), 'type' => 'unknown'); }
        $this->queue($this->answer(self::ACTIVE, $commands));
        for ($i = 0; $i < 20; $i++) { $this->queue(sitepulse_test_remote_response()); }

        Sitepulse_Remote_Management::check_in();

        $this->assertCount(21, Sitepulse_Test_WP::$http_requests);
        $remembered = Sitepulse_Remote_Management::state()['done_commands'];
        $this->assertCount(50, $remembered);
        $this->assertSame(sprintf('01JOLD%020d', 20), $remembered[0]);
        $this->assertSame(sprintf('01JNEW%020d', 19), $remembered[49]);
    }

    public function test_queued_events_are_delivered_in_batches_of_fifty(): void {
        $this->manage();
        $this->queueEvents(120);
        $this->queue($this->answer(self::ACTIVE), sitepulse_test_remote_response(), sitepulse_test_remote_response(), sitepulse_test_remote_response());

        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('check-in', 'events', 'events', 'events'), $this->endpoints());
        $this->assertSame(array(50, 50, 20), array_map(static function ($index) { return count(sitepulse_test_remote_request_body($index)['events']); }, array(1, 2, 3)));
        $this->assertSame('00000000-0000-4000-8000-000000000000', sitepulse_test_remote_request_body(1)['events'][0]['id']);
        $this->assertSame(array(), get_option(Sitepulse_Remote_Management::EVENTS_OPTION));
    }

    public function test_undelivered_events_wait_and_refused_batches_are_dropped(): void {
        $this->manage();
        $this->queueEvents(60);
        $this->queue($this->answer(self::ACTIVE), sitepulse_test_remote_response(array('success' => false), 422), sitepulse_test_remote_response(array('success' => false), 503));

        Sitepulse_Remote_Management::check_in();

        $left = get_option(Sitepulse_Remote_Management::EVENTS_OPTION);
        $this->assertCount(10, $left, 'The refused batch is dropped; the next one waits for the API to recover.');
        $this->assertSame('00000000-0000-4000-8000-000000000050', $left[0]['id']);
    }

    public function test_a_new_backup_plugin_is_reported_while_managed(): void {
        $this->manage(array('provider' => array('id' => 'backwpup', 'version' => '5.7.8', 'supported' => true)));
        $this->queue($this->answer(self::ACTIVE), sitepulse_test_remote_response(), sitepulse_test_remote_response());

        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('check-in', 'inventory', 'events'), $this->endpoints());
        $event = sitepulse_test_remote_request_body(2)['events'][0];
        $this->assertSame('backup_provider.changed', $event['type']);
        $this->assertSame(array('provider' => array('id' => 'updraftplus', 'version' => '1.26.9', 'supported' => true)), $event['data']);
        $this->assertSame('updraftplus', Sitepulse_Remote_Management::state()['provider']['id']);
    }

    public function test_turning_off_stops_commands_and_is_logged(): void {
        $this->manage();
        $this->queue($this->answer(array('active' => true) + self::IDLE, array(array('id' => '01JREFRESH0000000000000000', 'type' => 'refresh_inventory'))));
        update_option('sitepulse_settings', array('remote_management_blocked' => true));

        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('check-in'), $this->endpoints());
        $this->assertTrue(sitepulse_test_remote_request_body(0)['blocked']);
        $this->assertFalse(Sitepulse_Remote_Management::state()['active'], 'A local block wins over the dashboard.');
        $this->assertSame(array('Remote management turned off.'), $this->messages());
    }

    public function test_api_intervals_are_kept_within_safe_bounds(): void {
        $this->connect();
        $this->queue($this->answer(array('check_in_seconds' => 5, 'inventory_seconds' => 10)));
        Sitepulse_Remote_Management::check_in();
        $state = Sitepulse_Remote_Management::state();
        $this->assertSame(array(60, 3600), array($state['check_in_seconds'], $state['inventory_seconds']));

        $this->queue($this->answer(array('check_in_seconds' => 999999, 'inventory_seconds' => 9999999)));
        Sitepulse_Remote_Management::check_in();
        $state = Sitepulse_Remote_Management::state();
        $this->assertSame(array(86400, 604800), array($state['check_in_seconds'], $state['inventory_seconds']));

        $this->queue(sitepulse_test_remote_response(array('success' => true, 'remote_management' => 'corrupt')));
        Sitepulse_Remote_Management::check_in();
        $state = Sitepulse_Remote_Management::state();
        $this->assertSame(array(3600, 21600), array($state['check_in_seconds'], $state['inventory_seconds']));
    }

    public function test_failed_check_ins_are_logged_once_and_retried(): void {
        $this->connect();
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('check_in_seconds' => 300));
        $this->queue(sitepulse_test_remote_response(array('success' => false), 401), sitepulse_test_remote_response(array('success' => false), 401), sitepulse_test_remote_response(array('success' => false), 403), new WP_Error('http_request_failed', 'cURL error 6: could not resolve host'), $this->answer(self::IDLE));

        Sitepulse_Remote_Management::check_in();
        $this->assertEqualsWithDelta(time() + 300, $this->nextCheckIn(), 2);
        $this->assertSame('http_401', Sitepulse_Remote_Management::state()['last_error']);
        Sitepulse_Remote_Management::check_in();
        Sitepulse_Remote_Management::check_in();
        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array(
            'Check-in with SitePulse failed: SitePulse did not accept the signature of this site. Check that the server clock is correct and that the site address has not changed.',
            'Check-in with SitePulse failed: The SitePulse license of this site is not active.',
            'Check-in with SitePulse failed: cURL error 6: could not resolve host',
        ), $this->messages());

        Sitepulse_Remote_Management::check_in();
        $this->assertSame('', Sitepulse_Remote_Management::state()['last_error']);
    }

    public function test_events_are_recorded_only_while_managed_and_the_queue_is_bounded(): void {
        Sitepulse_Remote_Management::record_event('theme.switched', array('stylesheet' => 'astra'));
        Sitepulse_Remote_Management::inventory_changed();
        $this->assertSame(array(), get_option(Sitepulse_Remote_Management::EVENTS_OPTION, array()));
        $this->assertFalse(Sitepulse_Remote_Management::state()['inventory_due']);

        $this->manage();
        $this->queueEvents(200);
        Sitepulse_Remote_Management::record_event('theme.switched', array('stylesheet' => 'astra'));

        $events = get_option(Sitepulse_Remote_Management::EVENTS_OPTION);
        $this->assertCount(200, $events);
        $this->assertSame('00000000-0000-4000-8000-000000000001', $events[0]['id'], 'The oldest event is dropped first.');
        $this->assertSame(array('stylesheet' => 'astra'), $events[199]['data']);
        $this->assertTrue(Sitepulse_Remote_Management::state()['inventory_due']);

        update_option('sitepulse_settings', array('remote_management_blocked' => true));
        $this->assertFalse(Sitepulse_Remote_Management::is_active(), 'Blocking stops recording at once.');
    }

    public function test_stored_state_events_and_log_survive_corruption(): void {
        update_option(Sitepulse_Remote_Management::STATE_OPTION, 'corrupt');
        $this->assertSame(Sitepulse_Remote_Management::STATE_DEFAULTS, Sitepulse_Remote_Management::state());
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => 1, 'done_commands' => 'corrupt', 'unknown' => true));
        $state = Sitepulse_Remote_Management::state();
        $this->assertTrue($state['active']);
        $this->assertSame(array(), $state['done_commands']);
        $this->assertArrayNotHasKey('unknown', $state);

        update_option(Sitepulse_Remote_Management::LOG_OPTION, array('corrupt', array('time' => 1), array('time' => 1760544000, 'message' => 'Kept')));
        $this->assertSame(array(array('time' => 1760544000, 'message' => 'Kept')), Sitepulse_Remote_Management::log_entries());
        update_option(Sitepulse_Remote_Management::LOG_OPTION, 'corrupt');
        $this->assertSame(array(), Sitepulse_Remote_Management::log_entries());

        $this->manage();
        update_option(Sitepulse_Remote_Management::EVENTS_OPTION, 'corrupt');
        Sitepulse_Remote_Management::record_event('theme.switched', array());
        $this->assertCount(1, get_option(Sitepulse_Remote_Management::EVENTS_OPTION));
    }

    public function test_the_activity_log_keeps_the_last_hundred_entries(): void {
        $this->connect();
        for ($i = 0; $i < 105; $i++) {
            $this->queue(sitepulse_test_remote_response(array('success' => false), $i % 2 ? 401 : 403));
            Sitepulse_Remote_Management::check_in();
        }
        $this->assertCount(100, Sitepulse_Remote_Management::log_entries());
    }

    public function test_block_switch_saves_logs_and_tells_the_dashboard_at_once(): void {
        $this->connect();
        Sitepulse_Test_WP::$referer = 'https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_settings';
        $_REQUEST['_wpnonce'] = 'nonce-sitepulse_remote_management';
        $_POST['sitepulse_remote_blocked'] = '1';

        try { Sitepulse_Remote_Management::handle_block_form(); $this->fail('Expected a redirect.'); }
        catch (Sitepulse_Test_Redirect $redirect) { $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_settings', $redirect->getMessage()); }

        $this->assertTrue(Sitepulse_Remote_Management::is_blocked());
        $this->assertTrue(get_option('sitepulse_settings')['remote_management_blocked']);
        $this->assertEqualsWithDelta(time(), $this->nextCheckIn(), 2);
        $this->assertCount(1, Sitepulse_Test_WP::$scheduled);
        $this->assertSame(array('Remote management blocked on this site.'), $this->messages());

        unset($_POST['sitepulse_remote_blocked']);
        Sitepulse_Test_WP::$referer = false;
        try { Sitepulse_Remote_Management::handle_block_form(); $this->fail('Expected a redirect.'); }
        catch (Sitepulse_Test_Redirect $redirect) { $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_settings', $redirect->getMessage()); }
        $this->assertFalse(Sitepulse_Remote_Management::is_blocked());
        $this->assertSame('Remote management unblocked on this site.', $this->messages()[1]);
    }

    public function test_saving_an_unchanged_switch_changes_nothing(): void {
        $_REQUEST['_wpnonce'] = 'nonce-sitepulse_remote_management';
        try { Sitepulse_Remote_Management::handle_block_form(); } catch (Sitepulse_Test_Redirect $redirect) {}
        $this->assertSame(array(), $this->messages());

        $_POST['sitepulse_remote_blocked'] = '1';
        try { Sitepulse_Remote_Management::handle_block_form(); } catch (Sitepulse_Test_Redirect $redirect) {}
        $this->assertTrue(Sitepulse_Remote_Management::is_blocked());
        $this->assertSame(array(), Sitepulse_Test_WP::$scheduled, 'A site that cannot connect has nobody to tell.');
    }

    public function test_block_switch_rejects_users_without_permission_and_forged_requests(): void {
        $_POST['sitepulse_remote_blocked'] = '1';
        Sitepulse_Test_WP::$capabilities['manage_options'] = false; $_REQUEST['_wpnonce'] = 'nonce-sitepulse_remote_management';
        try { Sitepulse_Remote_Management::handle_block_form(); $this->fail('Expected wp_die.'); } catch (Sitepulse_Test_Die $die) { $this->assertStringContainsString('sufficient permissions', $die->getMessage()); }
        Sitepulse_Test_WP::$capabilities['manage_options'] = true; $_REQUEST['_wpnonce'] = 'forged';
        try { Sitepulse_Remote_Management::handle_block_form(); $this->fail('Expected wp_die.'); } catch (Sitepulse_Test_Die $die) { $this->assertStringContainsString('expired', $die->getMessage()); }
        $this->assertFalse(Sitepulse_Remote_Management::is_blocked());
    }

    /** @dataProvider statuses */
    public function test_settings_explain_whether_remote_management_works_and_what_is_missing(callable $configure, string $status, string $tone, string $message): void {
        $configure($this);
        $view = Sitepulse_Remote_Management::view_data();
        $this->assertSame(array($status, $tone, $message), array($view['status'], $view['tone'], $view['message']));
    }

    public static function statuses(): array {
        $licensed = static function () { update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE); };
        $ready = static function (array $state = array()) use ($licensed) { $licensed(); Sitepulse_Test_Backups::updraftplus(); update_option(Sitepulse_Remote_Management::STATE_OPTION, $state); };
        return array(
            'multisite' => array(static function () { Sitepulse_Test_WP::$multisite = true; }, 'unavailable', 'neutral', 'Remote management is not available on multisite networks yet.'),
            'Cloud Services off' => array(static function () { update_option('sitepulse_settings', array('external_api_enabled' => false)); }, 'unavailable', 'neutral', 'Turn on PageSpeed & AI Reports (Cloud Services) to use remote management.'),
            'no license' => array(static function () {}, 'unavailable', 'neutral', 'Remote management starts when this site receives its SitePulse license with its first site report.'),
            'blocked' => array(static function () use ($licensed) { $licensed(); update_option('sitepulse_settings', array('remote_management_blocked' => true)); }, 'blocked', 'warning', 'Blocked: the SitePulse dashboard cannot see or manage the backups and updates of this site.'),
            'no backup plugin' => array($licensed, 'off', 'neutral', 'Install and activate UpdraftPlus or BackWPup to manage this site from the SitePulse dashboard.'),
            'old UpdraftPlus' => array(static function () use ($licensed) { $licensed(); Sitepulse_Test_Backups::updraftplus('1.25.8'); }, 'off', 'neutral', 'Update UpdraftPlus to version 1.26 or later to use remote management.'),
            'BackWPup 4' => array(static function () use ($licensed) { $licensed(); Sitepulse_Test_Backups::$backwpup_version = '4.1.7'; }, 'off', 'neutral', 'Remote management works with BackWPup 5.'),
            'failed check-in' => array(static function () use ($ready) { $ready(array('last_error' => 'http_500', 'last_check_in' => 5, 'active' => true)); }, 'error', 'danger', 'The last check-in with SitePulse failed. See the activity below.'),
            'never checked in' => array(static function () use ($ready) { $ready(); }, 'off', 'neutral', 'Connecting to SitePulse…'),
            'active' => array(static function () use ($ready) { $ready(array('last_check_in' => 5, 'active' => true)); }, 'active', 'success', 'Active: the SitePulse dashboard can manage the backups and updates of this site.'),
            'switched on, waiting' => array(static function () use ($ready) { $ready(array('last_check_in' => 5, 'enabled' => true)); }, 'off', 'neutral', 'Turning on at the next check-in.'),
            'switched off' => array(static function () use ($ready) { $ready(array('last_check_in' => 5)); }, 'off', 'neutral', 'Off. Turn it on for this site in your SitePulse dashboard.'),
        );
    }

    public function test_settings_view_data_formats_the_provider_check_in_and_newest_activity_first(): void {
        $this->connect();
        Sitepulse_Test_Backups::$backwpup_version = '5.7.8';
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('last_check_in' => time() - 600, 'active' => true));
        $log = array();
        for ($i = 0; $i < 25; $i++) { $log[] = array('time' => 1760544000 + $i * 60, 'message' => 'Entry ' . $i); }
        update_option(Sitepulse_Remote_Management::LOG_OPTION, $log);

        $view = Sitepulse_Remote_Management::view_data();

        $this->assertSame('Active', $view['label']);
        $this->assertSame('BackWPup 5.7.8', $view['provider']);
        $this->assertSame('10 mins ago', $view['last_check_in']);
        $this->assertCount(20, $view['log']);
        $this->assertSame(array('when' => '2025-10-15 16:24', 'message' => 'Entry 24'), $view['log'][0]);
        $this->assertSame('Entry 5', $view['log'][19]['message']);
        $this->assertSame('https://example.test/wp-admin/admin-post.php', $view['action']);

        update_option(Sitepulse_Remote_Management::STATE_OPTION, array());
        Sitepulse_Test_Backups::$backwpup_version = '';
        $view = Sitepulse_Remote_Management::view_data();
        $this->assertSame(array('None', 'Never'), array($view['provider'], $view['last_check_in']));
    }

    /** @dataProvider dashboards */
    public function test_settings_render_on_both_dashboards(string $context, string $badge, string $toggle): void {
        $this->connect();
        Sitepulse_Test_Backups::updraftplus();
        update_option('sitepulse_settings', array('remote_management_blocked' => true));
        update_option(Sitepulse_Remote_Management::LOG_OPTION, array(array('time' => 1760544000, 'message' => 'Blocked <b>here</b>')));

        ob_start();
        do_action('sitepulse_settings_after_form', $context);
        $this->assertSame('', ob_get_clean(), 'Nothing renders before init() hooks the settings.');

        Sitepulse_Remote_Management::init();
        ob_start();
        do_action('sitepulse_settings_after_form', $context);
        $html = ob_get_clean();

        $this->assertStringContainsString('id="sitepulse-remote-management"', $html);
        $this->assertStringContainsString($badge, $html);
        $this->assertStringContainsString('UpdraftPlus 1.26.9', $html);
        $this->assertStringContainsString('action="https://example.test/wp-admin/admin-post.php"', $html);
        $this->assertStringContainsString('name="action" value="sitepulse_remote_management"', $html);
        $this->assertStringContainsString('name="_wpnonce" value="nonce-sitepulse_remote_management"', $html);
        $this->assertMatchesRegularExpression('/name="sitepulse_remote_blocked"[^>]*checked="checked"/', $html);
        $this->assertStringContainsString($toggle, $html);
        $this->assertStringContainsString('Blocked &lt;b&gt;here&lt;/b&gt;', $html);
        $this->assertStringContainsString('It never sends backup files, passwords, storage credentials or site content.', $html);
    }

    public static function dashboards(): array {
        return array(
            'Advanced' => array('advanced', '<span class="badge bg-warning text-dark">Blocked</span>', 'Block remote management on this site'),
            'Simple' => array('easy', '<span class="sp-badge sp-badge-warning">Blocked</span>', 'Block Remote Management'),
        );
    }

    public function test_settings_hide_the_activity_without_entries_and_from_users_who_cannot_manage_the_site(): void {
        foreach (array('advanced', 'easy') as $context) {
            ob_start(); Sitepulse_Remote_Management::render_settings($context); $html = ob_get_clean();
            $this->assertStringNotContainsString('<details', $html);
            $this->assertStringContainsString('Unavailable', $html);
            $this->assertDoesNotMatchRegularExpression('/name="sitepulse_remote_blocked"[^>]*checked/', $html);
        }

        Sitepulse_Test_WP::$capabilities['manage_options'] = false;
        ob_start(); Sitepulse_Remote_Management::render_settings(); $this->assertSame('', ob_get_clean());
    }
}

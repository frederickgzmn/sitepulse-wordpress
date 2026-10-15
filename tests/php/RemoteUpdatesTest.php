<?php
require_once __DIR__ . '/Support/RemoteManagement.php';
require_once __DIR__ . '/Support/Database.php';

final class RemoteUpdatesTest extends Sitepulse_Test_Case {
    private $files = array();

    protected function setUp(): void {
        parent::setUp();
        Sitepulse_Test_Backups::reset();
        Sitepulse_Test_Upgrades::reset();
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
        update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
        $this->core('7.1.2');
        // The pause for PHP's opcode cache is checked once, in its own test.
        add_filter('sitepulse_remote_health_check_delay', '__return_zero');
    }

    protected function tearDown(): void {
        foreach (array_reverse($this->files) as $path) { if (is_dir($path)) { rmdir($path); } elseif (is_file($path)) { unlink($path); } }
        Sitepulse_Test_Backups::reset();
        unset($GLOBALS['wpdb']);
        parent::tearDown();
    }

    /** A site the dashboard manages, so events are recorded. */
    private function connect(): void {
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true));
    }

    private function plugin(string $file, string $version): void {
        Sitepulse_Test_WP::$plugins[$file] = array('Name' => ucfirst(dirname($file)), 'Version' => $version);
        $path = WP_PLUGIN_DIR . '/' . $file;
        if (!is_dir(dirname($path))) { mkdir(dirname($path)); $this->files[] = dirname($path); }
        if (!is_file($path)) { touch($path); $this->files[] = $path; }
    }

    private function theme(string $stylesheet, string $version): void {
        $directory = get_theme_root() . '/' . $stylesheet;
        if (!is_dir($directory)) { mkdir($directory); $this->files[] = $directory; $this->files[] = $directory . '/style.css'; }
        file_put_contents($directory . '/style.css', "/*\nTheme Name: $stylesheet\nVersion: $version\n*/");
    }

    private function core(string $version): void {
        if (!is_dir(ABSPATH . WPINC)) { mkdir(ABSPATH . WPINC); }
        file_put_contents(ABSPATH . WPINC . '/version.php', "<?php\n\$wp_version = '$version';\n");
    }

    private function offer(string $kind, string $id, string $version, array $extra = array()): void {
        $transient = Sitepulse_Test_WP::$site_transients['update_' . $kind . 's'] ?? (object) array('response' => array());
        $offer = array_merge(array('new_version' => $version, 'package' => "https://downloads.wordpress.org/$kind/$id.$version.zip"), $extra);
        $transient->response[$id] = 'plugin' === $kind ? (object) $offer : $offer;
        Sitepulse_Test_WP::$site_transients['update_' . $kind . 's'] = $transient;
    }

    private function start(string $type, array $args, string $id = '01JUPDATE00000000000000000'): ?array {
        return Sitepulse_Remote_Updates::run_command($id, $type, $args);
    }

    private function job(): ?array { return get_option(Sitepulse_Remote_Updates::JOB_OPTION, null); }

    private function results(): array {
        foreach (Sitepulse_Test_WP::$http_requests as $request) {
            if (false !== strpos($request['url'], '/remote/command-result')) { return json_decode($request['args']['body'], true); }
        }
        $this->fail('No command result was sent.');
    }

    private function pageChecks(): array {
        return array_values(array_map(static function ($request) { return strtok($request['url'], '?'); }, array_filter(Sitepulse_Test_WP::$http_requests, static function ($request) { return 0 === strpos($request['url'], home_url('/')); })));
    }

    private function upgrades(): array {
        return array_values(array_filter(Sitepulse_Test_Upgrades::$calls, static function ($call) { return 'upgrade' === $call[0]; }));
    }

    private function events(): array { return get_option(Sitepulse_Remote_Management::EVENTS_OPTION, array()); }

    private function api(int $responses = 1): void {
        for ($i = 0; $i < $responses; $i++) { Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response(); }
    }

    /** @dataProvider invalidCommands */
    public function test_invalid_update_commands_are_refused(string $type, array $args): void {
        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'invalid_arguments'), $this->start($type, $args));
        $this->assertNull($this->job());
    }

    public static function invalidCommands(): array {
        return array(
            'no plugins' => array('update_plugins', array()),
            'plugins not a list' => array('update_plugins', array('plugins' => 'seo/seo.php')),
            'empty plugin' => array('update_plugins', array('plugins' => array(''))),
            'plugin not text' => array('update_plugins', array('plugins' => array(7))),
            'too many themes' => array('update_themes', array('themes' => array_map(static function ($i) { return "t$i"; }, range(1, 51)))),
            'backup wait too short' => array('update_themes', array('themes' => array('astra'), 'backup_wait' => 4)),
            'backup wait as text' => array('update_themes', array('themes' => array('astra'), 'backup_wait' => '60')),
            'no version' => array('update_core', array()),
            'unknown kind' => array('set_auto_updates', array('kind' => 'core', 'items' => array('x'), 'enabled' => true)),
            'no items' => array('set_auto_updates', array('kind' => 'plugin', 'items' => array(), 'enabled' => true)),
            'switch not a boolean' => array('set_auto_updates', array('kind' => 'plugin', 'items' => array('x'), 'enabled' => 'yes')),
        );
    }

    public function test_an_update_starts_in_cron_and_is_answered_when_done(): void {
        $this->assertNull($this->start('update_plugins', array('plugins' => array('seo/seo.php', 'seo/seo.php', 'forms/forms.php'))));

        $job = $this->job();
        $this->assertSame(array('01JUPDATE00000000000000000', 'update_plugins', 'plugin', array('seo/seo.php', 'forms/forms.php'), true, 60, 'backup'), array($job['command'], $job['type'], $job['kind'], $job['items'], $job['backup_first'], $job['backup_wait'], $job['phase']));
        $this->assertEqualsWithDelta(time(), wp_next_scheduled(Sitepulse_Remote_Updates::JOB_HOOK), 2);
        $this->assertSame(array('command' => '01JUPDATE00000000000000000', 'kind' => 'plugin', 'phase' => 'backup', 'done' => 0, 'total' => 2, 'started_at' => gmdate('Y-m-d\TH:i:s\Z', $job['started_at'])), Sitepulse_Remote_Updates::job_status());
        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'update_running'), $this->start('update_core', array('version' => '7.1.3'), '01JOTHER000000000000000000'));

        delete_option(Sitepulse_Remote_Updates::JOB_OPTION);
        $this->start('update_core', array('version' => '7.1.3', 'backup_first' => false));
        $this->assertSame('baseline', $this->job()['phase']);
    }

    /** @dataProvider unwritableSites */
    public function test_sites_where_wordpress_cannot_write_files_are_not_updated(callable $setup): void {
        $setup();
        $this->assertSame('cannot_update', $this->start('update_plugins', array('plugins' => array('seo/seo.php')))['error']);
        $this->assertNull($this->job());
    }

    public static function unwritableSites(): array {
        return array(
            'file changes disallowed' => array(static function () { add_filter('file_mod_allowed', '__return_false'); }),
            'FTP credentials needed' => array(static function () { Sitepulse_Test_WP::$filesystem_method = 'ftpext'; }),
        );
    }

    public function test_a_lost_update_is_rescheduled_from_the_dashboard_or_cron(): void {
        Sitepulse_Remote_Updates::init();
        $this->assertFalse(wp_next_scheduled(Sitepulse_Remote_Updates::JOB_HOOK));
        $this->start('update_plugins', array('plugins' => array('seo/seo.php')));
        Sitepulse_Remote_Updates::unschedule();

        Sitepulse_Test_WP::$is_admin = false;
        Sitepulse_Remote_Updates::init();
        $this->assertFalse(wp_next_scheduled(Sitepulse_Remote_Updates::JOB_HOOK), 'Page views do not check.');
        Sitepulse_Test_WP::$doing_cron = true;
        Sitepulse_Remote_Updates::init();
        $this->assertNotFalse(wp_next_scheduled(Sitepulse_Remote_Updates::JOB_HOOK));
        $this->assertNotFalse(has_action(Sitepulse_Remote_Updates::JOB_HOOK, array('Sitepulse_Remote_Updates', 'run_job')));
    }

    public function test_plugins_update_one_at_a_time_after_a_healthy_baseline(): void {
        $this->connect();
        Sitepulse_Remote_Events::init();
        $this->plugin('seo/seo.php', '1.0');
        $this->plugin('forms/forms.php', '2.0');
        $this->plugin('maps/maps.php', '3.0');
        $this->offer('plugin', 'seo/seo.php', '1.1');
        $this->offer('plugin', 'maps/maps.php', '3.1');
        Sitepulse_Test_Upgrades::$outcomes['plugin:seo/seo.php'] = function () {
            $this->plugin('seo/seo.php', '1.1');
            do_action('upgrader_process_complete', new stdClass(), array('action' => 'update', 'type' => 'plugin', 'plugin' => 'seo/seo.php'));
            return true;
        };
        Sitepulse_Test_Upgrades::$outcomes['plugin:maps/maps.php'] = function () {
            // WordPress keeps active plugins active only in background updates.
            $this->assertTrue(wp_doing_cron());
            $this->plugin('maps/maps.php', '3.1');
            return true;
        };
        $this->start('update_plugins', array('plugins' => array('seo/seo.php', 'forms/forms.php', 'maps/maps.php'), 'backup_first' => false));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $this->assertSame(array('id' => '01JUPDATE00000000000000000', 'status' => 'done', 'result' => array('backup' => null, 'items' => array(
            array('id' => 'seo/seo.php', 'from' => '1.0', 'to' => '1.1', 'ok' => true, 'error' => null, 'rolled_back' => false),
            array('id' => 'forms/forms.php', 'from' => '2.0', 'to' => '2.0', 'ok' => true, 'error' => 'up_to_date', 'rolled_back' => false),
            array('id' => 'maps/maps.php', 'from' => '3.0', 'to' => '3.1', 'ok' => true, 'error' => null, 'rolled_back' => false),
        )), 'error' => null), $this->results(), 'The offers stay available for every item of the batch.');
        $this->assertSame(array_fill(0, 3, 'https://example.test/'), array_values(array_filter($this->pageChecks(), static function ($url) { return 'https://example.test/' === $url; })), 'A baseline, then a check after each update.');
        $this->assertSame(array(array('maintenance', true), array('upgrade', 'plugin', 'seo/seo.php'), array('maintenance', false), array('maintenance', true), array('upgrade', 'plugin', 'maps/maps.php'), array('maintenance', false)), Sitepulse_Test_Upgrades::$calls);
        $this->assertSame(array(array('plugins', 'plugins'), array('update_plugins', 'fetched')), Sitepulse_Test_WP::$cache_deletes, 'WordPress offers updates afresh at the end.');
        $this->assertNull($this->job());
        $this->assertFalse(wp_next_scheduled(Sitepulse_Remote_Updates::JOB_HOOK));

        $events = $this->events();
        $this->assertCount(1, $events, 'WordPress\'s own update event is replaced by SitePulse\'s.');
        $this->assertSame(array('kind' => 'plugin', 'initiator' => 'sitepulse', 'items' => array(array('id' => 'seo/seo.php', 'from' => '1.0', 'to' => '1.1', 'ok' => true, 'rolled_back' => false), array('id' => 'maps/maps.php', 'from' => '3.0', 'to' => '3.1', 'ok' => true, 'rolled_back' => false))), $events[0]['data']);
        $this->assertFalse(Sitepulse_Remote_Events::$updating);
        $this->assertFalse(wp_doing_cron());
        $this->assertSame(array('Dashboard request done: update 3 plugins.'), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
        $this->assertTrue(Sitepulse_Remote_Management::state()['inventory_due']);
        $this->assertSame(array('plugin:seo/seo.php', 'plugin:maps/maps.php'), array_keys(get_option(Sitepulse_Remote_Activity::OPTION)['watches']), 'Each updated plugin\'s effect on page speed is measured.');
    }

    public function test_an_update_that_breaks_a_page_is_rolled_back_and_stops_the_rest(): void {
        $this->connect();
        $this->plugin('seo/seo.php', '1.0');
        $this->plugin('forms/forms.php', '2.0');
        $this->offer('plugin', 'seo/seo.php', '1.1');
        $this->offer('plugin', 'forms/forms.php', '2.1');
        Sitepulse_Test_Upgrades::$outcomes['plugin:seo/seo.php'] = function () {
            $this->plugin('seo/seo.php', '1.1');
            Sitepulse_Test_Upgrades::$pages[home_url('/')] = array('fatal' => "Uncaught Error: Call to undefined function seo_render() in wp-content/plugins/seo/seo.php:4\nStack trace:\n#0 {main}\n  thrown");
            // The same error, as SitePulse's error handler logged it: reported once.
            $GLOBALS['wpdb']->var = serialize(array('errors' => array(array('is_fatal' => true, 'timestamp' => (int) current_time('timestamp'), 'file' => WP_PLUGIN_DIR . '/seo/seo.php', 'message' => 'Uncaught Error: Call to undefined function seo_render()'))));
            return true;
        };
        Sitepulse_Test_Upgrades::$on_restore = function () {
            $this->plugin('seo/seo.php', '1.0');
            Sitepulse_Test_Upgrades::$pages = array();
        };
        $this->start('update_plugins', array('plugins' => array('seo/seo.php', 'forms/forms.php'), 'backup_first' => false));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $result = $this->results();
        $this->assertSame(array('failed', 'update_failed'), array($result['status'], $result['error']));
        $this->assertSame(array(
            array('id' => 'seo/seo.php', 'from' => '1.0', 'to' => '1.0', 'ok' => false, 'error' => 'health_check_failed', 'rolled_back' => true, 'problems' => array('https://example.test/: Uncaught Error: Call to undefined function seo_render() in wp-content/plugins/seo/seo.php:4')),
            array('id' => 'forms/forms.php', 'from' => null, 'to' => null, 'ok' => false, 'error' => 'skipped', 'rolled_back' => false),
        ), $result['result']['items']);
        $backup = array(array('dir' => 'plugins', 'slug' => 'seo', 'src' => WP_PLUGIN_DIR));
        $this->assertSame(array(array('restore', $backup), array('delete_temp_backup', $backup)), array_slice(Sitepulse_Test_Upgrades::$calls, 3));
        $this->assertCount(1, $this->upgrades());
        $this->assertContains(array('plugins', 'plugins'), Sitepulse_Test_WP::$cache_deletes);
        $this->assertSame(array('id' => 'seo/seo.php', 'from' => '1.0', 'to' => '1.0', 'ok' => false, 'rolled_back' => true), $this->events()[0]['data']['items'][0]);
        $this->assertFalse(get_option(Sitepulse_Remote_Activity::OPTION), 'A rolled-back update is not measured.');
        $this->assertSame(array('Dashboard request failed: update 2 plugins (update_failed).'), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
    }

    public function test_an_update_after_which_a_page_dies_silently_is_rolled_back(): void {
        $this->plugin('seo/seo.php', '1.0');
        $this->offer('plugin', 'seo/seo.php', '1.1');
        Sitepulse_Test_Upgrades::$outcomes['plugin:seo/seo.php'] = function () {
            $this->plugin('seo/seo.php', '1.1');
            Sitepulse_Test_Upgrades::$pages[home_url('/')] = array('silent' => true);
            return true;
        };
        Sitepulse_Test_Upgrades::$on_restore = function () { $this->plugin('seo/seo.php', '1.0'); };
        $this->start('update_plugins', array('plugins' => array('seo/seo.php'), 'backup_first' => false));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $item = $this->results()['result']['items'][0];
        $this->assertSame(array('health_check_failed', true, array('https://example.test/: stopped without finishing')), array($item['error'], $item['rolled_back'], $item['problems']));
    }

    public function test_a_theme_that_cannot_be_restored_stays_on_the_new_version(): void {
        $this->theme('astra', '4.0');
        $this->offer('theme', 'astra', '4.1');
        Sitepulse_Test_Upgrades::$outcomes['theme:astra'] = function () {
            $this->theme('astra', '4.1');
            Sitepulse_Test_Upgrades::$pages[admin_url()] = array('status' => 500);
            return true;
        };
        Sitepulse_Test_Upgrades::$restore = new WP_Error('fs_temp_backup_delete', 'Could not restore');
        $this->start('update_themes', array('themes' => array('astra'), 'backup_first' => false));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $this->assertSame(array('id' => 'astra', 'from' => '4.0', 'to' => '4.1', 'ok' => false, 'error' => 'health_check_failed', 'rolled_back' => false, 'problems' => array('https://example.test/wp-admin/: HTTP 500')), $this->results()['result']['items'][0]);
        $this->assertSame(array('restore', array(array('dir' => 'themes', 'slug' => 'astra', 'src' => get_theme_root()))), Sitepulse_Test_Upgrades::$calls[3]);
        $this->assertCount(4, Sitepulse_Test_Upgrades::$calls, 'Nothing is deleted when the restore failed.');
        $this->assertSame(array(array('themes', 'themes'), array('update_themes', 'fetched')), Sitepulse_Test_WP::$cache_deletes);
    }

    public function test_fatal_errors_logged_in_the_updated_code_fail_the_update(): void {
        $this->plugin('hello.php', '1.6');
        $this->offer('plugin', 'hello.php', '1.7');
        Sitepulse_Test_Upgrades::$outcomes['plugin:hello.php'] = function () {
            $this->plugin('hello.php', '1.7');
            $now = (int) current_time('timestamp');
            // The checked pages wrote the log in their own requests; this request holds an older copy.
            $GLOBALS['wpdb']->var = serialize(array('errors' => array(
                array('is_fatal' => true, 'timestamp' => $now, 'file' => WP_PLUGIN_DIR . '/hello.php', 'message' => "Maximum execution time exceeded\nStack trace:\n#0 {main}"),
                array('is_fatal' => false, 'timestamp' => $now, 'file' => WP_PLUGIN_DIR . '/hello.php', 'message' => 'A warning'),
                array('is_fatal' => true, 'timestamp' => $now - 60, 'file' => WP_PLUGIN_DIR . '/hello.php', 'message' => 'Earlier'),
                array('is_fatal' => true, 'timestamp' => $now, 'file' => WP_PLUGIN_DIR . '/other/other.php', 'message' => 'Elsewhere'),
                'corrupt',
            )));
            return true;
        };
        update_option('sitepulse_error_log', array('errors' => array()));
        Sitepulse_Test_Upgrades::$restore = false;
        $this->start('update_plugins', array('plugins' => array('hello.php'), 'backup_first' => false));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $item = $this->results()['result']['items'][0];
        $this->assertSame(array('health_check_failed', array('Maximum execution time exceeded')), array($item['error'], $item['problems']));
        $this->assertContains("SELECT option_value FROM wp_options WHERE option_name = 'sitepulse_error_log'", $GLOBALS['wpdb']->queries);
        $this->assertSame(array('restore', array(array('dir' => 'plugins', 'slug' => '.', 'src' => WP_PLUGIN_DIR))), Sitepulse_Test_Upgrades::$calls[3], 'Single-file plugins are restored as WordPress backed them up.');
    }

    public function test_the_site_is_checked_after_php_can_see_the_new_files(): void {
        remove_filter('sitepulse_remote_health_check_delay', '__return_zero');
        add_filter('sitepulse_remote_health_check_delay', static function ($seconds) { return $seconds - 1; });
        $this->plugin('seo/seo.php', '1.0');
        $this->offer('plugin', 'seo/seo.php', '1.1');
        Sitepulse_Test_Upgrades::$outcomes['plugin:seo/seo.php'] = function () { $this->plugin('seo/seo.php', '1.1'); return true; };
        $this->start('update_plugins', array('plugins' => array('seo/seo.php'), 'backup_first' => false));
        $this->api();

        $started = microtime(true);
        Sitepulse_Remote_Updates::run_job();

        $this->assertGreaterThanOrEqual(1.0, microtime(true) - $started, 'WordPress waits 2 seconds; the filter can change it.');
        $this->assertSame('done', $this->results()['status']);
    }

    public function test_an_update_wordpress_could_not_install_stops_the_rest(): void {
        $this->plugin('seo/seo.php', '1.0');
        $this->plugin('forms/forms.php', '2.0');
        $this->offer('plugin', 'seo/seo.php', '1.1');
        $this->offer('plugin', 'forms/forms.php', '2.1');
        Sitepulse_Test_Upgrades::$outcomes['plugin:seo/seo.php'] = static function () { return new WP_Error('download_failed', 'Download failed.'); };
        $this->start('update_plugins', array('plugins' => array('seo/seo.php', 'forms/forms.php'), 'backup_first' => false));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $this->assertSame(array(array('install_failed', '1.0'), array('skipped', null)), array_map(static function ($item) { return array($item['error'], $item['to']); }, $this->results()['result']['items']));
        $this->assertSame(array('https://example.test/', 'https://example.test/wp-admin/'), $this->pageChecks(), 'No check after a failed install.');
    }

    public function test_items_that_cannot_be_updated_do_not_stop_the_others(): void {
        $this->plugin('seo/seo.php', '1.0');
        $this->plugin('modern/modern.php', '1.0');
        $this->plugin('premium/premium.php', '1.0');
        $this->plugin('new-wp/new-wp.php', '1.0');
        $this->offer('plugin', 'seo/seo.php', '1.1');
        $this->offer('plugin', 'modern/modern.php', '2.0', array('requires_php' => '99.0'));
        $this->offer('plugin', 'premium/premium.php', '1.1', array('package' => ''));
        $this->offer('plugin', 'new-wp/new-wp.php', '1.1', array('requires' => '99.0'));
        Sitepulse_Test_Upgrades::$outcomes['plugin:seo/seo.php'] = function () { $this->plugin('seo/seo.php', '1.1'); return true; };
        $this->start('update_plugins', array('plugins' => array('gone/gone.php', 'modern/modern.php', 'premium/premium.php', 'new-wp/new-wp.php', 'seo/seo.php'), 'backup_first' => false));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $result = $this->results();
        $this->assertSame(array('failed', 'update_failed'), array($result['status'], $result['error']));
        $this->assertSame(array(
            array('gone/gone.php', 'not_installed', false),
            array('modern/modern.php', 'requirements', false),
            array('premium/premium.php', 'no_package', false),
            array('new-wp/new-wp.php', 'requirements', false),
            array('seo/seo.php', null, true),
        ), array_map(static function ($item) { return array($item['id'], $item['error'], $item['ok']); }, $result['result']['items']));
    }

    /** @dataProvider unhealthySites */
    public function test_sites_that_fail_before_updating_are_not_updated(array $pages, array $woocommerce, string $error): void {
        $this->plugin('seo/seo.php', '1.0');
        $this->offer('plugin', 'seo/seo.php', '1.1');
        Sitepulse_Test_Upgrades::$pages = $pages;
        Sitepulse_Test_Upgrades::$woocommerce = $woocommerce;
        $this->start('update_plugins', array('plugins' => array('seo/seo.php'), 'backup_first' => false));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $this->assertSame($error, $this->results()['error']);
        $this->assertSame(array(), $this->upgrades());
    }

    public static function unhealthySites(): array {
        return array(
            'home page does not load' => array(array('https://example.test/' => array('status' => 0)), array(), 'health_check_unavailable'),
            'wp-admin fails' => array(array('https://example.test/wp-admin/' => array('status' => 503)), array(), 'site_unhealthy'),
            'checkout has a fatal error' => array(array('https://example.test/checkout/' => array('fatal' => 'Payment gateway crashed')), array('cart' => 'https://example.test/cart/', 'checkout' => 'https://example.test/checkout/'), 'site_unhealthy'),
        );
    }

    public function test_health_checks_compare_with_the_baseline(): void {
        $url = 'https://example.test/';
        $page = static function ($status, $fatal = null, $checked = true) { return array('status' => $status, 'fatal' => $fatal, 'checked' => $checked); };

        $this->assertSame(array(), Sitepulse_Remote_Health::problems(array($url => $page(503)), array($url => $page(503))), 'A page that already failed is not blamed on the update.');
        $this->assertSame(array(), Sitepulse_Remote_Health::problems(array($url => $page(0)), array($url => $page(0))));
        $this->assertSame(array("$url: no answer"), Sitepulse_Remote_Health::problems(array($url => $page(200)), array($url => $page(0))));
        $this->assertSame(array("$url: HTTP 500"), Sitepulse_Remote_Health::problems(array(), array($url => $page(500))));
        $this->assertSame(array("$url: boom"), Sitepulse_Remote_Health::problems(array($url => $page(200)), array($url => $page(200, 'boom'))));
        $this->assertSame(array("$url: stopped without finishing"), Sitepulse_Remote_Health::problems(array($url => $page(200)), array($url => $page(200, null, false))), 'A page that dies before WordPress can report it failed too.');
        $this->assertSame(array(), Sitepulse_Remote_Health::problems(array($url => $page(200, null, false)), array($url => $page(200, null, false))), 'A cached page never answers the check.');
        $this->assertNull(Sitepulse_Remote_Health::baseline_error(array($url => $page(302))));
    }

    public function test_pages_without_the_check_markers_or_with_a_refused_key_are_not_fatal(): void {
        Sitepulse_Test_WP::$http_responder = static function ($url) {
            return false !== strpos($url, 'wp-admin') ? Sitepulse_Test_WP::response('<html>cached page</html>', 200) : null;
        };
        Sitepulse_Test_WP::$http_queue[] = static function ($url) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            delete_transient('scrape_key_' . $query['wp_scrape_key']);
            return sitepulse_test_site_pages($url, array());
        };

        $this->assertSame(array('https://example.test/' => array('status' => 200, 'fatal' => null, 'checked' => true), 'https://example.test/wp-admin/' => array('status' => 200, 'fatal' => null, 'checked' => false)), Sitepulse_Remote_Health::check());
        $this->assertSame(array(), Sitepulse_Test_WP::$transients, 'Check keys are removed.');
    }

    public function test_a_backup_runs_first_and_the_update_waits_for_it(): void {
        Sitepulse_Test_Backups::updraftplus();
        $this->plugin('seo/seo.php', '1.0');
        $this->offer('plugin', 'seo/seo.php', '1.1');
        Sitepulse_Test_Upgrades::$outcomes['plugin:seo/seo.php'] = function () { $this->plugin('seo/seo.php', '1.1'); return true; };
        $this->start('update_plugins', array('plugins' => array('seo/seo.php')));

        Sitepulse_Remote_Updates::run_job();

        $job = $this->job();
        $this->assertSame(array('updraftplus', 'abc123def456'), array($job['backup']['provider'], $job['backup']['job']));
        $this->assertSame('updraft_backupnow_backup_all', Sitepulse_Test_WP::$scheduled[0]->hook);
        $this->assertEqualsWithDelta(time() + 60, wp_next_scheduled(Sitepulse_Remote_Updates::JOB_HOOK), 2);
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);

        Sitepulse_Remote_Updates::run_job();
        $this->assertSame('backup', $this->job()['phase'], 'Still waiting for the backup.');

        update_option('updraft_last_backup', array('backup_time' => time() + 1, 'success' => 1));
        $this->api();
        Sitepulse_Remote_Updates::run_job();

        $result = $this->results();
        $this->assertSame(array('done', array('provider' => 'updraftplus', 'job' => 'abc123def456')), array($result['status'], $result['result']['backup']));
    }

    /** @dataProvider failedBackups */
    public function test_nothing_is_updated_without_the_backup(callable $setup, string $error): void {
        $this->plugin('seo/seo.php', '1.0');
        $this->offer('plugin', 'seo/seo.php', '1.1');
        $this->start('update_plugins', array('plugins' => array('seo/seo.php'), 'backup_wait' => 30));
        $setup($this);
        $this->api();

        Sitepulse_Remote_Updates::run_job();
        if (null !== $this->job()) { Sitepulse_Remote_Updates::run_job(); }

        $result = $this->results();
        $this->assertSame($error, $result['error']);
        $this->assertSame('skipped', $result['result']['items'][0]['error']);
        $this->assertSame(array(), $this->upgrades());
    }

    public static function failedBackups(): array {
        $job = static function (array $changes) { update_option(Sitepulse_Remote_Updates::JOB_OPTION, array_replace(get_option(Sitepulse_Remote_Updates::JOB_OPTION), $changes)); };
        return array(
            'no backup plugin' => array(static function () {}, 'no_backup_plugin'),
            'BackWPup did not start' => array(static function () { Sitepulse_Test_Backups::$backwpup_version = '5.7.8'; Sitepulse_Test_Backups::$destinations = array('FOLDER' => array()); Sitepulse_Test_Backups::$job_starts = false; }, 'backup_failed'),
            'the backup failed' => array(static function () use ($job) {
                Sitepulse_Test_Backups::updraftplus();
                $job(array('backup' => array('provider' => 'updraftplus', 'job' => 'x', 'requested_at' => time() - 120)));
                update_option('updraft_last_backup', array('backup_time' => time() - 60, 'success' => 0));
            }, 'backup_failed'),
            'the backup takes too long' => array(static function () use ($job) {
                Sitepulse_Test_Backups::updraftplus();
                $job(array('backup' => array('provider' => 'updraftplus', 'job' => 'x', 'requested_at' => time() - 31 * 60)));
            }, 'backup_timeout'),
            'another backup runs too long' => array(static function () use ($job) {
                Sitepulse_Test_Backups::updraftplus();
                Sitepulse_Test_WP::$cron[time()]['updraft_backup_resume']['k'] = array('args' => array(1, 'run111111111'));
                Sitepulse_Test_WP::$site_options['updraft_jobdata_run111111111'] = array('jobstatus' => 'dbcreating');
                $job(array('started_at' => time() - 31 * 60));
            }, 'backup_timeout'),
        );
    }

    public function test_an_update_waits_for_a_backup_that_is_already_running(): void {
        Sitepulse_Test_Backups::updraftplus();
        Sitepulse_Test_WP::$cron[time()]['updraft_backup_resume']['k'] = array('args' => array(1, 'run111111111'));
        Sitepulse_Test_WP::$site_options['updraft_jobdata_run111111111'] = array('jobstatus' => 'dbcreating');
        $this->start('update_plugins', array('plugins' => array('seo/seo.php')));

        Sitepulse_Remote_Updates::run_job();

        $this->assertNull($this->job()['backup']);
        $this->assertEqualsWithDelta(time() + 60, wp_next_scheduled(Sitepulse_Remote_Updates::JOB_HOOK), 2);
    }

    public function test_an_interrupted_update_is_reported(): void {
        $this->plugin('seo/seo.php', '1.1');
        $this->start('update_plugins', array('plugins' => array('seo/seo.php', 'forms/forms.php'), 'backup_first' => false));
        update_option(Sitepulse_Remote_Updates::JOB_OPTION, array_replace($this->job(), array('phase' => 'updating', 'baseline' => array(), 'current' => array('from' => '1.0'))));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $this->assertSame(array(
            array('id' => 'seo/seo.php', 'from' => '1.0', 'to' => '1.1', 'ok' => false, 'error' => 'interrupted', 'rolled_back' => false),
            array('id' => 'forms/forms.php', 'from' => null, 'to' => null, 'ok' => false, 'error' => 'skipped', 'rolled_back' => false),
        ), $this->results()['result']['items']);
    }

    public function test_blocking_remote_management_stops_an_update(): void {
        $this->start('update_plugins', array('plugins' => array('seo/seo.php'), 'backup_first' => false));
        update_option('sitepulse_settings', array('remote_management_blocked' => true));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $this->assertSame('blocked', $this->results()['error']);
        $this->assertSame(array(), $this->pageChecks());
    }

    public function test_one_request_at_a_time_works_on_the_update(): void {
        Sitepulse_Remote_Updates::run_job();
        $this->assertSame(array(), Sitepulse_Test_WP::$scheduled, 'Nothing to do without an update.');

        $this->start('update_plugins', array('plugins' => array('seo/seo.php'), 'backup_first' => false));
        set_transient(Sitepulse_Remote_Updates::LOCK, 1);
        Sitepulse_Remote_Updates::run_job();

        $this->assertSame('baseline', $this->job()['phase']);
        $this->assertEqualsWithDelta(time() + 60, wp_next_scheduled(Sitepulse_Remote_Updates::JOB_HOOK), 2);
    }

    public function test_wordpress_updates_to_the_offered_release_without_rollback(): void {
        Sitepulse_Remote_Events::init();
        $this->connect();
        Sitepulse_Test_WP::$core_updates = array(
            'not an offer',
            (object) array('response' => 'latest', 'current' => '7.1.2'),
            (object) array('response' => 'upgrade', 'current' => '7.1.3', 'packages' => (object) array('full' => 'https://downloads.wordpress.org/release/wordpress-7.1.3.zip'), 'php_version' => '7.4'),
        );
        Sitepulse_Test_Upgrades::$outcomes['core:7.1.3'] = function () {
            $this->core('7.1.3');
            do_action('_core_updated_successfully', '7.1.3');
            Sitepulse_Test_Upgrades::$pages[home_url('/')] = array('status' => 500);
            return '7.1.3';
        };
        $this->start('update_core', array('version' => '7.1.3', 'backup_first' => false));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $this->assertSame(array('id' => '7.1.3', 'from' => '7.1.2', 'to' => '7.1.3', 'ok' => false, 'error' => 'health_check_failed', 'rolled_back' => false, 'problems' => array('https://example.test/: HTTP 500')), $this->results()['result']['items'][0]);
        $this->assertSame(array('core_args', array('attempt_rollback' => true)), Sitepulse_Test_Upgrades::$calls[1]);
        $this->assertCount(4, Sitepulse_Test_Upgrades::$calls, 'WordPress core is not rolled back.');
        $this->assertSame(array('update.completed'), array_column($this->events(), 'type'));
        $this->assertSame('core', $this->events()[0]['data']['kind']);
    }

    /** @dataProvider coreOffers */
    public function test_wordpress_offers_decide_what_a_core_update_does(string $installed, array $offers, string $version, array $expected): void {
        $this->core($installed);
        Sitepulse_Test_WP::$core_updates = $offers;
        Sitepulse_Test_Upgrades::$outcomes['core:7.2'] = function () { $this->core('7.2'); return '7.2'; };
        $this->start('update_core', array('version' => $version, 'backup_first' => false));
        $this->api();

        Sitepulse_Remote_Updates::run_job();

        $item = $this->results()['result']['items'][0];
        $this->assertSame($expected, array($item['error'], $item['ok']));
    }

    public static function coreOffers(): array {
        $offer = static function (array $extra = array()) { return (object) array_merge(array('response' => 'upgrade', 'current' => '7.2', 'download' => 'https://downloads.wordpress.org/release/wordpress-7.2.zip'), $extra); };
        return array(
            'already on that release' => array('7.2', array(), '7.2', array('up_to_date', true)),
            'release not offered' => array('7.1.2', array(), '7.2', array('up_to_date', false)),
            'release needs a newer PHP' => array('7.1.2', array($offer(array('php_version' => '99.0'))), '7.2', array('requirements', false)),
            'release without a package' => array('7.1.2', array($offer(array('download' => ''))), '7.2', array('no_package', false)),
            'download link only' => array('7.1.2', array($offer()), '7.2', array(null, true)),
        );
    }

    public function test_wordpress_auto_updates_are_switched_for_installed_items_only(): void {
        Sitepulse_Test_WP::$plugins = array('seo/seo.php' => array(), 'forms/forms.php' => array());
        Sitepulse_Test_WP::$themes = array('astra' => array('Name' => 'Astra'));
        Sitepulse_Test_WP::$site_options['auto_update_plugins'] = array('forms/forms.php');
        Sitepulse_Test_WP::$site_options['auto_update_themes'] = array('astra', 'old');

        $this->assertSame(array('status' => 'done', 'result' => array('kind' => 'plugin', 'enabled' => true, 'items' => array('seo/seo.php')), 'error' => null), $this->start('set_auto_updates', array('kind' => 'plugin', 'items' => array('seo/seo.php', 'forms/forms.php', 'gone/gone.php'), 'enabled' => true)));
        $this->assertSame(array('forms/forms.php', 'seo/seo.php'), Sitepulse_Test_WP::$site_options['auto_update_plugins']);

        $this->assertSame(array('kind' => 'theme', 'enabled' => false, 'items' => array('astra')), $this->start('set_auto_updates', array('kind' => 'theme', 'items' => array('astra'), 'enabled' => false))['result']);
        $this->assertSame(array('old'), Sitepulse_Test_WP::$site_options['auto_update_themes']);
    }

    public function test_results_the_api_did_not_receive_are_sent_at_the_next_check_in(): void {
        $this->connect();
        $this->start('update_plugins', array('plugins' => array('seo/seo.php'), 'backup_first' => false));
        update_option('sitepulse_settings', array('remote_management_blocked' => true));
        Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response(array('success' => false), 503);

        Sitepulse_Remote_Updates::run_job();

        $pending = Sitepulse_Remote_Management::state()['pending_results'];
        $this->assertSame(array('01JUPDATE00000000000000000'), array_column($pending, 'id'));

        delete_option('sitepulse_license_key');
        Sitepulse_Remote_Management::finish_command('01JOFFLINE0000000000000000', 'cancel_backup', array(), Sitepulse_Remote_Management::command_result('done', array(), null));
        $this->assertCount(2, Sitepulse_Remote_Management::state()['pending_results'], 'A site that cannot connect keeps the result.');

        update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
        Sitepulse_Test_WP::$http_queue = array(sitepulse_test_remote_response(array('success' => true, 'remote_management' => array('enabled' => true, 'active' => false))), sitepulse_test_remote_response(), sitepulse_test_remote_response(array('success' => false), 500));
        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('01JOFFLINE0000000000000000'), array_column(Sitepulse_Remote_Management::state()['pending_results'], 'id'));
        $this->assertSame('blocked', json_decode(Sitepulse_Test_WP::$http_requests[2]['args']['body'], true)['error']);
    }

    public function test_check_in_starts_updates_and_reports_their_progress(): void {
        update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
        Sitepulse_Test_Backups::updraftplus();
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true, 'inventory_sent_at' => time(), 'provider' => Sitepulse_Remote_Backups::provider()));
        $commands = array(
            array('id' => '01JUPDATE00000000000000000', 'type' => 'update_themes', 'args' => array('themes' => array('astra', 'kadence'))),
            array('id' => '01JCORE0000000000000000000', 'type' => 'update_core', 'args' => array('version' => '7.1.3')),
            array('id' => '01JAUTO0000000000000000000', 'type' => 'set_auto_updates', 'args' => array('kind' => 'theme', 'items' => array('astra'), 'enabled' => true)),
        );
        Sitepulse_Test_WP::$http_queue = array(sitepulse_test_remote_response(array('success' => true, 'remote_management' => array('enabled' => true, 'active' => true, 'check_in_seconds' => 300), 'commands' => $commands)));
        $this->api(3);

        Sitepulse_Remote_Management::check_in();

        $endpoints = array_map('sitepulse_test_remote_endpoint', array_keys(Sitepulse_Test_WP::$http_requests));
        $this->assertSame(array('check-in', 'command-result', 'command-result', 'inventory'), $endpoints, 'The update answers later; the others at once.');
        $this->assertSame('update_running', sitepulse_test_remote_request_body(1)['error']);
        $this->assertSame(array('command' => '01JUPDATE00000000000000000', 'kind' => 'theme', 'phase' => 'backup', 'done' => 0, 'total' => 2), array_diff_key(sitepulse_test_remote_request_body(3)['updates']['job'], array('started_at' => 1)));
        $this->assertSame(array(
            'Dashboard request started: update 2 themes.',
            'Dashboard request failed: update WordPress to 7.1.3 (update_running).',
            'Dashboard request done: turn on WordPress auto-updates.',
        ), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
        $label = new ReflectionMethod('Sitepulse_Remote_Management', 'command_label');
        if (PHP_VERSION_ID < 80100) { $label->setAccessible(true); }
        $this->assertSame('turn off WordPress auto-updates', $label->invoke(null, 'set_auto_updates', array('enabled' => false)));
    }
}

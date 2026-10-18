<?php
require_once __DIR__ . '/Support/RemoteManagement.php';
require_once __DIR__ . '/Support/Database.php';

final class RemoteRecoveryTest extends Sitepulse_Test_Case {
    private const COMMAND = '01JRESTORE0000000000000000';
    private const NONCE = 'abc123def456';
    private const KEY = 'rk_rotated_key_0123456789abcdef0123';
    private const PACKAGE = 'https://downloads.wordpress.org/plugin/seo.1.0.zip';

    private $files = array();

    protected function setUp(): void {
        parent::setUp();
        Sitepulse_Test_Backups::reset();
        Sitepulse_Test_Upgrades::reset();
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
        update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
    }

    protected function tearDown(): void {
        foreach (array_reverse($this->files) as $path) { if (is_dir($path)) { rmdir($path); } elseif (is_file($path)) { unlink($path); } }
        Sitepulse_Test_Backups::reset();
        unset($GLOBALS['wpdb']);
        parent::tearDown();
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

    /** What WordPress.org's update check says about an item: in "response" or "no_update". */
    private function wordpressOrg(string $kind, string $id, string $list, array $entry): void {
        $transient = Sitepulse_Test_WP::$site_transients['update_' . $kind . 's'] ?? (object) array('response' => array(), 'no_update' => array());
        $transient->{$list}[$id] = 'plugin' === $kind ? (object) $entry : $entry;
        Sitepulse_Test_WP::$site_transients['update_' . $kind . 's'] = $transient;
    }

    /** seo 1.1 is installed from WordPress.org, and installing its package 1.0 puts that version back. */
    private function seoFromWordpressOrg(): void {
        $this->plugin('seo/seo.php', '1.1');
        $this->wordpressOrg('plugin', 'seo/seo.php', 'no_update', array('slug' => 'seo', 'new_version' => '1.1', 'package' => 'https://downloads.wordpress.org/plugin/seo.1.1.zip'));
        Sitepulse_Test_Upgrades::$outcomes['install:' . self::PACKAGE] = function () { $this->plugin('seo/seo.php', '1.0'); return true; };
    }

    /** An UpdraftPlus backup of the plugins and the database. */
    private function backup(array $set = array()): array {
        return $set + array('nonce' => self::NONCE, 'plugins' => array('backup_2026-10-18-1200_Site_abc123def456-plugins.zip'), 'db' => 'backup_2026-10-18-1200_Site_abc123def456-db.gz', 'label' => 'SitePulse');
    }

    private function start(string $type, array $args): ?array { return Sitepulse_Remote_Recovery::run_command(self::COMMAND, $type, $args); }

    private function restore(array $args = array()): ?array {
        return $this->start('restore_backup', $args + array('safe_update' => '01JSAFE0000000000000000000', 'provider' => 'updraftplus', 'job' => self::NONCE, 'kind' => 'plugin', 'item' => 'seo/seo.php'));
    }

    private function rollback(array $args = array()): ?array {
        return $this->start('rollback_item', $args + array('safe_update' => '01JSAFE0000000000000000000', 'kind' => 'plugin', 'item' => 'seo/seo.php', 'version' => '1.0'));
    }

    private function job(): ?array { return get_option(Sitepulse_Remote_Recovery::JOB_OPTION, null); }

    /** The stored recovery, changed. */
    private function changeJob(array $changes): void { update_option(Sitepulse_Remote_Recovery::JOB_OPTION, array_replace($this->job(), $changes), false); }

    private function results(): array {
        foreach (Sitepulse_Test_WP::$http_requests as $request) {
            if (false !== strpos($request['url'], '/remote/command-result')) { return array_diff_key(json_decode($request['args']['body'], true), array('id' => 1)); }
        }
        $this->fail('No command result was sent.');
    }

    /** How many command results were sent. */
    private function resultCount(): int {
        return count(array_filter(Sitepulse_Test_WP::$http_requests, static function ($request) { return false !== strpos($request['url'], '/remote/command-result'); }));
    }

    private function calls(string $name): array {
        return array_values(array_filter(Sitepulse_Test_Backups::$calls, static function ($call) use ($name) { return $call[0] === $name; }));
    }

    /** The PHP error handler in place now. */
    private function errorHandler() {
        $handler = set_error_handler(static function () { return false; });
        restore_error_handler();
        return $handler;
    }

    private function api(array $tamper = array()): void { Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response(array('success' => true), 200, $tamper); }

    /** Versions recoveries undid, without when. */
    private function undone(): array {
        return array_map(static function ($entry) { unset($entry['at']); return $entry; }, (array) get_option(Sitepulse_Remote_Recovery::UNDONE_OPTION, array()));
    }

    /** @dataProvider invalidCommands */
    public function test_invalid_recovery_commands_are_refused(string $type, array $args): void {
        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'invalid_arguments'), $this->start($type, $args));
        $this->assertNull($this->job());
    }

    public static function invalidCommands(): array {
        $restore = array('provider' => 'updraftplus', 'job' => self::NONCE, 'kind' => 'plugin', 'item' => 'seo/seo.php');
        $rollback = array('kind' => 'theme', 'item' => 'astra', 'version' => '4.0');
        return array(
            'core' => array('rollback_item', array('kind' => 'core') + $rollback),
            'no item' => array('restore_backup', array('item' => '') + $restore),
            'item not text' => array('rollback_item', array('item' => array('astra')) + $rollback),
            'a BackWPup backup' => array('restore_backup', array('provider' => 'backwpup') + $restore),
            'no backup' => array('restore_backup', array_diff_key($restore, array('job' => 1))),
            'a backup that is not a nonce' => array('restore_backup', array('job' => '../abc') + $restore),
            'a restore to a version that is not one' => array('restore_backup', array('version' => '1.0/../x') + $restore),
            'a restore to a version that is not text' => array('restore_backup', array('version' => 10) + $restore),
            'no version' => array('rollback_item', array_diff_key($rollback, array('version' => 1))),
            'a version that is not one' => array('rollback_item', array('version' => '4.0/../x') + $rollback),
        );
    }

    public function test_a_recovery_starts_in_cron_and_runs_alone(): void {
        $this->assertNull($this->rollback());

        $this->assertSame(array('command' => self::COMMAND, 'type' => 'rollback_item', 'started' => false), array_diff_key($this->job(), array('args' => 1, 'received_at' => 1)));
        $this->assertEqualsWithDelta(time(), $this->job()['received_at'], 2, 'It remembers when it was received.');
        $this->assertEqualsWithDelta(time(), wp_next_scheduled(Sitepulse_Remote_Recovery::JOB_HOOK), 2);
        $this->assertTrue(Sitepulse_Remote_Recovery::running());
        $this->assertSame('update_running', $this->restore()['error']);
        $this->assertSame('update_running', Sitepulse_Remote_Updates::run_command('01JUPDATE00000000000000000', 'update_plugins', array('plugins' => array('seo/seo.php')))['error']);

        delete_option(Sitepulse_Remote_Recovery::JOB_OPTION);
        update_option(Sitepulse_Remote_Updates::JOB_OPTION, array('command' => '01JUPDATE00000000000000000', 'items' => array(), 'results' => array()));
        $this->assertSame('update_running', $this->rollback()['error'], 'Not while an update runs.');

        delete_option(Sitepulse_Remote_Updates::JOB_OPTION);
        $this->assertNull($this->restore(array('version' => '1.0')), 'A restore may name the version it brings back.');
        $this->assertSame('1.0', $this->job()['args']['version']);
    }

    /** @dataProvider unknownVersions */
    public function test_a_restore_that_does_not_know_the_version_it_brings_back_is_not_checked_against_one(array $args): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        $this->plugin('seo/seo.php', '1.1');
        $this->assertNull($this->restore($args));
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'done', 'result' => array('restored' => array('plugins', 'db')), 'error' => null), $this->results());
        $this->assertSame(array(array('*', 'flush'), array('plugins', 'plugins'), array('update_plugins', 'fetched')), Sitepulse_Test_WP::$cache_deletes, 'Nothing is checked; WordPress checks for updates afresh.');
    }

    public static function unknownVersions(): array {
        return array(
            'no version' => array(array()),
            'no version known to the dashboard' => array(array('version' => '')),
            'a version of null' => array(array('version' => null)),
        );
    }

    /** @dataProvider unwritableSites */
    public function test_sites_where_wordpress_cannot_write_files_are_not_recovered(callable $setup): void {
        $setup();
        $this->assertSame('cannot_update', $this->restore()['error']);
        $this->assertNull($this->job());
    }

    public static function unwritableSites(): array {
        return array(
            'file changes disallowed' => array(static function () { add_filter('file_mod_allowed', '__return_false'); }),
            'FTP credentials needed' => array(static function () { Sitepulse_Test_WP::$filesystem_method = 'ftpext'; }),
        );
    }

    public function test_a_lost_recovery_is_rescheduled_from_the_dashboard_or_cron(): void {
        Sitepulse_Remote_Recovery::init();
        $this->assertFalse(wp_next_scheduled(Sitepulse_Remote_Recovery::JOB_HOOK));
        $this->rollback();
        Sitepulse_Remote_Recovery::unschedule();

        Sitepulse_Test_WP::$is_admin = false;
        Sitepulse_Remote_Recovery::init();
        $this->assertFalse(wp_next_scheduled(Sitepulse_Remote_Recovery::JOB_HOOK), 'Page views do not check.');
        Sitepulse_Test_WP::$doing_cron = true;
        Sitepulse_Remote_Recovery::init();
        $this->assertNotFalse(wp_next_scheduled(Sitepulse_Remote_Recovery::JOB_HOOK));
        $this->assertNotFalse(has_action(Sitepulse_Remote_Recovery::JOB_HOOK, array('Sitepulse_Remote_Recovery', 'run_job')));
    }

    public function test_the_backup_restores_the_plugin_and_the_database_and_keeps_sitepulse_settings(): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup(), 1760702400 => $this->backup(array('nonce' => 'older0000000'))));
        update_option('updraft_encryptionphrase', 'secret');
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true, 'done_commands' => array(self::COMMAND)));
        update_option(Sitepulse_Remote_Management::KEY_OPTION, self::KEY);
        update_option(Sitepulse_Remote_Management::LOG_OPTION, array(array('time' => 1760788000, 'message' => 'Dashboard request started: restore.')));
        update_option(Sitepulse_Remote_Activity::OPTION, array('watches' => array('plugin:seo/seo.php' => array('kind' => 'plugin', 'id' => 'seo/seo.php', 'name' => 'Seo', 'from' => '1.0', 'to' => '1.1', 'updated_at' => '2026-10-18T12:00:00Z', 'day' => 20379, 'before' => array('loads' => 50)))));
        $this->restore();
        $handler = $this->errorHandler();
        Sitepulse_Test_Backups::$restore = function () {
            // The database as it was at the time of the backup, while the update was running.
            Sitepulse_Test_Backups::restore_options(array(
                'blogname' => 'Restored',
                'sitepulse_license_key' => SITEPULSE_TEST_LICENSE,
                Sitepulse_Remote_Management::STATE_OPTION => array('active' => true),
                Sitepulse_Remote_Management::KEY_OPTION => 'rk_old',
                Sitepulse_Remote_Updates::JOB_OPTION => array('command' => '01JUPDATE00000000000000000', 'items' => array('seo/seo.php'), 'results' => array()),
            ));
            // UpdraftPlus restores the other tables and the plugin folder next: SitePulse's own settings are back already.
            $this->assertSame(array(self::KEY, array(self::COMMAND)), array(get_option(Sitepulse_Remote_Management::KEY_OPTION), Sitepulse_Remote_Management::state()['done_commands']));
            $this->assertFalse(get_option(Sitepulse_Remote_Updates::JOB_OPTION), 'The update the backup caught running is gone before another request can run it.');
            // Another table does not put them back.
            update_option(Sitepulse_Remote_Management::KEY_OPTION, 'rk_old');
            do_action('updraftplus_restored_db_table', 'wp_posts', 'wp_', 'InnoDB');
            $this->assertSame('rk_old', get_option(Sitepulse_Remote_Management::KEY_OPTION));
            return true;
        };
        $this->api(array('license' => self::KEY));

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'done', 'result' => array('restored' => array('plugins', 'db')), 'error' => null), $this->results());
        $this->assertSame(array(array('phase' => 'restoring', 'percent' => null, 'backup' => null, 'done' => 0, 'total' => 1, 'item' => 'seo/seo.php')), Sitepulse_Test_Upgrades::$progress, 'The restore says it started, signed with the current key.');
        $this->assertSame('Restored', get_option('blogname'), 'The database is the backup\'s.');
        $this->assertSame(array(self::KEY, array(self::COMMAND)), array(get_option(Sitepulse_Remote_Management::KEY_OPTION), Sitepulse_Remote_Management::state()['done_commands']), 'SitePulse\'s own settings are the current ones.');
        $this->assertFalse(get_option(Sitepulse_Remote_Updates::JOB_OPTION), 'The update the backup caught running does not run again.');
        $this->assertNull($this->job());
        $this->assertFalse(wp_next_scheduled(Sitepulse_Remote_Recovery::JOB_HOOK));
        $this->assertFalse(get_transient(Sitepulse_Remote_Recovery::LOCK));
        $this->assertSame(array(), get_option(Sitepulse_Remote_Activity::OPTION)['watches'], 'The undone update is no longer measured.');
        $this->assertSame(array('Dashboard request started: restore.', 'Dashboard request done: restore seo/seo.php and the database from the backup taken before its update.'), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
        $this->assertContains(array('update_plugins', 'fetched'), Sitepulse_Test_WP::$cache_deletes, 'WordPress checks for plugin updates afresh.');
        $this->assertFalse(get_option(Sitepulse_Remote_Recovery::UNDONE_OPTION), 'The plugin was not installed: no version was undone.');

        $options = array('include_unspecified_plugins' => false, 'plugins_to_restore' => array('seo'), 'plugins_to_skip' => array(), 'include_unspecified_tables' => true, 'tables_to_restore' => array(), 'tables_to_skip' => array(), 'updraft_encryptionphrase' => 'secret', 'updraft_restorer_wpcore_includewpconfig' => false, 'updraft_incremental_restore_point' => -1);
        $this->assertSame(array(
            array('initiate_restore_job'),
            array('jobdata_set_multi', array(array('updraft_restore' => array('plugins', 'db'), 'backup_timestamp' => 1760788800, 'updraft_restorer_restore_options' => $options, 'restore_options' => $options))),
            array('restorer', 'Updraft_Restorer_Skin', $this->backup() + array('timestamp' => 1760788800), $options, null),
            array('perform_restore', array('plugins' => 'plugins', 'db' => 'db')),
            array('post_restore_clean_up', true, false),
            array('rebuild'),
        ), Sitepulse_Test_Backups::$calls);
        $this->assertSame('restore00001', Sitepulse_Test_WP::$site_options['updraft_restore_in_progress']);
        $this->assertSame($handler, $this->errorHandler(), 'UpdraftPlus\'s error handler is gone with its clean-up.');
        $this->assertInstanceOf('Sitepulse_Test_Filesystem', $GLOBALS['wp_filesystem']);
        $this->assertContains(array('*', 'flush'), Sitepulse_Test_WP::$cache_deletes);
        $this->assertFalse(has_action('shutdown'));
        $this->assertFalse(has_action('updraftplus_restored_db_table'));
        $this->assertFalse(has_action('updraftplus_restored_db'));
    }

    public function test_sitepulse_settings_keep_whether_wordpress_loads_them_on_every_request(): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        update_option('sitepulse_settings', array('remote_management_blocked' => false));
        update_option(Sitepulse_Remote_Management::KEY_OPTION, self::KEY, false);
        update_option(Sitepulse_Remote_Management::EVENTS_OPTION, array(array('id' => 'event1')), false);
        update_option(Sitepulse_Remote_Management::LOG_OPTION, array(array('time' => 1760788000, 'message' => 'Now')), false);
        $this->restore();
        Sitepulse_Test_Backups::$restore = static function () {
            Sitepulse_Test_Backups::restore_options(
                array(
                    'sitepulse_license_key' => SITEPULSE_TEST_LICENSE,
                    // Unchanged, but no longer loaded on every request.
                    'sitepulse_settings' => array('remote_management_blocked' => false),
                    // Unchanged, and not loaded on every request as now.
                    Sitepulse_Remote_Management::KEY_OPTION => self::KEY,
                    // Loaded on every request at the time of the backup.
                    Sitepulse_Remote_Management::LOG_OPTION => array(array('time' => 1760700000, 'message' => 'Then')),
                ),
                array('sitepulse_settings' => false, Sitepulse_Remote_Management::KEY_OPTION => false)
            );
            return true;
        };
        $this->api(array('license' => self::KEY));

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame('done', $this->results()['status']);
        $loaded = wp_load_alloptions();
        $this->assertArrayHasKey('sitepulse_settings', $loaded, 'Loaded on every request, as before the restore.');
        $this->assertArrayHasKey('sitepulse_license_key', $loaded);
        foreach (array(Sitepulse_Remote_Management::KEY_OPTION, Sitepulse_Remote_Management::EVENTS_OPTION, Sitepulse_Remote_Management::LOG_OPTION) as $option) {
            $this->assertArrayNotHasKey($option, $loaded, $option . ' is loaded only when needed, as before the restore.');
        }
        $this->assertSame(array(array('remote_management_blocked' => false), self::KEY, array(array('id' => 'event1')), 'Now'), array(get_option('sitepulse_settings'), get_option(Sitepulse_Remote_Management::KEY_OPTION), get_option(Sitepulse_Remote_Management::EVENTS_OPTION), get_option(Sitepulse_Remote_Management::LOG_OPTION)[0]['message']));
    }

    public function test_an_update_the_backup_caught_running_is_not_run_while_the_rest_is_restored(): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        $this->restore();
        Sitepulse_Test_Backups::$restore = static function () {
            Sitepulse_Test_Backups::restore_options(array(
                'sitepulse_license_key' => SITEPULSE_TEST_LICENSE,
                Sitepulse_Remote_Management::STATE_OPTION => array('active' => true),
                Sitepulse_Remote_Updates::JOB_OPTION => array(
                    'command' => '01JUPDATE00000000000000000', 'type' => 'update_plugins', 'args' => array('plugins' => array('seo/seo.php')), 'kind' => 'plugin',
                    'items' => array('seo/seo.php'), 'backup_first' => true, 'backup_wait' => 60, 'backup_what' => 'plugins', 'safe_update' => '01JSAFE0000000000000000000',
                    'phase' => 'backup', 'backup' => array('provider' => 'updraftplus', 'job' => self::NONCE, 'requested_at' => time() - 7200),
                    'backup_skipped' => null, 'visual_since' => null, 'baseline' => null, 'current' => null, 'results' => array(),
                    'started_at' => time() - 7200, 'finished' => false, 'error' => null,
                ),
            ));
            // Another WP-Cron request, while UpdraftPlus restores the rest.
            Sitepulse_Remote_Updates::run_job();
            return true;
        };
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(1, $this->resultCount(), 'No result for the update the backup caught running.');
        $this->assertSame('done', $this->results()['status']);
        $this->assertFalse(get_option(Sitepulse_Remote_Updates::JOB_OPTION));
    }

    public function test_no_other_request_takes_a_restore_for_an_interrupted_one(): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        $this->restore();
        Sitepulse_Test_Backups::$restore = function () {
            // The restored options table has no lock.
            Sitepulse_Test_Backups::restore_options(array('sitepulse_license_key' => SITEPULSE_TEST_LICENSE));
            $this->assertSame(1, get_transient(Sitepulse_Remote_Recovery::LOCK));
            $this->assertSame(Sitepulse_Remote_Recovery::TIME_LIMIT, Sitepulse_Test_WP::$transient_expirations[Sitepulse_Remote_Recovery::LOCK]);
            Sitepulse_Remote_Recovery::run_job();
            // UpdraftPlus empties the object cache, which may hold the lock, once the database is back.
            delete_transient(Sitepulse_Remote_Recovery::LOCK);
            do_action('updraftplus_restored_db', array('expected_oldsiteurl' => 'https://example.test'), 'wp_');
            $this->assertSame(1, get_transient(Sitepulse_Remote_Recovery::LOCK));
            // Another WP-Cron request, while UpdraftPlus restores the plugin folder.
            Sitepulse_Remote_Recovery::run_job();
            return true;
        };
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(1, $this->resultCount());
        $this->assertSame('done', $this->results()['status'], 'Not interrupted.');
        $this->assertFalse(get_transient(Sitepulse_Remote_Recovery::LOCK));
        $this->assertFalse(wp_next_scheduled(Sitepulse_Remote_Recovery::JOB_HOOK));
    }

    /** @dataProvider restoredItems */
    public function test_only_the_updated_item_s_folder_is_restored(string $kind, string $item, string $part, string $folder): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup(array($part => array('backup-' . $part . '.zip')))));
        $this->restore(array('kind' => $kind, 'item' => $item));
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $options = $this->calls('restorer')[0][3];
        $this->assertSame(array(false, array($folder)), array($options['include_unspecified_' . $part], $options[$part . '_to_restore']));
        $this->assertSame(array($part => $part, 'db' => 'db'), $this->calls('perform_restore')[0][1]);
        $this->assertSame(array($part, 'db'), $this->results()['result']['restored']);
    }

    public static function restoredItems(): array {
        return array(
            'a theme' => array('theme', 'astra', 'themes', 'astra'),
            'a plugin of one file' => array('plugin', 'hello.php', 'plugins', 'hello.php'),
        );
    }

    /** @dataProvider restoredVersions */
    public function test_a_restore_that_names_the_version_it_brings_back_checks_it(string $kind, string $item, string $version, callable $restored, array $expected, string $cache): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup(array($kind . 's' => array('backup-' . $kind . 's.zip')))));
        'plugin' === $kind ? $this->plugin($item, '1.1') : $this->theme($item, '4.1');
        $this->restore(array('kind' => $kind, 'item' => $item, 'version' => $version));
        Sitepulse_Test_Backups::$restore = function () use ($restored) {
            $restored($this);
            Sitepulse_Test_WP::$cache_deletes = array();
            return true;
        };
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame($expected, $this->results());
        $this->assertSame(array($cache, $cache), Sitepulse_Test_WP::$cache_deletes[1], 'WordPress forgets what it read of the folder before the check.');
    }

    public static function restoredVersions(): array {
        $incomplete = static function (string $message) { return array('status' => 'failed', 'result' => array('message' => $message), 'error' => 'restore_incomplete'); };
        return array(
            'the plugin is back' => array('plugin', 'seo/seo.php', '1.0', static function (self $test) { $test->plugin('seo/seo.php', '1.0'); }, array('status' => 'done', 'result' => array('restored' => array('plugins', 'db')), 'error' => null), 'plugins'),
            'the theme is back' => array('theme', 'astra', '4.0', static function (self $test) { $test->theme('astra', '4.0'); }, array('status' => 'done', 'result' => array('restored' => array('themes', 'db')), 'error' => null), 'themes'),
            'the plugin is still the update' => array('plugin', 'seo/seo.php', '1.0', static function () {}, $incomplete('UpdraftPlus restored the backup, but seo/seo.php is not back to version 1.0: version 1.1 is installed.'), 'plugins'),
            'the plugin is gone' => array('plugin', 'seo/seo.php', '1.0', static function () { unset(Sitepulse_Test_WP::$plugins['seo/seo.php']); }, $incomplete('UpdraftPlus restored the backup, but seo/seo.php is not back to version 1.0: it is not installed.'), 'plugins'),
        );
    }

    public function test_a_backup_the_restored_history_forgot_is_found_in_updraftplus_folder(): void {
        Sitepulse_Test_Backups::updraftplus();
        Sitepulse_Test_Backups::$found = array(1760788800 => $this->backup());
        $this->restore();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame('done', $this->results()['status']);
        $this->assertSame(array(array('rebuild'), array('rebuild')), $this->calls('rebuild'), 'Before looking again, and after the restore.');
    }

    /** @dataProvider missingBackups */
    public function test_a_backup_updraftplus_no_longer_has_is_not_restored(array $history): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', $history);
        $this->restore();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'backup_not_found'), $this->results());
        $this->assertSame(array(), $this->calls('restorer'));
    }

    public static function missingBackups(): array {
        return array(
            'deleted' => array(array(1760702400 => array('nonce' => 'older0000000', 'db' => 'db.gz'), 1760616000 => 'corrupt')),
            'without the plugins' => array(array(1760788800 => array('nonce' => self::NONCE, 'db' => 'db.gz'))),
            'without the database' => array(array(1760788800 => array('nonce' => self::NONCE, 'plugins' => array('plugins.zip')))),
        );
    }

    public function test_a_backup_updraftplus_cannot_read_its_history_for_is_not_restored(): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        $this->restore();
        Sitepulse_Test_Backups::$throw = true;
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'backup_not_found'), $this->results());
        $this->assertSame(array(), $this->calls('restorer'));
    }

    /** @dataProvider withoutUpdraftplus */
    public function test_only_updraftplus_1_26_or_later_restores(callable $setup): void {
        $setup();
        $this->restore();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame('restore_unsupported', $this->results()['error']);
    }

    public static function withoutUpdraftplus(): array {
        return array(
            'deactivated since' => array(static function () { Sitepulse_Test_Backups::$backwpup_version = '5.7.8'; }),
            'an old UpdraftPlus' => array(static function () { Sitepulse_Test_Backups::updraftplus('1.25.8'); }),
        );
    }

    /** @dataProvider failedRestores */
    public function test_a_failed_restore_says_why_and_keeps_sitepulse_settings(callable $restore, string $message, $cleaned): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true, 'done_commands' => array(self::COMMAND)));
        $this->restore();
        $handler = $this->errorHandler();
        Sitepulse_Test_Backups::$restore = static function () use ($restore) {
            Sitepulse_Test_WP::$options[Sitepulse_Remote_Management::STATE_OPTION] = array('active' => false);
            return $restore();
        };
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => array('message' => $message), 'error' => 'restore_failed'), $this->results());
        $this->assertSame(array(array('post_restore_clean_up', $cleaned, false)), $this->calls('post_restore_clean_up'));
        $this->assertCount(true === $cleaned ? 1 : 0, $this->calls('rebuild'), 'UpdraftPlus lists its backups again after a restore it finished.');
        $this->assertSame(array(self::COMMAND), Sitepulse_Remote_Management::state()['done_commands']);
        $this->assertSame($handler, $this->errorHandler());
        $this->assertNull($this->job());
        $this->assertFalse(get_option(Sitepulse_Remote_Recovery::UNDONE_OPTION));
        $this->assertNotContains(array('update_plugins', 'fetched'), Sitepulse_Test_WP::$cache_deletes);
    }

    public static function failedRestores(): array {
        $log = 'UpdraftPlus could not restore the backup; its log of restore restore00001 has the details.';
        return array(
            'UpdraftPlus\'s error' => array(static function () { return new WP_Error('new_move_failed', 'Could not move the new files into place.'); }, 'Could not move the new files into place.', 'new_move_failed'),
            'not finished' => array(static function () { return false; }, $log, false),
            'an error recorded' => array(static function () { $GLOBALS['updraftplus']->errors[] = 'Table wp_posts: error'; return true; }, $log, true),
            'an error logged with its level' => array(static function () { $GLOBALS['updraftplus']->errors['restore-db-error'] = array('level' => 'error', 'message' => 'Too many database errors'); return true; }, $log, true),
            'an exception' => array(static function () { throw new RuntimeException('Out of disk space'); }, 'Out of disk space', 'restore_failed'),
        );
    }

    public function test_warnings_and_problems_from_before_the_restore_do_not_fail_it(): void {
        Sitepulse_Test_Backups::updraftplus();
        // Another task of this WP-Cron request, such as a backup, recorded an error before the restore.
        $GLOBALS['updraftplus']->errors[] = 'An earlier backup could not reach its remote storage';
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        $this->restore();
        Sitepulse_Test_Backups::$restore = static function () {
            // A warning UpdraftPlus logs on hosts whose database user may not rename tables; it restores all the same.
            $GLOBALS['updraftplus']->errors[] = array('level' => 'warning', 'message' => 'Database user has no permission to rename tables - restoration will be non-atomic');
            return true;
        };
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array(array('post_restore_clean_up', true, false)), $this->calls('post_restore_clean_up'));
        $this->assertSame(array('status' => 'done', 'result' => array('restored' => array('plugins', 'db')), 'error' => null), $this->results());
    }

    public function test_an_updraftplus_that_cannot_start_a_restore_fails_it(): void {
        Sitepulse_Test_Backups::updraftplus();
        $GLOBALS['updraftplus'] = new class('1.26.9') extends UpdraftPlus {
            public function initiate_restore_job($nonce = false) { throw new RuntimeException('Could not create the log file'); }
        };
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        $this->restore();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => array('message' => 'Could not create the log file'), 'error' => 'restore_failed'), $this->results());
        $this->assertSame(array(), $this->calls('restorer'));
    }

    public function test_a_request_that_ends_mid_restore_puts_sitepulse_settings_back_and_is_reported(): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        update_option(Sitepulse_Remote_Management::KEY_OPTION, self::KEY);
        $this->restore();
        Sitepulse_Test_Backups::$restore = function () {
            Sitepulse_Test_WP::$options = array('sitepulse_license_key' => SITEPULSE_TEST_LICENSE, Sitepulse_Remote_Management::KEY_OPTION => 'rk_old');
            // A PHP time limit: WordPress runs its shutdown actions, then the request is gone.
            do_action('shutdown');
            $this->assertSame(array(self::KEY, true), array(get_option(Sitepulse_Remote_Management::KEY_OPTION), $this->job()['started']));
            $this->assertFalse(get_transient(Sitepulse_Remote_Recovery::LOCK), 'The next request does not wait for the lock to expire.');
            return true;
        };
        $this->api(array('license' => self::KEY));
        Sitepulse_Remote_Recovery::run_job();
        $this->assertFalse(has_action('shutdown'));

        // The same, as the next request finds it.
        Sitepulse_Test_WP::$http_requests = array();
        update_option(Sitepulse_Remote_Recovery::JOB_OPTION, array('command' => self::COMMAND, 'type' => 'restore_backup', 'args' => array('kind' => 'plugin', 'item' => 'seo/seo.php'), 'started' => true));
        $this->api(array('license' => self::KEY));

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'interrupted'), $this->results());
        $this->assertCount(1, $this->calls('perform_restore'), 'Nothing is restored twice.');
        $this->assertNull($this->job());
    }

    /** @dataProvider busyUpdraftplus */
    public function test_no_restore_starts_while_an_updraftplus_backup_runs_or_waits_to(callable $busy): void {
        Sitepulse_Test_Backups::updraftplus();
        $busy();

        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'backup_running'), $this->restore());
        $this->assertNull($this->job());
        $this->assertNull($this->rollback(), 'A rollback leaves the database alone.');
    }

    public static function busyUpdraftplus(): array {
        return array(
            'a backup runs' => array(static function () {
                Sitepulse_Test_WP::$cron[time() + 60]['updraft_backup_resume']['k'] = array('args' => array(1, 'run111111111'));
                Sitepulse_Test_WP::$site_options['updraft_jobdata_run111111111'] = array('jobstatus' => 'dbcreating0');
            }),
            'a backup waits to start' => array(static function () { Sitepulse_Test_WP::$cron[time()]['updraft_backupnow_backup_all']['k'] = array('args' => array(array('use_nonce' => 'que111111111'))); }),
        );
    }

    public function test_a_restore_waits_for_a_backup_that_started_after_it_was_received(): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        $this->restore();
        // A scheduled backup started meanwhile.
        Sitepulse_Test_WP::$cron[time() + 60]['updraft_backup_resume']['k'] = array('args' => array(1, 'run111111111'));
        Sitepulse_Test_WP::$site_options['updraft_jobdata_run111111111'] = array('jobstatus' => 'dbcreating0');

        Sitepulse_Remote_Recovery::run_job();

        $this->assertFalse($this->job()['started']);
        $this->assertEqualsWithDelta(time() + 60, wp_next_scheduled(Sitepulse_Remote_Recovery::JOB_HOOK), 2);
        $this->assertSame(array(), Sitepulse_Test_WP::$http_requests, 'It has not started, so it says nothing yet.');
        $this->assertFalse(get_transient(Sitepulse_Remote_Recovery::LOCK));

        // The backup is done.
        Sitepulse_Test_WP::$cron = array();
        $this->api();
        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame('done', $this->results()['status']);
        $this->assertCount(1, $this->calls('perform_restore'));
    }

    public function test_a_restore_a_backup_still_holds_up_half_an_hour_after_it_arrived_fails(): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        $this->restore();
        $this->changeJob(array('received_at' => time() - Sitepulse_Remote_Recovery::BACKUP_WAIT_SECONDS));
        Sitepulse_Test_WP::$cron[time()]['updraft_backupnow_backup_database']['k'] = array('args' => array(array('use_nonce' => 'que111111111')));
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'backup_running'), $this->results());
        $this->assertSame(array(), $this->calls('perform_restore'));
        $this->assertNull($this->job());
    }

    /** @dataProvider lateRecoveries */
    public function test_a_recovery_that_could_not_start_within_an_hour_of_its_arrival_expires(string $type, ?int $waited): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        $this->seoFromWordpressOrg();
        'restore_backup' === $type ? $this->restore() : $this->rollback();
        $job = $this->job();
        if (null === $waited) { unset($job['received_at']); } else { $job['received_at'] = time() - $waited; }
        update_option(Sitepulse_Remote_Recovery::JOB_OPTION, $job);
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'expired'), $this->results());
        $this->assertSame(array(), $this->calls('perform_restore'));
        $this->assertSame(array(), Sitepulse_Test_Upgrades::$calls, 'The site is left as it is.');
        $this->assertNull($this->job());
    }

    public static function lateRecoveries(): array {
        return array(
            'a restore after an hour' => array('restore_backup', Sitepulse_Remote_Recovery::START_SECONDS + 60),
            'a rollback after an hour' => array('rollback_item', Sitepulse_Remote_Recovery::START_SECONDS + 60),
            'a restore received by an older version of the plugin' => array('restore_backup', null),
        );
    }

    public function test_a_recovery_starts_within_an_hour_of_its_arrival(): void {
        $this->seoFromWordpressOrg();
        $this->rollback();
        $this->changeJob(array('received_at' => time() - Sitepulse_Remote_Recovery::START_SECONDS + 60));
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame('done', $this->results()['status']);
    }

    public function test_a_site_that_cannot_connect_still_recovers(): void {
        $this->plugin('seo/seo.php', '1.1');
        $this->rollback();
        delete_option('sitepulse_license_key');

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array(), Sitepulse_Test_Upgrades::$progress);
        $this->assertSame('no_package', Sitepulse_Remote_Management::state()['pending_results'][0]['error'], 'Its result waits for the next check-in.');
    }

    public function test_one_request_at_a_time_runs_the_recovery(): void {
        Sitepulse_Remote_Recovery::run_job();
        $this->assertSame(array(), Sitepulse_Test_WP::$scheduled, 'Nothing to do without a recovery.');

        $this->rollback();
        set_transient(Sitepulse_Remote_Recovery::LOCK, 1);
        Sitepulse_Remote_Recovery::run_job();

        $this->assertFalse($this->job()['started']);
        $this->assertEqualsWithDelta(time() + 60, wp_next_scheduled(Sitepulse_Remote_Recovery::JOB_HOOK), 2);
    }

    public function test_a_plugin_is_rolled_back_to_its_version_from_wordpress_org(): void {
        Sitepulse_Remote_Events::init();
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true));
        $this->seoFromWordpressOrg();
        Sitepulse_Test_Upgrades::$outcomes['install:' . self::PACKAGE] = function () {
            $this->assertTrue(Sitepulse_Remote_Events::$updating);
            $this->assertSame(1, get_transient(Sitepulse_Remote_Recovery::LOCK));
            $this->plugin('seo/seo.php', '1.0');
            do_action('upgrader_process_complete', new stdClass(), array('action' => 'install', 'type' => 'plugin'));
            return true;
        };
        $this->rollback();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'done', 'result' => array('kind' => 'plugin', 'id' => 'seo/seo.php', 'from' => '1.1', 'to' => '1.0'), 'error' => null), $this->results());
        $this->assertSame('installing', Sitepulse_Test_Upgrades::$progress[0]['phase']);
        $head = array_values(array_filter(Sitepulse_Test_WP::$http_requests, static function ($request) { return self::PACKAGE === $request['url']; }));
        $this->assertSame(array(array('HEAD', 10)), array_map(static function ($request) { return array($request['args']['method'], $request['args']['timeout']); }, $head), 'WordPress.org is asked first whether it has that version.');
        $this->assertSame(array(array('maintenance', true), array('install', self::PACKAGE, array('overwrite_package' => true)), array('maintenance', false)), Sitepulse_Test_Upgrades::$calls);
        $this->assertContains(array('plugins', 'plugins'), Sitepulse_Test_WP::$cache_deletes);
        $this->assertContains(array('update_plugins', 'fetched'), Sitepulse_Test_WP::$cache_deletes, 'WordPress checks for plugin updates afresh.');
        $this->assertFalse(Sitepulse_Remote_Events::$updating);
        $this->assertFalse(has_action('shutdown'));
        $this->assertFalse(get_transient(Sitepulse_Remote_Recovery::LOCK));
        $this->assertFalse(get_option(Sitepulse_Remote_Management::EVENTS_OPTION), 'Reported as the command\'s result, not as an event.');
        $this->assertSame(array('Dashboard request done: roll seo/seo.php back to 1.0.'), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
    }

    public function test_a_theme_is_rolled_back_to_its_version_from_wordpress_org(): void {
        $this->theme('astra', '4.1');
        $this->wordpressOrg('theme', 'astra', 'response', array('theme' => 'astra', 'new_version' => '4.2', 'package' => 'https://downloads.wordpress.org/theme/astra.4.2.zip'));
        Sitepulse_Test_Upgrades::$outcomes['install:https://downloads.wordpress.org/theme/astra.4.0.zip'] = function () { $this->theme('astra', '4.0'); return true; };
        $this->rollback(array('kind' => 'theme', 'item' => 'astra', 'version' => '4.0'));
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('kind' => 'theme', 'id' => 'astra', 'from' => '4.1', 'to' => '4.0'), $this->results()['result']);
        $this->assertContains(array('themes', 'themes'), Sitepulse_Test_WP::$cache_deletes);
        $this->assertContains(array('update_themes', 'fetched'), Sitepulse_Test_WP::$cache_deletes, 'WordPress checks for theme updates afresh.');
    }

    /** @dataProvider itemsWithoutPackages */
    public function test_items_wordpress_org_does_not_have_are_not_rolled_back(callable $setup, string $item, string $error): void {
        $this->plugin('seo/seo.php', '1.1');
        $this->plugin('hello.php', '1.7');
        $setup($this);
        $this->rollback(array('item' => $item));
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => $error), $this->results());
        $this->assertSame(array(), Sitepulse_Test_Upgrades::$calls, 'The site is left as it is: no maintenance mode, nothing installed.');
    }

    public static function itemsWithoutPackages(): array {
        return array(
            'not installed' => array(static function () {}, 'forms/forms.php', 'not_installed'),
            'not in WordPress\'s update check' => array(static function () {}, 'seo/seo.php', 'no_package'),
            'a premium plugin' => array(static function (self $test) { $test->wordpressOrg('plugin', 'seo/seo.php', 'response', array('slug' => 'seo', 'package' => 'https://seo.example/download/seo.zip')); }, 'seo/seo.php', 'no_package'),
            'a plugin of one file' => array(static function (self $test) { $test->wordpressOrg('plugin', 'hello.php', 'no_update', array('slug' => 'hello-dolly', 'package' => 'https://downloads.wordpress.org/plugin/hello-dolly.1.7.2.zip')); }, 'hello.php', 'no_package'),
            'a version WordPress.org does not have' => array(static function (self $test) {
                $test->wordpressOrg('plugin', 'seo/seo.php', 'no_update', array('slug' => 'seo', 'package' => 'https://downloads.wordpress.org/plugin/seo.1.1.zip'));
                Sitepulse_Test_Upgrades::$packages[self::PACKAGE] = 404;
            }, 'seo/seo.php', 'no_package'),
        );
    }

    /** @dataProvider unansweredPackageChecks */
    public function test_a_rollback_wordpress_org_could_not_say_anything_about_is_left_to_the_install($answer): void {
        $this->seoFromWordpressOrg();
        Sitepulse_Test_Upgrades::$packages[self::PACKAGE] = $answer;
        $this->rollback();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame('done', $this->results()['status']);
    }

    public static function unansweredPackageChecks(): array {
        return array(
            'no answer' => array(new WP_Error('http_request_failed', 'Operation timed out')),
            'a server error' => array(503),
            'a redirect' => array(302),
        );
    }

    /** @dataProvider failedRollbacks */
    public function test_a_rollback_wordpress_could_not_install_fails(callable $install, array $result): void {
        $this->plugin('seo/seo.php', '1.1');
        $this->wordpressOrg('plugin', 'seo/seo.php', 'no_update', array('slug' => 'seo', 'package' => 'https://downloads.wordpress.org/plugin/seo.1.1.zip'));
        Sitepulse_Test_Upgrades::$outcomes['install:' . self::PACKAGE] = $install->bindTo($this, self::class);
        $this->rollback();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => array('kind' => 'plugin', 'id' => 'seo/seo.php') + $result, 'error' => 'install_failed'), $this->results());
        $this->assertFalse(get_option(Sitepulse_Remote_Recovery::UNDONE_OPTION));
    }

    public static function failedRollbacks(): array {
        return array(
            'the files could not be copied' => array(function () { return new WP_Error('copy_failed_dir', 'Could not copy files.'); }, array('from' => '1.1', 'to' => '1.1')),
            'WordPress.org refused the download it said it had' => array(function () { return new WP_Error('download_failed', 'Not Found'); }, array('from' => '1.1', 'to' => '1.1')),
            'another version arrived' => array(function () { $this->plugin('seo/seo.php', '0.9'); return true; }, array('from' => '1.1', 'to' => '0.9')),
            'the plugin is gone' => array(function () { unset(Sitepulse_Test_WP::$plugins['seo/seo.php']); return false; }, array('from' => '1.1', 'to' => null)),
        );
    }

    public function test_a_rollback_that_throws_leaves_maintenance_mode_and_frees_the_recovery(): void {
        $this->seoFromWordpressOrg();
        Sitepulse_Test_Upgrades::$outcomes['install:' . self::PACKAGE] = static function () {
            throw new TypeError('A callback hooked to upgrader_process_complete failed');
        };
        $this->rollback();

        try {
            Sitepulse_Remote_Recovery::run_job();
            $this->fail('The exception should end the request.');
        } catch (TypeError $e) {
            // The cron request ends with a fatal error; WordPress still runs its shutdown actions.
            do_action('shutdown');
        }

        $this->assertSame(array(array('maintenance', true), array('install', self::PACKAGE, array('overwrite_package' => true)), array('maintenance', false)), Sitepulse_Test_Upgrades::$calls, 'Out of maintenance mode.');
        $this->assertFalse(Sitepulse_Remote_Events::$updating, 'WordPress\'s updates are reported again.');
        $this->assertFalse(get_transient(Sitepulse_Remote_Recovery::LOCK), 'The next request does not wait for the lock to expire.');
        $this->assertFalse(has_action('shutdown'));
        $this->assertTrue($this->job()['started']);

        // The next WP-Cron request.
        $this->api();
        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'interrupted'), $this->results());
        $this->assertCount(2, array_filter(Sitepulse_Test_Upgrades::$calls, static function ($call) { return 'maintenance' === $call[0]; }), 'Nothing is installed twice.');
    }

    public function test_a_request_that_ends_while_rolling_back_leaves_maintenance_mode_as_it_ends(): void {
        $this->seoFromWordpressOrg();
        Sitepulse_Test_Upgrades::$outcomes['install:' . self::PACKAGE] = function () {
            // A PHP time limit: WordPress runs its shutdown actions, then the request is gone.
            do_action('shutdown');
            $this->assertSame(array('maintenance', false), end(Sitepulse_Test_Upgrades::$calls));
            $this->assertFalse(Sitepulse_Remote_Events::$updating);
            $this->assertFalse(get_transient(Sitepulse_Remote_Recovery::LOCK), 'The next request does not wait for the lock to expire.');
            throw new RuntimeException('Maximum execution time exceeded');
        };
        $this->rollback();

        try {
            Sitepulse_Remote_Recovery::run_job();
            $this->fail('The request should have ended.');
        } catch (RuntimeException $e) {
            $this->assertSame('Maximum execution time exceeded', $e->getMessage());
        }

        $this->assertFalse(has_action('shutdown'));
        $this->api();
        Sitepulse_Remote_Recovery::run_job();
        $this->assertSame('interrupted', $this->results()['error']);
    }

    public function test_wordpress_does_not_update_again_to_a_version_a_recovery_undid(): void {
        Sitepulse_Remote_Recovery::init();
        $this->seoFromWordpressOrg();
        $this->rollback();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('plugin:seo/seo.php' => array('kind' => 'plugin', 'item' => 'seo/seo.php', 'version' => '1.1')), $this->undone());
        $this->assertEqualsWithDelta(time(), get_option(Sitepulse_Remote_Recovery::UNDONE_OPTION)['plugin:seo/seo.php']['at'], 2);
        $this->assertArrayNotHasKey(Sitepulse_Remote_Recovery::UNDONE_OPTION, wp_load_alloptions(), 'Read only when WordPress updates.');
        // WordPress offers 1.1 again.
        $this->assertFalse(apply_filters('auto_update_plugin', true, (object) array('plugin' => 'seo/seo.php', 'new_version' => '1.1')));
        $this->assertFalse(apply_filters('auto_update_plugin', null, (object) array('plugin' => 'seo/seo.php', 'new_version' => '1.1')), 'Its Plugins screen shows automatic updates off for it.');
        $this->assertTrue(apply_filters('auto_update_plugin', true, (object) array('plugin' => 'seo/seo.php', 'new_version' => '1.2')), 'A newer release is up to the site\'s settings.');
        $this->assertNull(apply_filters('auto_update_plugin', null, (object) array('plugin' => 'forms/forms.php', 'new_version' => '1.1')));
        $this->assertTrue(apply_filters('auto_update_theme', true, (object) array('theme' => 'seo/seo.php', 'new_version' => '1.1')), 'Only that plugin.');
        $this->assertNull(apply_filters('auto_update_plugin', null, null));
    }

    public function test_wordpress_does_not_update_again_to_a_theme_version_a_recovery_undid(): void {
        Sitepulse_Remote_Recovery::init();
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup(array('themes' => array('themes.zip')))));
        $this->theme('astra', '4.1');
        $this->restore(array('kind' => 'theme', 'item' => 'astra', 'version' => '4.0'));
        Sitepulse_Test_Backups::$restore = function () { $this->theme('astra', '4.0'); return true; };
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame('done', $this->results()['status']);
        $this->assertSame(array('theme:astra' => array('kind' => 'theme', 'item' => 'astra', 'version' => '4.1')), $this->undone());
        $this->assertFalse(apply_filters('auto_update_theme', true, array('theme' => 'astra', 'new_version' => '4.1')));
        $this->assertTrue(apply_filters('auto_update_plugin', true, (object) array('plugin' => 'astra', 'new_version' => '4.1')));
    }

    /** @dataProvider undoneVersions */
    public function test_an_undone_version_is_held_back_until_a_newer_one_is_installed_or_for_90_days($entry, string $installed, ?bool $update, bool $kept): void {
        Sitepulse_Remote_Recovery::init();
        $this->plugin('seo/seo.php', $installed);
        update_option(Sitepulse_Remote_Recovery::UNDONE_OPTION, array('plugin:seo/seo.php' => $entry), false);

        $this->assertSame($update, apply_filters('auto_update_plugin', true, (object) array('plugin' => 'seo/seo.php', 'new_version' => '1.1')));
        $this->assertSame($kept ? array('plugin:seo/seo.php' => $entry) : false, get_option(Sitepulse_Remote_Recovery::UNDONE_OPTION));
    }

    public static function undoneVersions(): array {
        $entry = static function (int $age) { return array('kind' => 'plugin', 'item' => 'seo/seo.php', 'version' => '1.1', 'at' => time() - $age); };
        return array(
            'undone yesterday' => array($entry(86400), '1.0', false, true),
            'undone almost 90 days ago' => array($entry(Sitepulse_Remote_Recovery::UNDONE_SECONDS - 3600), '1.0', false, true),
            'undone 90 days ago' => array($entry(Sitepulse_Remote_Recovery::UNDONE_SECONDS), '1.0', true, false),
            'a newer version was installed since' => array($entry(86400), '1.2', true, false),
            'the undone version was installed by hand' => array($entry(86400), '1.1', false, true),
            'not one' => array('corrupt', '1.0', true, false),
            'of no known kind' => array(array('kind' => 'core') + $entry(86400), '1.0', true, false),
            'without its version' => array(array_diff_key($entry(86400), array('version' => 1)), '1.0', true, false),
        );
    }

    public function test_a_later_undo_of_the_same_item_replaces_the_version_held_back_and_others_stay(): void {
        Sitepulse_Remote_Recovery::init();
        $this->plugin('forms/forms.php', '2.0');
        update_option(Sitepulse_Remote_Recovery::UNDONE_OPTION, array(
            'plugin:seo/seo.php' => array('kind' => 'plugin', 'item' => 'seo/seo.php', 'version' => '0.9', 'at' => time() - 86400),
            'plugin:forms/forms.php' => array('kind' => 'plugin', 'item' => 'forms/forms.php', 'version' => '2.1', 'at' => time() - 86400),
        ), false);
        $this->seoFromWordpressOrg();
        $this->rollback();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('kind' => 'plugin', 'item' => 'seo/seo.php', 'version' => '1.1'), $this->undone()['plugin:seo/seo.php']);
        $this->assertSame(array('plugin:forms/forms.php', 'plugin:seo/seo.php'), array_keys($this->undone()));
        $this->assertFalse(apply_filters('auto_update_plugin', true, (object) array('plugin' => 'forms/forms.php', 'new_version' => '2.1')));
    }

    public function test_a_restore_neither_loses_nor_brings_back_versions_held_back(): void {
        Sitepulse_Test_Backups::updraftplus();
        update_option('updraft_backup_history', array(1760788800 => $this->backup()));
        $this->plugin('forms/forms.php', '2.0');
        $this->plugin('maps/maps.php', '3.0');
        $this->plugin('seo/seo.php', '1.1');
        $held = array('plugin:forms/forms.php' => array('kind' => 'plugin', 'item' => 'forms/forms.php', 'version' => '2.1', 'at' => time() - 86400));
        update_option(Sitepulse_Remote_Recovery::UNDONE_OPTION, $held, false);
        $this->restore(array('version' => '1.0'));
        Sitepulse_Test_Backups::$restore = function () {
            // The backup is older than the undo of forms 2.1, and remembers one forgotten since.
            Sitepulse_Test_Backups::restore_options(array(
                'sitepulse_license_key' => SITEPULSE_TEST_LICENSE,
                Sitepulse_Remote_Recovery::UNDONE_OPTION => array('plugin:maps/maps.php' => array('kind' => 'plugin', 'item' => 'maps/maps.php', 'version' => '3.1', 'at' => time() - 172800)),
            ));
            $this->plugin('seo/seo.php', '1.0');
            return true;
        };
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame('done', $this->results()['status']);
        $this->assertSame(array('plugin:forms/forms.php', 'plugin:seo/seo.php'), array_keys($this->undone()));
        $this->assertSame('1.1', $this->undone()['plugin:seo/seo.php']['version']);
    }

    public function test_check_in_starts_a_recovery_and_it_is_answered_when_done(): void {
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true, 'inventory_sent_at' => time()));
        $commands = array(array('id' => self::COMMAND, 'type' => 'rollback_item', 'args' => array('kind' => 'theme', 'item' => 'astra', 'version' => '4.0')));
        Sitepulse_Test_WP::$http_queue = array(sitepulse_test_remote_response(array('success' => true, 'remote_management' => array('enabled' => true, 'active' => true), 'commands' => $commands)));
        $this->api();

        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('id' => self::COMMAND, 'status' => 'running', 'result' => null, 'error' => null), sitepulse_test_remote_request_body(1));
        $this->assertSame('rollback_item', $this->job()['type']);
        $this->assertSame(array('Dashboard request started: roll astra back to 4.0.'), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
    }
}

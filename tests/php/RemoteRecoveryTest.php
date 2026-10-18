<?php
require_once __DIR__ . '/Support/RemoteManagement.php';
require_once __DIR__ . '/Support/Database.php';

final class RemoteRecoveryTest extends Sitepulse_Test_Case {
    private const COMMAND = '01JRESTORE0000000000000000';
    private const NONCE = 'abc123def456';
    private const KEY = 'rk_rotated_key_0123456789abcdef0123';

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

    private function results(): array {
        foreach (Sitepulse_Test_WP::$http_requests as $request) {
            if (false !== strpos($request['url'], '/remote/command-result')) { return array_diff_key(json_decode($request['args']['body'], true), array('id' => 1)); }
        }
        $this->fail('No command result was sent.');
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
            'no version' => array('rollback_item', array_diff_key($rollback, array('version' => 1))),
            'a version that is not one' => array('rollback_item', array('version' => '4.0/../x') + $rollback),
        );
    }

    public function test_a_recovery_starts_in_cron_and_runs_alone(): void {
        $this->assertNull($this->rollback());

        $this->assertSame(array('command' => self::COMMAND, 'type' => 'rollback_item', 'started' => false), array_diff_key($this->job(), array('args' => 1)));
        $this->assertEqualsWithDelta(time(), wp_next_scheduled(Sitepulse_Remote_Recovery::JOB_HOOK), 2);
        $this->assertTrue(Sitepulse_Remote_Recovery::running());
        $this->assertSame('update_running', $this->restore()['error']);
        $this->assertSame('update_running', Sitepulse_Remote_Updates::run_command('01JUPDATE00000000000000000', 'update_plugins', array('plugins' => array('seo/seo.php')))['error']);

        delete_option(Sitepulse_Remote_Recovery::JOB_OPTION);
        update_option(Sitepulse_Remote_Updates::JOB_OPTION, array('command' => '01JUPDATE00000000000000000', 'items' => array(), 'results' => array()));
        $this->assertSame('update_running', $this->rollback()['error'], 'Not while an update runs.');
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
        Sitepulse_Test_Backups::$restore = static function () {
            // The database as it was at the time of the backup, while the update was running.
            Sitepulse_Test_WP::$options = array(
                'blogname' => 'Restored',
                'sitepulse_license_key' => SITEPULSE_TEST_LICENSE,
                Sitepulse_Remote_Management::STATE_OPTION => array('active' => true),
                Sitepulse_Remote_Updates::JOB_OPTION => array('command' => '01JUPDATE00000000000000000', 'items' => array('seo/seo.php'), 'results' => array()),
            );
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
    }

    public static function failedRestores(): array {
        return array(
            'UpdraftPlus\'s error' => array(static function () { return new WP_Error('new_move_failed', 'Could not move the new files into place.'); }, 'Could not move the new files into place.', 'new_move_failed'),
            'not finished' => array(static function () { return false; }, 'UpdraftPlus could not restore the backup; its log of restore restore00001 has the details.', false),
            'errors logged' => array(static function () { $GLOBALS['updraftplus']->errors[] = 'Table wp_posts: error'; return true; }, 'UpdraftPlus could not restore the backup; its log of restore restore00001 has the details.', true),
            'an exception' => array(static function () { throw new RuntimeException('Out of disk space'); }, 'Out of disk space', 'restore_failed'),
        );
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
        $this->plugin('seo/seo.php', '1.1');
        $this->wordpressOrg('plugin', 'seo/seo.php', 'no_update', array('slug' => 'seo', 'new_version' => '1.1', 'package' => 'https://downloads.wordpress.org/plugin/seo.1.1.zip'));
        Sitepulse_Test_Upgrades::$outcomes['install:https://downloads.wordpress.org/plugin/seo.1.0.zip'] = function () {
            $this->assertTrue(Sitepulse_Remote_Events::$updating);
            $this->plugin('seo/seo.php', '1.0');
            do_action('upgrader_process_complete', new stdClass(), array('action' => 'install', 'type' => 'plugin'));
            return true;
        };
        $this->rollback();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'done', 'result' => array('kind' => 'plugin', 'id' => 'seo/seo.php', 'from' => '1.1', 'to' => '1.0'), 'error' => null), $this->results());
        $this->assertSame('installing', Sitepulse_Test_Upgrades::$progress[0]['phase']);
        $this->assertSame(array(array('maintenance', true), array('install', 'https://downloads.wordpress.org/plugin/seo.1.0.zip', array('overwrite_package' => true)), array('maintenance', false)), Sitepulse_Test_Upgrades::$calls);
        $this->assertContains(array('plugins', 'plugins'), Sitepulse_Test_WP::$cache_deletes);
        $this->assertFalse(Sitepulse_Remote_Events::$updating);
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
    }

    public static function itemsWithoutPackages(): array {
        return array(
            'not installed' => array(static function () {}, 'forms/forms.php', 'not_installed'),
            'not in WordPress\'s update check' => array(static function () {}, 'seo/seo.php', 'no_package'),
            'a premium plugin' => array(static function (self $test) { $test->wordpressOrg('plugin', 'seo/seo.php', 'response', array('slug' => 'seo', 'package' => 'https://seo.example/download/seo.zip')); }, 'seo/seo.php', 'no_package'),
            'a plugin of one file' => array(static function (self $test) { $test->wordpressOrg('plugin', 'hello.php', 'no_update', array('slug' => 'hello-dolly', 'package' => 'https://downloads.wordpress.org/plugin/hello-dolly.1.7.2.zip')); }, 'hello.php', 'no_package'),
            'a version WordPress.org does not have' => array(static function (self $test) { $test->wordpressOrg('plugin', 'seo/seo.php', 'no_update', array('slug' => 'seo', 'package' => 'https://downloads.wordpress.org/plugin/seo.1.1.zip')); }, 'seo/seo.php', 'no_package'),
        );
    }

    /** @dataProvider failedRollbacks */
    public function test_a_rollback_wordpress_could_not_install_fails(callable $install, array $result): void {
        $this->plugin('seo/seo.php', '1.1');
        $this->wordpressOrg('plugin', 'seo/seo.php', 'no_update', array('slug' => 'seo', 'package' => 'https://downloads.wordpress.org/plugin/seo.1.1.zip'));
        Sitepulse_Test_Upgrades::$outcomes['install:https://downloads.wordpress.org/plugin/seo.1.0.zip'] = $install->bindTo($this, self::class);
        $this->rollback();
        $this->api();

        Sitepulse_Remote_Recovery::run_job();

        $this->assertSame(array('status' => 'failed', 'result' => array('kind' => 'plugin', 'id' => 'seo/seo.php') + $result, 'error' => 'install_failed'), $this->results());
    }

    public static function failedRollbacks(): array {
        return array(
            'the files could not be copied' => array(function () { return new WP_Error('copy_failed_dir', 'Could not copy files.'); }, array('from' => '1.1', 'to' => '1.1')),
            'another version arrived' => array(function () { $this->plugin('seo/seo.php', '0.9'); return true; }, array('from' => '1.1', 'to' => '0.9')),
            'the plugin is gone' => array(function () { unset(Sitepulse_Test_WP::$plugins['seo/seo.php']); return false; }, array('from' => '1.1', 'to' => null)),
        );
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

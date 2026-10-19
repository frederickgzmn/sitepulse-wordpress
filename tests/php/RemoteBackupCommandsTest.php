<?php
require_once __DIR__ . '/Support/RemoteManagement.php';
require_once __DIR__ . '/Support/Database.php';

final class RemoteBackupCommandsTest extends Sitepulse_Test_Case {
    private const PRESET = array(
        'files' => array('frequency' => 'weekly', 'keep' => 4, 'include' => array('plugins', 'uploads')),
        'database' => array('frequency' => 'daily', 'keep' => 14),
    );

    protected function setUp(): void {
        parent::setUp();
        Sitepulse_Test_Backups::reset();
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
    }

    protected function tearDown(): void { Sitepulse_Test_Backups::reset(); unset($GLOBALS['wpdb']); parent::tearDown(); }

    private function run_command(string $type, array $args = array()): array { return Sitepulse_Remote_Backups::run_command($type, $args); }

    private function updraftplus(): void { Sitepulse_Test_Backups::updraftplus(); }

    private function backwpup(array $jobs = array()): void {
        Sitepulse_Test_Backups::$backwpup_version = '5.7.8';
        Sitepulse_Test_Backups::$destinations = array('FOLDER' => array(), 'S3' => array());
        Sitepulse_Test_Backups::$jobs = $jobs;
    }

    private function job(array $settings): array {
        return $settings + array('name' => 'Job', 'type' => array('DBDUMP'), 'destinations' => array('FOLDER'), 'activetype' => 'wpcron', 'frequency' => 'daily', 'cron' => '30 3 * * *', 'maxbackups' => 15);
    }

    private function cron(int $timestamp, string $hook, array $args = array()): void {
        Sitepulse_Test_WP::$cron[$timestamp][$hook][md5(serialize($args))] = array('schedule' => false, 'args' => $args);
    }

    private function calls(string $name): array {
        return array_values(array_filter(Sitepulse_Test_Backups::$calls, static function ($call) use ($name) { return $call[0] === $name; }));
    }

    public function test_the_dashboard_pick_wins_while_that_plugin_is_active_and_supported(): void {
        $this->updraftplus();
        $this->backwpup();
        $this->assertSame(array('updraftplus', 'backwpup'), array_column(Sitepulse_Remote_Backups::providers(), 'id'));
        $this->assertSame('updraftplus', Sitepulse_Remote_Backups::provider()['id'], 'UpdraftPlus when neither has backed up.');

        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('preferred_provider' => 'backwpup'));
        $this->assertSame('backwpup', Sitepulse_Remote_Backups::provider()['id']);

        Sitepulse_Test_Backups::$backwpup_version = '4.1.7';
        $this->assertSame('updraftplus', Sitepulse_Remote_Backups::provider()['id'], 'An unsupported pick is ignored.');
        Sitepulse_Test_Backups::$backwpup_version = '';
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('preferred_provider' => 'updraftplus'));
        unset($GLOBALS['updraftplus']);
        $this->assertNull(Sitepulse_Remote_Backups::provider());
    }

    /** @dataProvider recentBackups */
    public function test_without_a_pick_the_plugin_with_the_latest_successful_backup_is_managed(array $updraftplus, array $backwpup, string $expected): void {
        $this->updraftplus();
        $this->backwpup(array(1 => $this->job($backwpup)));
        update_option('updraft_last_backup', $updraftplus);
        Sitepulse_Test_Backups::$log_headers = array('/logs/ok.html' => array('errors' => '0'), '/logs/failed.html' => array('errors' => '2'));

        $this->assertSame($expected, Sitepulse_Remote_Backups::provider()['id']);
    }

    public static function recentBackups(): array {
        return array(
            'BackWPup backed up later' => array(array('backup_time' => 1760400000, 'success' => 1), array('lastrun' => 1760500000, 'logfile' => '/logs/ok.html'), 'backwpup'),
            'UpdraftPlus backed up later' => array(array('backup_time' => 1760600000, 'success' => 1), array('lastrun' => 1760500000, 'logfile' => '/logs/ok.html'), 'updraftplus'),
            'BackWPup\'s later backup failed' => array(array('backup_time' => 1760400000, 'success' => 1), array('lastrun' => 1760500000, 'logfile' => '/logs/failed.html'), 'updraftplus'),
            'UpdraftPlus\'s backup failed' => array(array('backup_time' => 1760600000, 'success' => 0), array('lastrun' => 1760500000, 'logfile' => '/logs/ok.html'), 'backwpup'),
        );
    }

    public function test_a_failing_plugin_counts_as_never_backed_up(): void {
        $this->updraftplus();
        $this->backwpup(array(1 => $this->job(array('lastrun' => 1760500000, 'logfile' => '/logs/ok.html'))));
        Sitepulse_Test_Backups::$log_headers = array('/logs/ok.html' => array('errors' => '0'));
        Sitepulse_Test_Backups::$throw = true;
        $this->assertSame('updraftplus', Sitepulse_Remote_Backups::provider()['id']);
    }

    /** @dataProvider withoutBackupPlugin */
    public function test_backup_commands_need_a_supported_backup_plugin(callable $setup): void {
        $setup();
        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'no_backup_plugin'), $this->run_command('backup_now'));
    }

    public static function withoutBackupPlugin(): array {
        return array(
            'none' => array(static function () {}),
            'old UpdraftPlus' => array(static function () { Sitepulse_Test_Backups::updraftplus('1.25.8'); }),
        );
    }

    /** @dataProvider updraftplusBackups */
    public function test_updraftplus_backup_now_queues_its_own_backup_in_cron(array $args, string $hook, string $what, array $options = array()): void {
        $this->updraftplus();

        $this->assertSame(array('status' => 'done', 'result' => array('provider' => 'updraftplus', 'what' => $what, 'job' => 'abc123def456'), 'error' => null), $this->run_command('backup_now', $args));

        $this->assertEquals(array((object) array('timestamp' => Sitepulse_Test_WP::$scheduled[0]->timestamp, 'schedule' => false, 'hook' => $hook, 'args' => array(array('nocloud' => false, 'use_nonce' => 'abc123def456', 'label' => 'SitePulse', 'always_keep' => false) + $options))), Sitepulse_Test_WP::$scheduled);
        $this->assertEqualsWithDelta(time(), Sitepulse_Test_WP::$scheduled[0]->timestamp, 2);
    }

    public static function updraftplusBackups(): array {
        return array(
            'full by default' => array(array(), 'updraft_backupnow_backup_all', 'full'),
            'database' => array(array('what' => 'database'), 'updraft_backupnow_backup_database', 'database'),
            'files' => array(array('what' => 'files'), 'updraft_backupnow_backup', 'files'),
            'the plugins and the database' => array(array('what' => 'plugins'), 'updraft_backupnow_backup_all', 'plugins', array('restrict_files_to_override' => array('plugins'))),
            'the themes and the database' => array(array('what' => 'themes'), 'updraft_backupnow_backup_all', 'themes', array('restrict_files_to_override' => array('themes'))),
        );
    }

    /** @dataProvider busyUpdraftplus */
    public function test_updraftplus_runs_one_backup_at_a_time(callable $busy): void {
        $this->updraftplus();
        $busy($this);
        $this->assertSame('backup_running', $this->run_command('backup_now')['error']);
        $this->assertSame(array(), Sitepulse_Test_WP::$scheduled);
    }

    public static function busyUpdraftplus(): array {
        return array(
            'a backup is running' => array(static function (self $test) { $test->cron(1760544000, 'updraft_backup_resume', array(1, 'run111111111')); Sitepulse_Test_WP::$site_options['updraft_jobdata_run111111111'] = array('jobstatus' => 'filescreating'); }),
            'a backup is queued' => array(static function (self $test) { $test->cron(1760544000, 'updraft_backupnow_backup_database', array(array('use_nonce' => 'que111111111'))); }),
        );
    }

    public function test_backup_now_refuses_unknown_contents(): void {
        $this->updraftplus();
        $this->assertSame('invalid_arguments', $this->run_command('backup_now', array('what' => 'everything'))['error']);
        $this->assertSame('invalid_arguments', $this->run_command('backup_now', array('what' => array('full')))['error']);
    }

    public function test_updraftplus_cancel_removes_queued_backups_and_stops_running_jobs(): void {
        $this->updraftplus();
        $this->cron(1760544000, 'updraft_backupnow_backup_all', array(array('use_nonce' => 'que111111111')));
        $this->cron(1760544000, 'updraft_backup_resume', array(2, 'run111111111'));
        $this->cron(1760544060, 'updraft_backup_resume', array(1, 'gone11111111'));
        $this->cron(1760544060, 'updraft_backup_resume', array(5, 'done11111111'));
        Sitepulse_Test_WP::$cron['version'] = 2;
        Sitepulse_Test_WP::$scheduled = array((object) array('timestamp' => 1760544000, 'hook' => 'updraft_backupnow_backup_all', 'args' => array(array('use_nonce' => 'que111111111'))));
        Sitepulse_Test_WP::$site_options = array(
            'updraft_jobdata_run111111111' => array('jobstatus' => 'clouduploading'),
            'updraft_jobdata_gone11111111' => array('jobstatus' => 'dbcreating'),
            'updraft_jobdata_done11111111' => array('jobstatus' => 'finished'),
        );
        Sitepulse_Test_Backups::$deletable = array('run111111111');

        $this->assertSame(array('status' => 'done', 'result' => array('provider' => 'updraftplus', 'cancelled' => 2), 'error' => null), $this->run_command('cancel_backup'));

        $this->assertSame(array(), Sitepulse_Test_WP::$scheduled);
        $this->assertSame(array(array('include', 'admin.php', 'include_once'), array('activejobs_delete', 'run111111111'), array('activejobs_delete', 'gone11111111')), Sitepulse_Test_Backups::$calls);
    }

    public function test_cancelling_when_nothing_runs_stops_nothing(): void {
        $this->updraftplus();
        $this->assertSame(array('provider' => 'updraftplus', 'cancelled' => 0), $this->run_command('cancel_backup')['result']);
        $this->assertSame(array(), Sitepulse_Test_Backups::$calls, 'UpdraftPlus\'s admin code is only loaded to stop a job.');
    }

    public function test_updraftplus_preset_uses_its_schedulers_and_settings(): void {
        $this->updraftplus();
        update_option('updraft_interval', 'monthly');
        update_option('updraft_interval_database', 'monthly');
        update_option('updraft_include_themes', 1);
        update_option('updraft_service', array('s3'));

        $result = $this->run_command('apply_backup_preset', array('preset_id' => 7, 'name' => 'Agency default', 'preset' => self::PRESET));

        $this->assertSame('done', $result['status']);
        $this->assertSame(array('provider', 'preset', 'created', 'schedules'), array_keys($result['result']));
        $this->assertSame(array('Agency default', array()), array($result['result']['preset'], $result['result']['created']));
        $this->assertSame(array(array('Files', 'weekly', 4, array('s3')), array('Database', 'daily', 14, array('s3'))), array_map(static function ($schedule) {
            return array($schedule['name'], $schedule['frequency'], $schedule['keep'], $schedule['destinations']);
        }, $result['result']['schedules']));
        $this->assertSame(array(array('schedule_backup', 'weekly', 'monthly'), array('schedule_backup_database', 'daily', 'monthly')), Sitepulse_Test_Backups::$calls, 'Each scheduler runs before its setting changes.');
        $this->assertSame(array(1, 0, 1, 0), array(get_option('updraft_include_plugins'), get_option('updraft_include_themes'), get_option('updraft_include_uploads'), get_option('updraft_include_others')));
    }

    public function test_a_preset_changes_only_what_it_sets(): void {
        $this->updraftplus();
        update_option('updraft_interval', 'monthly');
        update_option('updraft_retain', 9);
        update_option('updraft_include_themes', 1);

        $this->run_command('apply_backup_preset', array('preset' => array('database' => array('frequency' => 'manual', 'keep' => 1))));
        $this->run_command('apply_backup_preset', array('preset' => array('files' => array('frequency' => 'daily', 'keep' => 3))));

        $this->assertSame(array('daily', 3, 'manual', 1, 1), array(get_option('updraft_interval'), get_option('updraft_retain'), get_option('updraft_interval_database'), get_option('updraft_retain_db'), get_option('updraft_include_themes')));
    }

    /** @dataProvider invalidPresets */
    public function test_invalid_presets_are_refused(array $args): void {
        $this->updraftplus();
        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'invalid_preset'), $this->run_command('apply_backup_preset', $args));
        $this->assertSame(array(), Sitepulse_Test_Backups::$calls);
    }

    public static function invalidPresets(): array {
        return array(
            'no preset' => array(array()),
            'not a preset' => array(array('preset' => 'daily')),
            'nothing to set' => array(array('preset' => array('storage' => 's3'))),
            'files not settings' => array(array('preset' => array('files' => 'weekly'))),
            'unknown frequency' => array(array('preset' => array('database' => array('frequency' => 'hourly', 'keep' => 4)))),
            'keep as text' => array(array('preset' => array('database' => array('frequency' => 'daily', 'keep' => '4')))),
            'keeps nothing' => array(array('preset' => array('database' => array('frequency' => 'daily', 'keep' => 0)))),
            'keeps too much' => array(array('preset' => array('database' => array('frequency' => 'daily', 'keep' => 101)))),
            'include not a list' => array(array('preset' => array('files' => array('frequency' => 'daily', 'keep' => 4, 'include' => 'plugins')))),
            'unknown part' => array(array('preset' => array('files' => array('frequency' => 'daily', 'keep' => 4, 'include' => array('wp-config'))))),
        );
    }

    public function test_a_failing_backup_plugin_fails_the_command(): void {
        $this->backwpup();
        Sitepulse_Test_Backups::$throw = true;
        $this->assertSame(array('status' => 'failed', 'result' => null, 'error' => 'backup_plugin_error'), $this->run_command('apply_backup_preset', array('preset' => self::PRESET)));
    }

    public function test_backwpup_backup_now_runs_its_own_backup_now_job(): void {
        $this->backwpup(array(3 => $this->job(array('name' => 'Nightly'))));
        touch(Sitepulse_Test_Backups::backwpup_started()); // Left over from an earlier run.

        $this->assertSame(array('status' => 'done', 'result' => array('provider' => 'backwpup', 'what' => 'database', 'job' => 4), 'error' => null), $this->run_command('backup_now', array('what' => 'database')));

        $this->assertSame(4, Sitepulse_Test_WP::$site_options['backwpup_backup_now_job_id']);
        $job = Sitepulse_Test_Backups::$jobs[4];
        $this->assertSame(array('Backup Now', array('DBDUMP'), array('FOLDER'), '', false, true, 15), array($job['name'], $job['type'], $job['destinations'], $job['activetype'], $job['tempjob'], $job['backup_now'], $job['maxbackups']));
        $this->assertSame(array(array('get_jobrun_url', 'runnow', 4)), Sitepulse_Test_Backups::$calls);
        $this->assertFileDoesNotExist(Sitepulse_Test_Backups::backwpup_started(), 'The start file is removed, as BackWPup does.');

        $this->assertSame(4, $this->run_command('backup_now')['result']['job'], 'The same job is reused.');
        $this->assertSame(array('FILE', 'DBDUMP', 'WPPLUGIN'), Sitepulse_Test_Backups::$jobs[4]['type']);
    }

    public function test_backwpup_backs_up_one_folder_with_the_database(): void {
        $this->backwpup();
        $folders = static function (): array {
            return array_intersect_key(Sitepulse_Test_Backups::$jobs[1], array_flip(Sitepulse_Remote_Backups::BACKWPUP_FOLDERS));
        };

        $this->assertSame(array('provider' => 'backwpup', 'what' => 'themes', 'job' => 1), $this->run_command('backup_now', array('what' => 'themes'))['result']);
        $this->assertSame(array('FILE', 'DBDUMP', 'WPPLUGIN'), Sitepulse_Test_Backups::$jobs[1]['type']);
        $this->assertSame(array('backuproot' => false, 'backupcontent' => false, 'backupplugins' => false, 'backupthemes' => true, 'backupuploads' => false, 'backupspecialfiles' => false), $folders());

        $this->run_command('backup_now', array('what' => 'plugins'));
        $this->assertSame(array('backupplugins' => true, 'backupthemes' => false), array_intersect_key($folders(), array_flip(array('backupplugins', 'backupthemes'))));

        $this->run_command('backup_now');
        $this->assertSame(array_fill_keys(Sitepulse_Remote_Backups::BACKWPUP_FOLDERS, true), $folders(), 'A full backup saves every folder again.');
    }

    public function test_backwpup_backup_now_reports_a_job_that_did_not_start(): void {
        $this->backwpup();
        Sitepulse_Test_Backups::$job_starts = false;
        $this->assertSame('start_failed', $this->run_command('backup_now', array('what' => 'files'))['error']);
        $this->assertSame(array('FILE', 'WPPLUGIN'), Sitepulse_Test_Backups::$jobs[1]['type']);
    }

    public function test_backwpup_runs_one_backup_at_a_time_and_cancels_the_running_one(): void {
        $this->backwpup();
        $this->assertSame(array('provider' => 'backwpup', 'cancelled' => 0), $this->run_command('cancel_backup')['result']);

        Sitepulse_Test_Backups::$working = new BackWPup_Job();
        $this->assertSame('backup_running', $this->run_command('backup_now')['error']);
        $this->assertSame(array('provider' => 'backwpup', 'cancelled' => 1), $this->run_command('cancel_backup')['result']);
        $this->assertSame(array(array('user_abort')), Sitepulse_Test_Backups::$calls);
    }

    public function test_backwpup_preset_configures_jobs_by_what_they_save(): void {
        $this->backwpup(array(
            1 => $this->job(array('name' => 'Files', 'type' => array('FILE', 'WPPLUGIN'), 'destinations' => array('FOLDER', 'S3', 'GONE'), 'frequency' => 'monthly')),
            2 => $this->job(array('name' => 'Database', 'frequency' => 'daily', 'cron' => '30 3 * * *')),
            3 => $this->job(array('name' => 'Export', 'type' => array('WPEXP'), 'frequency' => 'monthly')),
            4 => $this->job(array('name' => 'Backup Now', 'backup_now' => true, 'activetype' => '')),
            5 => $this->job(array('name' => 'First backup', 'tempjob' => true, 'activetype' => '')),
        ));
        Sitepulse_Test_WP::$scheduled = array((object) array('timestamp' => 1, 'hook' => 'backwpup_cron', 'args' => array('arg' => 1)));

        $result = $this->run_command('apply_backup_preset', array('name' => 'Agency default', 'preset' => self::PRESET));

        $this->assertSame(array(), $result['result']['created']);
        $jobs = Sitepulse_Test_Backups::$jobs;
        $this->assertSame(array('weekly', '0 0 * * 0', 'wpcron', 4, 4), array($jobs[1]['frequency'], $jobs[1]['cron'], $jobs[1]['activetype'], $jobs[1]['maxbackups'], $jobs[1]['s3maxbackups']));
        $this->assertSame(array(true, false, true, false), array($jobs[1]['backupplugins'], $jobs[1]['backupthemes'], $jobs[1]['backupuploads'], $jobs[1]['backupcontent']));
        $this->assertSame(array('daily', '30 3 * * *', 14), array($jobs[2]['frequency'], $jobs[2]['cron'], $jobs[2]['maxbackups']), 'A job keeps its time of day when its frequency stays.');
        $this->assertArrayNotHasKey('backupplugins', $jobs[2]);
        $this->assertSame('monthly', $jobs[3]['frequency'], 'Jobs that save neither are left alone.');
        $this->assertSame(array('', ''), array($jobs[4]['activetype'], $jobs[5]['activetype']), 'BackWPup\'s "Backup Now" and temporary jobs stay unscheduled.');
        $this->assertSame(array(array(1760572800 + 9, array('arg' => 1)), array(1760572800 + 10, array('arg' => 2))), array_map(static function ($event) {
            return array($event->timestamp, $event->args);
        }, Sitepulse_Test_WP::$scheduled));
        $this->assertSame(array('Files', 'Database', 'Export'), array_column($result['result']['schedules'], 'name'));
    }

    public function test_a_backwpup_job_saving_both_gets_the_more_frequent_schedule(): void {
        $this->backwpup(array(1 => $this->job(array('type' => array('FILE', 'DBDUMP', 'WPPLUGIN'), 'frequency' => 'weekly', 'cron' => '0 2 * * 1'))));

        $this->run_command('apply_backup_preset', array('preset' => self::PRESET));

        $job = Sitepulse_Test_Backups::$jobs[1];
        $this->assertSame(array('daily', '0 0 * * *', 14, true), array($job['frequency'], $job['cron'], $job['maxbackups'], $job['backupplugins']));

        $this->run_command('apply_backup_preset', array('preset' => array('files' => array('frequency' => 'manual', 'keep' => 2), 'database' => array('frequency' => 'manual', 'keep' => 3))));
        $this->assertSame(array('', 3), array(Sitepulse_Test_Backups::$jobs[1]['activetype'], Sitepulse_Test_Backups::$jobs[1]['maxbackups']));
        $this->assertSame(array(), Sitepulse_Test_WP::$scheduled, 'A manual job is no longer scheduled.');
    }

    public function test_backwpup_gets_one_job_for_both_when_it_has_none_and_they_share_a_schedule(): void {
        $this->backwpup(array(4 => $this->job(array('name' => 'Backup Now', 'backup_now' => true, 'activetype' => '', 'destinations' => array('S3')))));

        $result = $this->run_command('apply_backup_preset', array('preset' => array('files' => array('frequency' => 'weekly', 'keep' => 4), 'database' => array('frequency' => 'weekly', 'keep' => 8))));

        $this->assertSame(array('Files and database (SitePulse)'), $result['result']['created']);
        $job = Sitepulse_Test_Backups::$jobs[5];
        $this->assertSame(array(array('FILE', 'DBDUMP', 'WPPLUGIN'), array('FOLDER'), 'weekly', 'wpcron', 8), array($job['type'], $job['destinations'], $job['frequency'], $job['activetype'], $job['maxbackups']), 'With no other jobs, archives stay on this server.');
        $this->assertSame('backwpup_cron', Sitepulse_Test_WP::$scheduled[0]->hook);
    }

    public function test_backwpup_gets_a_job_for_what_no_job_saves_with_the_existing_storage(): void {
        $this->backwpup(array(2 => $this->job(array('name' => 'Database', 'destinations' => array('S3')))));

        $result = $this->run_command('apply_backup_preset', array('preset' => self::PRESET));

        $this->assertSame(array('Files (SitePulse)'), $result['result']['created']);
        $job = Sitepulse_Test_Backups::$jobs[3];
        $this->assertSame(array(array('FILE', 'WPPLUGIN'), array('S3'), 'weekly', 4, false), array($job['type'], $job['destinations'], $job['frequency'], $job['s3maxbackups'], $job['backupthemes']));

        $this->backwpup();
        $result = $this->run_command('apply_backup_preset', array('preset' => array('files' => array('frequency' => 'manual', 'keep' => 2), 'database' => array('frequency' => 'monthly', 'keep' => 6))));
        $this->assertSame(array('Database (SitePulse)'), $result['result']['created'], 'An unscheduled part gets no job.');
        $this->assertSame(array('DBDUMP'), Sitepulse_Test_Backups::$jobs[1]['type']);
    }

    public function test_the_backwpup_backup_now_job_is_not_a_schedule_but_its_archives_are_backups(): void {
        $this->backwpup(array(4 => $this->job(array('name' => 'Backup Now', 'backup_now' => true, 'activetype' => '', 'lastrun' => 1760500000))));
        Sitepulse_Test_Backups::$destinations = array('FOLDER' => array('4_FOLDER' => array(array('file' => '/b/now.tar', 'filesize' => 10, 'time' => 1760500100))));

        $status = Sitepulse_Remote_Backups::status();

        $this->assertSame(array(), $status['schedules']);
        $this->assertSame('Backup Now', $status['recent'][0]['label']);
        $this->assertSame('2025-10-15T03:46:40Z', $status['last']['time']);
    }

    public function test_check_in_runs_backup_commands_then_sends_the_changed_inventory(): void {
        update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
        $this->updraftplus();
        $this->backwpup();
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true, 'inventory_sent_at' => time(), 'provider' => array('id' => 'updraftplus', 'version' => '1.26.9', 'supported' => true)));
        $commands = array(
            array('id' => '01JBACKUP00000000000000000', 'type' => 'backup_now', 'args' => array('what' => 'database')),
            array('id' => '01JPRESET00000000000000000', 'type' => 'apply_backup_preset', 'args' => array('preset_id' => 1, 'name' => 'Nightly', 'preset' => array('database' => array('frequency' => 'daily', 'keep' => 7)))),
            array('id' => '01JCANCEL00000000000000000', 'type' => 'cancel_backup'),
        );
        $remote = array('enabled' => true, 'active' => true, 'check_in_seconds' => 300, 'inventory_seconds' => 21600, 'backup_provider' => 'backwpup');
        Sitepulse_Test_WP::$http_queue = array(sitepulse_test_remote_response(array('success' => true, 'remote_management' => $remote, 'commands' => $commands)));
        for ($i = 0; $i < 5; $i++) { Sitepulse_Test_WP::$http_queue[] = sitepulse_test_remote_response(); }

        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('updraftplus', 'backwpup'), array_column(sitepulse_test_remote_request_body(0)['backup_providers'], 'id'));
        $this->assertSame(array('check-in', 'command-result', 'command-result', 'command-result', 'inventory', 'events'), array_map('sitepulse_test_remote_endpoint', array_keys(Sitepulse_Test_WP::$http_requests)));
        $this->assertSame('backwpup', sitepulse_test_remote_request_body(1)['result']['provider'], 'The dashboard\'s pick applies from this check-in on.');
        $this->assertSame('backup_provider.changed', sitepulse_test_remote_request_body(5)['events'][0]['type']);
        $this->assertSame('backwpup', Sitepulse_Remote_Management::state()['preferred_provider']);
        $this->assertSame(array(
            'Dashboard request done: back up now (database).',
            'Dashboard request done: apply the backup preset "Nightly".',
            'Dashboard request done: cancel the running backup.',
        ), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
    }

    public function test_failed_backup_commands_are_logged_and_leave_the_inventory_alone(): void {
        update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true, 'inventory_sent_at' => time()));
        $remote = array('enabled' => true, 'active' => true, 'backup_provider' => 'duplicator');
        Sitepulse_Test_WP::$http_queue = array(sitepulse_test_remote_response(array('success' => true, 'remote_management' => $remote, 'commands' => array(array('id' => '01JBACKUP00000000000000000', 'type' => 'backup_now', 'args' => 'corrupt')))), sitepulse_test_remote_response());

        Sitepulse_Remote_Management::check_in();

        $this->assertSame(array('check-in', 'command-result'), array_map('sitepulse_test_remote_endpoint', array_keys(Sitepulse_Test_WP::$http_requests)));
        $this->assertSame(array('Dashboard request failed: back up now (full) (no_backup_plugin).'), array_column(Sitepulse_Remote_Management::log_entries(), 'message'));
        $this->assertNull(Sitepulse_Remote_Management::state()['preferred_provider']);
    }
}

<?php
require_once __DIR__ . '/Support/RemoteManagement.php';

final class RemoteBackupsTest extends Sitepulse_Test_Case {
    protected function setUp(): void { parent::setUp(); Sitepulse_Test_Backups::reset(); }
    protected function tearDown(): void { Sitepulse_Test_Backups::reset(); parent::tearDown(); }

    /** Let remote management record events, as it does while the dashboard manages the site. */
    private function activateRemoteManagement(): void {
        update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true));
    }

    private function events(): array { return get_option(Sitepulse_Remote_Management::EVENTS_OPTION, array()); }

    public function test_no_backup_plugin_means_no_provider_and_no_backup_status(): void {
        $this->assertNull(Sitepulse_Remote_Backups::provider());
        $this->assertNull(Sitepulse_Remote_Backups::status());
    }

    /** @dataProvider providers */
    public function test_provider_prefers_a_supported_plugin_then_updraftplus(?string $updraftplus, string $backwpup, ?array $expected): void {
        if ($updraftplus !== null) { Sitepulse_Test_Backups::updraftplus($updraftplus); }
        Sitepulse_Test_Backups::$backwpup_version = $backwpup;
        $this->assertSame($expected, Sitepulse_Remote_Backups::provider());
    }

    public static function providers(): array {
        $updraft = static function ($version, $supported) { return array('id' => 'updraftplus', 'version' => $version, 'supported' => $supported); };
        $backwpup = static function ($version, $supported) { return array('id' => 'backwpup', 'version' => $version, 'supported' => $supported); };
        return array(
            'UpdraftPlus 1.26' => array('1.26.9', '', $updraft('1.26.9', true)),
            'UpdraftPlus 1.25 is too old' => array('1.25.8', '', $updraft('1.25.8', false)),
            'UpdraftPlus without a readable version' => array('', '', null),
            'BackWPup 5' => array(null, '5.7.8', $backwpup('5.7.8', true)),
            'BackWPup 4 is not supported' => array(null, '4.1.7', $backwpup('4.1.7', false)),
            'BackWPup 6 is not supported yet' => array(null, '6.0.0', $backwpup('6.0.0', false)),
            'both supported: UpdraftPlus' => array('1.26.9', '5.7.8', $updraft('1.26.9', true)),
            'old UpdraftPlus and BackWPup 5: BackWPup' => array('1.25.8', '5.7.8', $backwpup('5.7.8', true)),
            'neither supported: UpdraftPlus' => array('1.25.8', '4.1.7', $updraft('1.25.8', false)),
        );
    }

    public function test_unsupported_plugin_reports_only_itself(): void {
        Sitepulse_Test_Backups::$backwpup_version = '4.1.7';
        $this->assertSame(array('provider' => array('id' => 'backwpup', 'version' => '4.1.7', 'supported' => false)), Sitepulse_Remote_Backups::status());
    }

    public function test_updraftplus_status_lists_schedules_running_job_last_and_recent_backups(): void {
        Sitepulse_Test_Backups::updraftplus();
        Sitepulse_Test_WP::$options = array(
            'updraft_interval' => 'weekly', 'updraft_interval_database' => 'daily', 'updraft_retain' => '4', 'updraft_retain_db' => 14,
            'updraft_service' => array('googledrive', 'none', '', 'GoogleDrive', 7),
            'updraft_last_backup' => array('backup_time' => 1760493600, 'success' => 1, 'errors' => array('Upload slow', array('level' => 'warning', 'message' => 'Large file'), array('level' => 'warning'))),
            'updraft_backup_history' => array(
                1760320800 => array('db' => 'backup_db.gz', 'db-size' => 1000, 'service' => 'none', 'nonce' => 'a'),
                1760493600 => array('db' => 'backup_db.gz', 'db1' => 'second.gz', 'db-size' => 2000, 'db1-size' => '500', 'plugins' => array('p.zip'), 'plugins-size' => 3000, 'uploads1-size' => 'unknown', 'service' => array('googledrive'), 'label' => 'Before update'),
                1760407200 => array('uploads' => array('u.zip'), 'uploads-size' => 4000, 'service' => array('s3', 'dropbox')),
                1760234400 => 'not a backup',
                'corrupt' => array('db' => 'not keyed by time'),
            ),
        );
        Sitepulse_Test_WP::$scheduled = array(
            (object) array('timestamp' => 1760580000, 'hook' => 'updraft_backup', 'args' => array()),
            (object) array('timestamp' => 1760550000, 'hook' => 'updraft_backup_database', 'args' => array()),
        );
        Sitepulse_Test_WP::$cron = array(
            1760544000 => array('wp_version_check' => array('k' => array('args' => array())), 'updraft_backup_resume' => array('k1' => array('args' => array(1, 'finishednonce')), 'k2' => array('args' => array(2, 'runningnonce')))),
            'version' => 2,
        );
        Sitepulse_Test_WP::$site_options = array('updraft_jobdata_finishednonce' => array('jobstatus' => 'finished'), 'updraft_jobdata_runningnonce' => array('jobstatus' => 'clouduploading', 'label' => 'Nightly'));

        $status = Sitepulse_Remote_Backups::status();

        $this->assertSame(array('id' => 'updraftplus', 'version' => '1.26.9', 'supported' => true), $status['provider']);
        $this->assertSame(array(
            array('name' => 'Files', 'contents' => array('files'), 'frequency' => 'weekly', 'next' => '2025-10-16T02:00:00Z', 'keep' => 4, 'destinations' => array('googledrive')),
            array('name' => 'Database', 'contents' => array('database'), 'frequency' => 'daily', 'next' => '2025-10-15T17:40:00Z', 'keep' => 14, 'destinations' => array('googledrive')),
        ), $status['schedules']);
        $this->assertSame(array('name' => 'Nightly', 'progress' => null, 'stage' => 'clouduploading'), $status['running']);
        $this->assertSame(array('time' => '2025-10-15T02:00:00Z', 'ok' => true, 'errors' => 1, 'warnings' => 2), $status['last']);
        $this->assertSame('2025-10-15T17:40:00Z', $status['next']);
        $this->assertSame(array(
            array('time' => '2025-10-15T02:00:00Z', 'contents' => array('database', 'files'), 'size' => 5500, 'destinations' => array('googledrive'), 'label' => 'Before update'),
            array('time' => '2025-10-14T02:00:00Z', 'contents' => array('files'), 'size' => 4000, 'destinations' => array('s3', 'dropbox'), 'label' => ''),
            array('time' => '2025-10-13T02:00:00Z', 'contents' => array('database'), 'size' => 1000, 'destinations' => array(), 'label' => ''),
            array('time' => '2025-10-12T02:00:00Z', 'contents' => array(), 'size' => 0, 'destinations' => array(), 'label' => ''),
        ), $status['recent']);
    }

    public function test_updraftplus_defaults_when_nothing_is_scheduled_or_saved(): void {
        Sitepulse_Test_Backups::updraftplus();
        Sitepulse_Test_WP::$options = array('updraft_backup_history' => 'corrupt', 'updraft_last_backup' => array('success' => 1));

        $status = Sitepulse_Remote_Backups::status();

        $this->assertSame(array('manual', 'manual'), array_column($status['schedules'], 'frequency'));
        $this->assertSame(array(2, 2), array_column($status['schedules'], 'keep'));
        $this->assertSame(array(array(), array()), array_column($status['schedules'], 'destinations'));
        $this->assertSame(array(null, null), array_column($status['schedules'], 'next'));
        $this->assertNull($status['running']);
        $this->assertNull($status['last'], 'A record without a backup time is no backup.');
        $this->assertNull($status['next']);
        $this->assertSame(array(), $status['recent']);
    }

    public function test_recent_updraftplus_backups_are_limited_to_the_newest_ten(): void {
        Sitepulse_Test_Backups::updraftplus();
        $history = array();
        for ($day = 1; $day <= 12; $day++) { $history[1760000000 + $day * 86400] = array('db' => 'db.gz'); }
        update_option('updraft_backup_history', $history);

        $recent = Sitepulse_Remote_Backups::status()['recent'];

        $this->assertCount(10, $recent);
        $this->assertSame(gmdate('Y-m-d\TH:i:s\Z', 1760000000 + 12 * 86400), $recent[0]['time']);
        $this->assertSame(gmdate('Y-m-d\TH:i:s\Z', 1760000000 + 3 * 86400), $recent[9]['time']);
    }

    public function test_backwpup_status_lists_jobs_running_job_last_result_and_recent_archives(): void {
        Sitepulse_Test_Backups::$backwpup_version = '5.7.8';
        update_option('gmt_offset', -5);
        $job = static function (array $settings) { return $settings + array('type' => array('DBDUMP', 'FILE'), 'destinations' => array('FOLDER'), 'activetype' => 'wpcron', 'frequency' => 'daily', 'maxbackups' => 15); };
        Sitepulse_Test_Backups::$jobs = array(
            1 => $job(array('name' => 'Daily', 'lastrun' => 1760472000, 'logfile' => '/logs/1.html', 'destinations' => array('FOLDER', 'S3', 'GONE', 7))),
            2 => $job(array('name' => 'Weekly export', 'type' => array('WPEXP', 'WPPLUGIN', 'DBCHECK', 'CUSTOM'), 'activetype' => '', 'frequency' => 'weekly', 'lastrun' => 1760300000, 'logfile' => '/logs/2.html')),
            3 => $job(array('name' => 'Backup now', 'tempjob' => true, 'lastrun' => 1760500000)),
            4 => 'corrupt',
            5 => $job(array('name' => 'Sharing the folder', 'activetype' => 'easycron', 'maxbackups' => null, 'destinations' => array('FOLDER'))),
        );
        unset(Sitepulse_Test_Backups::$jobs[5]['frequency'], Sitepulse_Test_Backups::$jobs[5]['maxbackups']);
        Sitepulse_Test_Backups::$destinations = array(
            'FOLDER' => array(
                '1_FOLDER' => array(array('file' => '/backups/a.tar', 'filesize' => 100, 'time' => 1760472100), 'not an item'),
                '5_FOLDER' => array(array('file' => '/backups/a.tar', 'filesize' => 100, 'time' => 1760472100), array('file' => '/backups/b.tar', 'time' => 1760390000)),
            ),
            'S3' => array('1_S3' => array(array('file' => 'a.tar', 'filesize' => 100, 'time' => 1760472200))),
        );
        Sitepulse_Test_WP::$scheduled = array(
            (object) array('timestamp' => 1760558400, 'hook' => 'backwpup_cron', 'args' => array('arg' => 1)),
            (object) array('timestamp' => 1760600000, 'hook' => 'backwpup_cron', 'args' => array('arg' => 5)),
        );
        $working = new BackWPup_Job(); $working->job = array('name' => 'Daily'); $working->step_percent = 40; $working->step_working = 'DEST_S3';
        Sitepulse_Test_Backups::$working = $working;
        Sitepulse_Test_Backups::$log_headers = array('/logs/1.html' => array('errors' => '0', 'warnings' => '2'));

        $status = Sitepulse_Remote_Backups::status();

        $this->assertSame(array(
            array('name' => 'Daily', 'contents' => array('database', 'files'), 'frequency' => 'daily', 'next' => '2025-10-15T20:00:00Z', 'keep' => 15, 'destinations' => array('folder', 's3', 'gone')),
            array('name' => 'Weekly export', 'contents' => array('content_export', 'plugin_list', 'database_check', 'custom'), 'frequency' => 'manual', 'next' => null, 'keep' => 15, 'destinations' => array('folder')),
            array('name' => 'Sharing the folder', 'contents' => array('database', 'files'), 'frequency' => 'custom', 'next' => '2025-10-16T07:33:20Z', 'keep' => null, 'destinations' => array('folder')),
        ), $status['schedules']);
        $this->assertSame(array('name' => 'Daily', 'progress' => 40, 'stage' => 'DEST_S3'), $status['running']);
        $this->assertSame(array('time' => '2025-10-15T01:00:00Z', 'ok' => true, 'errors' => 0, 'warnings' => 2), $status['last'], 'BackWPup stores local time; the site is five hours behind UTC.');
        $this->assertSame('2025-10-15T20:00:00Z', $status['next']);
        $this->assertSame(array(
            array('time' => '2025-10-14T20:03:20Z', 'contents' => array('database', 'files'), 'size' => 100, 'destinations' => array('s3'), 'label' => 'Daily'),
            array('time' => '2025-10-14T20:01:40Z', 'contents' => array('database', 'files'), 'size' => 100, 'destinations' => array('folder'), 'label' => 'Sharing the folder'),
            array('time' => '2025-10-13T21:13:20Z', 'contents' => array('database', 'files'), 'size' => 0, 'destinations' => array('folder'), 'label' => 'Sharing the folder'),
        ), $status['recent'], 'An archive two jobs share is listed once.');
    }

    public function test_backwpup_without_runs_or_readable_logs(): void {
        Sitepulse_Test_Backups::$backwpup_version = '5.0.0';
        Sitepulse_Test_Backups::$jobs = array(1 => array('name' => 'New job', 'activetype' => 'link'));

        $status = Sitepulse_Remote_Backups::status();

        $this->assertSame(array(array('name' => 'New job', 'contents' => array(), 'frequency' => 'manual', 'next' => null, 'keep' => null, 'destinations' => array())), $status['schedules']);
        $this->assertNull($status['running']);
        $this->assertNull($status['last']);
        $this->assertNull($status['next']);
        $this->assertSame(array(), $status['recent']);

        Sitepulse_Test_Backups::$jobs = array(1 => array('lastrun' => 1760472000, 'logfile' => '/logs/missing.html'), 2 => array('lastrun' => 1760400000, 'logfile' => '/logs/older.html'), 3 => array('lastrun' => 1760300000));
        Sitepulse_Test_Backups::$log_headers = array('/logs/older.html' => array('errors' => '3'));
        $this->assertSame(array('time' => '2025-10-14T20:00:00Z', 'ok' => null, 'errors' => null, 'warnings' => null), Sitepulse_Remote_Backups::status()['last'], 'The newest run counts even when its log is unreadable.');

        Sitepulse_Test_Backups::$jobs = array(1 => array('lastrun' => 1760472000, 'logfile' => ''));
        $this->assertNull(Sitepulse_Remote_Backups::status()['last']['ok'], 'A job without a log has no known result.');
    }

    public function test_backwpup_last_reports_errors_from_the_log_header(): void {
        Sitepulse_Test_Backups::$backwpup_version = '5.7.8';
        Sitepulse_Test_Backups::$jobs = array(1 => array('lastrun' => 1760472000, 'logfile' => '/logs/1.html'));
        Sitepulse_Test_Backups::$log_headers = array('/logs/1.html' => array('errors' => 2));
        $this->assertSame(array('time' => '2025-10-14T20:00:00Z', 'ok' => false, 'errors' => 2, 'warnings' => 0), Sitepulse_Remote_Backups::status()['last']);
    }

    public function test_recent_backwpup_archives_are_limited_to_the_newest_ten(): void {
        Sitepulse_Test_Backups::$backwpup_version = '5.7.8';
        Sitepulse_Test_Backups::$jobs = array(1 => array('destinations' => array('FOLDER')));
        $files = array();
        for ($i = 1; $i <= 12; $i++) { $files[] = array('file' => "/b/$i.tar", 'time' => 1760000000 + $i); }
        Sitepulse_Test_Backups::$destinations = array('FOLDER' => array('1_FOLDER' => $files));

        $recent = Sitepulse_Remote_Backups::status()['recent'];

        $this->assertCount(10, $recent);
        $this->assertSame(gmdate('Y-m-d\TH:i:s\Z', 1760000012), $recent[0]['time']);
    }

    /** @dataProvider brokenPlugins */
    public function test_a_failing_backup_plugin_does_not_break_the_inventory(callable $activate, string $id): void {
        $activate();
        Sitepulse_Test_Backups::$throw = true;
        $status = Sitepulse_Remote_Backups::status();
        $this->assertSame($id, $status['provider']['id']);
        $this->assertSame('status_unavailable', $status['error']);
    }

    public static function brokenPlugins(): array {
        return array(
            'UpdraftPlus' => array(static function () { Sitepulse_Test_Backups::updraftplus(); }, 'updraftplus'),
            'BackWPup' => array(static function () { Sitepulse_Test_Backups::$backwpup_version = '5.7.8'; }, 'backwpup'),
        );
    }

    public function test_finished_backups_are_reported_while_remote_management_is_active(): void {
        Sitepulse_Remote_Backups::init();
        $this->activateRemoteManagement();
        $last = array('backup_time' => 1760493600, 'success' => 0, 'errors' => array('Upload failed', array('level' => 'warning')), 'backup_array' => array('db-size' => 10, 'plugins-size' => 20, 'nonce' => 'x'));

        $this->assertSame($last, apply_filters('updraftplus_save_last_backup', $last), 'UpdraftPlus saves the record unchanged.');
        $this->assertSame('not a record', apply_filters('updraftplus_save_last_backup', 'not a record'));
        $this->assertSame(array('backup_time' => 1), apply_filters('updraftplus_save_last_backup', array('backup_time' => 1)));
        $job = new BackWPup_Job(); $job->errors = 0; $job->warnings = 1; $job->backup_filesize = 2048;
        do_action('backwpup_end_job', array('jobid' => 1), 'archive.tar', $job);

        $events = $this->events();
        $this->assertCount(3, $events);
        $this->assertSame(array('backup.completed', 'backup.completed', 'backup.completed'), array_column($events, 'type'));
        $this->assertSame(array('provider' => 'updraftplus', 'ok' => false, 'errors' => 1, 'warnings' => 1, 'size' => 30, 'time' => '2025-10-15T02:00:00Z'), $events[0]['data']);
        $this->assertSame(array('provider' => 'updraftplus', 'ok' => false, 'errors' => 0, 'warnings' => 0, 'size' => 0, 'time' => '1970-01-01T00:00:01Z'), $events[1]['data']);
        $this->assertSame(array('provider' => 'backwpup', 'ok' => true, 'errors' => 0, 'warnings' => 1, 'size' => 2048), array_diff_key($events[2]['data'], array('time' => 1)));
        $this->assertEqualsWithDelta(time(), strtotime($events[2]['data']['time']), 2);
        $this->assertTrue(Sitepulse_Remote_Management::state()['inventory_due'], 'A new backup refreshes the inventory.');
    }

    public function test_an_updraftplus_run_that_will_resume_is_not_reported_as_a_failed_backup(): void {
        Sitepulse_Remote_Backups::init();
        $this->activateRemoteManagement();
        Sitepulse_Test_Backups::updraftplus();
        $updraftplus = $GLOBALS['updraftplus'];
        $failed = array('backup_time' => 1760493600, 'success' => 0, 'errors' => array('Infinite recursion'), 'backup_array' => array('db-size' => 10));

        // The first run hit an error and UpdraftPlus resumes the backup later.
        $updraftplus->newresumption_scheduled = 1760493900;
        $this->assertSame($failed, apply_filters('updraftplus_save_last_backup', $failed), 'UpdraftPlus saves the record unchanged.');
        $this->assertSame(array(), $this->events(), 'The backup has not finished.');

        // The resumption succeeds: a resumption may still be scheduled, but a successful backup is finished.
        $updraftplus->current_resumption = 1;
        apply_filters('updraftplus_save_last_backup', array('success' => 1, 'errors' => array()) + $failed);
        // Another backup fails on its last run (no resumption), and a third schedules a resumption only to report its failure.
        $updraftplus->newresumption_scheduled = false;
        apply_filters('updraftplus_save_last_backup', $failed);
        $updraftplus->newresumption_scheduled = 1760494200;
        $updraftplus->jobdata = array('fail_on_resume' => 2);
        apply_filters('updraftplus_save_last_backup', $failed);

        $this->assertSame(array(true, false, false), array_column(array_column($this->events(), 'data'), 'ok'));
    }

    public function test_backups_are_not_reported_while_remote_management_is_off(): void {
        Sitepulse_Remote_Backups::init();
        $job = new BackWPup_Job(); $job->errors = 1;
        do_action('backwpup_end_job', array(), '', $job);
        apply_filters('updraftplus_save_last_backup', array('backup_time' => 1760493600));
        $this->assertSame(array(), $this->events());
    }
}

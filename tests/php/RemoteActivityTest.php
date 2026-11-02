<?php
require_once __DIR__ . '/Support/RemoteManagement.php';
require_once __DIR__ . '/Support/Database.php';

final class RemoteActivityTest extends Sitepulse_Test_Case {
    /** @var int Today's UTC day number, as the profiler counts its windows. */
    private $today;

    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
        update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true));
        $this->today = (int) floor(time() / DAY_IN_SECONDS);
        Sitepulse_Test_WP::$plugins = array('seo/seo.php' => array('Name' => 'SEO'), 'hello.php' => array('Name' => 'Hello Dolly'));
        Sitepulse_Test_WP::$themes = array('astra' => array('Name' => 'Astra'));
    }

    protected function tearDown(): void { unset($GLOBALS['wpdb']); parent::tearDown(); }

    /**
     * A stored profiler row: its window (UTC day) and the own time and page loads of that day and the one before.
     */
    private function row(string $fileline, int $window, array $current, array $previous = array(0.0, 0)): array {
        return array('fileline' => $fileline, 'window' => $window, 'window_self_ms' => $current[0], 'window_loads' => $current[1], 'previous_self_ms' => $previous[0], 'previous_loads' => $previous[1]);
    }

    private function profile(array $rows): void { update_option('sitepulse_profiler_stats', array_combine(array_map('strval', array_keys($rows)), $rows)); }

    private function day(int $day): string { return gmdate('Y-m-d', $day * DAY_IN_SECONDS); }

    private function state(): array { return get_option(Sitepulse_Remote_Activity::OPTION); }

    /** @return array Data of the collected events of one type. */
    private function collected(string $type): array {
        return array_column(array_values(array_filter(Sitepulse_Remote_Activity::collect(), static function ($event) use ($type) { return $event[0] === $type; })), 1);
    }

    private function errorLog(array $errors): void { $GLOBALS['wpdb']->var = serialize(array('errors' => $errors)); }

    public function test_an_update_is_measured_on_the_full_days_before_and_after_it(): void {
        $t = $this->today;
        // Yesterday was the previous window of rows that ran today, and the current window of rows that did not.
        $this->profile(array(
            $this->row('wp-content/plugins/seo/seo.php:10', $t, array(500.0, 4), array(3000.0, 30)),
            $this->row('wp-content/plugins/seo/admin.php:5', $t - 1, array(1000.0, 25)),
            $this->row('wp-content/plugins/seo-addon/addon.php:1', $t, array(0.0, 0), array(900.0, 30)),
            $this->row('wp-content/plugins/seo/old.php:1', $t - 2, array(7000.0, 10)),
            $this->row('wp-content/plugins/seo/idle.php:1', $t, array(80.0, 2)),
        ));

        Sitepulse_Remote_Activity::watch('plugin', 'seo/seo.php', '1.0', '1.1');

        $watch = $this->state()['watches']['plugin:seo/seo.php'];
        $this->assertSame(array('day' => $this->day($t - 1), 'ms' => 140.0, 'loads' => 30), $watch['before'], 'Own time per page load of SEO\'s callbacks on the day before.');
        $this->assertSame(array('plugin', 'seo/seo.php', 'SEO', '1.0', '1.1', $t), array($watch['kind'], $watch['id'], $watch['name'], $watch['from'], $watch['to'], $watch['day']));
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ$/', $watch['updated_at']);
        $this->assertSame(array(), $this->collected('update.impact'), 'The day after the update is not over yet.');

        // Two days later: the day after the update is the profiler's previous window.
        $state = $this->state();
        $state['watches']['plugin:seo/seo.php']['day'] = $t - 2;
        update_option(Sitepulse_Remote_Activity::OPTION, $state);
        $this->profile(array(
            $this->row('wp-content/plugins/seo/seo.php:10', $t, array(90.0, 1), array(6000.0, 40)),
            $this->row('wp-content/plugins/seo/admin.php:5', $t - 1, array(1200.0, 30)),
        ));

        $this->assertSame(array(array(
            'kind' => 'plugin', 'id' => 'seo/seo.php', 'name' => 'SEO', 'from' => '1.0', 'to' => '1.1', 'updated_at' => $watch['updated_at'],
            'before' => $watch['before'], 'after' => array('day' => $this->day($t - 1), 'ms' => 190.0, 'loads' => 40), 'measured' => true, 'reason' => null,
        )), $this->collected('update.impact'));
        $this->assertSame(array(), $this->state()['watches'], 'A measurement is sent once.');
    }

    public function test_measurements_without_enough_traffic_or_read_too_late_say_why(): void {
        $t = $this->today;
        $before = static function (int $loads) use ($t) { return array('day' => gmdate('Y-m-d', ($t - 3) * DAY_IN_SECONDS), 'ms' => 12.5, 'loads' => $loads); };
        $watch = static function (string $id, int $day, int $loads) use ($before) { return array('kind' => 'plugin', 'id' => $id, 'name' => $id, 'from' => '1', 'to' => '2', 'updated_at' => '2026-01-01T00:00:00Z', 'day' => $day, 'before' => $before($loads)); };
        update_option(Sitepulse_Remote_Activity::OPTION, array('watches' => array(
            'plugin:quiet/quiet.php' => $watch('quiet/quiet.php', $t - 2, 50),
            'plugin:new/new.php' => $watch('new/new.php', $t - 2, 3),
            'plugin:late/late.php' => $watch('late/late.php', $t - 3, 50),
            'plugin:fresh/fresh.php' => $watch('fresh/fresh.php', $t - 1, 50),
        ), 'daily_day' => $t - 1));
        $this->profile(array(
            $this->row('wp-content/plugins/quiet/quiet.php:1', $t, array(0.0, 0), array(100.0, 19)),
            $this->row('wp-content/plugins/new/new.php:1', $t - 1, array(500.0, 25)),
        ));

        $impacts = $this->collected('update.impact');

        $this->assertSame(array(array('quiet/quiet.php', false, 'not_enough_traffic', 19), array('new/new.php', false, 'not_enough_traffic', 25)), array_map(static function ($impact) { return array($impact['id'], $impact['measured'], $impact['reason'], $impact['after']['loads']); }, array_slice($impacts, 0, 2)));
        $this->assertSame(array('late/late.php', false, 'missed', null), array($impacts[2]['id'], $impacts[2]['measured'], $impacts[2]['reason'], $impacts[2]['after']));
        $this->assertCount(3, $impacts);
        $this->assertSame(array('plugin:fresh/fresh.php'), array_keys($this->state()['watches']));
    }

    public function test_only_measurable_updates_of_a_managed_site_are_watched(): void {
        foreach (array(array('translation', 'plugin:seo:de_DE', '1', '2'), array('plugin', 'sitepulse/loader.php', '1.4', '1.5'), array('plugin', 'seo/seo.php', null, '1.1'), array('plugin', 'seo/seo.php', '1.1', '1.1'), array('plugin', 'seo/seo.php', '1.1', null)) as $update) {
            Sitepulse_Remote_Activity::watch(...$update);
        }
        $this->assertFalse(get_option(Sitepulse_Remote_Activity::OPTION));

        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => false));
        Sitepulse_Remote_Activity::watch('plugin', 'seo/seo.php', '1.0', '1.1');
        $this->assertFalse(get_option(Sitepulse_Remote_Activity::OPTION), 'Nothing is measured while the dashboard does not manage the site.');
    }

    public function test_a_later_update_replaces_a_pending_measurement_and_at_most_fifty_are_kept(): void {
        Sitepulse_Remote_Activity::watch('plugin', 'seo/seo.php', '1.0', '1.1');
        Sitepulse_Remote_Activity::watch('theme', 'astra', '4.0', '4.1');
        Sitepulse_Remote_Activity::watch('plugin', 'seo/seo.php', '1.1', '1.2');
        $this->assertSame(array('theme:astra', 'plugin:seo/seo.php'), array_keys($this->state()['watches']));
        $this->assertSame(array('1.1', '1.2'), array($this->state()['watches']['plugin:seo/seo.php']['from'], $this->state()['watches']['plugin:seo/seo.php']['to']));

        for ($i = 1; $i <= 49; $i++) { Sitepulse_Remote_Activity::watch('plugin', "p$i/p$i.php", '1', '2'); }
        $watches = array_keys($this->state()['watches']);
        $this->assertCount(50, $watches);
        $this->assertSame(array('plugin:seo/seo.php', 'plugin:p1/p1.php'), array_slice($watches, 0, 2), 'The oldest measurement is dropped.');
        $this->assertSame('p1/p1.php', $this->state()['watches']['plugin:p1/p1.php']['name'], 'A plugin WordPress does not list is named by its file.');
    }

    public function test_single_file_plugins_themes_and_core_cover_only_their_own_files(): void {
        $t = $this->today;
        $this->profile(array(
            $this->row('wp-content/plugins/hello.php:12', $t - 1, array(300.0, 30)),
            $this->row('wp-content/plugins/hello.php', $t - 1, array(150.0, 30)),
            $this->row('wp-content/plugins/hello.phpx/other.php:1', $t - 1, array(900.0, 30)),
            $this->row('wp-content/themes/astra/functions.php', $t - 1, array(600.0, 30)),
            $this->row('wp-content/themes/astra-child/functions.php:2', $t - 1, array(900.0, 30)),
            $this->row('wp-includes/class-wp-hook.php:300', $t - 1, array(900.0, 30)),
            $this->row('wp-admin/includes/plugin.php:9', $t - 1, array(120.0, 30)),
        ));

        Sitepulse_Remote_Activity::watch('plugin', 'hello.php', '1.7', '1.8');
        Sitepulse_Remote_Activity::watch('theme', 'astra', '4.0', '4.1');
        Sitepulse_Remote_Activity::watch('core', 'wordpress', '7.1.2', '7.1.3');

        $watches = $this->state()['watches'];
        $this->assertSame(array(15.0, 'Hello Dolly'), array($watches['plugin:hello.php']['before']['ms'], $watches['plugin:hello.php']['name']));
        $this->assertSame(array(20.0, 'Astra'), array($watches['theme:astra']['before']['ms'], $watches['theme:astra']['name']));
        $this->assertSame(array(34.0, 'WordPress'), array($watches['core:wordpress']['before']['ms'], $watches['core:wordpress']['name']));
    }

    public function test_yesterdays_figures_are_sent_once_with_the_slowest_sources(): void {
        $t = $this->today;
        update_option(Sitepulse_Remote_Activity::OPTION, array('errors_until' => 1));
        $this->profile(array(
            $this->row('wp-content/plugins/seo/seo.php:10', $t, array(0.0, 0), array(3000.0, 30)),
            $this->row('wp-content/plugins/seo/admin.php:5', $t - 1, array(1000.0, 25)),
            $this->row('wp-includes/class-wp-hook.php:300', $t - 1, array(1020.0, 30)),
            $this->row('wp-content/themes/astra/functions.php', $t - 1, array(600.0, 30)),
            $this->row('wp-content/plugins/hello.php:12', $t - 1, array(450.0, 30)),
            $this->row('wp-content/plugins/forms/forms.php:3', $t - 1, array(360.0, 30)),
            $this->row('wp-content/plugins/cache/cache.php:3', $t - 1, array(90.0, 30)),
            $this->row('wp-content/mu-plugins/host.php:7', $t - 1, array(210.0, 30)),
            $this->row('wp-content/plugins/sitepulse/class/profiler.php:40', $t - 1, array(9000.0, 30)),
            $this->row('wp-content/plugins/seo/today.php:1', $t, array(400.0, 4)),
        ));

        $this->assertSame(array(array(
            'day' => $this->day($t - 1), 'ms' => 231.0, 'loads' => 30,
            'top' => array(
                array('kind' => 'plugin', 'id' => 'seo', 'ms' => 140.0),
                array('kind' => 'core', 'id' => 'core', 'ms' => 34.0),
                array('kind' => 'theme', 'id' => 'astra', 'ms' => 20.0),
                array('kind' => 'plugin', 'id' => 'hello', 'ms' => 15.0),
                array('kind' => 'plugin', 'id' => 'forms', 'ms' => 12.0),
            ),
        )), $this->collected('performance.daily'), 'Code outside plugins, themes and core counts in the total only; SitePulse\'s own code never counts.');
        $this->assertSame($t - 1, $this->state()['daily_day']);
        $this->assertSame(array(), $this->collected('performance.daily'));
    }

    public function test_a_day_without_page_loads_sends_no_figures(): void {
        update_option(Sitepulse_Remote_Activity::OPTION, array('errors_until' => 1));
        $this->profile(array($this->row('wp-content/plugins/seo/seo.php:10', $this->today - 3, array(100.0, 10))));
        $this->assertSame(array(), Sitepulse_Remote_Activity::collect());
        $this->assertSame($this->today - 1, $this->state()['daily_day']);
    }

    public function test_new_fatal_errors_are_sent_grouped_with_their_source(): void {
        update_option(Sitepulse_Remote_Activity::OPTION, array('daily_day' => $this->today));
        $this->errorLog(array(array('is_fatal' => true, 'timestamp' => strtotime('2026-01-01 11:00:00 UTC'), 'file' => ABSPATH . 'wp-content/plugins/old/old.php', 'message' => 'Before remote management')));
        $this->assertSame(array(), Sitepulse_Remote_Activity::collect(), 'The first check-in only starts reading the log.');
        $since = strtotime('2026-01-01 12:00:00 UTC');
        $this->assertSame($since, $this->state()['errors_until']);

        Sitepulse_Test_WP::$now = '2026-01-01 13:00:00';
        update_option('gmt_offset', 2);
        $long = str_repeat('x', 400);
        $this->errorLog(array(
            array('is_fatal' => true, 'timestamp' => $since, 'file' => ABSPATH . 'wp-content/plugins/old/old.php', 'line' => 1, 'message' => 'Read last time'),
            array('is_fatal' => true, 'timestamp' => $since + 60, 'file' => ABSPATH . 'wp-content/plugins/forms/forms.php', 'line' => 10, 'message' => "Uncaught Error: Call to undefined function forms_render() in " . ABSPATH . "wp-content/plugins/forms/forms.php:10\nStack trace:\n#0 {main}"),
            array('is_fatal' => false, 'timestamp' => $since + 70, 'file' => ABSPATH . 'wp-content/plugins/forms/forms.php', 'line' => 11, 'message' => 'A warning'),
            array('is_fatal' => true, 'timestamp' => $since + 90, 'file' => ABSPATH . 'wp-content/themes/astra/functions.php', 'line' => 3, 'message' => $long),
            array('is_fatal' => true, 'timestamp' => $since + 120, 'file' => ABSPATH . 'wp-content/plugins/forms/forms.php', 'line' => 10, 'message' => 'Uncaught Error: Call to undefined function forms_render() in ' . ABSPATH . 'wp-content/plugins/forms/forms.php:10'),
            array('is_fatal' => true, 'timestamp' => $since + 130, 'file' => ABSPATH . 'wp-includes/load.php', 'line' => 7, 'message' => 'Allowed memory size exhausted'),
            array('is_fatal' => true, 'timestamp' => $since + 140, 'file' => ABSPATH . 'wp-content/plugins/hello.php', 'line' => 2, 'message' => 'Hello'),
            array('is_fatal' => true, 'timestamp' => $since + 150, 'message' => 'No file'),
            array('is_fatal' => true, 'timestamp' => $since + 7200, 'file' => ABSPATH . 'wp-content/plugins/later/later.php', 'message' => 'Logged after this check-in started'),
        ));

        $errors = $this->collected('error.fatal');

        $this->assertSame(array(
            'message' => 'Uncaught Error: Call to undefined function forms_render() in wp-content/plugins/forms/forms.php:10', 'file' => 'wp-content/plugins/forms/forms.php', 'line' => 10,
            'source' => array('kind' => 'plugin', 'id' => 'forms'), 'count' => 2, 'first_at' => '2026-01-01T10:01:00Z', 'last_at' => '2026-01-01T10:02:00Z',
        ), $errors[0], 'Times are UTC; the log stores local time. Paths start at the WordPress folder.');
        $this->assertSame(array(str_repeat('x', 300), array('kind' => 'theme', 'id' => 'astra'), 1), array($errors[1]['message'], $errors[1]['source'], $errors[1]['count']));
        $this->assertSame(array(array('kind' => 'core', 'id' => null), array('kind' => 'plugin', 'id' => 'hello'), array('kind' => 'other', 'id' => null)), array_column(array_slice($errors, 2), 'source'));
        $this->assertSame(array('(unknown)', 0), array($errors[4]['file'], $errors[4]['line']));
        $this->assertCount(5, $errors);
        $this->assertSame(strtotime('2026-01-01 13:00:00 UTC'), $this->state()['errors_until']);
    }

    public function test_at_most_ten_fatal_errors_are_sent_per_check_in(): void {
        update_option(Sitepulse_Remote_Activity::OPTION, array('daily_day' => $this->today, 'errors_until' => 1));
        $errors = array();
        for ($i = 0; $i < 12; $i++) { $errors[] = array('is_fatal' => true, 'timestamp' => 100 + $i, 'file' => ABSPATH . "wp-content/plugins/p$i/p.php", 'message' => "Error $i"); }
        $this->errorLog($errors);
        $this->assertSame(array('Error 0', 'Error 9'), array_values(array_intersect_key(array_column($this->collected('error.fatal'), 'message'), array(0 => 1, 9 => 1))));
        $this->errorLog($errors);
        update_option(Sitepulse_Remote_Activity::OPTION, array('daily_day' => $this->today, 'errors_until' => 1));
        $this->assertCount(10, $this->collected('error.fatal'));
    }

    public function test_a_corrupt_state_starts_over_and_reset_forgets_it(): void {
        update_option(Sitepulse_Remote_Activity::OPTION, 'corrupt');
        Sitepulse_Remote_Activity::watch('theme', 'astra', '4.0', '4.1');
        $this->assertSame(array('theme:astra'), array_keys($this->state()['watches']));

        update_option(Sitepulse_Remote_Activity::OPTION, array('watches' => array('broken' => array('kind' => 'plugin'), 'other' => 'x', 'theme:astra' => $this->state()['watches']['theme:astra']), 'daily_day' => 'x', 'errors_until' => null));
        Sitepulse_Remote_Activity::watch('core', 'wordpress', '7.1.2', '7.1.3');
        $this->assertSame(array('theme:astra', 'core:wordpress'), array_keys($this->state()['watches']));
        update_option(Sitepulse_Remote_Activity::OPTION, array('watches' => 'x'));
        Sitepulse_Remote_Activity::watch('core', 'wordpress', '7.1.2', '7.1.3');
        $this->assertSame(array('core:wordpress'), array_keys($this->state()['watches']));

        Sitepulse_Remote_Activity::reset();
        $this->assertFalse(get_option(Sitepulse_Remote_Activity::OPTION));
    }
}

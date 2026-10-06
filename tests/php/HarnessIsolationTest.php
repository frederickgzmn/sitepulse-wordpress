<?php

/**
 * Guard the PHP 7.4 reflection assumption used to isolate behavioral tests.
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class HarnessIsolationTest extends Sitepulse_Test_Case {
    public function test_reset_restores_pristine_wordpress_state_after_repeated_mutation(): void {
        foreach (array('first', 'second') as $value) {
            update_option('previous_test', $value);
            Sitepulse_Test_WP::$http_requests[] = array('url' => 'https://example.test/' . $value);
            Sitepulse_Test_WP::$capabilities['manage_options'] = false;
            Sitepulse_Test_WP::$screen = (object) array('id' => 'previous_screen');

            Sitepulse_Test_WP::reset();

            $this->assertSame(array(), Sitepulse_Test_WP::$options);
            $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
            $this->assertTrue(current_user_can('manage_options'));
            $this->assertNull(get_current_screen());
        }
    }

    public function test_reset_restores_classes_first_loaded_and_mutated_inside_a_test(): void {
        $first = Sitepulse_Settings::getInstance();
        Sitepulse_Profiler::$enabled = false;
        Sitepulse_Profiler::record('old_request', 10, 'callback', 'strlen', .002);

        Sitepulse_Test_WP::reset();

        $this->assertNotSame($first, Sitepulse_Settings::getInstance());
        $this->assertTrue(Sitepulse_Profiler::$enabled);
        $this->assertSame(array(), Sitepulse_Profiler::get_stats_by_hook('old_request'));
    }
}

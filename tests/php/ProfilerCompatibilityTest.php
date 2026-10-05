<?php
class Sitepulse_Test_Profiler_Callback {
    public function call($value) { return $value; }
    public static function static_call($value) { return $value; }
    public function __invoke($value) { return $value; }
}
final class ProfilerCompatibilityTest extends Sitepulse_Test_Case {
    public function test_single_page_request_enables_profiling_and_registers_lifecycle_measurement(): void {
        update_option('sitepulse_profiler_enabled', false); update_option('sitepulse_pageloadhttp_loadstatus', true); update_option('sitepulse_current_tracked_pageid', 42); Sitepulse_Test_WP::$post_id = 42;
        Sitepulse_Profiler::init();
        $this->assertTrue(Sitepulse_Profiler::$enabled);
        $this->assertNotFalse(has_action('plugin_loaded', array('Sitepulse_Profiler', 'capture_plugin_load_time')));
        $this->assertNotFalse(has_action('shutdown', array('Sitepulse_Profiler', 'persist')));
        do_action('setup_theme'); do_action('after_setup_theme');
        $this->assertCount(1, Sitepulse_Profiler::get_stats_by_hook('theme_bootstrap'));
    }
    /** @dataProvider callbackKinds */
    public function test_callback_signatures_identify_supported_callable_shapes($kind, $expected): void {
        $object = new Sitepulse_Test_Profiler_Callback();
        $callbacks = array('object_method' => array($object, 'call'), 'static_method' => array('Sitepulse_Test_Profiler_Callback', 'static_call'), 'invokable' => $object, 'invalid' => 17, 'unknown_method' => array($object, 'missing_method'));
        Sitepulse_Profiler::record('example', 10, 'callback', $callbacks[$kind], .002);
        $row = array_values(Sitepulse_Profiler::get_stats_by_hook('example'))[0];
        $this->assertSame($expected, $row['sig']);
        $this->assertSame(2.0, $row['total_ms']);
        if ($kind === 'invalid' || $kind === 'unknown_method') { $this->assertSame('(unknown)', $row['fileline']); }
        else { $this->assertStringContainsString('ProfilerCompatibilityTest.php:', $row['fileline']); }
    }
    public static function callbackKinds(): array {
        return array(array('object_method', 'Sitepulse_Test_Profiler_Callback->call'), array('static_method', 'Sitepulse_Test_Profiler_Callback::static_call'), array('invokable', 'Sitepulse_Test_Profiler_Callback::__invoke'), array('invalid', 'Callable'), array('unknown_method', 'Sitepulse_Test_Profiler_Callback->missing_method'));
    }
    public function test_hooks_with_incomplete_callback_entries_are_ignored(): void {
        $GLOBALS['wp_filter']['corrupt'] = 'not-a-hook';
        $GLOBALS['wp_filter']['partial'] = (object) array('callbacks' => array(10 => array('missing' => array(), 'invalid' => array('function' => 'undefined_callback'))));
        Sitepulse_Profiler::wrap_all_hooks();
        $this->assertSame(array(), Sitepulse_Profiler::get_formatted_stats());
        $GLOBALS['wp_filter'] = null;
        Sitepulse_Profiler::wrap_all_hooks();
        $this->assertSame(array(), Sitepulse_Profiler::get_formatted_stats());
    }
    public function test_empty_plugin_identifier_does_not_create_an_activity_row(): void {
        Sitepulse_Profiler::mark_plugin_load_complete(); Sitepulse_Profiler::capture_plugin_load_time('');
        $this->assertSame(array(), Sitepulse_Profiler::get_stats_by_hook('plugin_bootstrap'));
    }
    public function test_callbacks_defined_in_plugin_and_theme_files_receive_their_source_slug(): void {
        $plugin_file = WP_PLUGIN_DIR . '/source-plugin/callback.php';
        $theme_file = ABSPATH . 'wp-content/themes/source-theme/callback.php';
        foreach (array($plugin_file, $theme_file) as $file) { if (!is_dir(dirname($file))) { mkdir(dirname($file), 0777, true); } file_put_contents($file, '<?php return static function ($value) { return $value; };'); }
        $plugin_callback = require $plugin_file; $theme_callback = require $theme_file;
        unset($_SERVER['REQUEST_URI']);
        Sitepulse_Profiler::record('plugin-render', 10, 'plugin', $plugin_callback, .001);
        Sitepulse_Profiler::record('theme-render', 10, 'theme', $theme_callback, .002);
        $plugin = array_values(Sitepulse_Profiler::get_stats_by_hook('plugin-render'))[0]; $theme = array_values(Sitepulse_Profiler::get_stats_by_hook('theme-render'))[0];
        $this->assertSame('source-plugin', $plugin['source']); $this->assertSame('source-theme', $theme['source']); $this->assertSame('', $plugin['current_url']);
    }
}

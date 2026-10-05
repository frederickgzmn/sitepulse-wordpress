<?php
final class PluginProfilerTest extends Sitepulse_Test_Case {
    public function test_collection_maps_only_installed_active_plugins_and_counts_fresh_measurements(): void {
        update_option('active_plugins', array('sitepulse/loader.php', 'demo/main.php', 'standalone.php', 'missing/plugin.php'));
        Sitepulse_Test_WP::$plugins = array('sitepulse/loader.php' => array('Name' => 'SitePulse'), 'demo/main.php' => array('Name' => 'Demo', 'Version' => '2.0'), 'standalone.php' => array('Name' => 'Standalone'), 'inactive/main.php' => array('Name' => 'Inactive'));
        $this->setStaticProperty('Sitepulse_Profiler', 'stats', array('demo' => array('hook' => 'plugin_bootstrap', 'fileline' => 'wp-content/plugins/demo/main.php', 'calls' => 2, 'total' => .03, 'total_ms' => 30.0, 'avg_ms' => 15.0, 'max' => .02, 'max_ms' => 20.0, 'current_load_time' => 20.0)));
        Sitepulse_Plugin_Profiler::enable_data_collection(); Sitepulse_Plugin_Profiler::track_plugins();
        $rows = Sitepulse_Plugin_Profiler::get_stats(); $this->assertCount(2, $rows); $this->assertNull(Sitepulse_Plugin_Profiler::get_plugin_stats('sitepulse/loader.php')); $this->assertNull(Sitepulse_Plugin_Profiler::get_plugin_stats('missing/plugin.php'));
        $demo = Sitepulse_Plugin_Profiler::get_plugin_stats('demo/main.php'); $this->assertSame('Demo', $demo['plugin_name']); $this->assertSame('2.0', $demo['plugin_version']); $this->assertSame(2, $demo['calls']); $this->assertSame(20.0, $demo['current_load_time']);
        $standalone = Sitepulse_Plugin_Profiler::get_plugin_stats('standalone.php'); $this->assertSame('standalone', $standalone['plugin_slug']); $this->assertSame(0.0, $standalone['current_load_time']); $this->assertSame(20.0, Sitepulse_Plugin_Profiler::get_total_load_time());
        Sitepulse_Plugin_Profiler::persist(); $this->assertSame($rows, get_option('sitepulse_plugin_profiler_stats')); $this->assertSame(20.0, get_option('sitepulse_plugin_profiler_total_time')); $this->assertFalse(Sitepulse_Plugin_Profiler::should_collect_data());
    }
    public function test_previous_request_bootstrap_rows_are_not_counted_as_current_load(): void {
        update_option('active_plugins', array('demo/main.php')); Sitepulse_Test_WP::$plugins = array('demo/main.php' => array('Name' => 'Demo'));
        $row = array('hook' => 'plugin_bootstrap', 'fileline' => 'wp-content/plugins/demo/main.php', 'calls' => 2, 'total' => .03, 'total_ms' => 30.0, 'avg_ms' => 15.0, 'max' => .02, 'max_ms' => 20.0, 'current_load_time' => 20.0);
        $this->setStaticProperty('Sitepulse_Profiler', 'stats', array('demo' => $row)); Sitepulse_Plugin_Profiler::init(); Sitepulse_Plugin_Profiler::enable_data_collection(); Sitepulse_Plugin_Profiler::track_plugins();
        $this->assertSame(0, Sitepulse_Plugin_Profiler::get_plugin_stats('demo/main.php')['calls']); $this->assertSame(0.0, Sitepulse_Plugin_Profiler::get_total_load_time());
        $row['calls'] = 3; $row['current_load_time'] = 7.0; $this->setStaticProperty('Sitepulse_Profiler', 'stats', array('demo' => $row)); Sitepulse_Plugin_Profiler::track_plugins();
        $this->assertSame(3, Sitepulse_Plugin_Profiler::get_plugin_stats('demo/main.php')['calls']); $this->assertSame(7.0, Sitepulse_Plugin_Profiler::get_total_load_time());
    }
    public function test_network_active_plugins_are_deduplicated_with_local_plugins(): void {
        Sitepulse_Test_WP::$multisite = true; Sitepulse_Test_WP::$site_options['active_sitewide_plugins'] = array('network/main.php' => 100, 'local/main.php' => 101); update_option('active_plugins', array('local/main.php'));
        Sitepulse_Test_WP::$plugins = array('network/main.php' => array('Name' => 'Network'), 'local/main.php' => array('Name' => 'Local'));
        Sitepulse_Plugin_Profiler::enable_data_collection(); Sitepulse_Plugin_Profiler::track_plugins(); $this->assertCount(2, Sitepulse_Plugin_Profiler::get_stats()); $this->assertSame('Network', Sitepulse_Plugin_Profiler::get_plugin_stats('network/main.php')['plugin_name']);
    }
    public function test_collection_is_opt_in_and_disabled_profiling_never_persists(): void {
        update_option('active_plugins', array('demo/main.php')); Sitepulse_Test_WP::$plugins = array('demo/main.php' => array('Name' => 'Demo')); Sitepulse_Plugin_Profiler::track_plugins(); $this->assertSame(array(), Sitepulse_Plugin_Profiler::get_stats());
        Sitepulse_Plugin_Profiler::$enabled = false; Sitepulse_Plugin_Profiler::on_plugin_activated('demo/main.php'); Sitepulse_Plugin_Profiler::enable_data_collection(); Sitepulse_Plugin_Profiler::track_plugins(); Sitepulse_Plugin_Profiler::persist();
        $this->assertFalse(get_option('sitepulse_plugin_profiler_stats')); $this->assertFalse(get_transient('sitepulse_plugin_activated'));
    }
    public function test_activation_opens_collection_window_and_clear_resets_persisted_totals(): void {
        Sitepulse_Plugin_Profiler::on_plugin_activated('demo/main.php'); $this->assertTrue(Sitepulse_Plugin_Profiler::should_collect_data()); $this->assertSame('demo/main.php', get_transient('sitepulse_plugin_activated')); $this->assertSame(60, Sitepulse_Test_WP::$transient_expirations['sitepulse_plugin_activated']);
        $this->setStaticProperty('Sitepulse_Plugin_Profiler', 'stats', array('demo' => array('total_ms' => 20))); $this->setStaticProperty('Sitepulse_Plugin_Profiler', 'measured_total_time', 20.0);
        (new Sitepulse_Plugin_Profiler())->clear_stats(); $this->assertSame(array(), get_option('sitepulse_plugin_profiler_stats')); $this->assertSame(0.0, get_option('sitepulse_plugin_profiler_total_time'));
    }
    public function test_init_cleans_negative_legacy_metrics_but_keeps_legitimate_long_measurements(): void {
        update_option('sitepulse_plugin_profiler_stats', array('invalid' => array('avg_time' => -1, 'avg_memory' => 100), 'long' => array('avg_time' => 15000, 'avg_memory' => 1024))); update_option('sitepulse_plugin_profiler_total_time', -5);
        Sitepulse_Plugin_Profiler::init(); $rows = Sitepulse_Plugin_Profiler::get_stats(); $this->assertSame(0.0, $rows['invalid']['avg_time']); $this->assertSame(0, $rows['invalid']['loads']); $this->assertSame(15000, $rows['long']['avg_time']); $this->assertSame(0.0, Sitepulse_Plugin_Profiler::get_total_load_time());
    }
    public function test_legacy_memory_outliers_are_reset_and_nonarray_profiler_rows_are_ignored(): void {
        update_option('sitepulse_plugin_profiler_stats', array('legacy' => array('avg_memory' => 52428801, 'avg_time' => 5)));
        update_option('active_plugins', array('demo/main.php'));
        Sitepulse_Test_WP::$plugins = array('demo/main.php' => array('Name' => 'Demo'));
        // Options can contain old serialized objects. Their fields do not make them valid activity rows.
        $this->setStaticProperty('Sitepulse_Profiler', 'stats', array(new ArrayObject(array('hook' => 'plugin_bootstrap', 'fileline' => 'wp-content/plugins/demo/main.php', 'calls' => 20))));
        Sitepulse_Plugin_Profiler::init();
        $this->assertSame(0, Sitepulse_Plugin_Profiler::get_stats()['legacy']['avg_memory']);
        Sitepulse_Plugin_Profiler::enable_data_collection();
        Sitepulse_Plugin_Profiler::track_plugins();
        $this->assertSame(0, Sitepulse_Plugin_Profiler::get_plugin_stats('demo/main.php')['calls']);
        $this->assertSame(0.0, Sitepulse_Plugin_Profiler::get_total_load_time());
    }
}

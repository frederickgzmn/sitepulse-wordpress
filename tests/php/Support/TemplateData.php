<?php
/** Representative persisted records shared by view contract tests. */
function sitepulse_test_template_data() {
    $stats = array(); $events = array(); $plugins = array();
    foreach (array(1500, 750, 300, 150, 50) as $i => $ms) {
        $stats[] = array('key' => 'sample-' . $i, 'source' => 'sample-' . $i, 'hook' => 'init', 'fileline' => 'sample/main.php:12', 'sig' => 'sample_callback', 'calls' => $i, 'total_ms' => $ms, 'total' => $ms / 1000, 'current_load_time' => $ms, 'date_time' => gmdate('Y-m-d H:i:s'), 'current_url' => 'https://example.test/shop', 'last_seen_at' => $i ? time() - 300 : time());
        $events[] = array('elapsed' => $ms / 1000, 'url' => $i ? 'https://api.example.test/items?token=secret' : 'https://api.sitepulse.me/api/report', 'origin' => 'sample-' . $i, 'file' => 'sample/main.php', 'line' => 12, 'method' => 'POST', 'date' => '2026-01-01', 'code' => $i ? 500 : 200);
    }
    foreach (range(1, 11) as $i) { $plugins[] = array('plugin_name' => 'Fixture Plugin ' . $i, 'plugin_slug' => 'fixture-' . $i, 'plugin_version' => '1.0', 'current_load_time' => $i * 150, 'avg_ms' => $i * 120, 'load_time_ms' => $i * 150, 'total_time' => $i * 150); }
    $slow = array(array('name' => 'Slow plugin', 'time_ms' => 1500, 'time' => 1500, 'severity' => 'critical'), array('name' => 'Theme', 'time_ms' => 750, 'time' => 750, 'severity' => 'warning'));
    $requests = array(array('url' => 'https://api.example.test', 'origin' => 'fixture', 'code' => 500, 'time_ms' => 1500, 'severity' => 'critical'), array('url' => 'https://cdn.example.test', 'origin' => 'theme', 'code' => 200, 'time_ms' => 750, 'severity' => 'warning'));
    return array('stats' => $stats, 'curl_events' => $events, 'plugin_profiler_stats' => $plugins, 'all_slow_items' => $slow, 'slow_api_requests' => $requests,
        'mem' => array('formatted' => '20 MB (40%)', 'percent' => '40%', 'percent_class' => 'text-success'), 'snapshot_time' => '2026-01-01 12:00:00', 'active_plugins_count' => 11,
        'curLoader' => new Sitepulse_CurLoader(), 'sp_dashboard_view' => 'developer',
        'total_load_time' => 3500, 'loadsentinel_score' => 35, 'api_score' => 65, 'load_time_score' => 55, 'memory_score' => 70, 'plugins_score' => 50,
        'disk_write' => array('success' => true, 'avg_ms' => 120, 'min_ms' => 20, 'max_ms' => 220, 'times_ms' => array(20, 120, 220), 'size_bytes' => 1024, 'total_written_bytes' => 3072, 'iterations' => 3, 'dir' => '/tmp/sitepulse-fixture'),
    );
}
function sitepulse_test_seed_template_options() {
    $data = sitepulse_test_template_data();
    update_option(SITEPULSE_PROFILER_KEY, array_column($data['stats'], null, 'key'));
    update_option('sitepulse_profiler_enabled', true); update_option('sitepulse_curl_api_enabled', true);
    Sitepulse_Test_WP::$plugins = array();
    foreach (range(1, 6) as $i) { Sitepulse_Test_WP::$plugins['fixture-' . $i . '/main.php'] = array('Name' => 'Fixture ' . $i, 'Version' => '1.0'); }
    update_option('active_plugins', array_keys(Sitepulse_Test_WP::$plugins));
    update_option('date_format', 'Y-m-d'); update_option('time_format', 'H:i');
    return $data;
}

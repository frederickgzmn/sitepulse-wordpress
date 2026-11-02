<?php
/** Stored profiler rows and HTTP events as site traffic leaves them, $age seconds after their last run. */

/** A stored profiler row whose 24-hour window holds $loads page loads costing $load_ms of own time each. */
function sitepulse_test_recent_stat(array $fields = array(), float $load_ms = 100.0, int $loads = 1, float $age = 1.0): array {
    $seen = microtime(true) - $age;
    return array_merge(array(
        'key' => 'recent', 'hook' => 'init', 'priority' => 10, 'sig' => 'demo_callback', 'fileline' => 'wp-content/plugins/demo/main.php:12', 'source' => 'demo',
        'calls' => $loads, 'total' => $load_ms * $loads / 1000, 'total_ms' => $load_ms * $loads, 'avg_ms' => $load_ms, 'self_total_ms' => $load_ms * $loads,
        'max' => $load_ms / 1000, 'max_ms' => $load_ms, 'current_load_time' => $load_ms, 'date_time' => gmdate('Y-m-d H:i:s', (int) $seen), 'last_seen_at' => $seen,
        'window' => (int) floor($seen / Sitepulse_Profiler::RECENT_SECONDS), 'window_calls' => $loads, 'window_loads' => $loads,
        'window_ms' => $load_ms * $loads, 'window_self_ms' => $load_ms * $loads, 'window_max_ms' => $load_ms,
    ), $fields);
}

/** A stored slow HTTP request event. */
function sitepulse_test_http_event(array $fields = array(), int $age = 1): array {
    return array_merge(array(
        'ts' => time() - $age, 'elapsed' => 1.5, 'url' => 'https://api.example.test/v1', 'host' => 'api.example.test', 'code' => 200,
        'origin' => 'Plugin: Demo', 'file' => 'wp-content/plugins/demo/main.php', 'line' => 12, 'date' => gmdate('Y-m-d H:i:s', time() - $age),
    ), $fields);
}

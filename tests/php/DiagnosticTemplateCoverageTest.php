<?php
require_once __DIR__ . '/Support/PresentationWordPress.php';

/**
 * Exercise the original dashboard views with complete controller-output fixtures.
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class DiagnosticTemplateCoverageTest extends Sitepulse_Test_Case {
    protected function setUp(): void {
        parent::setUp();
        Sitepulse_Easy_Mode::init();
        update_option('date_format', 'Y-m-d');
        update_option('time_format', 'H:i');
        $_GET['page'] = 'wpsp_sitepulse';
    }

    private function data(): array {
        return array(
            'sitepulse_lowhttp_enabled' => 1, 'sitepulse_profiler_enabled' => 1,
            'sp_dashboard_view' => 'developer',
            'snapshot_time' => '2026-10-04 12:00:00', 'stats' => array(), 'curl_events' => array(),
            'curLoader' => new Sitepulse_CurLoader(), 'plugin_profiler_stats' => array(),
            'mem' => array('percent' => '25%', 'percent_class' => 'bg-success', 'formatted' => '32 MB / 128 MB'),
            'mem_usage_percent' => 25, 'plugin_percent' => 20, 'active_plugins_count' => 4,
            'overall_score' => 95, 'speed_score' => 95, 'resource_score' => 95, 'loadsentinel_score' => 95,
            'api_score' => 95, 'memory_score' => 95, 'plugins_score' => 95, 'error_log_score' => 100,
            'total_load_time' => 0, 'load_time_score' => 95, 'error_count' => 0, 'fatal_count' => 0, 'warning_count' => 0,
            'all_slow_items' => array(), 'top_slow_item' => null, 'slow_api_requests' => array(),
            'is_overall_slow' => false, 'show_performance_warnings' => false,
            'has_valid_pagespeed' => false, 'ps_performance_score' => 0, 'ps_data' => null, 'ps_strategy' => 'desktop',
            'vulnerabilities' => array(), 'last_vulnerability_check' => '',
            'sp_ai_external_api_enabled' => true, 'has_ai_report' => false, 'is_ai_pending' => false, 'is_ai_failed' => false,
            'ai_diagnostic_report' => null, 'ai_diagnostic_status' => null, 'ai_diagnostic_requested_at' => null,
            'ai_diagnostic_queue_position' => null,
            'disk_write' => array('avg_ms' => 5, 'min_ms' => 3, 'max_ms' => 7, 'dir' => '/tmp/test-logs', 'iterations' => 3, 'size_bytes' => 1024, 'total_written_bytes' => 3072, 'success' => true),
            'slow_queries' => array('success' => true, 'results' => array(), 'error' => '', 'message' => ''),
        );
    }

    private function render($template, array $overrides = array()): string {
        extract(array_replace($this->data(), $overrides), EXTR_SKIP);
        ob_start();
        try { require SITEPULSE_PATH . 'templates/' . $template; return ob_get_contents(); }
        finally { ob_end_clean(); }
    }

    private static function pagespeed(): array {
        return array(
            'scores' => array('performance' => 0.96, 'accessibility' => 65, 'best_practices' => 0.3, 'seo' => 0.9),
            'screenshot' => 'data:image/png;base64,YWJj',
            'core_web_vitals' => array(
                'fcp' => array('score' => 0.95, 'display_value' => '0.8 s'),
                'lcp' => array('score' => 0.65, 'display_value' => '2.8 s'),
                'tbt' => array('score' => 0.2, 'display_value' => '850 ms'),
                'cls' => array('score' => 0.9, 'display_value' => '0.05'),
                'si' => array('display_value' => '2.1 s'),
            ),
            'opportunities' => array(array('title' => 'Compress <images>', 'description' => 'Compress large images before uploading to reduce page weight.', 'savings' => '1.2 s', 'display_value' => '1.2 s')),
            'diagnostics' => array(array('title' => 'Reduce DOM nodes', 'description' => 'Review the number of elements in the page markup.')),
        );
    }

    private static function report(): array {
        return array(
            'status' => 'warning', 'health_score' => 65, 'summary' => '<script>fixture</script> needs improvement',
            'metrics_summary' => array('pagespeed_grade' => 'A', 'security_grade' => 'B', 'error_severity' => 'medium', 'overall_health_score' => 65),
            'insights' => array('performance' => 'Optimize loading', 'security' => array('finding' => 'Update vulnerable plugin', 'severity' => 'poor'), 'errors' => 'Check PHP errors'),
            'recommendations' => array('critical' => array('Patch security'), 'high' => array('Add caching'), 'moderate' => array('Compress assets'), 'low' => array('Review settings')),
            'quick_wins' => array(array('title' => 'Add page cache', 'impact' => 'Faster pages', 'fix' => 'Enable a cache', 'code_snippet' => '<cache enabled="true">')),
            'action_items' => array(
                array('title' => 'Upgrade plugin', 'priority' => 'critical', 'estimated_effort' => 'quick-fix', 'problem' => 'Old version', 'fix' => 'Install update', 'code_snippet' => 'wp plugin update sample'),
                array('title' => 'Review cache', 'priority' => 'high', 'estimated_effort' => 'moderate'),
            ),
            'wordpress_specific' => array(
                'wp_config_tweaks' => array(array('setting' => 'Enable cache', 'reason' => 'Reduce load', 'code' => 'define("WP_CACHE", true);')),
                'plugins_to_review' => array(array('name' => 'Old Plugin', 'issue' => 'Slow requests', 'action' => 'Replace it', 'alternative' => 'Modern Plugin'), 'Another Plugin'),
                'recommended_plugins' => array(array('name' => 'Cache Plugin', 'purpose' => 'Page caching', 'install_command' => 'wp plugin install cache'), 'Optimizer'),
                'hosting_notes' => 'Use supported PHP and enough memory.',
            ),
        );
    }

    public static function diagnostics(): array {
        $rows = array();
        foreach (array('backend/dashboard.php', 'easy/views/home.php') as $view) {
            foreach (array('pending', 'processing', 'failed', 'completed') as $status) {
                $rows[$view . ' ' . $status] = array($view, $status);
            }
        }
        return $rows;
    }

    /** @dataProvider diagnostics */
    public function test_diagnostic_states_show_pending_retry_or_escaped_report($view, $status): void {
        $html = $this->render($view, array(
            'ai_diagnostic_status' => $status, 'is_ai_pending' => in_array($status, array('pending', 'processing'), true),
            'is_ai_failed' => $status === 'failed', 'has_ai_report' => $status === 'completed',
            'ai_diagnostic_report' => self::report(), 'ai_diagnostic_requested_at' => '2026-10-04 12:00:00', 'ai_diagnostic_queue_position' => 3,
        ));
        $this->assertStringContainsString('data-status="' . $status . '"', $html);
        if ($status === 'completed') {
            $this->assertStringContainsString('&lt;script&gt;fixture&lt;/script&gt;', $html);
            $this->assertStringNotContainsString('<script>fixture</script>', $html);
            $this->assertStringContainsString('Optimize loading', $html);
            $this->assertStringContainsString('Optimizer', $html);
        } elseif ($status === 'failed') {
            $this->assertStringContainsString('Diagnostic Failed', $html);
            $this->assertStringContainsString('data-force="true"', $html);
        } else {
            $this->assertStringNotContainsString('needs improvement', $html);
            $this->assertStringContainsString($view === 'backend/dashboard.php' ? 'Auto-checking every minute' : 'safely navigate away', $html);
        }
    }

    public static function screenshotFormats(): array {
        return array('embedded image' => array(array('data' => 'data:image/png;base64,YWJj'), true), 'remote image' => array('https://example.test/preview.png', true), 'invalid image' => array('javascript:alert(1)', false), 'missing image' => array('', false));
    }

    /** @dataProvider screenshotFormats */
    public function test_speed_insights_render_scores_vitals_and_safe_image_formats($screenshot, $hasImage): void {
        $data = self::pagespeed();
        $data['screenshot'] = $screenshot;
        $html = $this->render('easy/views/insights.php', array('has_valid_pagespeed' => true, 'ps_data' => $data, 'ps_performance_score' => 96));
        $this->assertStringContainsString('data-score="96"', $html);
        foreach (array('success', 'warning', 'danger', 'neutral') as $color) {
            $this->assertStringContainsString('sp-cwv-card--' . $color, $html);
        }
        $this->assertStringContainsString('Compress &lt;images&gt;', $html);
        $this->assertStringContainsString('Reduce DOM nodes', $html);
        $this->assertSame($hasImage, strpos($html, 'alt="PageSpeed screenshot"') !== false);
        $this->assertStringNotContainsString('src="javascript:', $html);
    }

    public function test_speed_insights_show_optimized_empty_recommendations(): void {
        $data = self::pagespeed(); $data['opportunities'] = $data['diagnostics'] = array();
        $html = $this->render('easy/views/insights.php', array('has_valid_pagespeed' => true, 'ps_data' => $data));
        $this->assertStringContainsString('No improvements needed', $html);
        $this->assertStringContainsString('No additional details', $html);
    }

    public function test_easy_home_legacy_recommendations_and_failed_sql_are_actionable(): void {
        $report = self::report(); $report['action_items'] = array();
        $html = $this->render('easy/views/home.php', array('has_ai_report' => true, 'ai_diagnostic_report' => $report,
            'slow_queries' => array('success' => false, 'error' => 'savequeries_disabled', 'message' => 'Enable query monitoring', 'results' => array())));
        $this->assertStringContainsString('Patch security', $html);
        $this->assertStringContainsString('sp-enable-savequeries', $html);
        $this->assertStringContainsString('Enable query monitoring', $html);
    }

    public function test_easy_home_guides_new_users_and_keeps_the_promo_below_the_health_numbers(): void {
        $steps = array(
            array('id' => 'analyze', 'done' => true, 'title' => 'Analyze one page', 'text' => 'See it.', 'url' => 'https://example.test/analyze', 'action' => 'Analyze homepage'),
            array('id' => 'plugins', 'done' => false, 'title' => 'Find your <heaviest> plugins', 'text' => 'Rankings.', 'url' => 'https://example.test/plugins', 'action' => 'Open Plugin Activity'),
            array('id' => 'requests', 'done' => false, 'title' => 'Check external requests', 'text' => 'Waits.', 'url' => 'https://example.test/requests', 'action' => 'Open External Requests'),
        );
        $html = $this->render('easy/views/home.php', array('show_getting_started' => true, 'getting_started_steps' => $steps));
        $this->assertStringContainsString('Get started with SitePulse', $html); $this->assertStringContainsString('1 of 3 done.', $html); $this->assertStringContainsString('width: 33%;', $html);
        $this->assertSame(1, substr_count($html, 'sp-getting-started-step is-done')); $this->assertStringContainsString('Find your &lt;heaviest&gt; plugins', $html);
        $this->assertStringContainsString('href="https://example.test/plugins">Open Plugin Activity</a>', $html); $this->assertStringNotContainsString('>Analyze homepage<', $html);
        $this->assertStringContainsString('<input type="hidden" name="page" value="wpsp_sitepulse_page_analysis">', $html); $this->assertStringContainsString('id="sp-ai-card"', $html);
        $this->assertGreaterThan(strpos($html, 'Key Numbers'), strpos($html, 'Claim Your Early Access'));
        $this->assertStringContainsString('Performance Monitor', $html); $this->assertStringNotContainsString('Activity Monitor', $html);
        $this->assertStringNotContainsString('Get started with SitePulse', $this->render('easy/views/home.php'));
    }

    /** @dataProvider classicViews */
    public function test_classic_dashboard_renders_the_saved_view_without_waiting_for_javascript($view, $basic_style, $developer_style, $offer): void {
        $html = $this->render('backend/dashboard.php', array('sp_dashboard_view' => $view));
        $this->assertStringContainsString('<div id="sp-basic-view" class="sp-view-mode"' . $basic_style . '>', $html);
        $this->assertStringContainsString('<div id="sp-developer-view" class="sp-view-mode"' . $developer_style . '>', $html);
        $this->assertStringContainsString('data-view="' . $offer . '"', $html); $this->assertStringContainsString('class="sp-analyze-card"', $html);
        $this->assertGreaterThan(strpos($html, 'Your Site Health Overview'), strpos($html, 'Claim Your Early Access'));
        $this->assertStringContainsString('External Requests', $html); $this->assertStringNotContainsString('LoadSentinel', $html);
    }
    public static function classicViews(): array {
        return array('basic' => array('basic', '', ' style="display: none;"', 'developer'), 'developer' => array('developer', ' style="display: none;"', '', 'basic'));
    }

    private function populated(): array {
        $data = $this->data();
        $data['has_valid_pagespeed'] = true; $data['ps_data'] = self::pagespeed(); $data['ps_performance_score'] = 96;
        $data['total_load_time'] = 6000; $data['show_performance_warnings'] = true; $data['is_overall_slow'] = true;
        $data['error_count'] = 5; $data['fatal_count'] = 2; $data['warning_count'] = 3;
        $data['active_plugins_count'] = 35; $data['memory_score'] = 25;
        $data['vulnerabilities'] = array(array('name' => 'Unsafe <Plugin>', 'version' => '1.0'));
        $data['last_vulnerability_check'] = '2026-10-04 12:00:00';
        $data['slow_queries'] = array('success' => true, 'results' => array(
            array('sql' => 'SELECT * FROM wp_posts', 'time_ms' => 1200, 'caller' => 'Plugin::query'),
            array('sql' => 'SELECT 2', 'time_ms' => 750, 'caller' => ''), array('sql' => 'SELECT 3', 'time_ms' => 150, 'caller' => 'Theme::query'),
        ));
        for ($index = 0; $index < 12; $index++) {
            $milliseconds = array(2000, 750, 100)[$index % 3];
            $data['stats'][] = array('total' => $milliseconds / 1000, 'calls' => 2, 'source' => 'fixture-plugin-' . $index, 'hook' => 'init', 'fileline' => 'plugin.php:' . $index);
            $data['plugin_profiler_stats'][] = array('plugin_name' => 'Fixture Plugin ' . $index, 'plugin_slug' => 'fixture-' . $index, 'plugin_version' => '1.0', 'avg_ms' => $milliseconds, 'avg_memory' => 1024);
            $data['curl_events'][] = array('elapsed' => $milliseconds / 1000, 'origin' => 'Fixture HTTP ' . $index, 'url' => 'https://example.test/' . str_repeat('a', 90), 'file' => 'plugin.php', 'code' => $index % 2 ? 200 : 0);
            $data['all_slow_items'][] = array('name' => 'Slow Plugin ' . $index, 'severity' => $index % 2 ? 'warning' : 'critical', 'time_ms' => $index % 2 ? 750 : 2000, 'date_time' => '2026-10-04 12:00:00');
            $data['slow_api_requests'][] = array('origin' => 'Slow HTTP ' . $index, 'severity' => $index % 2 ? 'warning' : 'critical', 'time_ms' => $index % 2 ? 750 : 2000, 'url' => 'https://example.test/' . str_repeat('b', 90), 'code' => 200);
        }
        $data['top_slow_item'] = array('name' => 'Slow Plugin 0', 'time_ms' => 2000, 'hook' => 'init', 'fileline' => 'plugin.php:0');
        return $data;
    }

    public static function severityViews(): array {
        return array('classic high' => array('backend/dashboard.php', 95), 'classic medium' => array('backend/dashboard.php', 65), 'classic low' => array('backend/dashboard.php', 25), 'easy home high' => array('easy/views/home.php', 95), 'easy home medium' => array('easy/views/home.php', 65), 'easy home low' => array('easy/views/home.php', 25));
    }

    /** @dataProvider severityViews */
    public function test_populated_dashboards_explain_scores_and_escape_report_data($view, $score): void {
        $data = $this->populated();
        foreach (array('overall_score', 'speed_score', 'resource_score', 'loadsentinel_score', 'api_score', 'memory_score', 'plugins_score', 'error_log_score') as $metric) { $data[$metric] = $score; }
        $data['disk_write']['avg_ms'] = $score === 95 ? 5 : ($score === 65 ? 75 : 125);
        $html = $this->render($view, $data);
        $this->assertStringContainsString('SELECT * FROM wp_posts', $html);
        $this->assertStringContainsString('Slow Plugin 0', $html);
        $this->assertStringContainsString('0.8 s', $html);
        if ($view === 'backend/dashboard.php') {
            $this->assertStringContainsString('Unsafe &lt;Plugin&gt;', $html);
            $this->assertStringContainsString('Fixture Plugin 0', $html);
            $this->assertStringNotContainsString('Fixture Plugin 10<', $html);
        }
    }

    public static function dashboardMessages(): array {
        return array(
            'moderate estimated speed' => array(array('speed_score' => 65), 'Moderate Speed'),
            'slow estimated speed' => array(array('speed_score' => 25), 'Slow Loading'),
            'fatal errors only' => array(array('error_count' => 2, 'fatal_count' => 2), '2 Errors Found'),
            'warnings only' => array(array('error_count' => 2, 'warning_count' => 2), '2 Warnings Found'),
            'completed clean vulnerability scan' => array(array('last_vulnerability_check' => '2026-10-04 12:00:00'), 'No vulnerabilities found'),
            'moderate measured latency' => array(array('total_load_time' => 2500, 'show_performance_warnings' => true), 'Moderate Performance Issues'),
            'slow measured latency' => array(array('total_load_time' => 3500, 'loadsentinel_score' => 45, 'is_overall_slow' => true, 'show_performance_warnings' => true), 'Slow Performance:'),
            'low sampled score without a single bottleneck' => array(array('total_load_time' => 800, 'loadsentinel_score' => 55, 'is_overall_slow' => true, 'show_performance_warnings' => true), 'General Optimization'),
        );
    }

    /** @dataProvider dashboardMessages */
    public function test_dashboard_messages_explain_the_specific_observed_condition($overrides, $message): void {
        $this->assertStringContainsString($message, $this->render('backend/dashboard.php', $overrides));
    }

    public static function slowSeverities(): array { return array('critical only' => array('critical', 2000), 'warning only' => array('warning', 750)); }

    /** @dataProvider slowSeverities */
    public function test_dashboard_distinguishes_single_warning_and_critical_bottlenecks($severity, $milliseconds): void {
        $html = $this->render('backend/dashboard.php', array(
            'show_performance_warnings' => true,
            'top_slow_item' => array('name' => 'Bottleneck', 'time_ms' => $milliseconds, 'hook' => 'init', 'fileline' => 'plugin.php:1'),
            'all_slow_items' => array(array('name' => 'Bottleneck', 'severity' => $severity, 'time_ms' => $milliseconds)),
            'slow_api_requests' => array(array('origin' => 'Slow service', 'severity' => $severity, 'time_ms' => $milliseconds, 'url' => 'https://example.test', 'code' => 503)),
        ));
        $this->assertStringContainsString($severity === 'critical' ? '1 Critical Issue Found' : '1 Performance Warning', $html);
        $this->assertStringContainsString('Slow service', $html);
        $this->assertStringContainsString('503', $html);
    }

    public function test_dashboards_render_partial_pagespeed_results_and_available_screenshot(): void {
        $ps = self::pagespeed();
        $ps['scores'] = array('performance' => 0.65);
        $ps['core_web_vitals'] = array('fcp' => array('score' => 0.9, 'display_value' => '0.8 s'));
        $ps['screenshot'] = array('data' => 'data:image/png;base64,YWJj');
        $classic = $this->render('backend/dashboard.php', array('has_valid_pagespeed' => true, 'ps_data' => $ps, 'ps_performance_score' => 65));
        $this->assertStringContainsString('class="sp-screenshot-img"', $classic);
        $this->assertStringContainsString('0.8 s', $classic);
        $home = $this->render('easy/views/home.php', array('has_valid_pagespeed' => true, 'ps_data' => $ps, 'ps_performance_score' => 25, 'total_load_time' => 800));
        $this->assertStringContainsString('800ms', $home);
        $this->assertStringContainsString('0.8 s', $home);
    }

    public function test_classic_pagespeed_poor_performance_is_identified(): void {
        $this->assertStringContainsString('sp-score-poor', $this->render('backend/dashboard.php', array('has_valid_pagespeed' => true, 'ps_data' => self::pagespeed(), 'ps_performance_score' => 25)));
    }

    public static function troubleshootStates(): array { return array('active' => array(true), 'inactive' => array(false)); }

    /** @dataProvider troubleshootStates */
    public function test_easy_home_shows_external_pro_troubleshooter_state($active): void {
        // The separately distributed Pro plugin is an external boundary here.
        $pro = new class {
            public static $active;
            public static function get_instance() { return new self(); }
            public function is_active() { return self::$active; }
            public function get_disabled_plugins() { return array('sample/plugin.php', 'other/plugin.php'); }
        };
        $pro::$active = $active;
        class_alias(get_class($pro), 'Sitepulse_Pro_Plugin_Troubleshoot');
        $html = $this->render('easy/views/home.php');
        $this->assertStringContainsString('Plugin Conflict Checker', $html);
        $this->assertStringContainsString($active ? 'Testing Mode Active' : 'Plugin Conflict Testing', $html);
        $this->assertSame($active, strpos($html, 'sp-troubleshoot-disable') !== false);
    }

    public function test_classic_query_failure_is_not_reported_as_a_clean_scan(): void {
        $html = $this->render('backend/dashboard.php', array('slow_queries' => array('success' => false, 'error' => 'query_failed', 'message' => 'Query history unavailable', 'results' => array())));
        $this->assertStringContainsString('Query history unavailable', $html);
        $this->assertStringNotContainsString('No slow queries detected.', $html);
    }

    public function test_disabled_query_monitor_keeps_its_enable_action_in_the_error_state(): void {
        $html = $this->render('backend/dashboard.php', array('slow_queries' => array('success' => false, 'error' => 'savequeries_disabled', 'message' => 'Enable SAVEQUERIES to monitor SQL.', 'results' => array())));
        $this->assertTrue(strpos($html, 'fix_enable_savequeries') !== false, 'disabled query monitoring needs an enable action');
        $this->assertStringNotContainsString('No slow queries detected.', $html);
    }

    public function test_disabled_trackers_have_an_inactive_dashboard_status(): void {
        $html = $this->render('backend/dashboard.php', array('sitepulse_lowhttp_enabled' => 0, 'sitepulse_profiler_enabled' => 0));
        $this->assertStringContainsString('header-actions disabled', $html);
    }

    public function test_speed_insights_without_a_valid_report_prompt_for_initial_collection(): void {
        $this->assertStringContainsString('No Speed Data Yet', $this->render('easy/views/insights.php'));
    }
}

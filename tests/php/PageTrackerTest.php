<?php
final class PageTrackerTest extends Sitepulse_Test_Case {
    private const TOKEN = 'abcdefghijklmnopqrstuvwxyz012345';

    /** SitePulse's own folder, whatever the checkout or install directory is called. */
    private static function ownFolder(): string {
        return basename(dirname(SITEPULSE_PLUGIN_FILE));
    }

    private function stat($source, $fileline, $self, $total = null, $calls = 1, $hook = 'init'): array {
        $stat = array('source' => $source, 'fileline' => $fileline, 'hook' => $hook, 'sig' => $source . '_callback', 'calls' => $calls, 'total_ms' => $total ?? $self);
        if ($self !== null) { $stat['self_total_ms'] = $self; }
        return $stat;
    }

    /** @dataProvider rejectedTokens */
    public function test_requests_without_a_valid_issued_token_are_not_analysis_requests($query): void {
        set_transient('sitepulse_pa_token_' . self::TOKEN, 'analysis1'); $_GET = $query;
        Sitepulse_Page_Tracker::init();
        $this->assertFalse(Sitepulse_Page_Tracker::is_analysis_request()); $this->assertFalse(has_action('shutdown'));
        Sitepulse_Page_Tracker::record_http_event(array('url' => 'https://api.test')); Sitepulse_Page_Tracker::store_sample();
        $this->assertFalse(get_transient('sitepulse_pa_sample_' . self::TOKEN));
    }
    public static function rejectedTokens(): array {
        return array('absent' => array(array()), 'array injection' => array(array('sitepulse_analyze' => array(self::TOKEN))), 'too short' => array(array('sitepulse_analyze' => 'abc')),
            'never issued' => array(array('sitepulse_analyze' => str_repeat('z', 32))), 'markup is stripped to an unknown token' => array(array('sitepulse_analyze' => '<b>' . self::TOKEN . '</b>')));
    }
    public function test_issued_token_turns_on_isolated_profiling_until_the_end_of_the_request(): void {
        $this->startAnalysisRequest();
        $this->assertTrue(Sitepulse_Page_Tracker::is_analysis_request()); $this->assertSame(PHP_INT_MAX, has_action('shutdown', array('Sitepulse_Page_Tracker', 'store_sample')));
        $this->assertTrue(defined('DONOTCACHEPAGE') && DONOTCACHEPAGE);
        $_GET['sitepulse_analyze'] = strtoupper(self::TOKEN); $this->assertSame(self::TOKEN, Sitepulse_Page_Tracker::token_from_request());
    }
    public function test_sample_measures_the_request_and_attributes_exclusive_time_by_source(): void {
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true) - 0.25; Sitepulse_Test_WP::$num_queries = 42;
        Sitepulse_Test_WP::$document_title = 'Blue Shirt &#8211; Shop'; Sitepulse_Test_WP::$is_singular = true; Sitepulse_Test_WP::$post_type = 'product';
        Sitepulse_Test_WP::$post_types = array('product' => sitepulse_test_post_type('product', 'Product'));
        $this->startAnalysisRequest();
        $this->setStaticProperty('Sitepulse_Profiler', 'stats', array(
            $this->stat('woocommerce', 'wp-content/plugins/woocommerce/includes/class-wc.php:10', 120.0, 300.0, 4),
            $this->stat('woocommerce', 'wp-content/plugins/woocommerce/includes/cart.php:5', 30.0, 30.0, 2, 'wp_loaded'),
            $this->stat('storefront', 'wp-content/themes/storefront/functions.php:3', 80.0, 400.0, 1, 'theme_template'),
            $this->stat('(unknown)', 'wp-includes/script-loader.php:200', 15.0, 15.0, 3, 'wp_head'),
            $this->stat(self::ownFolder(), 'wp-content/plugins/' . self::ownFolder() . '/class/profiler.php:1', 50.0),
            $this->stat('legacy', 'wp-content/plugins/legacy/legacy.php:1', null, 9.5),
            array('source' => 'bare', 'fileline' => 'wp-content/plugins/bare/bare.php:1'),
        ));
        Sitepulse_Page_Tracker::store_sample();

        $sample = get_transient('sitepulse_pa_sample_' . self::TOKEN);
        $this->assertSame(900, Sitepulse_Test_WP::$transient_expirations['sitepulse_pa_sample_' . self::TOKEN]);
        $this->assertGreaterThanOrEqual(250, $sample['server_ms']); $this->assertLessThan(5000, $sample['server_ms']);
        $this->assertSame(42, $sample['queries']); $this->assertSame(200, $sample['status']); $this->assertGreaterThan(0, $sample['memory_peak']);
        $this->assertSame(array('title' => 'Blue Shirt – Shop', 'kind' => 'Product'), $sample['page']);
        $this->assertSame(array(
            array('source' => 'woocommerce', 'type' => 'plugin', 'self_ms' => 150.0, 'calls' => 6),
            array('source' => 'storefront', 'type' => 'theme', 'self_ms' => 80.0, 'calls' => 1),
            array('source' => '(unknown)', 'type' => 'core', 'self_ms' => 15.0, 'calls' => 3),
            array('source' => 'legacy', 'type' => 'plugin', 'self_ms' => 9.5, 'calls' => 1),
            array('source' => 'bare', 'type' => 'plugin', 'self_ms' => 0.0, 'calls' => 0),
        ), $sample['sources']);
        $this->assertSame(array('hook' => 'init', 'callback' => 'woocommerce_callback', 'fileline' => 'wp-content/plugins/woocommerce/includes/class-wc.php:10', 'source' => 'woocommerce', 'type' => 'plugin', 'calls' => 4, 'self_ms' => 120.0, 'total_ms' => 300.0), $sample['callbacks'][0]);
        $this->assertNotContains(self::ownFolder(), array_column($sample['callbacks'], 'source')); $this->assertSame(array('hook' => '', 'callback' => ''), array_intersect_key(end($sample['callbacks']), array('hook' => 1, 'callback' => 1)));
        $this->assertSame(array(), $sample['http']);
    }
    public function test_sample_lists_are_capped_and_request_start_falls_back_to_tracker_start(): void {
        unset($_SERVER['REQUEST_TIME_FLOAT']); $this->startAnalysisRequest(); $stats = array();
        for ($i = 0; $i < 30; $i++) { $stats[] = $this->stat('plugin-' . $i, 'wp-content/plugins/plugin-' . $i . '/main.php:1', (float) $i); }
        $this->setStaticProperty('Sitepulse_Profiler', 'stats', $stats); Sitepulse_Page_Tracker::store_sample();
        $sample = get_transient('sitepulse_pa_sample_' . self::TOKEN);
        $this->assertCount(25, $sample['sources']); $this->assertSame('plugin-29', $sample['sources'][0]['source']); $this->assertCount(15, $sample['callbacks']);
        $this->assertLessThan(1000, $sample['server_ms']);
    }
    /** @dataProvider pageKinds */
    public function test_page_kind_names_any_type_of_front_end_page(array $flags, $expected): void {
        foreach ($flags as $flag => $value) { Sitepulse_Test_WP::${$flag} = $value; }
        Sitepulse_Test_WP::$post_types = array('product' => sitepulse_test_post_type('product', 'Product'));
        $this->startAnalysisRequest(); Sitepulse_Page_Tracker::store_sample();
        $this->assertSame($expected, get_transient('sitepulse_pa_sample_' . self::TOKEN)['page']['kind']);
    }
    public static function pageKinds(): array {
        return array(
            'not found' => array(array('is_404' => true, 'is_front_page' => true), 'Not found (404)'), 'front page' => array(array('is_front_page' => true), 'Front page'),
            'posts page' => array(array('is_home' => true), 'Blog page'), 'product' => array(array('is_singular' => true, 'post_type' => 'product'), 'Product'),
            'unregistered type' => array(array('is_singular' => true, 'post_type' => 'ghost'), 'Single page'), 'search' => array(array('is_search' => true), 'Search results'),
            'archive' => array(array('is_archive' => true), 'Archive'), 'anything else' => array(array(), 'Page'),
        );
    }
    public function test_missing_profiler_and_document_title_still_produce_a_sample(): void {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/core-variants.php', array('mode' => 'tracker_without_profiler', 'exclude_classes' => array('Sitepulse_Profiler'), 'exclude_functions' => array('wp_get_document_title', 'get_num_queries')));
        $this->assertSame(0, $result['status'], $result['error']);
        $sample = json_decode($result['output'], true);
        $this->assertSame(array(), $sample['sources']); $this->assertSame('', $sample['page']['title']); $this->assertSame(0, $sample['queries']);
    }
}

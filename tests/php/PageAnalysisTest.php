<?php
require_once __DIR__ . '/Support/PresentationWordPress.php';

if (!function_exists('wc_get_page_id')) {
    function wc_get_page_id($page) { return $page === 'shop' ? (int) ($GLOBALS['sitepulse_test_shop_page'] ?? -1) : -1; }
}

final class PageAnalysisTest extends Sitepulse_Test_Case {
    protected function setUp(): void {
        parent::setUp();
        Sitepulse_Test_WP::$plugins = array('woocommerce/woocommerce.php' => array('Name' => 'WooCommerce'), 'hello.php' => array('Name' => 'Hello Dolly'), 'nameless/nameless.php' => array('Name' => ''));
        $GLOBALS['sitepulse_test_shop_page'] = -1;
    }

    /** A sample as the page tracker stores it. */
    private function sample($server_ms, array $overrides = array()): array {
        return array_replace(array(
            'server_ms' => $server_ms, 'memory_peak' => 40 * MB_IN_BYTES, 'queries' => 30, 'status' => 200,
            'page' => array('title' => 'Blue Shirt – Shop', 'kind' => 'Product'),
            'sources' => array(), 'callbacks' => array(), 'http' => array(), 'captured_at' => 1700000000,
        ), $overrides);
    }

    private function analysis(array $samples, array $extra = array()): array {
        return array_replace(array('id' => 'a1', 'url' => 'https://example.test/product/blue-shirt/', 'created_at' => 1700000000, 'samples' => $samples, 'pending_tokens' => array()), $extra);
    }

    /** Queue a loopback response; when $sample is given, the "analyzed request" stores it under the token from the URL. */
    private function queueLoopback($response, $sample = null): void {
        Sitepulse_Test_WP::$http_queue[] = static function ($url, $args) use ($response, $sample) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            if ($sample !== null) { set_transient('sitepulse_pa_sample_' . $query['sitepulse_analyze'], $sample); }
            return $response;
        };
    }

    public function test_admin_link_encodes_the_page_address_and_optional_autorun(): void {
        $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_page_analysis', Sitepulse_Page_Analysis::admin_link());
        $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_page_analysis&url=https%3A%2F%2Fexample.test%2F%3Fs%3Da%26b&autorun=1', Sitepulse_Page_Analysis::admin_link('https://example.test/?s=a&b', true));
    }

    /** @dataProvider acceptedAddresses */
    public function test_any_front_end_address_on_this_site_is_accepted($input, $expected, $home = 'https://example.test'): void {
        Sitepulse_Test_WP::$home_url = $home;
        $this->assertSame($expected, Sitepulse_Page_Analysis::normalize_url($input));
    }
    public static function acceptedAddresses(): array {
        return array(
            'full url' => array(' https://example.test/product/blue-shirt/ ', 'https://example.test/product/blue-shirt/'),
            'host relative path' => array('/shop/', 'https://example.test/shop/'),
            'path relative to home' => array('category/news/', 'https://example.test/category/news/'),
            'domain without scheme' => array('example.test/cart/', 'https://example.test/cart/'),
            'home scheme wins and host is lowercased' => array('http://EXAMPLE.test/checkout/', 'https://example.test/checkout/'),
            'site root' => array('https://example.test', 'https://example.test/'),
            'query kept, fragment and analysis token dropped' => array('https://example.test/?s=shoes&post_type=product&sitepulse_analyze=abc#results', 'https://example.test/?s=shoes&post_type=product'),
            'port kept for local sites' => array('/shop/', 'http://localhost:8080/shop/', 'http://localhost:8080'),
            'explicit port kept' => array('http://localhost:8080/about/', 'http://localhost:8080/about/', 'http://localhost:8080'),
            'omitted port means this site' => array('localhost/about/', 'http://localhost:8080/about/', 'http://localhost:8080'),
            'default port written out' => array('https://example.test:443/cart/', 'https://example.test/cart/'),
            'subdirectory install path' => array('/blog/contact/', 'https://example.test/blog/contact/', 'https://example.test/blog'),
            'subdirectory path meant relative to the site' => array('/contact/', 'https://example.test/blog/contact/', 'https://example.test/blog'),
            'subdirectory home without trailing slash' => array('https://example.test/blog', 'https://example.test/blog', 'https://example.test/blog'),
            'subdirectory full url' => array('https://example.test/blog/shop/', 'https://example.test/blog/shop/', 'https://example.test/blog'),
        );
    }

    /** @dataProvider rejectedAddresses */
    public function test_other_sites_admin_and_api_addresses_are_rejected_with_guidance($input, $code, $message): void {
        $error = Sitepulse_Page_Analysis::normalize_url($input);
        $this->assertInstanceOf(WP_Error::class, $error); $this->assertSame($code, $error->get_error_code());
        $this->assertSame(array('status' => 400), $error->get_error_data()); $this->assertStringContainsString($message, $error->get_error_message());
    }
    public static function rejectedAddresses(): array {
        return array(
            'empty' => array('   ', 'sitepulse_missing_url', 'Enter the address'), 'other site' => array('https://elsewhere.test/', 'sitepulse_external_url', 'Only pages on example.test'),
            'lookalike domain' => array('https://example.test.evil.test/', 'sitepulse_external_url', 'example.test'), 'protocol relative other host' => array('//elsewhere.test/x', 'sitepulse_external_url', 'example.test'),
            'malformed' => array('https:///nothing', 'sitepulse_external_url', 'example.test'),
            'another port on this host' => array('https://example.test:22/', 'sitepulse_external_url', 'example.test'),
            'internal service port' => array('https://example.test:6379/', 'sitepulse_external_url', 'example.test'), 'admin' => array('/wp-admin/edit.php', 'sitepulse_admin_url', 'Choose a page your visitors see'),
            'login' => array('https://example.test/wp-login.php?action=lostpassword', 'sitepulse_admin_url', 'login'), 'rest api' => array('/wp-json/wp/v2/posts', 'sitepulse_admin_url', 'API'),
            'xmlrpc' => array('/xmlrpc.php', 'sitepulse_admin_url', 'API'), 'cron' => array('/wp-cron.php', 'sitepulse_admin_url', 'API'),
        );
    }

    public function test_sites_sharing_the_host_in_a_network_cannot_be_analyzed_from_this_site(): void {
        Sitepulse_Test_WP::$home_url = 'https://example.test/store';
        foreach (array('https://example.test/', 'https://example.test/other-site/page/', 'https://example.test/storefront/') as $input) {
            $error = Sitepulse_Page_Analysis::normalize_url($input);
            $this->assertInstanceOf(WP_Error::class, $error, $input); $this->assertSame('Only pages on example.test/store can be analyzed.', $error->get_error_message());
        }
    }

    public function test_start_keeps_one_analysis_per_page_newest_first_and_caps_history(): void {
        for ($i = 0; $i < 9; $i++) { Sitepulse_Test_WP::$passwords[] = 'ID' . $i; Sitepulse_Page_Analysis::start('/page-' . $i . '/'); }
        Sitepulse_Test_WP::$passwords[] = 'REPEAT'; $repeat = Sitepulse_Page_Analysis::start('/page-5/');
        $stored = get_option('sitepulse_page_analyses');
        $this->assertSame(array('id' => 'repeat', 'url' => 'https://example.test/page-5/', 'created_at' => time(), 'samples' => array(), 'pending_tokens' => array()), array_replace($repeat, array('created_at' => time())));
        $this->assertCount(8, $stored); $this->assertSame('repeat', $stored[0]['id']); $this->assertSame(array('repeat', 'id8', 'id7', 'id6', 'id4', 'id3', 'id2', 'id1'), array_column($stored, 'id'));
        $this->assertSame(false, end(Sitepulse_Test_WP::$option_writes)['autoload']);
        $this->assertInstanceOf(WP_Error::class, Sitepulse_Page_Analysis::start('https://elsewhere.test/')); $this->assertCount(8, get_option('sitepulse_page_analyses'));
    }

    public function test_sample_loads_the_page_as_a_logged_out_visitor_and_stores_what_it_measured(): void {
        Sitepulse_Test_WP::$passwords = array('A1', 'TOKENTOKENTOKENTOKENTOKENTOKEN12'); Sitepulse_Page_Analysis::start('/product/blue-shirt/');
        add_filter('https_local_ssl_verify', '__return_true');
        $this->queueLoopback(Sitepulse_Test_WP::response('<html>page</html>'), $this->sample(321.5));
        $result = Sitepulse_Page_Analysis::run_sample('a1');

        $this->assertSame(array('ok' => true, 'samples' => 1, 'server_ms' => 321.5), $result);
        $request = Sitepulse_Test_WP::$http_requests[0];
        $this->assertSame('https://example.test/product/blue-shirt/?sitepulse_analyze=tokentokentokentokentokentoken12', $request['url']);
        $this->assertSame(20, $request['args']['timeout']); $this->assertTrue($request['args']['sslverify']); $this->assertSame(array(), $request['args']['cookies']);
        $this->assertSame('no-cache', $request['args']['headers']['Cache-Control']); $this->assertArrayNotHasKey('Authorization', $request['args']['headers']);
        $stored = get_option('sitepulse_page_analyses')[0];
        $this->assertCount(1, $stored['samples']); $this->assertGreaterThanOrEqual(0, $stored['samples'][0]['response_ms']); $this->assertSame(array(), $stored['pending_tokens']);
        $this->assertFalse(get_transient('sitepulse_pa_token_tokentokentokentokentokentoken12')); $this->assertFalse(get_transient('sitepulse_pa_sample_tokentokentokentokentokentoken12'));
    }

    public function test_sample_forwards_http_basic_credentials_like_site_health(): void {
        $_SERVER['PHP_AUTH_USER'] = 'staging'; $_SERVER['PHP_AUTH_PW'] = 'secret';
        Sitepulse_Test_WP::$passwords = array('A1'); Sitepulse_Page_Analysis::start('/');
        $this->queueLoopback(Sitepulse_Test_WP::response('ok'), $this->sample(100));
        Sitepulse_Page_Analysis::run_sample('a1');
        unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        $this->assertSame('Basic ' . base64_encode('staging:secret'), Sitepulse_Test_WP::$http_requests[0]['args']['headers']['Authorization']);
    }

    /** @dataProvider loopbackFailures */
    public function test_failed_page_loads_explain_why_and_keep_a_measurable_visit_link($response, $reason, $message): void {
        Sitepulse_Test_WP::$passwords = array('A1', 'VISITTOKENVISITTOKENVISITTOKEN12'); Sitepulse_Page_Analysis::start('/shop/');
        $this->queueLoopback($response);
        $result = Sitepulse_Page_Analysis::run_sample('a1');
        $this->assertFalse($result['ok']); $this->assertSame(0, $result['samples']); $this->assertSame($reason, $result['reason']); $this->assertStringContainsString($message, $result['message']);
        $this->assertSame('https://example.test/shop/?sitepulse_analyze=visittokenvisittokenvisittoken12', $result['visit_url']);
        $this->assertSame(array('visittokenvisittokenvisittoken12'), get_option('sitepulse_page_analyses')[0]['pending_tokens']);
        $this->assertSame('a1', get_transient('sitepulse_pa_token_visittokenvisittokenvisittoken12'));
    }
    public static function loopbackFailures(): array {
        return array(
            'connection refused' => array(new WP_Error('http_request_failed', 'cURL error 7: Failed to connect'), 'loopback', 'cURL error 7: Failed to connect'),
            'blocked by firewall' => array(Sitepulse_Test_WP::response('Forbidden', 403), 'http_error', 'HTTP 403'),
            'served from cache' => array(Sitepulse_Test_WP::response('cached html', 200), 'cached', 'page cache or CDN'),
        );
    }

    public function test_browser_visits_complete_a_pending_analysis(): void {
        update_option('sitepulse_page_analyses', array($this->analysis(array(), array('pending_tokens' => array('first', 'second')))));
        $writes = count(Sitepulse_Test_WP::$option_writes);
        $this->assertSame(array('ok' => true, 'collected' => 0, 'samples' => 0), Sitepulse_Page_Analysis::collect('a1'));
        $this->assertCount($writes, Sitepulse_Test_WP::$option_writes, 'Polling without new samples must not write the option.');
        set_transient('sitepulse_pa_sample_second', $this->sample(800)); set_transient('sitepulse_pa_token_second', 'a1');
        $this->assertSame(array('ok' => true, 'collected' => 1, 'samples' => 1), Sitepulse_Page_Analysis::collect('a1'));
        $stored = get_option('sitepulse_page_analyses')[0];
        $this->assertSame(array('first'), $stored['pending_tokens']); $this->assertNull($stored['samples'][0]['response_ms']); $this->assertFalse(get_transient('sitepulse_pa_token_second'));
    }

    public function test_finished_analysis_does_not_load_the_page_again(): void {
        update_option('sitepulse_page_analyses', array($this->analysis(array($this->sample(1), $this->sample(2), $this->sample(3)))));
        $this->assertSame(array('ok' => true, 'samples' => 3), Sitepulse_Page_Analysis::run_sample('a1')); $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }

    public function test_unknown_analyses_report_not_found_everywhere(): void {
        foreach (array('run_sample', 'collect', 'get_report') as $method) {
            $error = Sitepulse_Page_Analysis::$method('missing');
            $this->assertSame('sitepulse_analysis_not_found', $error->get_error_code()); $this->assertSame(array('status' => 404), $error->get_error_data());
        }
        $this->assertFalse(Sitepulse_Page_Analysis::delete('missing'));
    }

    public function test_malformed_storage_is_ignored(): void {
        update_option('sitepulse_page_analyses', 'corrupt'); $this->assertSame(array(), Sitepulse_Page_Analysis::get_recent());
        update_option('sitepulse_page_analyses', array('junk', array('id' => 'x'), array('id' => 'y', 'url' => 'u', 'created_at' => 1, 'samples' => 'no', 'pending_tokens' => array()), $this->analysis(array())));
        $this->assertSame(array('a1'), array_column(Sitepulse_Page_Analysis::get_recent(), 'id')); $this->assertFalse(Sitepulse_Page_Analysis::has_completed_analysis());
    }

    public function test_recent_list_summarizes_complete_and_unfinished_analyses(): void {
        update_option('sitepulse_page_analyses', array($this->analysis(array($this->sample(1600))), $this->analysis(array(), array('id' => 'a2', 'url' => 'https://example.test/cart/', 'created_at' => 1690000000))));
        $this->assertTrue(Sitepulse_Page_Analysis::has_completed_analysis());
        $this->assertSame(array(
            array('id' => 'a1', 'url' => 'https://example.test/product/blue-shirt/', 'title' => 'Blue Shirt – Shop', 'kind' => 'Product', 'server' => '1.60 s', 'level' => 'slow', 'created_at' => 1700000000),
            array('id' => 'a2', 'url' => 'https://example.test/cart/', 'title' => 'https://example.test/cart/', 'kind' => '', 'server' => '', 'level' => 'none', 'created_at' => 1690000000),
        ), Sitepulse_Page_Analysis::get_recent());
        $this->assertTrue(Sitepulse_Page_Analysis::delete('a1')); $this->assertSame(array('a2'), array_column(get_option('sitepulse_page_analyses'), 'id'));
    }

    public function test_suggestions_cover_home_blog_shop_and_recent_content_of_every_public_type(): void {
        update_option('page_for_posts', 7); $GLOBALS['sitepulse_test_shop_page'] = 9;
        Sitepulse_Test_WP::$post_types = array('post' => sitepulse_test_post_type('post', 'Post'), 'attachment' => sitepulse_test_post_type('attachment', 'Media'), 'product' => sitepulse_test_post_type('product', 'Product'));
        Sitepulse_Test_WP::$posts = array(11 => array('post_title' => 'Hello world'), 12 => array('post_title' => 'Blue Shirt'));
        Sitepulse_Test_WP::$posts_by_type = array('post' => array((object) array('ID' => 11), (object) array('ID' => 7)), 'product' => array((object) array('ID' => 12)), 'attachment' => array((object) array('ID' => 99)));
        $this->assertSame(array(
            array('label' => 'Homepage', 'url' => 'https://example.test/'), array('label' => 'Blog page', 'url' => 'https://example.test/?p=7'),
            array('label' => 'Shop', 'url' => 'https://example.test/?p=9'), array('label' => 'Post: Hello world', 'url' => 'https://example.test/?p=11'),
            array('label' => 'Product: Blue Shirt', 'url' => 'https://example.test/?p=12'),
        ), Sitepulse_Page_Analysis::get_suggestions());
        $this->assertSame(array('post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'orderby' => 'modified', 'order' => 'DESC', 'no_found_rows' => true), Sitepulse_Test_WP::$post_queries[0]);
        $this->assertNotContains('attachment', array_column(Sitepulse_Test_WP::$post_queries, 'post_type'));
    }

    public function test_suggestions_are_capped_and_skip_missing_permalinks(): void {
        Sitepulse_Test_WP::$post_types = array('page' => sitepulse_test_post_type('page', 'Page'), 'event' => sitepulse_test_post_type('event', 'Event'));
        $pages = array(); for ($i = 100; $i < 125; $i++) { $pages[] = (object) array('ID' => $i); }
        Sitepulse_Test_WP::$posts_by_type = array('page' => $pages, 'event' => array((object) array('ID' => 0)));
        add_filter('pre_option_page_for_posts', static function () { return 0; });
        $suggestions = Sitepulse_Page_Analysis::get_suggestions();
        $this->assertCount(20, $suggestions); $this->assertSame('Homepage', $suggestions[0]['label']);
    }

    public function test_incomplete_analysis_report_only_describes_progress(): void {
        $this->assertSame(array('id' => 'a1', 'url' => 'https://example.test/product/blue-shirt/', 'created_at' => 1700000000, 'samples' => 0, 'pending' => 1, 'complete' => false),
            Sitepulse_Page_Analysis::build_report($this->analysis(array('broken'), array('pending_tokens' => array('t')))));
    }

    public function test_report_ranks_plugins_and_theme_by_their_own_time_and_explains_the_cause(): void {
        $sources = array(
            array('source' => 'woocommerce', 'type' => 'plugin', 'self_ms' => 600.0, 'calls' => 40), array('source' => 'storefront', 'type' => 'theme', 'self_ms' => 260.0, 'calls' => 6),
            array('source' => '(unknown)', 'type' => 'core', 'self_ms' => 90.0, 'calls' => 20), array('source' => 'hello', 'type' => 'plugin', 'self_ms' => 0.4, 'calls' => 1),
        );
        $callbacks = array(array('hook' => 'wp_head', 'callback' => 'storefront_head', 'fileline' => 'wp-content/themes/storefront/inc/head.php:4', 'source' => 'storefront', 'type' => 'theme', 'calls' => 1, 'self_ms' => 40.0, 'total_ms' => 40.0),
            array('hook' => 'wp_loaded', 'callback' => 'WC_Cart->calculate', 'fileline' => 'wp-content/plugins/woocommerce/cart.php:10', 'source' => 'woocommerce', 'type' => 'plugin', 'calls' => 2, 'self_ms' => 300.0, 'total_ms' => 310.0));
        $http = array(array('url' => 'https://fonts.test/css', 'host' => 'fonts.test', 'code' => 200, 'origin' => 'Theme', 'elapsed_ms' => 20.0),
            array('url' => 'https://rates.test/v1?key=%2A%2A', 'host' => 'rates.test', 'code' => 200, 'origin' => 'Plugin: WooCommerce', 'elapsed_ms' => 1200.0));
        $report = Sitepulse_Page_Analysis::build_report($this->analysis(array(
            $this->sample(3000, array('sources' => $sources, 'callbacks' => $callbacks, 'http' => $http, 'queries' => 240, 'memory_peak' => 300 * MB_IN_BYTES)),
            $this->sample(1800, array('sources' => $sources, 'callbacks' => $callbacks, 'http' => array(array('url' => 'https://rates.test/v1?key=other', 'host' => 'rates.test', 'code' => 503, 'origin' => 'Plugin: WooCommerce', 'elapsed_ms' => 800.0)), 'queries' => 220, 'response_ms' => 1900.0)),
            $this->sample(1700, array('sources' => $sources, 'callbacks' => $callbacks, 'queries' => 230, 'response_ms' => 1750.0)),
        )));

        $this->assertTrue($report['complete']); $this->assertSame(3, $report['samples']); $this->assertSame(1800.0, $report['server_ms']); $this->assertSame(3000.0, $report['first_ms']);
        $this->assertSame(1825.0, $report['response_ms']); $this->assertSame(230, $report['queries']); $this->assertSame(300 * MB_IN_BYTES, $report['memory_peak']); $this->assertSame(200, $report['status']);
        $this->assertSame(array(
            array('name' => 'WooCommerce', 'slug' => 'woocommerce', 'type' => 'plugin', 'self_ms' => 600.0, 'share' => 33.3, 'calls' => 40, 'time' => '600 ms'),
            array('name' => 'Test Theme (theme)', 'slug' => 'storefront', 'type' => 'theme', 'self_ms' => 260.0, 'share' => 14.4, 'calls' => 6, 'time' => '260 ms'),
        ), $report['sources']);
        $this->assertSame(940.0, $report['core_ms']); $this->assertSame(52, $report['core_share']); $this->assertSame(673.3, $report['http_ms']);
        $this->assertSame(array('rates.test', 'fonts.test'), array_column($report['http'], 'host')); $this->assertSame(array('wp_loaded', 'wp_head'), array_column($report['callbacks'], 'hook'));
        $this->assertSame(array('url' => 'https://rates.test/v1?key=%2A%2A', 'host' => 'rates.test', 'origin' => 'Plugin: WooCommerce', 'code' => 503, 'calls' => 2, 'total_ms' => 2000.0, 'max_ms' => 1200.0, 'avg_ms' => 1000.0, 'per_load' => 0.7, 'time' => '1.00 s'), $report['http'][0]);
        $this->assertSame(array('hook' => 'wp_loaded', 'callback' => 'WC_Cart->calculate', 'fileline' => 'wp-content/plugins/woocommerce/cart.php:10', 'source' => 'woocommerce', 'type' => 'plugin', 'self_ms' => 300.0, 'time' => '300 ms'), $report['callbacks'][0]);
        $this->assertSame(array('server' => '1.80 s', 'first' => '3.00 s', 'response' => '1.83 s', 'core' => '940 ms', 'http' => '673 ms', 'memory' => '300 MB'), $report['display']);
        $this->assertSame('slow', $report['verdict']['level']); $this->assertSame('Slow', $report['verdict']['label']);
        $this->assertSame('The biggest share goes to WooCommerce.', $report['verdict']['detail']); $this->assertStringContainsString('1.80 s', $report['verdict']['summary']);

        $this->assertSame(array('danger', 'danger', 'danger', 'warning', 'info', 'info'), array_column($report['findings'], 'level'));
        $titles = array_column($report['findings'], 'title');
        $this->assertSame(array('2 external requests add 673 ms', '230 database queries', 'Peak memory of 300 MB', 'WooCommerce takes 600 ms', 'Test Theme (theme) takes 260 ms', 'The first load was slower'), $titles);
        $this->assertStringContainsString('most of it is WooCommerce waiting for rates.test to answer', $report['findings'][3]['text']); $this->assertStringContainsString('Your theme runs on every page', $report['findings'][4]['text']);
        $this->assertStringContainsString('rates.test and takes 1.20 s', $report['findings'][0]['text']);
    }

    public function test_plugin_finding_explains_when_its_time_is_spent_waiting_for_an_external_service(): void {
        $sample = $this->sample(1100.0, array(
            'sources' => array(array('source' => 'rate-fetcher', 'type' => 'plugin', 'self_ms' => 602.0, 'calls' => 1), array('source' => 'woocommerce', 'type' => 'plugin', 'self_ms' => 390.0, 'calls' => 9)),
            'http' => array(array('url' => 'https://rates.test/v1', 'host' => 'rates.test', 'code' => 200, 'origin' => 'Plugin: rate-fetcher', 'elapsed_ms' => 600.0), array('url' => 'https://api.woo.test/x', 'host' => 'api.woo.test', 'code' => 200, 'origin' => 'Plugin: woocommerce', 'elapsed_ms' => 40.0)),
        ));
        Sitepulse_Test_WP::$plugins['rate-fetcher/rate-fetcher.php'] = array('Name' => 'Rate Fetcher');
        $findings = Sitepulse_Page_Analysis::build_report($this->analysis(array($sample)))['findings'];
        $texts = array_column($findings, 'text', 'title');
        $this->assertStringContainsString('most of it is Rate Fetcher waiting for rates.test to answer', $texts['Rate Fetcher takes 602 ms']);
        $this->assertStringContainsString('If WooCommerce is not needed here', $texts['WooCommerce takes 390 ms'], 'A small request does not explain the plugin time.');
    }

    public function test_requests_name_the_plugin_behind_them_when_the_page_only_knew_its_folder(): void {
        $event = static function ($host, $origin) { return array('url' => 'https://' . $host . '/', 'host' => $host, 'code' => 200, 'origin' => $origin, 'elapsed_ms' => 10.0); };
        $report = Sitepulse_Page_Analysis::build_report($this->analysis(array($this->sample(200, array('http' => array(
            $event('a.test', 'Plugin: woocommerce'), $event('b.test', 'Plugin: removed-plugin'), $event('c.test', 'Plugin: nameless'), $event('d.test', 'Theme: Storefront'), $event('e.test', 'Stripe'),
        ))))));
        $this->assertSame(array('Plugin: WooCommerce', 'Plugin: removed-plugin', 'Plugin: nameless', 'Theme: Storefront', 'Stripe'), array_column($report['http'], 'origin'));
    }

    /** @dataProvider verdicts */
    public function test_verdict_and_findings_match_how_fast_the_page_is(array $sample, $level, $label, $detail, array $finding_titles): void {
        $report = Sitepulse_Page_Analysis::build_report($this->analysis(array($this->sample($sample['server_ms'], $sample))));
        $this->assertSame($level, $report['verdict']['level']); $this->assertSame($label, $report['verdict']['label']); $this->assertSame($detail, $report['verdict']['detail']);
        $this->assertSame($finding_titles, array_column($report['findings'], 'title'));
    }
    public static function verdicts(): array {
        $plugin = array(array('source' => 'woocommerce', 'type' => 'plugin', 'self_ms' => 150.0, 'calls' => 3));
        return array(
            'fast and clean' => array(array('server_ms' => 420.0), 'good', 'Fast', '', array('Nothing stands out')),
            'fast with a modest plugin and request' => array(array('server_ms' => 500.0, 'sources' => $plugin, 'http' => array(array('url' => 'https://a.test/x', 'host' => 'a.test', 'code' => 200, 'origin' => 'X', 'elapsed_ms' => 120.0))), 'good', 'Fast', '', array('WooCommerce takes 150 ms', '1 external request adds 120 ms')),
            'fair because of wordpress itself' => array(array('server_ms' => 900.0), 'fair', 'Could be faster', 'Most of it is spent in WordPress itself rather than in a single plugin.', array('Most time is spent in WordPress itself')),
            'fair with time spread thin' => array(array('server_ms' => 900.0, 'sources' => array(array('source' => 'a', 'type' => 'plugin', 'self_ms' => 300.0, 'calls' => 1), array('source' => 'b', 'type' => 'plugin', 'self_ms' => 80.0, 'calls' => 1), array('source' => 'c', 'type' => 'plugin', 'self_ms' => 90.0, 'calls' => 1), array('source' => 'd', 'type' => 'plugin', 'self_ms' => 90.0, 'calls' => 1))), 'fair', 'Could be faster', 'The biggest share goes to a.', array('a takes 300 ms')),
            'fair with no dominant cause' => array(array('server_ms' => 900.0, 'sources' => array_map(static function ($slug) { return array('source' => $slug, 'type' => 'plugin', 'self_ms' => 150.0, 'calls' => 1); }, array('a', 'b', 'c', 'd'))), 'fair', 'Could be faster', '', array('a takes 150 ms', 'b takes 150 ms')),
            'fair but spread across many small tasks' => array(array('server_ms' => 1000.0, 'sources' => array_map(static function ($i) { return array('source' => 'p' . $i, 'type' => 'plugin', 'self_ms' => 90.0, 'calls' => 1); }, range(1, 6))), 'fair', 'Could be faster', '', array('No single cause stands out')),
            'redirect measured instead of the page' => array(array('server_ms' => 40.0, 'status' => 301), 'good', 'Fast', '', array('This address redirects (HTTP 301)')),
            'not found page' => array(array('server_ms' => 300.0, 'status' => 404, 'page' => array('title' => '', 'kind' => 'Not found (404)')), 'good', 'Fast', '', array('This address returns an error (HTTP 404)')),
            'warning level queries memory and requests' => array(array('server_ms' => 450.0, 'queries' => 120, 'memory_peak' => 150 * MB_IN_BYTES, 'http' => array(array('url' => 'https://a.test/x', 'host' => 'a.test', 'code' => 200, 'origin' => 'X', 'elapsed_ms' => 400.0))), 'good', 'Fast', '', array('1 external request adds 400 ms', '120 database queries', 'Peak memory of 150 MB')),
        );
    }

    public function test_report_handles_zero_server_time_untitled_pages_and_tiny_memory(): void {
        $report = Sitepulse_Page_Analysis::build_report($this->analysis(array($this->sample(0.0, array('page' => array('title' => '', 'kind' => 'Page'), 'memory_peak' => 6 * MB_IN_BYTES,
            'sources' => array(array('source' => 'nameless', 'type' => 'plugin', 'self_ms' => 2.0, 'calls' => 1), array('source' => 'hello', 'type' => 'plugin', 'self_ms' => 1.0, 'calls' => 1)))))));
        $this->assertSame('https://example.test/product/blue-shirt/', $report['title']); $this->assertSame(0, $report['core_share']); $this->assertSame('6.0 MB', $report['display']['memory']);
        $this->assertSame(array(array('nameless', 0), array('Hello Dolly', 0)), array_map(static function ($source) { return array($source['name'], $source['share']); }, $report['sources']));
        $this->assertNull($report['response_ms']); $this->assertSame('', $report['display']['response']);
    }

    public function test_plugin_names_load_the_plugins_api_when_wordpress_has_not(): void {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/core-variants.php', array('mode' => 'analysis_plugin_names', 'exclude_functions' => array('get_plugins')));
        $this->assertSame(0, $result['status'], $result['error']); $this->assertSame('"Lazy Plugin"', $result['output']);
    }
}

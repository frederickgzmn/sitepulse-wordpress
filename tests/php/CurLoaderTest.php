<?php
final class CurLoaderTest extends Sitepulse_Test_Case {
    private function capture($url = 'https://api.test/task', $trace = array(), $age = 2, $response = null, $context = 'response'): void {
        Sitepulse_CurLoader::capture($response ?: Sitepulse_Test_WP::response(array(), 201), $context, 'Requests', array('_wshp_start' => microtime(true) - $age, '_wshp_trace' => $trace), $url);
    }
    public function test_enabled_requests_are_tagged_and_timeouts_only_fill_missing_values(): void {
        Sitepulse_CurLoader::init(); $args = apply_filters('http_request_args', array('method' => 'GET'), 'https://api.test'); $this->assertArrayHasKey('_wshp_start', $args); $this->assertSame(array(), $args['_wshp_trace']); $this->assertSame(15, $args['timeout']);
        $this->assertSame(3, apply_filters('http_request_args', array('timeout' => 3), 'https://api.test')['timeout']); $this->assertSame(15, apply_filters('http_request_timeout', 0)); $this->assertSame(8, apply_filters('http_request_timeout', 8));
    }
    public function test_disabled_tracking_neither_tags_requests_nor_captures_events(): void {
        update_option('sitepulse_curl_api_enabled', false); Sitepulse_CurLoader::init(); $this->assertSame(array('timeout' => 5), Sitepulse_CurLoader::tag_start_time(array('timeout' => 5), 'https://api.test')); $this->capture(); $this->assertSame(array(), Sitepulse_CurLoader::get_events());
    }
    public function test_fast_unstarted_and_nonresponse_callbacks_are_not_logged(): void {
        Sitepulse_CurLoader::init(); $this->capture('https://api.test', array(), .01); $this->capture('https://api.test', array(), 5, null, 'transport'); Sitepulse_CurLoader::capture(array(), 'response', 'Requests', array('timeout' => 15), 'https://api.test'); $this->assertSame(array(), Sitepulse_CurLoader::get_events());
    }
    public function test_slow_response_records_code_host_and_plugin_origin_skipping_core_frames(): void {
        Sitepulse_Test_WP::$plugins = array('demo/main.php' => array('Name' => 'Demo Service')); Sitepulse_CurLoader::init();
        $this->capture('https://api.test:8443/task', array(array('file' => ABSPATH . 'wp-includes/http.php', 'line' => 10), array('file' => WP_PLUGIN_DIR . '/demo/src/client.php', 'line' => 42)));
        $event = Sitepulse_CurLoader::get_events()[0]; $this->assertSame(201, $event['code']); $this->assertSame('api.test', $event['host']); $this->assertSame('Plugin: Demo Service', $event['origin']); $this->assertSame('wp-content/plugins/demo/src/client.php', $event['file']); $this->assertSame(42, $event['line']); $this->assertGreaterThanOrEqual(2, $event['elapsed']);
    }
    /** @dataProvider fallbackOrigins */
    public function test_unknown_stack_uses_known_service_url_without_matching_deceptive_domains($url, $expected): void {
        Sitepulse_CurLoader::init(); $this->capture($url); $this->assertSame($expected, Sitepulse_CurLoader::get_events()[0]['origin']);
    }
    public static function fallbackOrigins(): array { return array(array('https://api.wordpress.org/plugins/update-check', 'WordPress Core'), array('https://api.stripe.com/v1', 'Stripe'), array('https://connect.stripe.com/v1', 'Stripe'), array('https://www.google.com/recaptcha/api/siteverify', 'Google reCAPTCHA'), array('https://www.google.com/search', 'Unknown'), array('https://stripe.com.evil.test/v1', 'Unknown'), array('https://notstripe.com/v1', 'Unknown')); }
    public function test_error_response_is_recorded_with_zero_status_and_filter_extensions(): void {
        Sitepulse_CurLoader::init(); add_filter('sitepulse_curl_event_data', static function ($event, $response, $args, $url) { $event['failure'] = $response->get_error_code(); return $event; }, 10, 4);
        $this->capture('https://api.test', array(), 2, new WP_Error('timeout', 'Request timed out')); $event = Sitepulse_CurLoader::get_events()[0]; $this->assertSame(0, $event['code']); $this->assertSame('timeout', $event['failure']);
    }
    public function test_ring_buffer_keeps_only_two_hundred_latest_events(): void {
        update_option('sitepulse_curl_api_events', array_fill(0, 200, array('url' => 'old'))); Sitepulse_CurLoader::init(); $this->capture('https://api.test/new'); $events = Sitepulse_CurLoader::get_events(); $this->assertCount(200, $events); $this->assertSame('old', $events[0]['url']); $this->assertSame('https://api.test/new', $events[199]['url']);
        (new Sitepulse_CurLoader())->clear_events(); $this->assertSame(array(), Sitepulse_CurLoader::get_events());
    }
    public function test_page_tracking_enables_capture_and_stores_in_page_transient(): void {
        update_option('sitepulse_curl_api_enabled', false); update_option('sitepulse_pageloadhttp_curlstatus', true); update_option('sitepulse_current_tracked_pageid', 42); Sitepulse_Test_WP::$post_id = 42; Sitepulse_Test_WP::$posts[42] = array('post_status' => 'publish');
        Sitepulse_CurLoader::init(); $this->capture(); $this->assertCount(1, get_transient('sitepulse_single_page_42')); $this->assertSame(array(), Sitepulse_CurLoader::get_events());
    }
}

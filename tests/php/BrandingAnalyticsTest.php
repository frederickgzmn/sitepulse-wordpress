<?php
final class BrandingAnalyticsTest extends Sitepulse_Test_Case {
    public function test_whitelabel_sanitizes_branding_and_uses_it_only_when_enabled(): void {
        Sitepulse_Whitelabel_Service::store_whitelabel_data(array('enabled' => true, 'plugin_name' => '<b>Acme Monitor</b>', 'company_name' => '<i>Acme</i>', 'logo_url' => 'https://cdn.test/logo.png', 'accent_color' => '#abc', 'unexpected' => 'drop'));
        $this->assertTrue(Sitepulse_Whitelabel_Service::is_whitelabel_enabled()); $this->assertSame('Acme Monitor', Sitepulse_Whitelabel_Service::get_plugin_name()); $this->assertSame('Acme', Sitepulse_Whitelabel_Service::get_company_name());
        $this->assertSame('https://cdn.test/logo.png', Sitepulse_Whitelabel_Service::get_logo_url()); $this->assertSame('#abc', Sitepulse_Whitelabel_Service::get_accent_color()); $this->assertSame(Sitepulse_Test_WP::$now, Sitepulse_Whitelabel_Service::get_last_updated());
        $this->assertArrayNotHasKey('unexpected', get_option('sitepulse_whitelabel'));
        Sitepulse_Whitelabel_Service::clear_whitelabel_data(); $this->assertSame('SitePulse', Sitepulse_Whitelabel_Service::get_plugin_name()); $this->assertSame('', Sitepulse_Whitelabel_Service::get_accent_color()); $this->assertStringEndsWith('/sitepulse_34x34.svg', Sitepulse_Whitelabel_Service::get_logo_url('menu'));
    }
    /** @dataProvider invalidBranding */
    public function test_incomplete_or_disabled_branding_uses_default_name_and_logo($data): void {
        Sitepulse_Whitelabel_Service::store_whitelabel_data($data);
        $this->assertSame('SitePulse', Sitepulse_Whitelabel_Service::get_plugin_name()); $this->assertStringEndsWith('/only-logo.png', Sitepulse_Whitelabel_Service::get_logo_url());
    }
    public static function invalidBranding(): array { return array(array(array('enabled' => true)), array(array('plugin_name' => 'Acme', 'logo_url' => 'https://cdn.test/logo.png'))); }
    public function test_unsafe_branding_urls_and_colors_are_discarded(): void {
        Sitepulse_Whitelabel_Service::store_whitelabel_data(array('enabled' => true, 'plugin_name' => 'Acme', 'logo_url' => 'javascript:alert(1)', 'accent_color' => 'red;display:none'));
        $this->assertStringEndsWith('/only-logo.png', Sitepulse_Whitelabel_Service::get_logo_url()); $this->assertSame('', Sitepulse_Whitelabel_Service::get_accent_color());
    }
    public function test_analytics_reject_unknown_events_and_store_only_bounded_scalar_context(): void {
        $this->assertFalse(Sitepulse_Product_Analytics::track('private_event')); $this->assertFalse(get_option('sitepulse_product_events'));
        $this->assertTrue(Sitepulse_Product_Analytics::track('onboarding_viewed', array('Step' => 2, 'ok' => true, 'text' => '<b>Hello</b>', 'nested' => array('secret'), 'object' => (object) array('secret' => 1), '!!!' => 'bad', 'long' => str_repeat('a', 300))));
        $event = get_option('sitepulse_product_events')[0];
        $this->assertSame('onboarding_viewed', $event['event']); $this->assertSame(array('step' => 2, 'ok' => true, 'text' => 'Hello', 'long' => str_repeat('a', 200)), $event['context']); $this->assertSame(array(), Sitepulse_Test_WP::$http_requests);
    }
    public function test_analytics_ring_buffer_keeps_fifty_most_recent_events_and_recovers_corrupt_storage(): void {
        update_option('sitepulse_product_events', 'corrupt');
        for ($i = 0; $i < 55; $i++) { Sitepulse_Product_Analytics::track('dashboard_viewed', array('index' => $i)); }
        $events = get_option('sitepulse_product_events'); $this->assertCount(50, $events); $this->assertSame(5, $events[0]['context']['index']); $this->assertSame(54, $events[49]['context']['index']);
    }
    public function test_track_once_deduplicates_per_event_without_blocking_other_events(): void {
        $this->assertTrue(Sitepulse_Product_Analytics::track_once('plugin_activated')); $this->assertFalse(Sitepulse_Product_Analytics::track_once('plugin_activated')); $this->assertTrue(Sitepulse_Product_Analytics::track_once('onboarding_completed'));
        $this->assertFalse(Sitepulse_Product_Analytics::track_once('unknown')); $this->assertCount(2, get_option('sitepulse_product_events')); $this->assertArrayNotHasKey('unknown', get_option('sitepulse_product_events_once'));
    }
    public function test_summary_ignores_malformed_unknown_events_and_finds_chronological_boundaries(): void {
        update_option('sitepulse_product_events', array(array('event' => 'dashboard_viewed', 'timestamp' => 300), 'bad', array('event' => 'unknown', 'timestamp' => 999), array('event' => 'onboarding_completed', 'timestamp' => 100), array('event' => 'first_useful_finding', 'timestamp' => 200), array('event' => 'dashboard_viewed', 'timestamp' => 250)));
        $this->assertSame(array('event_count' => 4, 'counts' => array('dashboard_viewed' => 2, 'onboarding_completed' => 1, 'first_useful_finding' => 1), 'first_event_timestamp' => 100, 'last_event_timestamp' => 300, 'last_event' => 'dashboard_viewed', 'onboarding_completed' => true, 'useful_finding_seen' => true), Sitepulse_Product_Analytics::get_summary());
    }
}

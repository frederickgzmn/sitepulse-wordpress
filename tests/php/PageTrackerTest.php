<?php
final class PageTrackerTest extends Sitepulse_Test_Case {
    public function test_tracking_requires_matching_current_page_and_independent_feature_flags(): void {
        update_option('sitepulse_current_tracked_pageid', 42); Sitepulse_Test_WP::$post_id = 42;
        $this->assertFalse(Sitepulse_Page_Tracker::is_load_tracking_active());
        update_option('sitepulse_pageloadhttp_loadstatus', true); $this->assertTrue(Sitepulse_Page_Tracker::is_load_tracking_active()); $this->assertFalse(Sitepulse_Page_Tracker::is_curl_tracking_active());
        update_option('sitepulse_pageloadhttp_curlstatus', true); $this->assertTrue(Sitepulse_Page_Tracker::is_curl_tracking_active());
        Sitepulse_Test_WP::$post_id = 43; $this->assertFalse(Sitepulse_Page_Tracker::is_load_tracking_active()); $this->assertFalse(Sitepulse_Page_Tracker::is_curl_tracking_active());
    }
    /** @dataProvider invalidStorage */
    public function test_storage_rejects_disabled_missing_nonnumeric_and_deleted_pages($status, $id, $exists): void {
        update_option('sitepulse_pageloadhttp_loadstatus', $status); update_option('sitepulse_current_tracked_pageid', $id);
        if ($exists) { Sitepulse_Test_WP::$posts[$id] = array('post_status' => 'publish'); }
        $this->assertFalse(Sitepulse_Page_Tracker::save_single_page_events(array('event'))); $this->assertSame(array(), Sitepulse_Test_WP::$transients);
    }
    public static function invalidStorage(): array { return array(array(false, 42, true), array(true, 0, false), array(true, 'invalid', false), array(true, 999, false)); }
    /** @dataProvider storageTypes */
    public function test_events_are_partitioned_by_type_and_page_with_deduplicated_index_and_day_expiry($type, $option, $prefix, $list): void {
        update_option($option, true); update_option('sitepulse_current_tracked_pageid', 42); Sitepulse_Test_WP::$posts[42] = array('post_status' => 'publish');
        $this->assertTrue(Sitepulse_Page_Tracker::save_single_page_events(array(array('elapsed' => 12)), $type));
        $this->assertTrue(Sitepulse_Page_Tracker::save_single_page_events(array(array('elapsed' => 15)), $type));
        $this->assertSame(array(array('elapsed' => 15)), Sitepulse_Page_Tracker::get_single_page_events(42, $type));
        $this->assertSame(array($prefix . '42'), get_transient($list)); $this->assertSame(86400, Sitepulse_Test_WP::$transient_expirations[$prefix . '42']);
        $this->assertFalse(Sitepulse_Page_Tracker::get_single_page_events(43, $type));
    }
    public static function storageTypes(): array { return array(array('load', 'sitepulse_pageloadhttp_loadstatus', 'sitepulse_load_single_page_', 'sitepulse_load_single_page_list'), array('curl', 'sitepulse_pageloadhttp_curlstatus', 'sitepulse_single_page_', 'sitepulse_single_page_list')); }
}

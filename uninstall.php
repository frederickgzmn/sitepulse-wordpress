<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
    exit;
}

// Remove all plugin options
delete_option('sitepulse_version');
delete_option('sitepulse_fields');
delete_option('sitepulse_settings');
delete_option('sitepulse_profiler_enabled');
delete_option('sitepulse_profiler_stats');
delete_option('sitepulse_profiler_current_time');
delete_option('sitepulse_curl_api_enabled');
delete_option('sitepulse_curl_api_events');
delete_option('sitepulse_onboarding_completed');
delete_option('sitepulse_onboarding_dismissed');
delete_option('sitepulse_onboarding_current_step');
delete_option('sitepulse_plugins_profiler_enabled');
delete_option('sitepulse_dark_mode_active');
delete_option('sitepulse_report_mode_active');
delete_option('sitepulse_pageloadhttp_curlstatus');
delete_option('sitepulse_pageloadhttp_loadstatus');
delete_option('sitepulse_current_tracked_pageid');
delete_option('sitepulse_product_events');
delete_option('sitepulse_product_events_once');

// Remove all user meta
delete_metadata( 'user', 0, 'sitepulse_trackers_disabled_notice_dismissed', '', true );
delete_metadata( 'user', 0, 'sitepulse_onboarding_notice_dismissed', '', true );

// Remove transients
delete_transient('sitepulse_activation_redirect');


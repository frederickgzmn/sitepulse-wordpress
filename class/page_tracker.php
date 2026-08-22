<?php
/**
 * SitePulse Page Tracker Class
 * Handles single page tracking logic shared across profiler and curl loader
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Page_Tracker
 * Manages single page tracking state and transient storage
 */
class Sitepulse_Page_Tracker {

	/**
	 * Option key for load status
	 */
	const LOAD_STATUS_KEY = 'sitepulse_pageloadhttp_loadstatus';

	/**
	 * Option key for curl status
	 */
	const CURL_STATUS_KEY = 'sitepulse_pageloadhttp_curlstatus';

	/**
	 * Option key for current tracked page ID
	 */
	const TRACKED_PAGE_KEY = 'sitepulse_current_tracked_pageid';

	/**
	 * Transient prefix for load events
	 */
	const LOAD_TRANSIENT_PREFIX = 'sitepulse_load_single_page_';

	/**
	 * Transient prefix for curl events
	 */
	const CURL_TRANSIENT_PREFIX = 'sitepulse_single_page_';

	/**
	 * Transient key for load page list
	 */
	const LOAD_PAGE_LIST_KEY = 'sitepulse_load_single_page_list';

	/**
	 * Transient key for curl page list
	 */
	const CURL_PAGE_LIST_KEY = 'sitepulse_single_page_list';

	/**
	 * Check if load tracking is enabled for current page
	 *
	 * @return bool True if load tracking is active for current page
	 */
	public static function is_load_tracking_active() {
		$loadstatus = get_option( self::LOAD_STATUS_KEY );
		$page_id = get_option( self::TRACKED_PAGE_KEY );

		if ( $loadstatus && $page_id ) {
			// Check if the page_id matches the current page
			if ( $page_id === get_the_ID() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if curl tracking is enabled for current page
	 *
	 * @return bool True if curl tracking is active for current page
	 */
	public static function is_curl_tracking_active() {
		$curlstatus = get_option( self::CURL_STATUS_KEY );
		$page_id = get_option( self::TRACKED_PAGE_KEY );

		if ( $curlstatus && $page_id ) {
			// Check if the page_id matches the current page
			if ( $page_id === get_the_ID() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the currently tracked page ID
	 *
	 * @return int|null Page ID or null if not set
	 */
	public static function get_tracked_page_id() {
		$page_id = get_option( self::TRACKED_PAGE_KEY );
		return $page_id ? (int) $page_id : null;
	}

	/**
	 * Get load tracking status
	 *
	 * @return bool Load tracking enabled
	 */
	public static function get_load_status() {
		return (bool) get_option( self::LOAD_STATUS_KEY );
	}

	/**
	 * Get curl tracking status
	 *
	 * @return bool Curl tracking enabled
	 */
	public static function get_curl_status() {
		return (bool) get_option( self::CURL_STATUS_KEY );
	}

	/**
	 * Check if single page tracking should be used for storing data
	 *
	 * @param string $type Type of tracking: 'load' or 'curl'
	 * @return array|false Array with page_id and transient_key if active, false otherwise
	 */
	public static function should_use_single_page_storage( $type = 'load' ) {
		$status_key = $type === 'curl' ? self::CURL_STATUS_KEY : self::LOAD_STATUS_KEY;
		$status = get_option( $status_key );
		$page_id = get_option( self::TRACKED_PAGE_KEY );

		if ( ! $status || empty( $page_id ) ) {
			return false;
		}

		$safe_page_id = sanitize_text_field( wp_unslash( $page_id ) );

		if ( empty( $safe_page_id ) || ! is_numeric( $safe_page_id ) ) {
			return false;
		}

		// Check if the page or post exists
		if ( ! get_post_status( $safe_page_id ) ) {
			return false;
		}

		$prefix = $type === 'curl' ? self::CURL_TRANSIENT_PREFIX : self::LOAD_TRANSIENT_PREFIX;

		return array(
			'page_id'       => $safe_page_id,
			'transient_key' => $prefix . $safe_page_id,
		);
	}

	/**
	 * Save events to single page transient and update page list
	 *
	 * @param array  $events Events to save
	 * @param string $type   Type of tracking: 'load' or 'curl'
	 * @return bool True on success
	 */
	public static function save_single_page_events( $events, $type = 'load' ) {
		$storage = self::should_use_single_page_storage( $type );

		if ( ! $storage ) {
			return false;
		}

		$transient_key = $storage['transient_key'];
		$list_key = $type === 'curl' ? self::CURL_PAGE_LIST_KEY : self::LOAD_PAGE_LIST_KEY;

		// Save events to transient
		set_transient( $transient_key, $events, DAY_IN_SECONDS );

		// Update page list
		$page_list = get_transient( $list_key );
		$new_list = [];

		if ( $page_list && is_array( $page_list ) ) {
			$new_list = $page_list;
		}

		$new_list[] = $transient_key;
		$new_list = array_unique( $new_list );

		set_transient( $list_key, $new_list, false );

		return true;
	}

	/**
	 * Get single page events from transient
	 *
	 * @param int    $page_id Page ID
	 * @param string $type    Type of tracking: 'load' or 'curl'
	 * @return array|false Events array or false if not found
	 */
	public static function get_single_page_events( $page_id, $type = 'load' ) {
		$prefix = $type === 'curl' ? self::CURL_TRANSIENT_PREFIX : self::LOAD_TRANSIENT_PREFIX;
		$transient_key = $prefix . $page_id;

		$events = get_transient( $transient_key );

		return $events ? $events : false;
	}
}

<?php
/**
 * SitePulse remote management health checks
 *
 * Loads the site's own pages before and after an update, with WordPress's fatal-error
 * check (the "scrape" request its plugin and theme editors use), and compares them.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Remote_Health
 */
class Sitepulse_Remote_Health {

	/** Seconds to wait for each page. */
	const TIMEOUT = 30;

	/**
	 * Load the pages a broken update would break.
	 *
	 * @return array Per page URL: status (0 when it did not answer), fatal (the PHP fatal error, or null)
	 *               and checked (whether WordPress's error check answered).
	 */
	public static function check() {
		$pages = array();
		foreach ( self::pages() as $url ) {
			$pages[ $url ] = self::load( $url );
		}

		return $pages;
	}

	/**
	 * Problems of pages that were fine before.
	 *
	 * @param array $before Pages of the baseline check.
	 * @param array $after  Pages of the check after the update.
	 * @return array Problems; empty when the site is as healthy as before.
	 */
	public static function problems( array $before, array $after ) {
		$problems = array();
		foreach ( $after as $url => $page ) {
			$was = $before[ $url ] ?? array(
				'status'  => 200,
				'fatal'   => null,
				'checked' => false,
			);
			if ( null !== $page['fatal'] ) {
				$problems[] = $url . ': ' . $page['fatal'];
			} elseif ( $page['status'] >= 500 && $was['status'] < 500 ) {
				$problems[] = $url . ': HTTP ' . $page['status'];
			} elseif ( 0 === $page['status'] && 0 !== $was['status'] ) {
				$problems[] = $url . ': no answer';
			} elseif ( ! $page['checked'] && $was['checked'] ) {
				// The page stopped before WordPress could report how it ended, as a PHP fatal error does.
				$problems[] = $url . ': stopped without finishing';
			}
		}

		return $problems;
	}

	/**
	 * Problems that make a baseline unusable: a failing page, or a site that cannot load its own home page.
	 *
	 * @param array $pages Pages of the baseline check.
	 * @return string|null site_unhealthy, health_check_unavailable or null.
	 */
	public static function baseline_error( array $pages ) {
		if ( 0 === $pages[ home_url( '/' ) ]['status'] ) {
			return 'health_check_unavailable';
		}

		return self::problems( array(), $pages ) ? 'site_unhealthy' : null;
	}

	/**
	 * Fatal errors SitePulse's error handler recorded in some files since a moment.
	 *
	 * @param string $path  Directory or file of the updated code.
	 * @param int    $since Local time (as SitePulse's error log stores it) the update started.
	 * @return array Messages of those errors.
	 */
	public static function logged_fatal_errors( $path, $since ) {
		$path  = wp_normalize_path( $path );
		$found = array();
		foreach ( self::error_log_entries() as $error ) {
			if ( ! empty( $error['is_fatal'] ) && (int) ( $error['timestamp'] ?? 0 ) >= $since && 0 === strpos( wp_normalize_path( (string) ( $error['file'] ?? '' ) ), $path ) ) {
				$found[] = self::first_line( (string) ( $error['message'] ?? '' ) );
			}
		}

		return $found;
	}

	/**
	 * The entries of SitePulse's error log, oldest first.
	 *
	 * @return array Entries that are arrays; their timestamp is local time.
	 */
	public static function error_log_entries() {
		global $wpdb;
		// Other requests logged these errors, after this request may have cached the log: read it from the database.
		$log = maybe_unserialize( (string) $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'sitepulse_error_log' ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The cached copy is what must be skipped.

		return is_array( $log ) && is_array( $log['errors'] ?? null ) ? array_values( array_filter( $log['errors'], 'is_array' ) ) : array();
	}

	/**
	 * @param string $message A PHP error message.
	 * @return string Its first line (an uncaught exception's message goes on with its stack trace),
	 *                with files named from the WordPress folder, as WordPress's own error check names them.
	 */
	public static function first_line( $message ) {
		$line = strstr( $message, "\n", true );
		return str_replace( array_unique( array( ABSPATH, wp_normalize_path( ABSPATH ) ) ), '', false === $line ? $message : $line );
	}

	/**
	 * @return array The home page, wp-admin and, with WooCommerce, the cart and checkout.
	 */
	private static function pages() {
		$pages = array( home_url( '/' ), admin_url() );
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$pages[] = wc_get_page_permalink( 'cart' );
			$pages[] = wc_get_page_permalink( 'checkout' );
		}

		return array_values( array_unique( array_filter( $pages ) ) );
	}

	/**
	 * Load a page the way WordPress checks an edited plugin for fatal errors: the page
	 * reports any PHP fatal error between two markers.
	 *
	 * @param string $url Page URL.
	 * @return array status and fatal.
	 */
	private static function load( $url ) {
		$key   = md5( wp_generate_password( 20, false ) );
		$nonce = (string) wp_rand();
		set_transient( 'scrape_key_' . $key, $nonce, 2 * self::TIMEOUT );

		$response = wp_remote_get(
			add_query_arg(
				array(
					'wp_scrape_key'   => $key,
					'wp_scrape_nonce' => $nonce,
				),
				$url
			),
			array(
				'timeout'     => self::TIMEOUT,
				'redirection' => 0,
				'headers'     => array( 'Cache-Control' => 'no-cache' ),
				'sslverify'   => apply_filters( 'https_local_ssl_verify', false, $url ),
			)
		);
		delete_transient( 'scrape_key_' . $key );

		if ( is_wp_error( $response ) ) {
			return array(
				'status'  => 0,
				'fatal'   => null,
				'checked' => false,
			);
		}

		$body   = (string) wp_remote_retrieve_body( $response );
		$start  = "###### wp_scraping_result_start:$key ######";
		$end    = "###### wp_scraping_result_end:$key ######";
		$at     = strpos( $body, $start );
		$result = false === $at ? true : json_decode( trim( (string) strstr( substr( $body, $at + strlen( $start ) ), $end, true ) ), true );

		// A PHP error has a type; WordPress reports a failed key check without one.
		return array(
			'status'  => (int) wp_remote_retrieve_response_code( $response ),
			'fatal'   => is_array( $result ) && isset( $result['type'] ) ? self::first_line( (string) ( $result['message'] ?? '' ) ) : null,
			'checked' => false !== $at,
		);
	}
}

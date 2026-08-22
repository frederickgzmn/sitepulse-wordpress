<?php
/**
 * SitePulse Utilities Class
 * Shared utility functions used across the plugin
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Utils
 * Provides common utility functions for the SitePulse plugin
 */
class Sitepulse_Utils {

	/**
	 * Format time with appropriate units (ms for < 1s, s for >= 1s)
	 * Handles both seconds and milliseconds input
	 *
	 * @param float  $time  Time value
	 * @param string $unit  Input unit: 'seconds' or 'ms' (default: 'seconds')
	 * @return string Formatted time string (e.g., "125.50 ms" or "2.35 s")
	 */
	public static function format_time( $time, $unit = 'seconds' ) {
		// Convert to milliseconds for consistent handling
		if ( $unit === 'seconds' ) {
			$ms = (float) $time * 1000;
		} else {
			$ms = (float) $time;
		}

		// Validate input
		if ( $ms < 0 ) {
			return '0 ms';
		}

		// Cap at 60 seconds (60000ms) - anything larger is likely an error
		if ( $ms > 60000 ) {
			return '60.00 s';
		}

		// Format as seconds if >= 1000ms
		if ( $ms >= 1000 ) {
			$seconds = $ms / 1000;
			return number_format( $seconds, 2, '.', '' ) . ' s';
		}

		// Format as milliseconds with 2 decimal places
		return number_format( $ms, 2, '.', '' ) . ' ms';
	}

	/**
	 * Convert an absolute path to a path relative to ABSPATH
	 *
	 * @param string $path Absolute file path
	 * @return string Relative path or original path if not under ABSPATH
	 */
	public static function shortpath( $path ) {
		if ( ! $path ) {
			return '(unknown)';
		}

		$abspath = wp_normalize_path( ABSPATH );
		$p = wp_normalize_path( $path );

		if ( strpos( $p, $abspath ) === 0 ) {
			return ltrim( substr( $p, strlen( $abspath ) ), '/' );
		}

		return $p;
	}

	/**
	 * Format memory size for display
	 *
	 * @param int $bytes Memory in bytes
	 * @return string Formatted memory string (e.g., "125.50 MB" or "2.35 KB")
	 */
	public static function format_memory( $bytes ) {
		$bytes = (int) $bytes;

		if ( $bytes >= 1048576 ) {
			return number_format( $bytes / 1048576, 2 ) . ' MB';
		} elseif ( $bytes >= 1024 ) {
			return number_format( $bytes / 1024, 2 ) . ' KB';
		}

		return $bytes . ' B';
	}

	/**
	 * Get active plugins count (cached for current request)
	 *
	 * @return int Number of active plugins
	 */
	public static function get_active_plugins_count() {
		static $count = null;

		if ( $count === null ) {
			$active_plugins = get_option( 'active_plugins', [] );
			$count = is_array( $active_plugins ) ? count( $active_plugins ) : 0;
		}

		return $count;
	}

	/**
	 * Get active plugins array (cached for current request)
	 *
	 * @return array Active plugins
	 */
	public static function get_active_plugins() {
		static $plugins = null;

		if ( $plugins === null ) {
			$plugins = get_option( 'active_plugins', [] );
			if ( ! is_array( $plugins ) ) {
				$plugins = [];
			}
		}

		return $plugins;
	}

	/**
	 * Redact sensitive query parameters from a URL
	 *
	 * @param string $url The URL to redact
	 * @return string The redacted URL
	 */
	public static function redact_url_query( $url ) {
		if ( empty( $url ) || ! is_string( $url ) ) {
			return $url;
		}

		$parsed = wp_parse_url( $url );

		// `wp_parse_url()` can return false for malformed URLs.
		if ( false === $parsed || ! is_array( $parsed ) ) {
			return $url;
		}
		if ( empty( $parsed['query'] ) ) {
			return $url;
		}

		// Keys to redact
		$sensitive_keys = [
			'api_key',
			'key',
			'token',
			'access_token',
			'signature',
			'secret',
			'auth',
			'nonce',
			'email',
		];

		parse_str( $parsed['query'], $query_params );

		$redacted = false;
		foreach ( $query_params as $key => $value ) {
			if ( in_array( strtolower( $key ), $sensitive_keys, true ) ) {
				$query_params[ $key ] = '********';
				$redacted             = true;
			}
		}

		if ( ! $redacted ) {
			return $url;
		}

		// Rebuild the query string
		$new_query = http_build_query( $query_params );

		// Rebuild the URL
		$scheme   = ! empty( $parsed['scheme'] ) ? $parsed['scheme'] . '://' : '';
		$host     = ! empty( $parsed['host'] ) ? $parsed['host'] : '';
		$port     = ! empty( $parsed['port'] ) ? ':' . $parsed['port'] : '';
		$path     = ! empty( $parsed['path'] ) ? $parsed['path'] : '';
		$fragment = ! empty( $parsed['fragment'] ) ? '#' . $parsed['fragment'] : '';

		return $scheme . $host . $port . $path . '?' . $new_query . $fragment;
	}
}

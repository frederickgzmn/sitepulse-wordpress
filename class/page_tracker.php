<?php
/**
 * SitePulse Page Tracker Class
 * Profiles one front-end request on behalf of a Page Analysis run.
 *
 * Page Analysis loads a page with a one-time token in its query string. When
 * that request reaches WordPress, the tracker enables full profiling for that
 * request only and stores what it measured as a sample the admin side reads.
 * Any front-end URL can be analyzed: pages, posts, products, archives, search.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Page_Tracker
 * Detects analysis requests and records a compact performance sample for them.
 */
class Sitepulse_Page_Tracker {

	/**
	 * Query argument that carries the one-time analysis token.
	 */
	const QUERY_VAR = 'sitepulse_analyze';

	/**
	 * Transient prefix for issued tokens (value: analysis ID)
	 */
	const TOKEN_TRANSIENT_PREFIX = 'sitepulse_pa_token_';

	/**
	 * Transient prefix for samples stored by an analysis request
	 */
	const SAMPLE_TRANSIENT_PREFIX = 'sitepulse_pa_sample_';

	/**
	 * Token length, in lowercase alphanumeric characters
	 */
	const TOKEN_LENGTH = 32;

	/**
	 * Upper bounds that keep stored samples small
	 */
	const MAX_HTTP_EVENTS = 30;
	const MAX_SOURCES     = 25;
	const MAX_CALLBACKS   = 15;

	/**
	 * Validated token of the current request, or null when it is not an analysis request
	 *
	 * @var string|null
	 */
	private static $token = null;

	/**
	 * Request start time in seconds
	 *
	 * @var float
	 */
	private static $started_at = 0.0;

	/**
	 * Outgoing HTTP requests made while building the analyzed page
	 *
	 * @var array
	 */
	private static $http_events = array();

	/**
	 * Detect an analysis request. Must run before the profilers initialize.
	 */
	public static function init() {
		$token = self::token_from_request();

		if ( '' === $token || false === get_transient( self::TOKEN_TRANSIENT_PREFIX . $token ) ) {
			return;
		}

		self::$token       = $token;
		self::$started_at  = isset( $_SERVER['REQUEST_TIME_FLOAT'] ) ? (float) $_SERVER['REQUEST_TIME_FLOAT'] : microtime( true );
		self::$http_events = array();

		// Keep page caches from storing the profiled response.
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		add_action( 'shutdown', array( __CLASS__, 'store_sample' ), PHP_INT_MAX );
	}

	/**
	 * Read a well-formed token from the query string.
	 *
	 * @return string Token, or an empty string when absent or malformed
	 */
	public static function token_from_request() {
		if ( ! isset( $_GET[ self::QUERY_VAR ] ) || ! is_string( $_GET[ self::QUERY_VAR ] ) ) {
			return '';
		}

		$token = sanitize_key( wp_unslash( $_GET[ self::QUERY_VAR ] ) );

		return strlen( $token ) === self::TOKEN_LENGTH ? $token : '';
	}

	/**
	 * Whether the current request is being profiled for a Page Analysis run
	 *
	 * @return bool
	 */
	public static function is_analysis_request() {
		return null !== self::$token;
	}

	/**
	 * Record one outgoing HTTP request made by the analyzed page.
	 *
	 * @param array $event Request details from the HTTP tracker
	 */
	public static function record_http_event( array $event ) {
		if ( self::is_analysis_request() && count( self::$http_events ) < self::MAX_HTTP_EVENTS ) {
			self::$http_events[] = $event;
		}
	}

	/**
	 * Store this request's measurements for the admin side to collect.
	 */
	public static function store_sample() {
		if ( ! self::is_analysis_request() ) {
			return;
		}

		$stats = class_exists( 'Sitepulse_Profiler' ) ? Sitepulse_Profiler::get_stats() : array();

		$sample = array(
			'server_ms'   => round( ( microtime( true ) - self::$started_at ) * 1000, 1 ),
			'memory_peak' => memory_get_peak_usage( true ),
			'queries'     => function_exists( 'get_num_queries' ) ? (int) get_num_queries() : 0,
			'status'      => (int) http_response_code() ?: 200,
			'page'        => self::describe_page(),
			'sources'     => self::summarize_sources( $stats ),
			'callbacks'   => self::top_callbacks( $stats ),
			'http'        => self::$http_events,
			'captured_at' => time(),
		);

		set_transient( self::SAMPLE_TRANSIENT_PREFIX . self::$token, $sample, 15 * MINUTE_IN_SECONDS );
	}

	/**
	 * Describe the page WordPress rendered, in words a site owner recognizes.
	 *
	 * @return array Title and kind of page
	 */
	private static function describe_page() {
		$title = function_exists( 'wp_get_document_title' ) ? wp_strip_all_tags( wp_get_document_title() ) : '';

		return array(
			'title' => html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ),
			'kind'  => self::page_kind(),
		);
	}

	/**
	 * Name the kind of page that was rendered.
	 *
	 * @return string
	 */
	private static function page_kind() {
		if ( is_404() ) {
			return __( 'Not found (404)', 'sitepulse' );
		}
		if ( is_front_page() ) {
			return __( 'Front page', 'sitepulse' );
		}
		if ( is_home() ) {
			return __( 'Blog page', 'sitepulse' );
		}
		if ( is_singular() ) {
			$post_type = get_post_type_object( (string) get_post_type() );
			return $post_type ? $post_type->labels->singular_name : __( 'Single page', 'sitepulse' );
		}
		if ( is_search() ) {
			return __( 'Search results', 'sitepulse' );
		}
		if ( is_archive() ) {
			return __( 'Archive', 'sitepulse' );
		}

		return __( 'Page', 'sitepulse' );
	}

	/**
	 * Total each plugin's and theme's own time for this request.
	 *
	 * Uses exclusive (self) time so nested callbacks are not counted twice.
	 *
	 * @param array $stats Profiler stats for this request
	 * @return array Sources, slowest first
	 */
	private static function summarize_sources( array $stats ) {
		$sources = array();

		foreach ( $stats as $stat ) {
			$type = self::source_type( $stat );
			if ( null === $type ) {
				continue;
			}

			$key = $type . ':' . $stat['source'];
			if ( ! isset( $sources[ $key ] ) ) {
				$sources[ $key ] = array(
					'source'  => (string) $stat['source'],
					'type'    => $type,
					'self_ms' => 0.0,
					'calls'   => 0,
				);
			}

			$sources[ $key ]['self_ms'] += self::self_ms( $stat );
			$sources[ $key ]['calls']   += (int) ( $stat['calls'] ?? 0 );
		}

		$sources = array_values( $sources );
		usort( $sources, static function ( $a, $b ) {
			return $b['self_ms'] <=> $a['self_ms'];
		} );

		return array_map( static function ( $source ) {
			$source['self_ms'] = round( $source['self_ms'], 2 );
			return $source;
		}, array_slice( $sources, 0, self::MAX_SOURCES ) );
	}

	/**
	 * Pick the callbacks that cost this request the most time.
	 *
	 * @param array $stats Profiler stats for this request
	 * @return array Callbacks, slowest first
	 */
	private static function top_callbacks( array $stats ) {
		$callbacks = array();

		foreach ( $stats as $stat ) {
			$type = self::source_type( $stat );
			if ( null === $type ) {
				continue;
			}

			$callbacks[] = array(
				'hook'     => (string) ( $stat['hook'] ?? '' ),
				'callback' => (string) ( $stat['sig'] ?? '' ),
				'fileline' => (string) ( $stat['fileline'] ?? '' ),
				'source'   => (string) $stat['source'],
				'type'     => $type,
				'calls'    => (int) ( $stat['calls'] ?? 0 ),
				'self_ms'  => round( self::self_ms( $stat ), 2 ),
				'total_ms' => round( (float) ( $stat['total_ms'] ?? 0 ), 2 ),
			);
		}

		usort( $callbacks, static function ( $a, $b ) {
			return $b['self_ms'] <=> $a['self_ms'];
		} );

		return array_slice( $callbacks, 0, self::MAX_CALLBACKS );
	}

	/**
	 * Classify a stat as plugin, theme or core code; SitePulse's own work is skipped.
	 *
	 * @param array $stat Profiler stat
	 * @return string|null
	 */
	private static function source_type( array $stat ) {
		$fileline = (string) ( $stat['fileline'] ?? '' );
		$source   = (string) ( $stat['source'] ?? '' );

		if ( 0 === strpos( $fileline, 'wp-content/plugins/' ) ) {
			return $source === basename( dirname( SITEPULSE_PLUGIN_FILE ) ) ? null : 'plugin';
		}
		if ( 0 === strpos( $fileline, 'wp-content/themes/' ) ) {
			return 'theme';
		}

		return 'core';
	}

	/**
	 * Exclusive time of a stat in milliseconds, falling back to inclusive time.
	 *
	 * @param array $stat Profiler stat
	 * @return float
	 */
	private static function self_ms( array $stat ) {
		return (float) ( $stat['self_total_ms'] ?? $stat['total_ms'] ?? 0 );
	}
}

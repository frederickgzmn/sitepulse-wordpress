<?php
/**
 * SitePulse Page Analysis Class
 * Finds out what makes one specific page slow.
 *
 * An analysis loads any front-end URL of this site a few times as a logged-out
 * visitor, through a loopback request carrying a one-time token. The request
 * profiles itself (see Sitepulse_Page_Tracker) and this class turns the stored
 * samples into a plain-English report. When the server cannot request itself,
 * the person running the analysis opens the page in a browser tab instead and
 * that visit is measured.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Page_Analysis
 * Starts analyses, collects their samples and builds reports.
 */
class Sitepulse_Page_Analysis {

	/**
	 * Option holding recent analyses (newest first, one per URL)
	 */
	const OPTION = 'sitepulse_page_analyses';

	/**
	 * Admin page slug
	 */
	const ADMIN_PAGE = 'wpsp_sitepulse_page_analysis';

	/**
	 * Number of analyses kept
	 */
	const MAX_STORED = 8;

	/**
	 * Page loads measured per analysis
	 */
	const SAMPLES = 3;

	/**
	 * Seconds to wait for one page load
	 */
	const LOOPBACK_TIMEOUT = 20;

	/**
	 * Server time, in milliseconds, under which a page counts as fast
	 */
	const FAST_MS = 600;

	/**
	 * Server time, in milliseconds, from which a page counts as slow
	 */
	const SLOW_MS = 1500;

	/**
	 * Link to the Page Analysis screen.
	 *
	 * @param string $url     Page to prefill
	 * @param bool   $autorun Start analyzing as soon as the screen opens
	 * @return string
	 */
	public static function admin_link( $url = '', $autorun = false ) {
		$args = array( 'page' => self::ADMIN_PAGE );

		if ( '' !== $url ) {
			$args['url'] = rawurlencode( $url );
		}
		if ( $autorun ) {
			$args['autorun'] = 1;
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Turn user input into an absolute URL on this site.
	 *
	 * Accepts full URLs, host-relative paths ("/shop") and paths relative to the
	 * home page ("shop/"). Rejects other sites and admin, login or API addresses.
	 *
	 * @param string $raw User input
	 * @return string|WP_Error
	 */
	public static function normalize_url( $raw ) {
		$raw       = trim( (string) $raw );
		$home      = wp_parse_url( home_url( '/' ) );
		$home_path = isset( $home['path'] ) ? $home['path'] : '/';
		$site      = untrailingslashit( preg_replace( '#^https?://#i', '', home_url( '/' ) ) );

		if ( '' === $raw ) {
			return new WP_Error( 'sitepulse_missing_url', __( 'Enter the address of a page on this site.', 'sitepulse' ), array( 'status' => 400 ) );
		}

		if ( 0 === strpos( $raw, '//' ) ) {
			$raw = $home['scheme'] . ':' . $raw;
		} elseif ( 0 === strpos( $raw, '/' ) ) {
			// A path outside this site's folder ("/contact/" on a site at /blog/) is meant relative to the site.
			$raw = 0 === strpos( $raw, $home_path ) || rtrim( $home_path, '/' ) === $raw
				? $home['scheme'] . '://' . $home['host'] . ( isset( $home['port'] ) ? ':' . $home['port'] : '' ) . $raw
				: home_url( $raw );
		} elseif ( ! preg_match( '#^https?://#i', $raw ) ) {
			$raw = 0 === stripos( $raw, $home['host'] ) ? $home['scheme'] . '://' . $raw : home_url( '/' . ltrim( $raw, '/' ) );
		}

		$parts     = wp_parse_url( $raw );
		$home_port = isset( $home['port'] ) ? (int) $home['port'] : ( 'https' === $home['scheme'] ? 443 : 80 );
		$port      = isset( $parts['port'] ) ? (int) $parts['port'] : $home_port;
		$path      = is_array( $parts ) && isset( $parts['path'] ) && '' !== $parts['path'] ? $parts['path'] : '/';
		// On a subdirectory install or multisite network, other sites share the host but not the path.
		$in_site = 0 === strpos( $path, $home_path ) || rtrim( $home_path, '/' ) === $path;
		if ( ! is_array( $parts ) || empty( $parts['host'] ) || strtolower( $parts['host'] ) !== strtolower( $home['host'] ) || $port !== $home_port || ! $in_site ) {
			return new WP_Error(
				'sitepulse_external_url',
				/* translators: %s: this site's address */
				sprintf( __( 'Only pages on %s can be analyzed.', 'sitepulse' ), $site ),
				array( 'status' => 400 )
			);
		}

		if ( preg_match( '#/(wp-admin|wp-login\.php|wp-json|xmlrpc\.php|wp-cron\.php)(/|$)#i', $path ) ) {
			return new WP_Error( 'sitepulse_admin_url', __( 'Admin, login and API addresses can\'t be analyzed. Choose a page your visitors see.', 'sitepulse' ), array( 'status' => 400 ) );
		}

		// Always request this site's own scheme and port; another port could reach a different service.
		$url = $home['scheme'] . '://' . strtolower( $parts['host'] ) . ( isset( $home['port'] ) ? ':' . $home['port'] : '' ) . $path;
		if ( ! empty( $parts['query'] ) ) {
			$url .= '?' . $parts['query'];
		}

		return esc_url_raw( remove_query_arg( Sitepulse_Page_Tracker::QUERY_VAR, $url ) );
	}

	/**
	 * Start a new analysis, replacing any earlier one for the same page.
	 *
	 * @param string $raw_url Page address
	 * @return array|WP_Error The new analysis
	 */
	public static function start( $raw_url ) {
		$url = self::normalize_url( $raw_url );
		if ( is_wp_error( $url ) ) {
			return $url;
		}

		$analysis = array(
			'id'             => strtolower( wp_generate_password( 12, false, false ) ),
			'url'            => $url,
			'created_at'     => time(),
			'samples'        => array(),
			'pending_tokens' => array(),
		);

		$others = array_filter( self::get_all(), static function ( $item ) use ( $url ) {
			return $item['url'] !== $url;
		} );
		array_unshift( $others, $analysis );
		self::save_all( array_slice( array_values( $others ), 0, self::MAX_STORED ) );

		return $analysis;
	}

	/**
	 * Load the page once through a loopback request and store the sample.
	 *
	 * @param string $id Analysis ID
	 * @return array|WP_Error Result; on failure it explains why and links to a measurable visit
	 */
	public static function run_sample( $id ) {
		$analysis = self::find( $id );
		if ( null === $analysis ) {
			return self::not_found();
		}

		if ( count( $analysis['samples'] ) >= self::SAMPLES ) {
			return array( 'ok' => true, 'samples' => count( $analysis['samples'] ) );
		}

		$token    = self::issue_token( $analysis['id'] );
		$started  = microtime( true );
		$response = wp_remote_get( self::visit_url( $analysis['url'], $token ), self::loopback_args() );
		$elapsed  = round( ( microtime( true ) - $started ) * 1000, 1 );
		$sample   = self::take_sample( $token );

		if ( null !== $sample ) {
			$sample['response_ms'] = $elapsed;
			$analysis['samples'][] = $sample;
			self::save( $analysis );

			return array(
				'ok'        => true,
				'samples'   => count( $analysis['samples'] ),
				'server_ms' => $sample['server_ms'],
			);
		}

		// Keep the token alive so a browser visit can still be measured.
		$analysis['pending_tokens'][] = $token;
		self::save( $analysis );

		return array_merge(
			array(
				'ok'        => false,
				'samples'   => count( $analysis['samples'] ),
				'visit_url' => self::visit_url( $analysis['url'], $token ),
			),
			self::explain_failure( $response )
		);
	}

	/**
	 * Collect samples recorded by browser visits to a pending analysis link.
	 *
	 * @param string $id Analysis ID
	 * @return array|WP_Error
	 */
	public static function collect( $id ) {
		$analysis = self::find( $id );
		if ( null === $analysis ) {
			return self::not_found();
		}

		$collected = 0;
		$pending   = array();
		foreach ( $analysis['pending_tokens'] as $token ) {
			$sample = self::take_sample( $token );
			if ( null === $sample ) {
				$pending[] = $token;
				continue;
			}

			$sample['response_ms'] = null;
			$analysis['samples'][] = $sample;
			$collected++;
		}

		if ( $collected ) {
			$analysis['pending_tokens'] = $pending;
			self::save( $analysis );
		}

		return array(
			'ok'        => true,
			'collected' => $collected,
			'samples'   => count( $analysis['samples'] ),
		);
	}

	/**
	 * Report for one analysis.
	 *
	 * @param string $id Analysis ID
	 * @return array|WP_Error
	 */
	public static function get_report( $id ) {
		$analysis = self::find( $id );

		return null === $analysis ? self::not_found() : self::build_report( $analysis );
	}

	/**
	 * Remove one analysis.
	 *
	 * @param string $id Analysis ID
	 * @return bool Whether it existed
	 */
	public static function delete( $id ) {
		$all  = self::get_all();
		$kept = array_values( array_filter( $all, static function ( $item ) use ( $id ) {
			return $item['id'] !== $id;
		} ) );

		if ( count( $kept ) === count( $all ) ) {
			return false;
		}

		self::save_all( $kept );
		return true;
	}

	/**
	 * Recent analyses, newest first, summarized for a list.
	 *
	 * @return array
	 */
	public static function get_recent() {
		$recent = array();

		foreach ( self::get_all() as $analysis ) {
			$report   = self::build_report( $analysis );
			$recent[] = array(
				'id'         => $analysis['id'],
				'url'        => $analysis['url'],
				'title'      => $report['complete'] ? $report['title'] : $analysis['url'],
				'kind'       => $report['complete'] ? $report['kind'] : '',
				'server'     => $report['complete'] ? $report['display']['server'] : '',
				'level'      => $report['complete'] ? $report['verdict']['level'] : 'none',
				'created_at' => (int) $analysis['created_at'],
			);
		}

		return $recent;
	}

	/**
	 * Whether any analysis has produced a report yet.
	 *
	 * @return bool
	 */
	public static function has_completed_analysis() {
		foreach ( self::get_all() as $analysis ) {
			if ( ! empty( $analysis['samples'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Pages worth analyzing first: home, blog, shop and recently updated content
	 * of every public post type.
	 *
	 * @return array List of label and URL pairs
	 */
	public static function get_suggestions() {
		$items = array(
			array( 'label' => __( 'Homepage', 'sitepulse' ), 'url' => home_url( '/' ) ),
		);

		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page ) {
			$items[] = array( 'label' => __( 'Blog page', 'sitepulse' ), 'url' => get_permalink( $posts_page ) );
		}

		$shop_page = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
		if ( $shop_page > 0 ) {
			$items[] = array( 'label' => __( 'Shop', 'sitepulse' ), 'url' => get_permalink( $shop_page ) );
		}

		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) {
			if ( 'attachment' === $post_type->name ) {
				continue;
			}

			$posts = get_posts( array(
				'post_type'      => $post_type->name,
				'post_status'    => 'publish',
				'posts_per_page' => 3,
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			) );

			foreach ( $posts as $post ) {
				$items[] = array(
					'label' => $post_type->labels->singular_name . ': ' . get_the_title( $post ),
					'url'   => get_permalink( $post ),
				);
			}
		}

		$unique = array();
		foreach ( $items as $item ) {
			if ( is_string( $item['url'] ) && '' !== $item['url'] && ! isset( $unique[ $item['url'] ] ) ) {
				$unique[ $item['url'] ] = $item;
			}
		}

		return array_slice( array_values( $unique ), 0, 20 );
	}

	/**
	 * Build the report for a stored analysis.
	 *
	 * @param array $analysis Stored analysis
	 * @return array
	 */
	public static function build_report( array $analysis ) {
		$samples = array_values( array_filter( $analysis['samples'], 'is_array' ) );
		$report  = array(
			'id'         => $analysis['id'],
			'url'        => $analysis['url'],
			'created_at' => (int) $analysis['created_at'],
			'samples'    => count( $samples ),
			'pending'    => count( $analysis['pending_tokens'] ),
			'complete'   => ! empty( $samples ),
		);

		if ( ! $samples ) {
			return $report;
		}

		$last         = end( $samples );
		$server_ms    = self::median( array_column( $samples, 'server_ms' ) );
		$plugin_names = self::plugin_names();
		$sources      = self::merge_sources( $samples, $server_ms, $plugin_names );
		$attributed   = array_sum( array_column( $sources, 'self_ms' ) );
		$http         = self::merge_http( $samples, $plugin_names );
		$http_ms    = array_sum( array_column( $http, 'total_ms' ) ) / count( $samples );

		$report += array(
			'title'       => '' !== $last['page']['title'] ? $last['page']['title'] : $analysis['url'],
			'kind'        => $last['page']['kind'],
			'status'      => (int) $last['status'],
			'server_ms'   => $server_ms,
			'first_ms'    => (float) $samples[0]['server_ms'],
			'response_ms' => self::median( array_filter( array_column( $samples, 'response_ms' ), 'is_numeric' ) ),
			'queries'     => (int) round( self::median( array_column( $samples, 'queries' ) ) ),
			'memory_peak' => (int) max( array_column( $samples, 'memory_peak' ) ),
			'core_ms'     => round( max( 0.0, $server_ms - $attributed ), 1 ),
			'http_ms'     => round( $http_ms, 1 ),
			'sources'     => array_slice( $sources, 0, 12 ),
			'callbacks'   => self::merge_callbacks( $samples ),
			'http'        => $http,
		);

		$report['core_share'] = $server_ms > 0 ? (int) round( min( 100, $report['core_ms'] / $server_ms * 100 ) ) : 0;
		$report['display']    = array(
			'server'   => self::format_ms( $server_ms ),
			'first'    => self::format_ms( $report['first_ms'] ),
			'response' => null === $report['response_ms'] ? '' : self::format_ms( $report['response_ms'] ),
			'core'     => self::format_ms( $report['core_ms'] ),
			'http'     => self::format_ms( $http_ms ),
			'memory'   => self::format_bytes( $report['memory_peak'] ),
		);
		$report['verdict']  = self::verdict( $report );
		$report['findings'] = self::findings( $report );

		return $report;
	}

	/**
	 * Format milliseconds for people.
	 *
	 * @param float $ms Milliseconds
	 * @return string
	 */
	public static function format_ms( $ms ) {
		$ms = (float) $ms;

		/* translators: %s: duration in seconds */
		return $ms >= 1000 ? sprintf( __( '%s s', 'sitepulse' ), number_format_i18n( $ms / 1000, 2 ) )
			/* translators: %s: duration in milliseconds */
			: sprintf( __( '%s ms', 'sitepulse' ), number_format_i18n( $ms ) );
	}

	/**
	 * Format a memory size for people.
	 *
	 * @param int $bytes Bytes
	 * @return string
	 */
	public static function format_bytes( $bytes ) {
		/* translators: %s: memory in megabytes */
		return sprintf( __( '%s MB', 'sitepulse' ), number_format_i18n( $bytes / MB_IN_BYTES, $bytes < 10 * MB_IN_BYTES ? 1 : 0 ) );
	}

	/**
	 * One-line verdict on how fast WordPress builds the page.
	 *
	 * @param array $report Report being built
	 * @return array Level, label, summary and the main cause when one stands out
	 */
	private static function verdict( array $report ) {
		$time = $report['display']['server'];
		$top  = $report['sources'][0] ?? null;

		if ( $report['server_ms'] < self::FAST_MS ) {
			return array(
				'level'   => 'good',
				'label'   => __( 'Fast', 'sitepulse' ),
				/* translators: %s: time WordPress needs to build the page */
				'summary' => sprintf( __( 'WordPress builds this page in %s, so visitors are not kept waiting by your server.', 'sitepulse' ), $time ),
				'detail'  => '',
			);
		}

		$detail = '';
		if ( null !== $top && $top['share'] >= 20 ) {
			/* translators: %s: plugin or theme name */
			$detail = sprintf( __( 'The biggest share goes to %s.', 'sitepulse' ), $top['name'] );
		} elseif ( $report['core_share'] >= 50 ) {
			$detail = __( 'Most of it is spent in WordPress itself rather than in a single plugin.', 'sitepulse' );
		}

		if ( $report['server_ms'] < self::SLOW_MS ) {
			return array(
				'level'   => 'fair',
				'label'   => __( 'Could be faster', 'sitepulse' ),
				/* translators: 1: time WordPress needs to build the page, 2: target time */
				'summary' => sprintf( __( 'WordPress needs %1$s to build this page. Visitors notice the wait; aim for under %2$s.', 'sitepulse' ), $time, self::format_ms( self::FAST_MS ) ),
				'detail'  => $detail,
			);
		}

		return array(
			'level'   => 'slow',
			'label'   => __( 'Slow', 'sitepulse' ),
			/* translators: %s: time WordPress needs to build the page */
			'summary' => sprintf( __( 'WordPress needs %s to build this page, so visitors stare at a blank screen before anything appears.', 'sitepulse' ), $time ),
			'detail'  => $detail,
		);
	}

	/**
	 * Explain what the measurements mean and what to do about them.
	 *
	 * @param array $report Report being built
	 * @return array Findings, most severe first
	 */
	private static function findings( array $report ) {
		$findings = array();

		if ( $report['status'] >= 300 && $report['status'] < 400 ) {
			$findings[] = self::finding(
				'warning',
				/* translators: %d: HTTP status code */
				sprintf( __( 'This address redirects (HTTP %d)', 'sitepulse' ), $report['status'] ),
				__( 'SitePulse measured the redirect, not the page it leads to. Open the address in your browser and analyze the address you end up on.', 'sitepulse' )
			);
		}

		if ( $report['status'] >= 400 ) {
			$findings[] = self::finding(
				'danger',
				/* translators: %d: HTTP status code */
				sprintf( __( 'This address returns an error (HTTP %d)', 'sitepulse' ), $report['status'] ),
				__( 'Visitors see an error page here. Check the address, or fix the error before working on speed.', 'sitepulse' )
			);
		}

		foreach ( array_slice( $report['sources'], 0, 2 ) as $source ) {
			if ( $source['self_ms'] < 100 || $source['share'] < 10 ) {
				continue;
			}

			$share   = (int) round( $source['share'] );
			$waiting = 'plugin' === $source['type'] ? self::plugin_requests( $report, $source['name'] ) : array();
			if ( $waiting && $waiting['ms'] >= $source['self_ms'] / 2 ) {
				/* translators: 1: share of the page's server time, in percent, 2: plugin name, 3: host name */
				$text = sprintf( __( 'That is %1$d%% of the time WordPress spends on this page, and most of it is %2$s waiting for %3$s to answer. Caching that answer, or fetching it in the background, removes the wait.', 'sitepulse' ), $share, $source['name'], $waiting['host'] );
			} elseif ( 'theme' === $source['type'] ) {
				/* translators: %d: share of the page's server time, in percent */
				$text = sprintf( __( 'That is %d%% of the time WordPress spends on this page. Your theme runs on every page, so savings here speed up the whole site.', 'sitepulse' ), $share );
			} else {
				/* translators: 1: share of the page's server time, in percent, 2: plugin name */
				$text = sprintf( __( 'That is %1$d%% of the time WordPress spends on this page. If %2$s is not needed here, look in its settings for an option to load it only where it is used, or ask its developer about performance.', 'sitepulse' ), $share, $source['name'] );
			}

			$findings[] = self::finding(
				$source['self_ms'] >= 300 || $source['share'] >= 30 ? 'warning' : 'info',
				/* translators: 1: plugin or theme name, 2: duration */
				sprintf( __( '%1$s takes %2$s', 'sitepulse' ), $source['name'], $source['time'] ),
				$text
			);
		}

		if ( $report['http'] ) {
			$slowest = $report['http'][0];
			$count   = count( $report['http'] );
			$level   = $slowest['max_ms'] >= 1000 ? 'danger' : ( $slowest['max_ms'] >= 300 ? 'warning' : 'info' );

			$findings[] = self::finding(
				$level,
				/* translators: 1: number of external requests, 2: duration */
				sprintf( _n( '%1$d external request adds %2$s', '%1$d external requests add %2$s', $count, 'sitepulse' ), $count, $report['display']['http'] ),
				/* translators: 1: host name, 2: duration */
				sprintf( __( 'Your server waits for these before it can send the page. The slowest goes to %1$s and takes %2$s. Caching the answer or fetching it in the background removes the wait.', 'sitepulse' ), $slowest['host'], self::format_ms( $slowest['max_ms'] ) )
			);
		}

		if ( $report['queries'] >= 100 ) {
			$findings[] = self::finding(
				$report['queries'] >= 200 ? 'danger' : 'warning',
				/* translators: %d: number of database queries */
				sprintf( __( '%d database queries', 'sitepulse' ), $report['queries'] ),
				__( 'Well-built pages usually need fewer than 100. A high count often means a plugin queries inside a loop. A persistent object cache such as Redis also helps.', 'sitepulse' )
			);
		}

		if ( $report['memory_peak'] >= 128 * MB_IN_BYTES ) {
			$findings[] = self::finding(
				$report['memory_peak'] >= 256 * MB_IN_BYTES ? 'danger' : 'warning',
				/* translators: %s: memory size */
				sprintf( __( 'Peak memory of %s', 'sitepulse' ), $report['display']['memory'] ),
				__( 'This page uses a lot of memory, which limits how many visitors your server can handle at once.', 'sitepulse' )
			);
		}

		if ( $report['samples'] > 1 && $report['first_ms'] > 1.5 * $report['server_ms'] && $report['first_ms'] - $report['server_ms'] > 300 ) {
			$findings[] = self::finding(
				'info',
				__( 'The first load was slower', 'sitepulse' ),
				/* translators: 1: first load duration, 2: typical duration */
				sprintf( __( 'The first load took %1$s and later loads about %2$s. Caches were still warming up, so most visitors get the faster time.', 'sitepulse' ), $report['display']['first'], $report['display']['server'] )
			);
		}

		if ( $report['server_ms'] >= self::FAST_MS && $report['core_share'] >= 60 ) {
			$findings[] = self::finding(
				'info',
				__( 'Most time is spent in WordPress itself', 'sitepulse' ),
				__( 'No single plugin stands out. Database speed, the number of queries and your hosting matter most here, and a page cache would skip this work for most visitors.', 'sitepulse' )
			);
		}

		if ( ! $findings ) {
			$findings[] = $report['server_ms'] < self::FAST_MS
				? self::finding( 'success', __( 'Nothing stands out', 'sitepulse' ), __( 'This page is in good shape. Run the analysis again after adding plugins or changing your theme.', 'sitepulse' ) )
				: self::finding( 'info', __( 'No single cause stands out', 'sitepulse' ), __( 'The time is spread across WordPress, your theme and plugins. A page cache gives the biggest improvement in this situation.', 'sitepulse' ) );
		}

		$order = array( 'danger' => 0, 'warning' => 1, 'info' => 2, 'success' => 3 );
		usort( $findings, static function ( $a, $b ) use ( $order ) {
			return $order[ $a['level'] ] <=> $order[ $b['level'] ];
		} );

		return $findings;
	}

	/**
	 * Time a plugin spends waiting for its own external requests, per page load.
	 *
	 * @param array  $report Report being built
	 * @param string $name   Plugin name
	 * @return array Waiting time and the slowest host, or an empty array
	 */
	private static function plugin_requests( array $report, $name ) {
		$requests = array_values( array_filter( $report['http'], static function ( $request ) use ( $name ) {
			return 'Plugin: ' . $name === $request['origin'];
		} ) );

		if ( ! $requests ) {
			return array();
		}

		return array(
			'ms'   => array_sum( array_column( $requests, 'total_ms' ) ) / $report['samples'],
			'host' => $requests[0]['host'],
		);
	}

	/**
	 * Shape one finding.
	 *
	 * @param string $level danger, warning, info or success
	 * @param string $title Short statement
	 * @param string $text  What it means and what to do
	 * @return array
	 */
	private static function finding( $level, $title, $text ) {
		return array( 'level' => $level, 'title' => $title, 'text' => $text );
	}

	/**
	 * Average each plugin's and theme's own time per page load.
	 *
	 * @param array $samples      Samples
	 * @param float $server_ms    Typical server time
	 * @param array $plugin_names Plugin names by folder
	 * @return array Sources, slowest first
	 */
	private static function merge_sources( array $samples, $server_ms, array $plugin_names ) {
		$count  = count( $samples );
		$merged = array();

		foreach ( $samples as $sample ) {
			foreach ( $sample['sources'] as $source ) {
				if ( 'core' === $source['type'] ) {
					continue;
				}

				$key = $source['type'] . ':' . $source['source'];
				if ( ! isset( $merged[ $key ] ) ) {
					$merged[ $key ] = array( 'slug' => $source['source'], 'type' => $source['type'], 'self_ms' => 0.0, 'calls' => 0 );
				}
				$merged[ $key ]['self_ms'] += (float) $source['self_ms'];
				$merged[ $key ]['calls']   += (int) $source['calls'];
			}
		}

		$rows = array();
		foreach ( $merged as $row ) {
			$ms = $row['self_ms'] / $count;
			if ( $ms < 0.5 ) {
				continue;
			}

			$rows[] = array(
				'name'    => self::source_name( $row['type'], $row['slug'], $plugin_names ),
				'slug'    => $row['slug'],
				'type'    => $row['type'],
				'self_ms' => round( $ms, 1 ),
				'share'   => $server_ms > 0 ? round( min( 100, $ms / $server_ms * 100 ), 1 ) : 0,
				'calls'   => (int) round( $row['calls'] / $count ),
				'time'    => self::format_ms( $ms ),
			);
		}

		usort( $rows, static function ( $a, $b ) {
			return $b['self_ms'] <=> $a['self_ms'];
		} );

		return $rows;
	}

	/**
	 * Average the slowest individual callbacks per page load.
	 *
	 * @param array $samples Samples
	 * @return array Callbacks, slowest first
	 */
	private static function merge_callbacks( array $samples ) {
		$count  = count( $samples );
		$merged = array();

		foreach ( $samples as $sample ) {
			foreach ( $sample['callbacks'] as $callback ) {
				$key = $callback['hook'] . '|' . $callback['callback'] . '|' . $callback['fileline'];
				if ( ! isset( $merged[ $key ] ) ) {
					$merged[ $key ] = $callback;
					$merged[ $key ]['self_ms'] = 0.0;
				}
				$merged[ $key ]['self_ms'] += (float) $callback['self_ms'];
			}
		}

		$rows = array();
		foreach ( $merged as $callback ) {
			$ms     = $callback['self_ms'] / $count;
			$rows[] = array(
				'hook'     => $callback['hook'],
				'callback' => $callback['callback'],
				'fileline' => $callback['fileline'],
				'source'   => $callback['source'],
				'type'     => $callback['type'],
				'self_ms'  => round( $ms, 1 ),
				'time'     => self::format_ms( $ms ),
			);
		}

		usort( $rows, static function ( $a, $b ) {
			return $b['self_ms'] <=> $a['self_ms'];
		} );

		return array_slice( $rows, 0, 10 );
	}

	/**
	 * Group outgoing requests by address across all page loads.
	 *
	 * @param array $samples      Samples
	 * @param array $plugin_names Plugin names by folder, to name the plugin that made each request
	 * @return array Requests, slowest first
	 */
	private static function merge_http( array $samples, array $plugin_names ) {
		$count  = count( $samples );
		$merged = array();

		foreach ( $samples as $sample ) {
			foreach ( $sample['http'] as $event ) {
				$parts = wp_parse_url( (string) $event['url'] );
				$key   = ( $parts['host'] ?? '' ) . ( $parts['path'] ?? '' );
				if ( ! isset( $merged[ $key ] ) ) {
					$merged[ $key ] = array(
						'url'      => (string) $event['url'],
						'host'     => (string) $event['host'],
						'origin'   => self::origin_name( (string) $event['origin'], $plugin_names ),
						'code'     => 0,
						'calls'    => 0,
						'total_ms' => 0.0,
						'max_ms'   => 0.0,
					);
				}
				$merged[ $key ]['code']      = (int) $event['code'];
				$merged[ $key ]['calls']    += 1;
				$merged[ $key ]['total_ms'] += (float) $event['elapsed_ms'];
				$merged[ $key ]['max_ms']    = max( $merged[ $key ]['max_ms'], (float) $event['elapsed_ms'] );
			}
		}

		$rows = array();
		foreach ( $merged as $row ) {
			$row['avg_ms']   = round( $row['total_ms'] / $row['calls'], 1 );
			$row['per_load'] = round( $row['calls'] / $count, 1 );
			$row['time']     = self::format_ms( $row['avg_ms'] );
			$rows[]          = $row;
		}

		usort( $rows, static function ( $a, $b ) {
			return $b['max_ms'] <=> $a['max_ms'];
		} );

		return $rows;
	}

	/**
	 * Map plugin folder names to plugin names.
	 *
	 * @return array
	 */
	private static function plugin_names() {
		if ( ! function_exists( 'get_plugins' ) ) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }

		$names = array();
		foreach ( get_plugins() as $file => $data ) {
			$slug           = false !== strpos( $file, '/' ) ? strstr( $file, '/', true ) : basename( $file, '.php' );
			$names[ $slug ] = (string) ( $data['Name'] ?? '' );
		}

		return $names;
	}

	/**
	 * Replace the plugin folder in a request origin ("Plugin: rate-fetcher") with the plugin's name.
	 *
	 * Front-end requests cannot load the plugin list, so the tracker records folder names.
	 *
	 * @param string $origin       Origin label
	 * @param array  $plugin_names Plugin names by folder
	 * @return string
	 */
	private static function origin_name( $origin, array $plugin_names ) {
		$slug = 0 === strpos( $origin, 'Plugin: ' ) ? substr( $origin, 8 ) : '';

		return '' !== ( $plugin_names[ $slug ] ?? '' ) ? 'Plugin: ' . $plugin_names[ $slug ] : $origin;
	}

	/**
	 * Human name of a plugin or theme.
	 *
	 * @param string $type         plugin or theme
	 * @param string $slug         Folder name
	 * @param array  $plugin_names Plugin names by folder
	 * @return string
	 */
	private static function source_name( $type, $slug, array $plugin_names ) {
		if ( 'theme' === $type ) {
			$theme = wp_get_theme( $slug );
			/* translators: %s: theme name */
			return sprintf( __( '%s (theme)', 'sitepulse' ), $theme->exists() && '' !== $theme->get( 'Name' ) ? $theme->get( 'Name' ) : $slug );
		}

		return '' !== ( $plugin_names[ $slug ] ?? '' ) ? $plugin_names[ $slug ] : $slug;
	}

	/**
	 * Median of a list of numbers.
	 *
	 * @param array $values Numbers
	 * @return float|null
	 */
	private static function median( array $values ) {
		if ( ! $values ) {
			return null;
		}

		$values = array_map( 'floatval', array_values( $values ) );
		sort( $values );
		$middle = intdiv( count( $values ), 2 );

		return count( $values ) % 2 ? $values[ $middle ] : ( $values[ $middle - 1 ] + $values[ $middle ] ) / 2;
	}

	/**
	 * Arguments for loading a page as a logged-out visitor.
	 *
	 * @return array
	 */
	private static function loopback_args() {
		$args = array(
			'timeout'     => self::LOOPBACK_TIMEOUT,
			'redirection' => 5,
			'sslverify'   => apply_filters( 'https_local_ssl_verify', false ),
			'user-agent'  => 'SitePulse Page Analysis',
			'cookies'     => array(),
			'headers'     => array(
				'Cache-Control' => 'no-cache',
				'Pragma'        => 'no-cache',
			),
		);

		// Pass HTTP Basic credentials through, like WordPress Site Health loopback checks.
		if ( isset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) ) {
			$args['headers']['Authorization'] = 'Basic ' . base64_encode( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) . ':' . wp_unslash( $_SERVER['PHP_AUTH_PW'] ) );
		}

		return $args;
	}

	/**
	 * Explain why a page load produced no sample.
	 *
	 * @param array|WP_Error $response Loopback response
	 * @return array Reason code and message
	 */
	private static function explain_failure( $response ) {
		if ( is_wp_error( $response ) ) {
			return array(
				'reason'  => 'loopback',
				/* translators: %s: error message */
				'message' => sprintf( __( 'Your server could not load the page by itself (%s). Many hosts block these internal requests.', 'sitepulse' ), $response->get_error_message() ),
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code >= 400 ) {
			return array(
				'reason'  => 'http_error',
				/* translators: %d: HTTP status code */
				'message' => sprintf( __( 'The page answered with HTTP %d when your server requested it. A firewall, security plugin or password protection probably blocked the request.', 'sitepulse' ), $code ),
			);
		}

		return array(
			'reason'  => 'cached',
			'message' => __( 'The page was answered without running WordPress, usually because a page cache or CDN served a saved copy.', 'sitepulse' ),
		);
	}

	/**
	 * Issue a one-time token that lets a single page load profile itself.
	 *
	 * @param string $id Analysis ID
	 * @return string
	 */
	private static function issue_token( $id ) {
		$token = strtolower( wp_generate_password( Sitepulse_Page_Tracker::TOKEN_LENGTH, false, false ) );
		set_transient( Sitepulse_Page_Tracker::TOKEN_TRANSIENT_PREFIX . $token, $id, 15 * MINUTE_IN_SECONDS );

		return $token;
	}

	/**
	 * Take the sample a token's page load stored, retiring the token.
	 *
	 * @param string $token Token
	 * @return array|null
	 */
	private static function take_sample( $token ) {
		$sample = get_transient( Sitepulse_Page_Tracker::SAMPLE_TRANSIENT_PREFIX . $token );
		if ( ! is_array( $sample ) ) {
			return null;
		}

		delete_transient( Sitepulse_Page_Tracker::SAMPLE_TRANSIENT_PREFIX . $token );
		delete_transient( Sitepulse_Page_Tracker::TOKEN_TRANSIENT_PREFIX . $token );

		return $sample;
	}

	/**
	 * Page address carrying an analysis token.
	 *
	 * @param string $url   Page
	 * @param string $token Token
	 * @return string
	 */
	private static function visit_url( $url, $token ) {
		return add_query_arg( Sitepulse_Page_Tracker::QUERY_VAR, $token, $url );
	}

	/**
	 * Error for analyses that no longer exist.
	 *
	 * @return WP_Error
	 */
	private static function not_found() {
		return new WP_Error( 'sitepulse_analysis_not_found', __( 'This analysis no longer exists. Start a new one.', 'sitepulse' ), array( 'status' => 404 ) );
	}

	/**
	 * Find one stored analysis.
	 *
	 * @param string $id Analysis ID
	 * @return array|null
	 */
	private static function find( $id ) {
		foreach ( self::get_all() as $analysis ) {
			if ( $analysis['id'] === $id ) {
				return $analysis;
			}
		}

		return null;
	}

	/**
	 * Store one analysis in place.
	 *
	 * @param array $analysis Analysis
	 */
	private static function save( array $analysis ) {
		self::save_all( array_map( static function ( $item ) use ( $analysis ) {
			return $item['id'] === $analysis['id'] ? $analysis : $item;
		}, self::get_all() ) );
	}

	/**
	 * All stored analyses, newest first. Malformed entries are dropped.
	 *
	 * @return array
	 */
	private static function get_all() {
		$all = get_option( self::OPTION, array() );
		if ( ! is_array( $all ) ) {
			return array();
		}

		return array_values( array_filter( $all, static function ( $item ) {
			return is_array( $item ) && isset( $item['id'], $item['url'], $item['created_at'] ) && is_array( $item['samples'] ?? null ) && is_array( $item['pending_tokens'] ?? null );
		} ) );
	}

	/**
	 * Persist analyses.
	 *
	 * @param array $all Analyses
	 */
	private static function save_all( array $all ) {
		update_option( self::OPTION, $all, false );
	}
}

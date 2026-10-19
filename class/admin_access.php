<?php
/**
 * SitePulse wp-admin reachability
 *
 * Checks, from the site's own server, that wp-admin can be reached: it requests /wp-admin/ and
 * the login page the way WordPress checks an edited plugin for fatal errors (a loopback request
 * with WordPress's own fatal-error markers). The check runs every hour in WP-Cron, before a
 * check-in when the last check is older than 15 minutes, and when an administrator presses
 * Check now. The plugin's pages and the admin bar show the result, and the check-in reports it
 * as wp_admin (see "wp-admin reachability" in docs/remote-management.md).
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Admin_Access
 */
class Sitepulse_Admin_Access {

	/** Cron hook of the hourly check. */
	const CRON_HOOK = 'sitepulse_admin_access_check';

	/** AJAX action and nonce of the Check now button. */
	const AJAX_ACTION = 'sitepulse_admin_access_check';

	/** Held while a check runs, so two checks never run at once. */
	const LOCK = 'sitepulse_admin_access_lock';

	/** Seconds to wait for each page, as long as WordPress's own loopback check in Site Health waits. */
	const TIMEOUT = 10;

	/** Age in seconds of a result that a check-in checks again before reporting it. */
	const STALE_SECONDS = 900;

	/** Longest message kept, in characters. */
	const MESSAGE_LENGTH = 200;

	/**
	 * Register the hourly check and the Check now button.
	 */
	public static function init() {
		add_action( self::CRON_HOOK, array( __CLASS__, 'check' ) );
		add_action( 'init', array( __CLASS__, 'ensure_schedule' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_check' ) );
	}

	/**
	 * Schedule the hourly check when it is missing.
	 */
	public static function ensure_schedule() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'hourly', self::CRON_HOOK );
		}
	}

	/**
	 * Stop the hourly check; the plugin is being deactivated.
	 */
	public static function unschedule() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Request /wp-admin/ and the login page from the site's own server, and store the result.
	 *
	 * @return array|null The result, or null while another check runs.
	 */
	public static function check() {
		if ( get_transient( self::LOCK ) ) {
			return null;
		}
		// Long enough for both requests; a check that dies holds it no longer.
		set_transient( self::LOCK, 1, 3 * self::TIMEOUT );

		$start = microtime( true );
		$admin = Sitepulse_Remote_Health::fetch( admin_url(), self::TIMEOUT );
		$login = Sitepulse_Remote_Health::fetch( wp_login_url(), self::TIMEOUT );
		$ms    = (int) round( ( microtime( true ) - $start ) * 1000 );

		$result = self::result( $admin, $login, $ms );
		self::save( $result );
		delete_transient( self::LOCK );

		return $result;
	}

	/**
	 * The result a check-in reports. When the last check is older than 15 minutes it runs again
	 * first; otherwise the check-in does not wait for it.
	 *
	 * @return array|null The result, null before the first check.
	 */
	public static function report() {
		$last = self::last();
		if ( null !== $last && time() - strtotime( $last['checked_at'] ) > self::STALE_SECONDS ) {
			$fresh = self::check();
			$last  = null === $fresh ? $last : $fresh;
		}

		return $last;
	}

	/**
	 * @return array|null The last result, null before the first check.
	 */
	public static function last() {
		return self::stored()['last'];
	}

	/**
	 * Check now, from the plugin's pages: answers what the pages show.
	 */
	public static function ajax_check() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( self::AJAX_ACTION, 'nonce', false ) ) {
			wp_send_json_error( null, 403 );
		}

		if ( null === self::check() ) {
			wp_send_json_error( array( 'message' => __( 'A check is already running. Try again in a moment.', 'sitepulse' ) ), 409 );
		}
		wp_send_json_success( self::view() );
	}

	/**
	 * Show the result and the Check now button.
	 *
	 * @param string $context easy for the Simple dashboard, advanced otherwise.
	 */
	public static function render( $context = 'advanced' ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$sp_admin_access = self::view() + array(
			'url'    => admin_url( 'admin-ajax.php' ),
			'action' => self::AJAX_ACTION,
			'nonce'  => wp_create_nonce( self::AJAX_ACTION ),
		);
		wp_enqueue_script( 'sitepulse-admin-access', SITEPULSE_ADMIN_ASSETS_JS_URL . 'admin-access.js', array(), SITEPULSE_VERSION, true );
		include SITEPULSE_PATH . ( 'easy' === $context ? 'templates/easy/partials/admin-access.php' : 'templates/backend/admin_access.php' );
	}

	/**
	 * @return string The plugin page that shows the result: Site Information on the Simple
	 *                dashboard, the settings on the Advanced one.
	 */
	public static function page_url() {
		return admin_url( Sitepulse_Easy_Mode::is_enabled() ? 'admin.php?page=wpsp_sitepulse&sp_view=system#sitepulse-wp-admin' : 'admin.php?page=wpsp_sitepulse_settings#sitepulse-wp-admin' );
	}

	/**
	 * What the plugin's pages and the admin bar show.
	 *
	 * @return array label and tone (success, danger or neutral) of the badge, detail (the result
	 *               in plain words), message (the error's own words, or empty) and note (when the
	 *               result changed, or empty).
	 */
	public static function view() {
		$stored = self::stored();
		$last   = $stored['last'];
		if ( null === $last ) {
			return array(
				'label'   => __( 'Not checked yet', 'sitepulse' ),
				'tone'    => 'neutral',
				'detail'  => __( 'SitePulse checks every hour whether this server can reach its own wp-admin.', 'sitepulse' ),
				'message' => '',
				'note'    => '',
			);
		}

		$now     = time();
		$checked = strtotime( $last['checked_at'] );
		// The last check that found the opposite.
		$changed = null === $stored['previous'] ? 0 : strtotime( $stored['previous']['checked_at'] );
		if ( $last['reachable'] ) {
			$parts = array(
				$last['status'] >= 300
					/* translators: %d: HTTP status of /wp-admin/, a redirect to the login page */
					? sprintf( __( 'HTTP %d → login', 'sitepulse' ), $last['status'] )
					/* translators: %d: HTTP status of /wp-admin/ */
					: sprintf( __( 'HTTP %d', 'sitepulse' ), $last['status'] ),
				/* translators: %s: duration in milliseconds */
				sprintf( __( '%s ms', 'sitepulse' ), number_format_i18n( $last['ms'] ) ),
				/* translators: %s: human-readable time difference */
				sprintf( __( 'checked %s ago', 'sitepulse' ), human_time_diff( $checked, $now ) ),
			);

			return array(
				'label'   => __( 'Reachable', 'sitepulse' ),
				'tone'    => 'success',
				'detail'  => implode( ' · ', $parts ),
				'message' => '',
				// A problem of the last day is still worth knowing about.
				'note'    => $changed && $now - $changed < DAY_IN_SECONDS
					/* translators: %s: human-readable time difference */
					? sprintf( __( 'Was not reachable until %s ago.', 'sitepulse' ), human_time_diff( $changed, $now ) )
					: '',
			);
		}

		/* translators: %s: human-readable time difference */
		$note = sprintf( __( 'Checked %s ago.', 'sitepulse' ), human_time_diff( $checked, $now ) );
		if ( $changed ) {
			/* translators: %s: human-readable time difference */
			$note .= ' ' . sprintf( __( 'Was reachable until %s ago.', 'sitepulse' ), human_time_diff( $changed, $now ) );
		}

		return array(
			'label'   => __( 'Not reachable', 'sitepulse' ),
			'tone'    => 'danger',
			'detail'  => self::error_text( $last ),
			'message' => $last['message'] ?? '',
			'note'    => $note,
		);
	}

	/**
	 * @param array $result A result that is not reachable.
	 * @return string Why, in plain words.
	 */
	private static function error_text( array $result ) {
		$texts = array(
			/* translators: %d: seconds */
			'timeout'           => sprintf( __( 'wp-admin did not answer the server\'s own request within %d seconds.', 'sitepulse' ), self::TIMEOUT ),
			'connection_failed' => __( 'The server could not connect to its own wp-admin (loopback requests may be blocked by the host).', 'sitepulse' ),
			'fatal_error'       => __( 'A PHP fatal error stopped wp-admin or its login page.', 'sitepulse' ),
		);
		$error = (string) $result['error'];
		if ( isset( $texts[ $error ] ) ) {
			return $texts[ $error ];
		}

		// An HTTP status: of the login page when wp-admin answered as it should.
		return self::answered( $result['status'] )
			/* translators: %d: HTTP status */
			? sprintf( __( 'The login page answered HTTP %d instead of 200.', 'sitepulse' ), $result['login_status'] )
			/* translators: %d: HTTP status */
			: sprintf( __( 'wp-admin answered HTTP %d.', 'sitepulse' ), $result['status'] );
	}

	/**
	 * @param array $admin /wp-admin/, as Sitepulse_Remote_Health::fetch() loaded it.
	 * @param array $login The login page.
	 * @param int   $ms    How long both requests took.
	 * @return array The result, as the check-in reports it.
	 */
	private static function result( array $admin, array $login, $ms ) {
		$status       = (int) wp_remote_retrieve_response_code( $admin['response'] );
		$login_status = (int) wp_remote_retrieve_response_code( $login['response'] );
		$problem      = self::problem( $admin, self::answered( $status ) );
		$problem      = null === $problem ? self::problem( $login, 200 === $login_status ) : $problem;

		$result = array(
			'reachable'    => null === $problem,
			'status'       => $status,
			'login_status' => $login_status,
			'ms'           => $ms,
			'error'        => null === $problem ? null : $problem['error'],
			'checked_at'   => gmdate( 'Y-m-d\TH:i:s\Z' ),
		);
		if ( null !== $problem && '' !== $problem['message'] ) {
			$result['message'] = $problem['message'];
		}

		return $result;
	}

	/**
	 * @param array $page     A page, as Sitepulse_Remote_Health::fetch() loaded it.
	 * @param bool  $expected Whether its HTTP status is one a working site answers.
	 * @return array|null error and message (empty when there is none), or null when the page is fine.
	 */
	private static function problem( array $page, $expected ) {
		$response = $page['response'];
		if ( is_wp_error( $response ) ) {
			$message = $response->get_error_message();
			return array(
				// WordPress reports every transport error as http_request_failed; its message tells a timeout.
				'error'   => preg_match( '/cURL error 28\b|timed out|timeout/i', $message ) ? 'timeout' : 'connection_failed',
				'message' => self::shorten( $message ),
			);
		}
		if ( null !== $page['fatal'] ) {
			return array(
				'error'   => 'fatal_error',
				'message' => self::shorten( $page['fatal'] ),
			);
		}
		if ( ! $expected ) {
			return array(
				'error'   => 'http_' . (int) wp_remote_retrieve_response_code( $response ),
				'message' => '',
			);
		}

		return null;
	}

	/**
	 * @param int $status HTTP status of /wp-admin/.
	 * @return bool Whether a working wp-admin answers it: a page, or a redirect to the login page.
	 */
	private static function answered( $status ) {
		return $status >= 200 && $status < 400;
	}

	/**
	 * @param string $message An error message.
	 * @return string Its first 200 characters.
	 */
	private static function shorten( $message ) {
		return mb_substr( trim( $message ), 0, self::MESSAGE_LENGTH );
	}

	/**
	 * Store a result, keeping the last one that found the opposite, so the pages can say since when.
	 *
	 * @param array $result The new result.
	 */
	private static function save( array $result ) {
		$stored  = self::stored();
		$changed = null !== $stored['last'] && $stored['last']['reachable'] !== $result['reachable'];
		update_option(
			SITEPULSE_ADMIN_ACCESS_OPTION,
			array(
				'last'     => $result,
				'previous' => $changed ? $stored['last'] : $stored['previous'],
			),
			false
		);
	}

	/**
	 * @return array last and previous, the stored results; null when missing or unreadable.
	 */
	private static function stored() {
		$stored = get_option( SITEPULSE_ADMIN_ACCESS_OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();

		return array(
			'last'     => self::normalize( $stored['last'] ?? null ),
			'previous' => self::normalize( $stored['previous'] ?? null ),
		);
	}

	/**
	 * @param mixed $result A stored result.
	 * @return array|null The result with every field, or null when it is not one.
	 */
	private static function normalize( $result ) {
		if ( ! is_array( $result ) || ! is_bool( $result['reachable'] ?? null ) || ! is_string( $result['checked_at'] ?? null ) || false === strtotime( $result['checked_at'] ) ) {
			return null;
		}

		$normal = array(
			'reachable'    => $result['reachable'],
			'status'       => (int) ( $result['status'] ?? 0 ),
			'login_status' => (int) ( $result['login_status'] ?? 0 ),
			'ms'           => (int) ( $result['ms'] ?? 0 ),
			'error'        => is_string( $result['error'] ?? null ) ? $result['error'] : null,
			'checked_at'   => $result['checked_at'],
		);
		if ( is_string( $result['message'] ?? null ) && '' !== $result['message'] ) {
			$normal['message'] = $result['message'];
		}

		return $normal;
	}
}

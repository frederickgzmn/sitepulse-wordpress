<?php
/**
 * SitePulse Cron Fallback
 *
 * Provides a safe, low-frequency fallback runner when WP-Cron is not firing.
 * Detects overdue cron tasks and executes them on the shutdown hook,
 * so admin/frontend page loads are never delayed.
 *
 * Fires from both backend (admin_init) and frontend (template_redirect)
 * to recover overdue site status reports when traffic resumes.
 *
 * Performance strategy (3-tier caching):
 *  1. Static flag  — prevents duplicate work within the same PHP request (zero cost).
 *  2. Transient    — FRONTEND_CHECK_KEY throttles frontend checks to 1/hour.
 *  3. Transient    — HEALTHY_CACHE_KEY caches a "cron is fine" result for 5 min,
 *                    eliminating deeper DB checks (get_option, wp_next_scheduled).
 *
 * Worst-case DB cost per request when all caches are cold:
 *  Frontend: 1 get_transient (throttle) → return.  99.97% of requests stop here.
 *  Admin:    1 get_transient (healthy)  → return.  ~99% of admin loads stop here.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Cron_Fallback
 */
class Sitepulse_Cron_Fallback {

	/** Transient key for the execution lock. */
	private const DAILY_LOCK_KEY = 'sitepulse_cron_fallback_daily_lock';

	/** Transient key for the "cron is healthy" cache (avoids repeated checks). */
	private const HEALTHY_CACHE_KEY = 'sitepulse_cron_healthy';

	/** Transient key to throttle frontend checks. */
	private const FRONTEND_CHECK_KEY = 'sitepulse_cron_fallback_frontend_check';

	/** Seconds after scheduled time before the cron is considered stale (2 days). */
	private const DAILY_GRACE_SECONDS = 172800; // 2 days.

	/** Lock duration — prevents concurrent / repeated execution. */
	private const DAILY_LOCK_TTL = 600; // 10 minutes.

	/** How long to cache a "cron is fine" result to skip checks on subsequent requests. */
	private const HEALTHY_CACHE_TTL = 300; // 5 minutes.

	/** One-off hook kept for backwards compatibility with previously scheduled events. */
	private const IMMEDIATE_HOOK = 'sitepulse_cron_fallback_immediate';

	/**
	 * Static flag — ensures we run the check at most once per PHP request.
	 * This is the cheapest guard (zero DB cost, pure in-memory).
	 *
	 * @var bool
	 */
	private static $checked_this_request = false;

	/**
	 * Initialize fallback runner.
	 */
	public static function init() {
		// Backend: runs on every admin page load (gated by capability check).
		add_action( 'admin_init', array( __CLASS__, 'maybe_run_fallback' ), 20 );

		// Frontend: runs on public page loads (throttled to once per hour).
		add_action( 'template_redirect', array( __CLASS__, 'maybe_run_fallback_frontend' ) );

		// Keep the one-off hook registered for any events already in the DB
		// from a previous plugin version. These will self-clear after firing once.
		add_action( self::IMMEDIATE_HOOK, array( __CLASS__, 'execute_fallback_task' ) );
	}

	/**
	 * Backend fallback check — only for admins in the dashboard.
	 */
	public static function maybe_run_fallback() {
		// Static cache: already checked this request — zero cost return.
		if ( self::$checked_this_request ) {
			return;
		}

		// Skip during cron or AJAX requests.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return;
		}

		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
			return;
		}

		// Only run for admins in the dashboard.
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::run_common_checks();
	}

	/**
	 * Frontend fallback check — throttled, no capability requirement.
	 *
	 * Ensures the site status task fires even on sites with no admin visits.
	 */
	public static function maybe_run_fallback_frontend() {
		// Static cache: already checked this request — zero cost return.
		if ( self::$checked_this_request ) {
			return;
		}

		// Skip during cron requests.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return;
		}

		// Throttle frontend checks to once per hour.
		// This is the ONLY DB query for 99.97% of frontend page loads.
		if ( get_transient( self::FRONTEND_CHECK_KEY ) ) {
			return;
		}

		set_transient( self::FRONTEND_CHECK_KEY, 1, HOUR_IN_SECONDS );

		self::run_common_checks();
	}

	/**
	 * Shared checks for both backend and frontend entry points.
	 *
	 * DB cost breakdown (worst case, all caches cold):
	 *  - get_transient(HEALTHY_CACHE_KEY) .............. 1 query
	 *  - get_option('sitepulse_settings') via Settings .. 1 query (autoloaded)
	 *  - get_option('sitepulse_last_api_request') ....... 1 query
	 *  - wp_next_scheduled() ............................ 0 (cron array cached in memory)
	 *  - is_cron_system_failing() ....................... 1–2 queries (only when event is past-due)
	 *  - get_transient(DAILY_LOCK_KEY) .................. 1 query
	 *  Total worst case: ~6 queries, happens at most once every 5 minutes.
	 */
	private static function run_common_checks() {
		// Mark that we've already run the check for this PHP request.
		self::$checked_this_request = true;

		// Allow opt-out via filter.
		if ( ! apply_filters( 'sitepulse_enable_cron_fallback', true ) ) {
			return;
		}

		// Short-circuit: if we recently verified cron is healthy, skip all checks.
		// This single transient lookup eliminates ALL deeper checks for 5 minutes.
		if ( get_transient( self::HEALTHY_CACHE_KEY ) ) {
			return;
		}

		// Respect plugin's own "cron disabled" setting.
		if ( ! class_exists( 'Sitepulse_Settings' ) ) {
			require_once SITEPULSE_CLASS_PATH . 'settings.php';
		}

		if ( class_exists( 'Sitepulse_Settings' ) ) {
			$settings = Sitepulse_Settings::getInstance();
			if ( $settings->is_cron_disabled() ) {
				return;
			}
		}

		self::maybe_run_daily_api_fallback();
	}

	/**
	 * Run the site status API task if it is overdue or if the cron system is failing.
	 *
	 * Trigger conditions (any ONE of these fires the fallback):
	 *  1. SitePulse's own event is 2+ days overdue (DAILY_GRACE_SECONDS).
	 *  2. Event is 10+ min overdue AND the cron system is failing
	 *     (Action Scheduler has 10+ past-due actions, or WP-Cron queue is 1h+ stale).
	 *
	 * The last-run check (6h) always takes priority — if we already sent data
	 * within six hours, none of these conditions will trigger a duplicate send.
	 */
	private static function maybe_run_daily_api_fallback() {
		if ( ! class_exists( 'Sitepulse_Cron_Manager' ) ) {
			return;
		}

		// Check if the task already ran successfully within the last six hours.
		// This prevents double-sends when DISABLE_WP_CRON is true but a system
		// cron is handling execution, or when both backend and frontend trigger.
		// Note: the stored timestamp uses current_time('timestamp') (local offset),
		// so we compare with the same function for consistency.
		$last_run = get_option( 'sitepulse_last_api_request', 0 );
		if ( is_numeric( $last_run ) && $last_run > 0 && ( current_time( 'timestamp' ) - (int) $last_run ) < Sitepulse_Cron_Manager::API_INTERVAL_SECONDS ) {
			set_transient( self::HEALTHY_CACHE_KEY, 1, self::HEALTHY_CACHE_TTL );
			return;
		}

		$next_timestamp = Sitepulse_Cron_Manager::get_next_scheduled_time();

		// If nothing is scheduled at all, re-schedule and bail.
		if ( ! $next_timestamp ) {
			Sitepulse_Cron_Manager::schedule_daily_api_request();
			return;
		}

		// wp_next_scheduled() returns a UTC unix timestamp — compare with UTC time().
		$now     = time();
		$overdue = $now - $next_timestamp;

		// Determine whether to fire the fallback.
		$should_fire = false;
		$trigger     = '';

		if ( $overdue >= self::DAILY_GRACE_SECONDS ) {
			// Condition 1: Our own event is 2+ days overdue — definitely fire.
			$should_fire = true;
			$trigger     = 'overdue';
		} elseif ( $overdue > 600 && self::is_cron_system_failing() ) {
			// Condition 2: Event is at least 10 min past-due AND the broader
			// cron system is clearly broken (AS past-due or WP-Cron stale).
			// Don't wait 2 days when evidence already proves cron is dead.
			$should_fire = true;
			$trigger     = 'cron_system_failing';
		}

		if ( ! $should_fire ) {
			// Cron looks healthy enough — cache and skip for 5 minutes.
			set_transient( self::HEALTHY_CACHE_KEY, 1, self::HEALTHY_CACHE_TTL );
			return;
		}

		// Already processing — don't queue again.
		if ( self::is_locked( self::DAILY_LOCK_KEY ) ) {
			return;
		}

		// Acquire lock before executing.
		self::set_lock( self::DAILY_LOCK_KEY, self::DAILY_LOCK_TTL );

		// Execute on shutdown to avoid blocking the current page render.
		// This is intentionally NOT delegated to spawn_cron() because the whole
		// point of this fallback is that WP-Cron may be broken/unreachable.
		add_action( 'shutdown', array( __CLASS__, 'execute_fallback_task' ) );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'[SitePulse] Cron fallback triggered (%s): site status task was %d seconds overdue.',
				$trigger,
				$overdue
			) );
		}
	}

	// =========================================================================
	// Cron system health detection
	// =========================================================================

	/**
	 * Detect if the WordPress cron system is failing system-wide.
	 *
	 * Checks two independent signals:
	 *  1. Action Scheduler (WooCommerce, etc.) has 10+ past-due pending actions.
	 *  2. The core WP-Cron queue has events 1+ hour overdue.
	 *
	 * Either condition alone is a strong signal that cron is broken.
	 * This is only called when our own event is at least 10 min past-due,
	 * and at most once every 5 min (gated by HEALTHY_CACHE_KEY).
	 *
	 * @return bool True if the cron system appears to be failing.
	 */
	private static function is_cron_system_failing() {
		// Check Action Scheduler first (strongest signal, most common).
		if ( self::is_action_scheduler_failing() ) {
			return true;
		}

		// Check core WP-Cron queue for stale events.
		if ( self::is_wp_cron_stale() ) {
			return true;
		}

		return false;
	}

	/**
	 * Detect if Action Scheduler has a significant number of past-due actions.
	 *
	 * Action Scheduler (bundled with WooCommerce, WP Mail SMTP, and many others)
	 * processes its queue via WP-Cron. If it has many past-due pending actions,
	 * the entire cron system is broken — not just SitePulse's event.
	 *
	 * Uses a capped query (per_page) to avoid expensive full-table scans.
	 *
	 * @param int $threshold Minimum past-due actions to consider a failure. Default 10.
	 * @return bool
	 */
	private static function is_action_scheduler_failing( $threshold = 10 ) {
		// Action Scheduler not installed or not fully loaded yet.
		if ( ! class_exists( 'ActionScheduler_Store' ) ) {
			return false;
		}

		try {
			$store = ActionScheduler_Store::instance();

			// Query for pending actions whose scheduled time has already passed.
			// We only fetch ($threshold + 1) IDs — enough to know whether
			// the count exceeds the threshold without scanning the full table.
			$past_due_ids = $store->query_actions( array(
				'status'       => ActionScheduler_Store::STATUS_PENDING,
				'date'         => new DateTime( 'now', new DateTimeZone( 'UTC' ) ),
				'date_compare' => '<=',
				'per_page'     => $threshold + 1,
			), 'ids' );

			return is_array( $past_due_ids ) && count( $past_due_ids ) > $threshold;
		} catch ( Exception $e ) {
			// If AS throws, don't let it crash the fallback.
			return false;
		}
	}

	/**
	 * Detect if the core WP-Cron queue has significantly stale events.
	 *
	 * Checks the earliest timestamp in the cron array. If it's more than
	 * 1 hour overdue, WP-Cron is not processing events at all.
	 *
	 * Uses _get_cron_array() which is cached in memory after the first call,
	 * so this adds zero DB queries.
	 *
	 * @return bool
	 */
	private static function is_wp_cron_stale() {
		$cron_array = _get_cron_array();

		if ( ! is_array( $cron_array ) || empty( $cron_array ) ) {
			return false;
		}

		$timestamps = array_keys( $cron_array );
		$earliest = min( $timestamps );
		$overdue  = time() - $earliest;

		// 1 hour overdue = WP-Cron is clearly not processing.
		return $overdue > HOUR_IN_SECONDS;
	}

	/**
	 * Execute the actual fallback task.
	 *
	 * Runs on the 'shutdown' hook (or via the legacy one-off cron event),
	 * NOT inline during the page render.
	 *
	 * Calls close_connection() first so the browser receives its response
	 * immediately — the API call runs in the background without tying up
	 * the user's perceived page load time.
	 */
	public static function execute_fallback_task() {
		if ( ! class_exists( 'Sitepulse_Cron_Manager' ) ) {
			return;
		}

		// Release the connection to the browser BEFORE the API call.
		// This frees the PHP worker from the user's perspective while we
		// continue processing in the background.
		self::close_connection();

		$result = Sitepulse_Cron_Manager::send_daily_api_request();

		// Only reschedule if the API call didn't produce an explicit failure.
		$is_failure = is_array( $result ) && isset( $result['success'] ) && false === $result['success'];

		if ( ! $is_failure ) {
			Sitepulse_Cron_Manager::schedule_daily_api_request();
		} else {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$msg = is_array( $result ) && isset( $result['message'] ) ? $result['message'] : 'unknown error';
				error_log( '[SitePulse] Cron fallback API call failed: ' . $msg );
			}
		}

		// Release lock after execution completes.
		delete_transient( self::DAILY_LOCK_KEY );
	}

	/**
	 * Close the HTTP connection to the client so the browser gets its response
	 * immediately, then continue executing PHP in the background.
	 *
	 * Uses fastcgi_finish_request() on PHP-FPM (most modern hosts), or falls
	 * back to manual output flushing + Connection: close headers for Apache/CGI.
	 *
	 * This ensures the 30–60s API call does NOT hold the user's page load open.
	 */
	private static function close_connection() {
		// Prevent PHP from aborting if the client disconnects.
		ignore_user_abort( true );

		// Extend execution time for the background API call.
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 120 );
		}

		// PHP-FPM: the gold standard — flushes all output and closes the
		// connection in a single call. Available on most modern hosting.
		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
			return;
		}

		// Apache / mod_php / CGI fallback: manually send headers + flush.
		if ( ! headers_sent() ) {
			header( 'Connection: close' );
			header( 'Content-Length: ' . ob_get_length() );
		}

		// Flush all output buffers.
		$levels = ob_get_level();
		for ( $i = 0; $i < $levels; $i++ ) {
			ob_end_flush();
		}

		flush();
	}

	/**
	 * Check if a fallback lock is active.
	 *
	 * @param string $key Transient key.
	 * @return bool
	 */
	private static function is_locked( $key ) {
		return (bool) get_transient( $key );
	}

	/**
	 * Set a fallback lock.
	 *
	 * @param string $key Transient key.
	 * @param int    $ttl Lock duration in seconds.
	 */
	private static function set_lock( $key, $ttl ) {
		set_transient( $key, 1, $ttl );
	}
}

Sitepulse_Cron_Fallback::init();

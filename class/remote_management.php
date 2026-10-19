<?php
/**
 * SitePulse remote management
 *
 * Lets the SitePulse dashboard (api.sitepulse.me) watch, and step by step manage, the
 * site's updates and backups. The site checks in on a schedule the API sets; the API
 * answers with the dashboard switch and the commands queued for the site. Nothing runs
 * unless the dashboard switch is on (the site's connection key turns it on) and the site's
 * administrator has not blocked remote management; backups also need UpdraftPlus or
 * BackWPup. docs/remote-management.md describes the contract.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Remote_Management
 */
class Sitepulse_Remote_Management {

	/** Cron hook of the check-in. */
	const CHECK_IN_HOOK = 'sitepulse_remote_check_in';

	/** Seconds since the last check-in a wake asked for before a wake from the API checks in again. */
	const WOKEN_CHECK_IN_GAP = 60;

	/** Seconds a check-in may be late before an administrator's wp-admin page checks in instead. */
	const LATE_CHECK_IN_SECONDS = 600;

	/** Held while a late check-in runs, so other pages do not check in too. */
	const LATE_LOCK = 'sitepulse_remote_late_check_in';

	/** AJAX action and nonce the settings page asks, while it shows a key, whether the key was used. */
	const POLL_ACTION = 'sitepulse_remote_connection_poll';

	/** admin-post action and nonce of the block switch. */
	const BLOCK_ACTION = 'sitepulse_remote_management';

	/** admin-post action and nonce of the connection buttons. */
	const CONNECTION_ACTION = 'sitepulse_remote_connection';

	/** The site's connection key until it is used, and what SitePulse last said about its accounts. */
	const CONNECTION_OPTION = 'sitepulse_remote_connection';

	/** What the site knows about remote management. */
	const STATE_OPTION = 'sitepulse_remote_state';

	/** The key that signs remote management requests once the dashboard rotated it; the license until then. */
	const KEY_OPTION = 'sitepulse_remote_key';

	/** Events not delivered yet. */
	const EVENTS_OPTION = 'sitepulse_remote_events';

	/** Activity shown in the settings. */
	const LOG_OPTION = 'sitepulse_remote_log';

	/** Version of the request bodies. */
	const SCHEMA = 1;

	/** A SitePulse license: five groups of four hexadecimal characters, as a regular expression without delimiters (also the field's pattern attribute). */
	const LICENSE_PATTERN = '[0-9A-Fa-f]{4}(?:-[0-9A-Fa-f]{4}){4}';

	/** The API's codes for a refused site request (see "Signing" in docs/remote-management.md). */
	const REFUSALS = array( 'malformed_headers', 'request_expired', 'invalid_signature', 'request_replayed', 'site_blocked' );

	/** What this version runs beyond the base commands, reported at check-in: the API offers safe updates only to sites that report safe_update. */
	const FEATURES = array( 'safe_update' );

	/** Seconds until the first check-in once the site can connect. */
	const FIRST_CHECK_IN_DELAY = 60;

	/** Undelivered events kept; the oldest are dropped first. */
	const MAX_EVENTS = 200;

	/** Events per request. */
	const EVENTS_PER_REQUEST = 50;

	/** Activity entries kept. */
	const MAX_LOG = 100;

	/** Activity entries shown. */
	const SHOWN_LOG = 20;

	/** Commands run per check-in. */
	const MAX_COMMANDS = 20;

	/** Command ids remembered, so a command never runs twice. */
	const MAX_DONE_COMMANDS = 50;

	/** Stored state; the API sets the switch and the intervals at every check-in. */
	const STATE_DEFAULTS = array(
		'enabled'            => false,
		'active'             => false,
		'check_in_seconds'   => 3600,
		'inventory_seconds'  => 21600,
		'last_check_in'      => 0,
		'last_woken'         => 0,
		'last_error'         => '',
		'last_error_message' => '',
		'last_refusal'       => '',
		'inventory_sent_at'  => 0,
		'inventory_due'      => false,
		'provider'           => null,
		'preferred_provider' => null,
		'done_commands'      => array(),
		'pending_results'    => array(),
	);

	/**
	 * Register the check-in, the settings and the listeners.
	 */
	public static function init() {
		add_action( self::CHECK_IN_HOOK, array( __CLASS__, 'check_in' ) );
		add_action( 'admin_post_' . self::BLOCK_ACTION, array( __CLASS__, 'handle_block_form' ) );
		add_action( 'admin_post_' . self::CONNECTION_ACTION, array( __CLASS__, 'handle_connection_form' ) );
		add_action( 'wp_ajax_' . self::POLL_ACTION, array( __CLASS__, 'ajax_connection_poll' ) );
		add_action( 'admin_init', array( __CLASS__, 'check_in_when_late' ) );
		add_action( 'sitepulse_settings_after_form', array( __CLASS__, 'render_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SITEPULSE_PLUGIN_FILE ), array( __CLASS__, 'plugin_links' ) );
		Sitepulse_Remote_Events::init();
		Sitepulse_Remote_Backups::init();
		Sitepulse_Remote_Updates::init();
		Sitepulse_Remote_Recovery::init();

		// Keep the check-in scheduled from dashboard and cron requests; page views skip the check.
		if ( is_admin() || wp_doing_cron() ) {
			self::ensure_schedule();
		}
		if ( wp_doing_cron() ) {
			self::check_in_when_woken();
		}
	}

	/**
	 * The API requests wp-cron.php?sitepulse_check_in=1 when the dashboard queued work for this
	 * site or just set it up: check in during that cron run rather than at the next scheduled
	 * check-in. Anyone can request that address, so it only moves the scheduled check-in forward,
	 * at most once a minute.
	 */
	private static function check_in_when_woken() {
		// A flag that only moves a check-in forward; there is nothing to verify.
		if ( ! isset( $_GET['sitepulse_check_in'] ) || ! self::can_connect() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$next = wp_next_scheduled( self::CHECK_IN_HOOK );
		if ( false !== $next && $next <= time() ) {
			return;
		}
		$state = self::state();
		if ( time() - (int) $state['last_woken'] < self::WOKEN_CHECK_IN_GAP ) {
			return;
		}
		$state['last_woken'] = time();
		self::save_state( $state );
		self::schedule_next( 0 );
	}

	/**
	 * WP-Cron runs only when the site gets visitors or a server cron job requests it. When the
	 * scheduled check-in is 10 minutes late, an administrator's wp-admin page checks in once it
	 * is sent, so the dashboard keeps reaching a site whose WP-Cron does not run.
	 */
	public static function check_in_when_late() {
		if ( wp_doing_ajax() || wp_doing_cron() || ! current_user_can( 'manage_options' ) || ! self::can_connect() ) {
			return;
		}
		$next = wp_next_scheduled( self::CHECK_IN_HOOK );
		if ( false === $next || time() - $next < self::LATE_CHECK_IN_SECONDS || get_transient( self::LATE_LOCK ) ) {
			return;
		}

		set_transient( self::LATE_LOCK, 1, self::LATE_CHECK_IN_SECONDS );
		add_action( 'shutdown', array( __CLASS__, 'check_in_after_response' ) );
	}

	/**
	 * Check in once the page is sent, so the administrator does not wait for it.
	 */
	public static function check_in_after_response() {
		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		}
		self::check_in();
	}

	/**
	 * Schedule the check-in when the site can connect, and remove it when it cannot.
	 */
	public static function ensure_schedule() {
		$scheduled = wp_next_scheduled( self::CHECK_IN_HOOK );
		if ( ! self::can_connect() ) {
			if ( $scheduled ) {
				self::unschedule();
			}
			return;
		}

		if ( ! $scheduled ) {
			wp_schedule_single_event( time() + self::FIRST_CHECK_IN_DELAY, self::CHECK_IN_HOOK );
		}
	}

	/**
	 * Stop checking in; the plugin is being deactivated or can no longer connect.
	 */
	public static function unschedule() {
		wp_clear_scheduled_hook( self::CHECK_IN_HOOK );
	}

	/**
	 * Whether the site may talk to the API: a single site with a SitePulse license and
	 * Cloud Services on. SitePulse Pro brings its own connection.
	 *
	 * @return bool
	 */
	public static function can_connect() {
		return self::can_register() && '' !== self::license();
	}

	/**
	 * @return bool Whether the site may connect once it has a license: a single site without
	 *              SitePulse Pro, with Cloud Services on. Asking for a connection key registers it.
	 */
	private static function can_register() {
		return ! SITEPULSE_PRO_IS_ACTIVE && ! is_multisite() && self::cloud_services_enabled();
	}

	/**
	 * @return bool Whether the administrator blocked remote management on this site.
	 */
	public static function is_blocked() {
		$settings = get_option( 'sitepulse_settings', array() );
		return is_array( $settings ) && ! empty( $settings['remote_management_blocked'] );
	}

	/**
	 * @return bool Whether the dashboard manages the site right now.
	 */
	public static function is_active() {
		return self::state()['active'] && ! self::is_blocked() && self::can_connect();
	}

	/**
	 * @return array Stored state with defaults for anything missing.
	 */
	public static function state() {
		$stored = get_option( self::STATE_OPTION, array() );
		$state  = array_merge( self::STATE_DEFAULTS, is_array( $stored ) ? array_intersect_key( $stored, self::STATE_DEFAULTS ) : array() );

		$state['active']        = (bool) $state['active'];
		$state['done_commands']   = is_array( $state['done_commands'] ) ? $state['done_commands'] : array();
		$state['pending_results'] = is_array( $state['pending_results'] ) ? array_values( array_filter( $state['pending_results'], 'is_array' ) ) : array();
		return $state;
	}

	/**
	 * How WP-Cron runs here: the API requests this wp-cron.php to wake a site whose cron does not
	 * run by itself (no visitors, or DISABLE_WP_CRON without a server cron job), and the dashboard
	 * says when it does not run.
	 *
	 * @return array{url: string, disabled: bool, late: int} late: seconds the most overdue task has waited.
	 */
	private static function cron_health() {
		$now  = time();
		$late = 0;
		foreach ( array_keys( (array) _get_cron_array() ) as $timestamp ) {
			$late = max( $late, $now - (int) $timestamp );
		}

		return array(
			'url'      => site_url( 'wp-cron.php' ),
			'disabled' => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON,
			'late'     => $late,
		);
	}

	/**
	 * Check in with the API: report the site, then, while the dashboard manages the
	 * site, run the commands, send the inventory when it is due and deliver events.
	 */
	public static function check_in() {
		if ( ! self::can_connect() ) {
			self::unschedule();
			return;
		}

		$state    = self::state();
		$client   = new Sitepulse_Remote_Client( self::signing_key() );
		$blocked  = self::is_blocked();
		$provider = Sitepulse_Remote_Backups::provider();
		$payload  = array(
			'schema'           => self::SCHEMA,
			'plugin_version'   => SITEPULSE_VERSION,
			'wp_version'       => (string) get_bloginfo( 'version' ),
			'blocked'          => $blocked,
			'backup_provider'  => $provider,
			'backup_providers' => Sitepulse_Remote_Backups::providers(),
			'cron'             => self::cron_health(),
		);
		// Whether the site's own server can reach its wp-admin; nothing before the first check.
		$wp_admin = Sitepulse_Admin_Access::report();
		if ( null !== $wp_admin ) {
			$payload['wp_admin'] = $wp_admin;
		}
		$payload['features'] = self::FEATURES;
		$response            = $client->post( 'check-in', $payload );

		if ( is_wp_error( $response ) ) {
			$reason  = self::connection_error_message( $response );
			$refusal = self::refusal( $response );
			// Log a problem once, not at every check-in.
			if ( $state['last_error'] !== $response->get_error_code() || $state['last_refusal'] !== $refusal ) {
				/* translators: %s: why the check-in failed */
				self::log( sprintf( __( 'Check-in with SitePulse failed: %s', 'sitepulse' ), $reason ) );
			}
			$state['last_error']         = $response->get_error_code();
			$state['last_error_message'] = $reason;
			$state['last_refusal']       = $refusal;
			self::save_state( $state );
			self::schedule_next( (int) $state['check_in_seconds'] );
			return;
		}

		$remote     = is_array( $response['remote_management'] ?? null ) ? $response['remote_management'] : array();
		$was_active = $state['active'];

		$state['enabled']           = ! empty( $remote['enabled'] );
		$state['active']            = ! empty( $remote['active'] ) && ! $blocked;
		$state['check_in_seconds']  = self::clamp( $remote['check_in_seconds'] ?? self::STATE_DEFAULTS['check_in_seconds'], MINUTE_IN_SECONDS, DAY_IN_SECONDS );
		$state['inventory_seconds'] = self::clamp( $remote['inventory_seconds'] ?? self::STATE_DEFAULTS['inventory_seconds'], HOUR_IN_SECONDS, WEEK_IN_SECONDS );
		$state['last_check_in']     = time();
		$state['last_error']        = '';
		$state['last_error_message'] = '';
		$state['last_refusal']       = '';

		// The backup plugin picked in the dashboard applies from this check-in on.
		$preferred                   = $remote['backup_provider'] ?? null;
		$state['preferred_provider'] = in_array( $preferred, array( 'updraftplus', 'backwpup' ), true ) ? $preferred : null;
		self::save_state( $state );
		$client = self::rotate_key( $client, $remote['new_key'] ?? null );
		$provider = Sitepulse_Remote_Backups::provider();

		if ( $state['active'] !== $was_active ) {
			self::log( $state['active'] ? __( 'Remote management turned on from the SitePulse dashboard.', 'sitepulse' ) : __( 'Remote management turned off.', 'sitepulse' ) );
			$state['inventory_due'] = $state['inventory_due'] || $state['active'];
			if ( ! $state['active'] ) {
				Sitepulse_Remote_Activity::reset();
			}
		}
		if ( $was_active && $state['active'] && $provider !== $state['provider'] ) {
			self::queue_event( 'backup_provider.changed', array( 'provider' => $provider ) );
			$state['inventory_due'] = true;
		}
		$state['provider'] = $provider;

		self::send_pending_results( $client, $state );
		if ( $state['active'] ) {
			// Commands first, so the inventory shows what they changed.
			self::run_commands( $client, $state, $response['commands'] ?? array() );
			if ( $state['inventory_due'] || time() - (int) $state['inventory_sent_at'] >= $state['inventory_seconds'] ) {
				self::send_inventory( $client, $state );
			}
			foreach ( Sitepulse_Remote_Activity::collect() as $event ) {
				self::queue_event( $event[0], $event[1] );
			}
			self::send_events( $client );
		}

		self::save_state( $state );
		self::schedule_next( $state['check_in_seconds'] );
	}

	/**
	 * Queue an event for the dashboard; events are recorded only while it manages the site.
	 *
	 * @param string $type One of the event types of the contract.
	 * @param array  $data Event details.
	 */
	public static function record_event( $type, array $data ) {
		if ( ! self::is_active() ) {
			return;
		}

		self::queue_event( $type, $data );
		self::inventory_changed();
	}

	/**
	 * Send a fresh inventory at the next check-in.
	 */
	public static function inventory_changed() {
		if ( ! self::is_active() ) {
			return;
		}

		$state                  = self::state();
		$state['inventory_due'] = true;
		self::save_state( $state );
	}

	/**
	 * @return array Activity entries, oldest first.
	 */
	public static function log_entries() {
		$log = get_option( self::LOG_OPTION, array() );
		return array_values(
			array_filter(
				is_array( $log ) ? $log : array(),
				static function ( $entry ) {
					return is_array( $entry ) && isset( $entry['time'], $entry['message'] );
				}
			)
		);
	}

	/**
	 * Save the block switch from the settings.
	 */
	public static function handle_block_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sitepulse' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::BLOCK_ACTION );

		$blocked = ! empty( $_POST['sitepulse_remote_blocked'] );
		if ( $blocked !== self::is_blocked() ) {
			require_once SITEPULSE_CLASS_PATH . 'settings.php';
			Sitepulse_Settings::getInstance()->update_settings( array( 'remote_management_blocked' => $blocked ) );
			self::log( $blocked ? __( 'Remote management blocked on this site.', 'sitepulse' ) : __( 'Remote management unblocked on this site.', 'sitepulse' ) );
			// Tell the dashboard now instead of at the next scheduled check-in.
			if ( self::can_connect() ) {
				self::schedule_next( 0 );
			}
		}

		$referer = wp_get_referer();
		wp_safe_redirect( $referer ? $referer : admin_url( 'admin.php?page=wpsp_sitepulse_settings' ) );
		exit;
	}

	/**
	 * The connection buttons: get a connection key, check the connection, disconnect the site
	 * from its SitePulse accounts (see "Connecting a site to an account" in docs/remote-management.md),
	 * or set the site's license.
	 */
	public static function handle_connection_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sitepulse' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::CONNECTION_ACTION );

		$operation = isset( $_POST['sitepulse_connection'] ) ? sanitize_key( wp_unslash( $_POST['sitepulse_connection'] ) ) : '';
		// Asking for a key registers a site and a license can be set without one; the other buttons need it.
		$allowed = in_array( $operation, array( 'key', 'license' ), true ) ? self::can_register() : self::can_connect();
		if ( 'license' === $operation && $allowed ) {
			$license = isset( $_POST['sitepulse_license'] ) ? sanitize_text_field( wp_unslash( $_POST['sitepulse_license'] ) ) : '';
			update_option( self::CONNECTION_OPTION, self::license_request( $license ), false );
		} elseif ( in_array( $operation, array( 'key', 'status', 'disconnect' ), true ) && $allowed ) {
			update_option( self::CONNECTION_OPTION, self::connection_request( $operation ), false );
		}

		$referer = wp_get_referer();
		wp_safe_redirect( $referer ? $referer : admin_url( 'admin.php?page=wpsp_sitepulse_settings' ) );
		exit;
	}

	/**
	 * Press a connection button and return the connection to store. Asking for a key registers
	 * a site that has no license yet and checks in first, so the API can wake the site as soon as
	 * the key is used; the other buttons check in afterwards, so the settings show the result.
	 *
	 * @param string $operation key, status or disconnect.
	 * @return array
	 */
	private static function connection_request( $operation ) {
		$stored = array_merge( self::connection(), array( 'error_code' => '', 'notice' => '' ) );
		if ( 'key' === $operation ) {
			$license = Sitepulse_Api_Service::ensure_registered();
			if ( empty( $license['success'] ) ) {
				$stored['error'] = (string) $license['message'];
				return $stored;
			}
			self::check_in();
		}

		$endpoints = array(
			'key'        => 'connection/key',
			'status'     => 'connection',
			'disconnect' => 'connection/disconnect',
		);
		$response  = ( new Sitepulse_Remote_Client( self::signing_key() ) )->post( $endpoints[ $operation ], array() );
		if ( is_wp_error( $response ) ) {
			$stored['error']      = self::connection_error_message( $response );
			$stored['error_code'] = self::refusal( $response );
			return $stored;
		}

		$stored = self::connection_from( is_array( $response['data'] ?? null ) ? $response['data'] : array(), $stored );
		if ( 'disconnect' === $operation ) {
			self::log( __( 'This site was disconnected from its SitePulse accounts.', 'sitepulse' ) );
		}
		if ( 'key' !== $operation ) {
			self::check_in();
		}
		return $stored;
	}

	/**
	 * Set the site's license from the connection box, for a site whose license no longer matches
	 * SitePulse's record for its domain: such a site cannot disconnect or register again by itself.
	 * The site checks in with the new license, without the key the dashboard may have rotated (it
	 * was issued with the old license). The license is kept only when SitePulse accepts it: after a
	 * refusal, the license, the key and the state go back to what they were, so a wrong license
	 * never stops a site that worked.
	 *
	 * @param string $license The license entered.
	 * @return array The connection to store.
	 */
	private static function license_request( $license ) {
		$stored  = array_merge( self::connection(), array( 'error_code' => 'license', 'notice' => '' ) );
		$license = strtoupper( trim( $license ) );
		if ( 1 !== preg_match( '/^' . self::LICENSE_PATTERN . '$/D', $license ) ) {
			$stored['error'] = __( 'That is not a SitePulse license. A license is five groups of four characters (0 to 9 and A to F) separated by dashes, such as 1A2B-3C4D-5E6F-7A8B-9C0D.', 'sitepulse' );
			return $stored;
		}

		$previous = array();
		foreach ( array( 'sitepulse_license_key', self::KEY_OPTION, self::STATE_OPTION ) as $option ) {
			$previous[ $option ] = get_option( $option );
		}
		update_option( 'sitepulse_license_key', $license, false );
		delete_option( self::KEY_OPTION );
		self::check_in();

		$state = self::state();
		if ( '' === $state['last_error'] ) {
			self::log( __( 'The SitePulse license of this site was changed in its settings.', 'sitepulse' ) );
			$stored['error']      = '';
			$stored['error_code'] = '';
			$stored['notice']     = __( 'The license was saved and the site checked in.', 'sitepulse' );
			return $stored;
		}

		foreach ( $previous as $option => $value ) {
			if ( false === $value ) {
				delete_option( $option );
			} else {
				update_option( $option, $value, false );
			}
		}
		$stored['error'] = 'invalid_signature' === $state['last_refusal']
			? __( 'SitePulse does not accept that license for this site, so it was not saved. Enter the license your SitePulse dashboard shows for this site.', 'sitepulse' )
			/* translators: %s: why the check-in failed */
			: sprintf( __( 'The license was not saved: SitePulse could not check it. %s', 'sitepulse' ), $state['last_error_message'] );
		return $stored;
	}

	/**
	 * While the settings show a connection key, the page asks every few seconds whether the key
	 * was used. Once it was, the site checks in at once, so it is set up without waiting for its
	 * schedule, even when WP-Cron does not run or the API cannot reach the site.
	 */
	public static function ajax_connection_poll() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( self::POLL_ACTION, 'nonce', false ) ) {
			wp_send_json_error( null, 403 );
		}

		$stored = self::connection();
		if ( '' === $stored['key'] || ! self::can_connect() ) {
			wp_send_json_success( array( 'used' => '' === $stored['key'] ) );
		}
		$response = ( new Sitepulse_Remote_Client( self::signing_key() ) )->post( 'connection', array() );
		if ( is_wp_error( $response ) ) {
			wp_send_json_success( array( 'used' => false ) );
		}

		$stored = self::connection_from( is_array( $response['data'] ?? null ) ? $response['data'] : array(), $stored );
		update_option( self::CONNECTION_OPTION, $stored, false );
		if ( '' === $stored['key'] ) {
			self::check_in();
		}
		wp_send_json_success( array( 'used' => '' === $stored['key'] ) );
	}

	/**
	 * A "Connection key" link on the Plugins screen, as ManageWP's Worker has, to the
	 * remote management settings.
	 *
	 * @param array $links The plugin's action links.
	 * @return array
	 */
	public static function plugin_links( $links ) {
		if ( ! self::can_register() || ! current_user_can( 'manage_options' ) ) {
			return $links;
		}

		$url = admin_url( 'admin.php?page=wpsp_sitepulse_settings#sitepulse-remote-connection' );
		return array_merge( array( 'sitepulse-connection' => '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Connection key', 'sitepulse' ) . '</a>' ), (array) $links );
	}

	/**
	 * @return array The stored connection, with defaults for anything missing.
	 */
	private static function connection() {
		$stored = get_option( self::CONNECTION_OPTION, array() );
		return array_merge(
			array(
				'key'         => '',
				'connectable' => true,
				'connections' => array(),
				'checked_at'  => 0,
				'error'       => '',
				// What the error is about: one of the API's refusal codes, license, or empty.
				'error_code'  => '',
				'notice'      => '',
			),
			is_array( $stored ) ? $stored : array()
		);
	}

	/**
	 * The connection as SitePulse described it. A new key replaces the stored one; the stored key
	 * is kept while SitePulse still has a key for the site, and dropped once it was used.
	 *
	 * @param array $data   The response's data.
	 * @param array $stored The stored connection.
	 * @return array
	 */
	private static function connection_from( array $data, array $stored ) {
		$connections = array();
		foreach ( is_array( $data['connections'] ?? null ) ? $data['connections'] : array() as $connection ) {
			if ( is_array( $connection ) ) {
				$connections[] = array(
					'account'      => is_string( $connection['account'] ?? null ) ? $connection['account'] : '',
					'plan'         => is_string( $connection['plan'] ?? null ) ? $connection['plan'] : '',
					'connected_at' => is_string( $connection['connected_at'] ?? null ) ? $connection['connected_at'] : '',
				);
			}
		}

		if ( is_string( $data['key'] ?? null ) ) {
			$key = $data['key'];
		} else {
			$key = empty( $data['has_key'] ) ? '' : $stored['key'];
		}

		return array(
			'key'         => $key,
			'connectable' => ! isset( $data['connectable'] ) || (bool) $data['connectable'],
			'connections' => $connections,
			'checked_at'  => time(),
			'error'       => '',
			'error_code'  => '',
			'notice'      => '',
		);
	}

	/**
	 * Show the remote management settings below the SitePulse settings form.
	 *
	 * @param string $context easy for the Simple dashboard, advanced otherwise.
	 */
	public static function render_settings( $context = 'advanced' ) {
		if ( SITEPULSE_PRO_IS_ACTIVE || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$sp_remote                          = self::view_data();
		$sp_remote['connection']['button'] = 'easy' === $context ? 'sp-btn sp-btn-outline sp-btn-sm' : 'button';
		if ( '' !== $sp_remote['connection']['key'] ) {
			wp_enqueue_script( 'sitepulse-remote-connection', SITEPULSE_ADMIN_ASSETS_JS_URL . 'remote-connection.js', array(), SITEPULSE_VERSION, true );
		}
		include SITEPULSE_PATH . ( 'easy' === $context ? 'templates/easy/partials/remote-management.php' : 'templates/backend/remote_management.php' );
	}

	/**
	 * @return array What the settings show.
	 */
	public static function view_data() {
		$state    = self::state();
		$provider = Sitepulse_Remote_Backups::provider();
		$status   = self::status( $state, $provider );
		$badges   = array(
			'active'      => array( __( 'Active', 'sitepulse' ), 'success' ),
			'off'         => array( __( 'Off', 'sitepulse' ), 'neutral' ),
			'blocked'     => array( __( 'Blocked', 'sitepulse' ), 'warning' ),
			'error'       => array( __( 'Connection problem', 'sitepulse' ), 'danger' ),
			'unavailable' => array( __( 'Unavailable', 'sitepulse' ), 'neutral' ),
		);
		$format   = get_option( 'date_format', 'Y-m-d' ) . ' ' . get_option( 'time_format', 'H:i' );

		return array(
			'status'        => $status[0],
			'label'         => $badges[ $status[0] ][0],
			'tone'          => $badges[ $status[0] ][1],
			'message'       => $status[1],
			'provider'      => self::backups_text( $provider ),
			/* translators: %s: human-readable time difference */
			'last_check_in' => $state['last_check_in'] ? sprintf( __( '%s ago', 'sitepulse' ), human_time_diff( (int) $state['last_check_in'], time() ) ) : __( 'Never', 'sitepulse' ),
			'blocked'       => self::is_blocked(),
			'log'           => array_map(
				static function ( $entry ) use ( $format ) {
					return array(
						'when'    => wp_date( $format, (int) $entry['time'] ),
						'message' => (string) $entry['message'],
					);
				},
				array_reverse( array_slice( self::log_entries(), -self::SHOWN_LOG ) )
			),
			'action'        => admin_url( 'admin-post.php' ),
			'nonce_action'  => self::BLOCK_ACTION,
			'connection'    => self::connection_view( $format ),
		);
	}

	/**
	 * @param array|null $provider Active backup plugin.
	 * @return string What the settings say about the backup plugin.
	 */
	private static function backups_text( $provider ) {
		if ( null === $provider ) {
			return __( 'None. Install UpdraftPlus or BackWPup to back this site up from your SitePulse dashboard; updates work without one.', 'sitepulse' );
		}

		$name = ( 'updraftplus' === $provider['id'] ? 'UpdraftPlus' : 'BackWPup' ) . ' ' . $provider['version'];
		if ( $provider['supported'] ) {
			return $name;
		}
		return sprintf(
			'updraftplus' === $provider['id']
				/* translators: %s: backup plugin and its version */
				? __( '%s. Backups from your SitePulse dashboard need UpdraftPlus 1.26 or later.', 'sitepulse' )
				/* translators: %s: backup plugin and its version */
				: __( '%s. Backups from your SitePulse dashboard need BackWPup 5.', 'sitepulse' ),
			$name
		);
	}

	/**
	 * What the connection box shows.
	 *
	 * @param string $format Date and time format.
	 * @return array
	 */
	private static function connection_view( $format ) {
		$stored = self::connection();

		return array(
			'available'       => self::can_register(),
			'connectable'     => (bool) $stored['connectable'],
			'key'             => (string) $stored['key'],
			'managed'         => self::is_active(),
			'domain'          => (string) wp_parse_url( home_url(), PHP_URL_HOST ),
			'connections'     => array_map(
				static function ( $connection ) use ( $format ) {
					$time = strtotime( (string) $connection['connected_at'] );
					return array(
						// No account: SitePulse connected the site.
						'account' => '' !== $connection['account'] ? $connection['account'] : null,
						'plan'    => (string) $connection['plan'],
						'since'   => false === $time ? '' : wp_date( $format, $time ),
					);
				},
				$stored['connections']
			),
			/* translators: %s: human-readable time difference */
			'checked'         => $stored['checked_at'] ? sprintf( __( '%s ago', 'sitepulse' ), human_time_diff( (int) $stored['checked_at'], time() ) ) : '',
			'error'           => (string) $stored['error'],
			'notice'          => (string) $stored['notice'],
			// A site whose license SitePulse refuses is asked for it: the license form starts open.
			'license_open'    => in_array( $stored['error_code'], array( 'invalid_signature', 'license' ), true ) || 'invalid_signature' === self::state()['last_refusal'],
			'license_pattern' => self::LICENSE_PATTERN,
			'nonce_action'    => self::CONNECTION_ACTION,
			'poll'            => array(
				'url'    => admin_url( 'admin-ajax.php' ),
				'action' => self::POLL_ACTION,
				'nonce'  => wp_create_nonce( self::POLL_ACTION ),
			),
		);
	}

	/**
	 * Explain whether remote management works on this site, and what is missing if not.
	 *
	 * @param array      $state    Stored state.
	 * @param array|null $provider Active backup plugin.
	 * @return array Status (active, off, blocked, error or unavailable) and message.
	 */
	private static function status( array $state, $provider ) {
		if ( is_multisite() ) {
			return array( 'unavailable', __( 'Remote management is not available on multisite networks yet.', 'sitepulse' ) );
		}
		if ( ! self::cloud_services_enabled() ) {
			return array( 'unavailable', __( 'Turn on PageSpeed & AI Reports (Cloud Services) to use remote management.', 'sitepulse' ) );
		}
		if ( self::is_blocked() ) {
			return array( 'blocked', __( 'Blocked: the SitePulse dashboard cannot see or manage the updates and backups of this site.', 'sitepulse' ) );
		}
		// A site without a license has not connected yet, whatever it stored before.
		$licensed = '' !== self::license();
		if ( $licensed && '' !== $state['last_error'] ) {
			return array(
				'error',
				'' !== $state['last_error_message']
					/* translators: %s: why the check-in failed */
					? sprintf( __( 'The last check-in with SitePulse failed: %s', 'sitepulse' ), $state['last_error_message'] )
					// Stored by a version that kept only the error code; the activity has the reason.
					: __( 'The last check-in with SitePulse failed. See the activity below.', 'sitepulse' ),
			);
		}
		if ( $licensed && $state['active'] ) {
			return array(
				'active',
				null !== $provider && $provider['supported']
					? __( 'Active: your SitePulse dashboard manages the updates and backups of this site.', 'sitepulse' )
					: __( 'Active: your SitePulse dashboard manages the updates of this site.', 'sitepulse' ),
			);
		}

		return array( 'off', __( 'Not managed yet. Get a connection key below and enter it in your SitePulse dashboard.', 'sitepulse' ) );
	}

	/**
	 * @return string The site's SitePulse license key, empty before the API issued one.
	 */
	private static function license() {
		return (string) Sitepulse_Api_Service::get_license_key();
	}

	/**
	 * @return bool Whether the administrator allows SitePulse to send data to api.sitepulse.me.
	 */
	private static function cloud_services_enabled() {
		$settings = get_option( 'sitepulse_settings', array() );
		return ! is_array( $settings ) || ! isset( $settings['external_api_enabled'] ) || (bool) $settings['external_api_enabled'];
	}

	/**
	 * @param array $state State to store.
	 */
	private static function save_state( array $state ) {
		update_option( self::STATE_OPTION, $state, false );
	}

	/**
	 * @param int $seconds Seconds until the next check-in.
	 */
	private static function schedule_next( $seconds ) {
		self::unschedule();
		wp_schedule_single_event( time() + $seconds, self::CHECK_IN_HOOK );
	}

	/**
	 * @param Sitepulse_Remote_Client $client API client.
	 * @param array                   $state  State; records when the inventory was sent.
	 * @return bool Whether the API received the inventory.
	 */
	private static function send_inventory( Sitepulse_Remote_Client $client, array &$state ) {
		if ( is_wp_error( $client->post( 'inventory', Sitepulse_Remote_Inventory::collect() ) ) ) {
			return false;
		}

		$state['inventory_sent_at'] = time();
		$state['inventory_due']     = false;
		return true;
	}

	/**
	 * Run the commands the API delivered, each id once, and report each result.
	 *
	 * @param Sitepulse_Remote_Client $client   API client.
	 * @param array                   $state    State; records the commands that ran.
	 * @param mixed                   $commands Commands from the check-in response.
	 */
	private static function run_commands( Sitepulse_Remote_Client $client, array &$state, $commands ) {
		foreach ( array_slice( is_array( $commands ) ? $commands : array(), 0, self::MAX_COMMANDS ) as $command ) {
			$id = is_array( $command ) && is_string( $command['id'] ?? null ) ? $command['id'] : '';
			if ( '' === $id || in_array( $id, $state['done_commands'], true ) ) {
				continue;
			}

			// Remember the command before running it, so a crash can never run it twice.
			$state['done_commands'] = array_slice( array_merge( $state['done_commands'], array( $id ) ), -self::MAX_DONE_COMMANDS );
			self::save_state( $state );

			$type   = is_string( $command['type'] ?? null ) ? $command['type'] : '';
			$args   = is_array( $command['args'] ?? null ) ? $command['args'] : array();
			$result = self::run_command( $client, $state, $id, $type, $command, $args );
			if ( null === $result ) {
				// It answers when it finishes; the dashboard sees it running meanwhile.
				$client->post(
					'command-result',
					array(
						'id'     => $id,
						'status' => 'running',
						'result' => null,
						'error'  => null,
					)
				);
				/* translators: %s: name of a request from the SitePulse dashboard */
				self::log( sprintf( __( 'Dashboard request started: %s.', 'sitepulse' ), self::command_label( $type, $args ) ) );
				continue;
			}

			$client->post( 'command-result', array( 'id' => $id ) + $result );
			self::log_outcome( $type, $args, $result );
		}
	}

	/**
	 * Switch to a signing key the dashboard rotated, once the API has confirmed it: until
	 * then the old key keeps working, so the site and the API never disagree.
	 *
	 * @param Sitepulse_Remote_Client $client  Client signing with the current key.
	 * @param mixed                   $new_key The key the API offered at check-in, if any.
	 * @return Sitepulse_Remote_Client The client for the rest of the check-in.
	 */
	private static function rotate_key( Sitepulse_Remote_Client $client, $new_key ) {
		if ( ! is_string( $new_key ) || 1 !== preg_match( '/^[A-Za-z0-9._-]{32,128}$/', $new_key ) || self::signing_key() === $new_key ) {
			return $client;
		}

		$rotated = new Sitepulse_Remote_Client( $new_key );
		if ( is_wp_error( $rotated->post( 'key', array( 'schema' => self::SCHEMA ) ) ) ) {
			return $client;
		}

		update_option( self::KEY_OPTION, $new_key, false );
		self::log( __( 'Remote management key replaced from the SitePulse dashboard.', 'sitepulse' ) );
		return $rotated;
	}

	/**
	 * @return Sitepulse_Remote_Client|null A client for the API, null when the site cannot connect.
	 */
	public static function client() {
		return self::can_connect() ? new Sitepulse_Remote_Client( self::signing_key() ) : null;
	}

	/**
	 * @return string The key remote management requests are signed with: the rotated one, or the license.
	 */
	private static function signing_key() {
		$key = get_option( self::KEY_OPTION, '' );
		return is_string( $key ) && '' !== $key ? $key : self::license();
	}

	/**
	 * Answer a command that finished after its check-in, such as an update. A result the
	 * API does not receive is sent again at the next check-in.
	 *
	 * @param string $id     Command id.
	 * @param string $type   Command type.
	 * @param array  $args   Command arguments.
	 * @param array  $result status, result and error.
	 */
	public static function finish_command( $id, $type, array $args, array $result ) {
		$outcome = array( 'id' => $id ) + $result;
		$client  = self::client();
		if ( null === $client || is_wp_error( $client->post( 'command-result', $outcome ) ) ) {
			$state                      = self::state();
			$state['pending_results'][] = $outcome;
			self::save_state( $state );
		}

		self::log_outcome( $type, $args, $result );
		self::inventory_changed();
	}

	/**
	 * @param Sitepulse_Remote_Client $client API client.
	 * @param array                   $state  State; keeps the results the API did not receive.
	 */
	private static function send_pending_results( Sitepulse_Remote_Client $client, array &$state ) {
		$pending                  = $state['pending_results'];
		$state['pending_results'] = array();
		foreach ( $pending as $outcome ) {
			if ( is_wp_error( $client->post( 'command-result', $outcome ) ) ) {
				$state['pending_results'][] = $outcome;
			}
		}
	}

	/**
	 * @param string $type   Command type.
	 * @param array  $args   Command arguments.
	 * @param array  $result status, result and error.
	 */
	private static function log_outcome( $type, array $args, array $result ) {
		self::log(
			'done' === $result['status']
				/* translators: %s: name of a request from the SitePulse dashboard */
				? sprintf( __( 'Dashboard request done: %s.', 'sitepulse' ), self::command_label( $type, $args ) )
				/* translators: 1: name of a request from the SitePulse dashboard, 2: error code */
				: sprintf( __( 'Dashboard request failed: %1$s (%2$s).', 'sitepulse' ), self::command_label( $type, $args ), $result['error'] )
		);
	}

	/**
	 * @param Sitepulse_Remote_Client $client  API client.
	 * @param array                   $state   State.
	 * @param string                  $id      Command id.
	 * @param string                  $type    Command type.
	 * @param array                   $command The command.
	 * @param array                   $args    Its arguments.
	 * @return array|null status, result and error for the API; null for an update or a recovery, answered when it is done.
	 */
	private static function run_command( Sitepulse_Remote_Client $client, array &$state, $id, $type, array $command, array $args ) {
		$expires = strtotime( (string) ( $command['expires_at'] ?? '' ) );
		if ( false !== $expires && $expires < time() ) {
			return self::command_result( 'failed', null, 'expired' );
		}

		if ( 'refresh_inventory' === $type ) {
			return self::send_inventory( $client, $state )
				? self::command_result( 'done', array( 'inventory_sent' => true ), null )
				: self::command_result( 'failed', null, 'inventory_failed' );
		}

		if ( in_array( $type, Sitepulse_Remote_Backups::COMMANDS, true ) ) {
			$result = Sitepulse_Remote_Backups::run_command( $type, $args );
			// A started or cancelled backup, or new backup settings, change the backup status.
			$state['inventory_due'] = $state['inventory_due'] || 'done' === $result['status'];
			return $result;
		}

		if ( in_array( $type, Sitepulse_Remote_Updates::COMMANDS, true ) ) {
			$result = Sitepulse_Remote_Updates::run_command( $id, $type, $args );
			// The inventory shows a started update's progress and changed auto-update settings.
			$state['inventory_due'] = $state['inventory_due'] || null === $result || 'done' === $result['status'];
			return $result;
		}

		if ( in_array( $type, Sitepulse_Remote_Recovery::COMMANDS, true ) ) {
			return Sitepulse_Remote_Recovery::run_command( $id, $type, $args );
		}

		return self::command_result( 'failed', null, 'unsupported_command' );
	}

	/**
	 * @param string      $status done or failed.
	 * @param array|null  $result Details for the dashboard.
	 * @param string|null $error  Short error code.
	 * @return array A command's outcome for the API.
	 */
	public static function command_result( $status, $result, $error ) {
		return array(
			'status' => $status,
			'result' => $result,
			'error'  => $error,
		);
	}

	/**
	 * @param string $type Command type.
	 * @param array  $args Command arguments.
	 * @return string Name for the activity log.
	 */
	private static function command_label( $type, array $args ) {
		$labels = array(
			'refresh_inventory'   => __( 'refresh the site inventory', 'sitepulse' ),
			/* translators: %s: what the backup saves: full, files or database */
			'backup_now'          => sprintf( __( 'back up now (%s)', 'sitepulse' ), is_string( $args['what'] ?? null ) ? $args['what'] : 'full' ),
			'cancel_backup'       => __( 'cancel the running backup', 'sitepulse' ),
			/* translators: %s: name of a backup preset */
			'apply_backup_preset' => sprintf( __( 'apply the backup preset "%s"', 'sitepulse' ), is_string( $args['name'] ?? null ) ? $args['name'] : '' ),
			/* translators: %d: number of plugins */
			'update_plugins'      => sprintf( _n( 'update %d plugin', 'update %d plugins', count( (array) ( $args['plugins'] ?? array() ) ), 'sitepulse' ), count( (array) ( $args['plugins'] ?? array() ) ) ),
			/* translators: %d: number of themes */
			'update_themes'       => sprintf( _n( 'update %d theme', 'update %d themes', count( (array) ( $args['themes'] ?? array() ) ), 'sitepulse' ), count( (array) ( $args['themes'] ?? array() ) ) ),
			/* translators: %s: WordPress version */
			'update_core'         => sprintf( __( 'update WordPress to %s', 'sitepulse' ), is_string( $args['version'] ?? null ) ? $args['version'] : '' ),
			'set_auto_updates'    => empty( $args['enabled'] ) ? __( 'turn off WordPress auto-updates', 'sitepulse' ) : __( 'turn on WordPress auto-updates', 'sitepulse' ),
			/* translators: %s: plugin file or theme folder */
			'restore_backup'      => sprintf( __( 'restore %s and the database from the backup taken before its update', 'sitepulse' ), is_string( $args['item'] ?? null ) ? $args['item'] : '' ),
			/* translators: 1: plugin file or theme folder, 2: version */
			'rollback_item'       => sprintf( __( 'roll %1$s back to %2$s', 'sitepulse' ), is_string( $args['item'] ?? null ) ? $args['item'] : '', is_string( $args['version'] ?? null ) ? $args['version'] : '' ),
		);

		return $labels[ $type ] ?? $type;
	}

	/**
	 * Deliver queued events, oldest first; undelivered events wait for the next check-in.
	 *
	 * @param Sitepulse_Remote_Client $client API client.
	 */
	private static function send_events( Sitepulse_Remote_Client $client ) {
		for ( $batch = 0; $batch < self::MAX_EVENTS / self::EVENTS_PER_REQUEST; $batch++ ) {
			$events = array_slice( self::queued_events(), 0, self::EVENTS_PER_REQUEST );
			if ( ! $events ) {
				return;
			}

			$response = $client->post( 'events', array( 'events' => $events ) );
			// A batch the API refuses (HTTP 422) is dropped; it would block the queue forever.
			if ( is_wp_error( $response ) && 'http_422' !== $response->get_error_code() ) {
				return;
			}

			$sent = array_column( $events, 'id' );
			self::save_events(
				array_filter(
					self::queued_events(),
					static function ( $event ) use ( $sent ) {
						return ! in_array( $event['id'], $sent, true );
					}
				)
			);
		}
	}

	/**
	 * @param string $type Event type.
	 * @param array  $data Event details.
	 */
	private static function queue_event( $type, array $data ) {
		$events   = self::queued_events();
		$events[] = array(
			'id'          => wp_generate_uuid4(),
			'type'        => $type,
			'occurred_at' => gmdate( 'Y-m-d\TH:i:s\Z' ),
			'data'        => $data,
		);
		self::save_events( $events );
	}

	/**
	 * @return array Undelivered events, oldest first.
	 */
	private static function queued_events() {
		$events = get_option( self::EVENTS_OPTION, array() );
		return array_values(
			array_filter(
				is_array( $events ) ? $events : array(),
				static function ( $event ) {
					return is_array( $event ) && isset( $event['id'] );
				}
			)
		);
	}

	/**
	 * @param array $events Events to keep; only the newest MAX_EVENTS are kept.
	 */
	private static function save_events( array $events ) {
		update_option( self::EVENTS_OPTION, array_slice( array_values( $events ), -self::MAX_EVENTS ), false );
	}

	/**
	 * @param string $message Activity to show in the settings.
	 */
	private static function log( $message ) {
		$log   = self::log_entries();
		$log[] = array(
			'time'    => time(),
			'message' => (string) $message,
		);
		update_option( self::LOG_OPTION, array_slice( $log, -self::MAX_LOG ), false );
	}

	/**
	 * Why a request to SitePulse failed, in words that say where the problem is: this server
	 * cannot reach SitePulse, SitePulse has trouble, or SitePulse refused the site (and, when the
	 * API names its reason, what to fix). WordPress's own reason for a request that could not be
	 * sent is kept to 160 characters; the settings escape it when they show it.
	 *
	 * @param WP_Error $error Error of Sitepulse_Remote_Client::post().
	 * @return string
	 */
	private static function connection_error_message( WP_Error $error ) {
		$code = (string) $error->get_error_code();
		if ( 'unreachable' === $code ) {
			/* translators: %s: why WordPress could not send the request, such as "cURL error 28: Operation timed out" */
			return sprintf( __( 'SitePulse could not be reached from this server (%s). Check that the server can reach api.sitepulse.me, then try again.', 'sitepulse' ), mb_substr( $error->get_error_message(), 0, 160 ) );
		}
		if ( 'bad_signature' === $code ) {
			return __( 'SitePulse answered, but the answer was not signed for this site. Check the server\'s date and time, then try again.', 'sitepulse' );
		}
		if ( 'bad_response' === $code ) {
			return __( 'SitePulse\'s answer could not be understood. Please try again in a moment.', 'sitepulse' );
		}
		if ( 1 !== preg_match( '/^http_(\d+)$/', $code, $match ) ) {
			return __( 'SitePulse could not be reached. Please try again in a moment.', 'sitepulse' );
		}

		// The API's code for a refused request says what to fix; its own sentence is neither
		// signed nor translated, so the settings say it in their words.
		$refusal = self::refusal( $error );
		if ( '' !== $refusal ) {
			$refusals = array(
				'invalid_signature' => __( 'SitePulse does not accept this site\'s key: the site\'s license or address no longer matches SitePulse\'s record for this domain. Enter the license your SitePulse dashboard shows for this site below, then press Check connection.', 'sitepulse' ),
				'request_expired'   => __( 'SitePulse rejected the request because this server\'s clock is more than 5 minutes off. Fix the server\'s date and time, then try again.', 'sitepulse' ),
				'request_replayed'  => __( 'SitePulse had already received this request. Please try again.', 'sitepulse' ),
				'malformed_headers' => __( 'The request reached SitePulse without its signature headers: a proxy or firewall on this server strips them.', 'sitepulse' ),
				'site_blocked'      => __( 'SitePulse has blocked this site\'s license. Contact SitePulse support.', 'sitepulse' ),
			);
			return $refusals[ $refusal ];
		}

		$status = (int) $match[1];
		if ( $status >= 500 ) {
			/* translators: %d: HTTP status code, such as 503 */
			return sprintf( __( 'SitePulse is having trouble right now (HTTP %d). Please try again in a few minutes.', 'sitepulse' ), $status );
		}
		if ( 401 === $status || 403 === $status ) {
			/* translators: %d: HTTP status code, 401 or 403 */
			return sprintf( __( 'SitePulse refused this site\'s request (HTTP %d). Check the server\'s date and time, and that Cloud Services are on; if the site\'s address or license changed, disconnect and connect again.', 'sitepulse' ), $status );
		}
		if ( 429 === $status ) {
			/* translators: %d: HTTP status code, 429 */
			return sprintf( __( 'SitePulse asked this site to slow down (HTTP %d). Please try again in a minute.', 'sitepulse' ), $status );
		}
		/* translators: %d: HTTP status code */
		return sprintf( __( 'SitePulse answered HTTP %d. Please try again in a moment.', 'sitepulse' ), $status );
	}

	/**
	 * @param WP_Error $error Error of Sitepulse_Remote_Client::post().
	 * @return string The API's code for a refused request (one of REFUSALS), empty when it gave none.
	 */
	private static function refusal( WP_Error $error ) {
		$data = $error->get_error_data();
		$code = is_array( $data ) ? ( $data['error'] ?? '' ) : '';
		return in_array( $code, self::REFUSALS, true ) ? $code : '';
	}

	/**
	 * @param mixed $value Seconds from the API.
	 * @param int   $min   Shortest allowed interval.
	 * @param int   $max   Longest allowed interval.
	 * @return int
	 */
	private static function clamp( $value, $min, $max ) {
		return max( $min, min( $max, (int) $value ) );
	}
}

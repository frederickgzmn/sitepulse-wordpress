<?php
/**
 * SitePulse remote management
 *
 * Lets the SitePulse dashboard (api.sitepulse.me) watch, and step by step manage, the
 * site's backups and updates. The site checks in on a schedule the API sets; the API
 * answers with the dashboard switch and the commands queued for the site. Nothing runs
 * unless the dashboard switch is on, UpdraftPlus or BackWPup is active and the site's
 * administrator has not blocked remote management. docs/remote-management.md describes
 * the contract.
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

	/** admin-post action and nonce of the block switch. */
	const BLOCK_ACTION = 'sitepulse_remote_management';

	/** What the site knows about remote management. */
	const STATE_OPTION = 'sitepulse_remote_state';

	/** Events not delivered yet. */
	const EVENTS_OPTION = 'sitepulse_remote_events';

	/** Activity shown in the settings. */
	const LOG_OPTION = 'sitepulse_remote_log';

	/** Version of the request bodies. */
	const SCHEMA = 1;

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
		'last_error'         => '',
		'inventory_sent_at'  => 0,
		'inventory_due'      => false,
		'provider'           => null,
		'preferred_provider' => null,
		'done_commands'      => array(),
	);

	/**
	 * Register the check-in, the settings and the listeners.
	 */
	public static function init() {
		add_action( self::CHECK_IN_HOOK, array( __CLASS__, 'check_in' ) );
		add_action( 'admin_post_' . self::BLOCK_ACTION, array( __CLASS__, 'handle_block_form' ) );
		add_action( 'sitepulse_settings_after_form', array( __CLASS__, 'render_settings' ) );
		Sitepulse_Remote_Events::init();
		Sitepulse_Remote_Backups::init();

		// Keep the check-in scheduled from dashboard and cron requests; page views skip the check.
		if ( is_admin() || wp_doing_cron() ) {
			self::ensure_schedule();
		}
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
		return ! SITEPULSE_PRO_IS_ACTIVE && ! is_multisite() && self::cloud_services_enabled() && '' !== self::license();
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
		$state['done_commands'] = is_array( $state['done_commands'] ) ? $state['done_commands'] : array();
		return $state;
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
		$client   = new Sitepulse_Remote_Client( self::license() );
		$blocked  = self::is_blocked();
		$provider = Sitepulse_Remote_Backups::provider();
		$response = $client->post(
			'check-in',
			array(
				'schema'          => self::SCHEMA,
				'plugin_version'  => SITEPULSE_VERSION,
				'wp_version'      => (string) get_bloginfo( 'version' ),
				'blocked'          => $blocked,
				'backup_provider'  => $provider,
				'backup_providers' => Sitepulse_Remote_Backups::providers(),
			)
		);

		if ( is_wp_error( $response ) ) {
			// Log a problem once, not at every check-in.
			if ( $state['last_error'] !== $response->get_error_code() ) {
				self::log( self::error_message( $response ) );
			}
			$state['last_error'] = $response->get_error_code();
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

		// The backup plugin picked in the dashboard applies from this check-in on.
		$preferred                   = $remote['backup_provider'] ?? null;
		$state['preferred_provider'] = in_array( $preferred, array( 'updraftplus', 'backwpup' ), true ) ? $preferred : null;
		self::save_state( $state );
		$provider = Sitepulse_Remote_Backups::provider();

		if ( $state['active'] !== $was_active ) {
			self::log( $state['active'] ? __( 'Remote management turned on from the SitePulse dashboard.', 'sitepulse' ) : __( 'Remote management turned off.', 'sitepulse' ) );
			$state['inventory_due'] = $state['inventory_due'] || $state['active'];
		}
		if ( $was_active && $state['active'] && $provider !== $state['provider'] ) {
			self::queue_event( 'backup_provider.changed', array( 'provider' => $provider ) );
			$state['inventory_due'] = true;
		}
		$state['provider'] = $provider;

		if ( $state['active'] ) {
			// Commands first, so the inventory shows what they changed.
			self::run_commands( $client, $state, $response['commands'] ?? array() );
			if ( $state['inventory_due'] || time() - (int) $state['inventory_sent_at'] >= $state['inventory_seconds'] ) {
				self::send_inventory( $client, $state );
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
	 * Show the remote management settings below the SitePulse settings form.
	 *
	 * @param string $context easy for the Simple dashboard, advanced otherwise.
	 */
	public static function render_settings( $context = 'advanced' ) {
		if ( SITEPULSE_PRO_IS_ACTIVE || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$sp_remote = self::view_data();
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
		$names    = array(
			'updraftplus' => 'UpdraftPlus',
			'backwpup'    => 'BackWPup',
		);
		$format   = get_option( 'date_format', 'Y-m-d' ) . ' ' . get_option( 'time_format', 'H:i' );

		return array(
			'status'        => $status[0],
			'label'         => $badges[ $status[0] ][0],
			'tone'          => $badges[ $status[0] ][1],
			'message'       => $status[1],
			'provider'      => $provider ? $names[ $provider['id'] ] . ' ' . $provider['version'] : __( 'None', 'sitepulse' ),
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
		if ( '' === self::license() ) {
			return array( 'unavailable', __( 'Remote management starts when this site receives its SitePulse license with its first site report.', 'sitepulse' ) );
		}
		if ( self::is_blocked() ) {
			return array( 'blocked', __( 'Blocked: the SitePulse dashboard cannot see or manage the backups and updates of this site.', 'sitepulse' ) );
		}
		if ( null === $provider ) {
			return array( 'off', __( 'Install and activate UpdraftPlus or BackWPup to manage this site from the SitePulse dashboard.', 'sitepulse' ) );
		}
		if ( ! $provider['supported'] ) {
			return array( 'off', 'updraftplus' === $provider['id'] ? __( 'Update UpdraftPlus to version 1.26 or later to use remote management.', 'sitepulse' ) : __( 'Remote management works with BackWPup 5.', 'sitepulse' ) );
		}
		if ( '' !== $state['last_error'] ) {
			return array( 'error', __( 'The last check-in with SitePulse failed. See the activity below.', 'sitepulse' ) );
		}
		if ( 0 === (int) $state['last_check_in'] ) {
			return array( 'off', __( 'Connecting to SitePulse…', 'sitepulse' ) );
		}
		if ( $state['active'] ) {
			return array( 'active', __( 'Active: the SitePulse dashboard can see the backups and updates of this site and run and set up its backups.', 'sitepulse' ) );
		}

		return array( 'off', $state['enabled'] ? __( 'Turning on at the next check-in.', 'sitepulse' ) : __( 'Off. Turn it on for this site in your SitePulse dashboard.', 'sitepulse' ) );
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
			$result = self::run_command( $client, $state, $type, $command, $args );
			$client->post( 'command-result', array( 'id' => $id ) + $result );

			self::log(
				'done' === $result['status']
					/* translators: %s: name of a request from the SitePulse dashboard */
					? sprintf( __( 'Dashboard request done: %s.', 'sitepulse' ), self::command_label( $type, $args ) )
					/* translators: 1: name of a request from the SitePulse dashboard, 2: error code */
					: sprintf( __( 'Dashboard request failed: %1$s (%2$s).', 'sitepulse' ), self::command_label( $type, $args ), $result['error'] )
			);
		}
	}

	/**
	 * @param Sitepulse_Remote_Client $client  API client.
	 * @param array                   $state   State.
	 * @param string                  $type    Command type.
	 * @param array                   $command The command.
	 * @param array                   $args    Its arguments.
	 * @return array status, result and error for the API.
	 */
	private static function run_command( Sitepulse_Remote_Client $client, array &$state, $type, array $command, array $args ) {
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
	 * @param WP_Error $error Why the check-in failed.
	 * @return string Activity entry.
	 */
	private static function error_message( WP_Error $error ) {
		$hints = array(
			'http_401' => __( 'SitePulse did not accept the signature of this site. Check that the server clock is correct and that the site address has not changed.', 'sitepulse' ),
			'http_403' => __( 'The SitePulse license of this site is not active.', 'sitepulse' ),
		);

		/* translators: %s: reason */
		return sprintf( __( 'Check-in with SitePulse failed: %s', 'sitepulse' ), $hints[ $error->get_error_code() ] ?? $error->get_error_message() );
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

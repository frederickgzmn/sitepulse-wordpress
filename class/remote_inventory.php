<?php
/**
 * SitePulse remote management inventory
 *
 * Describes the site for the SitePulse dashboard: versions, the plugins and themes with
 * their available updates, whether WordPress can update itself, and the backup status.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Remote_Inventory
 */
class Sitepulse_Remote_Inventory {

	/** Core auto-update settings that also allow major releases. */
	const CORE_AUTO_UPDATE_ALL = array( true, 'beta', 'rc', 'development', 'branch-development' );

	/** Plugins that also update or manage the site: plugin file => id and name. */
	const MANAGEMENT_PLUGINS = array(
		'worker/init.php'                                => array( 'managewp', 'ManageWP Worker' ),
		'mainwp-child/mainwp-child.php'                  => array( 'mainwp', 'MainWP Child' ),
		'iwp-client/init.php'                            => array( 'infinitewp', 'InfiniteWP Client' ),
		'jetpack/jetpack.php'                            => array( 'jetpack', 'Jetpack' ),
		'stops-core-theme-and-plugin-updates/main.php'   => array( 'easy-updates-manager', 'Easy Updates Manager' ),
		'wp-umbrella/wp-umbrella.php'                    => array( 'wp-umbrella', 'WP Umbrella' ),
		'blogvault-real-time-backup/blogvault.php'       => array( 'blogvault', 'BlogVault' ),
		'wpremote/plugin.php'                            => array( 'wp-remote', 'WP Remote' ),
		'companion-auto-update/companion-auto-update.php' => array( 'companion-auto-update', 'Companion Auto Update' ),
	);

	/** Hosts that manage updates themselves, recognized by a constant they define: constant => id and name. */
	const MANAGED_HOSTS = array(
		'WPE_APIKEY'           => array( 'wpengine', 'WP Engine' ),
		'KINSTAMU_VERSION'     => array( 'kinsta', 'Kinsta' ),
		'PANTHEON_ENVIRONMENT' => array( 'pantheon', 'Pantheon' ),
		'IS_ATOMIC'            => array( 'wordpress-com', 'WordPress.com' ),
		'GD_SYSTEM_PLUGIN_DIR' => array( 'godaddy', 'GoDaddy Managed WordPress' ),
		'FLYWHEEL_CONFIG_DIR'  => array( 'flywheel', 'Flywheel' ),
	);

	/**
	 * Collect the inventory sent to the API.
	 *
	 * @return array
	 */
	public static function collect() {
		// Cron requests do not load the admin API that reports updates.
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/update.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		return array(
			'schema'       => Sitepulse_Remote_Management::SCHEMA,
			'collected_at' => gmdate( 'Y-m-d\TH:i:s\Z' ),
			'site'         => self::site(),
			'updates'      => self::updates(),
			'backups'      => Sitepulse_Remote_Backups::status(),
		);
	}

	/**
	 * @return array Versions and environment of the site.
	 */
	private static function site() {
		global $wpdb;

		return array(
			'wp_version'  => (string) get_bloginfo( 'version' ),
			'php_version' => PHP_VERSION,
			'db_version'  => (string) $wpdb->db_version(),
			'multisite'   => is_multisite(),
			'locale'      => get_locale(),
			'timezone'    => wp_timezone_string(),
			'disk_free'   => self::disk_free(),
		);
	}

	/**
	 * @return int|null Free bytes where WordPress writes updates, or null when the server does not tell.
	 */
	private static function disk_free() {
		$free = function_exists( 'disk_free_space' ) ? @disk_free_space( WP_CONTENT_DIR ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- WordPress core reads it the same way before updates.
		return false === $free ? null : (int) $free;
	}

	/**
	 * @return array Other tools that also update or manage the site: id and name.
	 */
	private static function management_tools() {
		$tools = array();
		foreach ( self::MANAGEMENT_PLUGINS as $file => $tool ) {
			if ( is_plugin_active( $file ) ) {
				$tools[] = array(
					'id'   => $tool[0],
					'name' => $tool[1],
				);
			}
		}
		foreach ( self::MANAGED_HOSTS as $constant => $tool ) {
			if ( defined( $constant ) ) {
				$tools[] = array(
					'id'   => $tool[0],
					'name' => $tool[1],
				);
			}
		}

		return $tools;
	}

	/**
	 * @return array Installed software, available updates and whether WordPress can apply them.
	 */
	private static function updates() {
		$reason = self::update_blocker();

		return array(
			'can_update'           => null === $reason,
			'reason'               => $reason,
			'core'                 => self::core(),
			'plugins'              => self::plugins(),
			'themes'               => self::themes(),
			'translations_pending' => count( (array) wp_get_translation_updates() ),
			'management_tools'     => self::management_tools(),
			'job'                  => Sitepulse_Remote_Updates::job_status(),
		);
	}

	/**
	 * Why WordPress cannot update files on its own, if it cannot.
	 *
	 * @return string|null file_mods_disabled, filesystem_credentials or null.
	 */
	private static function update_blocker() {
		if ( ! wp_is_file_mod_allowed( 'sitepulse_remote_updates' ) ) {
			return 'file_mods_disabled';
		}

		return 'direct' === get_filesystem_method() ? null : 'filesystem_credentials';
	}

	/**
	 * @return array Current WordPress version, offered releases and the core auto-update setting.
	 */
	private static function core() {
		$current = (string) get_bloginfo( 'version' );
		$updates = get_core_updates( array( 'dismissed' => true ) );
		$offers  = array();

		foreach ( is_array( $updates ) ? $updates : array() as $update ) {
			// Until its next version check, WordPress still offers the release it just installed.
			if ( ! is_object( $update ) || 'upgrade' !== ( $update->response ?? '' ) || empty( $update->current ) || version_compare( (string) $update->current, $current, '<=' ) ) {
				continue;
			}
			// WordPress offers the same release once per language.
			$version            = (string) $update->current;
			$offers[ $version ] = array(
				'version' => $version,
				'type'    => self::branch( $version ) === self::branch( $current ) ? 'minor' : 'major',
			);
		}

		return array(
			'current'     => $current,
			'offers'      => array_values( $offers ),
			'auto_update' => self::core_auto_update(),
		);
	}

	/**
	 * Which WordPress releases install automatically, as WordPress itself decides it.
	 *
	 * @return string major, minor or disabled.
	 */
	private static function core_auto_update() {
		$disabled = ! wp_is_file_mod_allowed( 'automatic_updater' )
			|| apply_filters( 'automatic_updater_disabled', defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED );
		if ( $disabled ) {
			return 'disabled';
		}

		$setting = defined( 'WP_AUTO_UPDATE_CORE' ) ? WP_AUTO_UPDATE_CORE : null;
		$all     = in_array( $setting, self::CORE_AUTO_UPDATE_ALL, true );
		$major   = $all || ( null === $setting && 'enabled' === get_site_option( 'auto_update_core_major', 'unset' ) );
		$minor   = $all || 'minor' === $setting || ( null === $setting && 'enabled' === get_site_option( 'auto_update_core_minor', 'enabled' ) );

		if ( apply_filters( 'allow_major_auto_core_updates', $major ) ) {
			return 'major';
		}

		return apply_filters( 'allow_minor_auto_core_updates', $minor ) ? 'minor' : 'disabled';
	}

	/**
	 * @return array Installed plugins with their available update.
	 */
	private static function plugins() {
		$updates   = self::update_offers( 'update_plugins' );
		$automatic = (array) get_site_option( 'auto_update_plugins', array() );
		$plugins   = array();

		foreach ( get_plugins() as $file => $data ) {
			$update    = isset( $updates[ $file ] ) ? (array) $updates[ $file ] : null;
			$plugins[] = array(
				'file'        => (string) $file,
				'name'        => (string) ( $data['Name'] ?? '' ),
				'version'     => (string) ( $data['Version'] ?? '' ),
				'active'      => is_plugin_active( $file ),
				'auto_update' => in_array( $file, $automatic, true ),
				'update'      => $update ? self::update_details( $update ) : null,
			);
		}

		return $plugins;
	}

	/**
	 * @return array Installed themes with their available update.
	 */
	private static function themes() {
		$updates   = self::update_offers( 'update_themes' );
		$automatic = (array) get_site_option( 'auto_update_themes', array() );
		$in_use    = array( get_stylesheet(), get_template() );
		$themes    = array();

		foreach ( wp_get_themes() as $stylesheet => $theme ) {
			$stylesheet = (string) $stylesheet;
			$update     = isset( $updates[ $stylesheet ] ) ? (array) $updates[ $stylesheet ] : null;
			$themes[]   = array(
				'stylesheet'  => $stylesheet,
				'name'        => (string) $theme->get( 'Name' ),
				'version'     => (string) $theme->get( 'Version' ),
				'active'      => in_array( $stylesheet, $in_use, true ),
				'auto_update' => in_array( $stylesheet, $automatic, true ),
				'update'      => $update ? self::update_details( $update ) : null,
			);
		}

		return $themes;
	}

	/**
	 * @param string $transient update_plugins or update_themes.
	 * @return array Offered updates keyed by plugin file or theme stylesheet.
	 */
	private static function update_offers( $transient ) {
		$updates = get_site_transient( $transient );
		return is_object( $updates ) && isset( $updates->response ) && is_array( $updates->response ) ? $updates->response : array();
	}

	/**
	 * @param array $update A plugin (object) or theme (array) update offer, as an array.
	 * @return array
	 */
	private static function update_details( array $update ) {
		return array(
			'version'      => (string) ( $update['new_version'] ?? '' ),
			'requires_wp'  => isset( $update['requires'] ) ? (string) $update['requires'] : null,
			'requires_php' => isset( $update['requires_php'] ) ? (string) $update['requires_php'] : null,
			'tested'       => isset( $update['tested'] ) ? (string) $update['tested'] : null,
			// Premium plugins without a valid license offer an update without a package.
			'download'     => ! empty( $update['package'] ),
		);
	}

	/**
	 * @param string $version A WordPress version.
	 * @return string Its release branch, such as 6.8.
	 */
	private static function branch( $version ) {
		return implode( '.', array_slice( explode( '.', $version ), 0, 2 ) );
	}
}

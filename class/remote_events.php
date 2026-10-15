<?php
/**
 * SitePulse remote management events
 *
 * Reports updates, plugin activations and theme switches to the SitePulse dashboard
 * while it manages the site.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Remote_Events
 */
class Sitepulse_Remote_Events {

	/**
	 * Versions before an update, keyed "plugin:<file>" or "theme:<stylesheet>".
	 *
	 * @var array
	 */
	private static $versions_before = array();

	/**
	 * WordPress version when the request started; a core update replaces it.
	 *
	 * @var string
	 */
	private static $core_version = '';

	/**
	 * Set while SitePulse runs an update; it reports the update itself, after its health check.
	 *
	 * @var bool
	 */
	public static $updating = false;

	/**
	 * Listen for software changes.
	 */
	public static function init() {
		self::$core_version = (string) get_bloginfo( 'version' );

		add_filter( 'upgrader_pre_install', array( __CLASS__, 'remember_version' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'update_completed' ), 10, 2 );
		add_action( '_core_updated_successfully', array( __CLASS__, 'core_updated' ) );
		add_action( 'activated_plugin', array( __CLASS__, 'plugin_activated' ) );
		add_action( 'deactivated_plugin', array( __CLASS__, 'plugin_deactivated' ) );
		add_action( 'switch_theme', array( __CLASS__, 'theme_switched' ), 10, 3 );
	}

	/**
	 * Remember the installed version before WordPress replaces a plugin or theme.
	 *
	 * @param mixed $response   Installation response, passed through.
	 * @param mixed $hook_extra What is being installed.
	 * @return mixed
	 */
	public static function remember_version( $response, $hook_extra ) {
		$hook_extra = (array) $hook_extra;
		if ( ! empty( $hook_extra['plugin'] ) ) {
			self::$versions_before[ 'plugin:' . $hook_extra['plugin'] ] = self::plugin_data( $hook_extra['plugin'] )['version'];
		}
		if ( ! empty( $hook_extra['theme'] ) ) {
			self::$versions_before[ 'theme:' . $hook_extra['theme'] ] = self::theme_version( $hook_extra['theme'] );
		}

		return $response;
	}

	/**
	 * Report finished plugin, theme and translation updates.
	 *
	 * @param object $upgrader   WordPress upgrader.
	 * @param mixed  $hook_extra What was installed or updated.
	 */
	public static function update_completed( $upgrader, $hook_extra ) {
		if ( self::$updating ) {
			return;
		}

		$hook_extra = (array) $hook_extra;
		$kind       = (string) ( $hook_extra['type'] ?? '' );
		if ( 'update' !== ( $hook_extra['action'] ?? '' ) ) {
			// A new plugin or theme changes the inventory without being an update.
			Sitepulse_Remote_Management::inventory_changed();
			return;
		}

		$items = array();
		if ( 'plugin' === $kind ) {
			foreach ( self::updated( $hook_extra, 'plugin' ) as $file ) {
				$items[] = self::item( $file, self::$versions_before[ 'plugin:' . $file ] ?? null, self::plugin_data( $file )['version'] );
			}
		} elseif ( 'theme' === $kind ) {
			foreach ( self::updated( $hook_extra, 'theme' ) as $stylesheet ) {
				$items[] = self::item( $stylesheet, self::$versions_before[ 'theme:' . $stylesheet ] ?? null, self::theme_version( $stylesheet ) );
			}
		} elseif ( 'translation' === $kind ) {
			foreach ( (array) ( $hook_extra['translations'] ?? array() ) as $translation ) {
				$translation = (array) $translation;
				$id          = implode( ':', array( $translation['type'] ?? '', $translation['slug'] ?? '', $translation['language'] ?? '' ) );
				$items[]     = self::item( $id, null, (string) ( $translation['version'] ?? '' ) );
			}
		}

		if ( 'translation' !== $kind ) {
			foreach ( $items as $item ) {
				if ( $item['ok'] ) {
					Sitepulse_Remote_Activity::watch( $kind, $item['id'], $item['from'], $item['to'] );
				}
			}
		}

		// Core updates are reported by core_updated(), with both versions.
		if ( $items ) {
			Sitepulse_Remote_Management::record_event(
				'update.completed',
				array(
					'kind'      => $kind,
					'initiator' => self::initiator(),
					'items'     => $items,
				)
			);
		}
	}

	/**
	 * Report a WordPress core update.
	 *
	 * @param string $version The new WordPress version.
	 */
	public static function core_updated( $version ) {
		if ( self::$updating ) {
			return;
		}

		$item = self::item( 'wordpress', self::$core_version, (string) $version );
		Sitepulse_Remote_Management::record_event(
			'update.completed',
			array(
				'kind'      => 'core',
				'initiator' => self::initiator(),
				'items'     => array( $item ),
			)
		);
		if ( $item['ok'] ) {
			Sitepulse_Remote_Activity::watch( 'core', 'wordpress', $item['from'], $item['to'] );
		}
	}

	/**
	 * @param string $file Plugin file.
	 */
	public static function plugin_activated( $file ) {
		self::plugin_event( 'plugin.activated', $file );
	}

	/**
	 * @param string $file Plugin file.
	 */
	public static function plugin_deactivated( $file ) {
		self::plugin_event( 'plugin.deactivated', $file );
	}

	/**
	 * @param string $new_name  Name of the new theme.
	 * @param object $new_theme The new WP_Theme.
	 * @param object $old_theme The previous WP_Theme.
	 */
	public static function theme_switched( $new_name, $new_theme, $old_theme ) {
		Sitepulse_Remote_Management::record_event(
			'theme.switched',
			array(
				'stylesheet' => (string) $new_theme->get_stylesheet(),
				'name'       => (string) $new_theme->get( 'Name' ),
				'version'    => (string) $new_theme->get( 'Version' ),
				'previous'   => (string) $old_theme->get_stylesheet(),
			)
		);
	}

	/**
	 * The installed version, read from disk.
	 *
	 * @param string $kind plugin, theme or core.
	 * @param string $id   Plugin file or theme stylesheet; ignored for core.
	 * @return string Empty when it is not installed.
	 */
	public static function installed_version( $kind, $id ) {
		if ( 'plugin' === $kind ) {
			return self::plugin_data( $id )['version'];
		}
		if ( 'theme' === $kind ) {
			return self::theme_version( $id );
		}

		// A core update replaces version.php while $wp_version keeps the old version.
		$file = ABSPATH . WPINC . '/version.php';
		$code = is_readable( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- A local file.
		return preg_match( '/\$wp_version\s*=\s*\'([^\']+)\'/', $code, $match ) ? $match[1] : (string) get_bloginfo( 'version' );
	}

	/**
	 * @param string $type plugin.activated or plugin.deactivated.
	 * @param string $file Plugin file.
	 */
	private static function plugin_event( $type, $file ) {
		$data = self::plugin_data( $file );
		Sitepulse_Remote_Management::record_event(
			$type,
			array(
				'file'    => (string) $file,
				'name'    => $data['name'],
				'version' => $data['version'],
			)
		);
	}

	/**
	 * @param array  $hook_extra Upgrader details.
	 * @param string $type       plugin or theme.
	 * @return array Plugin files or theme stylesheets; bulk updates list several.
	 */
	private static function updated( array $hook_extra, $type ) {
		return array_map( 'strval', (array) ( $hook_extra[ $type . 's' ] ?? $hook_extra[ $type ] ?? array() ) );
	}

	/**
	 * @param string      $id   Plugin file, theme stylesheet or "wordpress".
	 * @param string|null $from Version before the update, if known.
	 * @param string      $to   Installed version now; empty when it cannot be read.
	 * @return array An updated item; the update failed when the version did not change.
	 */
	private static function item( $id, $from, $to ) {
		return array(
			'id'   => $id,
			'from' => $from,
			'to'   => '' === $to ? null : $to,
			'ok'   => '' !== $to && $to !== $from,
		);
	}

	/**
	 * Read the plugin header from disk; WordPress's plugin list can still hold the old version.
	 *
	 * @param string $file Plugin file.
	 * @return array name and version, empty when the plugin is gone.
	 */
	private static function plugin_data( $file ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$path = WP_PLUGIN_DIR . '/' . $file;
		$data = is_readable( $path ) ? get_plugin_data( $path, false, false ) : array();

		return array(
			'name'    => (string) ( $data['Name'] ?? '' ),
			'version' => (string) ( $data['Version'] ?? '' ),
		);
	}

	/**
	 * Read the theme version from disk; WordPress caches theme headers for the request.
	 *
	 * @param string $stylesheet Theme directory.
	 * @return string Empty when the theme is gone.
	 */
	private static function theme_version( $stylesheet ) {
		$file = get_theme_root( $stylesheet ) . '/' . $stylesheet . '/style.css';
		return is_readable( $file ) ? (string) get_file_data( $file, array( 'Version' => 'Version' ) )['Version'] : '';
	}

	/**
	 * @return string auto for WordPress's automatic updates, user otherwise.
	 */
	private static function initiator() {
		return doing_action( 'wp_maybe_auto_update' ) ? 'auto' : 'user';
	}
}

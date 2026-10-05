<?php
/**
 * Setup functions on activate & deactivate events:
 * - Initialize custom options, database, etc.
 * - Upgrade custom options, database, etc.
 * - Cleanup on deactivate
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Sitepulse_Setup {
	const versionKey = 'version';

	/**
	 * Specify all codes required for plugin activation here.
	 */
	public function activate() {
		$version = $this->getInstalledVersion();
		if($version) {
			// If plugin already installed, check if that upgrade is needed
			if(version_compare( $version, SITEPULSE_VERSION ) < 0) {
				$this->upgrade();
			}
			
		} else {
			// First install of the plugin
			$this->install();
		}
	}

	/**
	 * Specify all codes required for plugin deactivation here.
	 */
	public function deactivate() {
	}

	/**
	 * Specify all codes required for plugin uninstall here.
	 *
	 */
	public function uninstall() {
	}
	
	public function getInstalledVersion() {
		return $this->getOption(self::versionKey);
	}

	public function install() {
		// Initialize plugin options
		$this->initOptions();
	}

	/**
	 * Perform upgrades (e.g. database changes, custom options, etc.)
	 */
	public function upgrade() {
		// Upgrade custom options
		$this->upgradeOptions();
	}
	/**
	 * Storing custom options
	 */
	public function initOptions() {
		// Storing current version number
		$this->setOption(self::versionKey, SITEPULSE_VERSION);
	}

	/**
	 * Update custom options
	 */
	public function upgradeOptions() {
		// Updating current version number
		$this->setOption(self::versionKey, SITEPULSE_VERSION);
	}

	
	/**
	 * Helper for using prefixes for all references.
	 */
	public function setPrefix($name) {
		$prefix = SITEPULSE_PREFIX . SITEPULSE_PREFIX_SEPARATOR;
		return strpos($name, $prefix) === 0 ? $name : $prefix . $name;
	}

	/**
	 * Helper for getting prefixed options.
	 */
	public function getOption( $name, $default = null ) {
		$ret = get_option($this->setPrefix($name));
		if(!$ret && $default) {
			$ret = $default;
		}
		return $ret;
	}

	/**
	 * Helper for adding/updating prefixed options.
	 */
	public function setOption( $name, $value ) {
		return update_option($this->setPrefix($name), $value, false);
	}

	/**
	 * Add SitePulse body class to admin pages
	 * Shared method for backend and onboarding
	 *
	 * @param string $classes   Current body classes
	 * @param array  $page_ids  Array of page IDs to check against
	 * @return string Modified body classes
	 */
	public function add_sitepulse_body_class( $classes, $page_ids = [] ) {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return $classes;
		}

		// Check if we're on one of our plugin pages
		$is_sitepulse_page = false;

		if ( ! empty( $page_ids ) && in_array( $screen->id, $page_ids, true ) ) {
			$is_sitepulse_page = true;
		} elseif ( strpos( $screen->id, 'sitepulse' ) !== false ) {
			$is_sitepulse_page = true;
		}

		if ( $is_sitepulse_page ) {
			$classes .= ' sitepulse_css_class ';
		}

		return $classes;
	}

	/**
	 * Check if SitePulse Pro plugin is installed and activated.
	 *
	 * Lightweight version optimized for every request:
	 * 1. PRO constant check - fastest, works after pro loads
	 * 2. PRO class check - reliable after plugins_loaded
	 * 3. Pattern search in active_plugins - lightweight, no file I/O
	 *
	 * Result is cached in a static variable to avoid repeated lookups.
	 *
	 * @return bool True if SitePulse Pro is active, false otherwise.
	 */
	public static function is_pro_active() {
		static $is_active = null;

		// Return cached result if available
		if ( $is_active !== null ) {
			return $is_active;
		}

		// Method 1: Check for PRO constant (works after plugins_loaded priority 20)
		if ( defined( 'SITEPULSE_PRO_VERSION' ) ) {
			$is_active = true;
			return $is_active;
		}

		// Method 2: Check for PRO loader class
		if ( class_exists( 'Sitepulse_Pro_Loader' ) ) {
			$is_active = true;
			return $is_active;
		}

		// Method 3: Lightweight search in active_plugins by TextDomain pattern
		// This avoids expensive get_plugins() call - just checks the option
		$is_active = self::find_pro_in_active_plugins();
		return $is_active;
	}

	/**
	 * Lightweight search for SitePulse Pro in active plugins list.
	 *
	 * Searches the active_plugins option for plugin paths containing
	 * the TextDomain 'sitepulse-pro'. This is much faster than get_plugins()
	 * as it only reads a single option from the database.
	 *
	 * @return bool True if a matching plugin path is found.
	 */
	private static function find_pro_in_active_plugins() {
		$active_plugins = get_option( 'active_plugins', array() );

		if ( empty( $active_plugins ) ) {
			return false;
		}

		foreach ( $active_plugins as $plugin_path ) {
			// Match plugin paths that START with 'sitepulse-pro/' to avoid false positives
			// Example: 'sitepulse-pro/loader.php'
			if ( strpos( $plugin_path, 'sitepulse-pro/' ) === 0 ) {
					return true;
			}
		}

		return false;
	}

}

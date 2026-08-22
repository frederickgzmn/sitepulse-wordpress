<?php
/**
 * SitePulse Whitelabel Service
 *
 * Handles whitelabel branding configuration received from the API.
 * Allows dynamic customization of plugin name, logo, and accent color.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Whitelabel_Service
 */
class Sitepulse_Whitelabel_Service {

	/**
	 * Option key for storing whitelabel data
	 *
	 * @var string
	 */
	private const WHITELABEL_OPTION_KEY = 'sitepulse_whitelabel';

	/**
	 * Default plugin name
	 *
	 * @var string
	 */
	private const DEFAULT_PLUGIN_NAME = 'SitePulse';

	/**
	 * Store whitelabel data from API response
	 *
	 * @param array $data Whitelabel data from API response
	 * @return bool True on success, false on failure
	 */
	public static function store_whitelabel_data( array $data ) {
		// Validate and sanitize the data
		$sanitized_data = array(
			'enabled'      => isset( $data['enabled'] ) ? (bool) $data['enabled'] : false,
			'company_name' => isset( $data['company_name'] ) ? sanitize_text_field( $data['company_name'] ) : '',
			'plugin_name'  => isset( $data['plugin_name'] ) ? sanitize_text_field( $data['plugin_name'] ) : '',
			'logo_url'     => isset( $data['logo_url'] ) ? esc_url_raw( $data['logo_url'] ) : '',
			'accent_color' => isset( $data['accent_color'] ) ? sanitize_hex_color( $data['accent_color'] ) : '',
			'updated_at'   => current_time( 'mysql' ),
		);

		return update_option( self::WHITELABEL_OPTION_KEY, $sanitized_data, false );
	}

	/**
	 * Get all whitelabel data
	 *
	 * @return array Whitelabel data or defaults
	 */
	public static function get_whitelabel_data() {
		$defaults = array(
			'enabled'      => false,
			'company_name' => '',
			'plugin_name'  => '',
			'logo_url'     => '',
			'accent_color' => '',
			'updated_at'   => null,
		);

		$data = get_option( self::WHITELABEL_OPTION_KEY, $defaults );

		return wp_parse_args( $data, $defaults );
	}

	/**
	 * Check if whitelabel is enabled
	 *
	 * @return bool True if whitelabel is enabled and has valid data
	 */
	public static function is_whitelabel_enabled() {
		$data = self::get_whitelabel_data();

		return ! empty( $data['enabled'] ) && ! empty( $data['plugin_name'] );
	}

	/**
	 * Get the plugin name (custom or default)
	 *
	 * @return string Plugin name
	 */
	public static function get_plugin_name() {
		if ( ! self::is_whitelabel_enabled() ) {
			return self::DEFAULT_PLUGIN_NAME;
		}

		$data = self::get_whitelabel_data();

		return ! empty( $data['plugin_name'] ) ? $data['plugin_name'] : self::DEFAULT_PLUGIN_NAME;
	}

	/**
	 * Get the company name
	 *
	 * @return string Company name or empty string
	 */
	public static function get_company_name() {
		$data = self::get_whitelabel_data();

		return ! empty( $data['company_name'] ) ? $data['company_name'] : '';
	}

	/**
	 * Get the logo URL (custom or default)
	 *
	 * @param string $type Logo type: 'menu' for 34x34 SVG, 'header' for full logo
	 * @return string Logo URL
	 */
	public static function get_logo_url( $type = 'header' ) {
		if ( ! self::is_whitelabel_enabled() ) {
			return self::get_default_logo_url( $type );
		}

		$data = self::get_whitelabel_data();

		// Return custom logo URL if set and not empty, otherwise return default
		if ( ! empty( $data['logo_url'] ) ) {
			return $data['logo_url'];
		}

		return self::get_default_logo_url( $type );
	}

	/**
	 * Get the default logo URL
	 *
	 * @param string $type Logo type: 'menu' for 34x34 SVG, 'header' for full logo
	 * @return string Default logo URL
	 */
	private static function get_default_logo_url( $type = 'header' ) {
		if ( $type === 'menu' ) {
			return SITEPULSE_URL . 'assets/img/sitepulse_34x34.svg';
		}

		return SITEPULSE_URL . 'assets/img/only-logo.png';
	}

	/**
	 * Get the accent color
	 *
	 * @return string Accent color hex code or empty string
	 */
	public static function get_accent_color() {
		if ( ! self::is_whitelabel_enabled() ) {
			return '';
		}

		$data = self::get_whitelabel_data();

		return ! empty( $data['accent_color'] ) ? $data['accent_color'] : '';
	}

	/**
	 * Clear whitelabel data (reset to defaults)
	 *
	 * @return bool True on success
	 */
	public static function clear_whitelabel_data() {
		return delete_option( self::WHITELABEL_OPTION_KEY );
	}

	/**
	 * Get the last update timestamp
	 *
	 * @return string|null Datetime string or null
	 */
	public static function get_last_updated() {
		$data = self::get_whitelabel_data();

		return $data['updated_at'];
	}
}

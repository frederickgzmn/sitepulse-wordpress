<?php
/**
 * SitePulse remote management client
 *
 * Sends requests to the remote-management endpoints of api.sitepulse.me, signed with
 * the site's license key, and accepts only responses the API signed for that request.
 * docs/remote-management.md describes the contract.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Remote_Client
 */
class Sitepulse_Remote_Client {


	/** Path of the site endpoints; it is part of every signature. */
	const PATH_PREFIX = '/api/website/remote/';

	/** Seconds a signed timestamp may differ from this server's clock. */
	const MAX_CLOCK_SKEW = 300;

	/** Seconds to wait for the API. */
	const TIMEOUT = 15;

	/**
	 * License key that signs the requests; it is never sent.
	 *
	 * @var string
	 */
	private $license;

	/**
	 * @param string $license The site's SitePulse license key.
	 */
	public function __construct( $license ) {
		$this->license = (string) $license;
	}

	/**
	 * Send a JSON body to a site endpoint.
	 *
	 * @param string $endpoint check-in, inventory, events or command-result.
	 * @param array  $payload  Request body.
	 * @return array|WP_Error The decoded response, or why there is no trustworthy one.
	 */
	public function post( $endpoint, array $payload ) {
		$path      = self::PATH_PREFIX . $endpoint;
		$body      = (string) wp_json_encode( $payload );
		$domain    = self::domain();
		$timestamp = (string) time();
		$nonce     = bin2hex( random_bytes( 16 ) );

		$response = wp_remote_post(
			self::base_url() . $path,
			array(
				'timeout'     => self::TIMEOUT,
				'data_format' => 'body',
				'body'        => $body,
				'headers'     => array(
					'Content-Type'          => 'application/json',
					'Accept'                => 'application/json',
					'X-SitePulse-Domain'    => $domain,
					'X-SitePulse-Timestamp' => $timestamp,
					'X-SitePulse-Nonce'     => $nonce,
					'X-SitePulse-Signature' => self::request_signature( 'POST', $path, $domain, $timestamp, $nonce, $body, $this->license ),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'unreachable', $response->get_error_message() );
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status > 299 ) {
			return new WP_Error( 'http_' . $status, sprintf( 'HTTP %d', $status ) );
		}

		$response_body      = (string) wp_remote_retrieve_body( $response );
		$response_timestamp = (string) wp_remote_retrieve_header( $response, 'x-sitepulse-timestamp' );
		$response_signature = (string) wp_remote_retrieve_header( $response, 'x-sitepulse-signature' );
		if ( ! self::is_fresh( $response_timestamp ) || ! hash_equals( self::response_signature( $nonce, $response_timestamp, $response_body, $this->license ), $response_signature ) ) {
			return new WP_Error( 'bad_signature', 'The response was not signed for this request.' );
		}

		$decoded = json_decode( $response_body, true );
		if ( ! is_array( $decoded ) || true !== ( $decoded['success'] ?? null ) ) {
			return new WP_Error( 'bad_response', 'The response was not understood.' );
		}

		return $decoded;
	}

	/**
	 * Signature of a request: HMAC-SHA256 of the canonical string, keyed with the license.
	 *
	 * @param string $method    HTTP method.
	 * @param string $path      Request path.
	 * @param string $domain    Normalized site host.
	 * @param string $timestamp Unix time in seconds.
	 * @param string $nonce     32 lowercase hexadecimal characters.
	 * @param string $body      Raw request body.
	 * @param string $license   License key.
	 * @return string Lowercase hexadecimal signature.
	 */
	public static function request_signature( $method, $path, $domain, $timestamp, $nonce, $body, $license ) {
		return hash_hmac( 'sha256', implode( "\n", array( $method, $path, $domain, $timestamp, $nonce, hash( 'sha256', $body ) ) ), $license );
	}

	/**
	 * Signature the API puts on a response, bound to the nonce of the request it answers.
	 *
	 * @param string $nonce     Nonce of the request.
	 * @param string $timestamp Response timestamp.
	 * @param string $body      Raw response body.
	 * @param string $license   License key.
	 * @return string Lowercase hexadecimal signature.
	 */
	public static function response_signature( $nonce, $timestamp, $body, $license ) {
		return hash_hmac( 'sha256', $nonce . "\n" . $timestamp . "\n" . hash( 'sha256', $body ), $license );
	}

	/**
	 * The site's host as the API knows it: lowercase, without "www.", port or path.
	 *
	 * @return string
	 */
	public static function domain() {
		$host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		return 0 === strpos( $host, 'www.' ) ? substr( $host, 4 ) : $host;
	}

	/**
	 * @return string API base URL without a trailing slash: SITEPULSE_REMOTE_API_BASE (remote
	 *                management only), else the plugin's API base (SITEPULSE_API_BASE).
	 */
	private static function base_url() {
		return defined( 'SITEPULSE_REMOTE_API_BASE' ) ? untrailingslashit( (string) SITEPULSE_REMOTE_API_BASE ) : Sitepulse_Api_Service::api_base();
	}

	/**
	 * @param string $timestamp Unix time in seconds.
	 * @return bool Whether the timestamp is within the allowed clock skew.
	 */
	private static function is_fresh( $timestamp ) {
		return 1 === preg_match( '/^\d{1,12}$/', $timestamp ) && abs( time() - (int) $timestamp ) <= self::MAX_CLOCK_SKEW;
	}
}

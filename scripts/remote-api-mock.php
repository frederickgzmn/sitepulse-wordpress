<?php
/**
 * A stand-in for api.sitepulse.me's remote management endpoints, for local development.
 * It checks and signs requests as the API does (see docs/remote-management.md, "Development").
 *
 *   SITEPULSE_MOCK_LICENSE=<the site's license> php -S 127.0.0.1:8099 scripts/remote-api-mock.php
 *
 * Then define SITEPULSE_REMOTE_API_BASE as 'http://127.0.0.1:8099' in the site's wp-config.php.
 *   GET /mock/queue?type=refresh_inventory&args={}  queues a command
 *   GET /mock/rotate?key=<new key>                  offers a new signing key at the next check-in
 *   GET /mock/state                                  shows what the site sent
 *   GET /mock/reset                                  forgets it
 *
 * Not shipped with the plugin.
 */

$state_file = (string) ( getenv( 'SITEPULSE_MOCK_STATE' ) ?: sys_get_temp_dir() . '/sitepulse-remote-api-mock.json' );
$empty      = array(
	'key'             => (string) getenv( 'SITEPULSE_MOCK_LICENSE' ),
	'pending_key'     => null,
	'queue'           => array(),
	'updating'        => null,
	'check_ins'       => array(),
	'inventory'       => null,
	'events'          => array(),
	'results'         => array(),
	'nonces'          => array(),
);
$stored = is_file( $state_file ) ? json_decode( (string) file_get_contents( $state_file ), true ) : null;
$state  = is_array( $stored ) ? array_merge( $empty, $stored ) : $empty;
$save   = static function () use ( &$state, $state_file ) {
	file_put_contents( $state_file, json_encode( $state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ), LOCK_EX );
};
$json   = static function ( $body, $status = 200, array $headers = array() ) {
	$raw = json_encode( $body, JSON_UNESCAPED_SLASHES );
	http_response_code( $status );
	header( 'Content-Type: application/json' );
	foreach ( $headers as $name => $value ) {
		header( $name . ': ' . $value );
	}
	echo $raw;
	return $raw;
};

$path = (string) parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );

// Developer controls.
if ( 0 === strpos( $path, '/mock/' ) ) {
	if ( '/mock/queue' === $path ) {
		$args = json_decode( (string) ( $_GET['args'] ?? '{}' ), true );
		// A ULID-like id: 26 characters of Crockford's base 32.
		$id = '';
		for ( $i = 0; $i < 26; $i++ ) {
			$id .= '0123456789ABCDEFGHJKMNPQRSTVWXYZ'[ random_int( 0, 31 ) ];
		}
		$command = array(
			'id'         => $id,
			'type'       => (string) ( $_GET['type'] ?? 'refresh_inventory' ),
			'args'       => (object) ( is_array( $args ) ? $args : array() ),
			'expires_at' => gmdate( 'Y-m-d\TH:i:s\Z', time() + 900 ),
		);
		$state['queue'][] = $command;
		$save();
		$json( $command );
	} elseif ( '/mock/rotate' === $path ) {
		$state['pending_key'] = (string) ( $_GET['key'] ?? '' );
		$save();
		$json( array( 'pending_key' => $state['pending_key'] ) );
	} elseif ( '/mock/reset' === $path ) {
		$state = $empty;
		$save();
		$json( $state );
	} else {
		$json( $state );
	}
	return true;
}

if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || 0 !== strpos( $path, '/api/website/remote/' ) ) {
	$json( array( 'success' => false, 'message' => 'Not found.' ), 404 );
	return true;
}

// The site's signature, with its key (the license until rotated) or a pending rotated one.
$header    = static function ( $name ) {
	return (string) ( $_SERVER[ 'HTTP_' . strtoupper( str_replace( '-', '_', $name ) ) ] ?? '' );
};
$body      = (string) file_get_contents( 'php://input' );
$domain    = $header( 'X-SitePulse-Domain' );
$timestamp = $header( 'X-SitePulse-Timestamp' );
$nonce     = $header( 'X-SitePulse-Nonce' );
$provided  = $header( 'X-SitePulse-Signature' );
$canonical = implode( "\n", array( 'POST', $path, $domain, $timestamp, $nonce, hash( 'sha256', $body ) ) );
$key       = null;
foreach ( array_filter( array( $state['key'], $state['pending_key'] ) ) as $candidate ) {
	if ( preg_match( '/^[a-f0-9]{64}$/', $provided ) && hash_equals( hash_hmac( 'sha256', $canonical, $candidate ), $provided ) ) {
		$key = $candidate;
	}
}
if ( '' === $domain || ! ctype_digit( $timestamp ) || abs( time() - (int) $timestamp ) > 300 || 1 !== preg_match( '/^[a-f0-9]{32}$/', $nonce ) || null === $key || in_array( $nonce, $state['nonces'], true ) ) {
	$json( array( 'success' => false, 'message' => 'Invalid signature.' ), 401 );
	return true;
}
$state['nonces'] = array_slice( array_merge( $state['nonces'], array( $nonce ) ), -200 );

$request  = json_decode( $body, true );
$request  = is_array( $request ) ? $request : array();
$endpoint = substr( $path, strlen( '/api/website/remote/' ) );
$status   = 200;
$answer   = array( 'success' => true );
if ( 'check-in' === $endpoint ) {
	$state['check_ins'][] = array( 'time' => gmdate( 'Y-m-d\TH:i:s\Z' ) ) + $request;
	$remote               = array(
		'enabled'           => true,
		'active'            => true,
		'check_in_seconds'  => 60,
		'inventory_seconds' => 3600,
		'backup_provider'   => null,
	);
	if ( $state['pending_key'] ) {
		$remote['new_key'] = $state['pending_key'];
	}
	// One update at a time, as the API delivers them: the others wait until it has a result.
	$deliver = array();
	$waiting = array();
	foreach ( $state['queue'] as $command ) {
		if ( in_array( $command['type'], array( 'update_plugins', 'update_themes', 'update_core' ), true ) ) {
			if ( null !== $state['updating'] ) {
				$waiting[] = $command;
				continue;
			}
			$state['updating'] = $command['id'];
		}
		$deliver[] = $command;
	}
	$answer        += array(
		'remote_management' => $remote,
		'commands'          => $deliver,
	);
	$state['queue'] = $waiting;
} elseif ( 'inventory' === $endpoint ) {
	$state['inventory'] = $request;
} elseif ( 'events' === $endpoint ) {
	$known    = array_column( $state['events'], 'id' );
	$accepted = 0;
	foreach ( (array) ( $request['events'] ?? array() ) as $event ) {
		if ( is_array( $event ) && ! in_array( $event['id'] ?? null, $known, true ) ) {
			$state['events'][] = $event;
			++$accepted;
		}
	}
	$answer['accepted'] = $accepted;
} elseif ( 'command-result' === $endpoint ) {
	$state['results'][] = $request;
	if ( ( $request['id'] ?? null ) === $state['updating'] && in_array( $request['status'] ?? null, array( 'done', 'failed' ), true ) ) {
		$state['updating'] = null;
	}
} elseif ( 'key' === $endpoint && $key === $state['pending_key'] ) {
	$state['key']         = $key;
	$state['pending_key'] = null;
} else {
	$status = 'key' === $endpoint ? 409 : 404;
	$answer = array(
		'success' => false,
		'message' => 'key' === $endpoint ? 'no_rotation_pending' : 'Not found.',
	);
}
$save();

// Signed with the key that signed the request, bound to its nonce, as the API signs it.
$raw     = json_encode( $answer, JSON_UNESCAPED_SLASHES );
$now     = (string) time();
$headers = array(
	'X-SitePulse-Timestamp' => $now,
	'X-SitePulse-Signature' => hash_hmac( 'sha256', $nonce . "\n" . $now . "\n" . hash( 'sha256', (string) $raw ), $key ),
);
$json( $answer, $status, $headers );
return true;

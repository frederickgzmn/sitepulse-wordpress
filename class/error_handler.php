<?php
/**
 * SitePulse Error Handler
 * 
 * Handles WordPress fatal errors and PHP warnings, sending notifications
 * to configured email addresses.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Error_Handler
 */
class Sitepulse_Error_Handler {

	/**
	 * Option key for recovery mode email address
	 *
	 * @var string
	 */
	private const RECOVERY_EMAIL_OPTION_KEY = 'sitepulse_recovery_mode_email_addresses';

	/**
	 * Option key for storing last error notification timestamp
	 *
	 * @var string
	 */
	private const LAST_ERROR_NOTIFICATION_KEY = 'sitepulse_last_error_notification';

	/**
	 * Option key for storing list of reported errors
	 *
	 * @var string
	 */
	private const ERROR_LOG_OPTION_KEY = 'sitepulse_error_log';

	/**
	 * Maximum number of errors to store in the log
	 *
	 * @var int
	 */
	private const MAX_ERROR_LOG_ENTRIES = 100;

	/**
	 * Minimum time between error notifications (in seconds)
	 * Prevents email spam from repeated errors
	 *
	 * @var int
	 */
	private const NOTIFICATION_COOLDOWN = 3600; // 1 hour

	/**
	 * Previous error handler (for chaining)
	 *
	 * @var callable|null
	 */
	private static $previous_error_handler = null;

	/**
	 * Previous exception handler (for chaining)
	 *
	 * @var callable|null
	 */
	private static $previous_exception_handler = null;

	/**
	 * Initialize the error handler
	 */
	public static function init() {
		// Prevent multiple initializations
		static $initialized = false;
		if ( $initialized ) {
			return;
		}
		$initialized = true;

		// Register shutdown handler for fatal errors (must be early priority)
		register_shutdown_function( array( __CLASS__, 'handle_shutdown' ) );

		// Register error handler for warnings and notices
		// Use a high priority to ensure our handler runs
		$previous_handler = set_error_handler( array( __CLASS__, 'handle_error' ), E_ALL );
		
		// Store previous handler to chain if needed
		if ( $previous_handler !== null ) {
			self::$previous_error_handler = $previous_handler;
		}

		// Register exception handler
		$previous_exception_handler = set_exception_handler( array( __CLASS__, 'handle_exception' ) );
		
		// Store previous exception handler
		if ( $previous_exception_handler !== null ) {
			self::$previous_exception_handler = $previous_exception_handler;
		}

		// Prevent WordPress from sending its own fatal error emails
		// WordPress 5.2+ has a built-in fatal error handler that sends emails to admin_email
		// We disable it so only our custom handler sends emails to the recovery email
		
		// Method 1: Disable WordPress's fatal error handler completely
		add_filter( 'wp_fatal_error_handler_enabled', '__return_false', 999 );
		
		// Method 2: Filter the email address to empty string (backup method)
		add_filter( 'wp_fatal_error_handler_email', array( __CLASS__, 'prevent_wordpress_fatal_error_email' ), 999 );
		
		// Method 3: Intercept wp_mail calls to block WordPress's fatal error emails
		// This catches any fatal error emails WordPress might still try to send
		add_filter( 'wp_mail', array( __CLASS__, 'prevent_wordpress_fatal_error_wp_mail' ), 999, 1 );
		
		// Method 4: Set constant to disable fatal error handler (if not already set)
		// This is done via filter since we can't set constants after PHP has started
		// Note: User can also add define('WP_DISABLE_FATAL_ERROR_HANDLER', true); to wp-config.php

		// Clean up old errors on initialization (runs once per page load)
		add_action( 'init', array( __CLASS__, 'cleanup_old_errors_on_init' ), 999 );
	}

	/**
	 * Clean up old errors on initialization
	 * Runs once per page load to remove warnings/notices older than 1 hour
	 * and fatal errors older than 2 days
	 * Only runs when user is on the dashboard page to avoid performance issues
	 */
	public static function cleanup_old_errors_on_init() {
		// Only run cleanup on the admin dashboard page to avoid performance issues
		if ( ! is_admin() ) {
			return;
		}

		// Only run cleanup once per page load to avoid performance issues
		static $cleaned = false;
		if ( $cleaned ) {
			return;
		}
		$cleaned = true;

		$error_log = get_option( self::ERROR_LOG_OPTION_KEY, array(
			'errors' => array(),
			'total_count' => 0,
			'last_error_date' => null,
		) );

		if ( ! empty( $error_log['errors'] ) ) {
			$cleaned_log = self::cleanup_old_errors( $error_log );
			// Only update if something was removed
			if ( count( $cleaned_log['errors'] ) < count( $error_log['errors'] ) ) {
				update_option( self::ERROR_LOG_OPTION_KEY, $cleaned_log, false );
			}
		}
	}

	/**
	 * Handle PHP errors (warnings, notices, etc.)
	 *
	 * @param int    $errno   Error number
	 * @param string $errstr  Error message
	 * @param string $errfile File where error occurred
	 * @param int    $errline Line number where error occurred
	 * @return bool True if error was handled, false to use default handler
	 */
	public static function handle_error( $errno, $errstr, $errfile, $errline ) {
		// Handle warnings, notices, and fatal errors
		// But only log warnings/notices temporarily (removed after 1 hour)
		// Fatal errors are kept for 2 days
		if ( ! ( $errno & ( E_WARNING | E_USER_WARNING | E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR ) ) ) {
			return false; // Let WordPress handle notices and strict warnings
		}

		// Check if error reporting is disabled for this specific error type
		$error_reporting = error_reporting();
		if ( $error_reporting !== 0 && ! ( $error_reporting & $errno ) ) {
			return false;
		}

		// Determine if this is a fatal error
		$is_fatal = (bool)( $errno & ( E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR ) );

		$call_stack = self::get_call_stack();

		// Log the error
		$error_data = array(
			'type'    => self::get_error_type_name( $errno ),
			'message' => $errstr,
			'file'    => $errfile,
			'line'    => $errline,
			'timestamp' => current_time( 'mysql' ),
			'is_fatal' => $is_fatal, // Mark if this is a fatal error
			'call_stack' => $call_stack, // Store call stack
		);

		// Store error in log (warnings/notices removed after 1 hour, fatal errors kept for 2 days)
		self::log_error( $error_data );

		// Send notification only for fatal errors
		if ( $is_fatal ) {
			self::send_error_notification( $error_data, 'PHP Error' );
		}

		// Call previous error handler if it exists (chain handlers)
		if ( self::$previous_error_handler !== null && is_callable( self::$previous_error_handler ) ) {
			call_user_func( self::$previous_error_handler, $errno, $errstr, $errfile, $errline );
		}

		// Return false to allow WordPress to also handle the error
		return false;
	}

	/**
	 * Handle uncaught exceptions
	 *
	 * @param Throwable $exception The exception object
	 */
	public static function handle_exception( $exception ) {
		// Capture call stack from exception trace
		$call_stack = self::format_exception_trace( $exception->getTrace() );
		
		$error_data = array(
			'type'      => 'Uncaught Exception',
			'message'   => $exception->getMessage(),
			'file'      => $exception->getFile(),
			'line'      => $exception->getLine(),
			'trace'     => $exception->getTraceAsString(),
			'timestamp' => current_time( 'mysql' ),
			'is_fatal'  => true, // Exceptions are treated as fatal errors
			'call_stack' => $call_stack, // Store formatted call stack
		);

		// Store error in log (exceptions are kept for 2 days like fatal errors)
		self::log_error( $error_data );

		self::send_error_notification( $error_data, 'PHP Exception' );

		// Call previous exception handler if it exists (chain handlers)
		if ( self::$previous_exception_handler !== null && is_callable( self::$previous_exception_handler ) ) {
			call_user_func( self::$previous_exception_handler, $exception );
		}
	}

	/**
	 * Handle fatal errors on shutdown
	 */
	public static function handle_shutdown() {
		$error = error_get_last();

		if ( $error !== null && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR ), true ) ) {
			// Capture call stack for fatal errors
			$call_stack = self::get_call_stack();
			
			$error_data = array(
				'type'      => self::get_error_type_name( $error['type'] ),
				'message'   => $error['message'],
				'file'      => $error['file'],
				'line'      => $error['line'],
				'timestamp' => current_time( 'mysql' ),
				'is_fatal'  => true, // Shutdown errors are always fatal
				'call_stack' => $call_stack, // Store call stack
			);

			// Store error in log (fatal errors kept for 2 days)
			self::log_error( $error_data );

			self::send_error_notification( $error_data, 'Fatal Error' );
		}
	}

	/**
	 * Get human-readable error type name
	 *
	 * @param int $errno Error number
	 * @return string Error type name
	 */
	private static function get_error_type_name( $errno ) {
		$error_types = array(
			E_ERROR             => 'Fatal Error',
			E_WARNING           => 'Warning',
			E_PARSE             => 'Parse Error',
			E_NOTICE            => 'Notice',
			E_CORE_ERROR        => 'Core Error',
			E_CORE_WARNING      => 'Core Warning',
			E_COMPILE_ERROR     => 'Compile Error',
			E_COMPILE_WARNING   => 'Compile Warning',
			E_USER_ERROR        => 'User Error',
			E_USER_WARNING      => 'User Warning',
			E_USER_NOTICE       => 'User Notice',
			2048                => 'Strict Notice', // E_STRICT; the constant is deprecated since PHP 8.4.
			E_RECOVERABLE_ERROR => 'Recoverable Error',
			E_DEPRECATED        => 'Deprecated',
			E_USER_DEPRECATED   => 'User Deprecated',
		);

		return isset( $error_types[ $errno ] ) ? $error_types[ $errno ] : 'Unknown Error';
	}

	/**
	 * Send error notification email
	 *
	 * @param array  $error_data Error information
	 * @param string $error_type Type of error for subject line
	 */
	private static function send_error_notification( $error_data, $error_type ) {
		// Get recovery email address
		$recovery_email = self::get_recovery_email();

		// Skip if no email is configured
		if ( empty( $recovery_email ) || ! is_email( $recovery_email ) ) {
			return;
		}

		// Check cooldown to prevent email spam
		$last_notification = get_option( self::LAST_ERROR_NOTIFICATION_KEY, 0 );
		$current_time = current_time( 'timestamp' );

		if ( ( $current_time - $last_notification ) < self::NOTIFICATION_COOLDOWN ) {
			return; // Too soon since last notification
		}

		// Update last notification timestamp
		update_option( self::LAST_ERROR_NOTIFICATION_KEY, $current_time, false );

		// Prepare email content
		$site_url = home_url();
		$site_name = get_bloginfo( 'name' );
		$admin_email = get_option( 'admin_email' );

		$subject = sprintf(
			/* translators: %1$s: Site name, %2$s: Error type */
			'[%1$s] SitePulse: %2$s Detected',
			$site_name,
			$error_type
		);

		$message = sprintf(
			/* translators: %1$s: Site name, %2$s: Site URL, %3$s: Error type, %4$s: Error message, %5$s: File, %6$s: Line, %7$s: Timestamp */
			"A %3\$s has been detected on your WordPress site.\n\n" .
			"Site: %1\$s (%2\$s)\n" .
			"Error Type: %3\$s\n" .
			"Error Message: %4\$s\n" .
			"File: %5\$s\n" .
			"Line: %6\$s\n" .
			"Timestamp: %7\$s\n\n",
			$site_name,
			$site_url,
			$error_type,
			$error_data['message'],
			$error_data['file'],
			$error_data['line'],
			$error_data['timestamp']
		);

		// Add stack trace if available
		if ( isset( $error_data['trace'] ) && ! empty( $error_data['trace'] ) ) {
			$message .= "Stack Trace:\n" . $error_data['trace'] . "\n\n";
		}

		$message .= "This is an automated notification from SitePulse Error Handler.\n";
		$message .= "Please investigate and resolve this issue as soon as possible.\n";

		// Set email headers
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: ' . $site_name . ' <' . $admin_email . '>',
		);

		// Send email
		wp_mail( $recovery_email, $subject, $message, $headers );
	}

	/**
	 * Get recovery mode email address
	 * Checks both the error handler option and settings class
	 *
	 * @return string|null Email address or null if not set
	 */
	public static function get_recovery_email() {
		// First check settings class
		if ( class_exists( 'Sitepulse_Settings' ) ) {
			$settings = Sitepulse_Settings::getInstance();
			$all_settings = $settings->get_all_settings();
			if ( isset( $all_settings['recovery_mode_email_addresses'] ) && ! empty( $all_settings['recovery_mode_email_addresses'] ) ) {
				$email = $all_settings['recovery_mode_email_addresses'];
				if ( is_email( $email ) ) {
					return $email;
				}
			}
		}

		// Fallback to direct option
		$email = get_option( self::RECOVERY_EMAIL_OPTION_KEY, '' );
		return ! empty( $email ) && is_email( $email ) ? $email : null;
	}

	/**
	 * Set recovery mode email address
	 * Updates both the error handler option and settings class
	 *
	 * @param string $email Email address
	 * @return bool True on success, false on failure
	 */
	public static function set_recovery_email( $email ) {
		if ( empty( $email ) ) {
			delete_option( self::RECOVERY_EMAIL_OPTION_KEY );
			// Also clear from settings
			if ( class_exists( 'Sitepulse_Settings' ) ) {
				$settings = Sitepulse_Settings::getInstance();
				$settings->update_settings( array( 'recovery_mode_email_addresses' => '' ) );
			}
			return true;
		}

		if ( ! is_email( $email ) ) {
			return false;
		}

		$sanitized_email = sanitize_email( $email );

		// Update direct option
		update_option( self::RECOVERY_EMAIL_OPTION_KEY, $sanitized_email, false );

		// Also update settings class
		if ( class_exists( 'Sitepulse_Settings' ) ) {
			$settings = Sitepulse_Settings::getInstance();
			$settings->update_settings( array( 'recovery_mode_email_addresses' => $sanitized_email ) );
		}

		return true;
	}

	/**
	 * Log error to WordPress option
	 * Stores error details, count, and date in a structured format
	 * Warnings/notices are removed after 1 hour, fatal errors kept for 2 days
	 *
	 * @param array $error_data Error information
	 */
	private static function log_error( $error_data ) {
		// Get existing error log
		$error_log = get_option( self::ERROR_LOG_OPTION_KEY, array(
			'errors' => array(),
			'total_count' => 0,
			'last_error_date' => null,
		) );

		// Ensure structure is correct
		if ( ! is_array( $error_log ) || ! isset( $error_log['errors'] ) ) {
			$error_log = array(
				'errors' => array(),
				'total_count' => 0,
				'last_error_date' => null,
			);
		}

		// Clean up old errors before adding new one
		// Warnings/notices removed after 1 hour, fatal errors after 2 days
		$error_log = self::cleanup_old_errors( $error_log );

		// Determine if this is a fatal error
		$is_fatal = isset( $error_data['is_fatal'] ) ? (bool) $error_data['is_fatal'] : false;

		// Create error entry
		$error_entry = array(
			'type'      => isset( $error_data['type'] ) ? $error_data['type'] : 'Unknown',
			'message'   => isset( $error_data['message'] ) ? $error_data['message'] : '',
			'file'      => isset( $error_data['file'] ) ? $error_data['file'] : '',
			'line'      => isset( $error_data['line'] ) ? $error_data['line'] : 0,
			'date'      => isset( $error_data['timestamp'] ) ? $error_data['timestamp'] : current_time( 'mysql' ),
			'timestamp' => current_time( 'timestamp' ),
			'is_fatal'  => $is_fatal, // Mark if this is a fatal error
			'call_stack' => isset( $error_data['call_stack'] ) ? $error_data['call_stack'] : array(), // Store call stack
		);

		// Check retention period based on error type
		$one_hour_ago = current_time( 'timestamp' ) - HOUR_IN_SECONDS;
		$two_days_ago = current_time( 'timestamp' ) - ( 2 * DAY_IN_SECONDS );
		
		// For fatal errors: keep if within 2 days
		// For warnings/notices: keep if within 1 hour
		$retention_threshold = $is_fatal ? $two_days_ago : $one_hour_ago;
		
		if ( $error_entry['timestamp'] >= $retention_threshold ) {
			// Add to errors array
			$error_log['errors'][] = $error_entry;

			// Limit the number of stored errors to prevent option from growing too large
			if ( count( $error_log['errors'] ) > self::MAX_ERROR_LOG_ENTRIES ) {
				// Remove oldest errors, keeping only the most recent
				$error_log['errors'] = array_slice( $error_log['errors'], -self::MAX_ERROR_LOG_ENTRIES );
			}

			// Update count and last error date
			$error_log['total_count'] = isset( $error_log['total_count'] ) ? (int) $error_log['total_count'] + 1 : 1;
			$error_log['last_error_date'] = $error_entry['date'];
		}

		// Save to option
		update_option( self::ERROR_LOG_OPTION_KEY, $error_log, false );
	}

	/**
	 * Clean up old errors based on error type
	 * Warnings/notices are removed after 1 hour
	 * Fatal errors are kept for 2 days
	 *
	 * @param array $error_log Error log array
	 * @return array Cleaned error log
	 */
	private static function cleanup_old_errors( $error_log ) {
		if ( ! is_array( $error_log ) || ! isset( $error_log['errors'] ) || ! is_array( $error_log['errors'] ) ) {
			return $error_log;
		}

		$one_hour_ago = current_time( 'timestamp' ) - HOUR_IN_SECONDS;
		$two_days_ago = current_time( 'timestamp' ) - ( 2 * DAY_IN_SECONDS );
		$filtered_errors = array();

		foreach ( $error_log['errors'] as $error ) {
			// Get error timestamp
			$error_timestamp = null;
			if ( isset( $error['timestamp'] ) ) {
				$error_timestamp = $error['timestamp'];
			} elseif ( isset( $error['date'] ) ) {
				$error_timestamp = strtotime( $error['date'] );
			}

			if ( $error_timestamp === null || $error_timestamp === false ) {
				continue; // Skip errors without valid timestamp
			}

			// Determine retention period based on error type
			$is_fatal = isset( $error['is_fatal'] ) ? (bool) $error['is_fatal'] : false;
			$retention_threshold = $is_fatal ? $two_days_ago : $one_hour_ago;

			// Keep error if it's within its retention period
			if ( $error_timestamp >= $retention_threshold ) {
				$filtered_errors[] = $error;
			}
		}

		$error_log['errors'] = $filtered_errors;

		// Update last error date if errors exist
		if ( ! empty( $filtered_errors ) ) {
			$last_error = end( $filtered_errors );
			$error_log['last_error_date'] = isset( $last_error['date'] ) ? $last_error['date'] : null;
		} else {
			$error_log['last_error_date'] = null;
		}

		return $error_log;
	}

	/**
	 * Get error log from WordPress option
	 * Automatically filters out errors older than 2 days
	 *
	 * @return array Error log with errors list, count, and last error date
	 */
	public static function get_error_log() {
		$error_log = get_option( self::ERROR_LOG_OPTION_KEY, array(
			'errors' => array(),
			'total_count' => 0,
			'last_error_date' => null,
		) );

		// Ensure structure is correct
		if ( ! is_array( $error_log ) || ! isset( $error_log['errors'] ) ) {
			return array(
				'errors' => array(),
				'total_count' => 0,
				'last_error_date' => null,
			);
		}

		// Clean up old errors before returning
		$error_log = self::cleanup_old_errors( $error_log );

		// Save cleaned log back to option
		update_option( self::ERROR_LOG_OPTION_KEY, $error_log, false );

		return $error_log;
	}

	/**
	 * Get error log statistics for status info
	 *
	 * @return array Error log statistics
	 */
	public static function get_error_log_stats() {
		$error_log = self::get_error_log();
		
		$error_count = is_array( $error_log['errors'] ) ? count( $error_log['errors'] ) : 0;
		$last_error_date = isset( $error_log['last_error_date'] ) ? $error_log['last_error_date'] : null;
		$total_count = isset( $error_log['total_count'] ) ? (int) $error_log['total_count'] : 0;

		// Count fatal errors vs warnings
		$fatal_count = 0;
		$warning_count = 0;
		if ( is_array( $error_log['errors'] ) ) {
			foreach ( $error_log['errors'] as $error ) {
				$is_fatal = isset( $error['is_fatal'] ) ? (bool) $error['is_fatal'] : false;
				if ( $is_fatal ) {
					$fatal_count++;
				} else {
					$warning_count++;
				}
			}
		}

		return array(
			'error_count' => $error_count,
			'fatal_count' => $fatal_count,
			'warning_count' => $warning_count,
			'total_count' => $total_count,
			'last_error_date' => $last_error_date,
		);
	}

	/**
	 * Clear error log
	 *
	 * @return bool True on success
	 */
	public static function clear_error_log() {
		return delete_option( self::ERROR_LOG_OPTION_KEY );
	}

	/**
	 * Get call stack formatted
	 *
	 * @return array Formatted call stack
	 */
	private static function get_call_stack() {
		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$stack = array();

		// Skip the first few entries (error handler itself)
		$skip = 3; // Skip: get_call_stack, handle_error/handle_shutdown, log_error
		$trace = array_slice( $trace, $skip );

		foreach ( $trace as $index => $frame ) {
			$stack[] = self::format_stack_frame( $frame, $index );
		}

		return $stack;
	}

	/**
	 * Format a single stack frame
	 *
	 * @param array $frame Stack frame from debug_backtrace
	 * @param int   $index Frame index
	 * @return array Formatted frame
	 */
	private static function format_stack_frame( $frame, $index ) {
		$formatted = array(
			'function' => '',
			'file'     => '',
			'line'     => 0,
			'class'    => '',
			'type'     => '',
		);

		// Get function name
		if ( isset( $frame['function'] ) ) {
			$formatted['function'] = $frame['function'];
		}

		// Get class and type (static or instance)
		if ( isset( $frame['class'] ) ) {
			$formatted['class'] = $frame['class'];
			$formatted['type'] = isset( $frame['type'] ) ? $frame['type'] : '->';
		}

		// Get file and line
		if ( isset( $frame['file'] ) ) {
			$formatted['file'] = $frame['file'];
		}
		if ( isset( $frame['line'] ) ) {
			$formatted['line'] = $frame['line'];
		}

		return $formatted;
	}

	/**
	 * Format exception trace to match our call stack format
	 *
	 * @param array $trace Exception trace
	 * @return array Formatted call stack
	 */
	private static function format_exception_trace( $trace ) {
		$stack = array();

		foreach ( $trace as $index => $frame ) {
			$stack[] = self::format_stack_frame( $frame, $index );
		}

		return $stack;
	}

	/**
	 * Format call stack for display
	 *
	 * @param array $call_stack Call stack array
	 * @return string Formatted call stack string
	 */
	public static function format_call_stack_for_display( $call_stack ) {
		if ( empty( $call_stack ) || ! is_array( $call_stack ) ) {
			return '';
		}

		$output = '';
		$output .= '<div class="sp-call-stack">';
		$output .= '<div class="sp-call-stack-header"><strong>Call stack:</strong></div>';
		$output .= '<ol class="sp-call-stack-list">';

		foreach ( $call_stack as $index => $frame ) {
			$function_name = '';
			
			// Build function name
			if ( ! empty( $frame['class'] ) ) {
				$function_name = $frame['class'] . ( isset( $frame['type'] ) ? $frame['type'] : '->' ) . $frame['function'];
			} else {
				$function_name = isset( $frame['function'] ) ? $frame['function'] : '';
			}

			// Format file path (remove ABSPATH for cleaner display)
			$file_path = isset( $frame['file'] ) ? $frame['file'] : '';
			$line = isset( $frame['line'] ) ? $frame['line'] : 0;
			
			// Make file path relative to ABSPATH if possible
			if ( defined( 'ABSPATH' ) && $file_path && strpos( $file_path, ABSPATH ) === 0 ) {
				$file_path = str_replace( ABSPATH, '', $file_path );
			}

			$output .= '<li class="sp-call-stack-item">';
			if ( $function_name ) {
				$output .= '<code class="sp-call-stack-function">' . esc_html( $function_name ) . '</code>';
			}
			
			if ( $file_path ) {
				if ( $function_name ) {
					$output .= '<br>';
				}
				$output .= '<span class="sp-call-stack-file">' . esc_html( $file_path );
				if ( $line > 0 ) {
					$output .= ':' . esc_html( $line );
				}
				$output .= '</span>';
			}
			
			$output .= '</li>';
		}

		$output .= '</ol>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Format error for display
	 *
	 * @param array $error Error data array
	 * @return string Formatted error display
	 */
	public static function format_error_for_display( $error ) {
		if ( ! is_array( $error ) ) {
			return '';
		}

		$output = '';
		
		// Error type and message
		$error_type = isset( $error['type'] ) ? $error['type'] : 'Unknown Error';
		$error_message = isset( $error['message'] ) ? $error['message'] : '';
		$error_file = isset( $error['file'] ) ? $error['file'] : '';
		$error_line = isset( $error['line'] ) ? $error['line'] : 0;
		
		// Make file path relative to ABSPATH if possible
		if ( defined( 'ABSPATH' ) && $error_file && strpos( $error_file, ABSPATH ) === 0 ) {
			$error_file_display = str_replace( ABSPATH, '', $error_file );
		} else {
			$error_file_display = $error_file;
		}

		$output .= '<div class="sp-error-display">';
		$output .= '<div class="sp-error-header">';
		$output .= '<strong class="sp-error-type">' . esc_html( $error_type ) . ':</strong> ';
		$output .= '<span class="sp-error-message">' . esc_html( $error_message ) . '</span>';
		$output .= '</div>';
		
		if ( $error_file ) {
			$output .= '<div class="sp-error-location">';
			$output .= 'in <code class="sp-error-file">' . esc_html( $error_file_display );
			if ( $error_line > 0 ) {
				$output .= ':' . esc_html( $error_line );
			}
			$output .= '</code>';
			$output .= '</div>';
		}

		// Add call stack if available
		if ( isset( $error['call_stack'] ) && ! empty( $error['call_stack'] ) ) {
			$output .= self::format_call_stack_for_display( $error['call_stack'] );
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Prevent WordPress from sending fatal error emails to admin email
	 * Returns empty string to disable WordPress's default email sending
	 *
	 * @param string $email The email address WordPress would use
	 * @return string Empty string to prevent WordPress from sending emails
	 */
	public static function prevent_wordpress_fatal_error_email( $email ) {
		// Return empty string to prevent WordPress from sending its own fatal error emails
		// Our custom handler will send emails to the recovery email address instead
		return '';
	}

	/**
	 * Prevent WordPress from sending fatal error emails via wp_mail
	 * Intercepts wp_mail calls that are for fatal error notifications
	 *
	 * @param array $mail_data Mail data array
	 * @return array|false Modified mail data or false to prevent sending
	 */
	public static function prevent_wordpress_fatal_error_wp_mail( $mail_data ) {
		// Preserve our shutdown notification only for the configured recovery recipient.
		// Its subject also contains the generic WordPress fatal-error pattern below.
		$recovery_email = self::get_recovery_email();
		$recipients = isset( $mail_data['to'] ) ? (array) $mail_data['to'] : array();
		if ( $recovery_email && count( $recipients ) === 1
			&& reset( $recipients ) === $recovery_email
			&& isset( $mail_data['subject'] )
			&& $mail_data['subject'] === sprintf( '[%s] SitePulse: Fatal Error Detected', get_bloginfo( 'name' ) ) ) {
			return $mail_data;
		}

		// Check if this is a WordPress fatal error email
		// WordPress fatal error emails typically have specific subject patterns
		if ( isset( $mail_data['subject'] ) ) {
			$subject = $mail_data['subject'];
			
			// Check for WordPress fatal error email patterns
			$fatal_error_patterns = array(
				'fatal error',
				'fatal error occurred',
				'your site is experiencing',
				'critical error',
			);
			
			foreach ( $fatal_error_patterns as $pattern ) {
				if ( stripos( $subject, $pattern ) !== false ) {
					// This is a WordPress fatal error email - prevent it
					// Our custom handler will send the email to recovery address instead
					return false;
				}
			}
		}
		
		// Check if the email is going to admin email and contains error information
		// WordPress sends fatal errors to admin_email by default
		if ( isset( $mail_data['to'] ) ) {
			$to = is_array( $mail_data['to'] ) ? $mail_data['to'][0] : $mail_data['to'];
			$admin_email = get_option( 'admin_email' );
			
			// If email is going to admin and contains error-related content, block it
			// unless it's going to our recovery email
			if ( $to === $admin_email && $to !== $recovery_email ) {
				if ( isset( $mail_data['message'] ) ) {
					$message = $mail_data['message'];
					$error_keywords = array( 'fatal', 'error', 'critical', 'wp_die', 'exception' );
					$error_count = 0;
					foreach ( $error_keywords as $keyword ) {
						if ( stripos( $message, $keyword ) !== false ) {
							$error_count++;
						}
					}
					// If message contains multiple error keywords, it's likely a fatal error email
					if ( $error_count >= 2 ) {
						return false; // Block WordPress's default fatal error email
					}
				}
			}
		}
		
		// Allow other emails to proceed normally
		return $mail_data;
	}
}
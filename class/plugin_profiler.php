<?php
declare(strict_types=1);

/**
 * SitePulse Plugin Profiler
 * Tracks RAM usage, CPU usage, and loading time for each installed plugin
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Sitepulse_Plugin_Profiler' ) ) {
	
	/**
	 * Class Sitepulse_Plugin_Profiler
	 */
	final class Sitepulse_Plugin_Profiler {
		
		/**
		 * Option key for storing plugin profiling data
		 */
		const OPTION_KEY = 'sitepulse_plugin_profiler_stats';
		
		/**
		 * Option key for storing enabled status
		 */
		const OPTION_ENABLED = 'sitepulse_plugin_profiler_enabled';
		
		/**
		 * Current time option key
		 */
		const OPTION_CURRENT_TIME = 'sitepulse_plugin_profiler_current_time';
		
		/**
		 * Option key for storing actual measured total load time
		 */
		const OPTION_TOTAL_TIME = 'sitepulse_plugin_profiler_total_time';
		
		/**
		 * Profiling data storage
		 *
		 * @var array<string,array>
		 */
		private static $stats = [];
		
		/**
		 * Actual measured total plugin load time in milliseconds
		 *
		 * @var float
		 */
		private static $measured_total_time = 0.0;
		
		/**
		 * Whether profiling is enabled
		 *
		 * @var bool
		 */
		public static $enabled = true;
		
		/**
		 * Memory usage before plugins load
		 *
		 * @var int
		 */
		private static $memory_before = 0;
		
		/**
		 * CPU time before plugins load
		 *
		 * @var float
		 */
		private static $cpu_before = 0.0;
		
		/**
		 * Individual plugin load times captured during execution
		 *
		 * @var array<string,float>
		 */
		private static $plugin_timings = [];

		/**
		 * Profiler call counts captured before the remaining plugins load.
		 *
		 * @var array<string,int>
		 */
		private static $profiler_call_baseline = [];
		
	/**
	 * Individual plugin memory usage captured during execution
	 *
	 * @var array<string,int>
	 */
	private static $plugin_memory = [];
	
	/**
	 * Global flag to control data collection
	 * Set to true when AJAX request is sent or plugin activation hook fires
	 *
	 * @var bool
	 */
	private static $should_collect_data = false;
	
	/**
	 * Enable data collection for the current request
	 * This should be called by AJAX endpoints or activation hooks
	 */
	public static function enable_data_collection() {
		self::$should_collect_data = true;
	}
	
	/**
	 * Check if data collection is enabled for current request
	 *
	 * @return bool
	 */
	public static function should_collect_data() {
		return self::$should_collect_data;
	}
	
	/**
	 * Reset data collection flag to false
	 * This should be called after data collection completes
	 */
	public static function reset_data_collection() {
		self::$should_collect_data = false;
	}
	
	/**
	 * Initialize the plugin profiler
	 */
	public static function init() {
		// Initialize WP_Filesystem for file operations
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		
		// Load persisted stats and enabled flag
		self::$stats = get_option( self::OPTION_KEY, [] );
		self::$measured_total_time = (float) get_option( self::OPTION_TOTAL_TIME, 0.0 );			// Validate and clean loaded stats - remove entries with invalid values
			if ( ! empty( self::$stats ) && is_array( self::$stats ) ) {
				foreach ( self::$stats as $key => $stat ) {
					$needs_reset = false;
					
					// Profiler measurements may legitimately exceed several seconds.
					if ( isset( $stat['avg_time'] ) && $stat['avg_time'] < 0.0 ) {
						$needs_reset = true;
					}
					
					// If avg_memory is unreasonable (> 50MB per plugin or < 0), reset it
					// Most plugins use between 100KB - 10MB
					if ( isset( $stat['avg_memory'] ) && ( $stat['avg_memory'] > 52428800 || $stat['avg_memory'] < 0 ) ) {
						$needs_reset = true;
					}
					
					if ( $needs_reset ) {
						// Reset this plugin's stats
						self::$stats[ $key ]['loads'] = 0;
						self::$stats[ $key ]['total_memory'] = 0;
						self::$stats[ $key ]['total_time'] = 0.0;
						self::$stats[ $key ]['total_cpu'] = 0.0;
						self::$stats[ $key ]['avg_memory'] = 0;
						self::$stats[ $key ]['avg_time'] = 0.0;
						self::$stats[ $key ]['avg_cpu'] = 0.0;
					}
				}
			}
			
			// A measured total can legitimately exceed several seconds.
			if ( self::$measured_total_time < 0.0 ) {
				self::$measured_total_time = 0.0;
			}
			
			$enabled_opt = get_option( self::OPTION_ENABLED, null );
			
			if ( $enabled_opt !== null ) {
				self::$enabled = (bool) $enabled_opt;
			}
			
			// Only track if enabled
			if ( ! self::$enabled ) {
				return;
			}
			
			// IMPORTANT: Capture baseline IMMEDIATELY, right now, before any more plugins load
			// SitePulse loads early, so this captures the state after SitePulse but before other plugins
			self::capture_baseline();
			
			// Set up individual plugin tracking
			self::setup_plugin_tracking();

			// Snapshot persisted profiler call counts before later plugins load.
			self::capture_profiler_call_baseline();
			
			// Track after plugins are loaded - use high priority to run after all plugins initialize
			add_action( 'plugins_loaded', [ __CLASS__, 'track_plugins' ], PHP_INT_MAX );
			
			// Persist stats at end of request
			add_action( 'shutdown', [ __CLASS__, 'persist' ] );
			
			// Hook into plugin activation to trigger immediate data collection
			add_action( 'activated_plugin', [ __CLASS__, 'on_plugin_activated' ], 10, 2 );
		}
		
		/**
		 * Capture baseline memory and CPU before other plugins load
		 */
		public static function capture_baseline() {
			if ( ! self::$enabled ) {
				return;
			}
			
			// Only capture if not already captured (in case this is called multiple times)
			if ( self::$memory_before === 0 && self::$cpu_before === 0.0 ) {
				self::$memory_before = memory_get_usage( true );
			self::$cpu_before = microtime( true );
		}
	}
	
	/**
	 * Set up individual plugin tracking by intercepting plugin loads
	 */
	public static function setup_plugin_tracking() {
		if ( ! self::$enabled ) {
			return;
		}			// Initialize tracking arrays
			self::$plugin_timings = [];
			self::$plugin_memory = [];
			
		}

	/**
	 * Capture profiler call counts at the beginning of this request.
	 */
	private static function capture_profiler_call_baseline() {
		self::$profiler_call_baseline = [];

		if ( ! class_exists( 'Sitepulse_Profiler' ) || ! method_exists( 'Sitepulse_Profiler', 'get_stats_by_hook' ) ) {
			return;
		}

		foreach ( Sitepulse_Profiler::get_stats_by_hook( 'plugin_bootstrap' ) as $stat ) {
			if ( ! is_array( $stat ) ) {
				continue;
			}

			$fileline = str_replace( '\\', '/', (string) ( $stat['fileline'] ?? '' ) );
			if ( '' === $fileline ) {
				continue;
			}

			self::$profiler_call_baseline[ $fileline ] = (int) ( $stat['calls'] ?? 0 );
		}
	}
		
	/**
	 * Hook callback when a plugin is activated
	 * Triggers data collection for the current request
	 *
	 * @param string $plugin Path to the plugin file relative to the plugins directory.
	 * @param bool   $network_wide Whether to enable the plugin for all sites in the network.
	 */
	public static function on_plugin_activated( $plugin, $network_wide = false ) {
		if ( ! self::$enabled ) {
			return;
		}
		
		// Enable data collection for this request
		self::$should_collect_data = true;
		
		// Also set a transient with the activated plugin name for notification
		set_transient( 'sitepulse_plugin_activated', $plugin, 60 );
	}
	
	/**
	 * Track all active plugins and measure their impact
	 */
	public static function track_plugins() {
		if ( ! self::$enabled ) {
			return;
		}
		
		// Only collect data if the global flag is set to true
		// This flag is set by AJAX requests or plugin activation hooks
		if ( ! self::$should_collect_data ) {
			return;
		}

		// Sitepulse_Profiler is the only timing engine. This class only adapts
		// plugin_bootstrap rows to the existing plugin activity data shape.
		if ( ! class_exists( 'Sitepulse_Profiler' ) || ! method_exists( 'Sitepulse_Profiler', 'get_stats_by_hook' ) ) {
			return;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins   = get_plugins();
		$active_plugins = (array) get_option( 'active_plugins', [] );

		if ( is_multisite() ) {
			$network_plugins = (array) get_site_option( 'active_sitewide_plugins', [] );
			$active_plugins  = array_merge( $active_plugins, array_keys( $network_plugins ) );
		}

		$active_plugins = array_unique( array_map(
			static function ( $plugin_file ) {
				return str_replace( '\\', '/', (string) $plugin_file );
			},
			$active_plugins
		) );

		$plugin_prefix       = 'wp-content/plugins/';
		$profiler_by_plugin  = [];
		$mapped_stats        = [];
		$measured_total_time = 0.0;
		$profiler_stats      = Sitepulse_Profiler::get_stats_by_hook( 'plugin_bootstrap' );

		foreach ( $profiler_stats as $stat ) {
			if ( ! is_array( $stat ) || 'plugin_bootstrap' !== ( $stat['hook'] ?? '' ) ) {
				continue;
			}

			$fileline = str_replace( '\\', '/', (string) ( $stat['fileline'] ?? '' ) );
			if ( 0 !== strpos( $fileline, $plugin_prefix ) ) {
				continue;
			}

			$plugin_file = ltrim( substr( $fileline, strlen( $plugin_prefix ) ), '/' );
			if ( '' === $plugin_file ) {
				continue;
			}

			$profiler_by_plugin[ $plugin_file ] = [
				'fileline' => $fileline,
				'stat'     => $stat,
			];
		}

		foreach ( $active_plugins as $plugin_file ) {
			if ( '' === $plugin_file || ! isset( $all_plugins[ $plugin_file ] ) ) {
				continue;
			}

			$plugin_slug = dirname( $plugin_file );
			if ( '.' === $plugin_slug ) {
				$plugin_slug = pathinfo( $plugin_file, PATHINFO_FILENAME );
			}

			// SitePulse starts the interval engine, so it cannot measure its own
			// bootstrap as a complete plugin interval.
			if ( 'sitepulse' === $plugin_slug ) {
				continue;
			}

			$plugin_data = $all_plugins[ $plugin_file ];
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
			$file_size   = is_file( $plugin_path ) ? (int) filesize( $plugin_path ) : 0;
			$key         = md5( $plugin_file );
			$stat        = [];
			$is_fresh    = false;

			if ( isset( $profiler_by_plugin[ $plugin_file ] ) ) {
				$fileline      = $profiler_by_plugin[ $plugin_file ]['fileline'];
				$stat          = $profiler_by_plugin[ $plugin_file ]['stat'];
				$baseline_calls = (int) ( self::$profiler_call_baseline[ $fileline ] ?? 0 );
				$current_calls  = (int) ( $stat['calls'] ?? 0 );
				$is_fresh       = $current_calls > $baseline_calls;
			}

			$mapped_stats[ $key ] = [
				'plugin_file'    => $plugin_file,
				'plugin_name'    => isset( $plugin_data['Name'] ) ? $plugin_data['Name'] : $plugin_slug,
				'plugin_version' => isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '0.0.0',
				'plugin_slug'    => $plugin_slug,
				'file_size'      => $file_size,
				'hook'           => 'plugin_bootstrap',
				'priority'       => (int) ( $stat['priority'] ?? 0 ),
				'sig'            => (string) ( $stat['sig'] ?? 'include' ),
				'fileline'       => (string) ( $stat['fileline'] ?? '' ),
				'source'         => (string) ( $stat['source'] ?? $plugin_slug ),
				'calls'          => $is_fresh ? (int) ( $stat['calls'] ?? 0 ) : 0,
				'total'             => $is_fresh ? (float) ( $stat['total'] ?? 0.0 ) : 0.0,
				'total_ms'          => $is_fresh ? (float) ( $stat['total_ms'] ?? 0.0 ) : 0.0,
				'avg_ms'            => $is_fresh ? (float) ( $stat['avg_ms'] ?? 0.0 ) : 0.0,
				'max'               => $is_fresh ? (float) ( $stat['max'] ?? 0.0 ) : 0.0,
				'max_ms'            => $is_fresh ? (float) ( $stat['max_ms'] ?? 0.0 ) : 0.0,
				'current_load_time' => $is_fresh ? (float) ( $stat['current_load_time'] ?? 0.0 ) : 0.0,
			];

			if ( $is_fresh ) {
				$measured_total_time += max( 0.0, (float) ( $stat['current_load_time'] ?? 0.0 ) );
			}
		}

		self::$stats               = $mapped_stats;
		self::$measured_total_time = $measured_total_time;
	}
		
	/**
	 * Persist stats to database
	 */
	public static function persist() {
		if ( ! self::$enabled ) {
			return;
		}
		
		update_option( self::OPTION_CURRENT_TIME, current_time( 'mysql' ), false );
		update_option( self::OPTION_KEY, self::$stats, false );
		update_option( self::OPTION_TOTAL_TIME, self::$measured_total_time, false );
		
		// Reset the collection flag after persisting data
		self::reset_data_collection();
	}
	
	/**
		 * Clear all plugin profiling data
		 */
		public function clear_stats() {
			self::$stats = [];
			self::$measured_total_time = 0.0;
			delete_option( self::OPTION_TOTAL_TIME );
			self::persist();
		}
		
		/**
		 * Get all plugin profiling stats
		 *
		 * @return array Plugin stats
		 */
		public static function get_stats() {
			return self::$stats;
		}
		
		/**
		 * Get the actual measured total plugin load time
		 *
		 * @return float Total time in milliseconds
		 */
		public static function get_total_load_time() {
			return self::$measured_total_time;
		}
		
		/**
		 * Get stats for a specific plugin
		 *
		 * @param string $plugin_file Plugin file path
		 * @return array|null Plugin stats or null if not found
		 */
		public static function get_plugin_stats( $plugin_file ) {
			$key = md5( $plugin_file );
			return isset( self::$stats[ $key ] ) ? self::$stats[ $key ] : null;
		}
		
		/**
		 * Format memory size for display
		 *
		 * @param int $bytes Memory in bytes
		 * @return string Formatted memory string
		 */
		public static function format_memory( $bytes ) {
			return Sitepulse_Utils::format_memory( $bytes );
		}
		
		/**
		 * Format time for display - shows only milliseconds or seconds
		 *
		 * @param float $ms Time in milliseconds
		 * @return string Formatted time string (e.g., "125.50 ms" or "2.35 s")
		 */
		public static function format_time( $ms ) {
			return Sitepulse_Utils::format_time( $ms, 'ms' );
		}
	}
}

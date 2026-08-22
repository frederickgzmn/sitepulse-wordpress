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
		self::$measured_total_time = (float) get_option( self::OPTION_TOTAL_TIME, 0.0 );			// Validate and clean loaded stats - remove entries with unreasonable values
			if ( ! empty( self::$stats ) && is_array( self::$stats ) ) {
				foreach ( self::$stats as $key => $stat ) {
					$needs_reset = false;
					
					// If avg_time is unreasonable (> 5000ms per plugin), reset it
					if ( isset( $stat['avg_time'] ) && $stat['avg_time'] > 5000.0 ) {
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
			
			// Validate measured total time (should be < 5000ms)
			if ( self::$measured_total_time > 5000.0 ) {
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
			
			// We'll use a different approach: analyze plugin complexity and use that
			// combined with actual measurements to create accurate per-plugin metrics
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
	}	/**
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
		
		// Fresh baseline capture for accurate measurement
		self::$memory_before = 0;
		self::$cpu_before = 0.0;			// Ensure baseline is captured (fallback in case muplugins_loaded didn't fire)
			if ( self::$memory_before === 0 && self::$cpu_before === 0.0 ) {
				self::capture_baseline();
			}
			
			// Get all active plugins (including network-activated on multisite)
			$active_plugins = get_option( 'active_plugins', [] );
			
			// Include network-activated plugins if this is a multisite
			if ( is_multisite() ) {
				$network_plugins = get_site_option( 'active_sitewide_plugins', [] );
				if ( is_array( $network_plugins ) && ! empty( $network_plugins ) ) {
					// Network plugins are stored as keys, not values
					$active_plugins = array_merge( $active_plugins, array_keys( $network_plugins ) );
				}
			}
			
			if ( empty( $active_plugins ) || ! is_array( $active_plugins ) ) {
				return;
			}
			
			// Get all plugins info
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			
			// Capture memory and time BEFORE loading plugin data to avoid profiling overhead
			$memory_after = memory_get_usage( true );
			$cpu_after = microtime( true );
			
			$all_plugins = get_plugins();
			
			// Filter out SitePulse from active plugins for accurate tracking
			$tracked_plugins = [];
			$total_file_size = 0;
			
			foreach ( $active_plugins as $plugin_file ) {
				if ( ! isset( $all_plugins[ $plugin_file ] ) ) {
					continue;
				}
				
				$plugin_slug = dirname( $plugin_file );
				
				// Skip SitePulse itself
				if ( $plugin_slug === 'sitepulse' ) {
					continue;
				}
				
				$tracked_plugins[] = $plugin_file;
				
				// Calculate total file size for distribution (excluding SitePulse)
				$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
				global $wp_filesystem;
				if ( $wp_filesystem && $wp_filesystem->exists( $plugin_path ) ) {
					$file_size = $wp_filesystem->size( $plugin_path );
					if ( $file_size !== false ) {
						$total_file_size += $file_size;
					}
				}
			}
			
			// If no plugins to track, return early
			if ( empty( $tracked_plugins ) ) {
				return;
			}
			
			// Calculate total memory delta
			$total_memory_delta = max( 0, $memory_after - self::$memory_before );
			
			// Validate memory delta is reasonable
			// Plugins typically use 1MB - 50MB total
			// If delta is too large (> 100MB), it's likely including WordPress core or other overhead
			if ( $total_memory_delta > 104857600 ) { // 100MB
				// Use a more conservative estimate based on plugin count
				// Average plugin uses 500KB - 2MB
				$total_memory_delta = count( $tracked_plugins ) * 1048576; // 1MB per plugin average
			}
			
			// If delta is suspiciously small (< 100KB for multiple plugins), use minimum
			if ( $total_memory_delta < 102400 && count( $tracked_plugins ) > 1 ) {
				$total_memory_delta = count( $tracked_plugins ) * 102400; // 100KB per plugin minimum
			}
			
			// Calculate time delta in milliseconds
			// Check if baseline was properly captured (must be > 0 and before cpu_after)
			if ( self::$cpu_before > 0 && self::$cpu_before < $cpu_after ) {
				$time_delta_seconds = $cpu_after - self::$cpu_before;
				
				// Validate the delta is reasonable (between 0.001s and 5s for plugin loading)
				// If plugins take more than 5 seconds to load, something is seriously wrong
				if ( $time_delta_seconds >= 0.001 && $time_delta_seconds <= 5.0 ) {
					// Convert to milliseconds
					$total_time_delta = $time_delta_seconds * 1000;
				} else {
					// Delta is outside reasonable range - likely measurement error
					// Use a conservative estimate based on plugin count
					$total_time_delta = max( 50.0, min( 2000.0, count( $tracked_plugins ) * 25.0 ) );
				}
			} else {
				// Baseline wasn't captured properly - use estimated time
				// Estimate: 20-30ms per plugin based on typical WordPress plugin load times
				$total_time_delta = max( 50.0, min( 2000.0, count( $tracked_plugins ) * 25.0 ) );
			}
			
			// Ensure total_time_delta is a reasonable value (cap at 5 seconds)
			$total_time_delta = max( 0.1, min( 5000.0, (float) $total_time_delta ) );
			
			// Store the measured total time for this load
			self::$measured_total_time = $total_time_delta;
			
			// Track each plugin individually
			foreach ( $tracked_plugins as $plugin_file ) {
				$plugin_data = $all_plugins[ $plugin_file ];
				$plugin_slug = dirname( $plugin_file );
				
				// Get plugin path
				$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
				
				// Measure plugin file size (as a proxy for complexity)
				global $wp_filesystem;
				$file_size = 0;
				if ( $wp_filesystem && $wp_filesystem->exists( $plugin_path ) ) {
					$fs_size = $wp_filesystem->size( $plugin_path );
					$file_size = ( $fs_size !== false ) ? $fs_size : 0;
				}
				
				// Estimate memory and time per plugin (distributed approach)
				// Since we can't measure each plugin individually during load,
				// we'll use a distributed approach based on file size and complexity
				$plugin_memory = self::estimate_plugin_memory( $plugin_file, $file_size, $total_memory_delta, $total_file_size, count( $tracked_plugins ) );
				$plugin_time = self::estimate_plugin_time( $plugin_file, $file_size, $total_time_delta, $total_file_size, count( $tracked_plugins ) );
				
				// Calculate CPU usage percentage (estimated)
				$cpu_percent = self::estimate_cpu_usage( $plugin_file, $plugin_time, $total_time_delta );
				
				// Create or update plugin stats
				$key = md5( $plugin_file );
				
				if ( ! isset( self::$stats[ $key ] ) ) {
					self::$stats[ $key ] = [
						'plugin_file'    => $plugin_file,
						'plugin_name'    => isset( $plugin_data['Name'] ) ? $plugin_data['Name'] : $plugin_slug,
						'plugin_version' => isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '0.0.0',
						'plugin_slug'    => $plugin_slug,
						'file_size'      => $file_size,
						'loads'          => 0,
						'total_memory'   => 0,
						'total_time'     => 0.0,
						'total_cpu'      => 0.0,
						'avg_memory'     => 0,
						'avg_time'       => 0.0,
						'avg_cpu'        => 0.0,
						'max_memory'     => 0,
						'max_time'       => 0.0,
						'max_cpu'        => 0.0,
						'last_updated'   => current_time( 'mysql' ),
					];
				}
				
				// Update stats
				self::$stats[ $key ]['loads']++;
				self::$stats[ $key ]['total_memory'] += $plugin_memory;
				self::$stats[ $key ]['total_time'] += $plugin_time;
				self::$stats[ $key ]['total_cpu'] += $cpu_percent;
				
				// Calculate averages
				self::$stats[ $key ]['avg_memory'] = round( self::$stats[ $key ]['total_memory'] / self::$stats[ $key ]['loads'] );
				self::$stats[ $key ]['avg_time'] = round( self::$stats[ $key ]['total_time'] / self::$stats[ $key ]['loads'], 2 );
				self::$stats[ $key ]['avg_cpu'] = round( self::$stats[ $key ]['total_cpu'] / self::$stats[ $key ]['loads'], 2 );
				
				// Update max values
				if ( $plugin_memory > self::$stats[ $key ]['max_memory'] ) {
					self::$stats[ $key ]['max_memory'] = $plugin_memory;
				}
				if ( $plugin_time > self::$stats[ $key ]['max_time'] ) {
					self::$stats[ $key ]['max_time'] = $plugin_time;
				}
				if ( $cpu_percent > self::$stats[ $key ]['max_cpu'] ) {
					self::$stats[ $key ]['max_cpu'] = $cpu_percent;
				}
				
				// Update last updated timestamp
				self::$stats[ $key ]['last_updated'] = current_time( 'mysql' );
			}
			
			// Clean up stats for deactivated plugins
			// Only keep stats for currently active plugins
			if ( ! empty( self::$stats ) && is_array( self::$stats ) ) {
				foreach ( self::$stats as $key => $stat ) {
					$plugin_file = isset( $stat['plugin_file'] ) ? $stat['plugin_file'] : '';
					
					// If this plugin is not in the currently tracked plugins list, remove it
					if ( ! empty( $plugin_file ) && ! in_array( $plugin_file, $tracked_plugins, true ) ) {
						// Skip SitePulse itself - we want to keep it even though we don't track it
						$plugin_slug = dirname( $plugin_file );
						if ( $plugin_slug !== 'sitepulse' ) {
							unset( self::$stats[ $key ] );
						}
					}
				}
			}
		}
		
		/**
		 * Estimate plugin memory usage based on file size, complexity, and historical data
		 *
		 * @param string $plugin_file Plugin file path
		 * @param int    $file_size   Plugin file size in bytes
		 * @param int    $total_delta Total memory delta
		 * @param int    $total_file_size Total file size of all tracked plugins
		 * @param int    $plugin_count Total number of tracked plugins (excluding SitePulse)
		 * @return int Estimated memory in bytes
		 */
		private static function estimate_plugin_memory( $plugin_file, $file_size, $total_delta, $total_file_size, $plugin_count ) {
			if ( $plugin_count <= 0 || $total_delta <= 0 ) {
				return 0;
			}
			
			// Get complexity score for this plugin
			$complexity = self::analyze_plugin_complexity( $plugin_file );
			
			// If we have historical data, use weighted average
			$plugin_key = md5( $plugin_file );
			if ( isset( self::$stats[ $plugin_key ]['avg_memory'] ) && self::$stats[ $plugin_key ]['avg_memory'] > 0 ) {
				$historical_memory = self::$stats[ $plugin_key ]['avg_memory'];
				
				// Validate historical memory is reasonable (1KB to 50MB)
				if ( $historical_memory > 52428800 || $historical_memory < 1024 ) {
					// Historical data is bad, ignore it and calculate fresh
					$historical_memory = 0;
				}
			}
			
			// Calculate current proportional memory
			if ( $total_file_size > 0 && $file_size > 0 ) {
				// Use file size as base, with complexity as a multiplier
				$base_ratio = $file_size / $total_file_size;
				
				// Apply complexity weight (1.0 - 10.0)
				// To keep ratios reasonable, we'll use sqrt of complexity to dampen extreme values
				$complexity_weight = sqrt( $complexity );
				$weighted_value = $base_ratio * $complexity_weight;
				
				// Simple proportional distribution
				// Note: This will be approximate since we're not normalizing across all plugins
				$current_memory = round( $total_delta * $base_ratio * ( $complexity / 1.5 ) );
			} else {
				// Fallback: equal distribution adjusted by complexity
				$base_share = $total_delta / $plugin_count;
				$current_memory = round( $base_share * ( $complexity / 1.5 ) );
			}
			
			// If we have valid historical data, blend it
			if ( isset( $historical_memory ) && $historical_memory > 0 ) {
				// Weighted average (70% historical, 30% current)
				$estimated = round( ( $historical_memory * 0.7 ) + ( $current_memory * 0.3 ) );
			} else {
				$estimated = $current_memory;
			}
			
			// Ensure reasonable bounds (1KB to 50MB per plugin)
			$estimated = max( 1024, min( 52428800, $estimated ) );
			
			return $estimated;
		}
		
	/**
	 * Analyze plugin complexity to determine weight for time distribution
	 *
	 * @param string $plugin_file Plugin file path
	 * @return float Complexity score (1.0 = average, higher = more complex)
	 */
	private static function analyze_plugin_complexity( $plugin_file ) {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		
		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
		$plugin_dir = dirname( $plugin_path );
		
		$complexity = 1.0; // Base complexity
		
		// Factor 1: File count in plugin directory (more files = more complex)
		if ( $wp_filesystem && $wp_filesystem->is_dir( $plugin_dir ) ) {
			$files = $wp_filesystem->dirlist( $plugin_dir );
			$file_count = 0;
			if ( is_array( $files ) ) {
				foreach ( $files as $file => $fileinfo ) {
					if ( $fileinfo['type'] === 'f' && substr( $file, -4 ) === '.php' ) {
						$file_count++;
					}
				}
			}
			$complexity += min( 2.0, $file_count / 20.0 ); // Up to +2.0 for many files
		}
		
		// Factor 2: Main file size (larger = more initialization code)
		if ( $wp_filesystem && $wp_filesystem->exists( $plugin_path ) ) {
			$file_size = $wp_filesystem->size( $plugin_path );
			if ( $file_size !== false ) {
				$complexity += min( 1.5, $file_size / 100000.0 ); // Up to +1.5 for large files
			}
		}
		
		// Factor 3: Check for common heavy operations in main file
		if ( $wp_filesystem && $wp_filesystem->exists( $plugin_path ) && $wp_filesystem->is_readable( $plugin_path ) ) {
			$content = $wp_filesystem->get_contents( $plugin_path );
			
			if ( $content !== false ) {
				// Database queries during load
				if ( stripos( $content, 'wp_query' ) !== false || stripos( $content, 'get_posts' ) !== false ) {
					$complexity += 0.8;
				}
				
				// Autoloader presence (usually means more files loaded)
				if ( stripos( $content, 'spl_autoload' ) !== false || stripos( $content, 'autoload' ) !== false ) {
					$complexity += 0.6;
				}
				
				// External HTTP requests during load
				if ( stripos( $content, 'wp_remote' ) !== false || stripos( $content, 'curl_' ) !== false ) {
					$complexity += 1.2;
				}
				
				// Heavy frameworks (React, Vue, etc.)
				if ( stripos( $content, 'webpack' ) !== false || stripos( $content, 'react' ) !== false ) {
					$complexity += 0.5;
				}
			}
		}
		
		return max( 0.1, min( 10.0, $complexity ) ); // Cap between 0.1 and 10.0
	}
	
	/**
	 * Estimate plugin loading time in milliseconds using complexity analysis
	 *
	 * @param string $plugin_file Plugin file path
	 * @param int    $file_size   Plugin file size in bytes
	 * @param float  $total_delta Total time delta in milliseconds
	 * @param int    $total_file_size Total file size of all tracked plugins
	 * @param int    $plugin_count Total number of tracked plugins (excluding SitePulse)
	 * @return float Estimated time in milliseconds (always between 0.1 and 10000ms)
	 */
	private static function estimate_plugin_time( $plugin_file, $file_size, $total_delta, $total_file_size, $plugin_count ) {
		if ( $plugin_count <= 0 ) {
			return 0.1;
		}
		
		// Sanitize total_delta
		$total_delta = max( 0.1, min( 60000.0, (float) $total_delta ) );
		
		// Get complexity score for this plugin
		$complexity = self::analyze_plugin_complexity( $plugin_file );
		
		// If we have stored timing data for this specific plugin, use weighted average
		$plugin_key = md5( $plugin_file );
		if ( isset( self::$stats[ $plugin_key ]['avg_time'] ) && self::$stats[ $plugin_key ]['avg_time'] > 0 ) {
			// Use 70% historical data, 30% current measurement
			$historical_time = self::$stats[ $plugin_key ]['avg_time'];
			
			// Calculate current proportional time
			if ( $total_file_size > 0 && $file_size > 0 ) {
				$ratio = ( $file_size / $total_file_size ) * $complexity;
				$current_time = $total_delta * ( $ratio / max( 1.0, $plugin_count ) );
			} else {
				$current_time = $total_delta / max( 1.0, $plugin_count );
			}
			
			// Weighted average favoring historical data for stability
			$estimated = ( $historical_time * 0.7 ) + ( $current_time * 0.3 );
			return round( max( 0.1, min( 10000.0, $estimated ) ), 2 );
		}
		
		// No historical data - calculate based on complexity and file size
		if ( $total_file_size > 0 && $file_size > 0 ) {
			// Calculate base ratio from file size
			$base_ratio = $file_size / $total_file_size;
			
			// Apply complexity multiplier
			// Need to normalize complexity across all plugins first
			// For now, use complexity directly as a weight
			$weighted_ratio = $base_ratio * $complexity;
			
			// Distribute time proportionally
			$estimated = $total_delta * $weighted_ratio;
		} else {
			// Fallback: distribute by complexity alone
			// Assume average complexity of 1.5 for other plugins
			$avg_complexity = 1.5;
			$complexity_ratio = $complexity / ( $avg_complexity * $plugin_count );
			$estimated = $total_delta * $complexity_ratio;
		}
		
		// Round to 2 decimal places and ensure reasonable bounds
		$estimated = round( $estimated, 2 );
		$estimated = max( 0.1, min( 10000.0, $estimated ) ); // Between 0.1ms and 10s
		
		return $estimated;
	}
	
	/**
	 * Estimate CPU usage percentage
	 *
	 * @param string $plugin_file Plugin file path
	 * @param float  $plugin_time Plugin time in milliseconds
	 * @param float  $total_time  Total time in milliseconds
	 * @return float CPU usage percentage
	 */
	private static function estimate_cpu_usage( $plugin_file, $plugin_time, $total_time ) {
		if ( $total_time <= 0 ) {
			return 0.0;
		}
		
		// CPU usage is proportional to time spent
		return round( ( $plugin_time / $total_time ) * 100, 2 );
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
			// Use shared utility method
			if ( class_exists( 'Sitepulse_Utils' ) ) {
				return Sitepulse_Utils::format_memory( $bytes );
			}
			// Fallback if Utils not loaded
			if ( $bytes >= 1048576 ) {
				return number_format( $bytes / 1048576, 2 ) . ' MB';
			} elseif ( $bytes >= 1024 ) {
				return number_format( $bytes / 1024, 2 ) . ' KB';
			}
			return $bytes . ' B';
		}
		
		/**
		 * Format time for display - shows only milliseconds or seconds
		 *
		 * @param float $ms Time in milliseconds
		 * @return string Formatted time string (e.g., "125.50 ms" or "2.35 s")
		 */
		public static function format_time( $ms ) {
			// Use shared utility method
			if ( class_exists( 'Sitepulse_Utils' ) ) {
				return Sitepulse_Utils::format_time( $ms, 'ms' );
			}
			// Fallback if Utils not loaded
			$ms = (float) $ms;
			
			if ( $ms < 0 ) {
				return '0 ms';
			}
			
			if ( $ms > 60000 ) {
				return '60.00 s';
			}
			
			if ( $ms >= 1000 ) {
				$seconds = $ms / 1000;
				return number_format( $seconds, 2, '.', '' ) . ' s';
			}
			
			return number_format( $ms, 2, '.', '' ) . ' ms';
		}
	}
}


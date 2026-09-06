<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Sitepulse_Profiler')) {
    final class Sitepulse_Profiler {
        /** @var array<string,array> */
        private static $stats = [];
        private static $wrapped = []; // map of "hook|priority|id" => true
        private static $plugin_load_baseline = null;
        private static $theme_bootstrap_baseline = null;
        private static $theme_template_stack = [];
        public static $enabled = true;
        private const SITEPULSE_OPTION_CURRENT_TIME = 'sitepulse_profiler_current_time';
        private static $request_date_time = '';

        public static function init() {
            self::$request_date_time = current_time( 'mysql' );

            // Load persisted stats + enabled flag. Listen.
            self::$stats   = get_option(SITEPULSE_PROFILER_KEY, []);
            $enabled_opt   = get_option(SITEPULSE_PROFILER_ENABLED, null);
            if ($enabled_opt !== null) self::$enabled = (bool) $enabled_opt;

            // Enable for Single report tracking page using Page Tracker helper
            if ( class_exists( 'Sitepulse_Page_Tracker' ) && Sitepulse_Page_Tracker::is_load_tracking_active() ) {
                self::$enabled = $enabled_opt = true;
            }

            if ( $enabled_opt ) {
                // Start now so hooks fired before plugins_loaded are captured.
                self::wrap_all_hooks();

                // Measure top-level execution in regular plugin main files. WordPress
                // fires plugin_loaded immediately after each plugin file is included.
                add_action('plugin_loaded', [__CLASS__, 'capture_plugin_load_time'], PHP_INT_MIN, 1);
                add_action('plugin_loaded', [__CLASS__, 'mark_plugin_load_complete'], PHP_INT_MAX, 0);

                // Measure active theme functions.php loading and every theme file
                // included through WordPress's load_template() function.
                add_action('setup_theme', [__CLASS__, 'start_theme_bootstrap'], PHP_INT_MAX, 0);
                add_action('after_setup_theme', [__CLASS__, 'capture_theme_bootstrap'], PHP_INT_MIN, 0);
                add_action('wp_before_load_template', [__CLASS__, 'start_theme_template_load'], PHP_INT_MAX, 1);
                add_action('wp_after_load_template', [__CLASS__, 'capture_theme_template_load'], PHP_INT_MIN, 1);

                // Wrap callbacks registered by plugins loaded after SitePulse before they run.
                add_action('plugins_loaded', [__CLASS__, 'wrap_all_hooks'], PHP_INT_MIN);

                // Themes often add callbacks during plugins_loaded; wrap them before they run.
                add_action('after_setup_theme', [__CLASS__, 'wrap_all_hooks'], PHP_INT_MIN);

                // Persist stats at end of request
                add_action('shutdown', [__CLASS__, 'persist']);
            }
        }

        /** Persist stats */
        public static function persist() {
            // Use Page Tracker helper for single page storage
            if ( class_exists( 'Sitepulse_Page_Tracker' ) && Sitepulse_Page_Tracker::save_single_page_events( self::$stats, 'load' ) ) {
                // Events saved to single page transient
                return;
            }

            // Save to global option
            update_option(SITEPULSE_PROFILER_CURRENT_TIME, self::$request_date_time, false);
            update_option(SITEPULSE_PROFILER_KEY, self::$stats, false);
        }

        public function clear_events() {
            self::$stats = [];
            self::persist();
        }

        /** Wrap all hooks' callbacks safely */
        public static function wrap_all_hooks() {
            if (!self::$enabled) return;

            global $wp_filter;

            if (!is_array($wp_filter)) return;

            // update_option(self::SITEPULSE_OPTION_CURRENT_TIME, self::$request_date_time, false);
            foreach ($wp_filter as $hook_name => $hook_obj) {

                if (!is_object($hook_obj) || !isset($hook_obj->callbacks) || !is_array($hook_obj->callbacks)) continue;

                foreach ($hook_obj->callbacks as $priority => $callbacks) {
                    foreach ($callbacks as $id => $cb) {
                        // Avoid double-wrapping and skip our own closures
                        $key = self::key($hook_name, $priority, $id);
                        if (isset(self::$wrapped[$key])) continue;

                        if (!isset($cb['function']) || !is_callable($cb['function'])) continue;

                        // Replace the callable with a timing wrapper
                        $orig = $cb['function'];

                        $hook_obj->callbacks[$priority][$id]['function'] = function(...$args) use ($hook_name, $priority, $id, $orig, $key) {
                            $start = microtime(true);
                            // Pass arguments by reference to match WordPress hook signature
                            $result = $orig(...$args);
                            $elapsed = microtime(true) - $start;

                            // Only record if execution time is measurable (> 0.0001 seconds = 0.1ms)
                            if ($elapsed > 0.0001) {
                                Sitepulse_Profiler::record($hook_name, (int)$priority, (string)$id, $orig, $elapsed);
                                // Allow pro version to record additional metrics
                                do_action( 'sitepulse_profiler_record', $hook_name, $priority, $id, $orig, $elapsed );
                            }
                            return $result;
                        };

                        // Mark wrapped
                        self::$wrapped[$key] = true;
                    }
                }
            }
        }

        /** Record the plugin main-file interval when plugin_loaded begins. */
        public static function capture_plugin_load_time(string $plugin): void {
            $now = microtime(true);

            // The first event belongs to SitePulse itself and establishes the baseline.
            if (self::$plugin_load_baseline === null) {
                return;
            }

            self::record_plugin_load($plugin, $now - self::$plugin_load_baseline);
        }

        /** Start the next plugin interval after plugin_loaded callbacks have completed. */
        public static function mark_plugin_load_complete(): void {
            self::$plugin_load_baseline = microtime(true);
        }

        /** Start timing after setup_theme callbacks and before theme functions.php files. */
        public static function start_theme_bootstrap(): void {
            self::$theme_bootstrap_baseline = microtime(true);
        }

        /** Record the combined child and parent theme functions.php bootstrap interval. */
        public static function capture_theme_bootstrap(): void {
            $now = microtime(true);

            if (self::$theme_bootstrap_baseline === null) {
                return;
            }

            $stylesheet = function_exists('get_stylesheet')
                ? trim(wp_normalize_path((string) get_stylesheet()), '/')
                : '(unknown)';
            $template = function_exists('get_template')
                ? trim(wp_normalize_path((string) get_template()), '/')
                : $stylesheet;
            $signature = $template !== $stylesheet
                ? 'functions.php (child + parent)'
                : 'functions.php';
            $fileline = $stylesheet === '(unknown)'
                ? '(unknown)'
                : 'wp-content/themes/' . $stylesheet . '/functions.php';

            self::record_sample(
                'theme_bootstrap',
                0,
                $signature,
                $fileline,
                $stylesheet,
                $now - self::$theme_bootstrap_baseline
            );

            self::$theme_bootstrap_baseline = null;
        }

        /** Start timing one theme template immediately before WordPress includes it. */
        public static function start_theme_template_load(string $template_file): void {
            $fileline = self::shortpath($template_file);

            if (strpos($fileline, 'wp-content/themes/') !== 0) {
                return;
            }

            self::$theme_template_stack[] = [
                'path'     => wp_normalize_path($template_file),
                'fileline' => $fileline,
                'start'    => microtime(true),
            ];
        }

        /** Record one theme template immediately after WordPress includes it. */
        public static function capture_theme_template_load(string $template_file): void {
            $now             = microtime(true);
            $normalized_path = wp_normalize_path($template_file);
            $entry_index     = null;

            for ($index = count(self::$theme_template_stack) - 1; $index >= 0; $index--) {
                if (self::$theme_template_stack[$index]['path'] === $normalized_path) {
                    $entry_index = $index;
                    break;
                }
            }

            if ($entry_index === null) {
                return;
            }

            $entry = self::$theme_template_stack[$entry_index];
            array_splice(self::$theme_template_stack, $entry_index, 1);

            $parts  = explode('/', $entry['fileline']);
            $source = isset($parts[2]) ? $parts[2] : '(unknown)';

            self::record_sample(
                'theme_template',
                0,
                'include',
                $entry['fileline'],
                $source,
                $now - $entry['start']
            );
        }

        /** Build array key */
        private static function key(string $hook, string $priority, string $id): string {
            return $hook.'|'.$priority.'|'.$id;
        }

        /** Create a human-readable signature for any callable */
        private static function signature($callable): string {
            if (is_string($callable)) return $callable; // function name
            if (is_array($callable) && count($callable) === 2) {
                [$obj_or_class, $method] = $callable;
                if (is_object($obj_or_class)) {
                    return get_class($obj_or_class) . '->' . $method;
                }
                return $obj_or_class . '::' . $method;
            }
            if ($callable instanceof Closure) return 'Closure';
            if (is_object($callable)) return get_class($callable) . '::__invoke';
            return 'Callable';
        }

        /** Determine file:line via reflection where possible */
        private static function fileline($callable): string {
            try {
                if (is_string($callable) && function_exists($callable)) {
                    $rf = new ReflectionFunction($callable);
                    return self::shortpath($rf->getFileName()).':'.$rf->getStartLine();
                }
                if (is_array($callable) && count($callable) === 2) {
                    [$obj_or_class, $method] = $callable;
                    $rm = new ReflectionMethod(is_object($obj_or_class) ? get_class($obj_or_class) : $obj_or_class, $method);
                    return self::shortpath($rm->getFileName()).':'.$rm->getStartLine();
                }
                if ($callable instanceof Closure) {
                    $rf = new ReflectionFunction($callable);
                    return self::shortpath($rf->getFileName()).':'.$rf->getStartLine();
                }
                if (is_object($callable) && method_exists($callable, '__invoke')) {
                    $rm = new ReflectionMethod(get_class($callable), '__invoke');
                    return self::shortpath($rm->getFileName()).':'.$rm->getStartLine();
                }
            } catch (Throwable $e) {
                // fall through
            }
            return '(unknown)';
        }

        private static function shortpath($path) {
            return Sitepulse_Utils::shortpath( $path );
        }

        /** Record one timing sample */
        public static function record(string $hook, int $priority, string $id, $callable, float $elapsed): void {
            $sig      = self::signature($callable);
            $fileline = self::fileline($callable);

            $plugin_or_theme = '(unknown)';
            if (strpos($fileline, 'wp-content/plugins/') === 0) {
                $parts = explode('/', $fileline);
                $plugin_or_theme = isset($parts[2]) ? $parts[2] : '(unknown)';
            } elseif (strpos($fileline, 'wp-content/themes/') === 0) {
                $parts = explode('/', $fileline);
                $plugin_or_theme = isset($parts[2]) ? $parts[2] : '(unknown)';
            }

            self::record_sample($hook, $priority, $sig, $fileline, $plugin_or_theme, $elapsed);
        }

        /**
         * Check if a specific hook is active.
         */
        public static function is_active( string $key ): bool {
            $inactive_after = defined( 'SITEPULSE_SETTINGS_PROFILER_INACTIVE_AFTER_SECONDS' ) ? SITEPULSE_SETTINGS_PROFILER_INACTIVE_AFTER_SECONDS : 60;

            $last_seen_at   = (float) ( self::$stats[ $key ]['last_seen_at'] ?? 0.0 );
            $age            = microtime( true ) - $last_seen_at;

            return $last_seen_at > 0 && $age >= 0 && $age <= $inactive_after;
        }

        /** Get collected stats for a specific hook */
        public static function get_stats_by_hook( string $hook ): array {
            return array_filter(
                self::$stats,
                static fn( $stat ) => ($stat['hook'] ?? '') === $hook
            );
        }

        /** Record top-level execution for one regular plugin main file. */
        private static function record_plugin_load(string $plugin, float $elapsed): void {
            $plugin_file = ltrim(wp_normalize_path(plugin_basename($plugin)), '/');

            if ($plugin_file === '') {
                return;
            }

            $parts  = explode('/', $plugin_file);
            $source = count($parts) > 1 ? $parts[0] : pathinfo($plugin_file, PATHINFO_FILENAME);

            self::record_sample(
                'plugin_bootstrap',
                0,
                'include',
                'wp-content/plugins/' . $plugin_file,
                $source,
                $elapsed
            );
        }

        /** Store one sample using the shared Activity Details schema. */
        private static function record_sample(
            string $hook,
            int $priority,
            string $sig,
            string $fileline,
            string $source,
            float $elapsed,
            bool $ignore = true
        ): void {

            // Convert seconds to milliseconds for better readability
            $elapsed_ms = $elapsed * 1000;

            // Ignore very fast callbacks and plugin loads (< 5ms)
            if ( ! $ignore && $elapsed_ms < 1 ) {
                return;
            }

            // Use a stable key per callback (hook+priority+signature+fileline)
            $key = md5(implode('|', [$hook, $priority, $sig, $fileline]));
    
            // Generate a key if the key doesnt exist
            if ( ! isset( self::$stats[$key] ) ) {
                
                // Detecting origin of the request
                $request_uri = isset( $_SERVER['REQUEST_URI'] )
                ? wp_parse_url(
                    sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
                    PHP_URL_PATH
                )
                : '';
                
                self::$stats[$key] = [
                    // Get the current url
                    'current_url' => $request_uri,
                    'key'      => $key,
                    'hook'     => $hook,
                    'priority' => $priority,
                    'sig'      => $sig,
                    'fileline' => $fileline,
                    'calls'    => 0,
                    'total'    => 0.0,
                    'total_ms' => 0.0,  // Total time in milliseconds
                    'avg_ms'   => 0.0,  // Average time in milliseconds
                    'max'      => 0.0,
                    'max_ms'   => 0.0,  // Max time in milliseconds
                    'current_load_time'=> 0.0, 
                    'source'   => $source,
                    'date_time' => self::$request_date_time,
                    'last_seen_at' => microtime(true),
                    'is_active' => true,
                    'status' => 'active'
                ];
            }
            
            self::$stats[$key]['calls']++;
            self::$stats[$key]['total'] += $elapsed;
            self::$stats[$key]['total_ms'] += $elapsed_ms;
            self::$stats[$key]['avg_ms'] = self::$stats[$key]['total_ms'] / self::$stats[$key]['calls'];
            self::$stats[$key]['current_load_time'] = $elapsed_ms; 
            self::$stats[$key]['date_time'] = self::$request_date_time;
            self::$stats[$key]['last_seen_at'] = microtime( true );

            // Max
            if ($elapsed > self::$stats[$key]['max']) {
                self::$stats[$key]['max'] = $elapsed;
                self::$stats[$key]['max_ms'] = $elapsed_ms;
            }
        }

        /**
         * Format elapsed time with appropriate units (ms for < 1s, s for >= 1s)
         * @param float $seconds Time in seconds
         * @return string Formatted time string
         */
        public static function format_time(float $seconds): string {
            return Sitepulse_Utils::format_time( $seconds, 'seconds' );
        }

        /**
         * Get stats with formatted timing values
         * @return array Stats with formatted timing
         */
        public static function get_formatted_stats(): array {
            $formatted_stats = [];
            foreach (self::$stats as $key => $stat) {
                $formatted_stats[$key] = $stat;
                // Format timing values
                $formatted_stats[$key]['total_formatted'] = self::format_time($stat['total']);
                $formatted_stats[$key]['avg_formatted'] = self::format_time($stat['total'] / max(1, $stat['calls']));
                $formatted_stats[$key]['max_formatted'] = self::format_time($stat['max']);
            }
            return $formatted_stats;
        }
    }
}

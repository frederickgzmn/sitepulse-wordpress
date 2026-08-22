<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Sitepulse_Profiler')) {
    final class Sitepulse_Profiler {
        /** @var array<string,array> */
        private static $stats = [];
        private static $wrapped = []; // map of "hook|priority|id" => true
        public static $enabled = true;
        private const SITEPULSE_OPTION_CURRENT_TIME = 'sitepulse_profiler_current_time';

        public static function init() {
            // Load persisted stats + enabled flag. Listen.
            self::$stats   = get_option(SITEPULSE_PROFILER_KEY, []);
            $enabled_opt   = get_option(SITEPULSE_PROFILER_ENABLED, null);
            if ($enabled_opt !== null) self::$enabled = (bool) $enabled_opt;

            // Enable for Single report tracking page using Page Tracker helper
            if ( class_exists( 'Sitepulse_Page_Tracker' ) && Sitepulse_Page_Tracker::is_load_tracking_active() ) {
                self::$enabled = $enabled_opt = true;
            }

            if ( $enabled_opt ) {
                // Wrap as late as possible on plugins_loaded so most callbacks are registered
                add_action('plugins_loaded', [__CLASS__, 'wrap_all_hooks'], PHP_INT_MAX);

                // Themes often add callbacks during after_setup_theme; wrap again there.
                add_action('after_setup_theme', [__CLASS__, 'wrap_all_hooks'], PHP_INT_MAX);

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
            update_option(SITEPULSE_PROFILER_CURRENT_TIME, current_time('mysql'), false);
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

            // Resetting
            self::$stats = [];

            update_option(self::SITEPULSE_OPTION_CURRENT_TIME, current_time('mysql'), false);
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
            // Use shared utility method
            if ( class_exists( 'Sitepulse_Utils' ) ) {
                return Sitepulse_Utils::shortpath( $path );
            }
            // Fallback if Utils not loaded
            if (!$path) return '(unknown)';
            $abspath = wp_normalize_path(ABSPATH);
            $p       = wp_normalize_path($path);
            if (strpos($p, $abspath) === 0) {
                return ltrim(substr($p, strlen($abspath)), '/');
            }
            return $p;
        }

        /** Record one timing sample */
        public static function record(string $hook, int $priority, string $id, $callable, float $elapsed): void {
            $sig      = self::signature($callable);
            $fileline = self::fileline($callable);

            // Convert seconds to milliseconds for better readability
            $elapsed_ms = $elapsed * 1000;

            // Ignore very fast callbacks (< 10ms)
            if ( $elapsed_ms < 10 ) {
                // Ignore very fast callbacks
                return;
            }

            // Use a stable key per callback (hook+priority+signature+fileline)
            $key = md5(implode('|', [$hook, $priority, $sig, $fileline]));

            if ( ! isset( self::$stats[$key] ) ) {
                $plugin_or_theme = '(unknown)';
                if (strpos($fileline, 'wp-content/plugins/') === 0) {
                    $parts = explode('/', $fileline);
                    $plugin_or_theme = isset($parts[2]) ? $parts[2] : '(unknown)';
                } elseif (strpos($fileline, 'wp-content/themes/') === 0) {
                    $parts = explode('/', $fileline);
                    $plugin_or_theme = isset($parts[2]) ? $parts[2] : '(unknown)';
                }

                self::$stats[$key] = [
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
                    'source'   => $plugin_or_theme,
                ];
            }
            
            self::$stats[$key]['calls']++;
            self::$stats[$key]['total'] += $elapsed;

            self::$stats[$key]['total_ms'] += $elapsed_ms;
            self::$stats[$key]['avg_ms'] = self::$stats[$key]['total_ms'] / self::$stats[$key]['calls'];

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
            // Use shared utility method
            if ( class_exists( 'Sitepulse_Utils' ) ) {
                return Sitepulse_Utils::format_time( $seconds, 'seconds' );
            }
            // Fallback if Utils not loaded
            if ($seconds >= 1.0) {
                return number_format($seconds, 3) . 's';
            } else {
                return number_format($seconds * 1000, 2) . 'ms';
            }
        }

        /**
         * Get stats with formatted timing values
         * @return array Stats with formatted timing
         */
        public static function get_formatted_stats(): array {
            $formatted_stats = [];
            foreach (self::$stats as $key => $stat) {
                $formatted_stats[$key] = $stat;
                $formatted_stats[$key]['total_formatted'] = self::format_time($stat['total']);
                $formatted_stats[$key]['avg_formatted'] = self::format_time($stat['total'] / max(1, $stat['calls']));
                $formatted_stats[$key]['max_formatted'] = self::format_time($stat['max']);
            }
            return $formatted_stats;
        }
    }
}

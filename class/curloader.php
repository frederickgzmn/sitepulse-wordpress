<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Sitepulse_CurLoader {
    const MAX_EVENTS = 200;  // ring buffer

    private static $enabled;
    private static $threshold;

    public static function init() {
        self::$enabled   = (bool) get_option(SITEPULSE_CURL_API_ENABLED, true);

        // Enable for Single report tracking page using Page Tracker helper
        if ( class_exists( 'Sitepulse_Page_Tracker' ) && Sitepulse_Page_Tracker::is_curl_tracking_active() ) {
            self::$enabled = true;
        }

        // Allow wp-config override: define('WPSLOWHTTP_THRESHOLD', 1.5);
        self::$threshold = defined('WPSLOWHTTP_THRESHOLD') ? (float) WPSLOWHTTP_THRESHOLD : SITEPULSE_CURL_API_DEFAULT_THRESHOLD;

        // Attach a start time to each request
        add_filter('http_request_args', [__CLASS__, 'tag_start_time'], 5, 2);
        // Read timings after response and log if slow
        add_action('http_api_debug', [__CLASS__, 'capture'], 10, 5);

        // Sensible timeouts if not provided
        add_filter('http_request_timeout', fn($t) => $t ?: 15);
        add_filter('http_request_args', function($args) { $args['timeout'] = $args['timeout'] ?? 15; return $args; });
    }

    public static function tag_start_time($args, $url) {
        if (!self::$enabled) return $args;
        $args['_wshp_start'] = microtime(true);
        // Also capture a short backtrace now, so we know who initiated the call
        $args['_wshp_trace'] = self::compact_backtrace();
        return $args;
    }

    public static function capture( $response, $context, $class, $args, $url ) {
        if (!$args || !self::$enabled || $context !== 'response') return;

        $start   = $args['_wshp_start'] ?? null;
        $elapsed = $start ? (microtime(true) - $start) : null;

        if ($elapsed === null) return;

        $threshold = self::$threshold;
        $code = is_wp_error($response) ? 0 : (int) wp_remote_retrieve_response_code($response);
        $trace = $args['_wshp_trace'] ?? [];

        if (round($elapsed) >= $threshold) {
            $origin = self::identify_origin($trace);

            // Fallback: if backtrace could not identify the source, try URL patterns
            if ( $origin['label'] === 'Unknown' || $origin['label'] === 'Custom code' ) {
                $url_origin = self::identify_origin_by_url( $url );
                if ( $url_origin !== null ) {
                    $origin['label'] = $url_origin;
                }
            }
            
            $event = [
                'ts'      => time(),
                'elapsed' => $elapsed,
                'url'     => $url,
                'host'    => wp_parse_url($url, PHP_URL_HOST) ?: '',
                'code'    => $code,
                'origin'  => $origin['label'],
                'file'    => $origin['file'],
                'line'    => $origin['line'],
            ];
            
            // Allow pro version to extend event data
            $event = apply_filters( 'sitepulse_curl_event_data', $event, $response, $args, $url );
            
            self::push_event($event);
        }
    }

    /** Find first non-core frame and map to Plugin/MU/Theme label */
    private static function identify_origin(array $trace): array {
        $abspath  = wp_normalize_path(ABSPATH);
        $wpinc    = wp_normalize_path(ABSPATH . WPINC);
        $wpadmin_folder  = 'wp-admin/';
        $wpincludes_folder  = 'wp-includes/';
        $plugdir  = WP_PLUGIN_DIR ? wp_normalize_path(WP_PLUGIN_DIR) : '';
        $muplug   = wp_normalize_path(WPMU_PLUGIN_DIR);
        $themeroot= wp_normalize_path(get_theme_root());

        $plugins = function_exists('get_plugins') ? get_plugins() : [];
        if ( isset($plugins['sitepulse/loader.php']) ) {
            unset($plugins['sitepulse/loader.php']);
        }
        $label = 'Unknown';
        $file = '(unknown)'; $line = 0;

        foreach ($trace as $f) {
            $fp = isset($f['file']) ? wp_normalize_path($f['file']) : '';
            if (!$fp) continue;
            // Skip core
            if (
                0 === strncmp($fp, $wpinc, strlen($wpinc)) ||
                strpos($fp, $wpadmin_folder) !== false ||
                strpos($fp, $wpincludes_folder) !== false
            ) continue;

            $file = self::shortpath($fp, $abspath);
            $line = $f['line'] ?? 0;

            // MU plugin?
            if ($muplug && 0 === strncmp($fp, $muplug, strlen($muplug))) {
                $label = 'MU Plugin: ' . self::basename_segment($fp, $muplug);
                break;
            }
            // Regular plugin?
            if ($plugdir && 0 === strncmp($fp, $plugdir, strlen($plugdir))) {
                $rel = trim(substr($fp, strlen($plugdir)+1), '/');
                $slug = explode('/', $rel)[0] ?? '';
                $label = 'Plugin: ' . self::plugin_name_from_slug($slug, $plugins);
                break;
            }
            // Theme?
            if ($themeroot && 0 === strncmp($fp, $themeroot, strlen($themeroot))) {
                $rel = trim(substr($fp, strlen($themeroot)+1), '/');
                $slug = explode('/', $rel)[0] ?? '';
                $theme = wp_get_theme($slug);
                $label = $theme->exists() ? 'Theme: ' . $theme->get('Name') : 'Theme: ' . $slug;
                break;
            }

            // If file is under wp-content but not matched above, still count as custom
            $upload_dir = wp_upload_dir();
            if ( isset( $upload_dir['basedir'] ) ) {
                if (strpos($fp, wp_normalize_path($upload_dir['basedir'])) !== false) {
                    $label = 'Custom code (wp-content)';
                    break;
                }
            }

            // First non-core file encountered
            $label = 'Custom code';
            break;
        }

        return ['label' => $label, 'file' => $file, 'line' => (int) $line];
    }

    /**
     * Identify the likely origin of an external request by its URL pattern.
     * Used as fallback when backtrace cannot determine the source.
     *
     * @param string $url The request URL.
     * @return string|null Plugin/service name or null if not matched.
     */
    private static function identify_origin_by_url( $url ) {
        $host = wp_parse_url( $url, PHP_URL_HOST );
        if ( ! $host ) {
            return null;
        }
        $host = strtolower( $host );
        $path = strtolower( wp_parse_url( $url, PHP_URL_PATH ) ?: '' );

        // Map of host patterns to service/plugin names
        $host_map = array(
            // WordPress Core
            'api.wordpress.org'         => 'WordPress Core',
            'downloads.wordpress.org'   => 'WordPress Core',
            // SitePulse
            'api.sitepulse.me'          => 'SitePulse (API)',
            // WooCommerce
            'woocommerce.com'           => 'WooCommerce',
            'wc-analytics'              => 'WooCommerce',
            // Jetpack
            'jetpack.wordpress.com'     => 'Jetpack',
            'public-api.wordpress.com'  => 'Jetpack / WordPress.com',
            'stats.wp.com'              => 'Jetpack Stats',
            // Google Services
            'fonts.googleapis.com'      => 'Google Fonts',
            'fonts.gstatic.com'         => 'Google Fonts',
            'www.google-analytics.com'  => 'Google Analytics',
            'www.googletagmanager.com'  => 'Google Tag Manager',
            'pagead2.googlesyndication.com' => 'Google AdSense',
            'oauth2.googleapis.com'     => 'Google OAuth',
            // Payment Gateways
            'api.stripe.com'            => 'Stripe',
            'api.paypal.com'            => 'PayPal',
            // Email Services
            'api.mailchimp.com'         => 'Mailchimp',
            'api.sendgrid.com'          => 'SendGrid',
            'api.mailgun.net'           => 'Mailgun',
            'api.postmarkapp.com'       => 'Postmark',
            'api.sendinblue.com'        => 'Brevo (Sendinblue)',
            // Social & Marketing
            'graph.facebook.com'        => 'Facebook / Meta Pixel',
            'api.twitter.com'           => 'Twitter / X',
            'api.hubspot.com'           => 'HubSpot',
            // CDN & Performance
            'cdn.cloudflare.com'        => 'Cloudflare',
            'api.cloudflare.com'        => 'Cloudflare',
            // SEO & Analytics
            'api.ahrefs.com'            => 'Ahrefs',
            'api.semrush.com'           => 'SEMrush',
            // Forms
            'www.google.com'            => ( strpos( $path, 'recaptcha' ) !== false ) ? 'Google reCAPTCHA' : null,
            // Akismet
            'rest.akismet.com'          => 'Akismet Anti-Spam',
        );

        // Direct host match
        if ( isset( $host_map[ $host ] ) ) {
            $result = $host_map[ $host ];
            // Some entries use path-based logic (returns null for non-match)
            return $result;
        }

        // Partial host matching for subdomains
        $subdomain_map = array(
            '.akismet.com'      => 'Akismet Anti-Spam',
            '.stripe.com'       => 'Stripe',
            '.paypal.com'       => 'PayPal',
            '.googleapis.com'   => 'Google API',
            '.wp.com'           => 'WordPress.com / Jetpack',
            '.gravatar.com'     => 'Gravatar',
            '.cloudflare.com'   => 'Cloudflare',
        );

        foreach ( $subdomain_map as $pattern => $name ) {
            if ( '' !== $pattern && substr( $host, -strlen( $pattern ) ) === $pattern ) {
                return $name;
            }
        }

        return null;
    }

    private static function plugin_name_from_slug($slug, $plugins) {
        foreach ($plugins as $file => $data) {
            if (0 === strncmp($file, $slug . '/', strlen($slug . '/'))) {
                return $data['Name'] ?? $slug;
            }
        }
        return $slug ?: 'Unknown';
    }

    private static function basename_segment($path, $root) {
        $rel = trim(substr($path, strlen($root)+1), '/');
        return explode('/', $rel)[0] ?? basename($path);
    }

    private static function shortpath($path, $abspath = '') {
        return Sitepulse_Utils::shortpath( $path );
    }

    private static function push_event(array $event) {
        $events = get_option(SITEPULSE_CURL_API_KEY, []);
        $event['date'] = gmdate('Y-m-d H:i:s');
        $events[] = $event;
        if (count($events) > self::MAX_EVENTS) {
            $events = array_slice($events, -self::MAX_EVENTS);
        }

        // Use Page Tracker helper for single page storage
        if ( class_exists( 'Sitepulse_Page_Tracker' ) && Sitepulse_Page_Tracker::save_single_page_events( $events, 'curl' ) ) {
            // Events saved to single page transient
            return;
        }

        // Save to global option
        update_option(SITEPULSE_CURL_API_KEY, $events, false);
    }

    public static function get_events(): array {
        return get_option(SITEPULSE_CURL_API_KEY, []);
    }

    private static function compact_backtrace(): array {
        // Keep it light; limit frames and omit args for performance
        // Use WordPress-recommended approach with error handling
        if (!WP_DEBUG) {
            return []; // Don't collect backtrace in production for performance
        }
        
        // Use WordPress's wp_die() backtrace method for better WordPress integration
        if (function_exists('_wp_call_all_hook')) {
            // Capture backtrace using WordPress's internal method
            $bt = wp_debug_backtrace_summary();
            // Since wp_debug_backtrace_summary returns a string, we need to parse it
            // or use an alternative WordPress approach
            if (is_string($bt) && !empty($bt)) {
                // Convert WordPress summary to our expected format
                $trace_lines = explode("\n", $bt);
                $parsed_bt = [];
                foreach ($trace_lines as $line) {
                    if (preg_match('/(\d+)\.\s+(.+?):(\d+)/', $line, $matches)) {
                        $parsed_bt[] = [
                            'file' => $matches[2] ?? '(unknown)',
                            'line' => (int)($matches[3] ?? 0),
                            'function' => 'wp_trace'
                        ];
                    }
                }
                $bt = !empty($parsed_bt) ? $parsed_bt : [];
            } else {
                $bt = [];
            }
        } else {
            $bt = [];
        }

        // Remove our own frames
        $out = [];
        foreach ($bt as $f) {
            if (!isset($f['file'])) continue;
            if (strpos($f['file'], 'slow-http-profiler.php') !== false) continue;
            $out[] = ['file' => $f['file'], 'line' => $f['line'] ?? 0, 'function' => $f['function'] ?? ''];
        }
        return $out;
    }

    public function clear_events() {
        update_option(SITEPULSE_CURL_API_KEY, [], false);
    }

    public static function truncate($s, $len) {
        return (strlen($s) > $len) ? substr($s, 0, $len - 1) . '…' : $s;
    }
}
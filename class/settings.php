<?php
/**
 * Simplified settings management class for SitePulse.
 * Uses a single serialized WordPress option for all settings.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once SITEPULSE_CLASS_PATH . 'setup.php';

class Sitepulse_Settings extends Sitepulse_Setup {
    
    // Single option key for all settings
    const SETTINGS_OPTION_KEY = 'sitepulse_settings';
    
    // Email blocking modes
    const EMAIL_MODE_SENDMAIL = 'sendmail_block';
    const EMAIL_MODE_SMTP = 'smtp_block';
    const EMAIL_MODE_BOTH = 'both';
    
    private static $instance = null;
    private $hooks_initialized = false;
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Simple constructor
     */
    public function __construct() {
        add_action('init', array($this, 'maybe_apply_settings'), 5);
    }
    
    /**
     * Get all settings from single option
     */
    private function get_settings() {
        $defaults = array(
            'email_blocking_enabled' => false,
            'email_blocking_available_modes' => array(
                self::EMAIL_MODE_SENDMAIL => 'Block WordPress wp_mail function (sendmail)',
                self::EMAIL_MODE_SMTP => 'Block SMTP plugins only',
                self::EMAIL_MODE_BOTH => 'Block both sendmail and SMTP'
            ),
            'email_blocking_mode' => self::EMAIL_MODE_SENDMAIL,
            'cron_disabled' => false,
            'original_cron_setting' => false,
            'recovery_mode_email_addresses' => '',
            'external_api_enabled' => true, // Default enabled for PageSpeed and AI features
        );
        
        $settings = get_option(self::SETTINGS_OPTION_KEY, $defaults);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get cron failure information
     *
     * Checks both the core WP-Cron queue and Action Scheduler (if present)
     * for signs of systemic cron failure.
     */
    public function get_cron_failure_info() {
        if ($this->is_cron_disabled()) {
            return array('failing' => false);
        }

        // --- Check core WP-Cron queue ---
        $cron_array = _get_cron_array();
        if (is_array($cron_array) && ! empty($cron_array)) {
            $current_time = time(); // Use system time for consistency with cron timestamps
            $earliest_timestamp = min(array_keys($cron_array));
            
            $overdue_seconds = $current_time - $earliest_timestamp;
            
            // If more than 10 minutes overdue
            if ($overdue_seconds > 600) {
                $days = floor($overdue_seconds / 86400);
                $hours = floor(($overdue_seconds % 86400) / 3600);
                $minutes = floor(($overdue_seconds % 3600) / 60);

                $description = '';
                if ($days > 0) {
                    $description = sprintf(
                        /* translators: %d: number of days */
                        __( 'wp cron failed for %d days', 'sitepulse' ),
                        $days
                    );
                } elseif ($hours > 0) {
                    $description = sprintf(
                        /* translators: %d: number of hours */
                        __( 'wp cron failed for %d hours', 'sitepulse' ),
                        $hours
                    );
                } else {
                    $description = sprintf(
                        /* translators: %d: number of minutes */
                        __( 'wp cron failed for %d minutes', 'sitepulse' ),
                        $minutes
                    );
                }

                return array(
                    'failing' => true,
                    'source' => 'wp_cron',
                    'overdue_seconds' => $overdue_seconds,
                    'description' => $description,
                    'days' => $days,
                    'hours' => $hours,
                    'minutes' => $minutes
                );
            }
        }

        // --- Check Action Scheduler (WooCommerce, WP Mail SMTP, etc.) ---
        $as_failure = $this->get_action_scheduler_failure_info();
        if ( $as_failure['failing'] ) {
            return $as_failure;
        }

        return array('failing' => false);
    }

    /**
     * Check if Action Scheduler has a significant number of past-due actions.
     *
     * @return array Failure info array with 'failing', 'source', 'description', and 'past_due_count' keys.
     */
    private function get_action_scheduler_failure_info() {
        if ( ! class_exists( 'ActionScheduler_Store' ) ) {
            return array( 'failing' => false );
        }

        $threshold = 10;

        try {
            $store = ActionScheduler_Store::instance();

            $past_due_ids = $store->query_actions( array(
                'status'       => ActionScheduler_Store::STATUS_PENDING,
                'date'         => new DateTime( 'now', new DateTimeZone( 'UTC' ) ),
                'date_compare' => '<=',
                'per_page'     => $threshold + 1,
            ), 'ids' );

            $past_due_count = is_array( $past_due_ids ) ? count( $past_due_ids ) : 0;

            if ( $past_due_count > $threshold ) {
                return array(
                    'failing'        => true,
                    'source'         => 'action_scheduler',
                    'past_due_count' => $past_due_count,
                    'description'    => sprintf(
                        /* translators: %d: number of past-due actions */
                        __( 'Action Scheduler has %d+ past-due actions; cron system may be broken', 'sitepulse' ),
                        $threshold
                    ),
                );
            }
        } catch ( Exception $e ) {
            // Don't crash the settings page if AS throws.
        }

        return array( 'failing' => false );
    }


    /**
     * Get status information
     */
    public function get_status_info() {
        $email_status = 'Enabled';
        if ($this->is_email_blocking_enabled()) {
            $mode_labels = array(
                self::EMAIL_MODE_SENDMAIL => 'Sendmail Blocked',
                self::EMAIL_MODE_SMTP => 'SMTP Blocked',
                self::EMAIL_MODE_BOTH => 'Both Blocked'
            );
            $email_status = $mode_labels[$this->get_email_blocking_mode()];
        }
        
        // Get error log statistics
        $error_log_stats = array(
            'error_count' => 0,
            'total_count' => 0,
            'last_error_date' => null,
        );
        
        if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
            $error_log_stats = Sitepulse_Error_Handler::get_error_log_stats();
        }
        
        // Check for cron failure
        $cron_failure = $this->get_cron_failure_info();
        if ( $cron_failure['failing'] ) {
            $error_log_stats['error_count'] += 1;
        }

        return array(
            'plugin_version' => SITEPULSE_VERSION,
            'email_status' => $email_status,
            'cron_status' => $this->is_cron_disabled() ? 'Disabled' : 'Enabled',
            'cron_failing' => $cron_failure['failing'],
            'cron_failure_description' => isset($cron_failure['description']) ? $cron_failure['description'] : '',
            'cron_failure_data' => $cron_failure,
            'cron_events_count' => is_array(_get_cron_array()) ? count(_get_cron_array()) : 0,
            'error_log_count' => $error_log_stats['error_count'],
            'error_log_total_count' => $error_log_stats['total_count'],
            'error_log_last_date' => $error_log_stats['last_error_date'],
        );
    }

    /**
     * Save all settings to single option
     */
    private function save_settings($settings) {
        return update_option(self::SETTINGS_OPTION_KEY, $settings, false);
    }
    
    /**
     * Get single setting value
     */
    private function get_setting($key, $default = null) {
        $settings = $this->get_settings();
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    /**
     * Set single setting value
     */
    private function set_setting($key, $value) {
        $settings = $this->get_settings();
        $settings[$key] = $value;
        return $this->save_settings($settings);
    }
    
    /**
     * Apply settings when needed
     */
    public function maybe_apply_settings() {
        if ( ! $this->hooks_initialized ) {
            $this->apply_active_settings();
            $this->hooks_initialized = true;
        }
    }
    
    /**
     * Apply active settings
     */
    private function apply_active_settings() {
        if ( $this->is_email_blocking_enabled() ) {
            $this->setup_email_blocking();
        }

        if ( $this->is_cron_disabled() ) {
            $this->setup_cron_blocking();
        }
    }
    
    /**
     * Setup email blocking based on mode
     */
    private function setup_email_blocking() {
        $mode = $this->get_email_blocking_mode();

        switch ( $mode ) {
            case self::EMAIL_MODE_SENDMAIL:
                add_filter('wp_mail', '__return_false', 999);
                break;
                
            case self::EMAIL_MODE_SMTP:
                add_filter('wp_mail_smtp_get_mailer', '__return_false', 999);
                add_filter('wp_mail_smtp_providers_smtp_get_options', '__return_empty_array', 999);
                break;
                
            case self::EMAIL_MODE_BOTH:
                add_filter('wp_mail', '__return_false', 999);
                add_filter('wp_mail_smtp_get_mailer', '__return_false', 999);
                add_filter('wp_mail_smtp_providers_smtp_get_options', '__return_empty_array', 999);
                break;
        }
    }
    
    /**
     * Setup cron blocking
     */
    private function setup_cron_blocking() {
        // Store original setting if not stored
        if ($this->get_setting('original_cron_setting') === false) {
            $original = defined('DISABLE_WP_CRON') ? DISABLE_WP_CRON : false;
            $this->set_setting('original_cron_setting', $original);
        }
        
        add_filter('cron_request', '__return_false', 999);
    }
    
    // ==========================================
    // PUBLIC METHODS
    // ==========================================
    
    /**
     * Check if email blocking is enabled
     */
    public function is_email_blocking_enabled() {
        return (bool) $this->get_setting('email_blocking_enabled', false);
    }
    
    /**
     * Enable or disable email blocking
     */
    public function set_email_blocking_enabled($enabled) {
        $this->set_setting('email_blocking_enabled', (bool) $enabled);
        
        if ($this->hooks_initialized) {
            $this->refresh_hooks();
        }
        
        return true;
    }
    
    /**
     * Get email blocking mode
     */
    public function get_email_blocking_mode() {
        return $this->get_setting('email_blocking_mode', self::EMAIL_MODE_SENDMAIL);
    }
    
    /**
     * Set email blocking mode
     */
    public function set_email_blocking_mode($mode) {
        $valid_modes = array(
            self::EMAIL_MODE_SENDMAIL,
            self::EMAIL_MODE_SMTP,
            self::EMAIL_MODE_BOTH
        );
        
        if (!in_array($mode, $valid_modes)) {
            return false;
        }
        
        $this->set_setting('email_blocking_mode', $mode);
        
        if ($this->hooks_initialized && $this->is_email_blocking_enabled()) {
            $this->refresh_hooks();
        }
        
        return true;
    }
    
    /**
     * Check if cron is disabled
     */
    public function is_cron_disabled() {
        return (bool) $this->get_setting('cron_disabled', false);
    }
    
    /**
     * Enable or disable cron
     */
    public function set_cron_disabled($disabled) {
        $this->set_setting('cron_disabled', (bool) $disabled);
        
        if ($this->hooks_initialized) {
            $this->refresh_hooks();
        }
        
        return true;
    }
    
    /**
     * Check if external API data collection is enabled
     */
    public function is_external_api_enabled() {
        return (bool) $this->get_setting('external_api_enabled', true);
    }
    
    /**
     * Enable or disable external API data collection
     */
    public function set_external_api_enabled($enabled) {
        $this->set_setting('external_api_enabled', (bool) $enabled);
        return true;
    }
    
    /**
     * Get recovery mode email addresses
     */
    public function get_recovery_mode_email_addresses() {
        return $this->get_setting('recovery_mode_email_addresses', '');
    }
    
    /**
     * Set recovery mode email addresses
     */
    public function set_recovery_mode_email_addresses($emails) {
        $this->set_setting('recovery_mode_email_addresses', sanitize_text_field($emails));
        return true;
    }
    
    /**
     * Refresh hooks based on current settings
     */
    private function refresh_hooks() {
        $this->remove_all_hooks();
        $this->apply_active_settings();
    }
    
    /**
     * Remove all registered hooks
     */
    private function remove_all_hooks() {
        remove_filter('wp_mail', '__return_false', 999);
        remove_filter('wp_mail_smtp_get_mailer', '__return_false', 999);
        remove_filter('wp_mail_smtp_providers_smtp_get_options', '__return_empty_array', 999);
        remove_filter('cron_request', '__return_false', 999);
    }
    
    /**
     * Get all available email blocking modes
     */
    public function get_email_blocking_modes() {
        return array(
            self::EMAIL_MODE_SENDMAIL => 'Block WordPress wp_mail function (sendmail)',
            self::EMAIL_MODE_SMTP => 'Block SMTP plugins only',
            self::EMAIL_MODE_BOTH => 'Block both sendmail and SMTP'
        );
    }
    
    /**
     * Get all settings
     */
    public function get_all_settings() {
        return $this->get_settings();
    }
    
    /**
     * Update multiple settings
     */
    public function update_settings($new_settings) {
        $current_settings = $this->get_settings();
        $updated = false;
        
        if (isset($new_settings['email_blocking_enabled'])) {
            $current_settings['email_blocking_enabled'] = (bool) $new_settings['email_blocking_enabled'];
            $updated = true;
        }
        
        if ( isset( $new_settings['email_blocking_mode'] ) ) {
            $valid_modes = array_keys($this->get_email_blocking_modes());
            if (in_array($new_settings['email_blocking_mode'], $valid_modes)) {
                $current_settings['email_blocking_mode'] = $new_settings['email_blocking_mode'];
                $updated = true;
            }
        }
        
        if (isset($new_settings['cron_disabled'])) {
            $current_settings['cron_disabled'] = (bool) $new_settings['cron_disabled'];
            $updated = true;
        }
        
        if (isset($new_settings['external_api_enabled'])) {
            $current_settings['external_api_enabled'] = (bool) $new_settings['external_api_enabled'];
            $updated = true;
        }
        
        if (isset($new_settings['recovery_mode_email_addresses'])) {
            $current_settings['recovery_mode_email_addresses'] = sanitize_text_field($new_settings['recovery_mode_email_addresses']);
            $updated = true;
        }
        
        if ($updated) {
            $this->save_settings($current_settings);
            if ($this->hooks_initialized) {
                $this->refresh_hooks();
            }
        }
        
        return $updated;
    }
    
    /**
     * Reset all settings to defaults
     */
    public function reset_all_settings() {
        $defaults = array(
            'email_blocking_enabled' => false,
            'email_blocking_mode' => self::EMAIL_MODE_SENDMAIL,
            'cron_disabled' => false,
            'original_cron_setting' => false
        );
        
        $this->save_settings($defaults);
        $this->remove_all_hooks();
        $this->hooks_initialized = false;
        
        return true;
    }
    
    /**
     * Cleanup on plugin deactivation
     */
    public function cleanup_on_deactivate() {
        $this->remove_all_hooks();
        
        // Disable all features
        $settings = $this->get_settings();
        $settings['email_blocking_enabled'] = false;
        $settings['cron_disabled'] = false;
        $this->save_settings($settings);
    }
    
    /**
     * Cleanup on plugin uninstall
     */
    public function cleanup_on_uninstall() {
        $this->remove_all_hooks();
        delete_option(self::SETTINGS_OPTION_KEY);
    }
}

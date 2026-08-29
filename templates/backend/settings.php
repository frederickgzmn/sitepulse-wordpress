<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div class="sp-dashboard container-fluid my-4">
    <?php require_once( SITEPULSE_PATH . '/templates/backend/header.php' ); ?>
    <hr>
    <div class="row g-3">
        <div class="col-lg-6 col-12">
            <div class="card sp-pagespeed-widget">
                <div class="sp-widget-header">
                    <div class="sp-widget-title-section">
                        <div class="sp-widget-title">
                            <span class="dashicons dashicons-admin-settings"></span>
                    <div>
                        <strong><?php echo esc_html__( 'Settings', 'sitepulse' ); ?></strong>
                                <div class="sp-widget-subtitle"><?php echo esc_html__( 'Configure SitePulse functionality and behavior', 'sitepulse' ); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sp-widget-content">
                    <div class="sp-settings-content">
                    <form method="post" action="">
                        <?php wp_nonce_field( 'sitepulse_settings_action', 'sitepulse_settings_nonce' ); ?>
                        <!-- Email Blocking Feature -->
                        <div class="sp-setting-item">
                            <div class="sp-setting-header">
                                <label class="form-switch">
                                    <input type="checkbox" name="sitepulse_email_blocking_enabled" id="sitepulse_email_blocking_enabled" 
                                           value="1" <?php checked( $current_settings['email_blocking_enabled'], true ); ?> class="form-check-input" />
                                    <span class="form-check-label fw-semibold"><?php echo esc_html__( 'Email Blocking', 'sitepulse' ); ?></span>
                                </label>
                                <div class="sp-setting-badges">
                                    <span class="badge <?php echo esc_attr( $current_settings['email_blocking_enabled'] ? 'bg-danger' : 'bg-secondary' ); ?>">
                                        <?php echo $current_settings['email_blocking_enabled'] ? esc_html__( 'Active', 'sitepulse' ) : esc_html__( 'Inactive', 'sitepulse' ); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="sp-setting-description">
                                <?php echo esc_html__( 'Block all outgoing emails from this site', 'sitepulse' ); ?>
                            </div>
                            <div class="alert alert-warning mt-2 mb-2" style="font-size: 0.85rem;">
                                <span class="dashicons dashicons-warning" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; vertical-align: text-top; margin-right: 4px;"></span>
                                <strong><?php echo esc_html__( 'Warning:', 'sitepulse' ); ?></strong>
                                <?php echo esc_html__( 'Enabling this will stop ALL outgoing emails, including password resets, order confirmations, contact form submissions, and admin notifications. Only use on development or staging sites.', 'sitepulse' ); ?>
                            </div>
                            <div class="sp-setting-options" id="email_blocking_mode_row">
                                <label for="sitepulse_email_blocking_mode" class="sp-setting-label">
                                    <?php echo esc_html__( 'Email Blocking Mode', 'sitepulse' ); ?>
                                </label>
                                <select name="sitepulse_email_blocking_mode" id="sitepulse_email_blocking_mode" class="form-select">
                                    <?php foreach ( $current_settings['email_blocking_available_modes'] as $mode_key => $mode_label ): ?>
                                        <option value="<?php echo esc_attr( $mode_key ); ?>" <?php selected( $current_settings['email_blocking_mode'], $mode_key ); ?>>
                                            <?php echo esc_html( $mode_label ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="sp-setting-help">
                                    <?php echo esc_html__( 'Choose how to block emails: disable wp_mail function or disable SMTP settings.', 'sitepulse' ); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Cron Management Feature -->
                        <div class="sp-setting-item">
                            <div class="sp-setting-header">
                                <label class="form-switch">
                                    <input type="checkbox" name="sitepulse_cron_disabled" id="sitepulse_cron_disabled" 
                                           value="1" <?php checked( $current_settings['cron_disabled'], true ); ?> class="form-check-input" />
                                    <span class="form-check-label fw-semibold"><?php echo esc_html__( 'WP Cron', 'sitepulse' ); ?></span>
                                </label>
                                <div class="sp-setting-badges">
                                    <span class="badge <?php echo esc_attr( $current_settings['cron_disabled'] ? 'bg-danger' : 'bg-success' ); ?>">
                                        <?php echo esc_html( $status_info['cron_status'] ); ?>
                                    </span>
                                    <?php if ( isset($status_info['cron_failing']) && $status_info['cron_failing'] ): ?>
                                        <span class="badge bg-danger">
                                            <?php echo esc_html__( 'Failing', 'sitepulse' ); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ( $status_info['cron_events_count'] > 0 ): ?>
                                        <span class="badge bg-info text-dark">
                                            <?php echo esc_html( sprintf(
                                                /* translators: %d: number of scheduled cron events */
                                                esc_html__( 'Events: %d', 'sitepulse' ),
                                                (int) $status_info['cron_events_count']
                                            ) ); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="sp-setting-description">
                                <?php echo esc_html__( 'Disable WP Cron jobs', 'sitepulse' ); ?>
                                <div class="alert alert-warning mt-2 mb-2" style="font-size: 0.85rem;">
                                    <span class="dashicons dashicons-warning" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; vertical-align: text-top; margin-right: 4px;"></span>
                                    <strong><?php echo esc_html__( 'Warning:', 'sitepulse' ); ?></strong>
                                    <?php echo esc_html__( 'Disabling WP Cron stops all scheduled tasks, including publishing scheduled posts, running backups, sending email digests, and checking for updates. Only disable if you have set up a real server-side cron job.', 'sitepulse' ); ?>
                                </div>
                                <?php if ( isset($status_info['cron_failing']) && $status_info['cron_failing'] ): ?>
                                    <div class="mt-2">
                                        <div class="alert alert-danger mb-0 py-2">
                                            <span class="dashicons dashicons-warning" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; vertical-align: text-top; margin-right: 4px;"></span>
                                            <strong><?php echo isset( $status_info['cron_failure_description'] ) ? esc_html( $status_info['cron_failure_description'] ) : ''; ?></strong>
                                            <?php echo esc_html__( 'There may be a problem with WP-Cron.', 'sitepulse' ); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- External API Data Collection Feature -->
                        <div class="sp-setting-item">
                            <div class="sp-setting-header">
                                <label class="form-switch">
                                    <input type="checkbox" name="sitepulse_external_api_enabled" id="sitepulse_external_api_enabled" 
                                           value="1" <?php checked( $current_settings['external_api_enabled'] ?? true, true ); ?> class="form-check-input" />
                                    <span class="form-check-label fw-semibold"><?php echo esc_html__( 'PageSpeed & AI Reports', 'sitepulse' ); ?></span>
                                </label>
                                <span class="badge <?php echo esc_attr( ($current_settings['external_api_enabled'] ?? true) ? 'bg-success' : 'bg-secondary' ); ?>">
                                    <?php echo esc_html( ($current_settings['external_api_enabled'] ?? true) ? __( 'Enabled', 'sitepulse' ) : __( 'Disabled', 'sitepulse' ) ); ?>
                                </span>
                            </div>
                            <div class="sp-setting-description">
                                <?php echo esc_html__( 'Enable external API to receive PageSpeed Insights and AI diagnostic reports', 'sitepulse' ); ?>
                            </div>
                            <div class="sp-setting-help-box">
                                <div class="alert alert-info" style="margin-top: 10px;">
                                    <strong><?php echo esc_html__( 'What data is sent:', 'sitepulse' ); ?></strong>
                                    <ul style="margin: 8px 0 0 20px;">
                                        <li><?php echo esc_html__( 'Plugin names, versions, and load times', 'sitepulse' ); ?></li>
                                        <li><?php echo esc_html__( 'Hook execution times and memory usage', 'sitepulse' ); ?></li>
                                        <li><?php echo esc_html__( 'API request URLs and response times', 'sitepulse' ); ?></li>
                                        <li><?php echo esc_html__( 'Site domain name', 'sitepulse' ); ?></li>
                                    </ul>
                                    <strong style="margin-top: 8px; display: block;"><?php echo esc_html__( 'What is NOT sent:', 'sitepulse' ); ?></strong>
                                    <ul style="margin: 8px 0 0 20px;">
                                        <li><?php echo esc_html__( '❌ No personal user data (emails, passwords, IP addresses)', 'sitepulse' ); ?></li>
                                        <li><?php echo esc_html__( '❌ No user content (posts, pages, comments)', 'sitepulse' ); ?></li>
                                        <li><?php echo esc_html__( '❌ No database content or sensitive information', 'sitepulse' ); ?></li>
                                    </ul>
                                    <p style="margin-top: 10px;">
                                        <small><?php echo esc_html__( 'Data is sent once daily to api.sitepulse.me for analysis. Disabling this will stop PageSpeed and AI diagnostic features.', 'sitepulse' ); ?></small>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Recovery Mode Email -->
                        <div class="sp-setting-item">
                            <div class="sp-setting-header">
                                <label for="sitepulse_recovery_mode_email_addresses" class="sp-setting-label fw-semibold">
                                    <?php echo esc_html__( 'Recovery Mode Email Address', 'sitepulse' ); ?>
                                </label>
                            </div>
                            <div class="sp-setting-description">
                                <?php echo esc_html__( 'Email address to receive fatal error and warning notifications', 'sitepulse' ); ?>
                            </div>
                            <div class="sp-setting-options">
                                <input type="email" 
                                       name="sitepulse_recovery_mode_email_addresses" 
                                       id="sitepulse_recovery_mode_email_addresses" 
                                       value="<?php echo esc_attr( $current_settings['recovery_mode_email_addresses'] ?? '' ); ?>" 
                                       class="form-control" 
                                       placeholder="<?php echo esc_attr__( 'email@example.com', 'sitepulse' ); ?>" />
                                <div class="sp-setting-help">
                                    <?php echo esc_html__( 'Enter a single email address to receive notifications when fatal errors or critical warnings occur on your site.', 'sitepulse' ); ?>
                                </div>
                            </div>
                        </div>

                        <div class="sp-settings-actions">
                            <?php submit_button( esc_html__( 'Save Settings', 'sitepulse' ), 'primary', 'submit', false, array( 'class' => 'sp-action-btn' ) ); ?>
                        </div>
                    </form>
                    <?php
                    // Hook for extensions (pro plugin) to add content after the settings form
                    do_action( 'sitepulse_settings_after_form' );
                    ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onboarding & Advanced Settings -->
        <div class="col-lg-6 col-12">
            <div class="card sp-pagespeed-widget">
                <div class="sp-widget-header">
                    <div class="sp-widget-title-section">
                        <div class="sp-widget-title">
                            <span class="dashicons dashicons-admin-generic"></span>
                    <div>
                        <strong><?php echo esc_html__( 'Setup Wizard', 'sitepulse' ); ?></strong>
                                <div class="sp-widget-subtitle"><?php echo esc_html__( 'Run the onboarding wizard again', 'sitepulse' ); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sp-widget-content">
                    <div class="sp-settings-content">
                    <?php
                    require_once SITEPULSE_CLASS_PATH . 'onboarding.php';
                    $onboarding = Sitepulse_Onboarding::getInstance();
                    $is_completed = $onboarding->is_onboarding_completed();
                    $is_dismissed = $onboarding->is_onboarding_dismissed();
                    ?>
                    
                        <div class="sp-setting-item">
                            <div class="sp-setting-header">
                                <span class="fw-semibold"><?php echo esc_html__( 'Onboarding Status', 'sitepulse' ); ?></span>
                            <?php if ( $is_completed ): ?>
                                <span class="badge bg-success"><?php echo esc_html__( 'Completed', 'sitepulse' ); ?></span>
                            <?php elseif ( $is_dismissed ): ?>
                                <span class="badge bg-warning text-dark"><?php echo esc_html__( 'Skipped', 'sitepulse' ); ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo esc_html__( 'Not Started', 'sitepulse' ); ?></span>
                            <?php endif; ?>
                        </div>
                        
                            <div class="sp-setting-description">
                            <?php echo esc_html__( 'The setup wizard helps you configure SitePulse with recommended settings. You can run it again at any time to reconfigure the plugin.', 'sitepulse' ); ?>
                            </div>
                        
                        <?php if ( $is_completed || $is_dismissed ): ?>
                                <div class="sp-setting-warning">
                                    <span class="dashicons dashicons-warning"></span>
                                <div>
                                    <strong><?php echo esc_html__( 'Warning:', 'sitepulse' ); ?></strong>
                                    <?php echo esc_html__( 'Resetting the onboarding will allow you to run the setup wizard again. Your current settings will remain unchanged.', 'sitepulse' ); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                            <div class="sp-settings-actions">
                                <button type="button" class="sp-action-btn sp-action-btn-primary" id="sp_run_onboarding">
                                    <span class="dashicons dashicons-admin-generic"></span>
                                <?php echo esc_html__( 'Reset & Run Again Setup Wizard', 'sitepulse' ); ?>
                            </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-3">
        <!-- System Information -->
        <div class="col-lg-6 col-12">
            <div class="card sp-pagespeed-widget">
                <div class="sp-widget-header" data-bs-toggle="collapse" data-bs-target="#sp-system-info-collapse" aria-expanded="true" aria-controls="sp-system-info-collapse">
                    <div class="sp-widget-title-section">
                        <div class="sp-widget-title">
                            <span class="dashicons dashicons-info"></span>
                    <div>
                        <strong><?php echo esc_html__( 'System Information', 'sitepulse' ); ?></strong>
                                <div class="sp-widget-subtitle"><?php echo esc_html__( 'Current system status and configuration details', 'sitepulse' ); ?></div>
                            </div>
                        </div>
                    </div>
                    <button class="sp-toggle-btn" type="button">
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                </div>
                <div class="collapse show" id="sp-system-info-collapse">
                    <div class="sp-widget-content">
                        <div class="sp-details-list">
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'SitePulse Version', 'sitepulse' ); ?></div>
                                    </div>
                                    <div class="sp-detail-metrics">
                            <span class="badge bg-primary"><?php echo esc_html( $status_info['plugin_version'] ); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'Email Status', 'sitepulse' ); ?></div>
                                    </div>
                                    <div class="sp-detail-metrics">
                            <span class="badge <?php echo esc_attr( $current_settings['email_blocking_enabled'] ? 'bg-danger' : 'bg-success' ); ?>">
                                <?php echo esc_html( $status_info['email_status'] ); ?>
                            </span>
                                    </div>
                                </div>
                            </div>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'Cron Status', 'sitepulse' ); ?></div>
                                <?php if ( $status_info['cron_events_count'] > 0 ): ?>
                                            <div class="sp-detail-meta">
                                        <?php echo esc_html( sprintf(
                                            /* translators: %d: number of scheduled cron events */
                                            esc_html__( '%d scheduled events', 'sitepulse' ),
                                            (int) $status_info['cron_events_count']
                                        ) ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                                    <div class="sp-detail-metrics">
                            <span class="badge <?php echo esc_attr( $current_settings['cron_disabled'] ? 'bg-danger' : 'bg-success' ); ?>">
                                <?php echo esc_html( $status_info['cron_status'] ); ?>
                            </span>
                                    </div>
                                </div>
                            </div>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'Error Log & List below', 'sitepulse' ); ?></div>
                                        <div class="sp-detail-meta">
                                            <?php 
                                            $error_count = isset( $status_info['error_log_count'] ) ? (int) $status_info['error_log_count'] : 0;
                                            $error_total = isset( $status_info['error_log_total_count'] ) ? (int) $status_info['error_log_total_count'] : 0;
                                            $error_last_date = isset( $status_info['error_log_last_date'] ) ? $status_info['error_log_last_date'] : null;
                                            
                                            if ( $error_count > 0 ) {
                                                echo esc_html( sprintf(
                                                    /* translators: %1$d: current error count, %2$d: total error count */
                                                    __( '%1$d errors (last 2 days), %2$d total', 'sitepulse' ),
                                                    $error_count,
                                                    $error_total
                                                ) );
                                                if ( $error_last_date ) {
                                                    echo '<br><span class="text-muted small">' . esc_html( sprintf(
                                                        /* translators: %s: last error date */
                                                        __( 'Last error: %s', 'sitepulse' ),
                                                        date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $error_last_date ) )
                                                    ) ) . '</span>';
                                                }
                                                
                                                // Show recent errors with call stacks
                                                if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
                                                    $error_log = Sitepulse_Error_Handler::get_error_log();
                                                    if ( ! empty( $error_log['errors'] ) && is_array( $error_log['errors'] ) ) {
                                                        // Show last 5 errors
                                                        $recent_errors = array_slice( array_reverse( $error_log['errors'] ), 0, 5 );
                                                        echo '<div class="sp-error-list mt-3" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px;border-radius: 4px;">';
                                                        foreach ( $recent_errors as $error ) {
                                                            echo '<div class="sp-error-item mb-3 pb-3" style="border-bottom: 1px solid #eee;">';
                                                            echo wp_kses_post( Sitepulse_Error_Handler::format_error_for_display( $error ) );
                                                            echo '</div>';
                                                        }
                                                        echo '</div>';
                                                    }
                                                }
                                            } else {
                                                echo '<span class="text-muted small">' . esc_html__( 'No errors in the last 2 days', 'sitepulse' ) . '</span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="sp-detail-metrics">
                                        <span class="badge <?php echo esc_attr( $error_count > 0 ? 'bg-warning text-dark' : 'bg-success' ); ?>">
                                            <?php echo esc_html( $error_count ); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'PHP Version', 'sitepulse' ); ?></div>
                                    </div>
                                    <div class="sp-detail-metrics">
                            <span class="badge bg-info text-dark"><?php echo esc_html( PHP_VERSION ); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'WordPress Version', 'sitepulse' ); ?></div>
                                    </div>
                                    <div class="sp-detail-metrics">
                            <span class="badge bg-info text-dark"><?php echo esc_html( get_bloginfo( 'version' ) ); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'Active Theme', 'sitepulse' ); ?></div>
                                    </div>
                                    <div class="sp-detail-metrics">
                            <span class="badge bg-secondary"><?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'Active Plugins', 'sitepulse' ); ?></div>
                                        <div class="sp-detail-meta mt-2">
                                <?php
                                if ( ! empty( $active_plugins ) ) {
                                    $plugin_count = 0;
                                    foreach ( $active_plugins as $plugin ) {
                                                    if ( $plugin_count >= 5 ) break;
                                        $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
                                        echo '<span class="badge bg-light text-dark me-1 mb-1">' . esc_html( $plugin_data['Name'] ) . ' v' . esc_html( $plugin_data['Version'] ) . '</span>';
                                        $plugin_count++;
                                    }
                                    if ( count( $active_plugins ) > 5 ) {
                                        echo '<span class="badge bg-outline-secondary"> ' . esc_html( sprintf(
                                            /* translators: %d: number of remaining plugins */
                                            __( '... and %d more', 'sitepulse' ),
                                            count( $active_plugins ) - 5
                                        ) ) . '</span>';
                                    }
                                } else {
                                                echo '<span class="text-muted small">' . esc_html__( 'No active plugins found.', 'sitepulse' ) . '</span>';
                                }
                                ?>
                                        </div>
                                    </div>
                                    <div class="sp-detail-metrics">
                                        <span class="badge bg-primary"><?php echo esc_html( count( $active_plugins ) ); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'Memory Usage', 'sitepulse' ); ?></div>
                                    </div>
                                    <div class="sp-detail-metrics">
                            <span class="badge bg-warning text-dark"><?php echo esc_html( isset($mem['formatted']) ? $mem['formatted'] : __( 'N/A', 'sitepulse' ) ); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Whitelabel Status -->
        <?php
        $whitelabel_enabled = class_exists( 'Sitepulse_Whitelabel_Service' ) && Sitepulse_Whitelabel_Service::is_whitelabel_enabled();
        $whitelabel_data = class_exists( 'Sitepulse_Whitelabel_Service' ) ? Sitepulse_Whitelabel_Service::get_whitelabel_data() : array();
        ?>
        <div class="col-lg-6 col-12">
            <div class="card sp-pagespeed-widget">
                <div class="sp-widget-header">
                    <div class="sp-widget-title-section">
                        <div class="sp-widget-title">
                            <span class="dashicons dashicons-tag"></span>
                    <div>
                        <strong><?php echo esc_html__( 'Whitelabel', 'sitepulse' ); ?></strong>
                                <div class="sp-widget-subtitle"><?php echo esc_html__( 'Custom branding from your license', 'sitepulse' ); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sp-widget-content">
                    <div class="sp-details-list">
                        <div class="sp-detail-item">
                            <div class="sp-detail-main">
                                <div class="sp-detail-info">
                                    <div class="sp-detail-title"><?php echo esc_html__( 'Whitelabel Status', 'sitepulse' ); ?></div>
                                </div>
                                <div class="sp-detail-metrics">
                                    <span class="badge <?php echo esc_attr( $whitelabel_enabled ? 'bg-success' : 'bg-secondary' ); ?>">
                                        <?php echo esc_html( $whitelabel_enabled ? __( 'Active', 'sitepulse' ) : __( 'Disabled', 'sitepulse' ) ); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if ( $whitelabel_enabled ) : ?>
                            <?php if ( ! empty( $whitelabel_data['company_name'] ) ) : ?>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'Company Name', 'sitepulse' ); ?></div>
                                    </div>
                                    <div class="sp-detail-metrics">
                                        <span class="badge bg-info text-dark"><?php echo esc_html( $whitelabel_data['company_name'] ); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ( ! empty( $whitelabel_data['plugin_name'] ) ) : ?>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'Plugin Name', 'sitepulse' ); ?></div>
                                    </div>
                                    <div class="sp-detail-metrics">
                                        <span class="badge bg-primary"><?php echo esc_html( $whitelabel_data['plugin_name'] ); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ( ! empty( $whitelabel_data['logo_url'] ) ) : ?>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'Custom Logo', 'sitepulse' ); ?></div>
                                        <div class="sp-detail-meta mt-2">
                                            <img src="<?php echo esc_url( $whitelabel_data['logo_url'] ); ?>" 
                                                 alt="<?php echo esc_attr__( 'Custom Logo', 'sitepulse' ); ?>" 
                                                 style="max-width: 100px; max-height: 50px; border-radius: 4px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ( ! empty( $whitelabel_data['updated_at'] ) ) : ?>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-title"><?php echo esc_html__( 'Last Updated', 'sitepulse' ); ?></div>
                                    </div>
                                    <div class="sp-detail-metrics">
                                        <span class="badge bg-light text-dark">
                                            <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $whitelabel_data['updated_at'] ) ) ); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php else : ?>
                            <div class="sp-detail-item">
                                <div class="sp-detail-main">
                                    <div class="sp-detail-info">
                                        <div class="sp-detail-meta text-muted">
                                            <?php echo esc_html__( 'No whitelabel data received from API. Contact your provider to enable custom branding.', 'sitepulse' ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php

if (!defined('ABSPATH')) {
  exit;
}

?>
<div class="sp-dashboard container-fluid my-4">
  <div class="row">
    <div class="col-lg-6 col-12"></div>
    <div class="col-lg-6 col-12">
      <div class="sp-buttons">
        <button type="button" class="btn btn-outline-danger sp_curl_api_clear_events m-1">
          <?php echo esc_html__('Clear Request Events', 'sitepulse'); ?>
        </button>
        <div class="header-actions">
          <?php $sitepulse_lowhttp_enabled = get_option('sitepulse_curl_api_enabled', false); ?>
          <label class="form-switch me-2">
            <input type="checkbox" <?php checked($sitepulse_lowhttp_enabled, '1'); ?> id="sp-http-load"
              class="form-check-input sp_http_load">
            <span class="form-check-label"><?php echo esc_html__('External Requests', 'sitepulse'); ?></span>
          </label>
        </div>
      </div>
    </div>
  </div>

  <?php require_once( SITEPULSE_PATH . '/templates/backend/header.php' ); ?>

  <hr>
  <div class="row  justify-content-center align-items-center">
    <div class="col-12 text-center">
      <?php
        $sitepulse_report_mode_active = get_option( "sitepulse_report_mode_active" );
        $sitepulse_report_mode_rain = '';
        $sitepulse_report_mode_display = 'display: none';
        if ( $sitepulse_report_mode_active ) {
          $sitepulse_report_mode_rain = 'rainbow-border';
          $sitepulse_report_mode_display = 'display: inline-block';
        }
      ?>

      <label class="form-switch me-2 sitepulse_report_mode <?php echo esc_attr( $sitepulse_report_mode_rain ); ?>">
        <input type="checkbox" <?php checked( $sitepulse_report_mode_active ); ?> id="sp-single-mode" class="form-check-input sp_report_mode" />
        <span class="form-check-label"><?php echo esc_html__( 'Toggle Page Request Report', 'sitepulse' ); ?></span>
      </label>

      <span class="badge bg-primary sitepulse_report_single_mode" style="<?php echo esc_attr( $sitepulse_report_mode_display ); ?>">
        <?php echo esc_html__( 'Single Mode Enabled', 'sitepulse' ); ?>
        <?php
          $sitepulse_current_tracked_pageid = get_option( "sitepulse_current_tracked_pageid" );

          if ( $sitepulse_current_tracked_pageid ) {
            echo ' - ' . esc_html__( 'Tracking Page ID:', 'sitepulse' ) . ' ' . esc_html( $sitepulse_current_tracked_title = get_the_title( $sitepulse_current_tracked_pageid ) );
          }
        ?>
      </span>

    </div>
  </div>
  <hr>

  <div class="row g-3">
    <div class="col-12">
      <div class="card sp-pagespeed-widget">
        <div class="sp-widget-header" data-bs-toggle="collapse" data-bs-target="#sp-curl-api-collapse" aria-expanded="true" aria-controls="sp-curl-api-collapse">
          <div class="sp-widget-title-section">
            <div class="sp-widget-title">
              <span class="dashicons dashicons-networking"></span>
              <div>
                <strong><?php echo esc_html__('External Requests', 'sitepulse'); ?></strong>
                <div class="sp-widget-subtitle"><?php echo esc_html__('External connections made during page load', 'sitepulse'); ?></div>
              </div>
            </div>
            <div class="sp-widget-stats">
              <?php if ( $single_curl_events && isset( $sitepulse_current_tracked_pageid ) ): 
                $safe_wpsp_sinpage_id = sanitize_text_field( wp_unslash( $sitepulse_current_tracked_pageid ) );
                if ( ! empty( $safe_wpsp_sinpage_id ) && is_numeric( $safe_wpsp_sinpage_id ) ) {
                  $wpsp_sinpage_id = $safe_wpsp_sinpage_id;
                  $wpsp_sinpage_title = get_the_title( $wpsp_sinpage_id );
                  if ( $wpsp_sinpage_title ) {
              ?>
                <div class="sp-stat-item">
                  <span class="sp-stat-value text-muted" style="font-size: 0.85rem;"><?php echo esc_html( $wpsp_sinpage_title ); ?></span>
                  <span class="sp-stat-label"><?php echo esc_html__( 'Single Mode', 'sitepulse' ); ?></span>
                </div>
              <?php 
                  }
                }
              endif; ?>
              <div class="sp-stat-item">
                <span class="sp-stat-value"><?php echo esc_html( count($curl_events) ); ?></span>
                <span class="sp-stat-label"><?php echo esc_html__( 'Events', 'sitepulse' ); ?></span>
              </div>
              <div class="sp-stat-item">
                <span class="sp-stat-value"><?php echo esc_html( defined('WPSLOWHTTP_THRESHOLD') ? (float) WPSLOWHTTP_THRESHOLD : SITEPULSE_CURL_API_DEFAULT_THRESHOLD ); ?>s</span>
                <span class="sp-stat-label"><?php echo esc_html__( 'Threshold', 'sitepulse' ); ?></span>
              </div>
            </div>
          </div>
          <button class="sp-toggle-btn" type="button">
            <span class="dashicons dashicons-arrow-down-alt2"></span>
          </button>
        </div>
        <div class="collapse show" id="sp-curl-api-collapse">
          <div class="sp-widget-content">
            <div class="sp-details-list">
              <?php
              if (empty($curl_events)) {
                ?>
                <div class="sp-detail-item sp-empty-state">
                  <div class="sp-detail-content">
                    <span class="dashicons dashicons-info"></span>
                    <span><?php echo esc_html__('No API requests detected. Please enable the monitor tracker to start collecting data.', 'sitepulse'); ?></span>
                  </div>
                </div>
                <?php
              }

              foreach ($curl_events as $event): 
                $time_ms = $event['elapsed'] * 1000;
                if ($time_ms > 1000) {
                  $perf_color = '#ff4444';
                } elseif ($time_ms > 500) {
                  $perf_color = '#ffb86b';
                } else {
                  $perf_color = '#38d39f';
                }

                // Label SitePulse internal API calls clearly
                $is_internal = false;
                $display_origin = $event['origin'];
                if ( isset( $event['url'] ) && strpos( $event['url'], 'api.sitepulse.me' ) !== false ) {
                  $is_internal = true;
                  $display_origin = esc_html__( 'SitePulse (API)', 'sitepulse' );
                }
              ?>
                <div class="sp-detail-item<?php echo $is_internal ? ' sp-internal-request' : ''; ?>" <?php echo $is_internal ? 'style="opacity: 0.6;"' : ''; ?>>
                  <div class="sp-detail-main">
                    <div class="sp-detail-info">
                      <div class="sp-detail-title">
                        <?php echo esc_html( $display_origin ); ?>
                        <?php if ( $is_internal ) : ?>
                          <span class="badge bg-secondary" style="font-size: 0.65rem; vertical-align: middle; margin-left: 4px;"><?php echo esc_html__( 'Internal', 'sitepulse' ); ?></span>
                        <?php endif; ?>
                      </div>
                      <div class="sp-detail-meta">
                        <span class="sp-detail-url mono"><?php echo esc_html($curLoader::truncate($event['url'], 80)); ?></span>
                        <span class="sp-detail-separator">•</span>
                        <span class="sp-detail-file"><?php echo esc_html($event['file']); ?></span>
                      </div>
                      <?php if (isset($event['date'])): ?>
                        <div class="sp-detail-meta mt-1">
                          <span class="sp-detail-file"><?php echo esc_html__('Date:', 'sitepulse'); ?> <?php echo esc_html($event['date']); ?></span>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div class="sp-detail-metrics">
                      <div class="sp-metric-badge" style="--metric-color: <?php echo esc_attr( $perf_color ); ?>">
                        <span class="sp-metric-value"><?php echo esc_html( number_format($time_ms, 0) ); ?></span>
                        <span class="sp-metric-unit">ms</span>
                      </div>
                    </div>
                  </div>
                  <div class="sp-detail-secondary">
                    <div class="sp-detail-stats">
                      <span class="sp-stat">
                        <span class="sp-stat-label"><?php echo esc_html__( 'Status', 'sitepulse' ); ?>:</span>
                        <span class="sp-stat-value sp-http-status"><?php echo esc_html($event['code'] ?: 'ERR'); ?></span>
                      </span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

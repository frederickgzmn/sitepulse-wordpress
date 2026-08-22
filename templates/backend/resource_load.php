<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="sp-dashboard container-fluid my-4">
  <div class="row">
    <div class="col-lg-6 col-12"></div>
    <div class="col-lg-6 col-12">
      <div class="sp-buttons">
        <button type="button" class="btn btn-outline-danger sp_profiler_clear_events m-1">
          <?php echo esc_html__( 'Clear Load Events', 'sitepulse' ); ?>
        </button>
        <div class="header-actions">
          <?php $sitepulse_profiler_enabled = get_option('sitepulse_profiler_enabled', null ); ?>
          <label class="form-switch me-2">
            <input type="checkbox" <?php checked($sitepulse_profiler_enabled, '1'); ?> id="sp-profiler" class="form-check-input sp_profiler">
            <span class="form-check-label"><?php echo esc_html__( 'Performance Monitor', 'sitepulse' ); ?></span>
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
        <span class="form-check-label"><?php echo esc_html__( 'Toggle Page Activity Report', 'sitepulse' ); ?></span>
      </label>

      <span class="badge bg-primary sitepulse_report_single_mode" style="<?php echo esc_attr( $sitepulse_report_mode_display ); ?>">
        <?php echo esc_html__( 'Single Mode Enabled', 'sitepulse' ); ?>

        <?php
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
        <div class="sp-widget-header" data-bs-toggle="collapse" data-bs-target="#sp-resource-hooks-collapse" aria-expanded="true" aria-controls="sp-resource-hooks-collapse">
          <div class="sp-widget-title-section">
            <div class="sp-widget-title">
              <span class="dashicons dashicons-admin-plugins"></span>
              <div>
                <strong><?php echo esc_html__( 'Plugin Activity', 'sitepulse' ); ?></strong>
                <div class="sp-widget-subtitle"><?php echo esc_html__( 'Performance impact of plugins and themes on your site', 'sitepulse' ); ?></div>
              </div>
            </div>
            <div class="sp-widget-stats">
              <?php if ( $single_load_events && isset( $sitepulse_current_tracked_pageid ) && ! empty( $sitepulse_current_tracked_pageid ) && is_numeric( $sitepulse_current_tracked_pageid ) ): 
                $sitepulse_load_single_page_id = sanitize_text_field( wp_unslash( $sitepulse_current_tracked_pageid ) );
                $sitepulse_load_single_page_title = get_the_title( $sitepulse_load_single_page_id );
              ?>
                <div class="sp-stat-item">
                  <span class="sp-stat-value text-muted" style="font-size: 0.85rem;"><?php echo esc_html( $sitepulse_load_single_page_title ); ?></span>
                  <span class="sp-stat-label"><?php echo esc_html__( 'Single Mode', 'sitepulse' ); ?></span>
                </div>
              <?php endif; ?>
              <div class="sp-stat-item">
                <span class="sp-stat-value"><?php echo esc_html( $active_plugins_count ); ?></span>
                <span class="sp-stat-label"><?php echo esc_html__( 'Plugins', 'sitepulse' ); ?></span>
              </div>
              <div class="sp-stat-item">
                <span class="sp-stat-value"><?php echo esc_html( count( $stats ) ); ?></span>
                <span class="sp-stat-label"><?php echo esc_html__( 'Events', 'sitepulse' ); ?></span>
              </div>
            </div>
          </div>
          <button class="sp-toggle-btn" type="button">
            <span class="dashicons dashicons-arrow-down-alt2"></span>
          </button>
        </div>
        <div class="collapse show" id="sp-resource-hooks-collapse">
          <div class="sp-widget-content">
            <div class="sp-details-list">
              <?php
              // Sort by total time desc by default
              $stats = array_values($stats);

              usort($stats, function($a, $b) {
                  return $b['total_ms'] <=> $a['total_ms'];
              });

              if ( empty( $stat ) && round( count( $stats ) ) == 0 ) {
                ?>
                <div class="sp-detail-item sp-empty-state">
                  <div class="sp-detail-content">
                    <span class="dashicons dashicons-info"></span>
                    <span><?php echo esc_html__( 'No plugin or theme activity detected. Please enable the monitor tracker to start collecting data.', 'sitepulse' ); ?></span>
                  </div>
                </div>
                <?php
              }

              foreach ( $stats as $stat ) :
                if ( ! empty( $stat ) ) {
                  $avg = $stat['calls'] ? ($stat['total_ms'] / $stat['calls']) : 0;
                  $time_ms = $stat['total_ms'];
                  
                  // Determine performance color
                  if ($time_ms > 1000) {
                    $perf_color = '#ff4444';
                  } elseif ($time_ms > 500) {
                    $perf_color = '#ffb86b';
                  } elseif ($time_ms > 200) {
                    $perf_color = '#00c2a8';
                  } elseif ($time_ms > 100) {
                    $perf_color = '#6c5ce7';
                  } else {
                    $perf_color = '#38d39f';
                  }

                  $plugin_or_theme = str_replace('-', ' ', $stat['source']);
                  ?>
                  <div class="sp-detail-item">
                    <div class="sp-detail-main">
                      <div class="sp-detail-info">
                        <div class="sp-detail-title"><?php echo esc_html( ucfirst( $plugin_or_theme ) ); ?></div>
                        <div class="sp-detail-meta">
                          <span class="sp-detail-hook"><?php echo esc_html( $stat['hook'] ); ?></span>
                          <span class="sp-detail-separator">•</span>
                          <span class="sp-detail-file mono"><?php echo esc_html( $stat['fileline'] ); ?></span>
                        </div>
                        <div class="sp-detail-meta mt-1">
                          <span class="sp-detail-sig"><?php echo esc_html( $stat['sig'] ); ?></span>
                        </div>
                      </div>
                      <div class="sp-detail-metrics">
                        <div class="sp-metric-badge" style="--metric-color: <?php echo esc_attr( $perf_color ); ?>">
                          <span class="sp-metric-value"><?php echo esc_html( number_format( $time_ms, 0 ) ); ?></span>
                          <span class="sp-metric-unit">ms</span>
                        </div>
                        <div class="sp-benchmark-context" style="font-size: 0.7rem; margin-top: 2px; text-align: center;">
                          <?php
                          if ( $time_ms > 1000 ) {
                            echo '<span style="color: #dc3545;">&#x1F534; ' . esc_html__( 'High Impact', 'sitepulse' ) . '</span>';
                          } elseif ( $time_ms >= 500 ) {
                            echo '<span style="color: #ffc107;">&#x1F7E1; ' . esc_html__( 'Average', 'sitepulse' ) . '</span>';
                          } else {
                            echo '<span style="color: #198754;">&#x1F7E2; ' . esc_html__( 'Good', 'sitepulse' ) . '</span>';
                          }
                          ?>
                        </div>
                      </div>
                    </div>
                    <div class="sp-detail-secondary">
                      <div class="sp-detail-stats">
                        <span class="sp-stat">
                          <span class="sp-stat-label"><?php echo esc_html__( 'Avg', 'sitepulse' ); ?>:</span>
                          <span class="sp-stat-value"><?php echo esc_html( number_format( $avg, 2 ) ); ?> ms</span>
                        </span>
                        <span class="sp-stat">
                          <span class="sp-stat-label"><?php echo esc_html__( 'Calls', 'sitepulse' ); ?>:</span>
                          <span class="sp-stat-value"><?php echo esc_html( $stat['calls'] ); ?></span>
                        </span>
                      </div>
                    </div>
                  </div>
                <?php
                }
              endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <?php if ( isset($disk_write) && is_array($disk_write) ): 
      $sitepulse_disk_score = 100;
      if ($disk_write['avg_ms'] > 100) {
        $sitepulse_disk_score = 50;
      } elseif ($disk_write['avg_ms'] > 50) {
        $sitepulse_disk_score = 75;
      }
    ?>
      <div class="col-12 col-lg-6">
        <div class="card sp-pagespeed-widget">
          <div class="sp-widget-header" data-bs-toggle="collapse" data-bs-target="#sp-resource-disk-collapse" aria-expanded="true" aria-controls="sp-resource-disk-collapse">
            <div class="sp-widget-title-section">
              <div class="sp-widget-title">
                <span class="dashicons dashicons-media-default"></span>
                <div>
                  <strong><?php echo esc_html__( 'Disk Write Test', 'sitepulse' ); ?></strong>
                  <div class="sp-widget-subtitle"><?php echo esc_html__( 'Performance of writing to disk (log directory)', 'sitepulse' ); ?></div>
                </div>
              </div>
              <div class="sp-widget-stats">
                <div class="sp-stat-item">
                  <span class="sp-stat-value"><?php echo esc_html( number_format($disk_write['avg_ms'], 1) ); ?></span>
                  <span class="sp-stat-label"><?php echo esc_html__( 'Avg (ms)', 'sitepulse' ); ?></span>
                </div>
                <div class="sp-stat-item">
                  <span class="sp-stat-value <?php echo !empty($disk_write['success']) ? 'text-success' : 'text-danger'; ?>">
                    <?php echo !empty($disk_write['success']) ? esc_html__( 'OK', 'sitepulse' ) : esc_html__( 'FAIL', 'sitepulse' ); ?>
                  </span>
                  <span class="sp-stat-label"><?php echo esc_html__( 'Status', 'sitepulse' ); ?></span>
                </div>
              </div>
            </div>
            <button class="sp-toggle-btn" type="button">
              <span class="dashicons dashicons-arrow-down-alt2"></span>
            </button>
          </div>
          <div class="collapse show" id="sp-resource-disk-collapse">
            <div class="sp-widget-content">
              <div class="sp-details-list">
                <div class="sp-detail-item">
                  <div class="sp-detail-main">
                    <div class="sp-detail-info">
                      <div class="sp-detail-title"><?php echo esc_html__( 'Directory', 'sitepulse' ); ?></div>
                      <div class="sp-detail-meta">
                        <span class="sp-detail-file mono"><?php echo esc_html( $disk_write['dir'] ); ?></span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="sp-detail-item">
                  <div class="sp-detail-main">
                    <div class="sp-detail-info">
                      <div class="sp-detail-title"><?php echo esc_html__( 'Test Results', 'sitepulse' ); ?></div>
                      <div class="sp-detail-meta">
                        <span><?php echo esc_html__( 'Iterations', 'sitepulse' ); ?>: <?php echo esc_html( $disk_write['iterations'] ); ?></span>
                        <span class="sp-detail-separator">•</span>
                        <span><?php echo esc_html__( 'File Size', 'sitepulse' ); ?>: <?php echo esc_html( number_format($disk_write['size_bytes'] / 1024, 2) . ' KB' ); ?></span>
                      </div>
                    </div>
                  </div>
                </div>
                <?php foreach ($disk_write['times_ms'] as $i => $ms): 
                  $perf_color = $ms > 100 ? '#ff4444' : ($ms > 50 ? '#ffb86b' : '#38d39f');
                ?>
                  <div class="sp-detail-item">
                    <div class="sp-detail-main">
                      <div class="sp-detail-info">
                        <div class="sp-detail-title"><?php echo esc_html__( 'Run', 'sitepulse' ); ?> <?php echo esc_html( $i+1 ); ?></div>
                      </div>
                      <div class="sp-detail-metrics">
                        <div class="sp-metric-badge" style="--metric-color: <?php echo esc_attr( $perf_color ); ?>">
                          <span class="sp-metric-value"><?php echo esc_html( number_format($ms, 1) ); ?></span>
                          <span class="sp-metric-unit">ms</span>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
                <div class="sp-detail-item">
                  <div class="sp-detail-main">
                    <div class="sp-detail-info">
                      <div class="sp-detail-title"><?php echo esc_html__( 'Summary', 'sitepulse' ); ?></div>
                    </div>
                  </div>
                  <div class="sp-detail-secondary">
                    <div class="sp-detail-stats">
                      <span class="sp-stat">
                        <span class="sp-stat-label"><?php echo esc_html__( 'Avg', 'sitepulse' ); ?>:</span>
                        <span class="sp-stat-value"><?php echo esc_html( number_format($disk_write['avg_ms'], 2) ); ?> ms</span>
                      </span>
                      <span class="sp-stat">
                        <span class="sp-stat-label"><?php echo esc_html__( 'Min', 'sitepulse' ); ?>:</span>
                        <span class="sp-stat-value"><?php echo esc_html( number_format($disk_write['min_ms'], 2) ); ?> ms</span>
                      </span>
                      <span class="sp-stat">
                        <span class="sp-stat-label"><?php echo esc_html__( 'Max', 'sitepulse' ); ?>:</span>
                        <span class="sp-stat-value"><?php echo esc_html( number_format($disk_write['max_ms'], 2) ); ?> ms</span>
                      </span>
                      <span class="sp-stat">
                        <span class="sp-stat-label"><?php echo esc_html__( 'Total Written', 'sitepulse' ); ?>:</span>
                        <span class="sp-stat-value"><?php echo esc_html( number_format($disk_write['total_written_bytes'] / 1024, 2) ); ?> KB</span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  </div>
</div>


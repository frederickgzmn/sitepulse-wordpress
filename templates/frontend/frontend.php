<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SitePulse Frontend - Modal Interface
 *
 * @package SitePulse
 */

if ( current_user_can( 'manage_options' ) ) {
  ?>
  <div id="sitepulse-modal" class="sitepulse-modal" data-sitepulse-realtime="<?php echo esc_attr( $tracking_status ); ?>" <?php if ( $tracking_status == "tracking" ) { echo "style='display: block;'"; } ?>>
    <div class="sitepulse-modal-content">
      <span class="sitepulse-close-btn" id="sitepulse-close-modal">&times;</span>
      <div class="sitepulse-modal-header">
        <h2 id="modal-title"><?php echo esc_html__( 'SitePulse Resource Tracking', 'sitepulse' ); ?></h2>
        <div class="sitepulse-status-indicator" id="tracking-status">
          <span class="sitepulse-status-dot idle"></span>
          <span class="status-text"><?php echo esc_html__( 'Ready to Track', 'sitepulse' ); ?></span>
        </div>
      </div>
      
      <div class="sitepulse-modal-body">
        <!-- Inactive/Waiting Content (Default/Visible) -->
        <div id="inactive-content" class="sitepulse-modal-content-section">
          <p class="sitepulse-tracking-description"><?php echo esc_html__( 'Monitor your website\'s performance and resource usage in real-time.', 'sitepulse' ); ?></p>
          <p class="sitepulse-tracking-description setup-info"><?php echo esc_html__( 'Click "Start Tracking" to begin monitoring page load events and resource performance. The system will track all network requests, loading times, and user interactions to provide comprehensive performance insights.', 'sitepulse' ); ?></p>
        </div>
        
        <!-- Active Real-Time Content (Hidden by default) -->
        <div id="active-content" class="sitepulse-modal-content-section" style="display: none;">
          <p class="sitepulse-tracking-description"><?php echo esc_html__( 'Real-time mode is now active and continuously monitoring all page load events.', 'sitepulse' ); ?></p>
          <p class="sitepulse-tracking-description current-page">
            <?php
            if ( $sitepulse_current_tracked_pageid ) {
              echo sprintf(
                /* translators: %s: link to the currently tracked page */
                esc_html__( 'Real-time tracking is active for: %s', 'sitepulse' ),
                '<a id="current-page-link" href="' . esc_url( get_permalink( $sitepulse_current_tracked_pageid ) ) . '">' . esc_html( get_the_title( $sitepulse_current_tracked_pageid ) ) . '</a>'
              );
            } else {
                echo esc_html__( 'No page is currently set for real-time tracking.', 'sitepulse' );
            }
            ?>
          </p>
          <p class="sitepulse-tracking-description tracking-explanation"><?php echo esc_html__( 'During this session, the page will automatically perform hard refreshes to replicate genuine user interactions and ensure all resources are fetched from the server in real conditions. This allows for precise measurement of load performance and real-time data accuracy.', 'sitepulse' ); ?></p>
        </div>
        
        <!-- Completed Real-Time Content (Hidden by default) -->
        <div id="completed-content" class="sitepulse-modal-content-section" style="display: none;">
          <div class="sitepulse-completion-animation">
            <div class="sitepulse-checkmark-circle">
              <span class="sitepulse-checkmark">✓</span>
            </div>
          </div>
          <h3 class="sitepulse-completion-title"><?php echo esc_html__( 'Tracking Complete!', 'sitepulse' ); ?></h3>
          <p class="sitepulse-tracking-description sitepulse-completion-message"><?php echo esc_html__( 'Your real-time performance tracking session has been successfully completed. All page load events, resource measurements, and user interactions have been captured and analyzed.', 'sitepulse' ); ?></p>
          <p class="sitepulse-tracking-description sitepulse-results-info"><?php echo esc_html__( 'The comprehensive performance data is now being processed and will be available in your dashboard shortly.', 'sitepulse' ); ?></p>
          <div class="sitepulse-completion-actions">
            <a href="<?php echo esc_url( admin_url('admin.php?page=' . SITEPULSE_PREFIX . SITEPULSE_PREFIX_SEPARATOR . SITEPULSE_SLUG . '_' . SITEPULSE_PROFILER_SLUG) ); ?>" class="sitepulse-btn sitepulse-btn-primary sitepulse-view-results-btn">
              <span class="sitepulse-btn-icon">&#x1F4C8;</span>
              <?php echo esc_html__( 'View Results Dashboard', 'sitepulse' ); ?>
            </a>
            <button id="close-completed-modal" class="sitepulse-btn sitepulse-btn-secondary">
              <span class="sitepulse-btn-icon">&#x2716;</span>
              <?php echo esc_html__( 'Close', 'sitepulse' ); ?>
            </button>
          </div>

          <div id="sitepulse-completion-info"></div>
        </div>
        
        <div class="sitepulse-tracking-controls">
          <button id="start-tracking-btn" class="sitepulse-btn sitepulse-btn-primary">
            <span class="sitepulse-btn-icon">&#x1F4CA;</span>
            <?php echo esc_html__( 'Start Tracking', 'sitepulse' ); ?>
          </button>
          
          <button id="stop-tracking-btn" class="sitepulse-btn sitepulse-btn-secondary" style="display: none;">
            <span class="sitepulse-btn-icon">&#x23F9;</span>
            <?php echo esc_html__( 'Stop Tracking', 'sitepulse' ); ?>
          </button>
        </div>
        
        <div class="sitepulse-tracking-info" id="tracking-info" style="display: none;">
          <div class="sitepulse-info-grid">
            <div class="sitepulse-info-item">
              <span class="sitepulse-info-label"><?php echo esc_html__( 'Duration:', 'sitepulse' ); ?></span>
              <span class="sitepulse-info-value" id="tracking-duration">00:00</span>
            </div>
            <div class="sitepulse-info-item">
              <span class="sitepulse-info-label"><?php echo esc_html__( 'Resources:', 'sitepulse' ); ?></span>
              <span class="sitepulse-info-value" id="resources-count">0</span>
            </div>
            <div class="sitepulse-info-item">
              <span class="sitepulse-info-label"><?php echo esc_html__( 'Status:', 'sitepulse' ); ?></span>
              <span class="sitepulse-info-value" id="tracking-state"><?php echo esc_html__( 'Initializing...', 'sitepulse' ); ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php
}
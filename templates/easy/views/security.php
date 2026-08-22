<?php
/**
 * Easy Mode — Security & Stability view.
 *
 * Zones:
 *  1. Stability Overview — Stability ring + Fatal / Warning metric cards
 *  2. Threats & Issues   — Vulnerability list + Error log
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Derive error log data from variables passed by backend.php
$sp_security_error_log = isset( $error_log ) ? $error_log : array();
$sp_security_error_count = isset( $error_count ) ? (int) $error_count : ( isset( $sp_security_error_log['error_count'] ) ? (int) $sp_security_error_log['error_count'] : 0 );
$sp_security_fatal_count = isset( $fatal_count ) ? (int) $fatal_count : ( isset( $sp_security_error_log['fatal_count'] ) ? (int) $sp_security_error_log['fatal_count'] : 0 );
$sp_security_warning_count = isset( $warning_count ) ? (int) $warning_count : ( isset( $sp_security_error_log['warning_count'] ) ? (int) $sp_security_error_log['warning_count'] : 0 );
$sp_security_error_log_score = isset( $error_log_score ) ? (int) $error_log_score : 100;
$sp_security_vulnerabilities = isset( $vulnerabilities ) ? $vulnerabilities : array();
$sp_security_last_vuln_check = isset( $last_vulnerability_check ) ? $last_vulnerability_check : get_option( 'sitepulse_last_vulnerability_check', '' );
?>

<!-- Page Header -->
<div class="sp-page-header sp-dashboard-header">
	<div class="sp-dashboard-header-left">
		<h2 class="sp-page-title"><?php echo esc_html__( 'Security & Stability', 'sitepulse' ); ?></h2>
		<p class="sp-page-subtitle"><?php echo esc_html__( 'Check for threats, bugs, and error logs to keep your site safe', 'sitepulse' ); ?></p>
	</div>
	<div class="sp-dashboard-header-right">
		<span class="sp-stat-pill sp-live-pill">
			<span class="sp-pulse-dot <?php echo $sp_security_fatal_count > 0 ? 'sp-pulse-dot-danger' : 'sp-pulse-dot-success'; ?>"></span>
			<?php echo $sp_security_fatal_count > 0
				? esc_html__( 'Issues Detected', 'sitepulse' )
				: esc_html__( 'All Clear', 'sitepulse' ); ?>
		</span>
	</div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 1: Stability Overview
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--health">

<div class="sp-card sp-card--hero sp-mb-16">
	<div class="sp-hero-scores">
		<?php
		$ring_score = $sp_security_error_log_score;
		$ring_label = __( 'Stability', 'sitepulse' );
		$ring_size  = 'lg';
		require __DIR__ . '/../partials/score-ring.php';
		?>
	</div>
</div>

<div class="sp-grid sp-grid-3 sp-mb-16">
	<div class="sp-card sp-card--metric">
		<?php
		$metric_icon  = 'dashicons-dismiss';
		$metric_color = $sp_security_fatal_count > 0 ? 'danger' : 'success';
		$metric_value = $sp_security_fatal_count;
		$metric_label = __( 'Fatal Errors', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
	<div class="sp-card sp-card--metric">
		<?php
		$metric_icon  = 'dashicons-warning';
		$metric_color = $sp_security_warning_count > 5 ? 'warning' : 'success';
		$metric_value = $sp_security_warning_count;
		$metric_label = __( 'Warnings', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
	<div class="sp-card sp-card--metric">
		<?php
		$metric_icon  = 'dashicons-editor-code';
		$metric_color = $sp_security_error_count > 10 ? 'warning' : 'info';
		$metric_value = $sp_security_error_count;
		$metric_label = __( 'Total Errors', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
</div>

</div><!-- /sp-zone--health -->


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 2: Threats & Issues
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--data">
<div class="sp-zone-header">
	<span class="dashicons dashicons-shield"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Threats & Errors', 'sitepulse' ); ?></span>
</div>

<div class="sp-grid sp-grid-2 sp-mb-16">
	<!-- Vulnerabilities -->
	<div class="sp-card">
		<div class="sp-card-header">
			<div>
				<h3 class="sp-card-title"><span class="dashicons dashicons-shield-alt"></span> <?php echo esc_html__( 'Known Threats', 'sitepulse' ); ?></h3>
				<?php if ( ! empty( $sp_security_last_vuln_check ) ) : ?>
					<p class="sp-card-subtitle">
						<?php echo esc_html( sprintf(
							/* translators: %s: last check time */
							__( 'Last checked: %s', 'sitepulse' ),
							wp_date( 'M j, g:i A', strtotime( $sp_security_last_vuln_check ) )
						) ); ?>
					</p>
				<?php endif; ?>
			</div>
			<span class="sp-badge <?php echo ! empty( $sp_security_vulnerabilities ) ? 'sp-badge-danger' : 'sp-badge-success'; ?>">
				<?php echo esc_html( count( $sp_security_vulnerabilities ) ); ?>
			</span>
		</div>
		<div class="sp-card-body sp-p-0">
			<?php if ( ! empty( $sp_security_vulnerabilities ) ) : ?>
				<?php foreach ( $sp_security_vulnerabilities as $vuln ) : ?>
					<?php
					$sev       = strtolower( $vuln['severity'] ?? '' );
					$sev_badge = 'sp-badge-danger';
					if ( 'critical' === $sev ) {
						$sev_badge = 'sp-badge-danger';
					} elseif ( 'high' === $sev ) {
						$sev_badge = 'sp-badge-danger';
					} elseif ( 'medium' === $sev ) {
						$sev_badge = 'sp-badge-warning';
					} elseif ( 'low' === $sev ) {
						$sev_badge = 'sp-badge-secondary';
					}
					?>
					<div class="sp-list-item" style="padding:10px 20px;">
						<div class="sp-list-item-icon" style="background:var(--sp-danger-soft);">
							<span class="dashicons dashicons-warning" style="color:var(--sp-danger);font-size:16px;width:16px;height:16px;"></span>
						</div>
						<div class="sp-list-item-body">
							<p class="sp-list-item-title"><?php echo esc_html( isset( $vuln['name'] ) ? $vuln['name'] : ( isset( $vuln['plugin'] ) ? $vuln['plugin'] : __( 'Unknown', 'sitepulse' ) ) ); ?></p>
							<?php if ( ! empty( $vuln['cve'] ) || ! empty( $vuln['cvss_score'] ) ) : ?>
								<p class="sp-text-xs sp-mb-0">
									<?php if ( ! empty( $vuln['cve'] ) ) : ?>
										<span class="sp-badge sp-badge-info" style="font-size:10px;"><?php echo esc_html( $vuln['cve'] ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $vuln['cvss_score'] ) ) : ?>
										<span class="sp-text-muted" style="font-size:11px;">CVSS: <?php echo esc_html( $vuln['cvss_score'] ); ?></span>
									<?php endif; ?>
								</p>
							<?php endif; ?>
							<?php if ( ! empty( $vuln['description'] ) ) : ?>
								<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( mb_strlen( $vuln['description'] ) > 120 ? mb_substr( $vuln['description'], 0, 120 ) . '...' : $vuln['description'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $vuln['patched'] ) && ! empty( $vuln['patched_versions'] ) ) : ?>
								<p class="sp-text-xs sp-mb-0" style="color:var(--sp-success);"><strong><?php echo esc_html__( 'Patch:', 'sitepulse' ); ?></strong> <?php echo esc_html( implode( ', ', $vuln['patched_versions'] ) ); ?></p>
							<?php elseif ( ! empty( $vuln['remediation'] ) ) : ?>
								<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( mb_strlen( $vuln['remediation'] ) > 100 ? mb_substr( $vuln['remediation'], 0, 100 ) . '...' : $vuln['remediation'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $vuln['references'] ) && is_array( $vuln['references'] ) ) : ?>
								<a href="<?php echo esc_url( $vuln['references'][0] ); ?>" target="_blank" rel="noopener noreferrer" class="sp-text-xs"><?php echo esc_html__( 'Details', 'sitepulse' ); ?> &rarr;</a>
							<?php endif; ?>
						</div>
						<span class="sp-badge <?php echo esc_attr( $sev_badge ); ?>"><?php echo esc_html( ucfirst( $vuln['severity'] ?? __( 'Unknown', 'sitepulse' ) ) ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="sp-empty" style="padding: 32px;">
					<div class="sp-empty-icon"><span class="dashicons dashicons-yes-alt" style="color:var(--sp-success);"></span></div>
					<p class="sp-empty-title"><?php echo esc_html__( 'No Threats Found', 'sitepulse' ); ?></p>
					<p class="sp-empty-desc"><?php echo esc_html__( 'All your plugins and themes are currently safe.', 'sitepulse' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<div class="sp-card-footer justify-content-center">
			<button type="button" class="btn btn-primary sp-check-vulnerabilities-btn">
				<span class="dashicons dashicons-shield-alt me-1"></span><?php echo esc_html__( 'Scan for Threats', 'sitepulse' ); ?>
			</button>
		</div>
	</div>

	<!-- Error Log -->
	<div class="sp-card">
		<div class="sp-card-header">
			<div>
				<h3 class="sp-card-title"><span class="dashicons dashicons-warning"></span> <?php echo esc_html__( 'Error Log', 'sitepulse' ); ?></h3>
				<p class="sp-card-subtitle"><?php echo esc_html__( 'Recent errors and warnings from your site', 'sitepulse' ); ?></p>
			</div>
		</div>
		<div class="sp-card-body sp-p-0">
			<?php
			$sp_recent_errors = array();
			if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
				$sp_error_log = Sitepulse_Error_Handler::get_error_log();
				$sp_recent_errors = isset( $sp_error_log['errors'] ) && is_array( $sp_error_log['errors'] )
					? array_slice( array_reverse( $sp_error_log['errors'] ), 0, 15 ) // Show 15 recent, reversed for newest first
					: array();
			}
			?>
			<?php if ( ! empty( $sp_recent_errors ) ) : ?>
				<?php foreach ( $sp_recent_errors as $err ) : ?>
					<?php 
						$is_fatal = isset( $err['is_fatal'] ) && $err['is_fatal'];
						$has_stack = ! empty( $err['call_stack'] );
						$short_file = class_exists( 'Sitepulse_Utils' ) ? Sitepulse_Utils::shortpath( $err['file'] ) : $err['file'];
					?>
					<div class="sp-list-item sp-align-start" style="padding:16px 20px; border-bottom: 1px solid var(--sp-border);">
						<div class="sp-list-item-icon" style="background:<?php echo $is_fatal ? 'var(--sp-danger-soft)' : 'var(--sp-warning-soft)'; ?>; margin-top: 2px;">
							<span class="dashicons <?php echo $is_fatal ? 'dashicons-dismiss' : 'dashicons-warning'; ?>" style="color:<?php echo $is_fatal ? 'var(--sp-danger)' : 'var(--sp-warning)'; ?>;font-size:16px;width:16px;height:16px;"></span>
						</div>
						<div class="sp-list-item-body">
							<div class="sp-flex sp-items-center sp-justify-between sp-mb-4">
								<span class="sp-badge <?php echo $is_fatal ? 'sp-badge-danger' : 'sp-badge-warning'; ?>" style="font-size: 9px;"><?php echo esc_html( isset( $err['type'] ) ? $err['type'] : 'Error' ); ?></span>
								<span class="sp-text-xs sp-text-muted"><?php echo esc_html( isset( $err['date'] ) ? $err['date'] : '' ); ?></span>
							</div>
							
							<div class="sp-error-message-full">
								<?php echo esc_html( isset( $err['message'] ) ? $err['message'] : __( 'Unknown error', 'sitepulse' ) ); ?>
							</div>

							<div class="sp-error-meta">
								<div class="sp-error-file-meta">
									<span class="dashicons dashicons-media-code"></span>
									<code><?php echo esc_html( $short_file ); ?><?php echo ! empty( $err['line'] ) ? ':' . esc_html( $err['line'] ) : ''; ?></code>
								</div>
							</div>

							<?php if ( $has_stack ) : ?>
								<button type="button" class="sp-error-expand-btn">
									<span class="dashicons dashicons-arrow-down-alt2"></span>
									<span class="sp-btn-text"><?php echo esc_html__( 'View Details', 'sitepulse' ); ?></span>
								</button>
								
								<div class="sp-error-callstack">
									<div class="sp-error-callstack-title"><?php echo esc_html__( 'Technical Details', 'sitepulse' ); ?></div>
									<?php foreach ( $err['call_stack'] as $frame ) : ?>
										<div class="sp-trace-line">
											<?php if ( ! empty( $frame['class'] ) ) : ?>
												<span class="sp-trace-class"><?php echo esc_html( $frame['class'] . $frame['type'] ); ?></span>
											<?php endif; ?>
											<span class="sp-trace-func"><?php echo esc_html( $frame['function'] ); ?>()</span>
											<?php if ( ! empty( $frame['file'] ) ) : ?>
												<br><span class="sp-trace-file"><?php echo esc_html( Sitepulse_Utils::shortpath( $frame['file'] ) ); ?>:<?php echo esc_html( $frame['line'] ); ?></span>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="sp-empty" style="padding: 48px 32px;">
					<div class="sp-empty-icon"><span class="dashicons dashicons-yes-alt" style="color:var(--sp-success);"></span></div>
					<p class="sp-empty-title"><?php echo esc_html__( 'Everything Looks Good!', 'sitepulse' ); ?></p>
					<p class="sp-empty-desc"><?php echo esc_html__( 'No errors or warnings have been found.', 'sitepulse' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $sp_recent_errors ) ) : ?>
			<div class="sp-card-footer justify-content-center">
				<button type="button" class="sp-topbar-action sp-clear-error-log-btn" style="width: 100%; justify-content: center; gap: 8px;">
					<span class="dashicons dashicons-trash"></span>
					<?php echo esc_html__( 'Clear Error Log', 'sitepulse' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
</div>

</div><!-- /sp-zone--data -->

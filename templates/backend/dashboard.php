<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SitePulse Profiler - Dashboard
 *
 * @package SitePulse
 */

?>

<div class="sp-dashboard container-fluid my-4">
	<div class="row">
		<div class="col-12"></div>
		<div class="col-12">
			<div class="sp-buttons d-flex justify-content-between align-items-center flex-wrap">
				<div class="d-flex align-items-center gap-2 flex-wrap">
					<button type="button" class="btn btn-outline-danger sp_curl_and_profiler_clear_events m-1">
						<?php echo esc_html__( 'Clear Event and Load Data', 'sitepulse' ); ?>
					</button>

					<div class="header-actions <?php if ( $sitepulse_lowhttp_enabled != 1 && $sitepulse_profiler_enabled != 1 ) {
						echo 'disabled';
					} ?>">
						<label class="form-switch me-2">
							<input type="checkbox" <?php checked( $sitepulse_lowhttp_enabled, '1' ); ?> id="sp-http-load"
								class="form-check-input sp_http_load">
							<span class="form-check-label"><?php echo esc_html__( 'API & Request', 'sitepulse' ); ?></span>
						</label>
						<label class="form-switch me-2">
							<input type="checkbox" <?php checked( $sitepulse_profiler_enabled, '1' ); ?> id="sp-profiler"
								class="form-check-input sp_profiler">
							<span class="form-check-label"><?php echo esc_html__( 'LoadSentinel', 'sitepulse' ); ?></span>
						</label>
					</div>
				</div>

				<!-- View Toggle Button -->
				<div class="sp-view-toggle">
					<button type="button" id="sp-toggle-view" class="sp-view-toggle-btn" data-view="basic">
						<span class="sp-view-toggle-icon">
							<span class="dashicons dashicons-admin-users"></span>
						</span>
						<span class="sp-view-label"><?php echo esc_html__( 'Basic View', 'sitepulse' ); ?></span>
						<span class="sp-view-toggle-arrow dashicons dashicons-arrow-right-alt2"></span>
					</button>
				</div>
			</div>
		</div>

		<?php require_once( SITEPULSE_PATH . '/templates/backend/header.php' ); ?>

		<?php if ( $loadstatus || $curlstatus ) { ?>
			<hr>
			<div class="row  justify-content-center align-items-center">
				<div class="col-12 text-center">
					<span class="badge bg-danger">
						<?php echo esc_html__( 'Single Track Mode Enabled For: ', 'sitepulse' ); ?>
						<a href="<?php echo esc_url( get_permalink( $sitepulse_current_tracked_pageid ) ); ?>"
							class="link-light link-underline-opacity-0 link-underline-opacity-75-hover">
							<?php echo esc_html( get_the_title( $sitepulse_current_tracked_pageid ) ); ?>
						</a>
					</span>
				</div>
			</div>
			<hr>
		<?php } ?>

		<!-- Basic View - Simplified Dashboard -->
		<div id="sp-basic-view" class="sp-view-mode" style="display: none;">
			<!-- Early Access Promo Banner -->
			<div class="sp-promo-banner mt-3">
				<div class="sp-promo-content">
					<h3><?php echo esc_html__( 'Claim Your Early Access', 'sitepulse' ); ?></h3>
					<p><?php echo esc_html__( 'Experience the future of WordPress performance today. SitePulse Pro is now open for early access. Secure your spot and start optimizing with elite tools.', 'sitepulse' ); ?></p>
				</div>
				<div class="sp-promo-action">
					<a href="https://sitepulse.me/" target="_blank" class="sp-btn-early-access sp-pulse">
						<span class="dashicons dashicons-star-filled me-2"></span>
						<?php echo esc_html__( 'Secure My Spot', 'sitepulse' ); ?>
					</a>
				</div>
			</div>

			<div class="row g-3">
				<!-- AI Diagnostic Report Widget (Basic View) -->
				<?php if ( $sp_ai_external_api_enabled ) :
					// Use shared variables from top of file
					$has_report = $has_ai_report;
					$is_pending = $is_ai_pending;
					$is_failed = $is_ai_failed;
				?>
				<div class="col-12">
					<div class="card sp-pagespeed-widget sp-ai-diagnostic-widget" data-status="<?php echo esc_attr( $ai_diagnostic_status ?? 'none' ); ?>">
						<div class="sp-widget-header">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-superhero-alt"></span>
									<div>
										<strong><?php echo esc_html__( 'AI Diagnostic Report', 'sitepulse' ); ?></strong>
										<span class="badge bg-info ms-2" style="font-size: 0.65rem; vertical-align: middle;"><?php echo esc_html__( 'Beta', 'sitepulse' ); ?></span>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'AI-powered analysis and recommendations', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
								<div class="sp-widget-stats">
									<div class="sp-stat-item">
										<?php if ( $has_report && isset( $ai_diagnostic_report['metrics_summary'] ) && is_array( $ai_diagnostic_report['metrics_summary'] ) && isset( $ai_diagnostic_report['metrics_summary']['overall_health_score'] ) && is_scalar( $ai_diagnostic_report['metrics_summary']['overall_health_score'] ) ) : ?>
											<span class="sp-stat-value sp-ai-health-score"><?php echo esc_html( (string) $ai_diagnostic_report['metrics_summary']['overall_health_score'] ); ?></span>
											<span class="sp-stat-label"><?php echo esc_html__( 'Health', 'sitepulse' ); ?></span>
										<?php elseif ( $is_pending ) : ?>
											<span class="sp-stat-value text-warning"><span class="spinner-border spinner-border-sm"></span></span>
											<span class="sp-stat-label"><?php echo $ai_diagnostic_status === 'processing' ? esc_html__( 'Processing', 'sitepulse' ) : esc_html__( 'Pending', 'sitepulse' ); ?></span>
										<?php else : ?>
											<span class="sp-stat-value text-muted">—</span>
											<span class="sp-stat-label"><?php echo esc_html__( 'Status', 'sitepulse' ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
						<div class="sp-widget-content sp-ai-diagnostic-content">
							<?php if ( $is_pending ) : ?>
								<!-- Pending/Processing State -->
								<div class="sp-ai-status-pending text-center py-4">
									<div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
										<span class="visually-hidden"><?php echo esc_html__( 'Loading...', 'sitepulse' ); ?></span>
									</div>
									<?php if ( $ai_diagnostic_status === 'processing' ) : ?>
										<h6><?php echo esc_html__( 'Processing Analysis', 'sitepulse' ); ?></h6>
										<p class="text-muted mb-2">
											<?php echo esc_html__( 'Our AI is analyzing your site data. This may take a few minutes.', 'sitepulse' ); ?>
										</p>
									<?php else : ?>
										<h6><?php echo esc_html__( 'Waiting in Queue', 'sitepulse' ); ?></h6>
										<p class="text-muted mb-2">
											<?php echo esc_html__( 'Your request has been queued. Processing will begin shortly.', 'sitepulse' ); ?>
											<?php echo esc_html__( 'AI can make mistakes, so always double-check recommendations.', 'sitepulse' ); ?>
										</p>
									<?php endif; ?>
									<?php if ( $ai_diagnostic_requested_at ) : ?>
										<small class="text-muted">
											<?php echo esc_html__( 'Requested:', 'sitepulse' ); ?>
											<?php echo esc_html( date( 'g:i A', strtotime( $ai_diagnostic_requested_at ) ) ); ?>
										</small>
									<?php endif; ?>
									<?php if ( $ai_diagnostic_queue_position && $ai_diagnostic_queue_position > 0 ) : ?>
										<div class="mt-2">
											<span class="badge bg-info">
												<?php echo esc_html__( 'Queue Position:', 'sitepulse' ); ?>
												<strong><?php echo esc_html( $ai_diagnostic_queue_position ); ?></strong>
											</span>
										</div>
									<?php endif; ?>
									<p class="text-muted small mt-2">
										<span class="dashicons dashicons-update-alt sp-spinning"></span>
										<?php echo esc_html__( 'Auto-checking every minute...', 'sitepulse' ); ?>
									</p>
								</div>
							<?php elseif ( $is_failed ) : ?>
								<!-- Failed State -->
								<div class="sp-ai-status-failed text-center py-4">
									<div class="text-danger mb-3">
										<span class="dashicons dashicons-warning" style="font-size: 48px; width: 48px; height: 48px;"></span>
									</div>
									<h6><?php echo esc_html__( 'Diagnostic Failed', 'sitepulse' ); ?></h6>
									<p class="text-muted mb-3">
										<?php echo esc_html__( 'The AI diagnostic could not be completed. Please try again.', 'sitepulse' ); ?>
									</p>
									<button type="button" class="btn btn-primary sp-request-ai-diagnostic-btn" data-force="true">
										<span class="dashicons dashicons-update-alt me-1"></span>
										<?php echo esc_html__( 'Retry Diagnostic', 'sitepulse' ); ?>
									</button>
								</div>
							<?php elseif ( $has_report ) : ?>
								<!-- Completed State - Show Results -->
								<?php
								$report_status = isset( $ai_diagnostic_report['status'] ) && is_string( $ai_diagnostic_report['status'] ) ? $ai_diagnostic_report['status'] : 'info';
								$status_class = $report_status === 'success' ? 'success' : ( $report_status === 'warning' ? 'warning' : ( $report_status === 'danger' ? 'danger' : 'info' ) );
								?>
								<div class="sp-ai-status-completed">
									<!-- Summary -->
									<?php if ( isset( $ai_diagnostic_report['summary'] ) && is_scalar( $ai_diagnostic_report['summary'] ) ) : ?>
										<div class="alert alert-<?php echo esc_attr( $status_class ); ?> mb-3">
											<div class="d-flex align-items-start">
												<span class="dashicons dashicons-<?php echo $report_status === 'success' ? 'yes-alt' : ( $report_status === 'warning' ? 'warning' : 'info' ); ?> me-2"></span>
												<div><?php echo esc_html( (string) $ai_diagnostic_report['summary'] ); ?></div>
											</div>
										</div>
									<?php endif; ?>

									<!-- Metrics Summary -->
									<?php if ( isset( $ai_diagnostic_report['metrics_summary'] ) && is_array( $ai_diagnostic_report['metrics_summary'] ) ) : ?>
										<div class="sp-ai-metrics-row mb-3">
											<div class="row g-2">
												<?php if ( isset( $ai_diagnostic_report['metrics_summary']['pagespeed_grade'] ) && is_scalar( $ai_diagnostic_report['metrics_summary']['pagespeed_grade'] ) ) : ?>
													<div class="col-3 text-center">
														<div class="sp-ai-grade-badge badge bg-<?php echo $ai_diagnostic_report['metrics_summary']['pagespeed_grade'] === 'A' ? 'success' : ( $ai_diagnostic_report['metrics_summary']['pagespeed_grade'] === 'B' ? 'info' : 'warning' ); ?> fs-5 px-3 py-2">
															<?php echo esc_html( (string) $ai_diagnostic_report['metrics_summary']['pagespeed_grade'] ); ?>
														</div>
														<small class="d-block text-muted mt-1"><?php echo esc_html__( 'Speed', 'sitepulse' ); ?></small>
													</div>
												<?php endif; ?>
												<?php if ( isset( $ai_diagnostic_report['metrics_summary']['security_grade'] ) && is_scalar( $ai_diagnostic_report['metrics_summary']['security_grade'] ) ) : ?>
													<div class="col-3 text-center">
														<div class="sp-ai-grade-badge badge bg-<?php echo $ai_diagnostic_report['metrics_summary']['security_grade'] === 'A' ? 'success' : ( $ai_diagnostic_report['metrics_summary']['security_grade'] === 'B' ? 'info' : 'warning' ); ?> fs-5 px-3 py-2">
															<?php echo esc_html( (string) $ai_diagnostic_report['metrics_summary']['security_grade'] ); ?>
														</div>
														<small class="d-block text-muted mt-1"><?php echo esc_html__( 'Security', 'sitepulse' ); ?></small>
													</div>
												<?php endif; ?>
												<?php if ( isset( $ai_diagnostic_report['metrics_summary']['error_severity'] ) && is_string( $ai_diagnostic_report['metrics_summary']['error_severity'] ) ) : ?>
													<div class="col-3 text-center">
														<?php 
														$error_sev = $ai_diagnostic_report['metrics_summary']['error_severity'];
														$error_class = $error_sev === 'low' ? 'success' : ( $error_sev === 'medium' ? 'warning' : 'danger' );
														?>
														<div class="sp-ai-grade-badge badge bg-<?php echo esc_attr( $error_class ); ?> fs-6 px-3 py-2">
															<?php echo esc_html( ucfirst( (string) $error_sev ) ); ?>
														</div>
														<small class="d-block text-muted mt-1"><?php echo esc_html__( 'Errors', 'sitepulse' ); ?></small>
													</div>
												<?php endif; ?>
												<?php if ( isset( $ai_diagnostic_report['metrics_summary']['overall_health_score'] ) && is_numeric( $ai_diagnostic_report['metrics_summary']['overall_health_score'] ) ) : ?>
													<div class="col-3 text-center">
														<?php 
														$health = $ai_diagnostic_report['metrics_summary']['overall_health_score'];
														$health_class = $health >= 80 ? 'success' : ( $health >= 60 ? 'warning' : 'danger' );
														?>
														<div class="sp-ai-grade-badge badge bg-<?php echo esc_attr( $health_class ); ?> fs-5 px-3 py-2">
															<?php echo esc_html( (string) $health ); ?>
														</div>
														<small class="d-block text-muted mt-1"><?php echo esc_html__( 'Health', 'sitepulse' ); ?></small>
													</div>
												<?php endif; ?>
											</div>
										</div>
									<?php endif; ?>

									<!-- Insights Accordion -->
									<?php if ( isset( $ai_diagnostic_report['insights'] ) && is_array( $ai_diagnostic_report['insights'] ) ) : ?>
										<div class="accordion accordion-flush mb-3" id="sp-ai-insights-accordion-basic">
											<?php foreach ( $ai_diagnostic_report['insights'] as $key => $insight ) : ?>
												<div class="accordion-item">
													<h2 class="accordion-header">
														<button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#sp-ai-insight-basic-<?php echo esc_attr( $key ); ?>">
															<span class="dashicons dashicons-<?php echo $key === 'performance' ? 'performance' : ( $key === 'security' ? 'shield' : 'warning' ); ?> me-2"></span>
															<?php echo esc_html( ucfirst( $key ) ); ?>
														</button>
													</h2>
													<div id="sp-ai-insight-basic-<?php echo esc_attr( $key ); ?>" class="accordion-collapse collapse" data-bs-parent="#sp-ai-insights-accordion-basic">
														<div class="accordion-body small">
															<?php if ( isset( $insight ) && is_scalar( $insight ) ) : ?>
																<?php echo esc_html( (string) $insight ); ?>
															<?php endif; ?>
														</div>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>

									<!-- Recommendations -->
									<?php if ( isset( $ai_diagnostic_report['recommendations'] ) && is_array( $ai_diagnostic_report['recommendations'] ) ) : ?>
										<?php 
										$recs = $ai_diagnostic_report['recommendations'];
										$has_recommendations = ( ! empty( $recs['critical'] ) || ! empty( $recs['high'] ) || ! empty( $recs['moderate'] ) || ! empty( $recs['low'] ) );
										?>
										<?php if ( $has_recommendations ) : ?>
											<div class="sp-ai-recommendations">
												<h6 class="mb-2"><span class="dashicons dashicons-lightbulb me-1"></span><?php echo esc_html__( 'Recommendations', 'sitepulse' ); ?></h6>
												<ul class="list-unstyled mb-0 small">
													<?php foreach ( array( 'critical', 'high', 'moderate', 'low' ) as $priority ) : ?>
														<?php if ( ! empty( $recs[ $priority ] ) && is_array( $recs[ $priority ] ) ) : ?>
															<?php foreach ( $recs[ $priority ] as $rec ) : ?>
																<li class="mb-1">
																	<span class="badge bg-<?php echo $priority === 'critical' ? 'danger' : ( $priority === 'high' ? 'warning text-dark' : ( $priority === 'moderate' ? 'info' : 'secondary' ) ); ?> me-1"><?php echo esc_html( ucfirst( $priority ) ); ?></span>
																	<?php if ( isset( $rec ) && is_scalar( $rec ) ) : ?>
																		<?php echo esc_html( (string) $rec ); ?>
																	<?php endif; ?>
																</li>
															<?php endforeach; ?>
														<?php endif; ?>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>
									<?php endif; ?>

									<!-- Recommended Plugins -->
									<?php if ( isset( $ai_diagnostic_report['wordpress_specific'] ) && is_array( $ai_diagnostic_report['wordpress_specific'] ) && isset( $ai_diagnostic_report['wordpress_specific']['recommended_plugins'] ) && is_array( $ai_diagnostic_report['wordpress_specific']['recommended_plugins'] ) && ! empty( $ai_diagnostic_report['wordpress_specific']['recommended_plugins'] ) ) : ?>
										<div class="sp-ai-wp-specific mt-3">
											<h6 class="mb-2"><span class="dashicons dashicons-admin-plugins me-1"></span><?php echo esc_html__( 'Recommended Plugins', 'sitepulse' ); ?></h6>
											<ul class="list-unstyled mb-0 small ps-2">
												<?php foreach ( $ai_diagnostic_report['wordpress_specific']['recommended_plugins'] as $plugin ) : ?>
													<?php if ( isset( $plugin ) && is_scalar( $plugin ) ) : ?>
														<li><span class="badge bg-success me-1"><?php echo esc_html__( '+', 'sitepulse' ); ?></span><?php echo esc_html( (string) $plugin ); ?></li>
													<?php endif; ?>
												<?php endforeach; ?>
											</ul>
										</div>
									<?php endif; ?>

									<!-- Request New Button -->
									<div class="text-end mt-3 pt-2 border-top">
										<?php if ( $ai_diagnostic_requested_at ) : ?>
											<small class="text-muted me-2">
												<?php echo esc_html__( 'AI can make mistakes, so always double-check recommendations.', 'sitepulse' ); ?>
												<?php echo esc_html__( 'Last run:', 'sitepulse' ); ?>
												<?php echo esc_html( date( 'M j, g:i A', strtotime( $ai_diagnostic_requested_at ) ) ); ?>
											</small>
										<?php endif; ?>
										<button type="button" class="btn btn-sm btn-outline-primary sp-request-ai-diagnostic-btn" data-force="true">
											<span class="dashicons dashicons-update-alt me-1"></span>
											<?php echo esc_html__( 'Run New Diagnostic', 'sitepulse' ); ?>
										</button>
									</div>
								</div>
							<?php else : ?>
								<!-- No Report State -->
								<div class="sp-ai-status-none text-center py-4">
									<div class="sp-placeholder-icon mb-3">
										<span class="dashicons dashicons-superhero-alt" style="font-size: 48px; width: 48px; height: 48px; color: var(--sp-accent-1);"></span>
									</div>
									<h6><?php echo esc_html__( 'AI-Powered Site Analysis', 'sitepulse' ); ?></h6>
									<p class="sp-placeholder-text text-muted mb-3">
										<?php echo esc_html__( 'Get intelligent insights about your site\'s performance, security, and optimization opportunities.', 'sitepulse' ); ?>
									</p>
									<button type="button" class="btn btn-primary sp-request-ai-diagnostic-btn" data-force="false">
										<span class="dashicons dashicons-superhero-alt me-1"></span>
										<?php echo esc_html__( 'Request AI Diagnostic', 'sitepulse' ); ?>
									</button>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<!-- Site Health Overview - Enhanced -->
				<div class="col-12">
					<div class="card sp-status-card sp-pagespeed-style">
						<div class="sp-status-header-compact">
							<div class="sp-status-header-content">
								<div class="sp-status-title-compact">
									<span class="dashicons dashicons-heart"></span>
									<strong><?php echo esc_html__( 'Your Site Health Overview', 'sitepulse' ); ?></strong>
								</div>
								<div class="sp-status-pulse">
									<span class="sp-pulse-dot"></span>
									<span class="sp-pulse-text">
										<?php echo esc_html__( 'Updated', 'sitepulse' ); ?>:
										<?php echo esc_html( current_time( 'g:i A' ) ); ?>
									</span>
								</div>
							</div>
						</div>
						<div class="card-body sp-pagespeed-body">
							<?php
							// All scores are calculated in backend.php class
							?>
							<div class="alert alert-info border mb-4 text-center">
								<small class="text-muted">
									<span class="badge bg-danger me-1">&lt;90</span>
									<?php echo esc_html__( 'Needs Improvement', 'sitepulse' ); ?>
									<span class="mx-2">|</span>
									<span class="badge bg-warning text-dark me-1">90-99</span>
									<?php echo esc_html__( 'Good, but can be better', 'sitepulse' ); ?>
									<span class="mx-2">|</span>
									<span class="badge bg-success me-1">100</span> <?php echo esc_html__( 'Optimal', 'sitepulse' ); ?>
								</small>
							</div>
							<div class="row justify-content-center mb-4">
								<!-- Overall Score - Enhanced -->
								<div class="col-md-4">
									<div class="sp-pagespeed-metric" data-score="<?php echo esc_attr( $overall_score ); ?>">
										<div class="sp-circular-progress">
											<div class="sp-progress-ring">
												<div class="sp-progress-circle"></div>
											</div>
											<div class="sp-score-value"><?php echo esc_html( $overall_score ); ?></div>
										</div>
										<div class="sp-metric-label">
											<span class="dashicons dashicons-heart"></span>
											<?php echo esc_html__( 'Overall Health Score', 'sitepulse' ); ?>
										</div>
										<div class="sp-metric-status">
											<?php if ( $overall_score >= 80 ) : ?>
												<span class="badge bg-success"><?php echo esc_html__( 'Excellent', 'sitepulse' ); ?></span>
											<?php elseif ( $overall_score >= 60 ) : ?>
												<span class="badge bg-warning text-dark"><?php echo esc_html__( 'Good', 'sitepulse' ); ?></span>
											<?php else : ?>
												<span class="badge bg-danger"><?php echo esc_html__( 'Needs Attention', 'sitepulse' ); ?></span>
											<?php endif; ?>
										</div>
									</div>
								</div>

								<?php if ( $has_valid_pagespeed && $ps_performance_score > 0 ) : ?>
									<div class="col-md-4">
										<div class="sp-pagespeed-metric" data-score="<?php echo esc_attr( $ps_performance_score ); ?>">
											<div class="sp-circular-progress">
												<div class="sp-progress-ring">
													<div class="sp-progress-circle"></div>
												</div>
												<div class="sp-score-value"><?php echo esc_html( $ps_performance_score ); ?></div>
											</div>
											<div class="sp-metric-label">
												<img src="https://www.google.com/favicon.ico" alt="Google Pagespeed Insight Logo" width="16"
													height="16" style="vertical-align: middle; margin-right: 4px;">
												<?php echo esc_html__( 'PageSpeed Performance', 'sitepulse' ); ?>
											</div>
											<div class="sp-metric-status">
												<span
													class="badge <?php echo $ps_performance_score >= 90 ? 'bg-success' : ( $ps_performance_score >= 50 ? 'bg-warning text-dark' : 'bg-danger' ); ?>">
													<?php echo $ps_performance_score >= 90 ? esc_html__( 'Excellent', 'sitepulse' ) : ( $ps_performance_score >= 50 ? esc_html__( 'Needs Work', 'sitepulse' ) : esc_html__( 'Poor', 'sitepulse' ) ); ?>
												</span>
											</div>
										</div>
									</div>
								<?php else : ?>
									<!-- Site Speed - Enhanced -->
									<div class="col-md-4">
										<div class="sp-pagespeed-metric" data-score="<?php echo esc_attr( $speed_score ); ?>">
											<div class="sp-circular-progress">
												<div class="sp-progress-ring">
													<div class="sp-progress-circle"></div>
												</div>
												<div class="sp-score-value"><?php echo esc_html( $speed_score ); ?></div>
											</div>
											<div class="sp-metric-label">
												<span class="dashicons dashicons-performance"></span>
												<?php echo esc_html__( 'Site Speed Score', 'sitepulse' ); ?>
											</div>
											<div class="sp-metric-status">
												<?php if ( $speed_score >= 80 ) : ?>
													<span class="badge bg-success"><?php echo esc_html__( 'Fast Loading', 'sitepulse' ); ?></span>
												<?php elseif ( $speed_score >= 60 ) : ?>
													<span
														class="badge bg-warning text-dark"><?php echo esc_html__( 'Moderate Speed', 'sitepulse' ); ?></span>
												<?php else : ?>
													<span class="badge bg-danger"><?php echo esc_html__( 'Slow Loading', 'sitepulse' ); ?></span>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php endif; ?>

								<!-- System Resources - Enhanced -->
								<div class="col-md-4">
									<div class="sp-pagespeed-metric" data-score="<?php echo esc_attr( $resource_score ); ?>">
										<div class="sp-circular-progress">
											<div class="sp-progress-ring">
												<div class="sp-progress-circle"></div>
											</div>
											<div class="sp-score-value"><?php echo esc_html( $resource_score ); ?></div>
										</div>
										<div class="sp-metric-label">
											<span class="dashicons dashicons-admin-settings"></span>
											<?php echo esc_html__( 'System Resources', 'sitepulse' ); ?>
										</div>
										<div class="sp-metric-status">
											<?php if ( $resource_score >= 80 ) : ?>
												<span class="badge bg-success"><?php echo esc_html__( 'Efficient', 'sitepulse' ); ?></span>
											<?php elseif ( $resource_score >= 60 ) : ?>
												<span
													class="badge bg-warning text-dark"><?php echo esc_html__( 'Acceptable', 'sitepulse' ); ?></span>
											<?php else : ?>
												<span class="badge bg-danger"><?php echo esc_html__( 'High Usage', 'sitepulse' ); ?></span>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<?php
				// Allow pro version to add widgets to basic view (loaded after Site Health Overview)
				do_action( 'sitepulse_dashboard_basic_view_widgets', $stats, $curl_events, $mem );
				?>

				<!-- Detailed Performance Cards -->
				<div class="col-md-6">
					<div class="card sp-pagespeed-widget h-100">
						<div class="sp-widget-header">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-chart-line"></span>
									<div>
										<strong><?php echo esc_html__( 'Page Loading Performance', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'How fast your pages load for visitors', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="sp-widget-content">
							<div class="mb-3">
								<div class="d-flex justify-content-between align-items-center mb-2">
									<span class="fw-bold"><?php echo esc_html__( 'Performance Score', 'sitepulse' ); ?></span>
									<span
										class="badge <?php echo $loadsentinel_score >= 80 ? 'bg-success' : ( $loadsentinel_score >= 60 ? 'bg-warning text-dark' : 'bg-danger' ); ?>">
										<?php echo esc_html( $loadsentinel_score ); ?>/100
									</span>
								</div>
								<div class="progress mb-3" style="height: 25px;">
									<div
										class="progress-bar <?php echo $loadsentinel_score >= 80 ? 'bg-success' : ( $loadsentinel_score >= 60 ? 'bg-warning' : 'bg-danger' ); ?>"
										role="progressbar" style="width: <?php echo esc_attr( $loadsentinel_score ); ?>%">
										<strong><?php echo esc_html( $loadsentinel_score ); ?>%</strong>
									</div>
								</div>
							</div>

							<div
								class="alert <?php echo $loadsentinel_score >= 80 ? 'alert-success' : ( $loadsentinel_score >= 60 ? 'alert-warning' : 'alert-danger' ); ?> mb-3">
								<?php if ( $loadsentinel_score >= 80 ) : ?>
									<strong><?php echo esc_html__( 'Great!', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'Your pages are loading quickly, which means visitors have a smooth experience on your site.', 'sitepulse' ); ?>
								<?php elseif ( $loadsentinel_score >= 60 ) : ?>
									<strong><?php echo esc_html__( 'Good, but can improve', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'Your pages load at a reasonable speed. To make them faster, consider optimizing images, using a caching plugin, or reducing the number of plugins.', 'sitepulse' ); ?>
								<?php else : ?>
									<strong><?php echo esc_html__( 'Needs Optimization', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'Your pages may be loading slowly. This can frustrate visitors and hurt your search engine rankings. Consider: optimizing images, using a caching plugin, reducing plugins, or upgrading your hosting plan.', 'sitepulse' ); ?>
								<?php endif; ?>
							</div>

							<div class="small text-muted">
								<strong><?php echo esc_html__( 'What this means:', 'sitepulse' ); ?></strong>
								<ul class="mb-0 mt-2">
									<li><?php echo esc_html__( 'Faster pages = Better user experience', 'sitepulse' ); ?></li>
									<li><?php echo esc_html__( 'Faster pages = Better search rankings', 'sitepulse' ); ?></li>
									<li><?php echo esc_html__( 'Faster pages = More conversions', 'sitepulse' ); ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="card sp-pagespeed-widget h-100">
						<div class="sp-widget-header">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-networking"></span>
									<div>
										<strong><?php echo esc_html__( 'External Connections', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'Connections to payment, social, and analytics services', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="sp-widget-content">
							<div class="mb-3">
								<div class="d-flex justify-content-between align-items-center mb-2">
									<span class="fw-bold"><?php echo esc_html__( 'Connection Score', 'sitepulse' ); ?></span>
									<span
										class="badge <?php echo $api_score >= 80 ? 'bg-success' : ( $api_score >= 60 ? 'bg-warning text-dark' : 'bg-danger' ); ?>">
										<?php echo esc_html( $api_score ); ?>/100
									</span>
								</div>
								<div class="progress mb-3" style="height: 25px;">
									<div
										class="progress-bar <?php echo $api_score >= 80 ? 'bg-success' : ( $api_score >= 60 ? 'bg-warning' : 'bg-danger' ); ?>"
										role="progressbar" style="width: <?php echo esc_attr( $api_score ); ?>%">
										<strong><?php echo esc_html( $api_score ); ?>%</strong>
									</div>
								</div>
							</div>

							<div
								class="alert <?php echo $api_score >= 80 ? 'alert-success' : ( $api_score >= 60 ? 'alert-warning' : 'alert-danger' ); ?> mb-3">
								<?php if ( $api_score >= 80 ) : ?>
									<strong><?php echo esc_html__( 'Excellent!', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'Your site connects to external services (like payment processors, social media, or analytics) very efficiently.', 'sitepulse' ); ?>
								<?php elseif ( $api_score >= 60 ) : ?>
									<strong><?php echo esc_html__( 'Working Well', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'Your external connections are functioning properly. Some connections may be slightly slower than ideal.', 'sitepulse' ); ?>
								<?php else : ?>
									<strong><?php echo esc_html__( 'Slow Connections', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'Some external services your site connects to are responding slowly. This could affect features like payments, social sharing, or analytics.', 'sitepulse' ); ?>
								<?php endif; ?>
							</div>

							<div class="small text-muted">
								<strong><?php echo esc_html__( 'External connections include:', 'sitepulse' ); ?></strong>
								<ul class="mb-0 mt-2">
									<li><?php echo esc_html__( 'Payment gateways (Stripe, PayPal, etc.)', 'sitepulse' ); ?></li>
									<li><?php echo esc_html__( 'Social media APIs (Facebook, Twitter, etc.)', 'sitepulse' ); ?></li>
									<li><?php echo esc_html__( 'Analytics services (Google Analytics, etc.)', 'sitepulse' ); ?></li>
									<li><?php echo esc_html__( 'Third-party services and APIs', 'sitepulse' ); ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="card sp-pagespeed-widget h-100">
						<div class="sp-widget-header">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-database"></span>
									<div>
										<strong><?php echo esc_html__( 'Memory Usage', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'How much server memory your site uses', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="sp-widget-content">
							<div class="mb-3">
								<div class="d-flex justify-content-between align-items-center mb-2">
									<span class="fw-bold"><?php echo esc_html__( 'Current Usage', 'sitepulse' ); ?></span>
									<span
										class="badge <?php echo $memory_score >= 80 ? 'bg-success' : ( $memory_score >= 60 ? 'bg-warning text-dark' : 'bg-danger' ); ?>">
										<?php echo esc_html( $mem['percent'] ); ?>
									</span>
								</div>
								<div class="progress mb-3" style="height: 25px;">
									<?php
									// Memory percentage is calculated in backend.php class
									?>
									<div
										class="progress-bar <?php echo $memory_score >= 80 ? 'bg-success' : ( $memory_score >= 60 ? 'bg-warning' : 'bg-danger' ); ?>"
										role="progressbar" style="width: <?php echo esc_attr( $mem_usage_percent ); ?>%">
										<strong><?php echo esc_html( $mem['percent'] ); ?></strong>
									</div>
								</div>
							</div>

							<div
								class="alert <?php echo $memory_score >= 80 ? 'alert-success' : ( $memory_score >= 60 ? 'alert-warning' : 'alert-danger' ); ?> mb-3">
								<?php if ( $memory_score >= 80 ) : ?>
									<strong><?php echo esc_html__( 'Excellent Memory Usage!', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'Your site is using memory very efficiently. This means your site can handle more visitors without slowing down.', 'sitepulse' ); ?>
								<?php elseif ( $memory_score >= 60 ) : ?>
									<strong><?php echo esc_html__( 'Acceptable Usage', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'Your memory usage is within acceptable limits. Monitor it to ensure it doesn\'t increase significantly.', 'sitepulse' ); ?>
								<?php else : ?>
									<strong><?php echo esc_html__( 'High Memory Usage', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'Your site is using a lot of memory. This could cause your site to slow down or crash during high traffic. Consider reducing plugins or upgrading your hosting plan.', 'sitepulse' ); ?>
								<?php endif; ?>
							</div>

							<div class="small text-muted">
								<strong><?php echo esc_html__( 'Memory is used for:', 'sitepulse' ); ?></strong>
								<ul class="mb-0 mt-2">
									<li><?php echo esc_html__( 'Running plugins and themes', 'sitepulse' ); ?></li>
									<li><?php echo esc_html__( 'Processing page requests', 'sitepulse' ); ?></li>
									<li><?php echo esc_html__( 'Storing temporary data', 'sitepulse' ); ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="card sp-pagespeed-widget h-100">
						<div class="sp-widget-header">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-admin-plugins"></span>
									<div>
										<strong><?php echo esc_html__( 'Active Plugins', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'Number of active plugins on your site', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="sp-widget-content">
							<div class="mb-3">
								<div class="d-flex justify-content-between align-items-center mb-2">
									<span class="fw-bold"><?php echo esc_html__( 'Total Plugins', 'sitepulse' ); ?></span>
									<span
										class="badge <?php echo $plugins_score >= 80 ? 'bg-success' : ( $plugins_score >= 60 ? 'bg-warning text-dark' : 'bg-danger' ); ?>">
										<?php echo esc_html( $active_plugins_count ); ?>
									</span>
								</div>
								<div class="progress mb-3" style="height: 25px;">
									<?php
									// Plugin percentage is calculated in backend.php class
									?>
									<div
										class="progress-bar <?php echo $plugins_score >= 80 ? 'bg-success' : ( $plugins_score >= 60 ? 'bg-warning' : 'bg-danger' ); ?>"
										role="progressbar" style="width: <?php echo esc_attr( $plugin_percent ); ?>%">
										<strong><?php echo esc_html( $active_plugins_count ); ?>
											<?php echo esc_html__( 'plugins', 'sitepulse' ); ?></strong>
									</div>
								</div>
							</div>

							<div
								class="alert <?php echo $plugins_score >= 80 ? 'alert-success' : ( $plugins_score >= 60 ? 'alert-warning' : 'alert-danger' ); ?> mb-3">
								<?php if ( $plugins_score >= 80 ) : ?>
									<strong><?php echo esc_html__( 'Good Plugin Count!', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'You have a reasonable number of plugins. Each plugin adds functionality but also uses resources, so keeping the count manageable is important.', 'sitepulse' ); ?>
								<?php elseif ( $plugins_score >= 60 ) : ?>
									<strong><?php echo esc_html__( 'Consider Reviewing', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'You have quite a few plugins. Review them regularly and deactivate any you\'re not using. Unused plugins can still slow down your site.', 'sitepulse' ); ?>
								<?php else : ?>
									<strong><?php echo esc_html__( 'Too Many Plugins', 'sitepulse' ); ?></strong>
									<?php echo esc_html__( 'You have a lot of plugins active. This can significantly slow down your site. Review and deactivate unused plugins, or consider combining functionality with fewer, more comprehensive plugins.', 'sitepulse' ); ?>
								<?php endif; ?>
							</div>

							<div class="small text-muted">
								<strong><?php echo esc_html__( 'Best practices:', 'sitepulse' ); ?></strong>
								<ul class="mb-0 mt-2">
									<li><?php echo esc_html__( 'Deactivate plugins you don\'t use', 'sitepulse' ); ?></li>
									<li><?php echo esc_html__( 'Delete unused plugins completely', 'sitepulse' ); ?></li>
									<li><?php echo esc_html__( 'Keep plugins updated', 'sitepulse' ); ?></li>
									<li><?php echo esc_html__( 'Use quality plugins from trusted sources', 'sitepulse' ); ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<!-- Security & Vulnerabilities Widget -->
				<div class="col-md-6">
					<div class="card sp-pagespeed-widget h-100">
						<div class="sp-widget-header">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-shield"></span>
									<div>
										<strong><?php echo esc_html__( 'Security & Vulnerabilities', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'Scan your site for known security vulnerabilities', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="sp-widget-content">
							<div class="d-flex justify-content-between align-items-center mb-3">
								<div class="text-muted small sp-last-check-time">
									<?php if ( ! empty( $last_vulnerability_check ) ) : ?>
										<?php echo esc_html__( 'Last checked:', 'sitepulse' ); ?>
										<span
											class="fw-bold"><?php echo esc_html( date( 'g:i A', strtotime( $last_vulnerability_check ) ) ); ?></span>
									<?php else : ?>
										<?php echo esc_html__( 'Not checked yet', 'sitepulse' ); ?>
									<?php endif; ?>
								</div>
							</div>

							<div class="sp-vulnerabilities-results">
								<?php if ( ! empty( $vulnerabilities ) ) : ?>
									<div class="alert alert-danger mb-0">
										<h6 class="alert-heading">
											<span class="dashicons dashicons-warning"></span>
											<?php echo esc_html__( 'Vulnerabilities Found', 'sitepulse' ); ?>
										</h6>
										<ul class="mb-0 ps-3">
											<?php foreach ( $vulnerabilities as $vuln ) : ?>
												<li>
													<?php echo esc_html( $vuln['name'] ); ?>
													<?php if ( isset( $vuln['version'] ) ) : ?>
														<span class="badge bg-danger ms-1"><?php echo esc_html( $vuln['version'] ); ?></span>
													<?php endif; ?>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php elseif ( ! empty( $last_vulnerability_check ) ) : ?>
									<div class="alert alert-success mb-0">
										<span class="dashicons dashicons-yes-alt me-1"></span>
										<?php echo esc_html__( 'No vulnerabilities found.', 'sitepulse' ); ?>
									</div>
								<?php else : ?>
									<div class="alert alert-info mb-0">
										<span class="dashicons dashicons-info me-1"></span>
										<?php echo esc_html__( 'Run a scan to check for vulnerabilities.', 'sitepulse' ); ?>
									</div>
								<?php endif; ?>
							</div>
							<div class="mt-3 text-end">
								<small class="text-muted fst-italic">
									<?php echo sprintf(
										/* translators: %s: Pro version link */
										esc_html__( 'Database powered by Wordfence and daily automatic checking & alerts with the %s', 'sitepulse' ),
										'<a href="https://sitepulse.me" target="_blank">' . esc_html__( 'Pro version', 'sitepulse' ) . '</a>'
									); ?>
								</small>
								<button type="button" class="btn btn-primary sp-check-vulnerabilities-btn">
									<span class="dashicons dashicons-shield-alt me-1"></span>
									<?php echo esc_html__( 'Check Vulnerabilities', 'sitepulse' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Error Status - Enhanced -->
				<div class="col-md-12">
					<div class="card sp-pagespeed-widget">
						<div class="sp-widget-header">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-warning"></span>
									<div>
										<strong><?php echo esc_html__( 'Error Status & Site Stability', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'Errors and warnings detected on your site', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="sp-widget-content">
							<div class="row">
								<div class="col-md-8">
									<?php if ( $error_count === 0 ) : ?>
										<div class="alert alert-success mb-3">
											<h6 class="alert-heading">
												<span class="dashicons dashicons-yes-alt"></span>
												<?php echo esc_html__( 'No Errors Found!', 'sitepulse' ); ?>
											</h6>
											<p class="mb-0">
												<?php echo esc_html__( 'Excellent news! Your site is running smoothly with no errors detected in the last 2 days. This means your site is stable and functioning properly.', 'sitepulse' ); ?>
											</p>
										</div>
										<div class="small text-muted">
											<strong><?php echo esc_html__( 'What this means:', 'sitepulse' ); ?></strong>
											<ul class="mb-0 mt-2">
												<li><?php echo esc_html__( 'Your site code is working correctly', 'sitepulse' ); ?></li>
												<li><?php echo esc_html__( 'No conflicts between plugins or themes', 'sitepulse' ); ?></li>
												<li><?php echo esc_html__( 'Visitors are experiencing a smooth site', 'sitepulse' ); ?></li>
												<li><?php echo esc_html__( 'Your site is ready for production use', 'sitepulse' ); ?></li>
											</ul>
										</div>
									<?php else : ?>
										<div class="alert <?php echo $fatal_count > 0 ? 'alert-danger' : 'alert-warning'; ?> mb-3">
											<h6 class="alert-heading">
												<span class="dashicons dashicons-warning"></span>
												<?php
												if ( $fatal_count > 0 && $warning_count > 0 ) {
													echo esc_html( sprintf(
														/* translators: %1$d: number of errors, %2$d: number of warnings */
														__( '%1$d Errors and %2$d Warnings Found', 'sitepulse' ),
														$fatal_count,
														$warning_count
													) );
												} elseif ( $fatal_count > 0 ) {
													echo esc_html( sprintf(
														/* translators: %d: number of errors */
														_n( '%d Error Found', '%d Errors Found', $fatal_count, 'sitepulse' ),
														$fatal_count
													) );
												} else {
													echo esc_html( sprintf(
														/* translators: %d: number of warnings */
														_n( '%d Warning Found', '%d Warnings Found', $warning_count, 'sitepulse' ),
														$warning_count
													) );
												}
												?>
											</h6>
											<p class="mb-2">
												<?php if ( $fatal_count > 0 ) : ?>
													<strong><?php echo esc_html__( 'Errors:', 'sitepulse' ); ?></strong>
													<?php echo esc_html__( 'These are serious issues that can cause parts of your site to stop working. They should be fixed as soon as possible.', 'sitepulse' ); ?>
												<?php endif; ?>
												<?php if ( $warning_count > 0 ) : ?>
													<?php if ( $fatal_count > 0 )
														echo '<br>'; ?>
													<strong><?php echo esc_html__( 'Warnings:', 'sitepulse' ); ?></strong>
													<?php echo esc_html__( 'These are less serious but may indicate potential problems. They should be reviewed and addressed when possible.', 'sitepulse' ); ?>
												<?php endif; ?>
											</p>
											<p class="mb-0 small">
												<strong><?php echo esc_html__( 'What to do:', 'sitepulse' ); ?></strong>
												<?php echo esc_html__( 'Switch to Developer View to see detailed information about each error, including where it occurred and how to fix it. If you\'re not technical, consider contacting your developer or hosting support.', 'sitepulse' ); ?>
											</p>
										</div>
									<?php endif; ?>
								</div>
								<div class="col-md-4 text-center p-2">
									<div class="sp-error-score-card">
										<div class="sp-error-score-icon">
											<span
												class="dashicons <?php echo $error_count === 0 ? 'dashicons-yes-alt' : ( $fatal_count > 0 ? 'dashicons-dismiss' : 'dashicons-warning' ); ?>"></span>
										</div>
										<div
											class="sp-error-score-value <?php echo $error_count === 0 ? 'text-success' : ( $fatal_count > 0 ? 'text-danger' : 'text-warning' ); ?>">
											<?php echo esc_html( $error_log_score ); ?>
										</div>
										<div class="sp-error-score-label"><?php echo esc_html__( 'Error Score', 'sitepulse' ); ?></div>
										<div class="sp-error-score-progress">
											<div
												class="sp-error-progress-bar <?php echo $error_count === 0 ? 'sp-progress-success' : ( $fatal_count > 0 ? 'sp-progress-danger' : 'sp-progress-warning' ); ?>"
												role="progressbar" style="width: <?php echo esc_attr( $error_log_score ); ?>%">
												<span class="sp-progress-text"><?php echo esc_html( $error_log_score ); ?>%</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Slow Plugins & Themes Warning Section -->
				<?php if ( $show_performance_warnings ) : ?>
					<div class="col-12">
						<div class="card sp-pagespeed-widget">
							<div class="sp-widget-header">
								<div class="sp-widget-title-section">
									<div class="sp-widget-title">
										<span class="dashicons dashicons-performance"></span>
										<div>
											<strong><?php echo esc_html__( 'Performance Warnings', 'sitepulse' ); ?></strong>
											<div class="sp-widget-subtitle">
												<?php echo esc_html__( 'Plugins, themes, or processes that are slowing down your site', 'sitepulse' ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="sp-widget-content">
								<!-- Performance Score Based on Load Time -->
								<?php if ( $total_load_time > 0 ) : ?>
									<div
										class="alert <?php echo $load_time_score >= 80 ? 'alert-success' : ( $load_time_score >= 60 ? 'alert-info' : ( $load_time_score >= 40 ? 'alert-warning' : 'alert-danger' ) ); ?> mb-3">
										<div class="d-flex justify-content-between align-items-center mb-2">
											<h6 class="alert-heading mb-0">
												<span class="dashicons dashicons-clock"></span>
												<?php echo esc_html__( 'Load Time Performance Score', 'sitepulse' ); ?>
											</h6>
											<span
												class="badge <?php echo $load_time_score >= 80 ? 'bg-success' : ( $load_time_score >= 60 ? 'bg-info' : ( $load_time_score >= 40 ? 'bg-warning text-dark' : 'bg-danger' ) ); ?>"
												style="font-size: 1.1rem; padding: 0.5rem 1rem;">
												<?php echo esc_html( $load_time_score ); ?>/100
											</span>
										</div>
										<p class="mb-0">
											<strong><?php echo esc_html__( 'Current Load Time:', 'sitepulse' ); ?></strong>
											<span
												class="badge <?php echo $total_load_time > 5000 ? 'bg-danger' : ( $total_load_time > 3000 ? 'bg-warning text-dark' : ( $total_load_time > 2000 ? 'bg-warning text-dark' : 'bg-success' ) ); ?> ms-2">
												<?php
												if ( $total_load_time >= 1000 ) {
													echo esc_html( number_format( $total_load_time / 1000, 2 ) . ' seconds' );
												} else {
													echo esc_html( number_format( $total_load_time, 0 ) . ' ms' );
												}
												?>
											</span>
										</p>
									</div>
								<?php endif; ?>

								<!-- Top Slowest Item -->
								<?php if ( $top_slow_item && $top_slow_item['time_ms'] > 500 ) : ?>
									<div class="alert alert-danger mb-3">
										<h6 class="alert-heading">
											<span class="dashicons dashicons-flag"></span>
											<?php echo esc_html__( 'Top Performance Issue', 'sitepulse' ); ?>
										</h6>
										<p class="mb-2">
											<strong><?php echo esc_html__( 'Slowest Item:', 'sitepulse' ); ?></strong>
											<span class="badge bg-danger ms-2">
												<?php echo esc_html( $top_slow_item['name'] ); ?>
											</span>
											<span class="badge bg-danger ms-2">
												<?php
												$time_seconds = round( $top_slow_item['time_ms'] / 1000, 2 );
												if ( $time_seconds >= 1 ) {
													echo esc_html( number_format( $top_slow_item['time_ms'], 0 ) . ' ms (' . $time_seconds . 's)' );
												} else {
													echo esc_html( number_format( $top_slow_item['time_ms'], 0 ) . ' ms' );
												}
												?>
											</span>
										</p>
										<?php if ( ! empty( $top_slow_item['hook'] ) || ! empty( $top_slow_item['fileline'] ) ) : ?>
											<p class="mb-0 small text-muted">
												<?php if ( ! empty( $top_slow_item['hook'] ) ) : ?>
													<strong><?php echo esc_html__( 'Hook:', 'sitepulse' ); ?></strong>
													<?php echo esc_html( $top_slow_item['hook'] ); ?>
													<?php if ( ! empty( $top_slow_item['fileline'] ) ) : ?> • <?php endif; ?>
												<?php endif; ?>
												<?php if ( ! empty( $top_slow_item['fileline'] ) ) : ?>
													<strong><?php echo esc_html__( 'Location:', 'sitepulse' ); ?></strong>
													<?php echo esc_html( $top_slow_item['fileline'] ); ?>
												<?php endif; ?>
											</p>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<!-- Slow Plugins/Themes List -->
								<?php if ( ! empty( $all_slow_items ) ) : ?>
									<?php
									$critical_count = 0;
									$warning_count_slow = 0;
									foreach ( $all_slow_items as $item ) {
										if ( $item['severity'] === 'critical' ) {
											$critical_count++;
										} else {
											$warning_count_slow++;
										}
									}
									$alert_type = $critical_count > 0 ? 'alert-danger' : 'alert-warning';
									?>
									<div class="alert <?php echo esc_attr( $alert_type ); ?> mb-3">
										<h6 class="alert-heading">
											<span class="dashicons dashicons-admin-plugins"></span>
											<?php
											if ( $critical_count > 0 && $warning_count_slow > 0 ) {
												echo esc_html( sprintf(
													/* translators: %1$d: number of critical issues, %2$d: number of warning issues */
													__( '%1$d Critical and %2$d Warning Issues Found', 'sitepulse' ),
													$critical_count,
													$warning_count_slow
												) );
											} elseif ( $critical_count > 0 ) {
												echo esc_html( sprintf(
													/* translators: %d: number of critical issues */
													_n( '%d Critical Issue Found', '%d Critical Issues Found', $critical_count, 'sitepulse' ),
													$critical_count
												) );
											} else {
												echo esc_html( sprintf(
													/* translators: %d: number of performance warnings */
													_n( '%d Performance Warning', '%d Performance Warnings', $warning_count_slow, 'sitepulse' ),
													$warning_count_slow
												) );
											}
											?>
										</h6>
										<p class="mb-2">
											<?php if ( $critical_count > 0 ) : ?>
												<strong><?php echo esc_html__( 'Critical Issues:', 'sitepulse' ); ?></strong>
												<?php echo esc_html__( 'These plugins or themes are taking more than 1 second to load, which significantly slows down your site. Consider replacing or optimizing them.', 'sitepulse' ); ?>
											<?php endif; ?>
											<?php if ( $warning_count_slow > 0 ) : ?>
												<?php if ( $critical_count > 0 )
													echo '<br>'; ?>
												<strong><?php echo esc_html__( 'Warnings:', 'sitepulse' ); ?></strong>
												<?php echo esc_html__( 'These items are taking longer than ideal to load (over 0.5 seconds). While not critical, optimizing them can improve your site speed.', 'sitepulse' ); ?>
											<?php endif; ?>
										</p>

										<div class="mt-3">
											<strong><?php echo esc_html__( 'Slow Items Detected:', 'sitepulse' ); ?></strong>
											<ul class="mb-0 mt-2">
												<?php
												$display_count = 0;
												foreach ( $all_slow_items as $item ) :
													if ( $display_count >= 5 )
														break; // Limit to top 5
													$time_seconds = round( $item['time_ms'] / 1000, 2 );
													$badge_class = $item['severity'] === 'critical' ? 'bg-danger' : 'bg-warning text-dark';
													?>
													<li class="mb-2">
														<strong><?php echo esc_html( $item['name'] ); ?></strong>
														<span class="badge <?php echo esc_attr( $badge_class ); ?> ms-2">
															<?php echo esc_html( number_format( $item['time_ms'], 0 ) ); ?> ms
															<?php if ( $time_seconds >= 1 ) : ?>
																(<?php echo esc_html( $time_seconds ); ?>s)
															<?php endif; ?>
														</span>
														<?php if ( $item['severity'] === 'critical' ) : ?>
															<span class="text-danger small ms-2">
																<span class="dashicons dashicons-warning"
																	style="font-size: 14px; width: 14px; height: 14px;"></span>
																<?php echo esc_html__( 'Very Slow', 'sitepulse' ); ?>
															</span>
														<?php endif; ?>
													</li>
													<?php
													$display_count++;
												endforeach;
												if ( count( $all_slow_items ) > 5 ) :
													?>
													<li class="text-muted small">
														<?php
														echo esc_html( sprintf(
															/* translators: %d: number of additional slow items not shown */
															__( '... and %d more. Switch to Developer View to see all.', 'sitepulse' ),
															count( $all_slow_items ) - 5
														) ); ?>
													</li>
												<?php endif; ?>
											</ul>
										</div>
									</div>
								<?php endif; ?>

								<!-- Slow API Requests -->
								<?php if ( ! empty( $slow_api_requests ) ) : ?>
									<?php
									$critical_api_count = 0;
									$warning_api_count = 0;
									foreach ( $slow_api_requests as $api ) {
										if ( $api['severity'] === 'critical' ) {
											$critical_api_count++;
										} else {
											$warning_api_count++;
										}
									}
									$api_alert_type = $critical_api_count > 0 ? 'alert-danger' : 'alert-warning';
									?>
									<div class="alert <?php echo esc_attr( $api_alert_type ); ?> mb-3">
										<h6 class="alert-heading">
											<span class="dashicons dashicons-networking"></span>
											<?php
											if ( $critical_api_count > 0 && $warning_api_count > 0 ) {
												echo esc_html( sprintf(
													/* translators: %1$d: number of critical slow API requests, %2$d: number of warning slow API requests */
													__( '%1$d Critical and %2$d Warning Slow API Requests', 'sitepulse' ),
													$critical_api_count,
													$warning_api_count
												) );
											} elseif ( $critical_api_count > 0 ) {
												echo esc_html( sprintf(
													/* translators: %d: number of critical slow API requests */
													_n( '%d Critical Slow API Request', '%d Critical Slow API Requests', $critical_api_count, 'sitepulse' ),
													$critical_api_count
												) );
											} else {
												echo esc_html( sprintf(
													/* translators: %d: number of slow API requests */
													_n( '%d Slow API Request', '%d Slow API Requests', $warning_api_count, 'sitepulse' ),
													$warning_api_count
												) );
											}
											?>
										</h6>
										<p class="mb-2">
											<?php echo esc_html__( 'Your site is making requests to external services (like payment processors, analytics, or social media) that are responding slowly. This can delay page loading.', 'sitepulse' ); ?>
										</p>
										<div class="mt-3">
											<strong><?php echo esc_html__( 'Slow API Requests:', 'sitepulse' ); ?></strong>
											<ul class="mb-0 mt-2">
												<?php
												$api_display_count = 0;
												foreach ( $slow_api_requests as $api ) :
													if ( $api_display_count >= 3 )
														break; // Limit to top 3
													$time_seconds = round( $api['time_ms'] / 1000, 2 );
													$badge_class = $api['severity'] === 'critical' ? 'bg-danger' : 'bg-warning text-dark';
													$url_display = strlen( $api['url'] ) > 60 ? substr( $api['url'], 0, 60 ) . '...' : $api['url'];
													?>
													<li class="mb-2">
														<strong><?php echo esc_html( $api['origin'] ); ?></strong>
														<span class="badge <?php echo esc_attr( $badge_class ); ?> ms-2">
															<?php echo esc_html( number_format( $api['time_ms'], 0 ) ); ?> ms
															<?php if ( $time_seconds >= 1 ) : ?>
																(<?php echo esc_html( $time_seconds ); ?>s)
															<?php endif; ?>
														</span>
														<?php if ( ! empty( $api['code'] ) ) : ?>
															<span class="badge bg-secondary ms-2">HTTP <?php echo esc_html( $api['code'] ); ?></span>
														<?php endif; ?>
														<div class="small text-muted mt-1 ms-3">
															<?php echo esc_html( $url_display ); ?>
														</div>
													</li>
													<?php
													$api_display_count++;
												endforeach;
												if ( count( $slow_api_requests ) > 3 ) :
													?>
													<li class="text-muted small">
														<?php
														echo esc_html( sprintf(
															/* translators: %d: number of additional slow API requests not shown */
															__( '... and %d more. Switch to Developer View to see all.', 'sitepulse' ),
															count( $slow_api_requests ) - 3
														) ); ?>
													</li>
												<?php endif; ?>
											</ul>
										</div>
									</div>
								<?php endif; ?>

								<!-- Possible Causes and Recommendations -->
								<div class="alert alert-info mb-3">
									<h6 class="alert-heading">
										<span class="dashicons dashicons-lightbulb"></span>
										<?php echo esc_html__( 'Possible Causes & Recommendations', 'sitepulse' ); ?>
									</h6>
									<div class="mt-2">
										<?php
										$causes = array();

										// Check for slow plugins/themes
										if ( ! empty( $all_slow_items ) ) {
											$causes[] = sprintf(
												/* translators: %d: number of slow plugins or themes */
												__( '<strong>Slow Plugins/Themes:</strong> %d plugin(s) or theme(s) are taking too long to load. Consider deactivating unused ones, updating them, or finding faster alternatives.', 'sitepulse' ),
												count( $all_slow_items )
											);
										}

										// Check for slow API requests
										if ( ! empty( $slow_api_requests ) ) {
											$causes[] = sprintf(
												/* translators: %d: number of slow external service connections */
												__( '<strong>Slow External Connections:</strong> %d external service(s) are responding slowly. These might be payment processors, analytics, or social media APIs. Consider using asynchronous loading or removing unnecessary integrations.', 'sitepulse' ),
												count( $slow_api_requests )
											);
										}

										// Check for overall slow load time
										if ( $total_load_time > 3000 ) {
											$causes[] = __( '<strong>Overall Slow Performance:</strong> Your site is taking more than 2 seconds to load. This could be due to multiple factors including hosting, database queries, or too many active plugins.', 'sitepulse' );
										} elseif ( $total_load_time > 2000 ) {
											$causes[] = __( '<strong>Moderate Performance Issues:</strong> Your site load time is above 2 seconds. While acceptable, optimizing could significantly improve user experience.', 'sitepulse' );
										}

										// Check for too many plugins
										if ( $active_plugins_count > 30 ) {
											$causes[] = sprintf(
												/* translators: %d: number of active plugins */
												__( '<strong>Too Many Plugins:</strong> You have %d active plugins. Each plugin adds overhead. Review and deactivate unused plugins.', 'sitepulse' ),
												$active_plugins_count
											);
										}

										// Check for high memory usage
										if ( $memory_score < 60 ) {
											$causes[] = __( '<strong>High Memory Usage:</strong> Your site is using a lot of server memory. This can slow down page loading. Consider reducing plugins or upgrading hosting.', 'sitepulse' );
										}

										// Default recommendations if no specific causes
										if ( empty( $causes ) ) {
											$causes[] = __( '<strong>General Optimization:</strong> While no major issues were detected, you can still improve performance by using caching, optimizing images, and keeping everything updated.', 'sitepulse' );
										}

										// Display causes
										if ( ! empty( $causes ) ) :
											?>
											<ul class="mb-0 mt-2">
												<?php foreach ( $causes as $cause ) : ?>
													<li class="mb-2"><?php echo wp_kses_post( $cause ); ?></li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</div>
								</div>

								<!-- Action Items -->
								<div class="mt-3 small">
									<strong><?php echo esc_html__( 'Recommended Actions:', 'sitepulse' ); ?></strong>
									<ul class="mb-0 mt-2">
										<?php if ( ! empty( $all_slow_items ) ) : ?>
											<li>
												<?php echo esc_html__( 'Review and optimize the slow plugins/themes listed above', 'sitepulse' ); ?>
											</li>
										<?php endif; ?>
										<?php if ( ! empty( $slow_api_requests ) ) : ?>
											<li>
												<?php echo esc_html__( 'Consider using asynchronous loading for external API calls', 'sitepulse' ); ?>
											</li>
											<li><?php echo esc_html__( 'Review if all external integrations are necessary', 'sitepulse' ); ?></li>
										<?php endif; ?>
										<li><?php echo esc_html__( 'Install and configure a caching plugin', 'sitepulse' ); ?></li>
										<li><?php echo esc_html__( 'Optimize images and reduce file sizes', 'sitepulse' ); ?></li>
										<li><?php echo esc_html__( 'Deactivate and delete unused plugins', 'sitepulse' ); ?></li>
										<li><?php echo esc_html__( 'Keep WordPress, themes, and plugins updated', 'sitepulse' ); ?></li>
										<li>
											<?php echo esc_html__( 'Consider upgrading your hosting plan for better performance', 'sitepulse' ); ?>
										</li>
										<li>
											<?php echo esc_html__( 'Switch to Developer View for detailed technical analysis', 'sitepulse' ); ?>
										</li>
									</ul>
								</div>

								<?php if ( $total_load_time > 2000 || $is_overall_slow ) : ?>
									<div
										class="alert <?php echo ( $total_load_time > 5000 || $loadsentinel_score < 40 ) ? 'alert-danger' : 'alert-warning'; ?> mb-3">
										<h6 class="alert-heading">
											<span class="dashicons dashicons-clock"></span>
											<?php echo esc_html__( 'Overall Site Performance', 'sitepulse' ); ?>
										</h6>
										<?php if ( $total_load_time > 2000 ) : ?>
											<p class="mb-2">
												<strong><?php echo esc_html__( 'Total Load Time:', 'sitepulse' ); ?></strong>
												<span
													class="badge <?php echo $total_load_time > 5000 ? 'bg-danger' : 'bg-warning text-dark'; ?> ms-2">
													<?php
													if ( $total_load_time >= 1000 ) {
														echo esc_html( number_format( $total_load_time / 1000, 2 ) . ' seconds' );
													} else {
														echo esc_html( number_format( $total_load_time, 0 ) . ' ms' );
													}
													?>
												</span>
											</p>
										<?php endif; ?>
										<?php if ( $is_overall_slow ) : ?>
											<p class="mb-2">
												<strong><?php echo esc_html__( 'Performance Score:', 'sitepulse' ); ?></strong>
												<span
													class="badge <?php echo $loadsentinel_score < 40 ? 'bg-danger' : 'bg-warning text-dark'; ?> ms-2">
													<?php echo esc_html( $loadsentinel_score ); ?>/100
												</span>
												<?php if ( $loadsentinel_score < 40 ) : ?>
													<span class="text-danger small ms-2">
														<span class="dashicons dashicons-warning"
															style="font-size: 14px; width: 14px; height: 14px;"></span>
														<?php echo esc_html__( 'Very Slow', 'sitepulse' ); ?>
													</span>
												<?php elseif ( $loadsentinel_score < 60 ) : ?>
													<span class="text-warning small ms-2">
														<span class="dashicons dashicons-warning"
															style="font-size: 14px; width: 14px; height: 14px;"></span>
														<?php echo esc_html__( 'Slow', 'sitepulse' ); ?>
													</span>
												<?php endif; ?>
											</p>
										<?php endif; ?>
										<p class="mb-0">
											<?php if ( $total_load_time > 5000 || $loadsentinel_score < 40 ) : ?>
												<strong><?php echo esc_html__( 'Very Slow Performance:', 'sitepulse' ); ?></strong>
												<?php echo esc_html__( 'Your site is loading very slowly. This significantly impacts user experience and search engine rankings. Visitors may leave before your site finishes loading. This needs immediate attention.', 'sitepulse' ); ?>
											<?php elseif ( $total_load_time > 3000 || $loadsentinel_score < 50 ) : ?>
												<strong><?php echo esc_html__( 'Slow Performance:', 'sitepulse' ); ?></strong>
												<?php echo esc_html__( 'Your site is loading slower than ideal. Most visitors expect sites to load in under 3 seconds. Improving this will help keep visitors on your site and improve your search rankings.', 'sitepulse' ); ?>
											<?php elseif ( $total_load_time > 2000 || $loadsentinel_score < 60 ) : ?>
												<strong><?php echo esc_html__( 'Moderate Performance:', 'sitepulse' ); ?></strong>
												<?php echo esc_html__( 'Your site load time is acceptable but could be improved. Aim for under 2 seconds for the best user experience and better search engine rankings.', 'sitepulse' ); ?>
											<?php else : ?>
												<strong><?php echo esc_html__( 'Performance Notice:', 'sitepulse' ); ?></strong>
												<?php echo esc_html__( 'Your site performance could be optimized further. Consider the recommendations below to improve load times.', 'sitepulse' ); ?>
											<?php endif; ?>
										</p>
										<div class="mt-3 small">
											<strong><?php echo esc_html__( 'To improve load time:', 'sitepulse' ); ?></strong>
											<ul class="mb-0 mt-2">
												<?php if ( ! empty( $all_slow_items ) ) : ?>
													<li><?php echo esc_html__( 'Address the slow plugins and themes listed above', 'sitepulse' ); ?>
													</li>
												<?php endif; ?>
												<li><?php echo esc_html__( 'Use a caching plugin to speed up page delivery', 'sitepulse' ); ?></li>
												<li><?php echo esc_html__( 'Optimize images and reduce their file sizes', 'sitepulse' ); ?></li>
												<li>
													<?php echo esc_html__( 'Consider upgrading your hosting plan for better performance', 'sitepulse' ); ?>
												</li>
												<li>
													<?php echo esc_html__( 'Use a Content Delivery Network (CDN) to serve content faster', 'sitepulse' ); ?>
												</li>
												<li>
													<?php echo esc_html__( 'Minimize the number of active plugins and remove unused ones', 'sitepulse' ); ?>
												</li>
											</ul>
										</div>
									</div>
								<?php endif; ?>

								<?php if ( empty( $all_slow_items ) && $total_load_time <= 2000 && ! $is_overall_slow ) : ?>
									<div class="alert alert-success mb-0">
										<h6 class="alert-heading">
											<span class="dashicons dashicons-yes-alt"></span>
											<?php echo esc_html__( 'No Performance Issues Detected!', 'sitepulse' ); ?>
										</h6>
										<p class="mb-0">
											<?php echo esc_html__( 'Great news! We haven\'t detected any plugins, themes, or processes that are significantly slowing down your site. Your site should be loading at a good speed.', 'sitepulse' ); ?>
										</p>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<!-- Quick Tips Section -->
				<div class="col-12">
					<div class="card sp-pagespeed-widget">
						<div class="sp-widget-header">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-lightbulb"></span>
									<div>
										<strong><?php echo esc_html__( 'Quick Tips to Improve Your Site Performance', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'Actionable advice to make your site faster and more efficient', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="sp-widget-content">
							<div class="row g-3">
								<div class="col-md-4 p-3">
									<div class="sp-tip-card sp-tip-card-1">
										<div class="sp-tip-icon">
											<span class="dashicons dashicons-format-image"></span>
										</div>
										<h6 class="sp-tip-title">
											<?php echo esc_html__( 'Optimize Images', 'sitepulse' ); ?>
										</h6>
										<p class="sp-tip-description">
											<?php echo esc_html__( 'Large images slow down your site. Use image optimization plugins or compress images before uploading.', 'sitepulse' ); ?>
										</p>
									</div>
								</div>
								<div class="col-md-4  p-3">
									<div class="sp-tip-card sp-tip-card-2">
										<div class="sp-tip-icon">
											<span class="dashicons dashicons-admin-plugins"></span>
										</div>
										<h6 class="sp-tip-title">
											<?php echo esc_html__( 'Review Plugins', 'sitepulse' ); ?>
										</h6>
										<p class="sp-tip-description">
											<?php echo esc_html__( 'Deactivate and delete plugins you don\'t use. Each plugin uses resources even when inactive.', 'sitepulse' ); ?>
										</p>
									</div>
								</div>
								<div class="col-md-4 p-3">
									<div class="sp-tip-card sp-tip-card-3">
										<div class="sp-tip-icon">
											<span class="dashicons dashicons-update"></span>
										</div>
										<h6 class="sp-tip-title">
											<?php echo esc_html__( 'Use Caching', 'sitepulse' ); ?>
										</h6>
										<p class="sp-tip-description">
											<?php echo esc_html__( 'Install a caching plugin to store frequently accessed pages and make them load faster.', 'sitepulse' ); ?>
										</p>
									</div>
								</div>
								<div class="col-md-4 p-3">
									<div class="sp-tip-card sp-tip-card-4">
										<div class="sp-tip-icon">
											<span class="dashicons dashicons-admin-tools"></span>
										</div>
										<h6 class="sp-tip-title">
											<?php echo esc_html__( 'Keep Updated', 'sitepulse' ); ?>
										</h6>
										<p class="sp-tip-description">
											<?php echo esc_html__( 'Keep WordPress, themes, and plugins updated. Updates often include performance improvements.', 'sitepulse' ); ?>
										</p>
									</div>
								</div>
								<div class="col-md-4 p-3">
									<div class="sp-tip-card sp-tip-card-5">
										<div class="sp-tip-icon">
											<span class="dashicons dashicons-cloud"></span>
										</div>
										<h6 class="sp-tip-title">
											<?php echo esc_html__( 'Quality Hosting', 'sitepulse' ); ?>
										</h6>
										<p class="sp-tip-description">
											<?php echo esc_html__( 'Choose a reliable hosting provider with good performance. Cheap hosting often means slow sites.', 'sitepulse' ); ?>
										</p>
									</div>
								</div>
								<div class="col-md-4 p-3">
									<div class="sp-tip-card sp-tip-card-6">
										<div class="sp-tip-icon">
											<span class="dashicons dashicons-admin-generic"></span>
										</div>
										<h6 class="sp-tip-title">
											<?php echo esc_html__( 'Monitor Regularly', 'sitepulse' ); ?>
										</h6>
										<p class="sp-tip-description">
											<?php echo esc_html__( 'Check this dashboard regularly to catch performance issues early before they become problems.', 'sitepulse' ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>



		<!-- Developer View - Technical Dashboard -->
		<div id="sp-developer-view" class="sp-view-mode">
			<!-- Status Section - PageSpeed Insights Style -->
			<div class="row g-3 mb-3">
				<div class="col-12">
					<div class="card sp-status-card sp-pagespeed-style">
						<div class="sp-status-header-compact">
							<div class="sp-status-header-content">
								<div class="sp-status-title-compact">
									<span class="dashicons dashicons-performance"></span>
									<strong><?php echo esc_html__( 'Site Insights', 'sitepulse' ); ?></strong>
								</div>
								<?php
								$is_monitoring_active = ( $sitepulse_profiler_enabled == 1 && $sitepulse_lowhttp_enabled == 1 );
								?>
								<div class="sp-status-pulse <?php echo ! $is_monitoring_active ? 'inactive' : ''; ?>">
									<span class="sp-pulse-dot"></span>
									<span class="sp-pulse-text">
										<?php echo $is_monitoring_active
											? esc_html__( 'Live', 'sitepulse' )
											: esc_html__( 'Inactive', 'sitepulse' );
										?>
									</span>
								</div>
							</div>
						</div>

						<div class="alert alert-info border mb-4 text-center">
							<small class="text-muted">
								<span class="badge bg-danger me-1">&lt;90</span>
								<?php echo esc_html__( 'Needs Improvement', 'sitepulse' ); ?>
								<span class="mx-2">|</span>
								<span class="badge bg-warning text-dark me-1">90-99</span>
								<?php echo esc_html__( 'Good, but can be better', 'sitepulse' ); ?>
								<span class="mx-2">|</span>
								<span class="badge bg-success me-1">100</span> <?php echo esc_html__( 'Optimal', 'sitepulse' ); ?>
							</small>
						</div>

						<div class="card-body sp-pagespeed-body">
							<div class="row g-3 justify-content-center">
								<!-- LoadSentinel Metric -->
								<div class="col-6 col-md-2">
									<div class="sp-pagespeed-metric" data-score="<?php echo esc_attr( $loadsentinel_score ); ?>">
										<div class="sp-circular-progress">
											<div class="sp-progress-ring">
												<div class="sp-progress-circle"></div>
											</div>
											<div class="sp-score-value"><?php echo esc_html( $loadsentinel_score ); ?></div>
										</div>
										<div class="sp-metric-label">
											<span class="dashicons dashicons-chart-line"></span>
											<?php echo esc_html__( 'LoadSentinel Score', 'sitepulse' ); ?>
										</div>
										<div class="sp-metric-status">
											<span
												class="badge <?php echo $sitepulse_profiler_enabled == 1 ? 'bg-success' : 'bg-secondary'; ?>">
												<?php echo $sitepulse_profiler_enabled == 1 ? esc_html__( 'Active', 'sitepulse' ) : esc_html__( 'Inactive', 'sitepulse' ); ?>
											</span>
										</div>
									</div>
								</div>

								<?php if ( $has_valid_pagespeed && $ps_performance_score > 0 ) : ?>
									<div class="col-6 col-md-2">
										<div class="sp-pagespeed-metric" data-score="<?php echo esc_attr( $ps_performance_score ); ?>">
											<div class="sp-circular-progress">
												<div class="sp-progress-ring">
													<div class="sp-progress-circle"></div>
												</div>
												<div class="sp-score-value"><?php echo esc_html( $ps_performance_score ); ?></div>
											</div>
											<div class="sp-metric-label">
												<span class="dashicons dashicons-performance"></span>
												<?php echo esc_html__( 'PageSpeed Performance', 'sitepulse' ); ?>
											</div>
											<div class="sp-metric-status">
												<span
													class="badge <?php echo $ps_performance_score >= 90 ? 'bg-success' : ( $ps_performance_score >= 50 ? 'bg-warning text-dark' : 'bg-danger' ); ?>">
													<?php echo $ps_performance_score >= 90 ? esc_html__( 'Excellent', 'sitepulse' ) : ( $ps_performance_score >= 50 ? esc_html__( 'Needs Work', 'sitepulse' ) : esc_html__( 'Poor', 'sitepulse' ) ); ?>
												</span>
											</div>
										</div>
									</div>
								<?php endif; ?>

								<!-- API & Request Metric -->
								<div class="col-6 col-md-2">
									<div class="sp-pagespeed-metric" data-score="<?php echo esc_attr( $api_score ); ?>">
										<div class="sp-circular-progress">
											<div class="sp-progress-ring">
												<div class="sp-progress-circle"></div>
											</div>
											<div class="sp-score-value"><?php echo esc_html( $api_score ); ?></div>
										</div>
										<div class="sp-metric-label">
											<span class="dashicons dashicons-networking"></span>
											<?php echo esc_html__( 'API & Request Score', 'sitepulse' ); ?>
										</div>
										<div class="sp-metric-status">
											<span
												class="badge <?php echo $sitepulse_lowhttp_enabled == 1 ? 'bg-success' : 'bg-secondary'; ?>">
												<?php echo $sitepulse_lowhttp_enabled == 1 ? esc_html__( 'Active', 'sitepulse' ) : esc_html__( 'Inactive', 'sitepulse' ); ?>
											</span>
										</div>
									</div>
								</div>

								<!-- Memory Usage Metric -->
								<div class="col-6 col-md-2">
									<div class="sp-pagespeed-metric sp-memory-metric"
										data-score="<?php echo esc_attr( round( $memory_score ) ); ?>" id="sp-memory-metric">
										<div class="sp-circular-progress">
											<div class="sp-progress-ring">
												<div class="sp-progress-circle"></div>
											</div>
											<div class="sp-score-value" id="sp-memory-score"><?php echo esc_html( round( $memory_score ) ); ?>
											</div>
										</div>
										<div class="sp-metric-label">
											<span class="dashicons dashicons-database"></span>
											<?php echo esc_html__( 'Memory Usage', 'sitepulse' ); ?>
										</div>
										<div class="sp-metric-status">
											<span class="badge <?php echo esc_attr( $mem['percent_class'] ); ?>" id="sp_js_memory">
												<?php echo esc_html( $mem['percent'] ); ?>
											</span>
										</div>
									</div>
								</div>

								<!-- Active Plugins Metric -->
								<div class="col-6 col-md-2">
									<div class="sp-pagespeed-metric" data-score="<?php echo esc_attr( round( $plugins_score ) ); ?>">
										<div class="sp-circular-progress">
											<div class="sp-progress-ring">
												<div class="sp-progress-circle"></div>
											</div>
											<div class="sp-score-value"><?php echo esc_html( round( $plugins_score ) ); ?></div>
										</div>
										<div class="sp-metric-label">
											<span class="dashicons dashicons-admin-plugins"></span>
											<?php echo esc_html__( 'Active Plugins', 'sitepulse' ); ?>
										</div>
										<div class="sp-metric-status">
											<span class="badge bg-primary"><?php echo esc_html( $active_plugins_count ); ?></span>
										</div>
									</div>
								</div>

								<!-- Error Log Metric -->
								<div class="col-6 col-md-2">
									<div class="sp-pagespeed-metric" data-score="<?php echo esc_attr( round( $error_log_score ) ); ?>">
										<div class="sp-circular-progress">
											<div class="sp-progress-ring">
												<div class="sp-progress-circle"></div>
											</div>
											<div class="sp-score-value"><?php echo esc_html( round( $error_log_score ) ); ?></div>
										</div>
										<div class="sp-metric-label">
											<span class="dashicons dashicons-warning"></span>
											<?php echo esc_html__( 'Error Log', 'sitepulse' ); ?>
										</div>
										<div class="sp-metric-status">
											<?php if ( $error_count > 0 ) : ?>
												<span class="badge bg-warning text-dark">
													<?php
													// Display breakdown of fatal errors and warnings
													$error_parts = array();
													if ( $fatal_count > 0 ) {
														$error_parts[] = sprintf(
															/* translators: %d: fatal error count */
															_n( '%d error', '%d errors', $fatal_count, 'sitepulse' ),
															$fatal_count
														);
													}
													if ( $warning_count > 0 ) {
														$error_parts[] = sprintf(
															/* translators: %d: warning count */
															_n( '%d warning', '%d warns', $warning_count, 'sitepulse' ),
															$warning_count
														);
													}
													echo esc_html( implode( ', ', $error_parts ) );
													?>
													<?php if ( $fatal_count > 0 || $warning_count > 0 ) {
														echo esc_html__( ', details in settings.', 'sitepulse' );
													} ?>
												</span>
											<?php else : ?>
												<span class="badge bg-success">
													<?php echo esc_html__( 'No errors in the last 2 days', 'sitepulse' ); ?>
												</span>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row g-3">
				<?php
				// Allow pro version to add widgets to developer view (loaded after Site Insights)
				do_action( 'sitepulse_dashboard_developer_view_top_widgets', $stats, $curl_events, $mem );
				?>
			</div>

			<!-- Widgets Grid - PageSpeed Insights Style -->
			<div class="row g-3">

				<?php 
				// Only show PageSpeed widget if user has enabled external API (PageSpeed & AI Reports)
				$sp_all_settings = get_option( 'sitepulse_settings', array() );
				$sp_external_api_enabled = isset( $sp_all_settings['external_api_enabled'] ) ? (bool) $sp_all_settings['external_api_enabled'] : true;
				if ( $sp_external_api_enabled ) :
				?>
				<?php if ( $has_valid_pagespeed && $ps_performance_score > 0 ) : ?>
					<!-- PageSpeed Insights Widget -->
					<div class="col-12 col-lg-6">
						<div class="card sp-pagespeed-widget">
							<div class="sp-widget-header" data-bs-toggle="collapse" data-bs-target="#sp-pagespeed-collapse"
								aria-expanded="true" aria-controls="sp-pagespeed-collapse">
								<div class="sp-widget-title-section">
									<div class="sp-widget-title">
										<span class="dashicons dashicons-performance"></span>
										<div>
											<strong><?php echo esc_html__( 'PageSpeed Insights', 'sitepulse' ); ?></strong>
											<div class="sp-widget-subtitle">
												<?php echo esc_html__( 'Google PageSpeed performance analysis', 'sitepulse' ); ?>
												(<?php echo esc_html( ucfirst( $ps_strategy ) ); ?>)
											</div>
										</div>
									</div>
									<div class="sp-widget-stats">
										<div class="sp-stat-item">
											<span class="sp-stat-value <?php
											if ( $ps_performance_score >= 90 )
												echo 'text-success';
											elseif ( $ps_performance_score >= 50 )
												echo 'sp-text-orange';
											else
												echo 'text-danger';
											?>"><?php echo esc_html( $ps_performance_score ); ?></span>
											<span class="sp-stat-label"><?php echo esc_html__( 'Performance', 'sitepulse' ); ?></span>
										</div>
									</div>
								</div>
								<button class="sp-toggle-btn" type="button">
									<span class="dashicons dashicons-arrow-down-alt2"></span>
								</button>
							</div>
							<div class="collapse show" id="sp-pagespeed-collapse">
								<div class="sp-widget-content">
									<!-- Lighthouse Scores Grid -->
									<div class="sp-lighthouse-scores">
										<?php
										$scores = $ps_data['scores'];
										$categories = array(
											'performance' => array(
												'title' => __( 'Performance', 'sitepulse' ),
												'icon' => 'dashicons-performance',
											),
											'accessibility' => array(
												'title' => __( 'Accessibility', 'sitepulse' ),
												'icon' => 'dashicons-universal-access',
											),
											'best_practices' => array(
												'title' => __( 'Best Practices', 'sitepulse' ),
												'icon' => 'dashicons-yes-alt',
											),
											'seo' => array(
												'title' => __( 'SEO', 'sitepulse' ),
												'icon' => 'dashicons-search',
											),
										);

										foreach ( $categories as $key => $cat ) :
											// Skip if score doesn't exist or is not valid
											if ( ! isset( $scores[ $key ] ) || ! is_numeric( $scores[ $key ] ) )
												continue;

											$score_value = floatval( $scores[ $key ] );
											// Handle both 0-1 and 0-100 formats
											$score = $score_value <= 1 ? round( $score_value * 100 ) : round( $score_value );

											// Determine color class based on score
											if ( $score >= 90 ) {
												$score_class = 'sp-score-good';
												$score_color = '#38d39f';
											} elseif ( $score >= 50 ) {
												$score_class = 'sp-score-average';
												$score_color = '#ffb86b';
											} else {
												$score_class = 'sp-score-poor';
												$score_color = '#ff4444';
											}
											?>
											<div class="sp-lighthouse-item">
												<div class="sp-pagespeed-metric" data-score="<?php echo esc_attr( $score ); ?>">
													<div class="sp-circular-progress sp-circular-sm">
														<div class="sp-progress-ring">
															<div class="sp-progress-circle"
																style="--progress-color: <?php echo esc_attr( $score_color ); ?>; --progress-angle: <?php echo esc_attr( $score * 3.6 ); ?>deg;">
															</div>
														</div>
														<div class="sp-score-value sp-score-sm <?php echo esc_attr( $score_class ); ?>">
															<?php echo esc_html( $score ); ?>
														</div>
													</div>
													<div class="sp-metric-label sp-metric-label-sm">
														<span class="dashicons <?php echo esc_attr( $cat['icon'] ); ?>"></span>
														<?php echo esc_html( $cat['title'] ); ?>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>

									<?php if ( isset( $ps_data['core_web_vitals'] ) && is_array( $ps_data['core_web_vitals'] ) && ! empty( $ps_data['core_web_vitals'] ) ) : ?>
										<!-- Core Web Vitals Section -->
										<div class="sp-cwv-section">
											<div class="sp-cwv-header">
												<span class="dashicons dashicons-chart-area"></span>
												<strong><?php echo esc_html__( 'Core Web Vitals', 'sitepulse' ); ?></strong>
											</div>
											<div class="sp-cwv-grid">
												<?php
												$cwv = $ps_data['core_web_vitals'];
												// Map abbreviated keys to full names
												$cwv_metrics = array(
													'fcp' => array(
														'label' => 'FCP',
														'full_name' => __( 'First Contentful Paint', 'sitepulse' ),
													),
													'lcp' => array(
														'label' => 'LCP',
														'full_name' => __( 'Largest Contentful Paint', 'sitepulse' ),
													),
													'tbt' => array(
														'label' => 'TBT',
														'full_name' => __( 'Total Blocking Time', 'sitepulse' ),
													),
													'cls' => array(
														'label' => 'CLS',
														'full_name' => __( 'Cumulative Layout Shift', 'sitepulse' ),
													),
													'si' => array(
														'label' => 'SI',
														'full_name' => __( 'Speed Index', 'sitepulse' ),
													),
													'tti' => array(
														'label' => 'TTI',
														'full_name' => __( 'Time to Interactive', 'sitepulse' ),
													),
												);

												foreach ( $cwv_metrics as $key => $metric ) :
													if ( ! isset( $cwv[ $key ] ) || ! is_array( $cwv[ $key ] ) )
														continue;
													$metric_data = $cwv[ $key ];

													// Get score - handle string or numeric
													$metric_score = 0;
													if ( isset( $metric_data['score'] ) && is_numeric( $metric_data['score'] ) ) {
														$metric_score = floatval( $metric_data['score'] );
													}

													// Determine color based on score (0-1)
													if ( $metric_score >= 0.9 ) {
														$metric_class = 'sp-cwv-good';
													} elseif ( $metric_score >= 0.5 ) {
														$metric_class = 'sp-cwv-average';
													} else {
														$metric_class = 'sp-cwv-poor';
													}

													$display_value = isset( $metric_data['display_value'] ) && ! empty( $metric_data['display_value'] ) ? $metric_data['display_value'] : '—';
													?>
													<div class="sp-cwv-item <?php echo esc_attr( $metric_class ); ?>"
														title="<?php echo esc_attr( $metric['full_name'] ); ?>">
														<div class="sp-cwv-label"><?php echo esc_html( $metric['label'] ); ?></div>
														<div class="sp-cwv-value"><?php echo esc_html( $display_value ); ?></div>
														<div class="sp-cwv-indicator"></div>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endif; ?>

									<?php
									// Validate screenshot data
									$has_valid_screenshot = isset( $ps_data['screenshot'] )
										&& is_array( $ps_data['screenshot'] )
										&& isset( $ps_data['screenshot']['data'] )
										&& ! empty( $ps_data['screenshot']['data'] )
										&& is_string( $ps_data['screenshot']['data'] )
										&& strpos( $ps_data['screenshot']['data'], 'data:image/' ) === 0;

									if ( $has_valid_screenshot ) :
										?>
										<!-- Screenshot Preview -->
										<div class="sp-screenshot-section">
											<div class="sp-screenshot-header">
												<span class="dashicons dashicons-format-image"></span>
												<strong><?php echo esc_html__( 'Page Screenshot', 'sitepulse' ); ?></strong>
											</div>
											<div class="sp-screenshot-preview">
												<img src="<?php echo esc_attr( $ps_data['screenshot']['data'] ); ?>"
													alt="<?php echo esc_attr__( 'Page Screenshot', 'sitepulse' ); ?>" class="sp-screenshot-img"
													loading="lazy" />
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<?php else : ?>
					<!-- PageSpeed Insights - Analyzing State -->
					<div class="col-12 col-lg-6">
						<div class="card sp-pagespeed-widget">
							<div class="sp-widget-header">
								<div class="sp-widget-title-section">
									<div class="sp-widget-title">
										<span class="dashicons dashicons-performance"></span>
										<div>
											<strong><?php echo esc_html__( 'PageSpeed Insights', 'sitepulse' ); ?></strong>
											<div class="sp-widget-subtitle">
												<?php echo esc_html__( 'Google PageSpeed performance analysis', 'sitepulse' ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="sp-widget-content">
								<div class="sp-analyzing-state text-center py-4">
									<div class="sp-analyzing-icon mb-3">
										<span class="dashicons dashicons-chart-line" style="font-size: 48px; width: 48px; height: 48px; color: var(--sp-primary, #6366f1);"></span>
									</div>
									<h5 class="mb-2"><?php echo esc_html__( 'Analyzing Your Site', 'sitepulse' ); ?></h5>
									<p class="sp-text-muted mb-0">
										<?php echo esc_html__( 'SitePulse is collecting performance data.', 'sitepulse' ); ?><br>
										<strong><?php echo esc_html__( 'Come back tomorrow for your detailed PageSpeed report!', 'sitepulse' ); ?></strong>
									</p>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<?php endif; // end external_api_enabled check ?>
				<!-- AI Diagnostic Report Widget -->
				<?php if ( $sp_ai_external_api_enabled ) :
					// Use shared variables from top of file
					$has_report = $has_ai_report;
					$is_pending = $is_ai_pending;
					$is_failed = $is_ai_failed;
				?>
				<div class="col-12 col-lg-6">
					<div class="card sp-pagespeed-widget sp-ai-diagnostic-widget" data-status="<?php echo esc_attr( $ai_diagnostic_status ?? 'none' ); ?>">
						<div class="sp-widget-header">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-superhero-alt"></span>
									<div>
										<strong><?php echo esc_html__( 'AI Diagnostic Report', 'sitepulse' ); ?></strong>
										<span class="badge bg-info ms-2" style="font-size: 0.65rem; vertical-align: middle;"><?php echo esc_html__( 'Beta', 'sitepulse' ); ?></span>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'AI-powered analysis and recommendations', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
								<div class="sp-widget-stats">
									<div class="sp-stat-item">
										<?php if ( $has_report && isset( $ai_diagnostic_report['metrics_summary'] ) && is_array( $ai_diagnostic_report['metrics_summary'] ) && isset( $ai_diagnostic_report['metrics_summary']['overall_health_score'] ) && is_scalar( $ai_diagnostic_report['metrics_summary']['overall_health_score'] ) ) : ?>
											<span class="sp-stat-value sp-ai-health-score"><?php echo esc_html( (string) $ai_diagnostic_report['metrics_summary']['overall_health_score'] ); ?></span>
											<span class="sp-stat-label"><?php echo esc_html__( 'Health', 'sitepulse' ); ?></span>
										<?php elseif ( $is_pending ) : ?>
											<span class="sp-stat-value text-warning"><span class="spinner-border spinner-border-sm"></span></span>
											<span class="sp-stat-label"><?php echo $ai_diagnostic_status === 'processing' ? esc_html__( 'Processing', 'sitepulse' ) : esc_html__( 'Pending', 'sitepulse' ); ?></span>
										<?php else : ?>
											<span class="sp-stat-value text-muted">—</span>
											<span class="sp-stat-label"><?php echo esc_html__( 'Status', 'sitepulse' ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
						<div class="sp-widget-content sp-ai-diagnostic-content">
							<?php if ( $is_pending ) : ?>
								<!-- Pending/Processing State -->
								<div class="sp-ai-status-pending text-center py-4">
									<div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
										<span class="visually-hidden"><?php echo esc_html__( 'Loading...', 'sitepulse' ); ?></span>
									</div>
									<?php if ( $ai_diagnostic_status === 'processing' ) : ?>
										<h6><?php echo esc_html__( 'Processing Analysis', 'sitepulse' ); ?></h6>
										<p class="text-muted mb-2">
											<?php echo esc_html__( 'Our AI is analyzing your site data. This may take a few minutes.', 'sitepulse' ); ?>
										</p>
									<?php else : ?>
										<h6><?php echo esc_html__( 'Waiting in Queue', 'sitepulse' ); ?></h6>
										<p class="text-muted mb-2">
											<?php echo esc_html__( 'Your request has been queued. Processing will begin shortly.', 'sitepulse' ); ?>
											<?php echo esc_html__( 'AI can make mistakes, so always double-check recommendations.', 'sitepulse' ); ?>
										</p>
									<?php endif; ?>
									<?php if ( $ai_diagnostic_requested_at ) : ?>
										<small class="text-muted">
											<?php echo esc_html__( 'Requested:', 'sitepulse' ); ?>
											<?php echo esc_html( date( 'g:i A', strtotime( $ai_diagnostic_requested_at ) ) ); ?>
										</small>
									<?php endif; ?>
									<?php if ( $ai_diagnostic_queue_position && $ai_diagnostic_queue_position > 0 ) : ?>
										<div class="mt-2">
											<span class="badge bg-info">
												<?php echo esc_html__( 'Queue Position:', 'sitepulse' ); ?>
												<strong><?php echo esc_html( $ai_diagnostic_queue_position ); ?></strong>
											</span>
										</div>
									<?php endif; ?>
									<p class="text-muted small mt-2">
										<span class="dashicons dashicons-update-alt sp-spinning"></span>
										<?php echo esc_html__( 'Auto-checking every minute...', 'sitepulse' ); ?>
									</p>
								</div>
							<?php elseif ( $is_failed ) : ?>
								<!-- Failed State -->
								<div class="sp-ai-status-failed text-center py-4">
									<div class="text-danger mb-3">
										<span class="dashicons dashicons-warning" style="font-size: 48px; width: 48px; height: 48px;"></span>
									</div>
									<h6><?php echo esc_html__( 'Diagnostic Failed', 'sitepulse' ); ?></h6>
									<p class="text-muted mb-3">
										<?php echo esc_html__( 'The AI diagnostic could not be completed. Please try again.', 'sitepulse' ); ?>
									</p>
									<button type="button" class="btn btn-primary sp-request-ai-diagnostic-btn" data-force="true">
										<span class="dashicons dashicons-update-alt me-1"></span>
										<?php echo esc_html__( 'Retry Diagnostic', 'sitepulse' ); ?>
									</button>
								</div>
							<?php elseif ( $has_report ) : ?>
								<!-- Completed State - Show Results -->
								<?php
								$report_status = isset( $ai_diagnostic_report['status'] ) && is_string( $ai_diagnostic_report['status'] ) ? $ai_diagnostic_report['status'] : 'info';
								$status_class = $report_status === 'success' ? 'success' : ( $report_status === 'warning' ? 'warning' : ( $report_status === 'danger' ? 'danger' : 'info' ) );
								?>
								<div class="sp-ai-status-completed">
									<!-- Summary -->
									<?php if ( isset( $ai_diagnostic_report['summary'] ) && is_scalar( $ai_diagnostic_report['summary'] ) ) : ?>
										<div class="alert alert-<?php echo esc_attr( $status_class ); ?> mb-3">
											<div class="d-flex align-items-start">
												<span class="dashicons dashicons-<?php echo $report_status === 'success' ? 'yes-alt' : ( $report_status === 'warning' ? 'warning' : 'info' ); ?> me-2"></span>
												<div><?php echo esc_html( (string) $ai_diagnostic_report['summary'] ); ?></div>
											</div>
										</div>
									<?php endif; ?>

									<!-- Metrics Summary -->
									<?php if ( isset( $ai_diagnostic_report['metrics_summary'] ) && is_array( $ai_diagnostic_report['metrics_summary'] ) ) : ?>
										<div class="sp-ai-metrics-row mb-3">
											<div class="row g-2">
												<?php if ( isset( $ai_diagnostic_report['metrics_summary']['pagespeed_grade'] ) && is_scalar( $ai_diagnostic_report['metrics_summary']['pagespeed_grade'] ) ) : ?>
													<div class="col-3 text-center">
														<div class="sp-ai-grade-badge badge bg-<?php echo $ai_diagnostic_report['metrics_summary']['pagespeed_grade'] === 'A' ? 'success' : ( $ai_diagnostic_report['metrics_summary']['pagespeed_grade'] === 'B' ? 'info' : 'warning' ); ?> fs-5 px-3 py-2">
															<?php echo esc_html( (string) $ai_diagnostic_report['metrics_summary']['pagespeed_grade'] ); ?>
														</div>
														<small class="d-block text-muted mt-1"><?php echo esc_html__( 'Speed', 'sitepulse' ); ?></small>
													</div>
												<?php endif; ?>
												<?php if ( isset( $ai_diagnostic_report['metrics_summary']['security_grade'] ) && is_scalar( $ai_diagnostic_report['metrics_summary']['security_grade'] ) ) : ?>
													<div class="col-3 text-center">
														<div class="sp-ai-grade-badge badge bg-<?php echo $ai_diagnostic_report['metrics_summary']['security_grade'] === 'A' ? 'success' : ( $ai_diagnostic_report['metrics_summary']['security_grade'] === 'B' ? 'info' : 'warning' ); ?> fs-5 px-3 py-2">
															<?php echo esc_html( (string) $ai_diagnostic_report['metrics_summary']['security_grade'] ); ?>
														</div>
														<small class="d-block text-muted mt-1"><?php echo esc_html__( 'Security', 'sitepulse' ); ?></small>
													</div>
												<?php endif; ?>
												<?php if ( isset( $ai_diagnostic_report['metrics_summary']['error_severity'] ) && is_string( $ai_diagnostic_report['metrics_summary']['error_severity'] ) ) : ?>
													<div class="col-3 text-center">
														<?php 
														$error_sev = $ai_diagnostic_report['metrics_summary']['error_severity'];
														$error_class = $error_sev === 'low' ? 'success' : ( $error_sev === 'medium' ? 'warning' : 'danger' );
														?>
														<div class="sp-ai-grade-badge badge bg-<?php echo esc_attr( $error_class ); ?> fs-6 px-3 py-2">
															<?php echo esc_html( ucfirst( (string) $error_sev ) ); ?>
														</div>
														<small class="d-block text-muted mt-1"><?php echo esc_html__( 'Errors', 'sitepulse' ); ?></small>
													</div>
												<?php endif; ?>
												<?php if ( isset( $ai_diagnostic_report['metrics_summary']['overall_health_score'] ) && is_numeric( $ai_diagnostic_report['metrics_summary']['overall_health_score'] ) ) : ?>
													<div class="col-3 text-center">
														<?php 
														$health = $ai_diagnostic_report['metrics_summary']['overall_health_score'];
														$health_class = $health >= 80 ? 'success' : ( $health >= 60 ? 'warning' : 'danger' );
														?>
														<div class="sp-ai-grade-badge badge bg-<?php echo esc_attr( $health_class ); ?> fs-5 px-3 py-2">
															<?php echo esc_html( (string) $health ); ?>
														</div>
														<small class="d-block text-muted mt-1"><?php echo esc_html__( 'Health', 'sitepulse' ); ?></small>
													</div>
												<?php endif; ?>
											</div>
										</div>
									<?php endif; ?>

									<!-- Insights Accordion -->
									<?php if ( isset( $ai_diagnostic_report['insights'] ) && is_array( $ai_diagnostic_report['insights'] ) ) : ?>
										<div class="accordion accordion-flush mb-3" id="sp-ai-insights-accordion">
											<?php foreach ( $ai_diagnostic_report['insights'] as $key => $insight ) : ?>
												<div class="accordion-item">
													<h2 class="accordion-header">
														<button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#sp-ai-insight-<?php echo esc_attr( $key ); ?>">
															<span class="dashicons dashicons-<?php echo $key === 'performance' ? 'performance' : ( $key === 'security' ? 'shield' : 'warning' ); ?> me-2"></span>
															<?php echo esc_html( ucfirst( $key ) ); ?>
														</button>
													</h2>
													<div id="sp-ai-insight-<?php echo esc_attr( $key ); ?>" class="accordion-collapse collapse" data-bs-parent="#sp-ai-insights-accordion">
														<div class="accordion-body small">
															<?php
																if ( isset( $insight ) && is_scalar( $insight ) ) {
																	echo esc_html( (string) $insight );
																}
															?>
														</div>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>

									<!-- Recommendations -->
									<?php if ( isset( $ai_diagnostic_report['recommendations'] ) && is_array( $ai_diagnostic_report['recommendations'] ) ) : ?>
										<?php 
										$recs = $ai_diagnostic_report['recommendations'];
										$has_recommendations = ( ! empty( $recs['critical'] ) || ! empty( $recs['high'] ) || ! empty( $recs['moderate'] ) || ! empty( $recs['low'] ) );
										?>
										<?php if ( $has_recommendations ) : ?>
											<div class="sp-ai-recommendations">
												<h6 class="mb-2"><span class="dashicons dashicons-lightbulb me-1"></span><?php echo esc_html__( 'Recommendations', 'sitepulse' ); ?></h6>
												<ul class="list-unstyled mb-0 small">
													<?php foreach ( array( 'critical', 'high', 'moderate', 'low' ) as $priority ) : ?>
														<?php if ( ! empty( $recs[ $priority ] ) && is_array( $recs[ $priority ] ) ) : ?>
															<?php foreach ( $recs[ $priority ] as $rec ) : ?>
																<li class="mb-1">
																	<span class="badge bg-<?php echo $priority === 'critical' ? 'danger' : ( $priority === 'high' ? 'warning text-dark' : ( $priority === 'moderate' ? 'info' : 'secondary' ) ); ?> me-1"><?php echo esc_html( ucfirst( $priority ) ); ?></span>
																	<?php if ( isset( $rec ) && is_scalar( $rec ) ) : ?>
																		<?php echo esc_html( (string) $rec ); ?>
																	<?php endif; ?>
																</li>
															<?php endforeach; ?>
														<?php endif; ?>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>
									<?php endif; ?>

									<!-- WordPress Specific Recommendations -->
									<!-- Recommended Plugins -->
									<?php if ( isset( $ai_diagnostic_report['wordpress_specific'] ) && is_array( $ai_diagnostic_report['wordpress_specific'] ) && isset( $ai_diagnostic_report['wordpress_specific']['recommended_plugins'] ) && is_array( $ai_diagnostic_report['wordpress_specific']['recommended_plugins'] ) && ! empty( $ai_diagnostic_report['wordpress_specific']['recommended_plugins'] ) ) : ?>
										<div class="sp-ai-wp-specific mt-3">
										<h6 class="mb-2"><span class="dashicons dashicons-admin-plugins me-1"></span><?php echo esc_html__( 'Recommended Plugins', 'sitepulse' ); ?></h6>
										<ul class="list-unstyled mb-0 small ps-2">
										<?php foreach ( $ai_diagnostic_report['wordpress_specific']['recommended_plugins'] as $plugin ) : ?>
											<?php if ( isset( $plugin ) && is_scalar( $plugin ) ) { ?>
												<li><span class="badge bg-success me-1"><?php echo esc_html__( '+', 'sitepulse' ); ?></span><?php echo esc_html( (string) $plugin ); ?></li>
											<?php } ?>
										<?php endforeach; ?>
									</ul>
									</div>
									<?php endif; ?>
									<!-- Request New Button -->
									<div class="text-end mt-3 pt-2 border-top">
										<?php if ( $ai_diagnostic_requested_at ) : ?>
											<small class="text-muted me-2">
												<?php echo esc_html__( 'AI can make mistakes, so always double-check recommendations.', 'sitepulse' ); ?>
												<?php echo esc_html__( 'Last run:', 'sitepulse' ); ?>
												<?php echo esc_html( date( 'M j, g:i A', strtotime( $ai_diagnostic_requested_at ) ) ); ?>
											</small>
										<?php endif; ?>
										<button type="button" class="btn btn-sm btn-outline-primary sp-request-ai-diagnostic-btn" data-force="true">
											<span class="dashicons dashicons-update-alt me-1"></span>
											<?php echo esc_html__( 'Run New Diagnostic', 'sitepulse' ); ?>
										</button>
									</div>
								</div>
							<?php else : ?>
								<!-- No Report State -->
								<div class="sp-ai-status-none text-center py-4">
									<div class="sp-placeholder-icon mb-3">
										<span class="dashicons dashicons-superhero-alt" style="font-size: 48px; width: 48px; height: 48px; color: var(--sp-accent-1);"></span>
									</div>
									<h6><?php echo esc_html__( 'AI-Powered Site Analysis', 'sitepulse' ); ?></h6>
									<p class="sp-placeholder-text text-muted mb-3">
										<?php echo esc_html__( 'Get intelligent insights about your site\'s performance, security, and optimization opportunities.', 'sitepulse' ); ?>
									</p>
									<button type="button" class="btn btn-primary sp-request-ai-diagnostic-btn" data-force="false">
										<span class="dashicons dashicons-superhero-alt me-1"></span>
										<?php echo esc_html__( 'Request AI Diagnostic', 'sitepulse' ); ?>
									</button>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endif; // end external_api_enabled check ?>

				<div class="col-12 col-lg-6">
					<div class="card sp-pagespeed-widget">
						<div class="sp-widget-header" data-bs-toggle="collapse" data-bs-target="#sp-hooks-collapse"
							aria-expanded="true" aria-controls="sp-hooks-collapse">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-admin-plugins"></span>
									<div>
										<strong><?php echo esc_html__( 'Plugins/Themes Hooks', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'Performance impact from plugins and themes', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
								<div class="sp-widget-stats">
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
						<div class="collapse show" id="sp-hooks-collapse">
							<div class="sp-widget-content">
								<div class="sp-details-list">
									<?php
									// Sort by total time desc by default
									$stats = array_values( $stats );

									usort( $stats, function ( $a, $b ) {
										return $b['total'] <=> $a['total'];
									} );

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

									$count_stats = 0;
									foreach ( $stats as $stat ) :

										if ( $count_stats >= 10 ) {
											break; // Limit to 10 items
										}

										if ( ! empty( $stat ) ) {
											$avg = $stat['calls'] ? ( $stat['total'] / $stat['calls'] ) : 0;
											$time_ms = $stat['total'] * 1000;

											// Determine performance level
											if ( $time_ms > 1000 ) {
												$perf_level = 'poor';
												$perf_color = '#ff4444';
											} elseif ( $time_ms > 500 ) {
												$perf_level = 'needs-improvement';
												$perf_color = '#ffb86b';
											} else {
												$perf_level = 'good';
												$perf_color = '#38d39f';
											}

											$plugin_or_theme = str_replace( '-', ' ', $stat['source'] );
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
													</div>
													<div class="sp-detail-metrics">
														<div class="sp-metric-badge" style="--metric-color: <?php echo esc_attr( $perf_color ); ?>">
															<span class="sp-metric-value"><?php echo esc_html( number_format( $time_ms, 0 ) ); ?></span>
															<span class="sp-metric-unit">ms</span>
														</div>
													</div>
												</div>
												<div class="sp-detail-secondary">
													<div class="sp-detail-stats">
														<span class="sp-stat">
															<span class="sp-stat-label"><?php echo esc_html__( 'Avg', 'sitepulse' ); ?>:</span>
															<span class="sp-stat-value"><?php echo esc_html( number_format( $avg * 1000, 2 ) ); ?>
																ms</span>
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
										$count_stats++;
									endforeach; ?>
								</div>
							</div>
							<div class="sp-widget-footer">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . SITEPULSE_PREFIX . SITEPULSE_PREFIX_SEPARATOR . SITEPULSE_SLUG . '_' . SITEPULSE_PROFILER_SLUG ) ); ?>"
									class="sp-footer-link">
									<?php echo esc_html__( 'View full report', 'sitepulse' ); ?>
									<span class="dashicons dashicons-arrow-right-alt2"></span>
								</a>
							</div>
						</div>
					</div>
				</div>

				<div class="col-12 col-lg-6">
					<div class="card sp-pagespeed-widget">
						<div class="sp-widget-header" data-bs-toggle="collapse" data-bs-target="#sp-curl-collapse"
							aria-expanded="true" aria-controls="sp-curl-collapse">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-networking"></span>
									<div>
										<strong><?php echo esc_html__( 'API / cURL Requests', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'Remote connections and external requests', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
								<div class="sp-widget-stats">
									<div class="sp-stat-item">
										<span class="sp-stat-value"><?php echo esc_html( count( $curl_events ) ); ?></span>
										<span class="sp-stat-label"><?php echo esc_html__( 'Events', 'sitepulse' ); ?></span>
									</div>
									<div class="sp-stat-item">
										<span
											class="sp-stat-value"><?php echo esc_html( defined( 'WPSLOWHTTP_THRESHOLD' ) ? (float) WPSLOWHTTP_THRESHOLD : SITEPULSE_CURL_API_DEFAULT_THRESHOLD ); ?>s</span>
										<span class="sp-stat-label"><?php echo esc_html__( 'Threshold', 'sitepulse' ); ?></span>
									</div>
								</div>
							</div>
							<button class="sp-toggle-btn" type="button">
								<span class="dashicons dashicons-arrow-down-alt2"></span>
							</button>
						</div>
						<div class="collapse show" id="sp-curl-collapse">
							<div class="sp-widget-content">
								<div class="sp-details-list">
									<?php
									if ( empty( $curl_events ) ) {
										?>
										<div class="sp-detail-item sp-empty-state">
											<div class="sp-detail-content">
												<span class="dashicons dashicons-info"></span>
												<span><?php echo esc_html__( 'No API requests detected. Please enable the monitor tracker to start collecting data.', 'sitepulse' ); ?></span>
											</div>
										</div>
										<?php
									}

									$sitepulse_curl_count = 0;
									foreach ( $curl_events as $event ) :
										if ( $sitepulse_curl_count > 5 )
											break;

										$time_ms = $event['elapsed'] * 1000;
										if ( $time_ms > 1000 ) {
											$perf_level = 'poor';
											$perf_color = '#ff4444';
										} elseif ( $time_ms > 500 ) {
											$perf_level = 'needs-improvement';
											$perf_color = '#ffb86b';
										} else {
											$perf_level = 'good';
											$perf_color = '#38d39f';
										}
										?>
										<div class="sp-detail-item">
											<div class="sp-detail-main">
												<div class="sp-detail-info">
													<div class="sp-detail-title"><?php echo esc_html( $event['origin'] ); ?></div>
													<div class="sp-detail-meta">
														<span
															class="sp-detail-url mono"><?php echo esc_html( $curLoader::truncate( $event['url'], 80 ) ); ?></span>
														<span class="sp-detail-separator">•</span>
														<span class="sp-detail-file"><?php echo esc_html( $event['file'] ); ?></span>
													</div>
												</div>
												<div class="sp-detail-metrics">
													<div class="sp-metric-badge" style="--metric-color: <?php echo esc_attr( $perf_color ); ?>">
														<span class="sp-metric-value"><?php echo esc_html( number_format( $time_ms, 0 ) ); ?></span>
														<span class="sp-metric-unit">ms</span>
													</div>
												</div>
											</div>
											<div class="sp-detail-secondary">
												<div class="sp-detail-stats">
													<span class="sp-stat">
														<span class="sp-stat-label"><?php echo esc_html__( 'Status', 'sitepulse' ); ?>:</span>
														<span
															class="sp-stat-value sp-http-status"><?php echo esc_html( $event['code'] ?: 'ERR' ); ?></span>
													</span>
												</div>
											</div>
										</div>
										<?php $sitepulse_curl_count++; endforeach; ?>
								</div>
							</div>
							<div class="sp-widget-footer">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . SITEPULSE_PREFIX . SITEPULSE_PREFIX_SEPARATOR . SITEPULSE_SLUG . '_' . SITEPULSE_CURL_API_SLUG ) ); ?>"
									class="sp-footer-link">
									<?php echo esc_html__( 'View full report', 'sitepulse' ); ?>
									<span class="dashicons dashicons-arrow-right-alt2"></span>
								</a>
							</div>
						</div>
					</div>
				</div>

				<!-- Security & Vulnerabilities Widget -->
				<div class="col-12 col-lg-6">
					<div class="card sp-pagespeed-widget">
						<div class="sp-widget-header">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-shield"></span>
									<div>
										<strong><?php echo esc_html__( 'Security & Vulnerabilities', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'Scan your site for known security vulnerabilities', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="sp-widget-content">
							<div class="d-flex justify-content-between align-items-center mb-3">
								<div class="text-muted small sp-last-check-time">
									<?php if ( ! empty( $last_vulnerability_check ) ) : ?>
										<?php echo esc_html__( 'Last checked:', 'sitepulse' ); ?>
										<span
											class="fw-bold"><?php echo esc_html( date( 'g:i A', strtotime( $last_vulnerability_check ) ) ); ?></span>
									<?php else : ?>
										<?php echo esc_html__( 'Not checked yet', 'sitepulse' ); ?>
									<?php endif; ?>
								</div>
							</div>

							<div class="sp-vulnerabilities-results">
								<?php if ( ! empty( $vulnerabilities ) ) : ?>
									<div class="alert alert-danger mb-0">
										<h6 class="alert-heading">
											<span class="dashicons dashicons-warning"></span>
											<?php echo esc_html__( 'Vulnerabilities Found', 'sitepulse' ); ?>
										</h6>
										<ul class="mb-0 ps-3">
											<?php foreach ( $vulnerabilities as $vuln ) : ?>
												<li>
													<?php echo esc_html( $vuln['name'] ); ?>
													<?php if ( isset( $vuln['version'] ) ) : ?>
														<span class="badge bg-danger ms-1"><?php echo esc_html( $vuln['version'] ); ?></span>
													<?php endif; ?>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php elseif ( ! empty( $last_vulnerability_check ) ) : ?>
									<div class="alert alert-success mb-0">
										<span class="dashicons dashicons-yes-alt me-1"></span>
										<?php echo esc_html__( 'No vulnerabilities found.', 'sitepulse' ); ?>
									</div>
								<?php else : ?>
									<div class="alert alert-info mb-0">
										<span class="dashicons dashicons-info me-1"></span>
										<?php echo esc_html__( 'Run a scan to check for vulnerabilities.', 'sitepulse' ); ?>
									</div>
								<?php endif; ?>
							</div>
							<div class="mt-3 text-end">
								<small class="text-muted fst-italic">
									<?php echo sprintf(
										/* translators: %s: Pro version link */
										esc_html__( 'Database powered by Wordfence and daily automatic checking & alerts with the %s', 'sitepulse' ),
										'<a href="https://sitepulse.me" target="_blank">' . esc_html__( 'Pro version', 'sitepulse' ) . '</a>'
									); ?>
								</small>
								<button type="button" class="btn btn-primary sp-check-vulnerabilities-btn">
									<span class="dashicons dashicons-shield-alt me-1"></span>
									<?php echo esc_html__( 'Check Vulnerabilities', 'sitepulse' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<?php if ( isset( $disk_write ) && is_array( $disk_write ) ) :
					// Calculate disk performance score
					$sitepulse_disk_score = 100;
					if ( $disk_write['avg_ms'] > 100 ) {
						$sitepulse_disk_score = 50;
					} elseif ( $disk_write['avg_ms'] > 50 ) {
						$sitepulse_disk_score = 75;
					}
					?>
					<div class="col-12 col-lg-6">
						<div class="card sp-pagespeed-widget">
							<div class="sp-widget-header" data-bs-toggle="collapse" data-bs-target="#sp-disk-collapse"
								aria-expanded="true" aria-controls="sp-disk-collapse">
								<div class="sp-widget-title-section">
									<div class="sp-widget-title">
										<span class="dashicons dashicons-media-default"></span>
										<div>
											<strong><?php echo esc_html__( 'Disk Write Test', 'sitepulse' ); ?></strong>
											<div class="sp-widget-subtitle">
												<?php echo esc_html__( 'Performance of writing to disk (log directory)', 'sitepulse' ); ?>
											</div>
										</div>
									</div>
									<div class="sp-widget-stats">
										<div class="sp-stat-item">
											<span
												class="sp-stat-value"><?php echo esc_html( number_format( $disk_write['avg_ms'], 1 ) ); ?></span>
											<span class="sp-stat-label"><?php echo esc_html__( 'Avg (ms)', 'sitepulse' ); ?></span>
										</div>
										<div class="sp-stat-item">
											<span class="sp-stat-value <?php echo $disk_write['success'] ? 'text-success' : 'text-danger'; ?>">
												<?php echo $disk_write['success'] ? esc_html__( 'OK', 'sitepulse' ) : esc_html__( 'FAIL', 'sitepulse' ); ?>
											</span>
											<span class="sp-stat-label"><?php echo esc_html__( 'Status', 'sitepulse' ); ?></span>
										</div>
									</div>
								</div>
								<button class="sp-toggle-btn" type="button">
									<span class="dashicons dashicons-arrow-down-alt2"></span>
								</button>
							</div>
							<div class="collapse show" id="sp-disk-collapse">
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
														<span><?php echo esc_html__( 'Iterations', 'sitepulse' ); ?>:
															<?php echo esc_html( $disk_write['iterations'] ); ?></span>
														<span class="sp-detail-separator">•</span>
														<span><?php echo esc_html__( 'File Size', 'sitepulse' ); ?>:
															<?php echo esc_html( number_format( $disk_write['size_bytes'] / 1024, 2 ) . ' KB' ); ?></span>
													</div>
												</div>
											</div>
										</div>
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
														<span
															class="sp-stat-value"><?php echo esc_html( number_format( $disk_write['avg_ms'], 2 ) ); ?>
															ms</span>
													</span>
													<span class="sp-stat">
														<span class="sp-stat-label"><?php echo esc_html__( 'Min', 'sitepulse' ); ?>:</span>
														<span
															class="sp-stat-value"><?php echo esc_html( number_format( $disk_write['min_ms'], 2 ) ); ?>
															ms</span>
													</span>
													<span class="sp-stat">
														<span class="sp-stat-label"><?php echo esc_html__( 'Max', 'sitepulse' ); ?>:</span>
														<span
															class="sp-stat-value"><?php echo esc_html( number_format( $disk_write['max_ms'], 2 ) ); ?>
															ms</span>
													</span>
													<span class="sp-stat">
														<span class="sp-stat-label"><?php echo esc_html__( 'Total Written', 'sitepulse' ); ?>:</span>
														<span
															class="sp-stat-value"><?php echo esc_html( number_format( $disk_write['total_written_bytes'] / 1024, 2 ) ); ?>
															KB</span>
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

				<!-- Plugin Profiler Widget -->
				<?php
					// Calculate total stats
					$total_plugins = count( $plugin_profiler_stats );
					$total_memory = array_sum( array_column( $plugin_profiler_stats, 'avg_memory' ) );
					// Use actual measured total load time instead of summing averages
					$total_time = class_exists( 'Sitepulse_Plugin_Profiler' ) ? Sitepulse_Plugin_Profiler::get_total_load_time() : 0;
				?>
				<div class="col-12 col-lg-6">
					<div class="card sp-pagespeed-widget">
						<div class="sp-widget-header" data-bs-toggle="collapse" data-bs-target="#sp-plugin-profiler-collapse"
							aria-expanded="true" aria-controls="sp-plugin-profiler-collapse">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-admin-plugins"></span>
									<div>
										<strong><?php echo esc_html__( 'Top Plugins Load', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'RAM and loading time per plugin', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
								<div class="sp-widget-stats">
									<div class="sp-stat-item">
										<span class="sp-stat-value"><?php echo esc_html( $total_plugins ); ?></span>
										<span class="sp-stat-label"><?php echo esc_html__( 'Plugins', 'sitepulse' ); ?></span>
									</div>
									<div class="sp-stat-item">
										<!-- Deprecated -->
										<!-- <span class="sp-stat-value"><?php // echo esc_html( class_exists( 'Sitepulse_Plugin_Profiler' ) ? Sitepulse_Plugin_Profiler::format_time( $total_time ) : number_format( $total_time, 2 ) . ' ms' ); ?></span> -->
										<!-- <span class="sp-stat-label"><?php // echo esc_html__( 'Total Time', 'sitepulse' ); ?></span> -->
									</div>
								</div>
								<button class="btn btn-sm btn-outline-primary sp-collect-plugin-data me-2" type="button"
									title="<?php echo esc_attr__( 'Manually collect fresh plugin performance data', 'sitepulse' ); ?>">
									<span class="dashicons dashicons-update-alt"></span>
									<?php echo esc_html__( 'Collect Data', 'sitepulse' ); ?>
								</button>
							</div>
							<button class="sp-toggle-btn" type="button">
								<span class="dashicons dashicons-arrow-down-alt2"></span>
							</button>
						</div>
						<div class="collapse show" id="sp-plugin-profiler-collapse">
							<div class="sp-widget-content">
								<div class="sp-details-list">
									<?php
									$plugin_count = 0;
									foreach ( $plugin_profiler_stats as $plugin_stat ) :
										if ( $plugin_count >= 10 ) {
											break; // Limit to 10 items
										}

										// Determine performance level based on time
										$time_ms = $plugin_stat['avg_ms'];
										if ( $time_ms > 1000 ) {
											$perf_level = 'poor';
											$perf_color = '#ff4444';
										} elseif ( $time_ms > 500 ) {
											$perf_level = 'needs-improvement';
											$perf_color = '#ffb86b';
										} else {
											$perf_level = 'good';
											$perf_color = '#38d39f';
										}
										?>
										<div class="sp-detail-item">
											<div class="sp-detail-main">
												<div class="sp-detail-info">
													<div class="sp-detail-title"><?php echo esc_html( $plugin_stat['plugin_name'] ); ?></div>
													<div class="sp-detail-meta">
														<span
															class="sp-detail-file mono"><?php echo esc_html( $plugin_stat['plugin_slug'] ); ?></span>
														<span class="sp-detail-separator">•</span>
														<span
															class="sp-detail-file"><?php echo esc_html__( 'v', 'sitepulse' ); ?><?php echo esc_html( $plugin_stat['plugin_version'] ); ?></span>
													</div>
												</div>
												<div class="sp-detail-metrics">
													<div class="sp-metric-badge" style="--metric-color: <?php echo esc_attr( $perf_color ); ?>">
														<span
															class="sp-metric-value"><?php echo esc_html( class_exists( 'Sitepulse_Plugin_Profiler' ) ? Sitepulse_Plugin_Profiler::format_time( $time_ms ) : number_format( $time_ms, 2 ) . ' ms' ); ?></span>
													</div>
												</div>
											</div>
										</div>
										<?php
										$plugin_count++;
									endforeach;
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
			// Skip Slow Query Test widget when pro is active (pro has SQL Monitor)
			if ( ! SITEPULSE_PRO_IS_ACTIVE ) :
			?>
				<div class="col-12 col-lg-6">
					<div class="card sp-pagespeed-widget">
						<div class="sp-widget-header" data-bs-toggle="collapse" data-bs-target="#sp-queries-collapse"
							aria-expanded="true" aria-controls="sp-queries-collapse">
							<div class="sp-widget-title-section">
								<div class="sp-widget-title">
									<span class="dashicons dashicons-database"></span>
									<div>
										<strong><?php echo esc_html__( 'Slow Query Test', 'sitepulse' ); ?></strong>
										<div class="sp-widget-subtitle">
											<?php echo esc_html__( 'MySQL queries & autoloaded options', 'sitepulse' ); ?>
										</div>
									</div>
								</div>
								<div class="sp-widget-stats">
									<div class="sp-stat-item">
										<span
											class="sp-stat-value"><?php echo esc_html( isset( $slow_queries['results'] ) ? count( $slow_queries['results'] ) : 0 ); ?></span>
										<span class="sp-stat-label"><?php echo esc_html__( 'Queries', 'sitepulse' ); ?></span>
									</div>
									<div class="sp-stat-item">
										<span
											class="sp-stat-value <?php echo ( isset( $slow_queries['success'] ) && $slow_queries['success'] ) ? 'text-success' : 'text-danger'; ?>">
											<?php echo ( isset( $slow_queries['success'] ) && $slow_queries['success'] ) ? esc_html__( 'ON', 'sitepulse' ) : esc_html__( 'OFF', 'sitepulse' ); ?>
										</span>
										<span class="sp-stat-label"><?php echo esc_html__( 'Status', 'sitepulse' ); ?></span>
									</div>
								</div>
							</div>
							<button class="sp-toggle-btn" type="button">
								<span class="dashicons dashicons-arrow-down-alt2"></span>
							</button>
						</div>
						<div class="collapse show" id="sp-queries-collapse">
							<!-- Section Toggle Segmented Control -->
							<div class="px-3 pt-2">
								<div class="d-inline-flex p-1 bg-black-20 rounded-2" style="background: rgba(0,0,0,0.2); border: 1px solid var(--sp-border-color, rgba(255,255,255,.08));">
									<button type="button" class="btn btn-sm sp-section-toggle active" data-section="sp-slow-queries-section" 
										style="font-size: 11px; padding: 4px 12px; border: 0; border-radius: 4px; transition: all 0.2s;">
										<?php echo esc_html__( 'Slow Queries', 'sitepulse' ); ?>
									</button>
									<button type="button" class="btn btn-sm sp-section-toggle" data-section="sp-autoload-section" 
										style="font-size: 11px; padding: 4px 12px; border: 0; border-radius: 4px; transition: all 0.2s; color: var(--sp-text-muted, #999);">
										<?php echo esc_html__( 'Autoload Options', 'sitepulse' ); ?>
									</button>
								</div>
							</div>

							<!-- Slow Queries Section -->
							<div id="sp-slow-queries-section">
								<div class="sp-widget-content">
								<?php if ( ! empty( $slow_queries['success'] ) && ! $slow_queries['success'] ) : ?>
									<div class="sp-details-list">
										<div class="sp-detail-item sp-empty-state">
											<div class="sp-detail-content">
												<span class="dashicons dashicons-warning"></span>
												<div>
													<strong><?php echo esc_html__( 'Error:', 'sitepulse' ); ?></strong>
													<p><?php echo esc_html( $slow_queries['error'] ); ?></p>
													<p><?php echo esc_html( $slow_queries['message'] ); ?></p>
												</div>
											</div>
										</div>
									</div>
								<?php elseif ( ! empty( $slow_queries['results'] ) && count( $slow_queries['results'] ) ) : ?>
									<div class="sp-details-list">
										<?php foreach ( $slow_queries['results'] as $query ) :
											$perf_color = $query['time_ms'] > 1000 ? '#ff4444' : ( $query['time_ms'] > 500 ? '#ffb86b' : '#38d39f' );
											?>
											<div class="sp-detail-item">
												<div class="sp-detail-main">
													<div class="sp-detail-info">
														<div class="sp-detail-title mono"><?php echo esc_html( $query['sql'] ); ?></div>
														<div class="sp-detail-meta">
															<span class="sp-detail-file"><?php echo esc_html( $query['caller'] ); ?></span>
														</div>
													</div>
													<div class="sp-detail-metrics">
														<div class="sp-metric-badge" style="--metric-color: <?php echo esc_attr( $perf_color ); ?>">
															<span
																class="sp-metric-value"><?php echo esc_html( number_format( $query['time_ms'], 0 ) ); ?></span>
															<span class="sp-metric-unit">ms</span>
														</div>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								<?php else : ?>
									<div class="sp-details-list">
										<div class="sp-detail-item sp-empty-state">
											<div class="sp-detail-content">
												<span class="dashicons dashicons-yes-alt"></span>
												<span><?php echo esc_html__( 'No slow queries detected.', 'sitepulse' ); ?></span>
											</div>
										</div>
										<?php if ( isset( $slow_queries['error'] ) && $slow_queries['error'] == 'savequeries_disabled' ) : ?>
											<div class="sp-detail-item sp-empty-state">
												<div class="sp-detail-content">
													<span class="dashicons dashicons-warning"></span>
													<div>
														<p><?php echo esc_html( $slow_queries['message'] ); ?></p>
														<a href="javascript:;" class="sp-action-btn fix_enable_savequeries">
															<span class="dashicons dashicons-admin-tools"></span>
															<?php echo esc_html__( 'Fix Issue', 'sitepulse' ); ?>
														</a>
													</div>
												</div>
											</div>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
								</div>
								<!-- Autoload Options Section -->
								<div id="sp-autoload-section" style="display: none;">
									<div class="sp-widget-content">
										<!-- Loading state -->
										<div id="sp-autoload-loading" class="text-center py-4">
											<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
											<?php echo esc_html__( 'Loading autoloaded options...', 'sitepulse' ); ?>
										</div>
										<!-- Error state -->
										<div id="sp-autoload-error" class="d-none px-3 py-3">
											<div class="alert alert-danger mb-0 small"></div>
										</div>
										<!-- Results -->
										<div id="sp-autoload-results" class="d-none" style="display: block !important;">
											<div class="d-flex justify-content-between align-items-center px-3 py-2" style="border-bottom: 1px solid var(--sp-border-color, rgba(255,255,255,.08)); width: 100%;">
												<small class="text-muted" id="sp-autoload-summary" style="font-size: 11px;"></small>
												<button type="button" class="btn btn-sm btn-outline-secondary" id="sp-autoload-refresh" style="padding: 2px 8px;" title="<?php echo esc_attr__( 'Refresh', 'sitepulse' ); ?>">
													<span class="dashicons dashicons-update-alt" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
												</button>
											</div>
											<div class="sp-details-list" id="sp-autoload-list"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>

			</div>
			<div class="row g-3 mt-3">
				<?php
				// Allow pro version to add widgets to bottom of developer view
				do_action( 'sitepulse_dashboard_developer_view_bottom_widgets', $stats, $curl_events, $mem );
				?>
			</div>
		</div>
		<!-- End Developer View -->
	</div>

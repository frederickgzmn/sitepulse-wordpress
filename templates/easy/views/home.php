<?php
/**
 * Easy Mode — Home (Dashboard) view.
 *
 * Organized into functional zones:
 *  1. Health Overview   — score rings + metric cards
 *  2. AI Intelligence   — AI diagnostic (flagship)
 *  3. Site Health    — server checks + web vitals
 *  4. Database & Monitoring (Pro) — SQL/autoload + performance trends + alerts
 *  5. Tools & Quick Access — troubleshoot + perf warnings + quick links
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Dashboard Header -->
<div class="sp-page-header sp-dashboard-header">
	<div class="sp-dashboard-header-left">
		<h2 class="sp-page-title"><?php echo esc_html__( 'Dashboard', 'sitepulse' ); ?></h2>
		<p class="sp-page-subtitle">
			<?php echo esc_html__( 'Your site\'s health at a glance — everything you need to know', 'sitepulse' ); ?>
		</p>
	</div>
	<div class="sp-dashboard-header-right">
		<span class="sp-stat-pill sp-live-pill">
			<span class="sp-pulse-dot sp-pulse-dot-success"></span>
			<?php echo esc_html__( 'Updated', 'sitepulse' ); ?>: <?php echo esc_html( current_time( 'g:i A' ) ); ?>
		</span>
		<?php
		$sitepulse_lowhttp_enabled  = isset( $sitepulse_lowhttp_enabled ) ? $sitepulse_lowhttp_enabled : get_option( 'sitepulse_curl_api_enabled', false );
		$sitepulse_profiler_enabled = isset( $sitepulse_profiler_enabled ) ? $sitepulse_profiler_enabled : get_option( 'sitepulse_profiler_enabled', null );
		?>
		<div class="sp-tracker-toggles">
			<label class="sp-tracker-toggle-label">
				<span class="sp-toggle">
					<input type="checkbox" id="sp-easy-profiler-toggle" class="sp_profiler" <?php checked( $sitepulse_profiler_enabled, '1' ); ?>>
					<span class="sp-toggle-track"></span>
				</span>
				<span class="sp-text-xs sp-fw-500"><?php echo esc_html__( 'Activity Monitor', 'sitepulse' ); ?></span>
			</label>
			<label class="sp-tracker-toggle-label">
				<span class="sp-toggle">
					<input type="checkbox" id="sp-easy-http-toggle" class="sp_http_load" <?php checked( $sitepulse_lowhttp_enabled, '1' ); ?>>
					<span class="sp-toggle-track"></span>
				</span>
				<span class="sp-text-xs sp-fw-500"><?php echo esc_html__( 'Outgoing Connections', 'sitepulse' ); ?></span>
			</label>
		</div>
	</div>
</div>

<?php
$sp_first_insight_title       = '';
$sp_first_insight_description = '';
$sp_first_insight_url         = '';
$sp_first_insight_cta         = '';
$sp_first_insight_tone        = 'info';
$sp_first_insight_icon        = 'chart-line';

if ( ! empty( $all_slow_items ) ) {
	$sp_first_slow_item          = reset( $all_slow_items );
	$sp_first_insight_title       = sprintf(
		/* translators: %s: plugin, theme, or hook name */
		__( 'Start here: %s is your slowest item', 'sitepulse' ),
		$sp_first_slow_item['name']
	);
	$sp_first_insight_description = sprintf(
		/* translators: %s: execution time in milliseconds */
		__( 'It took about %s ms in the latest sample. Review its timing before changing or disabling anything.', 'sitepulse' ),
		number_format_i18n( $sp_first_slow_item['time_ms'], 0 )
	);
	$sp_first_insight_url  = admin_url( 'admin.php?page=wpsp_sitepulse&sp_view=performance' );
	$sp_first_insight_cta  = __( 'Review slow items', 'sitepulse' );
	$sp_first_insight_tone = 'warning';
	$sp_first_insight_icon = 'performance';
} elseif ( ! empty( $slow_api_requests ) ) {
	$sp_first_slow_request = reset( $slow_api_requests );
	$sp_request_name       = ! empty( $sp_first_slow_request['origin'] ) && 'Unknown' !== $sp_first_slow_request['origin']
		? $sp_first_slow_request['origin']
		: wp_parse_url( $sp_first_slow_request['url'], PHP_URL_HOST );
	$sp_request_name       = $sp_request_name ? $sp_request_name : __( 'An external service', 'sitepulse' );

	$sp_first_insight_title       = sprintf(
		/* translators: %s: external service name */
		__( 'Start here: %s responded slowly', 'sitepulse' ),
		$sp_request_name
	);
	$sp_first_insight_description = sprintf(
		/* translators: %s: response time in milliseconds */
		__( 'The request took about %s ms. Check whether it is delaying pages that depend on it.', 'sitepulse' ),
		number_format_i18n( $sp_first_slow_request['time_ms'], 0 )
	);
	$sp_first_insight_url  = admin_url( 'admin.php?page=wpsp_sitepulse&sp_view=api-monitor' );
	$sp_first_insight_cta  = __( 'Review external requests', 'sitepulse' );
	$sp_first_insight_tone = 'warning';
	$sp_first_insight_icon = 'rest-api';
} elseif ( ! empty( $error_count ) ) {
	$sp_first_insight_title       = sprintf(
		/* translators: %d: number of detected errors */
		_n( 'SitePulse found %d recent error', 'SitePulse found %d recent errors', $error_count, 'sitepulse' ),
		$error_count
	);
	$sp_first_insight_description = __( 'Review the error details to identify recurring plugin, theme, or server problems.', 'sitepulse' );
	$sp_first_insight_url         = admin_url( 'admin.php?page=wpsp_sitepulse&sp_view=security' );
	$sp_first_insight_cta         = __( 'Review errors', 'sitepulse' );
	$sp_first_insight_tone        = 'danger';
	$sp_first_insight_icon        = 'warning';
} elseif ( ! empty( $stats ) || ! empty( $curl_events ) || ! empty( $plugin_profiler_stats ) ) {
	$sp_first_insight_title       = __( 'No major bottleneck found in the latest sample', 'sitepulse' );
	$sp_first_insight_description = __( 'SitePulse is monitoring locally. Keep it active and check again after normal traffic or a slow page visit.', 'sitepulse' );
	$sp_first_insight_url         = admin_url( 'admin.php?page=wpsp_sitepulse&sp_view=performance' );
	$sp_first_insight_cta         = __( 'See performance details', 'sitepulse' );
	$sp_first_insight_tone        = 'success';
	$sp_first_insight_icon        = 'yes-alt';
} else {
	$sp_first_insight_title       = __( 'Collecting your first useful measurements', 'sitepulse' );
	$sp_first_insight_description = __( 'Visit a few public pages while logged in, then refresh this dashboard to reveal slow plugins and external requests.', 'sitepulse' );
	$sp_first_insight_url         = admin_url( 'admin.php?page=wpsp_sitepulse' );
	$sp_first_insight_cta         = __( 'Refresh dashboard', 'sitepulse' );
}
?>

<section class="sp-first-insight sp-first-insight--<?php echo esc_attr( $sp_first_insight_tone ); ?> sp-mb-24" aria-labelledby="sp-first-insight-title">
	<div class="sp-first-insight-icon">
		<span class="dashicons dashicons-<?php echo esc_attr( $sp_first_insight_icon ); ?>"></span>
	</div>
	<div class="sp-first-insight-copy">
		<span class="sp-first-insight-kicker"><?php echo esc_html__( 'Recommended next step', 'sitepulse' ); ?></span>
		<h3 id="sp-first-insight-title"><?php echo esc_html( $sp_first_insight_title ); ?></h3>
		<p><?php echo esc_html( $sp_first_insight_description ); ?></p>
	</div>
	<a href="<?php echo esc_url( $sp_first_insight_url ); ?>" class="sp-btn sp-btn-primary sp-btn-sm">
		<?php echo esc_html( $sp_first_insight_cta ); ?>
		<span aria-hidden="true">→</span>
	</a>
</section>

<div class="sp-dashboard-layout">
<div class="sp-hero-grid sp-mb-24">

<!-- Score Rings Hero -->
<div class="sp-glass-card sp-p-32 sp-flex sp-items-center sp-justify-center" style="position:relative; overflow:hidden;">
	<div class="sp-hero-scores sp-flex sp-items-center" style="gap:40px; z-index:1; position:relative;">
		<?php
		$ring_score = isset( $overall_score ) ? (int) $overall_score : 0;
		$ring_label = __( 'Site Health', 'sitepulse' );
		$ring_size  = 'lg';
		require __DIR__ . '/../partials/score-ring.php';
        
		$hero_status = $ring_score >= 80 ? 'Optimal' : ($ring_score >= 60 ? 'Needs Attention' : 'Critical');
        $hero_color = $ring_score >= 80 ? 'var(--sp-success)' : ($ring_score >= 60 ? 'var(--sp-warning)' : 'var(--sp-danger)');
		?>
		<div class="sp-hero-text">
            <h3 style="font-size: 28px; font-weight: 700; margin: 0; color: var(--sp-text-primary); letter-spacing: -0.02em;">
                <?php echo esc_html(sprintf(__('Site is %s', 'sitepulse'), $hero_status)); ?>
            </h3>
            <p style="color: var(--sp-text-secondary); font-size: 14px; margin-top: 8px; line-height: 1.6; max-width: 280px; margin-bottom:0;">
                <?php echo esc_html__('We continuously check your server, database, and website speed so you don\'t have to.', 'sitepulse'); ?>
            </p>
            <div class="sp-flex sp-gap-12 sp-mt-20 sp-items-center">
                <span class="sp-badge" style="background:var(--sp-bg-input); border: 1px solid <?php echo $hero_color; ?>; color: <?php echo $hero_color; ?>; padding: 6px 14px; font-size: 13px; font-weight:600;">
                    <?php echo esc_html($hero_status); ?>
                </span>
                <span class="sp-text-sm sp-text-muted sp-flex sp-items-center" style="gap:6px; font-weight:500;">
                    <span class="dashicons dashicons-shield" style="font-size: 16px; width: 16px; height: 16px; color:var(--sp-success)"></span>
                    <?php echo esc_html__('Protected', 'sitepulse'); ?>
                </span>
            </div>
        </div>
	</div>
</div>
<!-- End Score Rings -->

	<!-- AI Intelligence (Now in Hero Grid) -->
	<?php if ( ! empty( $sp_ai_external_api_enabled ) ) : ?>
<div class="sp-glass-card sp-card--ai sp-ai-diagnostic-widget" data-status="<?php echo esc_attr( $ai_diagnostic_status ); ?>" style="max-height:100%; display:flex; flex-direction:column; overflow:hidden;">
	<div class="sp-card-header">
		<div>
			<h3 class="sp-card-title">
				<span class="dashicons dashicons-superhero-alt"></span>
				<?php echo esc_html__( 'AI Diagnostic', 'sitepulse' ); ?>
				<span class="sp-badge sp-badge-info"><?php echo esc_html__( 'Beta', 'sitepulse' ); ?></span>
			</h3>
			<p class="sp-card-subtitle"><?php echo esc_html__( 'Smart recommendations powered by AI', 'sitepulse' ); ?></p>
		</div>
		<?php if ( ! empty( $has_ai_report ) && isset( $ai_diagnostic_report['metrics_summary']['overall_health_score'] ) ) : ?>
			<span class="sp-badge sp-badge-solid-accent" style="font-size:16px;padding:6px 14px;">
				<?php echo esc_html( $ai_diagnostic_report['metrics_summary']['overall_health_score'] ); ?>
			</span>
		<?php endif; ?>
	</div>
	<div class="sp-card-body sp-p-0" style="overflow-y:auto; max-height:400px;">
		<div class="sp-chat-container">
			<div class="sp-chat-message sp-chat-user">
				<div class="sp-chat-avatar"><span class="dashicons dashicons-admin-users"></span></div>
				<div class="sp-chat-bubble">
					<p class="sp-mb-0"><?php echo esc_html__( 'Please check my site for any speed, security, or setup issues and tell me what I can improve.', 'sitepulse' ); ?></p>
				</div>
			</div>

			<div class="sp-chat-message sp-chat-ai">
				<div class="sp-chat-avatar"><span class="dashicons dashicons-superhero-alt"></span></div>
				<div class="sp-chat-bubble sp-ai-diagnostic-content">
					<?php if ( ! empty( $is_ai_pending ) ) : ?>
						<div class="sp-flex sp-items-center sp-gap-8">
							<div class="sp-typing-indicator"><div class="sp-typing-dot"></div><div class="sp-typing-dot"></div><div class="sp-typing-dot"></div></div>
							<span class="sp-text-sm sp-fw-500">
								<?php echo $ai_diagnostic_status === 'processing'
									? esc_html__( 'Analyzing your site data...', 'sitepulse' )
									: esc_html__( 'Waiting in queue...', 'sitepulse' ); ?>
							</span>
						</div>
						<p class="sp-text-xs sp-text-muted sp-mt-8 sp-mb-0"><?php echo esc_html__( 'This usually takes a minute. You can safely navigate away.', 'sitepulse' ); ?></p>

					<?php elseif ( ! empty( $is_ai_failed ) ) : ?>
						<p class="sp-fw-500 sp-text-danger sp-mb-8"><span class="dashicons dashicons-warning" style="vertical-align:text-top;"></span> <?php echo esc_html__( 'Diagnostic Failed', 'sitepulse' ); ?></p>
						<p class=""><?php echo esc_html__( 'Something went wrong while checking your site. Please try again.', 'sitepulse' ); ?></p>
						<button type="button" class="sp-btn sp-btn-sm sp-btn-primary sp-request-ai-diagnostic-btn" data-force="true">
							<span class="dashicons dashicons-update-alt"></span> <span class="sp-btn-text"><?php echo esc_html__( 'Try Again', 'sitepulse' ); ?></span>
						</button>

					<?php elseif ( ! empty( $has_ai_report ) ) : ?>
						<?php
						$metrics      = $ai_diagnostic_report['metrics_summary'] ?? [];
						$quick_wins   = $ai_diagnostic_report['quick_wins'] ?? [];
						$action_items = $ai_diagnostic_report['action_items'] ?? [];
						$wp_specific  = $ai_diagnostic_report['wordpress_specific'] ?? [];
						$insights     = $ai_diagnostic_report['insights'] ?? [];
						$recs         = $ai_diagnostic_report['recommendations'] ?? [];
						$health_score = $ai_diagnostic_report['health_score'] ?? ( $metrics['overall_health_score'] ?? null );
						$report_status = $ai_diagnostic_report['status'] ?? 'unknown';

						$sp_status_map = [
							'healthy'  => [ 'cls' => 'success', 'icon' => 'yes-alt', 'label' => __( 'Healthy', 'sitepulse' ) ],
							'warning'  => [ 'cls' => 'warning', 'icon' => 'warning', 'label' => __( 'Needs Attention', 'sitepulse' ) ],
							'critical' => [ 'cls' => 'danger',  'icon' => 'dismiss', 'label' => __( 'Critical', 'sitepulse' ) ],
							'unknown'  => [ 'cls' => 'info',    'icon' => 'info',    'label' => __( 'Analyzed', 'sitepulse' ) ],
						];
						$st         = $sp_status_map[ $report_status ] ?? $sp_status_map['unknown'];
						$health_cls = $health_score !== null ? ( $health_score >= 80 ? 'success' : ( $health_score >= 60 ? 'warning' : 'danger' ) ) : 'info';
						?>

						<!-- Executive Summary -->
						<div class="sp-ai-exec-summary ">
							<div class="sp-list-item-icon" style="background:var(--sp-<?php echo esc_attr( $st['cls'] ); ?>-soft);flex-shrink:0;">
								<span class="dashicons dashicons-<?php echo esc_attr( $st['icon'] ); ?>" style="color:var(--sp-<?php echo esc_attr( $st['cls'] ); ?>);"></span>
							</div>
							<div style="flex:1;min-width:0;">
								<p class="sp-fw-700 sp-mb-4" style="font-size:14px;color:var(--sp-<?php echo esc_attr( $st['cls'] ); ?>);"><?php echo esc_html( $st['label'] ); ?></p>
								<?php if ( ! empty( $ai_diagnostic_report['summary'] ) ) : ?>
									<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( $ai_diagnostic_report['summary'] ); ?></p>
								<?php endif; ?>
							</div>
							<?php if ( $health_score !== null ) : ?>
								<div class="sp-ai-health-ring sp-ai-health-ring--<?php echo esc_attr( $health_cls ); ?>">
									<span class="sp-ai-health-value"><?php echo esc_html( $health_score ); ?></span>
									<span class="sp-ai-health-label"><?php echo esc_html__( 'Health', 'sitepulse' ); ?></span>
								</div>
							<?php endif; ?>
						</div>

						<!-- Metrics Grade Bar -->
						<?php if ( ! empty( $metrics ) ) : ?>
							<div class="sp-ai-metrics-bar ">
								<?php
								$grades = [
									'pagespeed_grade'      => __( 'Speed', 'sitepulse' ),
									'security_grade'       => __( 'Security', 'sitepulse' ),
									'error_severity'       => __( 'Errors', 'sitepulse' ),
									'overall_health_score' => __( 'Overall', 'sitepulse' ),
								];
								foreach ( $grades as $key => $label ) :
									if ( isset( $metrics[ $key ] ) ) :
										$v = $metrics[ $key ];
										if ( $key === 'error_severity' ) {
											$cls = in_array( $v, [ 'none', 'low' ] ) ? 'success' : ( $v === 'medium' ? 'warning' : 'danger' );
											$val = ucfirst( $v );
										} elseif ( $key === 'overall_health_score' ) {
											$cls = $v >= 80 ? 'success' : ( $v >= 60 ? 'warning' : 'danger' );
											$val = $v;
										} else {
											$cls = in_array( $v, [ 'A', 'B' ] ) ? 'success' : ( $v === 'C' ? 'warning' : 'danger' );
											$val = $v;
										}
								?>
									<div class="sp-ai-metric-grade">
										<div class="sp-ai-grade-badge sp-ai-grade--<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( $val ); ?></div>
										<div class="sp-ai-grade-label"><?php echo esc_html( $label ); ?></div>
									</div>
								<?php endif; endforeach; ?>
							</div>
						<?php endif; ?>

						<!-- Findings & Recommendations -->
						<div class="sp-ai-findings">
							<?php if ( ! empty( $quick_wins ) ) : ?>
								<h4 class="sp-zone-subheader"><?php echo esc_html__( 'QUICK WINS', 'sitepulse' ); ?></h4>
								<?php foreach ( $quick_wins as $qw ) : ?>
									<div class="sp-list-item sp-mb-8" style="background:var(--sp-success-soft);border:1px solid rgba(16,185,129,0.1);flex-direction:column;align-items:stretch;">
										<div class="sp-flex sp-items-center sp-gap-12">
											<div class="sp-list-item-icon" style="background:var(--sp-success);flex-shrink:0;"><span class="dashicons dashicons-yes-alt" style="color:#fff;"></span></div>
											<div class="sp-list-item-body">
												<p class="sp-list-item-title"><?php echo esc_html( $qw['title'] ?? '' ); ?></p>
												<?php if ( ! empty( $qw['impact'] ) ) : ?><p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( $qw['impact'] ); ?></p><?php endif; ?>
											</div>
										</div>
										<?php if ( ! empty( $qw['fix'] ) ) : ?>
											<div style="margin-top:8px;padding-left:44px;">
												<p class="sp-text-xs sp-mb-4" style="color:var(--sp-text-secondary);"><?php echo esc_html( $qw['fix'] ); ?></p>
											</div>
										<?php endif; ?>
										<?php if ( ! empty( $qw['code_snippet'] ) ) : ?>
											<div style="margin-top:4px;padding-left:44px;">
												<pre class="sp-ai-code" style="margin-bottom:0;"><code><?php echo esc_html( $qw['code_snippet'] ); ?></code></pre>
											</div>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php if ( ! empty( $action_items ) ) : ?>
								<h4 class="sp-zone-subheader sp-mt-16"><?php echo esc_html__( 'ACTION ITEMS', 'sitepulse' ); ?></h4>
								<?php foreach ( $action_items as $ai_idx => $item ) :
									$p          = $item['priority'] ?? 'moderate';
									$badge_cls  = $p === 'critical' ? 'sp-badge-danger' : ( $p === 'high' ? 'sp-badge-warning' : 'sp-badge-neutral' );
									$icon_color = $p === 'critical' ? 'var(--sp-danger)' : ( $p === 'high' ? 'var(--sp-warning)' : 'var(--sp-accent)' );
									$has_details = ! empty( $item['problem'] ) || ! empty( $item['fix'] ) || ! empty( $item['code_snippet'] );
								?>
									<?php if ( $has_details ) : ?>
									<details class="sp-ai-action-detail-wrap sp-mb-8">
										<summary class="sp-list-item" style="cursor:pointer;list-style:none;padding:8px 12px;">
											<div class="sp-list-item-icon" style="background:var(--sp-bg-primary);flex-shrink:0;"><span class="dashicons dashicons-lightbulb" style="color:<?php echo $icon_color; ?>;"></span></div>
											<div class="sp-list-item-body" style="flex:1;min-width:0;">
												<p class="sp-list-item-title"><?php echo esc_html( $item['title'] ?? '' ); ?></p>
												<div class="sp-flex sp-items-center sp-gap-8 sp-mt-4">
													<span class="sp-badge <?php echo $badge_cls; ?>"><?php echo esc_html( ucfirst( $p ) ); ?></span>
													<?php if ( ! empty( $item['estimated_effort'] ) ) : ?>
														<span class="sp-text-xs sp-text-muted">
															<span class="dashicons dashicons-clock" style="font-size:12px;width:12px;height:12px;"></span>
															<?php
															$effort_map = [ 'quick-fix' => '< 5 min', 'moderate' => '15-30 min', 'complex' => '1+ hr' ];
															echo esc_html( $effort_map[ $item['estimated_effort'] ] ?? $item['estimated_effort'] );
															?>
														</span>
													<?php endif; ?>
												</div>
											</div>
											<span class="dashicons dashicons-arrow-down-alt2" style="color:var(--sp-text-muted);font-size:14px;width:14px;height:14px;flex-shrink:0;"></span>
										</summary>
										<div style="padding:12px 16px 12px 44px;border-top:1px solid var(--sp-border);background:var(--sp-bg-secondary);">
											<?php if ( ! empty( $item['problem'] ) ) : ?>
												<p class="sp-text-xs sp-mb-8"><strong><?php echo esc_html__( 'Problem:', 'sitepulse' ); ?></strong> <?php echo esc_html( $item['problem'] ); ?></p>
											<?php endif; ?>
											<?php if ( ! empty( $item['fix'] ) ) : ?>
												<p class="sp-text-xs sp-mb-8"><strong><?php echo esc_html__( 'Fix:', 'sitepulse' ); ?></strong> <?php echo esc_html( $item['fix'] ); ?></p>
											<?php endif; ?>
											<?php if ( ! empty( $item['code_snippet'] ) ) : ?>
												<pre class="sp-ai-code" style="margin-bottom:0;"><code><?php echo esc_html( $item['code_snippet'] ); ?></code></pre>
											<?php endif; ?>
										</div>
									</details>
									<?php else : ?>
									<div class="sp-list-item sp-mb-8">
										<div class="sp-list-item-icon" style="background:var(--sp-bg-primary);"><span class="dashicons dashicons-lightbulb" style="color:<?php echo $icon_color; ?>;"></span></div>
										<div class="sp-list-item-body">
											<p class="sp-list-item-title"><?php echo esc_html( $item['title'] ?? '' ); ?></p>
											<div class="sp-flex sp-items-center sp-gap-8 sp-mt-4">
												<span class="sp-badge <?php echo $badge_cls; ?>"><?php echo esc_html( ucfirst( $p ) ); ?></span>
												<?php if ( ! empty( $item['estimated_effort'] ) ) : ?>
													<span class="sp-text-xs sp-text-muted">
														<span class="dashicons dashicons-clock" style="font-size:12px;width:12px;height:12px;"></span>
														<?php
														$effort_map = [ 'quick-fix' => '< 5 min', 'moderate' => '15-30 min', 'complex' => '1+ hr' ];
														echo esc_html( $effort_map[ $item['estimated_effort'] ] ?? $item['estimated_effort'] );
														?>
													</span>
												<?php endif; ?>
											</div>
										</div>
									</div>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php if ( ! empty( $wp_specific['wp_config_tweaks'] ) ) : ?>
								<h4 class="sp-zone-subheader sp-mt-16"><?php echo esc_html__( 'SYSTEM TWEAKS', 'sitepulse' ); ?></h4>
								<?php foreach ( $wp_specific['wp_config_tweaks'] as $tweak ) : ?>
									<div class="sp-list-item sp-mb-8" style="background:var(--sp-bg-primary);">
										<div class="sp-list-item-icon"><span class="dashicons dashicons-admin-tools"></span></div>
										<div class="sp-list-item-body">
											<p class="sp-list-item-title"><?php echo esc_html( $tweak['setting'] ?? '' ); ?></p>
											<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( $tweak['reason'] ?? '' ); ?></p>
											<?php if ( ! empty( $tweak['code'] ) ) : ?>
												<pre class="sp-ai-code sp-mt-8" style="margin-bottom:0; background: rgba(0,0,0,0.03);"><code><?php echo esc_html( $tweak['code'] ); ?></code></pre>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php if ( ! empty( $insights ) ) : ?>
								<h4 class="sp-zone-subheader sp-mt-16"><?php echo esc_html__( 'INSIGHTS', 'sitepulse' ); ?></h4>
								<?php
								$insight_icons = [ 'performance' => 'performance', 'security' => 'shield', 'errors' => 'warning' ];
								foreach ( $insights as $key => $insight ) :
									$finding  = is_array( $insight ) ? ( $insight['finding'] ?? '' ) : $insight;
									$severity = is_array( $insight ) ? ( $insight['severity'] ?? 'needs-work' ) : 'needs-work';
									$icon     = $insight_icons[ $key ] ?? 'admin-generic';
									$icon_bg  = $severity === 'poor' ? 'var(--sp-danger-soft)' : ( $severity === 'needs-work' ? 'var(--sp-warning-soft)' : 'var(--sp-bg-primary)' );
									$icon_col = $severity === 'poor' ? 'var(--sp-danger)' : ( $severity === 'needs-work' ? 'var(--sp-warning)' : 'var(--sp-text-secondary)' );
								?>
									<div class="sp-list-item sp-mb-8" style="background:var(--sp-bg-primary);">
										<div class="sp-list-item-icon" style="background:<?php echo $icon_bg; ?>;">
											<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>" style="color:<?php echo $icon_col; ?>;"></span>
										</div>
										<div class="sp-list-item-body">
											<p class="sp-list-item-title"><?php echo esc_html( ucfirst( $key ) ); ?></p>
											<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( $finding ); ?></p>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php if ( ! empty( $wp_specific['plugins_to_review'] ) ) : ?>
								<h4 class="sp-zone-subheader sp-mt-16"><?php echo esc_html__( 'PLUGINS TO REVIEW', 'sitepulse' ); ?></h4>
								<?php foreach ( $wp_specific['plugins_to_review'] as $plugin ) :
									$p_name   = is_array( $plugin ) ? ( $plugin['name'] ?? '' ) : $plugin;
									$p_issue  = is_array( $plugin ) ? ( $plugin['issue'] ?? '' ) : '';
									$p_action = is_array( $plugin ) ? ( $plugin['action'] ?? '' ) : '';
									$p_alt    = is_array( $plugin ) ? ( $plugin['alternative'] ?? '' ) : '';
								?>
									<div class="sp-list-item sp-mb-8" style="background:var(--sp-warning-soft);border:1px solid rgba(245,158,11,0.2);">
										<div class="sp-list-item-icon"><span class="dashicons dashicons-warning" style="color:var(--sp-warning);"></span></div>
										<div class="sp-list-item-body">
											<p class="sp-list-item-title"><?php echo esc_html( $p_name ); ?></p>
											<?php if ( ! empty( $p_issue ) ) : ?>
												<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( $p_issue ); ?></p>
											<?php endif; ?>
											<?php if ( ! empty( $p_action ) ) : ?>
												<p class="sp-text-xs sp-mt-4 sp-mb-0" style="color:#b45309; font-weight:600;">
													<span class="dashicons dashicons-arrow-right-alt" style="font-size:14px;width:14px;height:14px;line-height:1.2;vertical-align:text-top;"></span>
													<?php echo esc_html( $p_action ); ?>
												</p>
											<?php endif; ?>
											<?php if ( ! empty( $p_alt ) ) : ?>
												<p class="sp-text-xs sp-mt-4 sp-mb-0 sp-text-muted">
													<?php echo esc_html__( 'Alternative:', 'sitepulse' ); ?> <?php echo esc_html( $p_alt ); ?>
												</p>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php if ( ! empty( $wp_specific['recommended_plugins'] ) ) : ?>
								<h4 class="sp-zone-subheader sp-mt-16"><?php echo esc_html__( 'RECOMMENDED PLUGINS', 'sitepulse' ); ?></h4>
								<?php foreach ( $wp_specific['recommended_plugins'] as $plugin ) :
									$p_name = is_array( $plugin ) ? ( $plugin['name'] ?? '' ) : $plugin;
									$p_purp = is_array( $plugin ) ? ( $plugin['purpose'] ?? '' ) : '';
									$p_cmd  = is_array( $plugin ) ? ( $plugin['install_command'] ?? '' ) : '';
								?>
									<div class="sp-list-item sp-mb-8" style="background:var(--sp-info-soft);border:1px solid rgba(59,130,246,0.1);">
										<div class="sp-list-item-icon" style="background:var(--sp-info);"><span class="dashicons dashicons-yes" style="color:#fff;"></span></div>
										<div class="sp-list-item-body">
											<p class="sp-list-item-title"><?php echo esc_html( $p_name ); ?></p>
											<?php if ( ! empty( $p_purp ) ) : ?>
												<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( $p_purp ); ?></p>
											<?php endif; ?>
											<?php if ( ! empty( $p_cmd ) ) : ?>
												<pre class="sp-ai-code sp-mt-4" style="margin-bottom:0;font-size:11px;"><code><?php echo esc_html( $p_cmd ); ?></code></pre>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php if ( ! empty( $wp_specific['hosting_notes'] ) ) : ?>
								<div class="sp-list-item sp-mt-16 sp-mb-8" style="background:var(--sp-bg-primary);">
									<div class="sp-list-item-icon"><span class="dashicons dashicons-cloud"></span></div>
									<div class="sp-list-item-body">
										<p class="sp-list-item-title"><?php echo esc_html__( 'Hosting Note', 'sitepulse' ); ?></p>
										<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( $wp_specific['hosting_notes'] ); ?></p>
									</div>
								</div>
							<?php endif; ?>

							<?php
							// Legacy Recommendations fallback
							if ( empty( $action_items ) && ! empty( $recs ) ) :
								$has_recs = ( ! empty( $recs['critical'] ) || ! empty( $recs['high'] ) || ! empty( $recs['moderate'] ) || ! empty( $recs['low'] ) );
								if ( $has_recs ) :
									$p_cls_map = [ 'critical' => 'sp-badge-danger', 'high' => 'sp-badge-warning', 'moderate' => 'sp-badge-neutral', 'low' => 'sp-badge-neutral' ];
							?>
								<h4 class="sp-zone-subheader sp-mt-16"><?php echo esc_html__( 'RECOMMENDATIONS', 'sitepulse' ); ?></h4>
								<?php foreach ( [ 'critical', 'high', 'moderate', 'low' ] as $prio ) :
									if ( ! empty( $recs[ $prio ] ) ) :
										foreach ( $recs[ $prio ] as $rec ) :
								?>
									<div class="sp-list-item sp-mb-8">
										<div class="sp-list-item-body">
											<div class="sp-flex sp-items-center sp-gap-8">
												<span class="sp-badge <?php echo esc_attr( $p_cls_map[ $prio ] ); ?>"><?php echo esc_html( ucfirst( $prio ) ); ?></span>
												<p class="sp-list-item-title sp-mb-0"><?php echo esc_html( $rec ); ?></p>
											</div>
										</div>
									</div>
								<?php endforeach; endif; endforeach; ?>
							<?php endif; endif; ?>
						</div>

						<!-- AI Disclaimer -->
						<p class="sp-text-xs sp-text-muted sp-mt-16 sp-mb-0" style="padding-top:12px;border-top:1px dashed var(--sp-border);">
							<?php echo esc_html__( 'AI can make mistakes - always double-check recommendations.', 'sitepulse' ); ?>
						</p>

					<?php else : ?>
						<p class=""><?php echo esc_html__( 'Hello! I\'m your AI assistant. I can check your site for speed issues, security concerns, and setup problems — then give you clear steps to fix them.', 'sitepulse' ); ?></p>
						<p class="sp-mb-0"><?php echo esc_html__( 'Ready for a full checkup?', 'sitepulse' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Action Panel - only when never or empty ai diagnostic-->
		<div class="sp-card-footer sp-ai-action-panel">
			<?php if ( ! empty( $ai_diagnostic_requested_at ) && ! empty( $has_ai_report ) ) : ?>
				<span class="sp-text-xs sp-text-muted">
					<?php echo esc_html__( 'Last check:', 'sitepulse' ); ?> <?php echo esc_html( date( 'M j, g:i A', strtotime( $ai_diagnostic_requested_at ) ) ); ?>
				</span>
			<?php else : ?>
				<span></span>
			<?php endif; ?>
			<button type="button" class="sp-btn sp-btn-primary sp-request-ai-diagnostic-btn" data-force="<?php echo ! empty( $has_ai_report ) || ! empty( $is_ai_failed ) ? 'true' : 'false'; ?>">
				<span class="dashicons <?php echo ! empty( $has_ai_report ) || ! empty( $is_ai_failed ) ? 'dashicons-update-alt' : 'dashicons-format-chat'; ?>"></span>
				<span class="sp-btn-text">
					<?php echo ! empty( $has_ai_report ) || ! empty( $is_ai_failed ) ? esc_html__( 'Run New Checkup', 'sitepulse' ) : esc_html__( 'Start Checkup', 'sitepulse' ); ?>
				</span>
			</button>
		</div>
	</div>
</div>
	<?php endif; ?>
</div><!-- End Hero Grid -->

<!-- Key Pulse Metrics moved below Hero -->
<h3 class="sp-page-title sp-mb-16" style="font-size:18px;"><?php echo esc_html__( 'Key Numbers', 'sitepulse' ); ?></h3>
<div class="sp-grid sp-grid-4 sp-mb-24">
	<div class="sp-card sp-pulse-card sp-metric">
		<?php
		$lt_display = '—';
		$lt_unit    = '';
		if ( isset( $total_load_time ) && $total_load_time > 0 ) {
			if ( $total_load_time >= 1000 ) {
				$lt_display = number_format( $total_load_time / 1000, 1 );
				$lt_unit    = 's';
			} else {
				$lt_display = number_format( $total_load_time, 0 );
				$lt_unit    = 'ms';
			}
		}
		$metric_icon  = 'dashicons-clock';
		$metric_color = ( isset( $total_load_time ) && $total_load_time < 1500 ) ? 'success' : ( ( isset( $total_load_time ) && $total_load_time < 3000 ) ? 'warning' : 'danger' );
		$metric_value = $lt_display . $lt_unit;
		$metric_label = __( 'Load Time', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
	<div class="sp-card sp-pulse-card sp-metric">
		<?php
		$metric_icon  = 'dashicons-database';
		$metric_color = ( isset( $memory_score ) && $memory_score >= 80 ) ? 'success' : ( ( isset( $memory_score ) && $memory_score >= 60 ) ? 'warning' : 'danger' );
		$metric_value = isset( $mem['percent'] ) ? $mem['percent'] : '—';
		$metric_label = __( 'Memory Usage', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
	<div class="sp-card sp-pulse-card sp-metric">
		<?php
		$metric_icon  = 'dashicons-admin-plugins';
		$metric_color = ( isset( $plugins_score ) && $plugins_score >= 80 ) ? 'success' : ( ( isset( $plugins_score ) && $plugins_score >= 60 ) ? 'warning' : 'danger' );
		$metric_value = isset( $active_plugins_count ) ? $active_plugins_count : 0;
		$metric_label = __( 'Active Plugins', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
	<div class="sp-card sp-pulse-card sp-metric">
		<?php
		$err = isset( $error_count ) ? (int) $error_count : 0;
		$metric_icon  = 'dashicons-warning';
		$metric_color = $err > 0 ? 'danger' : 'success';
		$metric_value = $err;
		$metric_label = __( 'Errors (2 days)', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
</div>



<!-- ═══════════════════════════════════════════════════════════════════════
     MAIN DASHBOARD COLUMNS
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-grid sp-grid-2" style="align-items:start; gap:24px;">

<!-- LEFT COLUMN -->
<div class="sp-col">
<!-- Site Health and Performance -->
<div class="sp-zone sp-zone--infra">
	<!-- Server Environment -->
	<?php
	$sitepulse_php_ok   = version_compare( PHP_VERSION, '8.1.0', '>=' );
	$sitepulse_wp_ok    = version_compare( get_bloginfo( 'version' ), '6.3', '>=' );
	$sitepulse_ssl_ok   = is_ssl();
	$sitepulse_mem_ok   = isset( $mem['percent'] ) ? ( (float) $mem['percent'] < 85 ) : true;
	$sitepulse_error_ok = ( isset( $error_log_score ) ? (int) $error_log_score : 100 ) >= 60;
	$all_pass           = $sitepulse_php_ok && $sitepulse_wp_ok && $sitepulse_ssl_ok && $sitepulse_mem_ok && $sitepulse_error_ok;
	?>
	<div class="sp-pulse-card">
		<div class="sp-pulse-card-header">
			<div>
				<h3 class="sp-pulse-card-title">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php echo esc_html__( 'Your Site\'s Foundation', 'sitepulse' ); ?>
				</h3>
				<p class="sp-pulse-card-subtitle"><?php echo esc_html__( 'The essential building blocks your site needs to run well', 'sitepulse' ); ?></p>
			</div>
			<span class="sp-badge <?php echo $all_pass ? 'sp-badge-success' : 'sp-badge-warning'; ?>">
				<?php echo $all_pass ? esc_html__( 'Looking Good', 'sitepulse' ) : esc_html__( 'Needs Attention', 'sitepulse' ); ?>
			</span>
		</div>
		<div class="sp-pulse-card-body sp-p-0">
			<div class="sp-list-item" style="padding:10px 20px;">
				<div class="sp-list-item-icon" style="background:<?php echo $sitepulse_php_ok ? 'var(--sp-success-soft)' : 'var(--sp-warning-soft)'; ?>;">
					<span class="dashicons dashicons-editor-code" style="color:<?php echo $sitepulse_php_ok ? 'var(--sp-success)' : 'var(--sp-warning)'; ?>;font-size:16px;width:16px;height:16px;"></span>
				</div>
				<div class="sp-list-item-body">
					<p class="sp-list-item-title"><?php echo esc_html__( 'Server Language', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( PHP_VERSION ); ?></p>
				</div>
				<div class="sp-list-item-right">
					<span class="sp-badge <?php echo $sitepulse_php_ok ? 'sp-badge-success' : 'sp-badge-warning'; ?>"><?php echo $sitepulse_php_ok ? esc_html__( 'Good', 'sitepulse' ) : esc_html__( 'Update Recommended', 'sitepulse' ); ?></span>
				</div>
			</div>
			<div class="sp-list-item" style="padding:10px 20px;">
				<div class="sp-list-item-icon" style="background:<?php echo $sitepulse_wp_ok ? 'var(--sp-success-soft)' : 'var(--sp-warning-soft)'; ?>;">
					<span class="dashicons dashicons-wordpress" style="color:<?php echo $sitepulse_wp_ok ? 'var(--sp-success)' : 'var(--sp-warning)'; ?>;font-size:16px;width:16px;height:16px;"></span>
				</div>
				<div class="sp-list-item-body">
					<p class="sp-list-item-title"><?php echo esc_html__( 'WordPress Version', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted sp-mb-0">v<?php echo esc_html( get_bloginfo( 'version' ) ); ?></p>
				</div>
				<div class="sp-list-item-right">
					<span class="sp-badge <?php echo $sitepulse_wp_ok ? 'sp-badge-success' : 'sp-badge-warning'; ?>"><?php echo $sitepulse_wp_ok ? esc_html__( 'Good', 'sitepulse' ) : esc_html__( 'Outdated', 'sitepulse' ); ?></span>
				</div>
			</div>
			<div class="sp-list-item" style="padding:10px 20px;">
				<div class="sp-list-item-icon" style="background:<?php echo $sitepulse_ssl_ok ? 'var(--sp-success-soft)' : 'var(--sp-danger-soft)'; ?>;">
					<span class="dashicons <?php echo $sitepulse_ssl_ok ? 'dashicons-lock' : 'dashicons-unlock'; ?>" style="color:<?php echo $sitepulse_ssl_ok ? 'var(--sp-success)' : 'var(--sp-danger)'; ?>;font-size:16px;width:16px;height:16px;"></span>
				</div>
				<div class="sp-list-item-body">
					<p class="sp-list-item-title"><?php echo esc_html__( 'Secure Connection (SSL)', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html__( 'The padlock icon visitors see in their browser', 'sitepulse' ); ?></p>
				</div>
				<div class="sp-list-item-right">
					<span class="sp-badge <?php echo $sitepulse_ssl_ok ? 'sp-badge-success' : 'sp-badge-danger'; ?>"><?php echo $sitepulse_ssl_ok ? esc_html__( 'Secure', 'sitepulse' ) : esc_html__( 'Not Secure', 'sitepulse' ); ?></span>
				</div>
			</div>
			<div class="sp-list-item" style="padding:10px 20px;">
				<div class="sp-list-item-icon" style="background:<?php echo $sitepulse_error_ok ? 'var(--sp-success-soft)' : 'var(--sp-warning-soft)'; ?>;">
					<span class="dashicons dashicons-portfolio" style="color:<?php echo $sitepulse_error_ok ? 'var(--sp-success)' : 'var(--sp-warning)'; ?>;font-size:16px;width:16px;height:16px;"></span>
				</div>
				<div class="sp-list-item-body">
					<p class="sp-list-item-title"><?php echo esc_html__( 'Available Memory', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo isset( $mem['formatted'] ) ? esc_html( $mem['formatted'] ) : ''; ?></p>
				</div>
				<div class="sp-list-item-right">
					<span class="sp-badge <?php echo $sitepulse_mem_ok ? 'sp-badge-success' : 'sp-badge-warning'; ?>"><?php echo $sitepulse_mem_ok ? esc_html__( 'Plenty of Room', 'sitepulse' ) : esc_html__( 'Running Low', 'sitepulse' ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<!-- PageSpeed Core Web Vitals -->
	<div class="sp-pulse-card">
		<div class="sp-pulse-card-header sp-flex sp-justify-between sp-items-center">
			<div>
				<h3 class="sp-pulse-card-title">
					<span class="dashicons dashicons-chart-area"></span>
					<?php echo esc_html__( 'Website Speed', 'sitepulse' ); ?>
				</h3>
				<p class="sp-pulse-card-subtitle"><?php echo esc_html__( 'How fast your website loads for visitors', 'sitepulse' ); ?></p>
			</div>
			<div class="sp-flex sp-items-center sp-gap-12">
				<?php if ( ! empty( $has_valid_pagespeed ) && isset( $ps_performance_score ) && is_numeric( $ps_performance_score ) ) : ?>
					<span class="sp-badge sp-badge-solid-accent" style="font-size:16px;padding:4px 10px;"><?php echo esc_html( $ps_performance_score ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<div class="sp-pulse-card-body sp-p-0">
			<?php if ( ! empty( $has_valid_pagespeed ) && isset( $ps_data['core_web_vitals'] ) && ! empty( $ps_data['core_web_vitals'] ) ) : ?>
				<div class="sp-grid sp-grid-2" style="padding:16px 20px;gap:12px;">
					<?php
					$cwv         = $ps_data['core_web_vitals'];
					$cwv_metrics = [
						'fcp' => [ 'label' => 'FCP', 'name' => 'First Content Visible' ],
						'lcp' => [ 'label' => 'LCP', 'name' => 'Main Content Loaded' ],
						'cls' => [ 'label' => 'CLS', 'name' => 'Page Stability' ],
						'tbt' => [ 'label' => 'TBT', 'name' => 'Browser Wait Time' ],
					];
					foreach ( $cwv_metrics as $key => $metric ) :
						if ( ! isset( $cwv[ $key ] ) ) {
							continue;
						}
						$m_data  = $cwv[ $key ];
						$m_score = isset( $m_data['score'] ) ? floatval( $m_data['score'] ) : 0;
						$m_cls   = $m_score >= 0.9 ? 'sp-text-success' : ( $m_score >= 0.5 ? 'sp-text-warning' : 'sp-text-danger' );
						$m_val   = isset( $m_data['display_value'] ) ? $m_data['display_value'] : '—';
					?>
						<div class="sp-cwv-card">
							<div class="sp-flex sp-justify-between sp-mb-4">
								<span class="sp-text-xs sp-fw-700"><?php echo esc_html( $metric['label'] ); ?></span>
								<span class="sp-text-xs sp-fw-700 <?php echo esc_attr( $m_cls ); ?>"><?php echo esc_html( $m_val ); ?></span>
							</div>
							<p class="sp-text-xs sp-text-muted sp-mb-0" style="font-size:10px;"><?php echo esc_html( $metric['name'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ( isset( $ps_data['opportunities'] ) && count( $ps_data['opportunities'] ) > 0 ) : ?>
					<div style="padding:0 20px 16px 20px;">
						<p class="sp-text-xs sp-fw-600 sp-text-muted sp-mb-8"><?php echo esc_html__( 'BIGGEST IMPROVEMENT', 'sitepulse' ); ?></p>
						<?php $top_opp = reset( $ps_data['opportunities'] ); ?><?php if ( isset( $ps_data['opportunities'] ) && is_array( $ps_data['opportunities'] ) && ! empty( $ps_data['opportunities'] ) ) : ?>
						<div class="sp-list-item" style="padding:10px;background:var(--sp-warning-soft);border:1px solid rgba(245,158,11,0.2);">
							<div class="sp-list-item-icon" style="background:var(--sp-warning);"><span class="dashicons dashicons-lightbulb" style="color:#fff;"></span></div>
							<div class="sp-list-item-body">
								<p class="sp-list-item-title sp-text-sm"><?php echo esc_html( $top_opp['title'] ?? '' ); ?></p>
								<?php if ( ! empty( $top_opp['display_value'] ) ) : ?>
									<span class="sp-badge sp-badge-warning sp-mt-4"><?php echo esc_html__( 'Potential savings:', 'sitepulse' ); ?> <?php echo esc_html( $top_opp['display_value'] ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<?php else : ?>
				<div class="sp-empty sp-flex sp-flex-col sp-items-center sp-justify-center" style="padding:32px 20px; height: 100%; min-height: 200px;">
					<div class="sp-empty-icon"><span class="dashicons dashicons-image-filter" style="color:var(--sp-neutral);"></span></div>
					<p class="sp-empty-title"><?php echo esc_html__( 'No Speed Data Yet', 'sitepulse' ); ?></p>
					<p class="sp-empty-desc"><?php echo esc_html__( 'Wait 4-8 hours for your first speed test result. The test is automatically performed every 24 hours.', 'sitepulse' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div><!-- /sp-zone--infra -->


</div><!-- END LEFT COLUMN -->

<!-- RIGHT COLUMN -->
<div class="sp-col">
<!-- Database & Monitoring -->
<?php if ( defined( 'SITEPULSE_PRO_IS_ACTIVE' ) && SITEPULSE_PRO_IS_ACTIVE ) : ?>
<div class="sp-zone sp-zone--data">
	<!-- Performance Trends -->
	<div class="sp-pulse-card">
		<div class="sp-pulse-card-header">
			<div>
				<h3 class="sp-pulse-card-title">
					<span class="dashicons dashicons-chart-line"></span>
					<?php echo esc_html__( 'Performance Trends', 'sitepulse-pro' ); ?>
				</h3>
				<p class="sp-pulse-card-subtitle"><?php echo esc_html__( 'Track how your site speed changes over time', 'sitepulse-pro' ); ?></p>
			</div>
		</div>
		<div class="sp-pulse-card-body">
			<?php
			if ( class_exists( 'Sitepulse_Pro_Performance_History' ) ) :
				$history_tracker = Sitepulse_Pro_Performance_History::get_instance();
				$api_history     = $history_tracker->get_api_history( 7 );
				$hooks_history   = $history_tracker->get_hooks_history( 7 );
				$has_trend_data  = ! empty( $api_history ) || ! empty( $hooks_history );

				if ( $has_trend_data ) :
					$total_days    = count( array_unique( array_merge( array_keys( $api_history ), array_keys( $hooks_history ) ) ) );
					$max_api_time  = 0;
					$max_hook_time = 0;
					foreach ( $api_history as $d ) {
						if ( isset( $d['max_time'] ) && $d['max_time'] > $max_api_time ) {
							$max_api_time = $d['max_time'];
						}
					}
					foreach ( $hooks_history as $d ) {
						if ( isset( $d['max_time'] ) && $d['max_time'] > $max_hook_time ) {
							$max_hook_time = $d['max_time'];
						}
					}
					$api_fmt  = class_exists( 'Sitepulse_Utils' ) ? Sitepulse_Utils::format_time( $max_api_time, 'seconds' ) : round( $max_api_time * 1000 ) . 'ms';
					$hook_fmt = class_exists( 'Sitepulse_Utils' ) ? Sitepulse_Utils::format_time( $max_hook_time, 'seconds' ) : round( $max_hook_time * 1000 ) . 'ms';
			?>
				<div class="sp-grid sp-grid-2 sp-mb-16">
					<div class="sp-pulse-card" style="background:var(--sp-bg-secondary);border:1px solid var(--sp-border);">
						<div class="sp-pulse-card-body sp-flex sp-items-center sp-gap-12">
							<span class="dashicons dashicons-admin-site-alt3" style="color:var(--sp-info);font-size:20px;width:20px;height:20px;"></span>
							<div>
								<p class="sp-fw-700 sp-mb-0" style="margin:0;font-size:16px;color:var(--sp-text-primary);"><?php echo esc_html( $api_fmt ); ?></p>
								<p class="sp-text-xs sp-text-muted" style="margin:2px 0 0;"><?php echo esc_html__( 'Slowest Connection (7 days)', 'sitepulse-pro' ); ?></p>
							</div>
						</div>
					</div>
					<div class="sp-pulse-card" style="background:var(--sp-bg-secondary);border:1px solid var(--sp-border);">
						<div class="sp-pulse-card-body sp-flex sp-items-center sp-gap-12">
							<span class="dashicons dashicons-performance" style="color:var(--sp-accent);font-size:20px;width:20px;height:20px;"></span>
							<div>
								<p class="sp-fw-700 sp-mb-0" style="margin:0;font-size:16px;color:var(--sp-text-primary);"><?php echo esc_html( $hook_fmt ); ?></p>
								<p class="sp-text-xs sp-text-muted" style="margin:2px 0 0;"><?php echo esc_html__( 'Slowest Plugin Action (7 days)', 'sitepulse-pro' ); ?></p>
							</div>
						</div>
					</div>
				</div>
				<div class="sp-alert sp-alert-success">
					<span class="dashicons dashicons-yes-alt"></span>
					<div>
						<strong><?php echo esc_html__( 'Tracking Active', 'sitepulse-pro' ); ?></strong>
						<span class="sp-text-xs sp-text-muted" style="display:block;margin-top:2px;">
							<?php echo esc_html( sprintf(
								_n( 'Collecting data for %d day.', 'Collecting data for %d days.', $total_days, 'sitepulse-pro' ),
								$total_days
							) ); ?>
						</span>
					</div>
				</div>
			<?php else : ?>
				<div class="sp-empty" style="padding:24px;">
					<div class="sp-empty-icon"><span class="dashicons dashicons-clock" style="color:var(--sp-info);"></span></div>
					<p class="sp-empty-title"><?php echo esc_html__( 'Collecting Performance Data', 'sitepulse-pro' ); ?></p>
					<p class="sp-empty-desc"><?php echo esc_html__( 'Trends will appear once slow hooks or API calls are detected.', 'sitepulse-pro' ); ?></p>
				</div>
			<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- Database Monitor (SQL + Autoload) -->
	<div class="sp-pulse-card">
		<div class="sp-pulse-card-header">
			<div>
				<h3 class="sp-pulse-card-title">
					<span class="dashicons dashicons-database"></span>
					<?php echo esc_html__( 'Database Monitor', 'sitepulse-pro' ); ?>
				</h3>
				<p class="sp-pulse-card-subtitle"><?php echo esc_html__( 'Database lookups and startup data', 'sitepulse-pro' ); ?></p>
			</div>
		</div>
		<div style="padding:12px 20px;border-bottom:1px solid var(--sp-border);">
			<div class="sp-section-buttons">
				<button type="button" class="sp-section-toggle active" data-section="sp-sql-queries-section">
					<?php echo esc_html__( 'Database Lookups', 'sitepulse-pro' ); ?>
				</button>
				<button type="button" class="sp-section-toggle" data-section="sp-autoload-section">
					<?php echo esc_html__( 'Startup Data', 'sitepulse-pro' ); ?>
				</button>
			</div>
		</div>
		<div id="sp-sql-queries-section" class="sp-pulse-card-body sp-p-0">
			<?php if ( class_exists( 'Sitepulse_Pro_SQL_Monitor' ) ) :
				$sql_monitor = Sitepulse_Pro_SQL_Monitor::get_instance();
				$sql_stats   = $sql_monitor->get_statistics();
				$sql_recent  = $sql_monitor->get_queries( 3 );
			?>
				<div class="sp-grid sp-grid-3" style="padding:16px 20px;gap:12px;">
					<div style="text-align:center;">
						<p class="sp-fw-700" style="font-size:18px;margin:0;color:var(--sp-text-primary);"><?php echo esc_html( $sql_stats['avg_queries'] ); ?></p>
						<p class="sp-text-xs sp-text-muted" style="margin:2px 0 0;"><?php echo esc_html__( 'Avg. Lookups', 'sitepulse-pro' ); ?></p>
					</div>
					<div style="text-align:center;">
						<p class="sp-fw-700" style="font-size:18px;margin:0;color:var(--sp-text-primary);"><?php echo esc_html( number_format( $sql_stats['avg_time'] * 1000, 0 ) ); ?>ms</p>
						<p class="sp-text-xs sp-text-muted" style="margin:2px 0 0;"><?php echo esc_html__( 'Avg. Lookup Time', 'sitepulse-pro' ); ?></p>
					</div>
					<div style="text-align:center;">
						<p class="sp-fw-700" style="font-size:18px;margin:0;color:var(--sp-text-primary);"><?php echo esc_html( count( $sql_recent ) ); ?></p>
						<p class="sp-text-xs sp-text-muted" style="margin:2px 0 0;"><?php echo esc_html__( 'Recent Snapshots', 'sitepulse-pro' ); ?></p>
					</div>
				</div>
				<?php
				$sp_al = isset( $autoload_metrics ) && is_array( $autoload_metrics )
					? $autoload_metrics
					: ( ( class_exists( 'Sitepulse_Utils' ) && method_exists( 'Sitepulse_Utils', 'get_autoload_metrics' ) ) ? Sitepulse_Utils::get_autoload_metrics() : [] );
				$autoload_fmt   = isset( $sp_al['fmt'] ) ? $sp_al['fmt'] : '—';
				$autoload_class = isset( $sp_al['class'] ) ? $sp_al['class'] : 'sp-text-muted';
				?>
				<div class="sp-flex sp-px-20 sp-pb-16 sp-content-center">
					<span class="sp-text-xs sp-text-muted"> <?php echo esc_html__( 'Startup Data Size:', 'sitepulse-pro' ); ?></span>
					<span class="sp-text-xs sp-fw-700 <?php echo esc_attr( $autoload_class ); ?>"><?php echo esc_html( $autoload_fmt ); ?></span>
				</div>
				<?php if ( ! empty( $sql_recent ) ) : ?>
					<?php foreach ( array_slice( $sql_recent, 0, 3 ) as $sql_entry ) : 
						$slowest_sq = ! empty( $sql_entry['slow_queries'] ) ? $sql_entry['slow_queries'][0] : null;
						$slowest_sql = $slowest_sq ? $slowest_sq['sql'] : '';
						$caller      = $slowest_sq ? $slowest_sq['stack'] : '';
						
						// If no slow query but we have a URL, use URL as title if SQL is truly missing
						$title_text  = ! empty( $slowest_sql ) ? $slowest_sql : $sql_entry['url'];
						$meta_text   = ! empty( $slowest_sql ) ? $sql_entry['url'] : '';
					?>
						<div class="sp-list-item" style="padding:12px 20px;">
							<div class="sp-list-item-body">
								<p class="sp-list-item-title sp-text-sm sp-cell-mono sp-cell-truncate" title="<?php echo esc_attr( $title_text ); ?>" style="color:var(--sp-primary-light); margin-bottom: 2px;">
									<?php echo esc_html( wp_trim_words( $title_text, 10, '...' ) ); ?>
								</p>
								<div class="sp-flex sp-items-center sp-gap-8 sp-text-xs sp-text-muted">
									<?php if ( ! empty( $caller ) ) : ?>
										<span class="sp-text-accent" style="font-family: var(--sp-font-mono);"><?php echo esc_html( $caller ); ?></span>
										<span class="sp-text-muted">&bull;</span>
									<?php endif; ?>
									<span><?php echo esc_html( $sql_entry['total_queries'] ); ?> queries</span>
									<span>&bull;</span>
									<span><?php echo esc_html( number_format( $sql_entry['total_time'] * 1000, 0 ) ); ?>ms</span>
									<?php if ( ! empty( $meta_text ) ) : ?>
										<span class="sp-text-muted">&bull;</span>
										<span class="sp-text-xs" style="opacity: 0.7;"><?php echo esc_html( wp_trim_words( $meta_text, 4 ) ); ?></span>
									<?php endif; ?>
								</div>
							</div>
							<?php if ( ! empty( $sql_entry['slow_queries'] ) && $sql_entry['slow_queries'][0]['time'] > 0.1 ) : ?>
								<span class="sp-badge sp-badge-danger"><?php echo esc_html( count( $sql_entry['slow_queries'] ) ); ?> slow</span>
							<?php else : ?>
								<span class="sp-badge sp-badge-success" style="opacity:0.8;"><?php echo esc_html__( 'OK', 'sitepulse' ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="sp-empty" style="padding:24px;">
						<p class="sp-empty-desc"><?php echo esc_html__( 'No SQL snapshots captured yet.', 'sitepulse-pro' ); ?></p>
						<?php if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) : ?>
							<button type="button" class="sp-btn sp-btn-sm sp-btn-accent sp-enable-savequeries sp-mt-8">
								<span class="dashicons dashicons-admin-generic" style="font-size:14px;width:14px;height:14px;"></span>
								<?php echo esc_html__( 'Turn On Database Tracking', 'sitepulse-pro' ); ?>
							</button>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<div id="sp-autoload-section" style="display:none;">
			<div id="sp-autoload-loading" class="text-center" style="padding:32px 20px;">
				<span class="spinner-border spinner-border-sm me-2" role="status"></span>
				<span class="sp-text-sm sp-text-muted"><?php echo esc_html__( 'Loading autoloaded options...', 'sitepulse-pro' ); ?></span>
			</div>
			<div id="sp-autoload-error" class="d-none" style="padding:16px 20px;">
				<div class="sp-alert sp-alert-danger sp-mb-0" style="font-size:13px;"></div>
			</div>
			<div id="sp-autoload-results" class="d-none">
				<div style="border-bottom:1px solid var(--sp-border);padding:12px 20px;display:flex;align-items:center;justify-content:space-between;">
					<small id="sp-autoload-summary" class="sp-text-xs sp-text-muted"></small>
					<button type="button" id="sp-autoload-refresh" class="sp-btn sp-btn-ghost sp-btn-sm">
						<span class="dashicons dashicons-update-alt" style="font-size:14px;width:14px;height:14px;"></span>
					</button>
				</div>
				<div class="sp-details-list" id="sp-autoload-list"></div>
			</div>
		</div>
	</div>

<?php
// Performance Alerts
if ( class_exists( 'Sitepulse_Pro_Alerts' ) ) :
	$alerts_instance = Sitepulse_Pro_Alerts::get_instance();
	$alert_settings  = $alerts_instance->get_settings();
	$alert_history   = $alerts_instance->get_alert_history( 3 );
	$score_trend     = $alerts_instance->get_score_trend();
	$ps_current      = $alerts_instance->get_current_pagespeed_score();
?>
<div class="sp-pulse-card sp-mb-16">
	<div class="sp-pulse-card-header">
		<div>
			<h3 class="sp-pulse-card-title">
				<span class="dashicons dashicons-bell"></span>
				<?php echo esc_html__( 'Performance Alerts', 'sitepulse-pro' ); ?>
			</h3>
			<p class="sp-pulse-card-subtitle"><?php echo esc_html__( 'Alerts when your site speed drops', 'sitepulse-pro' ); ?></p>
		</div>
		<div class="sp-flex sp-items-center sp-gap-8">
			<span class="sp-badge <?php echo $alert_settings['enabled'] ? 'sp-badge-success' : 'sp-badge-neutral'; ?>">
				<?php echo $alert_settings['enabled'] ? esc_html__( 'Alerts ON', 'sitepulse-pro' ) : esc_html__( 'Alerts OFF', 'sitepulse-pro' ); ?>
			</span>
			<?php if ( $ps_current ) : ?>
				<span class="sp-badge sp-badge-solid-accent" style="font-size:14px;padding:4px 10px;">
					<?php
					$trend_icon = $score_trend === 'up' ? '↑' : ( $score_trend === 'down' ? '↓' : '→' );
					echo esc_html( $trend_icon . ' ' . $ps_current['score'] );
					?>
				</span>
			<?php endif; ?>
		</div>
	</div>
	<div class="sp-pulse-card-body sp-p-0">
		<?php if ( ! empty( $alert_history ) ) : ?>
			<?php foreach ( $alert_history as $a ) : ?>
				<div class="sp-list-item" style="padding:10px 20px;">
					<?php
					$a_type = isset( $a['type'] ) ? $a['type'] : 'pagespeed_degradation';
					$a_time = isset( $a['timestamp'] ) ? wp_date( 'M j, H:i', $a['timestamp'] ) : '—';
					?>
					<?php if ( $a_type === 'pagespeed_degradation' ) : ?>
						<div class="sp-list-item-icon" style="background:var(--sp-danger-soft);">
							<span class="dashicons dashicons-arrow-down-alt" style="color:var(--sp-danger);font-size:16px;width:16px;height:16px;"></span>
						</div>
						<div class="sp-list-item-body">
							<p class="sp-list-item-title"><?php echo esc_html__( 'Speed score dropped', 'sitepulse-pro' ); ?></p>
							<p class="sp-text-xs sp-text-muted">
								<?php echo esc_html( ( isset( $a['previous_score'] ) ? $a['previous_score'] : '?' ) . ' → ' . ( isset( $a['current_score'] ) ? $a['current_score'] : '?' ) ); ?>
								&bull; <?php echo esc_html( $a_time ); ?>
							</p>
						</div>
						<span class="sp-badge sp-badge-danger">-<?php echo isset( $a['drop'] ) ? esc_html( $a['drop'] ) : '?'; ?> pts</span>
					<?php elseif ( $a_type === 'test_alert' ) : ?>
						<div class="sp-list-item-icon" style="background:var(--sp-info-soft, rgba(59,130,246,.12));">
							<span class="dashicons dashicons-email-alt" style="color:var(--sp-info);font-size:16px;width:16px;height:16px;"></span>
						</div>
						<div class="sp-list-item-body">
							<p class="sp-list-item-title"><?php echo esc_html__( 'Test alert email sent', 'sitepulse-pro' ); ?></p>
							<p class="sp-text-xs sp-text-muted"><?php echo esc_html( $a_time ); ?></p>
						</div>
						<span class="sp-badge sp-badge-info"><?php echo esc_html__( 'Test', 'sitepulse-pro' ); ?></span>
					<?php else : ?>
						<div class="sp-list-item-icon" style="background:var(--sp-accent-soft);">
							<span class="dashicons dashicons-calendar-alt" style="color:var(--sp-accent);font-size:16px;width:16px;height:16px;"></span>
						</div>
						<div class="sp-list-item-body">
							<p class="sp-list-item-title"><?php echo esc_html__( 'Scheduled report sent', 'sitepulse-pro' ); ?></p>
							<p class="sp-text-xs sp-text-muted"><?php echo esc_html( $a_time ); ?></p>
						</div>
						<span class="sp-badge sp-badge-accent"><?php echo esc_html__( 'Scheduled', 'sitepulse-pro' ); ?></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="sp-empty" style="padding:24px;">
				<div class="sp-empty-icon"><span class="dashicons dashicons-yes-alt" style="color:var(--sp-success);"></span></div>
				<p class="sp-empty-title"><?php echo esc_html__( 'No Alerts Yet', 'sitepulse-pro' ); ?></p>
				<p class="sp-empty-desc"><?php echo esc_html__( 'Your site speed has been steady — no drops detected.', 'sitepulse-pro' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

</div><!-- /sp-zone--data -->
<?php endif; ?>

<?php if ( ! defined( 'SITEPULSE_PRO_IS_ACTIVE' ) || ! SITEPULSE_PRO_IS_ACTIVE ) : ?>
	<?php
	$sp_slow_query_results = ( isset( $slow_queries['results'] ) && is_array( $slow_queries['results'] ) ) ? $slow_queries['results'] : array();
	$sp_slow_query_count   = count( $sp_slow_query_results );
	$sp_slow_query_success = isset( $slow_queries['success'] ) ? (bool) $slow_queries['success'] : false;
	$sp_slow_query_error   = isset( $slow_queries['error'] ) ? $slow_queries['error'] : '';
	$sp_slow_query_message = isset( $slow_queries['message'] ) ? $slow_queries['message'] : '';
	?>
	<div class="sp-zone sp-zone--data">
		<div class="sp-zone-header">
			<span class="dashicons dashicons-database"></span>
			<span class="sp-zone-title"><?php echo esc_html__( 'Queries and Alerts', 'sitepulse' ); ?></span>
		</div>
		<div class="sp-pulse-card">
			<div class="sp-pulse-card-header">
				<div>
					<h3 class="sp-pulse-card-title"><?php echo esc_html__( 'Database & Speed Checks', 'sitepulse' ); ?></h3>
					<p class="sp-pulse-card-subtitle"><?php echo esc_html__( 'Database lookups and site settings', 'sitepulse' ); ?></p>
				</div>
				<div class="sp-flex sp-items-center sp-gap-8">
					<span class="sp-badge sp-badge-info"><?php echo esc_html( $sp_slow_query_count ); ?> <?php echo esc_html__( 'Queries', 'sitepulse' ); ?></span>
					<span class="sp-badge <?php echo $sp_slow_query_success ? 'sp-badge-success' : 'sp-badge-warning'; ?>">
						<?php echo $sp_slow_query_success ? esc_html__( 'ON', 'sitepulse' ) : esc_html__( 'OFF', 'sitepulse' ); ?>
					</span>
				</div>
			</div>
			<div style="padding:12px 20px;border-bottom:1px solid var(--sp-border);">
				<div class="sp-section-buttons">
					<button type="button" class="sp-section-toggle active" data-section="sp-slow-queries-section">
						<?php echo esc_html__( 'Slow Queries', 'sitepulse' ); ?>
					</button>
					<button type="button" class="sp-section-toggle" data-section="sp-autoload-section">
						<?php echo esc_html__( 'Autoload Options', 'sitepulse' ); ?>
					</button>
				</div>
			</div>
			<div id="sp-slow-queries-section" class="sp-pulse-card-body sp-p-0">
				<?php if ( ! $sp_slow_query_success ) : ?>
					<div class="sp-empty" style="padding:20px; text-align:left;">
						<p class="sp-empty-desc" style="margin-bottom:10px;">
							<?php echo ! empty( $sp_slow_query_message ) ? esc_html( $sp_slow_query_message ) : esc_html__( 'Slow query collection is currently unavailable.', 'sitepulse' ); ?>
						</p>
						<?php if ( 'savequeries_disabled' === $sp_slow_query_error ) : ?>
							<button type="button" class="sp-btn sp-btn-sm sp-btn-primary sp-enable-savequeries">
								<?php echo esc_html__( 'Enable SAVEQUERIES', 'sitepulse' ); ?>
							</button>
						<?php endif; ?>
					</div>
				<?php elseif ( $sp_slow_query_count > 0 ) : ?>
					<?php foreach ( array_slice( $sp_slow_query_results, 0, 8 ) as $sp_query ) : ?>
						<?php
						$sp_query_sql    = isset( $sp_query['sql'] ) ? $sp_query['sql'] : '';
						$sp_query_time   = isset( $sp_query['time_ms'] ) ? (float) $sp_query['time_ms'] : 0;
						$sp_query_caller = isset( $sp_query['caller'] ) ? $sp_query['caller'] : '';
						$sp_query_badge  = $sp_query_time > 1000 ? 'sp-badge-danger' : ( $sp_query_time > 500 ? 'sp-badge-warning' : 'sp-badge-success' );
						?>
						<div class="sp-list-item" style="padding:10px 20px;">
							<div class="sp-list-item-body">
								<p class="sp-list-item-title sp-text-sm sp-cell-mono sp-cell-truncate" title="<?php echo esc_attr( $sp_query_sql ); ?>" style="color:var(--sp-primary-light); margin-bottom:2px;">
									<?php echo esc_html( wp_trim_words( $sp_query_sql, 10, '...' ) ); ?>
								</p>
								<?php if ( ! empty( $sp_query_caller ) ) : ?>
									<p class="sp-text-xs sp-text-muted sp-mb-0" style="font-family:var(--sp-font-mono);"><?php echo esc_html( $sp_query_caller ); ?></p>
								<?php endif; ?>
							</div>
							<span class="sp-badge <?php echo esc_attr( $sp_query_badge ); ?>"><?php echo esc_html( number_format( $sp_query_time, 0 ) ); ?>ms</span>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="sp-empty" style="padding:20px;">
						<div class="sp-empty-icon"><span class="dashicons dashicons-yes-alt" style="color:var(--sp-success);"></span></div>
						<p class="sp-empty-title"><?php echo esc_html__( 'No slow queries detected', 'sitepulse' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<div id="sp-autoload-section" style="display:none;">
				<div id="sp-autoload-loading" class="d-none text-center" style="padding:32px 20px;">
					<span class="spinner-border spinner-border-sm me-2" role="status"></span>
					<span class="sp-text-sm sp-text-muted"><?php echo esc_html__( 'Loading autoloaded options...', 'sitepulse' ); ?></span>
				</div>
				<div id="sp-autoload-error" class="d-none" style="padding:16px 20px;">
					<div class="alert alert-danger mb-0 small"></div>
				</div>
				<div id="sp-autoload-results" class="d-none">
					<div style="border-bottom:1px solid var(--sp-border);padding:12px 20px;display:flex;align-items:center;justify-content:space-between;">
						<small id="sp-autoload-summary" class="sp-text-xs sp-text-muted"></small>
						<button type="button" id="sp-autoload-refresh" class="sp-btn sp-btn-ghost sp-btn-sm">
							<span class="dashicons dashicons-update-alt" style="font-size:14px;width:14px;height:14px;"></span>
						</button>
					</div>
					<div class="sp-details-list" id="sp-autoload-list"></div>
				</div>
			</div>
		</div>


		<!-- Performance Warnings -->
		<div class="sp-card">
			<div class="sp-card-header">
				<div>
					<h3 class="sp-card-title">
						<span class="dashicons dashicons-performance"></span>
						<?php echo esc_html__( 'Speed Warnings', 'sitepulse' ); ?>
					</h3>
					<p class="sp-card-subtitle"><?php echo esc_html__( 'Plugins and connections that are slowing things down', 'sitepulse' ); ?></p>
				</div>
			</div>
			<div class="sp-card-body sp-p-0">
				<?php if ( ! empty( $all_slow_items ) ) : ?>
					<?php foreach ( array_slice( $all_slow_items, 0, 5 ) as $slow ) : ?>
						<div class="sp-list-item" style="padding:10px 20px;">
							<div class="sp-list-item-icon" style="background:<?php echo $slow['severity'] === 'critical' ? 'var(--sp-danger-soft)' : 'var(--sp-warning-soft)'; ?>;">
								<span class="dashicons dashicons-admin-plugins" style="color:<?php echo $slow['severity'] === 'critical' ? 'var(--sp-danger)' : 'var(--sp-warning)'; ?>;font-size:16px;width:16px;height:16px;"></span>
							</div>
							<div class="sp-list-item-body">
								<p class="sp-list-item-title"><?php echo esc_html( $slow['name'] ); ?></p>
							</div>
							<div class="sp-list-item-right">
								<span class="sp-badge <?php echo $slow['severity'] === 'critical' ? 'sp-badge-danger' : 'sp-badge-warning'; ?>">
									<?php echo esc_html( number_format( $slow['time_ms'], 0 ) ); ?>ms
								</span>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="sp-empty" style="padding:32px 20px;">
						<div class="sp-empty-icon"><span class="dashicons dashicons-yes-alt" style="color:var(--sp-success);"></span></div>
						<p class="sp-empty-title"><?php echo esc_html__( 'All Clear', 'sitepulse' ); ?></p>
						<p class="sp-empty-desc"><?php echo esc_html__( 'Everything is running smoothly! No speed issues found.', 'sitepulse' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $all_slow_items ) && count( $all_slow_items ) > 5 ) : ?>
				<div class="sp-card-footer">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpsp_sitepulse&sp_view=performance' ) ); ?>" class="sp-btn sp-btn-ghost sp-btn-sm">
						<?php echo esc_html__( 'View all', 'sitepulse' ); ?> →
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>


</div><!-- END RIGHT COLUMN -->
</div><!-- /sp-grid-2 MAIN COLUMNS -->

<!-- Tools & Quick Access -->
<div class="sp-zone sp-zone--tools">

	<div class="sp-grid sp-grid-2 sp-mb-16">
		<!-- Troubleshooting -->
		<?php
		if ( class_exists( 'Sitepulse_Pro_Plugin_Troubleshoot' ) ) :
			$troubleshoot           = Sitepulse_Pro_Plugin_Troubleshoot::get_instance();
			$is_troubleshoot_active = $troubleshoot->is_active();
			$disabled_plugins       = $troubleshoot->get_disabled_plugins();
		?>
		<div class="sp-card">
			<div class="sp-card-header">
				<div>
					<h3 class="sp-card-title">
						<span class="dashicons dashicons-admin-plugins"></span>
						<?php echo esc_html__( 'Plugin Conflict Checker', 'sitepulse-pro' ); ?>
						<span class="sp-badge sp-badge-accent"><?php echo esc_html__( 'PRO', 'sitepulse-pro' ); ?></span>
					</h3>
					<p class="sp-card-subtitle"><?php echo esc_html__( 'Safely test which plugins might be causing problems', 'sitepulse-pro' ); ?></p>
				</div>
				<span class="sp-badge <?php echo $is_troubleshoot_active ? 'sp-badge-solid-danger' : 'sp-badge-neutral'; ?>">
					<?php echo $is_troubleshoot_active ? esc_html__( 'ACTIVE', 'sitepulse-pro' ) : esc_html__( 'Inactive', 'sitepulse-pro' ); ?>
				</span>
			</div>
			<div class="sp-card-body">
				<?php if ( $is_troubleshoot_active ) : ?>
					<div class="sp-alert sp-alert-danger sp-mb-16">
						<span class="dashicons dashicons-warning"></span>
						<div>
							<strong><?php echo esc_html__( 'Testing Mode Active', 'sitepulse-pro' ); ?></strong>
							<p class="sp-text-xs" style="margin:2px 0 0;"><?php echo esc_html__( 'Some plugins are turned off temporarily for testing — only you are affected.', 'sitepulse-pro' ); ?></p>
						</div>
					</div>
					<div class="sp-flex sp-items-center sp-justify-between">
						<span class="sp-text-sm sp-fw-500"><?php echo count( $disabled_plugins ); ?> <?php echo esc_html__( 'Plugins Filtered', 'sitepulse-pro' ); ?></span>
						<button type="button" class="sp-btn sp-btn-outline sp-btn-sm sp-troubleshoot-disable">
							<?php echo esc_html__( 'Exit Mode', 'sitepulse-pro' ); ?>
						</button>
					</div>
				<?php else : ?>
					<div class="sp-empty" style="padding:16px;">
						<div class="sp-empty-icon"><span class="dashicons dashicons-admin-plugins" style="color:var(--sp-neutral);"></span></div>
						<p class="sp-empty-title" style="font-size:14px;"><?php echo esc_html__( 'Plugin Conflict Testing', 'sitepulse-pro' ); ?></p>
						<p class="sp-empty-desc" style="margin-bottom:12px;"><?php echo esc_html__( 'Safely test which plugins might be causing problems without affecting your visitors.', 'sitepulse-pro' ); ?></p>
						<p class="sp-text-xs sp-text-muted"><?php echo esc_html__( 'Start testing from the toolbar at the top of your site.', 'sitepulse-pro' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

	</div>

<!-- Quick Links -->
		<div class="sp-grid sp-grid-3 sp-mb-16">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpsp_sitepulse&sp_view=insights' ) ); ?>" class="sp-card sp-card--link">
			<div class="sp-card-body sp-flex sp-items-center sp-gap-12">
				<span class="dashicons dashicons-performance" style="color:var(--sp-accent);font-size:24px;width:24px;height:24px;"></span>
				<div>
					<p class="sp-fw-600 sp-mb-0" style="margin:0;color:var(--sp-text-primary);"><?php echo esc_html__( 'Speed Test Details', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted" style="margin:2px 0 0;"><?php echo esc_html__( 'Speed scores & improvement tips', 'sitepulse' ); ?></p>
				</div>
			</div>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpsp_sitepulse_curl_api' ) ); ?>" class="sp-card sp-card--link">
			<div class="sp-card-body sp-flex sp-items-center sp-gap-12">
				<span class="dashicons dashicons-rest-api" style="color:var(--sp-info);font-size:24px;width:24px;height:24px;"></span>
				<div>
					<p class="sp-fw-600 sp-mb-0" style="margin:0;color:var(--sp-text-primary);"><?php echo esc_html__( 'Outgoing Connections', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted" style="margin:2px 0 0;">
						<?php echo esc_html( sprintf(
							/* translators: %d: number of API events */
							__( '%d requests captured', 'sitepulse' ),
							isset( $curl_events ) ? count( $curl_events ) : 0
						) ); ?>
					</p>
				</div>
			</div>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpsp_sitepulse&sp_view=security' ) ); ?>" class="sp-card sp-card--link">
			<div class="sp-card-body sp-flex sp-items-center sp-gap-12">
				<span class="dashicons dashicons-shield" style="color:var(--sp-success);font-size:24px;width:24px;height:24px;"></span>
				<div>
					<p class="sp-fw-600 sp-mb-0" style="margin:0;color:var(--sp-text-primary);"><?php echo esc_html__( 'Security Center', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted" style="margin:2px 0 0;">
						<?php
						$vuln_count = isset( $vulnerabilities ) ? count( $vulnerabilities ) : 0;
						echo $vuln_count > 0
							? esc_html( sprintf( __( '%d vulnerabilities found', 'sitepulse' ), $vuln_count ) )
							: esc_html__( 'No issues detected', 'sitepulse' );
						?>
					</p>
				</div>
			</div>
			</a>
		</div>

		<!-- Pro is introduced after the user has received value from the free dashboard. -->
		<div class="sp-promo-banner sp-mb-16">
			<div class="sp-promo-content">
				<h3><?php echo esc_html__( 'Need history, automation, and agency workflows?', 'sitepulse' ); ?></h3>
				<p><?php echo esc_html__( 'Join the SitePulse Pro early-access list after you have explored the free monitoring tools.', 'sitepulse' ); ?></p>
			</div>
			<div class="sp-promo-action">
				<a href="https://sitepulse.me/" target="_blank" rel="noopener noreferrer" class="sp-btn-early-access">
					<span class="dashicons dashicons-star-filled"></span>
					<?php echo esc_html__( 'Explore SitePulse Pro', 'sitepulse' ); ?>
				</a>
			</div>
		</div>
	</div><!-- /sp-zone--tools -->


</div><!-- /sp-dashboard-layout -->

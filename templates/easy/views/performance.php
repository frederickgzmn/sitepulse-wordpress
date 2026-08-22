<?php
/**
 * Easy Mode — Performance view.
 *
 * Zones:
 *  1. Score Overview   — LoadSentinel, API, Load Time rings
 *  2. Bottlenecks      — Slow plugins/themes + Slow API requests
 *  3. Plugin Profiler  — RAM and loading time per plugin + Memory
 *  4. Pro Analytics    — Resource Diagnostics, Plugin Troubleshoot
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Page Header -->
<div class="sp-page-header sp-dashboard-header">
	<div class="sp-dashboard-header-left">
		<h2 class="sp-page-title"><?php echo esc_html__( 'Performance', 'sitepulse' ); ?></h2>
		<p class="sp-page-subtitle"><?php echo esc_html__( 'Find out what\'s slowing your site down and how to speed it up', 'sitepulse' ); ?></p>
	</div>
	<div class="sp-dashboard-header-right">
		<button type="button" class="sp-btn sp-btn-outline sp-btn-sm sp-refresh-performance">
			<span class="dashicons dashicons-update"></span> <?php echo esc_html__( 'Refresh', 'sitepulse' ); ?>
		</button>
	</div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 1: Score Overview
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--health">

<div class="sp-glass-card sp-p-32 sp-mb-24" style="position:relative; overflow:hidden;">
	<div style="position:absolute; top:-80px; right:-80px; width:260px; height:260px; background:var(--sp-accent); opacity:0.12; filter:blur(60px); border-radius:50%;"></div>
	<div style="position:absolute; bottom:-60px; left:-60px; width:200px; height:200px; background:var(--sp-info); opacity:0.08; filter:blur(50px); border-radius:50%;"></div>
	
	<div class="sp-hero-scores sp-flex sp-items-center sp-justify-around" style="position:relative; z-index:1; gap:24px;">
		<?php
		// Ring 1: LoadSentinel
		$ring_score = isset( $loadsentinel_score ) ? (int) $loadsentinel_score : 0;
		$ring_label = __( 'LoadSentinel', 'sitepulse' );
		$ring_size  = 'lg'; // Larger for impact
		require __DIR__ . '/../partials/score-ring.php';

		// Ring 2: API Score
		$ring_score = isset( $api_score ) ? (int) $api_score : 0;
		$ring_label = __( 'API Score', 'sitepulse' );
		$ring_size  = '';
		require __DIR__ . '/../partials/score-ring.php';

		// Ring 3: Load Time
		$ring_score = isset( $load_time_score ) ? (int) $load_time_score : 0;
		$ring_label = __( 'Load Time', 'sitepulse' );
		$ring_size  = '';
		require __DIR__ . '/../partials/score-ring.php';
		?>
	</div>
</div>

<!-- Key Metrics -->
<div class="sp-grid sp-grid-4 sp-mb-16">
	<div class="sp-card sp-card--metric">
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
	<div class="sp-card sp-card--metric">
		<?php
		$metric_icon  = 'dashicons-database';
		$metric_color = ( isset( $memory_score ) && $memory_score >= 80 ) ? 'success' : ( ( isset( $memory_score ) && $memory_score >= 60 ) ? 'warning' : 'danger' );
		$metric_value = isset( $mem['percent'] ) ? $mem['percent'] : '—';
		$metric_label = __( 'Memory', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
	<div class="sp-card sp-card--metric">
		<?php
		$metric_icon  = 'dashicons-admin-plugins';
		$metric_color = 'info';
		$metric_value = isset( $active_plugins_count ) ? $active_plugins_count : 0;
		$metric_label = __( 'Plugins', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
	<div class="sp-card sp-card--metric">
		<?php
		$metric_icon  = 'dashicons-media-default';
		$metric_color = 'accent';
		$metric_value = ( isset( $disk_write ) && ! empty( $disk_write['success'] ) ) ? $disk_write['avg_ms'] . 'ms' : '—';
		$metric_label = __( 'Storage Speed', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
</div>

</div><!-- /sp-zone--health -->


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 2: Bottlenecks
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--data">
<div class="sp-zone-header">
	<span class="dashicons dashicons-warning"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Slowdowns', 'sitepulse' ); ?></span>
</div>

<div class="sp-grid sp-grid-2 sp-mb-16">
	<!-- Slow Plugins & Themes -->
	<div class="sp-card">
		<div class="sp-card-header">
			<div>
				<h3 class="sp-card-title">
					<span class="dashicons dashicons-admin-plugins"></span>
					<?php echo esc_html__( 'Slow Plugins & Themes', 'sitepulse' ); ?>
				</h3>
				<p class="sp-card-subtitle"><?php echo esc_html__( 'Items taking longer than half a second to load', 'sitepulse' ); ?></p>
			</div>
		</div>
		<div class="sp-card-body sp-p-0">
			<?php if ( ! empty( $all_slow_items ) ) : ?>
				<?php foreach ( $all_slow_items as $item ) : ?>
					<div class="sp-list-item" style="padding:10px 20px;">
						<div class="sp-list-item-icon" style="background:<?php echo $item['severity'] === 'critical' ? 'var(--sp-danger-soft)' : 'var(--sp-warning-soft)'; ?>;">
							<span class="dashicons dashicons-admin-plugins" style="color:<?php echo $item['severity'] === 'critical' ? 'var(--sp-danger)' : 'var(--sp-warning)'; ?>;font-size:16px;width:16px;height:16px;"></span>
						</div>
						<div class="sp-list-item-body">
							<p class="sp-list-item-title"><?php echo esc_html( $item['name'] ); ?></p>
						</div>
						<div class="sp-flex sp-items-center sp-gap-8">
							<span class="sp-badge <?php echo $item['severity'] === 'critical' ? 'sp-badge-danger' : 'sp-badge-warning'; ?>">
								<?php echo esc_html( number_format( $item['time_ms'], 1 ) ); ?>ms
							</span>
							<span class="sp-badge <?php echo $item['severity'] === 'critical' ? 'sp-badge-danger' : 'sp-badge-warning'; ?>">
								<?php echo esc_html( ucfirst( $item['severity'] ) ); ?>
							</span>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="sp-empty">
					<div class="sp-empty-icon"><span class="dashicons dashicons-yes-alt" style="color:var(--sp-success);"></span></div>
					<p class="sp-empty-title"><?php echo esc_html__( 'No slowdowns found!', 'sitepulse' ); ?></p>
					<p class="sp-empty-desc"><?php echo esc_html__( 'Everything is loading quickly. Great job!', 'sitepulse' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Slow API Requests -->
	<div class="sp-card">
		<div class="sp-card-header">
			<div>
				<h3 class="sp-card-title">
					<span class="dashicons dashicons-rest-api"></span>
					<?php echo esc_html__( 'Slow Outgoing Connections', 'sitepulse' ); ?>
				</h3>
				<p class="sp-card-subtitle"><?php echo esc_html__( 'Connections to other services taking too long', 'sitepulse' ); ?></p>
			</div>
		</div>
		<div class="sp-card-body sp-p-0">
			<?php if ( ! empty( $slow_api_requests ) ) : ?>
				<?php foreach ( array_slice( $slow_api_requests, 0, 8 ) as $req ) : ?>
					<div class="sp-list-item" style="padding:10px 20px;">
						<div class="sp-list-item-icon" style="background:<?php echo $req['severity'] === 'critical' ? 'var(--sp-danger-soft)' : 'var(--sp-warning-soft)'; ?>;">
							<span class="dashicons dashicons-rest-api" style="color:<?php echo $req['severity'] === 'critical' ? 'var(--sp-danger)' : 'var(--sp-warning)'; ?>;font-size:16px;width:16px;height:16px;"></span>
						</div>
						<div class="sp-list-item-body">
							<p class="sp-list-item-title sp-cell-truncate" style="max-width:200px;" title="<?php echo esc_attr( $req['url'] ); ?>"><?php echo esc_html( $req['url'] ); ?></p>
							<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( $req['origin'] ); ?></p>
						</div>
						<div class="sp-flex sp-items-center sp-gap-8">
							<span class="sp-badge <?php echo $req['severity'] === 'critical' ? 'sp-badge-danger' : 'sp-badge-warning'; ?>">
								<?php echo esc_html( number_format( $req['time_ms'], 0 ) ); ?>ms
							</span>
							<?php if ( ! empty( $req['code'] ) ) : ?>
								<span class="sp-badge sp-badge-neutral"><?php echo esc_html( $req['code'] ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="sp-empty">
					<div class="sp-empty-icon"><span class="dashicons dashicons-yes-alt" style="color:var(--sp-success);"></span></div>
					<p class="sp-empty-title"><?php echo esc_html__( 'Connections are fast', 'sitepulse' ); ?></p>
					<p class="sp-empty-desc"><?php echo esc_html__( 'All outgoing connections are responding quickly.', 'sitepulse' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

</div><!-- /sp-zone--data -->


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 3: Plugin Profiler
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--infra">
<div class="sp-zone-header">
	<span class="dashicons dashicons-performance"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Plugin Speed Check', 'sitepulse' ); ?></span>
</div>

<div class="sp-card sp-mb-16">
	<div class="sp-card-header">
		<div>
			<h3 class="sp-card-title">
				<span class="dashicons dashicons-admin-plugins"></span>
				<?php echo esc_html__( 'Slowest Plugins', 'sitepulse' ); ?>
			</h3>
			<p class="sp-card-subtitle"><?php echo esc_html__( 'Memory and loading time for each plugin', 'sitepulse' ); ?></p>
		</div>
		<div class="sp-flex sp-items-center sp-gap-12">
			<span class="sp-badge sp-badge-neutral"><?php echo isset( $plugin_profiler_stats ) ? count( $plugin_profiler_stats ) : 0; ?> <?php echo esc_html__( 'Plugins', 'sitepulse' ); ?></span>
			<button type="button" class="sp-btn sp-btn-outline sp-btn-sm sp-collect-plugin-data">
				<span class="dashicons dashicons-update-alt"></span>
				<?php echo esc_html__( 'Measure Plugins', 'sitepulse' ); ?>
			</button>
		</div>
	</div>
	<div class="sp-card-body sp-p-0">
		<?php if ( ! empty( $plugin_profiler_stats ) ) : ?>
			<div class="sp-details-list">
				<?php
				$plugin_count = 0;
				foreach ( $plugin_profiler_stats as $plugin_stat ) :
					if ( $plugin_count >= 10 ) {
						break;
					}

					$time_ms    = $plugin_stat['avg_time'];
					$perf_color = $time_ms > 1000 ? 'sp-badge-danger' : ( $time_ms > 500 ? 'sp-badge-warning' : 'sp-badge-success' );

					$memory_formatted = class_exists( 'Sitepulse_Plugin_Profiler' )
						? Sitepulse_Plugin_Profiler::format_memory( $plugin_stat['avg_memory'] )
						: number_format( $plugin_stat['avg_memory'] / 1024, 2 ) . ' KB';

					$time_formatted = class_exists( 'Sitepulse_Plugin_Profiler' )
						? Sitepulse_Plugin_Profiler::format_time( $time_ms )
						: number_format( $time_ms, 2 ) . ' ms';
				?>
				<div class="sp-list-item" style="padding:12px 20px;">
					<div class="sp-list-item-body">
						<div class="sp-flex sp-items-center sp-gap-8 sp-mb-4">
							<span class="sp-text-sm sp-fw-600"><?php echo esc_html( $plugin_stat['plugin_name'] ); ?></span>
							<span class="sp-text-xs sp-text-muted">v<?php echo esc_html( $plugin_stat['plugin_version'] ); ?></span>
						</div>
						<div class="sp-text-xs sp-text-muted sp-cell-mono">
							<?php echo esc_html( $plugin_stat['plugin_slug'] ); ?>
						</div>
					</div>
					<div class="sp-flex sp-gap-12 sp-items-center">
						<div class="sp-text-right">
							<div class="sp-text-xs sp-text-muted sp-mb-4"><?php echo esc_html__( 'Memory', 'sitepulse' ); ?></div>
							<span class="sp-badge sp-badge-neutral"><?php echo esc_html( $memory_formatted ); ?></span>
						</div>
						<div class="sp-text-right">
							<div class="sp-text-xs sp-text-muted sp-mb-4"><?php echo esc_html__( 'Time', 'sitepulse' ); ?></div>
							<span class="sp-badge <?php echo esc_attr( $perf_color ); ?>"><?php echo esc_html( $time_formatted ); ?></span>
						</div>
					</div>
				</div>
				<?php
					$plugin_count++;
				endforeach;
				?>
			</div>
		<?php else : ?>
			<div class="sp-empty" style="padding: 32px;">
				<div class="sp-empty-icon"><span class="dashicons dashicons-analytics"></span></div>
				<p class="sp-empty-title"><?php echo esc_html__( 'No Speed Data Yet', 'sitepulse' ); ?></p>
				<p class="sp-empty-desc"><?php echo esc_html__( 'Click "Measure Plugins" above to see how each plugin affects your site speed.', 'sitepulse' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>

<!-- Memory -->
<div class="sp-card sp-mb-16">
	<div class="sp-card-header">
		<h3 class="sp-card-title"><span class="dashicons dashicons-database"></span> <?php echo esc_html__( 'Memory', 'sitepulse' ); ?></h3>
	</div>
	<div class="sp-card-body">
		<div class="sp-flex sp-justify-between sp-items-center sp-mb-8">
			<span class="sp-text-sm sp-fw-500"><?php echo esc_html__( 'Usage', 'sitepulse' ); ?></span>
			<span class="sp-badge <?php echo ( isset( $memory_score ) && $memory_score >= 80 ) ? 'sp-badge-success' : ( ( isset( $memory_score ) && $memory_score >= 60 ) ? 'sp-badge-warning' : 'sp-badge-danger' ); ?>">
				<?php echo isset( $mem['percent'] ) ? esc_html( $mem['percent'] ) : '—'; ?>
			</span>
		</div>
		<div class="sp-progress sp-progress-lg sp-mb-8">
			<?php $mup = isset( $mem_usage_percent ) ? $mem_usage_percent : 0; ?>
			<div class="sp-progress-fill" style="width:<?php echo esc_attr( $mup ); ?>%;background:<?php echo $mup > 70 ? 'var(--sp-danger)' : ( $mup > 40 ? 'var(--sp-warning)' : 'var(--sp-success)' ); ?>;"></div>
		</div>
		<p class="sp-text-xs sp-text-muted"><?php echo isset( $mem['formatted'] ) ? esc_html( $mem['formatted'] ) : ''; ?></p>
	</div>
</div>

</div><!-- /sp-zone--infra -->


<?php
// ─── Pro Performance Widgets ────────────────────────────────────────────
if ( defined( 'SITEPULSE_PRO_IS_ACTIVE' ) && SITEPULSE_PRO_IS_ACTIVE ) :
?>

<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 4: Pro Analytics
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--ai">
<div class="sp-zone-header">
	<span class="dashicons dashicons-chart-area"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Pro Analytics', 'sitepulse-pro' ); ?></span>
	<span class="sp-badge sp-badge-accent sp-zone-pro"><?php echo esc_html__( 'PRO', 'sitepulse-pro' ); ?></span>
</div>



<!-- Resource Diagnostics -->
<div class="sp-card sp-mb-16">
	<div class="sp-card-header">
		<div>
			<h3 class="sp-card-title">
				<span class="dashicons dashicons-admin-tools"></span>
				<?php echo esc_html__( 'Resource Diagnostics', 'sitepulse-pro' ); ?>
			</h3>
			<p class="sp-card-subtitle"><?php echo esc_html__( 'Server vs. site bottleneck analysis', 'sitepulse-pro' ); ?></p>
		</div>
		<button type="button" class="sp-btn sp-btn-outline sp-btn-sm sp-diagnostics-refresh-btn">
			<span class="dashicons dashicons-update"></span> <?php echo esc_html__( 'Run Health Check', 'sitepulse-pro' ); ?>
		</button>
	</div>
	<div class="sp-card-body">
		<div class="sp-resource-diagnostics-content">
			<div class="sp-loading" style="text-align: center; padding: 2rem;">
				<span class="dashicons dashicons-update sp-spin" style="font-size: 24px; width: 24px; height: 24px;"></span>
				<p class="sp-text-muted sp-text-xs" style="margin-top: 8px;">
					<?php echo esc_html__( 'Running diagnostics...', 'sitepulse-pro' ); ?>
				</p>
			</div>
		</div>
	</div>
</div>

<?php
// Plugin Troubleshoot status
if ( class_exists( 'Sitepulse_Pro_Plugin_Troubleshoot' ) ) :
	$troubleshoot = Sitepulse_Pro_Plugin_Troubleshoot::get_instance();
	$ts_active    = $troubleshoot->is_active();
	$ts_disabled  = $ts_active ? $troubleshoot->get_disabled_plugins() : array();
?>
<div class="sp-card sp-mb-16">
	<div class="sp-card-header">
		<div>
			<h3 class="sp-card-title">
				<span class="dashicons dashicons-admin-plugins"></span>
				<?php echo esc_html__( 'Plugin Troubleshoot Mode', 'sitepulse-pro' ); ?>
			</h3>
			<p class="sp-card-subtitle"><?php echo esc_html__( 'Temporarily disable plugins for your session only', 'sitepulse-pro' ); ?></p>
		</div>
		<span class="sp-badge <?php echo $ts_active ? 'sp-badge-warning' : 'sp-badge-neutral'; ?>">
			<?php echo $ts_active ? esc_html__( 'Active', 'sitepulse-pro' ) : esc_html__( 'Inactive', 'sitepulse-pro' ); ?>
		</span>
	</div>
	<div class="sp-card-body">
		<?php if ( $ts_active && ! empty( $ts_disabled ) ) : ?>
			<div class="sp-alert sp-alert-warning sp-mb-16">
				<span class="dashicons dashicons-warning"></span>
				<div>
					<strong><?php echo esc_html__( 'Troubleshoot mode is active', 'sitepulse-pro' ); ?></strong>
					<span class="sp-text-xs" style="display:block;margin-top:2px;">
						<?php echo esc_html( sprintf( _n( '%d plugin disabled for your session.', '%d plugins disabled for your session.', count( $ts_disabled ), 'sitepulse-pro' ), count( $ts_disabled ) ) ); ?>
					</span>
				</div>
			</div>
			<?php foreach ( $ts_disabled as $p_file ) :
				if ( ! function_exists( 'get_plugin_data' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}
				$p_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $p_file );
			?>
				<div class="sp-list-item" style="padding:6px 0;">
					<span class="sp-badge sp-badge-danger" style="font-size:10px;">OFF</span>
					<span class="sp-text-sm sp-fw-500" style="margin-left:8px;"><?php echo esc_html( $p_data['Name'] ); ?></span>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="sp-empty" style="padding:16px;">
				<p class="sp-empty-desc"><?php echo esc_html__( 'Use the Plugin Troubleshooting button in the admin bar to temporarily disable plugins.', 'sitepulse-pro' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

</div><!-- /sp-zone--ai (Pro Analytics) -->

<?php endif; // SITEPULSE_PRO_IS_ACTIVE ?>

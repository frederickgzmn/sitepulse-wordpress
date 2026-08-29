<?php
/**
 * Easy Mode — Resource Load (LoadSentinel) view.
 *
 * Zones:
 *  1. Controls & Stats — Profiler toggle, single-page mode, key stats
 *  2. Event Footprint  — Detailed hook events table
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_stats = isset( $stats ) ? $stats : array();
$sp_single = isset( $single_load_events ) ? $single_load_events : false;
$sp_snap   = isset( $snapshot_time ) ? $snapshot_time : null;
$sp_pi_count = isset( $active_plugins_count ) ? (int) $active_plugins_count : 0;
$sp_profiler_on = get_option( 'sitepulse_profiler_enabled', null );
$sp_report_mode = get_option( 'sitepulse_report_mode_active' );
$sp_tracked_pid = get_option( 'sitepulse_current_tracked_pageid' );
?>

<!-- Page Header -->
<div class="sp-page-header sp-dashboard-header">
	<div class="sp-dashboard-header-left">
		<h2 class="sp-page-title"><?php echo esc_html__( 'Activity Monitor', 'sitepulse' ); ?></h2>
		<p class="sp-page-subtitle"><?php echo esc_html__( 'Track what\'s running behind the scenes and how long each action takes', 'sitepulse' ); ?></p>
	</div>
	<div class="sp-dashboard-header-right">
		<?php if ( $sp_single ) : ?>
			<span class="sp-badge sp-badge-warning"><?php echo esc_html__( 'Single Page Mode', 'sitepulse' ); ?></span>
		<?php endif; ?>
	</div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 1: Controls & Stats
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--health">

<div class="sp-grid sp-grid-2 sp-mb-16">
	<!-- Control Panel -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-admin-settings"></span> <?php echo esc_html__( 'Control Panel', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body">
			<!-- Profiler Toggle -->
			<div class="sp-flex sp-justify-between sp-items-center sp-mb-16">
				<div>
					<p class="sp-text-sm sp-fw-500 sp-mb-0"><?php echo esc_html__( 'Activity Tracker', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html__( 'Record how long each action takes', 'sitepulse' ); ?></p>
				</div>
				<label class="sp-toggle">
					<input type="checkbox" class="sp_profiler" <?php checked( $sp_profiler_on, '1' ); ?>>
					<span class="sp-toggle-track"></span>
				</label>
			</div>

			<!-- Single Page Mode -->
			<div class="sp-flex sp-justify-between sp-items-center sp-mb-16">
				<div>
					<p class="sp-text-sm sp-fw-500 sp-mb-0"><?php echo esc_html__( 'Focus on One Page', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html__( 'Only track activity on a specific page', 'sitepulse' ); ?></p>
				</div>
				<span class="sp-badge <?php echo $sp_report_mode ? 'sp-badge-success' : 'sp-badge-neutral'; ?>">
					<?php echo $sp_report_mode ? esc_html__( 'Active', 'sitepulse' ) : esc_html__( 'Off', 'sitepulse' ); ?>
				</span>
			</div>

			<?php if ( $sp_report_mode && $sp_tracked_pid ) : ?>
				<div class="sp-alert sp-alert-info">
					<span class="dashicons dashicons-admin-page"></span>
					<div>
						<strong><?php echo esc_html__( 'Watching Page:', 'sitepulse' ); ?></strong>
						<span class="sp-text-xs" style="display:block;"><?php echo esc_html( get_the_title( (int) $sp_tracked_pid ) ); ?> (ID: <?php echo esc_html( $sp_tracked_pid ); ?>)</span>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Statistics -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-chart-bar"></span> <?php echo esc_html__( 'Statistics', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body">
			<div class="sp-flex sp-justify-between sp-items-center sp-mb-12">
				<span class="sp-text-sm"><?php echo esc_html__( 'Actions Recorded', 'sitepulse' ); ?></span>
				<span class="sp-badge sp-badge-accent"><?php echo esc_html( count( $sp_stats ) ); ?></span>
			</div>
			<div class="sp-flex sp-justify-between sp-items-center sp-mb-12">
				<span class="sp-text-sm"><?php echo esc_html__( 'Active Plugins', 'sitepulse' ); ?></span>
				<span class="sp-badge sp-badge-info"><?php echo esc_html( $sp_pi_count ); ?></span>
			</div>
			<div class="sp-flex sp-justify-between sp-items-center sp-mb-12">
				<span class="sp-text-sm"><?php echo esc_html__( 'Snapshot', 'sitepulse' ); ?></span>
				<span class="sp-text-xs sp-text-muted"><?php echo $sp_snap ? esc_html( wp_date( 'M j, g:i A', strtotime( $sp_snap ) ) ) : '—'; ?></span>
			</div>
			<?php if ( ! empty( $sp_stats ) ) : ?>
				<button type="button" class="sp-btn sp-btn-outline sp-btn-sm sp-clear-profiler-btn sp-mt-8" style="width:100%;">
					<span class="dashicons dashicons-trash"></span> <?php echo esc_html__( 'Clear Data', 'sitepulse' ); ?>
				</button>
			<?php endif; ?>
		</div>
	</div>
</div>

</div><!-- /sp-zone--health -->


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 2: Event Footprint
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--infra">
<div class="sp-zone-header">
	<span class="dashicons dashicons-performance"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Activity Details', 'sitepulse' ); ?></span>
</div>

<div class="sp-card sp-mb-16">
	<div class="sp-card-body sp-p-0">
		<?php if ( ! empty( $sp_stats ) ) : ?>
			<?php
			// Sort by total time descending
			usort( $sp_stats, function( $a, $b ) {
				$a_time = isset( $a['total_ms'] ) ? $a['total_ms'] : 0;
				$b_time = isset( $b['total_ms'] ) ? $b['total_ms'] : 0;
				return $b_time <=> $a_time;
			});
			?>
			<div class="sp-table-responsive">
				<table class="sp-table sp-table-hover">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Source', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Hook', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'File', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Signature', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Calls', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Current Load Time', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Avg Time', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Status', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Last run', 'sitepulse' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( array_slice( $sp_stats, 0, 50 ) as $footprint ) :
							$fp_time = isset( $footprint['total_ms'] ) ? (float) $footprint['total_ms'] : 0;
							$load_time = isset( $footprint['current_load_time'] ) ? (float) $footprint['current_load_time'] : 0;
							$fp_calls = isset( $footprint['calls'] ) ? (int) $footprint['calls'] : 1;
							$fp_avg = $fp_calls > 0 ? round( $fp_time / $fp_calls, 1 ) : 0;
						?>
						<tr>
							<td class="sp-text-xs sp-fw-500"><?php echo esc_html( isset( $footprint['source'] ) ? $footprint['source'] : '—' ); ?></td>
							<td class="sp-cell-mono sp-text-xs sp-cell-truncate" style="max-width:140px;" title="<?php echo esc_attr( isset( $footprint['hook'] ) ? $footprint['hook'] : '' ); ?>"><?php echo esc_html( isset( $footprint['hook'] ) ? $footprint['hook'] : '—' ); ?></td>
							<td class="sp-cell-mono sp-text-xs sp-cell-truncate" style="max-width:140px;" title="<?php echo esc_attr( isset( $footprint['fileline'] ) ? $footprint['fileline'] : '' ); ?>"><?php echo esc_html( isset( $footprint['fileline'] ) ? $footprint['fileline'] : '—' ); ?></td>
							<td class="sp-cell-mono sp-text-xs sp-cell-truncate" style="max-width:140px;" title="<?php echo esc_attr( isset( $footprint['sig'] ) ? $footprint['sig'] : '' ); ?>"><?php echo esc_html( isset( $footprint['sig'] ) ? $footprint['sig'] : '—' ); ?></td>
							<td class="sp-text-center"><?php echo esc_html( $fp_calls ); ?></td>
							<td class="sp-text-center">
								<?php
								// Microseconds to seconds
								if ( $load_time > 1000 ) {
									$load_time = round( $load_time / 1000, 1 );
									$unit = 's';
								} else {
									$unit = 'ms';
								}

								echo esc_html( round( $load_time, 1 ) );
								?>
								<span class="sp-text-xs sp-text-muted"><?php echo esc_html( $unit ); ?></span></td>
							<td>
								<span class="sp-badge <?php echo $fp_avg > 500 ? 'sp-badge-danger' : ( $fp_avg > 100 ? 'sp-badge-warning' : 'sp-badge-success' ); ?>">
									<?php echo esc_html( $fp_avg ); ?>ms
								</span>
							</td>
							<td>
								<?php
								if ( isset( $footprint['key'] ) ) {
									// Check activity status
									$is_active = Sitepulse_Profiler::is_active( $footprint['key'] );

									if ( $is_active ) : ?>
										<span class="sp-badge sp-badge-success">
											<?php echo esc_html__( 'Active', 'sitepulse' ); ?>
										</span>
									<?php else : ?>
										<span class="sp-badge sp-badge-neutral">
											<?php echo esc_html__( 'Inactive since ' . SITEPULSE_SETTINGS_PROFILER_INACTIVE_AFTER_SECONDS . 's', 'sitepulse' ); ?>
										</span>
									<?php endif; ?>

								<?php
								} else {
								?>
									<span class="sp-badge sp-badge-warning">
										<?php echo esc_html__( 'Unknown', 'sitepulse' ); ?>
									</span>
								<?php } ?>
							</td>
							<td>
								<?php
									if ( isset( $footprint['date_time'] ) ) {
										// time ago format
										$date_time = date_create_immutable(
											$footprint['date_time'],
											wp_timezone()
										);

										$time_ago = human_time_diff( $date_time->getTimestamp(), time() );
										echo esc_html( $time_ago . ' ago' );
									}
								?>
								<br>
								<small>
									<?php 
										if ( isset( $footprint['current_url'] ) && ! empty( $footprint['current_url'] ) ) {
											// limit characters
											echo esc_html( mb_strimwidth( $footprint['current_url'], 0, 30, '...' ) ); 
										}
									?>
								</small>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<div class="sp-empty" style="padding: 48px;">
				<div class="sp-empty-icon"><span class="dashicons dashicons-performance" style="font-size:36px;width:36px;height:36px;color:var(--sp-info);"></span></div>
				<p class="sp-empty-title"><?php echo esc_html__( 'No Activity Recorded Yet', 'sitepulse' ); ?></p>
				<p class="sp-empty-desc"><?php echo esc_html__( 'Turn on the Activity Monitor to start tracking what happens behind the scenes.', 'sitepulse' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>

</div><!-- /sp-zone--infra -->

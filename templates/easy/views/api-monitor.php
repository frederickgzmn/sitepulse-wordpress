<?php
/**
 * Easy Mode — External Requests view.
 *
 * Zones:
 *  1. Overview    — 4 metric cards (requests, slow, plugins, timestamp)
 *  2. Request Log — Full events table
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_events = isset( $curl_events ) ? $curl_events : array();
$sp_snap   = isset( $snapshot_time ) ? $snapshot_time : null;
$sp_plugins_ct = isset( $active_plugins_count ) ? (int) $active_plugins_count : 0;

$sp_total_reqs = count( $sp_events );
$sp_slow_reqs  = 0;
foreach ( $sp_events as $ev ) {
	if ( isset( $ev['elapsed'] ) && $ev['elapsed'] > 0.5 ) {
		$sp_slow_reqs++;
	}
}
?>

<!-- Page Header -->
<div class="sp-page-header sp-dashboard-header">
	<div class="sp-dashboard-header-left">
		<h2 class="sp-page-title"><?php echo esc_html__( 'External Requests', 'sitepulse' ); ?></h2>
		<p class="sp-page-subtitle"><?php echo esc_html__( 'Slow calls your site makes to outside services: updates, analytics, payment and shipping APIs, and more', 'sitepulse' ); ?></p>
	</div>
</div>

<div class="sp-alert sp-alert-info sp-mb-16">
	<span class="dashicons dashicons-search"></span>
	<div>
		<strong><?php echo esc_html__( 'Which page makes these requests?', 'sitepulse' ); ?></strong>
		<span class="sp-text-xs" style="display:block;margin-top:2px;"><?php echo esc_html__( 'This log collects slow requests from your whole site. Page Analysis lists every request one page makes while it loads.', 'sitepulse' ); ?></span>
		<a class="sp-text-xs sp-fw-600" href="<?php echo esc_url( Sitepulse_Page_Analysis::admin_link() ); ?>"><?php echo esc_html__( 'Analyze a page', 'sitepulse' ); ?> &rarr;</a>
	</div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 1: Overview
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--health">

<div class="sp-grid sp-grid-4 sp-mb-16">
	<div class="sp-card sp-card--metric">
		<?php
		$metric_icon  = 'dashicons-rest-api';
		$metric_color = 'accent';
		$metric_value = $sp_total_reqs;
		$metric_label = __( 'Total Connections', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
	<div class="sp-card sp-card--metric">
		<?php
		$metric_icon  = 'dashicons-warning';
		$metric_color = $sp_slow_reqs > 0 ? 'danger' : 'success';
		$metric_value = $sp_slow_reqs;
		$metric_label = __( 'Slow Connections', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
	<div class="sp-card sp-card--metric">
		<?php
		$metric_icon  = 'dashicons-admin-plugins';
		$metric_color = 'info';
		$metric_value = $sp_plugins_ct;
		$metric_label = __( 'Active Plugins', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
	<div class="sp-card sp-card--metric">
		<?php
		$metric_icon  = 'dashicons-clock';
		$metric_color = 'accent';
		$metric_value = $sp_snap ? wp_date( 'g:i A', strtotime( $sp_snap ) ) : '—';
		$metric_label = __( 'Last Checked', 'sitepulse' );
		$metric_trend = '';
		require __DIR__ . '/../partials/metric-card.php';
		?>
	</div>
</div>

</div><!-- /sp-zone--health -->


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 2: Request Log
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--infra">
<div class="sp-zone-header">
	<span class="dashicons dashicons-list-view"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Connection Log', 'sitepulse' ); ?></span>
	<span class="sp-badge sp-badge-neutral sp-ml-8"><?php echo esc_html( $sp_total_reqs ); ?></span>
</div>

<div class="sp-card sp-mb-16">
	<div class="sp-card-header">
		<h3 class="sp-card-title"><span class="dashicons dashicons-rest-api"></span> <?php echo esc_html__( 'Connection Details', 'sitepulse' ); ?></h3>
		<?php if ( ! empty( $sp_events ) ) : ?>
			<button type="button" class="sp-btn sp-btn-outline sp-btn-sm sp-clear-curl-btn">
				<span class="dashicons dashicons-trash"></span> <?php echo esc_html__( 'Clear', 'sitepulse' ); ?>
			</button>
		<?php endif; ?>
	</div>
	<div class="sp-card-body sp-p-0">
		<?php if ( ! empty( $sp_events ) ) : ?>
			<div class="sp-table-responsive">
				<table class="sp-table sp-table-hover">
					<thead>
						<tr>
							<th>#</th>
							<th><?php echo esc_html__( 'Destination', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Source', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Type', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Status', 'sitepulse' ); ?></th>
							<th><?php echo esc_html__( 'Duration', 'sitepulse' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$sp_ev_index = 0;
						foreach ( $sp_events as $ev ) :
							$sp_ev_index++;
							$ev_time = isset( $ev['elapsed'] ) ? (float) $ev['elapsed'] : 0;
							$ev_ms   = round( $ev_time * 1000, 0 );
							$ev_url  = isset( $ev['url'] ) ? Sitepulse_Utils::redact_url_query( $ev['url'] ) : '';
						?>
						<tr>
							<td class="sp-text-muted"><?php echo esc_html( $sp_ev_index ); ?></td>
							<td class="sp-cell-mono sp-cell-truncate" style="max-width:240px;" title="<?php echo esc_attr( $ev_url ); ?>"><?php echo esc_html( $ev_url ); ?></td>
							<td class="sp-text-xs"><?php echo esc_html( isset( $ev['origin'] ) ? $ev['origin'] : '—' ); ?></td>
							<td><span class="sp-badge sp-badge-neutral"><?php echo esc_html( isset( $ev['method'] ) ? strtoupper( $ev['method'] ) : 'GET' ); ?></span></td>
							<td>
								<?php
								$ev_code = isset( $ev['code'] ) ? (int) $ev['code'] : 0;
								$code_cls = ( $ev_code >= 200 && $ev_code < 300 ) ? 'sp-badge-success' : ( $ev_code >= 400 ? 'sp-badge-danger' : 'sp-badge-warning' );
								?>
								<span class="sp-badge <?php echo esc_attr( $code_cls ); ?>"><?php echo esc_html( $ev_code ? $ev_code : '—' ); ?></span>
							</td>
							<td>
								<span class="sp-badge <?php echo $ev_ms > 2000 ? 'sp-badge-danger' : ( $ev_ms > 500 ? 'sp-badge-warning' : 'sp-badge-success' ); ?>">
									<?php echo esc_html( $ev_ms ); ?>ms
								</span>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<div class="sp-empty" style="padding: 48px;">
				<div class="sp-empty-icon"><span class="dashicons dashicons-rest-api" style="font-size:36px;width:36px;height:36px;color:var(--sp-info);"></span></div>
				<p class="sp-empty-title"><?php echo esc_html__( 'No Connections Recorded Yet', 'sitepulse' ); ?></p>
				<p class="sp-empty-desc"><?php echo esc_html__( 'Turn on "External Requests" tracking on the Dashboard to see what your site connects to.', 'sitepulse' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>

</div><!-- /sp-zone--infra -->

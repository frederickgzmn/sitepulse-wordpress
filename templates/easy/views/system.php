<?php
/**
 * Easy Mode — System Information view.
 *
 * Zones:
 *  1. Core Environment   — WordPress, PHP, server info + Active plugins
 *  2. Infrastructure     — Disk I/O + Whitelabel
 *  3. Pro Status       — MU-Plugin status, Pro version & updates
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_active_plugins = isset( $active_plugins ) ? $active_plugins : get_option( 'active_plugins', array() );
$sp_settings_inst  = class_exists( 'Sitepulse_Settings' ) ? Sitepulse_Settings::getInstance() : null;
$sp_status_info    = isset( $status_info ) ? $status_info : ( $sp_settings_inst ? $sp_settings_inst->get_status_info() : array() );
?>

<!-- Page Header -->
<div class="sp-page-header sp-dashboard-header">
	<div class="sp-dashboard-header-left">
		<h2 class="sp-page-title"><?php echo esc_html__( 'Site Information', 'sitepulse' ); ?></h2>
		<p class="sp-page-subtitle"><?php echo esc_html__( 'Everything about your site\'s setup, plugins, and hosting', 'sitepulse' ); ?></p>
	</div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 1: Core Environment
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--infra">
<div class="sp-zone-header">
	<span class="dashicons dashicons-admin-generic"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Your Site\'s Setup', 'sitepulse' ); ?></span>
</div>

<div class="sp-grid sp-grid-2 sp-mb-16">
	<!-- Environment Table -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-admin-tools"></span> <?php echo esc_html__( 'Technical Details', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body sp-p-0">
			<table class="sp-table sp-table-striped">
				<tbody>
					<tr><td class="sp-fw-500"><?php echo esc_html__( 'WordPress', 'sitepulse' ); ?></td><td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td></tr>
					<tr><td class="sp-fw-500"><?php echo esc_html__( 'PHP', 'sitepulse' ); ?></td><td><?php echo esc_html( phpversion() ); ?></td></tr>
					<tr><td class="sp-fw-500"><?php echo esc_html__( 'Server', 'sitepulse' ); ?></td><td><?php echo esc_html( isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : __( 'Unknown', 'sitepulse' ) ); ?></td></tr>
					<tr><td class="sp-fw-500"><?php echo esc_html__( 'Memory Limit', 'sitepulse' ); ?></td><td><?php echo esc_html( ini_get( 'memory_limit' ) ); ?></td></tr>
					<tr><td class="sp-fw-500"><?php echo esc_html__( 'Max Run Time', 'sitepulse' ); ?></td><td><?php echo esc_html( ini_get( 'max_execution_time' ) ); ?>s</td></tr>
					<tr><td class="sp-fw-500"><?php echo esc_html__( 'Database', 'sitepulse' ); ?></td><td><?php global $wpdb; echo esc_html( $wpdb->db_version() ); ?></td></tr>
					<tr><td class="sp-fw-500"><?php echo esc_html__( 'Theme', 'sitepulse' ); ?></td><td><?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?></td></tr>
					<tr><td class="sp-fw-500"><?php echo esc_html__( 'Multisite', 'sitepulse' ); ?></td><td><?php echo is_multisite() ? esc_html__( 'Yes', 'sitepulse' ) : esc_html__( 'No', 'sitepulse' ); ?></td></tr>
					<?php if ( isset( $mem ) && ! empty( $mem['formatted'] ) ) : ?>
						<tr><td class="sp-fw-500"><?php echo esc_html__( 'Memory Usage', 'sitepulse' ); ?></td><td><?php echo esc_html( $mem['formatted'] ); ?> (<?php echo esc_html( $mem['percent'] ); ?>)</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Active Plugins -->
	<div class="sp-card">
		<div class="sp-card-header">
			<div>
				<h3 class="sp-card-title"><span class="dashicons dashicons-admin-plugins"></span> <?php echo esc_html__( 'Active Plugins', 'sitepulse' ); ?></h3>
			</div>
			<span class="sp-badge sp-badge-info"><?php echo esc_html( count( $sp_active_plugins ) ); ?></span>
		</div>
		<div class="sp-card-body sp-p-0" style="max-height:320px; overflow-y:auto;">
			<?php
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			foreach ( $sp_active_plugins as $plugin_file ) :
				$p_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );
			?>
				<div class="sp-list-item" style="padding:8px 20px;">
					<div class="sp-list-item-body">
						<p class="sp-text-sm sp-fw-500 sp-mb-0"><?php echo esc_html( $p_data['Name'] ); ?></p>
						<p class="sp-text-xs sp-text-muted sp-mb-0">v<?php echo esc_html( $p_data['Version'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

</div><!-- /sp-zone--infra -->

<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 2: Infrastructure
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--ai">
<div class="sp-zone-header">
	<span class="dashicons dashicons-networking"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Advanced Info', 'sitepulse' ); ?></span>
</div>

<div class="sp-grid sp-grid-2 sp-mb-16">
	<!-- Disk I/O Diagnostics -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-media-default"></span> <?php echo esc_html__( 'Storage Speed Test', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body">
			<?php
			$sp_disk = ( isset( $disk_write ) && is_array( $disk_write ) ) ? $disk_write : get_transient( 'sitepulse_disk_write_test_result' );
			if ( ! empty( $sp_disk ) && ! empty( $sp_disk['success'] ) ) : ?>
				<div class="sp-flex sp-justify-between sp-items-center sp-mb-8">
					<span class="sp-text-sm sp-fw-500"><?php echo esc_html__( 'Average Write Speed', 'sitepulse' ); ?></span>
					<span class="sp-badge sp-badge-info"><?php echo esc_html( $sp_disk['avg_ms'] ); ?>ms</span>
				</div>
				<div class="sp-flex sp-justify-between sp-items-center sp-mb-8">
					<span class="sp-text-sm sp-fw-500"><?php echo esc_html__( 'Test Rounds', 'sitepulse' ); ?></span>
					<span class="sp-text-sm"><?php echo esc_html( $sp_disk['iterations'] ); ?></span>
				</div>
				<p class="sp-text-xs sp-text-muted"><?php echo esc_html( sprintf( __( 'Directory: %s', 'sitepulse' ), $sp_disk['dir'] ) ); ?></p>
				<?php if ( isset( $sp_disk['min_ms'], $sp_disk['max_ms'] ) ) : ?>
					<p class="sp-text-xs sp-text-muted sp-mb-0">
						<?php echo esc_html( sprintf( __( 'Min/Max: %1$s ms / %2$s ms', 'sitepulse' ), number_format( (float) $sp_disk['min_ms'], 2 ), number_format( (float) $sp_disk['max_ms'], 2 ) ) ); ?>
					</p>
				<?php endif; ?>
			<?php elseif ( ! empty( $sp_disk ) && isset( $sp_disk['success'] ) && ! $sp_disk['success'] ) : ?>
				<div class="sp-flex sp-justify-between sp-items-center sp-mb-8">
					<span class="sp-text-sm sp-fw-500"><?php echo esc_html__( 'Status', 'sitepulse' ); ?></span>
					<span class="sp-badge sp-badge-danger"><?php echo esc_html__( 'FAIL', 'sitepulse' ); ?></span>
				</div>
				<?php if ( ! empty( $sp_disk['error'] ) ) : ?>
					<p class="sp-text-xs sp-text-muted"><?php echo esc_html( sprintf( __( 'Error: %s', 'sitepulse' ), $sp_disk['error'] ) ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $sp_disk['dir'] ) ) : ?>
					<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( sprintf( __( 'Directory: %s', 'sitepulse' ), $sp_disk['dir'] ) ); ?></p>
				<?php endif; ?>
			<?php else : ?>
				<p class="sp-text-sm sp-text-muted"><?php echo esc_html__( 'Storage speed test hasn\'t run yet.', 'sitepulse' ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Whitelabel Status -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-admin-appearance"></span> <?php echo esc_html__( 'Custom Branding', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body">
			<?php if ( class_exists( 'Sitepulse_Whitelabel_Service' ) && Sitepulse_Whitelabel_Service::is_whitelabel_enabled() ) : ?>
				<div class="sp-flex sp-justify-between sp-items-center sp-mb-8">
					<span class="sp-text-sm sp-fw-500"><?php echo esc_html__( 'Status', 'sitepulse' ); ?></span>
					<span class="sp-badge sp-badge-success"><?php echo esc_html__( 'Active', 'sitepulse' ); ?></span>
				</div>
				<div class="sp-flex sp-justify-between sp-items-center sp-mb-8">
					<span class="sp-text-sm sp-fw-500"><?php echo esc_html__( 'Custom Name', 'sitepulse' ); ?></span>
					<span class="sp-text-sm"><?php echo esc_html( Sitepulse_Whitelabel_Service::get_plugin_name() ); ?></span>
				</div>
			<?php else : ?>
				<div class="sp-flex sp-justify-between sp-items-center sp-mb-8">
					<span class="sp-text-sm sp-fw-500"><?php echo esc_html__( 'Status', 'sitepulse' ); ?></span>
					<span class="sp-badge sp-badge-neutral"><?php echo esc_html__( 'Inactive', 'sitepulse' ); ?></span>
				</div>
				<p class="sp-text-xs sp-text-muted"><?php echo esc_html__( 'Custom branding is not enabled.', 'sitepulse' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

</div><!-- /sp-zone--ai -->


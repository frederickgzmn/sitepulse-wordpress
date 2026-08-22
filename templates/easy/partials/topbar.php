<?php
/**
 * Easy Mode — Top bar with breadcrumb, theme toggle, and quick actions.
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_view = Sitepulse_Easy_Mode::get_current_view();
$theme        = Sitepulse_Easy_Mode::get_theme();

$view_labels = array(
	'home'          => __( 'Dashboard', 'sitepulse' ),
	'performance'   => __( 'Speed & Performance', 'sitepulse' ),
	'resource-load' => __( 'Activity Monitor', 'sitepulse' ),
	'security'      => __( 'Security & Stability', 'sitepulse' ),
	'insights'      => __( 'Speed Test Results', 'sitepulse' ),
	'api-monitor'   => __( 'Outgoing Connections', 'sitepulse' ),
	'system'        => __( 'Site Info', 'sitepulse' ),
	'settings'      => __( 'Settings', 'sitepulse' ),
);

$current_label = isset( $view_labels[ $current_view ] ) ? $view_labels[ $current_view ] : __( 'Dashboard', 'sitepulse' );
?>
<div class="sp-easy-topbar">
	<div class="sp-topbar-left">
		<button type="button" class="sp-topbar-burger" aria-label="<?php echo esc_attr__( 'Toggle menu', 'sitepulse' ); ?>">
			<span class="dashicons dashicons-menu" style="font-size:20px;width:20px;height:20px;"></span>
		</button>
		<div class="sp-topbar-breadcrumb">
			<?php echo esc_html( Sitepulse_Whitelabel_Service::get_plugin_name() ); ?>
			&nbsp;/&nbsp;
			<strong><?php echo esc_html( $current_label ); ?></strong>
		</div>
	</div>

	<div class="sp-topbar-right" style="display: flex; gap: 8px; align-items: center;">
		<?php if ( $current_view === 'home' ) : ?>
			<button type="button" class="sp-theme-btn sp-easy-clear-data" title="<?php echo esc_attr__( 'Reset collected data', 'sitepulse' ); ?>" style="margin-right: 8px; background: transparent; border: 1px solid var(--sp-border); color: var(--sp-danger);">
				<span class="dashicons dashicons-trash"></span>
			</button>
		<?php endif; ?>

		<div class="sp-theme-switch">
			<button type="button" class="sp-theme-btn <?php echo $theme === 'light' ? 'active' : ''; ?>" data-theme="light" title="<?php echo esc_attr__( 'Light mode', 'sitepulse' ); ?>">
				<span class="dashicons dashicons-lightbulb"></span>
			</button>
			<button type="button" class="sp-theme-btn <?php echo $theme === 'dark' ? 'active' : ''; ?>" data-theme="dark" title="<?php echo esc_attr__( 'Dark mode', 'sitepulse' ); ?>">
				<span class="dashicons dashicons-star-empty"></span>
			</button>
		</div>
	</div>
</div>

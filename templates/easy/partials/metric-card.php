<?php
/**
 * Easy Mode — Reusable metric card component.
 *
 * Usage:
 *   $metric_icon   = 'dashicons-admin-plugins';
 *   $metric_color  = 'accent'; // accent, success, warning, danger, info
 *   $metric_value  = '27';
 *   $metric_label  = 'Active Plugins';
 *   $metric_trend  = ''; // optional: "+2 since last week"
 *   require __DIR__ . '/metric-card.php';
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$metric_icon  = isset( $metric_icon ) ? $metric_icon : 'dashicons-marker';
$metric_color = isset( $metric_color ) ? $metric_color : 'accent';
$metric_value = isset( $metric_value ) ? $metric_value : '—';
$metric_label = isset( $metric_label ) ? $metric_label : '';
$metric_trend = isset( $metric_trend ) ? $metric_trend : '';

// Map color name to CSS variables
$color_map = array(
	'accent'  => array( 'bg' => 'var(--sp-accent-soft)', 'fg' => 'var(--sp-accent)' ),
	'success' => array( 'bg' => 'var(--sp-success-soft)', 'fg' => 'var(--sp-success)' ),
	'warning' => array( 'bg' => 'var(--sp-warning-soft)', 'fg' => 'var(--sp-warning)' ),
	'danger'  => array( 'bg' => 'var(--sp-danger-soft)', 'fg' => 'var(--sp-danger)' ),
	'info'    => array( 'bg' => 'var(--sp-info-soft)', 'fg' => 'var(--sp-info)' ),
);

$colors = isset( $color_map[ $metric_color ] ) ? $color_map[ $metric_color ] : $color_map['accent'];
?>
<div class="sp-metric">
	<div class="sp-metric-icon" style="background:<?php echo $colors['bg']; ?>; color:<?php echo $colors['fg']; ?>;">
		<span class="dashicons <?php echo esc_attr( $metric_icon ); ?>"></span>
	</div>
	<div class="sp-metric-body">
		<div class="sp-metric-value"><?php echo esc_html( $metric_value ); ?></div>
		<?php if ( $metric_label ) : ?>
			<div class="sp-metric-label"><?php echo esc_html( $metric_label ); ?></div>
		<?php endif; ?>
		<?php if ( $metric_trend ) : ?>
			<div class="sp-metric-trend"><?php echo esc_html( $metric_trend ); ?></div>
		<?php endif; ?>
	</div>
</div>

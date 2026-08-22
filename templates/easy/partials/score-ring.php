<?php
/**
 * Easy Mode — Reusable score ring component.
 *
 * Usage:
 *   $ring_score = 85;
 *   $ring_label = 'Overall';
 *   $ring_size  = ''; // '' (default), 'lg', 'sm'
 *   require __DIR__ . '/score-ring.php';
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ring_score = isset( $ring_score ) ? (int) $ring_score : 0;
$ring_label = isset( $ring_label ) ? $ring_label : '';
$ring_size  = isset( $ring_size ) ? $ring_size : '';

// Status text
if ( $ring_score >= 80 ) {
	$ring_status_text  = __( 'Excellent', 'sitepulse' );
	$ring_status_class = 'sp-badge-success';
} elseif ( $ring_score >= 60 ) {
	$ring_status_text  = __( 'Good', 'sitepulse' );
	$ring_status_class = 'sp-badge-info';
} elseif ( $ring_score >= 40 ) {
	$ring_status_text  = __( 'Fair', 'sitepulse' );
	$ring_status_class = 'sp-badge-warning';
} else {
	$ring_status_text  = __( 'Needs Work', 'sitepulse' );
	$ring_status_class = 'sp-badge-danger';
}

$size_class = '';
if ( $ring_size === 'lg' ) {
	$size_class = 'sp-ring-lg';
} elseif ( $ring_size === 'sm' ) {
	$size_class = 'sp-ring-sm';
}
?>
<div class="sp-score-ring-wrap">
	<div class="sp-score-ring <?php echo esc_attr( $size_class ); ?>" data-score="<?php echo esc_attr( $ring_score ); ?>">
		<svg viewBox="0 0 100 100">
			<circle class="sp-score-ring-bg" cx="50" cy="50" r="42" />
			<circle class="sp-score-ring-fill" cx="50" cy="50" r="42" />
		</svg>
		<span class="sp-score-ring-value"><?php echo esc_html( $ring_score ); ?></span>
	</div>
	<?php if ( $ring_label ) : ?>
		<span class="sp-score-ring-label"><?php echo esc_html( $ring_label ); ?></span>
	<?php endif; ?>
	<span class="sp-score-ring-status <?php echo esc_attr( $ring_status_class ); ?>"><?php echo esc_html( $ring_status_text ); ?></span>
</div>

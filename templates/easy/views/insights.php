<?php
/**
 * Easy Mode — PageSpeed Insights view.
 *
 * Zones:
 *  1. Performance Score — Ring + screenshot hero
 *  2. Core Web Vitals   — 6 metric cards
 *  3. Recommendations   — Opportunities + Diagnostics
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
		<h2 class="sp-page-title"><?php echo esc_html__( 'Website Speed Test', 'sitepulse' ); ?></h2>
		<p class="sp-page-subtitle"><?php echo esc_html__( 'See how fast your site loads and get tips to make it faster', 'sitepulse' ); ?></p>
	</div>
</div>

<?php if ( empty( $has_valid_pagespeed ) || empty( $ps_data ) ) : ?>

<!-- Empty State -->
<div class="sp-zone sp-zone--ai">
	<div class="sp-card sp-card--hero">
		<div class="sp-empty" style="padding: 48px 20px;">
			<div class="sp-empty-icon"><span class="dashicons dashicons-performance" style="font-size:48px;width:48px;height:48px;color:var(--sp-accent);"></span></div>
			<p class="sp-empty-title"><?php echo esc_html__( 'No Speed Data Yet', 'sitepulse' ); ?></p>
			<p class="sp-empty-desc"><?php echo esc_html__( 'Wait 4-8 hours for your first speed test result. The test is automatically performed every 24 hours.', 'sitepulse' ); ?></p>
		</div>
	</div>
</div>

<?php else : ?>

<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 1: Performance Score
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--health">

<div class="sp-grid sp-grid-3 sp-mb-16">
	<!-- Score Ring -->
	<div class="sp-card sp-card--hero">
		<div class="sp-card-body sp-text-center" style="padding: 24px;">
			<?php
			$ring_score = isset( $ps_performance_score ) ? (int) $ps_performance_score : 0;
			$ring_label = __( 'Performance', 'sitepulse' );
			$ring_size  = 'lg';
			require __DIR__ . '/../partials/score-ring.php';
			?>
			<p class="sp-text-xs sp-text-muted sp-mt-8">
				<?php echo esc_html( ucfirst( isset( $ps_strategy ) ? $ps_strategy : 'desktop' ) ); ?> <?php echo esc_html__( 'analysis', 'sitepulse' ); ?>
			</p>
		</div>
	</div>

	<!-- Screenshot -->
	<div class="sp-card" style="grid-column: span 2;">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-format-image"></span> <?php echo esc_html__( 'Page Preview', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body sp-text-center" style="position: relative;">
			<?php
			$sp_screenshot_src = '';
			$sp_raw_screenshot = '';
			if ( ! empty( $ps_data['screenshot'] ) ) {
				if ( is_array( $ps_data['screenshot'] ) && ! empty( $ps_data['screenshot']['data'] ) && is_string( $ps_data['screenshot']['data'] ) ) {
					$sp_raw_screenshot = trim( $ps_data['screenshot']['data'] );
				} elseif ( is_string( $ps_data['screenshot'] ) ) {
					$sp_raw_screenshot = trim( $ps_data['screenshot'] );
				}
			}

			if ( '' !== $sp_raw_screenshot ) {
				if ( 0 === strpos( $sp_raw_screenshot, 'data:image/' ) ) {
					$sp_screenshot_src = $sp_raw_screenshot;
				} else {
					$sp_validated_screenshot_url = wp_http_validate_url( $sp_raw_screenshot );
					$sp_screenshot_scheme        = $sp_validated_screenshot_url ? wp_parse_url( $sp_validated_screenshot_url, PHP_URL_SCHEME ) : '';

					if ( in_array( $sp_screenshot_scheme, array( 'http', 'https' ), true ) ) {
						$sp_screenshot_src = $sp_validated_screenshot_url;
					}
				}
			}
			?>
			<?php if ( ! empty( $sp_screenshot_src ) && is_string( $sp_screenshot_src ) ) : ?>
				<img src="<?php echo $sp_screenshot_src; ?>"
					 alt="<?php echo esc_attr__( 'PageSpeed screenshot', 'sitepulse' ); ?>"
					 style="max-width:100%;border-radius:var(--sp-radius-md);border:1px solid var(--sp-border);position: absolute;object-fit: cover;width: 100%;top: 0;right: 0;">
			<?php else : ?>
				<div class="sp-empty" style="padding: 32px;">
					<span class="dashicons dashicons-format-image" style="font-size:32px;color:var(--sp-text-tertiary);"></span>
					<p class="sp-text-xs sp-text-muted sp-mt-8"><?php echo esc_html__( 'No screenshot available', 'sitepulse' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

</div><!-- /sp-zone--health -->


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 2: Core Web Vitals
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--infra">
<div class="sp-zone-header">
	<span class="dashicons dashicons-chart-bar"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Website Speed Scores', 'sitepulse' ); ?></span>
</div>

<!-- Lighthouse Category Scores -->
<?php
$sp_category_scores = array(
	'performance'    => array(
		'label' => __( 'Performance', 'sitepulse' ),
		'icon'  => 'dashicons-performance',
	),
	'accessibility'  => array(
		'label' => __( 'Accessibility', 'sitepulse' ),
		'icon'  => 'dashicons-universal-access-alt',
	),
	'best_practices' => array(
		'label' => __( 'Best Practices', 'sitepulse' ),
		'icon'  => 'dashicons-awards',
	),
	'seo'            => array(
		'label' => __( 'SEO', 'sitepulse' ),
		'icon'  => 'dashicons-search',
	),
);
?>
<div class="sp-grid sp-grid-4 sp-mb-16">
	<?php foreach ( $sp_category_scores as $cat_key => $cat_meta ) :
		$cat_raw   = isset( $ps_data['scores'][ $cat_key ] ) ? $ps_data['scores'][ $cat_key ] : null;
		$cat_value = 0;
		if ( $cat_raw !== null ) {
			$cat_value = ( $cat_raw <= 1 ) ? (int) round( $cat_raw * 100 ) : (int) round( $cat_raw );
		}
	?>
	<div class="sp-card sp-text-center sp-lighthouse-score-card">
		<div class="sp-card-body" style="padding: 16px 8px;">
			<?php
			$ring_score = $cat_value;
			$ring_label = $cat_meta['label'];
			$ring_size  = 'sm';
			require __DIR__ . '/../partials/score-ring.php';
			?>
		</div>
	</div>
	<?php endforeach; ?>
</div>

<?php
$cwv_metrics = array(
	'fcp' => array(
		'label' => __( 'First Content Visible', 'sitepulse' ),
		'icon'  => 'dashicons-visibility',
		'desc'  => __( 'How quickly the first text or image appears', 'sitepulse' ),
	),
	'lcp' => array(
		'label' => __( 'Main Content Loaded', 'sitepulse' ),
		'icon'  => 'dashicons-format-image',
		'desc'  => __( 'When the biggest element on the page finishes loading', 'sitepulse' ),
	),
	'tbt' => array(
		'label' => __( 'Browser Wait Time', 'sitepulse' ),
		'icon'  => 'dashicons-clock',
		'desc'  => __( 'How long the browser is busy and can\'t respond to clicks', 'sitepulse' ),
	),
	'cls' => array(
		'label' => __( 'Page Stability', 'sitepulse' ),
		'icon'  => 'dashicons-move',
		'desc'  => __( 'Whether content jumps around while loading', 'sitepulse' ),
	),
	'si' => array(
		'label' => __( 'Visual Loading Speed', 'sitepulse' ),
		'icon'  => 'dashicons-dashboard',
		'desc'  => __( 'How quickly the visible content fills the screen', 'sitepulse' ),
	),
	'tti' => array(
		'label' => __( 'Ready to Use', 'sitepulse' ),
		'icon'  => 'dashicons-admin-users',
		'desc'  => __( 'When the page is fully ready for interaction', 'sitepulse' ),
	),
);
?>

<div class="sp-grid sp-grid-3 sp-mb-16">
	<?php foreach ( $cwv_metrics as $key => $meta ) :
		$cwv = isset( $ps_data['core_web_vitals'][ $key ] ) ? $ps_data['core_web_vitals'][ $key ] : null;
		$cwv_score = ( $cwv && isset( $cwv['score'] ) ) ? (float) $cwv['score'] : null;
		$cwv_display = ( $cwv && isset( $cwv['display_value'] ) ) ? $cwv['display_value'] : '—';

		if ( $cwv_score !== null ) {
			if ( $cwv_score >= 0.9 ) {
				$color_class = 'success';
			} elseif ( $cwv_score >= 0.5 ) {
				$color_class = 'warning';
			} else {
				$color_class = 'danger';
			}
		} else {
			$color_class = 'neutral';
		}
	?>
	<div class="sp-cwv-card sp-cwv-card--<?php echo esc_attr( $color_class ); ?>">
		<div class="sp-cwv-card-head">
			<span class="dashicons <?php echo esc_attr( $meta['icon'] ); ?>"></span>
			<span class="sp-cwv-label"><?php echo esc_html( $meta['label'] ); ?></span>
		</div>
		<div class="sp-cwv-value"><?php echo esc_html( $cwv_display ); ?></div>
		<div class="sp-cwv-desc"><?php echo esc_html( $meta['desc'] ); ?></div>
	</div>
	<?php endforeach; ?>
</div>

</div><!-- /sp-zone--infra -->


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 3: Recommendations
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--data">
<div class="sp-zone-header">
	<span class="dashicons dashicons-lightbulb"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Improvement Suggestions', 'sitepulse' ); ?></span>
</div>

<div class="sp-grid sp-grid-2 sp-mb-16">
	<!-- Opportunities -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-lightbulb"></span> <?php echo esc_html__( 'Opportunities', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body sp-p-0">
			<?php if ( ! empty( $ps_data['opportunities'] ) ) : ?>
				<?php foreach ( $ps_data['opportunities'] as $opp ) : ?>
					<div class="sp-list-item" style="padding:10px 20px;">
						<div class="sp-list-item-body">
							<p class="sp-list-item-title sp-text-sm"><?php echo esc_html( $opp['title'] ); ?></p>
							<?php if ( ! empty( $opp['description'] ) ) : ?>
								<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( wp_trim_words( $opp['description'], 15 ) ); ?></p>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $opp['savings'] ) ) : ?>
							<span class="sp-badge sp-badge-success"><?php echo esc_html( $opp['savings'] ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="sp-empty" style="padding: 24px;">
					<div class="sp-empty-icon"><span class="dashicons dashicons-yes-alt" style="color:var(--sp-success);"></span></div>
					<p class="sp-empty-title sp-text-sm"><?php echo esc_html__( 'Great job! No improvements needed', 'sitepulse' ); ?></p>
					<p class="sp-empty-desc sp-text-xs"><?php echo esc_html__( 'Your page is well optimized!', 'sitepulse' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Diagnostics -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-info-outline"></span> <?php echo esc_html__( 'Technical Details', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body sp-p-0">
			<?php if ( ! empty( $ps_data['diagnostics'] ) ) : ?>
				<?php foreach ( $ps_data['diagnostics'] as $diag ) : ?>
					<div class="sp-list-item" style="padding:10px 20px;">
						<div class="sp-list-item-body">
							<p class="sp-list-item-title sp-text-sm"><?php echo esc_html( $diag['title'] ); ?></p>
							<?php if ( ! empty( $diag['description'] ) ) : ?>
								<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html( wp_trim_words( $diag['description'], 15 ) ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="sp-empty" style="padding: 24px;">
					<div class="sp-empty-icon"><span class="dashicons dashicons-yes-alt" style="color:var(--sp-success);"></span></div>
					<p class="sp-empty-title sp-text-sm"><?php echo esc_html__( 'No additional details', 'sitepulse' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

</div><!-- /sp-zone--data -->

<?php endif; ?>

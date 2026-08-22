<?php
/**
 * Easy Mode — Sidebar navigation.
 * Refactored for dynamic multi-view routing.
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$nav_items    = Sitepulse_Easy_Mode::get_nav_items();
$current_view = Sitepulse_Easy_Mode::get_current_view();

// Error badge count from error handler
$error_badge = 0;
if ( class_exists( 'Sitepulse_Error_Handler' ) ) {
	$el_stats    = Sitepulse_Error_Handler::get_error_log_stats();
	$error_badge = isset( $el_stats['error_count'] ) ? (int) $el_stats['error_count'] : 0;
}
?>
<aside class="sp-easy-sidebar">
	<div class="sp-sidebar-brand">
		<?php
		$logo_url = Sitepulse_Whitelabel_Service::get_logo_url( 'sidebar' );
		if ( $logo_url ) : ?>
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( Sitepulse_Whitelabel_Service::get_plugin_name() ); ?>">
		<?php else : ?>
			<span class="dashicons dashicons-chart-line" style="color:#3b82f6;font-size:24px;width:28px;height:28px;"></span>
		<?php endif; ?>
		<span class="sp-sidebar-brand-name"><?php echo esc_html( Sitepulse_Whitelabel_Service::get_plugin_name() ); ?></span>
		<span class="sp-sidebar-brand-version"><?php echo esc_html( SITEPULSE_VERSION ); ?></span>
	</div>

	<nav class="sp-sidebar-nav">
		<div class="sp-sidebar-nav-group">
			<div class="sp-sidebar-nav-label"><?php echo esc_html__( 'Navigate', 'sitepulse' ); ?></div>
			<?php foreach ( $nav_items as $item ) :
				$is_active = ( $item['slug'] === $current_view );
				// Show badge on Security if there are errors
				$badge_count = 0;
				if ( $item['slug'] === 'security' && $error_badge > 0 ) {
					$badge_count = $error_badge;
				}
			?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"
				   class="sp-nav-item <?php echo $is_active ? 'active' : ''; ?>"
				   <?php if ( $is_active ) : ?>aria-current="page"<?php endif; ?>>
					<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>"></span>
					<span class="sp-nav-label"><?php echo esc_html( $item['label'] ); ?></span>
					<?php if ( $badge_count > 0 ) : ?>
						<span class="sp-nav-badge"><?php echo esc_html( $badge_count ); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</nav>

	<!-- Live Pulse Monitor -->
	<div class="sp-sidebar-pulse-section">
		<div class="sp-sidebar-section-label"><?php echo esc_html__( 'Live Status', 'sitepulse' ); ?></div>
		<div class="sp-topbar-pulse" aria-label="<?php echo esc_attr__( 'Live Status', 'sitepulse' ); ?>">
			<div class="sp-pulse-monitor">
				<span class="sp-pulse-line"></span>
				<span class="sp-pulse-glow"></span>
			</div>
		</div>
		<?php
		// Normalize the memory percentage for CSS output so we always emit a single trailing percent sign.
		$sp_memory_percent_width = '0';
		if ( isset( $mem['percent'] ) ) {
			$sp_memory_percent_width = rtrim( (string) $mem['percent'], '%' );
		}
		?>
		<div class="sp-sidebar-memory-widget">
			<div class="sp-sidebar-memory-label"><?php echo esc_html__( 'Memory', 'sitepulse' ); ?></div>
			<span class="badge <?php echo isset( $mem ) ? esc_attr( $mem['percent_class'] ) : 'bg-secondary'; ?> sp_memory_css small-b" id="sp_js_memory"><?php echo isset( $mem ) ? esc_html( $mem['formatted'] ) : '—'; ?></span>
			<div class="sp-sidebar-memory-bar">
				<div class="sp-sidebar-memory-fill" id="sp_js_memory_progress" style="width: <?php echo esc_attr( $sp_memory_percent_width ); ?>%;"></div>
			</div>
		</div>
	</div>

	<!-- SVG Pulse Waveform -->
	<div class="sp-sidebar-waveform">
		<svg id="mainSVG" viewBox="0 0 800 220" preserveAspectRatio="none">
			<polyline id="pulseLine" points=""></polyline>
		</svg>
	</div>

	<div class="sp-sidebar-footer">
		<button type="button" class="sp-sidebar-toggle-classic sp-toggle-easy-mode" title="<?php echo esc_attr__( 'Switch to the advanced dashboard view', 'sitepulse' ); ?>">
			<span class="dashicons dashicons-magic-wand" style="font-size:14px;width:14px;height:14px;"></span>
			<?php echo esc_html__( 'Advanced View', 'sitepulse' ); ?>
		</button>
	</div>
</aside>

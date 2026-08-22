<?php
/**
 * Easy Mode — Main layout shell.
 * Wraps sidebar + topbar + content area.
 *
 * Available vars: $easy_view, $easy_theme, plus allowlisted data variables.
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="sp-easy sp-fade-in" data-theme="<?php echo esc_attr( $easy_theme ); ?>">
	<div class="sp-easy-shell">

		<div class="sp-sidebar-overlay"></div>

		<?php require __DIR__ . '/partials/sidebar.php'; ?>

		<div class="sp-easy-main">
			<?php require __DIR__ . '/partials/topbar.php'; ?>

			<div class="sp-easy-content">
				<?php
				// Load the requested view
				$view_path = __DIR__ . '/views/' . $easy_view . '.php';
				if ( file_exists( $view_path ) ) {
					require $view_path;
				}
				?>
			</div>
		</div>
	</div>

	<?php require __DIR__ . '/partials/pet-assistant.php'; ?>

</div>

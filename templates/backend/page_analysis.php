<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SitePulse Page Analysis - classic (advanced) dashboard wrapper
 *
 * @package SitePulse
 */
?>
<div class="sp-dashboard container-fluid my-4">
	<?php require_once SITEPULSE_PATH . '/templates/backend/header.php'; ?>

	<div class="sp-pa-classic">
		<?php require SITEPULSE_PATH . 'templates/easy/views/page-analysis.php'; ?>
	</div>
</div>

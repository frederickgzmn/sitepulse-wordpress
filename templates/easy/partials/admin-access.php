<?php
/**
 * wp-admin reachability on the Simple dashboard's Site Information.
 *
 * @package SitePulse
 *
 * @var array $sp_admin_access See Sitepulse_Admin_Access::render().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_admin_access_badges = array(
	'success' => 'sp-badge sp-badge-success',
	'danger'  => 'sp-badge sp-badge-danger',
	'neutral' => 'sp-badge sp-badge-neutral',
);
?>
<div class="sp-card sp-mb-16" id="sitepulse-wp-admin" data-sitepulse-admin-access data-url="<?php echo esc_url( $sp_admin_access['url'] ); ?>" data-action="<?php echo esc_attr( $sp_admin_access['action'] ); ?>" data-nonce="<?php echo esc_attr( $sp_admin_access['nonce'] ); ?>">
	<div class="sp-card-header">
		<h3 class="sp-card-title"><span class="dashicons dashicons-admin-network"></span> <?php echo esc_html__( 'wp-admin Access', 'sitepulse' ); ?></h3>
		<span class="<?php echo esc_attr( $sp_admin_access_badges[ $sp_admin_access['tone'] ] ); ?>" data-admin-access-badge<?php foreach ( $sp_admin_access_badges as $sp_admin_access_tone => $sp_admin_access_class ) : ?> data-<?php echo esc_attr( $sp_admin_access_tone ); ?>="<?php echo esc_attr( $sp_admin_access_class ); ?>"<?php endforeach; ?>><?php echo esc_html( $sp_admin_access['label'] ); ?></span>
	</div>
	<div class="sp-card-body">
		<p class="sp-text-sm sp-mb-8" data-admin-access-detail><?php echo esc_html( $sp_admin_access['detail'] ); ?></p>
		<p class="sp-text-xs sp-mono sp-mb-8" data-admin-access-message<?php echo '' === $sp_admin_access['message'] ? ' hidden' : ''; ?>><?php echo esc_html( $sp_admin_access['message'] ); ?></p>
		<p class="sp-text-xs sp-text-muted sp-mb-8" data-admin-access-note<?php echo '' === $sp_admin_access['note'] ? ' hidden' : ''; ?>><?php echo esc_html( $sp_admin_access['note'] ); ?></p>
		<p class="sp-text-sm sp-fw-500 sp-mb-8" data-admin-access-error hidden></p>
		<div class="sp-flex sp-justify-between sp-items-center sp-gap-16">
			<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html__( 'Every hour, this server opens its own wp-admin and login page, the way WordPress checks an edited plugin for errors. Updates from your SitePulse dashboard need this to check the site after each update.', 'sitepulse' ); ?></p>
			<button type="button" class="sp-btn sp-btn-outline sp-btn-sm" data-admin-access-check data-checking="<?php echo esc_attr__( 'Checking…', 'sitepulse' ); ?>" data-failed="<?php echo esc_attr__( 'The check could not run. Reload the page and try again.', 'sitepulse' ); ?>"><?php echo esc_html__( 'Check now', 'sitepulse' ); ?></button>
		</div>
	</div>
</div>

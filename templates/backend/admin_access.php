<?php
/**
 * wp-admin reachability in the System Information of the Advanced dashboard.
 *
 * @package SitePulse
 *
 * @var array $sp_admin_access See Sitepulse_Admin_Access::render().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_admin_access_badges = array(
	'success' => 'badge bg-success',
	'danger'  => 'badge bg-danger',
	'neutral' => 'badge bg-secondary',
);
?>
<div class="sp-detail-item" id="sitepulse-wp-admin" data-sitepulse-admin-access data-url="<?php echo esc_url( $sp_admin_access['url'] ); ?>" data-action="<?php echo esc_attr( $sp_admin_access['action'] ); ?>" data-nonce="<?php echo esc_attr( $sp_admin_access['nonce'] ); ?>">
	<div class="sp-detail-main">
		<div class="sp-detail-info">
			<div class="sp-detail-title"><?php echo esc_html__( 'wp-admin', 'sitepulse' ); ?></div>
			<div class="sp-detail-meta" data-admin-access-detail><?php echo esc_html( $sp_admin_access['detail'] ); ?></div>
			<div class="sp-detail-meta font-monospace" data-admin-access-message<?php echo '' === $sp_admin_access['message'] ? ' hidden' : ''; ?>><?php echo esc_html( $sp_admin_access['message'] ); ?></div>
			<div class="sp-detail-meta" data-admin-access-note<?php echo '' === $sp_admin_access['note'] ? ' hidden' : ''; ?>><?php echo esc_html( $sp_admin_access['note'] ); ?></div>
			<div class="sp-detail-meta text-danger" data-admin-access-error hidden></div>
			<div class="sp-detail-meta"><?php echo esc_html__( 'Checked every hour from this server, as WordPress checks an edited plugin for errors. Updates from your SitePulse dashboard load wp-admin to check the site.', 'sitepulse' ); ?></div>
			<button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-admin-access-check data-checking="<?php echo esc_attr__( 'Checking…', 'sitepulse' ); ?>" data-failed="<?php echo esc_attr__( 'The check could not run. Reload the page and try again.', 'sitepulse' ); ?>"><?php echo esc_html__( 'Check now', 'sitepulse' ); ?></button>
		</div>
		<div class="sp-detail-metrics">
			<span class="<?php echo esc_attr( $sp_admin_access_badges[ $sp_admin_access['tone'] ] ); ?>" data-admin-access-badge<?php foreach ( $sp_admin_access_badges as $sp_admin_access_tone => $sp_admin_access_class ) : ?> data-<?php echo esc_attr( $sp_admin_access_tone ); ?>="<?php echo esc_attr( $sp_admin_access_class ); ?>"<?php endforeach; ?>><?php echo esc_html( $sp_admin_access['label'] ); ?></span>
		</div>
	</div>
</div>

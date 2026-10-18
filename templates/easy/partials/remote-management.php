<?php
/**
 * Remote management settings on the Simple dashboard.
 *
 * @package SitePulse
 *
 * @var array $sp_remote See Sitepulse_Remote_Management::view_data().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="sp-zone sp-zone--remote" id="sitepulse-remote-management">
	<div class="sp-zone-header">
		<span class="dashicons dashicons-cloud"></span>
		<span class="sp-zone-title"><?php echo esc_html__( 'Remote Management', 'sitepulse' ); ?></span>
	</div>

	<div class="sp-card sp-mb-16">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-backup"></span> <?php echo esc_html__( 'Updates & Backups from Your Dashboard', 'sitepulse' ); ?></h3>
			<span class="sp-badge sp-badge-<?php echo esc_attr( $sp_remote['tone'] ); ?>"><?php echo esc_html( $sp_remote['label'] ); ?></span>
		</div>
		<div class="sp-card-body">
			<p class="sp-text-sm sp-mb-8"><?php echo esc_html( $sp_remote['message'] ); ?></p>
			<p class="sp-text-xs sp-text-muted sp-mb-16">
				<?php echo esc_html__( 'Backup plugin:', 'sitepulse' ); ?> <?php echo esc_html( $sp_remote['provider'] ); ?>
				&middot;
				<?php echo esc_html__( 'Last check-in:', 'sitepulse' ); ?> <?php echo esc_html( $sp_remote['last_check_in'] ); ?>
			</p>

			<form method="post" action="<?php echo esc_url( $sp_remote['action'] ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( $sp_remote['nonce_action'] ); ?>">
				<?php wp_nonce_field( $sp_remote['nonce_action'] ); ?>
				<div class="sp-flex sp-justify-between sp-items-center sp-mb-16">
					<div>
						<p class="sp-text-sm sp-fw-500 sp-mb-0"><?php echo esc_html__( 'Block Remote Management', 'sitepulse' ); ?></p>
						<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html__( 'Your SitePulse dashboard cannot see or manage the backups and updates of this site', 'sitepulse' ); ?></p>
					</div>
					<label class="sp-toggle">
						<input type="checkbox" name="sitepulse_remote_blocked" value="1" <?php checked( $sp_remote['blocked'] ); ?>>
						<span class="sp-toggle-track"></span>
					</label>
				</div>
				<button type="submit" class="sp-btn sp-btn-outline sp-btn-sm"><?php echo esc_html__( 'Save', 'sitepulse' ); ?></button>
			</form>
			<?php include SITEPULSE_PATH . 'templates/backend/remote_connection.php'; ?>

			<p class="sp-text-xs sp-text-muted sp-mt-16 sp-mb-8"><?php echo esc_html__( 'While remote management is on, your SitePulse dashboard can update plugins, themes and WordPress, with a check that rolls back a broken update. With UpdraftPlus or BackWPup active, it can also start or cancel a backup, apply backup settings saved there, and back up before each update. It restores a backup only when you undo a safe update there, and never deletes backups.', 'sitepulse' ); ?></p>
			<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html__( 'While Cloud Services are on, this site checks in with api.sitepulse.me every hour, and every 5 minutes while remote management is on. It sends plugin and theme names, versions and available updates, the WordPress, PHP and database versions, the backup schedules and recent backups (dates, sizes, storage types), the time plugins, themes and WordPress take per page load, PHP fatal errors (message, file and line), and whether this server can reach its own wp-admin. It never sends backup files, passwords, storage credentials or site content.', 'sitepulse' ); ?></p>

			<?php if ( $sp_remote['log'] ) : ?>
				<details class="sp-mt-16">
					<summary class="sp-text-sm sp-fw-500"><?php echo esc_html__( 'Activity', 'sitepulse' ); ?></summary>
					<div class="sp-table-wrap">
						<table class="sp-table">
							<tbody>
								<?php foreach ( $sp_remote['log'] as $sp_remote_entry ) : ?>
									<tr>
										<td style="white-space: nowrap;"><?php echo esc_html( $sp_remote_entry['when'] ); ?></td>
										<td><?php echo esc_html( $sp_remote_entry['message'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</details>
			<?php endif; ?>
		</div>
	</div>
</div>

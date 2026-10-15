<?php
/**
 * Remote management settings on the Advanced dashboard.
 *
 * @package SitePulse
 *
 * @var array $sp_remote See Sitepulse_Remote_Management::view_data().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_remote_badges = array(
	'success' => 'bg-success',
	'neutral' => 'bg-secondary',
	'warning' => 'bg-warning text-dark',
	'danger'  => 'bg-danger',
);
?>
<div class="sp-setting-item" id="sitepulse-remote-management">
	<div class="sp-setting-header">
		<span class="form-check-label fw-semibold"><?php echo esc_html__( 'Remote Management', 'sitepulse' ); ?></span>
		<span class="badge <?php echo esc_attr( $sp_remote_badges[ $sp_remote['tone'] ] ); ?>"><?php echo esc_html( $sp_remote['label'] ); ?></span>
	</div>
	<div class="sp-setting-description">
		<p><?php echo esc_html( $sp_remote['message'] ); ?></p>
		<p class="mb-1"><strong><?php echo esc_html__( 'Backup plugin:', 'sitepulse' ); ?></strong> <?php echo esc_html( $sp_remote['provider'] ); ?></p>
		<p><strong><?php echo esc_html__( 'Last check-in:', 'sitepulse' ); ?></strong> <?php echo esc_html( $sp_remote['last_check_in'] ); ?></p>
	</div>
	<form method="post" action="<?php echo esc_url( $sp_remote['action'] ); ?>">
		<input type="hidden" name="action" value="<?php echo esc_attr( $sp_remote['nonce_action'] ); ?>" />
		<?php wp_nonce_field( $sp_remote['nonce_action'] ); ?>
		<label class="form-switch">
			<input type="checkbox" name="sitepulse_remote_blocked" id="sitepulse_remote_blocked" value="1" class="form-check-input" <?php checked( $sp_remote['blocked'] ); ?> />
			<span class="form-check-label"><?php echo esc_html__( 'Block remote management on this site', 'sitepulse' ); ?></span>
		</label>
		<button type="submit" class="button sp-action-btn"><?php echo esc_html__( 'Save', 'sitepulse' ); ?></button>
	</form>
	<div class="sp-setting-help-box">
		<div class="alert alert-info" style="margin-top: 10px;">
			<p class="mb-2"><?php echo esc_html__( 'When you turn remote management on in your SitePulse dashboard and UpdraftPlus or BackWPup is active, the dashboard can show the backups and updates of this site, start or cancel a backup, apply backup settings saved there, and update plugins, themes and WordPress, each after a backup and checked so a broken update is rolled back. Updates come from WordPress.org or a plugin\'s own updater, as in wp-admin. It never restores or deletes backups. Blocking it here always wins.', 'sitepulse' ); ?></p>
			<p class="mb-0"><small><?php echo esc_html__( 'While PageSpeed & AI Reports is on, this site checks in with api.sitepulse.me every hour, and every 5 minutes while remote management is on. It sends plugin and theme names, versions and available updates, the WordPress, PHP and database versions, the backup schedules and recent backups (dates, sizes, storage types), the time plugins, themes and WordPress take per page load, and PHP fatal errors (message, file and line). It never sends backup files, passwords, storage credentials or site content.', 'sitepulse' ); ?></small></p>
		</div>
	</div>
	<?php if ( $sp_remote['log'] ) : ?>
		<details>
			<summary class="fw-semibold"><?php echo esc_html__( 'Activity', 'sitepulse' ); ?></summary>
			<table class="table table-sm mt-2 mb-0">
				<tbody>
					<?php foreach ( $sp_remote['log'] as $sp_remote_entry ) : ?>
						<tr>
							<td style="white-space: nowrap;"><?php echo esc_html( $sp_remote_entry['when'] ); ?></td>
							<td><?php echo esc_html( $sp_remote_entry['message'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</details>
	<?php endif; ?>
</div>

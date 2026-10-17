<?php
/**
 * Connecting the site to a SitePulse account, in the remote management settings of both dashboards.
 *
 * @package SitePulse
 *
 * @var array $sp_remote See Sitepulse_Remote_Management::view_data(); this uses its connection and action.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_connection = $sp_remote['connection'];
?>
<div class="sp-remote-connection" id="sitepulse-remote-connection" style="margin: 16px 0;">
	<p style="font-weight: 600; margin: 0 0 4px;"><?php echo esc_html__( 'Connect this site to your SitePulse account', 'sitepulse' ); ?></p>
	<?php if ( ! $sp_connection['available'] ) : ?>
		<p style="margin: 0;"><?php echo esc_html__( 'Turn on Cloud Services so this site can connect to your SitePulse account.', 'sitepulse' ); ?></p>
	<?php elseif ( ! $sp_connection['connectable'] ) : ?>
		<p style="margin: 0;"><?php echo esc_html__( 'This site has a SitePulse Pro license, so it already appears in the account that owns the license.', 'sitepulse' ); ?></p>
	<?php else : ?>
		<?php if ( $sp_connection['connections'] ) : ?>
			<ul style="margin: 0 0 8px;">
				<?php foreach ( $sp_connection['connections'] as $sp_connection_entry ) : ?>
					<li>
						<?php
						/* translators: 1: account email (masked) or SitePulse, 2: plan name, 3: date */
						echo esc_html( sprintf( __( 'Connected to %1$s (%2$s plan) since %3$s.', 'sitepulse' ), $sp_connection_entry['account'], $sp_connection_entry['plan'], $sp_connection_entry['since'] ) );
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php elseif ( '' !== $sp_connection['key'] ) : ?>
			<p style="margin: 0 0 6px;"><?php echo esc_html__( 'In your SitePulse dashboard, open Remote Management, then Sites, then Connect a site, and enter this site\'s address and this connection key. A key works once.', 'sitepulse' ); ?></p>
			<p style="margin: 0 0 8px;">
				<label for="sitepulse-connection-key" class="screen-reader-text"><?php echo esc_html__( 'Connection key', 'sitepulse' ); ?></label>
				<input type="text" id="sitepulse-connection-key" class="regular-text code" readonly value="<?php echo esc_attr( $sp_connection['key'] ); ?>" />
			</p>
		<?php else : ?>
			<p style="margin: 0 0 8px;"><?php echo esc_html__( 'Not connected yet. Show a connection key and enter it in your SitePulse dashboard to see and manage this site there.', 'sitepulse' ); ?></p>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( $sp_remote['action'] ); ?>" style="display: flex; gap: 8px; flex-wrap: wrap;">
			<input type="hidden" name="action" value="<?php echo esc_attr( $sp_connection['nonce_action'] ); ?>" />
			<?php wp_nonce_field( $sp_connection['nonce_action'] ); ?>
			<?php if ( ! $sp_connection['connections'] ) : ?>
				<button type="submit" name="sitepulse_connection" value="key" class="button"><?php echo '' === $sp_connection['key'] ? esc_html__( 'Show connection key', 'sitepulse' ) : esc_html__( 'Create a new key', 'sitepulse' ); ?></button>
			<?php endif; ?>
			<button type="submit" name="sitepulse_connection" value="status" class="button"><?php echo esc_html__( 'Check connection', 'sitepulse' ); ?></button>
			<?php if ( $sp_connection['connections'] ) : ?>
				<button type="submit" name="sitepulse_connection" value="disconnect" class="button"><?php echo esc_html__( 'Disconnect from SitePulse accounts', 'sitepulse' ); ?></button>
			<?php endif; ?>
		</form>
		<?php if ( '' !== $sp_connection['checked'] ) : ?>
			<?php /* translators: %s: how long ago, such as "5 mins ago" */ ?>
			<p class="description" style="margin: 6px 0 0;"><?php echo esc_html( sprintf( __( 'Checked %s.', 'sitepulse' ), $sp_connection['checked'] ) ); ?></p>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ( '' !== $sp_connection['error'] ) : ?>
		<p style="color: #b32d2e; margin: 6px 0 0;"><?php echo esc_html( $sp_connection['error'] ); ?></p>
	<?php endif; ?>
</div>

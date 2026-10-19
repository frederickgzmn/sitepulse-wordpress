<?php
/**
 * Connecting the site to a SitePulse dashboard, in the remote management settings of both dashboards.
 *
 * @package SitePulse
 *
 * @var array $sp_remote See Sitepulse_Remote_Management::view_data(); this uses its connection and action.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_connection = $sp_remote['connection'];
$sp_waiting    = $sp_connection['available'] && $sp_connection['connectable'] && '' !== $sp_connection['key'];
?>
<div class="sp-remote-connection" id="sitepulse-remote-connection" style="margin: 16px 0;"<?php if ( $sp_waiting ) : ?> data-poll-url="<?php echo esc_url( $sp_connection['poll']['url'] ); ?>" data-poll-action="<?php echo esc_attr( $sp_connection['poll']['action'] ); ?>" data-poll-nonce="<?php echo esc_attr( $sp_connection['poll']['nonce'] ); ?>"<?php endif; ?>>
	<p style="font-weight: 600; margin: 0 0 4px;"><?php echo esc_html__( 'Connect this site to your SitePulse dashboard', 'sitepulse' ); ?></p>
	<?php if ( ! $sp_connection['available'] ) : ?>
		<p style="margin: 0;"><?php echo esc_html__( 'Turn on Cloud Services so this site can connect to your SitePulse dashboard.', 'sitepulse' ); ?></p>
	<?php elseif ( ! $sp_connection['connectable'] ) : ?>
		<p style="margin: 0;"><?php echo esc_html__( 'This site has a SitePulse Pro license, so it already appears in the account that owns the license.', 'sitepulse' ); ?></p>
	<?php else : ?>
		<?php foreach ( $sp_connection['connections'] as $sp_connection_entry ) : ?>
			<p style="margin: 0 0 6px;">
				<?php
				echo esc_html(
					null === $sp_connection_entry['account']
						/* translators: 1: plan name, 2: date */
						? sprintf( __( 'Connected to a SitePulse account (%1$s plan) by SitePulse since %2$s.', 'sitepulse' ), $sp_connection_entry['plan'], $sp_connection_entry['since'] )
						/* translators: 1: account email (masked), 2: plan name, 3: date */
						: sprintf( __( 'Connected to %1$s (%2$s plan) since %3$s.', 'sitepulse' ), $sp_connection_entry['account'], $sp_connection_entry['plan'], $sp_connection_entry['since'] )
				);
				?>
			</p>
		<?php endforeach; ?>
		<?php if ( $sp_waiting ) : ?>
			<ol style="margin: 0 0 8px 18px;">
				<li><?php echo esc_html__( 'Copy this connection key.', 'sitepulse' ); ?></li>
				<li><?php echo esc_html__( 'In your SitePulse dashboard, open Remote Management and choose Connect a site.', 'sitepulse' ); ?></li>
				<?php /* translators: %s: this site's address, such as example.com */ ?>
				<li><?php echo esc_html( sprintf( __( 'Enter this site\'s address (%s) and the key. SitePulse sets the site up at once; a key works once.', 'sitepulse' ), $sp_connection['domain'] ) ); ?></li>
			</ol>
			<p style="margin: 0 0 8px; display: flex; gap: 8px; flex-wrap: wrap;">
				<label for="sitepulse-connection-key" class="screen-reader-text"><?php echo esc_html__( 'Connection key', 'sitepulse' ); ?></label>
				<input type="text" id="sitepulse-connection-key" class="regular-text code" readonly value="<?php echo esc_attr( $sp_connection['key'] ); ?>" />
				<button type="button" class="<?php echo esc_attr( $sp_connection['button'] ); ?>" data-copy="sitepulse-connection-key" data-copied="<?php echo esc_attr__( 'Copied', 'sitepulse' ); ?>"><?php echo esc_html__( 'Copy', 'sitepulse' ); ?></button>
			</p>
			<p class="description" style="margin: 0 0 8px;" data-poll-status data-done="<?php echo esc_attr__( 'Your SitePulse dashboard used the key. Updating…', 'sitepulse' ); ?>"><?php echo esc_html__( 'Waiting for your SitePulse dashboard. This page updates by itself once you enter the key there.', 'sitepulse' ); ?></p>
		<?php elseif ( ! $sp_connection['managed'] ) : ?>
			<p style="margin: 0 0 8px;"><?php echo esc_html__( 'Get a connection key and enter it in your SitePulse dashboard to manage this site from there: its updates, and its backups with UpdraftPlus or BackWPup.', 'sitepulse' ); ?></p>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( $sp_remote['action'] ); ?>" style="display: flex; gap: 8px; flex-wrap: wrap;">
			<input type="hidden" name="action" value="<?php echo esc_attr( $sp_connection['nonce_action'] ); ?>" />
			<?php wp_nonce_field( $sp_connection['nonce_action'] ); ?>
			<?php // Offered on a managed site too: what the site knows about it can be up to one check-in old. ?>
			<button type="submit" name="sitepulse_connection" value="key" class="<?php echo esc_attr( $sp_connection['button'] ); ?>"><?php echo '' === $sp_connection['key'] ? esc_html__( 'Get connection key', 'sitepulse' ) : esc_html__( 'Get a new key', 'sitepulse' ); ?></button>
			<button type="submit" name="sitepulse_connection" value="status" class="<?php echo esc_attr( $sp_connection['button'] ); ?>"><?php echo esc_html__( 'Check connection', 'sitepulse' ); ?></button>
			<?php if ( $sp_connection['connections'] ) : ?>
				<button type="submit" name="sitepulse_connection" value="disconnect" class="<?php echo esc_attr( $sp_connection['button'] ); ?>"><?php echo esc_html__( 'Disconnect from SitePulse accounts', 'sitepulse' ); ?></button>
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
	<?php if ( '' !== $sp_connection['notice'] ) : ?>
		<p style="color: #00a32a; margin: 6px 0 0;"><?php echo esc_html( $sp_connection['notice'] ); ?></p>
	<?php endif; ?>
	<?php if ( $sp_connection['available'] && $sp_connection['connectable'] ) : ?>
		<?php // Open when SitePulse refuses the site's license, so the fix is at hand. ?>
		<details style="margin: 8px 0 0;"<?php if ( $sp_connection['license_open'] ) : ?> open<?php endif; ?>>
			<summary><?php echo esc_html__( 'Change the license this site uses', 'sitepulse' ); ?></summary>
			<form method="post" action="<?php echo esc_url( $sp_remote['action'] ); ?>" style="margin: 8px 0 0;">
				<input type="hidden" name="action" value="<?php echo esc_attr( $sp_connection['nonce_action'] ); ?>" />
				<?php wp_nonce_field( $sp_connection['nonce_action'] ); ?>
				<label for="sitepulse-license" style="display: block; margin: 0 0 4px;"><?php echo esc_html__( 'Set the site\'s SitePulse license', 'sitepulse' ); ?></label>
				<span style="display: flex; gap: 8px; flex-wrap: wrap;">
					<input type="text" id="sitepulse-license" name="sitepulse_license" class="regular-text code" required pattern="<?php echo esc_attr( $sp_connection['license_pattern'] ); ?>" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX" autocomplete="off" spellcheck="false" />
					<button type="submit" name="sitepulse_connection" value="license" class="<?php echo esc_attr( $sp_connection['button'] ); ?>"><?php echo esc_html__( 'Save', 'sitepulse' ); ?></button>
				</span>
				<span class="description" style="display: block; margin: 6px 0 0;"><?php echo esc_html__( 'Your SitePulse dashboard shows the license of each of your sites. The site checks in with the license you enter, and keeps it only if SitePulse accepts it.', 'sitepulse' ); ?></span>
			</form>
		</details>
	<?php endif; ?>
</div>

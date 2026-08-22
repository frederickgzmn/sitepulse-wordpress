<?php
/**
 * Easy Mode — Settings view.
 *
 * Zones:
 *  1. Monitoring     — Email blocking + WP Cron
 *  2. API & Alerts   — External API toggle + Recovery email + Pro Alerts
 *  3. Interface      — Setup wizard + mode toggle
 *  4. Pro License    — License management
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_settings = isset( $sitepulse_settings ) ? $sitepulse_settings : ( class_exists( 'Sitepulse_Settings' ) ? Sitepulse_Settings::getInstance() : null );
$sp_current  = isset( $current_settings ) ? $current_settings : ( $sp_settings ? $sp_settings->get_all_settings() : array() );
$sp_status   = isset( $status_info ) ? $status_info : ( $sp_settings ? $sp_settings->get_status_info() : array() );
$sp_cron_failure = isset( $sp_status['cron_status'] ) && $sp_status['cron_status'] !== 'ok';
?>

<!-- Page Header -->
<div class="sp-page-header sp-dashboard-header">
	<div class="sp-dashboard-header-left">
		<h2 class="sp-page-title"><?php echo esc_html__( 'Settings', 'sitepulse' ); ?></h2>
		<p class="sp-page-subtitle"><?php echo esc_html__( 'Customize how your site is monitored and how you get notified', 'sitepulse' ); ?></p>
	</div>
</div>
<?php if ( ! empty( $sp_settings_saved ) ) : ?>
<div class="sp-alert sp-alert-success sp-mb-16" style="align-items:center;">
	<span class="dashicons dashicons-yes-alt"></span>
	<div style="flex:1;">
		<strong><?php echo esc_html__( 'Settings saved successfully!', 'sitepulse' ); ?></strong>
	</div>
</div>
<?php endif; ?>

<form method="post" action="">
	<?php wp_nonce_field( 'sitepulse_settings_action', 'sitepulse_settings_nonce' ); ?>


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 1: Monitoring
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--health">
<div class="sp-zone-header">
	<span class="dashicons dashicons-visibility"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Monitoring', 'sitepulse' ); ?></span>
</div>

<div class="sp-grid sp-grid-2 sp-mb-16">
	<!-- Email Blocking -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-email"></span> <?php echo esc_html__( 'Email Blocking', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body">
			<div class="sp-flex sp-justify-between sp-items-center sp-mb-16">
				<div>
					<p class="sp-text-sm sp-fw-500 sp-mb-0"><?php echo esc_html__( 'Pause Outgoing Emails', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html__( 'Temporarily stop your site from sending emails', 'sitepulse' ); ?></p>
				</div>
				<label class="sp-toggle">
					<input type="checkbox" id="sp-easy-email-toggle" name="sitepulse_email_blocking_enabled" value="1" <?php checked( ! empty( $sp_current['email_blocking_enabled'] ) ); ?>>
					<span class="sp-toggle-track"></span>
				</label>
			</div>

			<div class="sp-mb-8">
				<label class="sp-text-xs sp-fw-500 sp-mb-4" style="display:block;"><?php echo esc_html__( 'Which emails to pause', 'sitepulse' ); ?></label>
				<select name="sitepulse_email_blocking_mode" class="sp-form-control sp-form-control-sm">
					<option value="all" <?php selected( isset( $sp_current['email_blocking_mode'] ) ? $sp_current['email_blocking_mode'] : '', 'all' ); ?>><?php echo esc_html__( 'All Emails', 'sitepulse' ); ?></option>
					<option value="non-admin" <?php selected( isset( $sp_current['email_blocking_mode'] ) ? $sp_current['email_blocking_mode'] : '', 'non-admin' ); ?>><?php echo esc_html__( 'Only Non-Admin Emails', 'sitepulse' ); ?></option>
				</select>
			</div>
		</div>
	</div>

	<!-- WP Cron -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-clock"></span> <?php echo esc_html__( 'Background Tasks', 'sitepulse' ); ?></h3>
			<?php if ( $sp_cron_failure ) : ?>
				<span class="sp-badge sp-badge-danger"><?php echo esc_html__( 'Issue', 'sitepulse' ); ?></span>
			<?php endif; ?>
		</div>
		<div class="sp-card-body">
			<div class="sp-flex sp-justify-between sp-items-center sp-mb-16">
				<div>
					<p class="sp-text-sm sp-fw-500 sp-mb-0"><?php echo esc_html__( 'Pause Background Tasks', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html__( 'Stop automatic tasks like scheduled posts, updates, and cleanups', 'sitepulse' ); ?></p>
				</div>
				<label class="sp-toggle">
					<input type="checkbox" id="sp-easy-cron-toggle" name="sitepulse_cron_disabled" value="1" <?php checked( ! empty( $sp_current['cron_disabled'] ) ); ?>>
					<span class="sp-toggle-track"></span>
				</label>
			</div>

			<?php if ( $sp_cron_failure ) : ?>
				<div class="sp-alert sp-alert-warning">
					<span class="dashicons dashicons-warning"></span>
					<div>
						<strong><?php echo esc_html__( 'Background Task Issue Found', 'sitepulse' ); ?></strong>
						<span class="sp-text-xs" style="display:block;"><?php echo esc_html( isset( $sp_status['cron_message'] ) ? $sp_status['cron_message'] : __( 'Automatic background tasks may not be running properly.', 'sitepulse' ) ); ?></span>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

</div><!-- /sp-zone--health -->


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 2: API & Notifications
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--infra">
<div class="sp-zone-header">
	<span class="dashicons dashicons-rest-api"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Cloud Services & Notifications', 'sitepulse' ); ?></span>
</div>

<div class="sp-grid sp-grid-2 sp-mb-16">
	<!-- External API -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-rest-api"></span> <?php echo esc_html__( 'Cloud Services', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body">
			<div class="sp-flex sp-justify-between sp-items-center sp-mb-16">
				<div>
					<p class="sp-text-sm sp-fw-500 sp-mb-0"><?php echo esc_html__( 'Connect to Cloud Services', 'sitepulse' ); ?></p>
					<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html__( 'Allow SitePulse to use online tools for deeper analysis', 'sitepulse' ); ?></p>
				</div>
				<label class="sp-toggle">
					<input type="checkbox" name="sitepulse_external_api_enabled" value="1" <?php checked( ! empty( $sp_current['external_api_enabled'] ) ); ?>>
					<span class="sp-toggle-track"></span>
				</label>
			</div>
		</div>
	</div>

	<!-- Notifications -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-email-alt2"></span> <?php echo esc_html__( 'Notifications', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body">
			<div class="sp-mb-12">
				<label class="sp-text-xs sp-fw-500 sp-mb-4" style="display:block;"><?php echo esc_html__( 'Emergency Contact Email', 'sitepulse' ); ?></label>
				<input type="email" name="sitepulse_recovery_mode_email_addresses" class="sp-form-control sp-form-control-sm"
					   value="<?php echo esc_attr( isset( $sp_current['recovery_mode_email_addresses'] ) ? $sp_current['recovery_mode_email_addresses'] : get_option( 'admin_email' ) ); ?>"
					   placeholder="<?php echo esc_attr__( 'admin@example.com', 'sitepulse' ); ?>">
				<p class="sp-text-xs sp-text-muted sp-mt-4"><?php echo esc_html__( 'We\'ll notify this email address if your site has a critical issue.', 'sitepulse' ); ?></p>
			</div>
		</div>
	</div>
</div>

<?php
// Pro Performance Alerts
if ( defined( 'SITEPULSE_PRO_IS_ACTIVE' ) && SITEPULSE_PRO_IS_ACTIVE && class_exists( 'Sitepulse_Pro_Alerts' ) ) :
	$sp_alerts = Sitepulse_Pro_Alerts::get_instance()->get_settings();
?>
<div class="sp-card sp-mb-16">
	<div class="sp-card-header">
		<div>
			<h3 class="sp-card-title">
				<span class="dashicons dashicons-bell"></span>
				<?php echo esc_html__( 'Speed Alerts', 'sitepulse-pro' ); ?>
			</h3>
			<p class="sp-card-subtitle"><?php echo esc_html__( 'Get an email when your site slows down', 'sitepulse-pro' ); ?></p>
		</div>
		<span class="sp-badge sp-badge-accent"><?php echo esc_html__( 'PRO', 'sitepulse-pro' ); ?></span>
	</div>
	<div class="sp-card-body">
		<div class="sp-flex sp-justify-between sp-items-center sp-mb-16">
			<div>
				<p class="sp-text-sm sp-fw-500 sp-mb-0"><?php echo esc_html__( 'Enable Alerts', 'sitepulse-pro' ); ?></p>
			</div>
			<label class="sp-toggle">
				<input type="checkbox" name="sitepulse_pro_alerts_enabled" value="1" <?php checked( ! empty( $sp_alerts['enabled'] ) ); ?>>
				<span class="sp-toggle-track"></span>
			</label>
		</div>

		<div class="sp-grid sp-grid-3 sp-mb-0">
			<div>
				<label class="sp-text-xs sp-fw-500 sp-mb-4" style="display:block;"><?php echo esc_html__( 'Alert Email', 'sitepulse-pro' ); ?></label>
				<input type="email" name="sitepulse_pro_alerts_email" class="sp-form-control sp-form-control-sm"
					   value="<?php echo esc_attr( isset( $sp_alerts['email'] ) ? $sp_alerts['email'] : get_option( 'admin_email' ) ); ?>">
			</div>
			<div>
				<label class="sp-text-xs sp-fw-500 sp-mb-4" style="display:block;"><?php echo esc_html__( 'Sensitivity (%)', 'sitepulse-pro' ); ?></label>
				<input type="number" name="sitepulse_pro_alerts_threshold" class="sp-form-control sp-form-control-sm"
					   value="<?php echo esc_attr( isset( $sp_alerts['degradation_threshold'] ) ? $sp_alerts['degradation_threshold'] : 15 ); ?>" min="1" max="100">
			</div>
			<div>
				<label class="sp-text-xs sp-fw-500 sp-mb-4" style="display:block;"><?php echo esc_html__( 'Wait Between Alerts (hrs)', 'sitepulse-pro' ); ?></label>
				<input type="number" name="sitepulse_pro_alerts_cooldown" class="sp-form-control sp-form-control-sm"
					   value="<?php echo esc_attr( isset( $sp_alerts['cooldown_hours'] ) ? $sp_alerts['cooldown_hours'] : 6 ); ?>" min="1" max="168">
			</div>
		</div>

		<div class="sp-flex sp-justify-between sp-items-center sp-mt-16">
			<div>
				<p class="sp-text-sm sp-fw-500 sp-mb-0"><?php echo esc_html__( 'Automatic Checkups', 'sitepulse-pro' ); ?></p>
				<p class="sp-text-xs sp-text-muted sp-mb-0"><?php echo esc_html__( 'Automatically check your site speed on a regular schedule', 'sitepulse-pro' ); ?></p>
			</div>
			<label class="sp-toggle">
				<input type="checkbox" name="sitepulse_pro_alerts_schedule_enabled" value="1" <?php checked( ! empty( $sp_alerts['schedule_enabled'] ) ); ?>>
				<span class="sp-toggle-track"></span>
			</label>
		</div>
	</div>
</div>
<?php endif; ?>

</div><!-- /sp-zone--infra -->


<!-- Save Button -->
<div class="sp-mb-24">
	<button type="submit" name="submit" class="sp-btn sp-btn-primary">
		<span class="dashicons dashicons-saved"></span> <?php echo esc_html__( 'Save Settings', 'sitepulse' ); ?>
	</button>
</div>

</form>

<?php do_action( 'sitepulse_settings_after_form' ); ?>


<!-- ═══════════════════════════════════════════════════════════════════════
     ZONE 3: Interface & Tools
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="sp-zone sp-zone--tools">
<div class="sp-zone-header">
	<span class="dashicons dashicons-admin-settings"></span>
	<span class="sp-zone-title"><?php echo esc_html__( 'Preferences & Tools', 'sitepulse' ); ?></span>
</div>

<div class="sp-grid sp-grid-2 sp-mb-16">
	<!-- Setup Wizard -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-welcome-learn-more"></span> <?php echo esc_html__( 'Setup Wizard', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body">
			<p class="sp-text-sm sp-text-muted sp-mb-16"><?php echo esc_html__( 'Walk through the guided setup again to reconfigure SitePulse step by step.', 'sitepulse' ); ?></p>
			<?php if ( class_exists( 'Sitepulse_Onboarding' ) ) :
				$sp_onboarding = Sitepulse_Onboarding::getInstance();
			?>
				<button type="button" class="sp-btn sp-btn-outline sp-btn-sm sp-wizard-restart-btn"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'sitepulse_wizard_nonce' ) ); ?>">
					<span class="dashicons dashicons-update"></span> <?php echo esc_html__( 'Restart Wizard', 'sitepulse' ); ?>
				</button>
			<?php endif; ?>
		</div>
	</div>

	<!-- Interface Mode -->
	<div class="sp-card">
		<div class="sp-card-header">
			<h3 class="sp-card-title"><span class="dashicons dashicons-layout"></span> <?php echo esc_html__( 'Interface Mode', 'sitepulse' ); ?></h3>
		</div>
		<div class="sp-card-body">
			<p class="sp-text-sm sp-text-muted sp-mb-16">
				<?php echo esc_html__( 'You\'re using the simplified view. Switch to Advanced for more detailed controls.', 'sitepulse' ); ?>
			</p>
			<button type="button" class="sp-btn sp-btn-outline sp-btn-sm sp-sidebar-toggle-classic">
				<span class="dashicons dashicons-desktop"></span> <?php echo esc_html__( 'Switch to Advanced', 'sitepulse' ); ?>
			</button>
		</div>
	</div>
</div>

</div><!-- /sp-zone--tools -->

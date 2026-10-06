<?php
/**
 * SitePulse Onboarding Template
 * Welcome wizard for first-time users: choose an interface, configure
 * tracking, then get a real first check-up of the homepage.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap sitepulse-onboarding-wrapper">
	<div class="sitepulse-onboarding-container">
		<!-- Hero Header with Logo -->
		<div class="onboarding-hero">
			<div class="onboarding-hero-content">
				<div class="onboarding-hero-logo">
					<img src="<?php echo esc_url( SITEPULSE_ADMIN_ASSETS_IMG_URL . 'sitepulse-logo.png' ); ?>" alt="SitePulse Logo">
				</div>
				<div class="onboarding-hero-text">
					<h1><?php echo esc_html__( 'Welcome to SitePulse!', 'sitepulse' ); ?></h1>
					<p><?php echo esc_html__( 'Three quick steps, and you will know what slows your site down.', 'sitepulse' ); ?></p>
				</div>
			</div>
		</div>

		<!-- WordPress-Style Stepper -->
		<div class="onboarding-stepper">
			<ul class="stepper-steps">
				<li class="stepper-step active" data-step="1">
					<div class="stepper-step-number">1</div>
					<div class="stepper-step-label"><?php echo esc_html__( 'Your dashboard', 'sitepulse' ); ?></div>
				</li>
				<li class="stepper-step" data-step="2">
					<div class="stepper-step-number">2</div>
					<div class="stepper-step-label"><?php echo esc_html__( 'Tracking', 'sitepulse' ); ?></div>
				</li>
				<li class="stepper-step" data-step="3">
					<div class="stepper-step-number">3</div>
					<div class="stepper-step-label"><?php echo esc_html__( 'First check-up', 'sitepulse' ); ?></div>
				</li>
			</ul>
		</div>

		<!-- Onboarding Steps Content -->
		<div class="onboarding-content">
			<!-- Step 1: Interface -->
			<div class="onboarding-step active" data-step="1">
				<div class="step-header">
					<h2><?php echo esc_html__( 'How do you want to see your site\'s health?', 'sitepulse' ); ?></h2>
					<p><?php echo esc_html__( 'Pick the dashboard that suits you. Both measure exactly the same things.', 'sitepulse' ); ?></p>
				</div>

				<div class="step-body">
					<div class="view-mode-selector" role="radiogroup" aria-label="<?php echo esc_attr__( 'Dashboard', 'sitepulse' ); ?>">
						<!-- Simple (Easy Mode) -->
						<div class="view-mode-card selected" data-view="simple" role="radio" tabindex="0" aria-checked="true">
							<div class="view-mode-header">
								<div class="view-mode-icon dashicons dashicons-visibility"></div>
								<h3><?php echo esc_html__( 'Simple', 'sitepulse' ); ?></h3>
								<span class="badge bg-info"><?php echo esc_html__( 'Recommended', 'sitepulse' ); ?></span>
							</div>
							<div class="view-mode-body">
								<p class="view-mode-description"><?php echo esc_html__( 'A clear dashboard that explains what each number means and what to do next.', 'sitepulse' ); ?></p>
								<ul class="view-mode-features">
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Health score at a glance', 'sitepulse' ); ?></span></li>
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Guided checklist and plain-English tips', 'sitepulse' ); ?></span></li>
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Light and dark themes', 'sitepulse' ); ?></span></li>
								</ul>
							</div>
						</div>

						<!-- Advanced (classic) -->
						<div class="view-mode-card" data-view="advanced" role="radio" tabindex="0" aria-checked="false">
							<div class="view-mode-header">
								<div class="view-mode-icon dashicons dashicons-editor-code"></div>
								<h3><?php echo esc_html__( 'Advanced', 'sitepulse' ); ?></h3>
								<span class="badge bg-primary"><?php echo esc_html__( 'For developers', 'sitepulse' ); ?></span>
							</div>
							<div class="view-mode-body">
								<p class="view-mode-description"><?php echo esc_html__( 'Every metric on one screen, with the technical detail developers expect.', 'sitepulse' ); ?></p>
								<ul class="view-mode-features">
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Hook-level timings', 'sitepulse' ); ?></span></li>
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Slow queries and autoloaded options', 'sitepulse' ); ?></span></li>
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Full PageSpeed data', 'sitepulse' ); ?></span></li>
								</ul>
							</div>
						</div>
					</div>

					<div class="view-mode-notice">
						<div class="alert alert-info">
							<span class="dashicons dashicons-info"></span>
							<div>
								<?php echo esc_html__( 'You can switch at any time: "Advanced View" at the bottom of the Simple sidebar, "Simple view" at the top of the Advanced dashboard.', 'sitepulse' ); ?>
							</div>
						</div>
					</div>

					<input type="hidden" id="onboarding_view_mode" value="simple">
				</div>
			</div>

			<!-- Step 2: Tracking & Configuration -->
			<div class="onboarding-step" data-step="2">
				<div class="step-header">
					<h2><?php echo esc_html__( 'What should SitePulse watch?', 'sitepulse' ); ?></h2>
					<p><?php echo esc_html__( 'Both trackers are on by default. You can pause them later without losing any data.', 'sitepulse' ); ?></p>
				</div>

				<div class="step-body">
					<div class="tracking-options mb-4">
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="tracking-option-card h-100" for="onboarding_profiler_enabled">
									<div class="tracking-option-header">
										<div class="tracking-option-icon dashicons dashicons-performance"></div>
										<div class="tracking-option-toggle">
											<span class="form-switch">
												<input type="checkbox" class="form-check-input" id="onboarding_profiler_enabled" checked>
											</span>
										</div>
									</div>
									<h3><?php echo esc_html__( 'Performance Monitor', 'sitepulse' ); ?></h3>
									<p class="small text-muted"><?php echo esc_html__( 'Measures how long each plugin and your theme take while pages load.', 'sitepulse' ); ?></p>
								</label>
							</div>
							<div class="col-md-6 mb-3">
								<label class="tracking-option-card h-100" for="onboarding_curl_enabled">
									<div class="tracking-option-header">
										<div class="tracking-option-icon dashicons dashicons-rest-api"></div>
										<div class="tracking-option-toggle">
											<span class="form-switch">
												<input type="checkbox" class="form-check-input" id="onboarding_curl_enabled" checked>
											</span>
										</div>
									</div>
									<h3><?php echo esc_html__( 'External Requests', 'sitepulse' ); ?></h3>
									<p class="small text-muted"><?php echo esc_html__( 'Records slow calls your site makes to outside services and APIs.', 'sitepulse' ); ?></p>
								</label>
							</div>
						</div>
					</div>

					<div class="configuration-section">
						<h3><?php echo esc_html__( 'Optional', 'sitepulse' ); ?></h3>
						<div class="config-option mb-3">
							<label class="form-switch">
								<input type="checkbox" class="form-check-input" id="onboarding_savequeries">
								<span class="form-check-label"><?php echo esc_html__( 'Track slow database queries', 'sitepulse' ); ?></span>
							</label>
							<p class="small text-muted"><?php echo esc_html__( 'Turns on WordPress query logging (adds SAVEQUERIES to wp-config.php). Best for troubleshooting sessions.', 'sitepulse' ); ?></p>
						</div>
						<div class="config-option mb-3">
							<label class="form-switch">
								<input type="checkbox" class="form-check-input" id="onboarding_external_api_enabled" checked>
								<span class="form-check-label"><?php echo esc_html__( 'PageSpeed and AI reports', 'sitepulse' ); ?></span>
							</label>
							<p class="small text-muted"><?php echo esc_html__( 'Sends performance metrics (never content or personal data) to api.sitepulse.me for PageSpeed scores and AI recommendations.', 'sitepulse' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<!-- Step 3: First check-up of the homepage -->
			<div class="onboarding-step" data-step="3">
				<div class="step-header">
					<h2><?php echo esc_html__( 'Your first check-up', 'sitepulse' ); ?></h2>
					<p><?php echo esc_html__( 'SitePulse is loading your homepage as a visitor would and measuring everything WordPress does to build it.', 'sitepulse' ); ?></p>
				</div>

				<div class="step-body">
					<div class="onboarding-checkup">
						<div class="checkup-progress">
							<div class="checkup-progress-head">
								<span class="checkup-spinner" aria-hidden="true"></span>
								<strong class="checkup-progress-text" aria-live="polite"><?php echo esc_html__( 'Getting ready…', 'sitepulse' ); ?></strong>
							</div>
							<div class="checkup-bar"><div class="checkup-bar-fill"></div></div>
						</div>

						<div class="checkup-result" hidden></div>

						<div class="checkup-unavailable" hidden>
							<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
							<div>
								<strong><?php echo esc_html__( 'The quick check could not run on this server.', 'sitepulse' ); ?></strong>
								<p class="checkup-unavailable-reason"></p>
								<p><?php echo esc_html__( 'No problem: you can analyze any page from Page Analysis, and it can measure your own visit instead.', 'sitepulse' ); ?></p>
							</div>
						</div>

						<div class="checkup-next" hidden>
							<h3><?php echo esc_html__( 'What next?', 'sitepulse' ); ?></h3>
							<div class="checkup-next-grid">
								<a class="checkup-next-item" href="<?php echo esc_url( Sitepulse_Page_Analysis::admin_link() ); ?>">
									<span class="dashicons dashicons-search" aria-hidden="true"></span>
									<strong><?php echo esc_html__( 'Analyze another page', 'sitepulse' ); ?></strong>
									<span><?php echo esc_html__( 'A product, a post, your shop: any page that feels slow.', 'sitepulse' ); ?></span>
								</a>
								<a class="checkup-next-item" href="<?php echo esc_url( admin_url( 'admin.php?page=wpsp_sitepulse&sp_view=resource-load' ) ); ?>">
									<span class="dashicons dashicons-admin-plugins" aria-hidden="true"></span>
									<strong><?php echo esc_html__( 'Find your heaviest plugins', 'sitepulse' ); ?></strong>
									<span><?php echo esc_html__( 'Rankings across your whole site, updated as visitors browse.', 'sitepulse' ); ?></span>
								</a>
								<a class="checkup-next-item" href="<?php echo esc_url( admin_url( 'admin.php?page=wpsp_sitepulse' ) ); ?>">
									<span class="dashicons dashicons-dashboard" aria-hidden="true"></span>
									<strong><?php echo esc_html__( 'Open your dashboard', 'sitepulse' ); ?></strong>
									<span><?php echo esc_html__( 'Health score, PageSpeed results and AI recommendations.', 'sitepulse' ); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Navigation Buttons -->
		<div class="onboarding-navigation">
			<button type="button" class="btn btn-outline-secondary" id="skipOnboarding">
				<?php echo esc_html__( 'Skip Setup', 'sitepulse' ); ?>
			</button>
			<div class="nav-buttons-right">
				<button type="button" class="btn btn-outline-primary" id="prevStep" style="display: none;">
					<?php echo esc_html__( 'Previous', 'sitepulse' ); ?>
				</button>
				<button type="button" class="btn btn-primary" id="nextStep">
					<?php echo esc_html__( 'Next', 'sitepulse' ); ?>
				</button>
				<button type="button" class="btn btn-success" id="finishOnboarding" style="display: none;">
					<?php echo esc_html__( 'Finish and open dashboard', 'sitepulse' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

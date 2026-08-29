<?php
/**
 * SitePulse Onboarding Template
 * Welcome wizard for first-time users
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
					<p><?php echo esc_html__( 'Let\'s get your site performance monitoring set up in just a few steps', 'sitepulse' ); ?></p>
				</div>
			</div>
		</div>

		<!-- WordPress-Style Stepper -->
		<div class="onboarding-stepper">
			<ul class="stepper-steps">
				<li class="stepper-step active" data-step="1">
					<div class="stepper-step-number">1</div>
					<div class="stepper-step-label"><?php echo esc_html__( 'Welcome', 'sitepulse' ); ?></div>
				</li>
				<li class="stepper-step" data-step="2">
					<div class="stepper-step-number">2</div>
					<div class="stepper-step-label"><?php echo esc_html__( 'Configuration', 'sitepulse' ); ?></div>
				</li>
				<li class="stepper-step" data-step="3">
					<div class="stepper-step-number">3</div>
					<div class="stepper-step-label"><?php echo esc_html__( 'Finish', 'sitepulse' ); ?></div>
				</li>
			</ul>
		</div>

		<!-- Onboarding Steps Content -->
		<div class="onboarding-content">
			
			<!-- Step 1: Welcome & View Mode -->
			<div class="onboarding-step active" data-step="1">
				<div class="step-header">
					<h2><?php echo esc_html__( 'Welcome to SitePulse', 'sitepulse' ); ?></h2>
					<p><?php echo esc_html__( 'Choose your dashboard experience level and get started.', 'sitepulse' ); ?></p>
				</div>
				
				<div class="step-body">
					<div class="view-mode-selector">
						<!-- Basic View Card -->
						<div class="view-mode-card selected" data-view="basic">
							<div class="view-mode-header">
								<div class="view-mode-icon dashicons dashicons-visibility"></div>
								<h3><?php echo esc_html__( 'Basic View', 'sitepulse' ); ?></h3>
								<span class="badge bg-info"><?php echo esc_html__( 'Recommended', 'sitepulse' ); ?></span>
							</div>
							<div class="view-mode-body">
								<p class="view-mode-description"><?php echo esc_html__( 'Simplified interface with essential metrics and insights.', 'sitepulse' ); ?></p>
								<ul class="view-mode-features">
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'User-friendly dashboard', 'sitepulse' ); ?></span></li>
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Key performance metrics', 'sitepulse' ); ?></span></li>
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Actionable insights', 'sitepulse' ); ?></span></li>
								</ul>
							</div>
							<div class="view-mode-footer">
								<button type="button" class="btn btn-primary select-view-btn" data-view="basic">
									<span class="dashicons dashicons-yes" style="margin-right: 5px;"></span><?php echo esc_html__( 'Selected', 'sitepulse' ); ?>
								</button>
							</div>
						</div>
						
						<!-- Developer View Card -->
						<div class="view-mode-card" data-view="developer">
							<div class="view-mode-header">
								<div class="view-mode-icon dashicons dashicons-editor-code"></div>
								<h3><?php echo esc_html__( 'Developer View', 'sitepulse' ); ?></h3>
								<span class="badge bg-primary"><?php echo esc_html__( 'Advanced', 'sitepulse' ); ?></span>
							</div>
							<div class="view-mode-body">
								<p class="view-mode-description"><?php echo esc_html__( 'Technical interface with detailed debugging tools.', 'sitepulse' ); ?></p>
								<ul class="view-mode-features">
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Technical metrics & hook timings', 'sitepulse' ); ?></span></li>
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Database & API monitoring', 'sitepulse' ); ?></span></li>
									<li><span class="dashicons dashicons-yes"></span><span><?php echo esc_html__( 'Memory profiling scripts', 'sitepulse' ); ?></span></li>
								</ul>
							</div>
							<div class="view-mode-footer">
								<button type="button" class="btn btn-outline-primary select-view-btn" data-view="developer">
									<?php echo esc_html__( 'Choose Developer View', 'sitepulse' ); ?>
								</button>
							</div>
						</div>
					</div>
					
					<div class="view-mode-notice">
						<div class="alert alert-info">
							<span class="dashicons dashicons-info"></span>
							<div>
								<strong><?php echo esc_html__( 'Note:', 'sitepulse' ); ?></strong>
								<?php echo esc_html__( 'You can switch between Basic and Developer views anytime from the Settings page.', 'sitepulse' ); ?>
							</div>
						</div>
					</div>
					<input type="hidden" id="onboarding_view_mode" value="basic">
				</div>
			</div>

			<!-- Step 2: Tracking & Configuration -->
			<div class="onboarding-step" data-step="2">
				<div class="step-header">
					<h2><?php echo esc_html__( 'Configure Tracking', 'sitepulse' ); ?></h2>
					<p><?php echo esc_html__( 'Select the monitoring features you want to activate and fine-tune your settings.', 'sitepulse' ); ?></p>
				</div>
				
				<div class="step-body">
					<div class="tracking-options mb-4">
						<div class="row">
							<div class="col-md-6 mb-3">
								<div class="tracking-option-card h-100">
									<div class="tracking-option-header">
										<div class="tracking-option-icon dashicons dashicons-performance"></div>
										<div class="tracking-option-toggle">
											<label class="form-switch">
												<input type="checkbox" class="form-check-input" id="onboarding_profiler_enabled" checked>
											</label>
										</div>
									</div>
									<h3><?php echo esc_html__( 'LoadSentinel', 'sitepulse' ); ?></h3>
									<p class="small text-muted"><?php echo esc_html__( 'Track hooks and execution times.', 'sitepulse' ); ?></p>
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<div class="tracking-option-card h-100">
									<div class="tracking-option-header">
										<div class="tracking-option-icon dashicons dashicons-admin-plugins"></div>
										<div class="tracking-option-toggle">
											<label class="form-switch">
												<input type="checkbox" class="form-check-input" id="onboarding_curl_enabled" checked>
											</label>
										</div>
									</div>
									<h3><?php echo esc_html__( 'API Monitor', 'sitepulse' ); ?></h3>
									<p class="small text-muted"><?php echo esc_html__( 'Monitor HTTP requests and API calls.', 'sitepulse' ); ?></p>
								</div>
							</div>
						</div>
					</div>

					<div class="configuration-section">
						<h3><?php echo esc_html__( 'Advanced Settings', 'sitepulse' ); ?></h3>
						<div class="config-option mb-3">
							<label class="form-switch">
								<input type="checkbox" class="form-check-input" id="onboarding_savequeries">
								<span class="form-check-label"><?php echo esc_html__( 'Enable Query Tracking', 'sitepulse' ); ?></span>
							</label>
							<p class="small text-muted"><?php echo esc_html__( 'Track slow database queries (modifies wp-config.php).', 'sitepulse' ); ?></p>
						</div>
						<div class="config-option mb-3">
							<label class="form-switch">
								<input type="checkbox" class="form-check-input" id="onboarding_external_api_enabled" checked>
								<span class="form-check-label"><?php echo esc_html__( 'Enable PageSpeed & AI Reports', 'sitepulse' ); ?></span>
							</label>
							<p class="small text-muted"><?php echo esc_html__( 'Receive AI-powered diagnostics based on performance metrics.', 'sitepulse' ); ?></p>
						</div>
					</div>

					<div class="tips-section mt-4 pt-3 border-top">
						<div class="tip-card py-2 px-3">
							<p class="mb-0 small"><span class="dashicons dashicons-lightbulb text-warning" style="vertical-align: middle;"></span> <strong><?php echo esc_html__( 'Tip:', 'sitepulse' ); ?></strong> <?php echo esc_html__( 'You can manage these settings anytime from the SitePulse Settings page.', 'sitepulse' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<!-- Step 3: Data Collection & Finish -->
			<div class="onboarding-step" data-step="3">
				<div class="step-header text-center">
					<h2><?php echo esc_html__( 'Finalizing Setup', 'sitepulse' ); ?></h2>
					<p><?php echo esc_html__( 'Gathering initial performance insights for your site...', 'sitepulse' ); ?></p>
				</div>
				
				<div class="step-body">
					<!-- Data Collection (Initially Visible) -->
					<div class="data-collection-container">
						<div class="collection-status text-center">
							<div class="collection-icon mb-3">
								<div class="spinner-border text-primary" role="status"></div>
							</div>
							<h3 class="collection-title"><?php echo esc_html__( 'Preparing data...', 'sitepulse' ); ?></h3>
						</div>
						
						<div class="collection-progress my-4">
							<div class="progress" style="height: 10px;">
								<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
							</div>
							<p class="progress-text text-center mt-2 small text-muted"><?php echo esc_html__( 'Initializing...', 'sitepulse' ); ?></p>
						</div>
						
						<div class="collection-details small">
							<div class="detail-item" data-task="profiler">
								<span class="detail-text"><?php echo esc_html__( 'Loading profiler data...', 'sitepulse' ); ?></span>
								<span class="detail-status pending float-end"><?php echo esc_html__( 'Pending', 'sitepulse' ); ?></span>
							</div>
							<div class="detail-item" data-task="hooks">
								<span class="detail-text"><?php echo esc_html__( 'Analyzing hooks and filters...', 'sitepulse' ); ?></span>
								<span class="detail-status pending float-end"><?php echo esc_html__( 'Pending', 'sitepulse' ); ?></span>
							</div>
							<div class="detail-item" data-task="http">
								<span class="detail-text"><?php echo esc_html__( 'Checking HTTP requests...', 'sitepulse' ); ?></span>
								<span class="detail-status pending float-end"><?php echo esc_html__( 'Pending', 'sitepulse' ); ?></span>
							</div>
							<div class="detail-item" data-task="memory">
								<span class="detail-text"><?php echo esc_html__( 'Gathering memory usage...', 'sitepulse' ); ?></span>
								<span class="detail-status pending float-end"><?php echo esc_html__( 'Pending', 'sitepulse' ); ?></span>
							</div>
							<div class="detail-item" data-task="plugins">
								<span class="detail-text"><?php echo esc_html__( 'Profiling installed plugins...', 'sitepulse' ); ?></span>
								<span class="detail-status pending float-end"><?php echo esc_html__( 'Pending', 'sitepulse' ); ?></span>
							</div>
						</div>
						
						<!-- Completion Message (Initially Hidden) -->
						<div class="collection-complete text-center mt-4" style="display: none;">
							<div class="completion-icon dashicons dashicons-yes-alt mb-3"></div>
							<h3><?php echo esc_html__( 'You\'re All Set!', 'sitepulse' ); ?></h3>
							<p><?php echo esc_html__( 'SitePulse is now monitoring your site.', 'sitepulse' ); ?></p>
							
							<div class="completion-actions mt-4">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpsp_sitepulse' ) ); ?>" class="btn btn-primary btn-lg px-5">
									<?php echo esc_html__( 'Go to Dashboard', 'sitepulse' ); ?>
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
					<?php echo esc_html__( 'Finish Setup', 'sitepulse' ); ?>
				</button>
			</div>
		</div>

	</div>
</div>


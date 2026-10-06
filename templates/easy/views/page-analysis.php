<?php
/**
 * Page Analysis — analyze any front-end URL and read what slows it down.
 * Shared by Easy Mode and the classic (advanced) dashboard.
 *
 * Available vars: $analysis_suggestions, $analysis_recent, $analysis_request.
 * The report itself is rendered by assets/js/page-analysis.js.
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_pa_suggestions = isset( $analysis_suggestions ) ? $analysis_suggestions : array();
$sp_pa_recent      = isset( $analysis_recent ) ? $analysis_recent : array();
$sp_pa_request     = isset( $analysis_request ) ? $analysis_request : array( 'url' => '', 'autorun' => false, 'analysis' => '' );
$sp_pa_url         = '' !== $sp_pa_request['url'] ? $sp_pa_request['url'] : home_url( '/' );
?>
<div class="sp-pa" data-autorun="<?php echo $sp_pa_request['autorun'] ? '1' : '0'; ?>" data-analysis="<?php echo esc_attr( $sp_pa_request['analysis'] ); ?>">

	<div class="sp-pa-header">
		<h2 class="sp-pa-heading"><?php echo esc_html__( 'Page Analysis', 'sitepulse' ); ?></h2>
		<p class="sp-pa-subheading"><?php echo esc_html__( 'Find out exactly what slows down any page on your site: products, posts, archives, your shop or search results.', 'sitepulse' ); ?></p>
	</div>

	<form class="sp-pa-card sp-pa-form" novalidate>
		<label for="sp-pa-url" class="sp-pa-label"><?php echo esc_html__( 'Page address', 'sitepulse' ); ?></label>
		<div class="sp-pa-input-row">
			<span class="sp-pa-input-wrap">
				<span class="dashicons dashicons-admin-links" aria-hidden="true"></span>
				<input type="text" id="sp-pa-url" class="sp-pa-input" list="sp-pa-suggestions" value="<?php echo esc_attr( $sp_pa_url ); ?>" placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>" autocomplete="off" spellcheck="false" aria-describedby="sp-pa-help">
			</span>
			<button type="submit" class="sp-pa-btn sp-pa-btn--primary sp-pa-submit">
				<span class="dashicons dashicons-search" aria-hidden="true"></span>
				<span class="sp-pa-submit-label"><?php echo esc_html__( 'Analyze page', 'sitepulse' ); ?></span>
			</button>
		</div>
		<datalist id="sp-pa-suggestions">
			<?php foreach ( $sp_pa_suggestions as $sp_pa_suggestion ) : ?>
				<option value="<?php echo esc_attr( $sp_pa_suggestion['url'] ); ?>" label="<?php echo esc_attr( $sp_pa_suggestion['label'] ); ?>"></option>
			<?php endforeach; ?>
		</datalist>
		<?php if ( $sp_pa_suggestions ) : ?>
			<div class="sp-pa-chips">
				<span class="sp-pa-chips-label"><?php echo esc_html__( 'Try:', 'sitepulse' ); ?></span>
				<?php foreach ( array_slice( $sp_pa_suggestions, 0, 5 ) as $sp_pa_suggestion ) : ?>
					<button type="button" class="sp-pa-chip" data-url="<?php echo esc_attr( $sp_pa_suggestion['url'] ); ?>" title="<?php echo esc_attr( $sp_pa_suggestion['url'] ); ?>"><?php echo esc_html( $sp_pa_suggestion['label'] ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<p class="sp-pa-help" id="sp-pa-help">
			<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
			<?php echo esc_html__( 'SitePulse loads the page a few times as a logged-out visitor and measures everything WordPress does to build it. It takes a few seconds and does not affect your visitors.', 'sitepulse' ); ?>
		</p>
		<p class="sp-pa-error" role="alert" hidden></p>
	</form>

	<div class="sp-pa-card sp-pa-progress" hidden>
		<div class="sp-pa-progress-head">
			<span class="sp-pa-spinner" aria-hidden="true"></span>
			<strong class="sp-pa-progress-text" aria-live="polite"></strong>
		</div>
		<div class="sp-pa-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
			<div class="sp-pa-bar-fill"></div>
		</div>
		<div class="sp-pa-fallback" hidden>
			<h3 class="sp-pa-fallback-title"></h3>
			<p class="sp-pa-fallback-message"></p>
			<div class="sp-pa-fallback-actions">
				<a class="sp-pa-btn sp-pa-btn--primary sp-pa-visit" href="#" target="_blank" rel="noopener"></a>
				<button type="button" class="sp-pa-btn sp-pa-btn--ghost sp-pa-cancel"><?php echo esc_html__( 'Cancel', 'sitepulse' ); ?></button>
			</div>
			<p class="sp-pa-fallback-hint"></p>
		</div>
	</div>

	<div class="sp-pa-report" hidden></div>

	<div class="sp-pa-intro"<?php echo $sp_pa_recent ? ' hidden' : ''; ?>>
		<div class="sp-pa-intro-item">
			<span class="dashicons dashicons-chart-bar" aria-hidden="true"></span>
			<h3><?php echo esc_html__( 'Where the time goes', 'sitepulse' ); ?></h3>
			<p><?php echo esc_html__( 'How long each plugin and your theme take on that page, not on average across the site.', 'sitepulse' ); ?></p>
		</div>
		<div class="sp-pa-intro-item">
			<span class="dashicons dashicons-rest-api" aria-hidden="true"></span>
			<h3><?php echo esc_html__( 'What the page waits for', 'sitepulse' ); ?></h3>
			<p><?php echo esc_html__( 'Every external request, database query count and memory peak while the page is built.', 'sitepulse' ); ?></p>
		</div>
		<div class="sp-pa-intro-item">
			<span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
			<h3><?php echo esc_html__( 'What to do about it', 'sitepulse' ); ?></h3>
			<p><?php echo esc_html__( 'Plain-English findings that point at the cause and the fix, ordered by impact.', 'sitepulse' ); ?></p>
		</div>
	</div>

	<div class="sp-pa-card sp-pa-history"<?php echo $sp_pa_recent ? '' : ' hidden'; ?>>
		<div class="sp-pa-card-head">
			<h3 class="sp-pa-card-title"><span class="dashicons dashicons-backup" aria-hidden="true"></span><?php echo esc_html__( 'Recent analyses', 'sitepulse' ); ?></h3>
			<p class="sp-pa-card-hint"><?php echo esc_html__( 'The latest result for each page you analyzed.', 'sitepulse' ); ?></p>
		</div>
		<ul class="sp-pa-history-list">
			<?php foreach ( $sp_pa_recent as $sp_pa_item ) : ?>
				<li class="sp-pa-history-item" data-id="<?php echo esc_attr( $sp_pa_item['id'] ); ?>">
					<span class="sp-pa-dot sp-pa-dot--<?php echo esc_attr( $sp_pa_item['level'] ); ?>" aria-hidden="true"></span>
					<button type="button" class="sp-pa-history-open" data-id="<?php echo esc_attr( $sp_pa_item['id'] ); ?>">
						<span class="sp-pa-history-title"><?php echo esc_html( $sp_pa_item['title'] ); ?></span>
						<span class="sp-pa-history-url"><?php echo esc_html( $sp_pa_item['url'] ); ?></span>
					</button>
					<span class="sp-pa-history-meta">
						<?php if ( '' !== $sp_pa_item['server'] ) : ?>
							<strong><?php echo esc_html( $sp_pa_item['server'] ); ?></strong>
						<?php endif; ?>
						<?php
						/* translators: %s: human-readable time difference */
						echo esc_html( sprintf( __( '%s ago', 'sitepulse' ), human_time_diff( $sp_pa_item['created_at'], time() ) ) );
						?>
					</span>
					<button type="button" class="sp-pa-icon-btn sp-pa-history-rerun" data-url="<?php echo esc_attr( $sp_pa_item['url'] ); ?>" title="<?php echo esc_attr__( 'Run again', 'sitepulse' ); ?>" aria-label="<?php echo esc_attr__( 'Run again', 'sitepulse' ); ?>">
						<span class="dashicons dashicons-update" aria-hidden="true"></span>
					</button>
					<button type="button" class="sp-pa-icon-btn sp-pa-history-delete" data-id="<?php echo esc_attr( $sp_pa_item['id'] ); ?>" title="<?php echo esc_attr__( 'Remove', 'sitepulse' ); ?>" aria-label="<?php echo esc_attr__( 'Remove', 'sitepulse' ); ?>">
						<span class="dashicons dashicons-trash" aria-hidden="true"></span>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

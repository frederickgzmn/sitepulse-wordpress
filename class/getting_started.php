<?php
/**
 * SitePulse Getting Started Class
 * A short checklist that walks new users through the features that answer
 * "why is my site slow?", ticking steps off as they are used.
 *
 * @package SitePulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sitepulse_Getting_Started
 */
class Sitepulse_Getting_Started {

	/**
	 * User meta flag set when the checklist is dismissed
	 */
	const DISMISSED_META = 'sitepulse_getting_started_dismissed';

	/**
	 * User meta listing the screens the user has opened
	 */
	const VISITED_META = 'sitepulse_getting_started_visited';

	/**
	 * Screens whose first visit completes a step
	 */
	const TRACKED_VIEWS = array( 'resource-load', 'api-monitor' );

	/**
	 * Remember that the current user opened a screen.
	 *
	 * @param string $view Screen slug
	 */
	public static function mark_visited( $view ) {
		if ( ! in_array( $view, self::TRACKED_VIEWS, true ) ) {
			return;
		}

		$visited = self::visited();
		if ( in_array( $view, $visited, true ) ) {
			return;
		}

		$visited[] = $view;
		update_user_meta( get_current_user_id(), self::VISITED_META, $visited );
	}

	/**
	 * Hide the checklist for the current user.
	 */
	public static function dismiss() {
		update_user_meta( get_current_user_id(), self::DISMISSED_META, 1 );
	}

	/**
	 * Checklist steps with their completion state.
	 *
	 * @param array $context has_ai_report, is_ai_pending and ai_enabled flags from the dashboard
	 * @return array
	 */
	public static function get_steps( array $context ) {
		$visited = self::visited();
		$base    = admin_url( 'admin.php?page=wpsp_sitepulse' );

		$steps = array(
			array(
				'id'     => 'analyze',
				'done'   => Sitepulse_Page_Analysis::has_completed_analysis(),
				'title'  => __( 'Analyze one page', 'sitepulse' ),
				'text'   => __( 'See exactly what slows down a single page. Your homepage is a good start.', 'sitepulse' ),
				'url'    => Sitepulse_Page_Analysis::admin_link( home_url( '/' ), true ),
				'action' => __( 'Analyze homepage', 'sitepulse' ),
			),
			array(
				'id'     => 'plugins',
				'done'   => in_array( 'resource-load', $visited, true ),
				'title'  => __( 'Find your heaviest plugins', 'sitepulse' ),
				'text'   => __( 'Plugin Activity ranks plugins and your theme by the time they take across the site.', 'sitepulse' ),
				'url'    => $base . '&sp_view=resource-load',
				'action' => __( 'Open Plugin Activity', 'sitepulse' ),
			),
			array(
				'id'     => 'requests',
				'done'   => in_array( 'api-monitor', $visited, true ),
				'title'  => __( 'Check external requests', 'sitepulse' ),
				'text'   => __( 'Slow calls to outside services hold up your pages until they answer.', 'sitepulse' ),
				'url'    => $base . '&sp_view=api-monitor',
				'action' => __( 'Open External Requests', 'sitepulse' ),
			),
		);

		if ( ! empty( $context['ai_enabled'] ) ) {
			$steps[] = array(
				'id'     => 'ai',
				'done'   => ! empty( $context['has_ai_report'] ) || ! empty( $context['is_ai_pending'] ),
				'title'  => __( 'Get an AI diagnosis', 'sitepulse' ),
				'text'   => __( 'Turn your measurements into a prioritized list of fixes.', 'sitepulse' ),
				'url'    => '#sp-ai-card',
				'action' => __( 'Request diagnosis', 'sitepulse' ),
			);
		}

		return $steps;
	}

	/**
	 * Whether the checklist should be shown.
	 *
	 * @param array $steps Steps from get_steps()
	 * @return bool
	 */
	public static function should_show( array $steps ) {
		if ( get_user_meta( get_current_user_id(), self::DISMISSED_META, true ) ) {
			return false;
		}

		return count( array_filter( array_column( $steps, 'done' ) ) ) < count( $steps );
	}

	/**
	 * Screens the current user has opened.
	 *
	 * @return array
	 */
	private static function visited() {
		$visited = get_user_meta( get_current_user_id(), self::VISITED_META, true );

		return is_array( $visited ) ? $visited : array();
	}
}

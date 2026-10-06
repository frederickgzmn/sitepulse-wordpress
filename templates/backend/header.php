<?php
// Prevent to access the file from outside of WordPress
if( ! defined('ABSPATH') ) {
	exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-3 sitepulse_header">
    <div class="logo_container">
        <img class="sitepulse_logo" src="<?php echo esc_url( Sitepulse_Whitelabel_Service::get_logo_url( 'header' ) ); ?>" alt="<?php echo esc_attr( Sitepulse_Whitelabel_Service::get_plugin_name() . ' logo' ); ?>">
        <h1><?php echo esc_html( Sitepulse_Whitelabel_Service::get_plugin_name() ); ?></h1>
    </div>
    <div class="heatmonitor_container">
        <div class="heatmonitor_wrapper">
        <svg id="mainSVG" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 630 200" width="630" height="200">
            <defs>
            <filter id="glow" x="-100%" y="-100%" width="250%" height="250%">
            <feGaussianBlur stdDeviation="8" result="coloredBlur" />
            <feOffset dx="0" dy="0" result="offsetblur"></feOffset>
            <feFlood id="glowAlpha" flood-color="#FFF" flood-opacity="1"></feFlood>
            <feComposite in2="offsetblur" operator="in"></feComposite>
            <feMerge>
            <feMergeNode/>
            <feMergeNode in="SourceGraphic"></feMergeNode>
            </feMerge>
            </filter>	
            </defs>
            <g filter="url(#glow)">
            <rect fill="url(#sqrs)" width="630" height="200" opacity="0.1"/>
            <polyline id="pulseLine"/>
            </g>
        </svg>
        </div>
    </div>

    <div class="header-actions">
        <button
            type="button"
            class="btn btn-sm sp-new-experience-cta"
            data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
            data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
            title="<?php echo esc_attr__( 'Switch to the simple dashboard with plain-language explanations', 'sitepulse' ); ?>"
        >
            <span class="dashicons dashicons-superhero-alt" aria-hidden="true"></span>
            <span class="sp-new-experience-cta-text"><?php echo esc_html__( 'Simple view', 'sitepulse' ); ?></span>
        </button>

        <span class="small text-muted"><?php echo esc_html__( 'Snapshot:', 'sitepulse' ); ?></span>
        <span class="badge bg-secondary small-b"><?php echo esc_html( $snapshot_time ?? esc_html__( 'Unknown', 'sitepulse' ) ); ?></span>
        <?php
        $sitepulse_stats_count = 0;
        $sitepulse_curl_count = 0;
        if ( isset( $curl_events ) || isset( $stats ) ) {
            if ( isset( $stats ) ) {
                $sitepulse_stats_count = count( $stats );
            }

            if ( isset( $curl_events ) ) {
                $sitepulse_curl_count = count( $curl_events );
            }

            ?>
            <span class="badge bg-light text-dark small-b">
            <?php
            echo esc_html( sprintf(
                /* translators: %d: total number of events (plugins/themes + cURL requests) in the snapshot */
                esc_html__( 'Total: %d', 'sitepulse' ),
                (int) ( $sitepulse_stats_count + $sitepulse_curl_count )
            ) );
            ?>
            </span>
            <?php
        }
        ?>
        <span class="badge <?php echo esc_attr( $mem['percent_class'] ); ?> sp_memory_css" id="sp_js_memory">
        <?php echo esc_html( $mem['formatted'] ); ?>
        </span>
    </div>
</div>
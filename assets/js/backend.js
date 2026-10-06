/**
 * This file should contains backend-related JavaScript codes.
 * ----------------------------------------------------------
 *
 * Plugin : SitePulse
 * Version: 1.0
 *
 */
// ---------------------------- Heat Monitor.
jQuery(function ($) {
    /**
     * Get color based on score
     */
    function getScoreColor(score) {
        if (score >= 95) {
            return '#00ff88'; // Bright vibrant green
        } else if (score >= 90) {
            return '#00ff88'; // Bright vibrant green
        } else if (score >= 85) {
            return '#00ff88'; // Bright vibrant green
        } else if (score >= 80) {
            return '#00e676'; // Bright green-teal
        } else if (score >= 75) {
            return '#5a7ce8'; // Teal-purple
        } else if (score >= 70) {
            return '#6c5ce7'; // Purple (accent-2)
        } else if (score >= 65) {
            return '#6c5ce7'; // Purple (accent-2)
        } else if (score >= 60) {
            return '#d49a5a'; // Purple-orange
        } else if (score >= 55) {
            return '#ffb86b'; // Orange (accent-3)
        } else if (score >= 50) {
            return '#ff8a5c'; // Orange-red
        } else if (score >= 45) {
            return '#ff6b6b'; // Red-orange
        } else if (score >= 40) {
            return '#ff5555'; // Red
        } else if (score >= 35) {
            return '#ff4444'; // Red
        } else if (score >= 30) {
            return '#e63939'; // Dark red
        } else if (score >= 25) {
            return '#cc2e2e'; // Very dark red
        } else if (score >= 20) {
            return '#b32424'; // Darkest red
        } else if (score >= 15) {
            return '#991a1a'; // Almost black red
        } else if (score >= 10) {
            return '#801010'; // Very dark
        } else if (score >= 5) {
            return '#660808'; // Almost black
        } else {
            return '#4d0606'; // Black red
        }
    }

    /**
     * Update circular progress for a specific element
     */
    function updateCircularProgressForElement($metric, score) {
        const angle = (score / 100) * 360;
        const color = getScoreColor(score);

        // Set CSS custom properties
        $metric.css({
            '--progress-angle': angle + 'deg',
            '--progress-color': color
        });
    }

    /**
     * Update circular progress indicators based on data-score attribute
     */
    function updateCircularProgress() {
        $('.sp-pagespeed-metric[data-score]').each(function () {
            const $metric = $(this);
            const score = parseInt($metric.attr('data-score')) || 0;
            updateCircularProgressForElement($metric, score);
        });
    }

    // Initialize on page load
    updateCircularProgress();

    /**
     * Toggle theme/plugins load active state on server.
     * wpslowhttp: string, active: boolean
     */
    async function setTPLoadActive(wpslowhttp) {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/wpslowhttp/set_active';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ wpslowhttp, _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    async function setSPReportMode(report_mode) {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/sp_report_mode/set_active';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ report_mode, _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    async function enableSaveQueries() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/save_queries/enable';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        if (res.ok) {
            showAlert('SAVEQUERIES has been enabled. Please reload the page.', 'success', 2000);

            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    // SitePulse Profiler.
    async function setSPProfilerActive(sitepulse_profiler_enabled) {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/sp_profiler/set_active';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ sitepulse_profiler_enabled, _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        setTimeout(() => {
            location.reload();
        }, 5000);

        return res.json();
    }

    // Possible dark and light mode.
    async function setDarkModeActive(dark_mode) {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/dark_mode/set_active';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ dark_mode, _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    // Possible clear load events
    async function setClearLoadEvents() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/clear_load_events/clear';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    // Possible clear cURL API events
    async function setClearCurlApiEvents() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/clear_curl_api_events/clear';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    // var spike_heatmonitor = 0;

    // Memory Checker.
    async function setMemoryChecker() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/memory_checker/check';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        // Get response text first to check if it's valid JSON
        const responseText = await res.text();
        let data = null;

        try {
            // Try to parse as JSON
            data = JSON.parse(responseText);
        } catch (err) {
            // If parsing fails, log the actual response for debugging
            console.error('Failed to parse JSON response. Status:', res.status, 'Response text:', responseText.substring(0, 200));
            // Don't throw error, just return early to prevent breaking the interval
            return;
        }

        // Check if response indicates success
        if (res.ok && data && data.success) {
            // Update the HTML element with the response data
            $('#sp_js_memory').text(data.memory.formatted);
            $('#sp_js_memory').attr('class', 'badge status-badge status-badge-compact ' + data.memory.percent_class);

            // Update memory progress bar (both regular and compact)
            if (data.memory && typeof data.memory.percent === 'string') {
                let percentValue = parseFloat(data.memory.percent.replace('%', ''));
                if (!isNaN(percentValue)) {
                    $('#sp_js_memory_progress, .status-progress-bar-compact').css('width', percentValue + '%');

                    // Calculate memory score (inverse: lower usage = higher score, matching PHP calculation)
                    // 0% RAM usage = 100 score, 100% RAM usage = 0 score
                    let memoryScore = Math.max(0, Math.min(100, Math.round(100 - percentValue)));

                    // Update memory score display
                    $('#sp-memory-score').text(memoryScore);

                    // Update data-score attribute on the metric element
                    const $memoryMetric = $('#sp-memory-metric');
                    $memoryMetric.attr('data-score', memoryScore);

                    // Update circular progress specifically for memory metric
                    updateCircularProgressForElement($memoryMetric, memoryScore);

                    // Set spike_heatmonitor based on memory.percent (0 = less spike, 6 = max)
                    if (percentValue > 10) {
                        // Map percent (0-100) to spike_heatmonitor (0-6)
                        spike_heatmonitor = Math.min(6, Math.max(0.5, Math.round(percentValue / 16.7)));
                    } else {
                        spike_heatmonitor = 0;
                    }
                } else {
                    spike_heatmonitor = 0;
                }
            } else {
                spike_heatmonitor = 0;
            }

            refreshPulse(true);
        } else {
            throw new Error('Request failed: ' + (data?.message || res.status));
        }
    }

    setInterval(async () => {
        try {
            await setMemoryChecker();
        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Profiler Load:', err);
        }

        // Each 10 seconds
    }, 10000);

    $('.sp_profiler_clear_events, .sp_curl_and_profiler_clear_events').on('click', async function () {
        try {
            const result = await setClearLoadEvents();

            showAlert('Performance data cleared.', 'success', 2000);

            setTimeout(() => {
                document.location.reload();
            }, 2000);
        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Clear Load Events:', err);
        }
    });

    $('.sp_curl_api_clear_events, .sp_curl_and_profiler_clear_events').on('click', async function () {
        try {
            const result = await setClearCurlApiEvents();

            showAlert('Cleared Curl API Events.', 'success', 2000);

            setTimeout(() => {
                document.location.reload();
            }, 2000);

        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Clear cURL API Events:', err);
        }
    });

    // Send AJAX request when clicking .sp_profiler
    $('.sp_profiler').on('click', async function () {
        var sitepulse_profiler_enabled = $(this).is(':checked');

        if (sitepulse_profiler_enabled) {
            sitepulse_profiler_enabled = 'enabled';
        } else {
            sitepulse_profiler_enabled = 'disabled';
        }

        try {
            const result = await setSPProfilerActive(sitepulse_profiler_enabled);
            // Optionally handle success, e.g. show a message or update UI
            if (jQuery('#sp-profiler').is(':checked')) {
                showAlert('Performance Monitor is enabled and tracking the site.', 'success', 2000);
            } else {
                showAlert('Performance Monitor is disabled.', 'warning', 2000);
            }
        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Profiler Load:', err);
        }
    });

    $('.fix_enable_savequeries').on('click', async function () {
        if (confirm('Are you sure you want to enable SAVEQUERIES?')) {
            try {
                await enableSaveQueries();
            } catch (err) {
                console.error('Failed to enable SAVEQUERIES:', err);
                showAlert('Could not enable SAVEQUERIES: ' + err.message, 'danger', 3000);
            }
        }
    });

    $('.sp-new-experience-cta').on('click', async function () {
        const $button = $(this);

        if ($button.hasClass('is-loading')) {
            return;
        }

        const ajaxUrl = String($button.data('ajax-url') || '');
        const nonce = String($button.data('nonce') || SitePulse.nonce || '');

        if (!ajaxUrl || !nonce) {
            showAlert('Could not start the new experience switch.', 'danger', 2500);
            return;
        }

        try {
            $button.addClass('is-loading').prop('disabled', true);

            const body = new URLSearchParams({
                action: 'sitepulse_toggle_easy_mode',
                nonce: nonce
            });

            const response = await fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString()
            });

            const responseData = await response.json().catch(() => ({}));
            if (!response.ok || (responseData && responseData.success === false)) {
                throw new Error(responseData?.data?.message || responseData?.message || String(response.status));
            }

            showAlert('New experience enabled. Reloading dashboard...', 'success', 1200);
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } catch (err) {
            console.error('Failed to switch to new experience:', err);
            showAlert('Could not switch experience. Please try again.', 'danger', 2500);
            $button.removeClass('is-loading').prop('disabled', false);
        }
    });

    // Send AJAX request when clicking .sp_report_mode
    $('.sp_report_mode').on('click', async function () {
        var sp_report_status = $(this).is(':checked');

        if (sp_report_status) {
            sp_report_status = 1;
        } else {
            sp_report_status = 0;
        }

        try {
            const result = await setSPReportMode(sp_report_status);
            // Optionally handle success, e.g. show a message or update UI

            if (result.report_mode == 1) {
                showAlert('Single Page Report is enabled.', 'success', 1000);
                $('.sitepulse_report_single_mode').show();
                // toggle sitepulse_report_mode
                $('.sitepulse_report_mode').toggleClass('rainbow-border');
            } else {
                showAlert('Single Page Report is disabled.', 'warning', 1000);
                $('.sitepulse_report_single_mode').hide();
                $('.sitepulse_report_mode').toggleClass('rainbow-border');
            }

            setTimeout(() => {
                location.reload();
            }, 2000);
        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update TP Load:', err);
        }
    });

    // Send AJAX request when clicking .sp_http_load
    $('.sp_http_load').on('click', async function () {
        var wpslowhttp = $(this).is(':checked');

        if (wpslowhttp) {
            wpslowhttp = 'enabled';
        } else {
            wpslowhttp = 'disabled';
        }

        try {
            const result = await setTPLoadActive(wpslowhttp);
            // Optionally handle success, e.g. show a message or update UI
            if (jQuery('#sp-http-load').is(':checked')) {
                showAlert('API & Request is enabled and tracking the site.', 'success', 2000);
            } else {
                showAlert('API & Request is disabled.', 'warning', 2000);
            }

            setTimeout(() => {
                location.reload();
            }, 2000);
        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update TP Load:', err);
        }
    });

    // Send AJAX request when clicking .sp_dark_mode
    $('.sp_dark_mode').on('click', async function () {
        var dark_mode = $(this).is(':checked');

        if (dark_mode) {
            dark_mode = 'darkmode';
        } else {
            dark_mode = 'lightmode';
        }

        try {
            const result = await setDarkModeActive(dark_mode);
            // Optionally handle success, e.g. show a message or update UI
            // Dark mode toggled successfully
        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Dark Mode:', err);
        }
    });

    // Heat monitor animation (vanilla JS version, no GSAP / CustomEase)

    const select = s => document.querySelector(s);
    let mainSVG = select('#mainSVG'),
        pulseLine = select('#pulseLine'),
        numPoints = 400,
        width = numPoints * 2,
        allPoints = [];

    // internal state
    let spike_heatmonitor = typeof window.spike_heatmonitor !== 'undefined' ? window.spike_heatmonitor : 0;
    let _animRunning = false;
    let _easeVariant = 'pulse-v0';
    let _rafId = null;
    let _lastBuildToken = 0;

    // Base y for line
    const BASE_Y = 110;

    // Variant amplitude scales (mirrors earlier concept)
    const VARIANT_SCALES = [0.15, 0.6, 0.8, 1.0, 1.2, 1.4]; // v0..v5

    // Simple easing curve approximating previous shape (can be refined)
    // Returns value in [0,1]
    function basePulseEase(t) {
        // composite ease: accelerate, overshoot, settle
        if (t <= 0) return 0;
        if (t >= 1) return 1;
        // blend of easeOutBack style & smoothstep
        const s = 1.70158;
        const u = --t;
        const back = (u * u * ((s + 1) * u + s) + 1); // easeOutBack
        const smooth = t * t * (3 - 2 * t); // smoothstep
        return (back * 0.55) + (smooth * 0.45);
    }

    function getVariantScale(spike) {
        const n = Number(spike) || 0;
        if (n <= 0) return VARIANT_SCALES[0];
        if (n >= 5) return VARIANT_SCALES[5];
        return VARIANT_SCALES[Math.round(n)];
    }

    function _getEaseNameForSpike(val) {
        const v = Number(val);
        if (isNaN(v) || v <= 0) return 'pulse-v0';
        if (v >= 5) return 'pulse-v5';
        return 'pulse-v' + Math.round(v);
    }

    function buildPoints() {
        if (!pulseLine || !mainSVG) return;
        // Clear
        while (pulseLine.points.numberOfItems) {
            pulseLine.points.removeItem(0);
        }
        allPoints = [];
        for (let i = 0; i < numPoints; i++) {
            let p = pulseLine.points.appendItem(mainSVG.createSVGPoint());
            p.y = BASE_Y;
            // x maps right->left similar to original
            p.x = (width - (i * (width / numPoints)));
            allPoints.push(p);
        }
    }

    // Animation parameters
    const STAGGER = 8; // ms between point starts (0.008s like original)
    const ACTIVE_DURATION = 1000; // ms animation phase
    const DELAY_AFTER = 1000; // ms rest (repeatDelay)
    const CYCLE = ACTIVE_DURATION + DELAY_AFTER; // full point cycle

    function animateLine(startTime, buildToken) {
        if (!_animRunning) return;
        const now = performance.now();
        const globalElapsed = now - startTime;

        const ampScale = getVariantScale(spike_heatmonitor);
        const verticalTravel = 100 * ampScale; // previously -=100 scaled by variant

        for (let i = 0; i < allPoints.length; i++) {
            const p = allPoints[i];
            // point-specific offset
            const pointStart = i * STAGGER;
            const localElapsed = (globalElapsed - pointStart);
            if (localElapsed < 0) {
                p.y = BASE_Y;
                continue;
            }
            const cyclePos = localElapsed % (CYCLE);
            if (cyclePos > ACTIVE_DURATION) {
                // resting portion
                p.y = BASE_Y - verticalTravel; // hold at end height until cycle restarts
                continue;
            }
            // normalized progress
            const t = cyclePos / ACTIVE_DURATION;
            const eased = basePulseEase(t);
            p.y = BASE_Y - (eased * verticalTravel);
        }

        if (buildToken === _lastBuildToken) {
            _rafId = requestAnimationFrame(() => animateLine(startTime, buildToken));
        }
    }

    function startPulseWithEase(easeName) {
        _easeVariant = easeName;
        if (_rafId) {
            cancelAnimationFrame(_rafId);
            _rafId = null;
        }
        _animRunning = true;
        const token = ++_lastBuildToken;
        animateLine(performance.now(), token);
    }

    function refreshPulse(rebuild = true) {
        if (rebuild) buildPoints();
        startPulseWithEase(_getEaseNameForSpike(spike_heatmonitor));
        window.__sitepulse_active_pulse = _easeVariant;
        window.__sitepulse_last_refresh = Date.now();
    }

    function setHeatmonitorSpike(val) {
        spike_heatmonitor = Number(val) || 0;
        startPulseWithEase(_getEaseNameForSpike(spike_heatmonitor));
        window.spike_heatmonitor = spike_heatmonitor;
        window.__sitepulse_active_pulse = _easeVariant;
    }

    // Public exposure
    window.refreshPulse = refreshPulse;
    window.setHeatmonitorSpike = setHeatmonitorSpike;
    window.spike_heatmonitor = spike_heatmonitor;

    // Initial build & start
    buildPoints();
    startPulseWithEase(_getEaseNameForSpike(spike_heatmonitor));

    // Initialize memory progress bar on page load
    function initializeMemoryProgress() {
        const memoryBadge = $('#sp_js_memory');
        if (memoryBadge.length) {
            const badgeText = memoryBadge.text();
            const percentMatch = badgeText.match(/(\d+\.?\d*)%/);
            if (percentMatch) {
                const percentValue = parseFloat(percentMatch[1]);
                if (!isNaN(percentValue)) {
                    $('#sp_js_memory_progress, .status-progress-bar-compact').css('width', percentValue + '%');
                }
            }
        }
    }

    // Initialize on page load
    initializeMemoryProgress();

    // Handle collapsible sections toggle icons
    $('.sp-widget-header').on('click', function () {
        const $icon = $(this).find('.sp-toggle-btn .dashicons');
        const target = $(this).data('bs-target');
        const $collapse = $(target);

        // Wait for Bootstrap collapse animation
        setTimeout(() => {
            if ($collapse.hasClass('show')) {
                $icon.css('transform', 'rotate(0deg)');
            } else {
                $icon.css('transform', 'rotate(-90deg)');
            }
        }, 100);
    });

    // Optional custom event support
    window.addEventListener('sitepulse:refresh', () => refreshPulse(true));

    // Toggle email blocking mode visibility
    function toggleEmailModeRow() {
        if ($('#sitepulse_email_blocking_enabled').is(':checked')) {
            $('#email_blocking_mode_row').show();
        } else {
            $('#email_blocking_mode_row').hide();
        }
    }

    // Initial state
    toggleEmailModeRow();

    // On change event
    $('#sitepulse_email_blocking_enabled').change(function () {
        toggleEmailModeRow();
    });

    // Add confirmation for dangerous operations
    $('#sitepulse_cron_disabled').change(function () {
        if ($(this).is(':checked')) {
            if (!confirm(SitePulse.cron_confirm_message)) {
                $(this).prop('checked', false);
            }
        }
    });

    $('#sitepulse_email_blocking_enabled').change(function () {
        if ($(this).is(':checked')) {
            if (!confirm(SitePulse.email_blocking_confirm_message)) {
                $(this).prop('checked', false);
                toggleEmailModeRow();
            }
        }
    });

    // Run onboarding wizard
    $('#sp_run_onboarding').on('click', function () {
        window.location.href = SitePulse.rest_url.replace('/wp-json/', '/wp-admin/') + 'admin.php?page=wpsp_sitepulse_onboarding';
    });

    // Reset onboarding
    $('#sp_reset_onboarding').on('click', async function () {
        if (!confirm('Are you sure you want to reset the onboarding wizard? This will allow you to run the setup wizard again. Your current settings will not be changed.')) {
            return;
        }

        const $btn = $(this);
        const originalHtml = $btn.html();

        // Add loading state
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Resetting...');

        try {
            await resetOnboarding();
            showAlert('Onboarding has been reset! Redirecting to setup wizard...', 'success', 2000);

            setTimeout(function () {
                window.location.href = SitePulse.rest_url.replace('/wp-json/', '/wp-admin/') + 'admin.php?page=wpsp_sitepulse_onboarding';
            }, 2000);
        } catch (err) {
            console.error('Failed to reset onboarding:', err);
            showAlert('Error resetting onboarding. Please try again.', 'danger', 3000);
            $btn.prop('disabled', false).html(originalHtml);
        }
    });

    // Reset onboarding function
    async function resetOnboarding() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/onboarding/reset';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    // Handle Collect Plugin Data button click
    $(document).on('click', '.sp-collect-plugin-data', async function (e) {
        e.preventDefault();

        const $btn = $(this);
        const originalHtml = $btn.html();

        // Disable button and show loading state
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt sp-spinning"></span> ' + 'Collecting...');

        try {
            const response = await collectPluginData();

            if (response.success) {
                showAlert('Plugin data collected successfully! Reloading page...', 'success', 2000);

                // Reload page after short delay to show updated data
                setTimeout(function () {
                    window.location.reload();
                }, 2000);
            } else {
                throw new Error(response.message || 'Failed to collect data');
            }
        } catch (err) {
            console.error('Failed to collect plugin data:', err);
            showAlert('Error collecting plugin data. Please try again.', 'danger', 3000);
            $btn.prop('disabled', false).html(originalHtml);
        }
    });

    // Collect plugin data function
    async function collectPluginData() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/plugin_profiler/collect';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    /**
     * Check vulnerabilities via REST API
     */
    async function checkVulnerabilities() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/vulnerability/check';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    /**
     * Handle vulnerability check button click
     */
    $('.sp-check-vulnerabilities-btn').on('click', async function () {
        const $btn = $(this);
        const originalHtml = $btn.html();

        // Update all buttons to loading state
        $('.sp-check-vulnerabilities-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Checking...');
        $('.sp-vulnerabilities-results').html('<div class="alert alert-info">Scanning... please wait.</div>');

        try {
            const response = await checkVulnerabilities();

            if (response.success) {
                if (response.data.vuln_wait) {
                    $('.sp-vulnerabilities-results').html('<div class="alert alert-warning"><span class="dashicons dashicons-hourglass me-1"></span> ' + response.data.message + '</div>');
                } else {
                    $('.sp-last-check-time').html('Last checked: <span class="fw-bold">' + response.data.timestamp + '</span>');

                    if (response.data.vulnerabilities && response.data.vulnerabilities.length > 0) {
                        let html = '<div class="alert alert-danger mb-0">';
                        html += '<h6 class="alert-heading"><span class="dashicons dashicons-warning"></span> Vulnerabilities Found</h6>';
                        html += '<ul class="mb-0 ps-3">';
                        response.data.vulnerabilities.forEach(function (vuln) {
                            html += '<li><strong>' + escapeHtml(vuln.name) + '</strong>';
                            if (vuln.version) {
                                html += ' <span class="badge bg-danger ms-1">' + escapeHtml(vuln.version) + '</span>';
                            }
                            html += '</li>';
                        });
                        html += '</ul></div>';
                        $('.sp-vulnerabilities-results').html(html);
                    } else {
                        $('.sp-vulnerabilities-results').html('<div class="alert alert-success mb-0"><span class="dashicons dashicons-yes-alt me-1"></span> No vulnerabilities found.</div>');
                    }

                    if (typeof showAlert === 'function') {
                        showAlert(response.data.message || 'Scan completed successfully.', 'success', 3000);
                    }
                }
            } else {
                $('.sp-vulnerabilities-results').html('<div class="alert alert-warning mb-0">Scan failed: ' + (response.data?.message || 'Unknown error') + '</div>');
                if (typeof showAlert === 'function') {
                    showAlert(response.data?.message || 'Scan failed.', 'warning', 3000);
                }
            }
        } catch (error) {
            $('.sp-vulnerabilities-results').html('<div class="alert alert-danger mb-0">Error: ' + error.message + '</div>');
            if (typeof showAlert === 'function') {
                showAlert('Error checking vulnerabilities: ' + error.message, 'danger', 3000);
            }
        } finally {
            $('.sp-check-vulnerabilities-btn').prop('disabled', false).html(originalHtml);
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    /**
     * AI Diagnostic Functions
     */
    let aiDiagnosticPollingInterval = null;
    const AI_DIAGNOSTIC_POLL_INTERVAL = 60000; // 60 seconds

    /**
     * Request AI diagnostic from server
     */
    async function requestAIDiagnostic(forceNew = false) {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/ai_diagnostic/request';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ force_new: forceNew, _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    /**
     * Check AI diagnostic status
     */
    async function checkAIDiagnosticStatus() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/ai_diagnostic/status';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Status check failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    /**
     * Start polling for AI diagnostic status
     */
    function startAIDiagnosticPolling() {
        if (aiDiagnosticPollingInterval) {
            clearInterval(aiDiagnosticPollingInterval);
        }

        aiDiagnosticPollingInterval = setInterval(async () => {
            try {
                const response = await checkAIDiagnosticStatus();
                if (response.success && response.data) {
                    const status = response.data.status;

                    // If completed or failed, stop polling and reload to show results
                    if (status === 'completed' || status === 'failed') {
                        stopAIDiagnosticPolling();

                        if (status === 'completed') {
                            showAlert('AI Diagnostic completed! Refreshing...', 'success', 2000);
                        } else {
                            showAlert('AI Diagnostic failed. Please try again.', 'danger', 3000);
                        }

                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                }
            } catch (err) {
                console.error('Failed to check AI diagnostic status:', err);
                // Don't stop polling on error - will try again next interval
            }
        }, AI_DIAGNOSTIC_POLL_INTERVAL);
    }

    /**
     * Stop polling for AI diagnostic status
     */
    function stopAIDiagnosticPolling() {
        if (aiDiagnosticPollingInterval) {
            clearInterval(aiDiagnosticPollingInterval);
            aiDiagnosticPollingInterval = null;
        }
    }

    /**
     * Handle AI diagnostic request button click
     */
    $(document).on('click', '.sp-request-ai-diagnostic-btn', async function () {
        const $btn = $(this);
        const forceNew = $btn.data('force') === true || $btn.data('force') === 'true';
        const originalHtml = $btn.html();

        // Disable button and show loading state
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Requesting...');

        try {
            const response = await requestAIDiagnostic(forceNew);

            if (response.success && response.data) {
                const status = response.data.status;

                if (status === 'completed') {
                    showAlert('AI Diagnostic completed! Refreshing...', 'success', 2000);
                    setTimeout(() => window.location.reload(), 2000);
                } else if (status === 'pending' || status === 'processing') {
                    showAlert('AI Diagnostic requested! Analysis is in progress...', 'info', 3000);

                    // Start polling
                    startAIDiagnosticPolling();

                    // Reload to show pending state
                    setTimeout(() => window.location.reload(), 2000);
                } else if (status === 'failed') {
                    showAlert('AI Diagnostic failed: ' + (response.data.message || 'Unknown error'), 'danger', 5000);
                    $btn.prop('disabled', false).html(originalHtml);
                }
            } else {
                showAlert(response.data?.message || 'Failed to request AI diagnostic', 'warning', 5000);
                $btn.prop('disabled', false).html(originalHtml);
            }
        } catch (error) {
            console.error('Failed to request AI diagnostic:', error);
            showAlert('Error: ' + error.message, 'danger', 5000);
            $btn.prop('disabled', false).html(originalHtml);
        }
    });

    /**
     * Auto-start polling if diagnostic is pending on page load
     */
    function initAIDiagnosticPolling() {
        const $widget = $('.sp-ai-diagnostic-widget');
        if ($widget.length) {
            const currentStatus = $widget.data('status');
            if (currentStatus === 'pending' || currentStatus === 'processing') {
                startAIDiagnosticPolling();
            }
        }
    }

    // Initialize AI diagnostic polling on page load
    initAIDiagnosticPolling();

    /**
     * Autoload Options Management
     */
    let autoloadDataLoaded = false;

    /**
     * Fetch autoload options from REST API
     */
    async function fetchAutoloadOptions() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/autoload_options';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    /**
     * Update autoload value for a specific option
     */
    async function updateAutoloadOption(optionId, autoload) {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/autoload_options/update';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ option_id: optionId, autoload: autoload, _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        return res.json();
    }

    /**
     * Render autoload options list
     */
    function renderAutoloadOptions(data) {
        const $loading = $('#sp-autoload-loading');
        const $error = $('#sp-autoload-error');
        const $results = $('#sp-autoload-results');
        const $list = $('#sp-autoload-list');
        const $summary = $('#sp-autoload-summary');

        $loading.addClass('d-none');
        $error.addClass('d-none');

        if (!data.success || !data.data || data.data.length === 0) {
            $list.html(
                '<div class="px-3 py-4 text-center">' +
                '<div class="mb-2"><span class="dashicons dashicons-database" style="font-size: 30px; width: 30px; height: 30px; color: var(--sp-border-color, rgba(255,255,255,.1));"></span></div>' +
                '<div class="text-muted small">No autoloaded options found.</div>' +
                '</div>'
            );
            $results.removeClass('d-none');
            return;
        }

        $summary.text(data.count + ' options · Total autoload size: ' + data.total_autoload_size_formatted);

        let html = '';
        data.data.forEach(function (opt) {
            const isAutoload = opt.autoload === 'on';
            const sizeColor = parseInt(opt.data_size) > 102400 ? '#ff4444' : (parseInt(opt.data_size) > 10240 ? '#ffb86b' : '#38d39f');
            html += '<div class="sp-detail-item" data-option-id="' + opt.option_id + '">';
            html += '<div class="sp-detail-main" style="align-items: center;">';
            html += '<div class="sp-detail-info" style="flex: 1; min-width: 0;">';
            html += '<div class="sp-detail-title" style="font-size: 12.5px; word-break: break-all;">' + escapeHtml(opt.option_name) + '</div>';
            html += '<div class="sp-detail-meta">';
            html += '<span class="sp-detail-file" style="font-size: 11px;">' + opt.data_size_formatted + '</span>';
            html += '</div></div>';
            html += '<div class="sp-detail-metrics" style="flex-shrink: 0; margin-left: 8px;">';
            const toggleId = 'autoload-toggle-' + opt.option_id;
            html += '<div class="form-check form-switch" style="margin-bottom: 0;">';
            html += '<input class="form-check-input sp-autoload-toggle" type="checkbox" role="switch" id="' + toggleId + '"';
            html += ' data-option-id="' + opt.option_id + '"';
            html += ' data-option-name="' + escapeHtml(opt.option_name) + '"';
            html += (isAutoload ? ' checked' : '') + '>';
            html += '<label class="form-check-label" for="' + toggleId + '">' + (isAutoload ? 'On' : 'Off') + '</label>';
            html += '</div></div></div></div>';
        });

        $list.html(html);
        $results.removeClass('d-none');
    }

    /**
     * Load autoload options via AJAX
     */
    async function loadAutoloadOptions() {
        const $loading = $('#sp-autoload-loading');
        const $error = $('#sp-autoload-error');
        const $results = $('#sp-autoload-results');

        $loading.removeClass('d-none');
        $error.addClass('d-none');
        $results.addClass('d-none');

        try {
            const data = await fetchAutoloadOptions();
            renderAutoloadOptions(data);
            autoloadDataLoaded = true;
        } catch (err) {
            $loading.addClass('d-none');
            $error.removeClass('d-none').find('.alert').text('Failed to load autoload options: ' + err.message);
        }
    }

    // Handle section toggle buttons (Slow Queries / Autoload Options)
    $(document).on('click', '.sp-section-toggle', function () {
        const $btn = $(this);
        const targetSection = $btn.data('section');

        // Update button styles (Segmented Control style)
        $('.sp-section-toggle').removeClass('active')
            .css({ background: 'transparent', color: 'var(--sp-text-muted, #999)' });
        $btn.addClass('active')
            .css({ background: 'var(--sp-bg-active, rgba(255,255,255,.08))', color: 'var(--sp-text-primary, #fff)' });

        // Toggle sections
        $('#sp-slow-queries-section, #sp-autoload-section').hide();
        $('#' + targetSection).show();

        // Load autoload options on first show
        if (targetSection === 'sp-autoload-section' && !autoloadDataLoaded) {
            loadAutoloadOptions();
        }
    });

    // Refresh button
    $(document).on('click', '#sp-autoload-refresh', function () {
        autoloadDataLoaded = false;
        loadAutoloadOptions();
    });

    // Toggle autoload on/off
    $(document).on('change', '.sp-autoload-toggle', async function () {
        const $toggle = $(this);
        const optionId = $toggle.data('option-id');
        const optionName = $toggle.data('option-name');
        const newAutoload = $toggle.is(':checked') ? 'on' : 'off';
        const $label = $toggle.siblings('.form-check-label');
        const $item = $toggle.closest('.sp-detail-item');

        // Visual feedback
        $item.css('opacity', '0.6');
        $toggle.prop('disabled', true);

        try {
            const response = await updateAutoloadOption(optionId, newAutoload);

            if (response.success) {
                $label.text(newAutoload);
                if (typeof showAlert === 'function') {
                    showAlert(response.message || 'Autoload updated.', 'success', 2000);
                }

                // If set to 'off', remove from list after a brief delay
                if (newAutoload === 'off') {
                    setTimeout(function () {
                        $item.slideUp(200, function () {
                            $(this).remove();
                            // Update count in summary
                            const remaining = $('#sp-autoload-list .sp-detail-item').length;
                            const $summary = $('#sp-autoload-summary');
                            const currentText = $summary.text();
                            $summary.text(currentText.replace(/^\d+/, remaining));
                        });
                    }, 500);
                }
            } else {
                // Revert toggle
                $toggle.prop('checked', !$toggle.is(':checked'));
                if (typeof showAlert === 'function') {
                    showAlert('Failed to update autoload: ' + (response.message || 'Unknown error'), 'danger', 3000);
                }
            }
        } catch (err) {
            // Revert toggle
            $toggle.prop('checked', !$toggle.is(':checked'));
            if (typeof showAlert === 'function') {
                showAlert('Error: ' + err.message, 'danger', 3000);
            }
        } finally {
            $item.css('opacity', '1');
            $toggle.prop('disabled', false);
        }
    });
});

// View Mode Helper Functions


(function () {
    // Get saved view preference or default to 'developer'
    const savedView = localStorage.getItem('sitepulse_dashboard_view') || 'developer';
    const toggleButton = document.getElementById('sp-toggle-view');
    const basicView = document.getElementById('sp-basic-view');
    const developerView = document.getElementById('sp-developer-view');

    function setView(view) {
        if (view === 'basic') {
            if (basicView != null) {
                basicView.style.display = 'block';
            }

            if (developerView != null) {
                developerView.style.display = 'none';
            }

            if (!toggleButton) return;

            toggleButton.setAttribute('data-view', 'developer');
            toggleButton.querySelector('.sp-view-label').textContent = 'Developer View';
            const iconElement = toggleButton.querySelector('.sp-view-toggle-icon .dashicons');
            if (iconElement) {
                iconElement.className = 'dashicons dashicons-editor-code';
            }
            localStorage.setItem('sitepulse_dashboard_view', 'basic');
        } else {
            if (basicView != null) {
                basicView.style.display = 'none';
            }

            if (developerView != null) {
                developerView.style.display = 'block';
            }

            if (!toggleButton) return;

            toggleButton.setAttribute('data-view', 'basic');
            toggleButton.querySelector('.sp-view-label').textContent = 'Basic View';
            const iconElement = toggleButton.querySelector('.sp-view-toggle-icon .dashicons');
            if (iconElement) {
                iconElement.className = 'dashicons dashicons-admin-users';
            }
            localStorage.setItem('sitepulse_dashboard_view', 'developer');
        }
    }

    // Set initial view
    setView(savedView);

    if (!toggleButton) return;

    // Toggle on button click
    toggleButton.addEventListener('click', function () {
        const currentView = this.getAttribute('data-view');
        setView(currentView);
    });
})();

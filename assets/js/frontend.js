/**
 * This file should contains frontend-related JavaScript codes.
 * ----------------------------------------------------------
 *
 * Plugin : SitePulse
 * Version: 1.0
 *
 */

jQuery('#curlSwitch, #loadSwitch').on('change', function () {
    if (jQuery('#curlSwitch').is(':checked') || jQuery('#loadSwitch').is(':checked')) {
        jQuery('.sitepulse-ontracking').show();
    }

    if (!jQuery('#curlSwitch').is(':checked') && !jQuery('#loadSwitch').is(':checked')) {
        jQuery('.sitepulse-ontracking').hide();
    }
});

jQuery(function ($) {
    /**
     * Toggle theme/plugins load active state on server.
     * wpslowhttp: string, active: boolean
     */
    async function setPageTPLoadActive(page_id, curlSwitch, loadSwitch) {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/wpspageloadhttp/set_active';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ page_id, curlSwitch, loadSwitch, _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        if (res.ok) {
            // add the class: .rainbow-border to the parent nearly selector .switch-item if checked
            if (curlSwitch) {
                $('#curlSwitch').closest('.switch-item').addClass('rainbow-border');
            } else {
                $('#curlSwitch').closest('.switch-item').removeClass('rainbow-border');
            }

            if (loadSwitch) {
                $('#loadSwitch').closest('.switch-item').addClass('rainbow-border');
            } else {
                $('#loadSwitch').closest('.switch-item').removeClass('rainbow-border');
            }
        }

        return res.json();
    }

    async function setRealTimeMode(real_time_status) {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/wpsprealtimemode/set_active';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ real_time_status, _wpnonce: SitePulse.nonce })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => null);
            throw new Error('Request failed: ' + (err?.message || res.status));
        }

        if (res.ok) {
            // if ( real_time_status ) {
            //     $('#curlSwitch').closest('.switch-item').addClass('rainbow-border');
            // } else {
            //     $('#curlSwitch').closest('.switch-item').removeClass('rainbow-border');
            // }
        }

        return res.json();
    }

    async function getRealTimeMode() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/wpsprealtimemode/get_active';
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

    $('#curlSwitch').on('click', async function () {

        var page_id = $(this).data('sitepulse-page-id');
        var curlSwitch = $('#curlSwitch').is(':checked') ? 1 : 0;

        try {
            const result = await setPageTPLoadActive(page_id, curlSwitch, 0);



        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Page tracker Events:', err);
        }
    });

    // Real-time tracking modal and functionality.
    let trackingInterval;
    let trackingStartTime;
    let isTracking = false;

    // Show modal on page load
    // $('#sitepulse-modal').show();

    // Close modal functionality
    $('#sitepulse-close-modal').on('click', function () {
        // Close modal and handle tracking state
        if (isTracking) {
            if (confirm('Are you sure you want to close the SitePulse tracking modal? The Real-Time tracking session will be stopped.')) {
                stopTracking();
                $('#sitepulse-modal').hide();
            }
        } else {
            // Close modal without stopping tracking
            $('#sitepulse-modal').hide();
        }
    });

    // Is the real-time tracking element present?
    if ($('[data-sitepulse-realtime="tracking"]').length > 0) {
        restoreTracking();
    }

    // Realtime tracking was active and finished
    if ($('[data-sitepulse-realtime="stopped"]').length > 0 && localStorage.getItem('sitepulse_realtime_tracking') === 'true') {
        localStorage.removeItem('sitepulse_realtime_tracking');
        var page_id = $('#curlSwitch').data('sitepulse-page-id');
        var curlSwitch = $('#curlSwitch').is(':checked') ? 1 : 0;

        // Loop through SitePulse.post_load_events and log each event's details
        Object.entries(SitePulse.post_load_events).forEach(([id, event]) => {
            // Skip events with missing essential data
            if (!event || !event.hook) {
                return;
            }

            // Generate HTML for event details
            // Format time values to milliseconds with 3 decimal places
            function formatMs(val) {
                return parseFloat(val).toFixed(3) + ' ms';
            }

            const eventHtml = `
            <div class="sitepulse-event">
                <strong>Event ID:</strong> ${id}<br>
                <strong>Hook:</strong> ${event.hook}<br>
                <strong>Priority:</strong> ${event.priority}<br>
                <strong>Signature:</strong> ${event.sig}<br>
                <strong>File/Line:</strong> ${event.fileline}<br>
                <strong>Calls:</strong> ${event.calls}<br>
                <strong>Total Time:</strong> ${formatMs(event.total_ms)}<br>
                <strong>Max Time:</strong> ${formatMs(event.max_ms)}<br>
                <strong>Avg:</strong> ${formatMs(event.avg_ms)}<br>
                <strong>Source:</strong> ${event.source}
            </div>`;

            // eventHtml dont add if empty
            $('#sitepulse-completion-info').append(eventHtml);
        });

        (async function () {
            try {
                const result = await setSPReportMode(1);
            } catch (err) {
                console.error('Failed to update Report mode:', err);
            }

            try {
                const result = await setPageTPLoadActive(page_id, curlSwitch, 0);
            } catch (err) {
                // Optionally handle error, e.g. show error message
                console.error('Failed to update Page tracker Events:', err);
            }

            showCompletionModal();
        })();
    }

    // // Close modal when clicking outside
    // $(window).on('click', function(event) {
    //     if (event.target.id === 'sitepulse-modal') {
    //         $('#sitepulse-modal').hide();
    //         if (isTracking) {
    //             stopTracking();
    //         }
    //     }
    // });

    // Realtime Tracking button in admin bar
    $('#realtime-tracking-btn').on('click', function () {
        $('#sitepulse-modal').show();
    });

    // Start tracking button
    $('#start-tracking-btn').on('click', function () {
        startTracking();
    });

    // Stop tracking button
    $('#stop-tracking-btn').on('click', function () {
        stopTracking();
    });

    async function startTracking() {
        isTracking = true;
        trackingStartTime = Date.now();

        // Update modal content when realtime activated
        $('#modal-title').text('Real-Time Tracking Enabled');
        $('#inactive-content').hide();
        $('#active-content').show();
        $('.sitepulse-tracking-description.current-page').text('Real-time tracking is active');

        localStorage.setItem('sitepulse_realtime_tracking', 'true');
        localStorage.setItem('sitepulse_tracking_start_time', trackingStartTime.toString());

        var page_id = $('#curlSwitch').data('sitepulse-page-id');
        var curlSwitch = $('#curlSwitch').is(':checked') ? 1 : 0;

        try {
            const result = await setPageTPLoadActive(page_id, curlSwitch, 1);
        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Page tracker Events:', err);
        }

        try {
            const result = await setRealTimeMode(1);

        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Page tracker Events:', err);
        }

        // Update UI
        updateTrackingStatus('tracking', 'Tracking Active');
        $('#start-tracking-btn').hide();
        $('#stop-tracking-btn').show();
        $('#tracking-info').show();
        $('#tracking-state').text('Monitoring...');

        // Start the tracking interval
        trackingInterval = setInterval(function () {
            updateTrackingDisplay();
            simulateResourceDetection();
        }, 1000);

        try {
            const result = await setSPReportMode(1);
        } catch (err) {
            console.error('Failed to update Report mode:', err);
        }



        setTimeout(() => {
            window.location.reload();
        }, 10000);
    }

    async function restoreTracking() {
        isTracking = true;

        // Restore tracking start time from localStorage or set to current time
        const savedStartTime = localStorage.getItem('sitepulse_tracking_start_time');
        trackingStartTime = savedStartTime ? parseInt(savedStartTime) : Date.now();

        // Update modal content when realtime activated
        $('#modal-title').text('Real-Time Tracking Enabled');
        $('#inactive-content').hide();
        $('#active-content').show();

        try {
            const result = await getRealTimeMode();

            if (result.success && result.real_time_status) {
                setTimeout(() => {
                    window.location.reload();
                }, 10000);
            }

            if (result.success && result.real_time_status == false) {
                await stopTracking();
                return;

            }

        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Page tracker Events:', err);
        }

        // Update UI
        updateTrackingStatus('tracking', 'Tracking Active');
        $('#start-tracking-btn').hide();
        $('#stop-tracking-btn').show();
        $('#tracking-info').show();
        $('#tracking-state').text('Monitoring...');

        // Start the tracking interval
        trackingInterval = setInterval(function () {
            updateTrackingDisplay();
            simulateResourceDetection();
        }, 1000);



        // You can add your actual tracking logic here
        // For example, monitoring network requests, performance metrics, etc.
    }

    async function stopTracking() {
        $('#modal-title').text('Real-Time Tracking Stopped');
        $('#inactive-content').show();
        $('#active-content').hide();

        isTracking = false;

        if (trackingInterval) {
            clearInterval(trackingInterval);
        }

        // Clear localStorage tracking data
        localStorage.removeItem('sitepulse_tracking_start_time');
        localStorage.removeItem('sitepulse_tracking_elapsed');
        localStorage.removeItem('sitepulse_realtime_tracking');

        // Update UI
        updateTrackingStatus('idle', 'Ready to Track');
        $('#start-tracking-btn').show();
        $('#stop-tracking-btn').hide();
        $('#tracking-info').hide();

        var page_id = $('#curlSwitch').data('sitepulse-page-id');
        var curlSwitch = $('#curlSwitch').is(':checked') ? 1 : 0;

        try {
            const result = await setPageTPLoadActive(page_id, curlSwitch, 0);
        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Page tracker Events:', err);
        }

        try {
            const result = await setRealTimeMode(0);

        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update Page tracker Events:', err);
        }


    }

    function updateTrackingStatus(status, text) {
        const statusDot = $('.sitepulse-status-dot');
        statusDot.removeClass('idle tracking error').addClass(status);
        $('.status-text').text(text);
    }

    function updateTrackingDisplay() {
        if (!isTracking) return;

        const elapsed = Date.now() - trackingStartTime;
        const minutes = Math.floor(elapsed / 60000);
        const seconds = Math.floor((elapsed % 60000) / 1000);

        // Save elapsed time to localStorage for persistence across refreshes
        localStorage.setItem('sitepulse_tracking_elapsed', elapsed.toString());

        $('#tracking-duration').text(
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
        );

        $('#resources-count').text(SitePulse.post_load_events_count);
    }

    function simulateResourceDetection() {
        // This is a simulation - replace with actual resource detection logic
        if (Math.random() < 0.3) { // 30% chance each second
            $('#tracking-state').text('Resource detected...');

            setTimeout(function () {
                if (isTracking) {
                    $('#tracking-state').text('Monitoring...');
                }
            }, 500);
        }
    }

    function showCompletionModal() {
        // Update modal title and show completion content
        $('#modal-title').text('SitePulse Tracking Complete');
        $('#inactive-content').hide();
        $('#active-content').hide();
        $('#completed-content').show();

        // Update status indicator
        updateTrackingStatus('idle', 'Tracking Complete');

        // Hide tracking controls and info
        $('.sitepulse-tracking-controls').hide();
        $('#tracking-info').hide();

        // Show the modal
        $('#sitepulse-modal').show();
    }

    // Close completion modal button
    $('#close-completed-modal').on('click', function () {
        $('#sitepulse-modal').hide();
        // Reset modal to default state
        resetModalToDefault();
    });

    function resetModalToDefault() {
        $('#modal-title').text('SitePulse Resource Tracking');
        $('#inactive-content').show();
        $('#active-content').hide();
        $('#completed-content').hide();
        $('.sitepulse-tracking-controls').show();
        updateTrackingStatus('idle', 'Ready to Track');
    }

    // Handle escape key to close modal
    $(document).on('keydown', function (event) {
        if (event.key === 'Escape' && $('#sitepulse-modal').is(':visible')) {
            $('#sitepulse-modal').hide();
            if (isTracking) {
                stopTracking();
            }
        }
    });

});
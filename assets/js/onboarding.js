/**
 * SitePulse Onboarding JavaScript
 * Handles the interactive onboarding wizard
 *
 * @package SitePulse
 */

jQuery(function ($) {
    'use strict';

    // Onboarding state
    let currentStep = 1;
    const totalSteps = 3;
    let dataCollectionComplete = false;
    let collectionInProgress = false;

    // Configuration state
    const config = {
        view_mode: 'basic',
        profiler_enabled: true,
        curl_enabled: true,
        savequeries: false,
        email_blocking: false,
        external_api_enabled: false
    };

    /**
     * Initialize onboarding
     */
    function init() {
        // Load view mode from localStorage (same key used in backend.js)
        const savedViewMode = localStorage.getItem('sitepulse_dashboard_view') || 'basic';
        if (savedViewMode === 'basic' || savedViewMode === 'developer') {
            config.view_mode = savedViewMode;
            $('#onboarding_view_mode').val(savedViewMode);

            // Update UI to reflect saved preference
            $('.view-mode-card').removeClass('selected');
            $(`.view-mode-card[data-view="${savedViewMode}"]`).addClass('selected');

            $('.select-view-btn').removeClass('btn-primary').addClass('btn-outline-primary');
            $(`.select-view-btn[data-view="${savedViewMode}"]`)
                .removeClass('btn-outline-primary')
                .addClass('btn-primary')
                .html('<span class="dashicons dashicons-yes" style="margin-right: 5px;"></span> Selected');
        }

        updateUI();
        bindEvents();
        updateProgress();
        trackProductEvent('onboarding_viewed', { step: currentStep });
        trackProductEvent('onboarding_step_viewed', { step: currentStep });
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        // Navigation buttons
        $('#nextStep').on('click', handleNext);
        $('#prevStep').on('click', handlePrev);
        $('#finishOnboarding, .sp-finish-onboarding').on('click', handleFinish);
        $('#skipOnboarding').on('click', handleSkip);
        $('#retryDataCollection').on('click', startDataCollection);

        // View mode selection
        $('.select-view-btn').on('click', function () {
            const selectedView = $(this).data('view');
            selectViewMode(selectedView);
        });

        $('.view-mode-card').on('click', function (e) {
            if (!$(e.target).hasClass('select-view-btn')) {
                const selectedView = $(this).data('view');
                selectViewMode(selectedView);
            }
        });

        // Configuration toggles
        $('#onboarding_profiler_enabled').on('change', function () {
            config.profiler_enabled = $(this).is(':checked');
        });

        $('#onboarding_curl_enabled').on('change', function () {
            config.curl_enabled = $(this).is(':checked');
        });

        $('#onboarding_savequeries').on('change', function () {
            config.savequeries = $(this).is(':checked');
        });

        $('#onboarding_email_blocking').on('change', function () {
            config.email_blocking = $(this).is(':checked');
        });

        $('#onboarding_external_api_enabled').on('change', function () {
            config.external_api_enabled = $(this).is(':checked');
        });

        // Stepper navigation (click on completed or current steps)
        $('.stepper-step').on('click', function () {
            const targetStep = parseInt($(this).data('step'));
            if (targetStep <= currentStep || $(this).hasClass('completed')) {
                goToStep(targetStep);
            }
        });
    }

    /**
     * Handle next button click
     */
    function handleNext() {
        if (currentStep < totalSteps) {
            // Mark current step as completed
            $(`.stepper-step[data-step="${currentStep}"]`).addClass('completed');

            currentStep++;
            goToStep(currentStep);

            // If moving to step 3 (data collection), trigger collection
            if (currentStep === 3 && !dataCollectionComplete) {
                startDataCollection();
            }
        }
    }

    /**
     * Handle previous button click
     */
    function handlePrev() {
        if (currentStep > 1) {
            currentStep--;
            goToStep(currentStep);
        }
    }

    /**
     * Navigate to specific step
     */
    function goToStep(step) {
        currentStep = step;

        // Hide all steps
        $('.onboarding-step').removeClass('active');

        // Show current step
        $(`.onboarding-step[data-step="${currentStep}"]`).addClass('active');

        // Update progress bar and dots
        updateProgress();
        updateUI();
        trackProductEvent('onboarding_step_viewed', { step: currentStep });
    }

    /**
     * Update stepper UI
     */
    function updateProgress() {
        // Update stepper steps
        $('.stepper-step').removeClass('active');
        $(`.stepper-step[data-step="${currentStep}"]`).addClass('active');

        // Mark previous steps as completed
        $('.stepper-step').each(function () {
            const stepNum = parseInt($(this).data('step'));
            if (stepNum < currentStep) {
                $(this).addClass('completed');
            } else if (stepNum > currentStep) {
                $(this).removeClass('completed');
            }
        });
    }

    /**
     * Update UI elements based on current step
     */
    function updateUI() {
        // Show/hide navigation buttons
        if (currentStep === 1) {
            $('#prevStep').hide();
        } else {
            $('#prevStep').show();
        }

        $('#nextStep').prop('disabled', false).text('Next');

        if (currentStep === totalSteps) {
            $('#nextStep').hide();
            $('#finishOnboarding').show();
        } else {
            $('#nextStep').show();
            $('#finishOnboarding').hide();
        }

        // Update skip button text
        if (currentStep === totalSteps || currentStep === 3) {
            $('#skipOnboarding').hide();
        } else {
            $('#skipOnboarding').show();
        }
    }

    /**
     * Select view mode
     */
    function selectViewMode(viewMode) {
        config.view_mode = viewMode;
        $('#onboarding_view_mode').val(viewMode);

        // Save to localStorage (same key used in backend.js)
        localStorage.setItem('sitepulse_dashboard_view', viewMode);

        // Update UI
        $('.view-mode-card').removeClass('selected');
        $(`.view-mode-card[data-view="${viewMode}"]`).addClass('selected');

        // Update button states
        $('.select-view-btn').removeClass('btn-primary').addClass('btn-outline-primary');
        $('.select-view-btn').html(function () {
            const view = $(this).data('view');
            return view === 'basic' ? 'Choose Basic View' : 'Choose Developer View';
        });

        $(`.select-view-btn[data-view="${viewMode}"]`)
            .removeClass('btn-outline-primary')
            .addClass('btn-primary')
            .html('<span class="dashicons dashicons-yes" style="margin-right: 5px;"></span> Selected');

        // Show confirmation
        showNotification(`${viewMode === 'basic' ? 'Basic' : 'Developer'} View selected`, 'success');
    }

    /**
     * Handle skip button
     */
    function handleSkip() {
        if (confirm('Are you sure you want to skip the setup? You can always configure SitePulse later from the Settings page.')) {
            dismissOnboarding();
        }
    }

    /**
     * Handle finish button
     */
    async function handleFinish() {
        const $buttons = $('#finishOnboarding, .sp-finish-onboarding');
        $buttons.addClass('loading').prop('disabled', true);

        try {
            // Save configuration
            await saveConfiguration();

            // Mark onboarding as completed
            await completeOnboarding();
            trackProductEvent('onboarding_completed', {
                collection_complete: dataCollectionComplete,
                external_api_enabled: config.external_api_enabled
            });

            // Show success message
            showNotification('Setup completed successfully! Redirecting to dashboard...', 'success');

            // Redirect to dashboard
            setTimeout(function () {
                window.location.href = SitePulseOnboarding.admin_url;
            }, 1500);

        } catch (error) {
            console.error('Error completing onboarding:', error);
            showNotification('Error completing setup. Please try again.', 'error');
            $buttons.removeClass('loading').prop('disabled', false);
        }
    }

    /**
     * Save configuration via REST API
     */
    async function saveConfiguration() {
        const promises = [];

        // Ensure view mode is saved to localStorage (already saved on selection)
        localStorage.setItem('sitepulse_dashboard_view', config.view_mode);

        // Save profiler status
        if (config.profiler_enabled !== true) {
            promises.push(
                makeRequest('sitepulse/v1/sp_profiler/set_active', {
                    sitepulse_profiler_enabled: config.profiler_enabled ? 'enabled' : 'disabled'
                })
            );
        }

        // Save CURL API status
        if (config.curl_enabled !== true) {
            promises.push(
                makeRequest('sitepulse/v1/wpslowhttp/set_active', {
                    wpslowhttp: config.curl_enabled ? 'enabled' : 'disabled'
                })
            );
        }

        // Enable SAVEQUERIES if requested
        if (config.savequeries) {
            promises.push(
                makeRequest('sitepulse/v1/save_queries/enable', {})
            );
        }

        // Save settings (email blocking, external API, etc)
        const settingsToUpdate = {};
        let hasSettings = false;

        if (config.email_blocking) {
            settingsToUpdate.email_blocking_enabled = config.email_blocking;
            settingsToUpdate.email_blocking_mode = 'sendmail_block';
            hasSettings = true;
        }

        // Always save the explicit cloud-sharing choice.
        settingsToUpdate.external_api_enabled = config.external_api_enabled;
        hasSettings = true;

        if (hasSettings) {
            promises.push(
                makeRequest('sitepulse/v1/settings/update', settingsToUpdate)
            );
        }

        // Wait for all promises to complete
        await Promise.all(promises);
    }

    /**
     * Mark onboarding as completed
     */
    async function completeOnboarding() {
        return makeRequest('sitepulse/v1/onboarding/complete', {});
    }

    /**
     * Dismiss onboarding
     */
    async function dismissOnboarding() {
        try {
            trackProductEvent('onboarding_skipped', { step: currentStep });
            await makeRequest('sitepulse/v1/onboarding/dismiss', {});
            window.location.href = SitePulseOnboarding.admin_url;
        } catch (error) {
            console.error('Error dismissing onboarding:', error);
            // Fallback: just redirect
            window.location.href = SitePulseOnboarding.admin_url;
        }
    }

    /**
     * Make REST API request
     */
    async function makeRequest(endpoint, data, timeoutMs = 8000) {
        const url = SitePulseOnboarding.rest_url.replace(/\/$/, '') + '/' + endpoint;
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': SitePulseOnboarding.nonce
                },
                body: JSON.stringify({
                    ...data,
                    _wpnonce: SitePulseOnboarding.nonce
                }),
                signal: controller.signal
            });

            if (!response.ok) {
                const error = await response.json().catch(() => null);
                throw new Error(error?.message || 'Request failed: ' + response.status);
            }

            return response.json();
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Request timed out');
            }
            throw error;
        } finally {
            clearTimeout(timeoutId);
        }
    }

    /**
     * Record a local product event without affecting the onboarding flow.
     */
    function trackProductEvent(event, context = {}) {
        makeRequest('sitepulse/v1/product-event', { event, context }, 5000).catch(() => {});
    }

    /**
     * Show notification message
     */
    function showNotification(message, type) {
        // Create notification element
        const $notification = $('<div>')
            .addClass('onboarding-notification')
            .addClass('notification-' + type)
            .text(message)
            .css({
                position: 'fixed',
                top: '20px',
                right: '20px',
                padding: '15px 25px',
                borderRadius: '8px',
                color: 'white',
                fontWeight: '600',
                zIndex: 9999999,
                boxShadow: '0 8px 25px rgba(0,0,0,0.3)',
                animation: 'slideInRight 0.3s ease'
            });

        // Set background color based on type
        if (type === 'success') {
            $notification.css('background', 'linear-gradient(135deg, #38d39f, #00c2a8)');
        } else if (type === 'error') {
            $notification.css('background', 'linear-gradient(135deg, #ff6b6b, #ee5a6f)');
        }

        // Append to body
        $('body').append($notification);

        // Auto remove after 3 seconds
        setTimeout(function () {
            $notification.fadeOut(300, function () {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Start data collection process
     */
    async function startDataCollection() {
        if (collectionInProgress) {
            return;
        }

        collectionInProgress = true;
        dataCollectionComplete = false;
        const collectionStartedAt = Date.now();
        let completedTasks = 0;
        let failedTasks = 0;

        $('.collection-status, .collection-progress, .collection-details').show();
        $('.collection-complete').hide();
        $('#retryDataCollection').hide();
        $('.detail-item').removeClass('active completed');
        $('.detail-status')
            .removeClass('loading complete error')
            .addClass('pending')
            .text('Pending');
        $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0);

        // Update status
        $('.collection-title').text('Collecting Data...');
        $('.progress-text').text('Starting initial check...');
        trackProductEvent('onboarding_collection_started');

        // First, ensure profiler is enabled for data collection
        try {
            await makeRequest('sitepulse/v1/sp_profiler/set_active', {
                sitepulse_profiler_enabled: 'enabled'
            }, 5000);
        } catch (error) {
            console.warn('Could not enable profiler:', error);
            // Continue anyway
        }

        const tasks = [
            { name: 'profiler', label: 'Loading profiler data...', endpoint: 'sitepulse/v1/profiler_stats' },
            { name: 'hooks', label: 'Analyzing plugin activity...', endpoint: 'sitepulse/v1/profiler_stats' },
            { name: 'http', label: 'Checking external requests...', endpoint: 'sitepulse/v1/curl_stats' },
            { name: 'memory', label: 'Gathering memory usage...', endpoint: 'sitepulse/v1/memory_info' },
            { name: 'plugins', label: 'Profiling installed plugins...', endpoint: 'sitepulse/v1/plugin_profiler_stats' }
        ];

        // Run independent checks together so onboarding never feels artificially slow.
        await Promise.all(tasks.map(async function (task) {
            const taskStartedAt = Date.now();
            // Mark as loading
            $(`.detail-item[data-task="${task.name}"]`).addClass('active');
            $(`.detail-item[data-task="${task.name}"] .detail-status`)
                .removeClass('pending')
                .addClass('loading')
                .text('Loading...');

            $('.progress-text').text(task.label);

            try {
                const data = await makeRequest(task.endpoint, {});

                // Mark as complete
                $(`.detail-item[data-task="${task.name}"]`)
                    .removeClass('active')
                    .addClass('completed');
                $(`.detail-item[data-task="${task.name}"] .detail-status`)
                    .removeClass('loading')
                    .addClass('complete')
                    .text('Complete');

                // Update stats if we got data
                if (data) {
                    updateCollectionStats(task.name, data);
                }
                completedTasks++;
                trackProductEvent('onboarding_task_completed', {
                    task: task.name,
                    duration_ms: Date.now() - taskStartedAt
                });

            } catch (error) {
                console.error(`Error collecting ${task.name} data:`, error);

                // Mark as error (but continue)
                $(`.detail-item[data-task="${task.name}"]`)
                    .removeClass('active');
                $(`.detail-item[data-task="${task.name}"] .detail-status`)
                    .removeClass('loading')
                    .addClass('error')
                    .text('Error');
                failedTasks++;
                trackProductEvent('onboarding_task_failed', {
                    task: task.name,
                    reason: error.message || 'request_failed'
                });
            } finally {
                const finishedTasks = completedTasks + failedTasks;
                const progress = Math.round((finishedTasks / tasks.length) * 100);
                $('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
            }
        }));

        // Complete progress bar
        $('.progress-bar').css('width', '100%').attr('aria-valuenow', 100);
        $('.progress-text').text(
            failedTasks > 0
                ? `${completedTasks} checks completed; ${failedTasks} need another try.`
                : 'Initial check complete!'
        );

        $('#collectionCompleteTitle').text(
            failedTasks > 0 ? 'Your dashboard is still ready' : 'Initial check complete'
        );
        $('#collectionCompleteMessage').text(
            failedTasks > 0
                ? 'Some checks could not finish. You can retry or continue to the dashboard.'
                : 'SitePulse is now monitoring your site.'
        );
        $('#retryDataCollection').toggle(failedTasks > 0);
        $('.collection-status').fadeOut(200);
        $('.collection-complete').fadeIn(300);

        dataCollectionComplete = true;
        collectionInProgress = false;
        trackProductEvent('onboarding_collection_completed', {
            completed_tasks: completedTasks,
            failed_tasks: failedTasks,
            duration_ms: Date.now() - collectionStartedAt
        });
        updateUI();
    }

    /**
     * Update collection statistics display
     */
    function updateCollectionStats(taskName, data) {
        try {
            switch (taskName) {
                case 'profiler':
                case 'hooks':
                    if (data && data.count !== undefined) {
                        $('#stat-hooks').text(data.count);
                    } else if (data && data.data && Array.isArray(data.data)) {
                        $('#stat-hooks').text(data.data.length);
                    } else {
                        // Show "Ready" if no data yet
                        $('#stat-hooks').text('0');
                    }
                    break;

                case 'http':
                    if (data && data.count !== undefined) {
                        $('#stat-http').text(data.count);
                    } else if (data && data.events && Array.isArray(data.events)) {
                        $('#stat-http').text(data.events.length);
                    } else {
                        $('#stat-http').text('0');
                    }
                    break;

                case 'memory':
                    if (data && data.memory) {
                        const memoryMB = parseFloat(data.memory);
                        $('#stat-memory').text(memoryMB.toFixed(2) + ' MB');
                    } else {
                        $('#stat-memory').text('N/A');
                    }
                    break;

                case 'plugins':
                    if (data && data.count !== undefined) {
                        $('#stat-plugins').text(data.count);
                    } else if (data && data.stats && typeof data.stats === 'object') {
                        $('#stat-plugins').text(Object.keys(data.stats).length);
                    } else {
                        $('#stat-plugins').text('0');
                    }
                    break;
            }
        } catch (error) {
            console.error('Error updating stats:', error);
            // Set default values on error
            if (taskName === 'memory') {
                $('#stat-memory').text('N/A');
            } else {
                $('#stat-' + (taskName === 'profiler' || taskName === 'hooks' ? 'hooks' : 'http')).text('0');
            }
        }
    }

    /**
     * Keyboard navigation
     */
    $(document).on('keydown', function (e) {
        // Left arrow - previous step
        if (e.keyCode === 37 && currentStep > 1) {
            handlePrev();
        }

        // Right arrow - next step
        if (e.keyCode === 39 && currentStep < totalSteps) {
            handleNext();
        }

        // Enter key - next/finish
        if (e.keyCode === 13) {
            if (currentStep === totalSteps) {
                handleFinish();
            } else {
                handleNext();
            }
        }
    });

    // Initialize when document is ready
    init();
});

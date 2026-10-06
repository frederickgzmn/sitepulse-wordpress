/**
 * SitePulse Onboarding JavaScript
 * Handles the interactive onboarding wizard: interface choice, tracking
 * configuration, and a real first check-up of the homepage.
 *
 * @package SitePulse
 */

jQuery(function ($) {
    'use strict';

    var data = window.SitePulseOnboarding || {};
    var i18n = data.i18n || {};

    // Onboarding state
    var currentStep = 1;
    var totalSteps = 3;
    var checkupStarted = false;

    // Configuration state
    var config = {
        interface: 'simple',
        profiler_enabled: true,
        curl_enabled: true,
        savequeries: false,
        email_blocking: false
    };

    /**
     * Initialize onboarding
     */
    function init() {
        bindEvents();
        goToStep(1);
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        // Navigation buttons
        $('#nextStep').on('click', handleNext);
        $('#prevStep').on('click', handlePrev);
        $('#finishOnboarding').on('click', handleFinish);
        $('#skipOnboarding').on('click', handleSkip);

        // Interface selection (cards behave like radio buttons)
        $('.view-mode-card').on('click', function () {
            selectInterface($(this).data('view'));
        }).on('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                e.stopPropagation();
                selectInterface($(this).data('view'));
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

        // Stepper navigation: completed steps can be revisited
        $('.stepper-step').on('click', function () {
            var targetStep = parseInt($(this).data('step'), 10);
            if (targetStep < currentStep) {
                goToStep(targetStep);
            }
        });

        $(document).on('keydown', handleKeys);
    }

    /**
     * Handle next button click
     */
    function handleNext() {
        if (currentStep < totalSteps) {
            goToStep(currentStep + 1);
        }
    }

    /**
     * Handle previous button click
     */
    function handlePrev() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    }

    /**
     * Navigate to specific step
     */
    function goToStep(step) {
        currentStep = step;

        $('.onboarding-step').removeClass('active').filter('[data-step="' + step + '"]').addClass('active');

        $('.stepper-step').each(function () {
            var stepNum = parseInt($(this).data('step'), 10);
            $(this).toggleClass('active', stepNum === step).toggleClass('completed', stepNum < step);
        });

        $('#prevStep').toggle(step > 1);
        $('#nextStep').toggle(step < totalSteps);
        $('#finishOnboarding').toggle(step === totalSteps);
        $('#skipOnboarding').toggle(step < totalSteps);

        if (step === totalSteps && !checkupStarted) {
            runCheckup();
        }
    }

    /**
     * Select the dashboard interface
     */
    function selectInterface(view) {
        config.interface = view;
        $('#onboarding_view_mode').val(view);

        $('.view-mode-card').removeClass('selected').attr('aria-checked', 'false');
        $('.view-mode-card[data-view="' + view + '"]').addClass('selected').attr('aria-checked', 'true');
    }

    /**
     * Analyze the homepage with the Page Analysis engine and show the result.
     */
    function runCheckup() {
        var analysis = window.SitePulsePageAnalysis;
        var blockedReason = '';

        checkupStarted = true;

        if (!analysis) {
            return Promise.resolve(showUnavailable(i18n.checkup_failed));
        }

        return analysis.run(data.home_url, {
            progress: function (text, percent) {
                $('.checkup-progress-text').text(text);
                $('.checkup-bar-fill').css('width', percent + '%');
            },
            fallback: function (result) {
                // The wizard does not wait for a manual visit; Page Analysis offers that later.
                blockedReason = result.message;
            },
            cancelled: function () {
                return blockedReason !== '';
            }
        }).then(function (report) {
            $('.checkup-progress').prop('hidden', true);
            $('.checkup-result').empty().append(analysis.renderReport(report, { compact: true })).prop('hidden', false);
            $('.checkup-next').prop('hidden', false);
        }).catch(function (error) {
            showUnavailable(blockedReason || error.message || i18n.checkup_failed);
        });
    }

    /**
     * Explain that the check-up could not run and point to the next steps.
     */
    function showUnavailable(reason) {
        $('.checkup-progress').prop('hidden', true);
        $('.checkup-unavailable-reason').text(reason);
        $('.checkup-unavailable').prop('hidden', false);
        $('.checkup-next').prop('hidden', false);
    }

    /**
     * Handle skip button
     */
    function handleSkip() {
        if (confirm(i18n.skip_confirm)) {
            dismissOnboarding();
        }
    }

    /**
     * Handle finish button
     */
    async function handleFinish() {
        var $btn = $('#finishOnboarding');
        $btn.addClass('loading').prop('disabled', true);

        try {
            // Save configuration
            await saveConfiguration();

            // Mark onboarding as completed and save the chosen interface
            await makeRequest('sitepulse/v1/onboarding/complete', { interface: config.interface });

            showNotification(i18n.finished, 'success');

            // Redirect to dashboard
            setTimeout(function () {
                window.location.href = data.admin_url;
            }, 1000);
        } catch (error) {
            console.error('Error completing onboarding:', error);
            showNotification(i18n.finish_error, 'error');
            $btn.removeClass('loading').prop('disabled', false);
        }
    }

    /**
     * Save configuration via REST API
     */
    async function saveConfiguration() {
        var promises = [];

        // Save profiler status
        if (config.profiler_enabled !== true) {
            promises.push(makeRequest('sitepulse/v1/sp_profiler/set_active', {
                sitepulse_profiler_enabled: 'disabled'
            }));
        }

        // Save CURL API status
        if (config.curl_enabled !== true) {
            promises.push(makeRequest('sitepulse/v1/wpslowhttp/set_active', {
                wpslowhttp: 'disabled'
            }));
        }

        // Enable SAVEQUERIES if requested
        if (config.savequeries) {
            promises.push(makeRequest('sitepulse/v1/save_queries/enable', {}));
        }

        // Save settings (email blocking, external API, etc)
        var settingsToUpdate = {};
        var hasSettings = false;

        if (config.email_blocking) {
            settingsToUpdate.email_blocking_enabled = config.email_blocking;
            settingsToUpdate.email_blocking_mode = 'sendmail_block';
            hasSettings = true;
        }

        if (typeof config.external_api_enabled !== 'undefined') {
            settingsToUpdate.external_api_enabled = config.external_api_enabled;
            hasSettings = true;
        }

        if (hasSettings) {
            promises.push(makeRequest('sitepulse/v1/settings/update', settingsToUpdate));
        }

        await Promise.all(promises);
    }

    /**
     * Dismiss onboarding
     */
    async function dismissOnboarding() {
        try {
            await makeRequest('sitepulse/v1/onboarding/dismiss', {});
        } catch (error) {
            console.error('Error dismissing onboarding:', error);
        }
        window.location.href = data.admin_url;
    }

    /**
     * Make REST API request
     */
    async function makeRequest(endpoint, payload) {
        var url = data.rest_url.replace(/\/$/, '') + '/' + endpoint;

        var response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': data.nonce
            },
            body: JSON.stringify($.extend({}, payload, { _wpnonce: data.nonce }))
        });

        if (!response.ok) {
            var error = await response.json().catch(function () {
                return null;
            });
            throw new Error((error && error.message) || 'Request failed: ' + response.status);
        }

        return response.json();
    }

    /**
     * Show notification message
     */
    function showNotification(message, type) {
        var $notification = $('<div>')
            .addClass('onboarding-notification notification-' + type)
            .attr('role', 'status')
            .text(message);

        $('body').append($notification);

        setTimeout(function () {
            $notification.fadeOut(300, function () {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Keyboard navigation (ignored while typing or on focused controls)
     */
    function handleKeys(e) {
        if ($(e.target).is('input, textarea, select, button, a, [role="radio"]')) {
            return;
        }

        if (e.key === 'ArrowLeft') {
            handlePrev();
        } else if (e.key === 'ArrowRight') {
            handleNext();
        } else if (e.key === 'Enter') {
            if (currentStep === totalSteps) {
                handleFinish();
            } else {
                handleNext();
            }
        }
    }

    // Initialize when document is ready
    init();
});

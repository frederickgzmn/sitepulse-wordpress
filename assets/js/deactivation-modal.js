/**
 * SitePulse Deactivation Feedback Modal
 * 
 * Intercepts plugin deactivation on plugins.php and shows a feedback modal
 * asking users why they're deactivating and a satisfaction rating.
 */
(function ($) {
    'use strict';

    var SitePulseDeactivation = {

        deactivateUrl: '',

        reasons: [
            { code: 'no_longer_needed', label: 'I no longer need the plugin' },
            { code: 'found_better', label: 'I found a better plugin' },
            { code: 'not_working', label: 'The plugin is not working' },
            { code: 'too_slow', label: "It's slowing down my site" },
            { code: 'confusing', label: "I couldn't understand how to use it" },
            { code: 'temporary', label: "It's a temporary deactivation" },
            { code: 'missing_feature', label: 'Missing a specific feature' },
            { code: 'other', label: 'Other' }
        ],

        detailRequiredReasons: [
            'found_better',
            'not_working',
            'too_slow',
            'confusing',
            'missing_feature',
            'other'
        ],

        detailPrompts: {
            found_better: {
                placeholder: 'Which plugin did you choose, and what made the difference?',
                helper: 'Plugin name and the deciding feature help us improve.'
            },
            not_working: {
                placeholder: 'What happened? For example: setup stopped, empty data, an error, or a conflict.',
                helper: 'Please include the screen or action where the problem appeared.'
            },
            too_slow: {
                placeholder: 'Which pages felt slower, and did it start after activation?',
                helper: 'A page type or rough timing is enough.'
            },
            confusing: {
                placeholder: 'What were you trying to find, and where did you get stuck?',
                helper: 'Tell us the result you expected to see.'
            },
            temporary: {
                placeholder: 'Optional: is this for troubleshooting, staging, an update, or a short pause?',
                helper: 'This detail is optional and helps us understand temporary use.'
            },
            missing_feature: {
                placeholder: 'Which specific feature did you need?',
                helper: 'A short feature name is enough.'
            },
            other: {
                placeholder: 'What is the main reason you are deactivating?',
                helper: 'A short detail is required so this response is actionable.'
            }
        },

        init: function () {
            this.interceptDeactivation();
        },

        /**
         * Intercept the "Deactivate" link click for SitePulse plugin
         */
        interceptDeactivation: function () {
            var self = this;

            // Find the deactivate link for our plugin
            var $deactivateLink = $('tr[data-plugin="sitepulse/loader.php"] .deactivate a, ' +
                '#deactivate-sitepulse, ' +
                'tr[data-slug="sitepulse"] .deactivate a');

            if ($deactivateLink.length === 0) {
                // Fallback: find any deactivate link that points to sitepulse
                $deactivateLink = $('a[href*="plugin=sitepulse"]').filter('[href*="action=deactivate"]');
            }

            if ($deactivateLink.length === 0) {
                return;
            }

            $deactivateLink.on('click', function (e) {
                e.preventDefault();
                self.deactivateUrl = $(this).attr('href');
                self.showModal();
            });
        },

        /**
         * Build and show the feedback modal
         */
        showModal: function () {
            var self = this;

            // Remove existing modal if any
            $('#sitepulse-deactivation-modal').remove();

            var reasonsHtml = '';
            $.each(this.reasons, function (i, reason) {
                reasonsHtml += '<label class="sp-deact-reason">' +
                    '<input type="radio" name="sp_deact_reason" value="' + reason.code + '" data-label="' + self.escapeHtml(reason.label) + '">' +
                    '<span class="sp-deact-reason-text">' + self.escapeHtml(reason.label) + '</span>' +
                    '</label>';
            });

            var starsHtml = '';
            for (var i = 1; i <= 5; i++) {
                starsHtml += '<span class="sp-deact-star" data-rating="' + i + '" title="' + i + ' star' + (i > 1 ? 's' : '') + '">&#9733;</span>';
            }

            var modalHtml =
                '<div id="sitepulse-deactivation-modal" class="sp-deact-overlay">' +
                '  <div class="sp-deact-modal">' +
                '    <div class="sp-deact-header">' +
                '      <h3>Quick Feedback</h3>' +
                '      <p>If you have a moment, please let us know why you are deactivating:</p>' +
                '    </div>' +
                '    <div class="sp-deact-body">' +
                '      <div class="sp-deact-reasons">' + reasonsHtml + '</div>' +
                '      <div class="sp-deact-other-wrap" style="display:none;">' +
                '        <textarea id="sp-deact-other-message" placeholder="Please share your feedback..." maxlength="1000" rows="3"></textarea>' +
                '        <p id="sp-deact-detail-helper" class="sp-deact-detail-helper"></p>' +
                '        <div class="sp-deact-char-count"><span id="sp-deact-char-current">0</span>/1000</div>' +
                '      </div>' +
                '      <div class="sp-deact-recovery" style="display:none;">' +
                '        <strong id="sp-deact-recovery-title">Want to try a quick recovery first?</strong>' +
                '        <p id="sp-deact-recovery-copy"></p>' +
                '        <div class="sp-deact-recovery-actions">' +
                '          <a class="button button-secondary" href="' + self.escapeHtml(SitePulseDeactivationData.dashboard_url || '#') + '">Open SitePulse</a>' +
                '          <a class="button button-link" target="_blank" rel="noopener noreferrer" href="' + self.escapeHtml(SitePulseDeactivationData.support_url || '#') + '">Get support</a>' +
                '        </div>' +
                '      </div>' +
                '      <div class="sp-deact-rating-section">' +
                '        <label class="sp-deact-rating-label">How would you rate your experience? <span>(optional)</span></label>' +
                '        <div class="sp-deact-stars">' + starsHtml + '</div>' +
                '        <input type="hidden" id="sp-deact-rating" value="0">' +
                '      </div>' +
                '      <p id="sp-deact-validation" class="sp-deact-validation" aria-live="polite"></p>' +
                '    </div>' +
                '    <div class="sp-deact-footer">' +
                '      <button id="sp-deact-submit" class="button button-primary" disabled>Submit &amp; Deactivate</button>' +
                '      <button id="sp-deact-skip" class="button button-link">Skip &amp; Deactivate</button>' +
                '    </div>' +
                '  </div>' +
                '</div>';

            $('body').append(modalHtml);

            // Bind events
            this.bindModalEvents();

            // Show with animation
            setTimeout(function () {
                $('#sitepulse-deactivation-modal').addClass('sp-deact-active');
            }, 10);
        },

        /**
         * Bind all modal event handlers
         */
        bindModalEvents: function () {
            var self = this;
            $(document).off('.spDeactModal');

            // Reason selection
            $(document).on('change.spDeactModal', 'input[name="sp_deact_reason"]', function () {
                var selectedCode = $(this).val();
                var prompt = self.detailPrompts[selectedCode];

                if (prompt) {
                    $('#sp-deact-other-message').attr('placeholder', prompt.placeholder);
                    $('#sp-deact-detail-helper').text(prompt.helper);
                    $('.sp-deact-other-wrap').slideDown(200);
                } else {
                    $('.sp-deact-other-wrap').slideUp(200);
                }

                self.updateRecoveryOptions(selectedCode);
                self.validateForm();
            });

            // Character count for "other" textarea
            $(document).on('input.spDeactModal', '#sp-deact-other-message', function () {
                var len = $(this).val().length;
                $('#sp-deact-char-current').text(len);
                self.validateForm();
            });

            // Star rating
            $(document).on('click.spDeactModal', '.sp-deact-star', function () {
                var rating = parseInt($(this).data('rating'));
                $('#sp-deact-rating').val(rating);

                // Visual feedback
                $('.sp-deact-star').each(function () {
                    var starVal = parseInt($(this).data('rating'));
                    $(this).toggleClass('sp-deact-star-active', starVal <= rating);
                });

                self.validateForm();
            });

            // Hover effect for stars
            $(document).on('mouseenter.spDeactModal', '.sp-deact-star', function () {
                var hoverRating = parseInt($(this).data('rating'));
                $('.sp-deact-star').each(function () {
                    var starVal = parseInt($(this).data('rating'));
                    $(this).toggleClass('sp-deact-star-hover', starVal <= hoverRating);
                });
            });

            $(document).on('mouseleave.spDeactModal', '.sp-deact-stars', function () {
                $('.sp-deact-star').removeClass('sp-deact-star-hover');
            });

            // Submit button
            $(document).on('click.spDeactModal', '#sp-deact-submit', function () {
                if ($(this).prop('disabled')) return;
                self.submitFeedback();
            });

            // Skip button
            $(document).on('click.spDeactModal', '#sp-deact-skip', function () {
                self.proceedWithDeactivation();
            });

            // Close on backdrop click
            $(document).on('click.spDeactModal', '#sitepulse-deactivation-modal', function (e) {
                if ($(e.target).hasClass('sp-deact-overlay')) {
                    self.closeModal();
                }
            });

            // Close on ESC key
            $(document).on('keydown.spDeactModal', function (e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    self.closeModal();
                }
            });
        },

        /**
         * Validate form and enable/disable submit button
         */
        validateForm: function () {
            var $selectedReason = $('input[name="sp_deact_reason"]:checked');
            var reasonCode = $selectedReason.val() || '';
            var message = $.trim($('#sp-deact-other-message').val() || '');
            var detailRequired = this.detailRequiredReasons.indexOf(reasonCode) !== -1;
            var valid = reasonCode !== '' && (!detailRequired || message.length >= 3);

            $('#sp-deact-submit').prop('disabled', !valid);

            if (reasonCode && detailRequired && message.length < 3) {
                $('#sp-deact-validation').text('Please add a short detail for this reason.');
            } else {
                $('#sp-deact-validation').text('');
            }

            return valid;
        },

        /**
         * Offer a relevant recovery path without blocking deactivation.
         */
        updateRecoveryOptions: function (reasonCode) {
            var recoveryCopy = {
                not_working: 'The dashboard may reveal whether data collection failed. Support can also help diagnose a conflict.',
                confusing: 'Open the dashboard to follow the recommended next step, or ask support where to find a result.',
                temporary: 'You can pause SitePulse monitoring from its settings and keep your configuration for later.',
                too_slow: 'You can pause the monitoring switches first and compare performance before removing SitePulse.'
            };

            if (recoveryCopy[reasonCode]) {
                $('#sp-deact-recovery-copy').text(recoveryCopy[reasonCode]);
                $('.sp-deact-recovery').slideDown(200);
            } else {
                $('.sp-deact-recovery').slideUp(200);
            }
        },

        /**
         * Submit the feedback to the API
         */
        submitFeedback: function () {
            var self = this;
            var $submitBtn = $('#sp-deact-submit');
            var $skipBtn = $('#sp-deact-skip');

            if (!this.validateForm()) {
                return;
            }

            // Get form data
            var $selectedReason = $('input[name="sp_deact_reason"]:checked');
            var reasonCode = $selectedReason.val();
            var reasonLabel = $selectedReason.data('label');
            var message = $('#sp-deact-other-message').val() || '';
            var rating = parseInt($('#sp-deact-rating').val(), 10) || 0;

            // Disable buttons during submission
            $submitBtn.prop('disabled', true).text('Submitting...');
            $skipBtn.prop('disabled', true);

            // Build payload
            var domain = SitePulseDeactivationData.domain || '';
            // Clean domain (strip protocol and trailing slash)
            domain = domain.replace(/^https?:\/\//i, '').replace(/\/$/, '');

            var payload = {
                domain: domain,
                reason_code: reasonCode,
                reason_label: reasonLabel,
                message: message.substring(0, 1000), // Enforce max length
                rating: rating,
                plugin_version: SitePulseDeactivationData.plugin_version || '',
                wp_version: SitePulseDeactivationData.wp_version || '',
                feedback_schema_version: 2,
                journey: SitePulseDeactivationData.journey || {}
            };

            // Send to API
            $.ajax({
                url: SitePulseDeactivationData.api_endpoint,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                timeout: 10000, // 10 second timeout
                success: function () {
                    self.proceedWithDeactivation();
                },
                error: function () {
                    // Even on error, proceed with deactivation — don't block the user
                    self.proceedWithDeactivation();
                }
            });
        },

        /**
         * Proceed with the actual plugin deactivation
         */
        proceedWithDeactivation: function () {
            // Clean up event handlers
            $(document).off('.spDeactModal');

            if (this.deactivateUrl) {
                window.location.href = this.deactivateUrl;
            }
        },

        /**
         * Close the modal without deactivating
         */
        closeModal: function () {
            var $modal = $('#sitepulse-deactivation-modal');
            $modal.removeClass('sp-deact-active');

            setTimeout(function () {
                $modal.remove();
            }, 300);

            $(document).off('.spDeactModal');
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function (text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function () {
        // Only initialize on the plugins page
        if (typeof SitePulseDeactivationData !== 'undefined') {
            SitePulseDeactivation.init();
        }
    });

})(jQuery);

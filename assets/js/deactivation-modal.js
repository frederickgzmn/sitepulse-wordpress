/**
 * SitePulse Deactivation Feedback Modal
 *
 * Intercepts plugin deactivation on plugins.php and shows a feedback modal
 * asking users why they're deactivating and a satisfaction rating. Some
 * reasons offer a better way out: pausing monitoring instead of deactivating,
 * a quick start for people who got lost, or a link to support.
 */
(function ($) {
    'use strict';

    var SitePulseDeactivation = {

        deactivateUrl: '',

        $trigger: null,

        // Stable English labels sent with the feedback, whatever language the screen uses.
        reasons: {
            no_longer_needed: 'I no longer need the plugin',
            found_better: 'I found a better plugin',
            not_working: 'The plugin is not working',
            too_slow: "It's slowing down my site",
            confusing: "I couldn't understand how to use it",
            temporary: "It's a temporary deactivation",
            missing_feature: 'Missing a specific feature',
            other: 'Other'
        },

        init: function () {
            this.data = window.SitePulseDeactivationData;
            this.i18n = this.data.i18n || {};
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
                self.$trigger = $(this);
                self.showModal();
            });
        },

        /**
         * Text for a key, falling back to the key itself.
         */
        t: function (key) {
            return this.i18n[key] || key;
        },

        /**
         * Build and show the feedback modal
         */
        showModal: function () {
            var self = this;

            // Remove existing modal if any
            self.removeModal();

            var reasonsHtml = '';
            $.each(this.reasons, function (code, englishLabel) {
                reasonsHtml += '<label class="sp-deact-reason">' +
                    '<input type="radio" name="sp_deact_reason" value="' + code + '" data-label="' + self.escapeHtml(englishLabel) + '">' +
                    '<span class="sp-deact-reason-text">' + self.escapeHtml(self.i18n['reason_' + code] || englishLabel) + '</span>' +
                    '</label>';
            });

            var starsHtml = '';
            for (var i = 1; i <= 5; i++) {
                starsHtml += '<span class="sp-deact-star" data-rating="' + i + '" role="button" tabindex="0" aria-label="' + self.escapeHtml(self.t('stars_' + i)) + '">&#9733;</span>';
            }

            var modalHtml =
                '<div id="sitepulse-deactivation-modal" class="sp-deact-overlay">' +
                '  <div class="sp-deact-modal" role="dialog" aria-modal="true" aria-labelledby="sp-deact-title">' +
                '    <div class="sp-deact-header">' +
                '      <h3 id="sp-deact-title">' + self.escapeHtml(self.t('title')) + '</h3>' +
                '      <p>' + self.escapeHtml(self.t('intro')) + '</p>' +
                '    </div>' +
                '    <div class="sp-deact-body">' +
                '      <div class="sp-deact-reasons" role="radiogroup">' + reasonsHtml + '</div>' +
                '      <div class="sp-deact-help" hidden></div>' +
                '      <div class="sp-deact-other-wrap" style="display:none;">' +
                '        <label class="screen-reader-text" for="sp-deact-other-message">' + self.escapeHtml(self.t('message_label')) + '</label>' +
                '        <textarea id="sp-deact-other-message" maxlength="1000" rows="3"></textarea>' +
                '        <div class="sp-deact-char-count"><span id="sp-deact-char-current">0</span>/1000</div>' +
                '      </div>' +
                '      <div class="sp-deact-rating-section">' +
                '        <label class="sp-deact-rating-label">' + self.escapeHtml(self.t('rating')) + '</label>' +
                '        <div class="sp-deact-stars">' + starsHtml + '</div>' +
                '        <input type="hidden" id="sp-deact-rating" value="0">' +
                '      </div>' +
                '    </div>' +
                '    <div class="sp-deact-footer">' +
                '      <button id="sp-deact-submit" class="button button-primary" disabled>' + self.escapeHtml(self.t('submit')) + '</button>' +
                '      <button id="sp-deact-skip" class="button button-link">' + self.escapeHtml(self.t('skip')) + '</button>' +
                '    </div>' +
                '  </div>' +
                '</div>';

            $('body').append(modalHtml);

            // Bind events
            this.bindModalEvents();

            // Show with animation
            setTimeout(function () {
                $('#sitepulse-deactivation-modal').addClass('sp-deact-active');
                $('input[name="sp_deact_reason"]').first().trigger('focus');
            }, 10);
        },

        /**
         * Bind all modal event handlers to the modal itself, so reopening never duplicates them.
         */
        bindModalEvents: function () {
            var self = this;
            var $modal = $('#sitepulse-deactivation-modal');

            // Reason selection
            $modal.on('change', 'input[name="sp_deact_reason"]', function () {
                var selectedCode = $(this).val();

                $('#sp-deact-other-message').attr('placeholder', self.t('prompt_' + selectedCode));
                $('.sp-deact-other-wrap').slideDown(200);
                self.showHelp(selectedCode);
                self.validateForm();
            });

            // Character count for the message
            $modal.on('input', '#sp-deact-other-message', function () {
                $('#sp-deact-char-current').text($(this).val().length);
            });

            // Star rating
            $modal.on('click keydown', '.sp-deact-star', function (e) {
                if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') {
                    return;
                }
                e.preventDefault();

                var rating = parseInt($(this).data('rating'), 10);
                $('#sp-deact-rating').val(rating);

                // Visual feedback
                $('.sp-deact-star').each(function () {
                    var starVal = parseInt($(this).data('rating'), 10);
                    $(this).toggleClass('sp-deact-star-active', starVal <= rating).attr('aria-pressed', starVal <= rating ? 'true' : 'false');
                });

                self.validateForm();
            });

            // Hover effect for stars
            $modal.on('mouseenter', '.sp-deact-star', function () {
                var hoverRating = parseInt($(this).data('rating'), 10);
                $('.sp-deact-star').each(function () {
                    $(this).toggleClass('sp-deact-star-hover', parseInt($(this).data('rating'), 10) <= hoverRating);
                });
            });

            $modal.on('mouseleave', '.sp-deact-stars', function () {
                $('.sp-deact-star').removeClass('sp-deact-star-hover');
            });

            // Pause monitoring instead of deactivating
            $modal.on('click', '.sp-deact-pause', function () {
                self.pauseInstead($(this));
            });

            // Close after pausing
            $modal.on('click', '.sp-deact-close', function () {
                self.closeModal();
            });

            // Submit button
            $modal.on('click', '#sp-deact-submit', function () {
                if ($(this).prop('disabled')) return;
                self.submitFeedback();
            });

            // Skip button
            $modal.on('click', '#sp-deact-skip', function () {
                self.proceedWithDeactivation();
            });

            // Close on backdrop click
            $modal.on('click', function (e) {
                if ($(e.target).hasClass('sp-deact-overlay')) {
                    self.closeModal();
                }
            });

            // Close on ESC key
            $(document).on('keydown.spDeact', function (e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    self.closeModal();
                }
            });
        },

        /**
         * Show the help panel that fits the chosen reason, if any.
         */
        showHelp: function (code) {
            var $help = $('.sp-deact-help').empty().attr('hidden', true);
            var html = '';

            if (code === 'temporary' || code === 'too_slow') {
                html = '<strong>' + this.escapeHtml(this.t(code === 'temporary' ? 'pause_title' : 'pause_title_slow')) + '</strong>' +
                    '<p>' + this.escapeHtml(this.t('pause_text')) + '</p>' +
                    '<button type="button" class="button button-primary sp-deact-pause">' + this.escapeHtml(this.t('pause_button')) + '</button>';
            } else if (code === 'confusing') {
                html = '<strong>' + this.escapeHtml(this.t('guide_title')) + '</strong>' +
                    '<ol>' +
                    '<li>' + this.escapeHtml(this.t('guide_step_1')) + '</li>' +
                    '<li>' + this.escapeHtml(this.t('guide_step_2')) + '</li>' +
                    '<li>' + this.escapeHtml(this.t('guide_step_3')) + '</li>' +
                    '</ol>' +
                    '<a class="button button-primary" href="' + this.escapeHtml(this.data.page_analysis_url) + '">' + this.escapeHtml(this.t('guide_button')) + '</a>';
            } else if (code === 'not_working') {
                html = '<strong>' + this.escapeHtml(this.t('support_title')) + '</strong>' +
                    '<p>' + this.escapeHtml(this.t('support_text')) + '</p>' +
                    '<a class="button" target="_blank" rel="noopener" href="' + this.escapeHtml(this.data.support_url) + '">' + this.escapeHtml(this.t('support_button')) + '</a>';
            }

            if (html) {
                $help.html(html).removeAttr('hidden');
            }
        },

        /**
         * Pause monitoring and keep the plugin active.
         */
        pauseInstead: function ($button) {
            var self = this;
            $button.prop('disabled', true).text(self.t('pausing'));

            $.ajax({
                url: self.data.rest_url + 'sitepulse/v1/monitoring/pause',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ _wpnonce: self.data.nonce }),
                headers: { 'X-WP-Nonce': self.data.nonce },
                timeout: 10000,
                success: function () {
                    $('.sp-deact-body').html(
                        '<div class="sp-deact-paused" role="status">' +
                        '<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>' +
                        '<strong>' + self.escapeHtml(self.t('paused_title')) + '</strong>' +
                        '<p>' + self.escapeHtml(self.t('paused_text')) + '</p>' +
                        '</div>'
                    );
                    $('.sp-deact-footer').html('<button type="button" class="button button-primary sp-deact-close">' + self.escapeHtml(self.t('close')) + '</button>');
                    $('.sp-deact-close').trigger('focus');
                },
                error: function () {
                    $button.prop('disabled', false).text(self.t('pause_button'));
                    $('.sp-deact-help').append('<p class="sp-deact-error" role="alert">' + self.escapeHtml(self.t('pause_error')) + '</p>');
                }
            });
        },

        /**
         * Validate form and enable/disable submit button
         */
        validateForm: function () {
            var reasonSelected = $('input[name="sp_deact_reason"]:checked').length > 0;
            var ratingGiven = parseInt($('#sp-deact-rating').val(), 10) > 0;
            $('#sp-deact-submit').prop('disabled', !(reasonSelected && ratingGiven));
        },

        /**
         * Submit the feedback to the API
         */
        submitFeedback: function () {
            var self = this;
            var $submitBtn = $('#sp-deact-submit');
            var $skipBtn = $('#sp-deact-skip');

            // Get form data
            var $selectedReason = $('input[name="sp_deact_reason"]:checked');
            var reasonCode = $selectedReason.val();
            var reasonLabel = $selectedReason.data('label');
            var message = $('#sp-deact-other-message').val() || '';
            var rating = parseInt($('#sp-deact-rating').val(), 10);

            // Disable buttons during submission
            $submitBtn.prop('disabled', true).text(self.t('submitting'));
            $skipBtn.prop('disabled', true);

            // Build payload
            var domain = self.data.domain || '';
            // Clean domain (strip protocol and trailing slash)
            domain = domain.replace(/^https?:\/\//i, '').replace(/\/$/, '');

            var payload = {
                domain: domain,
                reason_code: reasonCode,
                reason_label: reasonLabel,
                message: message.substring(0, 1000), // Enforce max length
                rating: rating,
                plugin_version: self.data.plugin_version || '',
                wp_version: self.data.wp_version || ''
            };

            // Send to API
            $.ajax({
                url: self.data.api_endpoint,
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
            $(document).off('keydown.spDeact');

            if (this.deactivateUrl) {
                window.location.href = this.deactivateUrl;
            }
        },

        /**
         * Remove the modal and its handlers right away.
         */
        removeModal: function () {
            $('#sitepulse-deactivation-modal').off().remove();
            $(document).off('keydown.spDeact');
        },

        /**
         * Close the modal without deactivating
         */
        closeModal: function () {
            var self = this;
            var $modal = $('#sitepulse-deactivation-modal');
            $modal.removeClass('sp-deact-active');
            $(document).off('keydown.spDeact');

            setTimeout(function () {
                $modal.off().remove();
                if (self.$trigger) {
                    self.$trigger.trigger('focus');
                }
            }, 300);
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function (text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML.replace(/"/g, '&quot;');
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

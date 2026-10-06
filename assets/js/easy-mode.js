/**
 * SitePulse Easy Mode — UI interactions + AJAX handlers.
 *
 * All existing SitePulse REST endpoints are reused. This file only handles
 * layout-specific behavior for the new experience.
 *
 * @package SitePulse
 */
(function ($) {
  'use strict';

  // Robust initialization prioritizing Pro localization
  var SP = window.SitePulseEasy || window.SitePulse || {};

  // Debug log for checking JS errors and initialization
  if (Object.keys(SP).length === 0) {
    console.error('SitePulse Easy Mode: No localization data found!');
  }

  /* ======================================================
   * Init
   * ====================================================== */
  $(document).ready(function () {
    initTheme();
    initScoreRings();
    initSidebar();
    initAccordions();
    initSettingsForms();
    initSQLDetailView();
    initWizardRestart();
    initClearEventData();
    initPetAssistant();
    initErrorLogActions();
    initSaveQueriesToggle();
    initGettingStarted();
  });

  /* ======================================================
   * Getting Started checklist
   * ====================================================== */
  function initGettingStarted() {
    $(document).on('click', '.sp-getting-started-dismiss', function () {
      var $card = $(this).closest('.sp-getting-started');
      $card.slideUp(200);

      $.ajax({
        url: SP.rest_url + 'sitepulse/v1/getting_started/dismiss',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ _wpnonce: SP.nonce }),
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', SP.nonce);
        }
      }).fail(function () {
        $card.slideDown(200);
      });
    });
  }

  /* ======================================================
   * Theme (dark / light)
   * ====================================================== */
  function initTheme() {
    var $root = $('.sp-easy');
    if (!$root.length) return;

    // Set initial theme from server
    $root.attr('data-theme', SP.theme || 'dark');

    $(document).on('click', '.sp-theme-btn', function () {
      var theme = $(this).data('theme');
      if (!theme) return;

      $('.sp-theme-btn').removeClass('active');
      $(this).addClass('active');
      $root.attr('data-theme', theme);
      SP.theme = theme;

      // Persist via AJAX
      $.ajax({
        url: SP.ajax_url,
        method: 'POST',
        data: {
          action: 'sitepulse_toggle_theme',
          theme: theme,
          nonce: SP.nonce
        }
      });
    });
  }

  /* ======================================================
   * Score Rings — animate on load
   * ====================================================== */
  function initScoreRings() {
    $('.sp-score-ring').each(function () {
      var $ring = $(this);
      var score = parseInt($ring.data('score'), 10) || 0;
      var $fill = $ring.find('.sp-score-ring-fill');
      var circumference = 2 * Math.PI * 42; // radius = 42

      $fill.css({
        'stroke-dasharray': circumference,
        'stroke-dashoffset': circumference
      });

      // Choose color based on score
      var color = getScoreColor(score);
      $fill.css('stroke', color);

      // Also set the value color
      $ring.find('.sp-score-ring-value').css('color', color);

      // Animate after short delay
      setTimeout(function () {
        var offset = circumference - (score / 100) * circumference;
        $fill.css('stroke-dashoffset', offset);
      }, 200);
    });
  }

  function getScoreColor(score) {
    var root = document.querySelector('.sp-easy');
    var style = getComputedStyle(root);
    if (score >= 80) return style.getPropertyValue('--sp-score-excellent').trim();
    if (score >= 60) return style.getPropertyValue('--sp-score-good').trim();
    if (score >= 40) return style.getPropertyValue('--sp-score-fair').trim();
    return style.getPropertyValue('--sp-score-poor').trim();
  }

  /* ======================================================
   * Sidebar — mobile toggle
   * ====================================================== */
  function initSidebar() {
    $(document).on('click', '.sp-topbar-burger', function () {
      if ($(window).width() > 768) {
        $('.sp-easy').toggleClass('sp-sidebar-collapsed');
      } else {
        $('.sp-easy-sidebar').toggleClass('sp-sidebar-open');
        $('.sp-sidebar-overlay').toggleClass('active');
      }
    });

    $(document).on('click', '.sp-sidebar-overlay', function () {
      $('.sp-easy-sidebar').removeClass('sp-sidebar-open');
      $(this).removeClass('active');
    });

    // Switch to classic view
    $(document).on('click', '.sp-sidebar-toggle-classic', function () {
      var $btn = $(this);
      $btn.prop('disabled', true).text(SP.i18n.switching || 'Switching...');

      $.ajax({
        url: SP.ajax_url,
        method: 'POST',
        data: {
          action: 'sitepulse_toggle_easy_mode',
          nonce: SP.nonce
        }
      }).done(function () {
        window.location.reload();
      }).fail(function () {
        $btn.prop('disabled', false).text('Switch to Classic');
      });
    });
  }

  /* ======================================================
   * Accordions
   * ====================================================== */
  function initAccordions() {
    $(document).on('click', '.sp-accordion-header', function () {
      var $header = $(this);
      var $body = $header.next('.sp-accordion-body');

      $header.toggleClass('active');
      $body.toggleClass('open');
    });
  }

  /* ======================================================
   * Quick Actions (Remaining uniqueness)
   * ====================================================== */
  /* ======================================================
   * Settings Forms — Live Toggles
   * ====================================================== */
  function initSettingsForms() {
    $(document).on('change', '#sp-easy-cron-toggle', function () {
      var $toggle = $(this);
      var enabled = $toggle.is(':checked');

      if (enabled && !confirm(SP.i18n.confirm_cron || 'Disabling WP-Cron can cause scheduled events to fail. Continue?')) {
        $toggle.prop('checked', false);
        return;
      }

      $.ajax({
        url: SP.rest_url + 'sitepulse/v1/settings/update',
        method: 'POST',
        data: JSON.stringify({
          cron_disabled: enabled,
          _wpnonce: SP.nonce
        }),
        contentType: 'application/json',
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', SP.nonce);
        }
      });
    });

    $(document).on('change', '#sp-easy-email-toggle', function () {
      var $toggle = $(this);
      var enabled = $toggle.is(':checked');

      if (enabled && !confirm(SP.i18n.confirm_email || 'This will block all outgoing emails from your site. Continue?')) {
        $toggle.prop('checked', false);
        return;
      }

      $.ajax({
        url: SP.rest_url + 'sitepulse/v1/settings/update',
        method: 'POST',
        data: JSON.stringify({
          email_blocking_enabled: enabled,
          _wpnonce: SP.nonce
        }),
        contentType: 'application/json',
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', SP.nonce);
        }
      });
    });
  }

  /* ======================================================
   * SQL Detail View (View query button in SQL monitor)
   * ====================================================== */
  function initSQLDetailView() {
    $(document).on('click', '.sp-sql-detail-btn', function (e) {
      e.preventDefault();
      var query = $(this).data('query');
      if (!query) return;

      // Check if modal already exists
      var $modal = $('#sp-sql-detail-modal');
      if (!$modal.length) {
        $('body').append(
          '<div id="sp-sql-detail-modal" class="sp-modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:100010;align-items:center;justify-content:center;">' +
          '<div class="sp-modal-content" style="background:var(--sp-bg-primary,#1e293b);border:1px solid var(--sp-border,rgba(255,255,255,0.08));border-radius:12px;max-width:700px;width:90%;max-height:80vh;overflow:auto;padding:24px;margin:auto;position:relative;top:50%;transform:translateY(-50%);">' +
          '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">' +
          '<h3 style="margin:0;font-size:16px;color:var(--sp-text-primary,#fff);">SQL Query Detail</h3>' +
          '<button type="button" class="sp-sql-detail-close" style="background:none;border:none;color:var(--sp-text-muted);font-size:20px;cursor:pointer;padding:4px 8px;">&times;</button>' +
          '</div>' +
          '<pre class="sp-sql-detail-code" style="background:var(--sp-bg-secondary,rgba(0,0,0,0.2));border:1px solid var(--sp-border);border-radius:8px;padding:16px;font-size:12px;white-space:pre-wrap;word-break:break-all;color:var(--sp-text-primary,#e2e8f0);margin:0;max-height:50vh;overflow:auto;"></pre>' +
          '<div class="sp-sql-detail-caller"></div>' +
          '</div>' +
          '</div>'
        );
        $modal = $('#sp-sql-detail-modal');

        // Close handlers
        $modal.on('click', '.sp-sql-detail-close', function () {
          $modal.hide();
        });
        $modal.on('click', function (e) {
          if ($(e.target).is('.sp-modal-overlay')) {
            $modal.hide();
          }
        });
      }

      var caller = $(this).data('caller');
      $modal.find('.sp-sql-detail-code').text(query);

      var $callerWrap = $modal.find('.sp-sql-detail-caller');
      if (caller) {
        $callerWrap.html('<strong>Caller:</strong> ' + $('<span>').text(caller).html()).show();
      } else {
        $callerWrap.hide();
      }

      $modal.css('display', 'block');
    });
  }

  /* ======================================================
   * Wizard Restart Button
   * ====================================================== */
  function initWizardRestart() {
    $(document).on('click', '.sp-wizard-restart-btn', function (e) {
      e.preventDefault();
      var $btn = $(this);
      var nonce = SP.nonce;

      if (!confirm('Are you sure you want to restart the setup wizard?')) return;

      $btn.prop('disabled', true).text('Restarting...');

      $.ajax({
        url: SP.rest_url + 'sitepulse/v1/onboarding/reset',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ _wpnonce: nonce }),
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', SP.nonce);
        }
      }).done(function () {
        window.location.href = SP.rest_url.replace('/wp-json/', '/wp-admin/') + 'admin.php?page=wpsp_sitepulse_onboarding';
      }).fail(function () {
        $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Restart Wizard');
        alert('Failed to reset wizard. Please try again.');
      });
    });
  }

  /* ======================================================
   * Clear Event Data (topbar trash button)
   * ====================================================== */
  function initClearEventData() {
    $(document).on('click', '.sp-easy-clear-data', function (e) {
      e.preventDefault();
      if (!confirm(SP.i18n.confirm_clear || 'Clear all event and load data?')) return;

      var $btn = $(this);
      $btn.prop('disabled', true);

      // Call both endpoints like the classic view does
      var baseUrl = SP.rest_url.replace(/\/$/, '');
      var loadReq = $.ajax({
        url: baseUrl + '/sitepulse/v1/clear_load_events/clear',
        method: 'POST',
        data: { _wpnonce: SP.nonce },
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', SP.nonce);
        }
      });

      var curlReq = $.ajax({
        url: baseUrl + '/sitepulse/v1/clear_curl_api_events/clear',
        method: 'POST',
        data: { _wpnonce: SP.nonce },
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', SP.nonce);
        }
      });

      $.when(loadReq, curlReq).done(function () {
        window.location.reload();
      }).fail(function () {
        alert('Failed to clear event data. Please try again.');
        $btn.prop('disabled', false);
      });
    });

    // Only clear the load events
    $(document).on('click', '.sp-clear-profiler-btn', function (e) {
      e.preventDefault();

      if (!confirm('Clear load data?')) return;

      var $btn = $(this);
      $btn.prop('disabled', true);

      // Call both endpoints like the classic view does
      var baseUrl = SP.rest_url.replace(/\/$/, '');
      var loadReq = $.ajax({
        url: baseUrl + '/sitepulse/v1/clear_load_events/clear',
        method: 'POST',
        data: { _wpnonce: SP.nonce },
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', SP.nonce);
        }
      });

      $.when(loadReq).done(function () {
        window.location.reload();
      }).fail(function () {
        alert('Failed to clear load data. Please try again.');
        $btn.prop('disabled', false);
      });

    });

    $(document).on('click', '.sp-clear-curl-btn', function (e) {
      e.preventDefault();

      if (!confirm('Clear curl data?')) return;

      var $btn = $(this);
      $btn.prop('disabled', true);

      // Call both endpoints like the classic view does
      var baseUrl = SP.rest_url.replace(/\/$/, '');
      var curlReq = $.ajax({
        url: baseUrl + '/sitepulse/v1/clear_curl_api_events/clear',
        method: 'POST',
        data: { _wpnonce: SP.nonce },
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', SP.nonce);
        }
      });

      $.when(curlReq).done(function () {
        window.location.reload();
      }).fail(function () {
        alert('Failed to clear curl data. Please try again.');
        $btn.prop('disabled', false);
      });
    });
  }

  /* ======================================================
   * Pulse Pet Assistant
   * ====================================================== */
  function initPetAssistant() {
    var $pet = $('#sp-pet-assistant');
    if (!$pet.length) return;

    var $trigger = $('#sp-pet-trigger');
    var $panel = $('#sp-pet-panel');
    var $close = $('#sp-pet-close');
    var isOpen = false;

    function openPanel() {
      $panel.show();
      $trigger.css('animation', 'none');
      isOpen = true;
    }

    function closePanel() {
      $panel.hide();
      $trigger.css('animation', '');
      isOpen = false;
    }

    $trigger.on('click', function () {
      if (isOpen) {
        closePanel();
      } else {
        openPanel();
      }
    });

    $close.on('click', function () {
      closePanel();
    });

    // Close when clicking outside
    $(document).on('click', function (e) {
      if (isOpen && !$(e.target).closest('#sp-pet-assistant').length) {
        closePanel();
      }
    });

    // Auto-nudge if there are alerts — subtle open after 8 seconds
    if ($pet.hasClass('sp-pet--alert')) {
      setTimeout(function () {
        if (!isOpen) {
          // Just add a stronger nudge, don't auto-open (respect the user)
          $trigger.addClass('sp-pet-nudge-hard');
          setTimeout(function () {
            $trigger.removeClass('sp-pet-nudge-hard');
          }, 2000);
        }
      }, 8000);
    }
  }

  /* ======================================================
   * Error Log Actions
   * ====================================================== */
  function initErrorLogActions() {
    // Clear Error Log
    $(document).on('click', '.sp-clear-error-log-btn', function (e) {
      e.preventDefault();
      if (!confirm(SP.i18n.confirm_clear_errors || 'Clear all PHP error logs?')) return;

      var $btn = $(this);
      $btn.prop('disabled', true).text(SP.i18n.clearing || 'Clearing...');

      $.ajax({
        url: SP.ajax_url,
        method: 'POST',
        data: {
          action: 'sitepulse_clear_error_log',
          nonce: SP.nonce
        }
      }).done(function () {
        window.location.reload();
      }).fail(function () {
        alert('Failed to clear error log. Please try again.');
        $btn.prop('disabled', false).text('Clear Error Log');
      });
    });

    // Toggle Error Details (Stack Trace)
    $(document).on('click', '.sp-error-expand-btn', function (e) {
      e.preventDefault();
      var $btn = $(this);
      var $target = $btn.closest('.sp-list-item-body').find('.sp-error-callstack');

      $target.toggleClass('open');

      var isVisible = $target.hasClass('open');
      $btn.find('.dashicons').toggleClass('dashicons-arrow-down-alt2', !isVisible).toggleClass('dashicons-arrow-up-alt2', isVisible);
      $btn.find('.sp-btn-text').text(isVisible ? 'Hide Details' : 'View Details');
    });
  }

  /* ======================================================
   * SAVEQUERIES Toggle
   * ====================================================== */
  function initSaveQueriesToggle() {
    $(document).on('click', '.sp-enable-savequeries', function (e) {
      e.preventDefault();
      if (!confirm('Enable SAVEQUERIES in wp-config.php? This is required for SQL monitoring but can have a small performance impact on very high-traffic sites.')) return;

      var $btn = $(this);
      var originalHtml = $btn.html();
      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> ' + (SP.i18n.working || 'Enabling...'));

      $.ajax({
        url: SP.ajax_url,
        method: 'POST',
        data: {
          action: 'sitepulse_enable_savequeries',
          nonce: SP.nonce
        }
      }).done(function (res) {
        if (res.success) {
          window.location.reload();
        } else {
          alert('Failed to enable: ' + (res.data.message || 'Unknown error'));
          $btn.prop('disabled', false).html(originalHtml);
        }
      }).fail(function () {
        alert('AJAX request failed. Check server permissions.');
        $btn.prop('disabled', false).html(originalHtml);
      });
    });
  }

})(jQuery);

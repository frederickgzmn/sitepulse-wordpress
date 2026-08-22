/**
 * This file should contains frontend-related JavaScript codes.
 * ----------------------------------------------------------
 *
 * Plugin : SitePulse
 * Version: 1.1.0
 *
 */

jQuery(function($) {
    // Dismiss SitePulse trackers disabled notice
    $('#sitepulse-trackers-disabled-notice').on('click', async function() {
        try {
            const result = await setSPTrackersDisabledNotice();
        } catch (err) {
            // Optionally handle error, e.g. show error message
            console.error('Failed to update SitePulse trackers disabled notice:', err);
        }
    });

    // Dismiss SitePulse onboarding notice
    $('#sitepulse-onboarding-notice').on('click', '.notice-dismiss', async function() {
        try {
            await setSPOnboardingNoticeDismissed();
        } catch (err) {
            console.error('Failed to dismiss SitePulse onboarding notice:', err);
        }
    });

    // Dismiss SitePulse trackers disabled notice
    async function setSPTrackersDisabledNotice() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/trackers_disabled_notice/dismiss';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        if ( ! res.ok ) {
            const err = await res.json().catch(()=>null);
            throw new Error( 'Request failed: ' + (err?.message || res.status) );
        }

        return res.json();
    }

    // Dismiss onboarding notice
    async function setSPOnboardingNoticeDismissed() {
        const url = SitePulse.rest_url.replace(/\/$/, '') + '/sitepulse/v1/onboarding_notice/dismiss';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': SitePulse.nonce
            },
            body: JSON.stringify({ _wpnonce: SitePulse.nonce })
        });

        if ( ! res.ok ) {
            const err = await res.json().catch(()=>null);
            throw new Error( 'Request failed: ' + (err?.message || res.status) );
        }

        return res.json();
    }
});

// Create or return alerts container
function _getAlertsContainer() {
    var id = 'sitepulse-alerts';
    var $c = jQuery('#' + id);
    if ($c.length) return $c;
    $c = jQuery('<div/>', { id: id });
    $c.appendTo('body');
    return $c;
}

// type: 'info' | 'success' | 'warning' | 'danger'
function showAlert(message, type, timeout) {
    type = type || 'info';
    timeout = (typeof timeout === 'number') ? timeout : 5000;

    var $alert = jQuery('<div/>', { 'class': 'sitepulse-alert sitepulse-alert-' + type })
        .html(message);

    var $container = _getAlertsContainer();
    $container.prepend($alert);

    // force layout then show via CSS class
    requestAnimationFrame(function(){
        $alert.addClass('show');
    });

    // auto-dismiss
    if (timeout > 0) {
        setTimeout(function(){
            _hideAndRemove($alert);
        }, timeout);
    }

    // click to dismiss
    $alert.on('click', function(){ _hideAndRemove($alert); });

    // remove after transition (fallback)
    $alert.on('transitionend', function(e){
        if (!jQuery(this).hasClass('show')) {
            jQuery(this).remove();
        }
    });

    return $alert;
}

function showToast(message, type, timeout) {
    // alias for showAlert
    return showAlert(message, type || 'success', typeof timeout === 'number' ? timeout : 3000);
}

function _hideAndRemove($el) {
    $el.removeClass('show');
    // in case transitionend doesn't fire, force remove after 300ms
    setTimeout(function(){ if ($el && $el.remove) $el.remove(); }, 350);
}

// Simple confirm dialog with callback( boolean )
function showConfirm(message, callback, options) {
    options = options || {};
    var okText = options.okText || 'OK';
    var cancelText = options.cancelText || 'Cancel';

    var $overlay = jQuery('<div/>').addClass('sitepulse-confirm-overlay');
    var $box = jQuery('<div/>').addClass('sitepulse-confirm-box');
    var $msg = jQuery('<div/>').addClass('sitepulse-confirm-message').text(message);
    var $btnWrap = jQuery('<div/>').addClass('sitepulse-confirm-actions');

    var $btnCancel = jQuery('<button/>').addClass('sitepulse-confirm-btn sitepulse-confirm-cancel').text(cancelText);
    var $btnOk = jQuery('<button/>').addClass('sitepulse-confirm-btn sitepulse-confirm-ok').text(okText);

    $btnWrap.append($btnCancel, $btnOk);
    $box.append($msg, $btnWrap);
    $overlay.append($box);
    jQuery('body').append($overlay);

    function cleanup() { $overlay.remove(); }

    $btnCancel.on('click', function(){ cleanup(); if (typeof callback === 'function') callback(false); });
    $btnOk.on('click', function(){ cleanup(); if (typeof callback === 'function') callback(true); });

    // close on ESC
    jQuery(document).on('keyup.sitepulse_confirm', function(e){
        if (e.key === 'Escape') {
            jQuery(document).off('keyup.sitepulse_confirm');
            cleanup();
            if (typeof callback === 'function') callback(false);
        }
    });
}
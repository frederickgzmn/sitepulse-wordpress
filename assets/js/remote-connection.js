/**
 * SitePulse remote management: the connection key in the settings.
 *
 * Copies the key, and while the settings show one, asks WordPress every few seconds whether
 * the SitePulse dashboard used it. Once it was, the site has already checked in, so the page
 * reloads to show the connection.
 */
(function () {
    'use strict';

    var POLL_SECONDS = 5;
    var POLL_MINUTES = 15;

    var box = document.getElementById('sitepulse-remote-connection');
    if (!box) {
        return;
    }

    var copy = box.querySelector('[data-copy]');
    if (copy) {
        copy.addEventListener('click', function () {
            var field = document.getElementById(copy.getAttribute('data-copy'));
            var copied = function () { copy.textContent = copy.getAttribute('data-copied'); };
            // The selected key stays easy to copy by hand when the browser refuses both ways.
            var select = function () {
                field.select();
                if (document.execCommand('copy')) {
                    copied();
                }
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(field.value).then(copied, select);
            } else {
                select();
            }
        });
    }

    var url = box.getAttribute('data-poll-url');
    if (!url) {
        return;
    }
    var status = box.querySelector('[data-poll-status]');
    var stopAt = Date.now() + POLL_MINUTES * 60000;

    function next() {
        if (Date.now() < stopAt) {
            window.setTimeout(poll, POLL_SECONDS * 1000);
        }
    }

    function poll() {
        var body = new URLSearchParams();
        body.append('action', box.getAttribute('data-poll-action'));
        body.append('nonce', box.getAttribute('data-poll-nonce'));
        fetch(url, { method: 'POST', credentials: 'same-origin', body: body })
            .then(function (response) { return response.json(); })
            .then(function (answer) {
                // A refused request (the session or its nonce expired) is not asked again.
                if (!answer || !answer.success) {
                    return;
                }
                if (answer.data && answer.data.used) {
                    status.textContent = status.getAttribute('data-done');
                    window.location.reload();
                    return;
                }
                next();
            })
            .catch(next);
    }

    next();
})();

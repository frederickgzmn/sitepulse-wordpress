/**
 * SitePulse: whether the site's own server can reach its wp-admin.
 *
 * The Check now button asks WordPress to check again, from its own server, and shows the new
 * result in place, without reloading the page.
 */
(function () {
    'use strict';

    Array.prototype.forEach.call(document.querySelectorAll('[data-sitepulse-admin-access]'), function (box) {
        var button = box.querySelector('[data-admin-access-check]');
        var badge = box.querySelector('[data-admin-access-badge]');
        var label = button.textContent;

        // Show a line, or hide it while it has nothing to say.
        function show(name, text) {
            var line = box.querySelector('[data-admin-access-' + name + ']');
            line.textContent = text || '';
            line.hidden = !text;
        }

        function failed(message) {
            show('error', message || button.getAttribute('data-failed'));
        }

        button.addEventListener('click', function () {
            var body = new URLSearchParams();
            body.append('action', box.getAttribute('data-action'));
            body.append('nonce', box.getAttribute('data-nonce'));
            button.disabled = true;
            button.textContent = button.getAttribute('data-checking');
            show('error', '');

            fetch(box.getAttribute('data-url'), { method: 'POST', credentials: 'same-origin', body: body })
                .then(function (response) { return response.json(); })
                .then(function (answer) {
                    // A refused check (expired session or nonce, or one already running) says why when it can.
                    if (!answer || !answer.success) {
                        failed(answer && answer.data && answer.data.message);
                        return;
                    }
                    var view = answer.data;
                    badge.className = badge.getAttribute('data-' + view.tone);
                    badge.textContent = view.label;
                    show('detail', view.detail);
                    show('message', view.message);
                    show('note', view.note);
                })
                .catch(function () { failed(''); })
                .then(function () {
                    button.disabled = false;
                    button.textContent = label;
                });
        });
    });
})();

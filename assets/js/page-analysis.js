/**
 * SitePulse Page Analysis
 *
 * Runs an analysis of one page through the REST API, renders the report, and
 * drives the Page Analysis screen. Exposes window.SitePulsePageAnalysis so the
 * onboarding wizard can analyze the homepage with the same engine.
 *
 * @package SitePulse
 */
(function ($) {
    'use strict';

    var data = window.SitePulsePageAnalysisData || {};
    var i18n = data.i18n || {};
    var POLL_INTERVAL = 2000;
    var MAX_POLLS = 450;

    /**
     * Fill %s, %d and numbered (%1$s) placeholders.
     */
    function format(template) {
        var values = Array.prototype.slice.call(arguments, 1);
        var next = 0;
        return String(template || '').replace(/%(?:(\d+)\$)?[sd]/g, function (match, position) {
            var value = position ? values[position - 1] : values[next++];
            return value === undefined ? '' : String(value);
        });
    }

    /**
     * POST to a Page Analysis endpoint and return the decoded body.
     */
    function api(action, payload) {
        return fetch(data.rest_url + action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': data.nonce
            },
            body: JSON.stringify($.extend({ _wpnonce: data.nonce }, payload))
        }).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (body) {
                if (!response.ok) {
                    throw new Error(body.message || i18n.error);
                }
                return body;
            });
        });
    }

    function wait(ms) {
        return new Promise(function (resolve) {
            setTimeout(resolve, ms);
        });
    }

    /**
     * Poll for samples recorded by a browser visit to the analysis link.
     */
    function waitForVisit(id, poll, cancelled) {
        if (cancelled()) {
            var error = new Error('cancelled');
            error.cancelled = true;
            return Promise.reject(error);
        }
        if (poll >= MAX_POLLS) {
            return Promise.reject(new Error(i18n.error));
        }
        return wait(POLL_INTERVAL).then(function () {
            return api('collect', { id: id });
        }).then(function (result) {
            return result.samples > 0 ? result : waitForVisit(id, poll + 1, cancelled);
        });
    }

    /**
     * Analyze a page: start, load it several times, then build the report.
     *
     * hooks.progress(text, percent) reports progress; hooks.fallback(result) is
     * called when the server cannot load the page itself and a browser visit is
     * needed instead; hooks.cancelled() stops waiting for that visit.
     */
    function run(url, hooks) {
        hooks = $.extend({ progress: $.noop, fallback: $.noop, cancelled: function () { return false; } }, hooks);
        hooks.progress(i18n.starting, 4);

        return api('start', { url: url }).then(function (started) {
            var total = started.samples;

            function sample(index) {
                hooks.progress(format(i18n.loading, index, total), 8 + ((index - 1) / total) * 84);
                return api('sample', { id: started.id }).then(function (result) {
                    if (!result.ok) {
                        if (result.samples > 0) {
                            return null;
                        }
                        hooks.fallback(result);
                        return waitForVisit(started.id, 0, hooks.cancelled);
                    }
                    return index < total ? sample(index + 1) : null;
                });
            }

            return sample(1).then(function () {
                hooks.progress(i18n.building, 96);
                return api('report', { id: started.id });
            });
        });
    }

    /* ------------------------------------------------------------------ */
    /* Report rendering. Every value from the site goes through .text().   */
    /* ------------------------------------------------------------------ */

    var LEVEL_ICONS = {
        danger: 'dashicons-warning',
        warning: 'dashicons-flag',
        info: 'dashicons-info-outline',
        success: 'dashicons-yes-alt'
    };

    function el(tag, className, text) {
        var $node = $('<' + tag + '>');
        if (className) {
            $node.addClass(className);
        }
        if (text !== undefined && text !== null) {
            $node.text(text);
        }
        return $node;
    }

    function icon(name) {
        return el('span', 'dashicons ' + name).attr('aria-hidden', 'true');
    }

    function card(title, iconName, hint) {
        var $card = el('section', 'sp-pa-card');
        var $head = el('div', 'sp-pa-card-head');
        $head.append(el('h3', 'sp-pa-card-title').append(icon(iconName), document.createTextNode(title)));
        if (hint) {
            $head.append(el('p', 'sp-pa-card-hint', hint));
        }
        return $card.append($head);
    }

    function metric(label, value, hint, level) {
        return el('div', 'sp-pa-metric' + (level ? ' sp-pa-metric--' + level : ''))
            .append(el('span', 'sp-pa-metric-label', label))
            .append(el('strong', 'sp-pa-metric-value', value))
            .append(el('span', 'sp-pa-metric-hint', hint));
    }

    function renderSummary(report) {
        var $summary = el('section', 'sp-pa-card sp-pa-summary sp-pa-level--' + report.verdict.level);
        var $headline = el('div', 'sp-pa-summary-headline')
            .append(el('span', 'sp-pa-kind', report.kind))
            .append(el('h3', 'sp-pa-title', report.title))
            .append(el('a', 'sp-pa-url', report.url).attr({ href: report.url, target: '_blank', rel: 'noopener' }));

        var $verdict = el('div', 'sp-pa-verdict')
            .append(el('div', 'sp-pa-verdict-time', report.display.server))
            .append(el('div', 'sp-pa-verdict-text')
                .append(el('span', 'sp-pa-verdict-label', report.verdict.label))
                .append(el('p', 'sp-pa-verdict-summary', report.verdict.summary))
                .append(report.verdict.detail ? el('p', 'sp-pa-verdict-detail', report.verdict.detail) : null));

        var $actions = el('div', 'sp-pa-summary-actions')
            .append(el('button', 'sp-pa-btn sp-pa-btn--ghost sp-pa-rerun').attr({ type: 'button', 'data-url': report.url })
                .append(icon('dashicons-update'), document.createTextNode(i18n.run_again)))
            .append(el('a', 'sp-pa-btn sp-pa-btn--ghost').attr({ href: report.url, target: '_blank', rel: 'noopener' })
                .append(icon('dashicons-external'), document.createTextNode(i18n.open_page)));

        return $summary.append($headline, $verdict, $actions);
    }

    function renderMetrics(report) {
        var queriesLevel = report.queries >= 200 ? 'danger' : (report.queries >= 100 ? 'warning' : '');
        var requestCount = report.http.length;

        return el('div', 'sp-pa-metrics').append(
            metric(i18n.server_time, report.display.server, report.samples > 1 ? format(i18n.first_load, report.display.first) : i18n.server_time_hint, report.verdict.level),
            metric(i18n.queries, String(report.queries), i18n.queries_hint, queriesLevel),
            metric(i18n.memory, report.display.memory, i18n.memory_hint, ''),
            metric(i18n.requests, String(requestCount), requestCount ? format(i18n.requests_hint, report.display.http) : i18n.requests_none, '')
        );
    }

    function renderFindings(findings, limit) {
        var $card = card(i18n.findings, 'dashicons-lightbulb');
        var $list = el('ul', 'sp-pa-findings');
        $.each(findings.slice(0, limit || findings.length), function (index, finding) {
            $list.append(el('li', 'sp-pa-finding sp-pa-finding--' + finding.level)
                .append(el('span', 'sp-pa-finding-icon').append(icon(LEVEL_ICONS[finding.level])))
                .append(el('div', 'sp-pa-finding-body')
                    .append(el('strong', 'sp-pa-finding-title', finding.title))
                    .append(el('p', 'sp-pa-finding-text', finding.text))));
        });
        return $card.append($list);
    }

    function renderBreakdown(report, limit) {
        var $card = card(i18n.breakdown, 'dashicons-chart-bar', i18n.breakdown_hint);
        var sources = report.sources.slice(0, limit || report.sources.length);
        var $bar = el('div', 'sp-pa-stack').attr('aria-hidden', 'true');
        var $rows = el('ul', 'sp-pa-sources');

        function row(name, typeLabel, time, share, segment) {
            $bar.append(el('span', 'sp-pa-seg sp-pa-seg--' + segment).css('width', share + '%').attr('title', name));
            return el('li', 'sp-pa-source')
                .append(el('span', 'sp-pa-swatch sp-pa-seg--' + segment))
                .append(el('span', 'sp-pa-source-name', name).append(typeLabel ? el('span', 'sp-pa-source-type', typeLabel) : null))
                .append(el('span', 'sp-pa-source-time', time))
                .append(el('span', 'sp-pa-source-share').append(el('span', 'sp-pa-source-share-fill').css('width', share + '%')))
                .append(el('span', 'sp-pa-source-percent', Math.round(share) + '%'));
        }

        $.each(sources, function (index, source) {
            $rows.append(row(source.name, source.type === 'theme' ? i18n.theme : i18n.plugin, source.time, source.share, Math.min(index, 5)));
        });
        $rows.append(row(i18n.core, '', report.display.core, report.core_share, 'core'));

        if (!sources.length) {
            $card.append(el('p', 'sp-pa-empty', i18n.no_sources));
        }
        return $card.append($bar, $rows);
    }

    function table(columns, rows) {
        var $head = el('tr');
        $.each(columns, function (index, column) {
            $head.append(el('th', null, column));
        });
        var $body = el('tbody');
        $.each(rows, function (index, cells) {
            var $row = el('tr');
            $.each(cells, function (cellIndex, cell) {
                $row.append(el('td', null, cell));
            });
            $body.append($row);
        });
        return el('div', 'sp-pa-table-wrap').append(el('table', 'sp-pa-table').append(el('thead').append($head), $body));
    }

    function renderHttp(report) {
        return card(i18n.http_title, 'dashicons-rest-api').append(table(
            [i18n.col_address, i18n.col_from, i18n.col_status, i18n.col_time],
            $.map(report.http, function (request) {
                return [[request.url, request.origin, String(request.code || '—'), request.time + ' · ' + format(i18n.per_load, request.per_load)]];
            })
        ));
    }

    function renderCallbacks(report) {
        var $details = el('details', 'sp-pa-card sp-pa-callbacks');
        $details.append(el('summary', 'sp-pa-card-title').append(icon('dashicons-editor-code'), document.createTextNode(i18n.callbacks_title)));
        return $details.append(table(
            [i18n.col_hook, i18n.col_callback, i18n.col_source, i18n.col_time],
            $.map(report.callbacks, function (callback) {
                return [[callback.hook, callback.callback, callback.source, callback.time]];
            })
        ));
    }

    /**
     * Render a report. options.compact shows only the essentials (onboarding).
     */
    function renderReport(report, options) {
        options = options || {};
        var $report = el('div', 'sp-pa-result' + (options.compact ? ' sp-pa-result--compact' : ''));

        if (options.compact) {
            return $report.append(renderSummary(report), renderMetrics(report), renderFindings(report.findings, 3), renderBreakdown(report, 3));
        }

        $report.append(renderSummary(report), renderMetrics(report), renderFindings(report.findings), renderBreakdown(report));
        if (report.http.length) {
            $report.append(renderHttp(report));
        }
        if (report.callbacks.length) {
            $report.append(renderCallbacks(report));
        }
        var allFromVisits = report.samples > 0 && !report.display.response;
        return $report.append(el('p', 'sp-pa-method', allFromVisits ? i18n.method_visit : format(i18n.method, report.samples)));
    }

    window.SitePulsePageAnalysis = {
        run: run,
        api: api,
        format: format,
        renderReport: renderReport
    };

    /* ------------------------------------------------------------------ */
    /* Page Analysis screen.                                                */
    /* ------------------------------------------------------------------ */

    function Screen($root) {
        this.$root = $root;
        this.$form = $root.find('.sp-pa-form');
        this.$input = $root.find('#sp-pa-url');
        this.$submit = $root.find('.sp-pa-submit');
        this.$error = $root.find('.sp-pa-error');
        this.$progress = $root.find('.sp-pa-progress');
        this.$report = $root.find('.sp-pa-report');
        this.$intro = $root.find('.sp-pa-intro');
        this.$history = $root.find('.sp-pa-history');
        this.busy = false;
        this.cancelled = false;
        this.bind();
    }

    Screen.prototype.bind = function () {
        var self = this;

        self.$form.on('submit', function (event) {
            event.preventDefault();
            self.analyze(self.$input.val());
        });
        self.$root.on('click', '.sp-pa-chip', function () {
            self.$input.val($(this).data('url'));
            self.analyze($(this).data('url'));
        });
        self.$root.on('click', '.sp-pa-rerun, .sp-pa-history-rerun', function () {
            self.$input.val($(this).data('url'));
            self.analyze($(this).data('url'));
        });
        self.$root.on('click', '.sp-pa-history-open', function () {
            self.open(String($(this).data('id')));
        });
        self.$root.on('click', '.sp-pa-history-delete', function () {
            self.remove(String($(this).data('id')));
        });
        self.$root.on('click', '.sp-pa-cancel', function () {
            self.cancelled = true;
        });
    };

    Screen.prototype.setBusy = function (busy) {
        this.busy = busy;
        this.$submit.prop('disabled', busy).find('.sp-pa-submit-label').text(busy ? i18n.analyzing : i18n.analyze);
        this.$root.toggleClass('is-busy', busy);
    };

    Screen.prototype.progress = function (text, percent) {
        this.$progress.prop('hidden', false);
        this.$progress.find('.sp-pa-progress-text').text(text);
        this.$progress.find('.sp-pa-bar').attr('aria-valuenow', Math.round(percent));
        this.$progress.find('.sp-pa-bar-fill').css('width', percent + '%');
    };

    Screen.prototype.fallback = function (result) {
        var $fallback = this.$progress.find('.sp-pa-fallback');
        $fallback.find('.sp-pa-fallback-title').text(i18n.fallback_title);
        $fallback.find('.sp-pa-fallback-message').text(result.message);
        $fallback.find('.sp-pa-visit').attr('href', result.visit_url).text(i18n.fallback_action);
        $fallback.find('.sp-pa-fallback-hint').text(i18n.fallback_hint);
        $fallback.prop('hidden', false);
        this.$progress.find('.sp-pa-progress-text').text(i18n.waiting_visit);
    };

    Screen.prototype.showError = function (message) {
        this.$error.text(message).prop('hidden', false);
    };

    Screen.prototype.analyze = function (url) {
        var self = this;
        if (self.busy) {
            return Promise.resolve();
        }

        self.setBusy(true);
        self.cancelled = false;
        self.$error.prop('hidden', true);
        self.$report.prop('hidden', true).empty();
        self.$intro.prop('hidden', true);
        self.$progress.find('.sp-pa-fallback').prop('hidden', true);

        return run($.trim(url), {
            progress: function (text, percent) {
                self.progress(text, percent);
            },
            fallback: function (result) {
                self.fallback(result);
            },
            cancelled: function () {
                return self.cancelled;
            }
        }).then(function (report) {
            self.show(report);
            self.remember(report);
        }).catch(function (error) {
            if (!error.cancelled) {
                self.showError(error.message);
            }
        }).then(function () {
            self.$progress.prop('hidden', true);
            self.setBusy(false);
        });
    };

    Screen.prototype.open = function (id) {
        var self = this;
        self.$error.prop('hidden', true);
        return api('report', { id: id }).then(function (report) {
            if (!report.complete) {
                return self.analyze(report.url);
            }
            self.$input.val(report.url);
            self.show(report);
        }).catch(function (error) {
            self.showError(error.message);
        });
    };

    Screen.prototype.show = function (report) {
        this.$intro.prop('hidden', true);
        this.$report.empty().append(renderReport(report)).prop('hidden', false);
        this.$history.find('.sp-pa-history-item').removeClass('is-current').filter('[data-id="' + report.id + '"]').addClass('is-current');

        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', data.admin_link + '&analysis=' + encodeURIComponent(report.id));
        }
    };

    Screen.prototype.remember = function (report) {
        var $list = this.$history.find('.sp-pa-history-list');
        $list.find('.sp-pa-history-item').filter(function () {
            return $(this).find('.sp-pa-history-rerun').data('url') === report.url;
        }).remove();

        var $item = el('li', 'sp-pa-history-item is-current').attr('data-id', report.id)
            .append(el('span', 'sp-pa-dot sp-pa-dot--' + report.verdict.level).attr('aria-hidden', 'true'))
            .append(el('button', 'sp-pa-history-open').attr({ type: 'button', 'data-id': report.id })
                .append(el('span', 'sp-pa-history-title', report.title), el('span', 'sp-pa-history-url', report.url)))
            .append(el('span', 'sp-pa-history-meta').append(el('strong', null, report.display.server), document.createTextNode(' ' + i18n.just_now)))
            .append(el('button', 'sp-pa-icon-btn sp-pa-history-rerun').attr({ type: 'button', 'data-url': report.url, title: i18n.run_again, 'aria-label': i18n.run_again }).append(icon('dashicons-update')))
            .append(el('button', 'sp-pa-icon-btn sp-pa-history-delete').attr({ type: 'button', 'data-id': report.id, title: i18n.remove, 'aria-label': i18n.remove }).append(icon('dashicons-trash')));

        $list.find('.sp-pa-history-item').removeClass('is-current');
        $list.prepend($item);
        this.$history.prop('hidden', false);
    };

    Screen.prototype.remove = function (id) {
        var self = this;
        return api('delete', { id: id }).then(function () {
            self.$history.find('.sp-pa-history-item[data-id="' + id + '"]').remove();
            if (!self.$history.find('.sp-pa-history-item').length) {
                self.$history.prop('hidden', true);
            }
        }).catch(function (error) {
            self.showError(error.message);
        });
    };

    $(function () {
        var $root = $('.sp-pa');
        if (!$root.length) {
            return;
        }

        var screen = new Screen($root);
        window.SitePulsePageAnalysis.screen = screen;

        if ($root.data('analysis')) {
            screen.open(String($root.data('analysis')));
        } else if (String($root.data('autorun')) === '1') {
            screen.analyze(screen.$input.val());
        }
    });
})(jQuery);

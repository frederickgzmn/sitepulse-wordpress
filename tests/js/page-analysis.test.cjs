const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse } = require('./helpers/browser.cjs');

const i18n = {
  loading: 'Loading the page as a visitor (%1$d of %2$d)…', starting: 'Preparing…', building: 'Building your report…',
  waiting_visit: 'Waiting for your visit…', error: 'Something went wrong.', analyze: 'Analyze page', analyzing: 'Analyzing…',
  run_again: 'Run again', open_page: 'Open page', server_time: 'Server time', server_time_hint: 'Typical build time', first_load: 'First load: %s',
  queries: 'Database queries', queries_hint: 'Per page load', memory: 'Peak memory', memory_hint: 'Highest use', requests: 'External requests',
  requests_none: 'None', requests_hint: '%s of waiting', findings: 'What we found', breakdown: 'Where the time goes', breakdown_hint: 'Own time',
  core: 'WordPress core', no_sources: 'Nothing measurable', http_title: 'External requests on this page', callbacks_title: 'Slowest callbacks',
  col_source: 'Plugin or theme', col_time: 'Time', col_share: 'Share', col_address: 'Address', col_from: 'Called by', col_status: 'Status',
  col_hook: 'Hook', col_callback: 'Callback', per_load: '%s× per load', plugin: 'Plugin', theme: 'Theme',
  method: 'Median of %d page loads.', method_visit: 'Measured from your visit.', fallback_title: 'We could not load this page',
  fallback_action: 'Open the page and measure my visit', fallback_hint: 'Come back here.', remove: 'Remove', just_now: 'just now'
};
const data = { SitePulsePageAnalysisData: { rest_url: 'https://sitepulse.test/wp-json/sitepulse/v1/page_analysis/', nonce: 'nonce-test', samples: 3, admin_link: 'https://sitepulse.test/wp-admin/admin.php?page=wpsp_sitepulse_page_analysis', i18n } };

const screenHtml = (attrs = 'data-autorun="0" data-analysis=""', history = '') => `
  <div class="sp-pa" ${attrs}>
    <form class="sp-pa-form"><input id="sp-pa-url" value="https://sitepulse.test/"><button type="submit" class="sp-pa-submit"><span class="sp-pa-submit-label">Analyze page</span></button>
      <button type="button" class="sp-pa-chip" data-url="https://sitepulse.test/shop/">Shop</button><p class="sp-pa-error" hidden></p></form>
    <div class="sp-pa-progress" hidden><strong class="sp-pa-progress-text"></strong><div class="sp-pa-bar"><div class="sp-pa-bar-fill"></div></div>
      <div class="sp-pa-fallback" hidden><h3 class="sp-pa-fallback-title"></h3><p class="sp-pa-fallback-message"></p><a class="sp-pa-visit" href="#"></a><button type="button" class="sp-pa-cancel">Cancel</button><p class="sp-pa-fallback-hint"></p></div></div>
    <div class="sp-pa-report" hidden></div>
    <div class="sp-pa-intro"></div>
    <div class="sp-pa-history" ${history ? '' : 'hidden'}><ul class="sp-pa-history-list">${history}</ul></div>
  </div>`;
const historyItem = (id, url) => `<li class="sp-pa-history-item" data-id="${id}"><button type="button" class="sp-pa-history-open" data-id="${id}">${url}</button><button type="button" class="sp-pa-history-rerun" data-url="${url}"></button><button type="button" class="sp-pa-history-delete" data-id="${id}"></button></li>`;

function report(overrides = {}) {
  return {
    id: 'abc123', url: 'https://sitepulse.test/product/blue/', title: 'Blue <img src=x onerror=alert(1)>', kind: 'Product', complete: true, samples: 3,
    status: 200, server_ms: 1800, queries: 230, http: [{ url: 'https://rates.test/v1', host: 'rates.test', origin: 'Plugin: WooCommerce', code: 503, time: '1.00 s', per_load: 0.7 }, { url: 'https://fonts.test/css', host: 'fonts.test', origin: 'Theme', code: 0, time: '20 ms', per_load: 1 }],
    callbacks: [{ hook: 'wp_loaded', callback: 'WC_Cart->calculate', source: 'woocommerce', time: '300 ms' }],
    sources: [
      { name: 'WooCommerce', type: 'plugin', time: '600 ms', share: 33.3 }, { name: 'Storefront (theme)', type: 'theme', time: '260 ms', share: 14.4 },
      { name: 'A', type: 'plugin', time: '50 ms', share: 2.8 }, { name: 'B', type: 'plugin', time: '40 ms', share: 2.2 }, { name: 'C', type: 'plugin', time: '30 ms', share: 1.7 },
      { name: 'D', type: 'plugin', time: '20 ms', share: 1.1 }, { name: 'E', type: 'plugin', time: '10 ms', share: 0.6 }
    ],
    core_share: 42,
    display: { server: '1.80 s', first: '3.00 s', response: '1.83 s', core: '760 ms', http: '673 ms', memory: '300 MB' },
    verdict: { level: 'slow', label: 'Slow', summary: 'WordPress needs 1.80 s.', detail: 'The biggest share goes to WooCommerce.' },
    findings: [
      { level: 'danger', title: '2 external requests add 673 ms', text: 'Waits.' }, { level: 'warning', title: '230 database queries', text: 'Many.' },
      { level: 'info', title: 'The first load was slower', text: 'Warm.' }, { level: 'success', title: 'Fine', text: 'Good.' }
    ],
    ...overrides
  };
}

async function setup(t, { html = screenHtml(), fetch, prepare } = {}) {
  return browser(t, { html, scripts: ['page-analysis.js'], globals: structuredClone(data), fetch, prepare });
}

// Answers like the REST API: start, then samples, then a report.
function server({ samples = () => ({ ok: true, samples: 1 }), collect = () => ({ ok: true, collected: 0, samples: 0 }), result = report(), start = () => ({ id: 'abc123', url: 'https://sitepulse.test/', samples: 3 }) } = {}) {
  let sample = 0;
  return call => {
    const action = call.url.split('/').pop();
    if (action === 'start') return jsonResponse(start(JSON.parse(call.body)));
    if (action === 'sample') return jsonResponse(samples(++sample));
    if (action === 'collect') return jsonResponse(collect());
    if (action === 'report') return jsonResponse(typeof result === 'function' ? result(JSON.parse(call.body)) : result);
    if (action === 'delete') return jsonResponse({ success: true });
    return jsonResponse({ message: 'unknown' }, 404);
  };
}

test('format fills plain and numbered placeholders and blanks missing values', async t => {
  const { window } = await setup(t, { html: '' });
  const { format } = window.SitePulsePageAnalysis;
  assert.equal(format('%1$d of %2$d', 2, 3), '2 of 3');
  assert.equal(format('%s and %d', 'a', 7), 'a and 7');
  assert.equal(format('%s and %s', 'only'), 'only and ');
  assert.equal(format(undefined), '');
});

test('analysis starts, loads the page three times with progress, and returns the report', async t => {
  const progress = [];
  const { window, network } = await setup(t, { html: '', fetch: server() });
  const result = await window.SitePulsePageAnalysis.run('https://sitepulse.test/', { progress: (text, percent) => progress.push([text, Math.round(percent)]) });
  assert.equal(result.id, 'abc123');
  assert.deepEqual(network.fetch.map(call => call.url.split('/').pop()), ['start', 'sample', 'sample', 'sample', 'report']);
  assert.equal(network.fetch[0].headers['X-WP-Nonce'], 'nonce-test');
  assert.deepEqual(JSON.parse(network.fetch[0].body), { _wpnonce: 'nonce-test', url: 'https://sitepulse.test/' });
  assert.deepEqual(JSON.parse(network.fetch[1].body), { _wpnonce: 'nonce-test', id: 'abc123' });
  assert.deepEqual(progress, [['Preparing…', 4], ['Loading the page as a visitor (1 of 3)…', 8], ['Loading the page as a visitor (2 of 3)…', 36], ['Loading the page as a visitor (3 of 3)…', 64], ['Building your report…', 96]]);
});

test('a later failed load still reports with the samples already measured', async t => {
  const fallbacks = [];
  const { window, network } = await setup(t, { html: '', fetch: server({ samples: n => n === 1 ? { ok: true, samples: 1 } : { ok: false, samples: 1, reason: 'cached', message: 'Cached', visit_url: 'x' } }) });
  await window.SitePulsePageAnalysis.run('/', { fallback: result => fallbacks.push(result) });
  assert.deepEqual(network.fetch.map(call => call.url.split('/').pop()), ['start', 'sample', 'sample', 'report']);
  assert.deepEqual(fallbacks, []);
});

test('when the server cannot load the page, a browser visit is awaited and then reported', async t => {
  let collected = 0;
  const fallbacks = [];
  const { window, network, clock } = await setup(t, { html: '', fetch: server({
    samples: () => ({ ok: false, samples: 0, reason: 'loopback', message: 'Blocked', visit_url: 'https://sitepulse.test/?sitepulse_analyze=tok' }),
    collect: () => ({ ok: true, collected: 0, samples: ++collected >= 3 ? 1 : 0 })
  }) });
  let done = false;
  const pending = window.SitePulsePageAnalysis.run('/', { fallback: result => fallbacks.push(result.visit_url) }).then(result => { done = result; });
  await clock.advance(4000);
  assert.equal(done, false);
  await clock.advance(2000);
  await pending;
  assert.deepEqual(fallbacks, ['https://sitepulse.test/?sitepulse_analyze=tok']);
  assert.equal(done.id, 'abc123');
  assert.deepEqual(network.fetch.map(call => call.url.split('/').pop()), ['start', 'sample', 'collect', 'collect', 'collect', 'report']);
});

test('waiting for a visit stops when cancelled or after fifteen minutes', async t => {
  const failure = { samples: () => ({ ok: false, samples: 0, message: 'Blocked', visit_url: 'v' }) };
  const { window, clock } = await setup(t, { html: '', fetch: server(failure) });
  let cancelled = false;
  const stop = window.SitePulsePageAnalysis.run('/', { cancelled: () => cancelled }).catch(error => error);
  await clock.advance(2000);
  cancelled = true;
  await clock.advance(2000);
  assert.equal((await stop).cancelled, true);

  const timeout = window.SitePulsePageAnalysis.run('/').catch(error => error);
  await clock.advance(2000 * 451);
  const error = await timeout;
  assert.equal(error.message, 'Something went wrong.');
  assert.equal(error.cancelled, undefined);
});

test('server errors surface their message, with a generic fallback for unreadable bodies', async t => {
  const { window, setFetch } = await setup(t, { html: '', fetch: () => jsonResponse({ message: 'Only pages on sitepulse.test can be analyzed.' }, 400) });
  await assert.rejects(window.SitePulsePageAnalysis.run('https://elsewhere.test/'), /Only pages on sitepulse.test/);
  setFetch(() => new Response('<html>Fatal error</html>', { status: 500 }));
  await assert.rejects(window.SitePulsePageAnalysis.api('report', { id: 'x' }), /Something went wrong/);
  setFetch(() => new Response('{}', { status: 500 }));
  await assert.rejects(window.SitePulsePageAnalysis.api('report', { id: 'x' }), /Something went wrong/);
});

test('full report renders every section and keeps page content as text', async t => {
  const { window, $ } = await setup(t, { html: '<div id="out"></div>' });
  $('#out').append(window.SitePulsePageAnalysis.renderReport(report()));
  assert.equal($('#out img').length, 0);
  assert.equal($('.sp-pa-title').text(), 'Blue <img src=x onerror=alert(1)>');
  assert.equal($('.sp-pa-summary').hasClass('sp-pa-level--slow'), true);
  assert.equal($('.sp-pa-url').attr('target'), '_blank');
  assert.equal($('.sp-pa-url').attr('rel'), 'noopener');
  assert.equal($('.sp-pa-verdict-time').text(), '1.80 s');
  assert.equal($('.sp-pa-verdict-detail').text(), 'The biggest share goes to WooCommerce.');
  assert.deepEqual($('.sp-pa-metric-value').map((i, el) => el.textContent).get(), ['1.80 s', '230', '300 MB', '2']);
  assert.deepEqual($('.sp-pa-metric-hint').map((i, el) => el.textContent).get(), ['First load: 3.00 s', 'Per page load', 'Highest use', '673 ms of waiting']);
  assert.equal($('.sp-pa-metric').eq(1).hasClass('sp-pa-metric--danger'), true);
  assert.deepEqual($('.sp-pa-finding').map((i, el) => el.className.split('--')[1]).get(), ['danger', 'warning', 'info', 'success']);
  assert.equal($('.sp-pa-finding--success .dashicons').hasClass('dashicons-yes-alt'), true);
  assert.equal($('.sp-pa-source').length, 8);
  assert.equal($('.sp-pa-source').last().find('.sp-pa-source-name').text(), 'WordPress core');
  assert.equal($('.sp-pa-source').eq(1).find('.sp-pa-source-type').text(), 'Theme');
  assert.equal($('.sp-pa-source').eq(6).find('.sp-pa-swatch').hasClass('sp-pa-seg--5'), true);
  assert.equal($('.sp-pa-seg--core').first().css('width'), '42%');
  assert.deepEqual($('.sp-pa-table').first().find('tbody tr').first().find('td').map((i, el) => el.textContent).get(), ['https://rates.test/v1', 'Plugin: WooCommerce', '503', '1.00 s · 0.7× per load']);
  assert.equal($('.sp-pa-table').first().find('tbody tr').eq(1).find('td').eq(2).text(), '—');
  assert.equal($('.sp-pa-callbacks summary').text(), 'Slowest callbacks');
  assert.equal($('.sp-pa-method').text(), 'Median of 3 page loads.');
});

test('compact report keeps only the essentials for onboarding', async t => {
  const { window, $ } = await setup(t, { html: '<div id="out"></div>' });
  $('#out').append(window.SitePulsePageAnalysis.renderReport(report(), { compact: true }));
  assert.equal($('.sp-pa-result--compact').length, 1);
  assert.equal($('.sp-pa-finding').length, 3);
  assert.equal($('.sp-pa-source').length, 4);
  assert.equal($('.sp-pa-table').length, 0);
  assert.equal($('.sp-pa-method').length, 0);
});

test('a fast single-visit report with nothing external explains how it was measured', async t => {
  const { window, $ } = await setup(t, { html: '<div id="out"></div>' });
  $('#out').append(window.SitePulsePageAnalysis.renderReport(report({
    samples: 1, queries: 120, http: [], callbacks: [], sources: [], verdict: { level: 'good', label: 'Fast', summary: 'Quick.', detail: '' },
    display: { server: '300 ms', first: '300 ms', response: '', core: '300 ms', http: '0 ms', memory: '20 MB' }
  })));
  assert.equal($('.sp-pa-verdict-detail').length, 0);
  assert.equal($('.sp-pa-metric-hint').first().text(), 'Typical build time');
  assert.equal($('.sp-pa-metric').eq(1).hasClass('sp-pa-metric--warning'), true);
  assert.equal($('.sp-pa-metric-hint').last().text(), 'None');
  assert.equal($('.sp-pa-empty').text(), 'Nothing measurable');
  assert.equal($('.sp-pa-table').length, 0);
  assert.equal($('.sp-pa-method').text(), 'Measured from your visit.');
  $('#out').empty().append(window.SitePulsePageAnalysis.renderReport(report({ queries: 12, display: { ...report().display, response: '' }, samples: 0 })));
  assert.equal($('.sp-pa-metric').eq(1).attr('class'), 'sp-pa-metric');
  assert.equal($('.sp-pa-method').text(), 'Median of 0 page loads.');
});

test('screen analyzes the entered page, shows progress, the report and remembers it', async t => {
  const { $, window, flush, clock } = await setup(t, { html: screenHtml('data-autorun="0" data-analysis=""', historyItem('old', 'https://sitepulse.test/product/blue/') + historyItem('keep', 'https://sitepulse.test/cart/')), fetch: server() });
  $('#sp-pa-url').val('  https://sitepulse.test/product/blue/ ');
  $('.sp-pa-form').trigger('submit');
  assert.equal($('.sp-pa-submit').prop('disabled'), true);
  assert.equal($('.sp-pa-submit-label').text(), 'Analyzing…');
  assert.equal($('.sp-pa').hasClass('is-busy'), true);
  assert.equal($('.sp-pa-progress').prop('hidden'), false);
  $('.sp-pa-form').trigger('submit');
  await clock.advance(0); await flush();
  assert.equal($('.sp-pa-report').prop('hidden'), false);
  assert.equal($('.sp-pa-intro').prop('hidden'), true);
  assert.equal($('.sp-pa-progress').prop('hidden'), true);
  assert.equal($('.sp-pa-submit').prop('disabled'), false);
  assert.equal($('.sp-pa-submit-label').text(), 'Analyze page');
  assert.deepEqual($('.sp-pa-history-item').map((i, el) => el.dataset.id).get(), ['abc123', 'keep']);
  assert.equal($('.sp-pa-history-item').first().hasClass('is-current'), true);
  assert.equal($('.sp-pa-history-item').first().find('.sp-pa-history-delete').attr('aria-label'), 'Remove');
  assert.equal(window.location.search, '?page=wpsp_sitepulse_page_analysis&analysis=abc123');
  assert.equal(window.SitePulsePageAnalysis.screen.busy, false);
});

test('chips and run-again buttons analyze their page', async t => {
  const starts = [];
  const { $, clock, flush } = await setup(t, { html: screenHtml('data-autorun="0" data-analysis=""', historyItem('h1', 'https://sitepulse.test/cart/')), fetch: server({ start: body => { starts.push(body.url); return { id: 'abc123', samples: 1 }; } }) });
  $('.sp-pa-chip').trigger('click');
  await clock.advance(0); await flush();
  assert.equal($('#sp-pa-url').val(), 'https://sitepulse.test/shop/');
  $('.sp-pa-report .sp-pa-rerun').trigger('click');
  await clock.advance(0); await flush();
  $('.sp-pa-history-rerun[data-url="https://sitepulse.test/cart/"]').trigger('click');
  await clock.advance(0); await flush();
  assert.deepEqual(starts, ['https://sitepulse.test/shop/', 'https://sitepulse.test/product/blue/', 'https://sitepulse.test/cart/']);
});

test('analysis errors are shown and the form becomes usable again', async t => {
  const { $, clock, flush } = await setup(t, { fetch: () => jsonResponse({ message: 'Admin, login and API addresses cannot be analyzed.' }, 400) });
  $('.sp-pa-form').trigger('submit');
  await clock.advance(0); await flush();
  assert.equal($('.sp-pa-error').prop('hidden'), false);
  assert.equal($('.sp-pa-error').text(), 'Admin, login and API addresses cannot be analyzed.');
  assert.equal($('.sp-pa-submit').prop('disabled'), false);
});

test('blocked loopback shows a measurable visit link and cancel stops waiting quietly', async t => {
  const { $, clock, flush } = await setup(t, { fetch: server({ samples: () => ({ ok: false, samples: 0, reason: 'loopback', message: 'Your server could not load the page.', visit_url: 'https://sitepulse.test/?sitepulse_analyze=tok' }) }) });
  $('.sp-pa-form').trigger('submit');
  await clock.advance(0); await flush();
  assert.equal($('.sp-pa-fallback').prop('hidden'), false);
  assert.equal($('.sp-pa-fallback-title').text(), 'We could not load this page');
  assert.equal($('.sp-pa-fallback-message').text(), 'Your server could not load the page.');
  assert.equal($('.sp-pa-visit').attr('href'), 'https://sitepulse.test/?sitepulse_analyze=tok');
  assert.equal($('.sp-pa-visit').text(), 'Open the page and measure my visit');
  assert.equal($('.sp-pa-progress-text').text(), 'Waiting for your visit…');
  $('.sp-pa-cancel').trigger('click');
  await clock.advance(2000); await flush();
  assert.equal($('.sp-pa-error').prop('hidden'), true);
  assert.equal($('.sp-pa-progress').prop('hidden'), true);
  assert.equal($('.sp-pa-submit').prop('disabled'), false);
});

test('opening a saved analysis shows its report or reruns an unfinished one', async t => {
  const reports = { done: report({ id: 'done', url: 'https://sitepulse.test/done/' }), unfinished: { id: 'unfinished', url: 'https://sitepulse.test/later/', complete: false, samples: 0 } };
  const { $, network, clock, flush } = await setup(t, {
    html: screenHtml('data-autorun="0" data-analysis=""', historyItem('done', 'https://sitepulse.test/done/') + historyItem('unfinished', 'https://sitepulse.test/later/')),
    fetch: server({ result: body => reports[body.id] || report() })
  });
  $('.sp-pa-history-open[data-id="done"]').trigger('click');
  await flush();
  assert.equal($('#sp-pa-url').val(), 'https://sitepulse.test/done/');
  assert.equal($('.sp-pa-history-item[data-id="done"]').hasClass('is-current'), true);
  $('.sp-pa-history-open[data-id="unfinished"]').trigger('click');
  await clock.advance(0); await flush();
  assert.equal(JSON.parse(network.fetch.find(call => call.url.endsWith('/start')).body).url, 'https://sitepulse.test/later/');
});

test('opening a missing analysis shows why', async t => {
  const { $, flush } = await setup(t, { html: screenHtml('data-autorun="0" data-analysis=""', historyItem('gone', 'https://sitepulse.test/')), fetch: () => jsonResponse({ message: 'This analysis no longer exists.' }, 404) });
  $('.sp-pa-history-open').trigger('click');
  await flush();
  assert.equal($('.sp-pa-error').text(), 'This analysis no longer exists.');
});

test('removing analyses updates the list and hides it when empty', async t => {
  const { $, network, flush, setFetch } = await setup(t, { html: screenHtml('data-autorun="0" data-analysis=""', historyItem('a', 'https://sitepulse.test/a/') + historyItem('123', 'https://sitepulse.test/b/')), fetch: server() });
  $('.sp-pa-history-delete[data-id="123"]').trigger('click');
  await flush();
  assert.deepEqual(JSON.parse(network.fetch[0].body), { _wpnonce: 'nonce-test', id: '123' });
  assert.equal($('.sp-pa-history-item').length, 1);
  assert.equal($('.sp-pa-history').prop('hidden'), false);
  setFetch(() => jsonResponse({ message: 'Forbidden' }, 403));
  $('.sp-pa-history-delete[data-id="a"]').trigger('click');
  await flush();
  assert.equal($('.sp-pa-error').text(), 'Forbidden');
  setFetch(server());
  $('.sp-pa-history-delete[data-id="a"]').trigger('click');
  await flush();
  assert.equal($('.sp-pa-history').prop('hidden'), true);
});

test('the screen can autorun a requested page or open a requested report on load', async t => {
  const autorun = await setup(t, { html: screenHtml('data-autorun="1" data-analysis=""'), fetch: server() });
  await autorun.clock.advance(0); await autorun.flush();
  assert.equal(autorun.network.fetch[0].url.split('/').pop(), 'start');
  assert.equal(autorun.$('.sp-pa-report').prop('hidden'), false);

  const open = await setup(t, { html: screenHtml('data-autorun="1" data-analysis="abc123"'), fetch: server() });
  await open.flush();
  assert.deepEqual(open.network.fetch.map(call => call.url.split('/').pop()), ['report']);
  assert.equal(open.$('.sp-pa-report').prop('hidden'), false);
});

test('pages without the analysis screen only get the shared engine', async t => {
  const { window, network } = await setup(t, { html: '<p>Other admin page</p>' });
  assert.equal(typeof window.SitePulsePageAnalysis.run, 'function');
  assert.equal(window.SitePulsePageAnalysis.screen, undefined);
  assert.equal(network.fetch.length, 0);
});

test('missing localization still rejects failed requests instead of crashing', async t => {
  const { window } = await browser(t, { html: '', scripts: ['page-analysis.js'], globals: { SitePulsePageAnalysisData: undefined }, fetch: () => jsonResponse({}, 500) });
  await assert.rejects(window.SitePulsePageAnalysis.api('report', {}), error => error.message === 'undefined' || error.message === '');
});

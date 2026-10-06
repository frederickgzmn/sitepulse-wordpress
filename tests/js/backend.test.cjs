const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse } = require('./helpers/browser.cjs');

function setup(t, html = '', options = {}) {
  return browser(t, { html, scripts: ['sitepulse_global.js', 'backend.js'], ...options });
}
function assertRest(call, endpoint, payload) {
  assert.equal(call.url, `https://sitepulse.test/wp-json/sitepulse/v1/${endpoint}`);
  assert.equal(call.method, 'POST');
  assert.equal(call.headers['X-WP-Nonce'], 'nonce-test');
  assert.equal(call.headers['Content-Type'], 'application/json');
  assert.deepEqual(JSON.parse(call.body), { ...payload, _wpnonce: 'nonce-test' });
}
const memoryHtml = '<span id="sp_js_memory">32 MB / 128 MB (25%)</span><div id="sp_js_memory_progress"></div><div class="status-progress-bar-compact"></div><div id="sp-memory-metric" class="sp-pagespeed-metric" data-score="75"><span id="sp-memory-score">75</span></div>';
const memoryResponse = { success: true, memory: { formatted: '96.5 MB / 128 MB (75.4%)', percent_class: 'danger', percent: '75.4%' } };

test('initial metrics derive a circular angle and memory usage bar from server markup', async t => {
  const { $ } = await setup(t, memoryHtml);
  assert.equal($('#sp-memory-metric')[0].style.getPropertyValue('--progress-angle'), '270deg');
  assert.equal($('#sp_js_memory_progress')[0].style.width, '25%');
  assert.equal($('.status-progress-bar-compact')[0].style.width, '25%');
});

test('memory polling updates usage, inverse score, circular progress, and heat intensity', async t => {
  const { $, clock, network, window, logs } = await setup(t, memoryHtml, { fetch: () => jsonResponse(memoryResponse) });
  await clock.advance(10000);
  assertRest(network.fetch[0], 'memory_checker/check', {});
  assert.equal($('#sp_js_memory').text(), '96.5 MB / 128 MB (75.4%)');
  assert.equal($('#sp_js_memory').hasClass('danger'), true);
  assert.equal($('#sp_js_memory_progress')[0].style.width, '75.4%');
  assert.equal($('#sp-memory-score').text(), '25');
  assert.equal($('#sp-memory-metric').attr('data-score'), '25');
  assert.equal($('#sp-memory-metric')[0].style.getPropertyValue('--progress-angle'), '90deg');
  assert.equal(window.__sitepulse_active_pulse, 'pulse-v5');
  assert.equal(logs.error.length, 0);
});

for (const [percent, score] of [['120%', '0'], ['-10%', '100'], ['100%', '0'], ['0%', '100']]) {
  test(`memory score remains within 0–100 for usage ${percent}`, async t => {
    const { $, clock } = await setup(t, memoryHtml, { fetch: () => jsonResponse({ success: true, memory: { formatted: percent, percent_class: 'normal', percent } }) });
    await clock.advance(10000);
    assert.equal($('#sp-memory-score').text(), score);
  });
}

test('non-JSON memory response preserves the display and the next poll can recover', async t => {
  const { $, clock, logs, network } = await setup(t, memoryHtml, {
    fetch: (_, index) => index === 0 ? new Response('<html>Unavailable</html>', { status: 503 }) : jsonResponse(memoryResponse)
  });
  await clock.advance(10000);
  assert.equal($('#sp-memory-score').text(), '75');
  assert.equal(logs.error.length, 1);
  await clock.advance(10000);
  assert.equal(network.fetch.length, 2);
  assert.equal($('#sp-memory-score').text(), '25');
});

test('memory API failure is logged and leaves the existing score in place', async t => {
  const { $, clock, logs } = await setup(t, memoryHtml, { fetch: () => jsonResponse({ success: false, message: 'Memory unavailable' }, 500) });
  await clock.advance(10000);
  assert.equal($('#sp-memory-score').text(), '75');
  assert.match(logs.error[0][1].message, /Memory unavailable/);
});

for (const [name, id, css, endpoint, field, on, off] of [
  ['profiler', 'sp-profiler', 'sp_profiler', 'sp_profiler/set_active', 'sitepulse_profiler_enabled', 'enabled', 'disabled'],
  ['HTTP tracking', 'sp-http-load', 'sp_http_load', 'wpslowhttp/set_active', 'wpslowhttp', 'enabled', 'disabled'],
  ['dark mode', 'dark-mode', 'sp_dark_mode', 'dark_mode/set_active', 'dark_mode', 'darkmode', 'lightmode']
]) {
  test(`${name} switch serializes both enabled and disabled states`, async t => {
    const { $, network, flush } = await setup(t, `<input type="checkbox" id="${id}" class="${css}">`);
    $(`#${id}`).trigger('click');
    await flush();
    assertRest(network.fetch[0], endpoint, { [field]: on });
    $(`#${id}`).trigger('click');
    await flush();
    assertRest(network.fetch[1], endpoint, { [field]: off });
  });
}

test('combined data clear requests both datasets and reports both successful actions', async t => {
  const { $, network, flush } = await setup(t, '<button class="sp_curl_and_profiler_clear_events">Clear</button>');
  $('.sp_curl_and_profiler_clear_events').trigger('click');
  await flush();
  assertRest(network.fetch[0], 'clear_load_events/clear', {});
  assertRest(network.fetch[1], 'clear_curl_api_events/clear', {});
  assert.equal($('.sitepulse-alert-success').length, 2);
});

test('failed data clear reports the failure without showing success', async t => {
  const { $, logs, flush } = await setup(t, '<button class="sp_profiler_clear_events">Clear</button>', { fetch: () => jsonResponse({ message: 'Not allowed' }, 403) });
  $('.sp_profiler_clear_events').trigger('click');
  await flush();
  assert.equal($('.sitepulse-alert-success').length, 0);
  assert.match(logs.error[0][1].message, /Not allowed/);
});

test('SAVEQUERIES requires confirmation and reports the successful server change', async t => {
  const { $, network, dialogs, flush } = await setup(t, '<button class="fix_enable_savequeries">Enable</button>');
  dialogs.answer = false;
  $('.fix_enable_savequeries').trigger('click');
  assert.equal(network.fetch.length, 0);
  dialogs.answer = true;
  $('.fix_enable_savequeries').trigger('click');
  await flush();
  assertRest(network.fetch[0], 'save_queries/enable', {});
  assert.equal($('.sitepulse-alert-success').length, 1);
});

test('new experience switch rejects missing AJAX configuration without a request', async t => {
  const { $, network } = await setup(t, '<button class="sp-new-experience-cta">Try</button>');
  $('.sp-new-experience-cta').trigger('click');
  assert.equal(network.fetch.length, 0);
  assert.equal($('.sitepulse-alert-danger').length, 1);
});

test('new experience switch prevents duplicate requests and sends the localized AJAX nonce', async t => {
  const page = await setup(t, '<button class="sp-new-experience-cta" data-ajax-url="https://sitepulse.test/wp-admin/admin-ajax.php" data-nonce="switch-nonce">Try</button>');
  const { $, network, flush } = page;
  let respond;
  page.setFetch(() => new Promise(resolve => { respond = resolve; }));
  $('.sp-new-experience-cta').trigger('click').trigger('click');
  assert.equal(network.fetch.length, 1);
  assert.equal($('.sp-new-experience-cta').prop('disabled'), true);
  assert.equal(network.fetch[0].url, 'https://sitepulse.test/wp-admin/admin-ajax.php');
  assert.deepEqual(Object.fromEntries(new URLSearchParams(network.fetch[0].body)), { action: 'sitepulse_toggle_easy_mode', nonce: 'switch-nonce' });
  respond(jsonResponse({ success: true, data: { easy_mode: true } }));
  await flush();
  assert.equal($('.sitepulse-alert-success').length, 1);
});

for (const status of [200, 500]) {
  test(`new experience failure with HTTP ${status} restores the switch`, async t => {
    const { $, flush } = await setup(t, '<button class="sp-new-experience-cta" data-ajax-url="https://sitepulse.test/wp-admin/admin-ajax.php">Try</button>', {
      fetch: () => jsonResponse({ success: false, data: { message: 'Cannot save preference' } }, status)
    });
    $('.sp-new-experience-cta').trigger('click');
    await flush();
    assert.equal($('.sp-new-experience-cta').prop('disabled'), false);
    assert.equal($('.sp-new-experience-cta').hasClass('is-loading'), false);
    assert.equal($('.sitepulse-alert-danger').length, 1);
  });
}

test('dangerous settings reject declined confirmation and hide the email mode options', async t => {
  const { $, dialogs } = await setup(t, '<input type="checkbox" id="sitepulse_cron_disabled"><input type="checkbox" id="sitepulse_email_blocking_enabled"><div id="email_blocking_mode_row"></div>');
  assert.equal($('#email_blocking_mode_row').css('display'), 'none');
  dialogs.answer = false;
  $('#sitepulse_cron_disabled, #sitepulse_email_blocking_enabled').prop('checked', true).trigger('change');
  assert.equal($('#sitepulse_cron_disabled').prop('checked'), false);
  assert.equal($('#sitepulse_email_blocking_enabled').prop('checked'), false);
  assert.equal($('#email_blocking_mode_row').css('display'), 'none');
  dialogs.answer = true;
  $('#sitepulse_email_blocking_enabled').prop('checked', true).trigger('change');
  assert.notEqual($('#email_blocking_mode_row').css('display'), 'none');
});

test('onboarding reset respects cancellation and restores its original markup after REST failure', async t => {
  const { $, dialogs, network, flush } = await setup(t, '<button id="sp_reset_onboarding"><strong>Reset</strong></button>', { fetch: () => jsonResponse({ message: 'Forbidden' }, 403) });
  dialogs.answer = false;
  $('#sp_reset_onboarding').trigger('click');
  assert.equal(network.fetch.length, 0);
  dialogs.answer = true;
  $('#sp_reset_onboarding').trigger('click');
  assert.equal($('#sp_reset_onboarding').prop('disabled'), true);
  await flush();
  assertRest(network.fetch[0], 'onboarding/reset', {});
  assert.equal($('#sp_reset_onboarding').prop('disabled'), false);
  assert.equal($('#sp_reset_onboarding strong').text(), 'Reset');
  assert.equal($('.sitepulse-alert-danger').length, 1);
});

for (const success of [true, false]) {
  test(`plugin data collection handles application success=${success}`, async t => {
    const { $, network, flush } = await setup(t, '<button class="sp-collect-plugin-data"><strong>Collect</strong></button>', {
      fetch: () => jsonResponse({ success, message: success ? 'Collected' : 'Collection unavailable' })
    });
    $('.sp-collect-plugin-data').trigger('click');
    assert.equal($('.sp-collect-plugin-data').prop('disabled'), true);
    await flush();
    assertRest(network.fetch[0], 'plugin_profiler/collect', {});
    assert.equal($(`.sitepulse-alert-${success ? 'success' : 'danger'}`).length, 1);
    if (!success) {
      assert.equal($('.sp-collect-plugin-data').prop('disabled'), false);
      assert.equal($('.sp-collect-plugin-data strong').text(), 'Collect');
    }
  });
}

const vulnerabilitiesHtml = '<button class="sp-check-vulnerabilities-btn">Check</button><button class="sp-check-vulnerabilities-btn">Check</button><div class="sp-vulnerabilities-results"></div><div class="sp-last-check-time"></div>';
test('vulnerability results escape plugin names and versions and restore every scan button', async t => {
  const { $, network, flush } = await setup(t, vulnerabilitiesHtml, {
    fetch: () => jsonResponse({ success: true, data: { vuln_wait: false, timestamp: '2026-10-04 12:00', message: 'Scan complete', vulnerabilities: [{ name: '<img src=x onerror=alert(1)>', version: '<b>1.0</b>' }] } })
  });
  $('.sp-check-vulnerabilities-btn').first().trigger('click');
  assert.equal($('.sp-check-vulnerabilities-btn:disabled').length, 2);
  await flush();
  assertRest(network.fetch[0], 'vulnerability/check', {});
  assert.equal($('.sp-vulnerabilities-results img, .sp-vulnerabilities-results b').length, 0);
  assert.match($('.sp-vulnerabilities-results').text(), /<img src=x onerror=alert\(1\)>/);
  assert.match($('.sp-vulnerabilities-results').text(), /<b>1\.0<\/b>/);
  assert.match($('.sp-last-check-time').text(), /2026-10-04 12:00/);
  assert.equal($('.sp-check-vulnerabilities-btn:disabled').length, 0);
});

for (const [label, response, css] of [
  ['clean scan', { success: true, data: { vuln_wait: false, timestamp: '2026-10-04', vulnerabilities: [], message: 'Complete' } }, 'alert-success'],
  ['rate limit', { success: true, data: { vuln_wait: true, message: 'Try later' } }, 'alert-warning'],
  ['application failure', { success: false, data: { message: 'Scan unavailable' } }, 'alert-warning']
]) {
  test(`vulnerability ${label} renders the appropriate result state`, async t => {
    const { $, flush } = await setup(t, vulnerabilitiesHtml, { fetch: () => jsonResponse(response) });
    $('.sp-check-vulnerabilities-btn').first().trigger('click');
    await flush();
    assert.equal($(`.sp-vulnerabilities-results .${css}`).length, 1);
    assert.equal($('.sp-check-vulnerabilities-btn:disabled').length, 0);
  });
}

test('network failure during a vulnerability scan renders an error and restores scan controls', async t => {
  const { $, flush } = await setup(t, vulnerabilitiesHtml, { fetch: () => { throw new Error('Offline'); } });
  $('.sp-check-vulnerabilities-btn').first().trigger('click');
  await flush();
  assert.match($('.sp-vulnerabilities-results .alert-danger').text(), /Offline/);
  assert.equal($('.sp-check-vulnerabilities-btn:disabled').length, 0);
});

for (const [status, css, polling] of [['completed', 'success', false], ['pending', 'info', true], ['processing', 'info', true], ['failed', 'danger', false]]) {
  test(`AI diagnostic ${status} state updates feedback and polling`, async t => {
    const { $, network, flush, clock } = await setup(t, '<button class="sp-request-ai-diagnostic-btn" data-force="true"><strong>Diagnose</strong></button>', {
      fetch: () => jsonResponse({ success: true, data: { status, message: 'Analysis result' } })
    });
    $('.sp-request-ai-diagnostic-btn').trigger('click');
    await flush();
    assertRest(network.fetch[0], 'ai_diagnostic/request', { force_new: true });
    assert.equal($(`.sitepulse-alert-${css}`).length, 1);
    assert.equal(clock.pendingIntervals().includes(60000), polling);
    if (status === 'failed') {
      assert.equal($('.sp-request-ai-diagnostic-btn').prop('disabled'), false);
      assert.equal($('.sp-request-ai-diagnostic-btn strong').text(), 'Diagnose');
    }
  });
}

test('AI diagnostic request failure restores the button and default force_new is false', async t => {
  const { $, network, flush } = await setup(t, '<button class="sp-request-ai-diagnostic-btn">Diagnose</button>', { fetch: () => jsonResponse({ message: 'Token expired' }, 403) });
  $('.sp-request-ai-diagnostic-btn').trigger('click');
  await flush();
  assertRest(network.fetch[0], 'ai_diagnostic/request', { force_new: false });
  assert.equal($('.sp-request-ai-diagnostic-btn').prop('disabled'), false);
  assert.match($('.sitepulse-alert-danger').text(), /Token expired/);
});

test('pending diagnostic retries a failed status check and stops polling when completed', async t => {
  let statusChecks = 0;
  const { $, network, clock } = await setup(t, '<div class="sp-ai-diagnostic-widget" data-status="pending"></div>', {
    fetch: call => {
      if (call.url.endsWith('/memory_checker/check')) return jsonResponse(memoryResponse);
      statusChecks++;
      if (statusChecks === 1) return jsonResponse({ message: 'Temporary outage' }, 503);
      return jsonResponse({ success: true, data: { status: 'completed' } });
    }
  });
  await clock.advance(60000);
  assert.equal(statusChecks, 1);
  assert.equal(clock.pendingIntervals().includes(60000), true);
  await clock.advance(60000);
  assert.equal(statusChecks, 2);
  assert.equal(clock.pendingIntervals().includes(60000), false);
  assert.equal($('.sitepulse-alert-success').length, 1);
  assertRest(network.fetch.find(call => call.url.endsWith('/ai_diagnostic/status')), 'ai_diagnostic/status', {});
});

const autoloadHtml = `
  <button class="sp-section-toggle" data-section="sp-autoload-section">Autoload</button>
  <button class="sp-section-toggle" data-section="sp-slow-queries-section">Queries</button>
  <section id="sp-slow-queries-section"></section><section id="sp-autoload-section" style="display:none">
  <button id="sp-autoload-refresh">Refresh</button><div id="sp-autoload-loading" class="d-none"></div>
  <div id="sp-autoload-error" class="d-none"><div class="alert"></div></div>
  <div id="sp-autoload-results" class="d-none"><div id="sp-autoload-summary"></div><div id="sp-autoload-list"></div></div></section>`;
const autoloadResponse = { success: true, count: 2, total_autoload_size_formatted: '122 KB', data: [
  { option_id: 7, option_name: 'cache_<img src=x>', autoload: 'on', data_size: '120000', data_size_formatted: '117 KB' },
  { option_id: 8, option_name: 'normal_option', autoload: 'on', data_size: '5120', data_size_formatted: '5 KB' }
] };
async function loadOptions(t, fetch) {
  const page = await setup(t, autoloadHtml, { fetch: fetch || (() => jsonResponse(autoloadResponse)) });
  page.$('.sp-section-toggle[data-section="sp-autoload-section"]').trigger('click');
  await page.flush();
  return page;
}

test('autoload section loads lazily, escapes option names, caches results, and supports refresh', async t => {
  const page = await setup(t, autoloadHtml, { fetch: () => jsonResponse(autoloadResponse) });
  const { $, network, flush } = page;
  assert.equal(network.fetch.length, 0);
  $('.sp-section-toggle[data-section="sp-autoload-section"]').trigger('click');
  assert.equal($('#sp-autoload-loading').hasClass('d-none'), false);
  await flush();
  assertRest(network.fetch[0], 'autoload_options', {});
  assert.equal($('#sp-autoload-list .sp-detail-item').length, 2);
  assert.equal($('#sp-autoload-list img').length, 0);
  assert.equal($('#autoload-toggle-7').data('option-name'), 'cache_<img src=x>');
  assert.equal($('#autoload-toggle-7').prop('checked'), true);
  assert.equal($('#sp-autoload-summary').text(), '2 options · Total autoload size: 122 KB');
  $('.sp-section-toggle[data-section="sp-slow-queries-section"]').trigger('click');
  $('.sp-section-toggle[data-section="sp-autoload-section"]').trigger('click');
  assert.equal(network.fetch.length, 1);
  $('#sp-autoload-refresh').trigger('click');
  await flush();
  assert.equal(network.fetch.length, 2);
});

test('autoload fetch failure displays a text-only error and permits another load', async t => {
  const page = await loadOptions(t, () => jsonResponse({ message: '<img src=x> Offline' }, 503));
  const { $, flush } = page;
  assert.equal($('#sp-autoload-error').hasClass('d-none'), false);
  assert.match($('#sp-autoload-error .alert').text(), /<img src=x> Offline/);
  assert.equal($('#sp-autoload-error img').length, 0);
  page.setFetch(() => jsonResponse(autoloadResponse));
  $('.sp-section-toggle[data-section="sp-autoload-section"]').trigger('click');
  await flush();
  assert.equal($('#sp-autoload-error').hasClass('d-none'), true);
  assert.equal($('#sp-autoload-list .sp-detail-item').length, 2);
});

test('empty autoload results replace loading state with an empty-state message', async t => {
  const { $ } = await loadOptions(t, () => jsonResponse({ success: true, count: 0, data: [], total_autoload_size_formatted: '0 B' }));
  assert.equal($('#sp-autoload-loading').hasClass('d-none'), true);
  assert.equal($('#sp-autoload-results').hasClass('d-none'), false);
  assert.equal($('.sp-autoload-toggle').length, 0);
  assert.match($('#sp-autoload-list').text(), /No autoloaded options/);
});

test('disabling autoload sends its option id then removes that option and reduces the count', async t => {
  const page = await loadOptions(t);
  const { $, network, flush, clock } = page;
  page.setFetch(() => jsonResponse({ success: true, message: 'Updated' }));
  $('#autoload-toggle-7').prop('checked', false).trigger('change');
  assert.equal($('#autoload-toggle-7').prop('disabled'), true);
  await flush();
  assertRest(network.fetch[1], 'autoload_options/update', { option_id: 7, autoload: 'off' });
  assert.equal($('#autoload-toggle-7').siblings('label').text(), 'off');
  await clock.advance(500);
  assert.equal($('#autoload-toggle-7').length, 0);
  assert.equal($('#autoload-toggle-8').length, 1);
  assert.match($('#sp-autoload-summary').text(), /^1 options/);
});

for (const status of [200, 403]) {
  test(`autoload update failure with HTTP ${status} restores checked state and interactivity`, async t => {
    const page = await loadOptions(t);
    const { $, flush } = page;
    page.setFetch(() => jsonResponse({ success: false, message: 'Cannot update' }, status));
    $('#autoload-toggle-7').prop('checked', false).trigger('change');
    await flush();
    assert.equal($('#autoload-toggle-7').prop('checked'), true);
    assert.equal($('#autoload-toggle-7').prop('disabled'), false);
    assert.equal($('#autoload-toggle-7').closest('.sp-detail-item').css('opacity'), '1');
    assert.match($('.sitepulse-alert-danger').text(), /Cannot update/);
  });
}

for (const [name, css, endpoint, field, onMessage, offMessage] of [
  ['Performance Monitor', 'sp_profiler', 'sp_profiler/set_active', 'sitepulse_profiler_enabled', 'Performance Monitor is enabled', 'Performance Monitor is disabled'],
  ['External Requests', 'sp_http_load', 'wpslowhttp/set_active', 'wpslowhttp', 'External Requests tracking is enabled', 'External Requests tracking is disabled']
]) {
  test(`${name} switch on the Simple dashboard reports the state of the switch that was clicked`, async t => {
    const { $, network, flush, navigation, clock } = await setup(t, `<input type="checkbox" id="sp-easy-switch" class="${css}">`);
    navigation.expected = css === 'sp_http_load' ? 2 : 0;
    $('#sp-easy-switch').trigger('click');
    await flush();
    assertRest(network.fetch[0], endpoint, { [field]: 'enabled' });
    assert.match($('.sitepulse-alert-success').text(), new RegExp(onMessage));
    $('#sp-easy-switch').trigger('click');
    await flush();
    assertRest(network.fetch[1], endpoint, { [field]: 'disabled' });
    assert.match($('.sitepulse-alert-warning').text(), new RegExp(offMessage));
    await clock.advance(2000);
  });

  test(`${name} switch flips back and explains when saving fails`, async t => {
    const { $, flush } = await setup(t, `<input type="checkbox" id="sp-easy-switch" class="${css}" checked>`, { fetch: () => jsonResponse({ message: 'Forbidden' }, 403) });
    $('#sp-easy-switch').trigger('click');
    assert.equal($('#sp-easy-switch').prop('checked'), false);
    await flush();
    assert.equal($('#sp-easy-switch').prop('checked'), true);
    assert.match($('.sitepulse-alert-danger').text(), /Could not change/);
  });
}

const viewToggle = view => `<button id="sp-toggle-view" data-view="${view === 'basic' ? 'developer' : 'basic'}" data-label-basic="Vista básica" data-label-developer="Vista de desarrollo"><span class="sp-view-label"></span><span class="sp-view-toggle-icon"><i class="dashicons"></i></span></button><div id="sp-basic-view"></div><div id="sp-developer-view"></div>`;

test('dashboard view switches instantly and saves the choice for this user on the server', async t => {
  const { $, network, flush, window } = await setup(t, viewToggle('basic'));
  $('#sp-toggle-view').trigger('click');
  assert.equal($('#sp-basic-view').css('display'), 'none');
  assert.notEqual($('#sp-developer-view').css('display'), 'none');
  assert.equal($('.sp-view-label').text(), 'Vista básica');
  assert.equal($('#sp-toggle-view').attr('data-view'), 'basic');
  assert.equal($('.sp-view-toggle-icon .dashicons').hasClass('dashicons-admin-users'), true);
  await flush();
  assertRest(network.fetch[0], 'dashboard_view', { view: 'developer' });
  $('#sp-toggle-view').trigger('click');
  assert.notEqual($('#sp-basic-view').css('display'), 'none');
  assert.equal($('.sp-view-label').text(), 'Vista de desarrollo');
  assert.equal($('.sp-view-toggle-icon .dashicons').hasClass('dashicons-editor-code'), true);
  await flush();
  assertRest(network.fetch[1], 'dashboard_view', { view: 'basic' });
  assert.equal(window.localStorage.getItem('sitepulse_dashboard_view'), null);
});

test('a failed view save is logged while the chosen view stays on screen', async t => {
  const { $, logs, flush } = await setup(t, viewToggle('developer'), { fetch: () => Promise.reject(new Error('offline')) });
  $('#sp-toggle-view').trigger('click');
  await flush();
  assert.notEqual($('#sp-basic-view').css('display'), 'none');
  assert.match(logs.error[0][0], /Failed to save dashboard view/);
});

test('view toggle is inert on screens without both dashboard views', async t => {
  const { network } = await setup(t, '<button id="sp-toggle-view" data-view="basic"></button>');
  assert.equal(network.fetch.length, 0);
});

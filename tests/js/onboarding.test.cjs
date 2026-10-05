const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse } = require('./helpers/browser.cjs');

const html = `
  <button id="prevStep">Previous</button><button id="nextStep">Next</button>
  <button id="finishOnboarding">Finish</button><button id="skipOnboarding">Skip</button>
  <input id="onboarding_view_mode" value="basic">
  <div class="view-mode-card selected" data-view="basic"><button class="select-view-btn" data-view="basic">Choose Basic View</button></div>
  <div class="view-mode-card" data-view="developer"><button class="select-view-btn" data-view="developer">Choose Developer View</button></div>
  <input type="checkbox" id="onboarding_profiler_enabled" checked>
  <input type="checkbox" id="onboarding_curl_enabled" checked>
  <input type="checkbox" id="onboarding_savequeries">
  <input type="checkbox" id="onboarding_email_blocking">
  <input type="checkbox" id="onboarding_external_api_enabled" checked>
  <button class="stepper-step active" data-step="1">One</button>
  <button class="stepper-step" data-step="2">Two</button>
  <button class="stepper-step" data-step="3">Three</button>
  <section class="onboarding-step active" data-step="1"></section>
  <section class="onboarding-step" data-step="2"></section>
  <section class="onboarding-step" data-step="3"></section>
  <div class="collection-status"><div class="collection-title"></div><div class="collection-message"></div></div>
  <div class="collection-progress"><div class="progress-bar"></div><div class="progress-text"></div></div>
  <div class="collection-details">
    <div class="detail-item" data-task="profiler"><span class="detail-status pending"></span></div>
    <div class="detail-item" data-task="hooks"><span class="detail-status pending"></span></div>
    <div class="detail-item" data-task="http"><span class="detail-status pending"></span></div>
    <div class="detail-item" data-task="memory"><span class="detail-status pending"></span></div>
    <div class="detail-item" data-task="plugins"><span class="detail-status pending"></span></div>
  </div>
  <div class="collection-complete" style="display:none"></div>
  <span id="stat-hooks"></span><span id="stat-http"></span><span id="stat-memory"></span><span id="stat-plugins"></span>`;

function setup(t, options = {}) {
  return browser(t, {
    html, scripts: ['onboarding.js'],
    globals: { SitePulseOnboarding: { rest_url: 'https://sitepulse.test/wp-json/', nonce: 'nonce-test', admin_url: '#dashboard' } },
    ...options
  });
}

async function finishCollection(page) {
  page.$('#nextStep').trigger('click');
  page.$('#nextStep').trigger('click');
  await page.clock.advance(5000);
}

test('onboarding restores a developer preference and begins on the first step', async t => {
  const { $ } = await setup(t, { storage: { sitepulse_dashboard_view: 'developer' } });
  assert.equal($('#onboarding_view_mode').val(), 'developer');
  assert.equal($('.view-mode-card.selected').data('view'), 'developer');
  assert.equal($('.stepper-step.active').data('step'), 1);
  assert.equal($('#prevStep').css('display'), 'none');
  assert.equal($('#finishOnboarding').css('display'), 'none');
});

test('view selection updates storage, selected card, and feedback; notification expires', async t => {
  const { $, window, clock } = await setup(t);
  $('.view-mode-card[data-view="developer"]').trigger('click');
  assert.equal(window.localStorage.getItem('sitepulse_dashboard_view'), 'developer');
  assert.equal($('.view-mode-card.selected').data('view'), 'developer');
  assert.equal($('.select-view-btn.btn-primary').data('view'), 'developer');
  assert.equal($('.notification-success').length, 1);
  await clock.advance(3000);
  assert.equal($('.onboarding-notification').length, 0);
});

test('stepper blocks future steps and keyboard navigation updates completed steps', async t => {
  const { $, window, network } = await setup(t);
  $('.stepper-step[data-step="3"]').trigger('click');
  assert.equal($('.stepper-step.active').data('step'), 1);
  $(window.document).trigger($.Event('keydown', { keyCode: 39 }));
  assert.equal($('.onboarding-step.active').data('step'), 2);
  assert.equal($('.stepper-step[data-step="1"]').hasClass('completed'), true);
  $(window.document).trigger($.Event('keydown', { keyCode: 37 }));
  assert.equal($('.onboarding-step.active').data('step'), 1);
  assert.equal(network.fetch.length, 0);
});

for (const [label, fixtures, expected] of [
  ['explicit counts', {
    profiler_stats: { success: true, count: 0, data: [] },
    curl_stats: { success: true, count: 12, events: [] },
    memory_info: { success: true, memory: '31.256' },
    plugin_profiler_stats: { success: true, count: 3, stats: {} }
  }, ['0', '12', '31.26 MB', '3']],
  ['collection fallbacks', {
    profiler_stats: { success: true, data: [{ hook: 'init' }, { hook: 'wp' }] },
    curl_stats: { success: true, events: [{ url: 'https://example.test' }] },
    memory_info: { success: true, memory: null },
    plugin_profiler_stats: { success: true, stats: { plugin_a: {}, plugin_b: {} } }
  }, ['2', '1', 'N/A', '2']]
]) {
  test(`collection requests all five datasets and renders ${label}`, async t => {
    const page = await setup(t, { fetch: call => jsonResponse(fixtures[call.url.split('/').pop()] || { success: true }) });
    const { $, network, clock, window } = page;
    $('#nextStep').trigger('click');
    $('#nextStep').trigger('click');
    assert.equal($('#prevStep').css('display'), 'none');
    $(window.document).trigger($.Event('keydown', { keyCode: 37 }));
    assert.equal($('.onboarding-step.active').data('step'), 3);
    await clock.advance(5000);
    assert.deepEqual(network.fetch.map(call => call.url.split('/sitepulse/v1/')[1]), [
      'sp_profiler/set_active', 'profiler_stats', 'profiler_stats', 'curl_stats', 'memory_info', 'plugin_profiler_stats'
    ]);
    assert.deepEqual(['hooks', 'http', 'memory', 'plugins'].map(stat => $(`#stat-${stat}`).text()), expected);
    assert.equal($('.detail-item.completed').length, 5);
    assert.equal($('.progress-bar').attr('aria-valuenow'), '100');
    assert.notEqual($('.collection-complete').css('display'), 'none');
    assert.notEqual($('#finishOnboarding').css('display'), 'none');
    assert.notEqual($('#prevStep').css('display'), 'none');
    $('#prevStep').trigger('click');
    $('#nextStep').trigger('click');
    await clock.advance(5000);
    assert.equal(network.fetch.length, 6, 'completed collection is not repeated');
  });
}

test('finishing persists changed settings before completing onboarding and redirecting', async t => {
  const page = await setup(t);
  const { $, network, window, clock, flush } = page;
  $('#onboarding_profiler_enabled, #onboarding_curl_enabled, #onboarding_external_api_enabled').prop('checked', false).trigger('change');
  $('#onboarding_savequeries, #onboarding_email_blocking').prop('checked', true).trigger('change');
  $('.select-view-btn[data-view="developer"]').trigger('click');
  await finishCollection(page);
  network.fetch.length = 0;
  $('#finishOnboarding').trigger('click');
  assert.equal($('#finishOnboarding').prop('disabled'), true);
  await flush();
  assert.deepEqual(network.fetch.map(call => [call.url.split('/sitepulse/v1/')[1], JSON.parse(call.body)]), [
    ['sp_profiler/set_active', { sitepulse_profiler_enabled: 'disabled', _wpnonce: 'nonce-test' }],
    ['wpslowhttp/set_active', { wpslowhttp: 'disabled', _wpnonce: 'nonce-test' }],
    ['save_queries/enable', { _wpnonce: 'nonce-test' }],
    ['settings/update', { email_blocking_enabled: true, email_blocking_mode: 'sendmail_block', external_api_enabled: false, _wpnonce: 'nonce-test' }],
    ['onboarding/complete', { _wpnonce: 'nonce-test' }]
  ]);
  for (const call of network.fetch) {
    assert.equal(call.method, 'POST');
    assert.equal(call.headers['X-WP-Nonce'], 'nonce-test');
  }
  assert.equal(window.localStorage.getItem('sitepulse_dashboard_view'), 'developer');
  await clock.advance(1500);
  assert.equal(window.location.hash, '#dashboard');
});

test('finishing unchanged defaults only marks onboarding complete', async t => {
  const page = await setup(t);
  await finishCollection(page);
  page.network.fetch.length = 0;
  page.$('#finishOnboarding').trigger('click');
  await page.flush();
  assert.deepEqual(page.network.fetch.map(call => call.url.split('/sitepulse/v1/')[1]), ['onboarding/complete']);
});

for (const failure of ['configuration', 'completion']) {
  test(`${failure} failure keeps the wizard open and restores Finish for retry`, async t => {
    const page = await setup(t);
    await finishCollection(page);
    const { $, network, window, flush, clock } = page;
    network.fetch.length = 0;
    if (failure === 'configuration') $('#onboarding_savequeries').prop('checked', true).trigger('change');
    page.setFetch(() => jsonResponse({ code: 'rest_forbidden', message: 'Access denied', data: { status: 403 } }, 403));
    $('#finishOnboarding').trigger('click');
    await flush();
    assert.equal($('#finishOnboarding').prop('disabled'), false);
    assert.equal($('#finishOnboarding').hasClass('loading'), false);
    assert.equal($('.notification-error').length, 1);
    assert.equal(network.fetch.length, 1);
    await clock.advance(2000);
    assert.equal(window.location.hash, '');
  });
}

test('declining skip leaves the wizard and server unchanged', async t => {
  const { $, network, dialogs, window } = await setup(t);
  dialogs.answer = false;
  $('#skipOnboarding').trigger('click');
  assert.equal(network.fetch.length, 0);
  assert.equal(window.location.hash, '');
});

for (const status of [200, 500]) {
  test(`confirmed skip redirects after HTTP ${status}`, async t => {
    const { $, network, window, flush } = await setup(t, { fetch: () => jsonResponse({ success: status === 200 }, status) });
    $('#skipOnboarding').trigger('click');
    await flush();
    assert.equal(network.fetch[0].url, 'https://sitepulse.test/wp-json/sitepulse/v1/onboarding/dismiss');
    assert.equal(window.location.hash, '#dashboard');
  });
}

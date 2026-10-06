const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse } = require('./helpers/browser.cjs');

const html = `
  <div class="switch-item"><input type="checkbox" id="curlSwitch" data-sitepulse-page-id="42"></div>
  <div class="switch-item"><input type="checkbox" id="loadSwitch"></div>
  <div class="sitepulse-ontracking" style="display:none"></div>
  <button id="realtime-tracking-btn">Open</button>
  <div id="sitepulse-modal" style="display:none"><h2 id="modal-title"></h2>
    <button id="sitepulse-close-modal">Close</button>
    <div id="inactive-content"></div><div id="active-content" style="display:none"></div>
    <div id="completed-content" style="display:none"><div id="sitepulse-completion-info"></div><button id="close-completed-modal">Done</button></div>
    <div class="sitepulse-tracking-controls"><button id="start-tracking-btn">Start</button><button id="stop-tracking-btn" style="display:none">Stop</button></div>
    <div id="tracking-info" style="display:none"><span id="tracking-duration"></span><span id="resources-count"></span><span id="tracking-state"></span></div>
    <span class="sitepulse-status-dot idle"></span><span class="status-text"></span>
    <p class="sitepulse-tracking-description current-page"></p>
  </div>`;
function setup(t, options = {}) { return browser(t, { html, scripts: ['frontend.js'], ...options }); }

test('tracking indicator stays visible while either per-page tracker is checked', async t => {
  const { $ } = await setup(t);
  $('#curlSwitch').prop('checked', true).trigger('change');
  assert.notEqual($('.sitepulse-ontracking').css('display'), 'none');
  $('#loadSwitch').prop('checked', true).trigger('change');
  $('#curlSwitch').prop('checked', false).trigger('change');
  assert.notEqual($('.sitepulse-ontracking').css('display'), 'none');
  $('#loadSwitch').prop('checked', false).trigger('change');
  assert.equal($('.sitepulse-ontracking').css('display'), 'none');
});

test('per-page curl switch sends the page identity, authenticated state, and updates its border', async t => {
  const { $, network, flush } = await setup(t);
  $('#curlSwitch').trigger('click');
  await flush();
  const call = network.fetch[0];
  assert.equal(call.url, 'https://sitepulse.test/wp-json/sitepulse/v1/wpspageloadhttp/set_active');
  assert.equal(call.method, 'POST');
  assert.equal(call.headers['X-WP-Nonce'], 'nonce-test');
  assert.deepEqual(JSON.parse(call.body), { page_id: 42, curlSwitch: 1, loadSwitch: 0, _wpnonce: 'nonce-test' });
  assert.equal($('#curlSwitch').closest('.switch-item').hasClass('rainbow-border'), true);
  $('#curlSwitch').trigger('click');
  await flush();
  assert.equal(JSON.parse(network.fetch[1].body).curlSwitch, 0);
  assert.equal($('#curlSwitch').closest('.switch-item').hasClass('rainbow-border'), false);
});

test('failed per-page update reports server errors without applying a success border', async t => {
  const { $, logs, flush } = await setup(t, { fetch: () => jsonResponse({ message: 'Invalid page' }, 400) });
  $('#curlSwitch').trigger('click');
  await flush();
  assert.equal($('#curlSwitch').closest('.switch-item').hasClass('rainbow-border'), false);
  assert.match(logs.error[0][1].message, /Invalid page/);
});

test('starting realtime tracking persists state, enables all server modes, and updates elapsed time', async t => {
  const { $, network, window, flush, clock } = await setup(t);
  window.SitePulse.post_load_events_count = 9;
  $('#start-tracking-btn').trigger('click');
  await flush();
  assert.deepEqual(network.fetch.map(call => [call.url.split('/sitepulse/v1/')[1], JSON.parse(call.body)]), [
    ['wpspageloadhttp/set_active', { page_id: 42, curlSwitch: 0, loadSwitch: 1, _wpnonce: 'nonce-test' }],
    ['wpsprealtimemode/set_active', { real_time_status: 1, _wpnonce: 'nonce-test' }],
    ['sp_report_mode/set_active', { report_mode: 1, _wpnonce: 'nonce-test' }]
  ]);
  assert.equal(window.localStorage.getItem('sitepulse_realtime_tracking'), 'true');
  assert.equal(window.localStorage.getItem('sitepulse_tracking_start_time'), '1700000000000');
  assert.equal($('.sitepulse-status-dot').hasClass('tracking'), true);
  assert.equal($('#start-tracking-btn').css('display'), 'none');
  assert.notEqual($('#stop-tracking-btn').css('display'), 'none');
  await clock.advance(2000);
  assert.equal($('#tracking-duration').text(), '00:02');
  assert.equal($('#resources-count').text(), '9');
  assert.equal(window.localStorage.getItem('sitepulse_tracking_elapsed'), '2000');
});

test('stopping realtime tracking clears storage and timers, restores controls, and disables server modes', async t => {
  const { $, network, window, flush, clock } = await setup(t);
  $('#start-tracking-btn').trigger('click');
  await flush();
  await clock.advance(1000);
  network.fetch.length = 0;
  $('#stop-tracking-btn').trigger('click');
  await flush();
  assert.deepEqual(network.fetch.map(call => JSON.parse(call.body)), [
    { page_id: 42, curlSwitch: 0, loadSwitch: 0, _wpnonce: 'nonce-test' },
    { real_time_status: 0, _wpnonce: 'nonce-test' }
  ]);
  for (const key of ['sitepulse_realtime_tracking', 'sitepulse_tracking_start_time', 'sitepulse_tracking_elapsed']) {
    assert.equal(window.localStorage.getItem(key), null);
  }
  assert.deepEqual(clock.pendingIntervals(), []);
  assert.equal($('.sitepulse-status-dot').hasClass('idle'), true);
  assert.equal($('#stop-tracking-btn').css('display'), 'none');
  assert.notEqual($('#start-tracking-btn').css('display'), 'none');
});

test('closing an active tracking modal honors cancellation and then stops after confirmation', async t => {
  const { $, dialogs, network, flush } = await setup(t);
  $('#realtime-tracking-btn').trigger('click');
  $('#start-tracking-btn').trigger('click');
  await flush();
  network.fetch.length = 0;
  dialogs.answer = false;
  $('#sitepulse-close-modal').trigger('click');
  assert.notEqual($('#sitepulse-modal').css('display'), 'none');
  assert.equal(network.fetch.length, 0);
  dialogs.answer = true;
  $('#sitepulse-close-modal').trigger('click');
  await flush();
  assert.equal($('#sitepulse-modal').css('display'), 'none');
  assert.equal(network.fetch.length, 2);
});

test('opening and closing an idle modal does not prompt or change server state', async t => {
  const { $, dialogs, network } = await setup(t);
  $('#realtime-tracking-btn').trigger('click');
  assert.notEqual($('#sitepulse-modal').css('display'), 'none');
  $('#sitepulse-close-modal').trigger('click');
  assert.equal($('#sitepulse-modal').css('display'), 'none');
  assert.equal(dialogs.confirmations.length, 0);
  assert.equal(network.fetch.length, 0);
});

test('restoring realtime tracking uses persisted start time and queries server status', async t => {
  const { $, network, clock } = await setup(t, {
    html: html + '<div data-sitepulse-realtime="tracking"></div>',
    storage: { sitepulse_tracking_start_time: '1699999935000', sitepulse_realtime_tracking: 'true' },
    fetch: () => jsonResponse({ success: true, real_time_status: true })
  });
  await clock.advance(1000);
  assert.equal(network.fetch[0].url, 'https://sitepulse.test/wp-json/sitepulse/v1/wpsprealtimemode/get_active');
  assert.equal($('#tracking-duration').text(), '01:06');
  assert.equal($('.sitepulse-status-dot').hasClass('tracking'), true);
});

test('completed tracking renders only valid events, enables reports, and resets its modal on close', async t => {
  const { $, network, window } = await setup(t, {
    html: html + '<div data-sitepulse-realtime="stopped"></div>',
    storage: { sitepulse_realtime_tracking: 'true' },
    globals: { SitePulse: {
      rest_url: 'https://sitepulse.test/wp-json/', nonce: 'nonce-test', post_load_events_count: 1,
      post_load_events: {
        event42: { hook: 'init', priority: 10, sig: 'Plugin::init', fileline: 'plugin.php:12', calls: 2, total_ms: 3, max_ms: 2, avg_ms: 1.5, source: 'plugin' },
        missing_hook: {}, empty: null
      }
    } }
  });
  assert.equal(window.localStorage.getItem('sitepulse_realtime_tracking'), null);
  assert.equal($('.sitepulse-event').length, 1);
  assert.match($('.sitepulse-event').text(), /event42[\s\S]*init[\s\S]*Plugin::init[\s\S]*plugin\.php:12/);
  assert.deepEqual(network.fetch.map(call => call.url.split('/sitepulse/v1/')[1]), ['sp_report_mode/set_active', 'wpspageloadhttp/set_active']);
  assert.notEqual($('#completed-content').css('display'), 'none');
  assert.equal($('.sitepulse-tracking-controls').css('display'), 'none');
  $('#close-completed-modal').trigger('click');
  assert.equal($('#sitepulse-modal').css('display'), 'none');
  assert.equal($('#completed-content').css('display'), 'none');
  assert.notEqual($('.sitepulse-tracking-controls').css('display'), 'none');
});

test('stopped marker without a stored active session does not create a duplicate report', async t => {
  const { $, network } = await setup(t, { html: html + '<div data-sitepulse-realtime="stopped"></div>' });
  assert.equal(network.fetch.length, 0);
  assert.equal($('#sitepulse-modal').css('display'), 'none');
});

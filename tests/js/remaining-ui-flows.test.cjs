const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse } = require('./helpers/browser.cjs');

test('trackers-disabled notice logs a rejected dismissal with a non-JSON server error', async t => {
  const { $, logs, flush } = await browser(t, {
    scripts: ['sitepulse_global.js'], html: '<div id="sitepulse-trackers-disabled-notice"></div>',
    fetch: () => new Response('Forbidden', { status: 403 })
  });
  $('#sitepulse-trackers-disabled-notice').trigger('click');
  await flush();
  assert.match(logs.error[0][1].message, /403/);
});

test('localized deactivation script leaves unrelated plugin pages alone', async t => {
  const { $, network } = await browser(t, { scripts: ['deactivation-modal.js'], globals: { SitePulseDeactivationData: {} } });
  assert.equal($('#sitepulse-deactivation-modal').length, 0);
  assert.equal(network.ajax.length, 0);
});

test('Easy Mode missing localization is reported without crashing an unrelated page', async t => {
  const { logs } = await browser(t, { scripts: ['easy-mode.js'], globals: { SitePulse: undefined } });
  assert.equal(logs.error.length, 1);
  assert.match(logs.error[0][0], /No localization data/);
});

const easyGlobals = { SitePulseEasy: {
  rest_url: 'https://sitepulse.test/wp-json/', ajax_url: 'https://sitepulse.test/wp-admin/admin-ajax.php',
  nonce: 'nonce-test', theme: 'dark', i18n: {}
} };
for (const [selector, expectedRequests] of [
  ['sp-sidebar-toggle-classic', 1], ['sp-wizard-restart-btn', 1], ['sp-easy-clear-data', 2],
  ['sp-clear-profiler-btn', 1], ['sp-clear-curl-btn', 1], ['sp-clear-error-log-btn', 1], ['sp-enable-savequeries', 1]
]) {
  test(`Easy Mode ${selector} navigates only after its successful server responses`, async t => {
    const { $, network, navigation, clock } = await browser(t, {
      scripts: ['easy-mode.js'], html: `<div class="sp-easy"><button class="${selector}">Continue</button></div>`, globals: structuredClone(easyGlobals)
    });
    navigation.expected = 1;
    $(`.${selector}`).trigger('click');
    assert.equal(network.ajax.length, expectedRequests);
    assert.equal(navigation.attempts, 0);
    for (const request of network.ajax) request.respond({ success: true, data: { message: 'Saved' } });
    await clock.advance(0);
    assert.equal(navigation.attempts, 1);
  });
}

test('clicking an open pet trigger closes its own panel', async t => {
  const { $ } = await browser(t, {
    scripts: ['easy-mode.js'], globals: structuredClone(easyGlobals),
    html: '<div id="sp-pet-assistant"><button id="sp-pet-trigger"></button><div id="sp-pet-panel" style="display:none"></div></div>'
  });
  $('#sp-pet-trigger').trigger('click');
  assert.notEqual($('#sp-pet-panel').css('display'), 'none');
  $('#sp-pet-trigger').trigger('click');
  assert.equal($('#sp-pet-panel').css('display'), 'none');
});

const frontHtml = '<input type="checkbox" id="curlSwitch" data-sitepulse-page-id="42"><div id="sitepulse-modal"><button id="start-tracking-btn">Start</button><button id="stop-tracking-btn">Stop</button><span id="tracking-state"></span><span class="sitepulse-status-dot"></span></div>';

test('failed realtime activation requests are handled and stopping still attempts both cleanup requests', async t => {
  const { $, logs, network, flush, window } = await browser(t, {
    scripts: ['frontend.js'], html: frontHtml,
    fetch: () => new Response('Unavailable', { status: 503 })
  });
  $('#start-tracking-btn').trigger('click');
  await flush();
  assert.equal(logs.error.length, 3);
  assert.deepEqual(network.fetch.map(request => request.url.split('/sitepulse/v1/')[1]), [
    'wpspageloadhttp/set_active', 'wpsprealtimemode/set_active', 'sp_report_mode/set_active'
  ]);
  $('#stop-tracking-btn').trigger('click');
  await flush();
  assert.equal(logs.error.length, 5);
  assert.equal(window.localStorage.getItem('sitepulse_realtime_tracking'), null);
  assert.equal($('.sitepulse-status-dot').hasClass('idle'), true);
});

for (const restore of [true, false]) {
  test(`${restore ? 'restored' : 'new'} realtime session refreshes after its tracking interval`, async t => {
    const { $, clock, navigation, window } = await browser(t, {
      scripts: ['frontend.js'], html: frontHtml + (restore ? '<div data-sitepulse-realtime="tracking"></div>' : ''),
      fetch: () => jsonResponse({ success: true, real_time_status: true })
    });
    navigation.expected = 1;
    if (!restore) $('#start-tracking-btn').trigger('click');
    await clock.advance(10000);
    assert.equal(navigation.attempts, 1);
    assert.equal(window.localStorage.getItem('sitepulse_tracking_elapsed'), '10000');
  });
}

test('restoration status failure is reported and the user can still stop tracking', async t => {
  const { $, logs, flush, network } = await browser(t, {
    scripts: ['frontend.js'], html: frontHtml + '<div data-sitepulse-realtime="tracking"></div>',
    fetch: call => call.url.endsWith('/get_active') ? new Response('Unavailable', { status: 503 }) : jsonResponse({ success: true })
  });
  assert.equal(logs.error.length, 1);
  $('#stop-tracking-btn').trigger('click');
  await flush();
  assert.equal(network.fetch.length, 3);
  assert.equal($('.sitepulse-status-dot').hasClass('idle'), true);
});

test('completion remains dismissible if saving report state fails', async t => {
  const { $, logs } = await browser(t, {
    scripts: ['frontend.js'], html: frontHtml + '<div data-sitepulse-realtime="stopped"></div><div id="completed-content"></div><button id="close-completed-modal">Close</button>',
    storage: { sitepulse_realtime_tracking: 'true' }, fetch: () => new Response('Forbidden', { status: 403 })
  });
  assert.equal(logs.error.length, 2);
  $('#close-completed-modal').trigger('click');
  assert.equal($('#sitepulse-modal').css('display'), 'none');
});

test('resource detection briefly updates tracking state and Escape stops the visible session', async t => {
  const { $, window, clock, flush } = await browser(t, {
    scripts: ['frontend.js'], html: frontHtml,
    prepare(window) {
      // jsdom has no layout engine; expose the visible modal's measured width
      // so jQuery's actual :visible predicate behaves as in a browser.
      const modal = window.document.getElementById('sitepulse-modal');
      Object.defineProperty(modal, 'offsetWidth', { get: () => modal.style.display === 'none' ? 0 : 300 });
    }
  });
  window.Math.random = () => 0.1;
  $('#start-tracking-btn').trigger('click');
  await clock.advance(1000);
  assert.equal($('#tracking-state').text(), 'Resource detected...');
  await clock.advance(500);
  assert.equal($('#tracking-state').text(), 'Monitoring...');
  $(window.document).trigger($.Event('keydown', { key: 'Escape' }));
  await flush();
  assert.equal($('#sitepulse-modal').css('display'), 'none');
  assert.equal(window.localStorage.getItem('sitepulse_realtime_tracking'), null);
  assert.deepEqual(clock.pendingIntervals(), []);
});

const onboardingGlobals = { SitePulseOnboarding: { rest_url: 'https://sitepulse.test/wp-json/', nonce: 'nonce-test', admin_url: '#dashboard' } };
const onboardingHtml = '<button id="nextStep">Next</button><button id="finishOnboarding">Finish</button><button class="stepper-step" data-step="1">First</button><div class="onboarding-step" data-step="1"></div><div class="onboarding-step" data-step="2"></div><div class="onboarding-step" data-step="3"></div><div class="collection-details"></div><span id="stat-hooks"></span><span id="stat-memory"></span>';

test('collection continues after profiler activation fails and malformed JSON values get safe defaults', async t => {
  const { $, clock, logs } = await browser(t, {
    scripts: ['onboarding.js'], html: onboardingHtml, globals: onboardingGlobals,
    fetch: call => {
      if (call.url.endsWith('/sp_profiler/set_active')) return jsonResponse({ message: 'Unavailable' }, 503);
      if (call.url.endsWith('/memory_info')) return jsonResponse({ memory: { toString: null } });
      return jsonResponse({ count: { toString: null } });
    }
  });
  $('#nextStep').trigger('click').trigger('click');
  await clock.advance(5000);
  assert.equal(logs.warn.length, 1);
  assert.equal($('#stat-hooks').text(), '0');
  assert.equal($('#stat-memory').text(), 'N/A');
  assert.notEqual($('#finishOnboarding').css('display'), 'none');
});

test('stepper can revisit the current step and Enter advances then finishes a completed wizard', async t => {
  const { $, window, clock } = await browser(t, { scripts: ['onboarding.js'], html: onboardingHtml, globals: onboardingGlobals });
  $('.stepper-step[data-step="1"]').trigger('click');
  assert.equal($('.onboarding-step.active').data('step'), 1);
  $(window.document).trigger($.Event('keydown', { keyCode: 13 }));
  assert.equal($('.onboarding-step.active').data('step'), 2);
  $(window.document).trigger($.Event('keydown', { keyCode: 13 }));
  await clock.advance(5000);
  $(window.document).trigger($.Event('keydown', { keyCode: 13 }));
  await clock.advance(1500);
  assert.equal(window.location.hash, '#dashboard');
});

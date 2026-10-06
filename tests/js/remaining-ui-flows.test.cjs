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

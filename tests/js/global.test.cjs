const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse } = require('./helpers/browser.cjs');

test('notices dismiss through distinct authenticated REST endpoints', async t => {
  const { $, network, flush } = await browser(t, {
    scripts: ['sitepulse_global.js'],
    html: '<div id="sitepulse-trackers-disabled-notice"><button class="notice-dismiss"></button></div><div id="sitepulse-onboarding-notice"><button class="notice-dismiss"></button></div>'
  });
  $('#sitepulse-onboarding-notice').trigger('click');
  assert.equal(network.fetch.length, 0);
  $('#sitepulse-trackers-disabled-notice .notice-dismiss').trigger('click');
  $('#sitepulse-onboarding-notice .notice-dismiss').trigger('click');
  await flush();
  assert.deepEqual(network.fetch.map(call => call.url), [
    'https://sitepulse.test/wp-json/sitepulse/v1/trackers_disabled_notice/dismiss',
    'https://sitepulse.test/wp-json/sitepulse/v1/onboarding_notice/dismiss'
  ]);
  for (const call of network.fetch) {
    assert.equal(call.method, 'POST');
    assert.equal(call.headers['X-WP-Nonce'], 'nonce-test');
    assert.deepEqual(JSON.parse(call.body), { _wpnonce: 'nonce-test' });
  }
});

for (const [name, response, message] of [
  ['JSON error', () => jsonResponse({ code: 'rest_forbidden', message: 'Nonce expired', data: { status: 403 } }, 403), 'Nonce expired'],
  ['non-JSON error', () => new Response('Service unavailable', { status: 503 }), '503'],
  ['network failure', () => { throw new Error('Offline'); }, 'Offline']
]) {
  test(`notice dismissal handles ${name} without an unhandled rejection`, async t => {
    const { $, logs, flush } = await browser(t, { scripts: ['sitepulse_global.js'], html: '<div id="sitepulse-onboarding-notice"><button class="notice-dismiss"></button></div>', fetch: response });
    $('.notice-dismiss').trigger('click');
    await flush();
    assert.equal(logs.error.length, 1);
    assert.match(logs.error[0][1].message, new RegExp(message));
  });
}

test('alerts share a container, prepend newer messages, and wait for their dismiss timeout', async t => {
  const { window, $, clock } = await browser(t, { scripts: ['sitepulse_global.js'] });
  window.showAlert('First', 'warning', 1000);
  window.showAlert('<strong>Second</strong>', 'danger', 0);
  clock.frame();
  assert.equal($('#sitepulse-alerts').length, 1);
  assert.deepEqual($('.sitepulse-alert').map((_, el) => $(el).text()).get(), ['Second', 'First']);
  assert.equal($('.sitepulse-alert.show').length, 2);
  assert.equal($('.sitepulse-alert-danger strong').text(), 'Second');
  await clock.advance(999);
  assert.equal($('.sitepulse-alert-warning').hasClass('show'), true);
  await clock.advance(1);
  assert.equal($('.sitepulse-alert-warning').hasClass('show'), false);
  await clock.advance(350);
  assert.equal($('.sitepulse-alert-warning').length, 0);
  assert.equal($('.sitepulse-alert-danger').length, 1);
});

test('click dismissal removes an alert on transitionend and toast uses a success state', async t => {
  const { window, $, clock } = await browser(t, { scripts: ['sitepulse_global.js'] });
  const toast = window.showToast('Saved');
  clock.frame();
  assert.equal(toast.hasClass('sitepulse-alert-success'), true);
  toast.trigger('transitionend');
  assert.equal($('.sitepulse-alert').length, 1);
  toast.trigger('click').trigger('transitionend');
  assert.equal($('.sitepulse-alert').length, 0);
});

for (const [action, expected] of [['.sitepulse-confirm-ok', true], ['.sitepulse-confirm-cancel', false], ['Escape', false]]) {
  test(`confirmation ${action} returns the decision and removes its modal`, async t => {
    const { window, $ } = await browser(t, { scripts: ['sitepulse_global.js'] });
    const results = [];
    window.showConfirm('<img src=x onerror=alert(1)>', value => results.push(value), { okText: 'Proceed', cancelText: 'Stay' });
    assert.equal($('.sitepulse-confirm-message img').length, 0);
    assert.equal($('.sitepulse-confirm-message').text(), '<img src=x onerror=alert(1)>');
    assert.equal($('.sitepulse-confirm-ok').text(), 'Proceed');
    if (action === 'Escape') $(window.document).trigger($.Event('keyup', { key: 'Escape' }));
    else $(action).trigger('click');
    assert.deepEqual(results, [expected]);
    assert.equal($('.sitepulse-confirm-overlay').length, 0);
  });
}

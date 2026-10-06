const test = require('node:test');
const assert = require('node:assert/strict');
const { browser } = require('./helpers/browser.cjs');

const localization = {
  SitePulseEasy: {
    rest_url: 'https://sitepulse.test/wp-json/', ajax_url: 'https://sitepulse.test/wp-admin/admin-ajax.php',
    nonce: 'nonce-easy', theme: 'light', i18n: {}
  }
};
function setup(t, html = '', options = {}) {
  return browser(t, {
    html: `<div class="sp-easy" style="--sp-score-excellent: #00ff00; --sp-score-good: #00ffff; --sp-score-fair: #ffff00; --sp-score-poor: #ff0000">${html}</div>`,
    scripts: ['easy-mode.js'], globals: structuredClone(localization), ...options
  });
}
function assertRest(call, endpoint, payload) {
  assert.equal(call.url, `https://sitepulse.test/wp-json/sitepulse/v1/${endpoint}`);
  assert.equal(call.method, 'POST');
  assert.equal(call.headers['X-WP-Nonce'], 'nonce-easy');
  assert.deepEqual(JSON.parse(call.body), { ...payload, _wpnonce: 'nonce-easy' });
}

test('theme selection updates the dashboard immediately and persists authenticated preference', async t => {
  const { $, network } = await setup(t, '<button class="sp-theme-btn active" data-theme="light">Light</button><button class="sp-theme-btn" data-theme="dark">Dark</button>');
  assert.equal($('.sp-easy').attr('data-theme'), 'light');
  $('.sp-theme-btn[data-theme="dark"]').trigger('click');
  assert.equal($('.sp-easy').attr('data-theme'), 'dark');
  assert.equal($('.sp-theme-btn.active').data('theme'), 'dark');
  assert.equal(network.ajax[0].url, 'https://sitepulse.test/wp-admin/admin-ajax.php');
  assert.deepEqual(Object.fromEntries(new URLSearchParams(network.ajax[0].body)), {
    action: 'sitepulse_toggle_theme', theme: 'dark', nonce: 'nonce-easy'
  });
});

test('score ring threshold colors and progress distinguish poor, fair, good, and excellent scores', async t => {
  const cases = [[0, '#ff0000'], [39, '#ff0000'], [40, '#ffff00'], [59, '#ffff00'], [60, '#00ffff'], [79, '#00ffff'], [80, '#00ff00'], [100, '#00ff00']];
  const { $, clock } = await setup(t, cases.map(([score]) => `<div class="sp-score-ring" data-score="${score}"><svg><circle class="sp-score-ring-fill"></circle></svg><span class="sp-score-ring-value">${score}</span></div>`).join(''));
  await clock.advance(200);
  for (const [score, color] of cases) {
    const fill = $(`.sp-score-ring[data-score="${score}"] .sp-score-ring-fill`);
    assert.equal(fill.css('stroke'), color);
  }
  assert.equal(parseFloat($('.sp-score-ring[data-score="100"] .sp-score-ring-fill').css('stroke-dashoffset')), 0);
  assert.ok(Math.abs(parseFloat($('.sp-score-ring[data-score="0"] .sp-score-ring-fill').css('stroke-dashoffset')) - 263.8937829) < 0.0001);
  assert.ok(Math.abs(parseFloat($('.sp-score-ring[data-score="40"] .sp-score-ring-fill').css('stroke-dashoffset')) - 158.3362697) < 0.0001);
});

test('sidebar uses a mobile overlay and collapses the desktop dashboard', async t => {
  const { $, window } = await setup(t, '<button class="sp-topbar-burger"></button><aside class="sp-easy-sidebar"></aside><div class="sp-sidebar-overlay"></div>');
  Object.defineProperty(window.document.documentElement, 'clientWidth', { configurable: true, value: 768 });
  $('.sp-topbar-burger').trigger('click');
  assert.equal($('.sp-easy-sidebar').hasClass('sp-sidebar-open'), true);
  assert.equal($('.sp-sidebar-overlay').hasClass('active'), true);
  $('.sp-sidebar-overlay').trigger('click');
  assert.equal($('.sp-easy-sidebar').hasClass('sp-sidebar-open'), false);
  assert.equal($('.sp-sidebar-overlay').hasClass('active'), false);
  Object.defineProperty(window.document.documentElement, 'clientWidth', { configurable: true, value: 1200 });
  $('.sp-topbar-burger').trigger('click');
  assert.equal($('.sp-easy').hasClass('sp-sidebar-collapsed'), true);
  assert.equal($('.sp-easy-sidebar').hasClass('sp-sidebar-open'), false);
});

test('accordion toggles only its adjacent body', async t => {
  const { $ } = await setup(t, '<button class="sp-accordion-header" id="first"></button><div class="sp-accordion-body"></div><button class="sp-accordion-header"></button><div class="sp-accordion-body"></div>');
  $('#first').trigger('click');
  assert.equal($('.sp-accordion-body.open').length, 1);
  assert.equal($('#first').hasClass('active'), true);
  $('#first').trigger('click');
  assert.equal($('.sp-accordion-body.open').length, 0);
});

for (const [selector, setting] of [['#sp-easy-cron-toggle', 'cron_disabled'], ['#sp-easy-email-toggle', 'email_blocking_enabled']]) {
  test(`${setting} requires confirmation to enable and sends booleans with nonce protection`, async t => {
    const { $, dialogs, network } = await setup(t, `<input type="checkbox" id="${selector.slice(1)}">`);
    dialogs.answer = false;
    $(selector).prop('checked', true).trigger('change');
    assert.equal($(selector).prop('checked'), false);
    assert.equal(network.ajax.length, 0);
    dialogs.answer = true;
    $(selector).prop('checked', true).trigger('change');
    assertRest(network.ajax[0], 'settings/update', { [setting]: true });
    $(selector).prop('checked', false).trigger('change');
    assertRest(network.ajax[1], 'settings/update', { [setting]: false });
    assert.equal(dialogs.confirmations.length, 2, 'turning protection off does not prompt');
  });
}

test('SQL detail view escapes query and caller text, reuses its modal, and handles missing callers', async t => {
  const { $ } = await setup(t, '<button class="sp-sql-detail-btn" id="query"></button><button class="sp-sql-detail-btn" id="empty"></button>');
  $('#empty').trigger('click');
  assert.equal($('#sp-sql-detail-modal').length, 0);
  $('#query').data({ query: 'SELECT "<img src=x onerror=alert(1)>"', caller: '<script>bad()</script>' }).trigger('click');
  assert.equal($('.sp-sql-detail-code').text(), 'SELECT "<img src=x onerror=alert(1)>"');
  assert.equal($('.sp-sql-detail-code img, .sp-sql-detail-caller script').length, 0);
  assert.match($('.sp-sql-detail-caller').text(), /<script>bad\(\)<\/script>/);
  $('.sp-sql-detail-close').trigger('click');
  assert.equal($('#sp-sql-detail-modal').css('display'), 'none');
  $('#query').data({ query: 'SELECT 1', caller: '' }).trigger('click');
  assert.equal($('#sp-sql-detail-modal').length, 1);
  assert.equal($('.sp-sql-detail-code').text(), 'SELECT 1');
  assert.equal($('.sp-sql-detail-caller').css('display'), 'none');
  $('#sp-sql-detail-modal').trigger('click');
  assert.equal($('#sp-sql-detail-modal').css('display'), 'none');
});

test('pet opens on request, closes outside, and nudges without opening itself', async t => {
  const { $, clock } = await setup(t, '<div id="sp-pet-assistant" class="sp-pet--alert"><button id="sp-pet-trigger"></button><div id="sp-pet-panel" style="display:none"><button id="sp-pet-close"></button></div></div><button id="outside"></button>');
  await clock.advance(8000);
  assert.equal($('#sp-pet-trigger').hasClass('sp-pet-nudge-hard'), true);
  assert.equal($('#sp-pet-panel').css('display'), 'none');
  await clock.advance(2000);
  assert.equal($('#sp-pet-trigger').hasClass('sp-pet-nudge-hard'), false);
  $('#sp-pet-trigger').trigger('click');
  assert.notEqual($('#sp-pet-panel').css('display'), 'none');
  $('#sp-pet-panel').trigger('click');
  assert.notEqual($('#sp-pet-panel').css('display'), 'none');
  $('#outside').trigger('click');
  assert.equal($('#sp-pet-panel').css('display'), 'none');
  $('#sp-pet-trigger').trigger('click');
  $('#sp-pet-close').trigger('click');
  assert.equal($('#sp-pet-panel').css('display'), 'none');
});

test('failed classic-mode switch restores its button for another attempt', async t => {
  const { $, network } = await setup(t, '<button class="sp-sidebar-toggle-classic">Switch to Classic</button>');
  $('.sp-sidebar-toggle-classic').trigger('click');
  assert.equal($('.sp-sidebar-toggle-classic').prop('disabled'), true);
  assert.deepEqual(Object.fromEntries(new URLSearchParams(network.ajax[0].body)), { action: 'sitepulse_toggle_easy_mode', nonce: 'nonce-easy' });
  network.ajax[0].respond({ success: false }, 500);
  assert.equal($('.sp-sidebar-toggle-classic').prop('disabled'), false);
});

test('wizard restart cancels cleanly and restores the action on REST failure', async t => {
  const { $, network, dialogs } = await setup(t, '<button class="sp-wizard-restart-btn">Restart Wizard</button>');
  dialogs.answer = false;
  $('.sp-wizard-restart-btn').trigger('click');
  assert.equal(network.ajax.length, 0);
  dialogs.answer = true;
  $('.sp-wizard-restart-btn').trigger('click');
  assertRest(network.ajax[0], 'onboarding/reset', {});
  network.ajax[0].respond({ message: 'Permission denied' }, 403);
  assert.equal($('.sp-wizard-restart-btn').prop('disabled'), false);
  assert.equal(dialogs.alerts.length, 1);
});

for (const [selector, endpoints] of [
  ['.sp-easy-clear-data', ['clear_load_events/clear', 'clear_curl_api_events/clear']],
  ['.sp-clear-profiler-btn', ['clear_load_events/clear']],
  ['.sp-clear-curl-btn', ['clear_curl_api_events/clear']]
]) {
  test(`${selector} clears the selected datasets and permits retry after failure`, async t => {
    const { $, network, dialogs, clock } = await setup(t, `<button class="${selector.slice(1)}">Clear</button>`);
    dialogs.answer = false;
    $(selector).trigger('click');
    assert.equal(network.ajax.length, 0);
    dialogs.answer = true;
    $(selector).trigger('click');
    assert.equal($(selector).prop('disabled'), true);
    assert.deepEqual(network.ajax.map(call => call.url.split('/sitepulse/v1/')[1]), endpoints);
    for (const call of network.ajax) {
      assert.equal(call.headers['X-WP-Nonce'], 'nonce-easy');
      assert.deepEqual(Object.fromEntries(new URLSearchParams(call.body)), { _wpnonce: 'nonce-easy' });
    }
    network.ajax[0].respond({ success: false }, 500);
    await clock.advance(0);
    assert.equal($(selector).prop('disabled'), false);
    assert.equal(dialogs.alerts.length, 1);
  });
}

test('error-log clearing uses its AJAX action and restores the button on server failure', async t => {
  const { $, network, dialogs } = await setup(t, '<button class="sp-clear-error-log-btn">Clear Error Log</button>');
  $('.sp-clear-error-log-btn').trigger('click');
  assert.equal($('.sp-clear-error-log-btn').prop('disabled'), true);
  assert.deepEqual(Object.fromEntries(new URLSearchParams(network.ajax[0].body)), { action: 'sitepulse_clear_error_log', nonce: 'nonce-easy' });
  network.ajax[0].respond({ success: false }, 500);
  assert.equal($('.sp-clear-error-log-btn').prop('disabled'), false);
  assert.equal(dialogs.alerts.length, 1);
});

test('error detail toggle updates the matching stack trace and accessible button text', async t => {
  const { $ } = await setup(t, '<div class="sp-list-item-body"><button class="sp-error-expand-btn"><span class="dashicons dashicons-arrow-down-alt2"></span><span class="sp-btn-text">View Details</span></button><pre class="sp-error-callstack"></pre></div>');
  $('.sp-error-expand-btn').trigger('click');
  assert.equal($('.sp-error-callstack').hasClass('open'), true);
  assert.equal($('.sp-btn-text').text(), 'Hide Details');
  assert.equal($('.dashicons').hasClass('dashicons-arrow-up-alt2'), true);
  $('.sp-error-expand-btn').trigger('click');
  assert.equal($('.sp-error-callstack').hasClass('open'), false);
  assert.equal($('.sp-btn-text').text(), 'View Details');
});

for (const status of [200, 500]) {
  test(`SAVEQUERIES failure with HTTP ${status} restores the original control`, async t => {
    const { $, network, dialogs } = await setup(t, '<button class="sp-enable-savequeries"><strong>Enable SQL monitoring</strong></button>');
    $('.sp-enable-savequeries').trigger('click');
    assert.equal($('.sp-enable-savequeries').prop('disabled'), true);
    assert.deepEqual(Object.fromEntries(new URLSearchParams(network.ajax[0].body)), { action: 'sitepulse_enable_savequeries', nonce: 'nonce-easy' });
    network.ajax[0].respond({ success: false, data: { message: 'Cannot write wp-config.php' } }, status);
    assert.equal($('.sp-enable-savequeries').prop('disabled'), false);
    assert.equal($('.sp-enable-savequeries strong').text(), 'Enable SQL monitoring');
    assert.equal(dialogs.alerts.length, 1);
    if (status === 200) assert.match(dialogs.alerts[0], /Cannot write wp-config/);
  });
}

test('declining SAVEQUERIES does not write to the server', async t => {
  const { $, network, dialogs } = await setup(t, '<button class="sp-enable-savequeries">Enable</button>');
  dialogs.answer = false;
  $('.sp-enable-savequeries').trigger('click');
  assert.equal(network.ajax.length, 0);
  assert.equal($('.sp-enable-savequeries').prop('disabled'), false);
});

for (const [status, hidden] of [[200, true], [500, false]]) {
  test(`dismissing the getting-started checklist hides it and ${hidden ? 'stays hidden' : 'comes back if saving fails'} (HTTP ${status})`, async t => {
    const { $, network, flush } = await setup(t, '<div class="sp-getting-started"><button class="sp-getting-started-dismiss">×</button><p>Steps</p></div>');
    $('.sp-getting-started-dismiss').trigger('click');
    assert.equal($('.sp-getting-started').css('display'), 'none');
    assertRest(network.ajax[0], 'getting_started/dismiss', {});
    network.ajax[0].respond(status === 200 ? { success: true } : { message: 'Error' }, status);
    await flush();
    assert.equal($('.sp-getting-started').css('display') === 'none', hidden);
  });
}

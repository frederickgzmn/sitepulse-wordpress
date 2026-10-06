const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse } = require('./helpers/browser.cjs');

// Expected-correct regressions for verified production defects, now repaired.
test('a completed confirmation must not invoke its callback again on a later Escape', async t => {
  const { window, $ } = await browser(t, { scripts: ['sitepulse_global.js'] });
  const decisions = [];
  window.showConfirm('Continue?', result => decisions.push(result));
  $('.sitepulse-confirm-ok').trigger('click');
  assert.deepEqual(decisions, [true]);
  $(window.document).trigger($.Event('keyup', { key: 'Escape' }));
  assert.deepEqual(decisions, [true]);
});

test('restoring a session the server has stopped must leave the UI idle with no tracking timer', async t => {
  const { $, clock, window } = await browser(t, {
    scripts: ['frontend.js'],
    html: '<input id="curlSwitch" type="checkbox" data-sitepulse-page-id="42"><div data-sitepulse-realtime="tracking"></div><span class="sitepulse-status-dot"></span><span class="status-text"></span><button id="start-tracking-btn"></button><button id="stop-tracking-btn"></button>',
    storage: { sitepulse_realtime_tracking: 'true', sitepulse_tracking_start_time: '1700000000000' },
    fetch: () => jsonResponse({ success: true, real_time_status: false })
  });
  assert.equal(window.localStorage.getItem('sitepulse_realtime_tracking'), null);
  assert.equal($('.sitepulse-status-dot').hasClass('idle'), true);
  assert.equal($('#stop-tracking-btn').css('display'), 'none');
  assert.deepEqual(clock.pendingIntervals(), []);
});

test('completed profiler events already in milliseconds must not be multiplied by 1000', async t => {
  const { $ } = await browser(t, {
    scripts: ['frontend.js'],
    html: '<input id="curlSwitch" type="checkbox" data-sitepulse-page-id="42"><div data-sitepulse-realtime="stopped"></div><div id="sitepulse-completion-info"></div>',
    storage: { sitepulse_realtime_tracking: 'true' },
    globals: { SitePulse: {
      rest_url: 'https://sitepulse.test/wp-json/', nonce: 'nonce-test', post_load_events_count: 1,
      post_load_events: {
        event42: { hook: 'init', priority: 10, sig: 'Plugin::init', fileline: 'plugin.php:12', calls: 2, total_ms: 3, max_ms: 2, avg_ms: 1.5, source: 'plugin' }
      }
    } }
  });
  assert.match($('.sitepulse-event').text(), /Total Time: 3\.000 ms/);
  assert.match($('.sitepulse-event').text(), /Max Time: 2\.000 ms/);
  assert.match($('.sitepulse-event').text(), /Avg: 1\.500 ms/);
});

test('a failed collection request is shown as an error instead of a completed task', async t => {
  const { $, clock } = await browser(t, {
    scripts: ['onboarding.js'],
    html: '<button id="nextStep">Next</button><div class="collection-details"><div class="detail-item" data-task="profiler"><span class="detail-status pending"></span></div></div>',
    globals: { SitePulseOnboarding: { rest_url: 'https://sitepulse.test/wp-json/', nonce: 'nonce-test', admin_url: '#dashboard' } },
    fetch: call => call.url.endsWith('/profiler_stats') ? jsonResponse({ message: 'Unavailable' }, 503) : jsonResponse({ success: true })
  });
  $('#nextStep').trigger('click').trigger('click');
  await clock.advance(5000);
  assert.equal($('.detail-item[data-task="profiler"] .detail-status').hasClass('error'), true);
  assert.equal($('.detail-item[data-task="profiler"]').hasClass('completed'), false);
});

test('a failed SAVEQUERIES request reports a retryable error without an unhandled rejection', async t => {
  const { $, flush } = await browser(t, {
    scripts: ['sitepulse_global.js', 'backend.js'],
    html: '<button class="fix_enable_savequeries">Enable</button>',
    fetch: () => jsonResponse({ message: 'Cannot update wp-config.php' }, 500)
  });
  $('.fix_enable_savequeries').trigger('click');
  await flush();
  assert.match($('.sitepulse-alert-danger').text(), /Cannot update wp-config\.php/);
});

const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse } = require('./helpers/browser.cjs');

const backend = (t, html, options = {}) => browser(t, { html, scripts: ['sitepulse_global.js', 'backend.js'], ...options });
const memory = { success: true, memory: { formatted: '32 MB', percent: '25%', percent_class: 'normal' } };

test('every dashboard score tier has a distinct usable progress color and angle', async t => {
  const tiers = [[95, '#00ff88'], [90, '#00ff88'], [85, '#00ff88'], [80, '#00e676'], [75, '#5a7ce8'], [70, '#6c5ce7'], [65, '#6c5ce7'], [60, '#d49a5a'], [55, '#ffb86b'], [50, '#ff8a5c'], [45, '#ff6b6b'], [40, '#ff5555'], [35, '#ff4444'], [30, '#e63939'], [25, '#cc2e2e'], [20, '#b32424'], [15, '#991a1a'], [10, '#801010'], [5, '#660808'], [0, '#4d0606']];
  const { $ } = await backend(t, tiers.map(([score]) => `<div class="sp-pagespeed-metric" data-score="${score}"></div>`).join(''));
  for (const [score, color] of tiers) {
    const style = $(`[data-score="${score}"]`)[0].style;
    assert.equal(style.getPropertyValue('--progress-color'), color);
    assert.ok(parseFloat(style.getPropertyValue('--progress-angle')) >= 0);
    assert.ok(parseFloat(style.getPropertyValue('--progress-angle')) <= 360);
  }
});

for (const percent of ['not-a-percentage', null]) {
  test(`malformed memory percentage ${percent} preserves the last score and clears heat spikes`, async t => {
    const { $, clock, window } = await backend(t, '<span id="sp_js_memory"></span><span id="sp-memory-score">80</span>', {
      fetch: () => jsonResponse({ success: true, memory: { formatted: 'Unknown', percent, percent_class: 'warning' } })
    });
    window.setHeatmonitorSpike(4);
    await clock.advance(10000);
    assert.equal($('#sp-memory-score').text(), '80');
    assert.equal(window.__sitepulse_active_pulse, 'pulse-v0');
  });
}

for (const [selector, element] of [
  ['.sp_profiler', '<input class="sp_profiler" type="checkbox">'],
  ['.sp_http_load', '<input class="sp_http_load" type="checkbox">'],
  ['.sp_dark_mode', '<input class="sp_dark_mode" type="checkbox">'],
  ['.sp_curl_api_clear_events', '<button class="sp_curl_api_clear_events">Clear</button>'],
  ['.sp-collect-plugin-data', '<button class="sp-collect-plugin-data">Collect</button>']
]) {
  test(`${selector} handles a server failure containing a non-JSON error page`, async t => {
    const { $, logs, flush, navigation } = await backend(t, element, { fetch: () => new Response('Server unavailable', { status: 503 }) });
    $(selector).trigger('click');
    await flush();
    assert.equal(logs.error.length, 1);
    assert.match(logs.error[0][1].message, /503/);
    assert.equal($('.sitepulse-alert-success').length, 0);
    assert.equal(navigation.attempts, 0);
  });
}

for (const [selector, element, expected] of [
  ['.sp_curl_and_profiler_clear_events', '<button class="sp_curl_and_profiler_clear_events">Clear</button>', 2],
  ['.sp_profiler', '<input id="sp-profiler" class="sp_profiler" type="checkbox">', 1],
  ['.sp_http_load', '<input id="sp-http-load" class="sp_http_load" type="checkbox">', 1],
  ['.fix_enable_savequeries', '<button class="fix_enable_savequeries">Enable</button>', 1],
  ['.sp-new-experience-cta', '<button class="sp-new-experience-cta" data-ajax-url="https://sitepulse.test/wp-admin/admin-ajax.php">Switch</button>', 1],
  ['#sp_reset_onboarding', '<button id="sp_reset_onboarding">Reset</button>', 1],
  ['.sp-collect-plugin-data', '<button class="sp-collect-plugin-data">Collect</button>', 1],
  ['.sp-request-ai-diagnostic-btn', '<button class="sp-request-ai-diagnostic-btn">Diagnose</button>', 1]
]) {
  test(`${selector} refreshes the dashboard after its successful action`, async t => {
    const { $, clock, navigation, network } = await backend(t, element, {
      fetch: () => jsonResponse({ success: true, report_mode: 1, data: { status: 'completed' } })
    });
    navigation.expected = expected;
    $(selector).trigger('click');
    await clock.advance(5000);
    assert.equal(navigation.attempts, expected);
    assert.equal(network.fetch.length, expected);
    assert.ok($('.sitepulse-alert-danger').length === 0);
  });
}

test('run-onboarding action navigates without resetting server settings', async t => {
  const { $, navigation, network } = await backend(t, '<button id="sp_run_onboarding">Run wizard</button>');
  navigation.expected = 1;
  $('#sp_run_onboarding').trigger('click');
  assert.equal(navigation.attempts, 1);
  assert.equal(network.fetch.length, 0);
});

test('heat monitor rebuilds its SVG points, animates through rise and rest, and changes intensity', async t => {
  const points = [];
  const { window, clock } = await backend(t, '<svg id="mainSVG"><polyline id="pulseLine"></polyline></svg>', {
    prepare(window) {
      // jsdom lacks SVGPoint/SVGPointList. This adapter models only those DOM
      // storage operations; the production easing and animation run unchanged.
      window.document.getElementById('mainSVG').createSVGPoint = () => ({ x: 0, y: 0 });
      Object.defineProperty(window.document.getElementById('pulseLine'), 'points', { value: {
        get numberOfItems() { return points.length; },
        appendItem(point) { const stored = { x: point.x, y: point.y }; points.push(stored); return stored; },
        removeItem(index) { return points.splice(index, 1)[0]; }
      } });
    }
  });
  assert.equal(points.length, 400);
  assert.deepEqual(points[0], { x: 800, y: 110 });
  assert.deepEqual(points[399], { x: 2, y: 110 });
  await clock.advance(500);
  clock.frame();
  assert.ok(points[0].y < 110 && Number.isFinite(points[0].y));
  assert.equal(points[399].y, 110, 'staggered points wait for their start time');
  await clock.advance(500);
  clock.frame();
  assert.equal(points[0].y, 95);
  await clock.advance(500);
  clock.frame();
  assert.equal(points[0].y, 95, 'completed point holds position during rest');
  window.setHeatmonitorSpike(2);
  assert.equal(window.__sitepulse_active_pulse, 'pulse-v2');
  window.setHeatmonitorSpike(5);
  await clock.advance(1000);
  clock.frame();
  assert.equal(points[0].y, -30);
  window.setHeatmonitorSpike('invalid');
  assert.equal(window.__sitepulse_active_pulse, 'pulse-v0');
  window.dispatchEvent(new window.Event('sitepulse:refresh'));
  assert.equal(points.length, 400, 'refresh replaces existing points instead of duplicating them');
});

test('collapsible widget icon follows the resulting Bootstrap visibility state', async t => {
  const { $, clock } = await backend(t, '<button class="sp-widget-header" data-bs-target="#widget"><span class="sp-toggle-btn"><i class="dashicons"></i></span></button><section id="widget" class="show"></section>');
  $('.sp-widget-header').trigger('click');
  await clock.advance(100);
  assert.equal($('.dashicons')[0].style.transform, 'rotate(0deg)');
  $('#widget').removeClass('show');
  $('.sp-widget-header').trigger('click');
  await clock.advance(100);
  assert.equal($('.dashicons')[0].style.transform, 'rotate(-90deg)');
});

test('server rejection of a vulnerability check releases all scan controls', async t => {
  const { $, flush } = await backend(t, '<button class="sp-check-vulnerabilities-btn">Check</button><div class="sp-vulnerabilities-results"></div>', {
    fetch: () => new Response('Unavailable', { status: 503 })
  });
  $('.sp-check-vulnerabilities-btn').trigger('click');
  await flush();
  assert.match($('.sp-vulnerabilities-results').text(), /503/);
  assert.equal($('.sp-check-vulnerabilities-btn').prop('disabled'), false);
});

test('failed diagnostic polling stops and refreshes to show the failed server result', async t => {
  const { $, clock, navigation } = await backend(t, '<div class="sp-ai-diagnostic-widget" data-status="processing"></div>', {
    fetch: call => jsonResponse(call.url.endsWith('/memory_checker/check') ? memory : { success: true, data: { status: 'failed' } })
  });
  navigation.expected = 1;
  await clock.advance(60000);
  assert.equal(clock.pendingIntervals().includes(60000), false);
  assert.equal($('.sitepulse-alert-danger').length, 1);
  await clock.advance(2000);
  assert.equal(navigation.attempts, 1);
});

test('pending AI request refreshes to its in-progress result and application rejection permits retry', async t => {
  const page = await backend(t, '<button class="sp-request-ai-diagnostic-btn">Diagnose</button>', {
    fetch: () => jsonResponse({ success: true, data: { status: 'pending' } })
  });
  page.navigation.expected = 1;
  page.$('.sp-request-ai-diagnostic-btn').trigger('click');
  await page.clock.advance(2000);
  assert.equal(page.navigation.attempts, 1);
  page.setFetch(() => jsonResponse({ success: false, data: { message: 'Daily limit reached' } }));
  page.$('.sp-request-ai-diagnostic-btn').prop('disabled', false).trigger('click');
  await page.flush();
  assert.match(page.$('.sitepulse-alert-warning').text(), /Daily limit/);
  assert.equal(page.$('.sp-request-ai-diagnostic-btn').prop('disabled'), false);
});

test('requesting another diagnostic replaces an existing polling interval instead of duplicating it', async t => {
  const { $, clock, network, navigation } = await backend(t, '<div class="sp-ai-diagnostic-widget" data-status="pending"></div><button class="sp-request-ai-diagnostic-btn" data-force="true">Run again</button>', {
    fetch: call => jsonResponse(call.url.endsWith('/memory_checker/check') ? memory : { success: true, data: { status: 'pending' } })
  });
  navigation.expected = 1;
  $('.sp-request-ai-diagnostic-btn').trigger('click');
  await clock.advance(60000);
  assert.equal(clock.pendingIntervals().filter(interval => interval === 60000).length, 1);
  assert.equal(network.fetch.filter(request => request.url.endsWith('/ai_diagnostic/status')).length, 1);
});

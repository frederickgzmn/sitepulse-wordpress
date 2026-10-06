const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse } = require('./helpers/browser.cjs');

const html = `
  <button id="prevStep">Previous</button><button id="nextStep">Next</button>
  <button id="finishOnboarding">Finish</button><button id="skipOnboarding">Skip</button>
  <input type="hidden" id="onboarding_view_mode" value="simple">
  <div class="view-mode-card selected" data-view="simple" role="radio" tabindex="0" aria-checked="true">Simple</div>
  <div class="view-mode-card" data-view="advanced" role="radio" tabindex="0" aria-checked="false">Advanced</div>
  <input type="checkbox" id="onboarding_profiler_enabled" checked>
  <input type="checkbox" id="onboarding_curl_enabled" checked>
  <input type="checkbox" id="onboarding_savequeries">
  <input type="checkbox" id="onboarding_email_blocking">
  <input type="checkbox" id="onboarding_external_api_enabled" checked>
  <div class="stepper-step active" data-step="1">One</div>
  <div class="stepper-step" data-step="2">Two</div>
  <div class="stepper-step" data-step="3">Three</div>
  <section class="onboarding-step active" data-step="1"></section>
  <section class="onboarding-step" data-step="2"></section>
  <section class="onboarding-step" data-step="3">
    <div class="onboarding-checkup">
      <div class="checkup-progress"><strong class="checkup-progress-text"></strong><div class="checkup-bar"><div class="checkup-bar-fill"></div></div></div>
      <div class="checkup-result" hidden></div>
      <div class="checkup-unavailable" hidden><p class="checkup-unavailable-reason"></p></div>
      <div class="checkup-next" hidden></div>
    </div>
  </section>`;

const i18n = { skip_confirm: 'Skip the setup?', finished: 'All set!', finish_error: 'Could not save your choices.', checkup_failed: 'Something went wrong while loading the page.' };
const analysisI18n = { starting: 'Preparing…', loading: 'Loading (%1$d of %2$d)…', building: 'Building…', error: 'Analysis error' };
const globals = () => ({
  SitePulseOnboarding: { rest_url: 'https://sitepulse.test/wp-json/', nonce: 'nonce-test', admin_url: '#dashboard', home_url: 'https://sitepulse.test/', i18n },
  SitePulsePageAnalysisData: { rest_url: 'https://sitepulse.test/wp-json/sitepulse/v1/page_analysis/', nonce: 'nonce-test', samples: 3, admin_link: '#analysis', i18n: analysisI18n }
});
const report = {
  id: 'home1', url: 'https://sitepulse.test/', title: 'Home', kind: 'Front page', complete: true, samples: 3, queries: 40, core_share: 70,
  http: [], callbacks: [], sources: [{ name: 'WooCommerce', type: 'plugin', time: '120 ms', share: 30 }],
  display: { server: '400 ms', first: '500 ms', response: '420 ms', core: '280 ms', http: '0 ms', memory: '40 MB' },
  verdict: { level: 'good', label: 'Fast', summary: 'Quick.', detail: '' },
  findings: [{ level: 'success', title: 'Nothing stands out', text: 'Good.' }]
};
// A REST server for the check-up endpoints and onboarding saves.
const analysisServer = ({ sample = () => ({ ok: true, samples: 1 }) } = {}) => call => {
  const path = call.url.split('/wp-json/')[1];
  if (path === 'sitepulse/v1/page_analysis/start') return jsonResponse({ id: 'home1', url: 'https://sitepulse.test/', samples: 3 });
  if (path === 'sitepulse/v1/page_analysis/sample') return jsonResponse(sample());
  if (path === 'sitepulse/v1/page_analysis/report') return jsonResponse(report);
  return jsonResponse({ success: true });
};

function setup(t, options = {}) {
  return browser(t, { html, scripts: ['page-analysis.js', 'onboarding.js'], globals: globals(), fetch: analysisServer(), ...options });
}
const saves = network => network.fetch.filter(call => !call.url.includes('/page_analysis/'));

async function reachCheckup(page) {
  page.$('#nextStep').trigger('click');
  page.$('#nextStep').trigger('click');
  await page.clock.advance(0);
  await page.flush();
}

test('onboarding starts on the first step with Simple preselected', async t => {
  const { $ } = await setup(t);
  assert.equal($('#onboarding_view_mode').val(), 'simple');
  assert.equal($('.onboarding-step.active').data('step'), 1);
  assert.equal($('#prevStep').css('display'), 'none');
  assert.equal($('#finishOnboarding').css('display'), 'none');
  assert.notEqual($('#skipOnboarding').css('display'), 'none');
});

test('interface cards behave like radio buttons with mouse and keyboard', async t => {
  const { $, window } = await setup(t);
  $('.view-mode-card[data-view="advanced"]').trigger('click');
  assert.equal($('#onboarding_view_mode').val(), 'advanced');
  assert.equal($('.view-mode-card.selected').data('view'), 'advanced');
  assert.equal($('.view-mode-card[data-view="simple"]').attr('aria-checked'), 'false');
  $('.view-mode-card[data-view="simple"]').trigger($.Event('keydown', { key: ' ' }));
  assert.equal($('.view-mode-card.selected').data('view'), 'simple');
  $('.view-mode-card[data-view="advanced"]').trigger($.Event('keydown', { key: 'Enter' }));
  assert.equal($('.view-mode-card.selected').data('view'), 'advanced');
  $('.view-mode-card[data-view="simple"]').trigger($.Event('keydown', { key: 'Tab' }));
  assert.equal($('.view-mode-card.selected').data('view'), 'advanced');
  assert.equal($('.onboarding-step.active').data('step'), 1, 'Enter on a card must not also advance the wizard');
  $(window.document).trigger($.Event('keydown', { key: 'ArrowRight' }));
  assert.equal($('.onboarding-step.active').data('step'), 2);
});

test('stepper only revisits completed steps and arrow keys move between steps', async t => {
  const { $, window, network } = await setup(t);
  $('.stepper-step[data-step="2"]').trigger('click');
  assert.equal($('.onboarding-step.active').data('step'), 1);
  $(window.document).trigger($.Event('keydown', { key: 'ArrowRight' }));
  assert.equal($('.stepper-step[data-step="1"]').hasClass('completed'), true);
  $('.stepper-step[data-step="1"]').trigger('click');
  assert.equal($('.onboarding-step.active').data('step'), 1);
  $(window.document).trigger($.Event('keydown', { key: 'ArrowLeft' }));
  assert.equal($('.onboarding-step.active').data('step'), 1);
  $('#nextStep').trigger('click');
  $('#prevStep').trigger('click');
  assert.equal($('.onboarding-step.active').data('step'), 1);
  $(window.document).trigger($.Event('keydown', { key: 'Escape' }));
  assert.equal(network.fetch.length, 0);
});

test('the first check-up analyzes the homepage and shows the compact report with next steps', async t => {
  const page = await setup(t);
  const { $, network } = page;
  await reachCheckup(page);
  assert.deepEqual(network.fetch.map(call => call.url.split('/').pop()), ['start', 'sample', 'sample', 'sample', 'report']);
  assert.equal(JSON.parse(network.fetch[0].body).url, 'https://sitepulse.test/');
  assert.equal($('.checkup-progress').prop('hidden'), true);
  assert.equal($('.checkup-result').prop('hidden'), false);
  assert.equal($('.checkup-result .sp-pa-result--compact').length, 1);
  assert.equal($('.checkup-next').prop('hidden'), false);
  assert.notEqual($('#finishOnboarding').css('display'), 'none');
  assert.equal($('#skipOnboarding').css('display'), 'none');
  $('#prevStep').trigger('click');
  $('#nextStep').trigger('click');
  await page.flush();
  assert.equal(network.fetch.length, 5, 'the check-up runs once');
});

test('progress is reported while the homepage loads', async t => {
  const page = await setup(t, { fetch: call => call.url.endsWith('/report') ? new Promise(() => {}) : analysisServer()(call) });
  await reachCheckup(page);
  assert.equal(page.$('.checkup-progress-text').text(), 'Building…');
  assert.equal(page.$('.checkup-bar-fill').css('width'), '96%');
});

test('a server that blocks loopback requests is explained without waiting for a visit', async t => {
  const page = await setup(t, { fetch: analysisServer({ sample: () => ({ ok: false, samples: 0, reason: 'loopback', message: 'Your server could not load the page by itself.', visit_url: 'x' }) }) });
  await reachCheckup(page);
  await page.clock.advance(2000);
  assert.equal(page.$('.checkup-unavailable').prop('hidden'), false);
  assert.equal(page.$('.checkup-unavailable-reason').text(), 'Your server could not load the page by itself.');
  assert.equal(page.$('.checkup-next').prop('hidden'), false);
  assert.equal(page.network.fetch.filter(call => call.url.endsWith('/collect')).length, 0);
});

test('check-up errors fall back to the server message or a generic one', async t => {
  const failing = await setup(t, { fetch: () => jsonResponse({ message: 'Rate limited' }, 429) });
  await reachCheckup(failing);
  assert.equal(failing.$('.checkup-unavailable-reason').text(), 'Rate limited');

  const silent = await setup(t, { fetch: () => jsonResponse({ message: '' }, 500), globals: { ...globals(), SitePulsePageAnalysisData: { ...globals().SitePulsePageAnalysisData, i18n: {} } } });
  await reachCheckup(silent);
  assert.equal(silent.$('.checkup-unavailable-reason').text(), 'Something went wrong while loading the page.');
});

test('the check-up degrades gracefully when the analysis engine is missing', async t => {
  const page = await browser(t, { html, scripts: ['onboarding.js'], globals: globals() });
  await reachCheckup(page);
  assert.equal(page.$('.checkup-unavailable-reason').text(), 'Something went wrong while loading the page.');
  assert.equal(page.network.fetch.length, 0);
});

test('finishing saves changed settings and the chosen interface, then opens the dashboard', async t => {
  const page = await setup(t);
  const { $, network, window, clock, flush } = page;
  $('.view-mode-card[data-view="advanced"]').trigger('click');
  $('#onboarding_profiler_enabled, #onboarding_curl_enabled, #onboarding_external_api_enabled').prop('checked', false).trigger('change');
  $('#onboarding_savequeries, #onboarding_email_blocking').prop('checked', true).trigger('change');
  await reachCheckup(page);
  $('#finishOnboarding').trigger('click');
  assert.equal($('#finishOnboarding').prop('disabled'), true);
  await flush();
  assert.deepEqual(saves(network).map(call => [call.url.split('/sitepulse/v1/')[1], JSON.parse(call.body)]), [
    ['sp_profiler/set_active', { sitepulse_profiler_enabled: 'disabled', _wpnonce: 'nonce-test' }],
    ['wpslowhttp/set_active', { wpslowhttp: 'disabled', _wpnonce: 'nonce-test' }],
    ['save_queries/enable', { _wpnonce: 'nonce-test' }],
    ['settings/update', { email_blocking_enabled: true, email_blocking_mode: 'sendmail_block', external_api_enabled: false, _wpnonce: 'nonce-test' }],
    ['onboarding/complete', { interface: 'advanced', _wpnonce: 'nonce-test' }]
  ]);
  assert.equal($('.notification-success').text(), 'All set!');
  assert.equal($('.notification-success').attr('role'), 'status');
  await clock.advance(1000);
  assert.equal(window.location.hash, '#dashboard');
  await clock.advance(3000);
  assert.equal($('.onboarding-notification').length, 0);
});

test('finishing unchanged defaults only completes onboarding with the Simple interface', async t => {
  const page = await setup(t);
  await reachCheckup(page);
  page.$('#finishOnboarding').trigger('click');
  await page.flush();
  assert.deepEqual(saves(page.network).map(call => JSON.parse(call.body)), [{ interface: 'simple', _wpnonce: 'nonce-test' }]);
});

test('Enter finishes from the last step but is ignored on focused controls', async t => {
  const page = await setup(t);
  const { $, window, clock } = page;
  $('#onboarding_savequeries').trigger($.Event('keydown', { key: 'Enter' }));
  assert.equal($('.onboarding-step.active').data('step'), 1);
  $(window.document).trigger($.Event('keydown', { key: 'Enter' }));
  $(window.document).trigger($.Event('keydown', { key: 'Enter' }));
  await clock.advance(0); await page.flush();
  assert.equal($('.onboarding-step.active').data('step'), 3);
  $(window.document).trigger($.Event('keydown', { key: 'ArrowRight' }));
  assert.equal($('.onboarding-step.active').data('step'), 3);
  $(window.document).trigger($.Event('keydown', { key: 'Enter' }));
  await page.flush();
  await clock.advance(1000);
  assert.equal(window.location.hash, '#dashboard');
});

for (const failure of ['configuration', 'completion']) {
  test(`${failure} failure keeps the wizard open and restores Finish for retry`, async t => {
    const page = await setup(t);
    await reachCheckup(page);
    const { $, network, window, flush, clock } = page;
    network.fetch.length = 0;
    if (failure === 'configuration') $('#onboarding_savequeries').prop('checked', true).trigger('change');
    page.setFetch(() => jsonResponse({ code: 'rest_forbidden', message: 'Access denied', data: { status: 403 } }, 403));
    $('#finishOnboarding').trigger('click');
    await flush();
    assert.equal($('#finishOnboarding').prop('disabled'), false);
    assert.equal($('#finishOnboarding').hasClass('loading'), false);
    assert.equal($('.notification-error').text(), 'Could not save your choices.');
    assert.equal(network.fetch.length, 1);
    await clock.advance(2000);
    assert.equal(window.location.hash, '');
  });
}

test('a failure without a readable body still keeps the wizard open', async t => {
  const page = await setup(t);
  await reachCheckup(page);
  page.setFetch(() => new Response('<html>error</html>', { status: 502 }));
  page.$('#finishOnboarding').trigger('click');
  await page.flush();
  assert.match(page.logs.error[0][1].message, /Request failed: 502/);
});

test('declining skip leaves the wizard and server unchanged', async t => {
  const { $, network, dialogs, window } = await setup(t);
  dialogs.answer = false;
  $('#skipOnboarding').trigger('click');
  assert.deepEqual(dialogs.confirmations, ['Skip the setup?']);
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

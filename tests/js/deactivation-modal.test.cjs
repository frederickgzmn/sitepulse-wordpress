const test = require('node:test');
const assert = require('node:assert/strict');
const { browser } = require('./helpers/browser.cjs');

const localization = {
  SitePulseDeactivationData: {
    api_endpoint: 'https://feedback.sitepulse.test/deactivate',
    domain: 'HTTPS://sitepulse.test/', plugin_version: '1.2.3', wp_version: '6.8',
    rest_url: 'https://sitepulse.test/wp-json/', nonce: 'nonce-test',
    page_analysis_url: 'https://sitepulse.test/wp-admin/admin.php?page=wpsp_sitepulse_page_analysis&autorun=1',
    support_url: 'https://wordpress.org/support/plugin/sitepulse/',
    i18n: {
      title: 'Quick feedback', reason_confusing: 'No entendí cómo usarlo', prompt_confusing: 'What were you trying to do?',
      prompt_temporary: 'Anything we should know? (optional)', prompt_missing_feature: 'Which feature were you looking for?',
      pause_title: 'Only need a break?', pause_title_slow: 'Pausing removes overhead.', pause_text: 'Settings stay.', pause_button: 'Pause instead',
      pausing: 'Pausing…', pause_error: 'Could not pause.', paused_title: 'Monitoring paused', paused_text: 'Resume from the toolbar.', close: 'Close',
      guide_title: 'SitePulse in three steps:', guide_step_1: 'Dashboard', guide_step_2: 'Page Analysis', guide_step_3: 'Plugin Activity', guide_button: 'Analyze my homepage now',
      support_title: 'Sorry about that!', support_text: 'Tell us more.', support_button: 'Open the support forum', submitting: 'Submitting…'
    }
  }
};
async function setup(t, options = {}) {
  const page = await browser(t, {
    scripts: ['deactivation-modal.js'],
    html: '<a id="deactivate-sitepulse" href="#deactivated">Deactivate</a>',
    globals: localization,
    ...options
  });
  page.$('#deactivate-sitepulse').trigger('click');
  return page;
}

test('deactivation requires a reason and rating before feedback can be submitted', async t => {
  const { $, network, clock } = await setup(t);
  await clock.advance(10);
  assert.equal($('#sitepulse-deactivation-modal').hasClass('sp-deact-active'), true);
  assert.equal($('#sp-deact-submit').prop('disabled'), true);
  assert.equal($('input[name="sp_deact_reason"]').length, 8);
  $('input[value="temporary"]').prop('checked', true).trigger('change');
  assert.equal($('#sp-deact-submit').prop('disabled'), true);
  $('.sp-deact-star[data-rating="4"]').trigger('click');
  assert.equal($('#sp-deact-submit').prop('disabled'), false);
  assert.equal($('.sp-deact-star-active').length, 4);
  assert.equal(network.ajax.length, 0);
});

test('every reason opens an optional message box with a question that fits it', async t => {
  const { $ } = await setup(t);
  $('input[value="missing_feature"]').prop('checked', true).trigger('change');
  assert.notEqual($('.sp-deact-other-wrap').css('display'), 'none');
  assert.equal($('#sp-deact-other-message').attr('placeholder'), 'Which feature were you looking for?');
  $('#sp-deact-other-message').val('More detail').trigger('input');
  assert.equal($('#sp-deact-char-current').text(), '11');
  $('input[value="temporary"]').prop('checked', true).trigger('change');
  assert.notEqual($('.sp-deact-other-wrap').css('display'), 'none');
  assert.equal($('#sp-deact-other-message').attr('placeholder'), 'Anything we should know? (optional)');
  $('input[value="found_better"]').prop('checked', true).trigger('change');
  assert.equal($('#sp-deact-other-message').attr('placeholder'), 'prompt_found_better');
  assert.equal($('.sp-deact-help').attr('hidden'), 'hidden');
});

test('screen shows translated reasons while feedback keeps the stable English label', async t => {
  const { $, network } = await setup(t);
  assert.equal($('input[value="confusing"]').next().text(), 'No entendí cómo usarlo');
  assert.equal($('#sp-deact-title').text(), 'Quick feedback');
  $('input[value="confusing"]').prop('checked', true).trigger('change');
  $('.sp-deact-star[data-rating="2"]').trigger('click');
  $('#sp-deact-submit').trigger('click');
  assert.equal(JSON.parse(network.ajax[0].body).reason_label, "I couldn't understand how to use it");
  assert.equal($('#sp-deact-submit').text(), 'Submitting…');
});

test('confused users get a three-step guide and a one-click homepage analysis', async t => {
  const { $ } = await setup(t);
  $('input[value="confusing"]').prop('checked', true).trigger('change');
  assert.equal($('.sp-deact-help').attr('hidden'), undefined);
  assert.deepEqual($('.sp-deact-help li').map((i, el) => el.textContent).get(), ['Dashboard', 'Page Analysis', 'Plugin Activity']);
  assert.equal($('.sp-deact-help a').attr('href'), 'https://sitepulse.test/wp-admin/admin.php?page=wpsp_sitepulse_page_analysis&autorun=1');
  assert.equal($('#sp-deact-other-message').attr('placeholder'), 'What were you trying to do?');
});

test('a broken plugin report points to the support forum in a new tab', async t => {
  const { $ } = await setup(t);
  $('input[value="not_working"]').prop('checked', true).trigger('change');
  assert.equal($('.sp-deact-help strong').text(), 'Sorry about that!');
  assert.equal($('.sp-deact-help a').attr('href'), 'https://wordpress.org/support/plugin/sitepulse/');
  assert.equal($('.sp-deact-help a').attr('target'), '_blank');
});

for (const [reason, title] of [['temporary', 'Only need a break?'], ['too_slow', 'Pausing removes overhead.']]) {
  test(`${reason} deactivation can pause monitoring instead and keep the plugin active`, async t => {
    const { $, network, window, clock, flush } = await setup(t);
    $(`input[value="${reason}"]`).prop('checked', true).trigger('change');
    assert.equal($('.sp-deact-help strong').text(), title);
    $('.sp-deact-pause').trigger('click');
    assert.equal($('.sp-deact-pause').prop('disabled'), true);
    assert.equal($('.sp-deact-pause').text(), 'Pausing…');
    const call = network.ajax[0];
    assert.equal(call.url, 'https://sitepulse.test/wp-json/sitepulse/v1/monitoring/pause');
    assert.equal(call.headers['X-WP-Nonce'], 'nonce-test');
    assert.deepEqual(JSON.parse(call.body), { _wpnonce: 'nonce-test' });
    call.respond({ success: true, paused: true });
    await flush();
    assert.equal($('.sp-deact-paused strong').text(), 'Monitoring paused');
    assert.equal($('#sp-deact-submit').length, 0);
    $('.sp-deact-close').trigger('click');
    await clock.advance(300);
    assert.equal($('#sitepulse-deactivation-modal').length, 0);
    assert.equal(window.location.hash, '');
    assert.equal(window.document.activeElement.id, 'deactivate-sitepulse');
  });
}

test('a failed pause is explained and deactivation stays available', async t => {
  const { $, network, flush } = await setup(t);
  $('input[value="temporary"]').prop('checked', true).trigger('change');
  $('.sp-deact-pause').trigger('click');
  network.ajax[0].respond({ message: 'Forbidden' }, 403);
  await flush();
  assert.equal($('.sp-deact-pause').prop('disabled'), false);
  assert.equal($('.sp-deact-pause').text(), 'Pause instead');
  assert.equal($('.sp-deact-error').text(), 'Could not pause.');
  assert.equal($('#sp-deact-skip').length, 1);
});

test('reopening the dialog never duplicates its handlers', async t => {
  const { $, network, window, clock } = await setup(t);
  $(window.document).trigger($.Event('keydown', { key: 'Escape' }));
  await clock.advance(300);
  $('#deactivate-sitepulse').trigger('click');
  $('#deactivate-sitepulse').trigger('click');
  assert.equal($('#sitepulse-deactivation-modal').length, 1);
  $('input[value="other"]').prop('checked', true).trigger('change');
  $('.sp-deact-star[data-rating="5"]').trigger('click');
  $('#sp-deact-submit').trigger('click');
  assert.equal(network.ajax.length, 1);
});

test('stars can be rated from the keyboard', async t => {
  const { $ } = await setup(t);
  $('.sp-deact-star[data-rating="3"]').trigger($.Event('keydown', { key: 'Tab' }));
  assert.equal($('#sp-deact-rating').val(), '0');
  $('.sp-deact-star[data-rating="3"]').trigger($.Event('keydown', { key: 'Enter' }));
  assert.equal($('#sp-deact-rating').val(), '3');
  assert.equal($('.sp-deact-star[data-rating="3"]').attr('aria-pressed'), 'true');
  assert.equal($('.sp-deact-star[data-rating="4"]').attr('aria-pressed'), 'false');
  $('.sp-deact-star[data-rating="1"]').trigger($.Event('keydown', { key: ' ' }));
  assert.equal($('#sp-deact-rating').val(), '1');
});

for (const status of [200, 503]) {
  test(`feedback strips the domain scheme, limits the message, and deactivates after HTTP ${status}`, async t => {
    const { $, network, window, flush } = await setup(t);
    $('input[value="other"]').prop('checked', true).trigger('change');
    $('#sp-deact-other-message').val('x'.repeat(1005));
    $('.sp-deact-star[data-rating="3"]').trigger('click');
    $('#sp-deact-submit').trigger('click');
    assert.equal(network.ajax.length, 1);
    const call = network.ajax[0];
    assert.equal(call.url, 'https://feedback.sitepulse.test/deactivate');
    assert.equal(call.method, 'POST');
    assert.deepEqual(JSON.parse(call.body), {
      domain: 'sitepulse.test', reason_code: 'other', reason_label: 'Other',
      message: 'x'.repeat(1000), rating: 3, plugin_version: '1.2.3', wp_version: '6.8'
    });
    assert.equal($('#sp-deact-submit').prop('disabled'), true);
    assert.equal($('#sp-deact-skip').prop('disabled'), true);
    assert.equal(window.location.hash, '');
    call.respond({ success: status === 200 }, status);
    await flush();
    assert.equal(window.location.hash, '#deactivated');
  });
}

test('skip deactivates without sending feedback', async t => {
  const { $, network, window } = await setup(t);
  $('#sp-deact-skip').trigger('click');
  assert.equal(network.ajax.length, 0);
  assert.equal(window.location.hash, '#deactivated');
});

for (const close of ['Escape', 'backdrop']) {
  test(`${close} dismisses feedback without deactivating`, async t => {
    const { $, window, clock } = await setup(t);
    $('.sp-deact-header').trigger('click');
    assert.equal($('#sitepulse-deactivation-modal').length, 1);
    if (close === 'Escape') $(window.document).trigger($.Event('keydown', { key: 'Escape' }));
    else $('#sitepulse-deactivation-modal').trigger('click');
    await clock.advance(300);
    assert.equal($('#sitepulse-deactivation-modal').length, 0);
    assert.equal(window.location.hash, '');
  });
}

test('hover previews stars without changing the saved rating', async t => {
  const { $ } = await setup(t);
  $('.sp-deact-star[data-rating="2"]').trigger('click');
  $('.sp-deact-star[data-rating="5"]').trigger('mouseenter');
  assert.equal($('.sp-deact-star-hover').length, 5);
  assert.equal($('#sp-deact-rating').val(), '2');
  $('.sp-deact-stars').trigger('mouseleave');
  assert.equal($('.sp-deact-star-hover').length, 0);
  assert.equal($('.sp-deact-star-active').length, 2);
});

test('feedback remains inactive outside the localized plugins page', async t => {
  const { $, network } = await setup(t, { globals: {} });
  assert.equal($('#sitepulse-deactivation-modal').length, 0);
  assert.equal(network.ajax.length, 0);
});

test('fallback plugin links are intercepted while unrelated plugin links are left alone', async t => {
  const { $ } = await setup(t, { html: '<a class="target" href="?plugin=sitepulse%2Floader.php&action=deactivate">Deactivate</a><a class="unrelated" href="#other">Other</a>' });
  $('.unrelated').trigger('click');
  assert.equal($('#sitepulse-deactivation-modal').length, 0);
  $('.target').trigger('click');
  assert.equal($('#sitepulse-deactivation-modal').length, 1);
});

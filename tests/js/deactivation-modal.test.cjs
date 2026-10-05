const test = require('node:test');
const assert = require('node:assert/strict');
const { browser } = require('./helpers/browser.cjs');

const localization = {
  SitePulseDeactivationData: {
    api_endpoint: 'https://feedback.sitepulse.test/deactivate',
    domain: 'HTTPS://sitepulse.test/', plugin_version: '1.2.3', wp_version: '6.8'
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

test('contextual feedback expands for issues, counts characters, and collapses for temporary deactivation', async t => {
  const { $ } = await setup(t);
  $('input[value="missing_feature"]').prop('checked', true).trigger('change');
  assert.notEqual($('.sp-deact-other-wrap').css('display'), 'none');
  assert.match($('#sp-deact-other-message').attr('placeholder'), /feature/i);
  $('#sp-deact-other-message').val('More detail').trigger('input');
  assert.equal($('#sp-deact-char-current').text(), '11');
  $('input[value="temporary"]').prop('checked', true).trigger('change');
  assert.equal($('.sp-deact-other-wrap').css('display'), 'none');
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

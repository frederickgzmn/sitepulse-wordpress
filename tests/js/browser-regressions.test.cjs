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

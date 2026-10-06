const test = require('node:test');
const assert = require('node:assert/strict');
const { browser } = require('./helpers/browser.cjs');

test('controlled intervals allow async callbacks to await independent browser timeouts', async t => {
  const { window, clock } = await browser(t);
  const started = [];
  const completed = [];
  const beginning = window.Date.now();
  const interval = window.setInterval(async () => {
    started.push(window.Date.now() - beginning);
    await new Promise(resolve => window.setTimeout(resolve, 5));
    completed.push(window.Date.now() - beginning);
  }, 10);
  await clock.advance(20);
  assert.deepEqual(started, [10, 20]);
  assert.deepEqual(completed, [15]);
  await clock.advance(5);
  assert.deepEqual(completed, [15, 25]);
  window.clearInterval(interval);
  assert.deepEqual(clock.pendingIntervals(), []);
});

const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse } = require('./helpers/browser.cjs');

// The connection box as the remote management settings render it while they show a key.
const box = ({ poll = true } = {}) => `
  <div id="sitepulse-remote-connection"${poll ? ' data-poll-url="https://sitepulse.test/wp-admin/admin-ajax.php" data-poll-action="sitepulse_remote_connection_poll" data-poll-nonce="nonce-poll"' : ''}>
    <input type="text" id="sitepulse-connection-key" readonly value="spc_test_key">
    <button type="button" data-copy="sitepulse-connection-key" data-copied="Copied">Copy</button>
    <p data-poll-status data-done="Your SitePulse dashboard used the key. Updating…">Waiting for your SitePulse dashboard.</p>
  </div>`;

async function settings(t, options = {}) {
  const copies = [];
  const page = await browser(t, {
    html: options.html === undefined ? box(options) : options.html,
    scripts: ['remote-connection.js'],
    fetch: options.fetch,
    prepare(window) {
      window.document.execCommand = command => { copies.push(command); return options.execCommand !== false; };
      if (options.clipboard) Object.defineProperty(window.navigator, 'clipboard', { value: options.clipboard, configurable: true });
    }
  });
  return { ...page, copies };
}

test('the copy button copies the key with the clipboard and says so', async t => {
  const written = [];
  const { $, flush, copies } = await settings(t, { poll: false, clipboard: { writeText: value => { written.push(value); return Promise.resolve(); } } });
  $('[data-copy]').trigger('click');
  await flush();
  assert.deepEqual(written, ['spc_test_key']);
  assert.deepEqual(copies, []);
  assert.equal($('[data-copy]').text(), 'Copied');
});

test('the copy button selects and copies the key when the clipboard refuses or is missing', async t => {
  const refused = await settings(t, { poll: false, clipboard: { writeText: () => Promise.reject(new Error('denied')) } });
  refused.$('[data-copy]').trigger('click');
  await refused.flush();
  assert.deepEqual(refused.copies, ['copy']);
  assert.equal(refused.$('[data-copy]').text(), 'Copied');

  const missing = await settings(t, { poll: false, execCommand: false });
  missing.$('[data-copy]').trigger('click');
  assert.deepEqual(missing.copies, ['copy']);
  assert.equal(missing.$('[data-copy]').text(), 'Copy', 'The key stays selected for copying by hand.');
});

test('the page asks every few seconds whether the key was used, and reloads once it was', async t => {
  let used = false;
  const { $, clock, network, navigation } = await settings(t, { fetch: () => jsonResponse({ success: true, data: { used } }) });
  assert.equal(network.fetch.length, 0);

  await clock.advance(5000);
  assert.equal(network.fetch.length, 1);
  const request = network.fetch[0];
  assert.equal(request.url, 'https://sitepulse.test/wp-admin/admin-ajax.php');
  assert.equal(request.method, 'POST');
  assert.equal(request.credentials, 'same-origin');
  assert.equal(request.body.toString(), 'action=sitepulse_remote_connection_poll&nonce=nonce-poll');

  await clock.advance(5000);
  assert.equal(network.fetch.length, 2);

  used = true;
  navigation.expected = 1;
  await clock.advance(5000);
  assert.equal($('[data-poll-status]').text(), 'Your SitePulse dashboard used the key. Updating…');
  assert.equal(navigation.attempts, 1);
  await clock.advance(60000);
  assert.equal(network.fetch.length, 3, 'Nothing is asked after the reload.');
});

test('a failed request is asked again, a refused one is not, and the page stops asking after 15 minutes', async t => {
  let answer = () => Promise.reject(new TypeError('offline'));
  const { clock, network } = await settings(t, { fetch: () => answer() });
  await clock.advance(5000);
  await clock.advance(5000);
  assert.equal(network.fetch.length, 2, 'Asked again after a network error.');

  answer = () => jsonResponse({ success: false, data: null }, 403);
  await clock.advance(5000);
  await clock.advance(60000);
  assert.equal(network.fetch.length, 3, 'A refused request is not repeated.');

  const quiet = await settings(t, { fetch: () => jsonResponse({ success: true, data: { used: false } }) });
  await quiet.clock.advance(15 * 60000);
  const asked = quiet.network.fetch.length;
  assert.ok(asked >= 170 && asked <= 180, `asked ${asked} times`);
  await quiet.clock.advance(60000);
  assert.equal(quiet.network.fetch.length, asked, 'No more questions after 15 minutes.');

  const empty = await settings(t, { fetch: () => jsonResponse(null) });
  await empty.clock.advance(5000);
  await empty.clock.advance(5000);
  assert.equal(empty.network.fetch.length, 1, 'An empty answer stops it too.');
});

test('the script does nothing without the connection box or a key', async t => {
  const { network, clock, copies } = await settings(t, { html: '<p>Other settings</p>' });
  await clock.advance(10000);
  assert.equal(network.fetch.length, 0);
  assert.deepEqual(copies, []);

  const plain = await settings(t, { html: '<div id="sitepulse-remote-connection"><p>Connected.</p></div>' });
  await plain.clock.advance(10000);
  assert.equal(plain.network.fetch.length, 0);
});

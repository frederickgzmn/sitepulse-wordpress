const test = require('node:test');
const assert = require('node:assert/strict');
const { browser, jsonResponse, flush } = require('./helpers/browser.cjs');

// The wp-admin row as the Advanced dashboard renders it, showing a reachable wp-admin.
const row = `
  <div id="sitepulse-wp-admin" data-sitepulse-admin-access data-url="https://sitepulse.test/wp-admin/admin-ajax.php" data-action="sitepulse_admin_access_check" data-nonce="nonce-check">
    <span class="badge bg-success" data-admin-access-badge data-success="badge bg-success" data-danger="badge bg-danger" data-neutral="badge bg-secondary">Reachable</span>
    <div data-admin-access-detail>HTTP 302 → login · 184 ms · checked 5 mins ago</div>
    <div data-admin-access-message hidden></div>
    <div data-admin-access-note hidden></div>
    <div data-admin-access-error hidden></div>
    <button type="button" data-admin-access-check data-checking="Checking…" data-failed="The check could not run. Reload the page and try again.">Check now</button>
  </div>`;

const FAILED = 'The check could not run. Reload the page and try again.';

// Response bodies are read asynchronously; let every step of the request settle.
const settle = async () => { for (let i = 0; i < 5; i++) await flush(); };

async function page(t, fetch, html = row) {
  const loaded = await browser(t, { html, scripts: ['admin-access.js'], fetch });
  const find = selector => loaded.window.document.querySelector(selector);
  const line = name => { const element = find(`[data-admin-access-${name}]`); return element.hidden ? null : element.textContent; };
  return { ...loaded, find, line, click: () => find('[data-admin-access-check]').click() };
}

test('check now asks WordPress to check again and shows the new result in place', async t => {
  let answer;
  const { network, find, line, click } = await page(t, () => new Promise(resolve => { answer = resolve; }));
  const button = find('[data-admin-access-check]');

  click();
  assert.equal(button.disabled, true);
  assert.equal(button.textContent, 'Checking…');
  assert.equal(network.fetch.length, 1);
  const request = network.fetch[0];
  assert.equal(request.url, 'https://sitepulse.test/wp-admin/admin-ajax.php');
  assert.equal(request.method, 'POST');
  assert.equal(request.credentials, 'same-origin');
  assert.equal(request.body.toString(), 'action=sitepulse_admin_access_check&nonce=nonce-check');

  answer(jsonResponse({ success: true, data: { label: 'Not reachable', tone: 'danger', detail: 'A PHP fatal error stopped wp-admin or its login page.', message: 'Uncaught Error: boom', note: 'Checked 1 min ago. Was reachable until 2 hours ago.' } }));
  await settle();
  const badge = find('[data-admin-access-badge]');
  assert.equal(badge.className, 'badge bg-danger');
  assert.equal(badge.textContent, 'Not reachable');
  assert.equal(line('detail'), 'A PHP fatal error stopped wp-admin or its login page.');
  assert.equal(line('message'), 'Uncaught Error: boom');
  assert.equal(line('note'), 'Checked 1 min ago. Was reachable until 2 hours ago.');
  assert.equal(line('error'), null);
  assert.equal(button.disabled, false);
  assert.equal(button.textContent, 'Check now');

  click();
  answer(jsonResponse({ success: true, data: { label: 'Reachable', tone: 'success', detail: 'HTTP 302 → login · 90 ms · checked 1 min ago', message: '', note: '' } }));
  await settle();
  assert.equal(badge.className, 'badge bg-success');
  assert.equal(line('detail'), 'HTTP 302 → login · 90 ms · checked 1 min ago');
  assert.equal(line('message'), null, 'Empty lines are hidden.');
  assert.equal(line('note'), null);
});

test('a refused check says why, or that it could not run, and keeps the result shown', async t => {
  let reply = () => jsonResponse({ success: false, data: { message: 'A check is already running. Try again in a moment.' } }, 409);
  const { find, line, click } = await page(t, () => reply());

  click();
  await settle();
  assert.equal(line('error'), 'A check is already running. Try again in a moment.');
  assert.equal(find('[data-admin-access-badge]').textContent, 'Reachable');
  assert.equal(line('detail'), 'HTTP 302 → login · 184 ms · checked 5 mins ago');
  assert.equal(find('[data-admin-access-check]').disabled, false);

  // An expired session or nonce: WordPress answers without a reason.
  for (const refusal of [() => jsonResponse({ success: false, data: null }, 403), () => new Response('-1', { status: 403 }), () => new Response('0', { status: 400 })]) {
    reply = refusal;
    click();
    assert.equal(line('error'), null, 'The previous error goes away while it checks.');
    await settle();
    assert.equal(line('error'), FAILED);
  }
});

test('a network failure or a page that is not an answer says the check could not run', async t => {
  let reply = () => Promise.reject(new TypeError('offline'));
  const { find, line, click } = await page(t, () => reply());

  click();
  await settle();
  assert.equal(line('error'), FAILED);
  assert.equal(find('[data-admin-access-check]').textContent, 'Check now');

  reply = () => new Response('<html>Error</html>', { status: 500 });
  click();
  await settle();
  assert.equal(line('error'), FAILED);
});

test('the script does nothing on pages without the row', async t => {
  const { network } = await page(t, undefined, '<p>Other settings</p>');
  assert.equal(network.fetch.length, 0);
});

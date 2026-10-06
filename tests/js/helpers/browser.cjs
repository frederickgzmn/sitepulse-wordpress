const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { JSDOM, VirtualConsole } = require('jsdom');
const jquery = require('jquery');
const coverage = process.env.SITEPULSE_COVERAGE_FILE ? require('./coverage.cjs') : null;

const flush = () => new Promise(resolve => setImmediate(resolve));
const jsonResponse = (data, status = 200) => new Response(JSON.stringify(data), {
  status,
  headers: { 'Content-Type': 'application/json' }
});

// Real jQuery, DOM events and storage run in a fresh realm for every test. Time
// and I/O are controlled; tests explicitly adapt missing jsdom platform APIs.
// Production scripts execute unchanged, or with coverage counters when enabled.
async function browser(t, { html = '', scripts = [], globals = {}, storage = {}, fetch: fetchHandler, prepare } = {}) {
  const diagnostics = [];
  const navigation = { expected: 0, attempts: 0 };
  const virtualConsole = new VirtualConsole();
  virtualConsole.on('jsdomError', error => {
    if (error.type === 'not implemented' && error.message.startsWith('Not implemented: navigation')) {
      navigation.attempts++;
    } else {
      diagnostics.push(error);
    }
  });
  const dom = new JSDOM(`<!doctype html><html><body>${html}</body></html>`, {
    url: 'https://sitepulse.test/wp-admin/admin.php',
    runScripts: 'outside-only',
    pretendToBeVisual: true,
    virtualConsole
  });
  t.after(() => {
    if (coverage) coverage.merge(dom.window.__coverage__);
    dom.window.close();
    assert.deepEqual(diagnostics, [], 'unexpected browser errors');
    assert.equal(navigation.attempts, navigation.expected, 'unexpected browser navigation');
  });
  const window = dom.window;
  const $ = jquery(window);
  window.$ = window.jQuery = $;
  $.fx.off = true;
  await new Promise(resolve => $(resolve));

  let now = 1700000000000;
  let nextId = 1;
  const timers = new Map();
  const frames = new Map();
  const addTimer = (callback, delay, interval, args) => {
    const id = nextId++;
    timers.set(id, { callback, due: now + Number(delay || 0), interval, args });
    return id;
  };
  window.setTimeout = (callback, delay, ...args) => addTimer(callback, delay, 0, args);
  window.setInterval = (callback, delay, ...args) => addTimer(callback, delay, Number(delay), args);
  window.clearTimeout = window.clearInterval = id => timers.delete(id);
  window.requestAnimationFrame = callback => { const id = nextId++; frames.set(id, callback); return id; };
  window.cancelAnimationFrame = id => frames.delete(id);
  window.Date.now = () => now;
  window.performance.now = () => now - 1700000000000;
  window.Math.random = () => 0.99;

  const logs = { error: [], warn: [] };
  window.console.error = (...args) => logs.error.push(args);
  window.console.warn = (...args) => logs.warn.push(args);
  const dialogs = { confirmations: [], alerts: [], answer: true };
  window.confirm = message => { dialogs.confirmations.push(message); return dialogs.answer; };
  window.alert = message => dialogs.alerts.push(message);

  const network = { fetch: [], ajax: [] };
  let reply = fetchHandler || (() => jsonResponse({ success: true }));
  window.fetch = async (url, options) => {
    const call = { url, ...options };
    network.fetch.push(call);
    return reply(call, network.fetch.length - 1);
  };
  // The real $.ajax builds requests, runs beforeSend, parses JSON and settles its
  // real Deferred. This transport replaces only the server connection.
  $.ajaxTransport('+*', options => ({
    send(headers, complete) {
      network.ajax.push({
        url: options.url,
        method: options.type,
        body: options.data,
        headers,
        respond(data = { success: true }, status = 200) {
          complete(status, status >= 400 ? 'error' : 'OK', { text: JSON.stringify(data) }, 'Content-Type: application/json');
        }
      });
    },
    abort() {}
  }));

  window.SitePulse = {
    rest_url: 'https://sitepulse.test/wp-json/', nonce: 'nonce-test',
    post_load_events: {}, post_load_events_count: 0,
    cron_confirm_message: 'Disable cron?', email_blocking_confirm_message: 'Block email?'
  };
  Object.assign(window, globals);
  for (const [key, value] of Object.entries(storage)) window.localStorage.setItem(key, value);

  const clock = {
    async advance(ms) {
      const target = now + ms;
      let iterations = 0;
      while (true) {
        await flush();
        const pending = [...timers].filter(([, timer]) => timer.due <= target).sort((a, b) => a[1].due - b[1].due)[0];
        if (!pending) break;
        if (++iterations > 10000) throw new Error('Timer loop did not settle');
        const [id, timer] = pending;
        now = timer.due;
        if (timer.interval) timer.due += timer.interval;
        else timers.delete(id);
        // Browser timers do not await returned promises. Awaiting here would
        // serialize intervals and deadlock callbacks that await another timer.
        const result = timer.callback(...timer.args);
        if (result && typeof result.then === 'function') {
          Promise.resolve(result).catch(error => diagnostics.push(error));
        }
      }
      now = target;
      await flush();
    },
    frame() {
      const pending = [...frames.values()];
      frames.clear();
      for (const callback of pending) callback(now);
    },
    pendingIntervals() { return [...timers.values()].filter(timer => timer.interval).map(timer => timer.interval); }
  };
  if (prepare) await prepare(window, $);
  for (const script of scripts) {
    const filename = path.resolve(__dirname, '../../../assets/js', script);
    const source = coverage ? coverage.source(filename) : fs.readFileSync(filename, 'utf8');
    window.eval(`${source}\n//# sourceURL=${filename}`);
  }
  await clock.advance(0);
  return { window, $, clock, network, dialogs, logs, diagnostics, navigation, flush, setFetch(handler) { reply = handler; } };
}

module.exports = { browser, jsonResponse, flush };

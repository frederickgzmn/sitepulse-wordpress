# Automated tests

Install development dependencies from the plugin directory:

```sh
composer install
npm ci
```

The PHP suite uses PHPUnit 9.6 and supports the plugin's PHP 7.4 minimum. PHP needs the usual PHPUnit extensions, including DOM, mbstring, XML, and XMLWriter. The JavaScript suite requires Node.js 22.8 or newer. Lockfiles make dependency installation reproducible. These dependencies are development tools; the plugin still ships prebuilt assets without a build step.

## Run

```sh
composer test
npm test
```

Defects found while adding the suite have been repaired, and their expected-correct regressions run in these regular suites. See [regression fixes](KNOWN_DEFECTS.md) for the behavior protected by those tests. There is no separate intentionally failing suite.

Run a focused test while developing:

```sh
vendor/bin/phpunit --filter SettingsTest
node --test tests/js/frontend.test.cjs
```

GitHub Actions runs the suites for pushes and pull requests, using PHP 7.4, 8.1, 8.3, and 8.5 and Node.js 22 and 24. PHP syntax checks also cover templates and test tooling. A separate PHP 8.3/Xdebug and Node.js 24 job requires exactly 100% executable-line coverage in each language and uploads the reports.

## PHP coverage

The PHP suite executes the production classes, functions, loader, uninstall script, and templates. `php/Support/WordPress.php` supplies in-memory WordPress options, transients, hooks, permissions, REST objects, and HTTP boundaries. Unexpected outbound HTTP requests throw. The tests do not load a WordPress installation or access a live database. Configuration writes use process-specific temporary WordPress directories that are removed on exit. Isolated processes exercise early exits, optional dependencies, native shutdown handlers, and alternate constants without replacing plugin classes. Their real Xdebug hits are merged into the parent report, including branch/path data when enabled.

| Area | Behaviors exercised |
| --- | --- |
| Loader and uninstall | Active-plugin ordering, Pro guard, activation flags, deactivation cron cleanup, notices, selective option/user-meta cleanup |
| Settings and setup | Defaults, validation, persistence, hook refresh, email/cron controls, overdue cron detection, option prefixes, body classes |
| Onboarding and Easy Mode | Completion, reset, redirect gates, preference persistence, permissions, AJAX validation |
| Utilities and branding | Unit formatting, sensitive URL parameters, paths, branding sanitization and fallbacks |
| Analytics | Event allowlist, property sanitization, payloads, permission/nonce checks |
| Profilers and tracking | Enablement, source classification, aggregation, thresholds, event storage, page tracking and cleanup |
| REST | Registered routes, permissions, nonces, malformed requests, settings/flags, statistics, API responses and autoload changes |
| API and cron | Report payloads, response errors, licenses, caching, diagnostic states, scheduling, locks and fallback behavior |
| Plugin diagnostics | Slow-query filtering/sorting, memory reporting, temporary config backups and write failures, disk benchmark cleanup |
| Presentation | Dashboard calculation thresholds, localized event sorting, escaping, metric cards and score rings |
| Error handling | Warning capture, retention, log recovery, recovery-email validation, escaped display and notification filtering |

Install and enable Xdebug 3, then run the line-coverage gate:

```sh
XDEBUG_MODE=coverage composer test:coverage
```

Reports are written to `coverage/php/index.html` and `coverage/php.xml` (Clover). The command fails if the driver is absent or disabled, the suite fails, the report is missing/invalid, any production source file is omitted, or even one executable line is uncovered. It compares integer counters, not rounded percentages, and removes the previous Clover report before running. The scope is every PHP file under `class/`, `inc/`, and `templates/`, plus `loader.php` and `uninstall.php`. Tests, boundary doubles, tooling, and dependencies are outside that production scope; no production file is excluded.

The console prints executable-line and fully covered class-method counters separately; the HTML report includes standalone functions as well. PHPUnit counts a method/function as fully covered when all its lines are covered in line mode, or all its branches in branch mode. To collect actual Xdebug branch/path information as well:

```sh
XDEBUG_MODE=coverage composer test:coverage:branches
```

This produces `coverage/php-branches/index.html` and `coverage/php-branches.xml`. It applies the same line gate; branch and method/function percentages are reported without a 100% threshold. Path collection uses substantially more memory than line collection. Configure a suitable PHP `memory_limit` in your CLI configuration if needed; the runner and isolated fixtures preserve that setting. A resource failure exits nonzero and does not count as a report. Without path collection, branch coverage is explicitly reported as uncollected, not as 100%.

Normal `composer test` does not require a coverage extension. Xdebug is required for the complete coverage command because the isolated fixtures also collect its raw execution data. Six deterministic DNS fallback cases run on PHP 8+ only: they disable and replace external DNS/machine-name functions inside a child process, which PHP 7.4 cannot do. They assert lookup arguments and precedence without querying a real resolver. CI measures complete coverage on PHP 8.3 while retaining the PHP 7.4 compatibility run.

## JavaScript coverage

The Node test runner loads each original script into a fresh jsdom window with real jQuery. The shared browser helper controls time, fetch responses, and jQuery's network transport. Tests interact with DOM events and assert rendered content, request payloads, state changes, loading states, and error recovery.

All six first-party browser scripts have dedicated tests: shared alerts/confirmations/notices, backend controls, frontend tracking, onboarding, deactivation feedback, and Easy Mode. Each test closes its window so event handlers, timers, and storage cannot leak to the next test. Bundled Bootstrap is an upstream dependency and is not retested here.

```sh
npm run test:coverage
```

Istanbul instruments the original `assets/js/*.js` scripts and includes all six files even if a test stops loading one. This command requires exactly 100% executable lines and also prints statement, function, and branch coverage separately. Reports are written to `coverage/js/index.html`, `coverage/js/lcov.info`, `coverage/js/coverage-final.json`, and `coverage/js/coverage-summary.json`. Use `npm run coverage:js` to generate the same reports without enforcing the line threshold.

The npm scripts use Node's `--experimental-test-isolation=none` option. Test files share the runner process, but each test gets its own browser window. Controlled timers and deferred responses exercise asynchronous scheduling and pending requests; jsdom is not a complete browser event loop.

## Limits and maintenance

100% executable-line coverage means each instrumented production line was executed; it does not imply every input combination, branch, function path, or integration was validated. The doubles intentionally implement only the WordPress contracts used by the suite. Real REST dispatch, MySQL queries, WordPress mail delivery, Action Scheduler integration, browser layout, and real navigation still require runtime checks. PHP template rendering is exercised with controlled WordPress data, while CSS and actual browser rendering are outside this suite. Use `blueprint.json` in WordPress Playground for a disposable site; it is not executed by these test commands.

When extending the suite, use literal or hand-checked expectations, test both successful and rejected inputs, and mock only external boundaries. Never load the local site's `wp-config.php` or send real API requests from a test. Add any new WordPress double to the shared support file and preserve its real WordPress semantics. Use separate PHP processes when a function-local static or constant cannot be reset.

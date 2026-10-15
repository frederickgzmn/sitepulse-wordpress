# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

SitePulse is a WordPress performance-monitoring plugin (hook/plugin profiling, outbound HTTP timing, fatal-error detection, cron management, Page Analysis). There is no build step; JS/CSS in `assets/` ship as-is. See `AGENTS.md` for the repo guidelines (also applicable here).

## Commands

- Setup: `composer install` and `npm ci` (Node >= 22.8, PHP platform pinned to 7.4.33 — keep code PHP 7.4 compatible).
- PHP tests: `composer test` (PHPUnit 9.6, `phpunit.xml.dist`). Single file: `vendor/bin/phpunit tests/php/ApiServiceTest.php`; single test: `vendor/bin/phpunit --filter testName`.
- JS tests: `npm test` (Node test runner + jsdom + real jQuery). Single file: `node --test --experimental-test-isolation=none tests/js/<file>.test.cjs`.
- Coverage (CI gate is **100% executable-line coverage** of all first-party PHP, including templates, `loader.php`, `uninstall.php`, and all JS): `XDEBUG_MODE=coverage composer test:coverage` (needs Xdebug 3; `test:coverage:branches` for path coverage) and `npm run test:coverage`. New production code needs tests that cover every line.
- Runtime validation: load `blueprint.json` in WordPress Playground; the test suites do not replace a real WordPress check.
- Release version: `composer bump-version` raises the patch number (1.4.6 → 1.4.7); pass `-- minor`, `-- major` or `-- X.Y.Z` for others and `-- --dry-run` to preview. It updates the `loader.php` header and `SITEPULSE_VERSION`, the `readme.txt` Stable tag and the `.pot` header, and warns when `readme.txt` has no changelog section for the new version. Test fixtures pin their own version and are not touched.
- Note: `AGENTS.md` mentions `tests/README.md`, which does not exist.

## Architecture

- `loader.php` is the bootstrap. It defines constants (paths, option keys, per-feature slugs like `SITEPULSE_PROFILER_*`, `SITEPULSE_CURL_API_*`), loads `class/error_handler.php` **first** (so load-time errors are caught), then the rest of `class/` and `inc/`, and instantiates `Sitepulse_Loader`. Its constructor runs `Sitepulse_Page_Tracker::init()` before the profilers (Page Analysis requests switch profilers into single-request mode), then `Sitepulse_Profiler`, `Sitepulse_CurLoader` (outbound HTTP/cURL timing), `Sitepulse_Plugin_Profiler`. `init()` fires `sitepulse_before_init` / `sitepulse_after_init` hooks for a pro/extension plugin, builds `Sitepulse_Plugin`, onboarding, monitoring (toolbar pause/resume) and Easy Mode. `SCHEMA_VERSION` plus `maybe_upgrade()` (on `admin_init`) migrates stored data — bump the version when an update needs to migrate it.
- `class/plugin.php` (`Sitepulse_Plugin`) wires the request lifecycle to `class/backend.php` (admin UI, menus) and `class/frontend.php` (admin-bar widget). `class/easy_mode.php` provides the Simple vs Advanced dashboard (per-user preference). `class/page_analysis.php` handles per-page analysis.
- `class/api_service.php` and `inc/api_backend.php` handle the external SitePulse API (registration/licensing, reports, vulnerability scans, AI diagnostics) and REST endpoints; `inc/cron_manager.php`, `inc/cron_fallback.php` (non-blocking fallback runner for stale cron) and `inc/ai_diagnostic_cron.php` schedule the background work. `class/whitelabel_service.php` handles branding.
- Warnings, rankings, scores and API/AI payloads describe only the last 24 hours: `Sitepulse_Profiler::get_recent_stats()` (rows ranked by `load_ms`, a callback's own time per page load) and `Sitepulse_CurLoader::get_recent_events()`. Never rank by the stored lifetime totals (`total`, `total_ms`, `calls`) — they grow with traffic and keep blaming code that has since changed. Stored profiler rows carry `window_*`/`previous_*` counters for two 24-hour windows, and `persist()` drops rows idle for 48 hours.
- Remote management (`class/remote_*.php`) lets the SitePulse dashboard see, and later manage, backups (UpdraftPlus 1.26+/BackWPup 5) and updates. `Sitepulse_Remote_Management` runs the `sitepulse_remote_check_in` cron, commands, event queue, activity log and the local block switch (`remote_management_blocked` setting); `Sitepulse_Remote_Client` signs requests with the license key and accepts only responses signed for that request; `Sitepulse_Remote_Inventory`, `Sitepulse_Remote_Backups` and `Sitepulse_Remote_Events` collect what is reported. `docs/remote-management.md` is the contract with the API repo (laravel-sitepulse-api keeps the same copy): change both together.
- Views live in `templates/` (PHP); tests cover them directly (`DiagnosticTemplateCoverageTest`, `DirectAccessTemplateTest`).
- Classes use the `Sitepulse_` prefix; files are lowercase_underscore (`api_service.php`). Collected metrics are stored in WordPress options/transients keyed by the constants in `loader.php`.

## Testing notes

- PHP tests (`tests/php/`, bootstrap in `tests/php/bootstrap.php`) run real production code against isolated WordPress boundary doubles and a disposable filesystem; JS tests (`tests/js/*.test.cjs`) run the shipped scripts in jsdom. Keep test-only helpers out of production code and cover failure paths.
- Remote-management tests use `tests/php/Support/RemoteManagement.php`: API responses signed for the request they answer, and UpdraftPlus/BackWPup stand-ins configured through `Sitepulse_Test_Backups`.
- Fixed defects are recorded in `docs/KNOWN_DEFECTS.md` with their regression tests (they live in the normal suites).

## Repo workflow

- **Never commit or push** in this repo; leave all changes uncommitted.
- A `graphify-out/` knowledge graph exists: for codebase questions run `graphify query "<question>"` first (also `graphify path`, `graphify explain`), and run `graphify update .` after modifying code.

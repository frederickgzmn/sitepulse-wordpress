# Repository Guidelines

## Project Structure & Module Organization

`loader.php` is the WordPress plugin bootstrap: it defines shared paths and settings, loads plugin classes and `inc/` helpers, and registers activation and deactivation hooks. `Sitepulse_Plugin` in `class/plugin.php` connects the request lifecycle to the admin handler in `class/backend.php` and the public handler in `class/frontend.php`. Keep reusable plugin components in `class/`, REST and cron helpers in `inc/`, PHP views in `templates/`, browser assets in `assets/`, and translations in `languages/`. `blueprint.json` configures the disposable WordPress Playground demo.

## Build, Test, and Development Commands

There is no build step; the browser assets ship pre-built. Install development dependencies with `composer install` and `npm ci`. Run PHP tests with `composer test` and JavaScript tests with `npm test`. See `tests/README.md` for focused runs, coverage, and harness boundaries. For a manual runtime check, load `blueprint.json` in WordPress Playground and use its demo WordPress site.

## Coding Style & Naming Conventions

No formatter or linter enforces a style. Existing PHP components use lowercase, underscore-separated filenames (for example, `api_service.php`), and class names use the `Sitepulse_` prefix. Follow these established names when adding a component.

## Testing Guidelines

PHPUnit tests live in `tests/php/`; Node test-runner tests live in `tests/js/`. PHP tests execute production code with isolated WordPress boundary doubles and a disposable filesystem. JavaScript tests execute the shipped scripts with jsdom and real jQuery. Keep test-only helpers out of production code, cover observable behavior and failure paths, and run both suites before submitting changes. These suites do not replace a real WordPress integration check; use the Playground blueprint when a change needs runtime validation.

CI enforces 100% executable-line coverage for all first-party PHP (including templates, loader, and uninstall) and JavaScript. Run `XDEBUG_MODE=coverage composer test:coverage` with Xdebug 3 and `npm run test:coverage`. Preserve the complete production scope; branch and method/function metrics are separate from the line gate.

## Commit & Pull Request Guidelines

Recent history contains short descriptive subjects and GitHub merge subjects; no consistent prefix convention is evident. No pull request template is present. Keep commit subjects descriptive and concise so they match the existing history.

## graphify

This project has a knowledge graph in `graphify-out/`. When `/graphify` is invoked, follow the installed graphify skill.

- For codebase questions, run `graphify query "<question>"` first when `graphify-out/graph.json` exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for a focused concept.
- Dirty `graphify-out/` files can result from updates; use the graph unless the question concerns stale graph output or the user declines it.
- Use `graphify-out/wiki/index.md` for broad navigation when present. Read `graphify-out/GRAPH_REPORT.md` for broad architecture or when query, path, and explain lack context.
- After modifying code, run `graphify update .` to refresh the graph.

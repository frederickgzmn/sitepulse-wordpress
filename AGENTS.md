# Repository Guidelines

## Project Structure & Module Organization

`loader.php` is the WordPress plugin bootstrap: it defines shared paths and settings, loads plugin classes and `inc/` helpers, and registers activation and deactivation hooks. `Sitepulse_Plugin` in `class/plugin.php` connects the request lifecycle to the admin handler in `class/backend.php` and the public handler in `class/frontend.php`. Keep reusable plugin components in `class/`, REST and cron helpers in `inc/`, PHP views in `templates/`, browser assets in `assets/`, and translations in `languages/`. `blueprint.json` configures the disposable WordPress Playground demo.

## Build, Test, and Development Commands

There is no build step; the README says the assets ship pre-built. The repository has no package manifest, test runner, or linter configuration, so it defines no automated test command. For a manual runtime check, load `blueprint.json` in WordPress Playground and use its demo WordPress site.

## Coding Style & Naming Conventions

No formatter or linter enforces a style. Existing PHP components use lowercase, underscore-separated filenames (for example, `api_service.php`), and class names use the `Sitepulse_` prefix. Follow these established names when adding a component.

## Testing Guidelines

No test files or test framework are present in the repository. Use the Playground blueprint for disposable WordPress runtime checks when a change needs integration validation.

## Commit & Pull Request Guidelines

Recent history contains short descriptive subjects and GitHub merge subjects; no consistent prefix convention is evident. No pull request template is present. Keep commit subjects descriptive and concise so they match the existing history.

## graphify

This project has a knowledge graph in `graphify-out/`. When `/graphify` is invoked, follow the installed graphify skill.

- For codebase questions, run `graphify query "<question>"` first when `graphify-out/graph.json` exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for a focused concept.
- Dirty `graphify-out/` files can result from updates; use the graph unless the question concerns stale graph output or the user declines it.
- Use `graphify-out/wiki/index.md` for broad navigation when present. Read `graphify-out/GRAPH_REPORT.md` for broad architecture or when query, path, and explain lack context.
- After modifying code, run `graphify update .` to refresh the graph.

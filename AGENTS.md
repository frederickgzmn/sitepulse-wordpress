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

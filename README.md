# SitePulse — See What's Powering (or Slowing) Your Site

[![WordPress compatibility](https://plugintests.com/plugins/wporg/sitepulse/wp-badge.svg)](https://plugintests.com/plugins/wporg/sitepulse/latest)
[![PHP compatibility](https://plugintests.com/plugins/wporg/sitepulse/php-badge.svg)](https://plugintests.com/plugins/wporg/sitepulse/latest)
[![Plugin version](https://img.shields.io/wordpress/plugin/v/sitepulse?label=version&logo=wordpress&logoColor=white)](https://wordpress.org/plugins/sitepulse/)
[![Downloads](https://img.shields.io/wordpress/plugin/dt/sitepulse?label=downloads)](https://wordpress.org/plugins/sitepulse/advanced/)
[![Active installs](https://img.shields.io/wordpress/plugin/installs/sitepulse?label=active%20installs)](https://wordpress.org/plugins/sitepulse/advanced/)
[![License: GPL-2.0+](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)

Find the plugin, hook, or external request slowing WordPress — and see the next place to investigate.

## Welcome to the SitePulse GitHub repository

While the [plugin documentation](https://wordpress.org/plugins/sitepulse/) and [support forums](https://wordpress.org/support/plugin/sitepulse/) live on WordPress.org, here you can browse the source of the project, find and discuss open issues, and even [contribute yourself](#contributing).

SitePulse helps you **stop guessing why WordPress feels slow**. It monitors activity inside your own WordPress installation, highlights the slowest plugins and hooks, and reveals delayed external requests such as payment, email, analytics, and licensing calls.

Activate it, visit a few pages as you normally would, then open **SitePulse → Dashboard** — the *Recommended next step* card points you to the most useful finding first.

> **🎮 Try it instantly in WordPress Playground** — [launch the demo](https://playground.wordpress.net/?plugin=sitepulse&login=yes&url=/wp-admin/admin.php?page=wpsp_sitepulse): a temporary WordPress environment in your browser with SitePulse pre-installed. No account or installation required.

## Screenshots

| Dashboard | Basic View | Onboarding Wizard |
| --- | :---: | :---: |
| ![Real-time Admin Dashboard](https://ps.w.org/sitepulse/assets/screenshot-1.png) | ![Simple Performance Overview](https://ps.w.org/sitepulse/assets/screenshot-2.png) | ![Interactive Setup Wizard](https://ps.w.org/sitepulse/assets/screenshot-5.png) |

More views — including Developer Mode, settings, and single-page analytics — are available in the [PluginTests report](https://plugintests.com/plugins/wporg/sitepulse/latest) and on [WordPress.org](https://wordpress.org/plugins/sitepulse/#screenshots).

## Key Features

### Performance Profiling

* **Hook-level timing** — measured execution time for every WordPress hook and callback.
* **Slow plugin profiler** — see which plugins are bottlenecks and how much time they consume.
* **Per-page tracking** — detailed load-time breakdowns for individual pages and posts.
* **Performance rankings** — sort plugins and hooks by execution time to focus on what matters.

### External Request Monitoring

* Tracks every outbound HTTP/cURL call — payments, email, analytics, licensing — with response times.
* Flags slow third-party services and shows exactly which endpoints are slow, including HTTP status codes.
* Configurable slow-request threshold.

### Site Health & Stability

* **Automatic fatal-error detection** with instant email notifications.
* PHP error/warning/notice tracking with automatic cleanup and recovery-mode access.
* Cron job management plus a non-blocking fallback runner for stale tasks.
* Per-plugin memory and CPU usage tracking.

### Smart Experience

* **Dual dashboard views** — beginner-friendly *Basic View* and *Developer View*; switch anytime.
* **3-step onboarding wizard** that establishes your first performance baseline.
* **Admin bar widget** for quick metrics from anywhere in wp-admin.
* **Google PageSpeed Insights & AI diagnostics** as optional cloud features.

## Measured Footprint

Independently verified by [PluginTests.com](https://plugintests.com/plugins/wporg/sitepulse/latest) on WordPress 7.0.2 / PHP 8.1.12:

| Metric | Result |
| --- | --- |
| PHP errors, warnings, notices | None ✅ |
| JavaScript exceptions | None ✅ |
| Average memory increase | ~1.0 MB |
| Average page-load impact | +0.058 s |

Every tracker is optional and can be toggled off at any time from the dashboard.

## Installation

### Option 1 — WordPress Playground (no install)

Open the demo blueprint in your browser: [Launch SitePulse Demo](https://playground.wordpress.net/?plugin=sitepulse&login=yes&url=/wp-admin/admin.php?page=wpsp_sitepulse)

### Option 2 — Standard install

1. Install directly from **Plugins → Add New** in wp-admin, or upload the `sitepulse` folder to `/wp-content/plugins/`.
2. Activate **SitePulse** from the Plugins screen.
3. The setup wizard launches automatically — open **SitePulse → Dashboard** when you're done.
4. *(Optional)* Configure scheduled checks via WP-Cron or your hosting panel's cron jobs.

## Configuration

Fine-tune SitePulse with constants in `wp-config.php`:

```php
/** Verbose SitePulse debugging (off by default) */
define( 'SITEPULSE_DEBUG', false );

/** Aggressive tracking mode for load testing (off by default) */
define( 'SITEPULSE_STRESS_MODE', false );

/** Slow HTTP request threshold, in seconds (default: 1) */
define( 'WPSLOWHTTP_THRESHOLD', 1.5 );
```

| Constant | Type | Default | Purpose |
| --- | --- | --- | --- |
| `SITEPULSE_DEBUG` | `bool` | `false` | Verbose debugging output |
| `SITEPULSE_STRESS_MODE` | `bool` | `false` | Aggressive tracking mode |
| `WPSLOWHTTP_THRESHOLD` | `float` | `1` (s) | Threshold for flagging slow HTTP requests |

The Performance Monitor, External Requests tracker, and Plugin Profiler can each be toggled at runtime from **SitePulse → Settings**.

## Privacy First

Core monitoring runs **100% locally** — hook timings, plugin profiling, HTTP tracking, and error logs never leave your installation.

Cloud features (PageSpeed reports, AI diagnostics) are **optional, off by default**, and connect to `api.sitepulse.me`. When enabled they send **only performance metrics**: plugin names/versions/slugs, hook execution times, memory usage, request URLs without query strings, response times, and your site domain. They **never send** names, emails, passwords, IPs, user content, database content, visitor data, or anything PII.

You can preview exactly what would be sent before enabling anything, disable it all anytime, and the plugin keeps working fully locally without it. No data is sold or shared with third parties.

Full details: [sitepulse.me/privacy](https://sitepulse.me/privacy/)

## FAQ

**Does SitePulse slow my site down?**
No. It's built to be lightweight and runs asynchronously — see the measured footprint above. Profiling is optional and toggleable at any time.

**Does it work with WooCommerce or multisite?**
Yes. SitePulse tracks any page or post type, including WooCommerce product pages and multisite environments.

**Is this a "Lite" version with limited features?**
No. This is the full Core version — a complete, unlimited performance monitor. Core features are **free forever**.

**How do I join the Pro waitlist?**
Sign up at [sitepulse.me](https://sitepulse.me/). Pro adds historical timelines, scheduled PageSpeed audits, multi-site dashboards, CSV/PDF exports, and unlimited AI diagnostics.

## Support

This is a developer's portal for SitePulse and should not be used for support. Please visit the [support forums on WordPress.org](https://wordpress.org/support/plugin/sitepulse/).

## Reporting bugs

If you find an issue, [let us know here](https://github.com/frederickgzmn/sitepulse/issues/new)! Including the following makes bugs much easier to fix:

* WordPress version, PHP version, and active plugin list
* Steps to reproduce
* Relevant entries from the SitePulse dashboard (Developer View → error logs)

## Contributing

Anyone is welcome to contribute to SitePulse. There is no build step required — the assets ship pre-built.

Within your WordPress installation, navigate to `wp-content/plugins` and clone the repository:

```bash
git clone https://github.com/frederickgzmn/sitepulse.git
cd sitepulse
```

Then activate **SitePulse** from wp-admin. For a disposable test environment, the [Playground blueprint](blueprint.json) reproduces our standard demo setup.

There are various ways you can contribute:

* ⭐ [Leave a review](https://wordpress.org/plugins/sitepulse/reviews/) on WordPress.org
* 🐛 [Raise an issue](https://github.com/frederickgzmn/sitepulse/issues) on GitHub
* 🔀 Send us a Pull Request with bug fixes and/or new features
* 🌍 Translate SitePulse into your language ([instructions](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/))

## Requirements

| | Minimum | Tested up to |
| --- | --- | --- |
| WordPress | 5.5 | 7.0 |
| PHP | 7.4 | 8.1 |

## License

SitePulse is licensed under the [GNU General Public License v2.0 or later](LICENSE).

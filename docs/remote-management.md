# Remote management

SitePulse lets api.sitepulse.me watch and, step by step, manage a site's backups and its plugin, theme and WordPress core updates. This document is the contract between the plugin and the API; both repositories keep the same copy.

## When it is active

Remote management works on a site only when all of these are true:

1. The site has a SitePulse license (`sitepulse_license_key`), issued by `POST /api/website/website-request` as today.
2. Sending data to SitePulse is allowed (the plugin's External API setting).
3. Remote management is switched on for the site in the api.sitepulse.me dashboard.
4. UpdraftPlus 1.26 or later, or BackWPup 5.x, is installed and active.
5. The site administrator has not turned on **Block remote management** in SitePulse's settings.

The site checks in even when 3–5 are false, so the dashboard can show why remote management is off (for example "Install UpdraftPlus or BackWPup"). Remote management is not available yet on multisite networks or when SitePulse Pro is active; those sites do not check in.

Out of scope for remote actions, by design: restoring or deleting backups, installing, deleting or uploading plugins and themes, and backup storage credentials. Updates always come from WordPress's own update process (WordPress.org or a premium plugin's own updater), never from api.sitepulse.me.

## Signing

The license key is the shared secret. It never travels in remote-management requests.

### Site → API

Every request is a JSON `POST` with these headers:

| Header | Value |
|---|---|
| `X-SitePulse-Domain` | The site's host: lowercase, no scheme, `www.` prefix, port or path |
| `X-SitePulse-Timestamp` | Unix time in seconds |
| `X-SitePulse-Nonce` | 32 lowercase hexadecimal characters, new for every request |
| `X-SitePulse-Signature` | Lowercase hexadecimal HMAC-SHA256 of the canonical string, keyed with the license |

Canonical string, lines joined with `\n`:

```
POST
/api/website/remote/check-in
example.com
1760544000
5f0c2b1e9a7d4c3b8e6f1a2d3c4b5a69
<lowercase hex SHA-256 of the raw request body>
```

The API finds the site by its normalized domain, verifies the signature against the stored license, and rejects a request (HTTP 401) when the signature is wrong, the timestamp is more than 300 seconds away from its clock, or the nonce was already used in the last 10 minutes. A site that is blocked or cancelled on the API side gets HTTP 403.

### API → site

Every response carries:

| Header | Value |
|---|---|
| `X-SitePulse-Timestamp` | Unix time in seconds |
| `X-SitePulse-Signature` | Lowercase hexadecimal HMAC-SHA256, keyed with the license, of `<request nonce>\n<timestamp>\n<lowercase hex SHA-256 of the raw response body>` |

The plugin ignores a response whose signature does not match, whose timestamp is more than 300 seconds away, or that is not bound to the nonce it sent. Binding the response to the request nonce means a recorded response cannot be replayed.

### Test vector

Both repositories test their signing code against this example:

| Input | Value |
|---|---|
| License | `sk_test_license_0123456789` |
| Request | `POST /api/website/remote/check-in`, domain `example.com`, timestamp `1760544000`, nonce `5f0c2b1e9a7d4c3b8e6f1a2d3c4b5a69`, body `{"schema":1}` |
| Request signature | `633c6b913e4b6653592e94e7980dd55c8e66bc4834fb4abd32e7739c1ada1603` |
| Response | timestamp `1760544060`, body `{"success":true}` |
| Response signature | `e5be8c72903694dda6265dd3fa13f7d3929d34662b8e8fd748fcd8f3487e1b14` |

## Endpoints

All paths are under `https://api.sitepulse.me`. The plugin can point at another API with the `SITEPULSE_REMOTE_API_BASE` constant (for development only).

### `POST /api/website/remote/check-in`

Called every `check_in_seconds` (5 minutes while active, hourly otherwise). The plugin keeps `check_in_seconds` between 1 minute and 1 day and `inventory_seconds` between 1 hour and 1 week, and checks in at once when the site administrator changes the block switch.

```json
{
  "schema": 1,
  "plugin_version": "1.5.0",
  "wp_version": "7.1.2",
  "blocked": false,
  "backup_provider": { "id": "updraftplus", "version": "1.26.5", "supported": true },
  "backup_providers": [
    { "id": "updraftplus", "version": "1.26.5", "supported": true },
    { "id": "backwpup", "version": "5.7.8", "supported": true }
  ]
}
```

`backup_providers` lists every active backup plugin; `backup_provider` is the one SitePulse manages, or `null` when neither UpdraftPlus nor BackWPup is active. With both active, SitePulse manages the one picked in the dashboard (`remote_management.backup_provider` below) while it is active and supported; otherwise the supported one with the most recent successful backup, and UpdraftPlus when neither has one. Response:

```json
{
  "success": true,
  "remote_management": { "enabled": true, "active": true, "check_in_seconds": 300, "inventory_seconds": 21600, "backup_provider": null },
  "commands": [
    { "id": "01JABCDEF0123456789ABCDEFG", "type": "refresh_inventory", "args": {}, "expires_at": "2026-10-15T18:15:00Z" }
  ]
}
```

`enabled` is the dashboard switch; `active` also requires a supported backup provider and no local block. `backup_provider` is the backup plugin picked in the dashboard (`updraftplus`, `backwpup`) or `null`. Commands are only returned while `active` is true. A delivered command moves from `queued` to `sent`.

### `POST /api/website/remote/inventory`

Sent when remote management becomes active, every `inventory_seconds`, after any update, plugin or theme change, and for a `refresh_inventory` command.

```json
{
  "schema": 1,
  "collected_at": "2026-10-15T18:00:00Z",
  "site": { "wp_version": "7.1.2", "php_version": "8.2.27", "db_version": "8.0.35", "multisite": false, "locale": "en_US", "timezone": "Europe/Madrid", "disk_free": 52428800000 },
  "updates": {
    "can_update": true,
    "reason": null,
    "core": { "current": "7.1.2", "offers": [{ "version": "7.1.3", "type": "minor" }], "auto_update": "minor" },
    "plugins": [
      { "file": "elementor/elementor.php", "name": "Elementor", "version": "3.30.1", "active": true, "auto_update": false,
        "update": { "version": "3.31.0", "requires_wp": "6.6", "requires_php": "7.4", "tested": "7.1", "download": true } }
    ],
    "themes": [
      { "stylesheet": "astra", "name": "Astra", "version": "4.11.0", "active": true, "auto_update": false, "update": null }
    ],
    "translations_pending": 3,
    "management_tools": [{ "id": "managewp", "name": "ManageWP Worker" }],
    "job": { "command": "01JABCDEF0123456789ABCDEFG", "kind": "plugin", "phase": "updating", "done": 1, "total": 3, "started_at": "2026-10-16T01:00:05Z" }
  },
  "backups": {
    "provider": { "id": "updraftplus", "version": "1.26.5", "supported": true },
    "schedules": [
      { "name": "Files", "contents": ["files"], "frequency": "weekly", "next": "2026-10-19T02:00:00Z", "keep": 4, "destinations": ["googledrive"] },
      { "name": "Database", "contents": ["database"], "frequency": "daily", "next": "2026-10-16T02:00:00Z", "keep": 14, "destinations": ["googledrive"] }
    ],
    "running": { "name": "", "progress": null, "stage": "clouduploading" },
    "last": { "time": "2026-10-15T02:00:00Z", "ok": true, "errors": 0, "warnings": 1 },
    "next": "2026-10-16T02:00:00Z",
    "recent": [
      { "time": "2026-10-15T02:00:00Z", "contents": ["database", "files"], "size": 52428800, "destinations": ["googledrive"], "label": "" }
    ]
  }
}
```

`site.timezone` is the site's time zone (a name such as `Europe/Madrid`, or an offset such as `+02:00`); `site.disk_free` is the free space in bytes where WordPress writes updates, or `null` when the server does not tell. `updates.management_tools` lists other tools that also update or manage the site: active ManageWP Worker, MainWP Child, InfiniteWP Client, Jetpack, Easy Updates Manager, WP Umbrella, BlogVault, WP Remote or Companion Auto Update, and hosts that manage updates themselves (WP Engine, Kinsta, Pantheon, WordPress.com, GoDaddy Managed WordPress, Flywheel). `updates.job` is the update SitePulse is running (see "Update commands"), or `null`.

`reason` explains `can_update: false`: `file_mods_disabled` (`DISALLOW_FILE_MODS`) or `filesystem_credentials` (WordPress needs FTP credentials to write files). `core.auto_update` is `major`, `minor` or `disabled`, as WordPress decides it from its constants, options and filters. `update.download` is `false` when a premium plugin's license provides no download package. Response: `{ "success": true }`.

`backups` is `null` without UpdraftPlus or BackWPup, holds only `provider` when that version is not supported, and holds `provider` and `"error": "status_unavailable"` when the backup plugin failed while it was read. Otherwise:

| Field | Meaning |
|---|---|
| `schedules` | UpdraftPlus has one schedule for files and one for the database; BackWPup has one per job ("Backup now" jobs are left out). |
| `schedules[].contents`, `recent[].contents` | `database`, `files`; BackWPup jobs can also have `content_export`, `plugin_list` and `database_check`. |
| `schedules[].frequency` | The plugin's own value (UpdraftPlus: `every4hours`, `every8hours`, `twicedaily`, `daily`, `weekly`, `fortnightly`, `monthly`; BackWPup: `hourly`, `daily`, `weekly`, `monthly`, or `custom`), or `manual` when the backup is not scheduled. |
| `schedules[].keep` | Backups the plugin keeps; for BackWPup the folder destination's limit, `null` when unknown. |
| `schedules[].destinations`, `recent[].destinations` | Storage methods, lowercase (`googledrive`, `s3`, `dropbox`, ...). UpdraftPlus lists only remote storage, so an empty list means the backups stay on this server; BackWPup lists `folder` for this server. |
| `running` | `null` when no backup is running. `progress` (percent) is `null` for UpdraftPlus; `stage` is the plugin's own step name. |
| `last` | The newest finished backup, or `null`. For BackWPup, `ok`, `errors` and `warnings` are `null` when its log cannot be read. |
| `next` | The next scheduled backup of any schedule, or `null`. |
| `recent` | The 10 newest backups. |

### `POST /api/website/remote/events`

Up to 50 events per request, oldest first. Event `id`s are UUIDs; the API ignores ids it already stored, so a retry never duplicates events.

```json
{
  "events": [
    { "id": "6f1d0c3e-6c0a-4a43-9a52-0d8f3a6b2c11", "type": "update.completed", "occurred_at": "2026-10-15T18:02:11Z",
      "data": { "kind": "plugin", "initiator": "user", "items": [{ "id": "elementor/elementor.php", "from": "3.30.1", "to": "3.31.0", "ok": true }] } }
  ]
}
```

| Type | `data` |
|---|---|
| `update.completed` | `kind` (`plugin`, `theme`, `core`, `translation`), `initiator` (`user`, `auto`, `sitepulse`), `items` with `id`, `from`, `to`, `ok` |
| `plugin.activated`, `plugin.deactivated` | `file`, `name`, `version` |
| `theme.switched` | `stylesheet`, `name`, `version`, `previous` |
| `backup.completed` | `provider`, `ok`, `errors`, `warnings`, `size`, `time` |
| `backup_provider.changed` | `provider` (object or `null`) |
| `update.impact` | `kind`, `id`, `name`, `from`, `to`, `updated_at`, `before` and `after` (`{ "day", "ms", "loads" }`, `after` `null` when `reason` is `missed`), `measured`, `reason` |
| `performance.daily` | `day`, `ms`, `loads`, `top` (up to 5 `{ "kind", "id", "ms" }`) |
| `error.fatal` | `message`, `file`, `line`, `source` (`{ "kind", "id" }`), `count`, `first_at`, `last_at` |

Response: `{ "success": true, "accepted": 1 }`.

**Performance figures.** SitePulse's profiler keeps each callback's time in fixed UTC days. A figure for a day (`ms`) is the sum, over the code it covers, of each callback's own time per page load it ran on, in milliseconds; `loads` is the most page loads any of those callbacks ran on that day. Code is attributed by file: a plugin's folder (or its file, for a single-file plugin), a theme's folder, or `wp-includes` and `wp-admin` for WordPress core. SitePulse's own code never counts.

- `update.impact`: when a plugin, theme or core update finishes while remote management is active (SitePulse's own, WordPress's automatic updates, or one made in wp-admin), the plugin keeps the item's figures for the full UTC day before the update (`before`). At its first check-in on the second UTC day after the update, it sends the figures for the full day after it (`after`). `measured` is `true` when both days have at least 20 page loads; otherwise `reason` is `not_enough_traffic`. `reason` is `missed` when the site did not check in during that second day, which is the only day those figures can be read. A later update of the same item replaces a pending measurement. SitePulse's own updates and translations are not measured. `day` is a date (`2026-10-14`), `updated_at` a UTC time.
- `performance.daily`: once per UTC day, for the previous full day: the figures for all code (`ms`, `loads`) and the five sources taking the most time (`kind` `plugin`, `theme` or `core`; `id` the plugin folder, theme stylesheet or `core`). Not sent for a day without page loads, for example while monitoring was paused.
- `error.fatal`: PHP fatal errors SitePulse's error log recorded since the last check-in, grouped by message, file and line. `message` is the first line, at most 300 characters; `file` is relative to the WordPress folder; `source.kind` is `plugin`, `theme`, `core` or `other` and `source.id` the plugin folder or theme stylesheet (`null` otherwise). At most 10 per check-in. Errors from before remote management was active are not sent.

### `POST /api/website/remote/command-result`

```json
{ "id": "01JABCDEF0123456789ABCDEFG", "status": "done", "result": { "inventory_sent": true }, "error": null }
```

`status` is `done` or `failed`; `error` is a short machine-readable code (`unsupported_command`, `expired`, `inventory_failed`, …). Response: `{ "success": true }`. The API ignores results for commands that already finished.

## Commands

| Type | `args` | Phase |
|---|---|---|
| `refresh_inventory` | none | 1 |
| `backup_now` | `what`: `full` (default), `database` or `files` | 2 |
| `cancel_backup` | none | 2 |
| `apply_backup_preset` | `preset_id`, `name`, `preset` (the API fills these from a saved preset) | 2 |
| `update_plugins` | `plugins` (1–50 plugin files), `backup_first` (default `true`), `backup_wait` (minutes, 5–240, default 60) | 3 |
| `update_themes` | `themes` (1–50 stylesheets), `backup_first`, `backup_wait` | 3 |
| `update_core` | `version`, `backup_first`, `backup_wait` | 3 |
| `set_auto_updates` | `kind` (`plugin` or `theme`), `items` (1–200), `enabled` | 3 |

### Backup commands

All three answer `no_backup_plugin` when no supported backup plugin is active and `backup_plugin_error` when the backup plugin fails while running the command. After a successful one, the plugin sends a fresh inventory in the same check-in. Results always carry `provider`.

| Command | Result when `done` | Errors |
|---|---|---|
| `backup_now` | `what`, `job`: UpdraftPlus's backup nonce, or the BackWPup job id | `backup_running` (a backup is running or already queued), `invalid_arguments`, `start_failed` (BackWPup did not start its job) |
| `cancel_backup` | `cancelled`: how many running or queued backups were stopped (0 when none) | |
| `apply_backup_preset` | `preset`: its name, `schedules`: the backup schedules afterwards (as in the inventory), `created`: BackWPup jobs created | `invalid_preset` |

- **UpdraftPlus** `backup_now` queues UpdraftPlus's own "Backup Now" (labelled "SitePulse") as a one-off WordPress cron event, so it starts at the next cron run and uses the storage set up in UpdraftPlus. `cancel_backup` stops running jobs the way UpdraftPlus's "Delete" link does and removes backups SitePulse queued that have not started.
- **BackWPup** `backup_now` runs BackWPup's own "Backup Now" job, which, like its button, keeps the archive on this server. `cancel_backup` aborts the running job.

### Backup preset format

```json
{
  "files": { "frequency": "weekly", "keep": 4, "include": ["plugins", "themes", "uploads", "other"] },
  "database": { "frequency": "daily", "keep": 14 }
}
```

`files` and `database` are optional, but a preset has at least one of them. `frequency` is `manual`, `daily`, `weekly` or `monthly` (`manual` turns the schedule off); `keep` (1–100) is how many backups the plugin keeps; `include` (files only, optional) lists the parts of the site saved: `plugins`, `themes`, `uploads` and `other` (the rest of `wp-content`). Presets never change where backups are stored or any storage credentials.

- **UpdraftPlus:** sets the files and database schedules through UpdraftPlus's own schedulers, the retention (`updraft_retain`, `updraft_retain_db`) and the included parts.
- **BackWPup:** jobs that save files get the files settings, jobs that save the database get the database settings, and a job that saves both gets the more frequent schedule and the larger retention. Retention applies to every storage of the job. When no job saves files (or the database) and its frequency is not `manual`, SitePulse creates one with the storage of the site's existing jobs (this server when there are none); with no jobs at all and the same frequency for both, it creates one job for both, as BackWPup's own setup does. Jobs run at midnight site time when their frequency changes.

### Update commands

Updates always run through WordPress's own upgrader, so packages come from WordPress.org or a premium plugin's own updater, exactly as in wp-admin; api.sitepulse.me never sends code. An update command is answered when the whole update is done or has failed, usually within minutes; the API waits up to 6 hours for the result of an update command (1 hour for other commands), and the inventory's `updates.job` shows its progress meanwhile. The plugin runs one update at a time, in WordPress cron requests:

1. **Checks.** WordPress may change files without FTP credentials (`cannot_update`), `wp-content` has at least 100 MB free (`not_enough_disk`), no other SitePulse update is running (`update_running`), and the arguments are valid (`invalid_arguments`).
2. **Backup first** (when `backup_first`): a full backup with the managed backup plugin, as `backup_now` makes it. Nothing is updated when no supported backup plugin is active (`no_backup_plugin`), the backup fails (`backup_failed`) or takes longer than `backup_wait` minutes (`backup_timeout`).
3. **Health baseline.** The plugin loads the home page, wp-admin and, with WooCommerce, the cart and checkout pages, using WordPress's own fatal-error check (the "scrape" request the plugin editor uses). A site that already fails is not updated (`site_unhealthy`), nor a site that cannot load its own home page, because its updates could not be checked (`health_check_unavailable`, typically a host that blocks such "loopback" requests).
4. **One item at a time.** WordPress's upgrader installs the update offered for the item, with maintenance mode only while files are replaced. Then the same pages are loaded again. The update fails its health check when a page shows a PHP fatal error (in any code, such as another plugin that relied on the updated one), answers with a 5xx status it did not have before, stops answering, ends before WordPress's error check can report (where it reported before; a PHP fatal error can end a page that way), or SitePulse's error log records a fatal error in the updated plugin's, theme's or WordPress's files. A plugin or theme that fails is rolled back to its previous version (WordPress 6.3 and later keep a copy during the update) and the remaining items are skipped. WordPress core cannot be rolled back automatically. When the site administrator blocks remote management while an update runs, the items not updated yet are skipped (`blocked`).

Result: `done` when every item is up to date afterwards, otherwise `failed` with the error of the first problem (`update_failed` when an item failed). `result` is `{ "backup": { "provider", "job" } | null, "items": [ { "id", "from", "to", "ok", "error", "rolled_back" } ] }`; an item that failed its health check also has `problems`, up to five short descriptions such as `https://example.com/: HTTP 500`. Item errors: `up_to_date` (no update offered; `ok` stays `true`), `not_installed`, `requirements` (the update needs a newer PHP or WordPress), `no_package` (a premium plugin without a valid license), `install_failed` (WordPress could not install it and kept the old version), `health_check_failed`, `interrupted` (the request updating it stopped, for example at a PHP time limit) and `skipped` (not tried after a failure). Items with `up_to_date`, `not_installed`, `requirements` or `no_package` do not stop the others. The plugin reports the update as an `update.completed` event with `initiator` `sitepulse` and `rolled_back` on each item.

`set_auto_updates` turns WordPress's own automatic updates on or off for installed plugins or themes (the "Enable auto-updates" links in wp-admin) and answers at once: `result` is `{ "kind", "enabled", "items" }` with the items whose setting changed.

A command expires 15 minutes after it is created unless it was delivered. The plugin runs up to 20 commands per check-in, in WordPress's background (cron) request. It remembers the ids of the last 50 commands so it never runs one twice, answers `expired` for a command whose `expires_at` has passed, and answers `unsupported_command` for types it does not know.

## Dashboard (portal) endpoints

The SitePulse dashboard calls these from trusted IPs with the existing portal authentication (`X-API-Key` and the HMAC `X-Portal-Auth` header), plus the site's `domain` and `license_key` in the body. Only dashboard users who own the license can reach a site; the license alone never creates commands.

| Endpoint | Body | Purpose |
|---|---|---|
| `POST /api/portal/remote-management` | — | Status: switch, `active`, last check-in, what the site reported, backup warnings |
| `POST /api/portal/remote-management/update` | `enabled`, `requested_by` | Turn remote management on or off |
| `POST /api/portal/remote-management/inventory` | — | Latest software and backup inventory, backup warnings |
| `POST /api/portal/remote-management/backup-provider` | `backup_provider` (`updraftplus`, `backwpup` or `null`) | Pick the backup plugin to manage when both are active |
| `POST /api/portal/remote-management/update-policy` | — | The site's update policy (defaults until it is changed) |
| `POST /api/portal/remote-management/update-policy/save` | `policy` (the update policy format) | Change the site's update policy |
| `POST /api/portal/remote-management/events` | `limit` (≤ 100), `before` (event time) | Event history, newest first |
| `POST /api/portal/remote-management/commands` | `limit` (≤ 100) | Recent commands and their results |
| `POST /api/portal/remote-management/commands/create` | `type`, `args`, `requested_by` | Queue a command (requires `active`); `apply_backup_preset` takes `args.preset_id`, a preset owned by `requested_by` |
| `POST /api/portal/remote-management/commands/cancel` | `id` | Cancel a command that was not delivered yet |
| `POST /api/portal/remote-management/timeline` | `limit` (≤ 100, default 50), `before` (time), `categories` (optional list) | The site's activity timeline, newest first |
| `POST /api/portal/remote-management/reports` | — | The site's stored monthly reports |
| `POST /api/portal/remote-management/reports/generate` | `month` (`YYYY-MM`) | Build (or rebuild) a month's report and return it |
| `POST /api/portal/remote-management/reports/download` | `month` | A link to the report as a PDF, valid for 24 hours |
| `POST /api/portal/remote-management/reports/send` | `month` | Email the report to the site's report recipients now |
| `POST /api/portal/remote-management/notifications` | — | Notification settings and the 20 latest notifications |
| `POST /api/portal/remote-management/notifications/save` | `notifications` (the settings format), `rotate_webhook_secret` (optional) | Change the notification settings |
| `POST /api/portal/remote-management/notifications/test` | — | Send a test message to every configured channel |

Backup presets belong to a dashboard user (their email) instead of a site, so these endpoints take `email` instead of `domain` and `license_key`:

| Endpoint | Body | Purpose |
|---|---|---|
| `POST /api/portal/remote-management/presets` | `email` | The user's presets, by name |
| `POST /api/portal/remote-management/presets/save` | `email`, `id` (to change one), `name` (unique per user), `settings` (the preset format) | Create or change a preset |
| `POST /api/portal/remote-management/presets/delete` | `email`, `id` | Delete a preset; commands already queued keep their copy |

### Backup warnings

The API checks the latest inventory whenever the dashboard reads the status or the inventory, and returns `backup_warnings`, a list of `{ "code", "message" }`:

| Code | When |
|---|---|
| `local_only` | Every scheduled backup (or, with none scheduled, every schedule) stores its archives only on this server (no storage for UpdraftPlus, only `folder` for BackWPup). |
| `no_backup` | A backup is scheduled but there is no successful backup yet. |
| `overdue` | The last successful backup is older than twice the most frequent schedule. |
| `small_backup` | The newest backup is less than half the size of the previous backup with the same contents. |

### Update policy

Each site has an update policy; until the dashboard changes it, it is this default:

```json
{
  "automatic": true,
  "plugins": "minor",
  "themes": "minor",
  "core": "minor",
  "exclude": [],
  "wait_days": 3,
  "window": { "start": 1, "end": 5 },
  "backup_first": true,
  "backup_wait": 60
}
```

`plugins` and `themes` are `none`, `security`, `minor` or `all`; `core` is `none`, `minor` or `all`:

- `security`: only updates that fix a vulnerability of the installed version, as SitePulse's Wordfence scan of the site lists it (`patched_versions`).
- `minor`: security updates and updates within the same major version (the first number for plugins and themes, such as 3.30.1 → 3.31.0; the same branch for WordPress, such as 7.1.2 → 7.1.3).
- `all`: every update.

`exclude` lists plugin files and theme stylesheets that are never updated automatically, such as `woocommerce/woocommerce.php`. `wait_days` (0–30) holds an update until that many days after SitePulse first saw it offered on any site, so quick fix releases arrive first; security updates do not wait. `window` is the hours of the day, in the site's time zone, in which automatic updates may start (`start` 0–23, `end` 1–24; a window such as 22–4 wraps past midnight). `backup_first` and `backup_wait` apply to automatic updates. `automatic: false` turns automatic updates off; the dashboard can still queue updates.

**Automatic updates.** While remote management is active, at each check-in in the site's window the API queues at most one automatic update command (`requested_by` is `automatic`): plugins first, then themes, then WordPress, each with the security updates first. It skips items that are excluded, have no download package, need a newer PHP or WordPress than the site has, or failed in an update command during the last 24 hours. It queues nothing while another update command is queued or running, before the inventory has been refreshed after the last update, or after an automatic update failed in the last 24 hours. WordPress minor releases are left to WordPress when its own automatic updates install them (`core.auto_update` `minor` or `major`).

**Visual check.** After an update command succeeds, the API starts a visual scan of the site (trigger `update`) when visual monitoring is on for it, and records the scan's id as `visual_scan` in the command's result.

### Update warnings

The status and inventory responses also return `update_warnings` and `security_updates`:

| Code | When |
|---|---|
| `cannot_update` | WordPress cannot update files on its own (`updates.reason`). |
| `other_manager` | Other tools also update or manage the site (`updates.management_tools`), so updates may be done twice or undone. |
| `security_updates` | Updates fix known vulnerabilities of installed plugins, themes or WordPress. |

`security_updates` lists those updates: `{ "kind", "id", "version" }`.

### Slowdowns and security fixes

The API marks:

- **Update slowdowns:** a measured `update.impact` whose `after.ms` is at least 50 ms and 20% above `before.ms`.
- **Site slowdowns:** a `performance.daily` with at least 50 page loads whose `ms` is at least 50 ms and 25% above the median of the site's previous 7 daily figures (it needs 3 earlier days).
- **Security fixes:** an `update.completed` item that fixes a vulnerability of the version it replaced, as the site's Wordfence scan lists it when the event arrives.

### Timeline

`POST /api/portal/remote-management/timeline` answers `{ "success": true, "timeline": [ { "time", "category", "type", "summary", "data" } ] }`, newest first. `summary` is one English sentence such as "Elementor 3.31.0 made pages 120 ms slower (182 → 302 ms per page load)."; `data` holds the details. For the next page, pass the oldest `time` as `before`; entries at exactly that time are not repeated.

| Category | Types | Comes from |
|---|---|---|
| `update` | `update.completed` (items carry `security`) | events |
| `performance` | `update.impact` (carries `slowdown`), `performance.daily` (carries `slowdown` and `median_ms`) | events |
| `backup` | `backup.completed`, `backup_provider.changed` | events |
| `plugin` | `plugin.activated`, `plugin.deactivated` | events |
| `theme` | `theme.switched` | events |
| `error` | `error.fatal` | events |
| `command` | `command.queued` (with `requested_by`), `command.finished` (with `status`, `error`, and `visual_scan`: `{ "status", "pages_total", "pages_changed" }` when one ran) | commands |
| `notification` | `notification.sent` (with `trigger`, `status`, `channels`) | notifications |

### Monthly reports

A report summarizes one month of a site for its client, in the site's time zone (UTC when it is not known):

```json
{
  "month": "2026-10",
  "timezone": "Europe/Madrid",
  "generated_at": "2026-11-01T12:00:04Z",
  "site": { "domain": "example.com", "name": "Example" },
  "updates": {
    "applied": [{ "kind": "plugin", "id": "elementor/elementor.php", "name": "Elementor", "from": "3.30.1", "to": "3.31.0", "time": "2026-10-16T01:10:00Z", "initiator": "sitepulse", "security": true }],
    "failed": [{ "kind": "plugin", "id": "forms/forms.php", "name": "Forms", "from": "2.0", "to": "2.0", "time": "2026-10-16T01:12:00Z", "rolled_back": true }],
    "security_fixes": 1
  },
  "backups": { "succeeded": 30, "failed": 1, "last_success": "2026-10-31T02:00:00Z", "largest": 55000000 },
  "performance": {
    "impacts": [{ "kind": "plugin", "id": "elementor/elementor.php", "name": "Elementor", "to": "3.31.0", "before_ms": 182.4, "after_ms": 302.9, "slowdown": true }],
    "average_ms": 410.2, "days": 31, "slowdowns": 1
  },
  "errors": { "count": 4, "top": [{ "message": "Uncaught Error: …", "source": { "kind": "plugin", "id": "forms" }, "count": 3 }] },
  "visual_checks": { "count": 3, "with_changes": 1 },
  "pending": { "updates": 2, "security_updates": 0 }
}
```

- `pending` is read from the latest inventory when the report is built.
- Uptime is not part of the report until SitePulse measures it.
- On the first of each month at 12:00 UTC, when the previous month is over in every time zone, the API builds that month's report for every site with remote management switched on. When the site has report recipients, it emails them the PDF.
- `updates.failed` lists items that did not update or were rolled back (`rolled_back`); the commands' results hold the reasons.
- `reports` answers `{ "reports": [ { "month", "generated_at", "sent_at" } ], "buildable_months": [...] }`; `reports/send` answers `{ "sent_to": 2 }`, or 422 when the site has no report recipients.
- `reports/generate` accepts the current month and the two previous ones (events are kept 90 days) and replaces the stored copy.
- `reports/download` and `reports/send` use the stored copy, building it first when there is none.
- Stored reports are kept 13 months.
- The PDF link expires after 24 hours, like the performance report's.

### Notifications

Each site has its notification settings; until the dashboard saves them, there are no channels and nothing is sent:

```json
{
  "emails": ["ops@agency.example"],
  "slack_webhook_url": "https://hooks.slack.com/services/T000/B000/XXXX",
  "webhook_url": "https://hooks.agency.example/sitepulse",
  "triggers": ["update_failed", "backup_failed", "backup_overdue", "check_ins_stopped", "fatal_error", "update_slowdown", "site_slowdown"],
  "report_emails": ["client@example.com"]
}
```

- **Fields:** `emails` and `report_emails` hold up to 10 addresses each. `slack_webhook_url` must be a Slack incoming webhook (`https://hooks.slack.com/…`). `webhook_url` must be HTTPS on a public host, with no user name or password; the API checks the host's addresses when the settings are saved and again before each delivery.
- **Webhook secret:** the settings response includes `webhook_secret`. It is created when a webhook URL is first saved, and `rotate_webhook_secret: true` replaces it.
- **`triggers`:** by default, every trigger is on.

| Trigger | When | Sent |
|---|---|---|
| `update_failed` | An update command (`update_plugins`, `update_themes`, `update_core`) finishes `failed`, including rolled-back updates | Once per command |
| `backup_failed` | A `backup.completed` event with `ok: false`, or a `backup_now` command that failed | Once per event or command |
| `backup_overdue` | The `overdue` or `no_backup` backup warning appears | Once until the warning clears |
| `check_ins_stopped` | An active site has not checked in for 2 hours: the site may be down, or WP-Cron is not running | Once, plus a second message when check-ins resume |
| `fatal_error` | `error.fatal` events arrive | At most once an hour per site, summarizing them |
| `update_slowdown` | An update slowdown | Once per measurement |
| `site_slowdown` | A site slowdown | Once per day |

The API checks `backup_overdue` and `check_ins_stopped` every 15 minutes, only for sites with remote management switched on. Deliveries are queued and tried up to 3 times per channel.

- **Email:** plain text, from the API's mail sender.
- **Slack:** a message with the subject and text.
- **Webhook:** `POST` JSON `{ "id", "trigger", "site": { "domain", "name" }, "subject", "message", "data", "occurred_at" }` with these headers:
  - `X-SitePulse-Event` (the trigger) and `X-SitePulse-Timestamp` (Unix seconds);
  - `X-SitePulse-Signature`: `sha256=` followed by the hexadecimal HMAC-SHA256 of `<timestamp>.<body>`, keyed with `webhook_secret`.

  Redirects are not followed, the timeout is 10 seconds, and any 2xx status counts as delivered.

The notifications log keeps `id`, `trigger`, `subject`, `message`, `status` (`pending`, `sent`, `partial`, `failed`), `channels` (each channel's `pending`, `sent` or `failed`), `created_at` and `sent_at`. `notifications`, and `notifications/save`, answer `{ "settings", "triggers", "recent" }`, where `recent` holds the 20 latest log entries. `notifications/test` sends a `test` notification right away and returns its log entry, or 422 when the site has no channel.

## Retention

The API keeps events, finished commands and notifications for 90 days, monthly reports for 13 months (their PDF links expire after 24 hours), and only the latest inventory per site. The plugin keeps up to 200 undelivered events (the oldest are dropped first), the last 100 remote-management activity entries and up to 50 pending update measurements; it sends events only while remote management is active, up to 50 per request. Uninstalling the plugin deletes all of them.

## Phases

1. **Read-only and switches:** signed endpoints, dashboard switch, backup provider detection, local block switch, activity log, inventory, events, `refresh_inventory`.
2. **Backups:** `backup_now`, `cancel_backup`, backup presets, warnings for backups kept only on the same server, unusually small backups and overdue backups.
3. **Updates:** `update_plugins`, `update_themes`, `update_core` and `set_auto_updates`; update policies (security and minor updates automatic, major updates manual, 3-day wait, 1–5 am window) with automatic updates; pre-checks, a backup first, one update at a time with health checks and rollback; a visual check after updates; warnings about security updates and other management tools.
4. **Extras:** performance impact of each update, daily performance and fatal errors, site activity timeline, monthly client reports, failure notifications by email, Slack and webhook.

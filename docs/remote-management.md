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
  "site": { "wp_version": "7.1.2", "php_version": "8.2.27", "db_version": "8.0.35", "multisite": false, "locale": "en_US" },
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
    "translations_pending": 3
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

Response: `{ "success": true, "accepted": 1 }`.

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
| `update_plugins` | `plugins` (plugin files), `backup_first` | 3 |
| `update_themes` | `themes` (stylesheets), `backup_first` | 3 |
| `update_core` | `version`, `backup_first` | 3 |
| `set_auto_updates` | `kind` (`plugin` or `theme`), `items`, `enabled` | 3 |

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

A command expires 15 minutes after it is created unless it was delivered. The plugin runs up to 20 commands per check-in, in WordPress's background (cron) request. It remembers the ids of the last 50 commands so it never runs one twice, answers `expired` for a command whose `expires_at` has passed, and answers `unsupported_command` for types it does not know.

## Dashboard (portal) endpoints

The SitePulse dashboard calls these from trusted IPs with the existing portal authentication (`X-API-Key` and the HMAC `X-Portal-Auth` header), plus the site's `domain` and `license_key` in the body. Only dashboard users who own the license can reach a site; the license alone never creates commands.

| Endpoint | Body | Purpose |
|---|---|---|
| `POST /api/portal/remote-management` | — | Status: switch, `active`, last check-in, what the site reported, backup warnings |
| `POST /api/portal/remote-management/update` | `enabled`, `requested_by` | Turn remote management on or off |
| `POST /api/portal/remote-management/inventory` | — | Latest software and backup inventory, backup warnings |
| `POST /api/portal/remote-management/backup-provider` | `backup_provider` (`updraftplus`, `backwpup` or `null`) | Pick the backup plugin to manage when both are active |
| `POST /api/portal/remote-management/events` | `limit` (≤ 100), `before` (event time) | Event history, newest first |
| `POST /api/portal/remote-management/commands` | `limit` (≤ 100) | Recent commands and their results |
| `POST /api/portal/remote-management/commands/create` | `type`, `args`, `requested_by` | Queue a command (requires `active`); `apply_backup_preset` takes `args.preset_id`, a preset owned by `requested_by` |
| `POST /api/portal/remote-management/commands/cancel` | `id` | Cancel a command that was not delivered yet |

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

## Retention

The API keeps events and finished commands for 90 days, and only the latest inventory per site. The plugin keeps up to 200 undelivered events (the oldest are dropped first) and the last 100 remote-management activity entries; it sends events only while remote management is active, up to 50 per request. Uninstalling the plugin deletes both.

## Phases

1. **Read-only and switches:** signed endpoints, dashboard switch, backup provider detection, local block switch, activity log, inventory, events, `refresh_inventory`.
2. **Backups:** `backup_now`, `cancel_backup`, backup presets, warnings for backups kept only on the same server, unusually small backups and overdue backups.
3. **Updates:** update policies (security and minor updates automatic, major updates manual, 3-day wait, 1–5 am window), pre-checks, one update at a time with health checks, rollback for WordPress.org plugins and themes, warnings about other management tools.
4. **Extras:** performance impact of each update, site activity timeline, client reports, failure notifications.

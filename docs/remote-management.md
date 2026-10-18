# Remote management

SitePulse lets api.sitepulse.me watch and, step by step, manage a site's backups and its plugin, theme and WordPress core updates. This document is the contract between the plugin and the API; both repositories keep the same copy.

## When it is active

Remote management works on a site only when all of these are true:

1. The site has a SitePulse license (`sitepulse_license_key`), issued by `POST /api/website/website-request` as today. The plugin registers a site that has none yet when its administrator asks for a connection key.
2. Sending data to SitePulse is allowed (the plugin's External API setting).
3. Remote management is switched on for the site in the api.sitepulse.me dashboard, which takes the site's connection key (see "Connecting a site to an account").
4. The site administrator has not turned on **Block remote management** in SitePulse's settings.
5. The site has checked in.

Updates need nothing else. Backups need UpdraftPlus 1.26 or later, or BackWPup 5.x, installed and active: without one, the API does not queue backup commands and updates run without a backup first.

The site checks in even when 3 and 4 are false, so the dashboard can show why remote management is off. Remote management is not available yet on multisite networks or when SitePulse Pro is active; those sites do not check in.

Out of scope for remote actions, by design: restoring or deleting backups, installing, deleting or uploading plugins and themes, and backup storage credentials. Updates always come from WordPress's own update process (WordPress.org or a premium plugin's own updater), never from api.sitepulse.me.

## Signing

The license key is the shared secret, until the dashboard rotates the site's key (see "Key rotation"); from then on that key signs remote-management requests and responses instead of the license. The license never travels in remote-management requests; a rotated key travels once, in the check-in response that offers it.

The site endpoints accept 30 requests a minute per site (by its `X-SitePulse-Domain` and address) and 1,200 a minute per address, which many sites share on shared hosting; more get `429`.

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

Called every `check_in_seconds` (5 minutes while active, hourly otherwise). The plugin keeps `check_in_seconds` between 1 minute and 1 day and `inventory_seconds` between 1 hour and 1 week, and checks in at once when the site administrator changes the block switch, asks for a connection key or checks the connection, and when its key was used (see "Connecting a site to an account").

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
  ],
  "cron": { "url": "https://shop.example/wp-cron.php", "disabled": false, "late": 40 },
  "features": ["safe_update"]
}
```

`features` lists what the plugin runs beyond the commands every version runs: `safe_update` means it runs safe updates (`backup_parts` and `safe_update` in update commands, the visual check before the update, `restore_backup` and `rollback_item`; see "Safe updates"). The API offers safe updates only to a site whose last check-in reported it, and the status (`POST /api/portal/remote-management`) repeats it in `site.features`; older plugins send no `features`. `cron` (plugin 1.5 and later; see "WP-Cron") is the site's `wp-cron.php`, whether `DISABLE_WP_CRON` is set, and `late`, the seconds the most overdue scheduled task had waited at the check-in. `backup_providers` lists every active backup plugin; `backup_provider` is the one SitePulse manages, or `null` when neither UpdraftPlus nor BackWPup is active. With both active, SitePulse manages the one picked in the dashboard (`remote_management.backup_provider` below) while it is active and supported; otherwise the supported one with the most recent successful backup, and UpdraftPlus when neither has one. Response:

```json
{
  "success": true,
  "remote_management": { "enabled": true, "active": true, "check_in_seconds": 300, "inventory_seconds": 21600, "backup_provider": null },
  "commands": [
    { "id": "01JABCDEF0123456789ABCDEFG", "type": "refresh_inventory", "args": {}, "expires_at": "2026-10-15T18:15:00Z" }
  ]
}
```

`enabled` is the dashboard switch; `active` also requires no local block (the site has checked in, with this request). `backup_provider` is the backup plugin picked in the dashboard (`updraftplus`, `backwpup`) or `null`. Commands are only returned while `active` is true. A delivered command moves from `queued` to `sent`.

While a key rotation is pending (see "Key rotation"), `remote_management` also holds `new_key`, the site's next signing key (32 to 128 characters). It is sent whether or not remote management is active.

### `POST /api/website/remote/key`

Confirms a key rotation. The plugin sends `{ "schema": 1 }` signed with the **new** key it received at check-in. The API then makes that key the site's signing key and answers `{ "success": true }`, signed with the new key. The plugin stores the new key (option `sitepulse_remote_key`) only after this answer; until then it keeps signing with the old key, and the API keeps offering the new one at each check-in. Asking again with the new key after it was confirmed also succeeds, so a lost answer is harmless. A request signed with a key that is not pending or newly confirmed gets 409 `no_rotation_pending`.

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

A command that answers later (the update commands) is first reported as `{ "id", "status": "running", "result": null, "error": null }` when the site starts it; the API records `started_at` and moves it from `sent` to `running`. The final `done` or `failed` is accepted from `sent` or `running`.

## Connecting a site to an account

A site is set up in a dashboard user's account with its **connection key**, as a ManageWP site is: the site's administrator gets the key from the plugin (Settings → Remote management → Get connection key) and the user enters it in the dashboard (Remote Management → Connect a site), with the site's address. For a site already in the user's account, the dashboard asks only for the key. The API then, in one step:

1. connects the site to the user's paid plan the user picked, within the plan's connected-site limit, when it is not in the user's account yet (it has none of the user's licenses and is connected to none of them); the site then appears in that account (sites, remote management, MCP) like a site an administrator connected in Backpack;
2. turns remote management on, with the user as `enabled_by` (a site with roles needs the user to be a `manager`), and, for a site that has checked in, queues a `refresh_inventory` command requested by the user;
3. wakes the site with `sitepulse_check_in=1` (see "WP-Cron"), so it checks in and sends its inventory at once instead of at its next scheduled check-in. While the command waits, the API wakes the site every minute, so a site it could not reach at first is set up as soon as it answers.

- A key is `spc_` and 40 random characters; the API keeps only its SHA-256 hash, one per site. A new key replaces the previous one, and setting the site up deletes it, so each key works once.
- Before it asks for a key, the plugin registers a site that has no license yet, and checks in, so the API can wake it once the key is used.
- While it shows a key, the plugin's settings page asks every few seconds whether the key was used (`has_key` below), for up to 15 minutes, and checks in at once when it was. A site the API cannot reach (behind a firewall, or on a development machine) is set up that way, or at its next check-in.
- A site belongs to one account: a site connected to another account is refused until its administrator disconnects it in the plugin. SitePulse administrators can still connect a site to more plans in Backpack.
- Pro sites (`type` `pro` or `is_pro`) are not connected: they already appear for the account that owns their license.
- Only the key turns remote management on: `remote-management/update` answers 422 to `enabled: true` for a site where it is off. Turning it off needs no key.
- Disconnecting in the plugin removes every connection of the site and switches remote management off for it, which cancels the commands still queued.

The plugin calls these with license-signed requests (see "Signing"), with an empty body:

| Endpoint | Response `data` |
|---|---|
| `POST /api/website/remote/connection` | `{ "connectable", "reason", "has_key", "connections": [ { "account", "plan", "connected_at" } ] }` |
| `POST /api/website/remote/connection/key` | the same, and `key`: a new connection key, shown this once |
| `POST /api/website/remote/connection/disconnect` | the same, after disconnecting |

`connectable` is `false` for a Pro site, with `reason` `pro_site`. `account` is the email of the dashboard user who connected the site, masked (`j***@agency.example`), or `null` when SitePulse connected it; `plan` is the plan's name.

The dashboard calls these with signed portal requests (see "Dashboard (portal) endpoints"); those that change something are accepted once:

| Endpoint | Body | Response |
|---|---|---|
| `POST /api/portal/site-connections` | `license_keys` (the user's licenses) | `data.plans`: `[ { "license_key", "plan", "active", "used", "limit", "remaining", "sites" } ]` for the licenses the API knows; `active` is whether the plan may connect sites now (paid, not cancelled, blocked or expired) and `sites` are the connected domains |
| `POST /api/portal/site-connections/connect` | `email`, `license_keys` (the user's licenses), `license_key` (the plan for a site not in the user's account yet), `domain`, `connection_key` | Sets the site up (see above). `data`: `{ "domain", "connected", "wake", "status", "plans" }`: `connected` is whether the site joined the account now, `wake` how the wake went (as `cron.wake` in "WP-Cron") and `status` the site's remote management status (as `POST /api/portal/remote-management` answers it). 422 with a message for the user when the key does not match the site, the site is Pro or in another account, no plan was picked for a site not in the account, the plan is not active or is full, or the user's role does not allow it |
| `POST /api/portal/site-connections/disconnect` | `license_keys` (the user's licenses), `domain` | `{ "success": true }` after disconnecting the site from those plans; 404 when it is connected to none of them |

## Commands

| Type | `args` | Phase |
|---|---|---|
| `refresh_inventory` | none | 1 |
| `backup_now` | `what`: `full` (default), `database`, `files`, or `plugins` or `themes` (that folder and the database) | 2 |
| `cancel_backup` | none | 2 |
| `apply_backup_preset` | `preset_id`, `name`, `preset` (the API fills these from a saved preset) | 2 |
| `update_plugins` | `plugins` (1–50 plugin files), `backup_first` (default `true`), `backup_wait` (minutes, 5–240, default 60); from a safe update also `backup_parts` (`["plugins"]`) and `safe_update` (its id); after waiting for a safe update that backed up the plugins, `backup_first: false` and `backup_reused` (see "Safe updates") | 3, 5 |
| `update_themes` | `themes` (1–50 stylesheets), `backup_first`, `backup_wait`; from a safe update also `backup_parts` (`["themes"]`) and `safe_update`; after waiting for a safe update that backed up the themes, `backup_first: false` and `backup_reused` | 3, 5 |
| `update_core` | `version`, `backup_first`, `backup_wait` | 3 |
| `set_auto_updates` | `kind` (`plugin` or `theme`), `items` (1–200), `enabled` | 3 |
| `restore_backup` | `safe_update`, `provider` (`updraftplus`), `job` (the backup's nonce), `kind` (`plugin` or `theme`), `item` | 5 |
| `rollback_item` | `safe_update`, `kind`, `item`, `version` (the version installed before the safe update) | 5 |

Only a safe update queues `restore_backup` and `rollback_item` (see "Safe updates"); `commands/create` does not accept them.

### Backup commands

All three answer `no_backup_plugin` when no supported backup plugin is active (the API does not queue them for a site that reported none, and a rollout skips such a site) and `backup_plugin_error` when the backup plugin fails while running the command. After a successful one, the plugin sends a fresh inventory in the same check-in. Results always carry `provider`.

| Command | Result when `done` | Errors |
|---|---|---|
| `backup_now` | `what`, `job`: UpdraftPlus's backup nonce, or the BackWPup job id | `backup_running` (a backup is running or already queued), `invalid_arguments`, `start_failed` (BackWPup did not start its job) |
| `cancel_backup` | `cancelled`: how many running or queued backups were stopped (0 when none) | |
| `apply_backup_preset` | `preset`: its name, `schedules`: the backup schedules afterwards (as in the inventory), `created`: BackWPup jobs created | `invalid_preset` |

- **UpdraftPlus** `backup_now` queues UpdraftPlus's own "Backup Now" (labelled "SitePulse") as a one-off WordPress cron event, so it starts at the next cron run and uses the storage set up in UpdraftPlus. `plugins` and `themes` back up the database and only that folder. `cancel_backup` stops running jobs the way UpdraftPlus's "Delete" link does and removes backups SitePulse queued that have not started.
- **BackWPup** `backup_now` runs BackWPup's own "Backup Now" job, which, like its button, keeps the archive on this server; for `plugins` and `themes` the job saves the database and only that folder. `cancel_backup` aborts the running job.

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
2. **Backup first** (when `backup_first`): a full backup with the managed backup plugin, as `backup_now` makes it, or with `backup_parts` the database and those folders (`plugins` or `themes`). Nothing is updated when the backup fails (`backup_failed`) or takes longer than `backup_wait` minutes (`backup_timeout`). Updates do not need a backup plugin: the API queues `backup_first: false` for a site that reported no supported one, and the plugin skips the backup when none is active as the update starts (`backup_skipped` in the result).
3. **Visual check first** (a safe update's command, with `safe_update`): the plugin asks `POST /api/website/remote/safe-update` whether it may update, every 30 seconds for up to 30 minutes, until the API's visual scan before the update has finished. It asks again while the API cannot be reached. Nothing is updated when the API says to stop (`visual_check_failed`) or the wait runs out (`visual_check_timeout`).
4. **Health baseline.** The plugin loads the home page, wp-admin and, with WooCommerce, the cart and checkout pages, using WordPress's own fatal-error check (the "scrape" request the plugin editor uses). A site that already fails is not updated (`site_unhealthy`), nor a site that cannot load its own home page, because its updates could not be checked (`health_check_unavailable`, typically a host that blocks such "loopback" requests).
5. **One item at a time.** WordPress's upgrader installs the update offered for the item, with maintenance mode only while files are replaced. Then the same pages are loaded again. The update fails its health check when a page shows a PHP fatal error (in any code, such as another plugin that relied on the updated one), answers with a 5xx status it did not have before, stops answering, ends before WordPress's error check can report (where it reported before; a PHP fatal error can end a page that way), or SitePulse's error log records a fatal error in the updated plugin's, theme's or WordPress's files. A plugin or theme that fails is rolled back to its previous version (WordPress 6.3 and later keep a copy during the update) and the remaining items are skipped. WordPress core cannot be rolled back automatically. When the site administrator blocks remote management while an update runs, the items not updated yet are skipped (`blocked`). Throughout, the plugin reports how far it is (`POST /api/website/remote/command-progress`).
6. **Interrupted updates.** When the request updating an item stops before it finishes (a PHP time limit or a fatal error), WordPress still runs its shutdown actions, and SitePulse uses them first:
   - it leaves maintenance mode;
   - it restores the item from WordPress's temporary backup before WordPress deletes that backup;
   - it frees the update for its next step.

   A request that is killed outright cannot do this. Then WordPress keeps the site in maintenance mode for up to 10 minutes and refuses cron requests meanwhile. The next step afterwards removes the maintenance file (`.maintenance`) and restores the item the same way. Either way the item is reported as `interrupted`, with `rolled_back` telling whether it was restored, and the rest is skipped.

Result: `done` when every item is up to date afterwards, otherwise `failed` with the error of the first problem (`update_failed` when an item failed). `result` is `{ "backup": { "provider", "job" } | null, "items": [ { "id", "from", "to", "ok", "error", "rolled_back" } ] }`, plus `"backup_skipped": "no_backup_plugin"` when the command asked for a backup first and no supported backup plugin was active; an item that failed its health check also has `problems`, up to five short descriptions such as `https://example.com/: HTTP 500`. Item errors: `up_to_date` (no update offered; `ok` stays `true`), `not_installed`, `requirements` (the update needs a newer PHP or WordPress), `no_package` (a premium plugin without a valid license), `install_failed` (WordPress could not install it and kept the old version), `health_check_failed`, `interrupted` (the request updating it stopped, for example at a PHP time limit) and `skipped` (not tried after a failure). Items with `up_to_date`, `not_installed`, `requirements` or `no_package` do not stop the others. The plugin reports the update as an `update.completed` event with `initiator` `sitepulse` and `rolled_back` on each item.

`set_auto_updates` turns WordPress's own automatic updates on or off for installed plugins or themes (the "Enable auto-updates" links in wp-admin) and answers at once: `result` is `{ "kind", "enabled", "items" }` with the items whose setting changed.

**Command states.** `queued` → `sent` (delivered at a check-in) → `running` (started; update commands only) → `done` or `failed`; or `expired` and `cancelled`. Each command records `requested_by`, `attempts`, `started_at` and `retry_of`.

- **Redelivery:** a command that was never delivered is queued again for another 15 minutes, up to 3 attempts (`attempts`), within 45 minutes of its creation. After that, it expires, so an old request never comes back to life.
- **No answer:** a delivered command without a result expires with `no_result` after 1 hour, or 6 hours for update and recovery (`restore_backup`, `rollback_item`) commands. Such a command is never resent automatically, because the site may have run it.
- **Retry:** `commands/retry` queues a copy of a `failed`, `expired` or `cancelled` command as a new command with `retry_of` set to the original.
- **One update at a time:** a site runs one update or recovery command at a time (the plugin answers `update_running` to another), so a check-in delivers at most one of them, and none while another is `sent` or `running`. Update commands waiting their turn stay `queued`, oldest first, and their 15 minutes start again at each check-in, so they wait as long as the update before them takes; they expire only when the site stops checking in. A safe update that runs or waits for a choice holds the other update and recovery commands the same way (see "Safe updates"). Other commands are delivered as usual.

A command expires 15 minutes after it is created unless it was delivered. The plugin runs up to 20 commands per check-in, in WordPress's background (cron) request. It remembers the ids of the last 50 commands so it never runs one twice, answers `expired` for a command whose `expires_at` has passed, and answers `unsupported_command` for types it does not know.

### Safe updates

A safe update updates one plugin or theme between two visual scans of the site, after a backup, so the dashboard can show what the update changed and undo it:

1. **Before.** The dashboard asks for it (`safe-updates/create`). At the same time:
   - the API scans the site's visual monitoring pages (the home page when it monitors none yet, even while its scheduled scans are off) on a comparison branch of the safe update's own, so that branch's baselines are the site right before the update;
   - the API queues the update command, with `backup_parts` (`plugins` or `themes`) and `safe_update`, so the site backs up that folder and the database while the scan runs. A site without a supported backup plugin updates without a backup.
2. **Update.** Once its backup is done, the site waits for the scan (see "Update commands", step 3), then updates as any update command does, with the health checks and the rollback of a plugin or theme that breaks a page.
3. **After.** When the update command is done, the API scans the same pages on the same branch, so each page is compared with its capture before the update.
4. **Verdict.** `passed` when no page changed by 20% or more of its pixels. Otherwise `changed`: a page changed by 20% or more, or a page that loaded before no longer does. The API sends the `visual_change` notification, and the dashboard shows each page before and after the update, with its differences, and offers:
   - **Keep the update** (`safe-updates/keep`);
   - **Restore** (`safe-updates/recover`): with a backup UpdraftPlus made, the API queues `restore_backup`, which restores the item's folder and the database from the backup taken before the update. Without one (no backup plugin, or BackWPup), it queues `rollback_item`, which reinstalls the version that was installed. Afterwards the API scans the pages once more and records how far they still are from the captures before the update (`check`).

**Statuses:** `preparing` (the scan before and the backup run) → `updating` → `checking` → `passed` or `changed` → `kept`, or `restoring` → `restored` or `restore_failed` (which may be retried); or `failed`, with `error`:

| Error | Meaning |
|---|---|
| `visual_check_failed` | The scan before the update failed; nothing was updated |
| `visual_check_timeout` | The scan before the update took more than 30 minutes; nothing was updated |
| `update_failed`, `backup_failed`, `backup_timeout`, … | The update command's error; a plugin or theme that broke a page was rolled back |
| `update_expired`, `update_cancelled` | The update command was never run |
| `after_check_failed` | The scan after the update did not finish within 30 minutes, or the site updated without waiting for the scan before it; the update stays, and can still be undone |
| `not_queued` | The update command could not be queued |

A site runs one safe update at a time, and a new one waits until the last one is no longer waiting for a choice. While a safe update runs or waits for the user's choice, it holds the site's other updates and recoveries: they stay `queued` (their 15 minutes start again at each check-in) until it is done, so its scans compare only its own update, and restoring its backup never puts the database back under an update made since; the API also queues no automatic update meanwhile. A plugin or theme update that waited for a safe update which backed up the same folder (and the database) before it makes no backup of its own: the API delivers it with `backup_first: false` and `backup_reused: { "provider", "job", "safe_update" }`, the safe update's backup. The API gives up on a scan that has not finished after 30 minutes and on a command that expired, in its 15-minute check.

**Recovery commands.** The plugin runs them in WordPress cron, like updates, reports them `running`, then `done` or `failed`. Both answer `invalid_arguments`, `update_running` (an update or another recovery runs), `cannot_update` (WordPress may not write files without FTP credentials) and `interrupted` (the request running it stopped before it finished, for example at a PHP time limit; it is not run again):

- `restore_backup` restores, through UpdraftPlus's own restorer, the item's folder and every database table from the backup whose nonce is `job`. SitePulse keeps its own settings (its settings and license, remote management state, signing key, connection, queued events, activity log and update measurements) as they were before the restore, also when the request stops mid-restore. When the backup is not in UpdraftPlus's history (a database restored since forgets the backups made after it), UpdraftPlus rebuilds the history from its backup folder first. Errors: `backup_not_found` (UpdraftPlus no longer has that backup, or it lacks the folder or the database), `restore_unsupported` (UpdraftPlus 1.26 or later is not active) and `restore_failed` (UpdraftPlus's error, or a pointer to its restore log, in `result.message`). `result` is `{ "restored": [ "plugins", "db" ] }`.
- `rollback_item` installs the version `version` of the plugin or theme from WordPress.org through WordPress's own upgrader, over the current one, in maintenance mode. Errors: `not_installed`, `no_package` (WordPress's update check does not list the item with a WordPress.org package, as for a premium plugin; a plugin of a single file; or WordPress.org has no such version) and `install_failed` (`result` still says which version is installed). `result` is `{ "kind", "id", "from", "to" }`.

### `POST /api/website/remote/safe-update`

During a safe update, once its backup is done, the plugin asks whether it may update: `{ "command": "<the update command's id>" }`. Response: `{ "success": true, "proceed": bool, "stop": bool }`. `proceed` once the scan before the update has finished; `stop` when it failed, took more than 30 minutes, or the command is not a safe update's.

### `POST /api/website/remote/command-progress`

While it runs an update or recovery command, the plugin says how far it is whenever that changes (a new phase, the backup's progress, the next item): `{ "id": "<command id>", "progress": { "phase", "percent", "backup", "done", "total", "item" } }`, answered `{ "success": true }`. The API keeps the latest report of a delivered (`sent` or `running`) update or recovery command of the site, with the time it arrived (`progress_at`), and ignores others.

- `phase`: `backup`, `visual` (waiting for the safe update's scan before the update), `baseline`, `updating`, or for recovery commands `restoring` and `installing`.
- `percent`: how far the update itself is, 0–100: 0 while it waits for its backup or the visual check, 5 during the health baseline, then 10 plus the share of items done; `null` when the plugin cannot tell (a recovery).
- `backup`: during the `backup` phase, `{ "percent", "stage" }`: the backup plugin's own progress (UpdraftPlus's six-stage progress bar, BackWPup's step percent; `null` when it does not say) and its stage (UpdraftPlus's job status, BackWPup's step, `queued` before it starts); otherwise `null`.
- `done` and `total`: items updated so far, of all; `item`: the one updating now (or the one a recovery acts on).

A report that does not arrive is sent again with the next change; the command's result always says how it ended.

## Dashboard (portal) endpoints

The SitePulse dashboard calls these from trusted IPs with the existing portal authentication (`X-API-Key` and the HMAC `X-Portal-Auth` header), plus the site's `domain` and `license_key` in the body. Only dashboard users who own the license can reach a site; the license alone never creates commands. Every endpoint that changes something takes `requested_by`, the email of the dashboard user acting, which the site's roles must allow (see "Roles"). A signed request that changes something is accepted once: the same `X-Portal-Auth` again (a replay within its five minutes) gets `409` with "This request was already received.".

| Endpoint | Body | Purpose |
|---|---|---|
| `POST /api/portal/remote-management` | — | Status: switch, `active`, last check-in, what the site reported, backup warnings, `cron` (see "WP-Cron") |
| `POST /api/portal/remote-management/update` | `enabled`, `requested_by` | Turn remote management off; turning it on takes the site's connection key (see "Connecting a site to an account"), so `enabled: true` answers 422 while it is off |
| `POST /api/portal/remote-management/inventory` | — | Latest software and backup inventory, backup warnings |
| `POST /api/portal/remote-management/backup-provider` | `backup_provider` (`updraftplus`, `backwpup` or `null`), `requested_by` | Pick the backup plugin to manage when both are active |
| `POST /api/portal/remote-management/update-policy` | — | The site's update policy (defaults until it is changed) |
| `POST /api/portal/remote-management/update-policy/save` | `policy` (the update policy format), `requested_by` | Change the site's update policy |
| `POST /api/portal/remote-management/events` | `limit` (≤ 100), `before` (event time) | Event history, newest first |
| `POST /api/portal/remote-management/commands` | `limit` (≤ 100) | Recent commands and their results, with `progress` and `progress_at` as the site last reported them (see `command-progress`), and `held_by` (`{ "id", "name", "status" }` of the safe update a queued update or recovery waits for, or `null`) |
| `POST /api/portal/remote-management/commands/create` | `type`, `args`, `requested_by` | Queue a command (requires `active`); `apply_backup_preset` takes `args.preset_id`, a preset owned by `requested_by` |
| `POST /api/portal/remote-management/commands/cancel` | `id`, `requested_by` | Cancel a command that was not delivered yet |
| `POST /api/portal/remote-management/commands/retry` | `id`, `requested_by` | Queue a copy of a `failed`, `expired` or `cancelled` command (requires `active`) |
| `POST /api/portal/remote-management/timeline` | `limit` (≤ 100, default 50), `before` (time), `categories` (optional list) | The site's activity timeline, newest first |
| `POST /api/portal/remote-management/reports` | — | The site's stored monthly reports |
| `POST /api/portal/remote-management/reports/generate` | `month` (`YYYY-MM`), `requested_by` | Build (or rebuild) a month's report and return it |
| `POST /api/portal/remote-management/reports/download` | `month` | A link to the report as a PDF, valid for 24 hours |
| `POST /api/portal/remote-management/reports/send` | `month`, `requested_by` | Email the report to the site's report recipients now |
| `POST /api/portal/remote-management/notifications` | — | Notification settings and the 20 latest notifications |
| `POST /api/portal/remote-management/notifications/save` | `notifications` (the settings format), `rotate_webhook_secret` (optional), `requested_by` | Change the notification settings |
| `POST /api/portal/remote-management/notifications/test` | `requested_by` | Send a test message to every configured channel |
| `POST /api/portal/remote-management/updates/overview` | `sites` (1–100 `{ "domain", "license_key" }`) | The updates each site offers, see "Updates across sites" |
| `POST /api/portal/remote-management/updates/queue` | `sites`, `requested_by`, `plugins` (files), `themes` (stylesheets), `core` (boolean) | Queue the chosen updates on every site that offers them, see "Updates across sites" |
| `POST /api/portal/remote-management/safe-updates` | `limit` (≤ 50, default 10) | The site's safe updates, newest first |
| `POST /api/portal/remote-management/safe-updates/create` | `kind` (`plugin` or `theme`), `item` (file or stylesheet), `requested_by` | Start a safe update (see "Safe updates"); 409 with a message when the site is not active, its SitePulse plugin does not run safe updates yet (no `safe_update` in its check-in `features`), runs one already or has one waiting for a choice, or offers no installable update for the item |
| `POST /api/portal/remote-management/safe-updates/show` | `id` | A safe update with `pages`: each page of its scan after the update, the big changes first, with `url`, `status`, `diff_percent`, `big`, `error`, and `images` (`before`, `after`, `diff`: public URLs, `null` when missing or expired) |
| `POST /api/portal/remote-management/safe-updates/keep` | `id`, `requested_by` | Keep the update after a big change |
| `POST /api/portal/remote-management/safe-updates/recover` | `id`, `requested_by` | Restore the backup taken before the update, or roll it back (`recovery` says which) |
| `POST /api/portal/remote-management/safe-updates/waiting` | `sites` (1–100 `{ "domain", "license_key" }`, instead of `domain` and `license_key`) | Safe updates of those sites waiting for a choice after a big change, and those running now: `{ "safe_updates": [ { "domain", … } ], "running": [ { "domain", … } ], "unknown_sites": [...] }` |
| `POST /api/portal/remote-management/activity` | `sites`, `limit` (≤ 100, default 50), `before` (time), `categories` (optional list) | The activity of all these sites in one list, newest first, see "Updates across sites" |
| `POST /api/portal/remote-management/alerts` | `sites` (1–100 `{ "domain", "license_key" }`, instead of `domain` and `license_key`), `after` (the id of the last alert read), `limit` (≤ 200, default 100) | Alerts of those sites after `after`, oldest first: `{ "alerts": [ { "id", "domain", "trigger", "subject", "message", "data", "created_at" } ], "unknown_sites": [...] }` |
| `POST /api/portal/remote-management/roles` | — | Who may view or manage the site |
| `POST /api/portal/remote-management/roles/save` | `roles`, `requested_by` | Change the site's roles |
| `POST /api/portal/remote-management/rotate-key` | `requested_by` | Give the site a new signing key (see "Key rotation") |

Backup presets belong to a dashboard user (their email) instead of a site, so these endpoints take `email` instead of `domain` and `license_key`:

| Endpoint | Body | Purpose |
|---|---|---|
| `POST /api/portal/remote-management/presets` | `email` | The user's presets, by name |
| `POST /api/portal/remote-management/presets/save` | `email`, `id` (to change one), `name` (unique per user), `settings` (the preset format) | Create or change a preset |
| `POST /api/portal/remote-management/presets/delete` | `email`, `id` | Delete a preset; commands already queued keep their copy |

Rollouts also belong to a dashboard user and span several sites, each named with its `domain` and `license_key`:

| Endpoint | Body | Purpose |
|---|---|---|
| `POST /api/portal/remote-management/rollouts` | `email` | The user's rollouts, newest first (up to 50) |
| `POST /api/portal/remote-management/rollouts/create` | `email`, `type`, `args`, `sites` (2–100 `{ "domain", "license_key" }`), `canary` (default 2), `wait_minutes` (0–1440, default 0) | Start a rollout |
| `POST /api/portal/remote-management/rollouts/show` | `email`, `id` | A rollout with each site's command and status |
| `POST /api/portal/remote-management/rollouts/cancel` | `email`, `id` | Stop a rollout and cancel its commands that were not delivered yet |

### Roles

A site has no roles until the dashboard saves some; until then, every dashboard user who reaches the site may manage it. Once saved, `roles` is a list of `{ "email", "role" }`:

| Role | May |
|---|---|
| `viewer` | Read only (no endpoint that takes `requested_by`) |
| `manager` | Everything that changes the site except roles and the signing key: the switch, backup plugin, update policy, commands and retries, reports, notifications, rollouts |
| `admin` | Everything, including `roles/save` and `rotate-key` |

- **Refusals:** a `requested_by` without the role an endpoint needs gets 403 `not_allowed`.
- **Saving roles:** `roles/save` must keep at least one `admin`. Once roles exist, only an `admin` may change them. The first save must list `requested_by` as an `admin`, so nobody locks themselves out.
- **Commands:** they keep `requested_by`, as before.

### Key rotation

`rotate-key` (admin) gives the site a new random signing key, for example when a backup holding the site's license may have leaked. The license itself does not change: free license keys are derived from the site and checked that way by every SitePulse service, and Pro keys belong to the license account. A leaked license no longer works against remote management.

1. The API keeps the new key as pending. The site goes on signing with its current key (the license, or an earlier rotated key), which stays valid.
2. At the next check-in the API sends `new_key`, and the site confirms it with `POST /api/website/remote/key`, signed with the new key.
3. On that confirmation the API makes the new key the site's signing key. The old one keeps working for 7 days, only so that a site that never received the confirmation can recover. At its next check-in the API offers the new key again, and the confirmation succeeds.

Rotating again before the site confirmed replaces the pending key. The timeline shows a `key.rotated` entry in the `key` category, with `requested_by`.

### Rollouts

A rollout queues the same command on several sites, a few at a time:

1. **Canary:** the command is queued on the first `canary` sites (1 to one less than the number of sites).
2. **Wait:** when every canary command is `done`, the rollout waits `wait_minutes`. As soon as a canary command fails, expires or is cancelled, or when every canary site was skipped, the rollout stops (`halted`) and the other sites are not touched; canary commands already queued are left as they are.
3. **The rest:** the command is queued on every other site. The rollout is `completed` when all of them have finished, whatever their results.

- **Statuses:** `canary`, `waiting`, `rolling_out`, `completed`, `halted`, `cancelled`.
- **Sites:** each site is `pending`, `queued`, `done`, `failed`, `skipped` (with `reason`: `not_active`, `not_allowed`, or `no_backup_plugin` for a backup command, checked when its stage starts) or `cancelled`, and carries its `command` id.
- **Commands:** `type` and `args` are those of `commands/create`, except `cancel_backup` and `apply_backup_preset`.
- **Progress:** the API advances a rollout when a command result arrives and every 15 minutes.
- **Retention:** finished rollouts are kept 90 days.

### Updates across sites

The dashboard shows what all of a user's sites can update, and updates chosen items on all of them at once. These endpoints take `sites` like `alerts`: up to 100 `{ "domain", "license_key" }`, each answered once, with the ones that do not match listed in `unknown_sites`.

`updates/overview` answers `{ "sites": [...], "unknown_sites": [...] }`. Each site:

```json
{
  "domain": "shop.example", "active": true, "enabled": true, "blocked": false, "last_check_in_at": "2026-10-16T14:43:03+00:00",
  "backup": {
    "provider": "updraftplus", "last": { "time": "2026-10-16T02:00:00Z", "ok": true }, "next": "2026-10-17T02:00:00Z", "running": null,
    "schedules": [ { "name": "Files", "contents": ["files"], "frequency": "weekly", "keep": 4, "destinations": ["googledrive"] } ],
    "warnings": [ { "code": "overdue", "message": "…" } ]
  },
  "can_update": true, "reason": null, "safe_update": true, "collected_at": "2026-10-16T02:56:19+00:00",
  "plugins": [ { "id": "elementor/elementor.php", "name": "Elementor", "from": "3.30.1", "to": "3.31.0", "installable": true, "security": false, "excluded": false } ],
  "themes": [ { "id": "astra", "name": "Astra", "from": "4.11.0", "to": "4.11.1", "installable": true, "security": false, "excluded": false } ],
  "core": { "id": "wordpress", "name": "WordPress", "from": "7.1.2", "to": "7.1.3", "installable": true, "security": false, "excluded": false },
  "pending": { "plugins": [ "woocommerce/woocommerce.php" ], "themes": [], "core": false },
  "progress": { "type": "update_plugins", "phase": "backup", "percent": 0, "backup": { "percent": 40, "stage": "filescreating" }, "done": 0, "total": 1, "item": null }
}
```

`enabled` is whether the dashboard turned remote management on, `blocked` whether the site's administrator blocked it, and `active` whether it works now (the site has also checked in). `backup` is the managed backup plugin (`provider` is `null` without one: the site can be updated but not backed up) and what the inventory says about backups: the `last` one (`null` until one is reported), the `next` scheduled one, the one `running` now (`{ "name", "stage" }` or `null`) and the `schedules`, with the site's `warnings` (see "Backup warnings"). It lists every update in the site's last inventory, whatever its update policy. `installable` is `false` for an update without a download package or that needs a newer PHP or WordPress than the site has; `security` says it fixes a vulnerability SitePulse found in the installed version; `excluded` that the site's update policy excludes it (see "Update policy"). `core` is the newest WordPress release offered and newer than the installed one, or `null`. `safe_update` is whether the site's plugin runs safe updates (`features` at check-in). `pending` holds what update commands that are `queued`, `sent` or `running` already install. `progress` is how far the running one is, as the site last reported it (see `command-progress`), with its `type`; `null` when none runs or it has not reported yet.

`updates/queue` queues, on each site, the chosen items it offers and can install, leaving out the ones already `pending` and the ones its update policy excludes, and answers `{ "sites": [ { "domain", "status", "reason", "commands", "items" } ], "unknown_sites": [...] }`. `status` is `queued` (with the new command ids and the number of items) or `skipped` with `reason`: `not_active`, `not_allowed` (the user's role on the site is below manager), `no_inventory`, `cannot_update` (`can_update` is `false`), `already_updating` (what it offers of the choice is already `pending`), `excluded` (its update policy excludes the rest) or `nothing_to_update`. A site gets one `update_plugins` command per 50 plugins, one `update_themes` command and one `update_core` command for the newest release, with the site's `backup_wait`; only its first command backs up first (when its policy says so and the site has a supported backup plugin), since the commands run one after the other (see "One update at a time"). At least one item or `core` must be chosen.

`updates/overview` also gives each site's `cron` (see "WP-Cron").

A safe update answers `{ "id", "kind", "item", "name", "from", "to", "status", "error", "requested_by", "backup": { "provider", "job" } | null, "recovery": "restore_backup" | "rollback_item" | null, "threshold_percent": 20, "pages_changed", "max_diff_percent", "check": { "status", "pages_changed", "max_diff_percent" } | null, "decided_by", "decided_at", "created_at", "finished_at", "progress" }`.

`progress` says how far each step is, for progress bars: `before`, `after` and `check` are the scans (`{ "status", "done", "total", "percent" }`: pages scanned so far, as the visual worker reports them; `null` before the scan exists); `backup` is `{ "status": "waiting" | "running" | "done" | "skipped" | "failed", "percent", "stage" }` from the update command's reports (`skipped` without a backup plugin); `update` and `undo` are the update and recovery commands (`{ "status": "waiting" | "running" | "done" | "failed", "phase", "percent", "item" }`; the update waits while its backup and the scan before it run; `null` without such a command).

`activity` answers `{ "activity": [ { "domain", "time", "category", "type", "summary", "data" } ], "unknown_sites": [...] }`: the entries of each site's timeline (see "Timeline", same `limit`, `before` and `categories`) merged, newest first, each with its site's `domain`.

### WP-Cron

The plugin checks in, runs commands, takes each update step and resumes backups in WP-Cron, which WordPress runs only when the site gets visitors, unless a server cron job runs it (often with `DISABLE_WP_CRON`). A quiet site would therefore pick up commands late and stop between update steps. Requesting a site's `wp-cron.php` runs its due tasks even with `DISABLE_WP_CRON`, so every minute the API wakes:

- sites with a command `queued`, `sent` or `running`, so a command starts within about a minute and every update step and backup resumption follows;
- sites whose last check-in is older than twice their check-in interval (10 minutes).

Only active sites (remote management on, not blocked on the site, and checked in at least once) are woken: a site with a command at most once a minute, a late site at most once per check-in interval (so a site that is gone costs a request every 5 minutes), 50 at a time, with an 8-second timeout and no redirects. A site is also woken, with `sitepulse_check_in=1`, as soon as it is set up with its connection key (see "Connecting a site to an account"). The request carries no `doing_wp_cron` (WordPress runs nothing when that value does not match the lock it set itself). For a site with a command still `queued` it adds `sitepulse_check_in=1`: the plugin then moves its next check-in to that cron run, so the command is delivered at once, unless a wake made it check in less than a minute ago (anyone can request that address, so the flag does nothing else). The API requests the `cron.url` the site reported when its host is the site's domain (with or without `www.`) and its path ends in `/wp-cron.php` with no query, and `https://<domain>/wp-cron.php` otherwise, so a site cannot point the API at another server or page. It resolves the host itself, refuses ports other than 80 and 443 (`blocked_port`), hosts that do not resolve (`unresolved`) and any address that is not public, such as private, loopback, link-local or reserved ones (`blocked_host`), then connects to the address it checked, so a changed DNS answer cannot send the request elsewhere; it reads at most 64 KB of the answer. A development API (`REMOTE_MANAGEMENT_WAKE_PRIVATE_HOSTS=true`) may wake private addresses and other ports. The wake runs in the API's scheduler every minute: a run starts requests for up to 20 seconds and at most 2,000 sites, the sites with a command first, then the longest silent, and the next run goes on with the sites left.

The status and `updates/overview` give each site's `cron`:

```json
{ "disabled": false, "late": 40, "wake": { "at": "2026-10-16T17:02:00Z", "ok": true, "status": 200, "error": null }, "warning": null }
```

`disabled` and `late` are from the last check-in (`null` before plugin 1.5); `wake` is the last wake (`error`: `unreachable`, `http_<status>`, `blocked_port`, `unresolved` or `blocked_host`). `warning` is `unreachable` when the last wake failed and the site has not checked in since (the site cannot be woken, so it waits for visitors), `not_running` when a task had waited an hour or more at the last check-in (WP-Cron does not run by itself, so scheduled backups run late), or `null`. Both are fixed with a server cron job that requests `wp-cron.php` every 5 minutes. Meanwhile, when its check-in is 10 minutes late, the plugin checks in at the end of a wp-admin page an administrator opens, after the page is sent.

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

`exclude` lists plugin files and theme stylesheets that are never updated automatically or by updates across sites (`updates/queue`), such as `woocommerce/woocommerce.php`; the dashboard can still update them on the site's own page (`commands/create`). `wait_days` (0–30) holds an update until that many days after SitePulse first saw it offered on any site, so quick fix releases arrive first; security updates do not wait. `window` is the hours of the day, in the site's time zone, in which automatic updates may start (`start` 0–23, `end` 1–24; a window such as 22–4 wraps past midnight). `backup_first` and `backup_wait` apply to automatic updates; a site without a supported backup plugin is updated without a backup. `automatic: false` turns automatic updates off; the dashboard can still queue updates.

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
| `key` | `key.rotated` (with `requested_by`) | key rotations |
| `command` | `command.queued` (with `requested_by`), `command.started`, `command.finished` (with `status`, `error`, and `visual_scan`: `{ "status", "pages_total", "pages_changed" }` when one ran) | commands |
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

Each site has its notification settings. Until the dashboard saves them, there are no channels and nothing is sent, but every alert whose trigger is on is still **recorded** (status `recorded`). The SitePulse dashboard reads recorded alerts with `alerts` and sends them through its own Slack connection:

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
| `update_failed` | An update command (`update_plugins`, `update_themes`, `update_core`) finishes `failed`, including rolled-back updates, or undoing a safe update failed (`restore_backup`, `rollback_item`) | Once per command |
| `backup_failed` | A `backup.completed` event with `ok: false`, or a `backup_now` command that failed | Once per event or command |
| `backup_overdue` | The `overdue` or `no_backup` backup warning appears | Once until the warning clears |
| `check_ins_stopped` | An active site has not checked in for 2 hours: the site may be down, or WP-Cron is not running | Once, plus a second message when check-ins resume, whose `data` has `"resolved": true` |
| `fatal_error` | `error.fatal` events arrive | At most once an hour per site, summarizing them |
| `update_slowdown` | An update slowdown | Once per measurement |
| `site_slowdown` | A site slowdown | Once per day |
| `visual_change` | A safe update changed a page by 20% or more, or a page no longer loads (see "Safe updates"); `data` holds `safe_update`, `kind`, `item`, `from`, `to` and the changed `pages` | Once per safe update |

The API checks `backup_overdue` and `check_ins_stopped` every 15 minutes, only for sites with remote management switched on. Deliveries are queued and tried up to 3 times per channel.

- **Email:** plain text, from the API's mail sender.
- **Slack:** a message with the subject and text.
- **Webhook:** `POST` JSON `{ "id", "trigger", "site": { "domain", "name" }, "subject", "message", "data", "occurred_at" }` with these headers:
  - `X-SitePulse-Event` (the trigger) and `X-SitePulse-Timestamp` (Unix seconds);
  - `X-SitePulse-Signature`: `sha256=` followed by the hexadecimal HMAC-SHA256 of `<timestamp>.<body>`, keyed with `webhook_secret`.

  Redirects are not followed, the timeout is 10 seconds, and any 2xx status counts as delivered.

The notifications log keeps `id`, `trigger`, `subject`, `message`, `status` (`pending`, `sent`, `partial`, `failed`, or `recorded` without channels), `channels` (each channel's `pending`, `sent` or `failed`), `created_at` and `sent_at`. `notifications`, and `notifications/save`, answer `{ "settings", "triggers", "recent" }`, where `recent` holds the 20 latest log entries. `notifications/test` sends a `test` notification right away and returns its log entry, or 422 when the site has no channel.

## Retention

The API keeps events, finished commands, notifications, finished rollouts and finished safe updates for 90 days, monthly reports for 13 months (their PDF links expire after 24 hours), and only the latest inventory per site. The plugin keeps up to 200 undelivered events (the oldest are dropped first), the last 100 remote-management activity entries and up to 50 pending update measurements; it sends events only while remote management is active, up to 50 per request. Uninstalling the plugin deletes all of them.

## Development

`scripts/remote-api-mock.php`, in the plugin repository, is a small stand-in for the API for local development. Run it with `SITEPULSE_MOCK_LICENSE=<the site's license> php -S 127.0.0.1:8099 scripts/remote-api-mock.php`, and define `SITEPULSE_REMOTE_API_BASE` as `http://127.0.0.1:8099` in the site's `wp-config.php`.

- **The site endpoints:** it checks and signs requests as the API does, and answers check-ins as active every minute.
- **Commands:** `GET /mock/queue?type=…&args=<JSON>` queues one. Like the API, it delivers one update command at a time: the next waits until the site reports the previous one `done` or `failed`.
- **Key rotation:** `GET /mock/rotate?key=…` offers a new signing key at the next check-in.
- **State:** `GET /mock/state` shows what the site sent (check-ins, inventory, events, command results). It is kept in a JSON file, `SITEPULSE_MOCK_STATE`, which defaults to the system's temporary folder.

It is not shipped with the plugin.

## Phases

1. **Read-only and switches:** signed endpoints, dashboard switch, backup provider detection, local block switch, activity log, inventory, events, `refresh_inventory`.
2. **Backups:** `backup_now`, `cancel_backup`, backup presets, warnings for backups kept only on the same server, unusually small backups and overdue backups.
3. **Updates:** `update_plugins`, `update_themes`, `update_core` and `set_auto_updates`; update policies (security and minor updates automatic, major updates manual, 3-day wait, 1–5 am window) with automatic updates; pre-checks, a backup first, one update at a time with health checks and rollback; a visual check after updates; warnings about security updates and other management tools.
4. **Extras:** performance impact of each update, daily performance and fatal errors, site activity timeline, monthly client reports, failure notifications by email, Slack and webhook.
5. **Safe updates:** a plugin or theme updated between two visual scans after a backup of that folder and the database, the `visual_change` notification, and restoring that backup (UpdraftPlus) or rolling the item back.

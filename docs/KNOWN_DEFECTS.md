# Regression fixes

The defects below were reproduced with expected-correct tests, then repaired. Their regressions now pass in the normal `composer test` and `npm test` suites. The old intentionally failing commands and `tests/regressions/` files have been removed.

## PHP

| Fixed behavior | Regression coverage |
| --- | --- |
| Dismissing onboarding persists a dismissed state. | [CoreRegressionTest](php/CoreRegressionTest.php) |
| Already-prefixed option names retain their prefix, and existing empty options can be updated. | [CoreRegressionTest](php/CoreRegressionTest.php) |
| Failed website reports do not advance the last successful-request timestamp used by fallback scheduling. | [ApiRegressionTest](php/ApiRegressionTest.php) |
| A first vulnerability scan uses the license issued during its registration. | [ApiRegressionTest](php/ApiRegressionTest.php) |
| Transient AI transport failures preserve pending polling; terminal results stop it. | [ApiRegressionTest](php/ApiRegressionTest.php), [CronCoverageTest](php/CronCoverageTest.php) |
| Disabling external API collection prevents registration on a first vulnerability scan. | [ApiRegressionTest](php/ApiRegressionTest.php) |
| A queued vulnerability scan preserves its wait state and previous findings, including when the API omits a message. | [ApiCoverageTest](php/ApiCoverageTest.php), [RestCoverageTest](php/RestCoverageTest.php) |
| A memory-diagnostic exception returns an error and closes the output buffer opened by that request. | [ApiVariantsTest](php/ApiVariantsTest.php) |
| Frontend load durations choose the correct seconds/minutes/hours unit without converting a formatted string back to a number. | [FrontendAdminBarTest](php/FrontendAdminBarTest.php) |
| Fatal-error recovery messages retain their format placeholders, and SitePulse's own fatal notifications are not blocked by its email filter. | [ErrorHandlerRecoveryTest](php/ErrorHandlerRecoveryTest.php) |
| An uncaught exception on a page WordPress checks for fatal errors (after a change in its plugin or theme editor) reaches that check as the fatal error it is, and is logged once. | [ErrorHandlerVariantsTest](php/ErrorHandlerVariantsTest.php) |
| Translation loading uses the plugin translations directory on disk. | [LifecycleTest](php/LifecycleTest.php) |
| A failed configuration rewrite returns an error before a duplicate SAVEQUERIES declaration can be added. | [PluginAdditionalBehaviorTest](php/PluginAdditionalBehaviorTest.php) |
| A failed query diagnostic displays its error and retains the SAVEQUERIES enable action. | [DiagnosticTemplateCoverageTest](php/DiagnosticTemplateCoverageTest.php) |
| Warnings, rankings and scores use the last 24 hours and each plugin's own time per page load, so code that was fixed, removed or only runs often is not flagged. | [RecentDashboardTest](php/RecentDashboardTest.php), [BackendTest](php/BackendTest.php) |
| Recent figures combine the current 24-hour window with the part of the previous one still inside it, and count each page load once. | [RecentActivityTest](php/RecentActivityTest.php), [ProfilerTest](php/ProfilerTest.php) |
| Saved activity of code that has not run for two days is removed. | [ProfilerTest](php/ProfilerTest.php) |
| Slow external requests older than 24 hours are neither warnings nor diagnosis input; the External Requests log keeps them. | [RecentDashboardTest](php/RecentDashboardTest.php), [RecentActivityTest](php/RecentActivityTest.php), [ApiServiceTest](php/ApiServiceTest.php) |
| Reports and AI diagnoses describe the last 24 hours, and an oversized report keeps the slowest entries. | [ApiServiceTest](php/ApiServiceTest.php), [ApiCoverageTest](php/ApiCoverageTest.php) |
| Plugin averages cover the last 24 hours and the slowest plugins are listed first. | [PluginProfilerTest](php/PluginProfilerTest.php) |
| The dashboard assistant shows the slowest item's time and takes the heaviest plugin from its recent average. | [PetAssistantTemplateTest](php/PetAssistantTemplateTest.php) |
| An UpdraftPlus run that hit an error but will resume is not reported as a failed backup (which sent a "Backup failed" alert); only the backup's last run is. | [RemoteBackupsTest](php/RemoteBackupsTest.php) |
| The Remote management connection box and the check-in status say why a request to SitePulse failed (unreachable, HTTP status, refused, unsigned or unreadable answer) instead of one generic sentence. | [RemoteConnectionTest](php/RemoteConnectionTest.php), [RemoteManagementTest](php/RemoteManagementTest.php) |
| An AI diagnosis or its status check on a site without a license key (one that reached the API before, so it skipped its first-request registration) registers the site first instead of sending no key and failing with HTTP 422; a refused registration reports the API's reason without requesting the diagnosis. | [ApiServiceTest](php/ApiServiceTest.php) |
| A vulnerability scan on a site without a license key registers it first; a refused scan says why (daily limit, Pro license, invalid field, rate limit, temporary unavailability or the API's reason) instead of "Free scan requests exhausted"; a clean scan, which still returns its sections and summary, says no known vulnerabilities were found instead of "Vulnerabilities detected". | [ApiServiceTest](php/ApiServiceTest.php), [RestApiTest](php/RestApiTest.php) |
| A queued vulnerability scan is remembered from when it was first queued and shown, with when to check again, until its results arrive; Easy Mode no longer calls a never-scanned site safe and shows each finding's stored severity instead of "Unknown". | [RestCoverageTest](php/RestCoverageTest.php), [DiagnosticTemplateCoverageTest](php/DiagnosticTemplateCoverageTest.php) |
| A failed AI diagnosis (which the API reports with success false) is stored with its reason and shown with a retry button instead of "Waiting in queue" forever, and one already in progress is shown as in progress; a server error or unreadable answer keeps the diagnosis and the previous report as they were instead of marking it failed; a status check that finds nothing in progress forgets the stale request. | [ApiServiceTest](php/ApiServiceTest.php), [CronTest](php/CronTest.php), [RestApiTest](php/RestApiTest.php), [DiagnosticTemplateCoverageTest](php/DiagnosticTemplateCoverageTest.php) |
| An update's backup phase ends only with its own UpdraftPlus backup: another backup that finished first is waited past (until `backup_wait`, then `backup_timeout`), and a backup UpdraftPlus's history does not hold with the database (and, for a safe update, the folder) stops the update with `backup_failed`, instead of updating without a backup and reporting the one that never ran. | [RemoteUpdatesTest](php/RemoteUpdatesTest.php) |
| An update saves each step before it reports its progress, so a request that ends while reporting no longer rolls back an item that was updated and passed its health check. | [RemoteUpdatesTest](php/RemoteUpdatesTest.php) |
| An update or recovery that could not start within an hour of its arrival answers `expired` instead of starting, so a late restore no longer takes the database back unexpectedly. | [RemoteUpdatesTest](php/RemoteUpdatesTest.php), [RemoteRecoveryTest](php/RemoteRecoveryTest.php) |
| A restore UpdraftPlus finished is not failed by its warnings or by problems recorded before it started; only errors recorded during the restore fail it. | [RemoteRecoveryTest](php/RemoteRecoveryTest.php) |
| A rollback whose install throws, or whose request ends meanwhile, leaves maintenance mode, reports WordPress's updates again and frees the recovery, which the next request answers as interrupted instead of waiting 30 minutes. | [RemoteRecoveryTest](php/RemoteRecoveryTest.php) |
| A restore puts SitePulse's settings back as soon as UpdraftPlus has restored the options table, so no other request runs an update the backup caught running, and keeps the recovery locked so none answers it as interrupted; each setting keeps whether WordPress loads it on every request. | [RemoteRecoveryTest](php/RemoteRecoveryTest.php) |
| WordPress's automatic updates no longer install again a plugin or theme version a recovery undid (for 90 days, or until a newer version is installed). | [RemoteRecoveryTest](php/RemoteRecoveryTest.php) |
| A rollback to a version WordPress.org does not have answers `no_package` before the site enters maintenance mode, and a download that fails afterwards is `install_failed`; recoveries refuse to start without the free disk space updates need, and a finished one has WordPress check for updates afresh. | [RemoteRecoveryTest](php/RemoteRecoveryTest.php), [RemoteVariantsTest](php/RemoteVariantsTest.php) |
| A restore that names the version it brings back fails with `restore_incomplete` when the item is not at that version afterwards, and no restore starts while an UpdraftPlus backup runs or waits to start. | [RemoteRecoveryTest](php/RemoteRecoveryTest.php) |
| Backups taken before an update are UpdraftPlus's automatic backups before updates ("SitePulse (before update)"), so they no longer push the owner's own backups out of UpdraftPlus's retention. | [RemoteUpdatesTest](php/RemoteUpdatesTest.php) |
| UpdraftPlus's upload of each archive while it still zips the others shows the progress its own bar shows instead of 0%, and an update's `backup_parts` must be the folder it updates. | [RemoteBackupsTest](php/RemoteBackupsTest.php), [RemoteUpdatesTest](php/RemoteUpdatesTest.php) |
| The readme and the settings page no longer say remote management never restores backups: it restores one when a safe update is undone. | [RemoteManagementTest](php/RemoteManagementTest.php) |

## JavaScript

| Fixed behavior | Regression coverage |
| --- | --- |
| A completed confirmation resolves once, removing its keyboard listener before a later Escape. | [Browser regressions](js/browser-regressions.test.cjs) |
| Restoring a session already stopped by the server restores idle controls without a tracking interval. | [Browser regressions](js/browser-regressions.test.cjs) |
| Profiler events already measured in milliseconds keep those units in completion reports. | [Browser regressions](js/browser-regressions.test.cjs) |
| Failed onboarding collection requests display an error rather than marking the task complete. | [Browser regressions](js/browser-regressions.test.cjs) |
| A rejected SAVEQUERIES request displays its failure without an unhandled promise rejection. | [Browser regressions](js/browser-regressions.test.cjs) |
| Vulnerability scans and AI diagnoses show every outcome as escaped text in a notice: a queued scan says when to check again (Easy Mode, which had nowhere to show it, reloads to show the queued or completed scan), a refusal gives its reason, an expired session asks for a reload, and polling stops on a failed or vanished diagnosis even though the answer is not a success. | [Backend](js/backend.test.cjs) |

These tests execute production code with isolated WordPress/browser boundaries. They protect these specific behaviors; they do not establish that no other defects remain.

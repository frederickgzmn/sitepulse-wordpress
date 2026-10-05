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
| Translation loading uses the plugin translations directory on disk. | [LifecycleTest](php/LifecycleTest.php) |
| A failed configuration rewrite returns an error before a duplicate SAVEQUERIES declaration can be added. | [PluginAdditionalBehaviorTest](php/PluginAdditionalBehaviorTest.php) |
| A failed query diagnostic displays its error and retains the SAVEQUERIES enable action. | [DiagnosticTemplateCoverageTest](php/DiagnosticTemplateCoverageTest.php) |

## JavaScript

| Fixed behavior | Regression coverage |
| --- | --- |
| A completed confirmation resolves once, removing its keyboard listener before a later Escape. | [Browser regressions](js/browser-regressions.test.cjs) |
| Restoring a session already stopped by the server restores idle controls without a tracking interval. | [Browser regressions](js/browser-regressions.test.cjs) |
| Profiler events already measured in milliseconds keep those units in completion reports. | [Browser regressions](js/browser-regressions.test.cjs) |
| Failed onboarding collection requests display an error rather than marking the task complete. | [Browser regressions](js/browser-regressions.test.cjs) |
| A rejected SAVEQUERIES request displays its failure without an unhandled promise rejection. | [Browser regressions](js/browser-regressions.test.cjs) |

These tests execute production code with isolated WordPress/browser boundaries. They protect these specific behaviors; they do not establish that no other defects remain.

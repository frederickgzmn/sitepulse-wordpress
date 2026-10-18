# Graph Report - sitepulse  (2026-10-18)

## Corpus Check
- 188 files · ~390,086 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4673 nodes · 11067 edges · 235 communities (99 shown, 136 thin omitted)
- Extraction: 75% EXTRACTED · 25% INFERRED · 0% AMBIGUOUS · INFERRED: 2736 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `4d2337a1`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- bootstrap.esm.js
- bootstrap.bundle.min.js
- bootstrap.esm.min.js
- Tooltip
- Nt
- Sitepulse_Error_Handler
- bootstrap.js
- remove
- Tooltip
- TemplateFactory
- Carousel
- Sitepulse_Remote_Events
- St
- BaseComponent
- TemplateFactory
- .hide
- bootstrap.bundle.js
- Tooltip
- backend.js
- Fs
- remove
- ._queueCallback
- WP_REST_Response
- Sitepulse_Test_Backups
- SitePulse
- Tooltip
- Sitepulse_Remote_Backups
- .hide
- apply_filters
- Qi
- Carousel
- ._queueCallback
- Carousel
- lt
- Carousel
- Backdrop
- RemoteBackupCommandsTest
- getBoundingClientRect
- Modal
- Backdrop
- Offcanvas
- bootstrap.min.js
- be
- Offcanvas
- R
- Offcanvas
- Sitepulse_Frontend
- Sitepulse_Setup
- Sitepulse_Test_Local_Filesystem
- Tab
- RemoteRecoveryTest
- remove
- do_action
- home_url
- BaseComponent
- Sitepulse_Remote_Updates
- Zs
- Dropdown
- Dropdown
- Dropdown
- Tab
- Tab
- BackendCoverageTest
- TemplateFactory
- Tab
- onboarding.js
- Sitepulse_Remote_Client
- Sitepulse_Profiler
- blueprint.json
- PopulatedTemplateTest
- BaseComponent
- AutoloadRestApiTest
- Dropdown
- remove
- BaseComponent
- Toast
- ScrollSpy
- Modal
- RemoteInventoryTest
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- Sitepulse_Remote_Activity
- Ss
- ScrollSpy
- Sitepulse_Onboarding
- Sitepulse_Utils
- ts
- Swipe
- Ti
- CoreAdditionalBehaviorTest
- Qn
- ScrollBarHelper
- Toast
- Backdrop
- Sitepulse_Settings
- Zi
- Ce
- Es
- PluginTest
- RemoteManagementTest
- Sitepulse_Whitelabel_Service
- FocusTrap
- sitepulse_global.js
- sitepulse-dev
- Repository Guidelines
- SitePulse Logo
- WordPress Pulse Logo
- CommandCode
- page-analysis.js
- SitePulse WordPress Plugin Icon
- SitePulse Logo
- Sitepulse Plugin Icon
- Sitepulse Logo
- VersionBumpTest
- Sitepulse_CurLoader
- get_plugins
- SitePulse — See What's Powering (or Slowing) Your Site
- RemoteConnectionTest
- Sitepulse_Backend
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- scripts
- PresentationWordPress.php
- add_action
- onboarding.test.cjs
- RemoteActivityTest
- BackendTest
- ps
- FocusTrap
- Sitepulse_Plugin
- Sitepulse_Plugin_Profiler
- browser.cjs
- backend.test.cjs
- Ln
- LifecycleTest
- wp_normalize_path
- WP_REST_Request
- Sitepulse_Remote_Management
- coverage.cjs
- ref_node_assert
- has_action
- contains
- DiagnosticTemplateCoverageTest
- remaining-browser-behavior.test.cjs
- js-coverage.cjs
- update_option
- Me
- Automated tests
- get_transient
- .time
- remote-connection.test.cjs
- PetAssistantTemplateTest
- Swipe
- WordPress.php
- PageTrackerTest
- CLAUDE.md
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- RemoteClientTest
- .roll_back
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- CronTest
- remote-connection.js
- Dashboard (portal) endpoints
- Remote management
- B
- Sitepulse_Remote_Inventory
- UpdraftPlus_Options
- RecentDashboardTest
- Sitepulse_Remote_Health
- CoreVariantsTest
- EasyModeSaveQueriesTest
- ApiVariantsTest
- RemoteVariantsTest
- ProfilerCompatibilityTest
- FrontendTest
- Sitepulse_Test_Filesystem
- UpdraftPlus_Backup_History
- Sitepulse_AI_Diagnostic_Cron
- RemoteManagement.php
- sitepulse_test_subprocess
- ot
- Sitepulse_Test_Database
- Fi
- ActionScheduler_Store
- ActionSchedulerTest
- TemplateTest
- pi
- BackWPup
- wp_next_scheduled
- Commands
- Sitepulse_Test_Case

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 251 edges
2. `get_option()` - 191 edges
3. `Sitepulse_Remote_Management` - 142 edges
4. `Sitepulse_Test_WP` - 136 edges
5. `Sitepulse_Api_Service` - 102 edges
6. `Sitepulse_Test_Backups` - 76 edges
7. `Sitepulse_Profiler` - 74 edges
8. `Sitepulse_Remote_Backups` - 73 edges
9. `Sitepulse_Test_Case` - 70 edges
10. `Sitepulse_Remote_Updates` - 69 edges

## Surprising Connections (you probably didn't know these)
- `Project Structure & Module Organization` --references--> `Sitepulse_Plugin`  [INFERRED]
  AGENTS.md → class/plugin.php
- `Testing notes` --references--> `Sitepulse_Test_Backups`  [INFERRED]
  CLAUDE.md → tests/php/Support/RemoteManagement.php
- `Architecture` --references--> `Sitepulse_CurLoader`  [INFERRED]
  CLAUDE.md → class/curloader.php
- `Architecture` --references--> `Sitepulse_Plugin`  [INFERRED]
  CLAUDE.md → class/plugin.php
- `Architecture` --references--> `Sitepulse_Plugin_Profiler`  [INFERRED]
  CLAUDE.md → class/plugin_profiler.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **SitePulse Core Monitoring Features** — readme_hook_level_timing, readme_slow_plugin_profiler, readme_per_page_tracking, readme_external_request_monitoring, readme_php_error_tracking [EXTRACTED 1.00]
- **SitePulse Logo Visual Elements** — assets_img_sitepulse_logo_sitepulse_logo, assets_img_sitepulse_logo_heartbeat_waveform, assets_img_sitepulse_logo_status_dots [INFERRED 0.85]

## Communities (235 total, 136 thin omitted)

### Community 0 - "bootstrap.esm.js"
Cohesion: 0.03
Nodes (72): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+64 more)

### Community 1 - "bootstrap.bundle.min.js"
Cohesion: 0.08
Nodes (50): be(), bi(), Ce(), ci(), D(), De(), _e(), ei() (+42 more)

### Community 2 - "bootstrap.esm.min.js"
Cohesion: 0.04
Nodes (65): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+57 more)

### Community 3 - "Tooltip"
Cohesion: 0.06
Nodes (3): Collapse, Popover, Tooltip

### Community 5 - "Sitepulse_Error_Handler"
Cohesion: 0.05
Nodes (10): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, StoredDataWritePathsTest, wp_mail(), current_time(), get_bloginfo() (+2 more)

### Community 6 - "bootstrap.js"
Cohesion: 0.07
Nodes (37): addHandler(), bootstrapDelegationHandler(), bootstrapHandler(), find(), findHandler(), focusableChildren(), get(), getDataAttribute() (+29 more)

### Community 7 - "remove"
Cohesion: 0.08
Nodes (4): Alert, Modal, remove(), Toast

### Community 8 - "Tooltip"
Cohesion: 0.08
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 9 - "TemplateFactory"
Cohesion: 0.08
Nodes (6): Config, getElement(), getUID(), isElement(), TemplateFactory, toType()

### Community 13 - "BaseComponent"
Cohesion: 0.07
Nodes (4): Backdrop, BaseComponent, Button, Config

### Community 14 - "TemplateFactory"
Cohesion: 0.09
Nodes (5): Config, getElement(), isElement(), TemplateFactory, toType()

### Community 16 - "bootstrap.bundle.js"
Cohesion: 0.05
Nodes (68): addHandler(), arrow(), bootstrapDelegationHandler(), bootstrapHandler(), computeAutoPlacement(), computeOffsets(), computeStyles(), detectOverflow() (+60 more)

### Community 17 - "Tooltip"
Cohesion: 0.07
Nodes (3): Collapse, Popover, Tooltip

### Community 18 - "backend.js"
Cohesion: 0.09
Nodes (23): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), checkVulnerabilities(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike() (+15 more)

### Community 20 - "remove"
Cohesion: 0.09
Nodes (4): Config, remove(), sanitizeHtml(), TemplateFactory

### Community 23 - "Sitepulse_Test_Backups"
Cohesion: 0.08
Nodes (5): BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, Updraft_Restorer, UpdraftPlus

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.05
Nodes (3): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup_Job

### Community 35 - "Backdrop"
Cohesion: 0.13
Nodes (6): Backdrop, execute(), executeAfterTransition(), getTransitionDurationFromElement(), Swipe, triggerTransitionEnd()

### Community 37 - "getBoundingClientRect"
Cohesion: 0.13
Nodes (37): applyStyles(), areValidElements(), debounce(), effect(), effect$2(), getBoundingClientRect(), getClientRectFromMixedType(), getClippingParents() (+29 more)

### Community 39 - "Backdrop"
Cohesion: 0.14
Nodes (3): Backdrop, execute(), Swipe

### Community 41 - "bootstrap.min.js"
Cohesion: 0.12
Nodes (16): B(), D(), I(), j(), M(), N(), O(), off() (+8 more)

### Community 44 - "R"
Cohesion: 0.11
Nodes (4): getDataAttributes(), H(), R, W

### Community 46 - "Sitepulse_Frontend"
Cohesion: 0.23
Nodes (3): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar

### Community 51 - "remove"
Cohesion: 0.17
Nodes (3): Ue(), remove(), Ze

### Community 52 - "do_action"
Cohesion: 0.17
Nodes (3): RemoteEventsTest, do_action(), get_theme_root()

### Community 53 - "home_url"
Cohesion: 0.09
Nodes (17): gethostbyname(), gethostname(), sitepulse_test_site_pages(), esc_url_raw(), home_url(), is_wp_error(), remove_query_arg(), sanitize_hex_color() (+9 more)

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Remote_Client"
Cohesion: 0.09
Nodes (3): Sitepulse_Remote_Client, ApiBaseTest, RemoteApiMockTest

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.06
Nodes (3): Sitepulse_Profiler, ProfilerTest, RecentActivityTest

### Community 68 - "blueprint.json"
Cohesion: 0.12
Nodes (16): Automated Test Setup, WordPress Playground, features, networking, landingPage, meta, author, categories (+8 more)

### Community 69 - "PopulatedTemplateTest"
Cohesion: 0.22
Nodes (3): PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 70 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 72 - "Dropdown"
Cohesion: 0.14
Nodes (3): distanceAndSkiddingToXY(), Dropdown, offset()

### Community 73 - "remove"
Cohesion: 0.07
Nodes (4): an, es, remove(), W

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.06
Nodes (5): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n(), wp_generate_password()

### Community 83 - "Sitepulse_Remote_Activity"
Cohesion: 0.08
Nodes (7): Sitepulse_Remote_Activity, register_activation_hook(), register_deactivation_hook(), register_activation_hook(), register_deactivation_hook(), get_plugin_data(), plugin_basename()

### Community 86 - "Sitepulse_Onboarding"
Cohesion: 0.07
Nodes (11): inc/ helpers, Sitepulse_Onboarding, Architecture, sitepulse_sanitize_param(), sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), Sitepulse_Loader, sitepulse_move_to_first_position() (+3 more)

### Community 91 - "CoreAdditionalBehaviorTest"
Cohesion: 0.09
Nodes (4): Sitepulse_Product_Analytics, CoreAdditionalBehaviorTest, absint(), sanitize_key()

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 101 - "RemoteManagementTest"
Cohesion: 0.21
Nodes (3): RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response()

### Community 102 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.12
Nodes (3): Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, wp_parse_args()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "sitepulse-dev"
Cohesion: 0.41
Nodes (13): sitepulse-dev script, cmd_start(), cmd_status(), current_target(), die(), info(), install_site(), others() (+5 more)

### Community 106 - "Repository Guidelines"
Cohesion: 0.25
Nodes (7): Build, Test, and Development Commands, Coding Style & Naming Conventions, Commit & Pull Request Guidelines, graphify, Project Structure & Module Organization, Repository Guidelines, Testing Guidelines

### Community 107 - "SitePulse Logo"
Cohesion: 0.50
Nodes (4): Heartbeat Waveform, SitePulse, SitePulse Logo, Status Indicator Dots

### Community 108 - "WordPress Pulse Logo"
Cohesion: 0.67
Nodes (3): Pulse Waveform, WordPress, WordPress Pulse Logo

### Community 110 - "page-analysis.js"
Cohesion: 0.19
Nodes (20): api(), card(), el(), format(), icon(), metric(), renderBreakdown(), row() (+12 more)

### Community 136 - "VersionBumpTest"
Cohesion: 0.09
Nodes (9): Rollouts, PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), CoverageGateTest (+1 more)

### Community 139 - "get_plugins"
Cohesion: 0.18
Nodes (3): get_plugins(), Sitepulse_Test_Theme, wp_get_theme()

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 143 - "Sitepulse_Backend"
Cohesion: 0.05
Nodes (17): Sitepulse_Backend, Sitepulse_Easy_Mode, Sitepulse_Getting_Started, EasyModeTest, GettingStartedTest, esc_js(), get_current_user_id(), get_user_meta() (+9 more)

### Community 144 - "Key Features"
Cohesion: 0.40
Nodes (5): External Request Monitoring, Key Features, Performance Profiling, Site Health & Stability, Smart Experience

### Community 145 - "Privacy First"
Cohesion: 0.67
Nodes (3): Core Monitoring, Optional Cloud Features, Privacy First

### Community 146 - "composer.json"
Cohesion: 0.12
Nodes (16): config, allow-plugins, platform, sort-packages, description, license, name, php (+8 more)

### Community 147 - "SitePulse Pro"
Cohesion: 0.67
Nodes (3): SitePulse Pro Waitlist, SitePulse Pro, SQL Query Monitor

### Community 149 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, coverage:js, test, test:coverage, test:js

### Community 150 - "PresentationWordPress.php"
Cohesion: 0.05
Nodes (18): Sitepulse_Monitoring, MonitoringTest, add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), human_time_diff(), load_textdomain() (+10 more)

### Community 151 - "add_action"
Cohesion: 0.07
Nodes (6): Sitepulse_Page_Tracker, add_action(), add_filter(), get_num_queries(), remove_action(), Sitepulse_Test_Hook

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 157 - "Sitepulse_Plugin"
Cohesion: 0.17
Nodes (3): Sitepulse_Plugin, wp_convert_hr_to_bytes(), PluginAdditionalBehaviorTest

### Community 158 - "Sitepulse_Plugin_Profiler"
Cohesion: 0.06
Nodes (4): Sitepulse_Plugin_Profiler, PluginProfilerTest, RestCoverageTest, StoredDataRobustnessTest

### Community 159 - "browser.cjs"
Cohesion: 0.13
Nodes (17): assert, { browser }, localization, setup(), test, assert, { browser }, localization (+9 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "wp_normalize_path"
Cohesion: 0.15
Nodes (3): wp_debug_backtrace_summary(), get_template(), wp_normalize_path()

### Community 164 - "WP_REST_Request"
Cohesion: 0.05
Nodes (41): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_check_vulnerabilities_api(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding() (+33 more)

### Community 165 - "Sitepulse_Remote_Management"
Cohesion: 0.06
Nodes (4): Sitepulse_Remote_Management, is_multisite(), site_url(), wp_generate_uuid4()

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): ref_node_fs, ref_node_path, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "ref_node_assert"
Cohesion: 0.12
Nodes (15): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+7 more)

### Community 168 - "has_action"
Cohesion: 0.15
Nodes (4): Sitepulse_Cron_Fallback, fastcgi_finish_request(), has_action(), has_filter()

### Community 170 - "contains"
Cohesion: 0.10
Nodes (4): contains(), effect$1(), FocusTrap, Modal

### Community 172 - "remaining-browser-behavior.test.cjs"
Cohesion: 0.29
Nodes (5): assert, backend(), { browser, jsonResponse }, memory, test

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (26): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-instrument (+18 more)

### Community 174 - "update_option"
Cohesion: 0.05
Nodes (8): Sitepulse_Api_Service, ApiCoverageTest, ApiRegressionTest, ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), get_option(), update_option()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 177 - "get_transient"
Cohesion: 0.11
Nodes (4): SetupOnboardingTest, delete_transient(), get_transient(), set_transient()

### Community 178 - ".time"
Cohesion: 0.08
Nodes (3): wp_clear_scheduled_hook(), wp_schedule_single_event(), wp_unschedule_event()

### Community 179 - "remote-connection.test.cjs"
Cohesion: 0.40
Nodes (5): assert, box(), { browser, jsonResponse }, settings(), test

### Community 182 - "WordPress.php"
Cohesion: 0.04
Nodes (56): sitepulse_register_route(), sitepulse_reorder_active_plugins_for_conflict(), RuntimeException, HarnessIsolationTest, get_permalink(), get_the_title(), wp_strip_all_tags(), add_menu_page() (+48 more)

### Community 184 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 189 - ".roll_back"
Cohesion: 0.28
Nodes (5): Automatic_Upgrader_Skin, wp_clean_plugins_cache(), wp_clean_themes_cache(), wp_update_plugins(), wp_update_themes()

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "CronTest"
Cohesion: 0.12
Nodes (5): Sitepulse_Cron_Manager, CronTest, wp_get_scheduled_event(), wp_get_schedules(), wp_schedule_event()

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.13
Nodes (15): Backup warnings, Connecting a site to an account, Dashboard (portal) endpoints, Key rotation, Monthly reports, Notifications, `POST /api/website/remote/command-progress`, Roles (+7 more)

### Community 201 - "Remote management"
Cohesion: 0.12
Nodes (15): API → site, Development, Endpoints, Phases, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/events`, `POST /api/website/remote/key` (+7 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 203 - "Sitepulse_Remote_Inventory"
Cohesion: 0.11
Nodes (8): Sitepulse_Remote_Inventory, disk_free_space(), get_filesystem_method(), get_site_option(), is_plugin_active(), wp_get_themes(), wp_get_translation_updates(), wp_is_file_mod_allowed()

### Community 206 - "Sitepulse_Remote_Health"
Cohesion: 0.15
Nodes (4): Sitepulse_Remote_Health, maybe_unserialize(), remove_filter(), sitepulse_test_callback_id()

### Community 221 - "RemoteManagement.php"
Cohesion: 0.14
Nodes (8): BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader, updraft_try_include_file()

### Community 233 - "BackWPup"
Cohesion: 0.11
Nodes (6): BackWPup, BackWPup_Cron, Updraft_Restorer_Skin, trailingslashit(), update_site_option(), wp_delete_file()

### Community 235 - "Commands"
Cohesion: 0.17
Nodes (8): Backup commands, Backup preset format, Commands, `POST /api/website/remote/inventory`, `POST /api/website/remote/safe-update`, Safe updates, Update commands, DirectAccessTemplateTest

### Community 236 - "Sitepulse_Test_Case"
Cohesion: 0.17
Nodes (3): CoreRegressionTest, HttpOriginTest, Sitepulse_Test_Case

## Knowledge Gaps
- **267 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+262 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1319 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **136 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `VersionBumpTest`, `Sitepulse_CurLoader`, `RemoteConnectionTest`, `Sitepulse_Backend`, `PresentationWordPress.php`, `add_action`, `RemoteActivityTest`, `BackendTest`, `Sitepulse_Remote_Backups`, `apply_filters`, `Sitepulse_Plugin`, `Sitepulse_Plugin_Profiler`, `LifecycleTest`, `RemoteBackupCommandsTest`, `WP_REST_Request`, `DiagnosticTemplateCoverageTest`, `update_option`, `Sitepulse_Frontend`, `Sitepulse_Setup`, `get_transient`, `RemoteRecoveryTest`, `PetAssistantTemplateTest`, `do_action`, `WordPress.php`, `PageTrackerTest`, `Sitepulse_Remote_Updates`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `ErrorHandlerVariantsTest`, `BackendCoverageTest`, `Sitepulse_Remote_Client`, `Sitepulse_Profiler`, `PopulatedTemplateTest`, `CronTest`, `AutoloadRestApiTest`, `RecentDashboardTest`, `RemoteInventoryTest`, `CoreVariantsTest`, `Sitepulse_Page_Analysis`, `EasyModeSaveQueriesTest`, `ApiVariantsTest`, `RemoteVariantsTest`, `ProfilerCompatibilityTest`, `FrontendTest`, `Sitepulse_Utils`, `Sitepulse_AI_Diagnostic_Cron`, `CoreAdditionalBehaviorTest`, `PluginTest`, `RemoteManagementTest`, `ActionSchedulerTest`, `Sitepulse_Whitelabel_Service`, `TemplateTest`, `Commands`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._
- **Why does `Dashboard (portal) endpoints` connect `Dashboard (portal) endpoints` to `VersionBumpTest`, `Remote management`, `get_plugins`, `Commands`, `remote-connection.test.cjs`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Why does `settings()` connect `remote-connection.test.cjs` to `Dashboard (portal) endpoints`, `browser.cjs`?**
  _High betweenness centrality (0.042) - this node is a cross-community bridge._
- **Are the 247 inferred relationships involving `update_option()` (e.g. with `.make_api_request()` and `.make_vulnerability_api_request()`) actually correct?**
  _`update_option()` has 247 INFERRED edges - model-reasoned connections that need verification._
- **Are the 186 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 186 INFERRED edges - model-reasoned connections that need verification._
- **Are the 91 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 91 INFERRED edges - model-reasoned connections that need verification._
- **Are the 47 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 47 INFERRED edges - model-reasoned connections that need verification._
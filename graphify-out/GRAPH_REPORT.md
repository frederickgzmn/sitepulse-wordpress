# Graph Report - sitepulse  (2026-10-18)

## Corpus Check
- 195 files · ~404,648 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4809 nodes · 11636 edges · 233 communities (91 shown, 142 thin omitted)
- Extraction: 74% EXTRACTED · 26% INFERRED · 0% AMBIGUOUS · INFERRED: 2974 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `e8b3a69e`
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
- RestApiTest
- Sitepulse_Test_Backups
- SitePulse
- Tooltip
- Sitepulse_Remote_Backups
- .hide
- Sitepulse_Settings
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
- Sitepulse_Page_Analysis
- Tab
- RemoteRecoveryTest
- remove
- do_action
- is_wp_error
- BaseComponent
- RemoteUpdatesTest
- Zs
- Dropdown
- Dropdown
- Dropdown
- Tab
- Tab
- admin_url
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
- PageAnalysisTest
- wp_normalize_path
- Ss
- ScrollSpy
- current_user_can
- Sitepulse_Utils
- ts
- Swipe
- RestCoverageTest
- Sitepulse_Whitelabel_Service
- Qn
- ScrollBarHelper
- Toast
- Backdrop
- Sitepulse_Remote_Inventory
- Zi
- Ce
- Es
- PluginTest
- sitepulse_test_remote_response
- .test_every_request_goes_to_the_api_the_setting_names
- FocusTrap
- sitepulse_global.js
- sitepulse-dev
- RecentActivityTest
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
- Sitepulse_Remote_Updates
- SitePulse — See What's Powering (or Slowing) Your Site
- RemoteConnectionTest
- Sitepulse_Easy_Mode
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- CoreRegressionTest
- PresentationWordPress.php
- .time
- onboarding.test.cjs
- Sitepulse_Remote_Activity
- plugin_basename
- ps
- FocusTrap
- Sitepulse_Backend
- update_option
- browser
- backend.test.cjs
- Ln
- LifecycleTest
- HttpOriginTest
- WP_REST_Request
- Sitepulse_Remote_Management
- coverage.cjs
- browser.cjs
- get_transient
- contains
- DiagnosticTemplateCoverageTest
- remaining-browser-behavior.test.cjs
- js-coverage.cjs
- get_option
- Me
- Automated tests
- .test_check_in_checks_wp_admin_again_first_when_the_result_is_older_than_15_minutes
- RemoteManagementTest
- remote-connection.test.cjs
- Sitepulse_Monitoring
- Swipe
- WordPress.php
- Sitepulse_Page_Tracker
- delete_option
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- package.json
- RemoteClientTest
- ProfilerCompatibilityTest
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- Sitepulse_Test_Filesystem
- remote-connection.js
- Dashboard (portal) endpoints
- Remote management
- B
- add_action
- Ti
- Sitepulse_Remote_Health
- CoreVariantsTest
- wp_load_alloptions
- ApiVariantsTest
- RemoteVariantsTest
- Sitepulse_Test_Case
- WP_REST_Response
- sitepulse_test_post_type
- admin-access.js
- PageAnalysisTest.php
- Sitepulse_Onboarding
- Sitepulse_Admin_Access
- sitepulse_test_subprocess
- ot
- Fi
- StoredDataRobustnessTest
- pi
- Commands
- build-zip.sh

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 277 edges
2. `get_option()` - 204 edges
3. `Sitepulse_Remote_Management` - 144 edges
4. `Sitepulse_Test_WP` - 137 edges
5. `Sitepulse_Api_Service` - 102 edges
6. `Sitepulse_Test_Backups` - 95 edges
7. `Sitepulse_Remote_Backups` - 80 edges
8. `Sitepulse_Remote_Updates` - 80 edges
9. `RemoteUpdatesTest` - 75 edges
10. `Sitepulse_Profiler` - 74 edges

## Surprising Connections (you probably didn't know these)
- `Architecture` --references--> `Sitepulse_CurLoader`  [INFERRED]
  CLAUDE.md → class/curloader.php
- `Project Structure & Module Organization` --references--> `Sitepulse_Plugin`  [INFERRED]
  AGENTS.md → class/plugin.php
- `Architecture` --references--> `Sitepulse_Plugin`  [INFERRED]
  CLAUDE.md → class/plugin.php
- `Architecture` --references--> `Sitepulse_Plugin_Profiler`  [INFERRED]
  CLAUDE.md → class/plugin_profiler.php
- `Architecture` --references--> `Sitepulse_Profiler`  [INFERRED]
  CLAUDE.md → class/profiler.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **SitePulse Core Monitoring Features** — readme_hook_level_timing, readme_slow_plugin_profiler, readme_per_page_tracking, readme_external_request_monitoring, readme_php_error_tracking [EXTRACTED 1.00]
- **SitePulse Logo Visual Elements** — assets_img_sitepulse_logo_sitepulse_logo, assets_img_sitepulse_logo_heartbeat_waveform, assets_img_sitepulse_logo_status_dots [INFERRED 0.85]

## Communities (233 total, 142 thin omitted)

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
Cohesion: 0.06
Nodes (8): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, date_i18n(), current_time(), is_email(), sanitize_email()

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

### Community 11 - "Sitepulse_Remote_Events"
Cohesion: 0.09
Nodes (4): Sitepulse_Remote_Events, Sitepulse_Test_Local_Filesystem, get_bloginfo(), get_file_data()

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
Cohesion: 0.04
Nodes (18): Commands, Repo workflow, Testing notes, Sitepulse_Test_Database, Automatic_Upgrader_Skin, BackWPup, BackWPup_Cron, BackWPup_JobTypes (+10 more)

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.04
Nodes (5): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup_Job, UpdraftPlus_Options, trailingslashit()

### Community 28 - "Sitepulse_Settings"
Cohesion: 0.07
Nodes (6): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters(), _get_cron_array()

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
Cohesion: 0.17
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 51 - "remove"
Cohesion: 0.17
Nodes (3): Ue(), remove(), Ze

### Community 53 - "is_wp_error"
Cohesion: 0.24
Nodes (7): is_wp_error(), sanitize_text_field(), wp_json_encode(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "admin_url"
Cohesion: 0.10
Nodes (19): RuntimeException, add_query_arg(), admin_url(), check_admin_referer(), esc_js(), esc_url_raw(), remove_query_arg(), rest_url() (+11 more)

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 68 - "blueprint.json"
Cohesion: 0.12
Nodes (16): Automated Test Setup, WordPress Playground, features, networking, landingPage, meta, author, categories (+8 more)

### Community 69 - "PopulatedTemplateTest"
Cohesion: 0.12
Nodes (4): PetAssistantTemplateTest, PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 70 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 71 - "AutoloadRestApiTest"
Cohesion: 0.20
Nodes (4): sitepulse_get_autoload_options(), sitepulse_get_protected_options(), sitepulse_update_autoload_option(), AutoloadRestApiTest

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

### Community 86 - "current_user_can"
Cohesion: 0.26
Nodes (6): check_ajax_referer(), current_user_can(), is_admin_bar_showing(), Sitepulse_Test_Json_Response, wp_send_json_error(), wp_send_json_success()

### Community 91 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.07
Nodes (6): Sitepulse_Product_Analytics, Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, absint(), sanitize_key(), wp_parse_args()

### Community 96 - "Sitepulse_Remote_Inventory"
Cohesion: 0.15
Nodes (3): Sitepulse_Remote_Inventory, disk_free_space(), is_plugin_active()

### Community 100 - "PluginTest"
Cohesion: 0.09
Nodes (4): PluginTest, wp_delete_file(), WP_Filesystem(), wp_upload_dir()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "sitepulse-dev"
Cohesion: 0.41
Nodes (13): sitepulse-dev script, cmd_start(), cmd_status(), current_target(), die(), info(), install_site(), others() (+5 more)

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
Cohesion: 0.10
Nodes (8): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), CoverageGateTest, VersionBumpTest

### Community 139 - "Sitepulse_Remote_Updates"
Cohesion: 0.10
Nodes (4): Sitepulse_Remote_Updates, get_filesystem_method(), get_theme_root(), wp_is_file_mod_allowed()

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 143 - "Sitepulse_Easy_Mode"
Cohesion: 0.06
Nodes (10): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, EasyModeTest, GettingStartedTest, get_current_user_id(), get_user_meta(), is_user_logged_in(), metadata_exists() (+2 more)

### Community 144 - "Key Features"
Cohesion: 0.40
Nodes (5): External Request Monitoring, Key Features, Performance Profiling, Site Health & Stability, Smart Experience

### Community 145 - "Privacy First"
Cohesion: 0.67
Nodes (3): Core Monitoring, Optional Cloud Features, Privacy First

### Community 146 - "composer.json"
Cohesion: 0.11
Nodes (17): config, allow-plugins, platform, sort-packages, description, license, name, php (+9 more)

### Community 147 - "SitePulse Pro"
Cohesion: 0.67
Nodes (3): SitePulse Pro Waitlist, SitePulse Pro, SQL Query Monitor

### Community 150 - "PresentationWordPress.php"
Cohesion: 0.09
Nodes (10): add_settings_section(), do_settings_sections(), get_locale(), human_time_diff(), load_textdomain(), _n(), number_format_i18n(), wp_date() (+2 more)

### Community 151 - ".time"
Cohesion: 0.06
Nodes (8): Sitepulse_AI_Diagnostic_Cron, Sitepulse_Cron_Manager, wp_clear_scheduled_hook(), wp_get_scheduled_event(), wp_get_schedules(), wp_next_scheduled(), wp_schedule_event(), wp_unschedule_event()

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 154 - "plugin_basename"
Cohesion: 0.11
Nodes (6): BackendTest, register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes(), get_plugin_data(), plugin_basename()

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 157 - "Sitepulse_Backend"
Cohesion: 0.06
Nodes (12): Build, Test, and Development Commands, Coding Style & Naming Conventions, Commit & Pull Request Guidelines, graphify, Project Structure & Module Organization, Repository Guidelines, Testing Guidelines, Sitepulse_Backend (+4 more)

### Community 158 - "update_option"
Cohesion: 0.05
Nodes (8): Sitepulse_Plugin_Profiler, ApiCoverageTest, CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataWritePathsTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), update_option()

### Community 159 - "browser"
Cohesion: 0.13
Nodes (17): assert, { browser, jsonResponse, flush }, page(), settle(), test, assert, { browser }, localization (+9 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 164 - "WP_REST_Request"
Cohesion: 0.08
Nodes (36): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice() (+28 more)

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): ref_node_fs, ref_node_path, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "browser.cjs"
Cohesion: 0.10
Nodes (20): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+12 more)

### Community 168 - "get_transient"
Cohesion: 0.08
Nodes (8): Sitepulse_Cron_Fallback, CronCoverageTest, CronTest, delete_transient(), get_transient(), has_action(), set_transient(), wp_generate_password()

### Community 170 - "contains"
Cohesion: 0.10
Nodes (4): contains(), effect$1(), FocusTrap, Modal

### Community 172 - "remaining-browser-behavior.test.cjs"
Cohesion: 0.29
Nodes (5): assert, backend(), { browser, jsonResponse }, memory, test

### Community 173 - "js-coverage.cjs"
Cohesion: 0.13
Nodes (14): ref_node_child_process, context, coverage, { createContext }, { createCoverageMap }, destination, directory, fs (+6 more)

### Community 174 - "get_option"
Cohesion: 0.05
Nodes (9): Sitepulse_Api_Service, ApiRegressionTest, ApiServiceTest, gethostbyname(), gethostname(), get_option(), home_url(), untrailingslashit() (+1 more)

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 179 - "remote-connection.test.cjs"
Cohesion: 0.40
Nodes (5): assert, box(), { browser, jsonResponse }, settings(), test

### Community 180 - "Sitepulse_Monitoring"
Cohesion: 0.11
Nodes (8): Sitepulse_Monitoring, MonitoringTest, submit_button(), wp_nonce_field(), esc_attr(), esc_html(), esc_html_e(), esc_url()

### Community 182 - "WordPress.php"
Cohesion: 0.04
Nodes (50): sitepulse_register_route(), get_permalink(), get_the_title(), wp_strip_all_tags(), add_menu_page(), add_option(), add_submenu_page(), delete_metadata() (+42 more)

### Community 184 - "delete_option"
Cohesion: 0.12
Nodes (7): inc/ helpers, Architecture, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), Sitepulse_Loader, sitepulse_move_to_first_position(), delete_option()

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "package.json"
Cohesion: 0.08
Nodes (24): description, devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom (+16 more)

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.14
Nodes (15): Backup warnings, Connecting a site to an account, Dashboard (portal) endpoints, Key rotation, Monthly reports, Notifications, Roles, Rollouts (+7 more)

### Community 201 - "Remote management"
Cohesion: 0.14
Nodes (13): API → site, Development, Endpoints, Phases, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/key`, Remote management (+5 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 203 - "add_action"
Cohesion: 0.08
Nodes (10): register_activation_hook(), register_deactivation_hook(), add_action(), add_filter(), is_admin(), remove_filter(), sitepulse_test_callback_id(), Sitepulse_Test_Hook (+2 more)

### Community 211 - "wp_load_alloptions"
Cohesion: 0.40
Nodes (3): sitepulse_reorder_active_plugins_for_conflict(), wp_cache_flush(), wp_load_alloptions()

### Community 214 - "Sitepulse_Test_Case"
Cohesion: 0.09
Nodes (5): EasyModeSaveQueriesTest, HarnessIsolationTest, RecentDashboardTest, Sitepulse_Test_Case, TemplateTest

### Community 220 - "Sitepulse_Onboarding"
Cohesion: 0.08
Nodes (6): Sitepulse_Onboarding, sitepulse_sanitize_param(), SetupOnboardingTest, remove_all_actions(), get_current_screen(), wp_kses_post()

### Community 221 - "Sitepulse_Admin_Access"
Cohesion: 0.06
Nodes (8): Sitepulse_Admin_Access, WP-Cron, AdminAccessTest, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader

### Community 235 - "Commands"
Cohesion: 0.11
Nodes (13): Backup commands, Backup preset format, Commands, `POST /api/website/remote/command-progress`, `POST /api/website/remote/events`, `POST /api/website/remote/inventory`, `POST /api/website/remote/safe-update`, Safe updates (+5 more)

## Knowledge Gaps
- **273 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+268 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1350 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **142 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Dashboard (portal) endpoints` connect `Dashboard (portal) endpoints` to `RemoteBackupCommandsTest`, `Remote management`, `Commands`, `remote-connection.test.cjs`, `Sitepulse_Admin_Access`?**
  _High betweenness centrality (0.063) - this node is a cross-community bridge._
- **Why does `settings()` connect `remote-connection.test.cjs` to `Dashboard (portal) endpoints`, `browser`?**
  _High betweenness centrality (0.062) - this node is a cross-community bridge._
- **Why does `browser()` connect `browser` to `backend.test.cjs`, `browser.cjs`, `remaining-browser-behavior.test.cjs`, `remote-connection.test.cjs`, `onboarding.test.cjs`, `page-analysis.test.cjs`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **Are the 273 inferred relationships involving `update_option()` (e.g. with `.save()` and `.make_api_request()`) actually correct?**
  _`update_option()` has 273 INFERRED edges - model-reasoned connections that need verification._
- **Are the 199 inferred relationships involving `get_option()` (e.g. with `.stored()` and `.check_ai_diagnostic_status()`) actually correct?**
  _`get_option()` has 199 INFERRED edges - model-reasoned connections that need verification._
- **Are the 93 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 93 INFERRED edges - model-reasoned connections that need verification._
- **Are the 47 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 47 INFERRED edges - model-reasoned connections that need verification._
# Graph Report - sitepulse  (2026-10-16)

## Corpus Check
- 182 files · ~370,160 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4482 nodes · 10293 edges · 233 communities (96 shown, 137 thin omitted)
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 2386 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `46c511b3`
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
- Commands
- St
- BaseComponent
- TemplateFactory
- Fi
- bootstrap.bundle.js
- Tooltip
- backend.js
- .hide
- remove
- ._queueCallback
- api_backend.php
- Sitepulse_Easy_Mode
- SitePulse
- Tooltip
- Sitepulse_Remote_Backups
- .hide
- do_action
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
- W
- Offcanvas
- Sitepulse_Frontend
- Sitepulse_Onboarding
- Sitepulse_Remote_Inventory
- Tab
- wp_parse_url
- Ze
- Sitepulse_Remote_Events
- Sitepulse_Monitoring
- BaseComponent
- Sitepulse_Remote_Updates
- Zs
- Dropdown
- Dropdown
- Dropdown
- Tab
- Tab
- Sitepulse_Backend
- TemplateFactory
- Tab
- onboarding.js
- remove
- Sitepulse_Profiler
- blueprint.json
- RestCoverageTest
- BaseComponent
- AutoloadRestApiTest
- Dropdown
- es
- BaseComponent
- Toast
- ScrollSpy
- Modal
- Sitepulse_Remote_Client
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- plugin_basename
- Ss
- ScrollSpy
- .time
- Sitepulse_Utils
- ts
- Swipe
- PageAnalysisTest
- Sitepulse_Product_Analytics
- Qn
- ScrollBarHelper
- Toast
- Backdrop
- Sitepulse_Settings
- Zi
- Ce
- Es
- PluginTest
- Sitepulse_Remote_Management
- PopulatedTemplateTest
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
- Sitepulse_Test_Database
- SitePulse — See What's Powering (or Slowing) Your Site
- wp_normalize_path
- get_current_user_id
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- scripts
- PresentationWordPress.php
- add_action
- onboarding.test.cjs
- Sitepulse_Remote_Activity
- BackendTest
- ps
- FocusTrap
- Sitepulse_Test_Local_Filesystem
- Sitepulse_Plugin_Profiler
- browser
- backend.test.cjs
- Ln
- LifecycleTest
- WP_REST_Request
- RestApiTest
- Sitepulse_Plugin
- coverage.cjs
- browser.cjs
- Sitepulse_Test_Upgrades
- contains
- DiagnosticTemplateCoverageTest
- js-coverage.cjs
- update_option
- Me
- Automated tests
- CoverageGateTest
- vs
- CoreVariantsTest
- ApiVariantsTest
- Swipe
- WordPress.php
- PageTrackerTest
- Sitepulse_Test_Case
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- RemoteClientTest
- CoreAdditionalBehaviorTest
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- wp_next_scheduled
- PetAssistantTemplateTest
- Dashboard (portal) endpoints
- ActionSchedulerTest
- B
- an
- TemplateRenderingTest
- RecentDashboardTest
- api-variants.php
- sanitize_text_field
- Sitepulse_Whitelabel_Service
- .test_every_request_goes_to_the_api_the_setting_names
- AI Diagnostics
- sitepulse_test_post_type
- ProfilerCompatibilityTest
- sitepulse_test_subprocess
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- StoredDataRobustnessTest
- _n
- is_wp_error
- Sitepulse_Test_Backups
- Installation
- .queue_event
- apply_filters
- WP_Error
- .upgrader
- TemplateTest
- ActionScheduler_Store
- CLAUDE.md

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 214 edges
2. `get_option()` - 164 edges
3. `Sitepulse_Test_WP` - 132 edges
4. `Sitepulse_Remote_Management` - 99 edges
5. `Sitepulse_Api_Service` - 83 edges
6. `Sitepulse_Profiler` - 74 edges
7. `Sitepulse_Remote_Backups` - 70 edges
8. `Sitepulse_Test_Case` - 68 edges
9. `Sitepulse_Page_Analysis` - 67 edges
10. `Sitepulse_Settings` - 60 edges

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

## Communities (233 total, 137 thin omitted)

### Community 0 - "bootstrap.esm.js"
Cohesion: 0.03
Nodes (72): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+64 more)

### Community 1 - "bootstrap.bundle.min.js"
Cohesion: 0.09
Nodes (49): be(), bi(), Ce(), ci(), D(), De(), _e(), ei() (+41 more)

### Community 2 - "bootstrap.esm.min.js"
Cohesion: 0.04
Nodes (65): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+57 more)

### Community 3 - "Tooltip"
Cohesion: 0.06
Nodes (3): Collapse, Popover, Tooltip

### Community 5 - "Sitepulse_Error_Handler"
Cohesion: 0.04
Nodes (11): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, StoredDataWritePathsTest, current_time(), esc_html(), esc_html_e() (+3 more)

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

### Community 11 - "Commands"
Cohesion: 0.16
Nodes (10): Backup commands, Backup preset format, Commands, Endpoints, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/events`, `POST /api/website/remote/inventory` (+2 more)

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
Nodes (20): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike(), getScoreColor() (+12 more)

### Community 19 - ".hide"
Cohesion: 0.09
Nodes (3): li(), Fs, pi

### Community 20 - "remove"
Cohesion: 0.09
Nodes (4): Config, remove(), sanitizeHtml(), TemplateFactory

### Community 22 - "api_backend.php"
Cohesion: 0.14
Nodes (15): sitepulse_dismiss_getting_started(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events(), sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report(), sitepulse_page_analysis_response() (+7 more)

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.12
Nodes (7): Sitepulse_Easy_Mode, EasyModeTest, check_ajax_referer(), Sitepulse_Test_Json_Response, update_user_meta(), wp_send_json_error(), wp_send_json_success()

### Community 24 - "SitePulse"
Cohesion: 0.10
Nodes (20): Admin Bar Widget, Basic View, Core Web Vitals, Cron Job Management, Developer View, Email Blocking, Fatal-Error Detection, Health Score System (+12 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.04
Nodes (5): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup_Job, UpdraftPlus_Options, trailingslashit()

### Community 28 - "do_action"
Cohesion: 0.18
Nodes (3): RemoteEventsTest, do_action(), get_theme_root()

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

### Community 44 - "W"
Cohesion: 0.09
Nodes (4): getDataAttributes(), H(), Ie, W

### Community 46 - "Sitepulse_Frontend"
Cohesion: 0.15
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Onboarding"
Cohesion: 0.05
Nodes (6): Sitepulse_Onboarding, Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest, get_current_screen()

### Community 48 - "Sitepulse_Remote_Inventory"
Cohesion: 0.05
Nodes (18): Sitepulse_Remote_Inventory, Phases, Update policy, disk_free_space(), get_core_updates(), get_filesystem_method(), get_plugins(), get_site_option() (+10 more)

### Community 50 - "wp_parse_url"
Cohesion: 0.10
Nodes (7): add_query_arg(), delete_transient(), remove_query_arg(), untrailingslashit(), wp_nonce_url(), wp_parse_url(), wp_unslash()

### Community 51 - "Ze"
Cohesion: 0.11
Nodes (3): Ue(), Wi, Ze

### Community 53 - "Sitepulse_Monitoring"
Cohesion: 0.10
Nodes (6): Sitepulse_Monitoring, Architecture, Sitepulse_Loader, MonitoringTest, delete_metadata(), delete_option()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Backend"
Cohesion: 0.12
Nodes (4): Sitepulse_Backend, BackendCoverageTest, has_filter(), sitepulse_test_callback_id()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "remove"
Cohesion: 0.15
Nodes (3): G, remove(), Ti

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.06
Nodes (3): Sitepulse_Profiler, ProfilerTest, RecentActivityTest

### Community 68 - "blueprint.json"
Cohesion: 0.12
Nodes (16): Automated Test Setup, WordPress Playground, features, networking, landingPage, meta, author, categories (+8 more)

### Community 69 - "RestCoverageTest"
Cohesion: 0.18
Nodes (6): sitepulse_check_memory(), sitepulse_check_vulnerabilities_api(), sitepulse_collect_plugin_data(), sitepulse_dismiss_onboarding(), sitepulse_enable_save_queries(), RestCoverageTest

### Community 70 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 71 - "AutoloadRestApiTest"
Cohesion: 0.20
Nodes (4): sitepulse_get_autoload_options(), sitepulse_get_protected_options(), sitepulse_update_autoload_option(), AutoloadRestApiTest

### Community 72 - "Dropdown"
Cohesion: 0.14
Nodes (3): distanceAndSkiddingToXY(), Dropdown, offset()

### Community 73 - "es"
Cohesion: 0.07
Nodes (3): es, ot, W

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 83 - "plugin_basename"
Cohesion: 0.14
Nodes (10): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), register_activation_hook(), register_deactivation_hook(), get_plugin_data() (+2 more)

### Community 86 - ".time"
Cohesion: 0.08
Nodes (9): Sitepulse_Cron_Fallback, Sitepulse_Cron_Manager, CronCoverageTest, CronTest, get_transient(), has_action(), set_transient(), wp_get_schedules() (+1 more)

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 101 - "Sitepulse_Remote_Management"
Cohesion: 0.11
Nodes (5): Sitepulse_Remote_Management, RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response(), wp_schedule_single_event()

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.22
Nodes (3): PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

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
Cohesion: 0.19
Nodes (6): PHPUnit\Framework\TestCase, sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 143 - "get_current_user_id"
Cohesion: 0.12
Nodes (8): Sitepulse_Getting_Started, sitepulse_set_sp_profiler(), GettingStartedTest, esc_url(), get_current_user_id(), get_user_meta(), is_user_logged_in(), metadata_exists()

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
Cohesion: 0.09
Nodes (15): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), human_time_diff(), load_textdomain(), remove_all_actions(), submit_button() (+7 more)

### Community 151 - "add_action"
Cohesion: 0.09
Nodes (4): Sitepulse_Page_Tracker, add_action(), add_filter(), Sitepulse_Test_Hook

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.08
Nodes (4): d(), ps, qt, remove()

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 164 - "RestApiTest"
Cohesion: 0.10
Nodes (6): sitepulse_complete_onboarding(), sitepulse_get_memory_info(), sitepulse_rest_pre_dispatch(), sitepulse_set_wpslowhttp(), sitepulse_update_settings(), RestApiTest

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 168 - "Sitepulse_Test_Upgrades"
Cohesion: 0.22
Nodes (5): Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader

### Community 170 - "contains"
Cohesion: 0.10
Nodes (4): contains(), effect$1(), FocusTrap, Modal

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "update_option"
Cohesion: 0.05
Nodes (8): Sitepulse_Api_Service, ApiCoverageTest, ApiRegressionTest, ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), get_option(), update_option()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 182 - "WordPress.php"
Cohesion: 0.04
Nodes (60): sitepulse_register_route(), RuntimeException, get_permalink(), get_the_title(), sitepulse_test_site_pages(), wc_get_page_permalink(), add_menu_page(), add_option() (+52 more)

### Community 184 - "Sitepulse_Test_Case"
Cohesion: 0.12
Nodes (4): EasyModeSaveQueriesTest, HarnessIsolationTest, HttpOriginTest, Sitepulse_Test_Case

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "wp_next_scheduled"
Cohesion: 0.10
Nodes (6): Sitepulse_AI_Diagnostic_Cron, wp_clear_scheduled_hook(), wp_doing_cron(), wp_get_scheduled_event(), wp_next_scheduled(), wp_unschedule_event()

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.10
Nodes (19): API → site, Backup warnings, Dashboard (portal) endpoints, Development, Key rotation, Monthly reports, Notifications, Remote management (+11 more)

### Community 202 - "B"
Cohesion: 0.07
Nodes (4): B, mo, U, x()

### Community 206 - "api-variants.php"
Cohesion: 0.22
Nodes (5): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes()

### Community 209 - "sanitize_text_field"
Cohesion: 0.24
Nodes (5): sitepulse_sanitize_param(), absint(), sanitize_key(), sanitize_text_field(), wp_kses_post()

### Community 212 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 220 - "is_wp_error"
Cohesion: 0.10
Nodes (13): Sitepulse_Remote_Health, Update commands, is_wp_error(), maybe_unserialize(), remove_action(), remove_filter(), wp_generate_password(), wp_json_encode() (+5 more)

### Community 221 - "Sitepulse_Test_Backups"
Cohesion: 0.07
Nodes (9): BackWPup, BackWPup_Cron, BackWPup_JobTypes, BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, Sitepulse_Test_Updraft_Admin, updraft_try_include_file() (+1 more)

### Community 224 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

### Community 228 - "WP_Error"
Cohesion: 0.16
Nodes (10): sitepulse_check_ai_diagnostic_status_api(), sitepulse_dismiss_onboarding_notice(), sitepulse_get_curl_stats(), sitepulse_get_plugin_profiler_stats(), sitepulse_get_profiler_stats(), sitepulse_request_ai_diagnostic_api(), sitepulse_reset_onboarding(), sitepulse_set_trackers_disabled_notice() (+2 more)

### Community 234 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

## Knowledge Gaps
- **263 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+258 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1295 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **137 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `VersionBumpTest`, `Sitepulse_CurLoader`, `Commands`, `Sitepulse_Test_Database`, `get_current_user_id`, `Sitepulse_Easy_Mode`, `add_action`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Remote_Backups`, `do_action`, `Sitepulse_Plugin_Profiler`, `LifecycleTest`, `RemoteBackupCommandsTest`, `Sitepulse_Plugin`, `RestApiTest`, `DiagnosticTemplateCoverageTest`, `update_option`, `Sitepulse_Onboarding`, `Sitepulse_Frontend`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Monitoring`, `PageTrackerTest`, `Sitepulse_Remote_Updates`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `CoreAdditionalBehaviorTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Backend`, `Sitepulse_Profiler`, `RestCoverageTest`, `AutoloadRestApiTest`, `PetAssistantTemplateTest`, `ActionSchedulerTest`, `TemplateRenderingTest`, `RecentDashboardTest`, `Sitepulse_Remote_Client`, `.test_every_request_goes_to_the_api_the_setting_names`, `.time`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `RemoteVariantsTest`, `PageAnalysisTest`, `Sitepulse_Product_Analytics`, `StoredDataRobustnessTest`, `apply_filters`, `PluginTest`, `Sitepulse_Remote_Management`, `PopulatedTemplateTest`, `TemplateTest`?**
  _High betweenness centrality (0.058) - this node is a cross-community bridge._
- **Why does ``POST /api/website/remote/inventory`` connect `Commands` to `page-analysis.js`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Test_Database`, `get_current_user_id`, `api_backend.php`, `add_action`, `Sitepulse_Remote_Activity`, `Sitepulse_Remote_Backups`, `do_action`, `Sitepulse_Plugin_Profiler`, `RestApiTest`, `RemoteBackupCommandsTest`, `DiagnosticTemplateCoverageTest`, `.analysis`, `Sitepulse_Frontend`, `Sitepulse_Onboarding`, `wp_parse_url`, `Sitepulse_Remote_Events`, `Sitepulse_Monitoring`, `WordPress.php`, `Sitepulse_Remote_Updates`, `CoreAdditionalBehaviorTest`, `Sitepulse_Backend`, `Sitepulse_Profiler`, `RestCoverageTest`, `wp_next_scheduled`, `TemplateRenderingTest`, `Sitepulse_Remote_Client`, `sanitize_text_field`, `Sitepulse_Page_Analysis`, `plugin_basename`, `.test_every_request_goes_to_the_api_the_setting_names`, `sitepulse_test_post_type`, `.time`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `StoredDataRobustnessTest`, `Sitepulse_Product_Analytics`, `is_wp_error`, `Sitepulse_Test_Backups`, `Sitepulse_Settings`, `apply_filters`, `WP_Error`, `Sitepulse_Remote_Management`, `PopulatedTemplateTest`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Are the 210 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 210 INFERRED edges - model-reasoned connections that need verification._
- **Are the 159 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 159 INFERRED edges - model-reasoned connections that need verification._
- **Are the 44 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 44 INFERRED edges - model-reasoned connections that need verification._
- **Are the 62 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 62 INFERRED edges - model-reasoned connections that need verification._
# Graph Report - sitepulse  (2026-10-16)

## Corpus Check
- 182 files · ~371,231 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4488 nodes · 10329 edges · 236 communities (107 shown, 129 thin omitted)
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 2408 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `3e79dad9`
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
- TemplateFactory
- ._queueCallback
- WP_REST_Response
- .ajax_toggle_theme
- SitePulse
- Tooltip
- Sitepulse_Remote_Backups
- Toast
- remove
- Qi
- Carousel
- ._queueCallback
- Carousel
- lt
- Carousel
- Backdrop
- RemoteBackupCommandsTest
- getWindow
- Modal
- Backdrop
- Offcanvas
- bootstrap.min.js
- be
- Offcanvas
- R
- Offcanvas
- Sitepulse_Frontend
- Sitepulse_Onboarding
- has_action
- contains
- wp_parse_url
- remove
- detectOverflow
- delete_option
- BaseComponent
- RemoteUpdatesTest
- Zs
- Dropdown
- Dropdown
- Dropdown
- Tab
- Tab
- Sitepulse_Plugin
- TemplateFactory
- Tab
- onboarding.js
- Sitepulse_Remote_Management
- Sitepulse_Profiler
- blueprint.json
- WP_Error
- BaseComponent
- AutoloadRestApiTest
- Dropdown
- remove
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
- get_transient
- Sitepulse_Utils
- ts
- Swipe
- addHandler
- get_option
- Qn
- Offcanvas
- Toast
- Backdrop
- Sitepulse_Settings
- Zi
- Ce
- Es
- PluginTest
- RemoteManagementTest
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
- RemoteInventoryTest
- SitePulse — See What's Powering (or Slowing) Your Site
- Sitepulse_Monitoring
- Sitepulse_Easy_Mode
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
- Sitepulse_Backend
- update_option
- browser
- backend.test.cjs
- Ln
- LifecycleTest
- WP_REST_Request
- RestApiTest
- popperGenerator
- coverage.cjs
- browser.cjs
- Sitepulse_Cron_Fallback
- Modal
- DiagnosticTemplateCoverageTest
- getBoundingClientRect
- js-coverage.cjs
- Sitepulse_Api_Service
- Me
- Automated tests
- CoverageGateTest
- Sitepulse_Test_Backups
- CoreVariantsTest
- ApiVariantsTest
- Swipe
- WordPress.php
- Sitepulse_Page_Tracker
- Sitepulse_Test_Case
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- RemoteClientTest
- Config
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- Sitepulse_AI_Diagnostic_Cron
- PetAssistantTemplateTest
- Dashboard (portal) endpoints
- do_action
- B
- .set_auto_updates
- TemplateRenderingTest
- RecentDashboardTest
- Sitepulse_Remote_Health
- api_backend.php
- Sitepulse_Remote_Events
- Sitepulse_Remote_Inventory
- AI Diagnostics
- Sitepulse_Remote_Updates
- ProfilerCompatibilityTest
- sitepulse_test_subprocess
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- StoredDataRobustnessTest
- BackWPup_Job
- is_wp_error
- RemoteManagement.php
- Installation
- ot
- Sitepulse_Test_Database
- Wi
- BackWPup
- Sitepulse_Test_Local_Filesystem
- TemplateTest
- Ie
- RecentActivityTest
- CLAUDE.md
- Signing

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 215 edges
2. `get_option()` - 164 edges
3. `Sitepulse_Test_WP` - 132 edges
4. `Sitepulse_Remote_Management` - 104 edges
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

## Communities (236 total, 129 thin omitted)

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
Cohesion: 0.07
Nodes (7): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, current_time(), is_email(), sanitize_email()

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
Cohesion: 0.14
Nodes (9): Backup commands, Backup preset format, Commands, `POST /api/website/remote/events`, `POST /api/website/remote/inventory`, Update commands, Update policy, DirectAccessTemplateTest (+1 more)

### Community 13 - "BaseComponent"
Cohesion: 0.07
Nodes (4): Backdrop, BaseComponent, Button, Config

### Community 14 - "TemplateFactory"
Cohesion: 0.09
Nodes (5): Config, getElement(), isElement(), TemplateFactory, toType()

### Community 16 - "bootstrap.bundle.js"
Cohesion: 0.08
Nodes (23): distanceAndSkiddingToXY(), getDataAttribute(), getDataAttributes(), getSideOffsets(), hide(), isAnySideFullyClipped(), normalizeData(), normalizeDataKey() (+15 more)

### Community 18 - "backend.js"
Cohesion: 0.09
Nodes (20): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike(), getScoreColor() (+12 more)

### Community 23 - ".ajax_toggle_theme"
Cohesion: 0.36
Nodes (4): check_ajax_referer(), Sitepulse_Test_Json_Response, wp_send_json_error(), wp_send_json_success()

### Community 24 - "SitePulse"
Cohesion: 0.10
Nodes (20): Admin Bar Widget, Basic View, Core Web Vitals, Cron Job Management, Developer View, Email Blocking, Fatal-Error Detection, Health Score System (+12 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.06
Nodes (3): Sitepulse_Remote_Backups, RemoteBackupsTest, UpdraftPlus_Options

### Community 35 - "Backdrop"
Cohesion: 0.13
Nodes (6): Backdrop, execute(), executeAfterTransition(), getTransitionDurationFromElement(), Swipe, triggerTransitionEnd()

### Community 37 - "getWindow"
Cohesion: 0.27
Nodes (18): applyStyles(), effect$2(), getClippingParents(), getComputedStyle$1(), getContainingBlock(), getNodeName(), getOffsetParent(), getParentNode() (+10 more)

### Community 39 - "Backdrop"
Cohesion: 0.14
Nodes (3): Backdrop, execute(), Swipe

### Community 41 - "bootstrap.min.js"
Cohesion: 0.12
Nodes (16): B(), D(), I(), j(), M(), N(), O(), off() (+8 more)

### Community 44 - "R"
Cohesion: 0.08
Nodes (5): getDataAttributes(), H(), R, Ti, W

### Community 46 - "Sitepulse_Frontend"
Cohesion: 0.15
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Onboarding"
Cohesion: 0.05
Nodes (7): Sitepulse_Onboarding, Sitepulse_Setup, CoreRegressionTest, HarnessIsolationTest, ProDetectionTest, SetupOnboardingTest, get_current_screen()

### Community 48 - "has_action"
Cohesion: 0.11
Nodes (6): Sitepulse_Cron_Manager, CronTest, has_action(), wp_get_scheduled_event(), wp_get_schedules(), wp_schedule_event()

### Community 49 - "contains"
Cohesion: 0.18
Nodes (3): contains(), effect$1(), Tab

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 52 - "detectOverflow"
Cohesion: 0.15
Nodes (23): arrow(), computeAutoPlacement(), computeOffsets(), computeStyles(), detectOverflow(), expandToHashMap(), flip(), getAltAxis() (+15 more)

### Community 53 - "delete_option"
Cohesion: 0.10
Nodes (11): Architecture, sitepulse_sanitize_param(), Sitepulse_Loader, submit_button(), wp_nonce_field(), delete_option(), esc_attr(), esc_html() (+3 more)

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Plugin"
Cohesion: 0.11
Nodes (4): Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest, has_filter()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Remote_Management"
Cohesion: 0.08
Nodes (4): Sitepulse_Remote_Management, is_multisite(), site_url(), wp_schedule_single_event()

### Community 68 - "blueprint.json"
Cohesion: 0.12
Nodes (16): Automated Test Setup, WordPress Playground, features, networking, landingPage, meta, author, categories (+8 more)

### Community 69 - "WP_Error"
Cohesion: 0.12
Nodes (9): sitepulse_check_memory(), sitepulse_collect_plugin_data(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_enable_save_queries(), sitepulse_get_plugin_profiler_stats(), RestCoverageTest, WP_Error (+1 more)

### Community 70 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 71 - "AutoloadRestApiTest"
Cohesion: 0.20
Nodes (5): sitepulse_get_autoload_options(), sitepulse_get_protected_options(), sitepulse_update_autoload_option(), AutoloadRestApiTest, wp_cache_delete()

### Community 73 - "remove"
Cohesion: 0.07
Nodes (4): an, es, remove(), W

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 78 - "Sitepulse_Remote_Client"
Cohesion: 0.09
Nodes (3): Sitepulse_Remote_Client, ApiBaseTest, RemoteApiMockTest

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.08
Nodes (3): Sitepulse_Page_Analysis, PageAnalysisTest, number_format_i18n()

### Community 83 - "plugin_basename"
Cohesion: 0.09
Nodes (16): inc/ helpers, sitepulse_enable_clear_load_events(), sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), gethostbyname(), gethostname() (+8 more)

### Community 86 - "get_transient"
Cohesion: 0.24
Nodes (3): delete_transient(), get_transient(), set_transient()

### Community 90 - "addHandler"
Cohesion: 0.16
Nodes (17): addHandler(), bootstrapDelegationHandler(), bootstrapHandler(), find(), findHandler(), focusableChildren(), getElementEvents(), getTypeEvent() (+9 more)

### Community 91 - "get_option"
Cohesion: 0.05
Nodes (10): Sitepulse_Product_Analytics, Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, absint(), get_option(), sanitize_hex_color(), sanitize_key(), sanitize_text_field() (+2 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.07
Nodes (6): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters(), _get_cron_array()

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 101 - "RemoteManagementTest"
Cohesion: 0.24
Nodes (3): RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response()

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
Cohesion: 0.17
Nodes (6): PHPUnit\Framework\TestCase, sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 142 - "Sitepulse_Monitoring"
Cohesion: 0.20
Nodes (3): Sitepulse_Monitoring, MonitoringTest, delete_metadata()

### Community 143 - "Sitepulse_Easy_Mode"
Cohesion: 0.07
Nodes (14): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, sitepulse_reset_onboarding(), sitepulse_set_sp_profiler(), sitepulse_set_trackers_disabled_notice(), sitepulse_set_wpslowhttp(), EasyModeTest, GettingStartedTest (+6 more)

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
Nodes (13): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), human_time_diff(), load_textdomain(), _n(), remove_all_actions() (+5 more)

### Community 151 - "add_action"
Cohesion: 0.11
Nodes (7): add_action(), add_filter(), remove_action(), remove_filter(), sitepulse_test_callback_id(), Sitepulse_Test_Hook, wp_doing_cron()

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 157 - "Sitepulse_Backend"
Cohesion: 0.16
Nodes (4): Sitepulse_Backend, add_menu_page(), current_user_can(), is_admin_bar_showing()

### Community 158 - "update_option"
Cohesion: 0.05
Nodes (7): Sitepulse_Plugin_Profiler, sitepulse_check_vulnerabilities_api(), ApiRegressionTest, CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataWritePathsTest, update_option()

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "WP_REST_Request"
Cohesion: 0.10
Nodes (4): ArrayAccess, sitepulse_get_curl_stats(), sitepulse_get_profiler_stats(), WP_REST_Request

### Community 164 - "RestApiTest"
Cohesion: 0.09
Nodes (7): sitepulse_complete_onboarding(), sitepulse_get_memory_info(), sitepulse_register_route(), sitepulse_rest_pre_dispatch(), sitepulse_update_settings(), RestApiTest, register_rest_route()

### Community 165 - "popperGenerator"
Cohesion: 0.15
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 168 - "Sitepulse_Cron_Fallback"
Cohesion: 0.13
Nodes (3): Sitepulse_Cron_Fallback, CronCoverageTest, fastcgi_finish_request()

### Community 172 - "getBoundingClientRect"
Cohesion: 0.26
Nodes (15): getBoundingClientRect(), getClientRectFromMixedType(), getCompositeRect(), getDocumentElement(), getDocumentRect(), getHTMLElementScroll(), getInnerBoundingClientRect(), getNodeScroll() (+7 more)

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "Sitepulse_Api_Service"
Cohesion: 0.06
Nodes (5): Sitepulse_Api_Service, ApiCoverageTest, ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 178 - "Sitepulse_Test_Backups"
Cohesion: 0.10
Nodes (4): BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, UpdraftPlus

### Community 182 - "WordPress.php"
Cohesion: 0.04
Nodes (56): RuntimeException, wc_get_page_id(), get_permalink(), get_the_title(), sitepulse_test_site_pages(), add_option(), add_query_arg(), add_submenu_page() (+48 more)

### Community 183 - "Sitepulse_Page_Tracker"
Cohesion: 0.07
Nodes (5): Sitepulse_Page_Tracker, wp_debug_backtrace_summary(), PageTrackerTest, get_num_queries(), sitepulse_test_post_type()

### Community 184 - "Sitepulse_Test_Case"
Cohesion: 0.14
Nodes (3): EasyModeSaveQueriesTest, HttpOriginTest, Sitepulse_Test_Case

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "Sitepulse_AI_Diagnostic_Cron"
Cohesion: 0.20
Nodes (3): Sitepulse_AI_Diagnostic_Cron, sitepulse_check_ai_diagnostic_status_api(), sitepulse_request_ai_diagnostic_api()

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.10
Nodes (20): Backup warnings, Dashboard (portal) endpoints, Development, Endpoints, Key rotation, Monthly reports, Notifications, `POST /api/website/remote/check-in` (+12 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 203 - ".set_auto_updates"
Cohesion: 0.14
Nodes (6): Phases, get_plugins(), get_site_option(), Sitepulse_Test_Theme, wp_get_theme(), wp_get_themes()

### Community 206 - "Sitepulse_Remote_Health"
Cohesion: 0.12
Nodes (4): Sitepulse_Remote_Health, get_template(), maybe_unserialize(), wp_normalize_path()

### Community 209 - "api_backend.php"
Cohesion: 0.24
Nodes (13): sitepulse_dismiss_getting_started(), sitepulse_enable_clear_curl_api_events(), sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report(), sitepulse_page_analysis_response(), sitepulse_page_analysis_sample() (+5 more)

### Community 211 - "Sitepulse_Remote_Inventory"
Cohesion: 0.13
Nodes (6): Sitepulse_Remote_Inventory, disk_free_space(), get_filesystem_method(), is_plugin_active(), wp_get_translation_updates(), wp_is_file_mod_allowed()

### Community 212 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 213 - "Sitepulse_Remote_Updates"
Cohesion: 0.14
Nodes (3): Sitepulse_Remote_Updates, Automatic_Upgrader_Skin, get_theme_root()

### Community 219 - "BackWPup_Job"
Cohesion: 0.14
Nodes (3): BackWPup_Job, trailingslashit(), wp_delete_file()

### Community 220 - "is_wp_error"
Cohesion: 0.22
Nodes (8): is_wp_error(), wp_generate_password(), wp_json_encode(), wp_rand(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 221 - "RemoteManagement.php"
Cohesion: 0.13
Nodes (9): BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader, updraft_try_include_file() (+1 more)

### Community 224 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

### Community 229 - "BackWPup"
Cohesion: 0.18
Nodes (3): BackWPup, BackWPup_Cron, update_site_option()

### Community 234 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

### Community 235 - "Signing"
Cohesion: 0.50
Nodes (4): API → site, Signing, Site → API, Test vector

## Knowledge Gaps
- **262 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+257 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1294 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **129 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `VersionBumpTest`, `Sitepulse_CurLoader`, `Commands`, `RemoteInventoryTest`, `Sitepulse_Monitoring`, `Sitepulse_Easy_Mode`, `.ajax_toggle_theme`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Remote_Backups`, `update_option`, `LifecycleTest`, `RemoteBackupCommandsTest`, `RestApiTest`, `Sitepulse_Cron_Fallback`, `DiagnosticTemplateCoverageTest`, `Sitepulse_Api_Service`, `Sitepulse_Onboarding`, `has_action`, `Sitepulse_Frontend`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Page_Tracker`, `RemoteUpdatesTest`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `Sitepulse_Plugin`, `ErrorHandlerVariantsTest`, `Sitepulse_Profiler`, `WP_Error`, `AutoloadRestApiTest`, `PetAssistantTemplateTest`, `do_action`, `TemplateRenderingTest`, `RecentDashboardTest`, `Sitepulse_Remote_Client`, `Sitepulse_Page_Analysis`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `RemoteVariantsTest`, `StoredDataRobustnessTest`, `get_option`, `Sitepulse_Settings`, `PluginTest`, `RemoteManagementTest`, `PopulatedTemplateTest`, `TemplateTest`, `RecentActivityTest`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does ``POST /api/website/remote/inventory`` connect `Commands` to `Dashboard (portal) endpoints`, `page-analysis.js`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Monitoring`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `Sitepulse_Remote_Backups`, `WP_REST_Request`, `RemoteBackupCommandsTest`, `RestApiTest`, `Sitepulse_Cron_Fallback`, `DiagnosticTemplateCoverageTest`, `Sitepulse_Api_Service`, `Sitepulse_Onboarding`, `has_action`, `Sitepulse_Frontend`, `wp_parse_url`, `Sitepulse_Test_Backups`, `delete_option`, `WordPress.php`, `Sitepulse_Page_Tracker`, `RemoteUpdatesTest`, `Sitepulse_Plugin`, `Sitepulse_Remote_Management`, `Sitepulse_Profiler`, `WP_Error`, `Sitepulse_AI_Diagnostic_Cron`, `do_action`, `TemplateRenderingTest`, `Sitepulse_Remote_Client`, `api_backend.php`, `Sitepulse_Page_Analysis`, `plugin_basename`, `Sitepulse_Remote_Events`, `get_transient`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `StoredDataRobustnessTest`, `get_option`, `is_wp_error`, `BackWPup_Job`, `Sitepulse_Settings`, `Sitepulse_Test_Database`, `RemoteManagementTest`, `PopulatedTemplateTest`, `RecentActivityTest`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Are the 211 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 211 INFERRED edges - model-reasoned connections that need verification._
- **Are the 159 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 159 INFERRED edges - model-reasoned connections that need verification._
- **Are the 44 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 44 INFERRED edges - model-reasoned connections that need verification._
- **Are the 65 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 65 INFERRED edges - model-reasoned connections that need verification._
# Graph Report - sitepulse  (2026-10-16)

## Corpus Check
- 180 files · ~368,078 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4453 nodes · 10214 edges · 237 communities (101 shown, 136 thin omitted)
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 2364 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `ab8200e6`
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
- current_user_can
- Sitepulse_Easy_Mode
- SitePulse
- Tooltip
- Sitepulse_Remote_Backups
- Toast
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
- R
- Offcanvas
- Sitepulse_Frontend
- Sitepulse_Onboarding
- Sitepulse_Remote_Inventory
- contains
- Sitepulse_Remote_Management
- remove
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
- Sitepulse_Test_Case
- Sitepulse_Profiler
- blueprint.json
- addHandler
- BaseComponent
- detectOverflow
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
- popperGenerator
- getDocumentElement
- Qn
- Offcanvas
- Toast
- Backdrop
- Sitepulse_Settings
- Zi
- Ce
- Es
- PluginTest
- sitepulse_test_remote_response
- PopulatedTemplateTest
- FocusTrap
- sitepulse_global.js
- Sitepulse_Cron_Manager
- Repository Guidelines
- SitePulse Logo
- WordPress Pulse Logo
- CommandCode
- Screen
- SitePulse WordPress Plugin Icon
- SitePulse Logo
- Sitepulse Plugin Icon
- Sitepulse Logo
- VersionBumpTest
- Sitepulse_CurLoader
- Sitepulse_Test_Database
- SitePulse — See What's Powering (or Slowing) Your Site
- remove
- AutoloadRestApiTest
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
- browser.cjs
- backend.test.cjs
- Ln
- LifecycleTest
- WP_REST_Request
- RestApiTest
- Sitepulse_Plugin
- home_url
- WP_REST_Response
- RemoteManagement.php
- Modal
- DiagnosticTemplateCoverageTest
- RestCoverageTest
- js-coverage.cjs
- Sitepulse_Api_Service
- Me
- Automated tests
- CoverageGateTest
- api_backend.php
- CoreVariantsTest
- ApiVariantsTest
- Swipe
- WordPress.php
- Sitepulse_Page_Tracker
- CronCoverageTest
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- RemoteClientTest
- ot
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- get_option
- WP_Filesystem
- Dashboard (portal) endpoints
- WP_Error
- B
- Wi
- TemplateRenderingTest
- RecentDashboardTest
- FocusTrap
- PetAssistantTemplateTest
- Ie
- Sitepulse_Remote_Health
- RemoteInventoryTest
- page-analysis.js
- ProfilerCompatibilityTest
- sitepulse_test_subprocess
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- StoredDataRobustnessTest
- Architecture
- is_wp_error
- Sitepulse_Test_Backups
- update_option
- Remote management
- apply_filters
- .time
- RecentActivityTest
- .admin_notice_onboarding
- TemplateTest
- wp_clear_scheduled_hook
- ActionScheduler_Store
- CLAUDE.md
- AI Diagnostics
- Installation

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 213 edges
2. `get_option()` - 164 edges
3. `Sitepulse_Test_WP` - 131 edges
4. `Sitepulse_Remote_Management` - 99 edges
5. `Sitepulse_Profiler` - 74 edges
6. `Sitepulse_Api_Service` - 73 edges
7. `Sitepulse_Remote_Backups` - 68 edges
8. `Sitepulse_Page_Analysis` - 67 edges
9. `Sitepulse_Test_Case` - 67 edges
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

## Communities (237 total, 136 thin omitted)

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
Nodes (8): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, wp_mail(), current_time(), is_email(), sanitize_email()

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
Cohesion: 0.18
Nodes (10): Backup commands, Backup preset format, Commands, Endpoints, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/events`, `POST /api/website/remote/inventory` (+2 more)

### Community 13 - "BaseComponent"
Cohesion: 0.07
Nodes (4): Backdrop, BaseComponent, Button, Config

### Community 14 - "TemplateFactory"
Cohesion: 0.09
Nodes (5): Config, getElement(), isElement(), TemplateFactory, toType()

### Community 16 - "bootstrap.bundle.js"
Cohesion: 0.07
Nodes (23): distanceAndSkiddingToXY(), getDataAttribute(), getDataAttributes(), getSideOffsets(), hide(), isAnySideFullyClipped(), normalizeData(), normalizeDataKey() (+15 more)

### Community 18 - "backend.js"
Cohesion: 0.09
Nodes (20): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike(), getScoreColor() (+12 more)

### Community 20 - "TemplateFactory"
Cohesion: 0.09
Nodes (3): Config, sanitizeHtml(), TemplateFactory

### Community 22 - "current_user_can"
Cohesion: 0.10
Nodes (16): sitepulse_register_route(), RuntimeException, HarnessIsolationTest, add_menu_page(), check_admin_referer(), check_ajax_referer(), current_user_can(), register_rest_route() (+8 more)

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.07
Nodes (13): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, sitepulse_set_sp_profiler(), sitepulse_set_trackers_disabled_notice(), sitepulse_set_wpslowhttp(), EasyModeTest, GettingStartedTest, get_current_user_id() (+5 more)

### Community 24 - "SitePulse"
Cohesion: 0.10
Nodes (20): Admin Bar Widget, Basic View, Core Web Vitals, Cron Job Management, Developer View, Email Blocking, Fatal-Error Detection, Health Score System (+12 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.05
Nodes (4): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup_Job, UpdraftPlus_Options

### Community 28 - "do_action"
Cohesion: 0.15
Nodes (4): HttpOriginTest, RemoteEventsTest, do_action(), get_theme_root()

### Community 35 - "Backdrop"
Cohesion: 0.13
Nodes (6): Backdrop, execute(), executeAfterTransition(), getTransitionDurationFromElement(), Swipe, triggerTransitionEnd()

### Community 37 - "getBoundingClientRect"
Cohesion: 0.21
Nodes (24): applyStyles(), effect$2(), getBoundingClientRect(), getClippingParents(), getCompositeRect(), getComputedStyle$1(), getContainingBlock(), getHTMLElementScroll() (+16 more)

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
Cohesion: 0.06
Nodes (5): Sitepulse_Onboarding, Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest

### Community 48 - "Sitepulse_Remote_Inventory"
Cohesion: 0.10
Nodes (8): Sitepulse_Remote_Inventory, disk_free_space(), get_core_updates(), get_filesystem_method(), get_site_transient(), is_plugin_active(), wp_get_translation_updates(), wp_is_file_mod_allowed()

### Community 49 - "contains"
Cohesion: 0.18
Nodes (3): contains(), effect$1(), Tab

### Community 50 - "Sitepulse_Remote_Management"
Cohesion: 0.12
Nodes (3): Sitepulse_Remote_Management, RemoteManagementTest, wp_generate_uuid4()

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 52 - "Sitepulse_Remote_Events"
Cohesion: 0.09
Nodes (5): Sitepulse_Remote_Events, get_bloginfo(), get_file_data(), remove_action(), remove_filter()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 55 - "Sitepulse_Remote_Updates"
Cohesion: 0.15
Nodes (3): Sitepulse_Remote_Updates, RemoteUpdatesTest, wp_next_scheduled()

### Community 62 - "Sitepulse_Backend"
Cohesion: 0.11
Nodes (4): Sitepulse_Backend, BackendCoverageTest, has_filter(), sitepulse_test_callback_id()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Test_Case"
Cohesion: 0.15
Nodes (3): DirectAccessTemplateTest, EasyModeSaveQueriesTest, Sitepulse_Test_Case

### Community 68 - "blueprint.json"
Cohesion: 0.12
Nodes (16): Automated Test Setup, WordPress Playground, features, networking, landingPage, meta, author, categories (+8 more)

### Community 69 - "addHandler"
Cohesion: 0.16
Nodes (17): addHandler(), bootstrapDelegationHandler(), bootstrapHandler(), find(), findHandler(), focusableChildren(), getElementEvents(), getTypeEvent() (+9 more)

### Community 70 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 71 - "detectOverflow"
Cohesion: 0.17
Nodes (21): arrow(), computeAutoPlacement(), computeOffsets(), computeStyles(), detectOverflow(), expandToHashMap(), flip(), getAltAxis() (+13 more)

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

### Community 83 - "plugin_basename"
Cohesion: 0.09
Nodes (15): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), gethostbyname(), gethostname(), register_activation_hook() (+7 more)

### Community 86 - "get_transient"
Cohesion: 0.12
Nodes (6): Sitepulse_Cron_Fallback, CronTest, delete_transient(), get_transient(), has_action(), set_transient()

### Community 90 - "popperGenerator"
Cohesion: 0.15
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "getDocumentElement"
Cohesion: 0.27
Nodes (11): getClientRectFromMixedType(), getClippingRect(), getDocumentElement(), getDocumentRect(), getInnerBoundingClientRect(), getUAString(), getViewportRect(), getWindowScroll() (+3 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.11
Nodes (3): Sitepulse_Settings, ActionSchedulerTest, _get_cron_array()

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.22
Nodes (3): PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "Sitepulse_Cron_Manager"
Cohesion: 0.17
Nodes (4): Sitepulse_Cron_Manager, wp_get_scheduled_event(), wp_get_schedules(), wp_schedule_event()

### Community 106 - "Repository Guidelines"
Cohesion: 0.25
Nodes (7): Build, Test, and Development Commands, Coding Style & Naming Conventions, Commit & Pull Request Guidelines, graphify, Project Structure & Module Organization, Repository Guidelines, Testing Guidelines

### Community 107 - "SitePulse Logo"
Cohesion: 0.50
Nodes (4): Heartbeat Waveform, SitePulse, SitePulse Logo, Status Indicator Dots

### Community 108 - "WordPress Pulse Logo"
Cohesion: 0.67
Nodes (3): Pulse Waveform, WordPress, WordPress Pulse Logo

### Community 110 - "Screen"
Cohesion: 0.29
Nodes (6): api(), run(), sample(), Screen(), wait(), waitForVisit()

### Community 136 - "VersionBumpTest"
Cohesion: 0.19
Nodes (6): PHPUnit\Framework\TestCase, sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 143 - "AutoloadRestApiTest"
Cohesion: 0.15
Nodes (8): sitepulse_get_autoload_options(), sitepulse_get_protected_options(), sitepulse_sanitize_param(), sitepulse_update_autoload_option(), AutoloadRestApiTest, absint(), wp_cache_delete(), wp_kses_post()

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
Cohesion: 0.10
Nodes (10): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), human_time_diff(), load_textdomain(), _n(), remove_all_actions() (+2 more)

### Community 151 - "add_action"
Cohesion: 0.15
Nodes (3): add_action(), add_filter(), Sitepulse_Test_Hook

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 159 - "browser.cjs"
Cohesion: 0.07
Nodes (40): ref_node_assert, ref_node_path, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse } (+32 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 164 - "RestApiTest"
Cohesion: 0.10
Nodes (7): sitepulse_check_vulnerabilities_api(), sitepulse_complete_onboarding(), sitepulse_get_memory_info(), sitepulse_request_ai_diagnostic_api(), sitepulse_rest_pre_dispatch(), sitepulse_update_settings(), RestApiTest

### Community 166 - "home_url"
Cohesion: 0.10
Nodes (16): add_query_arg(), admin_url(), esc_js(), esc_url_raw(), get_current_screen(), home_url(), remove_query_arg(), rest_url() (+8 more)

### Community 168 - "RemoteManagement.php"
Cohesion: 0.13
Nodes (10): Automatic_Upgrader_Skin, BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader (+2 more)

### Community 172 - "RestCoverageTest"
Cohesion: 0.18
Nodes (5): sitepulse_check_memory(), sitepulse_collect_plugin_data(), sitepulse_dismiss_onboarding(), sitepulse_enable_save_queries(), RestCoverageTest

### Community 173 - "js-coverage.cjs"
Cohesion: 0.06
Nodes (35): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-instrument (+27 more)

### Community 174 - "Sitepulse_Api_Service"
Cohesion: 0.07
Nodes (5): Sitepulse_Api_Service, ApiCoverageTest, ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 178 - "api_backend.php"
Cohesion: 0.23
Nodes (14): sitepulse_dismiss_getting_started(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events(), sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report(), sitepulse_page_analysis_response() (+6 more)

### Community 182 - "WordPress.php"
Cohesion: 0.05
Nodes (40): get_permalink(), get_the_title(), wp_strip_all_tags(), add_option(), add_submenu_page(), delete_user_meta(), did_action(), fastcgi_finish_request() (+32 more)

### Community 183 - "Sitepulse_Page_Tracker"
Cohesion: 0.08
Nodes (4): Sitepulse_Page_Tracker, wp_debug_backtrace_summary(), PageTrackerTest, sitepulse_test_post_type()

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.20
Nodes (6): assert, { browser, jsonResponse }, data, i18n, setup(), test

### Community 198 - "get_option"
Cohesion: 0.06
Nodes (7): Sitepulse_Product_Analytics, Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, get_option(), sanitize_hex_color(), sanitize_key(), wp_parse_args()

### Community 199 - "WP_Filesystem"
Cohesion: 0.20
Nodes (4): trailingslashit(), untrailingslashit(), wp_delete_file(), WP_Filesystem()

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.16
Nodes (12): Backup warnings, Dashboard (portal) endpoints, Development, Key rotation, Monthly reports, Notifications, Roles, Rollouts (+4 more)

### Community 201 - "WP_Error"
Cohesion: 0.18
Nodes (8): sitepulse_check_ai_diagnostic_status_api(), sitepulse_dismiss_onboarding_notice(), sitepulse_get_curl_stats(), sitepulse_get_plugin_profiler_stats(), sitepulse_get_profiler_stats(), sitepulse_reset_onboarding(), WP_Error, wp_verify_nonce()

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 211 - "Sitepulse_Remote_Health"
Cohesion: 0.06
Nodes (12): Sitepulse_Remote_Health, Phases, Update commands, sitepulse_test_site_pages(), doing_action(), get_plugins(), get_template(), maybe_unserialize() (+4 more)

### Community 213 - "page-analysis.js"
Cohesion: 0.42
Nodes (14): card(), el(), format(), icon(), metric(), renderBreakdown(), row(), renderCallbacks() (+6 more)

### Community 219 - "Architecture"
Cohesion: 0.19
Nodes (4): Architecture, Sitepulse_Loader, delete_metadata(), delete_option()

### Community 220 - "is_wp_error"
Cohesion: 0.31
Nodes (6): is_wp_error(), wp_json_encode(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 221 - "Sitepulse_Test_Backups"
Cohesion: 0.08
Nodes (6): BackWPup, BackWPup_Cron, BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, UpdraftPlus

### Community 224 - "update_option"
Cohesion: 0.06
Nodes (5): Sitepulse_AI_Diagnostic_Cron, ApiRegressionTest, CoreAdditionalBehaviorTest, StoredDataWritePathsTest, update_option()

### Community 225 - "Remote management"
Cohesion: 0.25
Nodes (7): API → site, Remote management, Retention, Signing, Site → API, Test vector, When it is active

### Community 230 - ".admin_notice_onboarding"
Cohesion: 0.27
Nodes (6): submit_button(), wp_nonce_field(), esc_attr(), esc_html(), esc_html_e(), esc_url()

### Community 234 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

### Community 235 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 236 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

## Knowledge Gaps
- **263 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+258 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1290 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **136 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does ``POST /api/website/remote/inventory`` connect `Commands` to `Screen`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `VersionBumpTest`, `Sitepulse_CurLoader`, `AutoloadRestApiTest`, `current_user_can`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Remote_Backups`, `do_action`, `Sitepulse_Plugin_Profiler`, `LifecycleTest`, `RemoteBackupCommandsTest`, `Sitepulse_Plugin`, `RestApiTest`, `DiagnosticTemplateCoverageTest`, `RestCoverageTest`, `Sitepulse_Api_Service`, `Sitepulse_Onboarding`, `Sitepulse_Frontend`, `Sitepulse_Remote_Management`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Monitoring`, `Sitepulse_Page_Tracker`, `CronCoverageTest`, `Sitepulse_Remote_Updates`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Backend`, `Sitepulse_Profiler`, `get_option`, `TemplateRenderingTest`, `RecentDashboardTest`, `Sitepulse_Remote_Client`, `PetAssistantTemplateTest`, `Sitepulse_Page_Analysis`, `RemoteInventoryTest`, `get_transient`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `RemoteVariantsTest`, `StoredDataRobustnessTest`, `Sitepulse_Settings`, `update_option`, `apply_filters`, `PluginTest`, `RecentActivityTest`, `PopulatedTemplateTest`, `TemplateTest`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Test_Database`, `current_user_can`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `Sitepulse_Remote_Backups`, `do_action`, `Sitepulse_Plugin_Profiler`, `RestApiTest`, `RemoteBackupCommandsTest`, `RestCoverageTest`, `Sitepulse_Api_Service`, `Sitepulse_Onboarding`, `Sitepulse_Frontend`, `Sitepulse_Remote_Management`, `api_backend.php`, `Sitepulse_Remote_Events`, `Sitepulse_Monitoring`, `WordPress.php`, `Sitepulse_Page_Tracker`, `Sitepulse_Remote_Updates`, `Sitepulse_Backend`, `Sitepulse_Profiler`, `get_option`, `WP_Error`, `TemplateRenderingTest`, `Sitepulse_Remote_Client`, `Sitepulse_Page_Analysis`, `plugin_basename`, `get_transient`, `Sitepulse_Utils`, `ProfilerCompatibilityTest`, `StoredDataRobustnessTest`, `Architecture`, `is_wp_error`, `Sitepulse_Test_Backups`, `Sitepulse_Settings`, `apply_filters`, `.time`, `RecentActivityTest`, `PopulatedTemplateTest`, `sitepulse_test_remote_response`, `Sitepulse_Cron_Manager`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Are the 209 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 209 INFERRED edges - model-reasoned connections that need verification._
- **Are the 159 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 159 INFERRED edges - model-reasoned connections that need verification._
- **Are the 43 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 43 INFERRED edges - model-reasoned connections that need verification._
- **Are the 62 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 62 INFERRED edges - model-reasoned connections that need verification._
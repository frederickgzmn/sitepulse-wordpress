# Graph Report - sitepulse  (2026-10-15)

## Corpus Check
- 180 files · ~367,797 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4452 nodes · 10210 edges · 228 communities (100 shown, 128 thin omitted)
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 2362 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `262d87e4`
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
- .time
- Toast
- Sitepulse_Test_WP
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
- home_url
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
- EasyModeSaveQueriesTest
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
- Sitepulse_Cron_Fallback
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
- PageTrackerTest
- PopulatedTemplateTest
- FocusTrap
- sitepulse_global.js
- Sitepulse_Cron_Manager
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
- update_option
- browser
- backend.test.cjs
- Ln
- LifecycleTest
- browser.cjs
- WP_REST_Request
- Sitepulse_Plugin
- admin_url
- WP_REST_Response
- RemoteManagement.php
- Modal
- DiagnosticTemplateCoverageTest
- RestCoverageTest
- js-coverage.cjs
- get_option
- Me
- Automated tests
- CoverageGateTest
- coverage.cjs
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
- Sitepulse_Whitelabel_Service
- sanitize_text_field
- Dashboard (portal) endpoints
- get_transient
- B
- Wi
- TemplateRenderingTest
- RecentDashboardTest
- FocusTrap
- PetAssistantTemplateTest
- Ie
- Sitepulse_Remote_Health
- RemoteInventoryTest
- api-variants.php
- ProfilerCompatibilityTest
- sitepulse_test_subprocess
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- Sitepulse_Test_Case
- Architecture
- get_plugins
- Sitepulse_Test_Backups
- Sitepulse_AI_Diagnostic_Cron
- Remote management
- FrontendTest

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
- `Architecture` --references--> `Sitepulse_CurLoader`  [INFERRED]
  CLAUDE.md → class/curloader.php
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

## Communities (228 total, 128 thin omitted)

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
Cohesion: 0.13
Nodes (12): Backup commands, Backup preset format, Commands, Endpoints, `POST /api/website/remote/check-in`, `POST /api/website/remote/events`, `POST /api/website/remote/inventory`, `POST /api/website/remote/key` (+4 more)

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
Cohesion: 0.14
Nodes (14): sitepulse_register_route(), RuntimeException, check_admin_referer(), check_ajax_referer(), current_user_can(), register_rest_route(), Sitepulse_Test_Die, Sitepulse_Test_Json_Response (+6 more)

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.07
Nodes (12): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, sitepulse_set_sp_profiler(), sitepulse_set_trackers_disabled_notice(), sitepulse_set_wpslowhttp(), EasyModeTest, GettingStartedTest, get_current_user_id() (+4 more)

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - ".time"
Cohesion: 0.07
Nodes (4): Sitepulse_Remote_Backups, human_time_diff(), UpdraftPlus_Options, wp_schedule_single_event()

### Community 28 - "Sitepulse_Test_WP"
Cohesion: 0.05
Nodes (12): ApiCoverageTest, HarnessIsolationTest, add_menu_page(), add_option(), add_submenu_page(), get_core_updates(), get_site_transient(), Sitepulse_Test_WP (+4 more)

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
Cohesion: 0.23
Nodes (3): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar

### Community 47 - "Sitepulse_Onboarding"
Cohesion: 0.06
Nodes (5): Sitepulse_Onboarding, Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest

### Community 48 - "Sitepulse_Remote_Inventory"
Cohesion: 0.11
Nodes (8): Sitepulse_Remote_Inventory, Phases, disk_free_space(), get_filesystem_method(), get_site_option(), wp_get_themes(), wp_get_translation_updates(), wp_is_file_mod_allowed()

### Community 49 - "contains"
Cohesion: 0.18
Nodes (3): contains(), effect$1(), Tab

### Community 50 - "Sitepulse_Remote_Management"
Cohesion: 0.10
Nodes (4): Sitepulse_Remote_Management, RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response()

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 52 - "home_url"
Cohesion: 0.11
Nodes (11): get_permalink(), get_the_title(), sitepulse_test_site_pages(), delete_transient(), get_bloginfo(), get_post_types(), get_posts(), get_the_ID() (+3 more)

### Community 53 - "Sitepulse_Monitoring"
Cohesion: 0.11
Nodes (5): Sitepulse_Monitoring, MonitoringTest, is_admin(), is_admin_bar_showing(), wp_doing_ajax()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 55 - "Sitepulse_Remote_Updates"
Cohesion: 0.06
Nodes (9): Sitepulse_Remote_Events, Sitepulse_Remote_Updates, RemoteEventsTest, RemoteUpdatesTest, do_action(), get_file_data(), get_theme_root(), wp_doing_cron() (+1 more)

### Community 62 - "Sitepulse_Backend"
Cohesion: 0.11
Nodes (4): Sitepulse_Backend, BackendCoverageTest, has_filter(), sitepulse_test_callback_id()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.06
Nodes (3): Sitepulse_Profiler, ProfilerTest, RecentActivityTest

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
Cohesion: 0.07
Nodes (4): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n()

### Community 83 - "plugin_basename"
Cohesion: 0.14
Nodes (10): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), register_activation_hook(), register_deactivation_hook(), get_plugin_data() (+2 more)

### Community 86 - "Sitepulse_Cron_Fallback"
Cohesion: 0.17
Nodes (4): Sitepulse_Cron_Fallback, CronTest, fastcgi_finish_request(), has_action()

### Community 90 - "popperGenerator"
Cohesion: 0.15
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "getDocumentElement"
Cohesion: 0.27
Nodes (11): getClientRectFromMixedType(), getClippingRect(), getDocumentElement(), getDocumentRect(), getInnerBoundingClientRect(), getUAString(), getViewportRect(), getWindowScroll() (+3 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.07
Nodes (6): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters(), _get_cron_array()

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.22
Nodes (3): PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "Sitepulse_Cron_Manager"
Cohesion: 0.15
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

### Community 110 - "page-analysis.js"
Cohesion: 0.19
Nodes (20): api(), card(), el(), format(), icon(), metric(), renderBreakdown(), row() (+12 more)

### Community 136 - "VersionBumpTest"
Cohesion: 0.19
Nodes (6): PHPUnit\Framework\TestCase, sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 143 - "AutoloadRestApiTest"
Cohesion: 0.20
Nodes (5): sitepulse_get_autoload_options(), sitepulse_get_protected_options(), sitepulse_update_autoload_option(), AutoloadRestApiTest, wp_cache_delete()

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
Cohesion: 0.08
Nodes (14): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), remove_all_actions(), submit_button() (+6 more)

### Community 151 - "add_action"
Cohesion: 0.14
Nodes (5): add_action(), add_filter(), remove_action(), remove_filter(), Sitepulse_Test_Hook

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 158 - "update_option"
Cohesion: 0.06
Nodes (8): Sitepulse_Plugin_Profiler, sitepulse_check_vulnerabilities_api(), CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataWritePathsTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), update_option()

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 164 - "WP_REST_Request"
Cohesion: 0.05
Nodes (32): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding_notice(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events() (+24 more)

### Community 166 - "admin_url"
Cohesion: 0.10
Nodes (18): sitepulse_sanitize_param(), absint(), add_query_arg(), admin_url(), esc_attr(), esc_html(), esc_html_e(), esc_js() (+10 more)

### Community 168 - "RemoteManagement.php"
Cohesion: 0.06
Nodes (16): Automatic_Upgrader_Skin, BackWPup, BackWPup_Cron, BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Backwpup_Destination, Sitepulse_Test_Updraft_Admin (+8 more)

### Community 172 - "RestCoverageTest"
Cohesion: 0.23
Nodes (4): sitepulse_check_memory(), sitepulse_dismiss_onboarding(), sitepulse_enable_save_queries(), RestCoverageTest

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "get_option"
Cohesion: 0.09
Nodes (4): Sitepulse_Api_Service, ApiRegressionTest, ApiServiceTest, get_option()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 182 - "WordPress.php"
Cohesion: 0.08
Nodes (25): did_action(), get_post_status(), get_post_type(), get_post_type_object(), get_stylesheet(), is_404(), is_archive(), is_front_page() (+17 more)

### Community 183 - "Sitepulse_Page_Tracker"
Cohesion: 0.11
Nodes (3): Sitepulse_Page_Tracker, wp_debug_backtrace_summary(), get_num_queries()

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.08
Nodes (5): Sitepulse_Product_Analytics, Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, sanitize_key(), wp_parse_args()

### Community 199 - "sanitize_text_field"
Cohesion: 0.15
Nodes (5): remove_query_arg(), sanitize_text_field(), untrailingslashit(), wp_parse_url(), wp_unslash()

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.16
Nodes (12): Backup warnings, Dashboard (portal) endpoints, Key rotation, Monthly reports, Notifications, `POST /api/website/remote/command-result`, Roles, Rollouts (+4 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 211 - "Sitepulse_Remote_Health"
Cohesion: 0.12
Nodes (4): Sitepulse_Remote_Health, get_template(), maybe_unserialize(), wp_normalize_path()

### Community 213 - "api-variants.php"
Cohesion: 0.22
Nodes (5): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes()

### Community 218 - "Sitepulse_Test_Case"
Cohesion: 0.11
Nodes (4): DirectAccessTemplateTest, HttpOriginTest, StoredDataRobustnessTest, Sitepulse_Test_Case

### Community 219 - "Architecture"
Cohesion: 0.15
Nodes (4): Architecture, Sitepulse_Loader, delete_metadata(), delete_option()

### Community 220 - "get_plugins"
Cohesion: 0.20
Nodes (3): get_plugins(), Sitepulse_Test_Theme, wp_get_theme()

### Community 221 - "Sitepulse_Test_Backups"
Cohesion: 0.05
Nodes (10): Commands, Repo workflow, Testing notes, RemoteBackupsTest, BackWPup_Job, BackWPup_Option, Sitepulse_Test_Backups, UpdraftPlus (+2 more)

### Community 225 - "Remote management"
Cohesion: 0.22
Nodes (8): API → site, Development, Remote management, Retention, Signing, Site → API, Test vector, When it is active

## Knowledge Gaps
- **264 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+259 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1291 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **128 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does ``POST /api/website/remote/inventory`` connect `Commands` to `page-analysis.js`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `VersionBumpTest`, `Sitepulse_CurLoader`, `AutoloadRestApiTest`, `current_user_can`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Test_WP`, `update_option`, `LifecycleTest`, `RemoteBackupCommandsTest`, `Sitepulse_Plugin`, `WP_REST_Request`, `DiagnosticTemplateCoverageTest`, `RestCoverageTest`, `get_option`, `Sitepulse_Onboarding`, `Sitepulse_Frontend`, `Sitepulse_Remote_Management`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Monitoring`, `Sitepulse_Remote_Updates`, `CronCoverageTest`, `Sitepulse_Page_Tracker`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Backend`, `EasyModeSaveQueriesTest`, `Sitepulse_Profiler`, `Sitepulse_Whitelabel_Service`, `TemplateRenderingTest`, `RecentDashboardTest`, `Sitepulse_Remote_Client`, `PetAssistantTemplateTest`, `Sitepulse_Page_Analysis`, `RemoteInventoryTest`, `Sitepulse_Cron_Fallback`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `RemoteVariantsTest`, `Sitepulse_Test_Backups`, `Sitepulse_Settings`, `FrontendTest`, `PluginTest`, `PageTrackerTest`, `PopulatedTemplateTest`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Why does `Sitepulse_Remote_Management` connect `Sitepulse_Remote_Management` to `Commands`, `PresentationWordPress.php`, `current_user_can`, `Sitepulse_Remote_Activity`, `.time`, `Sitepulse_Test_WP`, `WP_REST_Request`, `RemoteBackupCommandsTest`, `RemoteManagement.php`, `Sitepulse_Remote_Inventory`, `home_url`, `Sitepulse_Monitoring`, `WordPress.php`, `Sitepulse_Remote_Updates`, `Dashboard (portal) endpoints`, `Sitepulse_Remote_Client`, `plugin_basename`, `Sitepulse_Remote_Health`, `Architecture`, `Sitepulse_Test_Backups`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Are the 209 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 209 INFERRED edges - model-reasoned connections that need verification._
- **Are the 159 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 159 INFERRED edges - model-reasoned connections that need verification._
- **Are the 43 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 43 INFERRED edges - model-reasoned connections that need verification._
- **Are the 62 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 62 INFERRED edges - model-reasoned connections that need verification._
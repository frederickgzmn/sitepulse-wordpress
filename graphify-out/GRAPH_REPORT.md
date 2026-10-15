# Graph Report - sitepulse  (2026-10-15)

## Corpus Check
- 172 files · ~341,824 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4180 nodes · 9312 edges · 225 communities (93 shown, 132 thin omitted)
- Extraction: 79% EXTRACTED · 21% INFERRED · 0% AMBIGUOUS · INFERRED: 1994 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `dd5ca0ce`
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
- CronCoverageTest
- St
- BaseComponent
- TemplateFactory
- .hide
- bootstrap.bundle.js
- Tooltip
- backend.js
- Fs
- TemplateFactory
- ._queueCallback
- current_user_can
- Sitepulse_Easy_Mode
- SitePulse
- Tooltip
- Sitepulse_Remote_Backups
- remove
- WordPress.php
- .hide
- Carousel
- ._queueCallback
- Carousel
- lt
- Carousel
- Backdrop
- Ti
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
- Collapse
- contains
- RemoteManagementTest
- remove
- Sitepulse_Setup
- Sitepulse_Remote_Management
- BaseComponent
- Qi
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
- EasyModeSaveQueriesTest
- Sitepulse_Profiler
- blueprint.json
- addHandler
- BaseComponent
- preventOverflow
- Dropdown
- W
- BaseComponent
- Toast
- ScrollSpy
- Modal
- Sitepulse_Backend
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- home_url
- Ss
- ScrollSpy
- get_transient
- Sitepulse_Utils
- ts
- Swipe
- popperGenerator
- detectOverflow
- Qn
- Offcanvas
- Toast
- Backdrop
- Sitepulse_Settings
- Zi
- Ce
- Es
- PluginTest
- WP_REST_Request
- PopulatedTemplateTest
- FocusTrap
- sitepulse_global.js
- RestApiTest
- Repository Guidelines
- SitePulse Logo
- WordPress Pulse Logo
- CommandCode
- page-analysis.js
- SitePulse WordPress Plugin Icon
- SitePulse Logo
- Sitepulse Plugin Icon
- Sitepulse Logo
- Sitepulse_Page_Tracker
- Sitepulse_CurLoader
- DiagnosticTemplateCoverageTest
- SitePulse — See What's Powering (or Slowing) Your Site
- .time
- AutoloadRestApiTest
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- scripts
- PresentationWordPress.php
- plugin_basename
- onboarding.test.cjs
- ot
- BackendTest
- ps
- FocusTrap
- Sitepulse_Test_Local_Filesystem
- Sitepulse_Plugin_Profiler
- browser
- backend.test.cjs
- remove
- LifecycleTest
- browser.cjs
- Sitepulse_Test_Database
- TemplateTest
- get_current_user_id
- WP_REST_Response
- Swipe
- add_action
- VersionBumpTest
- RestCoverageTest
- js-coverage.cjs
- update_option
- WP_Error
- Automated tests
- Sitepulse_Remote_Client
- coverage.cjs
- CoreVariantsTest
- ApiVariantsTest
- sitepulse_test_subprocess
- Sitepulse_Whitelabel_Service
- Sitepulse_Test_Case
- `POST /api/website/remote/events`
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- Fi
- Sitepulse_Monitoring
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- get_option
- qt
- CoverageGateTest
- do_action
- B
- pi
- Ln
- RecentDashboardTest
- Sitepulse_Remote_Events
- TemplateRenderingTest
- Sitepulse_Getting_Started
- api_backend.php
- RemoteInventoryTest
- PetAssistantTemplateTest
- ProfilerCompatibilityTest
- api-variants.php
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- ActionScheduler_Store
- CLAUDE.md
- ActionSchedulerTest
- HarnessIsolationTest

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 179 edges
2. `get_option()` - 139 edges
3. `Sitepulse_Test_WP` - 121 edges
4. `Sitepulse_Remote_Management` - 75 edges
5. `Sitepulse_Api_Service` - 73 edges
6. `Sitepulse_Profiler` - 73 edges
7. `Sitepulse_Page_Analysis` - 67 edges
8. `Sitepulse_Test_Case` - 63 edges
9. `Sitepulse_Settings` - 60 edges
10. `WP_REST_Request` - 56 edges

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

## Communities (225 total, 132 thin omitted)

### Community 0 - "bootstrap.esm.js"
Cohesion: 0.03
Nodes (72): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+64 more)

### Community 1 - "bootstrap.bundle.min.js"
Cohesion: 0.07
Nodes (53): be(), bi(), Ce(), ci(), D(), De(), _e(), ei() (+45 more)

### Community 2 - "bootstrap.esm.min.js"
Cohesion: 0.04
Nodes (65): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+57 more)

### Community 3 - "Tooltip"
Cohesion: 0.06
Nodes (3): Collapse, Popover, Tooltip

### Community 5 - "Sitepulse_Error_Handler"
Cohesion: 0.05
Nodes (12): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, StoredDataWritePathsTest, wp_mail(), current_time(), esc_html() (+4 more)

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
Cohesion: 0.07
Nodes (25): distanceAndSkiddingToXY(), getDataAttribute(), getDataAttributes(), getFreshSideObject(), getSideOffsets(), hide(), isAnySideFullyClipped(), mergePaddingObject() (+17 more)

### Community 18 - "backend.js"
Cohesion: 0.09
Nodes (20): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike(), getScoreColor() (+12 more)

### Community 20 - "TemplateFactory"
Cohesion: 0.07
Nodes (4): Config, FocusTrap, sanitizeHtml(), TemplateFactory

### Community 22 - "current_user_can"
Cohesion: 0.21
Nodes (5): check_ajax_referer(), current_user_can(), wp_send_json_error(), wp_send_json_success(), wp_unslash()

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.14
Nodes (4): Sitepulse_Easy_Mode, EasyModeTest, remove_all_actions(), update_user_meta()

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.05
Nodes (9): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup, BackWPup_Job, BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, UpdraftPlus (+1 more)

### Community 27 - "remove"
Cohesion: 0.09
Nodes (3): Modal, remove(), Toast

### Community 28 - "WordPress.php"
Cohesion: 0.03
Nodes (57): Sitepulse_Remote_Inventory, RuntimeException, get_the_title(), add_menu_page(), add_option(), add_submenu_page(), check_admin_referer(), delete_metadata() (+49 more)

### Community 35 - "Backdrop"
Cohesion: 0.13
Nodes (6): Backdrop, execute(), executeAfterTransition(), getTransitionDurationFromElement(), Swipe, triggerTransitionEnd()

### Community 37 - "getWindow"
Cohesion: 0.24
Nodes (20): applyStyles(), effect$2(), getClippingParents(), getComputedStyle$1(), getContainingBlock(), getHTMLElementScroll(), getNodeName(), getNodeScroll() (+12 more)

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
Cohesion: 0.18
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Onboarding"
Cohesion: 0.07
Nodes (6): Sitepulse_Onboarding, Architecture, Sitepulse_Loader, SetupOnboardingTest, delete_option(), get_current_screen()

### Community 49 - "contains"
Cohesion: 0.18
Nodes (3): contains(), effect$1(), Tab

### Community 50 - "RemoteManagementTest"
Cohesion: 0.21
Nodes (3): RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response()

### Community 51 - "remove"
Cohesion: 0.17
Nodes (3): Ue(), remove(), Ze

### Community 53 - "Sitepulse_Remote_Management"
Cohesion: 0.08
Nodes (3): Sitepulse_Remote_Management, wp_generate_uuid4(), wp_schedule_single_event()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

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

### Community 71 - "preventOverflow"
Cohesion: 0.21
Nodes (17): arrow(), computeAutoPlacement(), computeOffsets(), computeStyles(), flip(), getAltAxis(), getBasePlacement(), getExpandedFallbackPlacements() (+9 more)

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 78 - "Sitepulse_Backend"
Cohesion: 0.13
Nodes (3): Sitepulse_Backend, BackendCoverageTest, has_filter()

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.06
Nodes (5): Sitepulse_Page_Analysis, PageAnalysisTest, number_format_i18n(), sitepulse_test_post_type(), wp_generate_password()

### Community 83 - "home_url"
Cohesion: 0.08
Nodes (23): Dashboard (portal) endpoints, wc_get_page_id(), get_permalink(), wp_nonce_field(), add_query_arg(), admin_url(), delete_transient(), esc_js() (+15 more)

### Community 86 - "get_transient"
Cohesion: 0.14
Nodes (5): Sitepulse_Cron_Fallback, CronTest, get_transient(), has_action(), set_transient()

### Community 90 - "popperGenerator"
Cohesion: 0.17
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "detectOverflow"
Cohesion: 0.24
Nodes (17): detectOverflow(), expandToHashMap(), getBoundingClientRect(), getClientRectFromMixedType(), getClippingRect(), getCompositeRect(), getDocumentElement(), getDocumentRect() (+9 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.10
Nodes (4): Sitepulse_Settings, SettingsTest, apply_filters(), _get_cron_array()

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 101 - "WP_REST_Request"
Cohesion: 0.10
Nodes (5): ArrayAccess, sitepulse_dismiss_getting_started(), sitepulse_enable_clear_load_events(), sitepulse_set_dashboard_view(), WP_REST_Request

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.22
Nodes (3): PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "RestApiTest"
Cohesion: 0.09
Nodes (6): sitepulse_complete_onboarding(), sitepulse_enable_clear_curl_api_events(), sitepulse_get_memory_info(), sitepulse_rest_pre_dispatch(), sitepulse_update_settings(), RestApiTest

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

### Community 136 - "Sitepulse_Page_Tracker"
Cohesion: 0.10
Nodes (3): Sitepulse_Page_Tracker, wp_debug_backtrace_summary(), PageTrackerTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 142 - ".time"
Cohesion: 0.08
Nodes (8): Sitepulse_AI_Diagnostic_Cron, Sitepulse_Cron_Manager, wp_clear_scheduled_hook(), wp_get_scheduled_event(), wp_get_schedules(), wp_next_scheduled(), wp_schedule_event(), wp_unschedule_event()

### Community 143 - "AutoloadRestApiTest"
Cohesion: 0.20
Nodes (4): sitepulse_get_autoload_options(), sitepulse_get_protected_options(), sitepulse_update_autoload_option(), AutoloadRestApiTest

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
Nodes (13): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), human_time_diff(), load_textdomain(), _n(), submit_button() (+5 more)

### Community 151 - "plugin_basename"
Cohesion: 0.12
Nodes (11): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), register_activation_hook(), register_deactivation_hook(), get_plugin_data() (+3 more)

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.08
Nodes (3): d(), ps, vs

### Community 158 - "Sitepulse_Plugin_Profiler"
Cohesion: 0.07
Nodes (3): Sitepulse_Plugin_Profiler, CoreAdditionalBehaviorTest, PluginProfilerTest

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 166 - "get_current_user_id"
Cohesion: 0.15
Nodes (11): sitepulse_sanitize_param(), sitepulse_set_sp_profiler(), sitepulse_set_wpslowhttp(), absint(), esc_url(), get_current_user_id(), get_user_meta(), is_user_logged_in() (+3 more)

### Community 170 - "add_action"
Cohesion: 0.13
Nodes (3): add_action(), add_filter(), Sitepulse_Test_Hook

### Community 171 - "VersionBumpTest"
Cohesion: 0.17
Nodes (6): PHPUnit\Framework\TestCase, sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "update_option"
Cohesion: 0.07
Nodes (7): Sitepulse_Api_Service, ApiCoverageTest, ApiRegressionTest, ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), update_option()

### Community 175 - "WP_Error"
Cohesion: 0.20
Nodes (15): sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_check_vulnerabilities_api(), sitepulse_collect_plugin_data(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_get_curl_stats(), sitepulse_get_plugin_profiler_stats() (+7 more)

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 182 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.14
Nodes (8): Sitepulse_Whitelabel_Service, is_wp_error(), wp_json_encode(), wp_parse_args(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 183 - "Sitepulse_Test_Case"
Cohesion: 0.20
Nodes (3): DirectAccessTemplateTest, HttpOriginTest, Sitepulse_Test_Case

### Community 184 - "`POST /api/website/remote/events`"
Cohesion: 0.12
Nodes (14): API → site, Commands, Endpoints, Phases, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/events`, `POST /api/website/remote/inventory` (+6 more)

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 189 - "Sitepulse_Monitoring"
Cohesion: 0.20
Nodes (4): Sitepulse_Monitoring, sitepulse_pause_monitoring(), sitepulse_resume_monitoring(), MonitoringTest

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "get_option"
Cohesion: 0.08
Nodes (4): Sitepulse_Product_Analytics, BrandingAnalyticsTest, CoreRegressionTest, get_option()

### Community 211 - "api_backend.php"
Cohesion: 0.33
Nodes (9): sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report(), sitepulse_page_analysis_response(), sitepulse_page_analysis_sample(), sitepulse_page_analysis_start(), sitepulse_register_route() (+1 more)

### Community 215 - "api-variants.php"
Cohesion: 0.22
Nodes (5): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes()

### Community 219 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

## Knowledge Gaps
- **260 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+255 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1252 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **132 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `Sitepulse_Page_Tracker`, `Sitepulse_CurLoader`, `CronCoverageTest`, `DiagnosticTemplateCoverageTest`, `AutoloadRestApiTest`, `Sitepulse_Easy_Mode`, `BackendTest`, `Sitepulse_Remote_Backups`, `WordPress.php`, `Sitepulse_Plugin_Profiler`, `LifecycleTest`, `Sitepulse_Test_Database`, `TemplateTest`, `VersionBumpTest`, `RestCoverageTest`, `update_option`, `Sitepulse_Frontend`, `Sitepulse_Onboarding`, `Sitepulse_Remote_Client`, `RemoteManagementTest`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Setup`, `OptionalViewIntegrationTest`, `Sitepulse_Monitoring`, `ErrorHandlerVariantsTest`, `Sitepulse_Plugin`, `EasyModeSaveQueriesTest`, `Sitepulse_Profiler`, `get_option`, `do_action`, `RecentDashboardTest`, `Sitepulse_Backend`, `TemplateRenderingTest`, `Sitepulse_Getting_Started`, `Sitepulse_Page_Analysis`, `RemoteInventoryTest`, `PetAssistantTemplateTest`, `get_transient`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `RemoteVariantsTest`, `ActionSchedulerTest`, `HarnessIsolationTest`, `Sitepulse_Settings`, `PluginTest`, `PopulatedTemplateTest`, `RestApiTest`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Why does ``POST /api/website/remote/inventory`` connect ``POST /api/website/remote/events`` to `page-analysis.js`?**
  _High betweenness centrality (0.042) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `DiagnosticTemplateCoverageTest`, `.time`, `plugin_basename`, `Sitepulse_Remote_Backups`, `WordPress.php`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Test_Database`, `get_current_user_id`, `add_action`, `RestCoverageTest`, `Sitepulse_Frontend`, `Sitepulse_Onboarding`, `WP_Error`, `RemoteManagementTest`, `Sitepulse_Setup`, `Sitepulse_Remote_Management`, `Sitepulse_Whitelabel_Service`, `Sitepulse_Monitoring`, `Sitepulse_Profiler`, `get_option`, `do_action`, `Sitepulse_Backend`, `Sitepulse_Remote_Events`, `TemplateRenderingTest`, `Sitepulse_Page_Analysis`, `home_url`, `Sitepulse_Getting_Started`, `get_transient`, `Sitepulse_Utils`, `ProfilerCompatibilityTest`, `Sitepulse_Settings`, `PopulatedTemplateTest`, `RestApiTest`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Are the 175 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 175 INFERRED edges - model-reasoned connections that need verification._
- **Are the 134 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 134 INFERRED edges - model-reasoned connections that need verification._
- **Are the 39 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 39 INFERRED edges - model-reasoned connections that need verification._
- **Are the 43 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.backwpup_finished()` and `.updraftplus_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 43 INFERRED edges - model-reasoned connections that need verification._
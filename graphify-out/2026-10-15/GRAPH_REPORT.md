# Graph Report - sitepulse  (2026-10-13)

## Corpus Check
- 156 files · ~324,846 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 3917 nodes · 8585 edges · 200 communities (88 shown, 112 thin omitted)
- Extraction: 81% EXTRACTED · 19% INFERRED · 0% AMBIGUOUS · INFERRED: 1654 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `08907870`
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
- Sitepulse_Cron_Manager
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
- Sitepulse_Backend
- Sitepulse_Easy_Mode
- SitePulse
- Tooltip
- ApiServiceTest
- remove
- WordPress.php
- remove
- Carousel
- ._queueCallback
- Carousel
- lt
- Carousel
- Backdrop
- remove
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
- PageTrackerTest
- Ze
- Sitepulse_Setup
- get_transient
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
- B
- BaseComponent
- Toast
- ScrollSpy
- Modal
- number_format_i18n
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- wp_create_nonce
- Ss
- ScrollSpy
- Sitepulse_Cron_Fallback
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
- PageAnalysisTest.php
- PopulatedTemplateTest
- FocusTrap
- sitepulse_global.js
- WP_REST_Request
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
- Sitepulse_AI_Diagnostic_Cron
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
- es
- LifecycleTest
- browser.cjs
- Sitepulse_Test_Case
- TemplateTest
- WP_REST_Response
- Swipe
- add_action
- VersionBumpTest
- RestCoverageTest
- js-coverage.cjs
- update_option
- is_wp_error
- Automated tests
- coverage.cjs
- CoreVariantsTest
- ApiVariantsTest
- sitepulse_test_subprocess
- Sitepulse_Whitelabel_Service
- .identify_origin
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
- pi
- RecentDashboardTest

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 160 edges
2. `get_option()` - 123 edges
3. `Sitepulse_Test_WP` - 109 edges
4. `Sitepulse_Profiler` - 73 edges
5. `Sitepulse_Api_Service` - 72 edges
6. `Sitepulse_Page_Analysis` - 67 edges
7. `Sitepulse_Settings` - 59 edges
8. `Sitepulse_Test_Case` - 57 edges
9. `WP_REST_Request` - 56 edges
10. `Sitepulse_Error_Handler` - 49 edges

## Surprising Connections (you probably didn't know these)
- `Project Structure & Module Organization` --references--> `Sitepulse_Plugin`  [INFERRED]
  AGENTS.md → class/plugin.php
- `Architecture` --references--> `Sitepulse_CurLoader`  [INFERRED]
  CLAUDE.md → class/curloader.php
- `Architecture` --references--> `Sitepulse_Plugin`  [INFERRED]
  CLAUDE.md → class/plugin.php
- `Architecture` --references--> `Sitepulse_Profiler`  [INFERRED]
  CLAUDE.md → class/profiler.php
- `Architecture` --references--> `DiagnosticTemplateCoverageTest`  [INFERRED]
  CLAUDE.md → tests/php/DiagnosticTemplateCoverageTest.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **SitePulse Core Monitoring Features** — readme_hook_level_timing, readme_slow_plugin_profiler, readme_per_page_tracking, readme_external_request_monitoring, readme_php_error_tracking [EXTRACTED 1.00]
- **SitePulse Logo Visual Elements** — assets_img_sitepulse_logo_sitepulse_logo, assets_img_sitepulse_logo_heartbeat_waveform, assets_img_sitepulse_logo_status_dots [INFERRED 0.85]

## Communities (200 total, 112 thin omitted)

### Community 0 - "bootstrap.esm.js"
Cohesion: 0.03
Nodes (72): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+64 more)

### Community 1 - "bootstrap.bundle.min.js"
Cohesion: 0.07
Nodes (50): be(), bi(), Ce(), D(), De(), _e(), ei(), F() (+42 more)

### Community 2 - "bootstrap.esm.min.js"
Cohesion: 0.04
Nodes (65): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+57 more)

### Community 3 - "Tooltip"
Cohesion: 0.06
Nodes (3): Collapse, Popover, Tooltip

### Community 5 - "Sitepulse_Error_Handler"
Cohesion: 0.06
Nodes (7): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, current_time(), get_bloginfo(), is_email()

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

### Community 22 - "Sitepulse_Backend"
Cohesion: 0.09
Nodes (12): Sitepulse_Backend, RuntimeException, check_ajax_referer(), current_user_can(), sanitize_email(), sanitize_key(), sanitize_text_field(), Sitepulse_Test_Json_Response (+4 more)

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.07
Nodes (10): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, EasyModeTest, GettingStartedTest, get_current_user_id(), get_user_meta(), is_user_logged_in(), metadata_exists() (+2 more)

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "ApiServiceTest"
Cohesion: 0.12
Nodes (3): ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat()

### Community 27 - "remove"
Cohesion: 0.09
Nodes (3): Modal, remove(), Toast

### Community 28 - "WordPress.php"
Cohesion: 0.05
Nodes (52): sitepulse_register_route(), get_permalink(), get_the_title(), add_menu_page(), add_option(), add_submenu_page(), check_admin_referer(), delete_metadata() (+44 more)

### Community 29 - "remove"
Cohesion: 0.10
Nodes (3): Ln, mo, remove()

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
Cohesion: 0.16
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Onboarding"
Cohesion: 0.11
Nodes (8): Sitepulse_Onboarding, remove_all_actions(), wp_nonce_field(), esc_attr(), esc_html(), esc_html_e(), esc_url(), get_current_screen()

### Community 49 - "contains"
Cohesion: 0.18
Nodes (3): contains(), effect$1(), Tab

### Community 52 - "Sitepulse_Setup"
Cohesion: 0.08
Nodes (4): Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest

### Community 53 - "get_transient"
Cohesion: 0.21
Nodes (3): get_transient(), set_transient(), wp_generate_password()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Plugin"
Cohesion: 0.11
Nodes (4): Sitepulse_Plugin, BackendCoverageTest, wp_convert_hr_to_bytes(), PluginAdditionalBehaviorTest

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.05
Nodes (5): Sitepulse_Profiler, ProfilerCompatibilityTest, Sitepulse_Test_Profiler_Callback, ProfilerTest, RecentActivityTest

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

### Community 73 - "B"
Cohesion: 0.06
Nodes (4): an, B, U, W

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 83 - "wp_create_nonce"
Cohesion: 0.16
Nodes (13): add_query_arg(), admin_url(), esc_js(), esc_url_raw(), remove_query_arg(), rest_url(), untrailingslashit(), wp_create_nonce() (+5 more)

### Community 86 - "Sitepulse_Cron_Fallback"
Cohesion: 0.16
Nodes (3): Sitepulse_Cron_Fallback, CronTest, has_action()

### Community 90 - "popperGenerator"
Cohesion: 0.17
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "detectOverflow"
Cohesion: 0.24
Nodes (17): detectOverflow(), expandToHashMap(), getBoundingClientRect(), getClientRectFromMixedType(), getClippingRect(), getCompositeRect(), getDocumentElement(), getDocumentRect() (+9 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.07
Nodes (6): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters(), _get_cron_array()

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.08
Nodes (5): PetAssistantTemplateTest, PopulatedTemplateTest, Sitepulse_Test_Database, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "WP_REST_Request"
Cohesion: 0.05
Nodes (34): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice() (+26 more)

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

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 142 - "Sitepulse_AI_Diagnostic_Cron"
Cohesion: 0.13
Nodes (7): Sitepulse_AI_Diagnostic_Cron, wp_clear_scheduled_hook(), wp_get_scheduled_event(), wp_get_schedules(), wp_next_scheduled(), wp_schedule_event(), wp_unschedule_event()

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
Cohesion: 0.10
Nodes (11): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), submit_button(), wp_date() (+3 more)

### Community 151 - "plugin_basename"
Cohesion: 0.10
Nodes (10): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), register_activation_hook(), register_deactivation_hook(), plugin_basename() (+2 more)

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 158 - "Sitepulse_Plugin_Profiler"
Cohesion: 0.07
Nodes (8): Sitepulse_Plugin_Profiler, Architecture, Commands, Repo workflow, Testing notes, Sitepulse_Loader, PluginProfilerTest, delete_option()

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 164 - "Sitepulse_Test_Case"
Cohesion: 0.12
Nodes (4): DirectAccessTemplateTest, HarnessIsolationTest, StoredDataRobustnessTest, Sitepulse_Test_Case

### Community 170 - "add_action"
Cohesion: 0.10
Nodes (8): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), add_action(), add_filter(), do_action(), Sitepulse_Test_Hook

### Community 171 - "VersionBumpTest"
Cohesion: 0.10
Nodes (8): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), CoverageGateTest, VersionBumpTest

### Community 172 - "RestCoverageTest"
Cohesion: 0.15
Nodes (5): sitepulse_enable_save_queries(), sitepulse_sanitize_param(), RestCoverageTest, absint(), wp_kses_post()

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "update_option"
Cohesion: 0.06
Nodes (6): Sitepulse_Api_Service, ApiCoverageTest, ApiRegressionTest, StoredDataWritePathsTest, delete_transient(), update_option()

### Community 175 - "is_wp_error"
Cohesion: 0.40
Nodes (5): is_wp_error(), wp_json_encode(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_response_code()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 182 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.13
Nodes (3): Sitepulse_Whitelabel_Service, sanitize_hex_color(), wp_parse_args()

### Community 183 - ".identify_origin"
Cohesion: 0.17
Nodes (4): HttpOriginTest, get_theme_root(), Sitepulse_Test_Theme, wp_get_theme()

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 189 - "Sitepulse_Monitoring"
Cohesion: 0.17
Nodes (4): Sitepulse_Monitoring, sitepulse_pause_monitoring(), sitepulse_resume_monitoring(), MonitoringTest

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "get_option"
Cohesion: 0.08
Nodes (5): Sitepulse_Product_Analytics, sitepulse_check_vulnerabilities_api(), BrandingAnalyticsTest, CoreAdditionalBehaviorTest, get_option()

### Community 203 - "pi"
Cohesion: 0.12
Nodes (3): ci(), Ie, pi

## Knowledge Gaps
- **253 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+248 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1199 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **112 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `Sitepulse_Page_Tracker`, `Sitepulse_CurLoader`, `Sitepulse_Cron_Manager`, `DiagnosticTemplateCoverageTest`, `AutoloadRestApiTest`, `Sitepulse_Backend`, `Sitepulse_Easy_Mode`, `BackendTest`, `ApiServiceTest`, `Sitepulse_Plugin_Profiler`, `LifecycleTest`, `TemplateTest`, `VersionBumpTest`, `RestCoverageTest`, `update_option`, `Sitepulse_Frontend`, `PageTrackerTest`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Setup`, `.identify_origin`, `OptionalViewIntegrationTest`, `Sitepulse_Monitoring`, `ErrorHandlerVariantsTest`, `Sitepulse_Plugin`, `EasyModeSaveQueriesTest`, `Sitepulse_Profiler`, `get_option`, `RecentDashboardTest`, `Sitepulse_Page_Analysis`, `Sitepulse_Cron_Fallback`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PluginTest`, `PopulatedTemplateTest`, `WP_REST_Request`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_AI_Diagnostic_Cron`, `plugin_basename`, `Sitepulse_Easy_Mode`, `ApiServiceTest`, `WordPress.php`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Test_Case`, `RestCoverageTest`, `Sitepulse_Frontend`, `is_wp_error`, `Sitepulse_Onboarding`, `PageTrackerTest`, `Sitepulse_Setup`, `get_transient`, `Sitepulse_Whitelabel_Service`, `Sitepulse_Monitoring`, `Sitepulse_Plugin`, `Sitepulse_Profiler`, `get_option`, `Sitepulse_Page_Analysis`, `Sitepulse_Cron_Fallback`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PopulatedTemplateTest`, `WP_REST_Request`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Why does `Sitepulse_Test_WP` connect `WordPress.php` to `Sitepulse_Error_Handler`, `Sitepulse_Page_Tracker`, `Sitepulse_CurLoader`, `Sitepulse_Cron_Manager`, `Sitepulse_AI_Diagnostic_Cron`, `AutoloadRestApiTest`, `Sitepulse_Backend`, `Sitepulse_Easy_Mode`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Test_Case`, `add_action`, `RestCoverageTest`, `update_option`, `Sitepulse_Onboarding`, `is_wp_error`, `get_transient`, `Sitepulse_Whitelabel_Service`, `.identify_origin`, `Sitepulse_Plugin`, `Sitepulse_Profiler`, `get_option`, `Sitepulse_Page_Analysis`, `wp_create_nonce`, `Sitepulse_Cron_Fallback`, `Sitepulse_Settings`, `WP_REST_Request`?**
  _High betweenness centrality (0.013) - this node is a cross-community bridge._
- **Are the 156 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 156 INFERRED edges - model-reasoned connections that need verification._
- **Are the 119 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 119 INFERRED edges - model-reasoned connections that need verification._
- **Are the 35 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 35 INFERRED edges - model-reasoned connections that need verification._
- **Are the 45 inferred relationships involving `Sitepulse_Profiler` (e.g. with `.get_resources_data()` and `.render_dashboard_section()`) actually correct?**
  _`Sitepulse_Profiler` has 45 INFERRED edges - model-reasoned connections that need verification._
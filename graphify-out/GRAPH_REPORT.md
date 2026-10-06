# Graph Report - sitepulse  (2026-10-06)

## Corpus Check
- 149 files · ~319,548 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 3854 nodes · 8459 edges · 200 communities (92 shown, 108 thin omitted)
- Extraction: 81% EXTRACTED · 19% INFERRED · 0% AMBIGUOUS · INFERRED: 1603 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d91d0131`
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
- B
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
- Sitepulse_Backend
- Sitepulse_Easy_Mode
- SitePulse
- Tooltip
- Sitepulse_Api_Service
- remove
- WordPress.php
- remove
- Carousel
- ._queueCallback
- Carousel
- lt
- Carousel
- Backdrop
- update_user_meta
- getWindow
- Modal
- Backdrop
- Offcanvas
- bootstrap.min.js
- be
- Offcanvas
- W
- Offcanvas
- Sitepulse_Frontend
- delete_option
- Collapse
- contains
- WP_Error
- remove
- Sitepulse_Onboarding
- WP_REST_Request
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
- update_option
- Sitepulse_Profiler
- blueprint.json
- addHandler
- BaseComponent
- preventOverflow
- Dropdown
- es
- BaseComponent
- Toast
- ScrollSpy
- Modal
- api_backend.php
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- wp_create_nonce
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
- PageAnalysisTest
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
- .ajax_toggle_theme
- Sitepulse_AI_Diagnostic_Cron
- AutoloadRestApiTest
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- scripts
- PresentationWordPress.php
- Sitepulse_Test_Filesystem
- onboarding.test.cjs
- ot
- BackendTest
- ps
- FocusTrap
- Sitepulse_Test_Local_Filesystem
- browser
- backend.test.cjs
- Ti
- LifecycleTest
- browser.cjs
- Sitepulse_Test_Database
- TemplateTest
- Me
- WP_REST_Response
- Swipe
- Sitepulse_Whitelabel_Service
- CoverageGateTest
- RestCoverageTest
- js-coverage.cjs
- add_action
- is_wp_error
- Automated tests
- sitepulse_test_post_type
- coverage.cjs
- CoreVariantsTest
- ApiVariantsTest
- sitepulse_test_subprocess
- sitepulse_register_route
- Sitepulse_Test_Case
- EasyModeSaveQueriesTest
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- Sitepulse_Monitoring
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- get_option

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 156 edges
2. `get_option()` - 124 edges
3. `Sitepulse_Test_WP` - 109 edges
4. `Sitepulse_Api_Service` - 71 edges
5. `Sitepulse_Page_Analysis` - 67 edges
6. `Sitepulse_Settings` - 59 edges
7. `Sitepulse_Profiler` - 58 edges
8. `WP_REST_Request` - 56 edges
9. `Sitepulse_Test_Case` - 55 edges
10. `Sitepulse_Error_Handler` - 49 edges

## Surprising Connections (you probably didn't know these)
- `Project Structure & Module Organization` --references--> `Sitepulse_Plugin`  [INFERRED]
  AGENTS.md → class/plugin.php
- `AI Diagnostic Reports` --semantically_similar_to--> `AI Diagnostics`  [INFERRED] [semantically similar]
  readme.txt → README.md
- `Google PageSpeed Integration` --semantically_similar_to--> `Google PageSpeed Insights`  [INFERRED] [semantically similar]
  readme.txt → README.md
- `sitepulse_check_ai_diagnostic_status_api()` --calls--> `Sitepulse_Api_Service`  [INFERRED]
  inc/api_backend.php → class/api_service.php
- `sitepulse_check_vulnerabilities_api()` --calls--> `Sitepulse_Api_Service`  [INFERRED]
  inc/api_backend.php → class/api_service.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **SitePulse Core Monitoring Features** — readme_hook_level_timing, readme_slow_plugin_profiler, readme_per_page_tracking, readme_external_request_monitoring, readme_php_error_tracking [EXTRACTED 1.00]
- **SitePulse Logo Visual Elements** — assets_img_sitepulse_logo_sitepulse_logo, assets_img_sitepulse_logo_heartbeat_waveform, assets_img_sitepulse_logo_status_dots [INFERRED 0.85]

## Communities (200 total, 108 thin omitted)

### Community 0 - "bootstrap.esm.js"
Cohesion: 0.03
Nodes (72): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+64 more)

### Community 1 - "bootstrap.bundle.min.js"
Cohesion: 0.09
Nodes (48): be(), bi(), Ce(), D(), De(), _e(), ei(), F() (+40 more)

### Community 2 - "bootstrap.esm.min.js"
Cohesion: 0.04
Nodes (65): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+57 more)

### Community 3 - "Tooltip"
Cohesion: 0.06
Nodes (3): Collapse, Popover, Tooltip

### Community 5 - "Sitepulse_Error_Handler"
Cohesion: 0.07
Nodes (8): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, current_time(), get_bloginfo(), is_email(), sanitize_email()

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

### Community 11 - "B"
Cohesion: 0.11
Nodes (3): B, qt, U

### Community 13 - "BaseComponent"
Cohesion: 0.07
Nodes (4): Backdrop, BaseComponent, Button, Config

### Community 14 - "TemplateFactory"
Cohesion: 0.09
Nodes (5): Config, getElement(), isElement(), TemplateFactory, toType()

### Community 15 - "Fi"
Cohesion: 0.07
Nodes (3): Fi, Qt, Wi

### Community 16 - "bootstrap.bundle.js"
Cohesion: 0.07
Nodes (25): distanceAndSkiddingToXY(), getDataAttribute(), getDataAttributes(), getFreshSideObject(), getSideOffsets(), hide(), isAnySideFullyClipped(), mergePaddingObject() (+17 more)

### Community 18 - "backend.js"
Cohesion: 0.09
Nodes (20): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike(), getScoreColor() (+12 more)

### Community 19 - ".hide"
Cohesion: 0.09
Nodes (4): ci(), li(), Fs, pi

### Community 20 - "TemplateFactory"
Cohesion: 0.07
Nodes (4): Config, FocusTrap, sanitizeHtml(), TemplateFactory

### Community 22 - "Sitepulse_Backend"
Cohesion: 0.19
Nodes (3): Sitepulse_Backend, remove_all_actions(), current_user_can()

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.08
Nodes (8): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, EasyModeTest, GettingStartedTest, get_current_user_id(), get_user_meta(), is_user_logged_in(), metadata_exists()

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 27 - "remove"
Cohesion: 0.09
Nodes (3): Modal, remove(), Toast

### Community 28 - "WordPress.php"
Cohesion: 0.05
Nodes (48): RuntimeException, get_permalink(), get_the_title(), add_menu_page(), add_option(), add_submenu_page(), check_admin_referer(), delete_metadata() (+40 more)

### Community 29 - "remove"
Cohesion: 0.08
Nodes (4): Ln, mo, remove(), x()

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

### Community 44 - "W"
Cohesion: 0.10
Nodes (4): getDataAttributes(), H(), Ie, W

### Community 46 - "Sitepulse_Frontend"
Cohesion: 0.18
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "delete_option"
Cohesion: 0.10
Nodes (10): sitepulse_sanitize_param(), Sitepulse_Loader, submit_button(), absint(), delete_option(), esc_attr(), esc_html(), esc_html_e() (+2 more)

### Community 49 - "contains"
Cohesion: 0.18
Nodes (3): contains(), effect$1(), Tab

### Community 50 - "WP_Error"
Cohesion: 0.14
Nodes (12): sitepulse_check_ai_diagnostic_status_api(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_get_curl_stats(), sitepulse_get_plugin_profiler_stats(), sitepulse_get_profiler_stats() (+4 more)

### Community 51 - "remove"
Cohesion: 0.13
Nodes (4): Ue(), G, remove(), Ze

### Community 52 - "Sitepulse_Onboarding"
Cohesion: 0.06
Nodes (6): Sitepulse_Onboarding, Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest, get_current_screen()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Plugin"
Cohesion: 0.11
Nodes (4): Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest, has_filter()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "update_option"
Cohesion: 0.05
Nodes (6): Sitepulse_Plugin_Profiler, ApiCoverageTest, CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataWritePathsTest, update_option()

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.05
Nodes (7): Sitepulse_Profiler, ProfilerCompatibilityTest, Sitepulse_Test_Profiler_Callback, ProfilerTest, do_action(), get_template(), wp_normalize_path()

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

### Community 73 - "es"
Cohesion: 0.07
Nodes (3): an, es, W

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 78 - "api_backend.php"
Cohesion: 0.23
Nodes (14): sitepulse_dismiss_getting_started(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events(), sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report(), sitepulse_page_analysis_response() (+6 more)

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.08
Nodes (3): Sitepulse_Page_Analysis, number_format_i18n(), wp_generate_password()

### Community 83 - "wp_create_nonce"
Cohesion: 0.12
Nodes (13): wp_nonce_field(), esc_js(), esc_url_raw(), is_admin_bar_showing(), remove_query_arg(), rest_url(), untrailingslashit(), wp_create_nonce() (+5 more)

### Community 86 - "get_transient"
Cohesion: 0.09
Nodes (7): Sitepulse_Cron_Fallback, CronCoverageTest, CronTest, delete_transient(), get_transient(), has_action(), set_transient()

### Community 90 - "popperGenerator"
Cohesion: 0.17
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "detectOverflow"
Cohesion: 0.24
Nodes (17): detectOverflow(), expandToHashMap(), getBoundingClientRect(), getClientRectFromMixedType(), getClippingRect(), getCompositeRect(), getDocumentElement(), getDocumentRect() (+9 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.06
Nodes (7): Sitepulse_Settings, ActionSchedulerTest, HarnessIsolationTest, SettingsTest, ActionScheduler_Store, apply_filters(), _get_cron_array()

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.12
Nodes (4): PetAssistantTemplateTest, PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "RestApiTest"
Cohesion: 0.10
Nodes (6): sitepulse_check_vulnerabilities_api(), sitepulse_get_memory_info(), sitepulse_request_ai_diagnostic_api(), sitepulse_rest_pre_dispatch(), sitepulse_set_wpslowhttp(), RestApiTest

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

### Community 141 - ".ajax_toggle_theme"
Cohesion: 0.36
Nodes (4): check_ajax_referer(), Sitepulse_Test_Json_Response, wp_send_json_error(), wp_send_json_success()

### Community 142 - "Sitepulse_AI_Diagnostic_Cron"
Cohesion: 0.09
Nodes (8): Sitepulse_AI_Diagnostic_Cron, Sitepulse_Cron_Manager, wp_clear_scheduled_hook(), wp_get_scheduled_event(), wp_get_schedules(), wp_next_scheduled(), wp_schedule_event(), wp_unschedule_event()

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
Nodes (15): config, allow-plugins, platform, sort-packages, description, license, name, php (+7 more)

### Community 147 - "SitePulse Pro"
Cohesion: 0.67
Nodes (3): SitePulse Pro Waitlist, SitePulse Pro, SQL Query Monitor

### Community 149 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, coverage:js, test, test:coverage, test:js

### Community 150 - "PresentationWordPress.php"
Cohesion: 0.10
Nodes (11): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), wp_date(), wp_mail() (+3 more)

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.09
Nodes (3): d(), ps, vs

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 170 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.06
Nodes (17): inc/ helpers, Sitepulse_Whitelabel_Service, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), gethostbyname(), gethostname() (+9 more)

### Community 171 - "CoverageGateTest"
Cohesion: 0.21
Nodes (3): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), CoverageGateTest

### Community 172 - "RestCoverageTest"
Cohesion: 0.15
Nodes (6): sitepulse_check_memory(), sitepulse_enable_save_queries(), sitepulse_set_sp_profiler(), sitepulse_update_settings(), RestCoverageTest, sanitize_text_field()

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "add_action"
Cohesion: 0.14
Nodes (3): add_action(), add_filter(), Sitepulse_Test_Hook

### Community 175 - "is_wp_error"
Cohesion: 0.40
Nodes (5): is_wp_error(), wp_json_encode(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_response_code()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 183 - "Sitepulse_Test_Case"
Cohesion: 0.18
Nodes (3): DirectAccessTemplateTest, HttpOriginTest, Sitepulse_Test_Case

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 189 - "Sitepulse_Monitoring"
Cohesion: 0.11
Nodes (6): Sitepulse_Monitoring, MonitoringTest, add_query_arg(), admin_url(), is_admin(), wp_nonce_url()

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "get_option"
Cohesion: 0.11
Nodes (5): Sitepulse_Product_Analytics, ApiRegressionTest, BrandingAnalyticsTest, get_option(), sanitize_key()

## Knowledge Gaps
- **249 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+244 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1178 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **108 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `Sitepulse_Page_Tracker`, `Sitepulse_CurLoader`, `DiagnosticTemplateCoverageTest`, `.ajax_toggle_theme`, `AutoloadRestApiTest`, `Sitepulse_Easy_Mode`, `BackendTest`, `Sitepulse_Api_Service`, `LifecycleTest`, `Sitepulse_Test_Database`, `update_user_meta`, `TemplateTest`, `CoverageGateTest`, `RestCoverageTest`, `Sitepulse_Frontend`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Onboarding`, `EasyModeSaveQueriesTest`, `OptionalViewIntegrationTest`, `Sitepulse_Monitoring`, `ErrorHandlerVariantsTest`, `Sitepulse_Plugin`, `update_option`, `Sitepulse_Profiler`, `get_option`, `get_transient`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PluginTest`, `PageAnalysisTest`, `PopulatedTemplateTest`, `RestApiTest`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_Page_Tracker`, `Sitepulse_CurLoader`, `Sitepulse_AI_Diagnostic_Cron`, `Sitepulse_Easy_Mode`, `Sitepulse_Api_Service`, `WordPress.php`, `.analysis`, `Sitepulse_Test_Database`, `update_user_meta`, `Sitepulse_Whitelabel_Service`, `RestCoverageTest`, `Sitepulse_Frontend`, `is_wp_error`, `delete_option`, `sitepulse_test_post_type`, `add_action`, `WP_Error`, `Sitepulse_Onboarding`, `Sitepulse_Monitoring`, `Sitepulse_Plugin`, `Sitepulse_Profiler`, `get_option`, `api_backend.php`, `Sitepulse_Page_Analysis`, `wp_create_nonce`, `get_transient`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PopulatedTemplateTest`, `RestApiTest`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **Why does `get_option()` connect `get_option` to `Sitepulse_Error_Handler`, `Sitepulse_Page_Tracker`, `Sitepulse_CurLoader`, `Sitepulse_AI_Diagnostic_Cron`, `Sitepulse_Backend`, `Sitepulse_Easy_Mode`, `Sitepulse_Api_Service`, `WordPress.php`, `.analysis`, `Sitepulse_Whitelabel_Service`, `RestCoverageTest`, `add_action`, `is_wp_error`, `delete_option`, `WP_Error`, `Sitepulse_Onboarding`, `Sitepulse_Monitoring`, `update_option`, `Sitepulse_Profiler`, `api_backend.php`, `Sitepulse_Page_Analysis`, `wp_create_nonce`, `get_transient`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PluginTest`, `PageAnalysisTest`, `RestApiTest`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._
- **Are the 152 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 152 INFERRED edges - model-reasoned connections that need verification._
- **Are the 120 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 120 INFERRED edges - model-reasoned connections that need verification._
- **Are the 35 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 35 INFERRED edges - model-reasoned connections that need verification._
- **Are the 52 inferred relationships involving `Sitepulse_Api_Service` (e.g. with `.render_dashboard_section()` and `.check_status_callback()`) actually correct?**
  _`Sitepulse_Api_Service` has 52 INFERRED edges - model-reasoned connections that need verification._
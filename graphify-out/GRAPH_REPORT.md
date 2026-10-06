# Graph Report - sitepulse  (2026-10-04)

## Corpus Check
- 140 files · ~282,097 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 3637 nodes · 7868 edges · 199 communities (82 shown, 117 thin omitted)
- Extraction: 83% EXTRACTED · 17% INFERRED · 0% AMBIGUOUS · INFERRED: 1342 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `b442f164`
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
- ps
- St
- BaseComponent
- TemplateFactory
- Wi
- bootstrap.bundle.js
- Tooltip
- backend.js
- .hide
- TemplateFactory
- ._queueCallback
- WP_Error
- Sitepulse_Easy_Mode
- SitePulse
- Tooltip
- update_option
- remove
- WordPress.php
- B
- Carousel
- ._queueCallback
- Carousel
- lt
- Carousel
- Backdrop
- WP_REST_Request
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
- Sitepulse_Page_Tracker
- Collapse
- contains
- Fi
- remove
- Sitepulse_Onboarding
- Ln
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
- Sitepulse_Plugin_Profiler
- Sitepulse_Profiler
- blueprint.json
- addHandler
- BaseComponent
- preventOverflow
- Dropdown
- remove
- BaseComponent
- Toast
- ScrollSpy
- Modal
- Sitepulse_Setup
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Me
- AI Diagnostics
- Ss
- ScrollSpy
- frontend.js
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
- FocusTrap
- PopulatedTemplateTest
- FocusTrap
- sitepulse_global.js
- RestApiTest
- Repository Guidelines
- SitePulse Logo
- WordPress Pulse Logo
- CommandCode
- Sitepulse_Test_Local_Filesystem
- SitePulse WordPress Plugin Icon
- SitePulse Logo
- Sitepulse Plugin Icon
- Sitepulse Logo
- Sitepulse_CurLoader
- DiagnosticTemplateCoverageTest
- SitePulse — See What's Powering (or Slowing) Your Site
- Sitepulse_Whitelabel_Service
- get_transient
- AutoloadRestApiTest
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- package.json
- PresentationWordPress.php
- Sitepulse_Test_Filesystem
- browser
- .compact_backtrace
- BackendTest
- wp_normalize_path
- FocusTrap
- Sitepulse_Backend
- .make_api_request
- ref_node_assert
- backend.test.cjs
- LifecycleTest
- browser.cjs
- Sitepulse_Test_Database
- TemplateTest
- .get_theme
- WP_REST_Response
- Swipe
- add_action
- CoverageGateTest
- RestCoverageTest
- js-coverage.cjs
- update_user_meta
- ot
- Automated tests
- Sitepulse_Test_Case
- coverage.cjs
- CoreVariantsTest
- ApiVariantsTest
- sitepulse_test_subprocess
- ProfilerCompatibilityTest
- Ie
- Installation
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- remaining-browser-behavior.test.cjs
- remaining-ui-flows.test.cjs
- ErrorHandlerVariantsTest
- PetAssistantTemplateTest
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- HarnessIsolationTest

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 140 edges
2. `get_option()` - 122 edges
3. `Sitepulse_Test_WP` - 89 edges
4. `Sitepulse_Api_Service` - 71 edges
5. `Sitepulse_Settings` - 59 edges
6. `Sitepulse_Test_Case` - 49 edges
7. `WP_REST_Request` - 49 edges
8. `Sitepulse_Error_Handler` - 46 edges
9. `Sitepulse_Profiler` - 43 edges
10. `Tooltip` - 41 edges

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

## Communities (199 total, 117 thin omitted)

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

### Community 11 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

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
Cohesion: 0.08
Nodes (20): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike(), getScoreColor() (+12 more)

### Community 20 - "TemplateFactory"
Cohesion: 0.10
Nodes (3): Config, sanitizeHtml(), TemplateFactory

### Community 22 - "WP_Error"
Cohesion: 0.21
Nodes (24): sitepulse_check_ai_diagnostic_status_api(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events(), sitepulse_get_curl_stats() (+16 more)

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.10
Nodes (10): Sitepulse_Easy_Mode, EasyModeTest, check_ajax_referer(), current_user_can(), get_post_status(), sanitize_text_field(), Sitepulse_Test_Json_Response, wp_send_json_error() (+2 more)

### Community 24 - "SitePulse"
Cohesion: 0.10
Nodes (20): Admin Bar Widget, Basic View, Core Web Vitals, Cron Job Management, Developer View, Email Blocking, Fatal-Error Detection, Health Score System (+12 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "update_option"
Cohesion: 0.05
Nodes (8): Sitepulse_Api_Service, ApiCoverageTest, ApiRegressionTest, ApiServiceTest, CoreAdditionalBehaviorTest, get_bloginfo(), get_option(), update_option()

### Community 27 - "remove"
Cohesion: 0.09
Nodes (3): Modal, remove(), Toast

### Community 28 - "WordPress.php"
Cohesion: 0.04
Nodes (55): sitepulse_sanitize_param(), RuntimeException, checked(), get_the_title(), submit_button(), wp_nonce_field(), absint(), add_menu_page() (+47 more)

### Community 29 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

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
Cohesion: 0.08
Nodes (5): getDataAttributes(), H(), R, Ti, W

### Community 46 - "Sitepulse_Frontend"
Cohesion: 0.15
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 49 - "contains"
Cohesion: 0.19
Nodes (3): contains(), effect$1(), Tab

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 52 - "Sitepulse_Onboarding"
Cohesion: 0.07
Nodes (4): Sitepulse_Onboarding, CoreRegressionTest, SetupOnboardingTest, delete_option()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Plugin"
Cohesion: 0.14
Nodes (3): Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest

### Community 65 - "onboarding.js"
Cohesion: 0.27
Nodes (17): bindEvents(), completeOnboarding(), dismissOnboarding(), goToStep(), handleFinish(), handleNext(), handlePrev(), handleSkip() (+9 more)

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

### Community 73 - "remove"
Cohesion: 0.07
Nodes (4): an, es, remove(), W

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 81 - "easy-mode.js"
Cohesion: 0.14
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 83 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 86 - "frontend.js"
Cohesion: 0.32
Nodes (12): getRealTimeMode(), resetModalToDefault(), restoreTracking(), setPageTPLoadActive(), setRealTimeMode(), setSPReportMode(), showCompletionModal(), simulateResourceDetection() (+4 more)

### Community 90 - "popperGenerator"
Cohesion: 0.17
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "detectOverflow"
Cohesion: 0.24
Nodes (17): detectOverflow(), expandToHashMap(), getBoundingClientRect(), getClientRectFromMixedType(), getClippingRect(), getCompositeRect(), getDocumentElement(), getDocumentRect() (+9 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.07
Nodes (6): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters(), _get_cron_array()

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.20
Nodes (3): PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "RestApiTest"
Cohesion: 0.09
Nodes (6): sitepulse_check_vulnerabilities_api(), sitepulse_get_memory_info(), sitepulse_register_route(), RestApiTest, get_user_meta(), register_rest_route()

### Community 106 - "Repository Guidelines"
Cohesion: 0.25
Nodes (7): Build, Test, and Development Commands, Coding Style & Naming Conventions, Commit & Pull Request Guidelines, graphify, Project Structure & Module Organization, Repository Guidelines, Testing Guidelines

### Community 107 - "SitePulse Logo"
Cohesion: 0.50
Nodes (4): Heartbeat Waveform, SitePulse, SitePulse Logo, Status Indicator Dots

### Community 108 - "WordPress Pulse Logo"
Cohesion: 0.67
Nodes (3): Pulse Waveform, WordPress, WordPress Pulse Logo

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 141 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.08
Nodes (6): Sitepulse_Product_Analytics, Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, sanitize_hex_color(), sanitize_key(), wp_parse_args()

### Community 142 - "get_transient"
Cohesion: 0.06
Nodes (16): Sitepulse_AI_Diagnostic_Cron, Sitepulse_Cron_Fallback, Sitepulse_Cron_Manager, Sitepulse_Loader, CronCoverageTest, CronTest, delete_transient(), get_transient() (+8 more)

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

### Community 149 - "package.json"
Cohesion: 0.12
Nodes (16): description, engines, node, name, private, scripts, coverage:js, test (+8 more)

### Community 150 - "PresentationWordPress.php"
Cohesion: 0.10
Nodes (10): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), get_permalink(), load_textdomain(), remove_all_actions(), wp_date() (+2 more)

### Community 152 - "browser"
Cohesion: 0.14
Nodes (15): assert, { browser }, localization, setup(), test, assert, { browser, jsonResponse }, setup() (+7 more)

### Community 158 - ".make_api_request"
Cohesion: 0.26
Nodes (6): is_wp_error(), wp_json_encode(), wp_parse_url(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_response_code()

### Community 159 - "ref_node_assert"
Cohesion: 0.16
Nodes (11): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+3 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.24
Nodes (8): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test, jsonResponse()

### Community 163 - "browser.cjs"
Cohesion: 0.14
Nodes (11): ref_node_path, assert, { browser }, localization, setup(), test, assert, fs (+3 more)

### Community 170 - "add_action"
Cohesion: 0.06
Nodes (19): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), gethostbyname(), gethostname(), register_activation_hook() (+11 more)

### Community 171 - "CoverageGateTest"
Cohesion: 0.21
Nodes (3): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), CoverageGateTest

### Community 172 - "RestCoverageTest"
Cohesion: 0.16
Nodes (4): sitepulse_check_memory(), sitepulse_enable_save_queries(), sitepulse_update_settings(), RestCoverageTest

### Community 173 - "js-coverage.cjs"
Cohesion: 0.13
Nodes (14): ref_node_child_process, context, coverage, { createContext }, { createCoverageMap }, destination, directory, fs (+6 more)

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 177 - "Sitepulse_Test_Case"
Cohesion: 0.15
Nodes (3): EasyModeSaveQueriesTest, HttpOriginTest, Sitepulse_Test_Case

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-coverage, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 181 - "sitepulse_test_subprocess"
Cohesion: 0.18
Nodes (3): DirectAccessTemplateTest, TestCase, sitepulse_test_subprocess()

### Community 184 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 188 - "remaining-browser-behavior.test.cjs"
Cohesion: 0.29
Nodes (5): assert, backend(), { browser, jsonResponse }, memory, test

### Community 189 - "remaining-ui-flows.test.cjs"
Cohesion: 0.29
Nodes (5): assert, { browser, jsonResponse }, easyGlobals, onboardingGlobals, test

## Knowledge Gaps
- **245 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+240 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1129 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **117 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `DiagnosticTemplateCoverageTest`, `Sitepulse_Whitelabel_Service`, `get_transient`, `AutoloadRestApiTest`, `Sitepulse_Easy_Mode`, `BackendTest`, `update_option`, `LifecycleTest`, `TemplateTest`, `CoverageGateTest`, `RestCoverageTest`, `Sitepulse_Frontend`, `Sitepulse_Page_Tracker`, `update_user_meta`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Onboarding`, `sitepulse_test_subprocess`, `ProfilerCompatibilityTest`, `OptionalViewIntegrationTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Plugin`, `PetAssistantTemplateTest`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `HarnessIsolationTest`, `Sitepulse_Setup`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PluginTest`, `PopulatedTemplateTest`, `RestApiTest`?**
  _High betweenness centrality (0.035) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Whitelabel_Service`, `get_transient`, `WP_Error`, `Sitepulse_Easy_Mode`, `WordPress.php`, `.make_api_request`, `Sitepulse_Test_Database`, `add_action`, `RestCoverageTest`, `Sitepulse_Frontend`, `Sitepulse_Page_Tracker`, `update_user_meta`, `Sitepulse_Onboarding`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `Sitepulse_Setup`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PopulatedTemplateTest`, `RestApiTest`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **Why does `get_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Whitelabel_Service`, `get_transient`, `WP_Error`, `Sitepulse_Easy_Mode`, `WordPress.php`, `Sitepulse_Backend`, `.make_api_request`, `add_action`, `RestCoverageTest`, `Sitepulse_Frontend`, `Sitepulse_Page_Tracker`, `Sitepulse_Onboarding`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `Sitepulse_Setup`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PluginTest`, `RestApiTest`?**
  _High betweenness centrality (0.015) - this node is a cross-community bridge._
- **Are the 136 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 136 INFERRED edges - model-reasoned connections that need verification._
- **Are the 118 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 118 INFERRED edges - model-reasoned connections that need verification._
- **Are the 29 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 29 INFERRED edges - model-reasoned connections that need verification._
- **Are the 52 inferred relationships involving `Sitepulse_Api_Service` (e.g. with `.render_dashboard_section()` and `.check_status_callback()`) actually correct?**
  _`Sitepulse_Api_Service` has 52 INFERRED edges - model-reasoned connections that need verification._
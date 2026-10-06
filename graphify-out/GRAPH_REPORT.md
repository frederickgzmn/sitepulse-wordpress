# Graph Report - sitepulse  (2026-10-06)

## Corpus Check
- 147 files · ~326,335 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 3832 nodes · 8386 edges · 204 communities (86 shown, 118 thin omitted)
- Extraction: 81% EXTRACTED · 19% INFERRED · 0% AMBIGUOUS · INFERRED: 1558 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `8d4630f7`
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
- vs
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
- Sitepulse_Page_Tracker
- Collapse
- contains
- Sitepulse_Getting_Started
- Ze
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
- es
- BaseComponent
- Toast
- ScrollSpy
- Modal
- Sitepulse_Setup
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
- FocusTrap
- Sitepulse_Whitelabel_Service
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
- PageTrackerTest
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
- .compact_backtrace
- BackendTest
- ps
- FocusTrap
- Sitepulse_Test_Local_Filesystem
- an
- browser
- backend.test.cjs
- remove
- LifecycleTest
- browser.cjs
- Sitepulse_Test_Database
- TemplateTest
- Me
- WP_REST_Response
- Swipe
- add_action
- CoverageGateTest
- RestCoverageTest
- js-coverage.cjs
- add_filter
- .handle_toggle
- Automated tests
- .identify_origin
- coverage.cjs
- CoreVariantsTest
- ApiVariantsTest
- sitepulse_test_subprocess
- ProfilerCompatibilityTest
- Sitepulse_Test_Case
- EasyModeSaveQueriesTest
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- HarnessIsolationTest
- Sitepulse_Monitoring
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- CoreAdditionalBehaviorTest
- CronCoverageTest
- .get_status_info
- ActionSchedulerTest

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 146 edges
2. `get_option()` - 122 edges
3. `Sitepulse_Test_WP` - 107 edges
4. `Sitepulse_Api_Service` - 71 edges
5. `Sitepulse_Page_Analysis` - 67 edges
6. `Sitepulse_Settings` - 59 edges
7. `WP_REST_Request` - 55 edges
8. `Sitepulse_Test_Case` - 53 edges
9. `Sitepulse_Profiler` - 51 edges
10. `Sitepulse_Error_Handler` - 46 edges

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

## Communities (204 total, 118 thin omitted)

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
Cohesion: 0.06
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

### Community 19 - ".hide"
Cohesion: 0.09
Nodes (3): li(), Fs, pi

### Community 20 - "TemplateFactory"
Cohesion: 0.10
Nodes (3): Config, sanitizeHtml(), TemplateFactory

### Community 22 - "Sitepulse_Backend"
Cohesion: 0.20
Nodes (3): Sitepulse_Backend, add_menu_page(), current_user_can()

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.13
Nodes (3): Sitepulse_Easy_Mode, EasyModeTest, remove_all_actions()

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "update_option"
Cohesion: 0.06
Nodes (7): Sitepulse_Api_Service, ApiCoverageTest, ApiRegressionTest, ApiServiceTest, delete_transient(), get_option(), update_option()

### Community 27 - "remove"
Cohesion: 0.09
Nodes (3): Modal, remove(), Toast

### Community 28 - "WordPress.php"
Cohesion: 0.04
Nodes (49): sitepulse_register_route(), sitepulse_reorder_active_plugins_for_conflict(), get_permalink(), get_the_title(), add_option(), add_submenu_page(), delete_metadata(), delete_user_meta() (+41 more)

### Community 29 - "B"
Cohesion: 0.07
Nodes (4): B, mo, U, x()

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
Cohesion: 0.09
Nodes (4): getDataAttributes(), H(), Ie, W

### Community 46 - "Sitepulse_Frontend"
Cohesion: 0.16
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Page_Tracker"
Cohesion: 0.12
Nodes (4): Sitepulse_Page_Tracker, wp_strip_all_tags(), get_num_queries(), wp_get_document_title()

### Community 49 - "contains"
Cohesion: 0.19
Nodes (3): contains(), effect$1(), Tab

### Community 51 - "Ze"
Cohesion: 0.11
Nodes (3): Ue(), Wi, Ze

### Community 52 - "Sitepulse_Onboarding"
Cohesion: 0.09
Nodes (3): Sitepulse_Onboarding, CoreRegressionTest, SetupOnboardingTest

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Plugin"
Cohesion: 0.11
Nodes (4): Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest, has_filter()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Plugin_Profiler"
Cohesion: 0.07
Nodes (8): inc/ helpers, Sitepulse_Plugin_Profiler, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), Sitepulse_Loader, sitepulse_move_to_first_position(), PluginProfilerTest, delete_option()

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.07
Nodes (5): Sitepulse_Profiler, ProfilerTest, do_action(), get_template(), wp_normalize_path()

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
Nodes (3): es, ot, W

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.06
Nodes (5): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n(), wp_generate_password()

### Community 83 - "home_url"
Cohesion: 0.08
Nodes (19): sitepulse_sanitize_param(), add_query_arg(), admin_url(), esc_js(), esc_url_raw(), get_current_screen(), home_url(), remove_query_arg() (+11 more)

### Community 86 - "get_transient"
Cohesion: 0.14
Nodes (5): Sitepulse_Cron_Fallback, CronTest, get_transient(), has_action(), set_transient()

### Community 90 - "popperGenerator"
Cohesion: 0.17
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "detectOverflow"
Cohesion: 0.24
Nodes (17): detectOverflow(), expandToHashMap(), getBoundingClientRect(), getClientRectFromMixedType(), getClippingRect(), getCompositeRect(), getDocumentElement(), getDocumentRect() (+9 more)

### Community 102 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.07
Nodes (7): Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, PetAssistantTemplateTest, PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data(), wp_parse_args()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "WP_REST_Request"
Cohesion: 0.05
Nodes (42): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_check_vulnerabilities_api(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding() (+34 more)

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

### Community 141 - ".ajax_toggle_theme"
Cohesion: 0.24
Nodes (5): check_ajax_referer(), Sitepulse_Test_Json_Response, WP_Filesystem(), wp_send_json_error(), wp_send_json_success()

### Community 142 - "Sitepulse_AI_Diagnostic_Cron"
Cohesion: 0.09
Nodes (8): Sitepulse_AI_Diagnostic_Cron, Sitepulse_Cron_Manager, wp_clear_scheduled_hook(), wp_get_scheduled_event(), wp_get_schedules(), wp_next_scheduled(), wp_schedule_event(), wp_unschedule_event()

### Community 143 - "AutoloadRestApiTest"
Cohesion: 0.21
Nodes (3): sitepulse_update_autoload_option(), AutoloadRestApiTest, wp_cache_delete()

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
Cohesion: 0.12
Nodes (9): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), wp_date(), wp_mail() (+1 more)

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

### Community 161 - "remove"
Cohesion: 0.15
Nodes (3): G, remove(), Ti

### Community 163 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 170 - "add_action"
Cohesion: 0.10
Nodes (9): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes(), register_activation_hook(), register_deactivation_hook(), add_action() (+1 more)

### Community 171 - "CoverageGateTest"
Cohesion: 0.20
Nodes (3): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), CoverageGateTest

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "add_filter"
Cohesion: 0.24
Nodes (3): add_filter(), apply_filters(), Sitepulse_Test_Hook

### Community 175 - ".handle_toggle"
Cohesion: 0.28
Nodes (7): RuntimeException, check_admin_referer(), Sitepulse_Test_Die, Sitepulse_Test_Redirect, wp_die(), wp_get_referer(), wp_safe_redirect()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 177 - ".identify_origin"
Cohesion: 0.17
Nodes (4): HttpOriginTest, get_theme_root(), Sitepulse_Test_Theme, wp_get_theme()

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 189 - "Sitepulse_Monitoring"
Cohesion: 0.13
Nodes (8): Sitepulse_Monitoring, MonitoringTest, submit_button(), wp_nonce_field(), esc_attr(), esc_html(), esc_html_e(), esc_url()

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "CoreAdditionalBehaviorTest"
Cohesion: 0.09
Nodes (4): Sitepulse_Product_Analytics, CoreAdditionalBehaviorTest, absint(), sanitize_key()

## Knowledge Gaps
- **249 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+244 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1173 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **118 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `PageTrackerTest`, `Sitepulse_CurLoader`, `DiagnosticTemplateCoverageTest`, `.ajax_toggle_theme`, `AutoloadRestApiTest`, `Sitepulse_Easy_Mode`, `BackendTest`, `update_option`, `LifecycleTest`, `update_user_meta`, `TemplateTest`, `CoverageGateTest`, `RestCoverageTest`, `Sitepulse_Frontend`, `Sitepulse_Page_Tracker`, `.identify_origin`, `Sitepulse_Getting_Started`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Onboarding`, `ProfilerCompatibilityTest`, `EasyModeSaveQueriesTest`, `OptionalViewIntegrationTest`, `HarnessIsolationTest`, `Sitepulse_Monitoring`, `ErrorHandlerVariantsTest`, `Sitepulse_Plugin`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `CoreAdditionalBehaviorTest`, `CronCoverageTest`, `ActionSchedulerTest`, `Sitepulse_Setup`, `Sitepulse_Page_Analysis`, `get_transient`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PluginTest`, `Sitepulse_Whitelabel_Service`, `WP_REST_Request`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `PageTrackerTest`, `Sitepulse_CurLoader`, `DiagnosticTemplateCoverageTest`, `Sitepulse_AI_Diagnostic_Cron`, `WordPress.php`, `Sitepulse_Test_Database`, `update_user_meta`, `RestCoverageTest`, `Sitepulse_Frontend`, `add_filter`, `Sitepulse_Getting_Started`, `Sitepulse_Onboarding`, `Sitepulse_Monitoring`, `Sitepulse_Plugin`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `CoreAdditionalBehaviorTest`, `Sitepulse_Setup`, `Sitepulse_Page_Analysis`, `home_url`, `get_transient`, `Sitepulse_Utils`, `Sitepulse_Settings`, `Sitepulse_Whitelabel_Service`, `WP_REST_Request`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._
- **Why does `get_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `.ajax_toggle_theme`, `Sitepulse_AI_Diagnostic_Cron`, `Sitepulse_Backend`, `WordPress.php`, `RestCoverageTest`, `add_filter`, `Sitepulse_Page_Tracker`, `Sitepulse_Getting_Started`, `Sitepulse_Onboarding`, `Sitepulse_Monitoring`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `CoreAdditionalBehaviorTest`, `CronCoverageTest`, `Sitepulse_Setup`, `Sitepulse_Page_Analysis`, `get_transient`, `Sitepulse_Utils`, `Sitepulse_Settings`, `Sitepulse_Whitelabel_Service`, `WP_REST_Request`?**
  _High betweenness centrality (0.014) - this node is a cross-community bridge._
- **Are the 142 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 142 INFERRED edges - model-reasoned connections that need verification._
- **Are the 118 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 118 INFERRED edges - model-reasoned connections that need verification._
- **Are the 33 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 33 INFERRED edges - model-reasoned connections that need verification._
- **Are the 52 inferred relationships involving `Sitepulse_Api_Service` (e.g. with `.render_dashboard_section()` and `.check_status_callback()`) actually correct?**
  _`Sitepulse_Api_Service` has 52 INFERRED edges - model-reasoned connections that need verification._
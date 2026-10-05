# Graph Report - sitepulse  (2026-10-04)

## Corpus Check
- 101 files · ~267,250 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 3242 nodes · 7043 edges · 168 communities (63 shown, 105 thin omitted)
- Extraction: 85% EXTRACTED · 15% INFERRED · 0% AMBIGUOUS · INFERRED: 1027 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0a627dfe`
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
- Fi
- bootstrap.bundle.js
- Tooltip
- backend.js
- Fs
- TemplateFactory
- ._queueCallback
- WP_REST_Request
- Sitepulse_Backend
- SitePulse
- Tooltip
- get_option
- Toast
- WordPress.php
- B
- Carousel
- ._queueCallback
- Carousel
- lt
- Carousel
- Backdrop
- .hide
- getBoundingClientRect
- Modal
- Backdrop
- Offcanvas
- bootstrap.min.js
- be
- Offcanvas
- Ti
- Offcanvas
- Sitepulse_Test_Case
- remove
- Collapse
- contains
- .hide
- remove
- Sitepulse_Onboarding
- remove
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
- detectOverflow
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
- Sitepulse_Whitelabel_Service
- qt
- Ss
- ScrollSpy
- frontend.js
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
- FocusTrap
- an
- FocusTrap
- sitepulse_global.js
- pi
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
- Swipe
- SitePulse — See What's Powering (or Slowing) Your Site
- add_action
- get_transient
- AutoloadRestApiTest
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- package.json
- CoreKnownDefectsTest
- Sitepulse_Test_Filesystem
- browser
- Automated tests
- BackendTest
- wp_normalize_path
- FocusTrap
- .make_api_request
- ref_node_assert
- backend.test.cjs
- LifecycleTest
- browser.cjs
- Sitepulse_Test_Database
- TemplateTest
- easy-mode.test.cjs
- WP_REST_Response
- onboarding.test.cjs

## God Nodes (most connected - your core abstractions)
1. `get_option()` - 107 edges
2. `update_option()` - 94 edges
3. `Sitepulse_Test_WP` - 62 edges
4. `Sitepulse_Settings` - 53 edges
5. `Sitepulse_Api_Service` - 50 edges
6. `WP_REST_Request` - 48 edges
7. `Tooltip` - 41 edges
8. `Tooltip` - 41 edges
9. `Tooltip` - 41 edges
10. `ps` - 40 edges

## Surprising Connections (you probably didn't know these)
- `Project Structure & Module Organization` --references--> `Sitepulse_Plugin`  [INFERRED]
  AGENTS.md → class/plugin.php
- `JavaScript` --references--> `Sitepulse_Profiler`  [INFERRED]
  tests/KNOWN_DEFECTS.md → class/profiler.php
- `AI Diagnostic Reports` --semantically_similar_to--> `AI Diagnostics`  [INFERRED] [semantically similar]
  readme.txt → README.md
- `Google PageSpeed Integration` --semantically_similar_to--> `Google PageSpeed Insights`  [INFERRED] [semantically similar]
  readme.txt → README.md
- `sitepulse_check_ai_diagnostic_status_api()` --calls--> `Sitepulse_Api_Service`  [INFERRED]
  inc/api_backend.php → class/api_service.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **SitePulse Core Monitoring Features** — readme_hook_level_timing, readme_slow_plugin_profiler, readme_per_page_tracking, readme_external_request_monitoring, readme_php_error_tracking [EXTRACTED 1.00]
- **SitePulse Logo Visual Elements** — assets_img_sitepulse_logo_sitepulse_logo, assets_img_sitepulse_logo_heartbeat_waveform, assets_img_sitepulse_logo_status_dots [INFERRED 0.85]

## Communities (168 total, 105 thin omitted)

### Community 0 - "bootstrap.esm.js"
Cohesion: 0.03
Nodes (72): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+64 more)

### Community 1 - "bootstrap.bundle.min.js"
Cohesion: 0.07
Nodes (53): be(), bi(), d(), Ce(), ci(), D(), De(), _e() (+45 more)

### Community 2 - "bootstrap.esm.min.js"
Cohesion: 0.04
Nodes (65): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+57 more)

### Community 3 - "Tooltip"
Cohesion: 0.06
Nodes (3): Collapse, Popover, Tooltip

### Community 5 - "Sitepulse_Error_Handler"
Cohesion: 0.09
Nodes (8): Sitepulse_Error_Handler, ErrorHandlerTest, current_time(), get_bloginfo(), home_url(), is_email(), sanitize_email(), site_url()

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
Nodes (23): distanceAndSkiddingToXY(), getDataAttribute(), getDataAttributes(), getSideOffsets(), hide(), isAnySideFullyClipped(), normalizeData(), normalizeDataKey() (+15 more)

### Community 18 - "backend.js"
Cohesion: 0.08
Nodes (20): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike(), getScoreColor() (+12 more)

### Community 20 - "TemplateFactory"
Cohesion: 0.09
Nodes (3): Config, sanitizeHtml(), TemplateFactory

### Community 22 - "WP_REST_Request"
Cohesion: 0.05
Nodes (36): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_check_vulnerabilities_api(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice() (+28 more)

### Community 23 - "Sitepulse_Backend"
Cohesion: 0.08
Nodes (10): Sitepulse_Backend, Sitepulse_Easy_Mode, EasyModeTest, check_ajax_referer(), current_user_can(), sanitize_text_field(), Sitepulse_Test_Json_Response, wp_send_json_error() (+2 more)

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "get_option"
Cohesion: 0.06
Nodes (10): Sitepulse_Api_Service, Sitepulse_Product_Analytics, sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), ApiServiceTest, get_option(), sanitize_key(), update_option() (+2 more)

### Community 28 - "WordPress.php"
Cohesion: 0.05
Nodes (54): sitepulse_sanitize_param(), RuntimeException, absint(), add_menu_page(), add_option(), add_submenu_page(), admin_url(), delete_metadata() (+46 more)

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

### Community 44 - "Ti"
Cohesion: 0.07
Nodes (5): getDataAttributes(), H(), Ie, Ti, W

### Community 46 - "Sitepulse_Test_Case"
Cohesion: 0.10
Nodes (10): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/, Sitepulse_Frontend, PHPUnit\Framework\TestCase (+2 more)

### Community 49 - "contains"
Cohesion: 0.19
Nodes (3): contains(), effect$1(), Tab

### Community 51 - "remove"
Cohesion: 0.17
Nodes (3): Ue(), remove(), Ze

### Community 52 - "Sitepulse_Onboarding"
Cohesion: 0.09
Nodes (3): Sitepulse_Onboarding, SetupOnboardingTest, delete_option()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

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

### Community 71 - "detectOverflow"
Cohesion: 0.17
Nodes (21): arrow(), computeAutoPlacement(), computeOffsets(), computeStyles(), detectOverflow(), expandToHashMap(), flip(), getAltAxis() (+13 more)

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 81 - "easy-mode.js"
Cohesion: 0.14
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.09
Nodes (8): inc/ helpers, Sitepulse_Whitelabel_Service, sitepulse_detect_first_position_conflict(), sitepulse_reorder_active_plugins_for_conflict(), BrandingAnalyticsTest, plugin_basename(), sanitize_hex_color(), wp_parse_args()

### Community 86 - "frontend.js"
Cohesion: 0.32
Nodes (12): getRealTimeMode(), resetModalToDefault(), restoreTracking(), setPageTPLoadActive(), setRealTimeMode(), setSPReportMode(), showCompletionModal(), simulateResourceDetection() (+4 more)

### Community 90 - "popperGenerator"
Cohesion: 0.15
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "getDocumentElement"
Cohesion: 0.27
Nodes (11): getClientRectFromMixedType(), getClippingRect(), getDocumentElement(), getDocumentRect(), getInnerBoundingClientRect(), getUAString(), getViewportRect(), getWindowScroll() (+3 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.07
Nodes (6): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters(), _get_cron_array()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

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
Cohesion: 0.14
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 141 - "add_action"
Cohesion: 0.08
Nodes (7): Sitepulse_Page_Tracker, register_activation_hook(), register_deactivation_hook(), PageTrackerTest, add_action(), add_filter(), Sitepulse_Test_Hook

### Community 142 - "get_transient"
Cohesion: 0.06
Nodes (15): Sitepulse_AI_Diagnostic_Cron, Sitepulse_Cron_Fallback, Sitepulse_Cron_Manager, Sitepulse_Loader, CronTest, do_action(), get_transient(), has_action() (+7 more)

### Community 143 - "AutoloadRestApiTest"
Cohesion: 0.22
Nodes (4): sitepulse_get_autoload_options(), sitepulse_get_protected_options(), sitepulse_update_autoload_option(), AutoloadRestApiTest

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
Nodes (15): description, devDependencies, jquery, jsdom, engines, node, name, private (+7 more)

### Community 152 - "browser"
Cohesion: 0.21
Nodes (11): assert, { browser }, localization, setup(), test, assert, { browser, jsonResponse }, setup() (+3 more)

### Community 153 - "Automated tests"
Cohesion: 0.18
Nodes (8): Existing defects reproduced by the new tests, JavaScript, PHP, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 158 - ".make_api_request"
Cohesion: 0.53
Nodes (5): is_wp_error(), wp_json_encode(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_response_code()

### Community 159 - "ref_node_assert"
Cohesion: 0.16
Nodes (11): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+3 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.24
Nodes (8): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test, jsonResponse()

### Community 163 - "browser.cjs"
Cohesion: 0.25
Nodes (7): ref_node_fs, ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path

### Community 166 - "easy-mode.test.cjs"
Cohesion: 0.29
Nodes (5): assert, { browser }, localization, setup(), test

### Community 168 - "onboarding.test.cjs"
Cohesion: 0.33
Nodes (4): assert, { browser, jsonResponse }, setup(), test

## Knowledge Gaps
- **208 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+203 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1000 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **105 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `get_option()` connect `get_option` to `Sitepulse_Settings`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `add_action`, `Sitepulse_Test_Case`, `Sitepulse_Setup`, `get_transient`, `Sitepulse_Whitelabel_Service`, `Sitepulse_Onboarding`, `Sitepulse_Utils`, `Sitepulse_Backend`, `Sitepulse_Plugin`, `WP_REST_Request`, `CoreKnownDefectsTest`, `WordPress.php`, `.make_api_request`?**
  _High betweenness centrality (0.015) - this node is a cross-community bridge._
- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `add_action`, `get_transient`, `AutoloadRestApiTest`, `WP_REST_Request`, `Sitepulse_Backend`, `CoreKnownDefectsTest`, `BackendTest`, `get_option`, `LifecycleTest`, `TemplateTest`, `Sitepulse_Onboarding`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `Sitepulse_Whitelabel_Service`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PluginTest`?**
  _High betweenness centrality (0.014) - this node is a cross-community bridge._
- **Why does `update_option()` connect `get_option` to `Sitepulse_Settings`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `add_action`, `Sitepulse_Setup`, `get_transient`, `Sitepulse_Test_Case`, `Sitepulse_Whitelabel_Service`, `Sitepulse_Onboarding`, `WP_REST_Request`, `Sitepulse_Utils`, `WordPress.php`, `.make_api_request`?**
  _High betweenness centrality (0.012) - this node is a cross-community bridge._
- **Are the 103 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.get_ai_diagnostic_report()`) actually correct?**
  _`get_option()` has 103 INFERRED edges - model-reasoned connections that need verification._
- **Are the 90 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 90 INFERRED edges - model-reasoned connections that need verification._
- **Are the 7 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_callback_persists_remote_state_and_stops_only_for_terminal_success()` and `.test_scheduled_callback_sends_actual_site_data_and_unschedule_clears_duplicates()`) actually correct?**
  _`Sitepulse_Test_WP` has 7 INFERRED edges - model-reasoned connections that need verification._
- **Are the 20 inferred relationships involving `Sitepulse_Settings` (e.g. with `.get_website_info()` and `.process_settings_form()`) actually correct?**
  _`Sitepulse_Settings` has 20 INFERRED edges - model-reasoned connections that need verification._
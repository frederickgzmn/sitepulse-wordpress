# Graph Report - sitepulse  (2026-10-05)

## Corpus Check
- 147 files · ~296,925 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 3820 nodes · 8359 edges · 209 communities (88 shown, 121 thin omitted)
- Extraction: 81% EXTRACTED · 19% INFERRED · 0% AMBIGUOUS · INFERRED: 1548 edges (avg confidence: 0.85)
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
- ps
- St
- BaseComponent
- TemplateFactory
- Fi
- bootstrap.bundle.js
- Popover
- backend.js
- Fs
- TemplateFactory
- ._queueCallback
- api_backend.php
- Sitepulse_Easy_Mode
- SitePulse
- Tooltip
- Sitepulse_Api_Service
- .hide
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
- Tooltip
- Tab
- .hide
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
- Sitepulse_Backend
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
- W
- BaseComponent
- Toast
- ScrollSpy
- Modal
- Sitepulse_Test_Case
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- .enqueue_styles_and_scripts
- Ss
- ScrollSpy
- get_transient
- Sitepulse_Utils
- ts
- Swipe
- popperGenerator
- getBoundingClientRect
- un
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
- get_option
- Repository Guidelines
- SitePulse Logo
- WordPress Pulse Logo
- CommandCode
- page-analysis.js
- SitePulse WordPress Plugin Icon
- SitePulse Logo
- Sitepulse Plugin Icon
- Sitepulse Logo
- contains
- Sitepulse_CurLoader
- DiagnosticTemplateCoverageTest
- SitePulse — See What's Powering (or Slowing) Your Site
- Sitepulse_Whitelabel_Service
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
- .hide
- FocusTrap
- remove
- is_wp_error
- browser
- backend.test.cjs
- Ti
- LifecycleTest
- browser.cjs
- Sitepulse_Test_Database
- TemplateTest
- mo
- WP_REST_Response
- Swipe
- add_action
- Sitepulse_Test_Local_Filesystem
- RestCoverageTest
- js-coverage.cjs
- apply_filters
- ot
- Automated tests
- .identify_origin
- coverage.cjs
- CoreVariantsTest
- ApiVariantsTest
- sitepulse_test_subprocess
- ProfilerCompatibilityTest
- pi
- home_url
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- .admin_notice_onboarding
- Sitepulse_Monitoring
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- update_option
- CronCoverageTest
- api-variants.php
- sanitize_text_field
- .test_page_analysis_routes_start_report_and_remove_an_analysis
- ActionScheduler_Store
- ActionSchedulerTest
- FrontendTest
- fastcgi_finish_request

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 145 edges
2. `get_option()` - 121 edges
3. `Sitepulse_Test_WP` - 106 edges
4. `Sitepulse_Api_Service` - 71 edges
5. `Sitepulse_Page_Analysis` - 66 edges
6. `Sitepulse_Settings` - 59 edges
7. `WP_REST_Request` - 55 edges
8. `Sitepulse_Test_Case` - 53 edges
9. `Sitepulse_Profiler` - 50 edges
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

## Communities (209 total, 121 thin omitted)

### Community 0 - "bootstrap.esm.js"
Cohesion: 0.03
Nodes (72): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+64 more)

### Community 1 - "bootstrap.bundle.min.js"
Cohesion: 0.07
Nodes (52): be(), bi(), Ce(), ci(), D(), De(), _e(), ei() (+44 more)

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
Cohesion: 0.09
Nodes (3): d(), ps, vs

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

### Community 20 - "TemplateFactory"
Cohesion: 0.09
Nodes (3): Config, sanitizeHtml(), TemplateFactory

### Community 22 - "api_backend.php"
Cohesion: 0.19
Nodes (20): sitepulse_check_ai_diagnostic_status_api(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events(), sitepulse_get_curl_stats() (+12 more)

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.08
Nodes (8): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, EasyModeTest, GettingStartedTest, get_current_user_id(), get_user_meta(), is_user_logged_in(), metadata_exists()

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 28 - "WordPress.php"
Cohesion: 0.06
Nodes (39): sitepulse_register_route(), RuntimeException, add_menu_page(), add_submenu_page(), check_admin_referer(), check_ajax_referer(), current_user_can(), delete_user_meta() (+31 more)

### Community 35 - "Backdrop"
Cohesion: 0.13
Nodes (6): Backdrop, execute(), executeAfterTransition(), getTransitionDurationFromElement(), Swipe, triggerTransitionEnd()

### Community 36 - "WP_REST_Request"
Cohesion: 0.10
Nodes (4): ArrayAccess, sitepulse_pause_monitoring(), sitepulse_resume_monitoring(), WP_REST_Request

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
Cohesion: 0.11
Nodes (4): getDataAttributes(), H(), R, W

### Community 46 - "Sitepulse_Frontend"
Cohesion: 0.23
Nodes (3): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar

### Community 47 - "Sitepulse_Page_Tracker"
Cohesion: 0.11
Nodes (3): Sitepulse_Page_Tracker, Sitepulse_Loader, PageTrackerTest

### Community 51 - "remove"
Cohesion: 0.17
Nodes (3): Ue(), remove(), Ze

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Backend"
Cohesion: 0.07
Nodes (7): Sitepulse_Backend, Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest, has_filter(), update_user_meta(), TemplateRenderingTest

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Plugin_Profiler"
Cohesion: 0.09
Nodes (5): Sitepulse_Plugin_Profiler, PluginProfilerTest, get_plugins(), get_site_option(), is_multisite()

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.07
Nodes (6): Sitepulse_Profiler, ProfilerTest, do_action(), get_num_queries(), get_template(), wp_normalize_path()

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
Cohesion: 0.15
Nodes (23): arrow(), computeAutoPlacement(), computeOffsets(), computeStyles(), detectOverflow(), expandToHashMap(), flip(), getAltAxis() (+15 more)

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 78 - "Sitepulse_Test_Case"
Cohesion: 0.07
Nodes (5): Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest, Sitepulse_Test_Case

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.06
Nodes (7): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n(), sitepulse_test_post_type(), wp_generate_password(), wp_remote_get()

### Community 83 - ".enqueue_styles_and_scripts"
Cohesion: 0.09
Nodes (15): remove_all_actions(), add_query_arg(), admin_url(), esc_js(), esc_url_raw(), get_current_screen(), remove_query_arg(), rest_url() (+7 more)

### Community 86 - "get_transient"
Cohesion: 0.14
Nodes (5): Sitepulse_Cron_Fallback, CronTest, get_transient(), has_action(), set_transient()

### Community 90 - "popperGenerator"
Cohesion: 0.15
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "getBoundingClientRect"
Cohesion: 0.26
Nodes (15): getBoundingClientRect(), getClientRectFromMixedType(), getCompositeRect(), getDocumentElement(), getDocumentRect(), getHTMLElementScroll(), getInnerBoundingClientRect(), getNodeScroll() (+7 more)

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.12
Nodes (4): PetAssistantTemplateTest, PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "get_option"
Cohesion: 0.09
Nodes (3): sitepulse_check_vulnerabilities_api(), RestApiTest, get_option()

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

### Community 136 - "contains"
Cohesion: 0.14
Nodes (4): contains(), effect$1(), Modal, remove()

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 141 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.06
Nodes (12): inc/ helpers, Sitepulse_Product_Analytics, Sitepulse_Whitelabel_Service, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), BrandingAnalyticsTest (+4 more)

### Community 142 - "Sitepulse_AI_Diagnostic_Cron"
Cohesion: 0.09
Nodes (9): Sitepulse_AI_Diagnostic_Cron, Sitepulse_Cron_Manager, delete_transient(), wp_clear_scheduled_hook(), wp_get_scheduled_event(), wp_get_schedules(), wp_next_scheduled(), wp_schedule_event() (+1 more)

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

### Community 149 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, coverage:js, test, test:coverage, test:js

### Community 150 - "PresentationWordPress.php"
Cohesion: 0.10
Nodes (11): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), wp_date(), wp_mail() (+3 more)

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 157 - "remove"
Cohesion: 0.16
Nodes (3): es, remove(), x()

### Community 158 - "is_wp_error"
Cohesion: 0.40
Nodes (5): is_wp_error(), wp_json_encode(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_response_code()

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 170 - "add_action"
Cohesion: 0.15
Nodes (5): register_activation_hook(), register_deactivation_hook(), add_action(), add_filter(), Sitepulse_Test_Hook

### Community 171 - "Sitepulse_Test_Local_Filesystem"
Cohesion: 0.09
Nodes (5): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), CoverageGateTest, EasyModeSaveQueriesTest, Sitepulse_Test_Local_Filesystem

### Community 172 - "RestCoverageTest"
Cohesion: 0.20
Nodes (4): sitepulse_check_memory(), sitepulse_enable_save_queries(), sitepulse_set_sp_profiler(), RestCoverageTest

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 177 - ".identify_origin"
Cohesion: 0.17
Nodes (4): HttpOriginTest, get_theme_root(), Sitepulse_Test_Theme, wp_get_theme()

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 181 - "sitepulse_test_subprocess"
Cohesion: 0.17
Nodes (3): DirectAccessTemplateTest, TestCase, sitepulse_test_subprocess()

### Community 184 - "home_url"
Cohesion: 0.18
Nodes (7): get_permalink(), get_the_title(), get_bloginfo(), get_post_types(), get_posts(), get_the_ID(), home_url()

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 188 - ".admin_notice_onboarding"
Cohesion: 0.19
Nodes (8): sitepulse_sanitize_param(), submit_button(), wp_nonce_field(), esc_attr(), esc_html(), esc_html_e(), esc_url(), wp_kses_post()

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "update_option"
Cohesion: 0.05
Nodes (8): ApiCoverageTest, ApiRegressionTest, CoreAdditionalBehaviorTest, HarnessIsolationTest, add_option(), delete_metadata(), Sitepulse_Test_WP, update_option()

### Community 200 - "api-variants.php"
Cohesion: 0.22
Nodes (5): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes()

### Community 201 - "sanitize_text_field"
Cohesion: 0.22
Nodes (6): sitepulse_dismiss_getting_started(), sitepulse_set_dashboard_view(), sitepulse_set_wpslowhttp(), absint(), sanitize_key(), sanitize_text_field()

### Community 202 - ".test_page_analysis_routes_start_report_and_remove_an_analysis"
Cohesion: 0.52
Nodes (6): sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report(), sitepulse_page_analysis_response(), sitepulse_page_analysis_sample()

## Knowledge Gaps
- **249 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+244 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1170 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **121 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `DiagnosticTemplateCoverageTest`, `Sitepulse_Whitelabel_Service`, `AutoloadRestApiTest`, `Sitepulse_Easy_Mode`, `BackendTest`, `Sitepulse_Api_Service`, `WordPress.php`, `LifecycleTest`, `TemplateTest`, `Sitepulse_Test_Local_Filesystem`, `RestCoverageTest`, `Sitepulse_Frontend`, `Sitepulse_Page_Tracker`, `apply_filters`, `.identify_origin`, `CoreVariantsTest`, `ApiVariantsTest`, `sitepulse_test_subprocess`, `ProfilerCompatibilityTest`, `OptionalViewIntegrationTest`, `Sitepulse_Monitoring`, `ErrorHandlerVariantsTest`, `Sitepulse_Backend`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `update_option`, `CronCoverageTest`, `ActionSchedulerTest`, `FrontendTest`, `Sitepulse_Page_Analysis`, `get_transient`, `Sitepulse_Utils`, `PluginTest`, `PopulatedTemplateTest`, `get_option`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Whitelabel_Service`, `Sitepulse_AI_Diagnostic_Cron`, `api_backend.php`, `Sitepulse_Easy_Mode`, `Sitepulse_Api_Service`, `WordPress.php`, `is_wp_error`, `Sitepulse_Test_Database`, `RestCoverageTest`, `Sitepulse_Frontend`, `apply_filters`, `Sitepulse_Onboarding`, `home_url`, `Sitepulse_Monitoring`, `Sitepulse_Backend`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `sanitize_text_field`, `Sitepulse_Test_Case`, `Sitepulse_Page_Analysis`, `get_transient`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PopulatedTemplateTest`, `get_option`?**
  _High betweenness centrality (0.021) - this node is a cross-community bridge._
- **Why does `get_option()` connect `get_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Whitelabel_Service`, `Sitepulse_AI_Diagnostic_Cron`, `api_backend.php`, `Sitepulse_Easy_Mode`, `Sitepulse_Api_Service`, `WordPress.php`, `is_wp_error`, `add_action`, `RestCoverageTest`, `apply_filters`, `Sitepulse_Onboarding`, `home_url`, `.admin_notice_onboarding`, `Sitepulse_Monitoring`, `Sitepulse_Backend`, `Sitepulse_Plugin_Profiler`, `Sitepulse_Profiler`, `update_option`, `CronCoverageTest`, `sanitize_text_field`, `Sitepulse_Test_Case`, `Sitepulse_Page_Analysis`, `get_transient`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PluginTest`?**
  _High betweenness centrality (0.014) - this node is a cross-community bridge._
- **Are the 141 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 141 INFERRED edges - model-reasoned connections that need verification._
- **Are the 117 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 117 INFERRED edges - model-reasoned connections that need verification._
- **Are the 33 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 33 INFERRED edges - model-reasoned connections that need verification._
- **Are the 52 inferred relationships involving `Sitepulse_Api_Service` (e.g. with `.render_dashboard_section()` and `.check_status_callback()`) actually correct?**
  _`Sitepulse_Api_Service` has 52 INFERRED edges - model-reasoned connections that need verification._
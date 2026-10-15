# Graph Report - sitepulse  (2026-10-15)

## Corpus Check
- 178 files · ~363,338 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4426 nodes · 10102 edges · 213 communities (96 shown, 117 thin omitted)
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 2314 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `bbc601b5`
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
- contains
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
- .hide
- WordPress.php
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
- W
- Offcanvas
- Sitepulse_Frontend
- Sitepulse_Setup
- remove
- Tab
- Sitepulse_Remote_Management
- Ze
- WP_Error
- Sitepulse_AI_Diagnostic_Cron
- BaseComponent
- Sitepulse_Remote_Updates
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
- Sitepulse_Test_Case
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
- Sitepulse_Backend
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- plugin_basename
- Ss
- ScrollSpy
- .time
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
- Sitepulse_Page_Tracker
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
- Swipe
- Sitepulse_CurLoader
- Sitepulse_Test_Database
- SitePulse — See What's Powering (or Slowing) Your Site
- vs
- AutoloadRestApiTest
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- scripts
- PresentationWordPress.php
- FocusTrap
- onboarding.test.cjs
- Sitepulse_Remote_Activity
- BackendTest
- ps
- FocusTrap
- an
- Sitepulse_Plugin_Profiler
- browser
- backend.test.cjs
- Ln
- LifecycleTest
- browser.cjs
- RestApiTest
- TemplateTest
- Sitepulse_Monitoring
- WP_REST_Response
- Sitepulse_Test_Upgrades
- add_action
- DiagnosticTemplateCoverageTest
- RestCoverageTest
- js-coverage.cjs
- update_option
- Me
- Automated tests
- Sitepulse_Remote_Client
- coverage.cjs
- CoreVariantsTest
- ApiVariantsTest
- HttpOriginTest
- is_wp_error
- wp_normalize_path
- .queue_event
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- get_option
- delete_transient
- B
- TemplateRenderingTest
- RecentDashboardTest
- Sitepulse_Remote_Health
- RemoteInventoryTest
- ProfilerCompatibilityTest
- sitepulse_test_subprocess
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- Sitepulse_Onboarding
- Sitepulse_Test_Backups

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 208 edges
2. `get_option()` - 159 edges
3. `Sitepulse_Test_WP` - 130 edges
4. `Sitepulse_Remote_Management` - 92 edges
5. `Sitepulse_Profiler` - 74 edges
6. `Sitepulse_Api_Service` - 73 edges
7. `Sitepulse_Remote_Backups` - 68 edges
8. `Sitepulse_Page_Analysis` - 67 edges
9. `Sitepulse_Test_Case` - 66 edges
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

## Communities (213 total, 117 thin omitted)

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
Cohesion: 0.05
Nodes (10): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, current_time(), esc_html(), esc_html_e(), get_bloginfo() (+2 more)

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

### Community 11 - "contains"
Cohesion: 0.15
Nodes (4): contains(), effect$1(), Modal, remove()

### Community 13 - "BaseComponent"
Cohesion: 0.07
Nodes (4): Backdrop, BaseComponent, Button, Config

### Community 14 - "TemplateFactory"
Cohesion: 0.09
Nodes (5): Config, getElement(), isElement(), TemplateFactory, toType()

### Community 16 - "bootstrap.bundle.js"
Cohesion: 0.07
Nodes (25): distanceAndSkiddingToXY(), getDataAttribute(), getDataAttributes(), getFreshSideObject(), getSideOffsets(), hide(), isAnySideFullyClipped(), mergePaddingObject() (+17 more)

### Community 17 - "Tooltip"
Cohesion: 0.07
Nodes (3): Collapse, Popover, Tooltip

### Community 18 - "backend.js"
Cohesion: 0.09
Nodes (20): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike(), getScoreColor() (+12 more)

### Community 19 - ".hide"
Cohesion: 0.09
Nodes (3): li(), Fs, pi

### Community 20 - "TemplateFactory"
Cohesion: 0.09
Nodes (3): Config, sanitizeHtml(), TemplateFactory

### Community 22 - "current_user_can"
Cohesion: 0.12
Nodes (10): sitepulse_register_route(), sitepulse_sanitize_param(), check_ajax_referer(), current_user_can(), register_rest_route(), sanitize_text_field(), Sitepulse_Test_Json_Response, wp_send_json_error() (+2 more)

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.07
Nodes (15): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, sitepulse_dismiss_onboarding_notice(), sitepulse_set_sp_profiler(), sitepulse_set_trackers_disabled_notice(), sitepulse_set_wpslowhttp(), EasyModeTest, GettingStartedTest (+7 more)

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.05
Nodes (4): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup_Job, UpdraftPlus_Options

### Community 28 - "WordPress.php"
Cohesion: 0.03
Nodes (60): Sitepulse_Remote_Inventory, RuntimeException, disk_free_space(), get_permalink(), get_the_title(), sitepulse_test_site_pages(), add_menu_page(), add_option() (+52 more)

### Community 35 - "Backdrop"
Cohesion: 0.13
Nodes (6): Backdrop, execute(), executeAfterTransition(), getTransitionDurationFromElement(), Swipe, triggerTransitionEnd()

### Community 37 - "getBoundingClientRect"
Cohesion: 0.19
Nodes (25): applyStyles(), effect$2(), getBoundingClientRect(), getClippingParents(), getCompositeRect(), getComputedStyle$1(), getContainingBlock(), getHTMLElementScroll() (+17 more)

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

### Community 47 - "Sitepulse_Setup"
Cohesion: 0.12
Nodes (3): Sitepulse_Setup, CoreRegressionTest, ProDetectionTest

### Community 48 - "remove"
Cohesion: 0.15
Nodes (3): G, remove(), Ti

### Community 50 - "Sitepulse_Remote_Management"
Cohesion: 0.10
Nodes (5): Sitepulse_Remote_Management, RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response(), wp_schedule_single_event()

### Community 51 - "Ze"
Cohesion: 0.11
Nodes (3): Ue(), Wi, Ze

### Community 52 - "WP_Error"
Cohesion: 0.19
Nodes (9): sitepulse_check_ai_diagnostic_status_api(), sitepulse_collect_plugin_data(), sitepulse_dismiss_onboarding(), sitepulse_get_curl_stats(), sitepulse_get_plugin_profiler_stats(), sitepulse_get_profiler_stats(), sitepulse_reset_onboarding(), WP_Error (+1 more)

### Community 53 - "Sitepulse_AI_Diagnostic_Cron"
Cohesion: 0.12
Nodes (3): Sitepulse_AI_Diagnostic_Cron, wp_clear_scheduled_hook(), wp_unschedule_event()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 55 - "Sitepulse_Remote_Updates"
Cohesion: 0.05
Nodes (11): Sitepulse_Remote_Events, Sitepulse_Remote_Updates, RemoteEventsTest, RemoteUpdatesTest, Sitepulse_Test_Local_Filesystem, Automatic_Upgrader_Skin, do_action(), get_file_data() (+3 more)

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Test_Case"
Cohesion: 0.15
Nodes (3): DirectAccessTemplateTest, EasyModeSaveQueriesTest, Sitepulse_Test_Case

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
Nodes (5): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n(), sitepulse_test_post_type()

### Community 83 - "plugin_basename"
Cohesion: 0.09
Nodes (15): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), gethostbyname(), gethostname(), register_activation_hook() (+7 more)

### Community 86 - ".time"
Cohesion: 0.07
Nodes (12): Sitepulse_Cron_Fallback, Sitepulse_Cron_Manager, CronCoverageTest, CronTest, human_time_diff(), get_num_queries(), get_transient(), has_action() (+4 more)

### Community 90 - "popperGenerator"
Cohesion: 0.17
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "detectOverflow"
Cohesion: 0.30
Nodes (12): detectOverflow(), expandToHashMap(), getClientRectFromMixedType(), getClippingRect(), getDocumentElement(), getDocumentRect(), getInnerBoundingClientRect(), getViewportRect() (+4 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.07
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

### Community 105 - "WP_REST_Request"
Cohesion: 0.10
Nodes (16): ArrayAccess, sitepulse_dismiss_getting_started(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events(), sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report() (+8 more)

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
Cohesion: 0.08
Nodes (15): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), remove_all_actions(), submit_button() (+7 more)

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 153 - "Sitepulse_Remote_Activity"
Cohesion: 0.05
Nodes (28): Sitepulse_Remote_Activity, API → site, Backup commands, Backup preset format, Backup warnings, Commands, Dashboard (portal) endpoints, Endpoints (+20 more)

### Community 155 - "ps"
Cohesion: 0.08
Nodes (4): d(), ps, qt, remove()

### Community 158 - "Sitepulse_Plugin_Profiler"
Cohesion: 0.06
Nodes (4): Sitepulse_Plugin_Profiler, CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataRobustnessTest

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 164 - "RestApiTest"
Cohesion: 0.10
Nodes (7): sitepulse_check_vulnerabilities_api(), sitepulse_complete_onboarding(), sitepulse_get_memory_info(), sitepulse_request_ai_diagnostic_api(), sitepulse_rest_pre_dispatch(), sitepulse_update_settings(), RestApiTest

### Community 166 - "Sitepulse_Monitoring"
Cohesion: 0.09
Nodes (15): Sitepulse_Monitoring, MonitoringTest, add_query_arg(), admin_url(), esc_js(), esc_url_raw(), is_admin(), is_admin_bar_showing() (+7 more)

### Community 168 - "Sitepulse_Test_Upgrades"
Cohesion: 0.22
Nodes (5): Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader

### Community 171 - "DiagnosticTemplateCoverageTest"
Cohesion: 0.05
Nodes (10): Timeline, PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), CoverageGateTest (+2 more)

### Community 172 - "RestCoverageTest"
Cohesion: 0.19
Nodes (3): sitepulse_check_memory(), sitepulse_enable_save_queries(), RestCoverageTest

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "update_option"
Cohesion: 0.06
Nodes (8): Sitepulse_Api_Service, ApiCoverageTest, ApiRegressionTest, ApiServiceTest, StoredDataWritePathsTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), update_option()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 182 - "is_wp_error"
Cohesion: 0.22
Nodes (8): is_wp_error(), wp_generate_password(), wp_json_encode(), wp_rand(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 183 - "wp_normalize_path"
Cohesion: 0.15
Nodes (3): wp_debug_backtrace_summary(), get_template(), wp_normalize_path()

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "get_option"
Cohesion: 0.06
Nodes (7): Sitepulse_Product_Analytics, Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, absint(), get_option(), sanitize_key(), wp_parse_args()

### Community 199 - "delete_transient"
Cohesion: 0.16
Nodes (4): delete_transient(), remove_query_arg(), untrailingslashit(), wp_parse_url()

### Community 202 - "B"
Cohesion: 0.07
Nodes (4): B, mo, U, x()

### Community 211 - "Sitepulse_Remote_Health"
Cohesion: 0.18
Nodes (3): Sitepulse_Remote_Health, wc_get_page_permalink(), maybe_unserialize()

### Community 219 - "Sitepulse_Onboarding"
Cohesion: 0.06
Nodes (7): Sitepulse_Onboarding, Architecture, Sitepulse_Loader, SetupOnboardingTest, delete_metadata(), delete_option(), get_current_screen()

### Community 221 - "Sitepulse_Test_Backups"
Cohesion: 0.05
Nodes (15): Commands, Repo workflow, Testing notes, BackWPup, BackWPup_Cron, BackWPup_JobTypes, BackWPup_Option, Sitepulse_Test_Backups (+7 more)

## Knowledge Gaps
- **261 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+256 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1285 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **117 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does ``POST /api/website/remote/inventory`` connect `Sitepulse_Remote_Activity` to `page-analysis.js`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `AutoloadRestApiTest`, `current_user_can`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Remote_Backups`, `Sitepulse_Plugin_Profiler`, `LifecycleTest`, `RemoteBackupCommandsTest`, `RestApiTest`, `Sitepulse_Monitoring`, `TemplateTest`, `DiagnosticTemplateCoverageTest`, `RestCoverageTest`, `update_option`, `Sitepulse_Setup`, `Sitepulse_Frontend`, `Sitepulse_Remote_Client`, `Sitepulse_Remote_Management`, `CoreVariantsTest`, `ApiVariantsTest`, `HttpOriginTest`, `Sitepulse_Remote_Updates`, `OptionalViewIntegrationTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Plugin`, `Sitepulse_Profiler`, `get_option`, `TemplateRenderingTest`, `RecentDashboardTest`, `Sitepulse_Backend`, `Sitepulse_Page_Analysis`, `RemoteInventoryTest`, `.time`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `RemoteVariantsTest`, `Sitepulse_Onboarding`, `Sitepulse_Settings`, `PluginTest`, `Sitepulse_Page_Tracker`, `PopulatedTemplateTest`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Test_Database`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `Sitepulse_Remote_Backups`, `WordPress.php`, `Sitepulse_Plugin_Profiler`, `RestApiTest`, `RemoteBackupCommandsTest`, `Sitepulse_Monitoring`, `add_action`, `RestCoverageTest`, `Sitepulse_Frontend`, `Sitepulse_Setup`, `Sitepulse_Remote_Management`, `WP_Error`, `Sitepulse_AI_Diagnostic_Cron`, `is_wp_error`, `Sitepulse_Remote_Updates`, `Sitepulse_Profiler`, `get_option`, `delete_transient`, `TemplateRenderingTest`, `Sitepulse_Backend`, `Sitepulse_Page_Analysis`, `plugin_basename`, `.time`, `Sitepulse_Utils`, `ProfilerCompatibilityTest`, `Sitepulse_Onboarding`, `Sitepulse_Test_Backups`, `Sitepulse_Settings`, `PopulatedTemplateTest`, `WP_REST_Request`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Are the 204 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 204 INFERRED edges - model-reasoned connections that need verification._
- **Are the 154 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 154 INFERRED edges - model-reasoned connections that need verification._
- **Are the 42 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 42 INFERRED edges - model-reasoned connections that need verification._
- **Are the 57 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 57 INFERRED edges - model-reasoned connections that need verification._
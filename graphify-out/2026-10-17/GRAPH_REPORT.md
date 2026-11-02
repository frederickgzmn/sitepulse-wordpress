# Graph Report - sitepulse  (2026-10-17)

## Corpus Check
- 184 files · ~377,673 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4539 nodes · 10528 edges · 232 communities (104 shown, 128 thin omitted)
- Extraction: 76% EXTRACTED · 24% INFERRED · 0% AMBIGUOUS · INFERRED: 2498 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c5fb6f74`
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
- Sitepulse_Api_Service
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
- WP_REST_Response
- Sitepulse_Test_Backups
- SitePulse
- Tooltip
- Sitepulse_Remote_Backups
- Toast
- remove
- Qi
- Carousel
- ._queueCallback
- Carousel
- lt
- Carousel
- Backdrop
- RemoteBackupCommandsTest
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
- Sitepulse_Setup
- RemoteManagementTest
- contains
- .admin_notice_onboarding
- remove
- detectOverflow
- home_url
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
- Sitepulse_Remote_Management
- Sitepulse_Profiler
- blueprint.json
- Sitepulse_Remote_Client
- BaseComponent
- AutoloadRestApiTest
- Dropdown
- remove
- BaseComponent
- Toast
- ScrollSpy
- Modal
- RemoteInventoryTest
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- api-variants.php
- Ss
- ScrollSpy
- Sitepulse_Monitoring
- Sitepulse_Utils
- ts
- Swipe
- addHandler
- get_option
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
- sitepulse-dev
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
- Ti
- SitePulse — See What's Powering (or Slowing) Your Site
- delete_option
- Sitepulse_Easy_Mode
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
- Sitepulse_Plugin
- update_option
- browser
- backend.test.cjs
- Ln
- LifecycleTest
- WP_REST_Request
- WP_Error
- popperGenerator
- coverage.cjs
- browser.cjs
- get_transient
- Modal
- DiagnosticTemplateCoverageTest
- getBoundingClientRect
- js-coverage.cjs
- ApiServiceTest
- Me
- Automated tests
- CoverageGateTest
- Sitepulse_Cron_Manager
- CoreVariantsTest
- ApiVariantsTest
- Swipe
- WordPress.php
- set_transient
- Sitepulse_Test_Case
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- RemoteClientTest
- Config
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- _get_cron_array
- RemoteApiMockTest
- Dashboard (portal) endpoints
- ActionSchedulerTest
- B
- Sitepulse_Remote_Inventory
- TemplateRenderingTest
- RecentDashboardTest
- Sitepulse_Remote_Health
- plugin_basename
- AI Diagnostics
- ProfilerCompatibilityTest
- sitepulse_test_subprocess
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- current_user_can
- is_wp_error
- RemoteManagement.php
- Installation
- ot
- Sitepulse_Test_Database
- Fi
- TemplateTest
- pi
- BackWPup_Job
- Architecture
- Remote management
- Sitepulse_Onboarding

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 226 edges
2. `get_option()` - 180 edges
3. `Sitepulse_Test_WP` - 133 edges
4. `Sitepulse_Remote_Management` - 116 edges
5. `Sitepulse_Api_Service` - 100 edges
6. `Sitepulse_Profiler` - 74 edges
7. `Sitepulse_Remote_Backups` - 70 edges
8. `Sitepulse_Test_Case` - 69 edges
9. `Sitepulse_Page_Analysis` - 67 edges
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

## Communities (232 total, 128 thin omitted)

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

### Community 11 - "Sitepulse_Api_Service"
Cohesion: 0.08
Nodes (4): Sitepulse_Api_Service, ApiCoverageTest, untrailingslashit(), wp_parse_url()

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
Nodes (23): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), checkVulnerabilities(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike() (+15 more)

### Community 23 - "Sitepulse_Test_Backups"
Cohesion: 0.10
Nodes (4): BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, UpdraftPlus

### Community 24 - "SitePulse"
Cohesion: 0.10
Nodes (20): Admin Bar Widget, Basic View, Core Web Vitals, Cron Job Management, Developer View, Email Blocking, Fatal-Error Detection, Health Score System (+12 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.06
Nodes (3): Sitepulse_Remote_Backups, RemoteBackupsTest, UpdraftPlus_Options

### Community 35 - "Backdrop"
Cohesion: 0.13
Nodes (6): Backdrop, execute(), executeAfterTransition(), getTransitionDurationFromElement(), Swipe, triggerTransitionEnd()

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
Cohesion: 0.15
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 49 - "contains"
Cohesion: 0.18
Nodes (3): contains(), effect$1(), Tab

### Community 50 - ".admin_notice_onboarding"
Cohesion: 0.16
Nodes (9): sitepulse_sanitize_param(), submit_button(), wp_nonce_field(), absint(), esc_attr(), esc_html(), esc_html_e(), esc_url() (+1 more)

### Community 51 - "remove"
Cohesion: 0.17
Nodes (3): Ue(), remove(), Ze

### Community 52 - "detectOverflow"
Cohesion: 0.15
Nodes (23): arrow(), computeAutoPlacement(), computeOffsets(), computeStyles(), detectOverflow(), expandToHashMap(), flip(), getAltAxis() (+15 more)

### Community 53 - "home_url"
Cohesion: 0.13
Nodes (17): get_permalink(), add_query_arg(), admin_url(), esc_js(), esc_url_raw(), get_post_types(), get_posts(), home_url() (+9 more)

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 55 - "Sitepulse_Remote_Updates"
Cohesion: 0.05
Nodes (10): Sitepulse_Remote_Events, Sitepulse_Remote_Updates, RemoteEventsTest, RemoteUpdatesTest, Sitepulse_Test_Local_Filesystem, do_action(), get_file_data(), wp_clear_scheduled_hook() (+2 more)

### Community 62 - "Sitepulse_Backend"
Cohesion: 0.13
Nodes (3): Sitepulse_Backend, BackendCoverageTest, has_filter()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Remote_Management"
Cohesion: 0.07
Nodes (3): Sitepulse_Remote_Management, wp_generate_uuid4(), wp_schedule_single_event()

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.06
Nodes (3): Sitepulse_Profiler, ProfilerTest, RecentActivityTest

### Community 68 - "blueprint.json"
Cohesion: 0.12
Nodes (16): Automated Test Setup, WordPress Playground, features, networking, landingPage, meta, author, categories (+8 more)

### Community 70 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 71 - "AutoloadRestApiTest"
Cohesion: 0.19
Nodes (5): sitepulse_get_autoload_options(), sitepulse_get_protected_options(), sitepulse_update_autoload_option(), AutoloadRestApiTest, wp_cache_delete()

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
Nodes (4): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n()

### Community 83 - "api-variants.php"
Cohesion: 0.22
Nodes (5): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes()

### Community 86 - "Sitepulse_Monitoring"
Cohesion: 0.13
Nodes (5): Sitepulse_Monitoring, MonitoringTest, is_admin(), wp_doing_ajax(), wp_doing_cron()

### Community 90 - "addHandler"
Cohesion: 0.16
Nodes (17): addHandler(), bootstrapDelegationHandler(), bootstrapHandler(), find(), findHandler(), focusableChildren(), getElementEvents(), getTypeEvent() (+9 more)

### Community 91 - "get_option"
Cohesion: 0.06
Nodes (5): Sitepulse_Product_Analytics, Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, get_option(), wp_parse_args()

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.12
Nodes (4): PetAssistantTemplateTest, PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "sitepulse-dev"
Cohesion: 0.41
Nodes (13): sitepulse-dev script, cmd_start(), cmd_status(), current_target(), die(), info(), install_site(), others() (+5 more)

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
Cohesion: 0.18
Nodes (5): sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 138 - "Sitepulse_CurLoader"
Cohesion: 0.11
Nodes (3): Sitepulse_CurLoader, CurLoaderTest, wp_debug_backtrace_summary()

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 142 - "delete_option"
Cohesion: 0.18
Nodes (3): RemoteConnectionTest, sitepulse_test_remote_endpoint(), delete_option()

### Community 143 - "Sitepulse_Easy_Mode"
Cohesion: 0.09
Nodes (9): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, EasyModeTest, GettingStartedTest, get_current_user_id(), get_user_meta(), is_user_logged_in(), metadata_exists() (+1 more)

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
Nodes (12): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), human_time_diff(), load_textdomain(), _n(), remove_all_actions() (+4 more)

### Community 151 - "add_action"
Cohesion: 0.14
Nodes (4): add_action(), add_filter(), apply_filters(), Sitepulse_Test_Hook

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 158 - "update_option"
Cohesion: 0.06
Nodes (8): Sitepulse_Plugin_Profiler, sitepulse_collect_plugin_data(), sitepulse_get_plugin_profiler_stats(), CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataRobustnessTest, StoredDataWritePathsTest, update_option()

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "WP_REST_Request"
Cohesion: 0.07
Nodes (26): ArrayAccess, sitepulse_check_memory(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_enable_save_queries(), sitepulse_page_analysis_collect() (+18 more)

### Community 164 - "WP_Error"
Cohesion: 0.04
Nodes (12): Sitepulse_AI_Diagnostic_Cron, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_vulnerabilities_api(), sitepulse_enable_clear_curl_api_events(), sitepulse_get_curl_stats(), sitepulse_get_memory_info(), sitepulse_get_profiler_stats(), sitepulse_request_ai_diagnostic_api() (+4 more)

### Community 165 - "popperGenerator"
Cohesion: 0.15
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 168 - "get_transient"
Cohesion: 0.12
Nodes (5): Sitepulse_Cron_Fallback, CronCoverageTest, CronTest, get_transient(), has_action()

### Community 172 - "getBoundingClientRect"
Cohesion: 0.26
Nodes (15): getBoundingClientRect(), getClientRectFromMixedType(), getCompositeRect(), getDocumentElement(), getDocumentRect(), getHTMLElementScroll(), getInnerBoundingClientRect(), getNodeScroll() (+7 more)

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "ApiServiceTest"
Cohesion: 0.08
Nodes (3): ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 177 - "CoverageGateTest"
Cohesion: 0.21
Nodes (3): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), CoverageGateTest

### Community 178 - "Sitepulse_Cron_Manager"
Cohesion: 0.15
Nodes (4): Sitepulse_Cron_Manager, wp_get_scheduled_event(), wp_get_schedules(), wp_schedule_event()

### Community 182 - "WordPress.php"
Cohesion: 0.05
Nodes (44): sitepulse_register_route(), get_the_title(), sitepulse_test_site_pages(), add_menu_page(), add_option(), add_submenu_page(), delete_metadata(), delete_transient() (+36 more)

### Community 183 - "set_transient"
Cohesion: 0.08
Nodes (4): Sitepulse_Page_Tracker, PageTrackerTest, set_transient(), sitepulse_test_post_type()

### Community 184 - "Sitepulse_Test_Case"
Cohesion: 0.12
Nodes (4): CoreRegressionTest, EasyModeSaveQueriesTest, HttpOriginTest, Sitepulse_Test_Case

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.14
Nodes (15): Backup warnings, Connecting a site to an account, Dashboard (portal) endpoints, Development, Key rotation, Monthly reports, Notifications, Roles (+7 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 203 - "Sitepulse_Remote_Inventory"
Cohesion: 0.06
Nodes (21): Sitepulse_Remote_Inventory, Backup commands, Backup preset format, Commands, `POST /api/website/remote/events`, `POST /api/website/remote/inventory`, Update commands, disk_free_space() (+13 more)

### Community 206 - "Sitepulse_Remote_Health"
Cohesion: 0.12
Nodes (3): Sitepulse_Remote_Health, maybe_unserialize(), wp_normalize_path()

### Community 209 - "plugin_basename"
Cohesion: 0.15
Nodes (9): inc/ helpers, sitepulse_enable_clear_load_events(), sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), register_activation_hook(), register_deactivation_hook() (+1 more)

### Community 212 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 215 - "sitepulse_test_subprocess"
Cohesion: 0.17
Nodes (3): DirectAccessTemplateTest, TestCase, sitepulse_test_subprocess()

### Community 216 - "Sitepulse_Test_Filesystem"
Cohesion: 0.13
Nodes (4): Sitepulse_Test_Filesystem, wp_delete_file(), WP_Filesystem(), wp_upload_dir()

### Community 219 - "current_user_can"
Cohesion: 0.13
Nodes (16): RuntimeException, check_admin_referer(), check_ajax_referer(), current_user_can(), sanitize_email(), sanitize_key(), sanitize_text_field(), Sitepulse_Test_Die (+8 more)

### Community 220 - "is_wp_error"
Cohesion: 0.33
Nodes (6): is_wp_error(), wp_json_encode(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 221 - "RemoteManagement.php"
Cohesion: 0.13
Nodes (10): Automatic_Upgrader_Skin, BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader (+2 more)

### Community 224 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

### Community 233 - "BackWPup_Job"
Cohesion: 0.10
Nodes (4): BackWPup, BackWPup_Cron, BackWPup_Job, trailingslashit()

### Community 234 - "Architecture"
Cohesion: 0.21
Nodes (5): Architecture, Commands, Repo workflow, Testing notes, Sitepulse_Loader

### Community 235 - "Remote management"
Cohesion: 0.15
Nodes (12): API → site, Endpoints, Phases, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/key`, Remote management, Retention (+4 more)

### Community 238 - "Sitepulse_Onboarding"
Cohesion: 0.10
Nodes (4): Sitepulse_Onboarding, HarnessIsolationTest, SetupOnboardingTest, get_current_screen()

## Knowledge Gaps
- **262 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+257 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1297 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **128 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Api_Service`, `delete_option`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Remote_Backups`, `Sitepulse_Plugin`, `update_option`, `LifecycleTest`, `WP_REST_Request`, `WP_Error`, `RemoteBackupCommandsTest`, `get_transient`, `DiagnosticTemplateCoverageTest`, `ApiServiceTest`, `Sitepulse_Frontend`, `Sitepulse_Setup`, `RemoteManagementTest`, `CoverageGateTest`, `CoreVariantsTest`, `ApiVariantsTest`, `set_transient`, `Sitepulse_Remote_Updates`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Backend`, `Sitepulse_Profiler`, `Sitepulse_Remote_Client`, `AutoloadRestApiTest`, `RemoteApiMockTest`, `ActionSchedulerTest`, `TemplateRenderingTest`, `RecentDashboardTest`, `RemoteInventoryTest`, `Sitepulse_Page_Analysis`, `Sitepulse_Monitoring`, `sitepulse_test_subprocess`, `ProfilerCompatibilityTest`, `RemoteVariantsTest`, `Sitepulse_Utils`, `get_option`, `current_user_can`, `Sitepulse_Settings`, `PluginTest`, `PopulatedTemplateTest`, `TemplateTest`, `Sitepulse_Onboarding`?**
  _High betweenness centrality (0.047) - this node is a cross-community bridge._
- **Why does ``POST /api/website/remote/inventory`` connect `Sitepulse_Remote_Inventory` to `Remote management`, `page-analysis.js`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Api_Service`, `delete_option`, `Sitepulse_Easy_Mode`, `add_action`, `Sitepulse_Test_Backups`, `Sitepulse_Remote_Activity`, `Sitepulse_Remote_Backups`, `WP_REST_Request`, `WP_Error`, `RemoteBackupCommandsTest`, `get_transient`, `ApiServiceTest`, `Sitepulse_Setup`, `Sitepulse_Frontend`, `RemoteManagementTest`, `Sitepulse_Cron_Manager`, `home_url`, `WordPress.php`, `Sitepulse_Remote_Updates`, `set_transient`, `Sitepulse_Backend`, `Sitepulse_Remote_Management`, `Sitepulse_Profiler`, `Sitepulse_Remote_Client`, `RemoteApiMockTest`, `TemplateRenderingTest`, `plugin_basename`, `Sitepulse_Page_Analysis`, `Sitepulse_Monitoring`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `current_user_can`, `get_option`, `is_wp_error`, `Sitepulse_Settings`, `Sitepulse_Test_Database`, `sitepulse_test_remote_response`, `PopulatedTemplateTest`, `BackWPup_Job`, `Architecture`, `Sitepulse_Onboarding`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **Are the 222 inferred relationships involving `update_option()` (e.g. with `.make_api_request()` and `.make_vulnerability_api_request()`) actually correct?**
  _`update_option()` has 222 INFERRED edges - model-reasoned connections that need verification._
- **Are the 175 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 175 INFERRED edges - model-reasoned connections that need verification._
- **Are the 45 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 45 INFERRED edges - model-reasoned connections that need verification._
- **Are the 73 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 73 INFERRED edges - model-reasoned connections that need verification._
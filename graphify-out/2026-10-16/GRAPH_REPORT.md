# Graph Report - sitepulse  (2026-10-16)

## Corpus Check
- 182 files · ~372,177 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4493 nodes · 10348 edges · 237 communities (104 shown, 133 thin omitted)
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 2416 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d11ef89e`
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
- Fi
- bootstrap.bundle.js
- Tooltip
- backend.js
- .hide
- TemplateFactory
- ._queueCallback
- WP_REST_Response
- current_user_can
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
- has_action
- contains
- home_url
- remove
- detectOverflow
- admin_url
- BaseComponent
- RemoteUpdatesTest
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
- Sitepulse_Remote_Management
- Sitepulse_Profiler
- blueprint.json
- RestCoverageTest
- BaseComponent
- AutoloadRestApiTest
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
- get_transient
- Sitepulse_Utils
- ts
- Swipe
- addHandler
- Sitepulse_Whitelabel_Service
- Qn
- Offcanvas
- Toast
- Backdrop
- Sitepulse_Settings
- Zi
- Ce
- Es
- PluginTest
- RemoteManagementTest
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
- Sitepulse_Remote_Inventory
- SitePulse — See What's Powering (or Slowing) Your Site
- Sitepulse_Monitoring
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
- Sitepulse_Backend
- update_option
- browser
- backend.test.cjs
- Ln
- LifecycleTest
- WP_REST_Request
- get_option
- popperGenerator
- coverage.cjs
- browser.cjs
- Sitepulse_Cron_Fallback
- Modal
- DiagnosticTemplateCoverageTest
- getDocumentElement
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
- PageTrackerTest
- Sitepulse_Test_Case
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- RemoteClientTest
- FocusTrap
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- WP_Error
- Dashboard (portal) endpoints
- do_action
- B
- .set_auto_updates
- TemplateRenderingTest
- RecentDashboardTest
- Sitepulse_Remote_Health
- wp_normalize_path
- Sitepulse_Remote_Events
- Sitepulse_Page_Tracker
- AI Diagnostics
- Sitepulse_Remote_Updates
- ProfilerCompatibilityTest
- sitepulse_test_subprocess
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- StoredDataRobustnessTest
- wp_unslash
- is_wp_error
- RemoteManagement.php
- Installation
- ot
- Sitepulse_Test_Database
- Wi
- .track_plugins
- Sitepulse_Test_Local_Filesystem
- TemplateTest
- Ie
- wp_clear_scheduled_hook
- Architecture
- Remote management
- HttpOriginTest

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 217 edges
2. `get_option()` - 166 edges
3. `Sitepulse_Test_WP` - 132 edges
4. `Sitepulse_Remote_Management` - 104 edges
5. `Sitepulse_Api_Service` - 87 edges
6. `Sitepulse_Profiler` - 74 edges
7. `Sitepulse_Remote_Backups` - 70 edges
8. `Sitepulse_Test_Case` - 68 edges
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

## Communities (237 total, 133 thin omitted)

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

### Community 11 - "Sitepulse_Api_Service"
Cohesion: 0.09
Nodes (3): Sitepulse_Api_Service, ApiCoverageTest, ApiRegressionTest

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

### Community 23 - "current_user_can"
Cohesion: 0.11
Nodes (16): sitepulse_register_route(), RuntimeException, check_admin_referer(), check_ajax_referer(), current_user_can(), is_admin(), register_rest_route(), Sitepulse_Test_Die (+8 more)

### Community 24 - "SitePulse"
Cohesion: 0.10
Nodes (20): Admin Bar Widget, Basic View, Core Web Vitals, Cron Job Management, Developer View, Email Blocking, Fatal-Error Detection, Health Score System (+12 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.03
Nodes (11): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup, BackWPup_Cron, BackWPup_Job, BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination (+3 more)

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
Cohesion: 0.16
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Onboarding"
Cohesion: 0.06
Nodes (6): Sitepulse_Onboarding, Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest, delete_transient()

### Community 48 - "has_action"
Cohesion: 0.17
Nodes (3): CronTest, has_action(), wp_get_scheduled_event()

### Community 49 - "contains"
Cohesion: 0.18
Nodes (3): contains(), effect$1(), Tab

### Community 50 - "home_url"
Cohesion: 0.13
Nodes (7): gethostbyname(), gethostname(), get_bloginfo(), home_url(), remove_query_arg(), untrailingslashit(), wp_parse_url()

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 52 - "detectOverflow"
Cohesion: 0.17
Nodes (21): arrow(), computeAutoPlacement(), computeOffsets(), computeStyles(), detectOverflow(), expandToHashMap(), flip(), getAltAxis() (+13 more)

### Community 53 - "admin_url"
Cohesion: 0.09
Nodes (16): HarnessIsolationTest, remove_all_actions(), add_query_arg(), admin_url(), esc_js(), esc_url(), esc_url_raw(), get_current_screen() (+8 more)

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Plugin"
Cohesion: 0.10
Nodes (5): Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest, has_filter(), sitepulse_test_callback_id()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Remote_Management"
Cohesion: 0.06
Nodes (5): Sitepulse_Remote_Management, human_time_diff(), site_url(), wp_generate_uuid4(), wp_schedule_single_event()

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
Cohesion: 0.20
Nodes (4): sitepulse_get_autoload_options(), sitepulse_get_protected_options(), sitepulse_update_autoload_option(), AutoloadRestApiTest

### Community 73 - "remove"
Cohesion: 0.07
Nodes (4): an, es, remove(), W

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 78 - "Sitepulse_Remote_Client"
Cohesion: 0.09
Nodes (3): Sitepulse_Remote_Client, ApiBaseTest, RemoteApiMockTest

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.07
Nodes (4): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n()

### Community 83 - "plugin_basename"
Cohesion: 0.12
Nodes (7): register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes(), register_activation_hook(), register_deactivation_hook(), get_plugin_data(), plugin_basename()

### Community 90 - "addHandler"
Cohesion: 0.16
Nodes (17): addHandler(), bootstrapDelegationHandler(), bootstrapHandler(), find(), findHandler(), focusableChildren(), getElementEvents(), getTypeEvent() (+9 more)

### Community 91 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.08
Nodes (4): Sitepulse_Product_Analytics, Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, wp_parse_args()

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.07
Nodes (6): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters(), _get_cron_array()

### Community 101 - "RemoteManagementTest"
Cohesion: 0.19
Nodes (3): RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response()

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

### Community 139 - "Sitepulse_Remote_Inventory"
Cohesion: 0.09
Nodes (7): Sitepulse_Remote_Inventory, disk_free_space(), RemoteInventoryTest, get_core_updates(), get_filesystem_method(), get_site_transient(), wp_is_file_mod_allowed()

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 142 - "Sitepulse_Monitoring"
Cohesion: 0.10
Nodes (4): Sitepulse_Monitoring, Sitepulse_Loader, MonitoringTest, delete_option()

### Community 143 - "Sitepulse_Easy_Mode"
Cohesion: 0.08
Nodes (13): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, sitepulse_set_sp_profiler(), sitepulse_set_trackers_disabled_notice(), sitepulse_set_wpslowhttp(), EasyModeTest, GettingStartedTest, get_current_user_id() (+5 more)

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
Nodes (16): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), submit_button(), wp_date() (+8 more)

### Community 151 - "add_action"
Cohesion: 0.12
Nodes (3): add_action(), add_filter(), Sitepulse_Test_Hook

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 158 - "update_option"
Cohesion: 0.06
Nodes (5): Sitepulse_Plugin_Profiler, CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataWritePathsTest, update_option()

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "WP_REST_Request"
Cohesion: 0.09
Nodes (16): ArrayAccess, sitepulse_dismiss_getting_started(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events(), sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report() (+8 more)

### Community 164 - "get_option"
Cohesion: 0.06
Nodes (8): inc/ helpers, Sitepulse_AI_Diagnostic_Cron, sitepulse_check_vulnerabilities_api(), sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), RestApiTest, get_option()

### Community 165 - "popperGenerator"
Cohesion: 0.15
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 172 - "getDocumentElement"
Cohesion: 0.27
Nodes (11): getClientRectFromMixedType(), getClippingRect(), getDocumentElement(), getDocumentRect(), getInnerBoundingClientRect(), getUAString(), getViewportRect(), getWindowScroll() (+3 more)

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "ApiServiceTest"
Cohesion: 0.09
Nodes (3): ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 177 - "CoverageGateTest"
Cohesion: 0.21
Nodes (3): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), CoverageGateTest

### Community 178 - "Sitepulse_Cron_Manager"
Cohesion: 0.15
Nodes (3): Sitepulse_Cron_Manager, wp_get_schedules(), wp_schedule_event()

### Community 182 - "WordPress.php"
Cohesion: 0.06
Nodes (36): sitepulse_reorder_active_plugins_for_conflict(), get_permalink(), get_the_title(), add_menu_page(), add_option(), add_submenu_page(), delete_metadata(), delete_user_meta() (+28 more)

### Community 184 - "Sitepulse_Test_Case"
Cohesion: 0.15
Nodes (3): DirectAccessTemplateTest, EasyModeSaveQueriesTest, Sitepulse_Test_Case

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "WP_Error"
Cohesion: 0.19
Nodes (16): sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_get_curl_stats(), sitepulse_get_memory_info() (+8 more)

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.15
Nodes (13): Backup warnings, Dashboard (portal) endpoints, Development, Key rotation, Monthly reports, Notifications, Roles, Rollouts (+5 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 203 - ".set_auto_updates"
Cohesion: 0.08
Nodes (16): Backup commands, Backup preset format, Commands, Phases, `POST /api/website/remote/events`, `POST /api/website/remote/inventory`, Update policy, doing_action() (+8 more)

### Community 206 - "Sitepulse_Remote_Health"
Cohesion: 0.17
Nodes (3): Sitepulse_Remote_Health, Update commands, maybe_unserialize()

### Community 212 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 213 - "Sitepulse_Remote_Updates"
Cohesion: 0.11
Nodes (5): Sitepulse_Remote_Updates, get_theme_root(), remove_action(), remove_filter(), wp_doing_cron()

### Community 216 - "Sitepulse_Test_Filesystem"
Cohesion: 0.13
Nodes (4): Sitepulse_Test_Filesystem, wp_delete_file(), WP_Filesystem(), wp_upload_dir()

### Community 219 - "wp_unslash"
Cohesion: 0.25
Nodes (4): sitepulse_sanitize_param(), absint(), sanitize_key(), wp_unslash()

### Community 220 - "is_wp_error"
Cohesion: 0.24
Nodes (8): is_wp_error(), wp_generate_password(), wp_json_encode(), wp_rand(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 221 - "RemoteManagement.php"
Cohesion: 0.11
Nodes (11): Automatic_Upgrader_Skin, BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, sitepulse_test_site_pages(), Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades (+3 more)

### Community 224 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

### Community 234 - "Architecture"
Cohesion: 0.29
Nodes (4): Architecture, Commands, Repo workflow, Testing notes

### Community 235 - "Remote management"
Cohesion: 0.17
Nodes (11): API → site, Endpoints, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/key`, Remote management, Retention, Signing (+3 more)

## Knowledge Gaps
- **262 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+257 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1296 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **133 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does ``POST /api/website/remote/inventory`` connect `.set_auto_updates` to `Remote management`, `page-analysis.js`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._
- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Api_Service`, `Sitepulse_Remote_Inventory`, `Sitepulse_Monitoring`, `Sitepulse_Easy_Mode`, `current_user_can`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Remote_Backups`, `update_option`, `LifecycleTest`, `RemoteBackupCommandsTest`, `get_option`, `Sitepulse_Cron_Fallback`, `DiagnosticTemplateCoverageTest`, `ApiServiceTest`, `Sitepulse_Onboarding`, `has_action`, `Sitepulse_Frontend`, `CoverageGateTest`, `CoreVariantsTest`, `ApiVariantsTest`, `admin_url`, `PageTrackerTest`, `RemoteUpdatesTest`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Plugin`, `Sitepulse_Profiler`, `RestCoverageTest`, `AutoloadRestApiTest`, `do_action`, `TemplateRenderingTest`, `RecentDashboardTest`, `Sitepulse_Remote_Client`, `Sitepulse_Page_Analysis`, `Sitepulse_Page_Tracker`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `RemoteVariantsTest`, `StoredDataRobustnessTest`, `Sitepulse_Whitelabel_Service`, `Sitepulse_Settings`, `PluginTest`, `RemoteManagementTest`, `PopulatedTemplateTest`, `TemplateTest`, `HttpOriginTest`?**
  _High betweenness centrality (0.042) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Api_Service`, `Sitepulse_Monitoring`, `Sitepulse_Easy_Mode`, `add_action`, `Sitepulse_Remote_Activity`, `Sitepulse_Remote_Backups`, `get_option`, `RemoteBackupCommandsTest`, `ApiServiceTest`, `Sitepulse_Onboarding`, `has_action`, `Sitepulse_Frontend`, `home_url`, `admin_url`, `WordPress.php`, `PageTrackerTest`, `RemoteUpdatesTest`, `Sitepulse_Plugin`, `Sitepulse_Remote_Management`, `Sitepulse_Profiler`, `RestCoverageTest`, `WP_Error`, `.api`, `do_action`, `TemplateRenderingTest`, `Sitepulse_Remote_Client`, `Sitepulse_Page_Analysis`, `Sitepulse_Remote_Events`, `Sitepulse_Remote_Updates`, `get_transient`, `Sitepulse_Utils`, `ProfilerCompatibilityTest`, `StoredDataRobustnessTest`, `Sitepulse_Whitelabel_Service`, `is_wp_error`, `Sitepulse_Settings`, `Sitepulse_Test_Database`, `RemoteManagementTest`, `PopulatedTemplateTest`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Are the 213 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 213 INFERRED edges - model-reasoned connections that need verification._
- **Are the 161 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 161 INFERRED edges - model-reasoned connections that need verification._
- **Are the 44 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 44 INFERRED edges - model-reasoned connections that need verification._
- **Are the 65 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 65 INFERRED edges - model-reasoned connections that need verification._
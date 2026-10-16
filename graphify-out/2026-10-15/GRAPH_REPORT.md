# Graph Report - sitepulse  (2026-10-15)

## Corpus Check
- 180 files · ~367,178 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4451 nodes · 10207 edges · 228 communities (98 shown, 130 thin omitted)
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 2360 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `599caa04`
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
- .set_auto_updates
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
- remove
- WordPress.php
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
- Sitepulse_Onboarding
- Sitepulse_Remote_Inventory
- contains
- Sitepulse_Remote_Management
- remove
- WP_Error
- wp_clear_scheduled_hook
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
- Sitepulse_Test_Case
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
- VersionBumpTest
- Sitepulse_CurLoader
- Sitepulse_Test_Database
- SitePulse — See What's Powering (or Slowing) Your Site
- Collapse
- AutoloadRestApiTest
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- scripts
- PresentationWordPress.php
- Sitepulse_Remote_Updates
- onboarding.test.cjs
- Sitepulse_Remote_Activity
- BackendTest
- ps
- FocusTrap
- Sitepulse_Test_Local_Filesystem
- update_option
- browser
- backend.test.cjs
- Ln
- LifecycleTest
- browser.cjs
- RestApiTest
- PageAnalysisTest
- admin_url
- WP_REST_Response
- RemoteManagement.php
- Sitepulse_Remote_Events
- DiagnosticTemplateCoverageTest
- RestCoverageTest
- js-coverage.cjs
- get_option
- Me
- Automated tests
- CoverageGateTest
- coverage.cjs
- CoreVariantsTest
- ApiVariantsTest
- api_backend.php
- is_wp_error
- wp_normalize_path
- do_action
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- BackWPup_Job
- ot
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- sanitize_text_field
- wp_parse_url
- Dashboard (portal) endpoints
- B
- Wi
- TemplateRenderingTest
- RecentDashboardTest
- Sitepulse_Whitelabel_Service
- PetAssistantTemplateTest
- Ie
- home_url
- RemoteInventoryTest
- api-variants.php
- ProfilerCompatibilityTest
- sitepulse_test_subprocess
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- StoredDataRobustnessTest
- Sitepulse_Monitoring
- wp_get_theme
- Sitepulse_Test_Backups
- ActionScheduler_Store
- CLAUDE.md
- sitepulse_test_post_type

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 213 edges
2. `get_option()` - 164 edges
3. `Sitepulse_Test_WP` - 131 edges
4. `Sitepulse_Remote_Management` - 99 edges
5. `Sitepulse_Profiler` - 74 edges
6. `Sitepulse_Api_Service` - 73 edges
7. `Sitepulse_Remote_Backups` - 68 edges
8. `Sitepulse_Page_Analysis` - 67 edges
9. `Sitepulse_Test_Case` - 67 edges
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

## Communities (228 total, 130 thin omitted)

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
Nodes (10): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, wp_mail(), current_time(), get_bloginfo(), is_email() (+2 more)

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

### Community 11 - ".set_auto_updates"
Cohesion: 0.08
Nodes (21): API → site, Backup commands, Backup preset format, Commands, Development, Endpoints, Phases, `POST /api/website/remote/check-in` (+13 more)

### Community 13 - "BaseComponent"
Cohesion: 0.07
Nodes (4): Backdrop, BaseComponent, Button, Config

### Community 14 - "TemplateFactory"
Cohesion: 0.09
Nodes (5): Config, getElement(), isElement(), TemplateFactory, toType()

### Community 16 - "bootstrap.bundle.js"
Cohesion: 0.07
Nodes (26): distanceAndSkiddingToXY(), effect$1(), getDataAttribute(), getDataAttributes(), getFreshSideObject(), getSideOffsets(), hide(), isAnySideFullyClipped() (+18 more)

### Community 18 - "backend.js"
Cohesion: 0.09
Nodes (20): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike(), getScoreColor() (+12 more)

### Community 20 - "TemplateFactory"
Cohesion: 0.07
Nodes (4): Config, sanitizeHtml(), Swipe, TemplateFactory

### Community 22 - "current_user_can"
Cohesion: 0.32
Nodes (5): check_ajax_referer(), current_user_can(), Sitepulse_Test_Json_Response, wp_send_json_error(), wp_send_json_success()

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.08
Nodes (9): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, EasyModeTest, GettingStartedTest, get_current_user_id(), get_user_meta(), is_user_logged_in(), metadata_exists() (+1 more)

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.06
Nodes (3): Sitepulse_Remote_Backups, RemoteBackupsTest, UpdraftPlus_Options

### Community 27 - "remove"
Cohesion: 0.09
Nodes (3): Modal, remove(), Toast

### Community 28 - "WordPress.php"
Cohesion: 0.03
Nodes (47): RuntimeException, ApiCoverageTest, get_permalink(), get_the_title(), add_menu_page(), add_option(), add_submenu_page(), check_admin_referer() (+39 more)

### Community 35 - "Backdrop"
Cohesion: 0.13
Nodes (6): Backdrop, execute(), executeAfterTransition(), getTransitionDurationFromElement(), Swipe, triggerTransitionEnd()

### Community 37 - "getWindow"
Cohesion: 0.25
Nodes (16): applyStyles(), effect$2(), getComputedStyle$1(), getContainingBlock(), getNodeName(), getOffsetParent(), getParentNode(), getScrollParent() (+8 more)

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
Nodes (5): Sitepulse_Onboarding, Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest

### Community 48 - "Sitepulse_Remote_Inventory"
Cohesion: 0.11
Nodes (7): Sitepulse_Remote_Inventory, disk_free_space(), get_core_updates(), get_filesystem_method(), get_site_transient(), is_plugin_active(), wp_is_file_mod_allowed()

### Community 50 - "Sitepulse_Remote_Management"
Cohesion: 0.06
Nodes (9): Sitepulse_Remote_Client, Sitepulse_Remote_Management, RemoteApiMockTest, RemoteClientTest, RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response(), wp_generate_uuid4() (+1 more)

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 52 - "WP_Error"
Cohesion: 0.20
Nodes (13): sitepulse_check_ai_diagnostic_status_api(), sitepulse_collect_plugin_data(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_get_curl_stats(), sitepulse_get_plugin_profiler_stats(), sitepulse_get_profiler_stats(), sitepulse_request_ai_diagnostic_api() (+5 more)

### Community 53 - "wp_clear_scheduled_hook"
Cohesion: 0.22
Nodes (3): BackWPup_Cron, wp_clear_scheduled_hook(), wp_unschedule_event()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Plugin"
Cohesion: 0.10
Nodes (5): Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest, has_filter(), sitepulse_test_callback_id()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Test_Case"
Cohesion: 0.07
Nodes (6): ActionSchedulerTest, EasyModeSaveQueriesTest, HarnessIsolationTest, HttpOriginTest, Sitepulse_Test_Case, TemplateTest

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.05
Nodes (6): Sitepulse_Profiler, ProfilerTest, RecentActivityTest, add_action(), add_filter(), Sitepulse_Test_Hook

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
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 83 - "plugin_basename"
Cohesion: 0.17
Nodes (10): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), register_activation_hook(), register_deactivation_hook(), get_plugin_data() (+2 more)

### Community 86 - ".time"
Cohesion: 0.07
Nodes (12): Sitepulse_Cron_Fallback, Sitepulse_Cron_Manager, CronCoverageTest, CronTest, delete_transient(), get_num_queries(), get_transient(), has_action() (+4 more)

### Community 90 - "popperGenerator"
Cohesion: 0.15
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "detectOverflow"
Cohesion: 0.20
Nodes (21): detectOverflow(), expandToHashMap(), getBoundingClientRect(), getClientRectFromMixedType(), getClippingParents(), getClippingRect(), getCompositeRect(), getDocumentElement() (+13 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.10
Nodes (4): Sitepulse_Settings, SettingsTest, apply_filters(), _get_cron_array()

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.22
Nodes (3): PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "WP_REST_Request"
Cohesion: 0.10
Nodes (5): ArrayAccess, sitepulse_enable_clear_load_events(), sitepulse_pause_monitoring(), sitepulse_resume_monitoring(), WP_REST_Request

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
Cohesion: 0.17
Nodes (6): PHPUnit\Framework\TestCase, sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

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
Cohesion: 0.09
Nodes (12): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), human_time_diff(), load_textdomain(), _n(), remove_all_actions() (+4 more)

### Community 151 - "Sitepulse_Remote_Updates"
Cohesion: 0.13
Nodes (5): Sitepulse_Remote_Updates, get_theme_root(), remove_action(), remove_filter(), wp_doing_cron()

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 158 - "update_option"
Cohesion: 0.04
Nodes (7): Sitepulse_Plugin_Profiler, Sitepulse_AI_Diagnostic_Cron, sitepulse_check_vulnerabilities_api(), CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataWritePathsTest, update_option()

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
Cohesion: 0.09
Nodes (7): sitepulse_complete_onboarding(), sitepulse_enable_clear_curl_api_events(), sitepulse_get_memory_info(), sitepulse_rest_pre_dispatch(), sitepulse_set_wpslowhttp(), sitepulse_update_settings(), RestApiTest

### Community 166 - "admin_url"
Cohesion: 0.12
Nodes (15): submit_button(), wp_nonce_field(), admin_url(), esc_attr(), esc_html(), esc_html_e(), esc_js(), esc_url() (+7 more)

### Community 168 - "RemoteManagement.php"
Cohesion: 0.13
Nodes (9): Automatic_Upgrader_Skin, BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader (+1 more)

### Community 172 - "RestCoverageTest"
Cohesion: 0.21
Nodes (3): sitepulse_check_memory(), sitepulse_enable_save_queries(), RestCoverageTest

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "get_option"
Cohesion: 0.07
Nodes (7): Sitepulse_Api_Service, ApiRegressionTest, ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), get_option(), wp_parse_args()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 181 - "api_backend.php"
Cohesion: 0.25
Nodes (11): sitepulse_dismiss_getting_started(), sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report(), sitepulse_page_analysis_response(), sitepulse_page_analysis_sample(), sitepulse_page_analysis_start() (+3 more)

### Community 182 - "is_wp_error"
Cohesion: 0.22
Nodes (8): is_wp_error(), wp_generate_password(), wp_json_encode(), wp_rand(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 188 - "BackWPup_Job"
Cohesion: 0.14
Nodes (3): BackWPup_Job, trailingslashit(), wp_delete_file()

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "sanitize_text_field"
Cohesion: 0.09
Nodes (9): Sitepulse_Product_Analytics, sitepulse_sanitize_param(), sitepulse_set_sp_profiler(), BrandingAnalyticsTest, absint(), sanitize_key(), sanitize_text_field(), wp_kses_post() (+1 more)

### Community 199 - "wp_parse_url"
Cohesion: 0.15
Nodes (5): add_query_arg(), remove_query_arg(), untrailingslashit(), wp_nonce_url(), wp_parse_url()

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.21
Nodes (10): Backup warnings, Dashboard (portal) endpoints, Key rotation, Monthly reports, Notifications, Roles, Rollouts, Slowdowns and security fixes (+2 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 211 - "home_url"
Cohesion: 0.12
Nodes (7): Sitepulse_Remote_Health, Update commands, sitepulse_test_site_pages(), wc_get_page_permalink(), doing_action(), home_url(), maybe_unserialize()

### Community 213 - "api-variants.php"
Cohesion: 0.22
Nodes (5): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes()

### Community 219 - "Sitepulse_Monitoring"
Cohesion: 0.09
Nodes (6): Sitepulse_Monitoring, Architecture, Sitepulse_Loader, MonitoringTest, delete_metadata(), delete_option()

### Community 221 - "Sitepulse_Test_Backups"
Cohesion: 0.08
Nodes (6): BackWPup, BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, UpdraftPlus, update_site_option()

### Community 225 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

## Knowledge Gaps
- **264 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+259 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1291 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **130 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does ``POST /api/website/remote/inventory`` connect `.set_auto_updates` to `page-analysis.js`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `VersionBumpTest`, `Sitepulse_CurLoader`, `.set_auto_updates`, `AutoloadRestApiTest`, `current_user_can`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Remote_Backups`, `WordPress.php`, `update_option`, `LifecycleTest`, `RemoteBackupCommandsTest`, `PageAnalysisTest`, `RestApiTest`, `DiagnosticTemplateCoverageTest`, `RestCoverageTest`, `get_option`, `Sitepulse_Onboarding`, `Sitepulse_Frontend`, `Sitepulse_Remote_Management`, `CoreVariantsTest`, `ApiVariantsTest`, `RemoteUpdatesTest`, `do_action`, `OptionalViewIntegrationTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Plugin`, `Sitepulse_Profiler`, `sanitize_text_field`, `TemplateRenderingTest`, `RecentDashboardTest`, `PetAssistantTemplateTest`, `RemoteInventoryTest`, `.time`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `RemoteVariantsTest`, `StoredDataRobustnessTest`, `Sitepulse_Monitoring`, `Sitepulse_Settings`, `PluginTest`, `Sitepulse_Page_Tracker`, `PopulatedTemplateTest`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Why does `Sitepulse_Remote_Management` connect `Sitepulse_Remote_Management` to `Sitepulse_Error_Handler`, `.set_auto_updates`, `PresentationWordPress.php`, `Sitepulse_Remote_Updates`, `current_user_can`, `Sitepulse_Remote_Backups`, `WordPress.php`, `RemoteBackupCommandsTest`, `Sitepulse_Remote_Events`, `get_option`, `Sitepulse_Remote_Inventory`, `WP_Error`, `wp_clear_scheduled_hook`, `is_wp_error`, `RemoteUpdatesTest`, `do_action`, `Dashboard (portal) endpoints`, `home_url`, `plugin_basename`, `.time`, `Sitepulse_Monitoring`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Are the 209 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 209 INFERRED edges - model-reasoned connections that need verification._
- **Are the 159 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 159 INFERRED edges - model-reasoned connections that need verification._
- **Are the 43 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 43 INFERRED edges - model-reasoned connections that need verification._
- **Are the 62 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 62 INFERRED edges - model-reasoned connections that need verification._
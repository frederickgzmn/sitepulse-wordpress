# Graph Report - sitepulse  (2026-10-16)

## Corpus Check
- 181 files · ~368,578 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4465 nodes · 10248 edges · 229 communities (95 shown, 134 thin omitted)
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 2383 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `4d9f895f`
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
- Commands
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
- do_action
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
- get_transient
- Sitepulse_Remote_Inventory
- Tab
- RemoteManagementTest
- remove
- Sitepulse_Remote_Events
- Sitepulse_Monitoring
- BaseComponent
- RemoteUpdatesTest
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
- Sitepulse_Remote_Client
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- plugin_basename
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
- Zi
- Ce
- Es
- PluginTest
- sitepulse_test_remote_response
- Sitepulse_Whitelabel_Service
- FocusTrap
- sitepulse_global.js
- Sitepulse_Setup
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
- wp_normalize_path
- Sitepulse_Getting_Started
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
- Sitepulse_Test_Local_Filesystem
- Sitepulse_Plugin_Profiler
- browser
- backend.test.cjs
- Ln
- LifecycleTest
- WP_REST_Request
- RestApiTest
- Sitepulse_Plugin
- coverage.cjs
- browser.cjs
- RemoteManagement.php
- contains
- DiagnosticTemplateCoverageTest
- BackWPup_Job
- js-coverage.cjs
- update_option
- Me
- Automated tests
- CoverageGateTest
- .test_a_theme_that_cannot_be_restored_stays_on_the_new_version
- CoreVariantsTest
- ApiVariantsTest
- Swipe
- WordPress.php
- Sitepulse_Page_Tracker
- EasyModeSaveQueriesTest
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- RemoteClientTest
- ot
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- get_option
- BackWPup
- Dashboard (portal) endpoints
- ActionSchedulerTest
- B
- Wi
- TemplateRenderingTest
- RecentDashboardTest
- FocusTrap
- HttpOriginTest
- Ie
- .set_auto_updates
- HarnessIsolationTest
- ProfilerCompatibilityTest
- sitepulse_test_subprocess
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- StoredDataRobustnessTest
- is_wp_error
- Sitepulse_Test_Backups
- Sitepulse_Settings
- Sitepulse_Remote_Management
- TemplateTest
- Sitepulse_Remote_Updates
- .get_status_info
- CLAUDE.md

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 214 edges
2. `get_option()` - 164 edges
3. `Sitepulse_Test_WP` - 132 edges
4. `Sitepulse_Remote_Management` - 99 edges
5. `Sitepulse_Api_Service` - 83 edges
6. `Sitepulse_Profiler` - 74 edges
7. `Sitepulse_Remote_Backups` - 68 edges
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

## Communities (229 total, 134 thin omitted)

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
Cohesion: 0.05
Nodes (8): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, StoredDataWritePathsTest, current_time(), is_email(), sanitize_email()

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

### Community 11 - "Commands"
Cohesion: 0.15
Nodes (11): Backup commands, Backup preset format, Commands, Endpoints, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/events`, `POST /api/website/remote/inventory` (+3 more)

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

### Community 20 - "TemplateFactory"
Cohesion: 0.09
Nodes (3): Config, sanitizeHtml(), TemplateFactory

### Community 22 - "current_user_can"
Cohesion: 0.24
Nodes (5): check_ajax_referer(), current_user_can(), Sitepulse_Test_Json_Response, wp_send_json_error(), wp_send_json_success()

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.15
Nodes (3): Sitepulse_Easy_Mode, EasyModeTest, update_user_meta()

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.06
Nodes (3): Sitepulse_Remote_Backups, RemoteBackupsTest, UpdraftPlus_Options

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

### Community 44 - "R"
Cohesion: 0.08
Nodes (5): getDataAttributes(), H(), R, Ti, W

### Community 46 - "Sitepulse_Frontend"
Cohesion: 0.16
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "get_transient"
Cohesion: 0.04
Nodes (14): Sitepulse_Onboarding, Sitepulse_Remote_Health, Architecture, Sitepulse_Loader, SetupOnboardingTest, sitepulse_test_site_pages(), delete_option(), delete_transient() (+6 more)

### Community 48 - "Sitepulse_Remote_Inventory"
Cohesion: 0.09
Nodes (9): Sitepulse_Remote_Inventory, disk_free_space(), get_core_updates(), get_filesystem_method(), get_site_transient(), is_plugin_active(), wp_get_translation_updates(), wp_is_file_mod_allowed() (+1 more)

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 53 - "Sitepulse_Monitoring"
Cohesion: 0.14
Nodes (4): Sitepulse_Monitoring, MonitoringTest, add_query_arg(), wp_nonce_url()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Backend"
Cohesion: 0.20
Nodes (3): Sitepulse_Backend, remove_all_actions(), add_menu_page()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

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
Nodes (5): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n(), sitepulse_test_post_type()

### Community 83 - "plugin_basename"
Cohesion: 0.11
Nodes (9): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes(), register_activation_hook(), register_deactivation_hook(), get_plugin_data() (+1 more)

### Community 86 - "Sitepulse_Cron_Fallback"
Cohesion: 0.07
Nodes (9): Sitepulse_Cron_Fallback, Sitepulse_Cron_Manager, CronCoverageTest, CronTest, fastcgi_finish_request(), has_action(), wp_get_scheduled_event(), wp_get_schedules() (+1 more)

### Community 90 - "popperGenerator"
Cohesion: 0.17
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "detectOverflow"
Cohesion: 0.30
Nodes (12): detectOverflow(), expandToHashMap(), getClientRectFromMixedType(), getClippingRect(), getDocumentElement(), getDocumentRect(), getInnerBoundingClientRect(), getViewportRect() (+4 more)

### Community 100 - "PluginTest"
Cohesion: 0.09
Nodes (4): PluginTest, wp_delete_file(), WP_Filesystem(), wp_upload_dir()

### Community 102 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.06
Nodes (7): Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, PetAssistantTemplateTest, PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data(), wp_parse_args()

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

### Community 110 - "page-analysis.js"
Cohesion: 0.19
Nodes (20): api(), card(), el(), format(), icon(), metric(), renderBreakdown(), row() (+12 more)

### Community 136 - "VersionBumpTest"
Cohesion: 0.19
Nodes (6): PHPUnit\Framework\TestCase, sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 142 - "wp_normalize_path"
Cohesion: 0.15
Nodes (3): wp_debug_backtrace_summary(), get_template(), wp_normalize_path()

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
Nodes (12): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), human_time_diff(), load_textdomain(), _n(), wp_date() (+4 more)

### Community 151 - "add_action"
Cohesion: 0.12
Nodes (3): add_action(), add_filter(), Sitepulse_Test_Hook

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 158 - "Sitepulse_Plugin_Profiler"
Cohesion: 0.06
Nodes (4): Sitepulse_Plugin_Profiler, CoreAdditionalBehaviorTest, PluginProfilerTest, RestCoverageTest

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "WP_REST_Request"
Cohesion: 0.06
Nodes (42): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_check_vulnerabilities_api(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding() (+34 more)

### Community 164 - "RestApiTest"
Cohesion: 0.07
Nodes (11): RestApiTest, submit_button(), wp_nonce_field(), esc_attr(), esc_html(), esc_html_e(), esc_url(), get_current_user_id() (+3 more)

### Community 165 - "Sitepulse_Plugin"
Cohesion: 0.11
Nodes (4): Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest, has_filter()

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 168 - "RemoteManagement.php"
Cohesion: 0.12
Nodes (10): Automatic_Upgrader_Skin, BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader (+2 more)

### Community 170 - "contains"
Cohesion: 0.15
Nodes (4): contains(), effect$1(), Modal, remove()

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "update_option"
Cohesion: 0.06
Nodes (7): Sitepulse_Api_Service, ApiBaseTest, ApiCoverageTest, ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), update_option()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 182 - "WordPress.php"
Cohesion: 0.04
Nodes (60): sitepulse_register_route(), sitepulse_sanitize_param(), sitepulse_reorder_active_plugins_for_conflict(), RuntimeException, get_permalink(), get_the_title(), absint(), add_option() (+52 more)

### Community 183 - "Sitepulse_Page_Tracker"
Cohesion: 0.11
Nodes (3): Sitepulse_Page_Tracker, PageTrackerTest, get_num_queries()

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
Nodes (10): inc/ helpers, Sitepulse_Product_Analytics, Sitepulse_AI_Diagnostic_Cron, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), ApiRegressionTest, CoreRegressionTest (+2 more)

### Community 199 - "BackWPup"
Cohesion: 0.18
Nodes (3): BackWPup, BackWPup_Cron, trailingslashit()

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.10
Nodes (19): API → site, Backup warnings, Dashboard (portal) endpoints, Development, Key rotation, Monthly reports, Notifications, Remote management (+11 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 211 - ".set_auto_updates"
Cohesion: 0.13
Nodes (8): Phases, Update policy, get_plugins(), get_site_option(), Sitepulse_Test_Theme, update_site_option(), wp_get_theme(), wp_get_themes()

### Community 220 - "is_wp_error"
Cohesion: 0.08
Nodes (12): is_wp_error(), sanitize_text_field(), wp_generate_password(), wp_json_encode(), wp_parse_url(), wp_rand(), wp_remote_post(), wp_remote_retrieve_body() (+4 more)

### Community 221 - "Sitepulse_Test_Backups"
Cohesion: 0.11
Nodes (4): BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, UpdraftPlus

### Community 226 - "Sitepulse_Settings"
Cohesion: 0.14
Nodes (3): Sitepulse_Settings, SettingsTest, apply_filters()

### Community 228 - "Sitepulse_Remote_Management"
Cohesion: 0.08
Nodes (3): Sitepulse_Remote_Management, is_multisite(), wp_schedule_single_event()

### Community 232 - "Sitepulse_Remote_Updates"
Cohesion: 0.12
Nodes (4): Sitepulse_Remote_Updates, wp_clear_scheduled_hook(), wp_doing_cron(), wp_unschedule_event()

### Community 234 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

## Knowledge Gaps
- **263 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+258 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1293 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **134 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `VersionBumpTest`, `Sitepulse_CurLoader`, `Sitepulse_Test_Database`, `Sitepulse_Getting_Started`, `current_user_can`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Remote_Backups`, `do_action`, `Sitepulse_Plugin_Profiler`, `LifecycleTest`, `WP_REST_Request`, `RemoteBackupCommandsTest`, `Sitepulse_Plugin`, `RestApiTest`, `DiagnosticTemplateCoverageTest`, `update_option`, `Sitepulse_Frontend`, `get_transient`, `RemoteManagementTest`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Monitoring`, `Sitepulse_Page_Tracker`, `EasyModeSaveQueriesTest`, `RemoteUpdatesTest`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Profiler`, `get_option`, `ActionSchedulerTest`, `TemplateRenderingTest`, `RecentDashboardTest`, `Sitepulse_Remote_Client`, `HttpOriginTest`, `Sitepulse_Page_Analysis`, `HarnessIsolationTest`, `Sitepulse_Cron_Fallback`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `RemoteVariantsTest`, `StoredDataRobustnessTest`, `Sitepulse_Settings`, `PluginTest`, `Sitepulse_Whitelabel_Service`, `TemplateTest`, `Sitepulse_Setup`?**
  _High betweenness centrality (0.051) - this node is a cross-community bridge._
- **Why does ``POST /api/website/remote/inventory`` connect `Commands` to `page-analysis.js`?**
  _High betweenness centrality (0.044) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Test_Database`, `Sitepulse_Getting_Started`, `add_action`, `Sitepulse_Remote_Activity`, `Sitepulse_Remote_Backups`, `do_action`, `Sitepulse_Test_Local_Filesystem`, `Sitepulse_Plugin_Profiler`, `WP_REST_Request`, `RemoteBackupCommandsTest`, `Sitepulse_Plugin`, `RestApiTest`, `DiagnosticTemplateCoverageTest`, `BackWPup_Job`, `Sitepulse_Frontend`, `get_transient`, `RemoteManagementTest`, `Sitepulse_Remote_Events`, `Sitepulse_Monitoring`, `WordPress.php`, `RemoteUpdatesTest`, `Sitepulse_Profiler`, `get_option`, `TemplateRenderingTest`, `Sitepulse_Remote_Client`, `Sitepulse_Page_Analysis`, `Sitepulse_Cron_Fallback`, `Sitepulse_Utils`, `ProfilerCompatibilityTest`, `StoredDataRobustnessTest`, `is_wp_error`, `Sitepulse_Test_Backups`, `Sitepulse_Settings`, `Sitepulse_Remote_Management`, `sitepulse_test_remote_response`, `Sitepulse_Whitelabel_Service`, `Sitepulse_Remote_Updates`, `Sitepulse_Setup`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Are the 210 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 210 INFERRED edges - model-reasoned connections that need verification._
- **Are the 159 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 159 INFERRED edges - model-reasoned connections that need verification._
- **Are the 44 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 44 INFERRED edges - model-reasoned connections that need verification._
- **Are the 62 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 62 INFERRED edges - model-reasoned connections that need verification._
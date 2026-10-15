# Graph Report - sitepulse  (2026-10-15)

## Corpus Check
- 173 files · ~347,631 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4262 nodes · 9528 edges · 219 communities (100 shown, 119 thin omitted)
- Extraction: 78% EXTRACTED · 22% INFERRED · 0% AMBIGUOUS · INFERRED: 2057 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `a844b5c8`
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
- CronCoverageTest
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
- .ajax_enable_savequeries
- Sitepulse_Easy_Mode
- SitePulse
- Tooltip
- Sitepulse_Remote_Backups
- remove
- WordPress.php
- .hide
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
- Collapse
- contains
- Sitepulse_Remote_Management
- remove
- Sitepulse_Setup
- .time
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
- Sitepulse_Test_Case
- Sitepulse_Profiler
- blueprint.json
- addHandler
- BaseComponent
- preventOverflow
- Dropdown
- W
- BaseComponent
- Toast
- ScrollSpy
- Modal
- Sitepulse_Backend
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- Sitepulse_Whitelabel_Service
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
- get_transient
- Sitepulse_CurLoader
- DiagnosticTemplateCoverageTest
- SitePulse — See What's Powering (or Slowing) Your Site
- Sitepulse_AI_Diagnostic_Cron
- AutoloadRestApiTest
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- scripts
- PresentationWordPress.php
- plugin_basename
- onboarding.test.cjs
- Commands
- BackendTest
- ps
- FocusTrap
- Sitepulse_Test_Local_Filesystem
- Sitepulse_Plugin_Profiler
- browser
- backend.test.cjs
- remove
- LifecycleTest
- browser.cjs
- StoredDataRobustnessTest
- TemplateTest
- get_current_user_id
- WP_REST_Response
- Swipe
- add_action
- VersionBumpTest
- RestCoverageTest
- js-coverage.cjs
- update_option
- Me
- Automated tests
- Sitepulse_Remote_Client
- coverage.cjs
- CoreVariantsTest
- sitepulse_test_subprocess
- .admin_bar_node
- is_wp_error
- wp_normalize_path
- Remote management
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- Wi
- Sitepulse_Monitoring
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- CoreAdditionalBehaviorTest
- AI Diagnostics
- CoverageGateTest
- do_action
- B
- Ie
- Installation
- RecentDashboardTest
- Sitepulse_Remote_Events
- Sitepulse_Getting_Started
- Sitepulse_Remote_Inventory
- PetAssistantTemplateTest
- ProfilerCompatibilityTest
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- Architecture

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 186 edges
2. `get_option()` - 143 edges
3. `Sitepulse_Test_WP` - 122 edges
4. `Sitepulse_Remote_Management` - 79 edges
5. `Sitepulse_Api_Service` - 73 edges
6. `Sitepulse_Profiler` - 73 edges
7. `Sitepulse_Page_Analysis` - 67 edges
8. `Sitepulse_Remote_Backups` - 66 edges
9. `Sitepulse_Test_Case` - 64 edges
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

## Communities (219 total, 119 thin omitted)

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
Nodes (9): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, StoredDataWritePathsTest, current_time(), get_bloginfo(), is_email() (+1 more)

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

### Community 20 - "TemplateFactory"
Cohesion: 0.07
Nodes (4): Config, FocusTrap, sanitizeHtml(), TemplateFactory

### Community 22 - ".ajax_enable_savequeries"
Cohesion: 0.31
Nodes (5): RuntimeException, check_ajax_referer(), Sitepulse_Test_Json_Response, wp_send_json_error(), wp_send_json_success()

### Community 23 - "Sitepulse_Easy_Mode"
Cohesion: 0.11
Nodes (4): Sitepulse_Easy_Mode, EasyModeTest, update_user_meta(), TemplateRenderingTest

### Community 24 - "SitePulse"
Cohesion: 0.10
Nodes (20): Admin Bar Widget, Basic View, Core Web Vitals, Cron Job Management, Developer View, Email Blocking, Fatal-Error Detection, Health Score System (+12 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.03
Nodes (17): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup, BackWPup_Cron, BackWPup_Job, BackWPup_JobTypes, BackWPup_Option, Sitepulse_Test_Backups (+9 more)

### Community 27 - "remove"
Cohesion: 0.09
Nodes (3): Modal, remove(), Toast

### Community 28 - "WordPress.php"
Cohesion: 0.03
Nodes (60): sitepulse_register_route(), sitepulse_sanitize_param(), gethostbyname(), gethostname(), wc_get_page_id(), get_permalink(), get_the_title(), add_option() (+52 more)

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
Cohesion: 0.18
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Onboarding"
Cohesion: 0.07
Nodes (4): Sitepulse_Onboarding, CoreRegressionTest, SetupOnboardingTest, delete_option()

### Community 49 - "contains"
Cohesion: 0.18
Nodes (3): contains(), effect$1(), Tab

### Community 50 - "Sitepulse_Remote_Management"
Cohesion: 0.09
Nodes (6): Sitepulse_Remote_Management, RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response(), is_multisite(), wp_generate_uuid4()

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 53 - ".time"
Cohesion: 0.14
Nodes (3): human_time_diff(), _get_cron_array(), wp_schedule_single_event()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Plugin"
Cohesion: 0.10
Nodes (5): Sitepulse_Plugin, BackendCoverageTest, wp_convert_hr_to_bytes(), PluginAdditionalBehaviorTest, has_filter()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Test_Case"
Cohesion: 0.15
Nodes (3): EasyModeSaveQueriesTest, HarnessIsolationTest, Sitepulse_Test_Case

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

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 78 - "Sitepulse_Backend"
Cohesion: 0.15
Nodes (4): Sitepulse_Backend, remove_all_actions(), add_menu_page(), current_user_can()

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.07
Nodes (4): Sitepulse_Page_Analysis, PageAnalysisTest, number_format_i18n(), wp_generate_password()

### Community 83 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.10
Nodes (3): Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, wp_parse_args()

### Community 86 - "Sitepulse_Cron_Fallback"
Cohesion: 0.15
Nodes (4): Sitepulse_Cron_Fallback, CronTest, has_action(), set_transient()

### Community 90 - "popperGenerator"
Cohesion: 0.17
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "detectOverflow"
Cohesion: 0.24
Nodes (17): detectOverflow(), expandToHashMap(), getBoundingClientRect(), getClientRectFromMixedType(), getClippingRect(), getCompositeRect(), getDocumentElement(), getDocumentRect() (+9 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.07
Nodes (5): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters()

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
Cohesion: 0.05
Nodes (38): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_check_vulnerabilities_api(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding() (+30 more)

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

### Community 136 - "get_transient"
Cohesion: 0.15
Nodes (3): PageTrackerTest, get_transient(), sitepulse_test_post_type()

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 142 - "Sitepulse_AI_Diagnostic_Cron"
Cohesion: 0.08
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
Nodes (16): config, allow-plugins, platform, sort-packages, description, license, name, php (+8 more)

### Community 147 - "SitePulse Pro"
Cohesion: 0.67
Nodes (3): SitePulse Pro Waitlist, SitePulse Pro, SQL Query Monitor

### Community 149 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, coverage:js, test, test:coverage, test:js

### Community 150 - "PresentationWordPress.php"
Cohesion: 0.10
Nodes (11): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), wp_date(), wp_mail() (+3 more)

### Community 151 - "plugin_basename"
Cohesion: 0.14
Nodes (12): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), register_activation_hook(), register_deactivation_hook(), register_activation_hook() (+4 more)

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 153 - "Commands"
Cohesion: 0.23
Nodes (6): Backup commands, Backup preset format, Commands, `POST /api/website/remote/inventory`, DirectAccessTemplateTest, is_plugin_active()

### Community 155 - "ps"
Cohesion: 0.09
Nodes (3): d(), ps, vs

### Community 158 - "Sitepulse_Plugin_Profiler"
Cohesion: 0.10
Nodes (3): Sitepulse_Plugin_Profiler, PluginProfilerTest, get_plugins()

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 161 - "remove"
Cohesion: 0.11
Nodes (3): es, Ln, remove()

### Community 163 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 166 - "get_current_user_id"
Cohesion: 0.14
Nodes (10): submit_button(), wp_nonce_field(), esc_attr(), esc_html(), esc_html_e(), esc_url(), get_current_user_id(), get_user_meta() (+2 more)

### Community 170 - "add_action"
Cohesion: 0.14
Nodes (3): add_action(), add_filter(), Sitepulse_Test_Hook

### Community 171 - "VersionBumpTest"
Cohesion: 0.17
Nodes (6): PHPUnit\Framework\TestCase, sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "update_option"
Cohesion: 0.06
Nodes (9): Sitepulse_Api_Service, ApiCoverageTest, ApiRegressionTest, ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), delete_transient(), get_option() (+1 more)

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 180 - "sitepulse_test_subprocess"
Cohesion: 0.14
Nodes (3): ApiVariantsTest, TestCase, sitepulse_test_subprocess()

### Community 181 - ".admin_bar_node"
Cohesion: 0.25
Nodes (4): is_admin(), is_admin_bar_showing(), wp_doing_ajax(), wp_doing_cron()

### Community 182 - "is_wp_error"
Cohesion: 0.42
Nodes (6): is_wp_error(), wp_json_encode(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 183 - "wp_normalize_path"
Cohesion: 0.12
Nodes (5): wp_debug_backtrace_summary(), HttpOriginTest, get_file_data(), get_theme_root(), wp_normalize_path()

### Community 184 - "Remote management"
Cohesion: 0.13
Nodes (13): API → site, Backup warnings, Dashboard (portal) endpoints, Endpoints, Phases, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, Remote management (+5 more)

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 189 - "Sitepulse_Monitoring"
Cohesion: 0.12
Nodes (9): Sitepulse_Monitoring, MonitoringTest, check_admin_referer(), delete_metadata(), Sitepulse_Test_Die, Sitepulse_Test_Redirect, wp_die(), wp_get_referer() (+1 more)

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "CoreAdditionalBehaviorTest"
Cohesion: 0.10
Nodes (4): Sitepulse_Product_Analytics, CoreAdditionalBehaviorTest, absint(), sanitize_key()

### Community 199 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 201 - "do_action"
Cohesion: 0.28
Nodes (4): `POST /api/website/remote/events`, RemoteEventsTest, do_action(), doing_action()

### Community 202 - "B"
Cohesion: 0.10
Nodes (3): B, qt, U

### Community 204 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

### Community 212 - "Sitepulse_Remote_Inventory"
Cohesion: 0.07
Nodes (5): Sitepulse_Remote_Inventory, RemoteInventoryTest, Sitepulse_Test_Database, get_filesystem_method(), wp_is_file_mod_allowed()

### Community 219 - "Architecture"
Cohesion: 0.19
Nodes (5): Architecture, Commands, Repo workflow, Testing notes, Sitepulse_Loader

## Knowledge Gaps
- **261 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+256 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1264 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **119 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `get_transient`, `Sitepulse_CurLoader`, `CronCoverageTest`, `DiagnosticTemplateCoverageTest`, `AutoloadRestApiTest`, `.ajax_enable_savequeries`, `Sitepulse_Easy_Mode`, `Commands`, `BackendTest`, `Sitepulse_Remote_Backups`, `Sitepulse_Plugin_Profiler`, `LifecycleTest`, `RemoteBackupCommandsTest`, `StoredDataRobustnessTest`, `TemplateTest`, `VersionBumpTest`, `RestCoverageTest`, `update_option`, `Sitepulse_Onboarding`, `Sitepulse_Frontend`, `Sitepulse_Remote_Client`, `Sitepulse_Remote_Management`, `CoreVariantsTest`, `sitepulse_test_subprocess`, `Sitepulse_Setup`, `wp_normalize_path`, `OptionalViewIntegrationTest`, `Sitepulse_Monitoring`, `ErrorHandlerVariantsTest`, `Sitepulse_Plugin`, `Sitepulse_Profiler`, `CoreAdditionalBehaviorTest`, `do_action`, `RecentDashboardTest`, `Sitepulse_Getting_Started`, `Sitepulse_Whitelabel_Service`, `Sitepulse_Page_Analysis`, `PetAssistantTemplateTest`, `Sitepulse_Cron_Fallback`, `ProfilerCompatibilityTest`, `Sitepulse_Remote_Inventory`, `RemoteVariantsTest`, `Sitepulse_Utils`, `Sitepulse_Settings`, `PluginTest`, `Sitepulse_Page_Tracker`, `PopulatedTemplateTest`, `WP_REST_Request`?**
  _High betweenness centrality (0.058) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `get_transient`, `Sitepulse_CurLoader`, `DiagnosticTemplateCoverageTest`, `Sitepulse_AI_Diagnostic_Cron`, `plugin_basename`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Backups`, `WordPress.php`, `Sitepulse_Plugin_Profiler`, `RemoteBackupCommandsTest`, `StoredDataRobustnessTest`, `add_action`, `RestCoverageTest`, `Sitepulse_Frontend`, `Sitepulse_Onboarding`, `Sitepulse_Remote_Management`, `Sitepulse_Setup`, `.time`, `is_wp_error`, `Sitepulse_Monitoring`, `Sitepulse_Plugin`, `Sitepulse_Profiler`, `CoreAdditionalBehaviorTest`, `do_action`, `Sitepulse_Remote_Events`, `Sitepulse_Page_Analysis`, `Sitepulse_Getting_Started`, `Sitepulse_Cron_Fallback`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `Architecture`, `Sitepulse_Settings`, `Sitepulse_Page_Tracker`, `PopulatedTemplateTest`, `WP_REST_Request`?**
  _High betweenness centrality (0.037) - this node is a cross-community bridge._
- **Why does ``POST /api/website/remote/inventory`` connect `Commands` to `Remote management`, `page-analysis.js`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **Are the 182 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 182 INFERRED edges - model-reasoned connections that need verification._
- **Are the 138 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 138 INFERRED edges - model-reasoned connections that need verification._
- **Are the 39 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 39 INFERRED edges - model-reasoned connections that need verification._
- **Are the 47 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.backwpup_finished()` and `.provider()`) actually correct?**
  _`Sitepulse_Remote_Management` has 47 INFERRED edges - model-reasoned connections that need verification._
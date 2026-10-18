# Graph Report - sitepulse  (2026-10-18)

## Corpus Check
- 188 files · ~388,508 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4663 nodes · 11035 edges · 240 communities (102 shown, 138 thin omitted)
- Extraction: 75% EXTRACTED · 25% INFERRED · 0% AMBIGUOUS · INFERRED: 2726 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `a3d9761b`
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
- Sitepulse_Remote_Events
- St
- BaseComponent
- TemplateFactory
- Fi
- bootstrap.bundle.js
- Tooltip
- backend.js
- .hide
- remove
- ._queueCallback
- WP_REST_Response
- Sitepulse_Test_Backups
- SitePulse
- Tooltip
- Sitepulse_Remote_Backups
- .hide
- RestCoverageTest
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
- Sitepulse_Test_Case
- Sitepulse_Test_Local_Filesystem
- Tab
- RemoteRecoveryTest
- remove
- do_action
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
- Sitepulse_Remote_Client
- Sitepulse_Profiler
- blueprint.json
- .test_every_request_goes_to_the_api_the_setting_names
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
- PageAnalysisTest
- plugin_basename
- Ss
- ScrollSpy
- Sitepulse_Onboarding
- Sitepulse_Utils
- ts
- Swipe
- Sitepulse_Page_Analysis
- sanitize_text_field
- Qn
- ScrollBarHelper
- Toast
- Backdrop
- Sitepulse_Settings
- Zi
- Ce
- Es
- PluginTest
- RemoteManagementTest
- Sitepulse_Whitelabel_Service
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
- wp_get_theme
- SitePulse — See What's Powering (or Slowing) Your Site
- RemoteConnectionTest
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
- browser.cjs
- backend.test.cjs
- Ln
- LifecycleTest
- WP_REST_Request
- RestApiTest
- WP_Error
- coverage.cjs
- ref_node_assert
- has_action
- contains
- DiagnosticTemplateCoverageTest
- remaining-browser-behavior.test.cjs
- js-coverage.cjs
- get_option
- Me
- Automated tests
- get_transient
- Sitepulse_Remote_Management
- remote-connection.test.cjs
- Sitepulse_Monitoring
- Swipe
- WordPress.php
- Sitepulse_Page_Tracker
- CLAUDE.md
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- RemoteClientTest
- api_backend.php
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- Sitepulse_Cron_Manager
- remote-connection.js
- Dashboard (portal) endpoints
- Remote management
- B
- Sitepulse_Remote_Inventory
- update_user_meta
- RecentDashboardTest
- Sitepulse_Remote_Health
- CoreVariantsTest
- CronCoverageTest
- ApiVariantsTest
- RemoteVariantsTest
- ProfilerCompatibilityTest
- api-variants.php
- Sitepulse_Test_Filesystem
- BackWPup_Job
- Sitepulse_AI_Diagnostic_Cron
- current_user_can
- is_wp_error
- RemoteManagement.php
- sitepulse_test_subprocess
- ot
- Sitepulse_Test_Database
- Wi
- ActionScheduler_Store
- ActionSchedulerTest
- TemplateTest
- Ie
- BackWPup
- wp_next_scheduled
- Commands
- HttpOriginTest
- sitepulse_test_post_type
- sitepulse_register_route
- PageAnalysisTest.php

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 250 edges
2. `get_option()` - 190 edges
3. `Sitepulse_Remote_Management` - 139 edges
4. `Sitepulse_Test_WP` - 136 edges
5. `Sitepulse_Api_Service` - 102 edges
6. `Sitepulse_Test_Backups` - 76 edges
7. `Sitepulse_Profiler` - 74 edges
8. `Sitepulse_Remote_Backups` - 72 edges
9. `Sitepulse_Test_Case` - 70 edges
10. `Sitepulse_Page_Analysis` - 67 edges

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

## Communities (240 total, 138 thin omitted)

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

### Community 11 - "Sitepulse_Remote_Events"
Cohesion: 0.12
Nodes (3): Sitepulse_Remote_Events, get_file_data(), wp_delete_file()

### Community 13 - "BaseComponent"
Cohesion: 0.07
Nodes (4): Backdrop, BaseComponent, Button, Config

### Community 14 - "TemplateFactory"
Cohesion: 0.09
Nodes (5): Config, getElement(), isElement(), TemplateFactory, toType()

### Community 16 - "bootstrap.bundle.js"
Cohesion: 0.05
Nodes (68): addHandler(), arrow(), bootstrapDelegationHandler(), bootstrapHandler(), computeAutoPlacement(), computeOffsets(), computeStyles(), detectOverflow() (+60 more)

### Community 17 - "Tooltip"
Cohesion: 0.07
Nodes (3): Collapse, Popover, Tooltip

### Community 18 - "backend.js"
Cohesion: 0.09
Nodes (23): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), checkVulnerabilities(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike() (+15 more)

### Community 20 - "remove"
Cohesion: 0.09
Nodes (4): Config, remove(), sanitizeHtml(), TemplateFactory

### Community 23 - "Sitepulse_Test_Backups"
Cohesion: 0.08
Nodes (5): BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, Updraft_Restorer, UpdraftPlus

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.06
Nodes (3): Sitepulse_Remote_Backups, RemoteBackupsTest, UpdraftPlus_Options

### Community 28 - "RestCoverageTest"
Cohesion: 0.19
Nodes (3): sitepulse_check_memory(), sitepulse_enable_save_queries(), RestCoverageTest

### Community 35 - "Backdrop"
Cohesion: 0.13
Nodes (6): Backdrop, execute(), executeAfterTransition(), getTransitionDurationFromElement(), Swipe, triggerTransitionEnd()

### Community 37 - "getBoundingClientRect"
Cohesion: 0.13
Nodes (37): applyStyles(), areValidElements(), debounce(), effect(), effect$2(), getBoundingClientRect(), getClientRectFromMixedType(), getClippingParents() (+29 more)

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

### Community 47 - "Sitepulse_Test_Case"
Cohesion: 0.07
Nodes (5): Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest, Sitepulse_Test_Case

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 52 - "do_action"
Cohesion: 0.17
Nodes (3): RemoteEventsTest, do_action(), get_theme_root()

### Community 53 - "home_url"
Cohesion: 0.08
Nodes (17): RuntimeException, add_query_arg(), admin_url(), check_admin_referer(), esc_url_raw(), home_url(), remove_query_arg(), sanitize_hex_color() (+9 more)

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

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

### Community 72 - "Dropdown"
Cohesion: 0.14
Nodes (3): distanceAndSkiddingToXY(), Dropdown, offset()

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
Cohesion: 0.10
Nodes (12): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), register_activation_hook(), register_deactivation_hook(), get_plugin_data() (+4 more)

### Community 86 - "Sitepulse_Onboarding"
Cohesion: 0.06
Nodes (11): Sitepulse_Onboarding, Architecture, Sitepulse_Loader, DirectAccessTemplateTest, submit_button(), wp_nonce_field(), delete_option(), esc_attr() (+3 more)

### Community 91 - "sanitize_text_field"
Cohesion: 0.29
Nodes (5): sitepulse_sanitize_param(), sitepulse_set_sp_profiler(), absint(), sanitize_text_field(), wp_kses_post()

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.10
Nodes (3): Sitepulse_Settings, SettingsTest, _get_cron_array()

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 101 - "RemoteManagementTest"
Cohesion: 0.21
Nodes (3): RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response()

### Community 102 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.05
Nodes (9): Sitepulse_Product_Analytics, Sitepulse_Whitelabel_Service, BrandingAnalyticsTest, PetAssistantTemplateTest, PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data(), sanitize_key() (+1 more)

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
Cohesion: 0.09
Nodes (9): Rollouts, PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), CoverageGateTest (+1 more)

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 143 - "Sitepulse_Easy_Mode"
Cohesion: 0.08
Nodes (8): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, EasyModeTest, GettingStartedTest, get_current_user_id(), get_user_meta(), is_user_logged_in(), metadata_exists()

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
Nodes (12): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), remove_all_actions(), wp_date() (+4 more)

### Community 151 - "add_action"
Cohesion: 0.12
Nodes (4): add_action(), add_filter(), apply_filters(), Sitepulse_Test_Hook

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 158 - "update_option"
Cohesion: 0.05
Nodes (7): Sitepulse_Plugin_Profiler, ApiRegressionTest, CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataRobustnessTest, StoredDataWritePathsTest, update_option()

### Community 159 - "browser.cjs"
Cohesion: 0.13
Nodes (17): assert, { browser }, localization, setup(), test, assert, { browser }, localization (+9 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 164 - "RestApiTest"
Cohesion: 0.09
Nodes (7): sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_vulnerabilities_api(), sitepulse_get_memory_info(), sitepulse_rest_pre_dispatch(), sitepulse_set_wpslowhttp(), sitepulse_update_settings(), RestApiTest

### Community 165 - "WP_Error"
Cohesion: 0.14
Nodes (12): sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_get_curl_stats(), sitepulse_get_plugin_profiler_stats(), sitepulse_get_profiler_stats(), sitepulse_request_ai_diagnostic_api() (+4 more)

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): ref_node_fs, ref_node_path, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "ref_node_assert"
Cohesion: 0.12
Nodes (15): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+7 more)

### Community 168 - "has_action"
Cohesion: 0.19
Nodes (3): Sitepulse_Cron_Fallback, CronTest, has_action()

### Community 170 - "contains"
Cohesion: 0.10
Nodes (4): contains(), effect$1(), FocusTrap, Modal

### Community 172 - "remaining-browser-behavior.test.cjs"
Cohesion: 0.29
Nodes (5): assert, backend(), { browser, jsonResponse }, memory, test

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (26): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-instrument (+18 more)

### Community 174 - "get_option"
Cohesion: 0.05
Nodes (6): Sitepulse_Api_Service, ApiCoverageTest, ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), get_option()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 177 - "get_transient"
Cohesion: 0.17
Nodes (4): delete_transient(), get_transient(), set_transient(), wp_doing_ajax()

### Community 178 - "Sitepulse_Remote_Management"
Cohesion: 0.06
Nodes (5): Sitepulse_Remote_Management, human_time_diff(), site_url(), wp_clear_scheduled_hook(), wp_schedule_single_event()

### Community 179 - "remote-connection.test.cjs"
Cohesion: 0.40
Nodes (5): assert, box(), { browser, jsonResponse }, settings(), test

### Community 180 - "Sitepulse_Monitoring"
Cohesion: 0.13
Nodes (4): Sitepulse_Monitoring, MonitoringTest, delete_metadata(), is_admin()

### Community 182 - "WordPress.php"
Cohesion: 0.04
Nodes (45): HarnessIsolationTest, get_permalink(), get_the_title(), add_menu_page(), add_option(), add_submenu_page(), delete_user_meta(), did_action() (+37 more)

### Community 183 - "Sitepulse_Page_Tracker"
Cohesion: 0.08
Nodes (4): Sitepulse_Page_Tracker, wp_debug_backtrace_summary(), PageTrackerTest, get_num_queries()

### Community 184 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 189 - "api_backend.php"
Cohesion: 0.23
Nodes (14): sitepulse_dismiss_getting_started(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events(), sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report(), sitepulse_page_analysis_response() (+6 more)

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "Sitepulse_Cron_Manager"
Cohesion: 0.14
Nodes (4): Sitepulse_Cron_Manager, wp_get_scheduled_event(), wp_get_schedules(), wp_schedule_event()

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.13
Nodes (15): Backup warnings, Connecting a site to an account, Dashboard (portal) endpoints, Key rotation, Monthly reports, Notifications, `POST /api/website/remote/check-in`, Roles (+7 more)

### Community 201 - "Remote management"
Cohesion: 0.15
Nodes (12): API → site, Development, Endpoints, Phases, `POST /api/website/remote/command-result`, `POST /api/website/remote/key`, Remote management, Retention (+4 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 203 - "Sitepulse_Remote_Inventory"
Cohesion: 0.11
Nodes (8): Sitepulse_Remote_Inventory, disk_free_space(), get_filesystem_method(), get_site_option(), is_plugin_active(), wp_get_themes(), wp_get_translation_updates(), wp_is_file_mod_allowed()

### Community 215 - "api-variants.php"
Cohesion: 0.22
Nodes (5): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes()

### Community 219 - "current_user_can"
Cohesion: 0.32
Nodes (5): check_ajax_referer(), current_user_can(), Sitepulse_Test_Json_Response, wp_send_json_error(), wp_send_json_success()

### Community 220 - "is_wp_error"
Cohesion: 0.12
Nodes (12): has_filter(), is_wp_error(), remove_action(), remove_filter(), sitepulse_test_callback_id(), wp_generate_password(), wp_json_encode(), wp_rand() (+4 more)

### Community 221 - "RemoteManagement.php"
Cohesion: 0.11
Nodes (11): Automatic_Upgrader_Skin, BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, sitepulse_test_remote_endpoint(), sitepulse_test_site_pages(), Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader (+3 more)

### Community 233 - "BackWPup"
Cohesion: 0.10
Nodes (6): BackWPup, BackWPup_Cron, Updraft_Restorer_Skin, UpdraftPlus_Backup_History, trailingslashit(), update_site_option()

### Community 234 - "wp_next_scheduled"
Cohesion: 0.28
Nodes (3): wp_doing_cron(), wp_next_scheduled(), wp_unschedule_event()

### Community 235 - "Commands"
Cohesion: 0.17
Nodes (10): Backup commands, Backup preset format, Commands, `POST /api/website/remote/events`, `POST /api/website/remote/inventory`, `POST /api/website/remote/safe-update`, Safe updates, Update commands (+2 more)

## Knowledge Gaps
- **267 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+262 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1314 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **138 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `VersionBumpTest`, `Sitepulse_CurLoader`, `RemoteConnectionTest`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Remote_Backups`, `RestCoverageTest`, `Sitepulse_Plugin`, `update_option`, `LifecycleTest`, `RemoteBackupCommandsTest`, `RestApiTest`, `has_action`, `DiagnosticTemplateCoverageTest`, `get_option`, `Sitepulse_Frontend`, `Sitepulse_Test_Local_Filesystem`, `get_transient`, `RemoteRecoveryTest`, `Sitepulse_Monitoring`, `do_action`, `WordPress.php`, `Sitepulse_Page_Tracker`, `Sitepulse_Remote_Updates`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `Sitepulse_Backend`, `ErrorHandlerVariantsTest`, `Sitepulse_Remote_Client`, `Sitepulse_Profiler`, `.test_every_request_goes_to_the_api_the_setting_names`, `AutoloadRestApiTest`, `update_user_meta`, `RecentDashboardTest`, `RemoteInventoryTest`, `CoreVariantsTest`, `PageAnalysisTest`, `CronCoverageTest`, `ApiVariantsTest`, `RemoteVariantsTest`, `Sitepulse_Onboarding`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `current_user_can`, `Sitepulse_Settings`, `PluginTest`, `RemoteManagementTest`, `ActionSchedulerTest`, `Sitepulse_Whitelabel_Service`, `TemplateTest`, `HttpOriginTest`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Remote_Events`, `RemoteConnectionTest`, `Sitepulse_Easy_Mode`, `add_action`, `Sitepulse_Test_Backups`, `Sitepulse_Remote_Activity`, `Sitepulse_Remote_Backups`, `RestCoverageTest`, `RestApiTest`, `RemoteBackupCommandsTest`, `WP_Error`, `has_action`, `DiagnosticTemplateCoverageTest`, `get_option`, `Sitepulse_Test_Case`, `Sitepulse_Frontend`, `get_transient`, `Sitepulse_Remote_Management`, `RemoteRecoveryTest`, `Sitepulse_Monitoring`, `home_url`, `WordPress.php`, `Sitepulse_Page_Tracker`, `do_action`, `Sitepulse_Remote_Updates`, `api_backend.php`, `Sitepulse_Backend`, `Sitepulse_Remote_Client`, `Sitepulse_Profiler`, `.test_every_request_goes_to_the_api_the_setting_names`, `Sitepulse_Cron_Manager`, `update_user_meta`, `PageAnalysisTest`, `plugin_basename`, `Sitepulse_Onboarding`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `BackWPup_Job`, `Sitepulse_Page_Analysis`, `current_user_can`, `is_wp_error`, `sanitize_text_field`, `Sitepulse_AI_Diagnostic_Cron`, `RemoteManagement.php`, `Sitepulse_Settings`, `Sitepulse_Test_Database`, `RemoteManagementTest`, `Sitepulse_Whitelabel_Service`, `BackWPup`, `sitepulse_test_post_type`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `Dashboard (portal) endpoints` connect `Dashboard (portal) endpoints` to `VersionBumpTest`, `Remote management`, `Commands`, `remote-connection.test.cjs`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **Are the 246 inferred relationships involving `update_option()` (e.g. with `.make_api_request()` and `.make_vulnerability_api_request()`) actually correct?**
  _`update_option()` has 246 INFERRED edges - model-reasoned connections that need verification._
- **Are the 185 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 185 INFERRED edges - model-reasoned connections that need verification._
- **Are the 88 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 88 INFERRED edges - model-reasoned connections that need verification._
- **Are the 47 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 47 INFERRED edges - model-reasoned connections that need verification._
# Graph Report - sitepulse  (2026-10-18)

## Corpus Check
- 189 files · ~398,364 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4733 nodes · 11431 edges · 238 communities (106 shown, 132 thin omitted)
- Extraction: 75% EXTRACTED · 25% INFERRED · 0% AMBIGUOUS · INFERRED: 2880 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `34418012`
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
- .hide
- bootstrap.bundle.js
- Tooltip
- backend.js
- Fs
- remove
- ._queueCallback
- RestApiTest
- Sitepulse_Test_Backups
- SitePulse
- Tooltip
- Sitepulse_Remote_Backups
- .hide
- Sitepulse_Settings
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
- Sitepulse_Test_Local_Filesystem
- Tab
- RemoteRecoveryTest
- remove
- do_action
- is_wp_error
- BaseComponent
- Sitepulse_Remote_Updates
- Zs
- Dropdown
- Dropdown
- Dropdown
- Tab
- Tab
- wp_create_nonce
- TemplateFactory
- Tab
- onboarding.js
- Sitepulse_Remote_Client
- Sitepulse_Profiler
- blueprint.json
- PopulatedTemplateTest
- BaseComponent
- AutoloadRestApiTest
- Dropdown
- remove
- BaseComponent
- Toast
- ScrollSpy
- Modal
- Sitepulse_Remote_Inventory
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- plugin_basename
- Ss
- ScrollSpy
- current_user_can
- Sitepulse_Utils
- ts
- Swipe
- RestCoverageTest
- Sitepulse_Product_Analytics
- Qn
- ScrollBarHelper
- Toast
- Backdrop
- .roll_back
- Zi
- Ce
- Es
- PluginTest
- sitepulse_test_remote_response
- Sitepulse_Api_Service
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
- get_option
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
- .time
- onboarding.test.cjs
- Sitepulse_Remote_Activity
- BackendTest
- ps
- FocusTrap
- Sitepulse_Backend
- Sitepulse_Plugin_Profiler
- browser.cjs
- backend.test.cjs
- Ln
- LifecycleTest
- HttpOriginTest
- WP_REST_Request
- Sitepulse_Remote_Management
- coverage.cjs
- ref_node_assert
- get_transient
- contains
- DiagnosticTemplateCoverageTest
- remaining-browser-behavior.test.cjs
- js-coverage.cjs
- ApiServiceTest
- Me
- Automated tests
- WP_Error
- RemoteManagementTest
- remote-connection.test.cjs
- Sitepulse_Monitoring
- Swipe
- WordPress.php
- Sitepulse_Page_Tracker
- delete_option
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- RemoteClientTest
- ProfilerCompatibilityTest
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- Sitepulse_Test_Filesystem
- remote-connection.js
- Dashboard (portal) endpoints
- Remote management
- B
- add_action
- Ti
- RecentDashboardTest
- home_url
- CoreVariantsTest
- update_option
- ApiVariantsTest
- RemoteVariantsTest
- Sitepulse_Test_Case
- WP_REST_Response
- api_backend.php
- Sitepulse_Plugin
- Sitepulse_Whitelabel_Service
- TemplateRenderingTest
- get_current_screen
- RemoteManagement.php
- sitepulse_test_subprocess
- ot
- PetAssistantTemplateTest
- Fi
- api-variants.php
- StoredDataRobustnessTest
- TemplateTest
- pi
- BrandingAnalyticsTest
- AI Diagnostics
- Commands
- Installation
- build-zip.sh

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 269 edges
2. `get_option()` - 200 edges
3. `Sitepulse_Remote_Management` - 142 edges
4. `Sitepulse_Test_WP` - 137 edges
5. `Sitepulse_Api_Service` - 102 edges
6. `Sitepulse_Test_Backups` - 95 edges
7. `Sitepulse_Remote_Backups` - 80 edges
8. `Sitepulse_Remote_Updates` - 80 edges
9. `RemoteUpdatesTest` - 75 edges
10. `Sitepulse_Profiler` - 74 edges

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

## Communities (238 total, 132 thin omitted)

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
Nodes (10): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, wp_mail(), current_time(), esc_html(), esc_html_e() (+2 more)

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
Cohesion: 0.09
Nodes (4): Sitepulse_Remote_Events, get_bloginfo(), get_file_data(), wp_delete_file()

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

### Community 22 - "RestApiTest"
Cohesion: 0.10
Nodes (7): sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_vulnerabilities_api(), sitepulse_complete_onboarding(), sitepulse_get_memory_info(), sitepulse_rest_pre_dispatch(), sitepulse_update_settings(), RestApiTest

### Community 23 - "Sitepulse_Test_Backups"
Cohesion: 0.05
Nodes (9): Commands, Repo workflow, Testing notes, Sitepulse_Test_Database, BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, Updraft_Restorer (+1 more)

### Community 24 - "SitePulse"
Cohesion: 0.10
Nodes (20): Admin Bar Widget, Basic View, Core Web Vitals, Cron Job Management, Developer View, Email Blocking, Fatal-Error Detection, Health Score System (+12 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.04
Nodes (5): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup_Job, UpdraftPlus_Options, trailingslashit()

### Community 28 - "Sitepulse_Settings"
Cohesion: 0.07
Nodes (6): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters(), _get_cron_array()

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
Cohesion: 0.11
Nodes (4): getDataAttributes(), H(), R, W

### Community 46 - "Sitepulse_Frontend"
Cohesion: 0.18
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Onboarding"
Cohesion: 0.06
Nodes (5): Sitepulse_Onboarding, Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest

### Community 51 - "remove"
Cohesion: 0.17
Nodes (3): Ue(), remove(), Ze

### Community 52 - "do_action"
Cohesion: 0.18
Nodes (3): RemoteEventsTest, do_action(), get_theme_root()

### Community 53 - "is_wp_error"
Cohesion: 0.19
Nodes (8): is_wp_error(), wp_generate_password(), wp_json_encode(), wp_rand(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "wp_create_nonce"
Cohesion: 0.17
Nodes (11): add_query_arg(), esc_js(), esc_url_raw(), remove_query_arg(), rest_url(), sanitize_hex_color(), wp_create_nonce(), wp_enqueue_script() (+3 more)

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Remote_Client"
Cohesion: 0.09
Nodes (3): Sitepulse_Remote_Client, ApiBaseTest, RemoteApiMockTest

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.06
Nodes (3): Sitepulse_Profiler, ProfilerTest, RecentActivityTest

### Community 68 - "blueprint.json"
Cohesion: 0.12
Nodes (16): Automated Test Setup, WordPress Playground, features, networking, landingPage, meta, author, categories (+8 more)

### Community 69 - "PopulatedTemplateTest"
Cohesion: 0.22
Nodes (3): PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

### Community 70 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 71 - "AutoloadRestApiTest"
Cohesion: 0.22
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

### Community 78 - "Sitepulse_Remote_Inventory"
Cohesion: 0.07
Nodes (8): Sitepulse_Remote_Inventory, disk_free_space(), RemoteInventoryTest, get_core_updates(), get_filesystem_method(), get_site_transient(), is_plugin_active(), wp_is_file_mod_allowed()

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.06
Nodes (5): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n(), sitepulse_test_post_type()

### Community 83 - "plugin_basename"
Cohesion: 0.13
Nodes (8): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), get_plugin_data(), plugin_basename(), wp_normalize_path()

### Community 86 - "current_user_can"
Cohesion: 0.13
Nodes (14): RuntimeException, check_admin_referer(), check_ajax_referer(), current_user_can(), Sitepulse_Test_Die, Sitepulse_Test_Json_Response, Sitepulse_Test_Redirect, wp_die() (+6 more)

### Community 90 - "RestCoverageTest"
Cohesion: 0.19
Nodes (3): sitepulse_check_memory(), sitepulse_enable_save_queries(), RestCoverageTest

### Community 91 - "Sitepulse_Product_Analytics"
Cohesion: 0.18
Nodes (3): Sitepulse_Product_Analytics, absint(), sanitize_key()

### Community 96 - ".roll_back"
Cohesion: 0.18
Nodes (9): Automatic_Upgrader_Skin, remove_action(), remove_filter(), sitepulse_test_callback_id(), wp_clean_plugins_cache(), wp_clean_themes_cache(), wp_remote_head(), wp_update_plugins() (+1 more)

### Community 102 - "Sitepulse_Api_Service"
Cohesion: 0.08
Nodes (4): Sitepulse_Api_Service, ApiCoverageTest, untrailingslashit(), wp_parse_url()

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
Cohesion: 0.10
Nodes (8): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), CoverageGateTest, VersionBumpTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 143 - "Sitepulse_Easy_Mode"
Cohesion: 0.07
Nodes (16): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, sitepulse_sanitize_param(), sitepulse_set_sp_profiler(), sitepulse_set_trackers_disabled_notice(), sitepulse_set_wpslowhttp(), EasyModeTest, GettingStartedTest (+8 more)

### Community 144 - "Key Features"
Cohesion: 0.40
Nodes (5): External Request Monitoring, Key Features, Performance Profiling, Site Health & Stability, Smart Experience

### Community 145 - "Privacy First"
Cohesion: 0.67
Nodes (3): Core Monitoring, Optional Cloud Features, Privacy First

### Community 146 - "composer.json"
Cohesion: 0.11
Nodes (17): config, allow-plugins, platform, sort-packages, description, license, name, php (+9 more)

### Community 147 - "SitePulse Pro"
Cohesion: 0.67
Nodes (3): SitePulse Pro Waitlist, SitePulse Pro, SQL Query Monitor

### Community 149 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, coverage:js, test, test:coverage, test:js

### Community 150 - "PresentationWordPress.php"
Cohesion: 0.09
Nodes (13): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), submit_button(), wp_date() (+5 more)

### Community 151 - ".time"
Cohesion: 0.06
Nodes (10): Sitepulse_AI_Diagnostic_Cron, Sitepulse_Cron_Manager, wp_clear_scheduled_hook(), wp_doing_ajax(), wp_doing_cron(), wp_get_scheduled_event(), wp_get_schedules(), wp_next_scheduled() (+2 more)

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 157 - "Sitepulse_Backend"
Cohesion: 0.11
Nodes (3): Sitepulse_Backend, BackendCoverageTest, has_filter()

### Community 158 - "Sitepulse_Plugin_Profiler"
Cohesion: 0.08
Nodes (4): Sitepulse_Plugin_Profiler, PluginProfilerTest, sitepulse_test_http_event(), sitepulse_test_recent_stat()

### Community 159 - "browser.cjs"
Cohesion: 0.13
Nodes (17): assert, { browser }, localization, setup(), test, assert, { browser }, localization (+9 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 165 - "Sitepulse_Remote_Management"
Cohesion: 0.06
Nodes (3): Sitepulse_Remote_Management, site_url(), wp_generate_uuid4()

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): ref_node_fs, ref_node_path, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "ref_node_assert"
Cohesion: 0.12
Nodes (15): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+7 more)

### Community 168 - "get_transient"
Cohesion: 0.10
Nodes (6): Sitepulse_Cron_Fallback, CronCoverageTest, CronTest, get_transient(), has_action(), set_transient()

### Community 170 - "contains"
Cohesion: 0.10
Nodes (4): contains(), effect$1(), FocusTrap, Modal

### Community 172 - "remaining-browser-behavior.test.cjs"
Cohesion: 0.29
Nodes (5): assert, backend(), { browser, jsonResponse }, memory, test

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (26): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-instrument (+18 more)

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 177 - "WP_Error"
Cohesion: 0.16
Nodes (10): sitepulse_collect_plugin_data(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_get_curl_stats(), sitepulse_get_plugin_profiler_stats(), sitepulse_get_profiler_stats(), sitepulse_request_ai_diagnostic_api(), sitepulse_reset_onboarding() (+2 more)

### Community 179 - "remote-connection.test.cjs"
Cohesion: 0.40
Nodes (5): assert, box(), { browser, jsonResponse }, settings(), test

### Community 180 - "Sitepulse_Monitoring"
Cohesion: 0.18
Nodes (4): Sitepulse_Monitoring, sitepulse_pause_monitoring(), sitepulse_resume_monitoring(), MonitoringTest

### Community 182 - "WordPress.php"
Cohesion: 0.04
Nodes (42): sitepulse_register_route(), get_permalink(), get_the_title(), add_menu_page(), add_option(), add_submenu_page(), delete_metadata(), delete_user_meta() (+34 more)

### Community 183 - "Sitepulse_Page_Tracker"
Cohesion: 0.09
Nodes (3): Sitepulse_Page_Tracker, wp_debug_backtrace_summary(), PageTrackerTest

### Community 184 - "delete_option"
Cohesion: 0.13
Nodes (4): Architecture, Sitepulse_Loader, delete_option(), delete_transient()

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
Nodes (13): Backup warnings, Connecting a site to an account, Dashboard (portal) endpoints, Key rotation, Monthly reports, Roles, Rollouts, Slowdowns and security fixes (+5 more)

### Community 201 - "Remote management"
Cohesion: 0.13
Nodes (13): API → site, Development, Endpoints, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/events`, `POST /api/website/remote/key`, Remote management (+5 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 203 - "add_action"
Cohesion: 0.08
Nodes (6): register_activation_hook(), register_deactivation_hook(), add_action(), add_filter(), is_admin(), Sitepulse_Test_Hook

### Community 206 - "home_url"
Cohesion: 0.17
Nodes (5): Sitepulse_Remote_Health, human_time_diff(), sitepulse_test_site_pages(), admin_url(), home_url()

### Community 211 - "update_option"
Cohesion: 0.09
Nodes (4): CoreAdditionalBehaviorTest, StoredDataWritePathsTest, update_option(), wp_load_alloptions()

### Community 214 - "Sitepulse_Test_Case"
Cohesion: 0.15
Nodes (3): DirectAccessTemplateTest, EasyModeSaveQueriesTest, Sitepulse_Test_Case

### Community 216 - "api_backend.php"
Cohesion: 0.28
Nodes (12): sitepulse_dismiss_getting_started(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events(), sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report(), sitepulse_page_analysis_response() (+4 more)

### Community 220 - "get_current_screen"
Cohesion: 0.18
Nodes (3): HarnessIsolationTest, remove_all_actions(), get_current_screen()

### Community 221 - "RemoteManagement.php"
Cohesion: 0.07
Nodes (14): BackWPup, BackWPup_Cron, BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades (+6 more)

### Community 229 - "api-variants.php"
Cohesion: 0.22
Nodes (5): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes()

### Community 234 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 235 - "Commands"
Cohesion: 0.18
Nodes (11): Backup commands, Backup preset format, Commands, Notifications, Phases, `POST /api/website/remote/command-progress`, `POST /api/website/remote/inventory`, `POST /api/website/remote/safe-update` (+3 more)

### Community 236 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

## Knowledge Gaps
- **269 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+264 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1332 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **132 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Dashboard (portal) endpoints` connect `Dashboard (portal) endpoints` to `Remote management`, `Commands`, `remote-connection.test.cjs`, `home_url`?**
  _High betweenness centrality (0.047) - this node is a cross-community bridge._
- **Why does `settings()` connect `remote-connection.test.cjs` to `Dashboard (portal) endpoints`, `browser.cjs`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `VersionBumpTest`, `Sitepulse_CurLoader`, `get_option`, `RemoteConnectionTest`, `Sitepulse_Easy_Mode`, `RestApiTest`, `Sitepulse_Remote_Activity`, `BackendTest`, `Sitepulse_Remote_Backups`, `Sitepulse_Settings`, `Sitepulse_Backend`, `Sitepulse_Plugin_Profiler`, `LifecycleTest`, `HttpOriginTest`, `RemoteBackupCommandsTest`, `get_transient`, `DiagnosticTemplateCoverageTest`, `ApiServiceTest`, `Sitepulse_Onboarding`, `Sitepulse_Frontend`, `RemoteManagementTest`, `RemoteRecoveryTest`, `Sitepulse_Monitoring`, `do_action`, `Sitepulse_Page_Tracker`, `Sitepulse_Remote_Updates`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `ProfilerCompatibilityTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Remote_Client`, `Sitepulse_Profiler`, `PopulatedTemplateTest`, `AutoloadRestApiTest`, `RecentDashboardTest`, `Sitepulse_Remote_Inventory`, `CoreVariantsTest`, `Sitepulse_Page_Analysis`, `update_option`, `ApiVariantsTest`, `RemoteVariantsTest`, `current_user_can`, `Sitepulse_Utils`, `Sitepulse_Plugin`, `RestCoverageTest`, `TemplateRenderingTest`, `get_current_screen`, `PetAssistantTemplateTest`, `PluginTest`, `Sitepulse_Api_Service`, `StoredDataRobustnessTest`, `TemplateTest`, `BrandingAnalyticsTest`?**
  _High betweenness centrality (0.044) - this node is a cross-community bridge._
- **Are the 265 inferred relationships involving `update_option()` (e.g. with `.make_api_request()` and `.make_vulnerability_api_request()`) actually correct?**
  _`update_option()` has 265 INFERRED edges - model-reasoned connections that need verification._
- **Are the 195 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 195 INFERRED edges - model-reasoned connections that need verification._
- **Are the 91 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 91 INFERRED edges - model-reasoned connections that need verification._
- **Are the 47 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 47 INFERRED edges - model-reasoned connections that need verification._
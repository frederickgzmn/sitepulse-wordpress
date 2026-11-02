# Graph Report - sitepulse  (2026-10-18)

## Corpus Check
- 195 files · ~405,715 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4811 nodes · 11641 edges · 242 communities (109 shown, 133 thin omitted)
- Extraction: 74% EXTRACTED · 26% INFERRED · 0% AMBIGUOUS · INFERRED: 2976 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c3849f77`
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
- CoverageGateTest
- is_wp_error
- BaseComponent
- Sitepulse_Remote_Updates
- Zs
- Dropdown
- Dropdown
- Dropdown
- Tab
- Tab
- home_url
- TemplateFactory
- Tab
- onboarding.js
- Sitepulse_Remote_Client
- Sitepulse_Profiler
- blueprint.json
- Sitepulse_Whitelabel_Service
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
- get_option
- Qn
- ScrollBarHelper
- Toast
- Backdrop
- delete_transient
- Zi
- Ce
- Es
- PluginTest
- RemoteManagementTest
- ApiCoverageTest
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
- Sitepulse_Test_Database
- SitePulse — See What's Powering (or Slowing) Your Site
- RemoteConnectionTest
- Sitepulse_Easy_Mode
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- Sitepulse_Admin_Access
- PresentationWordPress.php
- .connect
- onboarding.test.cjs
- Sitepulse_Remote_Activity
- BackendTest
- ps
- FocusTrap
- Sitepulse_Backend
- Sitepulse_AI_Diagnostic_Cron
- browser
- backend.test.cjs
- Ln
- LifecycleTest
- delete_option
- WP_REST_Request
- Sitepulse_Remote_Management
- coverage.cjs
- browser.cjs
- get_transient
- contains
- DiagnosticTemplateCoverageTest
- remaining-browser-behavior.test.cjs
- js-coverage.cjs
- Sitepulse_Api_Service
- Me
- Automated tests
- WP_Error
- Sitepulse_Remote_Health
- remote-connection.test.cjs
- Sitepulse_Monitoring
- Swipe
- WordPress.php
- Sitepulse_Page_Tracker
- esc_html
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- package.json
- RemoteClientTest
- get_theme_root
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
- RemoteInventoryTest
- CoreVariantsTest
- update_option
- ApiVariantsTest
- RemoteVariantsTest
- Sitepulse_Test_Case
- WP_REST_Response
- api_backend.php
- Sitepulse_Plugin
- CLAUDE.md
- TemplateRenderingTest
- get_current_screen
- Sitepulse_Test_Upgrades
- sitepulse_test_subprocess
- ot
- PetAssistantTemplateTest
- Fi
- .admin_bar_node
- StoredDataRobustnessTest
- TemplateTest
- pi
- .test_every_request_goes_to_the_api_the_setting_names
- AI Diagnostics
- Commands
- Installation
- build-zip.sh
- .cron
- admin-access.js

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 277 edges
2. `get_option()` - 204 edges
3. `Sitepulse_Remote_Management` - 144 edges
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

## Communities (242 total, 133 thin omitted)

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
Nodes (8): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, wp_mail(), current_time(), is_email(), sanitize_email()

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
Cohesion: 0.10
Nodes (3): Sitepulse_Remote_Events, get_bloginfo(), get_file_data()

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
Nodes (7): sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_vulnerabilities_api(), sitepulse_get_memory_info(), sitepulse_request_ai_diagnostic_api(), sitepulse_rest_pre_dispatch(), sitepulse_update_settings(), RestApiTest

### Community 23 - "Sitepulse_Test_Backups"
Cohesion: 0.05
Nodes (12): BackWPup, BackWPup_Cron, BackWPup_JobTypes, BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, Sitepulse_Test_Updraft_Admin, Updraft_Restorer (+4 more)

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
Nodes (5): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters()

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
Cohesion: 0.17
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Onboarding"
Cohesion: 0.06
Nodes (5): Sitepulse_Onboarding, Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest

### Community 50 - "RemoteRecoveryTest"
Cohesion: 0.10
Nodes (3): Sitepulse_Remote_Recovery, RemoteRecoveryTest, wp_load_alloptions()

### Community 51 - "remove"
Cohesion: 0.17
Nodes (3): Ue(), remove(), Ze

### Community 53 - "is_wp_error"
Cohesion: 0.14
Nodes (11): is_wp_error(), sanitize_hex_color(), sanitize_text_field(), wp_generate_password(), wp_json_encode(), wp_rand(), wp_remote_get(), wp_remote_post() (+3 more)

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 55 - "Sitepulse_Remote_Updates"
Cohesion: 0.07
Nodes (8): Sitepulse_Remote_Updates, disk_free_space(), RemoteEventsTest, RemoteUpdatesTest, Automatic_Upgrader_Skin, do_action(), wp_next_scheduled(), wp_unschedule_event()

### Community 62 - "home_url"
Cohesion: 0.19
Nodes (12): add_query_arg(), admin_url(), esc_js(), esc_url_raw(), home_url(), remove_query_arg(), rest_url(), wp_create_nonce() (+4 more)

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 67 - "Sitepulse_Profiler"
Cohesion: 0.05
Nodes (5): Sitepulse_Profiler, ProfilerCompatibilityTest, Sitepulse_Test_Profiler_Callback, ProfilerTest, RecentActivityTest

### Community 68 - "blueprint.json"
Cohesion: 0.12
Nodes (16): Automated Test Setup, WordPress Playground, features, networking, landingPage, meta, author, categories (+8 more)

### Community 69 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.10
Nodes (5): Sitepulse_Whitelabel_Service, PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data(), wp_parse_args()

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
Cohesion: 0.06
Nodes (15): Sitepulse_Remote_Inventory, Update commands, doing_action(), get_core_updates(), _get_cron_array(), get_filesystem_method(), get_site_option(), get_site_transient() (+7 more)

### Community 81 - "easy-mode.js"
Cohesion: 0.13
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.06
Nodes (4): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n()

### Community 83 - "plugin_basename"
Cohesion: 0.10
Nodes (13): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), gethostbyname(), gethostname(), register_activation_hook() (+5 more)

### Community 86 - "current_user_can"
Cohesion: 0.14
Nodes (13): sitepulse_register_route(), RuntimeException, check_admin_referer(), check_ajax_referer(), current_user_can(), register_rest_route(), Sitepulse_Test_Die, Sitepulse_Test_Json_Response (+5 more)

### Community 90 - "RestCoverageTest"
Cohesion: 0.18
Nodes (5): sitepulse_check_memory(), sitepulse_collect_plugin_data(), sitepulse_dismiss_onboarding(), sitepulse_enable_save_queries(), RestCoverageTest

### Community 91 - "get_option"
Cohesion: 0.08
Nodes (5): Sitepulse_Product_Analytics, BrandingAnalyticsTest, absint(), get_option(), sanitize_key()

### Community 96 - "delete_transient"
Cohesion: 0.24
Nodes (7): delete_transient(), remove_action(), wp_clean_plugins_cache(), wp_clean_themes_cache(), wp_remote_head(), wp_update_plugins(), wp_update_themes()

### Community 101 - "RemoteManagementTest"
Cohesion: 0.22
Nodes (3): RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response()

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
Cohesion: 0.16
Nodes (7): Rollouts, PHPUnit\Framework\TestCase, sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 143 - "Sitepulse_Easy_Mode"
Cohesion: 0.07
Nodes (12): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, sitepulse_complete_onboarding(), sitepulse_set_sp_profiler(), sitepulse_set_trackers_disabled_notice(), sitepulse_set_wpslowhttp(), EasyModeTest, GettingStartedTest (+4 more)

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

### Community 150 - "PresentationWordPress.php"
Cohesion: 0.10
Nodes (10): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), wp_date(), wp_strip_all_tags() (+2 more)

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 159 - "browser"
Cohesion: 0.13
Nodes (17): assert, { browser, jsonResponse, flush }, page(), settle(), test, assert, { browser }, localization (+9 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "delete_option"
Cohesion: 0.16
Nodes (3): Architecture, Sitepulse_Loader, delete_option()

### Community 165 - "Sitepulse_Remote_Management"
Cohesion: 0.07
Nodes (6): Sitepulse_Remote_Management, human_time_diff(), site_url(), wp_generate_uuid4(), wp_login_url(), wp_schedule_single_event()

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): ref_node_fs, ref_node_path, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "browser.cjs"
Cohesion: 0.10
Nodes (20): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+12 more)

### Community 168 - "get_transient"
Cohesion: 0.08
Nodes (7): Sitepulse_Cron_Fallback, Sitepulse_Cron_Manager, CronCoverageTest, CronTest, get_transient(), has_action(), set_transient()

### Community 170 - "contains"
Cohesion: 0.10
Nodes (4): contains(), effect$1(), FocusTrap, Modal

### Community 172 - "remaining-browser-behavior.test.cjs"
Cohesion: 0.29
Nodes (5): assert, backend(), { browser, jsonResponse }, memory, test

### Community 173 - "js-coverage.cjs"
Cohesion: 0.13
Nodes (14): ref_node_child_process, context, coverage, { createContext }, { createCoverageMap }, destination, directory, fs (+6 more)

### Community 174 - "Sitepulse_Api_Service"
Cohesion: 0.06
Nodes (6): Sitepulse_Api_Service, ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat(), untrailingslashit(), wp_parse_url()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 177 - "WP_Error"
Cohesion: 0.16
Nodes (7): sitepulse_dismiss_onboarding_notice(), sitepulse_get_curl_stats(), sitepulse_get_plugin_profiler_stats(), sitepulse_get_profiler_stats(), sitepulse_reset_onboarding(), WP_Error, wp_verify_nonce()

### Community 178 - "Sitepulse_Remote_Health"
Cohesion: 0.10
Nodes (6): Sitepulse_Remote_Health, sitepulse_test_site_pages(), maybe_unserialize(), remove_filter(), sitepulse_test_callback_id(), wp_normalize_path()

### Community 179 - "remote-connection.test.cjs"
Cohesion: 0.40
Nodes (5): assert, box(), { browser, jsonResponse }, settings(), test

### Community 182 - "WordPress.php"
Cohesion: 0.07
Nodes (34): get_permalink(), get_the_title(), add_menu_page(), add_option(), add_submenu_page(), delete_metadata(), delete_user_meta(), did_action() (+26 more)

### Community 183 - "Sitepulse_Page_Tracker"
Cohesion: 0.08
Nodes (4): Sitepulse_Page_Tracker, wp_debug_backtrace_summary(), PageTrackerTest, sitepulse_test_post_type()

### Community 184 - "esc_html"
Cohesion: 0.18
Nodes (8): sitepulse_sanitize_param(), submit_button(), wp_nonce_field(), esc_attr(), esc_html(), esc_html_e(), esc_url(), wp_kses_post()

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "package.json"
Cohesion: 0.08
Nodes (24): description, devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom (+16 more)

### Community 189 - "get_theme_root"
Cohesion: 0.17
Nodes (5): get_plugins(), get_theme_root(), is_multisite(), wp_get_theme(), wp_timezone_string()

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "Sitepulse_Test_Filesystem"
Cohesion: 0.13
Nodes (4): Sitepulse_Test_Filesystem, wp_delete_file(), WP_Filesystem(), wp_upload_dir()

### Community 200 - "Dashboard (portal) endpoints"
Cohesion: 0.15
Nodes (13): Backup warnings, Connecting a site to an account, Dashboard (portal) endpoints, Key rotation, Monthly reports, Notifications, `POST /api/website/remote/events`, Roles (+5 more)

### Community 201 - "Remote management"
Cohesion: 0.20
Nodes (9): API → site, Development, Phases, Remote management, Retention, Signing, Site → API, Test vector (+1 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 203 - "add_action"
Cohesion: 0.08
Nodes (5): add_action(), add_filter(), has_filter(), Sitepulse_Test_Hook, wp_get_scheduled_event()

### Community 211 - "update_option"
Cohesion: 0.06
Nodes (5): Sitepulse_Plugin_Profiler, CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataWritePathsTest, update_option()

### Community 214 - "Sitepulse_Test_Case"
Cohesion: 0.15
Nodes (3): EasyModeSaveQueriesTest, HttpOriginTest, Sitepulse_Test_Case

### Community 216 - "api_backend.php"
Cohesion: 0.23
Nodes (14): sitepulse_dismiss_getting_started(), sitepulse_enable_clear_curl_api_events(), sitepulse_enable_clear_load_events(), sitepulse_page_analysis_collect(), sitepulse_page_analysis_delete(), sitepulse_page_analysis_id(), sitepulse_page_analysis_report(), sitepulse_page_analysis_response() (+6 more)

### Community 217 - "Sitepulse_Plugin"
Cohesion: 0.12
Nodes (3): Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest

### Community 218 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

### Community 220 - "get_current_screen"
Cohesion: 0.18
Nodes (3): HarnessIsolationTest, remove_all_actions(), get_current_screen()

### Community 221 - "Sitepulse_Test_Upgrades"
Cohesion: 0.15
Nodes (6): Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader, wc_get_page_permalink()

### Community 224 - "sitepulse_test_subprocess"
Cohesion: 0.17
Nodes (3): DirectAccessTemplateTest, TestCase, sitepulse_test_subprocess()

### Community 229 - ".admin_bar_node"
Cohesion: 0.22
Nodes (4): is_admin(), is_admin_bar_showing(), wp_doing_ajax(), wp_doing_cron()

### Community 234 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 235 - "Commands"
Cohesion: 0.23
Nodes (8): Backup commands, Backup preset format, Commands, `POST /api/website/remote/command-progress`, `POST /api/website/remote/inventory`, `POST /api/website/remote/safe-update`, Safe updates, Updates across sites

### Community 236 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

### Community 238 - ".cron"
Cohesion: 0.25
Nodes (5): Endpoints, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/key`, WP-Cron

## Knowledge Gaps
- **273 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+268 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1350 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **133 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Dashboard (portal) endpoints` connect `Dashboard (portal) endpoints` to `VersionBumpTest`, `Remote management`, `Commands`, `.cron`, `remote-connection.test.cjs`, `get_theme_root`?**
  _High betweenness centrality (0.063) - this node is a cross-community bridge._
- **Why does `settings()` connect `remote-connection.test.cjs` to `Dashboard (portal) endpoints`, `browser`?**
  _High betweenness centrality (0.062) - this node is a cross-community bridge._
- **Why does `browser()` connect `browser` to `backend.test.cjs`, `browser.cjs`, `remaining-browser-behavior.test.cjs`, `remote-connection.test.cjs`, `onboarding.test.cjs`, `page-analysis.test.cjs`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **Are the 273 inferred relationships involving `update_option()` (e.g. with `.save()` and `.make_api_request()`) actually correct?**
  _`update_option()` has 273 INFERRED edges - model-reasoned connections that need verification._
- **Are the 199 inferred relationships involving `get_option()` (e.g. with `.stored()` and `.check_ai_diagnostic_status()`) actually correct?**
  _`get_option()` has 199 INFERRED edges - model-reasoned connections that need verification._
- **Are the 93 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 93 INFERRED edges - model-reasoned connections that need verification._
- **Are the 47 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 47 INFERRED edges - model-reasoned connections that need verification._
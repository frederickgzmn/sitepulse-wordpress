# Graph Report - sitepulse  (2026-10-18)

## Corpus Check
- 188 files · ~398,006 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4730 nodes · 11429 edges · 229 communities (98 shown, 131 thin omitted)
- Extraction: 75% EXTRACTED · 25% INFERRED · 0% AMBIGUOUS · INFERRED: 2880 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `5ca8f5c5`
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
- Sitepulse_Setup
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
- home_url
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
- RemoteInventoryTest
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Page_Analysis
- plugin_basename
- Ss
- ScrollSpy
- Sitepulse_Onboarding
- Sitepulse_Utils
- ts
- Swipe
- RestCoverageTest
- Sitepulse_Product_Analytics
- Qn
- ScrollBarHelper
- Toast
- Backdrop
- delete_transient
- Zi
- Ce
- Es
- PluginTest
- sitepulse_test_remote_response
- current_user_can
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
- CoverageGateTest
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
- Sitepulse_Whitelabel_Service
- ps
- FocusTrap
- Sitepulse_Backend
- update_option
- browser.cjs
- backend.test.cjs
- Ln
- LifecycleTest
- .mkdir
- WP_REST_Request
- Sitepulse_Remote_Management
- coverage.cjs
- ref_node_assert
- get_transient
- contains
- DiagnosticTemplateCoverageTest
- remaining-browser-behavior.test.cjs
- js-coverage.cjs
- get_option
- Me
- Automated tests
- wp_normalize_path
- RemoteManagementTest
- remote-connection.test.cjs
- Sitepulse_Monitoring
- Swipe
- WordPress.php
- Sitepulse_Page_Tracker
- Architecture
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
- apply_filters
- wp_clear_scheduled_hook
- RecentDashboardTest
- Sitepulse_Remote_Health
- CoreVariantsTest
- wp_load_alloptions
- ApiVariantsTest
- RemoteVariantsTest
- Sitepulse_Test_Case
- WP_REST_Response
- disk_free_space
- RemoteManagement.php
- sitepulse_test_subprocess
- ot
- Wi
- TemplateTest
- Ie
- .time
- Commands
- EasyModeSaveQueriesTest

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

## Communities (229 total, 131 thin omitted)

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
Nodes (11): Sitepulse_Error_Handler, ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, wp_mail(), current_time(), esc_html(), esc_html_e() (+3 more)

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
Cohesion: 0.11
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
Cohesion: 0.07
Nodes (6): Sitepulse_Test_Database, BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, Updraft_Restorer, UpdraftPlus

### Community 24 - "SitePulse"
Cohesion: 0.09
Nodes (25): Admin Bar Widget, AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Basic View, Core Web Vitals, Cron Job Management, Developer View (+17 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.04
Nodes (5): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup_Job, UpdraftPlus_Options, trailingslashit()

### Community 28 - "Sitepulse_Settings"
Cohesion: 0.06
Nodes (6): Sitepulse_Settings, ActionSchedulerTest, HarnessIsolationTest, SettingsTest, ActionScheduler_Store, _get_cron_array()

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
Cohesion: 0.16
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Setup"
Cohesion: 0.10
Nodes (3): Sitepulse_Setup, CoreRegressionTest, ProDetectionTest

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 53 - "is_wp_error"
Cohesion: 0.19
Nodes (8): is_wp_error(), wp_generate_password(), wp_json_encode(), wp_rand(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "home_url"
Cohesion: 0.11
Nodes (15): add_query_arg(), admin_url(), esc_js(), esc_url_raw(), home_url(), remove_query_arg(), rest_url(), untrailingslashit() (+7 more)

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
Cohesion: 0.12
Nodes (4): PetAssistantTemplateTest, PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data()

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

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.06
Nodes (5): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n(), sitepulse_test_post_type()

### Community 83 - "plugin_basename"
Cohesion: 0.11
Nodes (14): inc/ helpers, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), gethostbyname(), gethostname(), register_activation_hook() (+6 more)

### Community 86 - "Sitepulse_Onboarding"
Cohesion: 0.08
Nodes (11): Sitepulse_Onboarding, RuntimeException, remove_all_actions(), check_admin_referer(), get_current_screen(), Sitepulse_Test_Die, Sitepulse_Test_Json_Response, Sitepulse_Test_Redirect (+3 more)

### Community 90 - "RestCoverageTest"
Cohesion: 0.17
Nodes (3): sitepulse_enable_save_queries(), sitepulse_sanitize_param(), RestCoverageTest

### Community 91 - "Sitepulse_Product_Analytics"
Cohesion: 0.12
Nodes (4): Sitepulse_Product_Analytics, BrandingAnalyticsTest, absint(), sanitize_key()

### Community 96 - "delete_transient"
Cohesion: 0.17
Nodes (8): Automatic_Upgrader_Skin, delete_transient(), remove_action(), wp_clean_plugins_cache(), wp_clean_themes_cache(), wp_remote_head(), wp_update_plugins(), wp_update_themes()

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 102 - "current_user_can"
Cohesion: 0.23
Nodes (7): sitepulse_register_route(), check_ajax_referer(), current_user_can(), register_rest_route(), sanitize_text_field(), wp_send_json_error(), wp_send_json_success()

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

### Community 139 - "CoverageGateTest"
Cohesion: 0.21
Nodes (3): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), CoverageGateTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 143 - "Sitepulse_Easy_Mode"
Cohesion: 0.07
Nodes (13): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, sitepulse_set_sp_profiler(), sitepulse_set_wpslowhttp(), EasyModeTest, GettingStartedTest, esc_url(), get_current_user_id() (+5 more)

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
Nodes (12): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), human_time_diff(), load_textdomain(), _n(), submit_button() (+4 more)

### Community 151 - "add_action"
Cohesion: 0.07
Nodes (10): Sitepulse_AI_Diagnostic_Cron, add_action(), add_filter(), is_admin(), Sitepulse_Test_Hook, wp_doing_ajax(), wp_doing_cron(), wp_get_scheduled_event() (+2 more)

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 154 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.10
Nodes (3): Sitepulse_Whitelabel_Service, BackendTest, add_menu_page()

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 157 - "Sitepulse_Backend"
Cohesion: 0.07
Nodes (5): Sitepulse_Backend, Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest, TemplateRenderingTest

### Community 158 - "update_option"
Cohesion: 0.04
Nodes (12): Sitepulse_Plugin_Profiler, ApiRegressionTest, CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataRobustnessTest, StoredDataWritePathsTest, sitepulse_test_http_event(), sitepulse_test_recent_stat() (+4 more)

### Community 159 - "browser.cjs"
Cohesion: 0.13
Nodes (17): assert, { browser }, localization, setup(), test, assert, { browser }, localization (+9 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 164 - "WP_REST_Request"
Cohesion: 0.08
Nodes (33): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice() (+25 more)

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
Cohesion: 0.07
Nodes (9): Sitepulse_Cron_Fallback, Sitepulse_Cron_Manager, CronCoverageTest, CronTest, SetupOnboardingTest, get_transient(), has_action(), set_transient() (+1 more)

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
Cohesion: 0.06
Nodes (4): Sitepulse_Api_Service, ApiCoverageTest, ApiServiceTest, get_option()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 179 - "remote-connection.test.cjs"
Cohesion: 0.40
Nodes (5): assert, box(), { browser, jsonResponse }, settings(), test

### Community 182 - "WordPress.php"
Cohesion: 0.04
Nodes (42): Sitepulse_Remote_Inventory, get_permalink(), get_the_title(), wp_strip_all_tags(), sitepulse_test_site_pages(), add_option(), add_submenu_page(), delete_user_meta() (+34 more)

### Community 184 - "Architecture"
Cohesion: 0.24
Nodes (5): Architecture, Commands, Repo workflow, Testing notes, Sitepulse_Loader

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
Cohesion: 0.16
Nodes (13): Backup warnings, Connecting a site to an account, Dashboard (portal) endpoints, Key rotation, Monthly reports, Notifications, Roles, Rollouts (+5 more)

### Community 201 - "Remote management"
Cohesion: 0.15
Nodes (12): API → site, Development, Endpoints, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/key`, Remote management, Retention (+4 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 203 - "apply_filters"
Cohesion: 0.12
Nodes (7): apply_filters(), get_filesystem_method(), has_filter(), remove_filter(), sitepulse_test_callback_id(), wp_get_schedules(), wp_is_file_mod_allowed()

### Community 206 - "Sitepulse_Remote_Health"
Cohesion: 0.20
Nodes (3): Sitepulse_Remote_Health, Updates across sites, maybe_unserialize()

### Community 221 - "RemoteManagement.php"
Cohesion: 0.07
Nodes (14): BackWPup, BackWPup_Cron, BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades (+6 more)

### Community 235 - "Commands"
Cohesion: 0.13
Nodes (13): Backup commands, Backup preset format, Commands, Phases, `POST /api/website/remote/command-progress`, `POST /api/website/remote/events`, `POST /api/website/remote/inventory`, `POST /api/website/remote/safe-update` (+5 more)

## Knowledge Gaps
- **267 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+262 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1329 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **131 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `CoverageGateTest`, `RemoteConnectionTest`, `Sitepulse_Easy_Mode`, `RestApiTest`, `Sitepulse_Remote_Activity`, `Sitepulse_Whitelabel_Service`, `Sitepulse_Remote_Backups`, `Sitepulse_Settings`, `Sitepulse_Backend`, `update_option`, `LifecycleTest`, `.mkdir`, `RemoteBackupCommandsTest`, `get_transient`, `DiagnosticTemplateCoverageTest`, `get_option`, `Sitepulse_Setup`, `Sitepulse_Frontend`, `RemoteManagementTest`, `RemoteRecoveryTest`, `Sitepulse_Monitoring`, `do_action`, `Sitepulse_Page_Tracker`, `Sitepulse_Remote_Updates`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `ProfilerCompatibilityTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Remote_Client`, `Sitepulse_Profiler`, `PopulatedTemplateTest`, `AutoloadRestApiTest`, `RecentDashboardTest`, `RemoteInventoryTest`, `CoreVariantsTest`, `Sitepulse_Page_Analysis`, `ApiVariantsTest`, `RemoteVariantsTest`, `Sitepulse_Onboarding`, `Sitepulse_Utils`, `RestCoverageTest`, `Sitepulse_Product_Analytics`, `PluginTest`, `TemplateTest`, `EasyModeSaveQueriesTest`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Why does `Dashboard (portal) endpoints` connect `Dashboard (portal) endpoints` to `RemoteBackupCommandsTest`, `Remote management`, `Commands`, `Sitepulse_Remote_Health`, `remote-connection.test.cjs`, `home_url`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Why does `settings()` connect `remote-connection.test.cjs` to `Dashboard (portal) endpoints`, `browser.cjs`?**
  _High betweenness centrality (0.042) - this node is a cross-community bridge._
- **Are the 265 inferred relationships involving `update_option()` (e.g. with `.make_api_request()` and `.make_vulnerability_api_request()`) actually correct?**
  _`update_option()` has 265 INFERRED edges - model-reasoned connections that need verification._
- **Are the 195 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 195 INFERRED edges - model-reasoned connections that need verification._
- **Are the 91 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 91 INFERRED edges - model-reasoned connections that need verification._
- **Are the 47 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 47 INFERRED edges - model-reasoned connections that need verification._
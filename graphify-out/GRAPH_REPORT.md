# Graph Report - sitepulse  (2026-10-17)

## Corpus Check
- 186 files · ~381,339 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4571 nodes · 10669 edges · 227 communities (100 shown, 127 thin omitted)
- Extraction: 76% EXTRACTED · 24% INFERRED · 0% AMBIGUOUS · INFERRED: 2561 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `bedd0ae3`
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
- Sitepulse_Setup
- Sitepulse_Test_Local_Filesystem
- Tab
- get_current_screen
- remove
- do_action
- home_url
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
- Sitepulse_Remote_Management
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
- Sitepulse_Page_Analysis
- wp_normalize_path
- Ss
- ScrollSpy
- Sitepulse_Onboarding
- Sitepulse_Utils
- ts
- Swipe
- StoredDataRobustnessTest
- sanitize_text_field
- Qn
- ScrollBarHelper
- Toast
- Backdrop
- Sitepulse_Settings
- Zi
- Ce
- Es
- Sitepulse_Test_Case
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
- Sitepulse_Whitelabel_Service
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
- UpdraftPlus_Options
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
- CoverageGateTest
- .time
- remote-connection.test.cjs
- ApiRegressionTest
- Swipe
- WordPress.php
- Sitepulse_Page_Tracker
- CLAUDE.md
- Sitepulse_Plugin
- OptionalViewIntegrationTest
- devDependencies
- RemoteClientTest
- .test_admin_assets_follow_screen_and_mode_and_notices_are_suppressed_only_on_plugin_pages
- ErrorHandlerVariantsTest
- page-analysis.test.cjs
- Sitepulse_SSL_Health
- pro-loader-boundary.php
- .test_a_theme_that_cannot_be_restored_stays_on_the_new_version
- remote-connection.js
- Dashboard (portal) endpoints
- B
- Sitepulse_Remote_Inventory
- update_user_meta
- RecentDashboardTest
- Sitepulse_Remote_Health
- AI Diagnostics
- ProfilerCompatibilityTest
- Sitepulse_Test_Filesystem
- current_user_can
- is_wp_error
- RemoteManagement.php
- Installation
- ot
- Sitepulse_Test_Database
- Wi
- TemplateTest
- Ie
- BackWPup
- Sitepulse_Remote_Updates
- Remote management

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 234 edges
2. `get_option()` - 181 edges
3. `Sitepulse_Test_WP` - 135 edges
4. `Sitepulse_Remote_Management` - 129 edges
5. `Sitepulse_Api_Service` - 102 edges
6. `Sitepulse_Profiler` - 74 edges
7. `Sitepulse_Remote_Backups` - 71 edges
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

## Communities (227 total, 127 thin omitted)

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
Cohesion: 0.10
Nodes (4): BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, UpdraftPlus

### Community 24 - "SitePulse"
Cohesion: 0.10
Nodes (20): Admin Bar Widget, Basic View, Core Web Vitals, Cron Job Management, Developer View, Email Blocking, Fatal-Error Detection, Health Score System (+12 more)

### Community 25 - "Tooltip"
Cohesion: 0.10
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 26 - "Sitepulse_Remote_Backups"
Cohesion: 0.05
Nodes (3): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup_Job

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
Cohesion: 0.15
Nodes (4): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar, FrontendTest

### Community 47 - "Sitepulse_Setup"
Cohesion: 0.08
Nodes (4): Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest

### Community 50 - "get_current_screen"
Cohesion: 0.10
Nodes (10): HarnessIsolationTest, remove_all_actions(), submit_button(), wp_nonce_field(), esc_attr(), esc_html(), esc_html_e(), esc_url() (+2 more)

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 53 - "home_url"
Cohesion: 0.13
Nodes (14): add_query_arg(), admin_url(), esc_js(), esc_url_raw(), home_url(), remove_query_arg(), rest_url(), untrailingslashit() (+6 more)

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

### Community 82 - "Sitepulse_Page_Analysis"
Cohesion: 0.06
Nodes (4): Sitepulse_Page_Analysis, PageAnalysisTest, number_format_i18n(), sitepulse_test_post_type()

### Community 86 - "Sitepulse_Onboarding"
Cohesion: 0.08
Nodes (4): Sitepulse_Monitoring, Sitepulse_Onboarding, MonitoringTest, delete_option()

### Community 91 - "sanitize_text_field"
Cohesion: 0.09
Nodes (6): Sitepulse_Product_Analytics, sitepulse_sanitize_param(), BrandingAnalyticsTest, absint(), sanitize_key(), sanitize_text_field()

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.06
Nodes (7): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters(), _get_cron_array(), wp_parse_args()

### Community 100 - "Sitepulse_Test_Case"
Cohesion: 0.06
Nodes (5): DirectAccessTemplateTest, EasyModeSaveQueriesTest, HttpOriginTest, PluginTest, Sitepulse_Test_Case

### Community 101 - "RemoteManagementTest"
Cohesion: 0.17
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
Cohesion: 0.17
Nodes (6): PHPUnit\Framework\TestCase, sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), VersionBumpTest

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 143 - "Sitepulse_Easy_Mode"
Cohesion: 0.08
Nodes (5): Sitepulse_Easy_Mode, Sitepulse_Getting_Started, EasyModeTest, GettingStartedTest, get_user_meta()

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
Cohesion: 0.07
Nodes (16): wc_get_page_id(), add_settings_section(), do_settings_sections(), get_locale(), get_permalink(), get_the_title(), load_textdomain(), _n() (+8 more)

### Community 151 - "add_action"
Cohesion: 0.11
Nodes (3): add_action(), add_filter(), Sitepulse_Test_Hook

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 153 - "Sitepulse_Remote_Activity"
Cohesion: 0.07
Nodes (15): inc/ helpers, Sitepulse_Remote_Activity, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), gethostbyname(), gethostname() (+7 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 157 - "Sitepulse_Plugin"
Cohesion: 0.12
Nodes (3): Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest

### Community 158 - "update_option"
Cohesion: 0.06
Nodes (5): Sitepulse_Plugin_Profiler, CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataWritePathsTest, update_option()

### Community 159 - "browser.cjs"
Cohesion: 0.13
Nodes (17): assert, { browser }, localization, setup(), test, assert, { browser }, localization (+9 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 162 - "LifecycleTest"
Cohesion: 0.05
Nodes (6): ApiVariantsTest, CoreVariantsTest, LifecycleTest, RemoteVariantsTest, TestCase, sitepulse_test_subprocess()

### Community 163 - "WP_REST_Request"
Cohesion: 0.08
Nodes (37): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice() (+29 more)

### Community 164 - "RestApiTest"
Cohesion: 0.07
Nodes (4): Sitepulse_AI_Diagnostic_Cron, sitepulse_check_vulnerabilities_api(), RestApiTest, date_i18n()

### Community 166 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): ref_node_fs, ref_node_path, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 167 - "ref_node_assert"
Cohesion: 0.12
Nodes (15): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+7 more)

### Community 168 - "get_transient"
Cohesion: 0.09
Nodes (7): Sitepulse_Cron_Fallback, CronCoverageTest, CronTest, delete_transient(), get_transient(), has_action(), set_transient()

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

### Community 178 - ".time"
Cohesion: 0.07
Nodes (9): Sitepulse_Cron_Manager, human_time_diff(), site_url(), wp_clear_scheduled_hook(), wp_get_scheduled_event(), wp_get_schedules(), wp_schedule_event(), wp_schedule_single_event() (+1 more)

### Community 179 - "remote-connection.test.cjs"
Cohesion: 0.40
Nodes (5): assert, box(), { browser, jsonResponse }, settings(), test

### Community 182 - "WordPress.php"
Cohesion: 0.07
Nodes (34): add_menu_page(), add_option(), add_submenu_page(), delete_metadata(), delete_user_meta(), did_action(), fastcgi_finish_request(), get_num_queries() (+26 more)

### Community 184 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

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
Cohesion: 0.12
Nodes (17): Backup warnings, Connecting a site to an account, Dashboard (portal) endpoints, Development, Key rotation, Monthly reports, Notifications, `POST /api/website/remote/check-in` (+9 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 203 - "Sitepulse_Remote_Inventory"
Cohesion: 0.07
Nodes (15): Sitepulse_Remote_Inventory, disk_free_space(), get_bloginfo(), get_core_updates(), get_filesystem_method(), get_plugins(), get_site_option(), get_site_transient() (+7 more)

### Community 206 - "Sitepulse_Remote_Health"
Cohesion: 0.14
Nodes (4): Sitepulse_Remote_Health, maybe_unserialize(), remove_action(), remove_filter()

### Community 212 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 216 - "Sitepulse_Test_Filesystem"
Cohesion: 0.13
Nodes (4): Sitepulse_Test_Filesystem, wp_delete_file(), WP_Filesystem(), wp_upload_dir()

### Community 219 - "current_user_can"
Cohesion: 0.10
Nodes (18): sitepulse_register_route(), RuntimeException, check_admin_referer(), check_ajax_referer(), current_user_can(), is_admin(), is_admin_bar_showing(), register_rest_route() (+10 more)

### Community 220 - "is_wp_error"
Cohesion: 0.22
Nodes (8): is_wp_error(), wp_generate_password(), wp_json_encode(), wp_rand(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header(), wp_remote_retrieve_response_code()

### Community 221 - "RemoteManagement.php"
Cohesion: 0.12
Nodes (11): Automatic_Upgrader_Skin, BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, sitepulse_test_site_pages(), Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades (+3 more)

### Community 224 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

### Community 233 - "BackWPup"
Cohesion: 0.18
Nodes (3): BackWPup, BackWPup_Cron, trailingslashit()

### Community 234 - "Sitepulse_Remote_Updates"
Cohesion: 0.13
Nodes (5): Sitepulse_Remote_Updates, Architecture, Sitepulse_Loader, wp_doing_cron(), wp_next_scheduled()

### Community 235 - "Remote management"
Cohesion: 0.10
Nodes (17): API → site, Backup commands, Backup preset format, Commands, Endpoints, Phases, `POST /api/website/remote/events`, `POST /api/website/remote/inventory` (+9 more)

## Knowledge Gaps
- **265 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+260 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1304 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **127 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `VersionBumpTest`, `Sitepulse_CurLoader`, `RemoteConnectionTest`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Activity`, `Sitepulse_Whitelabel_Service`, `Sitepulse_Remote_Backups`, `RestCoverageTest`, `Sitepulse_Plugin`, `update_option`, `LifecycleTest`, `RemoteBackupCommandsTest`, `RestApiTest`, `get_transient`, `DiagnosticTemplateCoverageTest`, `get_option`, `Sitepulse_Setup`, `Sitepulse_Frontend`, `get_current_screen`, `ApiRegressionTest`, `do_action`, `Sitepulse_Page_Tracker`, `RemoteUpdatesTest`, `OptionalViewIntegrationTest`, `RemoteClientTest`, `ErrorHandlerVariantsTest`, `Sitepulse_Profiler`, `.test_every_request_goes_to_the_api_the_setting_names`, `AutoloadRestApiTest`, `update_user_meta`, `RecentDashboardTest`, `RemoteInventoryTest`, `Sitepulse_Page_Analysis`, `Sitepulse_Onboarding`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `StoredDataRobustnessTest`, `sanitize_text_field`, `current_user_can`, `Sitepulse_Settings`, `Sitepulse_Test_Database`, `RemoteManagementTest`, `PopulatedTemplateTest`, `TemplateTest`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `Sitepulse_CurLoader`, `Sitepulse_Remote_Events`, `RemoteConnectionTest`, `Sitepulse_Easy_Mode`, `add_action`, `Sitepulse_Test_Backups`, `Sitepulse_Remote_Activity`, `Sitepulse_Remote_Backups`, `RestCoverageTest`, `Sitepulse_Plugin`, `WP_REST_Request`, `RestApiTest`, `RemoteBackupCommandsTest`, `UpdraftPlus_Options`, `get_transient`, `DiagnosticTemplateCoverageTest`, `get_option`, `Sitepulse_Setup`, `Sitepulse_Frontend`, `.time`, `get_current_screen`, `ApiRegressionTest`, `do_action`, `WordPress.php`, `RemoteUpdatesTest`, `Sitepulse_Remote_Management`, `Sitepulse_Profiler`, `.test_every_request_goes_to_the_api_the_setting_names`, `update_user_meta`, `Sitepulse_Page_Analysis`, `Sitepulse_Onboarding`, `Sitepulse_Utils`, `ProfilerCompatibilityTest`, `StoredDataRobustnessTest`, `current_user_can`, `sanitize_text_field`, `is_wp_error`, `Sitepulse_Settings`, `Sitepulse_Test_Database`, `RemoteManagementTest`, `PopulatedTemplateTest`, `Sitepulse_Remote_Updates`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
- **Why does `Dashboard (portal) endpoints` connect `Dashboard (portal) endpoints` to `Remote management`, `remote-connection.test.cjs`, `Sitepulse_Remote_Inventory`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Are the 230 inferred relationships involving `update_option()` (e.g. with `.make_api_request()` and `.make_vulnerability_api_request()`) actually correct?**
  _`update_option()` has 230 INFERRED edges - model-reasoned connections that need verification._
- **Are the 176 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 176 INFERRED edges - model-reasoned connections that need verification._
- **Are the 47 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.queue()` and `.test_ai_poll_refreshes_queue_position_while_remaining_pending()`) actually correct?**
  _`Sitepulse_Test_WP` has 47 INFERRED edges - model-reasoned connections that need verification._
- **Are the 79 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.watch()` and `.backwpup_finished()`) actually correct?**
  _`Sitepulse_Remote_Management` has 79 INFERRED edges - model-reasoned connections that need verification._
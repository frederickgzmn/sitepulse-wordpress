# Graph Report - sitepulse  (2026-10-15)

## Corpus Check
- 176 files · ~357,880 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 19 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 4384 nodes · 9958 edges · 227 communities (99 shown, 128 thin omitted)
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 2242 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `58f32c20`
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
- Sitepulse_Cron_Fallback
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
- Collapse
- contains
- Sitepulse_Remote_Management
- remove
- Sitepulse_Test_WP
- .time
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
- Sitepulse_Whitelabel_Service
- Ss
- ScrollSpy
- has_action
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
- Screen
- SitePulse WordPress Plugin Icon
- SitePulse Logo
- Sitepulse Plugin Icon
- Sitepulse Logo
- PageTrackerTest
- Sitepulse_CurLoader
- DiagnosticTemplateCoverageTest
- SitePulse — See What's Powering (or Slowing) Your Site
- Sitepulse_Cron_Manager
- AutoloadRestApiTest
- Key Features
- Privacy First
- composer.json
- SitePulse Pro
- taste.md
- scripts
- PresentationWordPress.php
- api-variants.php
- onboarding.test.cjs
- Commands
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
- home_url
- admin_url
- WP_REST_Response
- RemoteManagement.php
- add_action
- VersionBumpTest
- RestCoverageTest
- js-coverage.cjs
- ApiServiceTest
- Me
- Automated tests
- Sitepulse_Remote_Client
- coverage.cjs
- CoreVariantsTest
- ApiVariantsTest
- Sitepulse_Remote_Updates
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
- get_option
- sanitize_text_field
- page-analysis.js
- do_action
- B
- Ie
- TemplateRenderingTest
- RecentDashboardTest
- Sitepulse_Remote_Events
- ot
- get_transient
- Sitepulse_Remote_Health
- Sitepulse_Test_Database
- RecentActivityTest
- ProfilerCompatibilityTest
- sitepulse_test_subprocess
- Sitepulse_Test_Filesystem
- RemoteVariantsTest
- .test_a_theme_that_cannot_be_restored_stays_on_the_new_version
- delete_option
- EasyModeSaveQueriesTest
- CLAUDE.md
- Sitepulse_Remote_Inventory
- FrontendTest
- .test_admin_assets_follow_screen_and_mode_and_notices_are_suppressed_only_on_plugin_pages

## God Nodes (most connected - your core abstractions)
1. `update_option()` - 196 edges
2. `get_option()` - 147 edges
3. `Sitepulse_Test_WP` - 130 edges
4. `Sitepulse_Remote_Management` - 90 edges
5. `Sitepulse_Api_Service` - 73 edges
6. `Sitepulse_Profiler` - 73 edges
7. `Sitepulse_Remote_Backups` - 68 edges
8. `Sitepulse_Page_Analysis` - 67 edges
9. `Sitepulse_Test_Case` - 65 edges
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

## Communities (227 total, 128 thin omitted)

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
Nodes (8): Sitepulse_Error_Handler, sitepulse_check_vulnerabilities_api(), ErrorHandlerRecoveryTest, RuntimeException, ErrorHandlerTest, current_time(), is_email(), sanitize_email()

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

### Community 11 - "Sitepulse_Cron_Fallback"
Cohesion: 0.17
Nodes (3): Sitepulse_Cron_Fallback, CronCoverageTest, fastcgi_finish_request()

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
Cohesion: 0.14
Nodes (14): sitepulse_register_route(), RuntimeException, check_admin_referer(), check_ajax_referer(), current_user_can(), register_rest_route(), Sitepulse_Test_Die, Sitepulse_Test_Json_Response (+6 more)

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
Cohesion: 0.04
Nodes (11): Sitepulse_Remote_Backups, RemoteBackupsTest, BackWPup, BackWPup_Job, BackWPup_Option, Sitepulse_Test_Backups, Sitepulse_Test_Backwpup_Destination, UpdraftPlus (+3 more)

### Community 27 - "remove"
Cohesion: 0.09
Nodes (3): Modal, remove(), Toast

### Community 28 - "WordPress.php"
Cohesion: 0.06
Nodes (26): submit_button(), wp_nonce_field(), delete_user_meta(), did_action(), esc_attr(), esc_html(), esc_html_e(), esc_url() (+18 more)

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
Cohesion: 0.23
Nodes (3): Sitepulse_Frontend, FrontendAdminBarTest, Sitepulse_Test_Admin_Bar

### Community 47 - "Sitepulse_Onboarding"
Cohesion: 0.06
Nodes (5): Sitepulse_Onboarding, Sitepulse_Setup, CoreRegressionTest, ProDetectionTest, SetupOnboardingTest

### Community 50 - "Sitepulse_Remote_Management"
Cohesion: 0.11
Nodes (4): Sitepulse_Remote_Management, RemoteManagementTest, sitepulse_test_remote_request_body(), sitepulse_test_remote_response()

### Community 51 - "remove"
Cohesion: 0.12
Nodes (4): Ue(), G, remove(), Ze

### Community 52 - "Sitepulse_Test_WP"
Cohesion: 0.08
Nodes (9): Sitepulse_Api_Service, ApiCoverageTest, ApiRegressionTest, Sitepulse_Test_WP, wp_clean_plugins_cache(), wp_clean_themes_cache(), wp_get_theme(), wp_update_plugins() (+1 more)

### Community 53 - ".time"
Cohesion: 0.08
Nodes (6): human_time_diff(), BackWPup_Cron, _get_cron_array(), wp_clear_scheduled_hook(), wp_schedule_single_event(), wp_unschedule_event()

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 62 - "Sitepulse_Plugin"
Cohesion: 0.12
Nodes (3): Sitepulse_Plugin, BackendCoverageTest, PluginAdditionalBehaviorTest

### Community 65 - "onboarding.js"
Cohesion: 0.31
Nodes (15): bindEvents(), dismissOnboarding(), goToStep(), handleFinish(), handleKeys(), handleNext(), handlePrev(), handleSkip() (+7 more)

### Community 66 - "Sitepulse_Test_Case"
Cohesion: 0.10
Nodes (5): DirectAccessTemplateTest, HarnessIsolationTest, HttpOriginTest, StoredDataRobustnessTest, Sitepulse_Test_Case

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
Cohesion: 0.07
Nodes (4): Sitepulse_Page_Analysis, PageAnalysisTest, wc_get_page_id(), number_format_i18n()

### Community 83 - "Sitepulse_Whitelabel_Service"
Cohesion: 0.09
Nodes (12): inc/ helpers, Sitepulse_Whitelabel_Service, sitepulse_detect_first_position_conflict(), sitepulse_is_first_plugin(), sitepulse_move_to_first_position(), sitepulse_reorder_active_plugins_for_conflict(), add_menu_page(), add_submenu_page() (+4 more)

### Community 90 - "popperGenerator"
Cohesion: 0.15
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "detectOverflow"
Cohesion: 0.20
Nodes (21): detectOverflow(), expandToHashMap(), getBoundingClientRect(), getClientRectFromMixedType(), getClippingParents(), getClippingRect(), getCompositeRect(), getDocumentElement() (+13 more)

### Community 96 - "Sitepulse_Settings"
Cohesion: 0.08
Nodes (5): Sitepulse_Settings, ActionSchedulerTest, SettingsTest, ActionScheduler_Store, apply_filters()

### Community 100 - "PluginTest"
Cohesion: 0.10
Nodes (3): PluginTest, WP_Filesystem(), wp_upload_dir()

### Community 102 - "PopulatedTemplateTest"
Cohesion: 0.09
Nodes (5): PetAssistantTemplateTest, PopulatedTemplateTest, sitepulse_test_seed_template_options(), sitepulse_test_template_data(), TemplateTest

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 105 - "WP_REST_Request"
Cohesion: 0.08
Nodes (36): ArrayAccess, sitepulse_check_ai_diagnostic_status_api(), sitepulse_check_memory(), sitepulse_collect_plugin_data(), sitepulse_complete_onboarding(), sitepulse_dismiss_getting_started(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice() (+28 more)

### Community 106 - "Repository Guidelines"
Cohesion: 0.25
Nodes (7): Build, Test, and Development Commands, Coding Style & Naming Conventions, Commit & Pull Request Guidelines, graphify, Project Structure & Module Organization, Repository Guidelines, Testing Guidelines

### Community 107 - "SitePulse Logo"
Cohesion: 0.50
Nodes (4): Heartbeat Waveform, SitePulse, SitePulse Logo, Status Indicator Dots

### Community 108 - "WordPress Pulse Logo"
Cohesion: 0.67
Nodes (3): Pulse Waveform, WordPress, WordPress Pulse Logo

### Community 110 - "Screen"
Cohesion: 0.29
Nodes (6): api(), run(), sample(), Screen(), wait(), waitForVisit()

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.13
Nodes (14): Configuration, Contributing, FAQ, Installation, License, Measured Footprint, Option 1 — WordPress Playground (no install), Option 2 — Standard install (+6 more)

### Community 142 - "Sitepulse_Cron_Manager"
Cohesion: 0.14
Nodes (4): Sitepulse_Cron_Manager, wp_get_scheduled_event(), wp_get_schedules(), wp_schedule_event()

### Community 143 - "AutoloadRestApiTest"
Cohesion: 0.19
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
Nodes (12): add_settings_section(), date_i18n(), do_settings_sections(), get_locale(), load_textdomain(), _n(), remove_all_actions(), wp_date() (+4 more)

### Community 151 - "api-variants.php"
Cohesion: 0.22
Nodes (5): gethostbyname(), gethostname(), register_activation_hook(), register_deactivation_hook(), wp_convert_hr_to_bytes()

### Community 152 - "onboarding.test.cjs"
Cohesion: 0.20
Nodes (9): analysisI18n, analysisServer(), assert, { browser, jsonResponse }, globals(), i18n, report, setup() (+1 more)

### Community 153 - "Commands"
Cohesion: 0.16
Nodes (10): Backup commands, Backup preset format, Commands, Endpoints, `POST /api/website/remote/check-in`, `POST /api/website/remote/command-result`, `POST /api/website/remote/events`, `POST /api/website/remote/inventory` (+2 more)

### Community 155 - "ps"
Cohesion: 0.07
Nodes (4): d(), ps, qt, vs

### Community 158 - "update_option"
Cohesion: 0.05
Nodes (7): Sitepulse_Plugin_Profiler, Sitepulse_AI_Diagnostic_Cron, CoreAdditionalBehaviorTest, PluginProfilerTest, StoredDataWritePathsTest, add_option(), update_option()

### Community 159 - "browser"
Cohesion: 0.08
Nodes (28): ref_node_assert, ref_node_test, assert, { browser }, test, assert, { browser, jsonResponse }, test (+20 more)

### Community 160 - "backend.test.cjs"
Cohesion: 0.22
Nodes (7): assert, autoloadResponse, { browser, jsonResponse }, loadOptions(), memoryResponse, setup(), test

### Community 163 - "browser.cjs"
Cohesion: 0.17
Nodes (10): ref_node_path, assert, fs, jquery, { JSDOM, VirtualConsole }, path, assert, { browser, jsonResponse } (+2 more)

### Community 165 - "home_url"
Cohesion: 0.10
Nodes (11): get_permalink(), get_the_title(), sitepulse_test_site_pages(), delete_transient(), get_bloginfo(), get_core_updates(), get_post_types(), get_posts() (+3 more)

### Community 166 - "admin_url"
Cohesion: 0.16
Nodes (11): add_query_arg(), admin_url(), esc_js(), esc_url_raw(), get_current_screen(), rest_url(), wp_create_nonce(), wp_enqueue_script() (+3 more)

### Community 168 - "RemoteManagement.php"
Cohesion: 0.13
Nodes (9): BackWPup_JobTypes, Core_Upgrader, Plugin_Upgrader, Sitepulse_Test_Updraft_Admin, Sitepulse_Test_Upgrader, Sitepulse_Test_Upgrades, Theme_Upgrader, updraft_try_include_file() (+1 more)

### Community 170 - "add_action"
Cohesion: 0.11
Nodes (5): register_activation_hook(), register_deactivation_hook(), add_action(), add_filter(), Sitepulse_Test_Hook

### Community 171 - "VersionBumpTest"
Cohesion: 0.10
Nodes (8): PHPUnit\Framework\TestCase, sitepulse_read_php_coverage(), sitepulse_apply_version_bump(), sitepulse_next_version(), sitepulse_plan_version_bump(), sitepulse_version_locations(), CoverageGateTest, VersionBumpTest

### Community 173 - "js-coverage.cjs"
Cohesion: 0.08
Nodes (25): description, engines, node, name, private, version, istanbul-lib-coverage, istanbul-lib-report (+17 more)

### Community 174 - "ApiServiceTest"
Cohesion: 0.10
Nodes (3): ApiServiceTest, sitepulse_test_http_event(), sitepulse_test_recent_stat()

### Community 176 - "Automated tests"
Cohesion: 0.20
Nodes (8): JavaScript, PHP, Regression fixes, Automated tests, JavaScript coverage, Limits and maintenance, PHP coverage, Run

### Community 178 - "coverage.cjs"
Cohesion: 0.17
Nodes (10): istanbul-lib-instrument, ref_node_fs, coverage, { createCoverageMap }, { createInstrumenter }, directory, fs, merge() (+2 more)

### Community 181 - "Sitepulse_Remote_Updates"
Cohesion: 0.15
Nodes (3): Sitepulse_Remote_Updates, Automatic_Upgrader_Skin, wp_doing_cron()

### Community 182 - "is_wp_error"
Cohesion: 0.21
Nodes (9): is_wp_error(), wp_generate_password(), wp_json_encode(), wp_rand(), wp_remote_get(), wp_remote_post(), wp_remote_retrieve_body(), wp_remote_retrieve_header() (+1 more)

### Community 183 - "wp_normalize_path"
Cohesion: 0.15
Nodes (4): wp_debug_backtrace_summary(), get_template(), maybe_unserialize(), wp_normalize_path()

### Community 184 - "Remote management"
Cohesion: 0.17
Nodes (10): API → site, Backup warnings, Dashboard (portal) endpoints, Remote management, Retention, Signing, Site → API, Test vector (+2 more)

### Community 185 - "Sitepulse_Plugin"
Cohesion: 0.25
Nodes (6): Browser assets in assets/, class/ components, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/

### Community 187 - "devDependencies"
Cohesion: 0.29
Nodes (7): devDependencies, istanbul-lib-coverage, istanbul-lib-instrument, istanbul-lib-report, istanbul-reports, jquery, jsdom

### Community 189 - "Sitepulse_Monitoring"
Cohesion: 0.11
Nodes (5): Sitepulse_Monitoring, MonitoringTest, is_admin(), is_admin_bar_showing(), wp_doing_ajax()

### Community 191 - "page-analysis.test.cjs"
Cohesion: 0.18
Nodes (8): jsonResponse(), assert, { browser, jsonResponse }, data, i18n, server(), setup(), test

### Community 198 - "get_option"
Cohesion: 0.09
Nodes (3): Sitepulse_Product_Analytics, BrandingAnalyticsTest, get_option()

### Community 199 - "sanitize_text_field"
Cohesion: 0.12
Nodes (8): sitepulse_sanitize_param(), absint(), remove_query_arg(), sanitize_key(), sanitize_text_field(), untrailingslashit(), wp_parse_url(), wp_unslash()

### Community 200 - "page-analysis.js"
Cohesion: 0.42
Nodes (14): card(), el(), format(), icon(), metric(), renderBreakdown(), row(), renderCallbacks() (+6 more)

### Community 202 - "B"
Cohesion: 0.11
Nodes (3): B, U, x()

### Community 211 - "Sitepulse_Remote_Health"
Cohesion: 0.20
Nodes (3): Sitepulse_Remote_Health, remove_action(), remove_filter()

### Community 219 - "delete_option"
Cohesion: 0.17
Nodes (4): Architecture, Sitepulse_Loader, delete_metadata(), delete_option()

### Community 221 - "CLAUDE.md"
Cohesion: 0.40
Nodes (3): Commands, Repo workflow, Testing notes

### Community 224 - "Sitepulse_Remote_Inventory"
Cohesion: 0.08
Nodes (13): Sitepulse_Remote_Inventory, Phases, Update policy, disk_free_space(), get_filesystem_method(), get_plugins(), get_site_option(), is_multisite() (+5 more)

## Knowledge Gaps
- **260 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+255 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1278 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **128 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Sitepulse_Test_Case` connect `Sitepulse_Test_Case` to `Sitepulse_Error_Handler`, `PageTrackerTest`, `Sitepulse_CurLoader`, `Sitepulse_Cron_Fallback`, `DiagnosticTemplateCoverageTest`, `AutoloadRestApiTest`, `current_user_can`, `Sitepulse_Easy_Mode`, `BackendTest`, `Sitepulse_Remote_Backups`, `update_option`, `LifecycleTest`, `RemoteBackupCommandsTest`, `RestApiTest`, `VersionBumpTest`, `RestCoverageTest`, `ApiServiceTest`, `Sitepulse_Onboarding`, `Sitepulse_Frontend`, `Sitepulse_Remote_Client`, `Sitepulse_Remote_Management`, `CoreVariantsTest`, `ApiVariantsTest`, `Sitepulse_Test_WP`, `RemoteUpdatesTest`, `OptionalViewIntegrationTest`, `Sitepulse_Monitoring`, `ErrorHandlerVariantsTest`, `Sitepulse_Plugin`, `Sitepulse_Profiler`, `get_option`, `do_action`, `TemplateRenderingTest`, `RecentDashboardTest`, `Sitepulse_Page_Analysis`, `Sitepulse_Test_Database`, `RecentActivityTest`, `has_action`, `ProfilerCompatibilityTest`, `Sitepulse_Utils`, `RemoteVariantsTest`, `EasyModeSaveQueriesTest`, `Sitepulse_Settings`, `FrontendTest`, `PluginTest`, `Sitepulse_Page_Tracker`, `PopulatedTemplateTest`?**
  _High betweenness centrality (0.051) - this node is a cross-community bridge._
- **Why does ``POST /api/website/remote/inventory`` connect `Commands` to `Screen`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Why does `update_option()` connect `update_option` to `Sitepulse_Error_Handler`, `PageTrackerTest`, `Sitepulse_CurLoader`, `Sitepulse_Cron_Manager`, `Sitepulse_Easy_Mode`, `Sitepulse_Remote_Backups`, `WordPress.php`, `Sitepulse_Test_Local_Filesystem`, `RemoteBackupCommandsTest`, `home_url`, `RestApiTest`, `add_action`, `RestCoverageTest`, `ApiServiceTest`, `Sitepulse_Onboarding`, `Sitepulse_Frontend`, `Sitepulse_Remote_Management`, `Sitepulse_Test_WP`, `.time`, `is_wp_error`, `Sitepulse_Remote_Updates`, `RemoteUpdatesTest`, `Sitepulse_Monitoring`, `Sitepulse_Plugin`, `Sitepulse_Test_Case`, `Sitepulse_Profiler`, `get_option`, `do_action`, `TemplateRenderingTest`, `Sitepulse_Remote_Events`, `Sitepulse_Page_Analysis`, `Sitepulse_Whitelabel_Service`, `get_transient`, `Sitepulse_Test_Database`, `has_action`, `Sitepulse_Utils`, `ProfilerCompatibilityTest`, `delete_option`, `Sitepulse_Settings`, `Sitepulse_Page_Tracker`, `PopulatedTemplateTest`, `WP_REST_Request`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **Are the 192 inferred relationships involving `update_option()` (e.g. with `.check_ai_diagnostic_status()` and `.make_ai_diagnostic_api_request()`) actually correct?**
  _`update_option()` has 192 INFERRED edges - model-reasoned connections that need verification._
- **Are the 142 inferred relationships involving `get_option()` (e.g. with `.check_ai_diagnostic_status()` and `.check_vulnerabilities()`) actually correct?**
  _`get_option()` has 142 INFERRED edges - model-reasoned connections that need verification._
- **Are the 42 inferred relationships involving `Sitepulse_Test_WP` (e.g. with `.test_ai_poll_refreshes_queue_position_while_remaining_pending()` and `.test_ai_request_handles_corrupt_vulnerability_storage_as_empty_list()`) actually correct?**
  _`Sitepulse_Test_WP` has 42 INFERRED edges - model-reasoned connections that need verification._
- **Are the 55 inferred relationships involving `Sitepulse_Remote_Management` (e.g. with `.backwpup_finished()` and `.provider()`) actually correct?**
  _`Sitepulse_Remote_Management` has 55 INFERRED edges - model-reasoned connections that need verification._
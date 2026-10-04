# Graph Report - sitepulse  (2026-09-25)

## Corpus Check
- 59 files · ~248,392 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 18 file(s) not represented in the graph (top: .css 8, .map 7, (none) 2)

## Summary
- 2663 nodes · 5311 edges · 149 communities (54 shown, 95 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 141 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `adab8e10`
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
- .hide
- Tooltip
- TemplateFactory
- Carousel
- ps
- St
- BaseComponent
- TemplateFactory
- Fi
- bootstrap.bundle.js
- Tooltip
- backend.js
- .hide
- TemplateFactory
- Tooltip
- api_backend.php
- Sitepulse_Backend
- SitePulse
- Popover
- Sitepulse_Api_Service
- Toast
- Sitepulse_Cron_Fallback
- B
- Carousel
- ._queueCallback
- Carousel
- Sitepulse_CurLoader
- Carousel
- Backdrop
- .hide
- getBoundingClientRect
- Modal
- Backdrop
- Offcanvas
- bootstrap.min.js
- be
- Offcanvas
- W
- Offcanvas
- loader.php
- Modal
- remove
- contains
- Qt
- remove
- Sitepulse_Onboarding
- remove
- BaseComponent
- Qi
- Zs
- Dropdown
- Dropdown
- Dropdown
- Tab
- Tab
- Ti
- TemplateFactory
- Tab
- onboarding.js
- Sitepulse_Plugin_Profiler
- Sitepulse_Profiler
- blueprint.json
- addHandler
- BaseComponent
- detectOverflow
- Dropdown
- es
- BaseComponent
- .hide
- ScrollSpy
- Modal
- Sitepulse_Setup
- ScrollSpy
- ScrollSpy
- easy-mode.js
- Sitepulse_Whitelabel_Service
- qt
- Ss
- ScrollSpy
- frontend.js
- Sitepulse_Utils
- ts
- Swipe
- popperGenerator
- getDocumentElement
- Qn
- Offcanvas
- Toast
- Backdrop
- Sitepulse_Settings
- Zi
- Ce
- Es
- remove
- FocusTrap
- an
- FocusTrap
- sitepulse_global.js
- Sitepulse_Product_Analytics
- G
- SitePulse Logo
- WordPress Pulse Logo
- CommandCode
- Repository Guidelines
- SitePulse WordPress Plugin Icon
- SitePulse Logo
- Sitepulse Plugin Icon
- Sitepulse Logo
- Swipe
- SitePulse — See What's Powering (or Slowing) Your Site
- Backdrop
- Sitepulse_Cron_Manager
- AI Diagnostics
- Key Features
- Privacy First
- Installation
- SitePulse Pro
- taste.md

## God Nodes (most connected - your core abstractions)
1. `Sitepulse_Settings` - 42 edges
2. `Tooltip` - 41 edges
3. `Tooltip` - 41 edges
4. `Tooltip` - 41 edges
5. `ps` - 40 edges
6. `Tooltip` - 40 edges
7. `Fi` - 40 edges
8. `Carousel` - 29 edges
9. `St` - 29 edges
10. `Carousel` - 29 edges

## Surprising Connections (you probably didn't know these)
- `Project Structure & Module Organization` --references--> `Sitepulse_Plugin`  [INFERRED]
  AGENTS.md → class/plugin.php
- `AI Diagnostic Reports` --semantically_similar_to--> `AI Diagnostics`  [INFERRED] [semantically similar]
  readme.txt → README.md
- `Google PageSpeed Integration` --semantically_similar_to--> `Google PageSpeed Insights`  [INFERRED] [semantically similar]
  readme.txt → README.md
- `sitepulse_check_vulnerabilities_api()` --calls--> `Sitepulse_Api_Service`  [INFERRED]
  inc/api_backend.php → class/api_service.php
- `sitepulse_dismiss_onboarding()` --calls--> `Sitepulse_Onboarding`  [INFERRED]
  inc/api_backend.php → class/onboarding.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **SitePulse Core Monitoring Features** — readme_hook_level_timing, readme_slow_plugin_profiler, readme_per_page_tracking, readme_external_request_monitoring, readme_php_error_tracking [EXTRACTED 1.00]
- **SitePulse Logo Visual Elements** — assets_img_sitepulse_logo_sitepulse_logo, assets_img_sitepulse_logo_heartbeat_waveform, assets_img_sitepulse_logo_status_dots [INFERRED 0.85]

## Communities (149 total, 95 thin omitted)

### Community 0 - "bootstrap.esm.js"
Cohesion: 0.03
Nodes (72): addHandler(), allowedAttribute(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default (+64 more)

### Community 1 - "bootstrap.bundle.min.js"
Cohesion: 0.07
Nodes (51): be(), bi(), Ce(), ci(), D(), De(), _e(), ei() (+43 more)

### Community 2 - "bootstrap.esm.min.js"
Cohesion: 0.04
Nodes (63): addHandler(), AttachmentMap, bootstrapDelegationHandler(), bootstrapHandler(), customEvents, Data, Default, Default$1 (+55 more)

### Community 3 - "Tooltip"
Cohesion: 0.07
Nodes (3): Collapse, Popover, Tooltip

### Community 6 - "bootstrap.js"
Cohesion: 0.07
Nodes (37): addHandler(), bootstrapDelegationHandler(), bootstrapHandler(), find(), findHandler(), focusableChildren(), get(), getDataAttribute() (+29 more)

### Community 8 - "Tooltip"
Cohesion: 0.08
Nodes (4): findShadowRoot(), noop(), Popover, Tooltip

### Community 9 - "TemplateFactory"
Cohesion: 0.08
Nodes (8): allowedAttribute(), Config, getElement(), getUID(), isElement(), sanitizeHtml(), TemplateFactory, toType()

### Community 11 - "ps"
Cohesion: 0.09
Nodes (3): d(), ps, vs

### Community 13 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 14 - "TemplateFactory"
Cohesion: 0.09
Nodes (5): Config, getElement(), isElement(), TemplateFactory, toType()

### Community 16 - "bootstrap.bundle.js"
Cohesion: 0.07
Nodes (23): distanceAndSkiddingToXY(), getDataAttribute(), getDataAttributes(), getSideOffsets(), hide(), isAnySideFullyClipped(), normalizeData(), normalizeDataKey() (+15 more)

### Community 18 - "backend.js"
Cohesion: 0.08
Nodes (20): animateLine(), basePulseEase(), buildPoints(), checkAIDiagnosticStatus(), escapeHtml(), fetchAutoloadOptions(), _getEaseNameForSpike(), getScoreColor() (+12 more)

### Community 19 - ".hide"
Cohesion: 0.10
Nodes (3): li(), Fs, pi

### Community 20 - "TemplateFactory"
Cohesion: 0.09
Nodes (3): Config, sanitizeHtml(), TemplateFactory

### Community 21 - "Tooltip"
Cohesion: 0.09
Nodes (4): Collapse, findShadowRoot(), noop(), Tooltip

### Community 22 - "api_backend.php"
Cohesion: 0.10
Nodes (21): Sitepulse_Plugin, sitepulse_check_memory(), sitepulse_check_vulnerabilities_api(), sitepulse_dismiss_onboarding(), sitepulse_dismiss_onboarding_notice(), sitepulse_enable_save_queries(), sitepulse_get_autoload_options(), sitepulse_get_curl_stats() (+13 more)

### Community 23 - "Sitepulse_Backend"
Cohesion: 0.07
Nodes (3): Sitepulse_Backend, Sitepulse_Easy_Mode, Sitepulse_Frontend

### Community 24 - "SitePulse"
Cohesion: 0.10
Nodes (20): Admin Bar Widget, Basic View, Core Web Vitals, Cron Job Management, Developer View, Email Blocking, Fatal-Error Detection, Health Score System (+12 more)

### Community 26 - "Sitepulse_Api_Service"
Cohesion: 0.07
Nodes (4): Sitepulse_Api_Service, Sitepulse_AI_Diagnostic_Cron, sitepulse_check_ai_diagnostic_status_api(), sitepulse_request_ai_diagnostic_api()

### Community 29 - "B"
Cohesion: 0.09
Nodes (3): B, U, W

### Community 33 - "Sitepulse_CurLoader"
Cohesion: 0.07
Nodes (3): Sitepulse_CurLoader, Sitepulse_Page_Tracker, sitepulse_enable_clear_curl_api_events()

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

### Community 44 - "W"
Cohesion: 0.09
Nodes (4): getDataAttributes(), H(), Ie, W

### Community 46 - "loader.php"
Cohesion: 0.14
Nodes (10): Browser assets in assets/, class/ components, inc/ helpers, Prebuilt Assets, Sitepulse_Plugin, PHP views in templates/, Translations in languages/, sitepulse_enable_clear_load_events() (+2 more)

### Community 49 - "contains"
Cohesion: 0.19
Nodes (3): contains(), effect$1(), Tab

### Community 51 - "remove"
Cohesion: 0.17
Nodes (3): Ue(), remove(), Ze

### Community 52 - "Sitepulse_Onboarding"
Cohesion: 0.08
Nodes (4): Sitepulse_Onboarding, sitepulse_complete_onboarding(), sitepulse_reset_onboarding(), Sitepulse_Loader

### Community 54 - "BaseComponent"
Cohesion: 0.12
Nodes (5): Alert, BaseComponent, Button, enableDismissTrigger(), isDisabled()

### Community 63 - "TemplateFactory"
Cohesion: 0.07
Nodes (4): Config, FocusTrap, sanitizeHtml(), TemplateFactory

### Community 65 - "onboarding.js"
Cohesion: 0.27
Nodes (17): bindEvents(), completeOnboarding(), dismissOnboarding(), goToStep(), handleFinish(), handleNext(), handlePrev(), handleSkip() (+9 more)

### Community 66 - "Sitepulse_Plugin_Profiler"
Cohesion: 0.11
Nodes (3): Sitepulse_Plugin_Profiler, sitepulse_collect_plugin_data(), sitepulse_get_plugin_profiler_stats()

### Community 68 - "blueprint.json"
Cohesion: 0.12
Nodes (16): Automated Test Setup, WordPress Playground, features, networking, landingPage, meta, author, categories (+8 more)

### Community 69 - "addHandler"
Cohesion: 0.16
Nodes (17): addHandler(), bootstrapDelegationHandler(), bootstrapHandler(), find(), findHandler(), focusableChildren(), getElementEvents(), getTypeEvent() (+9 more)

### Community 70 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 71 - "detectOverflow"
Cohesion: 0.17
Nodes (21): arrow(), computeAutoPlacement(), computeOffsets(), computeStyles(), detectOverflow(), expandToHashMap(), flip(), getAltAxis() (+13 more)

### Community 74 - "BaseComponent"
Cohesion: 0.13
Nodes (3): Alert, BaseComponent, Button

### Community 80 - "ScrollSpy"
Cohesion: 0.18
Nodes (3): getNextActiveElement(), isVisible(), ScrollSpy

### Community 81 - "easy-mode.js"
Cohesion: 0.14
Nodes (3): getScoreColor(), initPetAssistant(), initScoreRings()

### Community 86 - "frontend.js"
Cohesion: 0.32
Nodes (12): getRealTimeMode(), resetModalToDefault(), restoreTracking(), setPageTPLoadActive(), setRealTimeMode(), setSPReportMode(), showCompletionModal(), simulateResourceDetection() (+4 more)

### Community 90 - "popperGenerator"
Cohesion: 0.15
Nodes (11): areValidElements(), debounce(), effect(), get(), mergeByName(), order(), sort(), orderModifiers() (+3 more)

### Community 91 - "getDocumentElement"
Cohesion: 0.27
Nodes (11): getClientRectFromMixedType(), getClippingRect(), getDocumentElement(), getDocumentRect(), getInnerBoundingClientRect(), getUAString(), getViewportRect(), getWindowScroll() (+3 more)

### Community 104 - "sitepulse_global.js"
Cohesion: 0.31
Nodes (5): _getAlertsContainer(), _hideAndRemove(), showAlert(), showConfirm(), showToast()

### Community 107 - "SitePulse Logo"
Cohesion: 0.50
Nodes (4): Heartbeat Waveform, SitePulse, SitePulse Logo, Status Indicator Dots

### Community 108 - "WordPress Pulse Logo"
Cohesion: 0.67
Nodes (3): Pulse Waveform, WordPress, WordPress Pulse Logo

### Community 110 - "Repository Guidelines"
Cohesion: 0.25
Nodes (7): Build, Test, and Development Commands, Coding Style & Naming Conventions, Commit & Pull Request Guidelines, graphify, Project Structure & Module Organization, Repository Guidelines, Testing Guidelines

### Community 140 - "SitePulse — See What's Powering (or Slowing) Your Site"
Cohesion: 0.17
Nodes (11): Configuration, Contributing, FAQ, License, Measured Footprint, Reporting bugs, Requirements, Screenshots (+3 more)

### Community 143 - "AI Diagnostics"
Cohesion: 0.40
Nodes (5): AI Diagnostic Reports, AI Diagnostics, api.sitepulse.me, Google PageSpeed Integration, Google PageSpeed Insights

### Community 144 - "Key Features"
Cohesion: 0.40
Nodes (5): External Request Monitoring, Key Features, Performance Profiling, Site Health & Stability, Smart Experience

### Community 145 - "Privacy First"
Cohesion: 0.67
Nodes (3): Core Monitoring, Optional Cloud Features, Privacy First

### Community 146 - "Installation"
Cohesion: 0.67
Nodes (3): Installation, Option 1 — WordPress Playground (no install), Option 2 — Standard install

### Community 147 - "SitePulse Pro"
Cohesion: 0.67
Nodes (3): SitePulse Pro Waitlist, SitePulse Pro, SQL Query Monitor

## Knowledge Gaps
- **151 isolated node(s):** `elementMap`, `Data`, `DOMContentLoadedCallbacks`, `eventRegistry`, `customEvents` (+146 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 937 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **95 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Tooltip` connect `Tooltip` to `bootstrap.esm.min.js`, `TemplateFactory`, `BaseComponent`, `Modal`, `Popover`, `Dropdown`?**
  _High betweenness centrality (0.011) - this node is a cross-community bridge._
- **Why does `Dropdown` connect `Dropdown` to `bootstrap.esm.js`, `Tooltip`, `TemplateFactory`, `BaseComponent`, `._queueCallback`?**
  _High betweenness centrality (0.009) - this node is a cross-community bridge._
- **Why does `ps` connect `ps` to `bootstrap.bundle.min.js`, `es`, `qt`, `remove`, `Qi`, `B`?**
  _High betweenness centrality (0.008) - this node is a cross-community bridge._
- **Are the 9 inferred relationships involving `Sitepulse_Settings` (e.g. with `.get_website_info()` and `.process_settings_form()`) actually correct?**
  _`Sitepulse_Settings` has 9 INFERRED edges - model-reasoned connections that need verification._
- **What connects `elementMap`, `Data`, `DOMContentLoadedCallbacks` to the rest of the system?**
  _151 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `bootstrap.esm.js` be split into smaller, more focused modules?**
  _Cohesion score 0.03192982456140351 - nodes in this community are weakly interconnected._
- **Should `bootstrap.bundle.min.js` be split into smaller, more focused modules?**
  _Cohesion score 0.0681081081081081 - nodes in this community are weakly interconnected._
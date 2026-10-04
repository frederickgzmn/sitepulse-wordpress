=== SitePulse - Performance Monitor and AI Diagnostics ===
Contributors: nilbug, frederickgzmn
Tags: performance, speed, optimization, monitoring, profiler
Requires at least: 5.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.4.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Find and fix what slows your WordPress site. Real-time performance monitoring, plugin profiling, and external request tracking.

== Description ==

**SitePulse** tells you **exactly what slows your site** and **what to do about it**, directly from your WordPress dashboard.
Activate it, and within seconds you will see which plugins are heavy, which external calls are slow, and how to make your site faster.

### 🚀 Key Features

**🚀Stop Guessing Why Your Site is Slow**
- 🚀 **AI Diagnostic** – Get instant insights into what's slowing down your site with AI-powered diagnostics
- ⚡ **Real-Time Performance Dashboard** – See live load-time metrics, Core Web Vitals, and performance trends at a glance
- 📊 **Health Score System** – Get a composite health score based on load time, TTFB, error rates, plugin count, memory usage, and CPU utilization
- 🎯 **Page-by-Page Performance Tracking** – Identify which pages and posts are slow with detailed performance breakdowns
- 📈 **Trend Analysis** – Track performance metrics over time to spot issues before they become critical

**🔍Identify Heavy Plugins Instantly**
- 🔍 **Slow Plugin Profiler** – Instantly detect which plugins are bottlenecks and how much time they consume
- 🎣 **WordPress Hook Analyzer** – See execution times for every hook to pinpoint slow callbacks
- ⏱️ **Detailed Timing Data** – Get millisecond-level timing information for debugging and optimization
- 🏆 **Performance Rankings** – Sort plugins and hooks by execution time to focus on what matters most

**API & External Service Monitoring**
- 🌐 **API Request Tracker** – Monitor all external API calls (payments, analytics, social media, etc.) with response times
- ⚠️ **Slow API Detection** – Get alerts when third-party services are slowing down your site
- 📍 **URL & Response Code Tracking** – See exactly which endpoints are slow and their HTTP status codes

**Site Health & Stability**
- 🚨 **Automatic Fatal Error Detection** – Get instant email notifications when critical errors occur
- 📋 **Comprehensive Error Logs** – Track all PHP errors, warnings, and notices with automatic cleanup
- 🔄 **Recovery Mode Access** – Safe access to fix critical issues without being locked out of your site
- 💾 **Error Statistics** – View error trends and identify recurring issues

**Server & Resource Management**
- 💾 **Memory Usage Monitoring** – Track server memory consumption and identify memory hogs
- ⚙️ **CPU & System Resource Tracking** – Monitor system resource usage in real-time
- 🔧 **Cron Job Spam Prevention** – Automatically disable excessive scheduled tasks during high-traffic periods
- 📊 **Plugin Count Analysis** – Get recommendations on how many plugins are optimal for your setup

**Smart Features**
- 🎨 **Dual Dashboard Views** – Switch between user-friendly Basic View and advanced Developer View
- 🧠 **AI-Powered Diagnostics** – Get AI recommendations for performance optimization (coming soon)
- 🎯 **Google PageSpeed Integration** – Built-in PageSpeed Insights reports with actionable recommendations
- 📧 **Email Alerts** – Customizable alerts for critical errors with team member management
- ✅ **Smart Onboarding** – Interactive setup wizard guides you through configuration and preferences

**Developer-Friendly**
- 👨‍💻 **Advanced Profiler Data** – Hook-level metrics and detailed execution analysis for developers
- 🔐 **Privacy-First Design** – Runs 100% locally with optional external features you control
- 📱 **Admin Bar Widget** – Quick access to performance metrics from anywhere in WordPress

**Customization & Control**
- 🎚️ **Toggle Features On/Off** – Enable or disable monitoring features as needed
- 🔐 **Optional External API** – Use PageSpeed and AI features completely optional—disable anytime without affecting core monitoring
- 📊 **Custom Reporting** – View detailed reports filtered by date, plugin, hook, or error type
- 🛡️ **Email Blocking** – Block outgoing emails during testing or prevent email spam

**Security**
- 🛡️ **Vulnerability Scanner** – Scan your site for known vulnerabilities and get recommendations for fixing them

### 💎 Core Features are Free Forever
We believe every site owner deserves a fast website. The **Performance Monitoring**, **Plugin Profiler**, **Error Tracking** and all the current features included in this version are **Free Forever**. We will never paywall these essential tools.

### 🚀 Coming Soon: SitePulse Pro
We are developing a Pro version for agencies and power users who need historical data and automation.
* **Ai-Powered Auto-Fixing** (Coming Soon)
* **Access to our portal** (Coming Soon)
* **📤 **CSV & PDF Exports** – Download performance logs and data for deeper analysis and reporting
* **Historical Performance Timelines** (Coming Soon)
* **Advanced SQL Query Monitor** (Coming Soon)
* **Multi-Site Dashboard** – Manage and monitor all websites from a single admin dashboard.(Coming Soon)
* **Performance Alerts & Notifications** – Get notified of performance regressions with detailed change analysis(Coming Soon)
* **PageSpeed Deep Dive** – Full PageSpeed Insights reports with opportunities, diagnostics, and lab data(Coming Soon)
* **Historical Performance Charts** – Track performance metrics over weeks and months with trend analysis(Coming Soon)
* **Opportunities & Recommendations** – Actionable optimization suggestions from PageSpeed with implementation guides(Coming Soon)
* **Custom Reports & Exports** – Generate and download detailed performance reports in multiple formats(Coming Soon)
* **Multi-Page PageSpeed Tracking** – Monitor PageSpeed Insights performance across multiple pages and URLs with individual reports and comparisons(Coming Soon)
* **URL Performance Dashboard** – Track performance metrics for specific URLs, homepage, key landing pages, and custom URLs you select(Coming Soon)
* **Scheduled PageSpeed Audits** – Automatically run PageSpeed Insights on multiple URLs on a schedule to track performance trends(Coming Soon)
* **Comparative Analysis** – Compare PageSpeed scores and metrics across different pages to identify which content needs optimization(Coming Soon)
* **Vulnerability Scanner automatic & Alerts** – Scan your site for known vulnerabilities and get recommendations for fixing them
* **Unlimited and fast and precise AI Diagnostic** – Get instant insights into what's slowing down your site with AI-powered diagnostics
* **And more.

👉 **[Join the Pro Waitlist](https://sitepulse.me/)** – Get early access, exclusive launch pricing, and be among the first to unlock advanced features.

---

== External Services & Data Privacy ==

**SitePulse** uses an external service to provide enhanced features including PageSpeed analysis and AI-powered diagnostics.

### 🔗 External Service Integration
SitePulse communicates with **api.sitepulse.me** (operated by the plugin developer) to provide:
- **PageSpeed Reports** – Google PageSpeed Insights analysis with performance scores, Core Web Vitals metrics, and page screenshots
- **AI Diagnostic Reports** – AI-powered performance analysis and optimization recommendations

### 📊 What Data is Collected?
To provide these enhanced features, SitePulse sends **only performance-related metrics** to the external API:
- Plugin names, versions, and slugs installed on your site
- WordPress hook execution times and memory usage
- API request URLs and response times
- Site domain name
- Performance statistics and load times

### 🔒 What Data is NOT Collected?
SitePulse is designed with privacy in mind and **does NOT collect**:
- ❌ Personal user data (names, emails, passwords, IP addresses)
- ❌ User content (posts, pages, comments, custom data)
- ❌ Database content or sensitive information
- ❌ Customer or visitor information
- ❌ Any personally identifiable information (PII)

### ⏰ Data Transmission Schedule
- You can view what data will be sent in the SitePulse dashboard before transmission
- Transmission only occurs when enhanced features are enabled

### 🔐 Your Control & Privacy
- All data transmission is for the purpose of providing PageSpeed and AI diagnostic features
- You can disable these features at any time from the plugin settings
- The plugin continues to work locally without external features
- No data is sold or shared with third parties

**Service Terms**: By using SitePulse's enhanced features, you acknowledge that performance metrics are sent to api.sitepulse.me for analysis.
**Privacy**: For more information, visit: https://sitepulse.me/privacy/

---

== Installation ==
### Try it in WordPress Playground (No Installation Required)
You can instantly try SitePulse without installing anything using WordPress Playground:

[Try SitePulse Demo](https://playground.wordpress.net/?plugin=sitepulse&login=yes&url=/wp-admin/admin.php?page=wpsp_sitepulse)

This opens a temporary WordPress environment in your browser with SitePulse pre-installed and activated.

### Standard Installation1. Upload the `sitepulse` folder to `/wp-content/plugins/`
2. Activate **SitePulse** from the Plugins screen.
3. Open the **SitePulse → Dashboard** menu to view metrics and configure options.
4. (Optional) Set up scheduled checks using WP-Cron or your hosting panel’s cron jobs.

---

== Frequently Asked Questions ==

= Can I try SitePulse without installing it? =
Yes! You can instantly try SitePulse using WordPress Playground — a browser-based WordPress environment. Click [Try SitePulse Demo](https://playground.wordpress.net/?plugin=sitepulse&login=yes&url=/wp-admin/admin.php?page=wpsp_sitepulse) to launch a temporary WordPress site with SitePulse pre-installed. No account or installation required.

= Does SitePulse slow my site down? =
No. SitePulse is built to be lightweight and runs asynchronously. Profiling is optional and can be toggled off anytime.

= Does SitePulse use external services? =
Yes, but only for optional enhanced features. SitePulse can communicate with api.sitepulse.me to provide PageSpeed reports and AI diagnostics. The core monitoring features work 100% locally without any external connection. Only performance metrics (plugin names, load times, memory usage) are sent — never personal data or user content.

= What data does SitePulse send externally? =
When enhanced features are enabled, SitePulse sends only performance-related data: plugin names/versions, hook execution times, memory usage, and API request metrics. NO personal information, user data, database content, or sensitive information is ever collected or transmitted.

= Can it work with WooCommerce or multisite? =
Yes. SitePulse tracks any page or post type, including WooCommerce product pages and multisite environments.

= Is this a "Lite" version with limited features? =
No. This is the Core version of SitePulse. It is a fully functional, unlimited performance monitor. We are building a separate Pro version for advanced historical data and AI features, but the features you see here today will remain free forever.

= How do I join the Pro waitlist? =
You can sign up to be notified when Pro launches at [sitepulse.me](https://sitepulse.me/).

---

== Screenshots ==
1. Real-time Admin Dashboard – View load-time metrics, Core Web Vitals, and Google PageSpeed Insights reports at a glance
2. Simple Performance Overview – Easily spot what's slowing your site down with a beginner-friendly dashboard view
3. Advanced Settings & Controls – Customize monitoring features, manage error logs, and toggle services on/off for testing and optimization
4. Single-Page Performance Analytics – Identify performance bottlenecks on specific pages and posts with detailed load-time breakdowns
5. Interactive Setup Wizard – Step-by-step onboarding guide to configure monitoring preferences and get started instantly
6. Choose your experience level (Basic or Developer View) during onboarding, switch anytime from dashboard.
7. Developer View – Unlock advanced profiler data, hook-level metrics, and detailed execution analysis for power users
8. Interactive onboarding collects initial performance baseline to establish monitoring baselines.
9. Single-Page Performance Analytics – Monitoring individual page performance in real-time to identify and resolve bottlenecks quickly.
10. Actionable Performance Recommendations – Receive tailored optimization suggestions to quickly identify and resolve bottlenecks

---

== Changelog ==
= 1.4.5 =
* Better reporting
* Cron Fixes

= 1.4.4 =
* Fixed an UI bug
* Improved performance for plugins with hooks

= 1.4.3 =
* Hooks now display the url of the page that triggered it
* Remove sitepulse pro dependencies
* Added early profiler
* Auto increment profiler calls when persistent
* Auto detect active hooks activately consuming resources
* Fixed a bug where the hook report was not showing


= 1.4.2 =
* Hook and api hook bugs fixed


= 1.4.1 =
* Better performance handling
* sitepulse alternative cronjobs management
* Fixes

= 1.4.0 =
* Easy Mode for beginners
* AI Assistant integration
* Fixes

= 1.3.6 =
* Streamlined onboarding wizard from 7 steps to 3 steps for faster setup
* Added performance impact indicators (High Impact, Average, Good) for plugin activity
* Added internal request identification in External Requests reports
* Admin bar compact view mode option for cleaner interface
* Clarified feature naming: "Performance Monitor" and "External Requests tracker"
* Enhanced settings page with email blocking and WP Cron configuration warnings
* Improved deactivation feedback modal

= 1.3.5 =
* Autoload option
* Error report free version
* Cron fails detection
* Feedback from deactivation - Help us to improve

= 1.3.0 =
* Whitelabel support
* AI Diagnostic is here!

= 1.2.8 =
* vulnerabilities scan + fix and improvements

= 1.2.2 =
* Hotfix and cleanup

= 1.2.0 =
* Complete UI redesign with modern, intuitive dashboard interface
* Enhanced plugin load time detection and performance metrics
* Refreshed frontend and backend iconography for better visual clarity
* Improved API request tracking and cron job management
* Comprehensive error handling with fatal error email notifications
* New Basic View for simplified performance overview
* Code optimization and performance improvements
* Added PageSpeed Insights widget integration
* Fixed recovery mode email notifications
* Introduced performance glossary for user education
* Refined button styling with design system guidelines
* Google PageSpeed Insights branding and integration
* Confirmed compatibility with WordPress 6.9

= 1.1.1 =
* Added admin notices to surface important updates and status messages
* Automatically enable Tracker mode when the plugin is activated
* Confirmed compatibility with WordPress 6.8

= 1.1.0 =
* Added frontend tracking interface
* Block cronjobs and outgoing emails
* Load time tracking per page and plugin usage report

= 1.0.0 =
* Initial public release with uptime monitoring, dashboard, and basic profiler

---

== Upgrade Notice ==
= 1.1.1 =
Adds admin notices to surface important updates and status messages, automatically enables Tracker mode on activation so performance tracking starts immediately, and confirms compatibility with WordPress 6.8.

---

== Support ==
Having trouble or have suggestions?  
→ Visit the [SitePulse Support Forum](https://wordpress.org/support/plugin/sitepulse/) or open an issue on GitHub.  
We respond quickly and love feedback that helps improve performance visibility for everyone.

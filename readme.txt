=== Apex Addons for Elementor ===
Contributors: arhamashfaq
Tags: elementor, widgets, animations, forms, header footer
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build interactive Elementor pages with local icon libraries, motion widgets, forms, carousels, scroll effects, and a theme builder.

== Description ==

Apex Addons for Elementor is a collection of complete, configurable widgets and extensions for the free Elementor page builder. Features can be enabled individually from the Apex Addons dashboard so a site only loads what it uses.

The plugin includes:

* Theme Builder for headers, footers, single posts, single pages, archives, WooCommerce templates, popups, and display conditions.
* Form Builder with common and advanced field types, multi-step flows, email, entry storage, redirects, webhooks, uploads, spam protection, and server-side validation.
* Conversational Funnel Builder with Elementor-native guided flows, conditional routes, lead scoring, inline, modal, and floating presentations, email notifications, and a secured lead inbox.
* Quiz Builder with scored questions, score-range outcomes, optional lead gates, saved responses, and complete styling controls.
* Container Carousel, Nested Slider, Classic Scroll Stack, and Horizontal Scroll Section.
* Motion Typography, Text Highlight Reveal, Scroll Marquee, Magnetic Attraction, and Cinematic Background Slideshow.
* Dual Heading, Glass Card, Blob Background, Portfolio Hover Showcase, Flex Accordion, Team Member, Poker Fan Carousel, SVG and Icon, Site Logo, Navigation Menu, Header Search, and post-template widgets.
* Multiple locally hosted open-source icon libraries. No icon CDN request is made on the visitor-facing site.

All features distributed in this plugin are usable without a license key, account, quota, or trial period.

= Requirements =

Elementor must be installed and active. WooCommerce is optional and is only required for WooCommerce template conditions and product widgets.

= Privacy and external services =

Apex Addons for Elementor does not include telemetry, advertising, or usage tracking, and it does not send site or visitor data to the plugin author.

The Form Builder and Conversational Funnel can send submitted fields by email through the WordPress `wp_mail()` system when a site owner configures an email action. Form, funnel, and quiz entries can be stored in the site's WordPress database so authorized administrators can review or export them. A Form Builder webhook sends the configured submission data to the URL explicitly entered by the site owner. File and signature fields can save uploads in the WordPress uploads directory. No data is sent to the plugin author. Site owners are responsible for providing an appropriate privacy notice, lawful basis, retention policy, third-party disclosure, and deletion process when collecting personal data.

The plugin's fonts, icons, CSS, and JavaScript are bundled locally. Elementor and any optional plugins have their own privacy practices.

== Installation ==

1. In WordPress, open Plugins > Add New Plugin > Upload Plugin.
2. Upload the Apex Addons ZIP, install it, and activate it.
3. Install and activate Elementor if it is not already active.
4. Open Apex Addons in the WordPress admin menu and enable the features you need.
5. Edit a page with Elementor and find the widgets under the Apex Addons category.

== Frequently Asked Questions ==

= Does this require Elementor Pro? =

No. The features described in this listing work with the free Elementor plugin. Some optional integrations, such as WooCommerce templates, require their related plugin.

= Does the plugin load icon fonts from a CDN? =

No. Included icon assets are served locally from your WordPress installation.

= Can I disable widgets I do not use? =

Yes. Use the Apex Addons dashboard to enable or disable individual widgets, extensions, backgrounds, and icon libraries.

= Does the Form Builder store entries? =

Only when the site owner selects the Collect Submissions action. Email-only forms are not added to the local submissions inbox.

= Where are third-party licenses documented? =

See `THIRD-PARTY-NOTICES.txt` in the plugin folder. All bundled libraries use GPL-compatible open-source licenses.

= Where is the uncompressed source? =

The submitted plugin contains the readable PHP, JavaScript, and CSS source used by the plugin. Third-party libraries are identified with their project and source links in `THIRD-PARTY-NOTICES.txt`. The release ZIP is assembled by `build-wordpress-org-package.ps1`, which copies runtime files without compiling or obfuscating them.

== Changelog ==

= 1.3.1 =
* Improved Theme Builder rendering and dynamic context support for single posts, archives, and WooCommerce product archives.

= 1.3.0 =
* Added Apex Quiz Builder with ordered screen composition, scored single and multiple choice questions, lead fields, secure server-side scoring, saved responses, accessible navigation, and complete styling controls.
* Added score-based outcomes and optional lead-gated quiz results.

= 1.2.4 =
* Fixed stale Elementor funnel previews and removed automatic content or button fallbacks from blank Step items.

= 1.2.3 =
* Rebuilt the Conversational Funnel as an ordered Step, content, field and button composer.
* Fixed Elementor editor focus stealing and active-step resets during control updates.
* Added distinct accessible radio and checkbox controls plus complete navigation and input focus styling.

= 1.2.2 =
* Rebuilt Conversational Funnel around a Step and Fields workflow so each screen can contain one conversational question or a complete responsive field layout.
* Added per-field desktop, tablet, and mobile widths plus text, email, phone, textarea, select, radio, checkbox, acceptance, number, date, time, HTML, and hidden fields.
* Updated secure validation, conditional routing, lead scoring, lead storage, and frontend rendering for multi-field steps.

= 1.2.1 =
* Added an Elementor-native Conversational Funnel Builder with configurable steps, choices, contact capture, responsive presentation modes, transitions, styling controls, secure server-side validation, notifications, and Funnel Leads storage.
* Removed the separate dashboard funnel-construction workflow so funnel design stays with the Elementor page where it is used.
* Added hardened widget-bound submission verification, required-step validation, rate limiting, honeypot protection, and CSV-safe lead exports.

= 1.2.0 =

* Added the reusable Conversational Funnel Builder and Elementor presentation widget.
* Added inline, floating-launcher, and button-modal funnel modes.
* Added a secured funnel lead inbox, email notifications, CSV export, request limiting, honeypot protection, and server-authoritative validation.
* Added accessible navigation, responsive controls, reusable styling, and upgrade-safe companion extension hooks.

= 1.1.0 =

* Prepared the plugin metadata and package for WordPress.org distribution.
* Removed unavailable-feature placeholders from Free admin and Elementor panels.
* Hardened AJAX input validation, template creation, SVG output, and form handling.
* Added privacy, dependency, and third-party license documentation.
* Reduced the release package by excluding obsolete font formats and development documentation.

=== DevBrothers Simple Translator ===
Contributors: lzolotarev
Tags: translate, translator, translation, language, multilingual
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.0.1
Requires PHP: 7.4
Requires Plugins: devbrothers-admin-panel
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simple and free language switcher based on Google Translate. Shortcodes, widgets, customizable styles.

== Description ==

DevBrothers Simple Translator is a simple and elegant solution for adding instant translation to your WordPress site using Google Translate.

= Key Features =

* **Completely free** - uses free Google Translate Widget
* **No API keys required** - no registration or payment needed
* **5 widget styles** - dropdown, flags, flags+dropdown, text links, buttons
* **Shortcode** - `[devbsitr_translator]` for insertion anywhere
* **PHP function** - `devbsitr_translator()` for insertion in theme code
* **Floating widget** - fixed in corner of screen
* **Language configuration** - choose needed languages from 10 available
* **Instant translation** - page translation "on the fly" in browser
* **Hide Google toolbar** - clean look without Google branding
* **DevBrothers integration** - unified admin panel
* **Responsive design** - works on all devices

= Available Languages =

* English
* Deutsch (German)
* Français (French)
* Español (Spanish)
* Italiano (Italian)
* Português (Portuguese)
* 中文 (Chinese)
* 日本語 (Japanese)
* 한국어 (Korean)
* العربية (Arabic)

= Widget Styles =

1. **Dropdown** - classic dropdown list
2. **Flags** - country flags for switching
3. **Flags + Dropdown** - flags in dropdown list
4. **Text Links** - text links (RU | EN | DE)
5. **Buttons** - stylish buttons

= Usage =

**Shortcode:**
`[devbsitr_translator]`

**With parameters:**
`[devbsitr_translator style="flags" languages="en,de,fr"]`

**PHP code:**
`<?php devbsitr_translator(); ?>`

**With parameters:**
`<?php devbsitr_translator(['style' => 'text_links']); ?>`

= Technical Features =

* Uses official Google Translate Widget
* No API key required
* Does not load server (translation in browser)
* Saves selected language in localStorage
* Automatically restores language on next visit
* Completely free without limits

== Installation ==

= Requirements =

1. DevBrothers Admin Panel (base plugin)

= Installation =

1. Install DevBrothers Admin Panel
2. Upload the `devbrothers-simple-translator` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu
4. Go to DevBrothers → Simple Translator to configure

= Configuration =

1. Select the main site language (Russian/English)
2. Mark available languages for translation
3. Choose widget style
4. (Optional) Enable floating widget
5. Insert shortcode `[devbsitr_translator]` or PHP code `devbsitr_translator()` in desired location

== External services ==

This plugin integrates with Google Translate service to provide instant website translation functionality.

The plugin loads the Google Translate JavaScript library from translate.google.com to enable real-time translation of website content in the user's browser.

The following data is sent to Google Translate:
* Page content when a user selects a different language
* The selected language preference
* Browser and device information (as part of standard HTTP requests)

Translation is performed "on the fly" in the user's browser. The plugin does not create separate pages for each language and translations are not indexed by search engines.

This service is provided by Google LLC: Terms of Service (https://policies.google.com/terms), Privacy Policy (https://policies.google.com/privacy).

Note: Google Translate may collect anonymous usage statistics according to Google's privacy policy. The plugin uses the free Google Translate Widget which does not require API keys or registration.

== Frequently Asked Questions ==

= Is a Google API key needed? =

No! The plugin uses the free Google Translate Widget, which does not require an API key, registration, or payment.

= Are there limits on translations? =

No limits. Google Translate Widget is free and works without restrictions.

= Are separate pages created for languages? =

No. Translation happens "on the fly" in the user's browser. This is not an SEO-friendly solution.

= Are translations indexed by search engines? =

No. For SEO-friendly translations, other solutions are needed (such as WPML, Polylang).

= Can I add other languages? =

Yes, 10 popular languages are available in settings. To add others, please contact us.

= Does it work with WooCommerce? =

Yes, it translates all page content, including WooCommerce products.

= How do I insert it into theme code? =

Add to header.php, footer.php, or any other theme file:
`<?php if (function_exists('devbsitr_translator')) devbsitr_translator(); ?>`

= Can I customize styles? =

Yes, you can add your own CSS styles for the `.devbsitr-widget` class.

== Screenshots ==

1. Plugin settings in DevBrothers Admin Panel
2. Dropdown style switcher
3. Flags style with country flags
4. Text Links style (RU | EN | DE)
5. Buttons style
6. Floating widget in corner of screen

== Changelog ==

= 1.0.1 =
* Updated code for WordPress 7.0 compatibility
* Security and performance improvements


= 1.0.0 =
* Initial release
* 5 widget styles
* Shortcode and PHP function
* Floating widget
* 10 available languages
* Integration with DevBrothers Admin Panel
* Hide Google toolbar
* localStorage for language saving

== Upgrade Notice ==

= 1.0.0 =
Initial release of DevBrothers Simple Translator plugin.

== Additional Info ==

= Support =

support@devbrothers.ru

= Technology =

The plugin uses Google Translate Element API - the official free widget from Google for translating websites.

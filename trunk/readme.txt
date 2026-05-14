=== BodyMetric BMI Calculator ===
Contributors: bodymetriccalculator
Tags: bmi calculator, bmi, calculator, health, shortcode
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

BodyMetric BMI Calculator adds a lightweight, accessible body mass index calculator to any post or page using a shortcode.

== Description ==

BodyMetric BMI Calculator is a free shortcode-based plugin for WordPress site owners who want a clean BMI calculator without external dependencies, tracking, or hidden backlinks.

The frontend uses a modern card layout with a metric/imperial toggle, live result updates as visitors type, and a clear BMI status display with color-coded categories.

Features include:

* Shortcode support with configurable unit, theme, and title attributes.
* Metric and imperial BMI calculation in the browser with instant feedback.
* Single-card interface with live score updates and category badge output.
* Custom color controls with safe per-site defaults and optional per-shortcode primary color override.
* Optional editable credit link with placement controls and no hidden backlinks.
* FAQ schema support and WebApplication schema support.
* Responsive, accessible frontend with keyboard-friendly unit controls.
* No external requests, no tracking, no hidden backlinks, and no personal data collection.

Use shortcode examples:

* `[bodybmca_bmi_calculator]`
* `[bmi_calculator]` (legacy alias)
* `[bodybmca_bmi_calculator unit="metric"]`
* `[bodybmca_bmi_calculator unit="imperial"]`
* `[bodybmca_bmi_calculator theme="modern"]`
* `[bodybmca_bmi_calculator theme="minimal"]`
* `[bodybmca_bmi_calculator title="Check Your BMI"]`
* `[bodybmca_bmi_calculator primary_color="#2563eb"]`
* `[bodybmca_bmi_calculator show_credit="false"]`
* `[bodybmca_bmi_calculator show_schema="true"]`

== Installation ==

1. Upload the `bodymetric-bmi-calculator` folder to the `/wp-content/plugins/` directory, or upload the plugin zip from the WordPress admin area.
2. Activate the plugin through the `Plugins` screen in WordPress.
3. Go to `Settings -> BodyMetric BMI Calculator` to adjust defaults.
4. Add the `[bodybmca_bmi_calculator]` shortcode to any post or page. The legacy alias `[bmi_calculator]` is also supported.

== Frequently Asked Questions ==

= Does the plugin collect personal data? =

No. The calculator runs in the browser and does not store or transmit user measurements.

= Does the plugin add backlinks automatically? =

No. The credit link is disabled by default and only appears as a visible link if a site owner enables it in settings. The plugin does not add hidden backlinks or invisible links.

= Can I use imperial or metric units? =

Yes. You can set a default in settings and visitors can switch units directly in the calculator interface.

= Which shortcode should I use? =

Use `[bodybmca_bmi_calculator]` for the prefixed shortcode. The simpler `[bmi_calculator]` shortcode is also supported as a backward-compatible alias.

= What inputs does the calculator use? =

Metric mode uses height in centimeters and weight in kilograms. Imperial mode uses height in inches and weight in pounds.

= Does the result update automatically? =

Yes. The BMI score and category update automatically as visitors enter valid values.

= Can I customize the calculator colors? =

Yes. The settings page includes scoped color controls for the frontend calculator card, text, borders, and result states.

= Does the plugin support structured data? =

Yes. You can enable FAQ schema and WebApplication schema in the settings page. Schema is only output on pages where the calculator shortcode is rendered.

== Screenshots ==

1. Default BMI calculator displayed on the frontend with metric mode selected.
2. Imperial mode calculator with imperial inputs.
3. Admin settings page with custom color controls.
4. Admin settings page with optional credit link and schema markup settings.

== Changelog ==

= 1.1.0 =

* Added custom color controls using safe CSS variables.
* Added editable visible credit link settings with placement options and new-tab control.
* Added FAQ schema and WebApplication schema output when the shortcode is present.
* Added shortcode overrides for `primary_color`, `show_credit`, and `show_schema`.
* Added uninstall cleanup for plugin settings.

= 1.0.0 =

* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release of BodyMetric BMI Calculator.

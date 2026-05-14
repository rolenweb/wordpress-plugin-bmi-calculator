<?php
/**
 * Plugin Name:       BodyMetric BMI Calculator
 * Plugin URI:        https://bodymetriccalculator.com/bmi-calculator
 * Description:       Add a lightweight BMI calculator to any post or page with a shortcode.
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            BodyMetricCalculator.com
 * Author URI:        https://bodymetriccalculator.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bodymetric-bmi-calculator
 * Domain Path:       /languages
 *
 * @package BodyMetricBmiCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BODYBMCA_VERSION', '1.2.0' );
define( 'BODYBMCA_PLUGIN_FILE', __FILE__ );
define( 'BODYBMCA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BODYBMCA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BODYBMCA_TEXT_DOMAIN', 'bodymetric-bmi-calculator' );

require_once BODYBMCA_PLUGIN_DIR . 'includes/class-bodybmca-assets.php';
require_once BODYBMCA_PLUGIN_DIR . 'includes/class-bodybmca-settings.php';
require_once BODYBMCA_PLUGIN_DIR . 'includes/class-bodybmca-schema.php';
require_once BODYBMCA_PLUGIN_DIR . 'includes/class-bodybmca-shortcode.php';
require_once BODYBMCA_PLUGIN_DIR . 'includes/class-bodybmca-plugin.php';

/**
 * Placeholder premium check for future expansion.
 *
 * @return bool
 */
function bodybmca_is_premium() {
	return (bool) apply_filters( 'bodybmca_is_premium', false );
}

/**
 * Bootstrap the plugin.
 *
 * @return BODYBMCA_Plugin
 */
function bodybmca_init_plugin() {
	return BODYBMCA_Plugin::get_instance();
}

add_action( 'plugins_loaded', 'bodybmca_init_plugin' );

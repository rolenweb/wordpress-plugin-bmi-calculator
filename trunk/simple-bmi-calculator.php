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
 * Text Domain:       body-metric-bmi-calculator
 * Domain Path:       /languages
 *
 * @package SimpleBmiCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SBC_VERSION', '1.2.0' );
define( 'SBC_PLUGIN_FILE', __FILE__ );
define( 'SBC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SBC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once SBC_PLUGIN_DIR . 'includes/class-sbc-assets.php';
require_once SBC_PLUGIN_DIR . 'includes/class-sbc-settings.php';
require_once SBC_PLUGIN_DIR . 'includes/class-sbc-schema.php';
require_once SBC_PLUGIN_DIR . 'includes/class-sbc-shortcode.php';
require_once SBC_PLUGIN_DIR . 'includes/class-sbc-plugin.php';

/**
 * Placeholder premium check for future expansion.
 *
 * @return bool
 */
function sbc_is_premium() {
	return (bool) apply_filters( 'sbc_is_premium', false );
}

/**
 * Bootstrap the plugin.
 *
 * @return SBC_Plugin
 */
function sbc_init_plugin() {
	return SBC_Plugin::get_instance();
}

add_action( 'plugins_loaded', 'sbc_init_plugin' );

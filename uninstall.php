<?php
/**
 * Uninstall cleanup.
 *
 * @package SimpleBmiCalculator
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'simple_bmi_calculator_options' );

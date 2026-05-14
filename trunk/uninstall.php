<?php
/**
 * Uninstall cleanup.
 *
 * @package BodyMetricBmiCalculator
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bodybmca_options' );
delete_option( 'bodymetric_bmi_calculator_options' );
delete_option( 'simple_bmi_calculator_options' );

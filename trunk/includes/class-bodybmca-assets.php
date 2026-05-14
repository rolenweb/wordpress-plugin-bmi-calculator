<?php
/**
 * Asset handling.
 *
 * @package BodyMetricBmiCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register and enqueue frontend assets.
 */
class BODYBMCA_Assets {

	/**
	 * Script handle.
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'bodybmca-frontend-script';

	/**
	 * Style handle.
	 *
	 * @var string
	 */
	const STYLE_HANDLE = 'bodybmca-frontend-style';

	/**
	 * Prevent duplicate localization.
	 *
	 * @var bool
	 */
	private $localized = false;

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Register assets for later enqueue.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			self::STYLE_HANDLE,
			BODYBMCA_PLUGIN_URL . 'assets/css/bodymetric-bmi-calculator.css',
			array(),
			BODYBMCA_VERSION
		);

		wp_register_script(
			self::SCRIPT_HANDLE,
			BODYBMCA_PLUGIN_URL . 'assets/js/bodymetric-bmi-calculator.js',
			array(),
			BODYBMCA_VERSION,
			true
		);
	}

	/**
	 * Enqueue assets and localize script data.
	 *
	 * @param array $config Frontend configuration.
	 * @return void
	 */
	public function enqueue_assets( $config ) {
		if ( ! wp_style_is( self::STYLE_HANDLE, 'registered' ) || ! wp_script_is( self::SCRIPT_HANDLE, 'registered' ) ) {
			$this->register_assets();
		}

		wp_enqueue_style( self::STYLE_HANDLE );
		wp_enqueue_script( self::SCRIPT_HANDLE );

		if ( $this->localized ) {
			return;
		}

		wp_localize_script(
			self::SCRIPT_HANDLE,
			'bodybmcaData',
			array(
				'i18n' => array(
					'errorMetric'  => esc_html__( 'Enter valid height in centimeters and weight in kilograms.', 'bodymetric-bmi-calculator' ),
					'errorImperial' => esc_html__( 'Enter valid height in feet and inches, and weight in pounds.', 'bodymetric-bmi-calculator' ),
					'underweight'  => esc_html__( 'Underweight', 'bodymetric-bmi-calculator' ),
					'normalWeight' => esc_html__( 'Normal Weight', 'bodymetric-bmi-calculator' ),
					'overweight'   => esc_html__( 'Overweight', 'bodymetric-bmi-calculator' ),
					'obesity'      => esc_html__( 'Obesity', 'bodymetric-bmi-calculator' ),
					'bmiScore'     => esc_html__( 'BMI Score', 'bodymetric-bmi-calculator' ),
					'rangePrefix'  => esc_html__( 'Reference range:', 'bodymetric-bmi-calculator' ),
				),
			)
		);

		$this->localized = true;
	}
}

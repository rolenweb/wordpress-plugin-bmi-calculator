<?php
/**
 * Asset handling.
 *
 * @package SimpleBmiCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register and enqueue frontend assets.
 */
class SBC_Assets {

	/**
	 * Script handle.
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'simple-bmi-calculator';

	/**
	 * Style handle.
	 *
	 * @var string
	 */
	const STYLE_HANDLE = 'simple-bmi-calculator';

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
			SBC_PLUGIN_URL . 'assets/css/simple-bmi-calculator.css',
			array(),
			SBC_VERSION
		);

		wp_register_script(
			self::SCRIPT_HANDLE,
			SBC_PLUGIN_URL . 'assets/js/simple-bmi-calculator.js',
			array(),
			SBC_VERSION,
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
			'sbcCalculatorData',
			array(
				'i18n' => array(
					'errorMetric'  => esc_html__( 'Enter valid height in centimeters and weight in kilograms.', 'simple-bmi-calculator' ),
					'errorImperial' => esc_html__( 'Enter valid height in feet and inches, and weight in pounds.', 'simple-bmi-calculator' ),
					'underweight'  => esc_html__( 'Underweight', 'simple-bmi-calculator' ),
					'normalWeight' => esc_html__( 'Normal Weight', 'simple-bmi-calculator' ),
					'overweight'   => esc_html__( 'Overweight', 'simple-bmi-calculator' ),
					'obesity'      => esc_html__( 'Obesity', 'simple-bmi-calculator' ),
					'bmiScore'     => esc_html__( 'BMI Score', 'simple-bmi-calculator' ),
					'rangePrefix'  => esc_html__( 'Reference range:', 'simple-bmi-calculator' ),
				),
			)
		);

		$this->localized = true;
	}
}

<?php
/**
 * Main plugin class.
 *
 * @package SimpleBmiCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin bootstrap.
 */
class SBC_Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var SBC_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Assets handler.
	 *
	 * @var SBC_Assets
	 */
	private $assets;

	/**
	 * Settings handler.
	 *
	 * @var SBC_Settings
	 */
	private $settings;

	/**
	 * Shortcode handler.
	 *
	 * @var SBC_Shortcode
	 */
	private $shortcode;

	/**
	 * Get singleton instance.
	 *
	 * @return SBC_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_textdomain();

		$this->assets    = new SBC_Assets();
		$this->settings  = new SBC_Settings();
		$this->shortcode = new SBC_Shortcode( $this->assets, $this->settings );

		$this->settings->init();
		$this->assets->init();
		$this->shortcode->init();
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	private function load_textdomain() {
		load_plugin_textdomain(
			'simple-bmi-calculator',
			false,
			dirname( plugin_basename( SBC_PLUGIN_FILE ) ) . '/languages'
		);
	}
}

<?php
/**
 * Main plugin class.
 *
 * @package BodyMetricBmiCalculator
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
	 * Schema handler.
	 *
	 * @var SBC_Schema
	 */
	private $schema;

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
		$this->schema    = new SBC_Schema( $this->settings );
		$this->shortcode = new SBC_Shortcode( $this->assets, $this->settings, $this->schema );

		$this->settings->init();
		$this->assets->init();
		$this->schema->init();
		$this->shortcode->init();
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	private function load_textdomain() {
		load_plugin_textdomain(
			'bodymetric-bmi-calculator',
			false,
			dirname( plugin_basename( SBC_PLUGIN_FILE ) ) . '/languages'
		);
	}
}

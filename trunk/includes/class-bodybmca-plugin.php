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
class BODYBMCA_Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var BODYBMCA_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Assets handler.
	 *
	 * @var BODYBMCA_Assets
	 */
	private $assets;

	/**
	 * Settings handler.
	 *
	 * @var BODYBMCA_Settings
	 */
	private $settings;

	/**
	 * Schema handler.
	 *
	 * @var BODYBMCA_Schema
	 */
	private $schema;

	/**
	 * Shortcode handler.
	 *
	 * @var BODYBMCA_Shortcode
	 */
	private $shortcode;

	/**
	 * Get singleton instance.
	 *
	 * @return BODYBMCA_Plugin
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

		$this->assets    = new BODYBMCA_Assets();
		$this->settings  = new BODYBMCA_Settings();
		$this->schema    = new BODYBMCA_Schema( $this->settings );
		$this->shortcode = new BODYBMCA_Shortcode( $this->assets, $this->settings, $this->schema );

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
			BODYBMCA_TEXT_DOMAIN,
			false,
			dirname( plugin_basename( BODYBMCA_PLUGIN_FILE ) ) . '/languages'
		);
	}
}

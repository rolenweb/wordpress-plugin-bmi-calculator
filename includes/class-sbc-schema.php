<?php
/**
 * Schema output handling.
 *
 * @package SimpleBmiCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render structured data when the shortcode is present.
 */
class SBC_Schema {

	/**
	 * Settings handler.
	 *
	 * @var SBC_Settings
	 */
	private $settings;

	/**
	 * Whether the shortcode was rendered on the current request.
	 *
	 * @var bool
	 */
	private $has_shortcode = false;

	/**
	 * Whether FAQ schema should be output.
	 *
	 * @var bool
	 */
	private $should_output_faq = false;

	/**
	 * Whether calculator schema should be output.
	 *
	 * @var bool
	 */
	private $should_output_calculator = false;

	/**
	 * Constructor.
	 *
	 * @param SBC_Settings $settings Settings handler.
	 */
	public function __construct( SBC_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_footer', array( $this, 'render_schema' ), 20 );
	}

	/**
	 * Register a shortcode instance for the current page.
	 *
	 * @param null|bool $schema_override Optional shortcode override.
	 * @return void
	 */
	public function register_shortcode_instance( $schema_override = null ) {
		$options = $this->settings->get_options();

		$this->has_shortcode = true;

		if ( false === $schema_override ) {
			return;
		}

		$enable_faq_schema = true === $schema_override ? true : ! empty( $options['enable_faq_schema'] );
		$enable_app_schema = true === $schema_override ? true : ! empty( $options['enable_calculator_schema'] );

		if ( $enable_faq_schema && ! empty( $this->get_faq_items() ) ) {
			$this->should_output_faq = true;
		}

		if ( $enable_app_schema ) {
			$this->should_output_calculator = true;
		}
	}

	/**
	 * Output JSON-LD once per page when needed.
	 *
	 * @return void
	 */
	public function render_schema() {
		if ( ! $this->has_shortcode ) {
			return;
		}

		if ( $this->should_output_faq ) {
			$faq_items = $this->get_faq_items();

			if ( ! empty( $faq_items ) ) {
				$this->print_schema(
					array(
						'@context'    => 'https://schema.org',
						'@type'       => 'FAQPage',
						'mainEntity'  => $faq_items,
					)
				);
			}
		}

		if ( $this->should_output_calculator ) {
			$this->print_schema( $this->get_calculator_schema() );
		}
	}

	/**
	 * Print a JSON-LD script tag.
	 *
	 * @param array $schema Schema payload.
	 * @return void
	 */
	private function print_schema( $schema ) {
		if ( empty( $schema ) ) {
			return;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE ) . '</script>';
	}

	/**
	 * Build calculator schema payload.
	 *
	 * @return array
	 */
	private function get_calculator_schema() {
		$name = apply_filters( 'sbc_schema_name', __( 'Simple BMI Calculator', 'simple-bmi-calculator' ) );
		$description = apply_filters( 'sbc_schema_description', __( 'A free BMI calculator for WordPress websites.', 'simple-bmi-calculator' ) );

		return array(
			'@context'            => 'https://schema.org',
			'@type'               => 'WebApplication',
			'name'                => sanitize_text_field( $name ),
			'applicationCategory' => 'HealthApplication',
			'operatingSystem'     => 'Any',
			'description'         => sanitize_text_field( $description ),
			'offers'              => array(
				'@type'         => 'Offer',
				'price'         => '0',
				'priceCurrency' => 'USD',
			),
		);
	}

	/**
	 * Build valid FAQ items.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_faq_items() {
		$options = $this->settings->get_options();
		$items   = array();

		for ( $index = 1; $index <= 3; $index++ ) {
			$question = isset( $options[ 'faq_question_' . $index ] ) ? trim( (string) $options[ 'faq_question_' . $index ] ) : '';
			$answer   = isset( $options[ 'faq_answer_' . $index ] ) ? trim( (string) $options[ 'faq_answer_' . $index ] ) : '';

			if ( '' === $question || '' === $answer ) {
				continue;
			}

			$items[] = array(
				'@type'          => 'Question',
				'name'           => $question,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $answer,
				),
			);
		}

		return $items;
	}
}

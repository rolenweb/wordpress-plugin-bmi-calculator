<?php
/**
 * Shortcode rendering.
 *
 * @package SimpleBmiCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle BMI calculator shortcode output.
 */
class SBC_Shortcode {

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
	 * Constructor.
	 *
	 * @param SBC_Assets   $assets Assets handler.
	 * @param SBC_Settings $settings Settings handler.
	 */
	public function __construct( SBC_Assets $assets, SBC_Settings $settings ) {
		$this->assets   = $assets;
		$this->settings = $settings;
	}

	/**
	 * Register shortcode.
	 *
	 * @return void
	 */
	public function init() {
		add_shortcode( 'bmi_calculator', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render shortcode output.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		$options = $this->settings->get_options();

		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		$atts = array_change_key_case( $atts, CASE_LOWER );
		$atts    = shortcode_atts(
			array(
				'unit'  => $options['default_unit'],
				'theme' => $options['default_theme'],
				'title' => '',
			),
			$atts,
			'bmi_calculator'
		);

		$raw_unit  = sanitize_key( wp_unslash( $atts['unit'] ) );
		$raw_theme = sanitize_key( wp_unslash( $atts['theme'] ) );
		$unit      = in_array( $raw_unit, array( 'metric', 'imperial' ), true ) ? $raw_unit : $options['default_unit'];
		$theme     = in_array( $raw_theme, array( 'default', 'minimal' ), true ) ? $raw_theme : $options['default_theme'];
		$title     = sanitize_text_field( wp_unslash( $atts['title'] ) );
		$is_imperial = 'imperial' === $unit;
		$title_text  = ! empty( $title ) ? $title : esc_html__( 'BMI Calculator', 'simple-bmi-calculator' );
		$instance_id = wp_unique_id( 'sbc-calculator-' );

		$this->assets->enqueue_assets( array() );

		$disclaimer = $options['disclaimer_text'];

		ob_start();
		?>
		<div
			id="<?php echo esc_attr( $instance_id ); ?>"
			class="<?php echo esc_attr( 'sbc-calculator sbc-theme-' . $theme . ' sbc-unit-' . $unit ); ?>"
			data-default-unit="<?php echo esc_attr( $unit ); ?>"
			data-current-unit="<?php echo esc_attr( $unit ); ?>"
			data-theme="<?php echo esc_attr( $theme ); ?>"
		>
			<h2 class="sbc-calculator__title"><?php echo esc_html( $title_text ); ?></h2>

			<div class="sbc-unit-toggle" role="group" aria-label="<?php echo esc_attr__( 'Measurement system', 'simple-bmi-calculator' ); ?>">
				<button type="button" class="sbc-unit-toggle__button <?php echo esc_attr( 'metric' === $unit ? 'sbc-unit-toggle__button--active' : '' ); ?>" data-unit="metric" aria-pressed="<?php echo esc_attr( 'metric' === $unit ? 'true' : 'false' ); ?>">
					<?php echo esc_html__( 'Metric', 'simple-bmi-calculator' ); ?>
				</button>
				<button type="button" class="sbc-unit-toggle__button <?php echo esc_attr( 'imperial' === $unit ? 'sbc-unit-toggle__button--active' : '' ); ?>" data-unit="imperial" aria-pressed="<?php echo esc_attr( 'imperial' === $unit ? 'true' : 'false' ); ?>">
					<?php echo esc_html__( 'Imperial', 'simple-bmi-calculator' ); ?>
				</button>
			</div>

			<div class="sbc-fields">
				<div class="sbc-field">
					<label for="<?php echo esc_attr( $instance_id . '-height' ); ?>" class="sbc-field__label" data-label-metric="<?php echo esc_attr__( 'Height (cm)', 'simple-bmi-calculator' ); ?>" data-label-imperial="<?php echo esc_attr__( 'Height (inches)', 'simple-bmi-calculator' ); ?>">
						<?php echo esc_html( $is_imperial ? esc_html__( 'Height (inches)', 'simple-bmi-calculator' ) : esc_html__( 'Height (cm)', 'simple-bmi-calculator' ) ); ?>
					</label>
					<input id="<?php echo esc_attr( $instance_id . '-height' ); ?>" class="sbc-input" type="number" min="1" step="0.1" inputmode="decimal" placeholder="0" data-field="height" />
				</div>
				<div class="sbc-field">
					<label for="<?php echo esc_attr( $instance_id . '-weight' ); ?>" class="sbc-field__label" data-label-metric="<?php echo esc_attr__( 'Weight (kg)', 'simple-bmi-calculator' ); ?>" data-label-imperial="<?php echo esc_attr__( 'Weight (lbs)', 'simple-bmi-calculator' ); ?>">
						<?php echo esc_html( $is_imperial ? esc_html__( 'Weight (lbs)', 'simple-bmi-calculator' ) : esc_html__( 'Weight (kg)', 'simple-bmi-calculator' ) ); ?>
					</label>
					<input id="<?php echo esc_attr( $instance_id . '-weight' ); ?>" class="sbc-input" type="number" min="1" step="0.1" inputmode="decimal" placeholder="0" data-field="weight" />
				</div>
			</div>

			<p class="sbc-error" role="alert" aria-live="polite" hidden></p>

			<div class="sbc-result" aria-live="polite" hidden>
				<p class="sbc-result__eyebrow"><?php echo esc_html__( 'Your Score', 'simple-bmi-calculator' ); ?></p>
				<p class="sbc-result__number">0.0</p>
				<p class="sbc-result__badge"><?php echo esc_html__( 'Healthy', 'simple-bmi-calculator' ); ?></p>
				<div class="sbc-result__scale" aria-hidden="true">
					<div class="sbc-result__scale-segment sbc-result__scale-segment--underweight"></div>
					<div class="sbc-result__scale-segment sbc-result__scale-segment--healthy"></div>
					<div class="sbc-result__scale-segment sbc-result__scale-segment--overweight"></div>
					<div class="sbc-result__scale-segment sbc-result__scale-segment--obese"></div>
				</div>
			</div>

			<p class="sbc-disclaimer"><?php echo esc_html( $disclaimer ); ?></p>

			<?php if ( ! empty( $options['show_credit_link'] ) ) : ?>
				<p class="sbc-credit">
					<a href="<?php echo esc_url( $options['credit_link_url'] ); ?>" rel="nofollow sponsored">
						<?php echo esc_html( $options['credit_link_text'] ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
		<?php

		return (string) ob_get_clean();
	}
}

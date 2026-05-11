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
		$unit_label  = $is_imperial ? esc_html__( 'Imperial units', 'simple-bmi-calculator' ) : esc_html__( 'Metric units', 'simple-bmi-calculator' );
		$theme_label = 'minimal' === $theme ? esc_html__( 'Minimal', 'simple-bmi-calculator' ) : esc_html__( 'Default', 'simple-bmi-calculator' );

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
			<div class="sbc-calculator__header">
				<div class="sbc-calculator__header-copy">
					<p class="sbc-calculator__eyebrow"><?php echo esc_html__( 'Health calculator', 'simple-bmi-calculator' ); ?></p>
					<h2 class="sbc-calculator__title"><?php echo esc_html( $title_text ); ?></h2>
					<p class="sbc-calculator__description"><?php echo esc_html__( 'Estimate body mass index using metric or imperial measurements.', 'simple-bmi-calculator' ); ?></p>
				</div>
				<div class="sbc-calculator__header-meta">
					<span class="sbc-calculator__theme-tag"><?php echo esc_html( $theme_label ); ?></span>
					<span class="sbc-calculator__unit-tag"><?php echo esc_html( $unit_label ); ?></span>
				</div>
			</div>

			<div class="sbc-calculator__unit-switcher">
				<p class="sbc-calculator__unit-label"><?php echo esc_html__( 'Measurement system', 'simple-bmi-calculator' ); ?></p>
				<div class="sbc-unit-toggle" role="group" aria-label="<?php echo esc_attr__( 'Measurement system', 'simple-bmi-calculator' ); ?>">
					<button type="button" class="sbc-unit-toggle__button <?php echo esc_attr( 'metric' === $unit ? 'sbc-unit-toggle__button--active' : '' ); ?>" data-unit="metric" aria-pressed="<?php echo esc_attr( 'metric' === $unit ? 'true' : 'false' ); ?>">
						<?php echo esc_html__( 'Metric', 'simple-bmi-calculator' ); ?>
					</button>
					<button type="button" class="sbc-unit-toggle__button <?php echo esc_attr( 'imperial' === $unit ? 'sbc-unit-toggle__button--active' : '' ); ?>" data-unit="imperial" aria-pressed="<?php echo esc_attr( 'imperial' === $unit ? 'true' : 'false' ); ?>">
						<?php echo esc_html__( 'Imperial', 'simple-bmi-calculator' ); ?>
					</button>
				</div>
			</div>

			<div class="sbc-calculator__form-shell">
				<div class="sbc-fields sbc-fields--metric" data-unit-group="metric" aria-hidden="<?php echo esc_attr( $is_imperial ? 'true' : 'false' ); ?>" <?php if ( $is_imperial ) : ?>hidden<?php endif; ?>>
					<p class="sbc-fields__title"><?php echo esc_html__( 'Metric measurements', 'simple-bmi-calculator' ); ?></p>
					<div class="sbc-field">
						<label for="<?php echo esc_attr( $instance_id . '-height-cm' ); ?>"><?php echo esc_html__( 'Height (cm)', 'simple-bmi-calculator' ); ?></label>
						<input id="<?php echo esc_attr( $instance_id . '-height-cm' ); ?>" class="sbc-input" type="number" min="1" step="0.1" inputmode="decimal" <?php if ( $is_imperial ) : ?>disabled<?php endif; ?> />
					</div>
					<div class="sbc-field">
						<label for="<?php echo esc_attr( $instance_id . '-weight-kg' ); ?>"><?php echo esc_html__( 'Weight (kg)', 'simple-bmi-calculator' ); ?></label>
						<input id="<?php echo esc_attr( $instance_id . '-weight-kg' ); ?>" class="sbc-input" type="number" min="1" step="0.1" inputmode="decimal" <?php if ( $is_imperial ) : ?>disabled<?php endif; ?> />
					</div>
				</div>

				<div class="sbc-fields sbc-fields--imperial" data-unit-group="imperial" aria-hidden="<?php echo esc_attr( $is_imperial ? 'false' : 'true' ); ?>" <?php if ( ! $is_imperial ) : ?>hidden<?php endif; ?>>
					<p class="sbc-fields__title"><?php echo esc_html__( 'Imperial measurements', 'simple-bmi-calculator' ); ?></p>
					<div class="sbc-field">
						<label for="<?php echo esc_attr( $instance_id . '-height-ft' ); ?>"><?php echo esc_html__( 'Height (ft)', 'simple-bmi-calculator' ); ?></label>
						<input id="<?php echo esc_attr( $instance_id . '-height-ft' ); ?>" class="sbc-input" type="number" min="0" step="1" inputmode="numeric" <?php if ( ! $is_imperial ) : ?>disabled<?php endif; ?> />
					</div>
					<div class="sbc-field">
						<label for="<?php echo esc_attr( $instance_id . '-height-in' ); ?>"><?php echo esc_html__( 'Additional inches', 'simple-bmi-calculator' ); ?></label>
						<input id="<?php echo esc_attr( $instance_id . '-height-in' ); ?>" class="sbc-input" type="number" min="0" step="1" inputmode="numeric" <?php if ( ! $is_imperial ) : ?>disabled<?php endif; ?> />
					</div>
					<div class="sbc-field">
						<label for="<?php echo esc_attr( $instance_id . '-weight-lb' ); ?>"><?php echo esc_html__( 'Weight (lb)', 'simple-bmi-calculator' ); ?></label>
						<input id="<?php echo esc_attr( $instance_id . '-weight-lb' ); ?>" class="sbc-input" type="number" min="1" step="0.1" inputmode="decimal" <?php if ( ! $is_imperial ) : ?>disabled<?php endif; ?> />
					</div>
				</div>
			</div>

			<div class="sbc-calculator__actions">
				<button type="button" class="sbc-button"><?php echo esc_html__( 'Calculate BMI', 'simple-bmi-calculator' ); ?></button>
			</div>

			<p class="sbc-error" role="alert" aria-live="polite" hidden></p>

			<div class="sbc-result" aria-live="polite">
				<div class="sbc-result__panel">
					<p class="sbc-result__label"><?php echo esc_html__( 'Your BMI', 'simple-bmi-calculator' ); ?></p>
					<p class="sbc-result__number">-</p>
				</div>
				<div class="sbc-result__panel">
					<p class="sbc-result__label"><?php echo esc_html__( 'Category', 'simple-bmi-calculator' ); ?></p>
					<p class="sbc-result__category-text">-</p>
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

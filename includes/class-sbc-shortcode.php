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
	 * Schema handler.
	 *
	 * @var SBC_Schema
	 */
	private $schema;

	/**
	 * Constructor.
	 *
	 * @param SBC_Assets   $assets Assets handler.
	 * @param SBC_Settings $settings Settings handler.
	 * @param SBC_Schema   $schema Schema handler.
	 */
	public function __construct( SBC_Assets $assets, SBC_Settings $settings, SBC_Schema $schema ) {
		$this->assets   = $assets;
		$this->settings = $settings;
		$this->schema   = $schema;
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
		$atts = shortcode_atts(
			array(
				'unit'          => $options['default_unit'],
				'theme'         => $options['default_theme'],
				'title'         => '',
				'primary_color' => '',
				'show_credit'   => '',
				'show_schema'   => '',
			),
			$atts,
			'bmi_calculator'
		);

		$raw_unit  = sanitize_key( wp_unslash( $atts['unit'] ) );
		$raw_theme = sanitize_key( wp_unslash( $atts['theme'] ) );
		$unit      = in_array( $raw_unit, array( 'metric', 'imperial' ), true ) ? $raw_unit : $options['default_unit'];
		$theme     = $this->normalize_theme( $raw_theme, $options['default_theme'] );
		$title     = sanitize_text_field( wp_unslash( $atts['title'] ) );
		$is_imperial = 'imperial' === $unit;
		$title_text  = '' !== $title ? $title : esc_html__( 'BMI Calculator', 'simple-bmi-calculator' );
		$instance_id = wp_unique_id( 'sbc-calculator-' );

		$show_credit_override = $this->parse_optional_bool( $atts['show_credit'] );
		$show_schema_override = $this->parse_optional_bool( $atts['show_schema'] );
		$show_credit          = $this->should_show_credit( $options, $show_credit_override );
		$credit_placement     = $show_credit ? $this->get_credit_placement( $options ) : 'none';
		$credit_target        = ! empty( $options['open_credit_new_tab'] ) ? '_blank' : '';
		$credit_rel           = 'nofollow sponsored noopener noreferrer';
		$color_config         = $this->get_color_config( $options, $atts );
		$wrapper_style        = $this->build_style_attribute( $color_config );

		$this->assets->enqueue_assets( array() );
		$this->schema->register_shortcode_instance( $show_schema_override );

		ob_start();
		?>
		<div
			id="<?php echo esc_attr( $instance_id ); ?>"
			class="<?php echo esc_attr( 'sbc-calculator sbc-theme-' . $theme . ' sbc-unit-' . $unit ); ?>"
			data-default-unit="<?php echo esc_attr( $unit ); ?>"
			data-current-unit="<?php echo esc_attr( $unit ); ?>"
			data-theme="<?php echo esc_attr( $theme ); ?>"
			data-credit-placement="<?php echo esc_attr( $credit_placement ); ?>"
			<?php if ( '' !== $wrapper_style ) : ?>
				style="<?php echo esc_attr( $wrapper_style ); ?>"
			<?php endif; ?>
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

			<?php if ( 'under_result' === $credit_placement ) : ?>
				<?php echo $this->get_credit_markup( $options, $credit_rel, $credit_target, 'sbc-credit sbc-credit--under-result', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			<p class="sbc-disclaimer"><?php echo esc_html( $options['disclaimer_text'] ); ?></p>

			<?php if ( 'under_calculator' === $credit_placement ) : ?>
				<?php echo $this->get_credit_markup( $options, $credit_rel, $credit_target, 'sbc-credit sbc-credit--under-calculator', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			<?php if ( 'footer' === $credit_placement ) : ?>
				<div class="sbc-footer">
					<?php echo $this->get_credit_markup( $options, $credit_rel, $credit_target, 'sbc-credit sbc-credit--footer', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Determine whether the credit link should render.
	 *
	 * @param array     $options Sitewide options.
	 * @param null|bool $show_credit_override Shortcode override.
	 * @return bool
	 */
	private function should_show_credit( $options, $show_credit_override ) {
		if ( false === $show_credit_override ) {
			return false;
		}

		if ( empty( $options['show_credit_link'] ) ) {
			return false;
		}

		if ( 'none' === $this->get_credit_placement( $options ) ) {
			return false;
		}

		return ! empty( $options['credit_link_text'] ) && ! empty( $options['credit_link_url'] );
	}

	/**
	 * Normalize credit placement.
	 *
	 * @param array $options Sitewide options.
	 * @return string
	 */
	private function get_credit_placement( $options ) {
		$placement = isset( $options['credit_link_placement'] ) ? sanitize_key( $options['credit_link_placement'] ) : 'under_calculator';

		return in_array( $placement, array( 'under_calculator', 'under_result', 'footer', 'none' ), true ) ? $placement : 'under_calculator';
	}

	/**
	 * Parse an optional boolean shortcode attribute.
	 *
	 * @param mixed $value Raw attribute value.
	 * @return null|bool
	 */
	private function parse_optional_bool( $value ) {
		$value = is_scalar( $value ) ? strtolower( trim( (string) $value ) ) : '';

		if ( '' === $value ) {
			return null;
		}

		if ( in_array( $value, array( '1', 'true', 'yes', 'on' ), true ) ) {
			return true;
		}

		if ( in_array( $value, array( '0', 'false', 'no', 'off' ), true ) ) {
			return false;
		}

		return null;
	}

	/**
	 * Normalize supported theme values while keeping legacy aliases working.
	 *
	 * @param string $theme Raw theme value.
	 * @param string $fallback Fallback theme.
	 * @return string
	 */
	private function normalize_theme( $theme, $fallback ) {
		if ( 'default' === $theme ) {
			$theme = 'modern';
		}

		if ( 'default' === $fallback ) {
			$fallback = 'modern';
		}

		return in_array( $theme, array( 'modern', 'minimal' ), true ) ? $theme : $fallback;
	}

	/**
	 * Build color configuration for the instance.
	 *
	 * @param array $options Sitewide options.
	 * @param array $atts Shortcode attributes.
	 * @return array<string, string>
	 */
	private function get_color_config( $options, $atts ) {
		$colors = array();

		foreach ( SBC_Settings::get_color_defaults() as $option_key => $default_color ) {
			$colors[ $option_key ] = isset( $options[ $option_key ] ) && sanitize_hex_color( $options[ $option_key ] )
				? $options[ $option_key ]
				: $default_color;
		}

		$primary_override = isset( $atts['primary_color'] ) ? sanitize_hex_color( wp_unslash( $atts['primary_color'] ) ) : null;

		if ( $primary_override ) {
			$colors['primary_color'] = $primary_override;
		}

		return $colors;
	}

	/**
	 * Build a safe inline style attribute from CSS variables.
	 *
	 * @param array<string, string> $colors Instance color config.
	 * @return string
	 */
	private function build_style_attribute( $colors ) {
		$map = array(
			'primary_color'       => '--sbc-primary',
			'primary_hover_color' => '--sbc-primary-hover',
			'card_background'     => '--sbc-card-bg',
			'text_color'          => '--sbc-text',
			'muted_text_color'    => '--sbc-muted-text',
			'border_color'        => '--sbc-border',
			'result_background'   => '--sbc-result-bg',
			'success_color'       => '--sbc-success',
			'warning_color'       => '--sbc-warning',
			'danger_color'        => '--sbc-danger',
		);
		$styles = array();

		foreach ( $map as $color_key => $property ) {
			if ( empty( $colors[ $color_key ] ) ) {
				continue;
			}

			$styles[] = $property . ':' . $colors[ $color_key ];
		}

		$primary_rgb = $this->hex_to_rgb_string( $colors['primary_color'] );

		if ( '' !== $primary_rgb ) {
			$styles[] = '--sbc-primary-rgb:' . $primary_rgb;
		}

		return implode( '; ', $styles );
	}

	/**
	 * Render visible credit link markup.
	 *
	 * @param array  $options Sitewide options.
	 * @param string $rel Rel attribute.
	 * @param string $target Target attribute.
	 * @param string $class_name Wrapper class.
	 * @param bool   $hidden Whether to hide initially.
	 * @return string
	 */
	private function get_credit_markup( $options, $rel, $target, $class_name, $hidden ) {
		$target_markup = '';

		if ( '' !== $target ) {
			$target_markup = ' target="' . esc_attr( $target ) . '"';
		}

		ob_start();
		?>
		<p class="<?php echo esc_attr( $class_name ); ?>"<?php echo $hidden ? ' hidden' : ''; ?>>
			<a href="<?php echo esc_url( $options['credit_link_url'] ); ?>" rel="<?php echo esc_attr( $rel ); ?>"<?php echo $target_markup; ?>>
				<?php echo esc_html( $options['credit_link_text'] ); ?>
			</a>
		</p>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Convert a hex color to an RGB string for CSS alpha usage.
	 *
	 * @param string $hex_color Hex color.
	 * @return string
	 */
	private function hex_to_rgb_string( $hex_color ) {
		$hex_color = (string) sanitize_hex_color( $hex_color );

		if ( '' === $hex_color ) {
			return '';
		}

		$hex_color = ltrim( $hex_color, '#' );

		if ( 6 !== strlen( $hex_color ) ) {
			return '';
		}

		$red   = hexdec( substr( $hex_color, 0, 2 ) );
		$green = hexdec( substr( $hex_color, 2, 2 ) );
		$blue  = hexdec( substr( $hex_color, 4, 2 ) );

		return $red . ' ' . $green . ' ' . $blue;
	}
}

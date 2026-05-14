<?php
/**
 * Shortcode rendering.
 *
 * @package BodyMetricBmiCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle BMI calculator shortcode output.
 */
class BODYBMCA_Shortcode {

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
	 * Constructor.
	 *
	 * @param BODYBMCA_Assets   $assets Assets handler.
	 * @param BODYBMCA_Settings $settings Settings handler.
	 * @param BODYBMCA_Schema   $schema Schema handler.
	 */
	public function __construct( BODYBMCA_Assets $assets, BODYBMCA_Settings $settings, BODYBMCA_Schema $schema ) {
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
		add_shortcode( 'bodybmca_bmi_calculator', array( $this, 'render_shortcode' ) );
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
			'bodybmca_bmi_calculator'
		);

		$raw_unit           = sanitize_key( wp_unslash( $atts['unit'] ) );
		$raw_theme          = sanitize_key( wp_unslash( $atts['theme'] ) );
		$unit               = in_array( $raw_unit, array( 'metric', 'imperial' ), true ) ? $raw_unit : $options['default_unit'];
		$theme              = $this->normalize_theme( $raw_theme, $options['default_theme'] );
		$title              = sanitize_text_field( wp_unslash( $atts['title'] ) );
		$title_text         = '' !== $title ? $title : esc_html__( 'BMI Calculator', 'bodymetric-bmi-calculator' );
		$subtitle_text      = esc_html__( 'Calculate your Body Mass Index', 'bodymetric-bmi-calculator' );
		$instance_id        = wp_unique_id( 'bodybmca-calculator-' );
		$metric_hidden      = 'metric' !== $unit;
		$imperial_hidden    = 'imperial' !== $unit;
		$metric_height_id   = $instance_id . '-height-cm';
		$metric_weight_id   = $instance_id . '-weight-kg';
		$imperial_feet_id   = $instance_id . '-height-ft';
		$imperial_inches_id = $instance_id . '-height-in';
		$imperial_weight_id = $instance_id . '-weight-lb';

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
			<div class="sbc-card"><div class="sbc-header"><div class="sbc-title-group"><div class="sbc-icon-box" aria-hidden="true"><?php echo $this->get_calculator_icon_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="sbc-title-text"><h3 class="sbc-title"><?php echo esc_html( $title_text ); ?></h3><p class="sbc-subtitle"><?php echo esc_html( $subtitle_text ); ?></p></div></div><div class="sbc-unit-toggle" role="group" aria-label="<?php echo esc_attr__( 'Measurement system', 'bodymetric-bmi-calculator' ); ?>"><button type="button" class="<?php echo esc_attr( 'sbc-unit-toggle__button' . ( 'metric' === $unit ? ' sbc-unit-toggle__button--active' : '' ) ); ?>" data-unit="metric" aria-pressed="<?php echo esc_attr( 'metric' === $unit ? 'true' : 'false' ); ?>"><?php echo esc_html__( 'Metric', 'bodymetric-bmi-calculator' ); ?></button><button type="button" class="<?php echo esc_attr( 'sbc-unit-toggle__button' . ( 'imperial' === $unit ? ' sbc-unit-toggle__button--active' : '' ) ); ?>" data-unit="imperial" aria-pressed="<?php echo esc_attr( 'imperial' === $unit ? 'true' : 'false' ); ?>"><?php echo esc_html__( 'Imperial', 'bodymetric-bmi-calculator' ); ?></button></div></div><div class="sbc-fields sbc-fields--metric" data-unit-fields="metric"<?php echo $metric_hidden ? ' hidden' : ''; ?>><div class="sbc-input-grid"><div class="sbc-input-group"><label for="<?php echo esc_attr( $metric_height_id ); ?>" class="sbc-input-label"><?php echo esc_html__( 'Height', 'bodymetric-bmi-calculator' ); ?></label><div class="sbc-field-container"><input id="<?php echo esc_attr( $metric_height_id ); ?>" class="sbc-input" type="number" min="1" step="0.1" inputmode="decimal" placeholder="0" data-field="height-cm" /><span class="sbc-unit-label" aria-hidden="true"><?php echo esc_html__( 'cm', 'bodymetric-bmi-calculator' ); ?></span></div></div><div class="sbc-input-group"><label for="<?php echo esc_attr( $metric_weight_id ); ?>" class="sbc-input-label"><?php echo esc_html__( 'Weight', 'bodymetric-bmi-calculator' ); ?></label><div class="sbc-field-container"><input id="<?php echo esc_attr( $metric_weight_id ); ?>" class="sbc-input" type="number" min="1" step="0.1" inputmode="decimal" placeholder="0" data-field="weight-kg" /><span class="sbc-unit-label" aria-hidden="true"><?php echo esc_html__( 'kg', 'bodymetric-bmi-calculator' ); ?></span></div></div></div></div><div class="sbc-fields sbc-fields--imperial" data-unit-fields="imperial"<?php echo $imperial_hidden ? ' hidden' : ''; ?>><div class="sbc-input-grid sbc-input-grid--imperial"><div class="sbc-input-group"><label for="<?php echo esc_attr( $imperial_feet_id ); ?>" class="sbc-input-label"><?php echo esc_html__( 'Height', 'bodymetric-bmi-calculator' ); ?></label><div class="sbc-field-container"><input id="<?php echo esc_attr( $imperial_feet_id ); ?>" class="sbc-input" type="number" min="0" step="1" inputmode="decimal" placeholder="0" data-field="height-ft" /><span class="sbc-unit-label" aria-hidden="true"><?php echo esc_html__( 'ft', 'bodymetric-bmi-calculator' ); ?></span></div></div><div class="sbc-input-group"><label for="<?php echo esc_attr( $imperial_inches_id ); ?>" class="sbc-input-label"><?php echo esc_html__( 'Inches', 'bodymetric-bmi-calculator' ); ?></label><div class="sbc-field-container"><input id="<?php echo esc_attr( $imperial_inches_id ); ?>" class="sbc-input" type="number" min="0" step="0.1" inputmode="decimal" placeholder="0" data-field="height-in" /><span class="sbc-unit-label" aria-hidden="true"><?php echo esc_html__( 'in', 'bodymetric-bmi-calculator' ); ?></span></div></div><div class="sbc-input-group"><label for="<?php echo esc_attr( $imperial_weight_id ); ?>" class="sbc-input-label"><?php echo esc_html__( 'Weight', 'bodymetric-bmi-calculator' ); ?></label><div class="sbc-field-container"><input id="<?php echo esc_attr( $imperial_weight_id ); ?>" class="sbc-input" type="number" min="1" step="0.1" inputmode="decimal" placeholder="0" data-field="weight-lb" /><span class="sbc-unit-label" aria-hidden="true"><?php echo esc_html__( 'lb', 'bodymetric-bmi-calculator' ); ?></span></div></div></div></div><button type="button" class="sbc-calc-btn"><?php echo esc_html__( 'Calculate BMI', 'bodymetric-bmi-calculator' ); ?></button><p class="sbc-error" role="alert" hidden></p><div class="sbc-result-box" aria-live="polite" hidden><div class="sbc-result-left"><p class="sbc-result-label"><?php echo esc_html__( 'BMI Score', 'bodymetric-bmi-calculator' ); ?></p><p class="sbc-bmi-value">0.0</p></div><div class="sbc-result-right"><p class="sbc-category-badge"><?php echo esc_html__( 'Normal Weight', 'bodymetric-bmi-calculator' ); ?></p><p class="sbc-range-text"><?php echo esc_html__( 'Reference range: 18.5 – 24.9', 'bodymetric-bmi-calculator' ); ?></p><p class="sbc-feedback-text"><?php echo esc_html__( 'Your BMI is within the standard adult reference range.', 'bodymetric-bmi-calculator' ); ?></p></div></div><?php if ( 'under_result' === $credit_placement ) : ?><?php echo $this->get_credit_markup( $options, $credit_rel, $credit_target, 'sbc-credit sbc-credit--under-result', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php endif; ?><div class="sbc-footer-area"><div class="sbc-footer-note"><span class="sbc-footer-note__icon" aria-hidden="true"><?php echo $this->get_shield_icon_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><strong><?php echo esc_html__( 'Private.', 'bodymetric-bmi-calculator' ); ?></strong> <?php echo esc_html__( 'No data is stored.', 'bodymetric-bmi-calculator' ); ?></span></div><?php if ( ! empty( $options['disclaimer_text'] ) ) : ?><p class="sbc-disclaimer"><?php echo esc_html( $options['disclaimer_text'] ); ?></p><?php endif; ?><?php if ( 'footer' === $credit_placement ) : ?><?php echo $this->get_credit_markup( $options, $credit_rel, $credit_target, 'sbc-credit sbc-credit--footer', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php endif; ?></div></div><?php if ( 'under_calculator' === $credit_placement ) : ?><?php echo $this->get_credit_markup( $options, $credit_rel, $credit_target, 'sbc-credit sbc-credit--under-calculator', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php endif; ?>
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

		foreach ( BODYBMCA_Settings::get_color_defaults() as $option_key => $default_color ) {
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
	 * Get the calculator icon markup.
	 *
	 * @return string
	 */
	private function get_calculator_icon_markup() {
		return '<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><rect x="4" y="2.5" width="16" height="19" rx="3" fill="none" stroke="currentColor" stroke-width="1.8"/><rect x="7" y="5.5" width="10" height="3.5" rx="1" fill="currentColor" opacity="0.92"/><path d="M8 12h2M14 12h2M8 15.5h2M14 15.5h2M8 19h2M11.5 15.5h1M11.5 19h1" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/></svg>';
	}

	/**
	 * Get the privacy icon markup.
	 *
	 * @return string
	 */
	private function get_shield_icon_markup() {
		return '<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path d="M12 3.5 5.5 6v5.7c0 4.3 2.6 8.2 6.5 9.8 3.9-1.6 6.5-5.5 6.5-9.8V6L12 3.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m9.5 12 1.7 1.7 3.3-3.4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>';
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

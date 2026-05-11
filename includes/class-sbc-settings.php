<?php
/**
 * Settings handling.
 *
 * @package SimpleBmiCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings API integration.
 */
class SBC_Settings {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'simple_bmi_calculator_options';

	/**
	 * Get plugin defaults.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'default_unit'      => 'metric',
			'default_theme'     => 'default',
			'show_credit_link'  => 0,
			'credit_link_url'   => 'https://bodymetriccalculator.com/bmi-calculator/',
			'credit_link_text'  => 'BMI calculator by Simple BMI Calculator',
			'disclaimer_text'   => 'BMI is a general screening tool and is not medical advice.',
		);
	}

	/**
	 * Initialize admin hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Get merged settings.
	 *
	 * @return array
	 */
	public function get_options() {
		$options = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return wp_parse_args( $options, self::get_defaults() );
	}

	/**
	 * Add settings page.
	 *
	 * @return void
	 */
	public function add_settings_page() {
		add_options_page(
			esc_html__( 'Simple BMI Calculator', 'simple-bmi-calculator' ),
			esc_html__( 'Simple BMI Calculator', 'simple-bmi-calculator' ),
			'manage_options',
			'simple-bmi-calculator',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'sbc_settings_group',
			self::OPTION_NAME,
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'sbc_general_section',
			esc_html__( 'Calculator Settings', 'simple-bmi-calculator' ),
			'__return_false',
			'simple-bmi-calculator'
		);

		add_settings_field(
			'default_unit',
			esc_html__( 'Default unit', 'simple-bmi-calculator' ),
			array( $this, 'render_default_unit_field' ),
			'simple-bmi-calculator',
			'sbc_general_section'
		);

		add_settings_field(
			'default_theme',
			esc_html__( 'Default theme', 'simple-bmi-calculator' ),
			array( $this, 'render_default_theme_field' ),
			'simple-bmi-calculator',
			'sbc_general_section'
		);

		add_settings_field(
			'show_credit_link',
			esc_html__( 'Show credit link', 'simple-bmi-calculator' ),
			array( $this, 'render_show_credit_link_field' ),
			'simple-bmi-calculator',
			'sbc_general_section'
		);

		add_settings_field(
			'credit_link_url',
			esc_html__( 'Credit link URL', 'simple-bmi-calculator' ),
			array( $this, 'render_credit_link_url_field' ),
			'simple-bmi-calculator',
			'sbc_general_section'
		);

		add_settings_field(
			'credit_link_text',
			esc_html__( 'Credit link text', 'simple-bmi-calculator' ),
			array( $this, 'render_credit_link_text_field' ),
			'simple-bmi-calculator',
			'sbc_general_section'
		);

		add_settings_field(
			'disclaimer_text',
			esc_html__( 'Custom disclaimer text', 'simple-bmi-calculator' ),
			array( $this, 'render_disclaimer_text_field' ),
			'simple-bmi-calculator',
			'sbc_general_section'
		);
	}

	/**
	 * Sanitize saved options.
	 *
	 * @param array $input Raw values.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$defaults = self::get_defaults();
		$output   = array();

		$output['default_unit'] = ( isset( $input['default_unit'] ) && in_array( $input['default_unit'], array( 'metric', 'imperial' ), true ) )
			? $input['default_unit']
			: $defaults['default_unit'];

		$output['default_theme'] = ( isset( $input['default_theme'] ) && in_array( $input['default_theme'], array( 'default', 'minimal' ), true ) )
			? $input['default_theme']
			: $defaults['default_theme'];

		$output['show_credit_link'] = isset( $input['show_credit_link'] ) ? 1 : 0;
		$output['credit_link_url']  = isset( $input['credit_link_url'] ) ? sanitize_url( $input['credit_link_url'] ) : $defaults['credit_link_url'];
		$output['credit_link_text'] = isset( $input['credit_link_text'] ) ? sanitize_text_field( $input['credit_link_text'] ) : $defaults['credit_link_text'];
		$output['disclaimer_text']  = isset( $input['disclaimer_text'] ) ? sanitize_textarea_field( $input['disclaimer_text'] ) : $defaults['disclaimer_text'];

		if ( empty( $output['credit_link_url'] ) ) {
			$output['credit_link_url'] = $defaults['credit_link_url'];
		}

		if ( empty( $output['credit_link_text'] ) ) {
			$output['credit_link_text'] = $defaults['credit_link_text'];
		}

		if ( empty( $output['disclaimer_text'] ) ) {
			$output['disclaimer_text'] = $defaults['disclaimer_text'];
		}

		return $output;
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Simple BMI Calculator', 'simple-bmi-calculator' ); ?></h1>
			<p><?php echo esc_html__( 'Credit link is optional. Enable it only if you want to support the plugin author.', 'simple-bmi-calculator' ); ?></p>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'sbc_settings_group' );
				do_settings_sections( 'simple-bmi-calculator' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render default unit field.
	 *
	 * @return void
	 */
	public function render_default_unit_field() {
		$options = $this->get_options();
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_unit]">
			<option value="metric" <?php selected( $options['default_unit'], 'metric' ); ?>><?php echo esc_html__( 'Metric', 'simple-bmi-calculator' ); ?></option>
			<option value="imperial" <?php selected( $options['default_unit'], 'imperial' ); ?>><?php echo esc_html__( 'Imperial', 'simple-bmi-calculator' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Render default theme field.
	 *
	 * @return void
	 */
	public function render_default_theme_field() {
		$options = $this->get_options();
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_theme]">
			<option value="default" <?php selected( $options['default_theme'], 'default' ); ?>><?php echo esc_html__( 'Default', 'simple-bmi-calculator' ); ?></option>
			<option value="minimal" <?php selected( $options['default_theme'], 'minimal' ); ?>><?php echo esc_html__( 'Minimal', 'simple-bmi-calculator' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Render credit link field.
	 *
	 * @return void
	 */
	public function render_show_credit_link_field() {
		$options = $this->get_options();
		?>
		<label for="sbc-show-credit-link">
			<input
				id="sbc-show-credit-link"
				name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_credit_link]"
				type="checkbox"
				value="1"
				<?php checked( (int) $options['show_credit_link'], 1 ); ?>
			/>
			<?php echo esc_html__( 'Display an optional visible credit link below the calculator.', 'simple-bmi-calculator' ); ?>
		</label>
		<?php
	}

	/**
	 * Render credit link URL field.
	 *
	 * @return void
	 */
	public function render_credit_link_url_field() {
		$options = $this->get_options();
		?>
		<input
			class="regular-text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[credit_link_url]"
			type="url"
			value="<?php echo esc_attr( $options['credit_link_url'] ); ?>"
		/>
		<?php
	}

	/**
	 * Render credit link text field.
	 *
	 * @return void
	 */
	public function render_credit_link_text_field() {
		$options = $this->get_options();
		?>
		<input
			class="regular-text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[credit_link_text]"
			type="text"
			value="<?php echo esc_attr( $options['credit_link_text'] ); ?>"
		/>
		<?php
	}

	/**
	 * Render disclaimer field.
	 *
	 * @return void
	 */
	public function render_disclaimer_text_field() {
		$options = $this->get_options();
		?>
		<textarea
			class="large-text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[disclaimer_text]"
			rows="3"
		><?php echo esc_textarea( $options['disclaimer_text'] ); ?></textarea>
		<?php
	}
}

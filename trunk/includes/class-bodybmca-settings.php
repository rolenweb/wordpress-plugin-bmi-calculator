<?php
/**
 * Settings handling.
 *
 * @package BodyMetricBmiCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings API integration.
 */
class BODYBMCA_Settings {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'bodybmca_options';

	/**
	 * Legacy option name used before the plugin rename.
	 *
	 * @var string
	 */
	const LEGACY_OPTION_NAMES = array(
		'bodymetric_bmi_calculator_options',
		'simple_bmi_calculator_options',
	);

	/**
	 * Credit link placement options.
	 *
	 * @var string[]
	 */
	private static $credit_placements = array(
		'under_calculator',
		'under_result',
		'footer',
		'none',
	);

	/**
	 * Supported color fields and defaults.
	 *
	 * @return array<string, string>
	 */
	public static function get_color_defaults() {
		return array(
			'primary_color'       => '#2563eb',
			'primary_hover_color' => '#1d4ed8',
			'card_background'     => '#ffffff',
			'text_color'          => '#1e293b',
			'muted_text_color'    => '#64748b',
			'border_color'        => '#e2e8f0',
			'result_background'   => '#ffffff',
			'success_color'       => '#22c55e',
			'warning_color'       => '#f59e0b',
			'danger_color'        => '#dc2626',
		);
	}

	/**
	 * Get plugin defaults.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array_merge(
			array(
				'default_unit'            => 'metric',
				'default_theme'           => 'modern',
				'disclaimer_text'         => 'BMI is a general screening tool and is not medical advice.',
				'show_credit_link'        => 0,
				'credit_link_text'        => 'Powered by BodyMetricCalculator.com',
				'credit_link_url'         => 'https://bodymetriccalculator.com/',
				'credit_link_placement'   => 'under_calculator',
				'open_credit_new_tab'     => 1,
				'enable_faq_schema'       => 0,
				'enable_calculator_schema'=> 1,
				'faq_question_1'          => '',
				'faq_answer_1'            => '',
				'faq_question_2'          => '',
				'faq_answer_2'            => '',
				'faq_question_3'          => '',
				'faq_answer_3'            => '',
			),
			self::get_color_defaults()
		);
	}

	/**
	 * Initialize admin hooks.
	 *
	 * @return void
	 */
	public function init() {
		$this->maybe_migrate_legacy_options();
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

		if ( empty( $options ) ) {
			foreach ( self::LEGACY_OPTION_NAMES as $legacy_option_name ) {
				$legacy_options = get_option( $legacy_option_name, array() );

				if ( is_array( $legacy_options ) && ! empty( $legacy_options ) ) {
					$options = $legacy_options;
					break;
				}
			}
		}

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
			esc_html__( 'BodyMetric BMI Calculator', 'bodymetric-bmi-calculator' ),
			esc_html__( 'BodyMetric BMI Calculator', 'bodymetric-bmi-calculator' ),
			'manage_options',
			'bodymetric-bmi-calculator',
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
			'bodybmca_settings',
			self::OPTION_NAME,
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'bodybmca_general_section',
			esc_html__( 'General', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_general_section_description' ),
			'bodymetric-bmi-calculator'
		);

		add_settings_field(
			'bodybmca_default_unit',
			esc_html__( 'Default unit', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_default_unit_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_general_section'
		);

		add_settings_field(
			'bodybmca_default_theme',
			esc_html__( 'Default theme', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_default_theme_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_general_section'
		);

		add_settings_field(
			'bodybmca_disclaimer_text',
			esc_html__( 'Custom disclaimer text', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_disclaimer_text_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_general_section'
		);

		add_settings_section(
			'bodybmca_colors_section',
			esc_html__( 'Colors', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_colors_section_description' ),
			'bodymetric-bmi-calculator'
		);

		foreach ( $this->get_color_field_map() as $field_key => $field_label ) {
			add_settings_field(
				'bodybmca_' . $field_key,
				$field_label,
				array( $this, 'render_color_field' ),
				'bodymetric-bmi-calculator',
				'bodybmca_colors_section',
				array(
					'field_key' => $field_key,
				)
			);
		}

		add_settings_section(
			'bodybmca_credit_section',
			esc_html__( 'Credit Link', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_credit_section_description' ),
			'bodymetric-bmi-calculator'
		);

		add_settings_field(
			'bodybmca_show_credit_link',
			esc_html__( 'Show credit link', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_show_credit_link_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_credit_section'
		);

		add_settings_field(
			'bodybmca_credit_link_text',
			esc_html__( 'Credit link text', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_credit_link_text_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_credit_section'
		);

		add_settings_field(
			'bodybmca_credit_link_url',
			esc_html__( 'Credit link URL', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_credit_link_url_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_credit_section'
		);

		add_settings_field(
			'bodybmca_credit_link_placement',
			esc_html__( 'Credit link placement', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_credit_link_placement_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_credit_section'
		);

		add_settings_field(
			'bodybmca_open_credit_new_tab',
			esc_html__( 'Open credit link in new tab', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_open_credit_new_tab_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_credit_section'
		);

		add_settings_field(
			'bodybmca_credit_link_rel',
			esc_html__( 'Credit link rel attribute', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_credit_link_rel_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_credit_section'
		);

		add_settings_section(
			'bodybmca_schema_section',
			esc_html__( 'Schema Markup', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_schema_section_description' ),
			'bodymetric-bmi-calculator'
		);

		add_settings_field(
			'bodybmca_enable_faq_schema',
			esc_html__( 'Enable FAQ schema', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_enable_faq_schema_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_schema_section'
		);

		add_settings_field(
			'bodybmca_enable_calculator_schema',
			esc_html__( 'Enable calculator schema', 'bodymetric-bmi-calculator' ),
			array( $this, 'render_enable_calculator_schema_field' ),
			'bodymetric-bmi-calculator',
			'bodybmca_schema_section'
		);

		for ( $index = 1; $index <= 3; $index++ ) {
			add_settings_field(
				'bodybmca_faq_question_' . $index,
				sprintf(
					/* translators: %d: FAQ item number. */
					esc_html__( 'FAQ question %d', 'bodymetric-bmi-calculator' ),
					$index
				),
				array( $this, 'render_faq_question_field' ),
				'bodymetric-bmi-calculator',
				'bodybmca_schema_section',
				array(
					'index' => $index,
				)
			);

			add_settings_field(
				'bodybmca_faq_answer_' . $index,
				sprintf(
					/* translators: %d: FAQ item number. */
					esc_html__( 'FAQ answer %d', 'bodymetric-bmi-calculator' ),
					$index
				),
				array( $this, 'render_faq_answer_field' ),
				'bodymetric-bmi-calculator',
				'bodybmca_schema_section',
				array(
					'index' => $index,
				)
			);
		}
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
		$input    = is_array( $input ) ? $input : array();

		$output['default_unit'] = ( isset( $input['default_unit'] ) && in_array( $input['default_unit'], array( 'metric', 'imperial' ), true ) )
			? $input['default_unit']
			: $defaults['default_unit'];

		$theme = isset( $input['default_theme'] ) ? sanitize_key( $input['default_theme'] ) : '';

		if ( 'default' === $theme ) {
			$theme = 'modern';
		}

		$output['default_theme'] = in_array( $theme, array( 'modern', 'minimal' ), true )
			? $theme
			: $defaults['default_theme'];

		$output['disclaimer_text'] = isset( $input['disclaimer_text'] ) ? sanitize_textarea_field( $input['disclaimer_text'] ) : $defaults['disclaimer_text'];
		$output['show_credit_link'] = isset( $input['show_credit_link'] ) ? 1 : 0;
		$output['credit_link_text'] = isset( $input['credit_link_text'] ) ? sanitize_text_field( $input['credit_link_text'] ) : $defaults['credit_link_text'];
		$output['credit_link_url'] = isset( $input['credit_link_url'] ) ? esc_url_raw( $input['credit_link_url'] ) : $defaults['credit_link_url'];
		$output['credit_link_placement'] = ( isset( $input['credit_link_placement'] ) && in_array( $input['credit_link_placement'], self::$credit_placements, true ) )
			? $input['credit_link_placement']
			: $defaults['credit_link_placement'];
		$output['open_credit_new_tab'] = isset( $input['open_credit_new_tab'] ) ? 1 : 0;
		$output['enable_faq_schema'] = isset( $input['enable_faq_schema'] ) ? 1 : 0;
		$output['enable_calculator_schema'] = isset( $input['enable_calculator_schema'] ) ? 1 : 0;

		if ( '' === $output['disclaimer_text'] ) {
			$output['disclaimer_text'] = $defaults['disclaimer_text'];
		}

		if ( '' === $output['credit_link_text'] ) {
			$output['credit_link_text'] = $defaults['credit_link_text'];
		}

		if ( empty( $output['credit_link_url'] ) ) {
			$output['credit_link_url'] = $defaults['credit_link_url'];
		}

		foreach ( self::get_color_defaults() as $field_key => $default_color ) {
			$sanitized_color = isset( $input[ $field_key ] ) ? sanitize_hex_color( $input[ $field_key ] ) : null;
			$output[ $field_key ] = $sanitized_color ? $sanitized_color : $default_color;
		}

		for ( $index = 1; $index <= 3; $index++ ) {
			$question_key = 'faq_question_' . $index;
			$answer_key   = 'faq_answer_' . $index;

			$output[ $question_key ] = isset( $input[ $question_key ] ) ? sanitize_text_field( $input[ $question_key ] ) : '';
			$output[ $answer_key ]   = isset( $input[ $answer_key ] ) ? sanitize_textarea_field( $input[ $answer_key ] ) : '';
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
			<h1><?php echo esc_html__( 'BodyMetric BMI Calculator', 'bodymetric-bmi-calculator' ); ?></h1>
			<p><?php echo esc_html__( 'Configure calculator defaults, colors, visible credit behavior, and optional schema output. This plugin does not add hidden backlinks or tracking.', 'bodymetric-bmi-calculator' ); ?></p>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'bodybmca_settings' );
				do_settings_sections( 'bodymetric-bmi-calculator' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render general section description.
	 *
	 * @return void
	 */
	public function render_general_section_description() {
		echo '<p>' . esc_html__( 'Set the default calculator behavior used when shortcode attributes are not provided.', 'bodymetric-bmi-calculator' ) . '</p>';
	}

	/**
	 * Render colors section description.
	 *
	 * @return void
	 */
	public function render_colors_section_description() {
		echo '<p>' . esc_html__( 'Customize the frontend card using scoped CSS variables. Invalid values fall back to safe defaults.', 'bodymetric-bmi-calculator' ) . '</p>';
	}

	/**
	 * Render credit section description.
	 *
	 * @return void
	 */
	public function render_credit_section_description() {
		echo '<p>' . esc_html__( 'The credit link is always optional, visible when enabled, and never rendered as a hidden or forced backlink.', 'bodymetric-bmi-calculator' ) . '</p>';
	}

	/**
	 * Render schema section description.
	 *
	 * @return void
	 */
	public function render_schema_section_description() {
		echo '<p>' . esc_html__( 'Schema markup is output only on pages where the calculator shortcode appears, and only once per page.', 'bodymetric-bmi-calculator' ) . '</p>';
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
			<option value="metric" <?php selected( $options['default_unit'], 'metric' ); ?>><?php echo esc_html__( 'Metric', 'bodymetric-bmi-calculator' ); ?></option>
			<option value="imperial" <?php selected( $options['default_unit'], 'imperial' ); ?>><?php echo esc_html__( 'Imperial', 'bodymetric-bmi-calculator' ); ?></option>
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
			<option value="modern" <?php selected( $options['default_theme'], 'modern' ); ?>><?php echo esc_html__( 'Modern', 'bodymetric-bmi-calculator' ); ?></option>
			<option value="minimal" <?php selected( $options['default_theme'], 'minimal' ); ?>><?php echo esc_html__( 'Minimal', 'bodymetric-bmi-calculator' ); ?></option>
		</select>
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

	/**
	 * Render a color field.
	 *
	 * @param array $args Field args.
	 * @return void
	 */
	public function render_color_field( $args ) {
		$options   = $this->get_options();
		$field_key = isset( $args['field_key'] ) ? sanitize_key( $args['field_key'] ) : '';
		$value     = isset( $options[ $field_key ] ) ? $options[ $field_key ] : '';
		?>
		<input
			class="regular-text bodybmca-color-field"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $field_key ); ?>]"
			type="text"
			pattern="^#[A-Fa-f0-9]{6}$"
			value="<?php echo esc_attr( $value ); ?>"
		/>
		<?php
	}

	/**
	 * Render show credit link field.
	 *
	 * @return void
	 */
	public function render_show_credit_link_field() {
		$options = $this->get_options();
		?>
		<label for="bodybmca-show-credit-link">
			<input
				id="bodybmca-show-credit-link"
				name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_credit_link]"
				type="checkbox"
				value="1"
				<?php checked( (int) $options['show_credit_link'], 1 ); ?>
			/>
			<?php echo esc_html__( 'Display a visible credit link when enabled by the site owner.', 'bodymetric-bmi-calculator' ); ?>
		</label>
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
	 * Render credit placement field.
	 *
	 * @return void
	 */
	public function render_credit_link_placement_field() {
		$options = $this->get_options();
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[credit_link_placement]">
			<option value="under_calculator" <?php selected( $options['credit_link_placement'], 'under_calculator' ); ?>><?php echo esc_html__( 'Under calculator', 'bodymetric-bmi-calculator' ); ?></option>
			<option value="under_result" <?php selected( $options['credit_link_placement'], 'under_result' ); ?>><?php echo esc_html__( 'Under result', 'bodymetric-bmi-calculator' ); ?></option>
			<option value="footer" <?php selected( $options['credit_link_placement'], 'footer' ); ?>><?php echo esc_html__( 'Footer', 'bodymetric-bmi-calculator' ); ?></option>
			<option value="none" <?php selected( $options['credit_link_placement'], 'none' ); ?>><?php echo esc_html__( 'None', 'bodymetric-bmi-calculator' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Render open credit in new tab field.
	 *
	 * @return void
	 */
	public function render_open_credit_new_tab_field() {
		$options = $this->get_options();
		?>
		<label for="bodybmca-open-credit-new-tab">
			<input
				id="bodybmca-open-credit-new-tab"
				name="<?php echo esc_attr( self::OPTION_NAME ); ?>[open_credit_new_tab]"
				type="checkbox"
				value="1"
				<?php checked( (int) $options['open_credit_new_tab'], 1 ); ?>
			/>
			<?php echo esc_html__( 'Open the visible credit link in a new tab.', 'bodymetric-bmi-calculator' ); ?>
		</label>
		<?php
	}

	/**
	 * Render rel attribute note.
	 *
	 * @return void
	 */
	public function render_credit_link_rel_field() {
		echo '<code>nofollow sponsored noopener noreferrer</code>';
		echo '<p class="description">' . esc_html__( 'The free version always keeps nofollow and sponsored on the visible credit link.', 'bodymetric-bmi-calculator' ) . '</p>';
	}

	/**
	 * Render enable FAQ schema field.
	 *
	 * @return void
	 */
	public function render_enable_faq_schema_field() {
		$options = $this->get_options();
		?>
		<label for="bodybmca-enable-faq-schema">
			<input
				id="bodybmca-enable-faq-schema"
				name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_faq_schema]"
				type="checkbox"
				value="1"
				<?php checked( (int) $options['enable_faq_schema'], 1 ); ?>
			/>
			<?php echo esc_html__( 'Output FAQPage JSON-LD when the calculator shortcode is present and valid FAQ items exist.', 'bodymetric-bmi-calculator' ); ?>
		</label>
		<?php
	}

	/**
	 * Render enable calculator schema field.
	 *
	 * @return void
	 */
	public function render_enable_calculator_schema_field() {
		$options = $this->get_options();
		?>
		<label for="bodybmca-enable-calculator-schema">
			<input
				id="bodybmca-enable-calculator-schema"
				name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_calculator_schema]"
				type="checkbox"
				value="1"
				<?php checked( (int) $options['enable_calculator_schema'], 1 ); ?>
			/>
			<?php echo esc_html__( 'Output WebApplication JSON-LD when the calculator shortcode is present.', 'bodymetric-bmi-calculator' ); ?>
		</label>
		<?php
	}

	/**
	 * Render FAQ question field.
	 *
	 * @param array $args Field args.
	 * @return void
	 */
	public function render_faq_question_field( $args ) {
		$options = $this->get_options();
		$index   = isset( $args['index'] ) ? (int) $args['index'] : 1;
		$key     = 'faq_question_' . $index;
		?>
		<input
			class="regular-text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $key ); ?>]"
			type="text"
			value="<?php echo esc_attr( $options[ $key ] ); ?>"
		/>
		<?php
	}

	/**
	 * Render FAQ answer field.
	 *
	 * @param array $args Field args.
	 * @return void
	 */
	public function render_faq_answer_field( $args ) {
		$options = $this->get_options();
		$index   = isset( $args['index'] ) ? (int) $args['index'] : 1;
		$key     = 'faq_answer_' . $index;
		?>
		<textarea
			class="large-text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $key ); ?>]"
			rows="3"
		><?php echo esc_textarea( $options[ $key ] ); ?></textarea>
		<?php
	}

	/**
	 * Get color field labels.
	 *
	 * @return array<string, string>
	 */
	private function get_color_field_map() {
		return array(
			'primary_color'       => esc_html__( 'Primary color', 'bodymetric-bmi-calculator' ),
			'primary_hover_color' => esc_html__( 'Primary hover color', 'bodymetric-bmi-calculator' ),
			'card_background'     => esc_html__( 'Card background color', 'bodymetric-bmi-calculator' ),
			'text_color'          => esc_html__( 'Text color', 'bodymetric-bmi-calculator' ),
			'muted_text_color'    => esc_html__( 'Muted text color', 'bodymetric-bmi-calculator' ),
			'border_color'        => esc_html__( 'Border color', 'bodymetric-bmi-calculator' ),
			'result_background'   => esc_html__( 'Result background color', 'bodymetric-bmi-calculator' ),
			'success_color'       => esc_html__( 'Success/category color', 'bodymetric-bmi-calculator' ),
			'warning_color'       => esc_html__( 'Warning/category color', 'bodymetric-bmi-calculator' ),
			'danger_color'        => esc_html__( 'Danger/category color', 'bodymetric-bmi-calculator' ),
		);
	}

	/**
	 * Migrate legacy saved options to the unique prefixed option name.
	 *
	 * @return void
	 */
	private function maybe_migrate_legacy_options() {
		$new_options = get_option( self::OPTION_NAME, false );

		if ( false !== $new_options ) {
			return;
		}

		foreach ( self::LEGACY_OPTION_NAMES as $legacy_option_name ) {
			$legacy_options = get_option( $legacy_option_name, false );

			if ( false !== $legacy_options ) {
				update_option( self::OPTION_NAME, $legacy_options );
				return;
			}
		}
	}
}

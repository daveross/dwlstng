<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Cannot access pages directly.' );
}

class DavesWordPressLiveSearchAdmin {

	const option_group = 'daves-wordpress-live-search';

	const SETTINGS_PAGE_SLUG = 'daves-wordpress-live-search';

	/**
	 * Contains the slug of the settings page once it's registered
	 *
	 * @var
	 */
	protected $_settings_page_hook;

	function __construct() {

		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

	}

	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	public static function default_id_from_name( $options ) {
		return isset( $options['id'] ) ? $options : array_merge(array('id' => $options['name']), $options);
	}

	public function admin_enqueue_scripts() {

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script(
			'daves-wordpress-live-search-color-picker',
			DWLS_TNG_URL . '/js/src/color-picker.js',
			array( 'wp-color-picker' ),
			DWLS_TNG_VERSION,
			true
		);

	}

	public function add_settings_page() {
		$this->_settings_page_hook = add_options_page(
			'Live Search',
			'Live Search',
			'manage_options',
			self::SETTINGS_PAGE_SLUG,
			array(
				$this,
				'render_page',
			)
		);
	}

	public function register_settings() {

		add_settings_section(
			self::option_group,
			'',
			'__return_empty_string',
			$this->_settings_page_hook
		);

		// Headlines & Messages
		register_setting( self::option_group, $this->_settings_page_hook . '_max_results', 'absint' );
		register_setting( self::option_group, $this->_settings_page_hook . '_results_direction', array(
			$this,
			'validate_results_direction'
		) );
		register_setting( self::option_group, $this->_settings_page_hook . '_minchars', 'absint' );
		register_setting( self::option_group, $this->_settings_page_hook . '_display_post_meta', array(
			$this,
			'validate_boolean_string'
		) );
		register_setting( self::option_group, $this->_settings_page_hook . '_display_thumbnail', array(
			$this,
			'validate_boolean_string'
		) );
		register_setting( self::option_group, $this->_settings_page_hook . '_display_excerpt', array(
			$this,
			'validate_boolean_string'
		) );
		register_setting( self::option_group, $this->_settings_page_hook . '_excerpt_length', 'absint' );
		register_setting( self::option_group, $this->_settings_page_hook . '_more_results', array(
			$this,
			'validate_boolean_string'
		) );

		register_setting( self::option_group, $this->_settings_page_hook . '_results_width', 'absint' );

		register_setting( self::option_group, $this->_settings_page_hook . '_title_color', array($this, 'sanitize_hex_color') );
		register_setting( self::option_group, $this->_settings_page_hook . '_fg_color', array($this, 'sanitize_hex_color') );
		register_setting( self::option_group, $this->_settings_page_hook . '_bg_color', array($this, 'sanitize_hex_color') );
		register_setting( self::option_group, $this->_settings_page_hook . '_hover_bg_color', array($this, 'sanitize_hex_color') );
		register_setting( self::option_group, $this->_settings_page_hook . '_divider_color', array($this, 'sanitize_hex_color') );
		register_setting( self::option_group, $this->_settings_page_hook . '_footer_bg_color', array($this, 'sanitize_hex_color') );
		register_setting( self::option_group, $this->_settings_page_hook . '_footer_fg_color', array($this, 'sanitize_hex_color') );
		register_setting( self::option_group, $this->_settings_page_hook . '_shadow',  array(
			$this,
			'validate_boolean_string'
		)  );

		add_settings_field(
			$this->_settings_page_hook . '_max_results',
			'Max # results',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_max_results',
				'type'  => 'number',
				'value' => get_option( $this->_settings_page_hook . '_max_results', 10 ),
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_results_direction',
			'Results direction',
			array( $this, 'select_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'    => $this->_settings_page_hook . '_results_direction',
				'options' => array( 'up' => 'Up', 'down' => 'Down' ),
				'value'   => get_option( $this->_settings_page_hook . '_results_direction', 'down' ),
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_minchars',
			'Minimum characters to search',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_minchars',
				'value' => get_option( $this->_settings_page_hook . '_minchars', '3' ),
				'type'  => 'number',
			)
		);

		add_settings_section(
			self::option_group,
			'Design',
			'__return_empty_string',
			$this->_settings_page_hook
		);

		add_settings_field(
			$this->_settings_page_hook . '_display_post_meta',
			'Display post metadata',
			array( $this, 'checkbox_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_display_post_meta',
				'value' => get_option( $this->_settings_page_hook . '_display_post_meta', 'true' ),
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_display_thumbnail',
			'Display thumbnail',
			array( $this, 'checkbox_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_display_thumbnail',
				'value' => get_option( $this->_settings_page_hook . '_display_thumbnail', 'true' ),
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_display_excerpt',
			'Display excerpt',
			array( $this, 'checkbox_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_display_excerpt',
				'value' => get_option( $this->_settings_page_hook . '_display_excerpt', 'true' ),
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_excerpt_length',
			'Excerpt length',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_excerpt_length',
				'value' => get_option( $this->_settings_page_hook . '_excerpt_length', 30 ),
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_more_results',
			'Show "more results" link',
			array( $this, 'checkbox_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_more_results',
				'value' => get_option( $this->_settings_page_hook . '_more_results', 'true' ),
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_results_width',
			'Results box width',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_results_width',
				'value' => get_option( $this->_settings_page_hook . '_results_width', '300' ),
				'type'  => 'number',
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_title_color',
			'Title color',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_title_color',
				'value' => get_option( $this->_settings_page_hook . '_title_color', '#aaaadd' ),
				'class' => 'dwls_color_picker',
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_fg_color',
			'Text color',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_fg_color',
				'value' => get_option( $this->_settings_page_hook . '_fg_color', '#aaaadd' ),
				'class' => 'dwls_color_picker',
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_bg_color',
			'Background color',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_bg_color',
				'value' => get_option( $this->_settings_page_hook . '_bg_color', '#111133' ),
				'class' => 'dwls_color_picker',
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_hover_bg_color',
			'Hover background color',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_hover_bg_color',
				'value' => get_option( $this->_settings_page_hook . '_hover_bg_color', '#444477' ),
				'class' => 'dwls_color_picker',
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_divider_color',
			'Divider color',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_divider_color',
				'value' => get_option( $this->_settings_page_hook . '_divider_color', '#111122' ),
				'class' => 'dwls_color_picker',
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_footer_bg_color',
			'Footer background color',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_footer_bg_color',
				'value' => get_option( $this->_settings_page_hook . '_footer_bg_color', '#555577' ),
				'class' => 'dwls_color_picker',
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_footer_fg_color',
			'Footer foreground color',
			array( $this, 'text_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_footer_fg_color',
				'value' => get_option( $this->_settings_page_hook . '_footer_fg_color', '#ffffff' ),
				'class' => 'dwls_color_picker',
			)
		);

		add_settings_field(
			$this->_settings_page_hook . '_shadow',
			'Shadow',
			array( $this, 'checkbox_field' ),
			$this->_settings_page_hook,
			self::option_group,
			array(
				'name'  => $this->_settings_page_hook . '_shadow',
				'value' => get_option( $this->_settings_page_hook . '_shadow', 'false' ),
			)
		);

	}

	public function text_field( $options ) {

		$options = array_merge(
			array(
				'name'  => '',
				'class' => '',
				'type'  => 'text',
				'value' => '',
			),
			self::default_id_from_name( $options )
		);

		echo '<input type="' . esc_attr( $options['type'] ) . '" id="' . esc_attr( $options['id'] ) . '" name="' . esc_attr( $options['name'] ) . '" value="' . esc_attr( $options['value'] ) . '" class="' . esc_attr( $options['class'] ) . '" />';

	}

	public function select_field( $options ) {

		$options = array_merge(
			array(
				'name'    => '',
				'class'   => '',
				'options' => array(),
				'value'   => '',
			),
			self::default_id_from_name( $options )
		);

		echo '<select id="' . esc_attr( $options['id'] ) . '" name="' . esc_attr( $options['name'] ) . '">';

		foreach ( $options['options'] as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '" ' . selected( $options['value'], $value, false ) . '>' . esc_html( $label ) . '</option>';
		}

		echo '</select>';

	}

	public function checkbox_field( $options ) {

		$options = array_merge(
			array(
				'name'  => '',
				'class' => '',
				'value' => '',
			),
			self::default_id_from_name( $options )
		);

		echo '<input type="hidden" name="' . esc_attr( $options['name'] ) . '" id="' . esc_attr( $options['id'] ) . '" value="false" />';
		echo '<input type="checkbox" name="' . esc_attr( $options['name'] ) . '" id="' . esc_attr( $options['id'] ) . '" class="' . esc_attr( $options['class'] ) . '" value="true" ' . checked( 'true', $options['value'], false ) . '/>';

	}


	public function render_page() {
		?>
		<h2>Dave's WordPress Live Search</h2>
		<form action="options.php" method="post" class="daves-wordpress-live-search-settings-form" style="max-width: 550px;">
			<?php
			settings_fields( self::option_group );
			do_settings_sections( $this->_settings_page_hook );

			submit_button( 'Submit' );
			?>
		</form>
	<?php
	}

	public function validate_results_direction( $value ) {
		return ( 'up' === $value ) ? 'up' : 'down';
	}

	public function validate_boolean_string( $value ) {
		return ( 'true' === $value ) ? 'true' : 'false';
	}

	/**
	 * Sanitizes a hex color.
	 *
	 * Returns either '', a 3 or 6 digit hex color (with #), or null.
	 * For sanitizing values without a #, see sanitize_hex_color_no_hash().
	 *
	 * @since 3.4.0
	 *
	 * @param string $color
	 * @return string|null
	 */
	public function sanitize_hex_color( $color ) {
		if ( '' === $color )
			return '';

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) )
			return $color;

		return null;
	}

}

$DavesWordPressLiveSearchAdmin = new DavesWordPressLiveSearchAdmin();

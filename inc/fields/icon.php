<?php
defined( 'ABSPATH' ) || die;

/**
 * The icon field.
 */
class RWMB_Icon_Field extends RWMB_Select_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-select2', RWMB_CSS_URL . 'select2/select2.css', [], '4.0.10' );
		wp_register_script( 'rwmb-select2', RWMB_JS_URL . 'select2/select2.min.js', [ 'jquery' ], '4.0.10', true );

		// Localize.
		$dependencies = [ 'rwmb-select2', 'rwmb-select', 'underscore' ];
		$locale       = str_replace( '_', '-', get_locale() );
		$locale_short = substr( $locale, 0, 2 );
		$locale       = file_exists( RWMB_DIR . "js/select2/i18n/$locale.js" ) ? $locale : $locale_short;

		if ( file_exists( RWMB_DIR . "js/select2/i18n/$locale.js" ) ) {
			wp_register_script( 'rwmb-select2-i18n', RWMB_JS_URL . "select2/i18n/$locale.js", [ 'rwmb-select2' ], '4.0.10', true );
			$dependencies[] = 'rwmb-select2-i18n';
		}

		wp_enqueue_style( 'rwmb-icon', RWMB_CSS_URL . 'icon.css', [], RWMB_VER );
		wp_enqueue_script( 'rwmb-icon', RWMB_JS_URL . 'icon.js', $dependencies, RWMB_VER, true );

		self::enqueue_style();
	}

	private static function enqueue_style() {
		wp_enqueue_style( 'rwmb-fontawesome', RWMB_CSS_URL . 'fontawesome/fontawesome.css', [], RWMB_VER );
	}

	public static function html( $meta, $field ) {
		$attributes                  = static::get_attributes( $field, $meta );
		$attributes['data-selected'] = $meta;
		$walker                      = new RWMB_Walker_Select( $field, $meta );

		$output = sprintf( '<select %s>', self::render_attributes( $attributes ) );

		if ( ! $field['multiple'] && $field['placeholder'] ) {
			$output .= '<option value="">' . esc_html( $field['placeholder'] ) . '</option>';
		}

		$options = self::get_list_fonts();
		$output .= $walker->walk( $options, $field['flatten'] ? -1 : 0 );
		$output .= '</select>';
		$output .= parent::get_select_all_html( $field );
		return $output;
	}

	private static function get_list_fonts() {
		$icons     = json_decode( file_get_contents( RWMB_CSS_DIR . 'fontawesome/icons.json' ), true );
		$icon_list = [];

		foreach ( $icons as $key => $icon ) {
			$obj        = new stdClass();
			$obj->label = $icon['label'];
			$icon_style = $icon['styles'];

			$font_prefix = $icon_style[0] === 'brands' ? 'fab' : 'fas';
			$obj->value  = $font_prefix . ' fa-' . $icon_style[0] . ' fa-' . $key;

			$icon_list[ $key ] = $obj;
		}

		return $icon_list;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'js_options'  => [],
			'icon_set'    => 'fontawesome',
			'placeholder' => __( 'Select an icon', 'meta-box' ),
		] );

		$field = parent::normalize( $field );

		$field['js_options'] = wp_parse_args( $field['js_options'], [
			'allowClear'        => true,
			'dropdownAutoWidth' => true,
			'placeholder'       => $field['placeholder'],
			'width'             => 'style',
		] );

		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, [
			'value'        => $value,
			'data-options' => wp_json_encode( $field['js_options'] ),
		] );

		return $attributes;
	}

	/**
	 * Format value for the helper functions.
	 *
	 * @param array        $field   Field parameters.
	 * @param string|array $value   The field meta value.
	 * @param array        $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null     $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_value( $field, $value, $args, $post_id ) {
		// Enqueue style for frontend
		self::enqueue_style();

		$value  = parent::call( 'get_value', $field, $args, $post_id );
		$output = sprintf( '<i class="%s"></i>', $value );
		return $output;
	}
}

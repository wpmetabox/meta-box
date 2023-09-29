<?php
defined( 'ABSPATH' ) || die;

/**
 * The icon field.
 */
class RWMB_Icon_Field extends RWMB_Select_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_script( 'fontawesome-kit', 'https://kit.fontawesome.com/780364a977.js', array(), null, false );

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
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, [
			'js_options'  => [],
			'placeholder' => __( 'Select an item', 'meta-box' ),
		] );

		$field = parent::normalize( $field );

		$field['js_options'] = wp_parse_args( $field['js_options'], [
			'type'              => 'icon',
			'icon_set'          => 'fontawesome',
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
			'type'         => $field['type'],
			'icon_set'     => $field['icon_set'],
			'data-options' => wp_json_encode( $field['js_options'] ),
		] );
		if ( isset( $field['size'] ) ) {
			$attributes['size'] = $field['size'];
		}

		return $attributes;
	}

	public static function render_attributes( array $attributes ) : string {
		$output = '';

		$attributes = array_filter( $attributes, 'RWMB_Helpers_Value::is_valid_for_attribute' );
		foreach ( $attributes as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = wp_json_encode( $value );
			}

			$output .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
		}

		return $output;
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

		$value = parent::call( 'get_value', $field, $args, $post_id );
		if ( ! ( $field['type'] == 'icon' ) ) {
			return '';
		}
		$output = sprintf(
			'<i class="%s" id="%s"></i>',
			$value,
			$field['id'],
			$field['name'],
			$field['type']
		);
		return $output;
	}
}

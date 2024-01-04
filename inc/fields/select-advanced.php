<?php
defined( 'ABSPATH' ) || die;

/**
 * The beautiful select field using select2 library.
 */
class RWMB_Select_Advanced_Field extends RWMB_Select_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-select2', RWMB_CSS_URL . 'select2/select2.css', [], '4.0.10' );
		wp_style_add_data( 'rwmb-select2', 'path', RWMB_CSS_DIR . 'select2/select2.css' );

		wp_enqueue_style( 'rwmb-select-advanced', RWMB_CSS_URL . 'select-advanced.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-select-advanced', 'path', RWMB_CSS_DIR . 'select-advanced.css' );

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

		wp_enqueue_script( 'rwmb-select-advanced', RWMB_JS_URL . 'select-advanced.js', $dependencies, RWMB_VER, true );
		RWMB_Helpers_Field::localize_script_once( 'rwmb-select-advanced', 'rwmbSelect2', [
			'isAdmin' => is_admin(),
		]);
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'js_options'  => [],
			'placeholder' => __( 'Select an item', 'meta-box' ),
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
			'data-options' => wp_json_encode( $field['js_options'] ),
		] );

		return $attributes;
	}
}

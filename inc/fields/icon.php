<?php
defined( 'ABSPATH' ) || die;

/**
 * The icon field.
 */
class RWMB_Icon_Field extends RWMB_Select_Advanced_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();

		wp_enqueue_style( 'rwmb-icon', RWMB_CSS_URL . 'icon.css', [], RWMB_VER );
		wp_enqueue_script( 'rwmb-icon', RWMB_JS_URL . 'icon.js', [ 'rwmb-select2', 'rwmb-select', 'underscore' ], RWMB_VER, true );

		self::enqueue_style();
	}

	private static function enqueue_style() {
		wp_enqueue_style( 'rwmb-fontawesome', RWMB_CSS_URL . 'fontawesome/all.min.css', [], RWMB_VER );
	}

	private static function get_list_fonts() {
		$icons     = json_decode( file_get_contents( RWMB_CSS_DIR . 'fontawesome/icons.json' ), true );
		$icon_list = [];

		foreach ( $icons as $key => $icon ) {
			$icon_entry = [
				'label' => $icon['label'],
			];
			$icon_style = $icon['styles'];

			// $font_prefix = $icon_style[0] === 'brands' ? 'fab' : 'fas';
			$icon_entry['value'] = 'fa-' . $icon_style[0] . ' fa-' . $key;

			// Append the icon entry to the $icon_list array
			$icon_list[] = $icon_entry;
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
			'dropdownAutoWidth' => false,
			'placeholder'       => $field['placeholder'],
			'width'             => 'style',
			'showIcon'          => true,
		] );

		$field['options'] = self::get_list_fonts();

		return $field;
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

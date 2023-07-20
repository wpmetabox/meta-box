<?php
defined( 'ABSPATH' ) || die;

/**
 * The sidebar select field.
 */
class RWMB_Sidebar_Field extends RWMB_Object_Choice_Field {
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'placeholder' => __( 'Select a sidebar', 'meta-box' ),
		] );

		$field = parent::normalize( $field );

		return $field;
	}

	public static function query( $meta, array $field ) : array {
		global $wp_registered_sidebars;
		$options = [];
		foreach ( $wp_registered_sidebars as $sidebar ) {
			$options[ $sidebar['id'] ] = [
				'value' => $sidebar['id'],
				'label' => $sidebar['name'],
			];
		}
		return $options;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param string   $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		if ( ! is_active_sidebar( $value ) ) {
			return '';
		}
		ob_start();
		dynamic_sidebar( $value );
		return ob_get_clean();
	}
}

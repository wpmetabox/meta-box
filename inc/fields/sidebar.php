<?php
/**
 * The sidebar select field.
 *
 * @package Meta Box
 */

/**
 * Sidebar field class.
 */
class RWMB_Sidebar_Field extends RWMB_Object_Choice_Field {
	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		// Set default field args.
		$field = parent::normalize( $field );

		// Prevent select tree for user since it's not hierarchical.
		$field['field_type'] = 'select_tree' === $field['field_type'] ? 'select' : $field['field_type'];

		// Set to always flat.
		$field['flatten'] = true;

		// Set default placeholder.
		$field['placeholder'] = empty( $field['placeholder'] ) ? __( 'Select a sidebar', 'meta-box' ) : $field['placeholder'];

		return $field;
	}

	/**
	 * Get users.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function get_options( $field ) {
		global $wp_registered_sidebars;
		$options = array();
		foreach ( $wp_registered_sidebars as $sidebar ) {
			$options[] = (object) $sidebar;
		}
		return $options;
	}

	/**
	 * Get field names of object to be used by walker.
	 *
	 * @return array
	 */
	public static function get_db_fields() {
		return array(
			'parent' => 'parent',
			'id'     => 'id',
			'label'  => 'name',
		);
	}

	/**
	 * Get option label.
	 *
	 * @param array  $field Field parameters.
	 * @param string $value Option value.
	 *
	 * @return string
	 */
	public static function get_option_label( $field, $value ) {
		if ( ! is_active_sidebar( $value ) ) {
			return '';
		}
		ob_start();
		dynamic_sidebar( $value );
		return ob_get_clean();
	}
}

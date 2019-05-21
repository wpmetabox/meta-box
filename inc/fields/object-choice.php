<?php
/**
 * The object choice class which allows users to select specific objects in WordPress.
 *
 * @package Meta Box
 */

/**
 * Abstract field to select an object: post, user, taxonomy, etc.
 */
abstract class RWMB_Object_Choice_Field extends RWMB_Choice_Field {
	/**
	 * Show field HTML.
	 * Populate field options before showing to make sure query is made only once.
	 *
	 * @param array $field   Field parameters.
	 * @param bool  $saved   Whether the meta box is saved at least once.
	 * @param int   $post_id Post ID.
	 */
	public static function show( $field, $saved, $post_id = 0 ) {
		$field['options'] = self::call( $field, 'query' );

		parent::show( $field, $saved, $post_id );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$html = call_user_func( array( self::get_type_class( $field ), 'html' ), $meta, $field );

		if ( $field['add_new'] ) {
			$html .= self::call( 'add_new_form', $field );
		}

		return $html;
	}

	/**
	 * Render "Add New" form
	 *
	 * @param array $field Field settings.
	 * @return string
	 */
	public static function add_new_form( $field ) {
		return '';
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
		$field = wp_parse_args(
			$field,
			array(
				'flatten'    => true,
				'query_args' => array(),
				'field_type' => 'select_advanced',
				'add_new'    => false,
			)
		);

		if ( 'checkbox_tree' === $field['field_type'] ) {
			$field['field_type'] = 'checkbox_list';
			$field['flatten']    = false;
		}
		if ( 'radio_list' === $field['field_type'] ) {
			$field['multiple'] = false;
		}
		if ( 'checkbox_list' === $field['field_type'] ) {
			$field['multiple'] = true;
		}
		return call_user_func( array( self::get_type_class( $field ), 'normalize' ), $field );
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes = call_user_func( array( self::get_type_class( $field ), 'get_attributes' ), $field, $value );
		if ( 'select_advanced' === $field['field_type'] ) {
			$attributes['class'] .= ' rwmb-select_advanced';
		} elseif ( 'select' === $field['field_type'] ) {
			$attributes['class'] .= ' rwmb-select';
		}
		return $attributes;
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		RWMB_Input_List_Field::admin_enqueue_scripts();
		RWMB_Select_Field::admin_enqueue_scripts();
		RWMB_Select_Tree_Field::admin_enqueue_scripts();
		RWMB_Select_Advanced_Field::admin_enqueue_scripts();
	}

	/**
	 * Get correct rendering class for the field.
	 *
	 * @param array $field Field parameters.
	 * @return string
	 */
	protected static function get_type_class( $field ) {
		if ( in_array( $field['field_type'], array( 'checkbox_list', 'radio_list' ), true ) ) {
			return 'RWMB_Input_List_Field';
		}
		return RWMB_Helpers_Field::get_class(
			array(
				'type' => $field['field_type'],
			)
		);
	}
}

<?php
use MetaBox\Support\Arr;

/**
 * The object choice class which allows users to select specific objects (post, user, taxonomy) in WordPress.
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
		// Get unique saved IDs for ajax fields.
		$meta = static::meta( $post_id, $saved, $field );
		$meta = self::filter( 'field_meta', $meta, $field, $saved );
		$meta = Arr::flatten( (array) $meta );
		$meta = array_filter( wp_parse_id_list( $meta ) );
		sort( $meta );

		$field['options'] = static::query( $meta, $field );

		parent::show( $field, $saved, $post_id );
	}

	abstract public static function query( $meta, array $field ) : array;

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$html = call_user_func( [ self::get_type_class( $field ), 'html' ], $meta, $field );

		if ( $field['add_new'] ) {
			$html .= static::add_new_form( $field );
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
		$field = wp_parse_args( $field, [
			'flatten'    => true,
			'query_args' => [],
			'field_type' => 'select_advanced',
			'add_new'    => false,
			'ajax'       => true,
		] );
		if ( 'select_advanced' !== $field['field_type'] ) {
			$field['ajax'] = false;
		}
		if ( 'checkbox_tree' === $field['field_type'] ) {
			$field['field_type'] = 'checkbox_list';
			$field['flatten']    = false;
		}
		if ( 'radio_list' === $field['field_type'] ) {
			$field['field_type'] = 'radio';
		}
		$field = call_user_func( [ self::get_type_class( $field ), 'normalize' ], $field );

		return $field;
	}

	/**
	 * Set ajax parameters.
	 *
	 * @param array $field Field settings.
	 */
	protected static function set_ajax_params( &$field ) {
		if ( ! $field['ajax'] ) {
			return;
		}

		if ( empty( $field['js_options']['ajax'] ) ) {
			$field['js_options']['ajax'] = [];
		}
		$field['js_options']['ajax']      = wp_parse_args(
			[
				'url' => admin_url( 'admin-ajax.php' ),
			],
			$field['js_options']['ajax']
		);
		$field['js_options']['ajax_data'] = [
			'field'    => [
				'id'         => $field['id'],
				'type'       => $field['type'],
				'query_args' => $field['query_args'],
			],
			'_wpnonce' => wp_create_nonce( 'query' ),
		];
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
		$attributes = call_user_func( [ self::get_type_class( $field ), 'get_attributes' ], $field, $value );
		if ( 'select_advanced' === $field['field_type'] ) {
			$attributes['class'] .= ' rwmb-select_advanced';
		} elseif ( 'select' === $field['field_type'] ) {
			$attributes['class'] .= ' rwmb-select';
		}
		return $attributes;
	}

	public static function admin_enqueue_scripts() {
		RWMB_Input_List_Field::admin_enqueue_scripts();
		RWMB_Select_Field::admin_enqueue_scripts();
		RWMB_Select_Tree_Field::admin_enqueue_scripts();
		RWMB_Select_Advanced_Field::admin_enqueue_scripts();
	}

	/**
	 * Get correct rendering class for the field.
	 */
	protected static function get_type_class( array $field ) : string {
		return RWMB_Helpers_Field::get_class( [ 'type' => $field['field_type'] ] );
	}
}

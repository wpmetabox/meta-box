<?php
/**
 * Plugin public functions.
 *
 * @package Meta Box
 */

if ( ! function_exists( 'rwmb_meta' ) ) {
	/**
	 * Get post meta.
	 *
	 * @param string   $key     Meta key. Required.
	 * @param array    $args    Array of arguments. Optional.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed
	 */
	function rwmb_meta( $key, $args = array(), $post_id = null ) {
		$args  = wp_parse_args( $args );
		$field = rwmb_get_field_settings( $key, $args, $post_id );

		/*
		 * If field is not found, which can caused by registering meta boxes for the backend only or conditional registration.
		 * Then fallback to the old method to retrieve meta (which uses get_post_meta() as the latest fallback).
		 */
		if ( false === $field ) {
			return apply_filters( 'rwmb_meta', rwmb_meta_legacy( $key, $args, $post_id ) );
		}
		$meta = in_array( $field['type'], array( 'oembed', 'map', 'osm' ), true ) ?
			rwmb_the_value( $key, $args, $post_id, false ) :
			rwmb_get_value( $key, $args, $post_id );
		return apply_filters( 'rwmb_meta', $meta, $key, $args, $post_id );
	}
}

if ( ! function_exists( 'rwmb_get_field_settings' ) ) {
	/**
	 * Get field settings.
	 *
	 * @param string   $key       Meta key. Required.
	 * @param array    $args      Array of arguments. Optional.
	 * @param int|null $object_id Object ID. null for current post. Optional.
	 *
	 * @return array
	 */
	function rwmb_get_field_settings( $key, $args = array(), $object_id = null ) {
		$args = wp_parse_args(
			$args,
			array(
				'object_type' => 'post',
			)
		);

		/**
		 * Filter meta type from object type and object id.
		 *
		 * @var string     Meta type, default is post type name.
		 * @var string     Object type.
		 * @var string|int Object id.
		 */
		$type = apply_filters( 'rwmb_meta_type', '', $args['object_type'], $object_id );
		if ( ! $type ) {
			$type = get_post_type( $object_id );
		}

		return rwmb_get_registry( 'field' )->get( $key, $type, $args['object_type'] );
	}
}

if ( ! function_exists( 'rwmb_meta_legacy' ) ) {
	/**
	 * Get post meta.
	 *
	 * @param string   $key     Meta key. Required.
	 * @param array    $args    Array of arguments. Optional.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed
	 */
	function rwmb_meta_legacy( $key, $args = array(), $post_id = null ) {
		$args  = wp_parse_args(
			$args,
			array(
				'type'     => 'text',
				'multiple' => false,
				'clone'    => false,
			)
		);
		$field = array(
			'id'       => $key,
			'type'     => $args['type'],
			'clone'    => $args['clone'],
			'multiple' => $args['multiple'],
		);

		$method = 'get_value';
		switch ( $args['type'] ) {
			case 'taxonomy':
			case 'taxonomy_advanced':
				$field['taxonomy'] = $args['taxonomy'];
				break;
			case 'map':
			case 'osm':
			case 'oembed':
				$method = 'the_value';
				break;
		}
		$field = RWMB_Field::call( 'normalize', $field );

		return RWMB_Field::call( $method, $field, $args, $post_id );
	}
}

if ( ! function_exists( 'rwmb_get_value' ) ) {
	/**
	 * Get value of custom field.
	 * This is used to replace old version of rwmb_meta key.
	 *
	 * @param  string   $field_id Field ID. Required.
	 * @param  array    $args     Additional arguments. Rarely used. See specific fields for details.
	 * @param  int|null $post_id  Post ID. null for current post. Optional.
	 *
	 * @return mixed false if field doesn't exist. Field value otherwise.
	 */
	function rwmb_get_value( $field_id, $args = array(), $post_id = null ) {
		$args  = wp_parse_args( $args );
		$field = rwmb_get_field_settings( $field_id, $args, $post_id );

		// Get field value.
		$value = $field ? RWMB_Field::call( 'get_value', $field, $args, $post_id ) : false;

		/*
		 * Allow developers to change the returned value of field.
		 * For version < 4.8.2, the filter name was 'rwmb_get_field'.
		 *
		 * @param mixed    $value   Field value.
		 * @param array    $field   Field parameters.
		 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
		 * @param int|null $post_id Post ID. null for current post. Optional.
		 */
		$value = apply_filters( 'rwmb_get_value', $value, $field, $args, $post_id );

		return $value;
	}
}

if ( ! function_exists( 'rwmb_the_value' ) ) {
	/**
	 * Display the value of a field
	 *
	 * @param  string   $field_id Field ID. Required.
	 * @param  array    $args     Additional arguments. Rarely used. See specific fields for details.
	 * @param  int|null $post_id  Post ID. null for current post. Optional.
	 * @param  bool     $echo     Display field meta value? Default `true` which works in almost all cases. We use `false` for  the [rwmb_meta] shortcode.
	 *
	 * @return string
	 */
	function rwmb_the_value( $field_id, $args = array(), $post_id = null, $echo = true ) {
		$args  = wp_parse_args( $args );
		$field = rwmb_get_field_settings( $field_id, $args, $post_id );

		if ( ! $field ) {
			return '';
		}

		$output = RWMB_Field::call( 'the_value', $field, $args, $post_id );

		/*
		 * Allow developers to change the returned value of field.
		 * For version < 4.8.2, the filter name was 'rwmb_get_field'.
		 *
		 * @param mixed    $value   Field HTML output.
		 * @param array    $field   Field parameters.
		 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
		 * @param int|null $post_id Post ID. null for current post. Optional.
		 */
		$output = apply_filters( 'rwmb_the_value', $output, $field, $args, $post_id );

		if ( $echo ) {
			echo $output; // WPCS: XSS OK.
		}

		return $output;
	}
}

if ( ! function_exists( 'rwmb_get_object_fields' ) ) {
	/**
	 * Get defined meta fields for object.
	 *
	 * @param int|string $type_or_id  Object ID or post type / taxonomy (for terms) / user (for users).
	 * @param string     $object_type Object type. Use post, term.
	 *
	 * @return array
	 */
	function rwmb_get_object_fields( $type_or_id, $object_type = 'post' ) {
		$meta_boxes = rwmb_get_registry( 'meta_box' )->get_by( array( 'object_type' => $object_type ) );
		array_walk( $meta_boxes, 'rwmb_check_meta_box_supports', array( $object_type, $type_or_id ) );
		$meta_boxes = array_filter( $meta_boxes );

		$fields = array();
		foreach ( $meta_boxes as $meta_box ) {
			foreach ( $meta_box->fields as $field ) {
				$fields[ $field['id'] ] = $field;
			}
		}

		return $fields;
	}
}

if ( ! function_exists( 'rwmb_check_meta_box_supports' ) ) {
	/**
	 * Check if a meta box supports an object.
	 *
	 * @param  object $meta_box    Meta Box object.
	 * @param  int    $key         Not used.
	 * @param  array  $object_data Object data (type and ID).
	 */
	function rwmb_check_meta_box_supports( &$meta_box, $key, $object_data ) {
		list( $object_type, $type_or_id ) = $object_data;

		$type = null;
		$prop = null;
		switch ( $object_type ) {
			case 'post':
				$type = is_numeric( $type_or_id ) ? get_post_type( $type_or_id ) : $type_or_id;
				$prop = 'post_types';
				break;
			case 'term':
				$type = $type_or_id;
				if ( is_numeric( $type_or_id ) ) {
					$term = get_term( $type_or_id );
					$type = is_array( $term ) ? $term->taxonomy : null;
				}
				$prop = 'taxonomies';
				break;
			case 'user':
				$type = 'user';
				$prop = 'user';
				break;
			case 'setting':
				$type = 'setting';
				$prop = 'settings_pages';
				break;
		}
		if ( ! $type ) {
			$meta_box = false;
			return;
		}
		if ( isset( $meta_box->meta_box[ $prop ] ) && ! in_array( $type, $meta_box->meta_box[ $prop ], true ) ) {
			$meta_box = false;
		}
	}
}

if ( ! function_exists( 'rwmb_meta_shortcode' ) ) {
	/**
	 * Shortcode to display meta value.
	 *
	 * @param array $atts Shortcode attributes, same as rwmb_meta() function, but has more "meta_key" parameter.
	 *
	 * @return string
	 */
	function rwmb_meta_shortcode( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'id'        => '',
				'object_id' => null,
				'attribute' => '',
			)
		);
		RWMB_Helpers_Array::change_key( $atts, 'post_id', 'object_id' );
		RWMB_Helpers_Array::change_key( $atts, 'meta_key', 'id' );

		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$field_id  = $atts['id'];
		$object_id = $atts['object_id'];
		unset( $atts['id'], $atts['object_id'] );

		$attribute = $atts['attribute'];
		if ( ! $attribute ) {
			return rwmb_the_value( $field_id, $atts, $object_id, false );
		}

		$value = rwmb_get_value( $field_id, $atts, $object_id );

		if ( ! is_array( $value ) && ! is_object( $value ) ) {
			return $value;
		}

		if ( is_object( $value ) ) {
			return $value->$attribute;
		}

		$value = wp_list_pluck( $value, $attribute );
		$value = implode( ',', $value );

		return $value;
	}

	add_shortcode( 'rwmb_meta', 'rwmb_meta_shortcode' );
}

if ( ! function_exists( 'rwmb_get_registry' ) ) {
	/**
	 * Get the registry by type.
	 * Always return the same instance of the registry.
	 *
	 * @param string $type Registry type.
	 *
	 * @return object
	 */
	function rwmb_get_registry( $type ) {
		static $data = array();

		$class = 'RWMB_' . RWMB_Helpers_String::title_case( $type ) . '_Registry';
		if ( ! isset( $data[ $type ] ) ) {
			$data[ $type ] = new $class();
		}

		return $data[ $type ];
	}
}

if ( ! function_exists( 'rwmb_get_storage' ) ) {
	/**
	 * Get storage instance.
	 *
	 * @param string      $object_type Object type. Use post or term.
	 * @param RW_Meta_Box $meta_box    Meta box object. Optional.
	 * @return RWMB_Storage_Interface
	 */
	function rwmb_get_storage( $object_type, $meta_box = null ) {
		$class   = 'RWMB_' . RWMB_Helpers_String::title_case( $object_type ) . '_Storage';
		$class   = class_exists( $class ) ? $class : 'RWMB_Post_Storage';
		$storage = rwmb_get_registry( 'storage' )->get( $class );

		return apply_filters( 'rwmb_get_storage', $storage, $object_type, $meta_box );
	}
}

if ( ! function_exists( 'rwmb_request' ) ) {
	/**
	 * Get request object.
	 *
	 * @return RWMB_Request
	 */
	function rwmb_request() {
		static $request;
		if ( ! $request ) {
			$request = new RWMB_Request();
		}
		return $request;
	}
}

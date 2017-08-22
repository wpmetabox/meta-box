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
		$field = rwmb_get_field_data( $key, $args, $post_id );

		/*
		 * If field is not found, which can caused by registering meta boxes for the backend only or conditional registration.
		 * Then fallback to the old method to retrieve meta (which uses get_post_meta() as the latest fallback).
		 */
		if ( false === $field ) {
			return apply_filters( 'rwmb_meta', rwmb_meta_legacy( $key, $args, $post_id ) );
		}
		$meta = in_array( $field['type'], array( 'oembed', 'map' ), true ) ?
			rwmb_the_value( $key, $args, $post_id, false ) :
			rwmb_get_value( $key, $args, $post_id );
		return apply_filters( 'rwmb_meta', $meta, $key, $args, $post_id );
	}
}

if ( ! function_exists( 'rwmb_get_field_data' ) ) {
	/**
	 * Get field data.
	 *
	 * @param string   $key       Meta key. Required.
	 * @param array    $args      Array of arguments. Optional.
	 * @param int|null $object_id Object ID. null for current post. Optional.
	 *
	 * @return array
	 */
	function rwmb_get_field_data( $key, $args = array(), $object_id = null ) {
		$args = wp_parse_args( $args, array(
			'object_type' => 'post',
		) );
		$type = get_post_type( $object_id );

		/**
		 * Filter meta type from object type and object id.
		 *
		 * @var string     Meta type, default is post type name.
		 * @var string     Object type.
		 * @var string|int Object id.
		 */
		$type = apply_filters( 'rwmb_meta_type', $type, $args['object_type'], $object_id );

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
		$args  = wp_parse_args( $args, array(
			'type'     => 'text',
			'multiple' => false,
			'clone'    => false,
		) );
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
			case 'oembed':
				$method = 'the_value';
				break;
		}
		$field = RWMB_Field::call( 'normalize', $field );

		return RWMB_Field::call( $method, $field, $args, $post_id );
	}
} // End if().

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
		$field = rwmb_get_field_data( $field_id, $args, $post_id );

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
		$field = rwmb_get_field_data( $field_id, $args, $post_id );

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
} // End if().

if ( ! function_exists( 'rwmb_meta_shortcode' ) ) {
	/**
	 * Shortcode to display meta value.
	 *
	 * @param array $atts Shortcode attributes, same as rwmb_meta() function, but has more "meta_key" parameter.
	 *
	 * @return string
	 */
	function rwmb_meta_shortcode( $atts ) {
		$atts = wp_parse_args( $atts, array(
			'post_id' => get_the_ID(),
		) );
		if ( empty( $atts['meta_key'] ) ) {
			return '';
		}

		$field_id = $atts['meta_key'];
		$post_id  = $atts['post_id'];
		unset( $atts['meta_key'], $atts['post_id'] );

		return rwmb_the_value( $field_id, $atts, $post_id, false );
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

		$type  = str_replace( array( '-', '_' ), ' ', $type );
		$class = 'RWMB_' . ucwords( $type ) . '_Registry';
		$class = str_replace( ' ', '_', $class );
		if ( ! isset( $data[ $type ] ) ) {
			$data[ $type ] = new $class();
		}

		return $data[ $type ];
	}
}

if ( ! function_exists( 'rwmb_get_storage_class_name' ) ) {
	/**
	 * Get storage class name.
	 *
	 * @param string $object_type Object type. Use post or term.
	 * @return string
	 */
	function rwmb_get_storage_class_name( $object_type ) {
		$object_type = str_replace( array( '-', '_' ), ' ', $object_type );
		$object_type = ucwords( $object_type );
		$object_type = str_replace( ' ', '_', $object_type );
		$class_name = 'RWMB_' . $object_type . '_Storage';

		if ( ! class_exists( $class_name ) ) {
			$class_name = 'RWMB_Post_Storage';
		}

		return apply_filters( 'rwmb_storage_class_name', $class_name, $object_type );
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
		$class_name = rwmb_get_storage_class_name( $object_type );
		$storage = rwmb_get_registry( 'storage' )->get( $class_name );

		return apply_filters( 'rwmb_get_storage', $storage, $object_type, $meta_box );
	}
}

if ( ! function_exists( 'rwmb_get_meta_box' ) ) {
	/**
	 * Get meta box object from meta box data.
	 *
	 * @param  array $meta_box Array of meta box data.
	 * @return RW_Meta_Box
	 */
	function rwmb_get_meta_box( $meta_box ) {
		/**
		 * Allow filter meta box class name.
		 *
		 * @var string Meta box class name.
		 * @var array  Meta box data.
		 */
		$class_name = apply_filters( 'rwmb_meta_box_class_name', 'RW_Meta_Box', $meta_box );

		return new $class_name( $meta_box );
	}
}

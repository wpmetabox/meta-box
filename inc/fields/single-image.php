<?php
/**
 * The advanced image upload field which uses WordPress media popup to upload and select images.
 *
 * @package Meta Box
 */

/**
 * Image advanced field class.
 */
class RWMB_Single_Image_Field extends RWMB_Image_Advanced_Field {
	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$attributes = self::get_attributes( $field, esc_attr( $meta ) );
		$field['js_options']['maxFiles'] = 1;
		$attributes['data-single-image'] = 1;

		return sprintf(
			'<input %s data-options="%s">',
			self::render_attributes( $attributes ),
			esc_attr( wp_json_encode( $field['js_options'] ) )
		);
	}

	/**
	 * Get the field value. Return meaningful info of the files.
	 *
	 * @param  array    $field   Field parameters.
	 * @param  array    $args    Not used for this field.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Full info of uploaded files
	 */
	public static function get_value( $field, $args = array(), $post_id = null ) {
		$value = RWMB_Field::get_value( $field, $args, $post_id );
		$return = RWMB_Image_Field::file_info( $value, $args );
		return $return;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field   = wp_parse_args( $field, array(
			'max_file_uploads' => 1,
			'max_status'       => false,
			'clone'				=>false,
		) );
		$field = parent::normalize( $field );
		$field['multiple'] = false;
		return $field;
	}
		/**
	 * Get meta values to save.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return array|mixed
	 */
	public static function value( $new, $old, $post_id, $field ) {
		$input = $field['file_input_name'];

		// @codingStandardsIgnoreLine
		if ( empty( $_FILES[ $input ] ) ) {
			return $new;
		}

		// Non-cloneable field.
		if ( ! $field['clone'] ) {
			$count = self::transform( $input );
			for ( $i = 0; $i <= $count; $i ++ ) {
				$attachment = media_handle_upload( "{$input}_{$i}", $post_id );
				if ( ! is_wp_error( $attachment ) ) {
					$new[] = $attachment;
				}
			}
			return $new;
		}

		// Cloneable field.
		$counts = self::transform_cloneable( $input );
		foreach ( $counts as $clone_index => $count ) {
			if ( empty( $new[ $clone_index ] ) ) {
				$new[ $clone_index ] = array();
			}
			for ( $i = 0; $i <= $count; $i ++ ) {
				$attachment = media_handle_upload( "{$input}_{$clone_index}_{$i}", $post_id );
				if ( ! is_wp_error( $attachment ) ) {
					$new[ $clone_index ][] = $attachment;
				}
			}
		}

		return $new;
	}
	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field The field parameters.
	 * @param mixed $value The attribute value.
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes           = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, array(
			'type'        => $field['type'],
		) );
		$attributes['class']  .= ' rwmb-image_advanced';
		return $attributes;
	}
}

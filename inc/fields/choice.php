<?php
defined( 'ABSPATH' ) || die;

/**
 * The abstract choice field.
 */
abstract class RWMB_Choice_Field extends RWMB_Field {

	/**
	 * Show field HTML
	 * Filters are put inside this method, not inside methods such as "meta", "html", "begin_html", etc.
	 * That ensures the returned value are always been applied filters.
	 * This method is not meant to be overwritten in specific fields.
	 *
	 * @param array $field   Field parameters.
	 * @param bool  $saved   Whether the meta box is saved at least once.
	 * @param int   $post_id Post ID.
	 */
	public static function show( array $field, bool $saved, $post_id = 0 ) {
		$meta = self::call( $field, 'meta', $post_id, $saved );
		$meta = self::filter( 'field_meta', $meta, $field, $saved );

		$meta = self::remove_options_deleted( (array) $meta, $field );
		$meta = RWMB_Helpers_Array::array_filter_recursive( $meta );

		$begin = static::begin_html( $field );
		$begin = self::filter( 'begin_html', $begin, $field, $meta );

		// Separate code for cloneable and non-cloneable fields to make easy to maintain.
		if ( $field['clone'] ) {
			$field_html = RWMB_Clone::html( $meta, $field );
		} else {
			// Call separated methods for displaying each type of field.
			$field_html = self::call( $field, 'html', $meta );
			$field_html = self::filter( 'html', $field_html, $field, $meta );
		}

		$end = static::end_html( $field );
		$end = self::filter( 'end_html', $end, $field, $meta );

		$html = self::filter( 'wrapper_html', "$begin$field_html$end", $field, $meta );

		// Display label and input in DIV and allow user-defined classes to be appended.
		$classes = "rwmb-field rwmb-{$field['type']}-wrapper " . $field['class'];
		if ( ! empty( $field['required'] ) ) {
			$classes .= ' required';
		}

		$outer_html = sprintf(
			$field['before'] . '<div class="%s">%s</div>' . $field['after'],
			esc_attr( trim( $classes ) ),
			$html
		);
		$outer_html = self::filter( 'outer_html', $outer_html, $field, $meta );

		echo $outer_html; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * remove_options_deleted
	 * @param array $meta Meta value.
	 * @param array $field Field parameters.
	 * @return array
	 */
	private static function remove_options_deleted( array $meta, array $field ): array {
		if ( ! isset( $field['options'] ) || empty( $meta ) ) {
			return $meta;
		}

		$options = array_keys( $field['options'] );
		array_walk_recursive($meta, function ( &$value ) use ( $options ) {
			if ( ! empty( $value ) && ! in_array( $value, $options ) ) {
				$value = null;
			}
		});

		return array_filter( $meta );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		return '';
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, [
			'flatten' => true,
			'options' => [],
		] );

		// Use callback: function_name format from Meta Box Builder.
		if ( isset( $field['_callback'] ) && is_callable( $field['_callback'] ) ) {
			$field['options'] = call_user_func( $field['_callback'] );
		}

		return $field;
	}

	public static function transform_options( $options ): array {
		$transformed = [];
		$options     = (array) $options;
		foreach ( $options as $value => $label ) {
			$option = is_array( $label ) ? $label : [
				'label' => (string) $label,
				'value' => (string) $value,
			];
			if ( isset( $option['label'] ) && isset( $option['value'] ) ) {
				$transformed[ $option['value'] ] = (object) $option;
			}
		}
		return $transformed;
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
		$options = self::transform_options( $field['options'] );
		return isset( $options[ $value ] ) ? $options[ $value ]->label : '';
	}
}

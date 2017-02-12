<?php
/**
 * The abstract choice field.
 *
 * @package Meta Box
 */

/**
 * Abstract class for any kind of choice field.
 */
abstract class RWMB_Choice_Field extends RWMB_Field {
	/**
	 * Walk options.
	 *
	 * @param array $field     Field parameters.
	 * @param mixed $options   Select options.
	 * @param mixed $db_fields Database fields to use in the output.
	 * @param mixed $meta      Meta value.
	 * @return string
	 */
	public static function walk( $field, $options, $db_fields, $meta ) {
		return '';
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$meta      = (array) $meta;
		$options   = self::call( 'get_options', $field );
		$options   = self::call( 'filter_options', $field, $options );
		$db_fields = self::call( 'get_db_fields', $field );
		return ! empty( $options ) ? self::call( 'walk', $field, $options, $db_fields, $meta ) : null;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'flatten' => true,
			'options' => array(),
		) );

		return $field;
	}

	/**
	 * Get field names of object to be used by walker.
	 *
	 * @return array
	 */
	public static function get_db_fields() {
		return array(
			'parent' => 'parent',
			'id'     => 'value',
			'label'  => 'label',
		);
	}

	/**
	 * Get options for walker.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function get_options( $field ) {
		$options = array();
		foreach ( (array) $field['options'] as $value => $label ) {
			$option = is_array( $label ) ? $label : array(
				'label' => (string) $label,
				'value' => (string) $value,
			);
			if ( isset( $option['label'] ) && isset( $option['value'] ) ) {
				$options[ $option['value'] ] = (object) $option;
			}
		}
		return $options;
	}

	/**
	 * Filter options for walker.
	 *
	 * @param array $field   Field parameters.
	 * @param array $options Array of choice options.
	 *
	 * @return array
	 */
	public static function filter_options( $field, $options ) {
		$db_fields = self::call( 'get_db_fields', $field );
		$label     = $db_fields['label'];
		foreach ( $options as &$option ) {
			$option         = apply_filters( 'rwmb_option', $option, $field );
			$option->$label = apply_filters( 'rwmb_option_label', $option->$label, $option, $field );
		}
		return $options;
	}

	/**
	 * Format a single value for the helper functions.
	 *
	 * @param array  $field Field parameters.
	 * @param string $value Meta value.
	 * @return string
	 */
	public static function format_single_value( $field, $value ) {
		return self::call( 'get_option_label', $field, $value );
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
		$options = self::call( 'get_options', $field );
		return $options[ $value ]->label;
	}
}

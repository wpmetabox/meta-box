<?php
defined( 'ABSPATH' ) || die;

/**
 * The abstract choice field.
 */
abstract class RWMB_Choice_Field extends RWMB_Field {
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

	/**
	 * Get the field settings from a core Ajax request.
	 * Stop the request if the nonce or the action is not correct.
	 *
	 * @return array
	 */
	protected static function get_ajax_field(): array {
		$request = rwmb_request();
		$payload = $request->post( 'field' );
		$payload = is_string( $payload ) ? wp_unslash( $payload ) : '';

		check_ajax_referer( 'rwmb-query-' . $payload );

		$field = json_decode( $payload, true );
		if ( $field['ajax_action'] !== $request->post( 'action' ) ) {
			wp_die( -1, 403 );
		}

		$field['clone']        = false;
		$field['_original_id'] = $field['id'];

		return $field;
	}
}

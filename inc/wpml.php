<?php
/**
 * The WPML compatibility module, allowing all fields are translatable by WPML plugin.
 *
 * @package Meta Box
 */

/**
 * WPML compatibility class
 */
class RWMB_WPML {

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_filter( 'wpml_duplicate_generic_string', array( $this, 'wpml_translate_values' ), 10, 3 );
	}

	/**
	 * Translating IDs stored as field values upon WPML post/page duplication.
	 *
	 * @param mixed  $value           Meta value.
	 * @param string $target_language Target language.
	 * @param array  $meta_data       Meta arguments.
	 * @return mixed
	 */
	public function wpml_translate_values( $value, $target_language, $meta_data ) {
		$fields = RWMB_Core::get_fields();

		foreach ( $fields as $field ) {
			if ( ! in_array( $field['type'], array( 'post', 'taxonomy_advanced' ) ) || $field['id'] !== $meta_data['key'] ) {
				continue;
			}

			// Post type needed for WPML filter differs between fields.
			$post_type = 'taxonomy_advanced' === $field['type'] ? $field['taxonomy'] : $field['post_type'];

			// Translating values, whether are stored as comma separated strings or not.
			if ( false === strpos( $value, ',' ) ) {
				$value = apply_filters( 'wpml_object_id', $value, $post_type, true, $target_language );
				return $value;
			}

			// Dealing with IDs stored as comma separated strings.
			$translated_values = array();
			$values            = explode( ',', $value );

			foreach ( $values as $v ) {
				$translated_values[] = apply_filters( 'wpml_object_id', $v, $post_type, true, $target_language );
			}

			$value = implode( ',', $translated_values );
			return $value;
		}

		return $value;
	}
}

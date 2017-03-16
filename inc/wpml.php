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
	 * List of fields that need to translate values (because they're saved as IDs).
	 *
	 * @var array
	 */
	protected $field_types = array( 'post', 'taxonomy_advanced' );

	/**
	 * Initialize.
	 */
	public function init() {
		/**
		 * Run before meta boxes are registered so it can modify fields.
		 *
		 * @see modify_field()
		 */
		add_action( 'init', array( $this, 'register_hooks' ), 9 );
	}

	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return;
		}
		add_filter( 'wpml_duplicate_generic_string', array( $this, 'translate_ids' ), 10, 3 );
		add_filter( 'rwmb_normalize_field', array( $this, 'modify_field' ) );
	}

	/**
	 * Translating IDs stored as field values upon WPML post/page duplication.
	 *
	 * @param mixed  $value           Meta value.
	 * @param string $target_language Target language.
	 * @param array  $meta_data       Meta arguments.
	 * @return mixed
	 */
	public function translate_ids( $value, $target_language, $meta_data ) {
		if ( 'custom_field' !== $meta_data['context'] ) {
			return $value;
		}

		$field = rwmb_get_registry( 'field' )->get( $meta_data['key'], get_post_type( $meta_data['master_post_id'] ) );
		if ( false !== $field || ! in_array( $field['type'], $this->field_types, true ) ) {
			return $value;
		}

		// Object type needed for WPML filter differs between fields.
		$object_type = 'taxonomy_advanced' === $field['type'] ? $field['taxonomy'] : $field['post_type'];

		// Translating values, whether are stored as comma separated strings or not.
		if ( false === strpos( $value, ',' ) ) {
			$value = apply_filters( 'wpml_object_id', $value, $object_type, true, $target_language );
			return $value;
		}

		// Dealing with IDs stored as comma separated strings.
		$translated_values = array();
		$values            = explode( ',', $value );

		foreach ( $values as $v ) {
			$translated_values[] = apply_filters( 'wpml_object_id', $v, $object_type, true, $target_language );
		}

		$value = implode( ',', $translated_values );
		return $value;
	}

	/**
	 * Modified field depends on its translation status.
	 * If the post is a translated version of another post and the field is set to:
	 * - Do not translate: hide the field.
	 * - Copy: make it disabled so users cannot edit.
	 * - Translate: do nothing.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return mixed
	 */
	public function modify_field( $field ) {
		global $wpml_post_translations;

		if ( empty( $field['id'] ) ) {
			return $field;
		}

		// Get post ID.
		$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $post_id ) {
			$post_id = filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_NUMBER_INT );
		}

		// If the post is the original one: do nothing.
		if ( ! $wpml_post_translations->get_source_lang_code( $post_id ) ) {
			return $field;
		}

		// Get setting for the custom field translation.
		$custom_fields_translation = apply_filters( 'wpml_sub_setting', false, 'translation-management', 'custom_fields_translation' );
		if ( ! isset( $custom_fields_translation[ $field['id'] ] ) ) {
			return $field;
		}

		$setting = intval( $custom_fields_translation[ $field['id'] ] );
		if ( 0 === $setting ) {           // Do not translate: hide it.
			$field['class'] .= ' hidden';
		} elseif ( 1 === $setting ) {     // Copy: disable editing.
			$field['disabled'] = true;
		}

		return $field;
	}
}

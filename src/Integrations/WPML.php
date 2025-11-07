<?php
namespace MetaBox\Integrations;

class WPML {
	/**
	 * List of fields that need to translate values (because they're saved as IDs).
	 *
	 * @var array
	 */
	private $field_types = [ 'post', 'taxonomy_advanced' ];

	public function __construct() {
		// Run before meta boxes are registered (at `init` with priority 20) so it can modify fields.
		add_action( 'init', [ $this, 'init' ] );
	}

	public function init(): void {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return;
		}
		add_filter( 'wpml_duplicate_generic_string', [ $this, 'translate_ids' ], 10, 3 );
		add_filter( 'rwmb_normalize_field', [ $this, 'modify_field' ] );

		// Filter the value on the front end.
		add_filter( 'rwmb_get_value', [ $this, 'get_translated_value' ], 10, 2 );
		add_filter( '_rwmb_post_format_single_value', [ $this, 'get_translated_value' ], 10, 2 );
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
		if ( false === $field || ! in_array( $field['type'], $this->field_types, true ) ) {
			return $value;
		}

		// Object type needed for WPML filter differs between fields.
		$object_type = 'taxonomy_advanced' === $field['type'] ? $field['taxonomy'] : $field['post_type'];

		// Translating values, whether are stored as comma separated strings or not.
		if ( ! str_contains( $value, ',' ) ) {
			$value = apply_filters( 'wpml_object_id', $value, $object_type, true, $target_language );
			return $value;
		}

		// Dealing with IDs stored as comma separated strings.
		$translated_values = [];
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
	 */
	public function modify_field( array $field ): array {
		global $wpml_post_translations;

		if ( empty( $field['id'] ) ) {
			return $field;
		}

		// Get post ID.
		$request = rwmb_request();
		$post_id = $request->filter_get( 'post', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $post_id ) {
			$post_id = $request->filter_post( 'post_ID', FILTER_SANITIZE_NUMBER_INT );
		}

		// If the post is the original one: do nothing.
		if ( ! method_exists( $wpml_post_translations, 'get_source_lang_code' ) || ! $wpml_post_translations->get_source_lang_code( $post_id ) ) {
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

	public function get_translated_value( $value, $field ) {
		if ( ! is_array( $field ) || empty( $field['type'] ) || $field['type'] !== 'post' ) {
			return $value;
		}

		$type             = is_array( $field['post_type'] ) ? reset( $field['post_type'] ) : $field['post_type'];
		$current_language = apply_filters( 'wpml_current_language', null );
		return $this->get_translated_id( $value, $type, $current_language );
	}

	private function get_translated_id( $id, $type, $current_language ) {
		if ( is_array( $id ) ) {
			return array_map( function ( $sub_id ) use ( $type, $current_language ) {
				return $this->get_translated_id( $sub_id, $type, $current_language );
			}, $id );
		}

		return is_numeric( $id ) ? apply_filters( 'wpml_object_id', $id, $type, true, $current_language ) : $id;
	}
}

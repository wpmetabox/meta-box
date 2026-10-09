<?php
namespace MetaBox\Integrations;

class WPML {
	/**
	 * List of fields that need to translate values (because they're saved as IDs).
	 *
	 * @var array
	 */
	private $field_types = [ 'post', 'taxonomy_advanced' ];

	/**
	 * Field types whose value is text that users write. The values of other field types are choices,
	 * numbers, colors or IDs, which must not be translated.
	 *
	 * @var array
	 */
	private $text_field_types = [ 'text', 'textarea', 'wysiwyg' ];

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

		// Declare the text fields of blocks. Priority 9: WPML reads the blocks config at priority 10.
		add_filter( 'wpml_config_array', [ $this, 'add_blocks_config' ], 9 );

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

	/**
	 * Declare each block's text fields to WPML, so WPML translates only them.
	 * Without this, WPML sees a block with no config and offers all its values for translation,
	 * including select values, colors and the block ID.
	 * A block without text fields is declared with translate="0": nothing to translate.
	 *
	 * @param array $config WPML config.
	 * @return array
	 */
	public function add_blocks_config( $config ) {
		if ( ! is_array( $config ) || ! isset( $config['wpml-config'] ) ) {
			return $config;
		}

		foreach ( rwmb_get_registry( 'meta_box' )->all() as $meta_box ) {
			if ( 'block' !== ( $meta_box->meta_box['type'] ?? '' ) ) {
				continue;
			}

			$keys  = $this->get_text_field_keys( $meta_box->meta_box['fields'] ?? [] );
			$block = [
				'value' => '',
				'attr'  => [
					'type'      => 'meta-box/' . $meta_box->id,
					'translate' => $keys ? '1' : '0',
				],
			];

			if ( $keys ) {
				// All field values are saved in the block's "data" attribute.
				$block['key'] = [
					[
						'value' => '',
						'attr'  => [ 'name' => 'data' ],
						'key'   => $keys,
					],
				];
			}

			$config['wpml-config']['gutenberg-blocks']['gutenberg-block'][] = $block;
		}

		return $config;
	}

	/**
	 * Get the WPML keys of the text fields, including the text fields inside groups.
	 *
	 * @param array $fields Fields.
	 * @return array
	 */
	private function get_text_field_keys( array $fields ): array {
		$keys = [];

		foreach ( $fields as $field ) {
			if ( empty( $field['id'] ) ) {
				continue;
			}

			$type = $field['type'] ?? 'text';
			$key  = [
				'value' => '',
				'attr'  => [ 'name' => $field['id'] ],
			];

			if ( 'group' === $type ) {
				$sub_keys = $this->get_text_field_keys( $field['fields'] ?? [] );
				if ( ! $sub_keys ) {
					continue;
				}
				$key['key'] = $sub_keys;
			} elseif ( ! in_array( $type, $this->text_field_types, true ) ) {
				continue;
			}

			// A cloneable field saves a list of values: "*" matches each of them.
			if ( ! empty( $field['clone'] ) ) {
				$item         = $key;
				$item['attr'] = [ 'name' => '*' ];
				$key          = [
					'value' => '',
					'attr'  => [ 'name' => $field['id'] ],
					'key'   => [ $item ],
				];
			}

			$keys[] = $key;
		}

		return $keys;
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

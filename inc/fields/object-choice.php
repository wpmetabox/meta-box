<?php
defined( 'ABSPATH' ) || die;

use MetaBox\Support\Arr;

/**
 * The object choice class which allows users to select specific objects (post, user, taxonomy) in WordPress.
 */
abstract class RWMB_Object_Choice_Field extends RWMB_Choice_Field {
	/**
	 * Show field HTML.
	 * Populate field options before showing to make sure query is made only once.
	 *
	 * @param array $field   Field parameters.
	 * @param bool  $saved   Whether the meta box is saved at least once.
	 * @param int   $post_id Post ID.
	 */
	public static function show( array $field, bool $saved, $post_id = 0 ) {
		// Get unique saved IDs for ajax fields.
		$meta = static::meta( $post_id, $saved, $field );
		$meta = self::filter( 'field_meta', $meta, $field, $saved );
		$meta = Arr::flatten( (array) $meta );
		$meta = array_filter( wp_parse_id_list( $meta ) );
		sort( $meta );

		$field['options'] = static::query( $meta, $field );

		parent::show( $field, $saved, $post_id );
	}

	abstract public static function query( $meta, array $field ): array;

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$html = call_user_func( [ self::get_type_class( $field ), 'html' ], $meta, $field );

		if ( $field['add_new'] ) {
			$html .= static::add_new_form( $field );
		}

		return $html;
	}

	public static function add_new_form( array $field ): string {
		return '';
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, [
			'flatten'     => true,
			'query_args'  => [],
			'field_type'  => 'select_advanced',
			'add_new'     => false,
			'ajax'        => true,
			'ajax_action' => '',
		] );
		if ( 'select_advanced' !== $field['field_type'] ) {
			$field['ajax'] = false;
		}
		if ( 'checkbox_tree' === $field['field_type'] ) {
			$field['field_type'] = 'checkbox_list';
			$field['flatten']    = false;
		}
		if ( 'radio_list' === $field['field_type'] ) {
			$field['field_type'] = 'radio';
		}
		$field = call_user_func( [ self::get_type_class( $field ), 'normalize' ], $field );

		return $field;
	}

	/**
	 * Set ajax parameters.
	 *
	 * @param array $field Field settings.
	 */
	protected static function set_ajax_params( &$field ) {
		if ( ! $field['ajax'] ) {
			return;
		}

		if ( empty( $field['js_options']['ajax'] ) ) {
			$field['js_options']['ajax'] = [];
		}
		$field['js_options']['ajax'] = wp_parse_args(
			[
				'url' => admin_url( 'admin-ajax.php' ),
			],
			$field['js_options']['ajax']
		);
		$field_data                  = [
			'id'          => $field['id'],
			'type'        => $field['type'],
			'meta_box_id' => (string) ( $field['meta_box_id'] ?? '' ),
		];
		// ajax_action stays in field. select-advanced.js removes data.action from the cache key.
		$ajax_action = $field['ajax_action'] ?? '';
		$ajax_action = is_string( $ajax_action ) ? sanitize_key( $ajax_action ) : '';
		if ( ! $ajax_action ) {
			$actions     = [
				'post'              => 'rwmb_get_posts',
				'taxonomy'          => 'rwmb_get_terms',
				'taxonomy_advanced' => 'rwmb_get_terms',
				'user'              => 'rwmb_get_users',
			];
			$ajax_action = $actions[ $field['type'] ] ?? '';
		}
		if ( $ajax_action ) {
			$field_data['ajax_action'] = $ajax_action;
		}
		$field['js_options']['ajax_data'] = [
			'field'    => $field_data,
			'_wpnonce' => wp_create_nonce( self::ajax_nonce_action( $field ) ),
		];
	}

	/**
	 * Nonce action bound to one meta box field.
	 * The length prefix keeps the action unique when both ids contain hyphens.
	 */
	public static function ajax_nonce_action( array $field ): string {
		$box = (string) ( $field['meta_box_id'] ?? '' );
		$id  = (string) ( $field['id'] ?? '' );

		return sprintf( 'rwmb-query-%d-%s-%s', strlen( $box ), $box, $id );
	}

	/**
	 * Stamp meta box id onto Ajax fields and rebuild their payloads after normalization.
	 * Nested fields (groups) are included so their nonce matches the parent box.
	 */
	public static function bind_ajax_fields( array $fields, string $meta_box_id ): array {
		foreach ( $fields as &$field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			$options    = $field['js_options'] ?? null;
			$ajax_field = is_array( $options ) ? ( $options['ajax_data']['field'] ?? null ) : null;
			if ( is_array( $ajax_field ) ) {
				$field['meta_box_id'] = $meta_box_id;
				unset( $ajax_field['query_args'], $ajax_field['display_field'], $ajax_field['show_thumbnail'] );
				$ajax_field['meta_box_id']                    = $meta_box_id;
				$field['js_options']['ajax_data']['field']    = $ajax_field;
				$field['js_options']['ajax_data']['_wpnonce'] = wp_create_nonce( self::ajax_nonce_action( $field ) );
			}

			if ( is_array( $field['fields'] ?? null ) ) {
				$field['fields'] = self::bind_ajax_fields( $field['fields'], $meta_box_id );
			}
		}
		unset( $field );

		return $fields;
	}

	/**
	 * Load the registered field for an Ajax query.
	 * The browser may send the field id, meta box id, search term, and page.
	 *
	 * @param string[] $expected_types Field types this handler accepts.
	 */
	protected static function resolve_ajax_field( array $expected_types ): array {
		$request = rwmb_request();
		$posted  = $request->filter_post( 'field', FILTER_DEFAULT, FILTER_FORCE_ARRAY );
		$posted  = is_array( $posted ) ? $posted : [];
		$box_id  = $posted['meta_box_id'] ?? null;
		$id      = $posted['id'] ?? null;
		$box_id  = is_string( $box_id ) ? $box_id : '';
		$id      = is_string( $id ) ? $id : '';

		check_ajax_referer( self::ajax_nonce_action( [
			'meta_box_id' => $box_id,
			'id'          => $id,
		] ) );

		$field = self::find_ajax_field( $id, $box_id );
		if ( ! $field || empty( $field['ajax'] ) || ! in_array( $field['type'] ?? '', $expected_types, true ) ) {
			self::deny_ajax();
			return [];
		}

		$field['clone']        = false;
		$field['_original_id'] = $field['id'];
		$field['query_args']   = is_array( $field['query_args'] ?? null ) ? $field['query_args'] : [];

		return $field;
	}

	/**
	 * Stop an Ajax query that is not allowed.
	 *
	 * @return never
	 */
	protected static function deny_ajax(): void {
		wp_send_json_error( __( 'Invalid request.', 'meta-box' ), 403 );
	}

	protected static function find_ajax_field( string $field_id, string $meta_box_id ): array {
		if ( ! $field_id || ! function_exists( 'rwmb_get_registry' ) ) {
			return [];
		}

		$matches = [];
		foreach ( rwmb_get_registry( 'meta_box' )->all() as $meta_box ) {
			if ( $meta_box_id && (string) $meta_box->id !== $meta_box_id ) {
				continue;
			}

			$fields = $meta_box->fields;
			if ( is_array( $fields ) ) {
				self::collect_ajax_fields( $fields, $field_id, $matches );
			}
		}

		return count( $matches ) === 1 ? $matches[0] : [];
	}

	private static function collect_ajax_fields( array $fields, string $field_id, array &$matches ): void {
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			if ( ( $field['id'] ?? '' ) === $field_id && ! empty( $field['ajax'] ) ) {
				$matches[] = $field;
			}

			if ( is_array( $field['fields'] ?? null ) ) {
				self::collect_ajax_fields( $field['fields'], $field_id, $matches );
			}
		}
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes = call_user_func( [ self::get_type_class( $field ), 'get_attributes' ], $field, $value );
		if ( 'select_advanced' === $field['field_type'] ) {
			$attributes['class'] .= ' rwmb-select_advanced';
		} elseif ( 'select' === $field['field_type'] ) {
			$attributes['class'] .= ' rwmb-select';
		}
		return $attributes;
	}

	public static function admin_enqueue_scripts() {
		RWMB_Input_List_Field::admin_enqueue_scripts();
		RWMB_Select_Field::admin_enqueue_scripts();
		RWMB_Select_Tree_Field::admin_enqueue_scripts();
		RWMB_Select_Advanced_Field::admin_enqueue_scripts();

		// Field is the 1st param.
		$field = func_get_arg( 0 );
		if ( empty( $field['add_new'] ) ) {
			return;
		}

		wp_enqueue_style( 'rwmb-modal', RWMB_CSS_URL . 'modal.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-modal', 'path', RWMB_CSS_DIR . 'modal.css' );
		wp_enqueue_script( 'rwmb-modal', RWMB_JS_URL . 'modal.js', [ 'jquery' ], RWMB_VER, true );

		$type = $field['type'] === 'taxonomy_advanced' ? 'taxonomy' : $field['type'];
		wp_enqueue_script( "rwmb-$type", RWMB_JS_URL . "$type.js", [ 'jquery', 'rwmb-modal' ], RWMB_VER, true );
	}

	/**
	 * Get correct rendering class for the field.
	 */
	protected static function get_type_class( array $field ): string {
		return RWMB_Helpers_Field::get_class( [ 'type' => $field['field_type'] ] );
	}
}

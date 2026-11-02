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
	 * Core Ajax actions receive the field configuration as a signed JSON string
	 * in `field`, so changing any value invalidates the nonce. Custom Ajax
	 * actions keep the legacy array format and must validate the request
	 * themselves.
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

		$field_data = [
			'id'         => $field['id'],
			'type'       => $field['type'],
			'query_args' => is_array( $field['query_args'] ?? null ) ? $field['query_args'] : [],
		];
		if ( $ajax_action ) {
			$field_data['ajax_action'] = $ajax_action;
		}
		if ( array_key_exists( 'display_field', $field ) ) {
			$field_data['display_field'] = $field['display_field'];
		}
		if ( array_key_exists( 'show_thumbnail', $field ) ) {
			$field_data['show_thumbnail'] = (bool) $field['show_thumbnail'];
		}

		$core = in_array( $ajax_action, [ 'rwmb_get_posts', 'rwmb_get_terms', 'rwmb_get_users' ], true );
		if ( $core ) {
			$payload = wp_json_encode( $field_data );
			if ( is_string( $payload ) && '' !== $payload ) {
				$field['js_options']['ajax_data'] = [
					'field'    => $payload,
					'_wpnonce' => wp_create_nonce( 'rwmb-query-' . $payload ),
				];
			}
			return;
		}

		$field['js_options']['ajax_data'] = [
			'field'    => $field_data,
			'_wpnonce' => wp_create_nonce( 'query' ),
		];
	}

	/**
	 * Load the signed field configuration sent by a core Ajax handler.
	 *
	 * The payload is the JSON string in `field`.
	 *
	 * @return array
	 */
	protected static function get_ajax_field(): array {
		$request = rwmb_request();
		$payload = $request->post( 'field' );
		$payload = is_string( $payload ) ? (string) wp_unslash( $payload ) : '';
		$action  = $request->post( 'action' ) ?: $request->get( 'action' );
		$action  = is_string( $action ) ? $action : '';

		if ( ! $payload ) {
			self::deny_ajax();
		}

		check_ajax_referer( 'rwmb-query-' . $payload );

		$field = json_decode( $payload, true );
		if ( ! is_array( $field ) || ( $field['ajax_action'] ?? '' ) !== $action ) {
			self::deny_ajax();
		}

		$field['query_args']   = is_array( $field['query_args'] ?? null ) ? $field['query_args'] : [];
		$field['clone']        = false;
		$field['_original_id'] = is_string( $field['id'] ?? null ) ? $field['id'] : '';

		return $field;
	}

	/**
	 * Stop an Ajax query that is not allowed.
	 *
	 * @return never
	 */
	protected static function deny_ajax(): void {
		wp_die( -1, 403 );
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

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
	 * For core Ajax actions, `field` is a JSON string and the nonce is made from it.
	 * If a visitor changes a value, the nonce is not correct.
	 * Custom Ajax actions keep the old array format. They must check the request themselves.
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
			'query_args' => $field['query_args'],
		];
		// Keep ajax_action inside field (not top-level ajax_data): select-advanced.js
		// strips data.action from the cache key, so two fields with the same type +
		// query_args but different ajax_action would otherwise share a cache entry.
		if ( $ajax_action ) {
			$field_data['ajax_action'] = $ajax_action;
		}
		if ( array_key_exists( 'display_field', $field ) ) {
			$field_data['display_field'] = $field['display_field'];
		}
		if ( array_key_exists( 'show_thumbnail', $field ) ) {
			$field_data['show_thumbnail'] = (bool) $field['show_thumbnail'];
		}

		if ( in_array( $ajax_action, [ 'rwmb_get_posts', 'rwmb_get_terms', 'rwmb_get_users' ], true ) ) {
			$payload                          = wp_json_encode( $field_data );
			$field['js_options']['ajax_data'] = [
				'field'    => $payload,
				'_wpnonce' => wp_create_nonce( 'rwmb-query-' . $payload ),
			];
			return;
		}

		$field['js_options']['ajax_data'] = [
			'field'    => $field_data,
			'_wpnonce' => wp_create_nonce( 'query' ),
		];
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

<?php
use MetaBox\Support\Arr;

class RWMB_Shortcode {
	public function init() {
		add_shortcode( 'rwmb_meta', [ $this, 'register_shortcode' ] );
	}

	public function register_shortcode( $atts ) {
		$atts = wp_parse_args( $atts, [
			'id'                => '',
			'object_id'         => null,
			'attribute'         => '',
			'render_shortcodes' => 'true',
		] );
		Arr::change_key( $atts, 'post_id', 'object_id' );
		Arr::change_key( $atts, 'meta_key', 'id' );

		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$field_id  = $atts['id'];
		$object_id = $atts['object_id'];
		
		unset( $atts['id'], $atts['object_id'] );

		$value = $this->get_value( $field_id, $object_id, $atts );
		$value = 'true' === $atts['render_shortcodes'] ? do_shortcode( $value ) : $value;

		$secure = apply_filters( 'rwmb_meta_shortcode_secure', true, $field_id, $atts, $object_id );
		$secure = apply_filters( "rwmb_meta_shortcode_secure_{$field_id}", $secure, $atts, $object_id );

		if ( $secure ) {
			$value = wp_kses_post( $value );
		}

		return $value;
	}

	private function get_value( $field_id, $object_id, $atts ) {
		// If we pass object_id via shortcode, we need to make sure current user 
		// has permission to view the object
		if ( ! is_null ( $object_id ) ) {
			$has_object_permission = $this->check_object_permission( $object_id, $atts );
			
			if ( ! $has_object_permission ) {
				return null;
			}
		}

		$attribute = $atts['attribute'];
		if ( ! $attribute ) {
			return rwmb_the_value( $field_id, $atts, $object_id, false );
		}

		$value = rwmb_get_value( $field_id, $atts, $object_id );

		if ( ! is_array( $value ) && ! is_object( $value ) ) {
			return $value;
		}

		if ( is_object( $value ) ) {
			return $value->$attribute;
		}

		if ( isset( $value[ $attribute ] ) ) {
			return $value[ $attribute ];
		}

		$value = wp_list_pluck( $value, $attribute );
		$value = implode( ',', array_filter( $value ) );

		return $value;
	}

	private function check_object_permission( $object_id, $atts ) {
		// Skip checking if object_type is not post
		if ( isset( $atts['object_type'] ) && $atts['object_type'] !== 'post' ) {
			return true;
		}

		$post = get_post( $object_id );
		if ( ! $post ) {
			return false;
		}

		// Skip checking if post status is publish AND no password is set
		if ( 'publish' === $post->post_status && ! post_password_required( $post ) ) {
			return true;
		}

		$object_type = get_post_type_object( $post->post_type );
		if ( ! $object_type ) {
			return false;
		}

		$read_post = $object_type->cap->read_post;

		return current_user_can( $read_post, $object_id );
	}
}

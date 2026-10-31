<?php
defined( 'ABSPATH' ) || die;

/**
 * The WYSIWYG (editor) field.
 */
class RWMB_Wysiwyg_Field extends RWMB_Field {
	// TinyMCE removes attribute-less <span> tags when content round-trips through the editor. Keep them with
	// an explicit attribute whitelist ("span[*]" keeps event handlers, which the default schema strips).
	const SPAN_VALID_ELEMENTS = 'span[id|accesskey|class|dir|lang|style|tabindex|title|role|contenteditable|contextmenu|draggable|dropzone|hidden|spellcheck|translate]';

	public static function admin_enqueue_scripts() {
		wp_enqueue_editor();
		wp_enqueue_style( 'rwmb-wysiwyg', RWMB_CSS_URL . 'wysiwyg.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-wysiwyg', 'path', RWMB_CSS_DIR . 'wysiwyg.css' );
		wp_enqueue_script( 'rwmb-wysiwyg', RWMB_JS_URL . 'wysiwyg.js', [ 'jquery', 'rwmb' ], RWMB_VER, true );
		wp_localize_script( 'rwmb-wysiwyg', 'rwmbWysiwyg', [
			'extendedValidElements' => self::SPAN_VALID_ELEMENTS,
		] );
	}

	/**
	 * Change field value on save.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 * @return string
	 */
	public static function value( $new, $old, $post_id, $field ) {
		return $field['raw'] ? $new : wpautop( $new );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		// Using output buffering because wp_editor() echos directly.
		ob_start();

		$attributes = self::get_attributes( $field );

		$options                  = $field['options'];
		$options['textarea_name'] = $field['field_name'];
		if ( ! empty( $attributes['required'] ) ) {
			$options['editor_class'] .= ' rwmb-wysiwyg-required';
		}

		wp_editor( $meta, $attributes['id'], $options );
		echo '<script class="rwmb-wysiwyg-id" type="text/html" data-id="', esc_attr( $attributes['id'] ), '" data-options="', esc_attr( wp_json_encode( $options ) ), '"></script>';
		echo '<script>if ( typeof rwmb !== "undefined" ) rwmb.$document.trigger( "mb_init_editors" );</script>';

		return ob_get_clean();
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
			'raw'     => false,
			'options' => [],
		] );

		$field['options'] = wp_parse_args( $field['options'], [
			'editor_class' => 'rwmb-wysiwyg',
			'dfw'          => true, // Use default WordPress full screen UI.
		] );

		if ( ! isset( $field['options']['tinymce'] ) ) {
			$field['options']['tinymce'] = [];
		}

		if ( is_array( $field['options']['tinymce'] ) ) {
			$extended = (string) ( $field['options']['tinymce']['extended_valid_elements'] ?? '' );

			// Respect user-defined span rules: they already keep attribute-less <span> and control its attributes.
			if ( ! preg_match( '/(?:^|,)\s*span\s*(?:\[|,|$)/i', $extended ) ) {
				$field['options']['tinymce']['extended_valid_elements'] = $extended ? $extended . ',' . self::SPAN_VALID_ELEMENTS : self::SPAN_VALID_ELEMENTS;
			}
		}

		// Keep the filter to be compatible with previous versions.
		$field['options'] = apply_filters( 'rwmb_wysiwyg_settings', $field['options'] );

		return $field;
	}
}

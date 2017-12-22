<?php
/**
 * The WYSIWYG (editor) field.
 *
 * @package Meta Box
 */

/**
 * WYSIWYG (editor) field class.
 */
class RWMB_Wysiwyg_Field extends RWMB_Field {
	/**
	 * Array of cloneable editors.
	 *
	 * @var array
	 */
	protected static $cloneable_editors = array();

	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-wysiwyg', RWMB_CSS_URL . 'wysiwyg.css', array(), RWMB_VER );
		wp_enqueue_script( 'rwmb-wysiwyg', RWMB_JS_URL . 'wysiwyg.js', array( 'jquery' ), RWMB_VER, true );
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

		$field['options']['textarea_name'] = $field['field_name'];
		$attributes = self::get_attributes( $field );

		// Use new wp_editor() since WP 3.3.
		wp_editor( $meta, $attributes['id'], $field['options'] );

		return ob_get_clean();
	}

	/**
	 * Escape meta for field output.
	 *
	 * @param mixed $meta Meta value.
	 * @return mixed
	 */
	public static function esc_meta( $meta ) {
		return $meta;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'raw'     => false,
			'options' => array(),
		) );

		$field['options'] = wp_parse_args( $field['options'], array(
			'editor_class' => 'rwmb-wysiwyg',
			'dfw'          => true, // Use default WordPress full screen UI.
		) );

		// Keep the filter to be compatible with previous versions.
		$field['options'] = apply_filters( 'rwmb_wysiwyg_settings', $field['options'] );

		return $field;
	}
}

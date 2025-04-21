<?php
defined( 'ABSPATH' ) || die;

/**
 * The abstract input field which is used for all <input> fields.
 */
abstract class RWMB_Input_Field extends RWMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-input', RWMB_CSS_URL . 'input.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-input', 'path', RWMB_CSS_DIR . 'input.css' );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$output = '';

		if ( $field['prepend'] || $field['append'] ) {
			$output = '<div class="rwmb-input-group">';
		}

		if ( $field['prepend'] ) {
			$output .= '<span class="rwmb-input-group-text">' . $field['prepend'] . '</span>';
		}

		$attributes = static::get_attributes( $field, $meta );
		$output .= sprintf( '<input %s>%s', self::render_attributes( $attributes ), self::datalist( $field ) );

		if ( $field['type'] === 'password' ) {
			$output .= sprintf(
				'<button type="button" class="rwmb-password-toggle" data-for="%s">
                    <svg class="rwmb-eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/>
                    </svg>
                    <svg class="rwmb-eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                        <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z" fill="currentColor"/>
                    </svg>
                </button>',
				esc_attr( $field['id'] )
			);
		}

		if ( $field['append'] ) {
			$output .= '<span class="rwmb-input-group-text">' . $field['append'] . '</span>';
		}

		if ( $field['prepend'] || $field['append'] ) {
			$output .= '</div>';
		}

		return $output;
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
			'autocomplete' => false,
			'datalist'     => false,
			'readonly'     => false,
			'maxlength'    => false,
			'minlength'    => false,
			'pattern'      => false,
			'prepend'      => '',
			'append'       => '',
		] );
		if ( $field['datalist'] ) {
			$field['datalist'] = wp_parse_args( $field['datalist'], [
				'id'      => $field['id'] . '_list',
				'options' => [],
			] );
		}
		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, [
			'autocomplete' => $field['autocomplete'],
			'list'         => $field['datalist'] ? $field['datalist']['id'] : false,
			'readonly'     => $field['readonly'],
			'maxlength'    => $field['maxlength'],
			'minlength'    => $field['minlength'],
			'pattern'      => $field['pattern'],
			'value'        => $value,
			'placeholder'  => $field['placeholder'],
			'type'         => $field['type'],
		] );
		if ( isset( $field['size'] ) ) {
			$attributes['size'] = $field['size'];
		}

		return $attributes;
	}

	protected static function datalist( array $field ): string {
		if ( empty( $field['datalist'] ) ) {
			return '';
		}

		$datalist = $field['datalist'];
		$html     = sprintf( '<datalist id="%s">', $datalist['id'] );
		foreach ( $datalist['options'] as $option ) {
			$html .= sprintf( '<option value="%s"></option>', $option );
		}
		$html .= '</datalist>';
		return $html;
	}
}

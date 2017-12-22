<?php
/**
 * The Background field.
 *
 * @package Meta Box.
 */

/**
 * The Background field.
 */
class RWMB_Background_Field extends RWMB_Select_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-background', RWMB_CSS_URL . 'background.css', '', RWMB_VER );

		// js color.
		wp_enqueue_script( 'rwmb-color', RWMB_JS_URL . 'color.js', '', RWMB_VER, true );

		// js image.
		wp_enqueue_media();
		wp_enqueue_script( 'rwmb-background-image', RWMB_JS_URL . 'background.js', '', RWMB_VER, true );
	}


	/**
	 * Walk options.
	 *
	 * @param array $field     Field parameters.
	 * @param mixed $options   Select options.
	 * @param mixed $db_fields Database fields to use in the output.
	 * @param mixed $meta      Meta value.
	 *
	 * @return string
	 */
	public static function walk( $field, $options, $db_fields, $meta ) {

		$attributes = self::call( 'get_attributes', $field, $meta );
		$output = '<div class="rwmb-background">';
		$select_options = array();

		// background color field.
		$output .= sprintf(
			'<input size="30"  type="text" id="' . esc_attr( $attributes['id'] ) . '_color" class="' . esc_attr( $attributes['id'] ) . '_color rwmb-color" name="' . esc_attr( $attributes['id'] ) . '[color]" value="%s" />',
			isset( $meta['color'] ) ? esc_attr( $meta['color'] ) : ''
		);

		// background repeat field.
		$options_repeat = array(
			'no-repeat' => esc_html__( 'No Repeat', 'meta-box' ),
			'repeat'    => esc_html__( 'Repeat All', 'meta-box' ),
			'repeat-x'  => esc_html__( 'Repeat Horizontally', 'meta-box' ),
			'repeat-y'  => esc_html__( 'Repeat Vertically', 'meta-box' ),
			'inherit'   => esc_html__( 'Inherit', 'meta-box' ),
		);

		// change data option.
		if ( is_array( $options_repeat ) ) {
			$select_options = $options_repeat;
			foreach ( $options_repeat as $value => $label ) {
				$select_options[ $value ] = (object) array(
					'value' => $value,
					'label' => $label,
				);
			}
		}

		$output .= '<select id="' . esc_attr( $attributes['id'] ) . '_repeat" name="' . esc_attr( $attributes['id'] ) . '[repeat]" class="select_background ' . esc_attr( $attributes['id'] ) . '_repeat">';

		foreach ( $select_options as $key => $value ) {
			$output .= sprintf(
				'<option value="%s" %s>%s</option>',
				$value->value,
				selected( in_array( esc_attr( $value->value ), $meta, true ), true, false ),
				esc_html( $value->label )
			);
		}

		$output .= '</select>';

		// background size field.
		$options_size = array(
			'inherit' => esc_html__( 'Inherit', 'meta-box' ),
			'cover'   => esc_html__( 'Cover', 'meta-box' ),
			'contain' => esc_html__( 'Contain', 'meta-box' ),
		);

		// change data option.
		if ( is_array( $options_size ) ) {
			$select_options = $options_size;
			foreach ( $options_size as $value => $label ) {
				$select_options[ $value ] = (object) array(
					'value' => $value,
					'label' => $label,
				);
			}
		}

		// get data select.
		$output .= '<select id="' . esc_attr( $attributes['id'] ) . '_size" name="' . esc_attr( $attributes['id'] ) . '[size]" class="select_background ' . esc_attr( $attributes['id'] ) . '_size">';

		foreach ( $select_options as $key => $value ) {
			$output .= sprintf(
				'<option value="%s" %s>%s</option>',
				$value->value,
				selected( in_array( esc_attr( $value->value ), $meta, true ), true, false ),
				esc_html( $value->label )
			);
		}
		$output .= '</select>';

		// background attachment field.
		$options_attachment = array(
			'fixed'   => esc_html__( 'Fixed', 'meta-box' ),
			'scroll'  => esc_html__( 'Scroll', 'meta-box' ),
			'inherit' => esc_html__( 'Inherit', 'meta-box' ),
		);

		// change data option.
		if ( is_array( $options_attachment ) ) {
			$select_options = $options_attachment;
			foreach ( $options_attachment as $value => $label ) {
				$select_options[ $value ] = (object) array(
					'value' => $value,
					'label' => $label,
				);
			}
		}

		// get data select.
		$output .= '<select id="' . esc_attr( $attributes['id'] ) . '_attachment" name="' . esc_attr( $attributes['id'] ) . '[attachment]" class="select_background ' . esc_attr( $attributes['id'] ) . '_attachment">';
		foreach ( $select_options as $key => $value ) {
			$output .= sprintf(
				'<option value="%s" %s>%s</option>',
				$value->value,
				selected( in_array( esc_attr( $value->value ), $meta, true ), true, false ),
				esc_html( $value->label )
			);
		}
		$output .= '</select>';

		// background position field.
		$options_position = array(
			'left_top'      => esc_html__( 'Left Top', 'meta-box' ),
			'left_center'   => esc_html__( 'Left center', 'meta-box' ),
			'left_bottom'   => esc_html__( 'Left Bottom', 'meta-box' ),
			'center_top'    => esc_html__( 'Center Top', 'meta-box' ),
			'center_center' => esc_html__( 'Center Center', 'meta-box' ),
			'center_bottom' => esc_html__( 'Center Bottom', 'meta-box' ),
			'right_top'     => esc_html__( 'Right Top', 'meta-box' ),
			'right_center'  => esc_html__( 'Right center', 'meta-box' ),
			'right_bottom'  => esc_html__( 'Right Bottom', 'meta-box' ),
		);

		// change data option.
		if ( is_array( $options_position ) ) {
			$select_options = $options_position;
			foreach ( $options_position as $value => $label ) {
				$select_options[ $value ] = (object) array(
					'value' => $value,
					'label' => $label,
				);
			}
		}

		// get data select.
		$output .= '<select id="' . esc_attr( esc_attr( $attributes['id'] ) ) . '_position" name="' . esc_attr( $attributes['id'] ) . '[position]" class="select_background ' . esc_attr( $attributes['id'] ) . '_position">';
		foreach ( $select_options as $key => $value ) {
			$output .= sprintf(
				'<option value="%s" %s>%s</option>',
				$value->value,
				selected( in_array( esc_attr( $value->value ), $meta, true ), true, false ),
				esc_html( $value->label )
			);
		}
		$output .= '</select>';

		// background image field.
		$output .= sprintf( '<div class="rwmb-background-image"><input id="' . esc_attr( $attributes['id'] ) . '_image" class="' . esc_attr( $attributes['id'] ) . '_image  rwmb-upload-background" type="text"  name="' . esc_attr( $attributes['id'] ) . '[image]" value="%s" /><button class="rwmb-upload-image button">' . esc_attr( 'Upload', 'textdomain' ) . '</button></div>',
			isset( $meta['image'] ) ? esc_attr( $meta['image'] ) : ''
		);

		$output .= '</div>';
		return $output;

	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, array(
			'background' => array(),
			'options' => true,
		) );

		$field = parent::normalize( $field );

		$field['background'] = wp_parse_args( $field['background'], array(
			'background_repeat' => true,
			'background_size' => true,
			'background_attachment' => true,
			'background_position' => true,
			'background_color' => true,
			'background_image' => true,
		) );

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
		$attributes = wp_parse_args( $attributes, array(
			'type'        => $field['type'],
		) );

		return $attributes;
	}
}

<?php
/**
 * The Background field.
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

		// style + js color 
		$args = func_get_args();
		$field = $args[0];
		$js_dependency = array( 'wp-color-picker' );
		wp_enqueue_style( 'rwmb-color', RWMB_CSS_URL . 'color.css', array( 'wp-color-picker' ), RWMB_VER );
		if ( $field['alpha_channel'] ) {
			wp_enqueue_script( 'wp-color-picker-alpha', RWMB_JS_URL . 'wp-color-picker-alpha/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), RWMB_VER, true );
			$js_dependency = array( 'wp-color-picker-alpha' );
		}
		wp_enqueue_script( 'rwmb-color', RWMB_JS_URL . 'color.js', $js_dependency, RWMB_VER, true );

		// js image
		wp_enqueue_media();
		wp_enqueue_script( 'rwmb-background-image', RWMB_JS_URL . 'bg-image.js', '', RWMB_VER, true );
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
		$walker     = new RWMB_Walker_Background( $db_fields, $field, $meta );
		$output		= '<div class="rwmb-background">';

		if ( $field['background']['background_color'] == True ){
			$attributes['class'] .= ' color';

			$output .= sprintf(
	            '<input size="30"  type="text" id="'. $attributes['id'] .'_color" class="'. $attributes['id'] .'_color rwmb-color" name="' . $attributes['id'] . '[color]" value="%s" />',
	            isset( $meta['color'] ) ? esc_attr( $meta['color'] ) : ''
	        );
		}

		if ( $field['background']['background_repeat'] == True ){
 			$options_repeat = array(
                'no-repeat' => 'No Repeat',
                'repeat'    => 'Repeat All',
                'repeat-x'  => 'Repeat Horizontally',
                'repeat-y'  => 'Repeat Vertically',
                'inherit'   => 'Inherit',
            );

            // change data option
		 	$select_options = $options_repeat;
			if ( is_array( $options_repeat ) ) {
				$select_options = array();
				foreach ( $options_repeat as $value => $label ) {
					$select_options[$value] = (object) array(
						'value' => $value,
						'label' => $label,
					);
				}
			}
			// get data select
			$attributes['class'] .= ' repeat';

			$output .= '<select id="'. $attributes['id'] .'_repeat" name="' . $attributes['id'] . '[repeat]" class="select_background '. $attributes['id'] .'_repeat">';
			$output .= $walker->walk( $select_options, $field['flatten'] ? - 1 : 0 );
			$output .= '</select>';
		}
			
		if ( $field['background']['background_size'] == True ){
 			$options_size = array(
				'inherit' => 'Inherit',
				'cover'   => 'Cover',
				'contain' => 'Contain',
            );

            // change data option
		 	$select_options = $options_size;
			if ( is_array( $options_size ) ) {
				$select_options = array();
				foreach ( $options_size as $value => $label ) {
					$select_options[$value] = (object) array(
						'value' => $value,
						'label' => $label,
					);
				}
			}
			// get data select
			$attributes['class'] .= ' size';

			$output .= '<select id="'. $attributes['id'] .'_size" name="' . $attributes['id'] . '[size]" class="select_background '. $attributes['id'] .'_size">';

			$output .= $walker->walk( $select_options, $field['flatten'] ? - 1 : 0 );
			$output .= '</select>';
		}

		if ( $field['background']['background_attachment'] == True ){
 			$options_attachment = array(
				'fixed'   => 'Fixed',
				'scroll'  => 'Scroll',
				'inherit' => 'Inherit',
            );

            // change data option
		 	$select_options = $options_attachment;
			if ( is_array( $options_attachment ) ) {
				$select_options = array();
				foreach ( $options_attachment as $value => $label ) {
					$select_options[$value] = (object) array(
						'value' => $value,
						'label' => $label,
					);
				}
			}
			// get data select
			$attributes['class'] .= ' attachment';

			$output .= '<select id="'. $attributes['id'] .'_attachment" name="'. $attributes['id'] .'[attachment]" class="select_background '. $attributes['id'] .'_attachment">';
			$output .= $walker->walk( $select_options, $field['flatten'] ? - 1 : 0 );
			$output .= '</select>';
		}

		if ( $field['background']['background_position'] == True ){
 			$options_position = array(
				'left_top'      => 'Left Top',
				'left_center'   => 'Left center',
				'left_bottom'   => 'Left Bottom',
				'center_top'    => 'Center Top',
				'center_center' => 'Center Center',
				'center_bottom' => 'Center Bottom',
				'right_top'     => 'Right Top',
				'right_center'  => 'Right center',
				'right_bottom'  => 'Right Bottom',
            );

            // change data option
		 	$select_options = $options_position;
			if ( is_array( $options_position ) ) {
				$select_options = array();
				foreach ( $options_position as $value => $label ) {
					$select_options[$value] = (object) array(
						'value' => $value,
						'label' => $label,
					);
				}
			}
			// get data select
			$attributes['class'] .= ' position';

			$output .= '<select id="'. $attributes['id'] .'_position" name="' . $attributes['id'] . '[position]" class="select_background '. $attributes['id'] .'_position">';
			$output .= $walker->walk( $select_options, $field['flatten'] ? - 1 : 0 );
			$output .= '</select>';
		}

		if ( $field['background']['background_image'] == True ){
			$attributes['class'] .= ' image';

			$output .= sprintf( '<div class="rwmb-background-image"><input id="'. $attributes['id'] .'_image" class="'. $attributes['id'] .'_image  rwmb-upload-background" type="text"  name="' . $attributes['id'] . '[image]" value="%s" /><button class="rwmb-upload-image button">'. esc_attr( 'Upload', 'textdomain' ) .'</button></div>',
				isset( $meta['image'] ) ? esc_attr( $meta['image'] ) : ''
			);
		}

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
			'alpha_channel' => false,
			'js_options'  => array(),
			'placeholder' => __( 'Select an item', 'meta-box' ),
			'background'	=> array(),	
			'options'		=> true,
			
		) );

		$field = parent::normalize( $field );

		$field['js_options'] = wp_parse_args( $field['js_options'], array(
			'allowClear'  => true,
			'width'       => 'none',
			'placeholder' => $field['placeholder'],
			// color
			'defaultColor' => false,
			'hide'         => true,
			'palettes'     => true,
		) );
		$field['background'] = wp_parse_args( $field['background'], array(
			'background_repeat'		=> true,
			'background_size'		=> true,
			'background_attachment'	=> true,
			'background_position'	=> true,
			'background_color'		=> true,
			'background_image'		=> true,
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
			'data-options' => wp_json_encode( $field['js_options'] ),
			'data-background' => wp_json_encode( $field['background'] ),
			'type'        => $field['type'],
		) );

		if ( $field['alpha_channel'] ) {
			$attributes['data-alpha'] = 'true';
		}

		return $attributes;
	}
}

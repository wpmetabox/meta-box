<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Radio_Field' ) )
{
	class RWMB_Radio_Field extends RWMB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'rwmb-radio', RWMB_CSS_URL . 'radio.css', array(), RWMB_VER );
			wp_enqueue_script( 'rwmb-radio', RWMB_JS_URL . 'radio.js', array( 'jquery' ), RWMB_VER, true );
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			$html = array();
			$tpl = '<label %s>%s<input type="radio" class="rwmb-radio" name="%s" value="%s"%s> %s</label>';

			foreach ( $field['options'] as $value => $label )
			{
				$label_class = '';
				$label_image = '';

				// for backward compatibility add label image only if label is an array
				if ( is_array($label) ) {
					if( isset($label['image']) && is_array($label['image']) ) {
						$image_arr = $label['image'];

						$image_tpl = '<img src="%s" width="%d" height="%d" />';
						$image_src = $image_arr[0]; //image src
						$image_w = $image_arr[1]?$image_arr[1]:100; //set image width or default to 100px
						$image_h = $image_arr[2]?$image_arr[2]:100; //set image height or default to 100px
						$image_2state = $image_arr[3]; //does the image has 2 states? second state if checked
						
						if ($image_2state) {
							//the image has 2 states so we make the image as background image for a span
							$image_tpl = '<span style="background-image:url(\'' . esc_url(get_template_directory_uri() . '%s') . '\');width:%dpx; height:%dpx;"></span>';
							
							//we need to add some javascript to some labels
							$checked = checked( $value, $meta, false );
							if ( $checked ) {
								$label_class = 'class="rwmb-radio-checked"';
							}
						}

						$label_image = sprintf(
							$image_tpl,
							$image_src,
							absint($image_w),
							absint($image_h)
						);
					}
					//set the label
					$label = $label['label'];
				}

				$html[] = sprintf(
					$tpl,
					$label_class,
					$label_image,
					$field['field_name'],
					$value,
					checked( $value, $meta, false ),
					$label
				);
			}

			return implode( ' ', $html );
		}
	}
}

<?php
/**
 * Image select field class which uses images as radio options.
 */
class RWMB_Image_Select_Field extends RWMB_Field
{
	/**
	 * Enqueue scripts and styles
	 */
	static function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'rwmb-image-select', RWMB_CSS_URL . 'image-select.css', array(), RWMB_VER );
		wp_enqueue_script( 'rwmb-image-select', RWMB_JS_URL . 'image-select.js', array( 'jquery' ), RWMB_VER, true );
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 * @return string
	 */
	static function html( $meta, $field )
	{
		$html = array();
		$tpl  = '<label class="rwmb-image-select"><img src="%s"><input type="%s" class="hidden" name="%s" value="%s"%s></label>';

		$meta = (array) $meta;
		foreach ( $field['options'] as $value => $image )
		{
			$html[] = sprintf(
				$tpl,
				$image,
				$field['multiple'] ? 'checkbox' : 'radio',
				$field['field_name'],
				$value,
				checked( in_array( $value, $meta ), true, false )
			);
		}

		return implode( ' ', $html );
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = parent::normalize( $field );
		$field['field_name'] .= $field['multiple'] ? '[]' : '';

		return $field;
	}

	/**
	 * Output the field value
	 * Display unordered list of images with option for size and link to full size
	 *
	 * @param  array    $field   Field parameters
	 * @param  array    $args    Additional arguments. Not used for these fields.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 * @return mixed Field value
	 */
	static function the_value( $field, $args = array(), $post_id = null )
	{
		$value = self::get_value( $field, $args, $post_id );
		if ( ! $value )
			return '';

		if ( $field['clone'] )
		{
			$output = '<ul>';
			if ( $field['multiple'] )
			{
				foreach ( $value as $subvalue )
				{
					$output .= '<li><ul>';
					foreach ( $subvalue as $option )
					{
						$output .= sprintf( '<li><img src="%s"></li>', esc_url( $field['options'][$option] ) );
					}
					$output .= '</ul></li>';
				}
			}
			else
			{
				foreach ( $value as $subvalue )
				{
					$output .= sprintf( '<li><img src="%s"></li>', esc_url( $field['options'][$subvalue] ) );
				}
			}
			$output .= '</ul>';
		}
		else
		{
			if ( $field['multiple'] )
			{
				$output = '<ul>';
				foreach ( $value as $subvalue )
				{
					$output .= sprintf( '<li><img src="%s"></li>', esc_url( $field['options'][$subvalue] ) );
				}
				$output .= '</ul>';
			}
			else
			{
				$output = sprintf( '<img src="%s">', esc_url( $field['options'][$value] ) );
			}
		}

		return $output;
	}
}

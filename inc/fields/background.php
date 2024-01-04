<?php
defined( 'ABSPATH' ) || die;

/**
 * The background field.
 */
class RWMB_Background_Field extends RWMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-background', RWMB_CSS_URL . 'background.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-background', 'path', RWMB_CSS_DIR . 'background.css' );

		$args  = func_get_args();
		$field = reset( $args );
		$color = RWMB_Color_Field::normalize( [
			'type'          => 'color',
			'id'            => "{$field['id']}_color",
			'field_name'    => "{$field['field_name']}[color]",
			'alpha_channel' => true,
		] );
		RWMB_Color_Field::admin_enqueue_scripts( $color );
		RWMB_File_Input_Field::admin_enqueue_scripts();
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field settings.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$meta = wp_parse_args( $meta, [
			'color'      => '',
			'image'      => '',
			'repeat'     => '',
			'attachment' => '',
			'position'   => '',
			'size'       => '',
		] );

		$output = '<div class="rwmb-background-row">';

		// Color.
		$color   = RWMB_Color_Field::normalize( [
			'type'          => 'color',
			'id'            => "{$field['id']}_color",
			'field_name'    => "{$field['field_name']}[color]",
			'alpha_channel' => true,
		] );
		$output .= RWMB_Color_Field::html( $meta['color'], $color );

		$output .= '</div><!-- .rwmb-background-row -->';
		$output .= '<div class="rwmb-background-row">';

		// Image.
		$image   = RWMB_File_Input_Field::normalize( [
			'type'        => 'file_input',
			'id'          => "{$field['id']}_image",
			'field_name'  => "{$field['field_name']}[image]",
			'placeholder' => __( 'Background Image', 'meta-box' ),
		] );
		$output .= RWMB_File_Input_Field::html( $meta['image'], $image );

		$output .= '</div><!-- .rwmb-background-row -->';
		$output .= '<div class="rwmb-background-row">';

		// Repeat.
		$repeat  = RWMB_Select_Field::normalize( [
			'type'        => 'select',
			'id'          => "{$field['id']}_repeat",
			'field_name'  => "{$field['field_name']}[repeat]",
			'placeholder' => esc_html__( '-- Repeat --', 'meta-box' ),
			'options'     => [
				'no-repeat' => esc_html__( 'No Repeat', 'meta-box' ),
				'repeat'    => esc_html__( 'Repeat All', 'meta-box' ),
				'repeat-x'  => esc_html__( 'Repeat Horizontally', 'meta-box' ),
				'repeat-y'  => esc_html__( 'Repeat Vertically', 'meta-box' ),
				'inherit'   => esc_html__( 'Inherit', 'meta-box' ),
			],
		] );
		$output .= RWMB_Select_Field::html( $meta['repeat'], $repeat );

		// Position.
		$position = RWMB_Select_Field::normalize( [
			'type'        => 'select',
			'id'          => "{$field['id']}_position",
			'field_name'  => "{$field['field_name']}[position]",
			'placeholder' => esc_html__( '-- Position --', 'meta-box' ),
			'options'     => [
				'top left'      => esc_html__( 'Top Left', 'meta-box' ),
				'top center'    => esc_html__( 'Top Center', 'meta-box' ),
				'top right'     => esc_html__( 'Top Right', 'meta-box' ),
				'center left'   => esc_html__( 'Center Left', 'meta-box' ),
				'center center' => esc_html__( 'Center Center', 'meta-box' ),
				'center right'  => esc_html__( 'Center Right', 'meta-box' ),
				'bottom left'   => esc_html__( 'Bottom Left', 'meta-box' ),
				'bottom center' => esc_html__( 'Bottom Center', 'meta-box' ),
				'bottom right'  => esc_html__( 'Bottom Right', 'meta-box' ),
			],
		] );
		$output  .= RWMB_Select_Field::html( $meta['position'], $position );

		// Attachment.
		$attachment = RWMB_Select_Field::normalize( [
			'type'        => 'select',
			'id'          => "{$field['id']}_attachment",
			'field_name'  => "{$field['field_name']}[attachment]",
			'placeholder' => esc_html__( '-- Attachment --', 'meta-box' ),
			'options'     => [
				'fixed'   => esc_html__( 'Fixed', 'meta-box' ),
				'scroll'  => esc_html__( 'Scroll', 'meta-box' ),
				'inherit' => esc_html__( 'Inherit', 'meta-box' ),
			],
		] );
		$output    .= RWMB_Select_Field::html( $meta['attachment'], $attachment );

		// Size.
		$size    = RWMB_Select_Field::normalize( [
			'type'        => 'select',
			'id'          => "{$field['id']}_size",
			'field_name'  => "{$field['field_name']}[size]",
			'placeholder' => esc_html__( '-- Size --', 'meta-box' ),
			'options'     => [
				'inherit' => esc_html__( 'Inherit', 'meta-box' ),
				'cover'   => esc_html__( 'Cover', 'meta-box' ),
				'contain' => esc_html__( 'Contain', 'meta-box' ),
			],
		] );
		$output .= RWMB_Select_Field::html( $meta['size'], $size );
		$output .= '</div><!-- .rwmb-background-row -->';

		return $output;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param array    $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		if ( empty( $value ) ) {
			return '';
		}
		$output = '';
		$value  = array_filter( $value );
		foreach ( $value as $key => $subvalue ) {
			$subvalue = 'image' === $key ? 'url(' . esc_url( $subvalue ) . ')' : $subvalue;
			$output  .= 'background-' . $key . ': ' . $subvalue . ';';
		}
		return $output;
	}
}

<?php
defined( 'ABSPATH' ) || die;

/**
 * The link field.
 */
class RWMB_Link_Field extends RWMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-link', RWMB_CSS_URL . 'link.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-link', 'path', RWMB_CSS_DIR . 'link.css' );
		wp_enqueue_script( 'rwmb-link', RWMB_JS_URL . 'link.js', [ 'jquery' ], RWMB_VER, true );
		wp_localize_script( 'rwmb-link', 'rwmbLink', [
			'editText'       => esc_html__( 'Edit', 'meta-box' ),
			'removeText'     => esc_html__( 'Remove', 'meta-box' ),
			'newTabText'     => esc_html__( '(new tab)', 'meta-box' ),
			'selectLinkText' => esc_html__( 'Add link', 'meta-box' ),
		] );
		wp_enqueue_editor();
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
			'url'     => '',
			'title'   => '',
			'target'  => '',
			'post_id' => 0,
		] );

		$name = $field['field_name'];

		$output  = '<div class="rwmb-link" data-field-name="' . esc_attr( $name ) . '">';
		$output .= '<input type="hidden" name="' . esc_attr( $name ) . '[url]" value="' . esc_attr( $meta['url'] ) . '">';
		$output .= '<input type="hidden" name="' . esc_attr( $name ) . '[title]" value="' . esc_attr( $meta['title'] ) . '">';
		$output .= '<input type="hidden" name="' . esc_attr( $name ) . '[target]" value="' . esc_attr( $meta['target'] ) . '">';
		$output .= '<input type="hidden" name="' . esc_attr( $name ) . '[post_id]" value="' . esc_attr( $meta['post_id'] ) . '">';

		if ( $meta['url'] ) {
			$output .= '<div class="rwmb-link-display">';
			$output .= '<span class="rwmb-link-text">';
			$output .= '<span class="dashicons dashicons-admin-links"></span> ';
			$output .= '<a href="' . esc_url( $meta['url'] ) . '" target="_blank">' . esc_html( $meta['title'] ) . '</a>';
			if ( '_blank' === $meta['target'] ) {
				$output .= ' <span class="rwmb-link-target">' . esc_html__( '(new tab)', 'meta-box' ) . '</span>';
			}
			$output .= '</span> ';
			$output .= '<a href="#" class="rwmb-link-edit">' . esc_html__( 'Edit', 'meta-box' ) . '</a>';
			$output .= '<a href="#" class="rwmb-link-remove">' . esc_html__( 'Remove', 'meta-box' ) . '</a>';
			$output .= '</div>';
		} else {
			$output .= '<div class="rwmb-link-display">';
			$output .= '<a href="#" class="rwmb-link-select">' . esc_html__( 'Add link', 'meta-box' ) . '</a>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Set value of meta before saving into database.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return mixed
	 */
	public static function value( $new, $old, $post_id, $field ) {
		$new = wp_parse_args( $new, [
			'url'     => '',
			'title'   => '',
			'target'  => '',
			'post_id' => 0,
		] );

		$new['url']     = esc_url_raw( $new['url'] );
		$new['title']   = wp_kses_post( $new['title'] );
		$new['target']  = in_array( $new['target'], [ '_blank', '' ], true ) ? $new['target'] : '';
		$new['post_id'] = absint( $new['post_id'] );

		$all_empty = empty( $new['url'] ) && empty( $new['title'] ) && empty( $new['post_id'] );
		return $all_empty ? [] : $new;
	}

	/**
	 * Format a single value for the helper functions.
	 *
	 * @param array    $field   Field parameters.
	 * @param array    $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		if ( empty( $value['url'] ) ) {
			return '';
		}
		$url    = esc_url( $value['url'] );
		$title  = esc_html( $value['title'] );
		$target = ! empty( $value['target'] ) ? ' target="' . esc_attr( $value['target'] ) . '"' : '';
		return '<a href="' . $url . '"' . $target . '>' . $title . '</a>';
	}
}

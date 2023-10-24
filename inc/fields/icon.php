<?php
defined( 'ABSPATH' ) || die;

/**
 * The icon field.
 */
class RWMB_Icon_Field extends RWMB_Select_Advanced_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();

		wp_enqueue_style( 'rwmb-icon', RWMB_CSS_URL . 'icon.css', [], RWMB_VER );
		wp_enqueue_script( 'rwmb-icon', RWMB_JS_URL . 'icon.js', [ 'rwmb-select2', 'rwmb-select', 'underscore' ], RWMB_VER, true );

		self::enqueue_style();
	}

	private static function enqueue_style() {
		wp_enqueue_style( 'rwmb-fontawesome', RWMB_CSS_URL . 'fontawesome/all.min.css', [], RWMB_VER );
	}

	private static function get_list_fonts( $icon_json = null ) {

		if ( $icon_json && ! file_exists( RWMB_CSS_DIR . 'fontawesome/icons.json' ) ) {
			return [];
		}

		$icons = $icon_json ?
		json_decode( file_get_contents( wp_normalize_path( $icon_json ) ), true ) :
		json_decode( file_get_contents( RWMB_CSS_DIR . 'fontawesome/icons.json' ), true );

		$icon_list = [];

		foreach ( $icons as $key => $icon ) {
			$icon_entry          = [
				'label' => $icon['label'],
			];
			$icon_entry['value'] = $icon_json ? $key : 'fa-' . $icon['styles'][0] . ' fa-' . $key;
			// Append the icon entry to the $icon_list array
			$icon_list[] = $icon_entry;
		}
		return $icon_list;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'js_options'     => [],
			'list_icon'      => [],
			'icon_set'       => 'fontawesome',
			'enqueue_script' => '',
			'icon_json'      => '',
			'placeholder'    => __( 'Select an icon', 'meta-box' ),
		] );

		$field['list_icon'] = array_merge( $field['list_icon'], [ 'fontawesome' ] );

		$field = parent::normalize( $field );

		$field['js_options'] = wp_parse_args( $field['js_options'], [
			'allowClear'        => true,
			'dropdownAutoWidth' => true,
			'placeholder'       => $field['placeholder'],
			'width'             => 'style',
		] );

		if ( $field['icon_set'] != 'fontawesome' ) {
			$field['options'] = self::get_list_fonts( $field['icon_json'] );
			if ( ! is_string( $field['enqueue_script'] ) ) {
				add_action( 'admin_enqueue_scripts', $field['enqueue_script'] );
				return $field;
			}

			add_action('admin_enqueue_scripts', function () use ( $field ) {
				wp_enqueue_style( 'rwmb-custom-icon', $field['enqueue_script'], [], RWMB_VER );
			});

			return $field;
		}
		$field['options'] = self::get_list_fonts();
		return $field;
	}

	/**
	 * Format value for the helper functions.
	 *
	 * @param array        $field   Field parameters.
	 * @param string|array $value   The field meta value.
	 * @param array        $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null     $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_value( $field, $value, $args, $post_id ) {
		// Enqueue style for frontend
		if ( empty( $field['enqueue_script'] ) ) {
			self::enqueue_style();
			$value  = parent::call( 'get_value', $field, $args, $post_id );
			$output = sprintf( '<i class="%s"></i>', $value );
			return $output;
		}

		// Custom Icon
		! is_string( $field['enqueue_script'] ) ? $field['enqueue_script']() : wp_enqueue_style( 'rwmb-custom-icon', $field['enqueue_script'], [], RWMB_VER );

		$value  = parent::call( 'get_value', $field, $args, $post_id );
		$output = sprintf( '<i class="%s"></i>', $value );
		return $output;
	}
}

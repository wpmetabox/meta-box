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

		$args  = func_get_args();
		$field = $args[0];
		self::enqueue_icon_font_style( $field['enqueue_script'] );
	}

	private static function enqueue_icon_font_style( $enqueue_script = null ) {
		if ( empty( $enqueue_script ) ) {
			wp_enqueue_style( 'fontawesome-free', RWMB_CSS_URL . 'fontawesome/all.min.css', [], '6.4.2' );
			return;
		}

		if( is_string( $enqueue_script ) ){
			wp_enqueue_style('rwmb-custom-icon', $enqueue_script, [], '6.4.2');
		}else{
			$enqueue_script();
		}
	}

	private static function get_icons( $field ) {
		// Get from cache to prevent reading large files.
		$icons = wp_cache_get( 'fontawesome-icons', 'meta-box-icon-field' );
		if ( false !== $icons || ! file_exists( RWMB_DIR . 'css/fontawesome/icons.json' ) ) {
			return $icons;
		}

		$data = wp_cache_get( 'fontawesome-icons', 'meta-box-icon-field-' . $field['icon_set'] );
		if ( false === $data ) {
			if ( empty( $field['icon_json'] ) ) {
				$data = json_decode( file_get_contents( RWMB_DIR . 'css/fontawesome/icons.json' ), true );
			} else {
				$data = json_decode( file_get_contents( $field['icon_json'] ), true );
			}

			wp_cache_set( 'fontawesome-icons', $data, 'meta-box-post-field-' . $field['icon_set'] );
		}

		$icons = [];

		foreach ( $data as $key => $icon ) {
			// use icon set other
			if ( ! empty( $field['icon_json'] ) ) {
				$icons[] = [
					'label' => $icon['label'],
					'value' => $key,
				];
				continue;
			}

			$icons[] = [
				'label' => $icon['label'],
				'value' => "fa-{$icon['styles'][0]} fa-{$key}",
			];
		}

		// Cache the result.
		wp_cache_set( 'fontawesome-icons', $icons, 'meta-box-post-field' );

		return $icons;
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
			'list_icon'      => [],
			'icon_set'       => 'fontawesome',
			'placeholder'    => __( 'Select an icon', 'meta-box' ),
			'enqueue_script' => '',
			'icon_json'      => '',
			'options'        => self::get_icons( $field ),
		] );

		$field['list_icon'] = array_merge( $field['list_icon'], [ 'fontawesome' ] );
		$field              = parent::normalize( $field );

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
		self::enqueue_icon_font_style( $field['enqueue_script'] );

		return sprintf( '<i class="%s"></i>', $value );
	}
}

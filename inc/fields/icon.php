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

		self::enqueue_icon_font_style();
	}

	private static function enqueue_icon_font_style() {
		wp_enqueue_style( 'rwmb-fontawesome', RWMB_CSS_URL . 'fontawesome/all.min.css', [], RWMB_VER );
	}

	private static function get_icons() {
		// Get from cache to prevent reading large files.
		$icons = wp_cache_get( 'fontawesome-icons', 'meta-box-icon-field' );
		if ( false !== $icons ) {
			return $icons;
		}

		$data  = json_decode( file_get_contents( RWMB_CSS_DIR . 'fontawesome/icons.json' ), true );
		$icons = [];

		foreach ( $data as $key => $icon ) {
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
			'icon_set'    => 'fontawesome',
			'placeholder' => __( 'Select an icon', 'meta-box' ),
			'options'     => self::get_icons(),
		] );

		$field = parent::normalize( $field );

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
		self::enqueue_icon_font_style();

		return sprintf( '<i class="%s"></i>', $value );
	}
}

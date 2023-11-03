<?php
defined( 'ABSPATH' ) || die;

/**
 * The icon field.
 */
class RWMB_Icon_Field extends RWMB_Select_Advanced_Field {
	const CACHE_GROUP = 'meta-box-icon-field';

	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();

		wp_enqueue_style( 'rwmb-icon', RWMB_CSS_URL . 'icon.css', [], RWMB_VER );
		wp_enqueue_script( 'rwmb-icon', RWMB_JS_URL . 'icon.js', [ 'rwmb-select2', 'rwmb-select', 'underscore' ], RWMB_VER, true );

		$args  = func_get_args();
		$field = $args[0];
		self::enqueue_icon_font_style( $field );
	}

	private static function enqueue_icon_font_style( array $field ) {
		if ( $field['icon_set'] === 'font-awesome-free' ) {
			wp_enqueue_style( 'font-awesome-free', RWMB_CSS_URL . 'fontawesome/all.min.css', [], '6.4.2' );
			return;
		}

		if ( is_string( $field['icon_style'] ) ) {
			wp_enqueue_style( $field['icon_set'], $field['icon_style'], [], RWMB_VER );
		} else {
			$field['icon_style']();
		}
	}

	private static function get_icons( $field ) {
		// Get from cache to prevent reading large files.
		$cache_key = "{$field['icon_set']}-icons";
		$icons     = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $icons ) {
			return $icons;
		}

		// Get icon from a JSON or a text file.
		$data    = file_get_contents( $field['icon_file'] );
		$decoded = json_decode( $data, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			$data = $decoded;
		} else {
			// Text file: each icon on a line.
			$data = explode( "\n", $data );
			$data = array_map( 'trim', $data );
		}

		// Reformat icons.
		$icons = [];
		foreach ( $data as $key => $icon ) {
			// Default: FontAwesome
			if ( $field['icon_set'] === 'font-awesome-free' ) {
				$icons[] = [
					'value' => "fa-{$icon['styles'][0]} fa-{$key}",
					'label' => $icon['label'],
					'svg'   => '',
				];
				continue;
			}

			// Text file: each icon on a line.
			if ( is_string( $icon ) && is_numeric( $key ) ) {
				$icons[] = [
					'value' => $icon,
					'label' => $icon,
					'svg'   => '',
				];
				continue;
			}

			// JSON file: "icon-class": "Label" or "icon-class": "<svg...>".
			if ( is_string( $icon ) ) {
				$label   = str_contains( $icon, '<svg' ) ? $key : $icon;
				$svg     = str_contains( $icon, '<svg' ) ? $icon : '';
				$icons[] = [
					'value' => $key,
					'label' => $label,
					'svg'   => $svg,
				];
				continue;
			}

			// JSON file: "icon-class": { "label": "Label", "svg": "<svg...>" }
			$label   = empty( $icon['label'] ) ? $key : $icon['label'];
			$svg     = empty( $icon['svg'] ) ? '' : $icon['svg'];
			$icons[] = [
				'value' => $key,
				'label' => $label,
				'svg'   => $svg,
			];
		}

		// Cache the result.
		wp_cache_set( $cache_key, $icons, self::CACHE_GROUP );
		return $icons;
	}

	/**
	 * Normalize field settings.
	 *
	 * @param array $field Field settings.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'placeholder' => __( 'Select an icon', 'meta-box' ),
			'icon_style'  => '',
			'icon_set'    => 'font-awesome-free',
			'icon_file'   => RWMB_DIR . 'css/fontawesome/icons.json',
            'svg_output'  => false,
			'svg_dir'     => '',
		] );

		$field['options'] = self::get_icons( $field );

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
		// Render icon font.
		if ( empty( $field['svg_output'] ) ) {
			self::enqueue_icon_font_style( $field['enqueue_script'] );
			return sprintf( '<i class="%s"></i>', $value );
		}

		// Render svg for Font Awesome Free.
		if ( $field['icon_set'] === 'font-awesome-free' ) {
			$pattern = '/fa-(brands|solid|regular) fa-(.*)/';
			preg_match( $pattern, $value, $matches );
			$svg = file_get_contents( RWMB_CSS_URL . 'fontawesome/svgs/' . $matches[1] . '/' . $matches[2] . '.svg' );
			return $svg;
		}

		// Render svg with path svg_dir.
		if ( ! empty( $field['svg_dir'] ) ) {
			$file = trailingslashit( $field[ 'svg_dir' ] ) . $value . '.svg';
			return file_exists( $file ) ? file_get_contents( $file ) : '';
		}

		// Render svg from options.
		$key = array_search( $value, array_column( $field['options'], 'value' ) );
		if ( $key !== false ) {
			return $field['options'][ $key ]['svg'];
		}
	}
}

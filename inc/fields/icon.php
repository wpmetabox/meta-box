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
		$field = $args[ 0 ];
		self::enqueue_icon_font_style( $field );
	}

	private static function enqueue_icon_font_style( array $field ): void {
		// Use SVG instead of CSS.
		if ( $field[ 'icon_dir' ] ) {
			return;
		}

		if ( is_string( $field[ 'icon_style' ] ) ) {
			wp_enqueue_style( $field[ 'icon_set' ], $field[ 'icon_style' ], [], RWMB_VER );
		} elseif ( is_callable( $field[ 'icon_style' ] ) ) {
			$field[ 'icon_style' ]();
		}
	}

	private static function get_icons( array $field ): array {
		if ( ! file_exists( $field[ 'icon_file' ] ) ) {
			return [];
		}

		// Get from cache to prevent reading large files.
		$cache_key = $field[ 'icon_dir' ] ? "{$field[ 'icon_set' ]}-icons-svg" : "{$field[ 'icon_set' ]}-icons";
		$icons     = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $icons ) {
			return $icons;
		}

		// Get icon from a JSON or a text file.
		$data    = file_get_contents( $field[ 'icon_file' ] );
		$decoded = json_decode( $data, true );
		if ( JSON_ERROR_NONE === json_last_error() ) {
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
			if ( $field[ 'icon_set' ] === 'font-awesome-free' ) {
				$icons[] = [
					'value' => "fa-{$icon[ 'styles' ][ 0 ]} fa-{$key}",
					'label' => $icon[ 'label' ],
					'svg'   => $icon[ 'svg' ][ $icon[ 'styles' ][ 0 ] ][ 'raw' ],
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
			$label   = empty( $icon[ 'label' ] ) ? $key : $icon[ 'label' ];
			$svg     = empty( $icon[ 'svg' ] ) ? '' : $icon[ 'svg' ];
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

	private static function get_svg( array $field, string $value ): string {
		$file = trailingslashit( $field[ 'icon_dir' ] ) . $value . '.svg';
		return file_exists( $file ) ? file_get_contents( $file ) : '';
	}

	private static function get_options( array $field ): array {
		$icons = self::get_icons( $field );

		$options = [];
		foreach ( $icons as $icon ) {
			$svg = $field[ 'icon_dir' ] ? self::get_svg( $field, $icon[ 'value' ] ) : $icon[ 'svg' ];

			$options[] = [
				'value' => $icon[ 'value' ],
				'label' => $svg . $icon[ 'label' ],
			];
		}

		return $options;
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
			'icon_dir'    => '',
		] );

		$field[ 'options' ] = self::get_options( $field );

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
		// SVG from file.
		if ( $field[ 'icon_dir' ] ) {
			return self::get_svg( $field, $value );
		}

		$icons = self::get_icons( $field );
		$key   = array_search( $value, array_column( $icons, 'value' ) );
		if ( false === $key ) {
			return '';
		}

		// Embed SVG.
		if ( $icons[ $key ][ 'svg' ] ) {
			return $icons[ $key ][ 'svg' ];
		}

		// Render with class and use css.
		self::enqueue_icon_font_style( $field );
		return sprintf( '<span class="%s"></span>', $value );
	}
}

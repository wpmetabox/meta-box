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
		wp_style_add_data( 'rwmb-icon', 'path', RWMB_CSS_DIR . 'icon.css' );
		wp_enqueue_script( 'rwmb-icon', RWMB_JS_URL . 'icon.js', [ 'rwmb-select2', 'rwmb-select', 'underscore' ], RWMB_VER, true );

		$args  = func_get_args();
		$field = $args[0];
		self::enqueue_icon_font_style( $field );
	}

	private static function enqueue_icon_font_style( array $field ): void {
		if ( is_string( $field['icon_css'] ) ) {
			$handle = md5( $field['icon_css'] );
			wp_enqueue_style( $handle, $field['icon_css'], [], RWMB_VER );
		} elseif ( is_callable( $field['icon_css'] ) ) {
			$field['icon_css']();
		}
	}

	private static function get_icons( array $field ): array {
		// Get from cache to prevent reading large files.
		$params    = [
			'icon_file' => $field['icon_file'],
			'icon_dir'  => $field['icon_dir'],
			'icon_css'  => is_string( $field['icon_css'] ) ? $field['icon_css'] : '',
		];
		$cache_key = md5( serialize( $params ) ) . '-icons';
		$icons     = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $icons ) {
			return $icons;
		}

		$data = self::parse_icon_data( $field );

		// Reformat icons.
		$icons = [];
		foreach ( $data as $key => $icon ) {
			$icon = self::normalize_icon( $field, $key, $icon );

			if ( is_numeric( key( $icon ) ) ) {
				$icons = array_merge( $icons, $icon );
				continue;
			}

			$icons[] = $icon;
		}

		// Cache the result.
		wp_cache_set( $cache_key, $icons, self::CACHE_GROUP );
		return $icons;
	}

	private static function parse_icon_data( array $field ): array {
		$keys = [
			'icon_file',
			'icon_css',
			'icon_dir',
		];
		foreach ( $keys as $key ) {
			if ( ! empty( $field[ $key ] ) && is_string( $field[ $key ] ) ) {
				return call_user_func( [ __CLASS__, "parse_$key" ], $field );
			}
		}

		return [];
	}

	private static function parse_icon_file( array $field ): array {
		if ( ! file_exists( $field['icon_file'] ) ) {
			return [];
		}

		$data    = (string) file_get_contents( $field['icon_file'] );
		$decoded = json_decode( $data, true );

		// JSON file.
		if ( JSON_ERROR_NONE === json_last_error() ) {
			return $decoded;
		}

		// Text file: each icon on a line.
		return array_map( 'trim', explode( "\n", $data ) );
	}

	private static function parse_icon_css( array $field ): array {
		// Parse local CSS file only.
		$file = self::url_to_path( $field['icon_css'] );
		if ( ! file_exists( $file ) ) {
			return [];
		}

		$css = (string) file_get_contents( $file );

		preg_match_all( '/\.([^\s:]+):before/', $css, $matches );

		if ( empty( $matches[1] ) ) {
			preg_match_all( '/\.([^\s:]+)/', $css, $matches );
		}

		return $matches[1];
	}

	private static function parse_icon_dir( array $field ): array {
		$dir = $field['icon_dir'];
		if ( ! is_dir( $dir ) ) {
			return [];
		}

		$icons = [];
		$files = glob( trailingslashit( $dir ) . '*.svg' );

		foreach ( $files as $file ) {
			$filename = substr( basename( $file ), 0, -4 );
			$icons[]  = [
				'value' => $filename,
				'label' => $filename,
				'svg'   => file_get_contents( $file ),
			];
		}

		return $icons;
	}

	private static function normalize_icon( array $field, $key, $icon ): array {
		// Default: Font Awesome Free.
		if ( $field['icon_set'] === 'font-awesome-free' ) {
			$style = $icon['styles'][0];
			return [
				'value' => "fa-{$style} fa-{$key}",
				'label' => $icon['label'],
				'svg'   => $icon['svg'][ $style ]['raw'],
			];
		}

		// Font Awesome Pro.
		if ( $field['icon_set'] === 'font-awesome-pro' ) {
			$icons = [];
			foreach ( $icon['styles'] as $style ) {
				$icons[] = [
					'value' => "fa-{$style} fa-{$key}",
					'label' => "{$icon[ 'label' ]} ({$style})",
					'svg'   => $icon['svg'][ $style ]['raw'],
				];
			}
			return $icons;
		}

		// JSON file: "icon-class": { "label": "Label", "svg": "<svg...>" }
		if ( is_array( $icon ) ) {
			$label = empty( $icon['label'] ) ? $key : $icon['label'];
			$svg   = empty( $icon['svg'] ) ? '' : $icon['svg'];
			return [
				'value' => $key,
				'label' => $label,
				'svg'   => $svg,
			];
		}

		// JSON file: "icon-class": "Label" or "icon-class": "<svg...>".
		if ( is_string( $key ) ) {
			$label = str_contains( $icon, '<svg' ) ? $key : $icon;
			$svg   = str_contains( $icon, '<svg' ) ? $icon : '';
			return [
				'value' => $key,
				'label' => $label,
				'svg'   => $svg,
			];
		}

		// Parse classes from CSS.
		if ( $field['icon_css'] && ! $field['icon_file'] ) {
			$icon = trim( $field['icon_base_class'] . ' ' . $icon );
		}

		// Text file: each icon on a line.
		return [
			'value' => $icon,
			'label' => $icon,
			'svg'   => '',
		];
	}

	private static function get_svg( array $field, string $value ): string {
		$file = trailingslashit( $field['icon_dir'] ) . $value . '.svg';
		return file_exists( $file ) ? file_get_contents( $file ) : '';
	}

	private static function get_options( array $field ): array {
		$icons = self::get_icons( $field );

		$options = [];
		foreach ( $icons as $icon ) {
			$svg = ! $icon['svg'] && $field['icon_dir'] ? self::get_svg( $field, $icon['value'] ) : $icon['svg'];

			$options[] = [
				'value' => $icon['value'],
				'label' => $svg . $icon['label'],
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
			'placeholder'     => __( 'Select an icon', 'meta-box' ),
			'icon_css'        => '',
			'icon_set'        => '',
			'icon_file'       => '',
			'icon_dir'        => '',
			'icon_base_class' => '',
		] );

		// Ensure absolute paths and URLs.
		$field['icon_file'] = self::ensure_absolute_path( $field['icon_file'] );
		$field['icon_dir']  = self::ensure_absolute_path( $field['icon_dir'] );
		if ( is_string( $field['icon_css'] ) && $field['icon_css'] ) {
			$field['icon_css'] = self::ensure_absolute_url( $field['icon_css'] );
		}

		// Font Awesome Pro.
		if ( $field['icon_set'] === 'font-awesome-pro' ) {

		} elseif ( $field['icon_file'] || $field['icon_dir'] || $field['icon_css'] ) {
			// Custom icon set.
			$field['icon_set'] = 'custom';
		} else {
			// Font Awesome Free.
			$field['icon_set']  = 'font-awesome-free';
			$field['icon_file'] = RWMB_DIR . 'css/fontawesome/icons.json';
		}

		$field['options'] = self::get_options( $field );

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
		if ( $field['icon_dir'] ) {
			return self::get_svg( $field, $value );
		}

		$icons = self::get_icons( $field );
		$key   = array_search( $value, array_column( $icons, 'value' ) );
		if ( false === $key ) {
			return '';
		}

		// Embed SVG.
		if ( $icons[ $key ]['svg'] ) {
			return $icons[ $key ]['svg'];
		}

		// Render with class and use css.
		self::enqueue_icon_font_style( $field );
		return sprintf( '<span class="%s"></span>', $value );
	}
	private static function url_to_path( string $url ): string {
		return str_starts_with( $url, home_url( '/' ) ) ? str_replace( home_url( '/' ), trailingslashit( ABSPATH ), $url ) : '';
	}

	private static function ensure_absolute_path( string $path ): string {
		if ( ! $path || file_exists( $path ) ) {
			return $path;
		}

		$root = wp_normalize_path( ABSPATH );
		$path = wp_normalize_path( $path );

		return str_starts_with( $path, $root ) ? $path : trailingslashit( $root ) . ltrim( $path, '/' );
	}

	private static function ensure_absolute_url( string $url ): string {
		return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : home_url( $url );
	}
}

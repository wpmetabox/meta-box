<?php
defined( 'ABSPATH' ) || die;

/**
 * Block editor field leveraging the isolated block editor package.
 *
 * @see https://github.com/Automattic/isolated-block-editor
 */
class RWMB_Block_Editor_Field extends RWMB_Field {
	/**
	 * Enqueue scripts and styles for the field.
	 * @see https://github.com/Automattic/isolated-block-editor/blob/trunk/examples/wordpress-php/iso-gutenberg.php
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_editor();
		wp_enqueue_media();

		do_action( 'enqueue_block_editor_assets' );

		wp_register_script(
			'isolated-block-editor',
			'https://cdn.jsdelivr.net/gh/Automattic/isolated-block-editor@2.29.0/build-browser/isolated-block-editor.js',
			[ 'wp-block-library', 'wp-format-library', 'wp-editor' ],
			'2.29.0',
			true
		);

		wp_enqueue_script(
			'rwmb-block-editor',
			RWMB_JS_URL . 'block-editor.js',
			[ 'isolated-block-editor' ],
			RWMB_VER,
			true
		);

		wp_register_style(
			'isolated-block-editor-core',
			'https://cdn.jsdelivr.net/gh/Automattic/isolated-block-editor@2.29.0/build-browser/core.css',
			[],
			'2.29.0'
		);

		wp_register_style(
			'isolated-block-editor',
			'https://cdn.jsdelivr.net/gh/Automattic/isolated-block-editor@2.29.0/build-browser/isolated-block-editor.css',
			[ 'wp-edit-post', 'wp-format-library' ],
			'2.29.0'
		);

		wp_enqueue_style(
			'rwmb-block-editor',
			RWMB_CSS_URL . 'block-editor.css',
			[ 'isolated-block-editor-core', 'isolated-block-editor' ],
			RWMB_VER
		);
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );

		$field = wp_parse_args( $field, [
			'allowed_blocks' => [],
		] );

		// Parse allowed_blocks from textarea (one block per line) to array for MB Builder
		if ( is_string( $field['allowed_blocks'] ) ) {
			$field['allowed_blocks'] = array_map( 'trim', explode( "\n", $field['allowed_blocks'] ) );
		}

		$field['allowed_blocks'] = array_values( array_filter( $field['allowed_blocks'] ) );

		return $field;
	}

	/**
	 * Get field HTML.
	 *
	 * @param string $meta  Meta value.
	 * @param array  $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		return sprintf(
			// Translators: %1$s - editor settings, %2$s - value
			'<textarea data-settings="%1$s">%2$s</textarea>',
			esc_attr( wp_json_encode( self::prepare_settings( $field ) ) ),
			esc_textarea( $meta )
		);
	}

	/**
	 * Format the value on the front end.
	 *
	 * @param array  $field   Field parameters.
	 * @param string $value   The saved value.
	 * @param array  $args    Additional arguments.
	 * @param int    $post_id Current post ID.
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		return do_blocks( $value );
	}

	/**
	 * Build settings passed to the isolated editor instance.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	protected static function prepare_settings( array $field ): array {
		$settings = [
			'iso' => [
				'blocks' => [
					'allowBlocks' => $field['allowed_blocks'],
				],
			],
			'upload' => current_user_can( 'upload_files' ),
		];

		return $settings;
	}
}

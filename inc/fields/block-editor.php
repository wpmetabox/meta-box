<?php
defined( 'ABSPATH' ) || die;

/**
 * Block Editor field leveraging the isolated block editor package.
 *
 * @since 1.0.0
 */
class RWMB_Block_Editor_Field extends RWMB_Field {
	/**
	 * Enqueue scripts and styles for the field.
	 */
	public static function admin_enqueue_scripts() {
		do_action( 'enqueue_block_editor_assets' );
		do_action( 'enqueue_block_assets' );

		$packages = [
			'wp-element',
			'wp-blocks',
			'wp-block-editor',
			'wp-components',
			'wp-data',
			'wp-compose',
			'wp-i18n',
			'wp-hooks',
			'wp-media-utils',
		];

		wp_register_script(
			'isolated-block-editor',
			RWMB_JS_URL . 'isolated-block-editor.js',
			$packages,
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
			RWMB_CSS_URL . 'isolated-block-editor-core.css',
			[],
			'2.29.0'
		);

		wp_register_style(
			'isolated-block-editor',
			RWMB_CSS_URL . 'isolated-block-editor.css',
			[ 'isolated-block-editor-core' ],
			'2.29.0'
		);

		wp_enqueue_style(
			'rwmb-block-editor',
			RWMB_CSS_URL . 'block-editor.css',
			[ 'wp-components', 'wp-block-library', 'wp-edit-blocks', 'isolated-block-editor' ],
			RWMB_VER
		);
		wp_style_add_data( 'rwmb-block-editor', 'path', RWMB_CSS_DIR . 'block-editor.css' );
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

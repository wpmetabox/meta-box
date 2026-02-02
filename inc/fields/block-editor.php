<?php
defined( 'ABSPATH' ) || die;

class RWMB_Block_Editor_Field extends RWMB_Field {
	public static function admin_enqueue_scripts(): void {
		$asset_file = RWMB_DIR . 'js/block-editor/build/block-editor.asset.php';
		$asset      = require $asset_file;

		wp_enqueue_style(
			'rwmb-block-editor',
			RWMB_URL . 'js/block-editor/build/style-block-editor.css',
			[
				'wp-block-editor',        // @wordpress/block-editor/build-style/style.css
				'wp-components',          // @wordpress/components/build-style/style.css
				'wp-edit-blocks',         // @wordpress/block-library/build-style/editor.css
				'wp-block-library',       // @wordpress/block-library/build-style/style.css
				'wp-block-library-theme', // @wordpress/block-library/build-style/theme.css
				'wp-format-library',
			],
			$asset['version']
		);

		wp_enqueue_script(
			'rwmb-block-editor',
			RWMB_URL . 'js/block-editor/build/block-editor.js',
			array_merge( $asset['dependencies'], [ 'rwmb' ] ),
			$asset['version'],
			true
		);

		$block_editor_context = new WP_Block_Editor_Context();
		$editor_settings      = get_block_editor_settings( [], $block_editor_context );
		RWMB_Helpers_Field::localize_script_once( 'rwmb-block-editor', 'rwmbBlockEditor', [
			'editor_settings' => $editor_settings,
		] );
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
			'height'         => '300px',
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
	 * @param array  $field Field settings.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		return sprintf(
			'<textarea data-settings="%1$s" %2$s>%3$s</textarea>',
			esc_attr( wp_json_encode( self::get_editor_settings( $field ) ) ),
			self::render_attributes( self::get_attributes( $field, $meta ) ),
			esc_textarea( (string) $meta )
		);
	}

	/**
	 * Format the value on the front end.
	 *
	 * @param array  $field   Field settings.
	 * @param string $value   The saved value.
	 * @param array  $args    Additional arguments.
	 * @param int    $post_id Current post ID.
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		return do_blocks( $value );
	}

	protected static function get_editor_settings( array $field ): array {
		$keys = [ 'allowed_blocks', 'height' ];
		return array_filter( array_intersect_key( $field, array_flip( $keys ) ) );
	}
}

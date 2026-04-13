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

		// Load block categories.
		wp_add_inline_script(
			'wp-blocks',
			sprintf( 'wp.blocks.setCategories( %s );', wp_json_encode( get_block_categories( $block_editor_context ) ) ),
			'after'
		);

		// Preload server-registered block schemas.
		wp_add_inline_script(
			'wp-blocks',
			'wp.blocks.unstable__bootstrapServerSideBlockDefinitions(' . wp_json_encode( get_block_editor_server_block_settings(), JSON_HEX_TAG | JSON_UNESCAPED_SLASHES ) . ');'
		);

		// Load 3rd party blocks.
		add_filter( 'should_load_block_editor_scripts_and_styles', '__return_true' );

		if ( ! did_action( 'enqueue_block_editor_assets' ) ) {
			do_action( 'enqueue_block_editor_assets' );
		}

		do_action( 'rwmb_enqueue_block_editor_assets' );
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
			'allowed_blocks'   => [],
			'height'           => '300px',
			'toolbar_position' => 'top',
		] );

		$field['toolbar_position'] = in_array( $field['toolbar_position'], [ 'top', 'contextual' ], true )
			? $field['toolbar_position']
			: 'top';

		// Parse allowed_blocks from textarea (one block per line) to array for MB Builder
		if ( is_string( $field['allowed_blocks'] ) ) {
			$field['allowed_blocks'] = array_map( 'trim', explode( "\n", $field['allowed_blocks'] ) );
		}

		$field['allowed_blocks'] = self::add_child_blocks( $field['allowed_blocks'] );
		$field['allowed_blocks'] = array_unique( array_values( array_filter( $field['allowed_blocks'] ) ) );

		return $field;
	}

	private static function add_child_blocks( array $blocks ): array {
		$all_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

		$list = $blocks;
		foreach ( $blocks as $block_type ) {
			$children = array_filter( $all_blocks, fn( $block ) => is_array( $block->parent ) && in_array( $block_type, $block->parent, true ) );
			$children = array_map( fn( $block ) => $block->name, $children );
			$list     = array_merge( $list, $children );
		}

		return $list;
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
		$new = (string) $new;

		// Remove the only empty paragraph block.
		$pattern = '/^\s*<!-- wp:paragraph -->\s*?<p><\/p>\s*?<!-- \/wp:paragraph -->\s*$/';

		return preg_replace( $pattern, '', $new );
	}

	protected static function get_editor_settings( array $field ): array {
		$keys = [ 'allowed_blocks', 'height', 'toolbar_position' ];
		return array_filter( array_intersect_key( $field, array_flip( $keys ) ) );
	}
}

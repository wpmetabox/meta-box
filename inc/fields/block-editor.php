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
		wp_enqueue_editor();
		wp_enqueue_media();

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
		];

		$bundle_url = RWMB_JS_URL . 'isolated-block-editor.js';

		wp_register_script(
			'rwmb-block-editor-iso',
			$bundle_url,
			$packages,
			'2.29.0',
			true
		);

		wp_enqueue_script( 'rwmb-block-editor-iso' );

		wp_enqueue_script(
			'rwmb-block-editor',
			RWMB_JS_URL . 'block-editor.js',
			array_merge( $packages, [ 'rwmb-block-editor-iso' ] ),
			RWMB_VER,
			true
		);

		wp_localize_script(
			'rwmb-block-editor',
			'rwmbBlockEditorField',
			[
				'bundleUrl' => $bundle_url,
			]
		);

		wp_register_style(
			'rwmb-block-editor-iso-core',
			RWMB_CSS_URL . 'isolated-block-editor-core.css',
			[],
			'2.29.0'
		);

		wp_register_style(
			'rwmb-block-editor-iso',
			RWMB_CSS_URL . 'isolated-block-editor.css',
			[ 'rwmb-block-editor-iso-core' ],
			'2.29.0'
		);

		wp_enqueue_style(
			'rwmb-block-editor',
			RWMB_CSS_URL . 'block-editor.css',
			[ 'wp-components', 'wp-block-library', 'wp-edit-blocks', 'rwmb-block-editor-iso' ],
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

		$field = wp_parse_args(
			$field,
			[
				'allowed_blocks' => [],
			]
		);

		$field['allowed_blocks'] = array_values(
			array_filter(
				array_map(
					static function ( $block ) {
						return is_string( $block ) ? sanitize_text_field( $block ) : '';
					},
					(array) $field['allowed_blocks']
				)
			)
		);

		return $field;
	}

	/**
	 * Sanitize the value before saving.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 * @return string
	 */
	public static function value( $new, $old, $post_id, $field ) {
		return is_string( $new ) ? wp_kses_post( $new ) : '';
	}

	/**
	 * Get field HTML.
	 *
	 * @param string $meta  Meta value.
	 * @param array  $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$attributes = self::get_attributes( $field, $meta );
		$attributes['class'] = trim( $attributes['class'] . ' rwmb-block-editor-input' );
		$attributes['style'] = 'display:none;';

		$editor_id = $attributes['id'] ?: 'rwmb-block-editor-' . uniqid();
		$settings  = self::prepare_settings( $field );

		$encoded_settings = wp_json_encode( $settings );
		if ( false === $encoded_settings ) {
			$encoded_settings = '{}';
		}

		ob_start();
		?>
		<div
			class="rwmb-block-editor-wrapper"
			data-editor-id="<?php echo esc_attr( $editor_id ); ?>"
			data-settings="<?php echo esc_attr( $encoded_settings ); ?>"
		>
			<textarea<?php echo self::render_attributes( $attributes ); ?>><?php echo esc_textarea( $meta ); ?></textarea>
		</div>
		<?php
		return ob_get_clean();
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
		if ( empty( $value ) ) {
			return '';
		}

		if ( function_exists( 'do_blocks' ) ) {
			return do_blocks( $value );
		}

		return apply_filters( 'the_content', $value );
	}

	/**
	 * Build settings passed to the isolated editor instance.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	protected static function prepare_settings( $field ) {
		$settings = [
			'editor' => self::get_editor_settings(),
			'iso'    => [
				'blocks'            => [
					'allowBlocks' => self::get_allowed_blocks( $field ),
				],
				'toolbar'           => false,
				'moreMenu'          => false,
				'defaultPreferences'=> false,
				'allowEmbeds'       => [
					'youtube',
					'vimeo',
					'wordpress',
					'wordpress-tv',
					'videopress',
					'crowdsignal',
					'imgur',
				],
			],
			'editorType'        => 'core',
			'allowUrlEmbed'     => false,
			'pastePlainText'    => false,
			'replaceParagraphCode' => false,
		];

		return $settings;
	}

	/**
	 * Determine allowed blocks.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array|bool
	 */
	protected static function get_allowed_blocks( $field ) {
		if ( empty( $field['allowed_blocks'] ) ) {
			return true;
		}

		return array_values( array_unique( $field['allowed_blocks'] ) );
	}

	/**
	 * Build the editor settings array passed to WordPress.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	protected static function get_editor_settings() {
		global $editor_styles, $post;

		wp_add_inline_script(
			'wp-blocks',
			sprintf( 'wp.blocks.setCategories( %s );', wp_json_encode( get_block_categories( $post ) ) )
		);

		$styles = [];
		if ( ! empty( $editor_styles ) && current_theme_supports( 'editor-styles' ) ) {
			foreach ( $editor_styles as $style ) {
				if ( preg_match( '~^(https?:)?//~', $style ) ) {
					$response = wp_remote_get( $style );
					if ( ! is_wp_error( $response ) ) {
						$styles[] = [
							'css' => wp_remote_retrieve_body( $response ),
						];
					}
				} else {
					$file = get_theme_file_path( $style );
					if ( is_file( $file ) ) {
						$styles[] = [
							'css'     => file_get_contents( $file ),
							'baseURL' => get_theme_file_uri( $style ),
						];
					}
				}
			}
		}

		$image_size_names = apply_filters(
			'image_size_names_choose',
			[
				'thumbnail' => __( 'Thumbnail' ),
				'medium'    => __( 'Medium' ),
				'large'     => __( 'Large' ),
				'full'      => __( 'Full Size' ),
			]
		);

		$available_image_sizes = [];
		foreach ( $image_size_names as $image_size_slug => $image_size_name ) {
			$available_image_sizes[] = [
				'slug' => $image_size_slug,
				'name' => $image_size_name,
			];
		}

		$allowed_block_types = true;
		if ( has_filter( 'allowed_block_types_all' ) ) {
			$allowed_block_types = apply_filters( 'allowed_block_types_all', $allowed_block_types, $post );
		} else {
			$allowed_block_types = apply_filters( 'allowed_block_types', $allowed_block_types, $post );
		}

		$editor_settings = [
			'enableUpload'           => true,
			'enableLibrary'          => true,
			'alignWide'              => get_theme_support( 'align-wide' ),
			'disableCustomColors'    => get_theme_support( 'disable-custom-colors' ),
			'disableCustomFontSizes' => get_theme_support( 'disable-custom-font-sizes' ),
			'disablePostFormats'     => ! current_theme_supports( 'post-formats' ),
			'titlePlaceholder'       => apply_filters( 'enter_title_here', __( 'Add title', 'meta-box' ), $post ),
			'bodyPlaceholder'        => apply_filters( 'write_your_story', __( 'Start writing or type / to choose a block', 'meta-box' ), $post ),
			'isRTL'                  => is_rtl(),
			'autosaveInterval'       => defined( 'AUTOSAVE_INTERVAL' ) ? AUTOSAVE_INTERVAL : 0,
			'maxUploadFileSize'      => wp_max_upload_size() ? wp_max_upload_size() : 0,
			'styles'                 => $styles,
			'imageSizes'             => $available_image_sizes,
			'richEditingEnabled'     => user_can_richedit(),
			'codeEditingEnabled'     => true,
			'canLockBlocks'          => true,
			'allowedBlockTypes'      => $allowed_block_types,
			'supportsTemplateMode'   => current_theme_supports( 'block-templates' ),
			'__experimentalCanUserUseUnfilteredHTML' => false,
			'__experimentalBlockPatterns'            => [],
			'__experimentalBlockPatternCategories'   => [],
		];

		$color_palette = current( (array) get_theme_support( 'editor-color-palette' ) );
		if ( false !== $color_palette ) {
			$editor_settings['colors'] = $color_palette;
		}

		$font_sizes = current( (array) get_theme_support( 'editor-font-sizes' ) );
		if ( false !== $font_sizes ) {
			$editor_settings['fontSizes'] = $font_sizes;
		}

		$gradient_presets = current( (array) get_theme_support( 'editor-gradient-presets' ) );
		if ( false !== $gradient_presets ) {
			$editor_settings['gradients'] = $gradient_presets;
		}

		if ( class_exists( 'WP_Block_Editor_Context' ) && function_exists( 'get_block_editor_settings' ) ) {
			$block_editor_context = new \WP_Block_Editor_Context(
				[
					'post' => $post,
				]
			);

			return get_block_editor_settings( $editor_settings, $block_editor_context );
		}

		return $editor_settings;
	}
}


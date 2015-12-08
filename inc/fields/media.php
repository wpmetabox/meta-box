<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Media_Field' ) )
{
	class RWMB_Media_Field extends RWMB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_media();
			wp_enqueue_style( 'rwmb-media', RWMB_CSS_URL . 'media.css', array(), RWMB_VER );
			wp_enqueue_script( 'rwmb-media', RWMB_JS_URL . 'media.js', array( 'jquery-ui-sortable', 'underscore', 'backbone' ), RWMB_VER, true );
		}

		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions()
		{
			// Print attachment templates
			add_action( 'print_media_templates', array( __CLASS__, 'print_templates' ) );
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			$meta = (array) $meta;
			$meta = implode( ',', $meta );
			$html = sprintf(
				'<input type="hidden" name="%s" value="%s" class="rwmb-media">
				<div class="rwmb-media-view"  data-mime-type="%s" data-max-files="%s" data-force-delete="%s"></div>',
				$field['field_name'],
				esc_attr( $meta ),
				$field['mime_type'],
				$field['max_file_uploads'],
				$field['force_delete'] ? 'true' : 'false'
			);

			return $html;
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'std'              => array(),
				'mime_type'        => '',
				'max_file_uploads' => 0,
				'force_delete'     => false,
			) );

			$field['multiple'] = false;

			return $field;
		}

		/**
		 * Get meta value
		 *
		 * @param int   $post_id
		 * @param bool  $saved
		 * @param array $field
		 *
		 * @return mixed
		 */
		static function meta( $post_id, $saved, $field )
		{
			$field['multiple'] = true;
			return parent::meta( $post_id, $saved, $field );
		}

		/**
		 * Get field value
		 * It's the combination of new (uploaded) images and saved images
		 *
		 * @param array $new
		 * @param array $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return array|mixed
		 */
		static function value( $new, $old, $post_id, $field )
		{
			if ( $field['clone'] )
			{
				foreach ( $new as &$value )
				{
					$value = wp_parse_id_list( $value );
				}
			}
			else
			{
				$new = wp_parse_id_list( $new );
			}
			return $new;
		}

		/**
		 * Save meta value
		 *
		 * @param $new
		 * @param $old
		 * @param $post_id
		 * @param $field
		 */
		static function save( $new, $old, $post_id, $field )
		{
			$name = $field['id'];

			delete_post_meta( $post_id, $name );

			if ( '' === $new || array() === $new )
			{
				return;
			}

			// If field is cloneable, value is saved as a single entry in the database
			if ( $field['clone'] )
			{
				$new = (array) $new;
				$new = array_filter( $new );
				update_post_meta( $post_id, $name, $new );
				return;
			}
			else
			{
				foreach ( $new as $new_value )
				{
					add_post_meta( $post_id, $name, $new_value, false );
				}
				return;
			}
		}

		/**
		 * Template for media item
		 * @return void
		 */
		static function print_templates()
		{
			$i18n_add            = apply_filters( 'rwmb_media_add_string', _x( '+ Add Media', 'media', 'meta-box' ) );
			$i18n_remove         = apply_filters( 'rwmb_media_remove_string', _x( 'Remove', 'media', 'meta-box' ) );
			$i18n_edit           = apply_filters( 'rwmb_media_edit_string', _x( 'Edit', 'media', 'meta-box' ) );
			$i18n_view           = apply_filters( 'rwmb_media_view_string', _x( 'View', 'media', 'meta-box' ) );
			$i18n_single_files   = apply_filters( 'rwmb_media_single_files_string', _x( ' file', 'media', 'meta-box' ) );
			$i18n_multiple_files = apply_filters( 'rwmb_media_multiple_files_string', _x( ' files', 'media', 'meta-box' ) );
			$i18n_title          = _x( 'No Title', 'media', 'meta-box' );
			?>
			<script id="tmpl-rwmb-media-item" type="text/html">
				<div class="rwmb-media-preview">
					<div class="rwmb-media-content">
						<div class="centered">
							<# if ( 'image' === data.type && data.sizes ) { #>
								<# if ( data.sizes.thumbnail ) { #>
									<img src="{{{ data.sizes.thumbnail.url }}}">
								<# } else { #>
									<img src="{{{ data.sizes.full.url }}}">
								<# } #>
							<# } else { #>
								<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
									<img src="{{ data.image.src }}" />
								<# } else { #>
									<img src="{{ data.icon }}" />
								<# } #>
							<# } #>
						</div>
					</div>
				</div>
				<div class="rwmb-media-info">
					<h4>
						<a href="{{{ data.url }}}" target="_blank" title="<?php echo esc_attr( $i18n_view ); ?>">
							<# if( data.title ) { #> {{{ data.title }}}
								<# } else { #> <?php echo esc_attr( $i18n_title ); ?>
							<# } #>
						</a>
					</h4>
					<p>{{{ data.mime }}}</p>
					<p>
						<a class="rwmb-edit-media" title="<?php echo esc_attr( $i18n_edit ); ?>" href="{{{ data.editLink }}}" target="_blank">
							<span class="dashicons dashicons-edit"></span><?php echo esc_attr( $i18n_edit ); ?>
						</a>
						<a href="#" class="rwmb-remove-media" title="<?php echo esc_attr( $i18n_remove ); ?>">
							<span class="dashicons dashicons-no-alt"></span><?php echo esc_attr( $i18n_remove ); ?>
						</a>
					</p>
				</div>
			</script>

			<script id="tmpl-rwmb-image-item" type="text/html">
				<div class="rwmb-media-preview">
					<div class="rwmb-media-content">
						<div class="centered">
							<# if ( 'image' === data.type && data.sizes ) { #>
								<# if ( data.sizes.thumbnail ) { #>
									<img src="{{{ data.sizes.thumbnail.url }}}">
								<# } else { #>
									<img src="{{{ data.sizes.full.url }}}">
								<# } #>
							<# } else { #>
								<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
									<img src="{{ data.image.src }}" />
								<# } else { #>
									<img src="{{ data.icon }}" />
								<# } #>
							<# } #>
						</div>
					</div>
				</div>
				<div class="rwmb-overlay"></div>
				<div class="rwmb-media-bar">
					<a class="rwmb-edit-media" title="<?php echo esc_attr( $i18n_edit ); ?>" href="{{{ data.editLink }}}" target="_blank">
						<span class="dashicons dashicons-edit"></span>
					</a>
					<a href="#" class="rwmb-remove-media" title="<?php echo esc_attr( $i18n_remove ); ?>">
						<span class="dashicons dashicons-no-alt"></span>
					</a>
				</div>
			</script>

			<script id="tmpl-rwmb-add-media" type="text/html">
				<?php echo $i18n_add; ?>
			</script>

			<script id="tmpl-rwmb-media-status" type="text/html">
				<# if ( data.maxFiles > 0 ) { #>
					{{{ data.items }}}/{{{ data.maxFiles }}}
					<# if ( data.items > 1 || data.items < 1 ) { #>  <?php echo $i18n_multiple_files; ?> <# } else {#> <?php echo $i18n_single_files; ?> <# } #>
				<# } #>
			</script>
			<?php
		}
	}
}

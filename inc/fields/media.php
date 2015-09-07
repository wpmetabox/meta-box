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
			$i18n_add   = apply_filters( 'rwmb_media_add_string', _x( 'Add Media', 'media', 'meta-box' ) );
			$meta =  wp_json_encode( (array) $meta );

			$html .= sprintf(
				'<div class="rwmb-media"  data-name="%s" data-values="%s" data-mime-type="%s" data-multiple="%s">
					<ul class="rwmb-media-list"></ul>
					<a href="#" class="rwmb-add-media button">
						<span class="dashicons dashicons-plus rwmb-icon"></span>%s
					</a>
				</div>',
				$field['field_name'],
				esc_attr( $meta ),
				$field['mime_type'],
				$field['multiple'] ? 'true' : 'false',
				$i18n_add				
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
			$field             = wp_parse_args( $field, array(
				'std'              => array(),
				'multiple'         => true,
				'mime_type'        => '',
			) );
			
			if ( ! $field['clone'] && $field['multiple'] )
				$field['field_name'] .= '[]';

			return $field;
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
				foreach ( $new as $k => $v )
				{
					if ( '' === $v )
						unset( $new[$k] );
				}
				update_post_meta( $post_id, $name, $new );
				return;
			}

			// If field is multiple, value is saved as multiple entries in the database (WordPress behaviour)
			if ( $field['multiple'] )
			{
				foreach ( $new as $new_value )
				{
					add_post_meta( $post_id, $name, $new_value, false );
				}
				return;
			}

			// Default: just update post meta
			update_post_meta( $post_id, $name, $new );
		}
		
		static function print_templates()
		{
			$i18n_remove   = apply_filters( 'rwmb_attachment_remove_string', _x( 'Remove', 'attachment', 'meta-box' ) );
			?>
			<script id="tmpl-rwmb-media-item" type="text/html">
				<div class="rwmb-media-preview">
					<div class="rwmb-media-content" >
						<div class="centered">
							<# if( 'image' === data.attachment.type && data.attachment.sizes ){ #>
							
								<# if ( data.attachment.sizes.thumbnail ) { #>
									<img src="{{{ data.attachment.sizes.thumbnail.url }}}">
								<# } else { #>
									<img src="{{{ data.attachment.sizes.full.url }}}">
								<# } #>
							<# } else { #>
								<# if ( data.attachment.image && data.attachment.image.src && data.attachment.image.src !== data.attachment.icon ) { #>
									<img src="{{ data.attachment.image.src }}" />
								<# } else { #>
									<img src="{{ data.attachment.icon }}" />
								<# } #>
							<# } #>	
						</div>				
					</div>					
				</div>
				<a href="#" class="rwmb-remove-media">
					<span class="dashicons dashicons-no"></span>
				</a>
				<input type="hidden" name="{{{ data.name }}}" value="{{{ data.attachment.id }}}" />
			</script>
			<?php
		}
	}
}

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
			$html .= sprintf(
				'<input type="hidden" name="%s" value="%s" class="rwmb-media" />
				<div class="rwmb-media-view"  data-mime-type="%s" data-max-files="%s"></div>',
				$field['field_name'],
				esc_attr( $meta ),
				$field['mime_type'],
				$field['max_file_uploads'] 
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
				'mime_type'        => '',
				'max_file_uploads' => 0,
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
			if( $field['clone'] )
			{
				foreach( $new as &$value )
				{
					$value = explode( ',', $value );
				}
			}
			else
			{
				$new = explode( ',', $new );	
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
		
		static function print_templates()
		{
			$i18n_remove   = apply_filters( 'rwmb_data_remove_string', _x( 'Remove', 'data', 'meta-box' ) );
			$i18n_add   = apply_filters( 'rwmb_media_add_string', _x( 'Add Media', 'media', 'meta-box' ) );
			$i18n_edit   = apply_filters( 'rwmb_file_edit_string', _x( 'Edit', 'file upload', 'meta-box' ) );
			?>
			<script id="tmpl-rwmb-media-item" type="text/html">
				<div class="rwmb-icon">
					<img src="<# if ( data.type == 'image' ){ #>{{{ data.sizes.thumbnail.url }}}<# } else { #>{{{ data.icon }}}<# } #>">
				</div>
				<div class="rwmb-info">
					<a href="{{{ data.url }}}" target="_blank">{{{ data.title }}}</a>
					<p>{{{ data.mime }}}</p>
					<a title="<?php echo esc_attr( $i18n_edit ); ?>" href="{{{ data.editLink }}}" target="_blank"><?php echo esc_html( $i18n_edit ); ?></a> |
					<a title="<?php echo esc_attr( $i18n_delete ); ?>" class="rwmb-remove-media" href="#" ><?php echo esc_html( $i18n_remove ); ?></a>
				</div>
			</script>
            
            <script id="tmpl-rwmb-media-list" type="text/html"> 
				<ul class="rwmb-media-list"></ul>
				<a href="#" class="rwmb-add-media button">
					<span class="dashicons dashicons-plus rwmb-icon"></span><?php echo $i18n_add; ?>
				</a>
			</script>
			<?php
		}
	}
}

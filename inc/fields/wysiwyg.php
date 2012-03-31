<?php
// Prevent loading this file directly - Busted!
if( ! class_exists('WP') )
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'RWMB_Wysiwyg_Field' ) )
{
	class RWMB_Wysiwyg_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return	void
		 */
		static function admin_enqueue_scripts( )
		{
			wp_enqueue_style( 'rwmb-meta-box-wysiwyg', RWMB_CSS_URL.'wysiwyg.css', RWMB_VER );
		}

		/**
		 * Add field actions
		 *
		 * @return	void
		 */
		static function add_actions( )
		{
			// Add TinyMCE script for WP version < 3.3
			global $wp_version;

			if ( version_compare( $wp_version, '3.2.1' ) < 1 )
			{
				add_action( 'admin_print_footer-post.php', 'wp_tiny_mce', 25 );
				add_action( 'admin_print_footer-post-new.php', 'wp_tiny_mce', 25 );
			}
		}

		/**
		 * Change field value on save
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return string
		 */
		static function value( $new, $old, $post_id, $field )
		{
			return wpautop( $new );
		}

		/**
		 * Get field HTML
		 *
		 * @param string $html
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $html, $meta, $field )
		{
			global $wp_version;
			$name = "name='{$field['field_name']}'";

			if ( version_compare( $wp_version, '3.2.1' ) < 1 )
			{
				return "<textarea class='rwmb-wysiwyg theEditor large-text' {$name} id='{$field['id']}' cols='60' rows='10'>$meta</textarea>";
			}
			else
			{
				// Apply filter to wp_editor() settings
				$editor_settings = apply_filters( 'rwmb_wysiwyg_settings', array( 'editor_class' => 'rwmb-wysiwyg' ), 10, 1 );
				// Using output buffering because wp_editor() echos directly
				ob_start( );
				// Use new wp_editor() since WP 3.3
				wp_editor( $meta, $field['id'], $editor_settings );

				return ob_get_clean( );
			}
		}
	}
}
<?php

if ( !class_exists( 'RWMB_Wysiwyg_Field' ) ) {

	class RWMB_Wysiwyg_Field {

		/**
		 * Enqueue scripts and styles
		 */
		static function admin_print_styles( ) {
			wp_enqueue_style( 'rwmb-meta-box-wysiwyg', RWMB_CSS_URL . 'wysiwyg.css', RWMB_VER );
		}

		/**
		 * Add field actions
		 */
		static function add_actions( ) {
			// Add TinyMCE script for WP version < 3.3
			global $wp_version;

			if ( version_compare( $wp_version, '3.2.1' ) < 1 ) {
				add_action( 'admin_print_footer-post.php', 'wp_tiny_mce', 25 );
				add_action( 'admin_print_footer-post-new.php', 'wp_tiny_mce', 25 );
			}
		}

		/**
		 * Change field value on save
		 * @param $new
		 * @param $old
		 * @param $post_id
		 * @param $field
		 * @return string
		 */
		static function value( $new, $old, $post_id, $field ) {
			return wpautop( $new );
		}

		/**
		 * Get field end HTML
		 * @param $html
		 * @param $meta
		 * @param $field
		 * @return string
		 */
		static function html( $html, $meta, $field ) {
			global $wp_version;

			if ( version_compare( $wp_version, '3.2.1' ) < 1 ) {

				return "<textarea class='rwmb-wysiwyg theEditor large-text' name='{$field['id']}' id='{$field['id']}' cols='60' rows='10'>$meta</textarea>";
			} else {
				// Use new wp_editor() since WP 3.3

				// Using output buffering because wp_editor() echos directly
				ob_start( );
				wp_editor( $meta, $field['id'], array( 'editor_class' => 'rwmb-wysiwyg' ) );
				return ob_get_clean( );
			}
		}
	}
}
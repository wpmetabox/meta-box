<?php

if ( !class_exists( 'RWMB_Wysiwyg_Field' ) ) {

	class RWMB_Wysiwyg_Field {

		/**
		 * Enqueue scripts and styles
		 */
		static function admin_print_styles( ) {
			wp_enqueue_style( 'rwmb-meta-box-wysiwyg', RWMB_CSS . 'wysiwyg.css', RWMB_VER );
		}

		/**
		 * Add field actions
		 */
		static function add_actions( ) {
			// Add TinyMCE script
			add_action( 'admin_print_footer-post.php', 'wp_tiny_mce', 25 );
			add_action( 'admin_print_footer-post-new.php', 'wp_tiny_mce', 25 );

			// Change field value on save
			add_action( 'rwmb_wysiwyg_value', array( __CLASS__, 'value' ), 1, 3 );
		}

		/**
		 * Change field value on save
		 * @param $new
		 * @param $field
		 * @param $old
		 * @return string
		 */
		static function value( $new, $field, $old ) {
			return wpautop( $new );
		}

		/**
		 * Get field end HTML
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			return "<textarea class='rwmb-wysiwyg theEditor large-text' name='{$field['id']}' id='{$field['id']}' cols='60' rows='10'>$meta</textarea>";
		}
	}
}
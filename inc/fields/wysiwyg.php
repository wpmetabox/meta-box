<?php

if ( !class_exists( 'RW_Meta_Box_Wysiwyg_Field' ) ) {

	class RW_Meta_Box_Wysiwyg_Field {

		/**
		 * Enqueue scripts and styles for select field
		 */
		static function admin_print_styles( ) {
			wp_enqueue_style( 'rw-meta-box-wysiwyg', RW_META_BOX_CSS . 'wysiwyg.css', RW_META_BOX_VER );
		}

		/**
		 * Add actions for WYSIWYG field
		 */
		static function add_actions( ) {
			add_action( 'admin_print_footer-post.php', 'wp_tiny_mce', 25 );
			add_action( 'admin_print_footer-post-new.php', 'wp_tiny_mce', 25 );
		}

		/**
		 * Show HTML markup for checkbox field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			return "<textarea class='rw-wysiwyg theEditor large-text' name='{$field['id']}' id='{$field['id']}' cols='60' rows='10'>$meta</textarea>";
		}

		/**
		 * Save WYSIWYG field
		 * @param $post_id
		 * @param $field
		 * @param $old
		 * @param $new
		 */
		static function save( $post_id, $field, $old, $new ) {
			$new = wpautop( $new );
			RW_Meta_Box::save_field( $post_id, $field, $old, $new );
		}
	}
}
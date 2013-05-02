<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "text" field is loaded
require_once RWMB_FIELDS_DIR . 'url.php';

if ( ! class_exists( 'RWMB_OEmbed_Field' ) )
{
	class RWMB_OEmbed_Field extends RWMB_URL_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_script( 'rwmb-oembed', RWMB_JS_URL . 'oembed.js', array(  ), RWMB_VER, true );
			//wp_enqueue_style( 'rwmb-oembed', RWMB_CSS_URL . 'oembed.css', array(  ), RWMB_VER );
			wp_localize_script( 'rwmb-oembed', 'RWMB_OEmbed', array( 'url' => RWMB_URL ) );
		}

		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions()
		{
			// Attach images via Ajax
			add_action( 'wp_ajax_rwmb_get_embed', array( __CLASS__, 'wp_ajax_get_embed' ) );
		}

		/**
		 * Ajax callback for returning oEmbed HTML
		 *
		 * @return void
		 */
		static function wp_ajax_get_embed()
		{
			global $post;
			$url = isset( $_POST['oembed_url'] ) ? $_POST['oembed_url'] : 0;
			$post_id = is_numeric( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
			if ( isset( $_REQUEST['post_id'] ) )
				$post = get_post( $_REQUEST['post_id'] );
			$embed = self::get_embed( $url );
			RW_Meta_Box::ajax_response( $embed, 'success' );
			exit;
		}

		/***
		* Get embed html from url
		* @param 	string $url
		* $return 	string
		*/

		static function get_embed( $url )
		{
				
			$embed = wp_oembed_get( esc_url( $url ) );

			if( $embed )
			{
				return $embed;
			}
			else
			{
				return  'Embed not available.';
			}

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
			return sprintf(
				'<input type="url" class="rwmb-oembed" name="%s" id="%s" value="%s" size="%s" />
				<span class="spinner" style="display: none;"></span>
				<a href="#" class="show-embed button-secondary">Show embed</a>
				<div class="embed-code"> %s </div>',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['size'],
				self::get_embed( $meta )
			);
		}
	}
}

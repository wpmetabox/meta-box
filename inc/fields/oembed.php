<?php
/**
 * oEmbed field class.
 */
class RWMB_OEmbed_Field extends RWMB_URL_Field
{
	/**
	 * Enqueue scripts and styles
	 */
	static function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'rwmb-oembed', RWMB_CSS_URL . 'oembed.css' );
		wp_enqueue_script( 'rwmb-oembed', RWMB_JS_URL . 'oembed.js', array(), RWMB_VER, true );
	}

	/**
	 * Add actions
	 */
	static function add_actions()
	{
		add_action( 'wp_ajax_rwmb_get_embed', array( __CLASS__, 'wp_ajax_get_embed' ) );
	}

	/**
	 * Ajax callback for returning oEmbed HTML
	 */
	static function wp_ajax_get_embed()
	{
		$url = (string) filter_input( INPUT_POST, 'url', FILTER_SANITIZE_URL );
		wp_send_json_success( self::get_embed( $url ) );
	}

	/**
	 * Get embed html from url
	 *
	 * @param string $url
	 * @return string
	 */
	static function get_embed( $url )
	{
		// Try oembed first
		$embed = wp_oembed_get( $url );

		// If no oembed provides found, try WordPress auto embed
		if ( ! $embed )
		{
			$embed = $GLOBALS['wp_embed']->shortcode( array(), $url );
		}

		return $embed ? $embed : __( 'Embed HTML not available.', 'meta-box' );
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 * @return string
	 */
	static function html( $meta, $field )
	{
		$attributes = self::get_attributes( $field, $meta );
		return sprintf(
			'<input %s>
			<a href="#" class="show-embed button">%s</a>
			<span class="spinner"></span>
			<div class="embed-code">%s</div>',
			self::render_attributes( $attributes ),
			__( 'Preview', 'meta-box' ),
			$meta ? self::get_embed( $meta ) : ''
		);
	}

	/**
	 * Output the field value
	 * Display embed media
	 *
	 * @param  array    $field   Field parameters
	 * @param  array    $args    Additional arguments. Not used for these fields.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Field value
	 */
	static function the_value( $field, $args = array(), $post_id = null )
	{
		$value = self::get_value( $field, $args, $post_id );
		if ( $field['clone'] )
		{
			$output = '<ul>';
			foreach ( $value as $subvalue )
			{
				$output .= '<li>' . self::get_embed( $subvalue ) . '</li>';
			}
			$output .= '</ul>';
		}
		else
		{
			$output = self::get_embed( $value );
		}
		return $output;
	}
}

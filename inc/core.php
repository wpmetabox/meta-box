<?php
/**
 * The plugin core class which initialize plugin's code.
 *
 * @package Meta Box
 */

/**
 * The Meta Box core class.
 *
 * @package Meta Box
 */
class RWMB_Core {
	/**
	 * Initialization.
	 */
	public function init() {
		load_plugin_textdomain( 'meta-box', false, plugin_basename( RWMB_DIR ) . '/languages/' );

		add_filter( 'plugin_action_links_meta-box/meta-box.php', array( $this, 'plugin_links' ) );
		add_action( 'init', array( $this, 'register_meta_boxes' ) );
		add_action( 'edit_page_form', array( $this, 'fix_page_template' ) );
	}

	/**
	 * Add links to Documentation and Extensions in plugin's list of action links.
	 *
	 * @since 4.3.11
	 * @param array $links Array of plugin links.
	 * @return array
	 */
	public function plugin_links( $links ) {
		$links[] = '<a href="https://metabox.io/docs/">' . esc_html__( 'Documentation', 'meta-box' ) . '</a>';
		$links[] = '<a href="https://metabox.io/plugins/">' . esc_html__( 'Extensions', 'meta-box' ) . '</a>';
		return $links;
	}

	/**
	 * Register meta boxes.
	 * Advantages:
	 * - prevents incorrect hook.
	 * - no need to check for class existences.
	 */
	public function register_meta_boxes() {
		$configs    = apply_filters( 'rwmb_meta_boxes', array() );
		$meta_boxes = rwmb_get_registry( 'meta_box' );

		foreach ( $configs as $config ) {
			$meta_box = rwmb_get_meta_box( $config );
			$meta_boxes->add( $meta_box );
			$meta_box->register_fields();
		}
	}

	/**
	 * WordPress will prevent post data saving if a page template has been selected that does not exist.
	 * This is especially a problem when switching to our theme, and old page templates are in the post data.
	 * Unset the page template if the page does not exist to allow the post to save.
	 *
	 * @param WP_Post $post Post object.
	 * @since 4.3.10
	 */
	public function fix_page_template( WP_Post $post ) {
		$template       = get_post_meta( $post->ID, '_wp_page_template', true );
		$page_templates = wp_get_theme()->get_page_templates();

		// If the template doesn't exists, remove the data to allow WordPress to save.
		if ( ! isset( $page_templates[ $template ] ) ) {
			delete_post_meta( $post->ID, '_wp_page_template' );
		}
	}

	/**
	 * Get registered meta boxes via a filter.
	 *
	 * @deprecated No longer used. Keep for backward-compatibility with extensions.
	 *
	 * @return array
	 */
	public static function get_meta_boxes() {
		$meta_boxes = rwmb_get_registry( 'meta_box' )->all();
		return wp_list_pluck( $meta_boxes, 'meta_box' );
	}
}

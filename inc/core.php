<?php
class RWMB_Core {
	public function init() {
		add_action( 'init', [ $this, 'load_textdomain' ] );

		add_filter( 'plugin_action_links_meta-box/meta-box.php', [ $this, 'plugin_links' ], 20 );

		// Uses priority 20 to support custom port types registered using the default priority.
		add_action( 'init', [ $this, 'register_meta_boxes' ], 20 );
		add_action( 'edit_page_form', [ $this, 'fix_page_template' ] );
		$this->add_context_hooks();
	}

	public function load_textdomain() {
		unload_textdomain( 'meta-box' );
		load_plugin_textdomain( 'meta-box', false, plugin_basename( RWMB_DIR ) . '/languages/' );
	}

	public function plugin_links( array $links ) : array {
		$links[] = '<a href="https://docs.metabox.io">' . esc_html__( 'Docs', 'meta-box' ) . '</a>';
		return $links;
	}

	public function register_meta_boxes() {
		$configs  = apply_filters( 'rwmb_meta_boxes', [] );
		$registry = rwmb_get_registry( 'meta_box' );

		foreach ( $configs as $config ) {
			if ( ! is_array( $config ) || empty( $config ) ) {
				continue;
			}
			$meta_box = $registry->make( $config );
			$meta_box->register_fields();
		}
	}

	/**
	 * WordPress will prevent post data saving if a page template has been selected that does not exist.
	 * This is especially a problem when switching themes, and old page templates are in the post data.
	 * Unset the page template if the page does not exist to allow the post to save.
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
	 * @deprecated No longer used. Keep for backward-compatibility with extensions.
	 */
	public static function get_meta_boxes() : array {
		$meta_boxes = rwmb_get_registry( 'meta_box' )->all();
		return wp_list_pluck( $meta_boxes, 'meta_box' );
	}

	public function add_context_hooks() {
		$hooks = [
			'edit_form_top',
			'edit_form_after_title',
			'edit_form_after_editor',
			'edit_form_before_permalink',
		];

		foreach ( $hooks as $hook ) {
			add_action( $hook, [ $this, 'render_meta_boxes_for_context' ] );
		}
	}

	public function render_meta_boxes_for_context( $post ) {
		$hook    = current_filter();
		$context = 'edit_form_top' === $hook ? 'form_top' : substr( $hook, 10 );
		do_meta_boxes( null, $context, $post );
	}
}

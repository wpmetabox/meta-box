<?php
/**
 * The plugin core class which initialize plugin's code.
 * @package Meta Box
 */

/**
 * The Meta Box core class.
 * @package Meta Box
 */
class RWMB_Core
{
	/**
	 * Stores all registered meta boxes
	 * @var array
	 */
	private static $meta_boxes = null;

	/**
	 * Register hooks.
	 */
	public function __construct()
	{
		$plugin = 'meta-box/meta-box.php';
		add_filter( "plugin_action_links_$plugin", array( $this, 'plugin_links' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_meta_boxes' ) );
        add_action( 'init', array( $this, 'register_wpml_hooks' ) );
		add_action( 'edit_page_form', array( $this, 'fix_page_template' ) );
	}

	/**
	 * Add links to Documentation and Extensions in plugin's list of action links.
	 *
	 * @since 4.3.11
	 * @param array $links Array of action links
	 * @return array
	 */
	public function plugin_links( $links )
	{
		$links[] = '<a href="https://metabox.io/docs/">' . __( 'Documentation', 'meta-box' ) . '</a>';
		$links[] = '<a href="https://metabox.io/plugins/">' . __( 'Extensions', 'meta-box' ) . '</a>';
		return $links;
	}

	/**
	 * Load plugin translation.
	 */
	public function load_textdomain()
	{
		load_plugin_textdomain( 'meta-box', false, plugin_basename( RWMB_DIR ) . '/lang/' );
	}

	/**
	 * Register meta boxes.
	 * Advantages:
	 * - prevents incorrect hook.
	 * - no need to check for class existences.
	 */
	public function register_meta_boxes()
	{
		$meta_boxes = self::get_meta_boxes();
		foreach ( $meta_boxes as $meta_box )
		{
			new RW_Meta_Box( $meta_box );
		}
	}

	/**
	 * Get registered meta boxes via a filter.
	 * Advantages:
	 * - prevents duplicated global variables.
	 * - allows users to remove/hide registered meta boxes.
	 */
	public static function get_meta_boxes()
	{
		if ( null === self::$meta_boxes )
		{
			self::$meta_boxes = apply_filters( 'rwmb_meta_boxes', array() );
			self::$meta_boxes = empty( self::$meta_boxes ) || ! is_array( self::$meta_boxes ) ? array() : self::$meta_boxes;
		}
		return self::$meta_boxes;
	}

    /**
     * Get all registered fields.
     *
     * @return array
     */
    public static function get_fields() {
        $fields = array();

        foreach ( self::$meta_boxes as $meta_box ) {
            foreach ( $meta_box['fields'] as $field ) {
                if ( ! empty( $field['id'] ) ) {
                    $fields[ $field['id'] ] = $field;
                }
            }
        }

        return $fields;
    }

	/**
	 * WordPress will prevent post data saving if a page template has been selected that does not exist
	 * This is especially a problem when switching to our theme, and old page templates are in the post data
	 * Unset the page template if the page does not exist to allow the post to save
	 *
	 * @param WP_Post $post
	 * @since 4.3.10
	 */
	public function fix_page_template( WP_Post $post )
	{
		$template       = get_post_meta( $post->ID, '_wp_page_template', true );
		$page_templates = wp_get_theme()->get_page_templates();

		// If the template doesn't exists, remove the data to allow WordPress to save
		if ( ! isset( $page_templates[$template] ) )
		{
			delete_post_meta( $post->ID, '_wp_page_template' );
		}
	}

    /**
     * Register wpml compatibility hooks
     */
    public function register_wpml_hooks() {
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
            new RWMB_WPML;
        }
	}
}
<?php
/**
 * Handle common actions for the plugin: load text domain, add plugin links.
 *
 * @package Meta Box
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Common' ) )
{
	/**
	 * Class that handle actions for the plugin.
	 */
	class RWMB_Common
	{
		/**
		 * Add actions when class is loaded.
		 * @return void
		 */
		public static function load()
		{
			add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );

			$plugin = 'meta-box/meta-box.php';
			add_filter( "plugin_action_links_$plugin", array( __CLASS__, 'plugin_links' ) );
		}

		/**
		 * Load plugin translation.
		 * @return void
		 */
		public static function load_textdomain()
		{
			load_plugin_textdomain( 'meta-box', false, plugin_basename( RWMB_DIR ) . '/lang/' );
		}

		/**
		 * Add links to Documentation and Extensions in plugin's list of action links.
		 *
		 * @since 4.3.11
		 * @param array $links Array of action links
		 * @return array
		 */
		public static function plugin_links( $links )
		{
			$links[] = '<a href="http://metabox.io/docs/">' . __( 'Documentation', 'meta-box' ) . '</a>';
			$links[] = '<a href="http://metabox.io/plugins/">' . __( 'Extensions', 'meta-box' ) . '</a>';
			return $links;
		}
	}

	RWMB_Common::load();
}

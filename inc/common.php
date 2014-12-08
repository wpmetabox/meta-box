<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Common' ) )
{
	/**
	 * Common functions for the plugin
	 * Independent from meta box/field classes
	 */
	class RWMB_Common
	{
		/**
		 * Do actions when class is loaded
		 *
		 * @return void
		 */
		public static function on_load()
		{
			self::load_textdomain();

			$plugin = 'meta-box/meta-box.php';
			add_filter( "plugin_action_links_$plugin", array( __CLASS__, 'plugin_links' ) );
		}

		/**
		 * Load plugin translation
		 *
		 * @return void
		 */
		public static function load_textdomain()
		{
			// l18n translation files
			$locale = get_locale();
			$dir    = trailingslashit( RWMB_DIR . 'lang' );
			$mofile = "{$dir}{$locale}.mo";

			// In themes/plugins/mu-plugins directory
			load_textdomain( 'meta-box', $mofile );
		}

		/**
		 * Add links to Documentation and Extensions in plugin's list of action links
		 *
		 * @since 4.3.11
		 *
		 * @param array $links Array of action links
		 *
		 * @return array
		 */
		public static function plugin_links( $links )
		{
			$links[] = '<a href="http://metabox.io/docs/">' . __( 'Documentation', 'meta-box' ) . '</a>';
			$links[] = '<a href="http://metabox.io/plugins/">' . __( 'Extensions', 'meta-box' ) . '</a>';
			return $links;
		}

	}

	RWMB_Common::on_load();
}

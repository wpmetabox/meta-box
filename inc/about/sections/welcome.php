<?php
/**
 * Welcome section.
 *
 * @package    Meta Box
 * @subpackage MB Custom Post Type
 */

?>
<h1>
	<?php
	// Translators: %1$s - Plugin name, %2$s - Plugin version.
	echo esc_html( sprintf( __( 'Welcome to %1$s %2$s', 'meta-box' ), $this->plugin['Name'], $this->plugin['Version'] ) );
	?>
</h1>
<div class="about-text"><?php esc_html_e( 'This plugin is a lightweight and powerful toolkit that helps you to create custom meta boxes and custom fields in WordPress fast and easy. Follow the instruction below to get started.', 'meta-box' ); ?></div>
<a target="_blank" href="<?php echo esc_url( 'https://metabox.io/?utm_source=plugin_about_page&utm_medium=badge_link&utm_campaign=meta_box_about' ); ?>" class="wp-badge"><?php echo esc_html( $this->plugin['Name'] ); ?></a>

<?php
/*
Plugin Name: Meta Box
Plugin URI: http://www.deluxeblogtips.com/meta-box-script-for-wordpress/
Description: Create meta box for editing pages in WordPress. Compatible with custom post types since WordPress 3.0. Support input types: text, textarea, checkbox, checkbox list, radio box, select, wysiwyg, file, image, date, time, color
Version: 4.0
Author: Rilwis
Author URI: http://www.deluxeblogtips.com
*/

// Script version, used to add version for scripts and styles
if ( !defined( 'RW_META_BOX_VER' ) )
	define( 'RW_META_BOX_VER', '4.0' );

// Define plugin URLs, for fast enqueuing scripts and styles
if ( !defined( 'RW_META_BOX_URL' ) )
	define( 'RW_META_BOX_URL', plugin_dir_url( __FILE__ ) );
if ( !defined( 'RW_META_BOX_JS' ) )
	define( 'RW_META_BOX_JS', trailingslashit( RW_META_BOX_URL . 'js' ) );
if ( !defined( 'RW_META_BOX_CSS' ) )
	define( 'RW_META_BOX_CSS', trailingslashit( RW_META_BOX_URL . 'css' ) );

// Plugin paths, for including files
if ( !defined( 'RW_META_BOX_PATH' ) )
	define( 'RW_META_BOX_PATH', plugin_dir_path( __FILE__ ) );
if ( !defined( 'RW_META_BOX_INC' ) )
	define( 'RW_META_BOX_INC', trailingslashit( RW_META_BOX_PATH . 'inc' ) );
if ( !defined( 'RW_META_BOX_FIELDS' ) )
	define( 'RW_META_BOX_FIELDS', trailingslashit( RW_META_BOX_INC . 'fields' ) );

// Global array of field class instances
global $rw_meta_box_field_objects;
$rw_meta_box_field_objects = array( );

// Include field classes and create field class instances
$supported_types = array( 'text', 'textarea', 'checkbox', 'checkbox_list', 'radio', 'select', 'wysiwyg', 'file', 'image', 'date', 'time', 'color' );
foreach ( $supported_types as $type ) {
	$file = RW_META_BOX_FIELDS . "{$type}.php";
	if ( !file_exists( $file ) )
		continue;

	require_once $file;
}

/**
 * Meta Box Class
 */
if ( !class_exists( 'RW_Meta_Box' ) ) {

	class RW_Meta_Box {

		var $meta_box; // Meta box information
		var $fields;   // Fields information
		var $types;    // Contains all field types of current meta box

		/**
		 * Create meta box based on given data
		 * @param array $meta_box Meta box definition
		 * @see usage.php file for details
		 */
		function __construct( $meta_box ) {
			// Run script only in admin area
			if ( !is_admin( ) )
				return;

			// Assign meta box values to local variables and add it's missed values
			$this->meta_box = self::normalize( $meta_box );
			$this->fields = &$this->meta_box['fields'];

			// List of meta box field types
			$this->types = array_unique( wp_list_pluck( $this->fields, 'type' ) );

			// Enqueue common scripts and styles
			add_action( 'admin_print_styles-post.php', array( __CLASS__, 'admin_print_styles' ) );
			add_action( 'admin_print_styles-post-new.php', array( __CLASS__, 'admin_print_styles' ) );

			foreach ( $this->types as $type ) {
				$class = self::get_class_name( $type );

				// Enqueue scripts and styles for fields
				if ( method_exists( $class, 'admin_print_styles' ) ) {
					add_action( 'admin_print_styles-post.php', array( $class, 'admin_print_styles' ) );
					add_action( 'admin_print_styles-post-new.php', array( $class, 'admin_print_styles' ) );
				}

				// Add additional actions for fields
				if ( method_exists( $class, 'add_actions' ) )
					call_user_func( array( $class, 'add_actions' ) );
			}

			// Add meta box
			add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
			add_action( 'save_post', array( &$this, 'save_post' ) );
		}

		/**
		 * Enqueue common scripts and styles
		 */
		static function admin_print_styles( ) {
			wp_enqueue_style( 'rw-meta-box', RW_META_BOX_CSS . 'style.css', RW_META_BOX_VER );
		}

		/**************************************************
			SHOW META BOX
		**************************************************/

		/**
		 * Add meta box for multiple post types
		 */
		function add_meta_boxes( ) {
			foreach ( $this->meta_box['pages'] as $page ) {
				add_meta_box( $this->meta_box['id'], $this->meta_box['title'], array( &$this, 'show' ), $page, $this->meta_box['context'], $this->meta_box['priority'] );
			}
		}

		/**
		 * Callback function to show fields in meta box
		 */
		function show( ) {
			global $post;

			wp_nonce_field( "save_meta_box-{$this->meta_box['id']}", "nonce_{$this->meta_box['id']}" );
			echo '<table class="form-table">';

			foreach ( $this->fields as $field ) {
				$meta = get_post_meta( $post->ID, $field['id'], !$field['multiple'] );
				$meta = empty( $meta ) ? $field['std'] : $meta;

				$meta = is_array( $meta ) ? array_map( 'esc_attr', $meta ) : esc_attr( $meta );

				// Call separated methods for displaying each type of field
				$begin = self::show_field_begin( $field, $meta );

				$class = self::get_class_name( $field['type'] );
				$field_html = '';
				if ( method_exists( $class, 'html' ) )
					$field_html = call_user_func( array( $class, 'html' ), $field, $meta );
				$field_html = apply_filters( "rw_meta_box_{$field['id']}_html", $field_html );

				$end = self::show_field_end( $field, $meta );

				echo "<tr>{$begin}{$field_html}{$end}</tr>";
			}
			echo '</table>';
		}

		/**
		 * Show begin HTML markup for fields
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function show_field_begin( $field, $meta ) {
			$html = <<<HTML
<th class="rw-label">
	<label for="{$field['id']}">{$field['name']}</label><br />
</th>
<td class="rw-field">
HTML;
			$html = apply_filters( "rw_meta_box_{$field['id']}_begin", $html, $field, $meta );

			return $html;
		}

		/**
		 * Show end HTML markup for fields
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function show_field_end( $field, $meta ) {
			$html = "<p class='description'>{$field['desc']}</p></td>";

			$html = apply_filters( "rw_meta_box_{$field['id']}_begin", $html );

			return $html;
		}

		/**************************************************
			SAVE META BOX
		**************************************************/

		/**
		 * Save data from meta box
		 * @param $post_id Post ID
		 * @return
		 */
		function save_post( $post_id ) {
			global $post_type;
			$post_type_object = get_post_type_object( $post_type );

			/*
			 * Check whether:
			 * - the post is autosaved
			 * - the post is a revision
			 * - current post type is supported
			 * - user has proper capability
			 */
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				|| ( !isset( $_POST['post_ID'] ) || $post_id != $_POST['post_ID'] )
				|| ( !in_array( $post_type, $this->meta_box['pages'] ) )
				|| ( !current_user_can( $post_type_object->cap->edit_post, $post_id ) )
			) {
				return $post_id;
			}

			// Verify nonce
			check_admin_referer( "save_meta_box-{$this->meta_box['id']}", "nonce_{$this->meta_box['id']}" );

			foreach ( $this->fields as $field ) {
				$name = $field['id'];
				$type = $field['type'];
				$old = get_post_meta( $post_id, $name, !$field['multiple'] );
				$new = isset( $_POST[$name] ) ? $_POST[$name] : ( $field['multiple'] ? array( ) : '' );

				// Use filter to change field value
				$new = apply_filters( "rw_meta_box_{$field['id']}_value", $new );

				// Call defined method to save meta value, if there's no methods, call common one
				$class = self::get_class_name( $field['type'] );
				if ( method_exists( $class, 'save' ) )
					call_user_func( array( $class, 'save' ), $post_id, $field, $old, $new );
				else
					self::save_field( $post_id, $field, $old, $new );
			}
		}

		/**
		 * Common functions for saving field
		 * @param $post_id
		 * @param $field
		 * @param $old
		 * @param $new
		 */
		static function save_field( $post_id, $field, $old, $new ) {
			$name = $field['id'];

			delete_post_meta( $post_id, $name );
			if ( $new === '' || $new === array( ) )
				return;

			if ( $field['multiple'] ) {
				foreach ( $new as $add_new ) {
					add_post_meta( $post_id, $name, $add_new, false );
				}
			} else {
				update_post_meta( $post_id, $name, $new );
			}
		}

		/**************************************************
			HELPER FUNCTIONS
		**************************************************/

		/**
		 * Normalize parameters for meta box
		 * @param $meta_box Meta box definition
		 * @return Normalized meta box
		 */
		static function normalize( $meta_box ) {
			// Set default values for meta box
			$meta_box = wp_parse_args( $meta_box, array(
				'context' => 'normal',
				'priority' => 'high',
				'pages' => array( 'post' )
			) );

			// Set default values for fields
			foreach ( $meta_box['fields'] as &$field ) {
				$multiple = in_array( $field['type'], array( 'checkbox_list', 'file', 'image' ) );
				$std = $multiple ? array( ) : '';
				$format = 'date' == $field['type'] ? 'yy-mm-dd' : ( 'time' == $field['type'] ? 'hh:mm' : '' );

				$field = wp_parse_args( $field, array(
					'multiple' => $multiple,
					'std' => $std,
					'desc' => '',
					'format' => $format
				) );
			}

			return $meta_box;
		}

		/**
		 * Get field class name
		 * @param string $type Field type
		 * @return string Field class name
		 */
		static function get_class_name( $type ) {
			$type = ucwords( $type );
			$class = "RW_Meta_Box_{$type}_Field";

			if ( class_exists( $class ) )
				return $class;
			else
				return false;
		}
	}
}

// Demo
include 'usage.php';

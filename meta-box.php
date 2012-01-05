<?php
/*
Plugin Name: Meta Box
Plugin URI: http://www.deluxeblogtips.com/meta-box-script-for-wordpress/
Description: Create meta box for editing pages in WordPress. Compatible with custom post types since WordPress 3.0. Support input types: text, textarea, checkbox, checkbox list, radio box, select, wysiwyg, file, image, date, time, color
Version: 4.1
Author: Rilwis
Author URI: http://www.deluxeblogtips.com
*/

// Meta Box Class
if ( ! class_exists( 'RW_Meta_Box' ) )
{
	// Script version, used to add version for scripts and styles
	define( 'RWMB_VER', '4.0.2' );

	// Define plugin URLs, for fast enqueuing scripts and styles
	if ( ! defined( 'RWMB_URL' ) )
		define( 'RWMB_URL', plugin_dir_url( __FILE__ ) );
	define( 'RWMB_JS_URL', trailingslashit( RWMB_URL . 'js' ) );
	define( 'RWMB_CSS_URL', trailingslashit( RWMB_URL . 'css' ) );

	// Plugin paths, for including files
	if ( ! defined( 'RWMB_DIR' ) )
		define( 'RWMB_DIR', plugin_dir_path( __FILE__ ) );
	define( 'RWMB_INC_DIR', trailingslashit( RWMB_DIR . 'inc' ) );
	define( 'RWMB_FIELDS_DIR', trailingslashit( RWMB_INC_DIR . 'fields' ) );

	// Plugin textdomain
	define( 'RWMB_TEXTDOMAIN', 'rwmb' );

	// Include field classes
	foreach ( glob( RWMB_FIELDS_DIR . '*.php' ) as $file )
	{
		require_once $file;
	}

	class RW_Meta_Box
	{
		/**
		 * Meta box information
		 */
		var $meta_box;

		/**
		 * Fields information
		 */
		var $fields;

		/**
		 * Contains all field types of current meta box
		 */
		var $types;

		/**
		 * Create meta box based on given data
		 *
		 * @see demo/demo.php file for details
		 *
		 * @param array $meta_box Meta box definition
		 *
		 * @return \RW_Meta_Box
		 */
		function __construct( $meta_box )
		{
			// Run script only in admin area
			if ( ! is_admin() )
				return;

			// Assign meta box values to local variables and add it's missed values
			$this->meta_box = self::normalize( $meta_box );
			$this->fields   = &$this->meta_box['fields'];

			// List of meta box field types
			$this->types = array_unique( wp_list_pluck( $this->fields, 'type' ) );

			// Load translation file
			add_action( 'admin_init', array( __CLASS__, 'load_textdomain' ) );

			// Enqueue common scripts and styles
			add_action( 'admin_print_styles-post.php', array( __CLASS__, 'admin_print_styles' ) );
			add_action( 'admin_print_styles-post-new.php', array( __CLASS__, 'admin_print_styles' ) );

			foreach ( $this->types as $type )
			{
				$class = self::get_class_name( $type );

				// Enqueue scripts and styles for fields
				if ( method_exists( $class, 'admin_print_styles' ) )
				{
					add_action( 'admin_print_styles-post.php', array( $class, 'admin_print_styles' ) );
					add_action( 'admin_print_styles-post-new.php', array( $class, 'admin_print_styles' ) );
				}

				// Add additional actions for fields
				if ( method_exists( $class, 'add_actions' ) )
					call_user_func( array( $class, 'add_actions' ) );
			}

			// Add meta box
			add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );

			// Show hidden fields
			add_action( 'dbx_post_sidebar', array( __CLASS__, 'dbx_post_sidebar' ) );

			// Save post meta
			add_action( 'save_post', array( &$this, 'save_post' ) );
		}

		/**
		 * Load plugin translation
		 *
		 * @link http://wordpress.stackexchange.com/a/33314 Translation Tutorial by the author
		 * @return void
		 */
		static function load_textdomain()
		{
			// l18n translation files
			$dir       = basename( RWMB_DIR );
			$dir       = "{$dir}/lang";
			$domain    = RWMB_TEXTDOMAIN;
			$l18n_file = "{$dir}/{$domain}-{$GLOBALS['locale']}.mo";

			// In themes/plugins/mu-plugins directory
			load_textdomain( $domain, $l18n_file );
		}

		/**
		 * Enqueue common scripts and styles
		 *
		 * @return void
		 */
		static function admin_print_styles()
		{
			wp_enqueue_style( 'rwmb', RWMB_CSS_URL . 'style.css', RWMB_VER );
		}

		/**************************************************
			SHOW META BOX
		**************************************************/

		/**
		 * Add meta box for multiple post types
		 *
		 * @return void
		 */
		function add_meta_boxes()
		{
			foreach ( $this->meta_box['pages'] as $page )
			{
				add_meta_box( $this->meta_box['id'], $this->meta_box['title'], array( &$this, 'show' ), $page, $this->meta_box['context'], $this->meta_box['priority'] );
			}
		}

		/**
		 * Callback function to show fields in meta box
		 *
		 * @return void
		 */
		function show()
		{
			global $post;

			$saved = self::has_been_saved( $post->ID, $this->fields );

			wp_nonce_field( "rwmb-save-{$this->meta_box['id']}", "nonce_{$this->meta_box['id']}" );

			// Allow users to add custom code before meta box content
			// 1st action applies to all meta box
			// 2nd action applies to only current meta box
			do_action( 'rwmb_before' );
			do_action( "rwmb_before_{$this->meta_box['id']}" );

			foreach ( $this->fields as $field )
			{
				$meta = get_post_meta( $post->ID, $field['id'], ! $field['multiple'] );

				// Use $field['std'] only when the meta box hasn't been saved (i.e. the first time we run)
				$meta = ( ! $saved && '' === $meta OR array() === $meta ) ? $field['std'] : $meta;

				// Escape attributes for non-wysiwyg fields
				if ( $field['type'] !== 'wysiwyg' )
					$meta = is_array( $meta ) ? array_map( 'esc_attr', $meta ) : esc_attr( $meta );

				$begin = self::apply_field_class_filters( $field, 'begin_html', '', $meta );

				// Apply filter to field begin HTML
				// 1st filter applies to all fields
				// 2nd filter applies to all fields with the same type
				// 3rd filter applies to current field only
				$begin = apply_filters( "rwmb_begin_html", $begin, $field, $meta );
				$begin = apply_filters( "rwmb_{$field['type']}_begin_html", $begin, $field, $meta );
				$begin = apply_filters( "rwmb_{$field['id']}_begin_html", $begin, $field, $meta );

				// Call separated methods for displaying each type of field
				$field_html = self::apply_field_class_filters( $field, 'html', '', $meta );

				// Apply filter to field HTML
				// 1st filter applies to all fields with the same type
				// 2nd filter applies to current field only
				$field_html = apply_filters( "rwmb_{$field['type']}_html", $field_html, $field, $meta );
				$field_html = apply_filters( "rwmb_{$field['id']}_html", $field_html, $field, $meta );

				$end = self::apply_field_class_filters( $field, 'end_html', '', $meta );

				// Apply filter to field end HTML
				// 1st filter applies to all fields
				// 2nd filter applies to all fields with the same type
				// 3rd filter applies to current field only
				$end = apply_filters( "rwmb_end_html", $end, $field, $meta );
				$end = apply_filters( "rwmb_{$field['type']}_end_html", $end, $field, $meta );
				$end = apply_filters( "rwmb_{$field['id']}_end_html", $end, $field, $meta );

				// Apply filter to field wrapper
				// This allow users to change whole HTML markup of the field wrapper (i.e. table row)
				// 1st filter applies to all fields with the same type
				// 2nd filter applies to current field only
				$html = apply_filters( "rwmb_{$field['type']}_wrapper_html", "{$begin}{$field_html}{$end}", $field, $meta );
				$html = apply_filters( "rwmb_{$field['id']}_wrapper_html", $html, $field, $meta );

				// Display label and input in DIV and allow user-defined classes to be appended
				$class = 'rwmb-field';
				if ( isset( $field['class'] ) )
					$class = $this->add_cssclass( $field['class'], $class );
				echo "<div class='{$class}'>{$html}</div>";
			}

			// Allow users to add custom code after meta box content
			// 1st action applies to all meta box
			// 2nd action applies to only current meta box
			do_action( 'rwmb_after' );
			do_action( "rwmb_after_{$this->meta_box['id']}" );
		}

		/**
		 * Show hidden fields like nonce, post ID, etc.
		 *
		 * @return void
		 */
		static function dbx_post_sidebar()
		{
			global $post;

			echo "<input type='hidden' class='rwmb-post-id' value='{$post->ID}' />";
		}

		/**
		 * Show begin HTML markup for fields
		 *
		 * @param string $html
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function begin_html( $html, $meta, $field )
		{
			$html = <<<HTML
<div class="rwmb-label">
	<label for="{$field['id']}">{$field['name']}</label>
</div>
<div class="rwmb-input">
HTML;
			return $html;
		}

		/**
		 * Show end HTML markup for fields
		 *
		 * @param string $html
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function end_html( $html, $meta, $field )
		{
			$html  = ! empty( $field['desc'] ) ? "<p class='description'>{$field['desc']}</p>" : '';
			// Closes the container
			$html .= '</div>';

			return $html;
		}

		/**************************************************
			SAVE META BOX
		**************************************************/

		/**
		 * Save data from meta box
		 *
		 * @param int $post_id Post ID
		 *
		 * @return int|void
		 */
		function save_post( $post_id )
		{
			global $post_type;
			$post_type_object = get_post_type_object( $post_type );

			// Check whether:
			// - the post is autosaved
			// - the post is a revision
			// - current post type is supported
			// - user has proper capability
			if (
				( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				|| ( ! isset( $_POST['post_ID'] ) || $post_id != $_POST['post_ID'] )
				|| ( ! in_array( $post_type, $this->meta_box['pages'] ) )
				|| ( ! current_user_can( $post_type_object->cap->edit_post, $post_id ) )
				)
			{
				return $post_id;
			}

			// Verify nonce
			check_admin_referer( "rwmb-save-{$this->meta_box['id']}", "nonce_{$this->meta_box['id']}" );

			foreach ( $this->fields as $field )
			{
				$name = $field['id'];
				$old  = get_post_meta( $post_id, $name, ! $field['multiple'] );
				$new  = isset( $_POST[$name] ) ? $_POST[$name] : ( $field['multiple'] ? array() : '' );

				// Allow field class change the value
				$new = self::apply_field_class_filters( $field, 'value', $new, $old, $post_id );

				// Use filter to change field value
				// 1st filter applies to all fields with the same type
				// 2nd filter applies to current field only
				$new = apply_filters( "rwmb_{$field['type']}_value", $new, $field, $old );
				$new = apply_filters( "rwmb_{$field['id']}_value", $new, $field, $old );

				// Call defined method to save meta value, if there's no methods, call common one
				self::do_field_class_actions( $field, 'save', $new, $old, $post_id );
			}
		}

		/**
		 * Common functions for saving field
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int $post_id
		 * @param array $field
		 *
		 * @return void
		 */
		static function save( $new, $old, $post_id, $field )
		{
			$name = $field['id'];

			delete_post_meta( $post_id, $name );
			if ( '' === $new || array() === $new )
				return;

			if ( $field['multiple'] )
			{
				foreach ( $new as $add_new )
				{
					add_post_meta( $post_id, $name, $add_new, false );
				}
			}
			else
			{
				update_post_meta( $post_id, $name, $new );
			}
		}

		/**************************************************
			HELPER FUNCTIONS
		**************************************************/

		/**
		 * Normalize parameters for meta box
		 *
		 * @param array $meta_box Meta box definition
		 *
		 * @return array $meta_box Normalized meta box
		 */
		static function normalize( $meta_box )
		{
			// Set default values for meta box
			$meta_box = wp_parse_args( $meta_box, array(
				'context'  => 'normal',
				'priority' => 'high',
				'pages'    => array( 'post' )
			) );

			// Set default values for fields
			foreach ( $meta_box['fields'] as &$field )
			{
				$multiple = in_array( $field['type'], array( 'checkbox_list', 'file', 'image' ) );
				$std      = $multiple ? array() : '';
				$format   = 'date' === $field['type'] ? 'yy-mm-dd' : ( 'time' === $field['type'] ? 'hh:mm' : '' );

				$field = wp_parse_args( $field, array(
					'multiple'=> $multiple,
					'std'     => $std,
					'desc'    => '',
					'format'  => $format
				) );

				// Allow field class add/change default field values
				$field = self::apply_field_class_filters( $field, 'normalize_field', $field );
			}

			return $meta_box;
		}

		/**
		 * Get field class name
		 *
		 * @param string $type Field type
		 *
		 * @return bool|string Field class name OR false on failure
		 */
		static function get_class_name( $type )
		{
			$type	= ucwords( $type );
			$class	= "RWMB_{$type}_Field";

			if ( class_exists( $class ) )
				return $class;

			return false;
		}

		/**
		 * Apply filters by field class, fallback to RW_Meta_Box method
		 *
		 * @param array  $field
		 * @param string $method_name
		 * @param mixed  $value
		 *
		 * @return mixed $value
		 */
		static function apply_field_class_filters( $field, $method_name, $value )
		{
			$args	= array_slice( func_get_args(), 2 );
			$args[]	= $field;

			// Call:     field class method
			// Fallback: RW_Meta_Box method
			$class = self::get_class_name( $field['type'] );
			if ( method_exists( $class, $method_name ) )
			{
				$value = call_user_func_array( array( $class, $method_name ), $args );
			}
			elseif ( method_exists( __CLASS__, $method_name ) )
			{
				$value = call_user_func_array( array( __CLASS__, $method_name ), $args );
			}

			return $value;
		}

		/**
		 * Call field class method for actions, fallback to RW_Meta_Box method
		 *
		 * @param array  $field
		 * @param string $method_name
		 *
		 * @return mixed
		 */
		static function do_field_class_actions( $field, $method_name )
		{
			$args   = array_slice( func_get_args(), 2 );
			$args[] = $field;

			// Call:     field class method
			// Fallback: RW_Meta_Box method
			$class = self::get_class_name( $field['type'] );
			if ( method_exists( $class, $method_name ) )
			{
				call_user_func_array( array( $class, $method_name ), $args );
			}
			elseif ( method_exists( __CLASS__, $method_name ) )
			{
				call_user_func_array( array( __CLASS__, $method_name ), $args );
			}
		}

		/**
		 * Format Ajax response
		 *
		 * @param string $message
		 * @param string $status
		 *
		 * @return void
		 */
		static function ajax_response( $message, $status )
		{
			$response = array( 'what' => 'meta-box' );
			$response['data'] = 'error' === $status ? new WP_Error( 'error', $message ) : $message;
			$x = new WP_Ajax_Response( $response );
			$x->send();
		}

		/**
		 * Check if meta box has been saved
		 * This helps saving empty value in meta fields (for text box, check box, etc.)
		 *
		 * @param int   $post_id
		 * @param array $fields
		 *
		 * @return bool
		 */
		static function has_been_saved( $post_id, $fields )
		{
			$saved = false;
			foreach ( $fields as $field )
			{
				if ( get_post_meta( $post_id, $field['id'], !$field['multiple'] ) )
				{
					$saved = true;
					break;
				}
			}
			return $saved;
		}

		/**
		 * Adds a css class
		 * Mainly a copy of the core admin menu function
		 * As the core fn is only meant to be used by core internally,
		 * we copy it here - in case core changes functionality or drops the fn.
		 * 
		 * @param string $add
		 * @param string $class | Class name - Default: empty
		 * @return $class
		 */
		function add_cssclass( $add, $class = '' ) 
		{
			$class .= empty( $class ) ? $add : " {$add}";

			return $class;
		}
	}
}

/**
 * Adds [whatever] to the global debug array
 *
 * @param mixed  $input
 * @param string $print_or_export
 *
 * @return array
 */
function rwmb_debug( $input, $print_or_export = 'print' )
{
	global $rwmb_debug;

	$html = 'print' === $print_or_export ? print_r( $input, true ) : var_export( $input, true );

	return $rwmb_debug[] = $html;
}

/**
 * Prints or exports the content of the global debug array at the 'shutdown' hook
 *
 * @return void
 */
function rwmb_debug_print()
{
	global $rwmb_debug;
	if ( ! $rwmb_debug || ( is_user_logged_in() && is_user_admin() ) )
		return;

	$html  = '<h3>RW_Meta_Box Debug:</h3><pre>';
	foreach ( $rwmb_debug as $debug )
	{
		$html .= "{$debug}<hr />";
	}
	$html .= '</pre>';

	die( $html );
}

add_action( 'shutdown', 'rwmb_debug_print', 999 );
<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Meta Box Class
if ( ! class_exists( 'RW_Meta_Box' ) )
{
	/**
	 * A class to rapid develop meta boxes for custom & built in content types
	 * Piggybacks on WordPress
	 *
	 * @author  Rilwis
	 * @author  Co-Authors @see https://github.com/rilwis/meta-box
	 * @license GNU GPL2+
	 * @package RW Meta Box
	 */
	class RW_Meta_Box
	{
		/**
		 * @var array Meta box information
		 */
		public $meta_box;

		/**
		 * @var array Fields information
		 */
		public $fields;

		/**
		 * @var array Contains all field types of current meta box
		 */
		public $types;

		/**
		 * @var bool Used to prevent duplicated calls like revisions, manual hook to wp_insert_post, etc.
		 */
		public $saved = false;

		/**
		 * Create meta box based on given data
		 *
		 * @see demo/demo.php file for details
		 *
		 * @param array $meta_box Meta box definition
		 *
		 * @return RW_Meta_Box
		 */
		function __construct( $meta_box )
		{
			// Run script only in admin area
			if ( ! is_admin() )
				return;

			// Assign meta box values to local variables and add it's missed values
			$this->meta_box = self::normalize( $meta_box );
			$this->fields   = &$this->meta_box['fields'];

			// Allow users to show/hide meta box
			// 1st action applies to all meta boxes
			// 2nd action applies to only current meta box
			$show = true;
			$show = apply_filters( 'rwmb_show', $show, $this->meta_box );
			$show = apply_filters( "rwmb_show_{$this->meta_box['id']}", $show, $this->meta_box );
			if ( ! $show )
				return;

			// Enqueue common styles and scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			// Add additional actions for fields
			$fields = self::get_fields( $this->fields );
			foreach ( $fields as $field )
			{
				call_user_func( array( self::get_class_name( $field ), 'add_actions' ) );
			}

			// Add meta box
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

			// Hide meta box if it's set 'default_hidden'
			add_filter( 'default_hidden_meta_boxes', array( $this, 'hide' ), 10, 2 );

			// Save post meta
			foreach ( $this->meta_box['post_types'] as $post_type )
			{
				if ( 'attachment' === $post_type )
				{
					// Attachment uses other hooks
					// @see wp_update_post(), wp_insert_attachment()
					add_action( 'edit_attachment', array( $this, 'save_post' ) );
					add_action( 'add_attachment', array( $this, 'save_post' ) );
				}
				else
				{
					add_action( "save_post_{$post_type}", array( $this, 'save_post' ) );
				}
			}
		}

		/**
		 * Enqueue common styles
		 *
		 * @return void
		 */
		function admin_enqueue_scripts()
		{
			if ( ! $this->is_edit_screen() )
				return;

			wp_enqueue_style( 'rwmb', RWMB_CSS_URL . 'style.css', array(), RWMB_VER );

			// Load clone script conditionally
			$fields = self::get_fields( $this->fields );
			foreach ( $fields as $field )
			{
				if ( $field['clone'] )
				{
					wp_enqueue_script( 'rwmb-clone', RWMB_JS_URL . 'clone.js', array( 'jquery' ), RWMB_VER, true );
					break;
				}
			}

			// Enqueue scripts and styles for fields
			foreach ( $fields as $field )
			{
				call_user_func( array( self::get_class_name( $field ), 'admin_enqueue_scripts' ) );
			}

			// Auto save
			if ( $this->meta_box['autosave'] )
				wp_enqueue_script( 'rwmb-autosave', RWMB_JS_URL . 'autosave.js', array( 'jquery' ), RWMB_VER, true );

			/**
			 * Allow developers to enqueue more scripts and styles
			 *
			 * @param RW_Meta_Box $object Meta Box object
			 */
			do_action( 'rwmb_enqueue_scripts', $this );
		}

		/**
		 * Get all fields of a meta box, recursively
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		static function get_fields( $fields )
		{
			$all_fields = array();
			foreach ( $fields as $field )
			{
				$all_fields[] = $field;
				if ( isset( $field['fields'] ) )
					$all_fields = array_merge( $all_fields, self::get_fields( $field['fields'] ) );
			}

			return $all_fields;
		}

		/**************************************************
		 * SHOW META BOX
		 **************************************************/

		/**
		 * Add meta box for multiple post types
		 *
		 * @return void
		 */
		function add_meta_boxes()
		{
			foreach ( $this->meta_box['post_types'] as $post_type )
			{
				add_meta_box(
					$this->meta_box['id'],
					$this->meta_box['title'],
					array( $this, 'show' ),
					$post_type,
					$this->meta_box['context'],
					$this->meta_box['priority']
				);
			}
		}

		/**
		 * Hide meta box if it's set 'default_hidden'
		 *
		 * @param array  $hidden Array of default hidden meta boxes
		 * @param object $screen Current screen information
		 *
		 * @return array
		 */
		function hide( $hidden, $screen )
		{
			if ( $this->is_edit_screen( $screen ) && $this->meta_box['default_hidden'] )
			{
				$hidden[] = $this->meta_box['id'];
			}

			return $hidden;
		}

		/**
		 * Callback function to show fields in meta box
		 *
		 * @return void
		 */
		function show()
		{
			$saved = $this->is_saved();

			// Container
			printf(
				'<div class="rwmb-meta-box" data-autosave="%s">',
				$this->meta_box['autosave'] ? 'true' : 'false'
			);

			wp_nonce_field( "rwmb-save-{$this->meta_box['id']}", "nonce_{$this->meta_box['id']}" );

			// Allow users to add custom code before meta box content
			// 1st action applies to all meta boxes
			// 2nd action applies to only current meta box
			do_action( 'rwmb_before', $this );
			do_action( "rwmb_before_{$this->meta_box['id']}", $this );

			foreach ( $this->fields as $field )
			{
				call_user_func( array( self::get_class_name( $field ), 'show' ), $field, $saved );
			}

			// Allow users to add custom code after meta box content
			// 1st action applies to all meta boxes
			// 2nd action applies to only current meta box
			do_action( 'rwmb_after', $this );
			do_action( "rwmb_after_{$this->meta_box['id']}", $this );

			// End container
			echo '</div>';
		}

		/**************************************************
		 * SAVE META BOX
		 **************************************************/

		/**
		 * Save data from meta box
		 *
		 * @param int $post_id Post ID
		 *
		 * @return void
		 */
		function save_post( $post_id )
		{
			// Check if this function is called to prevent duplicated calls like revisions, manual hook to wp_insert_post, etc.
			if ( true === $this->saved )
				return;
			$this->saved = true;

			// Check whether form is submitted properly
			$id    = $this->meta_box['id'];
			$nonce = isset( $_POST["nonce_{$id}"] ) ? sanitize_key( $_POST["nonce_{$id}"] ) : '';
			if ( empty( $_POST["nonce_{$id}"] ) || ! wp_verify_nonce( $nonce, "rwmb-save-{$id}" ) )
				return;

			// Autosave
			if ( defined( 'DOING_AUTOSAVE' ) && ! $this->meta_box['autosave'] )
				return;

			// Make sure meta is added to the post, not a revision
			if ( $the_post = wp_is_post_revision( $post_id ) )
				$post_id = $the_post;

			// Before save action
			do_action( 'rwmb_before_save_post', $post_id );
			do_action( "rwmb_{$this->meta_box['id']}_before_save_post", $post_id );

			foreach ( $this->fields as $field )
			{
				$name   = $field['id'];
				$single = $field['clone'] || ! $field['multiple'];
				$old    = get_post_meta( $post_id, $name, $single );
				$new    = isset( $_POST[$name] ) ? $_POST[$name] : ( $single ? '' : array() );

				// Allow field class change the value
				$new = call_user_func( array( self::get_class_name( $field ), 'value' ), $new, $old, $post_id, $field );

				// Use filter to change field value
				// 1st filter applies to all fields with the same type
				// 2nd filter applies to current field only
				$new = apply_filters( "rwmb_{$field['type']}_value", $new, $field, $old );
				$new = apply_filters( "rwmb_{$name}_value", $new, $field, $old );

				// Call defined method to save meta value, if there's no methods, call common one
				call_user_func( array( self::get_class_name( $field ), 'save' ), $new, $old, $post_id, $field );
			}

			// After save action
			do_action( 'rwmb_after_save_post', $post_id );
			do_action( "rwmb_{$this->meta_box['id']}_after_save_post", $post_id );
		}

		/**************************************************
		 * HELPER FUNCTIONS
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
				'id'             => sanitize_title( $meta_box['title'] ),
				'context'        => 'normal',
				'priority'       => 'high',
				'post_types'     => 'post',
				'autosave'       => false,
				'default_hidden' => false,
			) );

			/**
			 * Use 'post_types' for better understanding and fallback to 'pages' for previous versions
			 *
			 * @since 4.4.1
			 */
			if ( ! empty( $meta_box['pages'] ) )
			{
				$meta_box['post_types'] = $meta_box['pages'];
			}

			// Allow to set 'post_types' param by string
			if ( is_string( $meta_box['post_types'] ) )
			{
				$meta_box['post_types'] = array( $meta_box['post_types'] );
			}

			// Set default values for fields
			$meta_box['fields'] = self::normalize_fields( $meta_box['fields'] );

			// Allow to add default values for meta box
			$meta_box = apply_filters( 'rwmb_normalize_meta_box', $meta_box );
			$meta_box = apply_filters( "rwmb_normalize_{$meta_box['id']}_meta_box", $meta_box );

			return $meta_box;
		}

		/**
		 * Normalize an array of fields
		 *
		 * @param array $fields Array of fields
		 *
		 * @return array $fields Normalized fields
		 */
		static function normalize_fields( $fields )
		{
			foreach ( $fields as $k => $field )
			{
				$field = wp_parse_args( $field, array(
					'id'          => '',
					'name'        => '',
					'multiple'    => false,
					'std'         => '',
					'desc'        => '',
					'format'      => '',
					'before'      => '',
					'after'       => '',
					'field_name'  => isset( $field['id'] ) ? $field['id'] : '',
					'required'    => false,
					'placeholder' => '',

					'clone'      => false,
					'max_clone'  => 0,
					'sort_clone' => false,
				) );

				$class = self::get_class_name( $field );

				// Make sure field has correct 'type', ignore warning error when users forget to set field type or set incorrect one
				if ( false === $class )
				{
					unset( $fields[$k] );
					continue;
				}

				// Allow field class add/change default field values
				$field = call_user_func( array( $class, 'normalize_field' ), $field );

				if ( isset( $field['fields'] ) )
					$field['fields'] = self::normalize_fields( $field['fields'] );

				// Allow to add default values for fields
				$field = apply_filters( 'rwmb_normalize_field', $field );
				$field = apply_filters( "rwmb_normalize_{$field['type']}_field", $field );
				$field = apply_filters( "rwmb_normalize_{$field['id']}_field", $field );

				$fields[$k] = $field;
			}

			return $fields;
		}

		/**
		 * Get field class name
		 *
		 * @param array $field Field array
		 *
		 * @return bool|string Field class name OR false on failure
		 */
		static function get_class_name( $field )
		{
			// Convert underscores to whitespace so ucwords works as expected. Otherwise: plupload_image -> Plupload_image instead of Plupload_Image
			$type = str_replace( '_', ' ', $field['type'] );

			// Uppercase first words
			$class = 'RWMB_' . ucwords( $type ) . '_Field';

			// Relace whitespace with underscores
			$class = str_replace( ' ', '_', $class );

			return class_exists( $class ) ? $class : false;
		}

		/**
		 * Check if meta box is saved before.
		 * This helps saving empty value in meta fields (for text box, check box, etc.) and set the correct
		 * default values.
		 *
		 * @return bool
		 */
		public function is_saved()
		{
			$post = get_post();

			foreach ( $this->fields as $field )
			{
				$value = get_post_meta( $post->ID, $field['id'], ! $field['multiple'] );
				if (
					( ! $field['multiple'] && '' !== $value )
					|| ( $field['multiple'] && array() !== $value )
				)
				{
					return true;
				}
			}

			return false;
		}

		/**
		 * Check if we're on the right edit screen.
		 *
		 * @param WP_Screen $screen Screen object. Optional. Use current screen object by default.
		 *
		 * @return bool
		 */
		function is_edit_screen( $screen = null )
		{
			if ( ! ( $screen instanceof WP_Screen ) )
			{
				$screen = get_current_screen();
			}
			return 'post' == $screen->base && in_array( $screen->post_type, $this->meta_box['post_types'] );
		}
	}
}

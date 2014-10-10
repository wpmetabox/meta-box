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
		 * @var array Validation information
		 */
		public $validation;

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
			$this->meta_box   = self::normalize( $meta_box );
			$this->fields     = &$this->meta_box['fields'];
			$this->validation = &$this->meta_box['validation'];

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
			add_action( 'save_post', array( $this, 'save_post' ) );

			// Attachment uses other hooks
			// @see wp_update_post(), wp_insert_attachment()
			add_action( 'edit_attachment', array( $this, 'save_post' ) );
			add_action( 'add_attachment', array( $this, 'save_post' ) );
		}

		/**
		 * Enqueue common styles
		 *
		 * @return void
		 */
		function admin_enqueue_scripts()
		{
			$screen = get_current_screen();

			// Enqueue scripts and styles for registered pages (post types) only
			if ( 'post' != $screen->base || ! in_array( $screen->post_type, $this->meta_box['pages'] ) )
				return;

			wp_enqueue_style( 'rwmb', RWMB_CSS_URL . 'style.css', array(), RWMB_VER );

			// Load clone script conditionally
			$has_clone = false;
			$fields    = self::get_fields( $this->fields );

			foreach ( $fields as $field )
			{
				if ( $field['clone'] )
					$has_clone = true;

				// Enqueue scripts and styles for fields
				call_user_func( array( self::get_class_name( $field ), 'admin_enqueue_scripts' ) );
			}

			if ( $has_clone )
				wp_enqueue_script( 'rwmb-clone', RWMB_JS_URL . 'clone.js', array( 'jquery' ), RWMB_VER, true );

			if ( $this->validation )
			{
				wp_enqueue_script( 'jquery-validate', RWMB_JS_URL . 'jquery.validate.min.js', array( 'jquery' ), RWMB_VER, true );
				wp_enqueue_script( 'rwmb-validate', RWMB_JS_URL . 'validate.js', array( 'jquery-validate' ), RWMB_VER, true );
			}

			// Auto save
			if ( $this->meta_box['autosave'] )
				wp_enqueue_script( 'rwmb-autosave', RWMB_JS_URL . 'autosave.js', array( 'jquery' ), RWMB_VER, true );
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
			foreach ( $this->meta_box['pages'] as $page )
			{
				add_meta_box(
					$this->meta_box['id'],
					$this->meta_box['title'],
					array( $this, 'show' ),
					$page,
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
			if (
				'post' === $screen->base
				&& in_array( $screen->post_type, $this->meta_box['pages'] )
				&& $this->meta_box['default_hidden']
			)
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
			global $post;

			$saved = self::has_been_saved( $post->ID, $this->fields );

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

			// Include validation settings for this meta-box
			if ( isset( $this->validation ) && $this->validation )
			{
				echo '
					<script>
					if ( typeof rwmb == "undefined" )
					{
						var rwmb = {
							validationOptions : jQuery.parseJSON( \'' . json_encode( $this->validation ) . '\' ),
							summaryMessage : "' . esc_js( __( 'Please correct the errors highlighted below and try again.', 'meta-box' ) ) . '"
						};
					}
					else
					{
						var tempOptions = jQuery.parseJSON( \'' . json_encode( $this->validation ) . '\' );
						jQuery.extend( true, rwmb.validationOptions, tempOptions );
					}
					</script>
				';
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
			if ( $this->saved === true )
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
				$name = $field['id'];
				$old  = get_post_meta( $post_id, $name, ! $field['multiple'] );
				$new  = isset( $_POST[$name] ) ? $_POST[$name] : ( $field['multiple'] ? array() : '' );

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
				'pages'          => array( 'post' ),
				'autosave'       => false,
				'default_hidden' => false,
			) );

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
			foreach ( $fields as &$field )
			{
				$field = wp_parse_args( $field, array(
					'multiple'    => false,
					'clone'       => false,
					'std'         => '',
					'desc'        => '',
					'format'      => '',
					'before'      => '',
					'after'       => '',
					'field_name'  => isset( $field['id'] ) ? $field['id'] : '',
					'required'    => false,
					'placeholder' => '',
				) );

				do_action( 'rwmb_before_normalize_field', $field );
				do_action( "rwmb_before_normalize_{$field['type']}_field", $field );
				do_action( "rwmb_before_normalize_{$field['id']}_field", $field );

				// Allow field class add/change default field values
				$field = call_user_func( array( self::get_class_name( $field ), 'normalize_field' ), $field );

				if ( isset( $field['fields'] ) )
					$field['fields'] = self::normalize_fields( $field['fields'] );

				// Allow to add default values for fields
				$field = apply_filters( 'rwmb_normalize_field', $field );
				$field = apply_filters( "rwmb_normalize_{$field['type']}_field", $field );
				$field = apply_filters( "rwmb_normalize_{$field['id']}_field", $field );

				do_action( 'rwmb_after_normalize_field', $field );
				do_action( "rwmb_after_normalize_{$field['type']}_field", $field );
				do_action( "rwmb_after_normalize_{$field['id']}_field", $field );
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
			foreach ( $fields as $field )
			{
				$value = get_post_meta( $post_id, $field['id'], ! $field['multiple'] );
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
	}
}

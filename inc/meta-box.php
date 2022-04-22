<?php
/**
 * A class to rapid develop meta boxes for custom & built in content types
 * Piggybacks on WordPress
 *
 * @author  Tran Ngoc Tuan Anh <rilwis@gmail.com>
 * @license GNU GPL2+
 * @package Meta Box
 */

/**
 * The main meta box class.
 *
 * @property string $id             Meta Box ID.
 * @property string $title          Meta Box title.
 * @property array  $fields         List of fields.
 * @property array  $post_types     List of post types that the meta box is created for.
 * @property string $style          Meta Box style.
 * @property bool   $closed         Whether to collapse the meta box when page loads.
 * @property string $priority       The meta box priority.
 * @property string $context        Where the meta box is displayed.
 * @property bool   $default_hidden Whether the meta box is hidden by default.
 * @property bool   $autosave       Whether the meta box auto saves.
 * @property bool   $media_modal    Add custom fields to media modal when viewing/editing an attachment.
 *
 * @package Meta Box
 */
class RW_Meta_Box {
	/**
	 * Meta box parameters.
	 *
	 * @var array
	 */
	public $meta_box;

	/**
	 * Detect whether the meta box is saved at least once.
	 * Used to prevent duplicated calls like revisions, manual hook to wp_insert_post, etc.
	 *
	 * @var bool
	 */
	public $saved = false;

	/**
	 * The object ID.
	 *
	 * @var int
	 */
	public $object_id = null;

	/**
	 * The object type.
	 *
	 * @var string
	 */
	protected $object_type = 'post';

	/**
	 * Create meta box based on given data.
	 *
	 * @param array $meta_box Meta box definition.
	 */
	public function __construct( $meta_box ) {
		$meta_box       = static::normalize( $meta_box );
		$this->meta_box = $meta_box;

		$this->meta_box['fields'] = static::normalize_fields( $meta_box['fields'], $this->get_storage() );

		$this->meta_box = apply_filters( 'rwmb_meta_box_settings', $this->meta_box );

		if ( $this->is_shown() ) {
			$this->global_hooks();
			$this->object_hooks();
		}
	}

	/**
	 * Add fields to field registry.
	 */
	public function register_fields() {
		$field_registry = rwmb_get_registry( 'field' );

		foreach ( $this->post_types as $post_type ) {
			foreach ( $this->fields as $field ) {
				$field_registry->add( $field, $post_type );
			}
		}
	}

	/**
	 * Conditional check for whether initializing meta box.
	 *
	 * - 1st filter applies to all meta boxes.
	 * - 2nd filter applies to only current meta box.
	 *
	 * @return bool
	 */
	public function is_shown() {
		$show = apply_filters( 'rwmb_show', true, $this->meta_box );

		return apply_filters( "rwmb_show_{$this->id}", $show, $this->meta_box );
	}

	/**
	 * Add global hooks.
	 */
	protected function global_hooks() {
		// Enqueue common styles and scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// Add additional actions for fields.
		foreach ( $this->fields as $field ) {
			RWMB_Field::call( $field, 'add_actions' );
		}
	}

	/**
	 * Specific hooks for meta box object. Default is 'post'.
	 * This should be extended in sub-classes to support meta fields for terms, user, settings pages, etc.
	 */
	protected function object_hooks() {
		// Add meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Hide meta box if it's set 'default_hidden'.
		add_filter( 'default_hidden_meta_boxes', array( $this, 'hide' ), 10, 2 );

		// Save post meta.
		foreach ( $this->post_types as $post_type ) {
			if ( 'attachment' === $post_type ) {
				// Attachment uses other hooks.
				// @see wp_update_post(), wp_insert_attachment().
				add_action( 'edit_attachment', array( $this, 'save_post' ) );
				add_action( 'add_attachment', array( $this, 'save_post' ) );
			} else {
				add_action( "save_post_{$post_type}", array( $this, 'save_post' ) );
			}
		}
	}

	/**
	 * Enqueue common scripts and styles.
	 */
	public function enqueue() {
		if ( is_admin() && ! $this->is_edit_screen() ) {
			return;
		}

		wp_enqueue_style( 'rwmb', RWMB_CSS_URL . 'style.css', array(), RWMB_VER );
		if ( is_rtl() ) {
			wp_enqueue_style( 'rwmb-rtl', RWMB_CSS_URL . 'style-rtl.css', array(), RWMB_VER );
		}

		wp_enqueue_script( 'rwmb', RWMB_JS_URL . 'script.js', array( 'jquery' ), RWMB_VER, true );

		// Load clone script conditionally.
		foreach ( $this->fields as $field ) {
			if ( $field['clone'] ) {
				wp_enqueue_script( 'rwmb-clone', RWMB_JS_URL . 'clone.js', array( 'jquery-ui-sortable' ), RWMB_VER, true );
				break;
			}
		}

		// Enqueue scripts and styles for fields.
		foreach ( $this->fields as $field ) {
			RWMB_Field::call( $field, 'admin_enqueue_scripts' );
		}

		// Auto save.
		if ( $this->autosave ) {
			wp_enqueue_script( 'rwmb-autosave', RWMB_JS_URL . 'autosave.js', array( 'jquery' ), RWMB_VER, true );
		}

		/**
		 * Allow developers to enqueue more scripts and styles
		 *
		 * @param RW_Meta_Box $object Meta Box object
		 */
		do_action( 'rwmb_enqueue_scripts', $this );
	}

	/**
	 * Add meta box for multiple post types
	 */
	public function add_meta_boxes() {
		$screen = get_current_screen();
		add_filter( "postbox_classes_{$screen->id}_{$this->id}", array( $this, 'postbox_classes' ) );

		foreach ( $this->post_types as $post_type ) {
			add_meta_box(
				$this->id,
				$this->title,
				array( $this, 'show' ),
				$post_type,
				$this->context,
				$this->priority
			);
		}
	}

	/**
	 * Modify meta box postbox classes.
	 *
	 * @param  array $classes Array of classes.
	 * @return array
	 */
	public function postbox_classes( $classes ) {
		if ( $this->closed ) {
			$classes[] = 'closed';
		}
		$classes[] = "rwmb-{$this->style}";

		return $classes;
	}

	/**
	 * Hide meta box if it's set 'default_hidden'
	 *
	 * @param array  $hidden Array of default hidden meta boxes.
	 * @param object $screen Current screen information.
	 *
	 * @return array
	 */
	public function hide( $hidden, $screen ) {
		if ( $this->is_edit_screen( $screen ) && $this->default_hidden ) {
			$hidden[] = $this->id;
		}

		return $hidden;
	}

	/**
	 * Callback function to show fields in meta box
	 */
	public function show() {
		if ( null === $this->object_id ) {
			$this->object_id = $this->get_current_object_id();
		}
		$saved = $this->is_saved();

		// Container.
		printf(
			'<div class="%s" data-autosave="%s" data-object-type="%s" data-object-id="%s">',
			esc_attr( trim( "rwmb-meta-box {$this->class}" ) ),
			esc_attr( $this->autosave ? 'true' : 'false' ),
			esc_attr( $this->object_type ),
			esc_attr( $this->object_id )
		);

		wp_nonce_field( "rwmb-save-{$this->id}", "nonce_{$this->id}" );

		// Allow users to add custom code before meta box content.
		// 1st action applies to all meta boxes.
		// 2nd action applies to only current meta box.
		do_action( 'rwmb_before', $this );
		do_action( "rwmb_before_{$this->id}", $this );

		foreach ( $this->fields as $field ) {
			RWMB_Field::call( 'show', $field, $saved, $this->object_id );
		}

		// Allow users to add custom code after meta box content.
		// 1st action applies to all meta boxes.
		// 2nd action applies to only current meta box.
		do_action( 'rwmb_after', $this );
		do_action( "rwmb_after_{$this->id}", $this );

		// End container.
		echo '</div>';
	}

	/**
	 * Save data from meta box
	 *
	 * @param int $object_id Object ID.
	 */
	public function save_post( $object_id ) {
		if ( ! $this->validate() ) {
			return;
		}
		$this->saved = true;

		$object_id       = $this->get_real_object_id( $object_id );
		$this->object_id = $object_id;

		// Before save action.
		do_action( 'rwmb_before_save_post', $object_id );
		do_action( "rwmb_{$this->id}_before_save_post", $object_id );

		array_map( array( $this, 'save_field' ), $this->fields );

		// After save action.
		do_action( 'rwmb_after_save_post', $object_id );
		do_action( "rwmb_{$this->id}_after_save_post", $object_id );
	}

	/**
	 * Save field.
	 *
	 * @param array $field Field settings.
	 */
	public function save_field( $field ) {
		$single  = $field['clone'] || ! $field['multiple'];
		$default = $single ? '' : array();
		$old     = RWMB_Field::call( $field, 'raw_meta', $this->object_id );
		$new     = rwmb_request()->post( $field['id'], $default );
		$new     = RWMB_Field::process_value( $new, $this->object_id, $field );

		// Filter to allow the field to be modified.
		$field = RWMB_Field::filter( 'field', $field, $field, $new, $old );

		// Call defined method to save meta value, if there's no methods, call common one.
		RWMB_Field::call( $field, 'save', $new, $old, $this->object_id );

		RWMB_Field::filter( 'after_save_field', null, $field, $new, $old, $this->object_id );
	}

	/**
	 * Validate form when submit. Check:
	 * - If this function is called to prevent duplicated calls like revisions, manual hook to wp_insert_post, etc.
	 * - Autosave
	 * - If form is submitted properly
	 *
	 * @return bool
	 */
	public function validate() {
		$nonce = rwmb_request()->filter_post( "nonce_{$this->id}" );

		return ! $this->saved
			&& ( ! defined( 'DOING_AUTOSAVE' ) || $this->autosave )
			&& wp_verify_nonce( $nonce, "rwmb-save-{$this->id}" );
	}

	/**
	 * Normalize parameters for meta box
	 *
	 * @param array $meta_box Meta box definition.
	 *
	 * @return array $meta_box Normalized meta box.
	 */
	public static function normalize( $meta_box ) {
		$default_title = __( 'Meta Box Title', 'meta-box' );
		// Set default values for meta box.
		$meta_box = wp_parse_args(
			$meta_box,
			array(
				'title'          => $default_title,
				'id'             => ! empty( $meta_box['title'] ) ? sanitize_title( $meta_box['title'] ) : sanitize_title( $default_title ),
				'context'        => 'normal',
				'priority'       => 'high',
				'post_types'     => 'post',
				'autosave'       => false,
				'default_hidden' => false,
				'style'          => 'default',
				'class'          => '',
				'fields'         => array(),
			)
		);

		/**
		 * Use 'post_types' for better understanding and fallback to 'pages' for previous versions.
		 *
		 * @since 4.4.1
		 */
		RWMB_Helpers_Array::change_key( $meta_box, 'pages', 'post_types' );

		// Make sure the post type is an array and is sanitized.
		$meta_box['post_types'] = array_map( 'sanitize_key', RWMB_Helpers_Array::from_csv( $meta_box['post_types'] ) );

		return $meta_box;
	}

	/**
	 * Normalize an array of fields
	 *
	 * @param array                  $fields Array of fields.
	 * @param RWMB_Storage_Interface $storage Storage object. Optional.
	 *
	 * @return array $fields Normalized fields.
	 */
	public static function normalize_fields( $fields, $storage = null ) {
		foreach ( $fields as $k => $field ) {
			$field = RWMB_Field::call( 'normalize', $field );

			// Allow to add default values for fields.
			$field = apply_filters( 'rwmb_normalize_field', $field );
			$field = apply_filters( "rwmb_normalize_{$field['type']}_field", $field );
			$field = apply_filters( "rwmb_normalize_{$field['id']}_field", $field );

			$field['storage'] = $storage;

			$fields[ $k ] = $field;
		}

		return $fields;
	}

	/**
	 * Check if meta box is saved before.
	 * This helps saving empty value in meta fields (text, check box, etc.) and set the correct default values.
	 *
	 * @return bool
	 */
	public function is_saved() {
		foreach ( $this->fields as $field ) {
			if ( empty( $field['id'] ) ) {
				continue;
			}

			$value = RWMB_Field::call( $field, 'raw_meta', $this->object_id );
			if ( false === $value ) {
				continue;
			}

			$single = ! $field['multiple'];
			if ( $field['clone'] ) {
				$single = ! $field['clone_as_multiple'];
			}

			if (
				( $single && '' !== $value )
				|| ( ! $single && is_array( $value ) && array() !== $value )
			) {
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
	public function is_edit_screen( $screen = null ) {
		if ( ! ( $screen instanceof WP_Screen ) ) {
			$screen = get_current_screen();
		}

		return in_array( $screen->base, array( 'post', 'upload' ), true ) && in_array( $screen->post_type, $this->post_types, true );
	}

	/**
	 * Magic function to get meta box property.
	 *
	 * @param string $key Meta box property name.
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		return isset( $this->meta_box[ $key ] ) ? $this->meta_box[ $key ] : false;
	}

	/**
	 * Set the object ID.
	 *
	 * @param mixed $id Object ID.
	 */
	public function set_object_id( $id = null ) {
		$this->object_id = $id;
	}

	/**
	 * Get object type.
	 *
	 * @return string
	 */
	public function get_object_type() {
		return $this->object_type;
	}

	/**
	 * Get storage object.
	 *
	 * @return RWMB_Storage_Interface
	 */
	public function get_storage() {
		return rwmb_get_storage( $this->object_type, $this );
	}

	/**
	 * Get current object id.
	 *
	 * @return int
	 */
	protected function get_current_object_id() {
		return get_the_ID();
	}

	/**
	 * Get real object ID when submitting.
	 *
	 * @param int $object_id Object ID.
	 * @return int
	 */
	protected function get_real_object_id( $object_id ) {
		// Make sure meta is added to the post, not a revision.
		if ( 'post' !== $this->object_type ) {
			return $object_id;
		}
		$parent = wp_is_post_revision( $object_id );

		return $parent ? $parent : $object_id;
	}
}

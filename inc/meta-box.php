<?php
use MetaBox\Support\Arr;

/**
 * A class to rapid develop meta boxes for custom & built in content types
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

	public function __construct( array $meta_box ) {
		$meta_box       = static::normalize( $meta_box );
		$this->meta_box = $meta_box;

		$this->meta_box['fields'] = static::normalize_fields( $meta_box['fields'], $this->get_storage() );

		$this->meta_box = apply_filters( 'rwmb_meta_box_settings', $this->meta_box );

		if ( $this->is_shown() ) {
			$this->global_hooks();
			$this->object_hooks();
		}
	}

	public function register_fields() {
		$field_registry = rwmb_get_registry( 'field' );

		foreach ( $this->post_types as $post_type ) {
			foreach ( $this->fields as $field ) {
				$field_registry->add( $field, $post_type );
			}
		}
	}

	public function is_shown() : bool {
		$show = apply_filters( 'rwmb_show', true, $this->meta_box );
		return apply_filters( "rwmb_show_{$this->id}", $show, $this->meta_box );
	}

	protected function global_hooks() {
		// Enqueue common styles and scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );

		// Enqueue assets for the block editor only, just for previewing (submission forms, custom blocks).
		// Don't enqueue on frontend as front-end forms and blocks already call the enqueue() method.
		// TODO: Uncomment this when we have a way to enqueue assets for the block/site editor.
		// if ( is_admin() ) {
		// 	add_action( 'enqueue_block_assets', [ $this, 'enqueue' ] );
		// }

		// Add additional actions for fields.
		foreach ( $this->fields as $field ) {
			RWMB_Field::call( $field, 'add_actions' );
		}
	}

	/**
	 * Specific hooks for meta box object. Default is 'post'.
	 * This should be extended in subclasses to support meta fields for terms, user, settings pages, etc.
	 */
	protected function object_hooks() {
		// Add meta box.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		// Hide meta box if it's set 'default_hidden'.
		add_filter( 'default_hidden_meta_boxes', [ $this, 'hide' ], 10, 2 );

		// Save post meta.
		foreach ( $this->post_types as $post_type ) {
			if ( 'attachment' === $post_type ) {
				// Attachment uses other hooks.
				// @see wp_update_post(), wp_insert_attachment().
				add_action( 'edit_attachment', [ $this, 'save_post' ] );
				add_action( 'add_attachment', [ $this, 'save_post' ] );
			} else {
				add_action( "save_post_{$post_type}", [ $this, 'save_post' ] );
			}
		}
	}

	public function enqueue() {
		if ( is_admin() && ! $this->is_edit_screen() && ! $this->is_gutenberg_screen() ) {
			return;
		}

		wp_enqueue_style( 'rwmb', RWMB_CSS_URL . 'style.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb', 'path', RWMB_CSS_DIR . 'style.css' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'rwmb-rtl', RWMB_CSS_URL . 'style-rtl.css', [], RWMB_VER );
			wp_style_add_data( 'rwmb-rtl', 'path', RWMB_CSS_DIR . 'style-rtl.css' );
		}

		wp_enqueue_script( 'rwmb', RWMB_JS_URL . 'script.js', [ 'jquery' ], RWMB_VER, true );

		// Load clone script conditionally.
		foreach ( $this->fields as $field ) {
			if ( $field['clone'] ) {
				wp_enqueue_script( 'rwmb-clone', RWMB_JS_URL . 'clone.js', [ 'jquery-ui-sortable' ], RWMB_VER, true );
				break;
			}
		}

		// Enqueue scripts and styles for fields.
		foreach ( $this->fields as $field ) {
			RWMB_Field::call( $field, 'admin_enqueue_scripts' );
		}

		// Auto save.
		if ( $this->autosave ) {
			wp_enqueue_script( 'rwmb-autosave', RWMB_JS_URL . 'autosave.js', [ 'jquery' ], RWMB_VER, true );
		}

		/**
		 * Allow developers to enqueue more scripts and styles
		 *
		 * @param RW_Meta_Box $object Meta Box object
		 */
		do_action( 'rwmb_enqueue_scripts', $this );
	}

	private function is_gutenberg_screen() : bool {
		$screen = get_current_screen();

		return in_array( $screen->base, [ 'site-editor', 'widgets' ] );
	}

	/**
	 * Add meta box for multiple post types
	 */
	public function add_meta_boxes() {
		$screen = get_current_screen();
		add_filter( "postbox_classes_{$screen->id}_{$this->id}", [ $this, 'postbox_classes' ] );

		foreach ( $this->post_types as $post_type ) {
			add_meta_box(
				$this->id,
				$this->title,
				[ $this, 'show' ],
				$post_type,
				$this->context,
				$this->priority
			);
		}
	}

	public function postbox_classes( array $classes ) : array {
		if ( $this->closed ) {
			$classes[] = 'closed';
		}
		$classes[] = "rwmb-{$this->style}";

		return $classes;
	}

	public function hide( array $hidden, $screen ) : array {
		if ( $this->is_edit_screen( $screen ) && $this->default_hidden ) {
			$hidden[] = $this->id;
		}

		return $hidden;
	}

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

		$this->render_cleanup();
		// Allow users to add custom code after meta box content.
		// 1st action applies to all meta boxes.
		// 2nd action applies to only current meta box.
		do_action( 'rwmb_after', $this );
		do_action( "rwmb_after_{$this->id}", $this );

		// End container.
		echo '</div>';
	}

	protected function get_cleanup_fields( $fields, $prefix = '' ) {
		$names = [];

		foreach ( $fields as $field ) {
			$field_id = $prefix . $field['id'];
			if ( ! empty( $field['fields'] ) ) {
				$suffix = $field[ 'clone' ] ? '.*.' : '.';
				$names = array_merge( $names, $this->get_cleanup_fields( $field['fields'], $field_id . $suffix ) );
			}

			if ( $field['clone'] ) {
				$names[] = $field_id;
			}
		}

		return $names;
	}

	protected function render_cleanup() {
		$names = $this->get_cleanup_fields( $this->fields );

		echo '<input type="hidden" name="rwmb_cleanup[]" value="' . esc_attr( wp_json_encode( $names ) ) . '">';
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

		array_map( [ $this, 'save_field' ], $this->fields );

		// After save action.
		do_action( 'rwmb_after_save_post', $object_id );
		do_action( "rwmb_{$this->id}_after_save_post", $object_id );
	}

	public function save_field( array $field ) {
		$single  = $field['clone'] || ! $field['multiple'];
		$default = $single ? '' : [];
		$old     = RWMB_Field::call( $field, 'raw_meta', $this->object_id );
		$new     = rwmb_request()->post( $field['id'], $default );
		$new     = RWMB_Field::process_value( $new, $this->object_id, $field );

		// Filter to allow the field to be modified.
		$field = RWMB_Field::filter( 'field', $field, $field, $new, $old );

		// Call defined method to save meta value, if there's no methods, call common one.
		RWMB_Field::call( $field, 'save', $new, $old, $this->object_id );

		RWMB_Field::filter( 'after_save_field', null, $field, $new, $old, $this->object_id );
	}

	public function validate() : bool {
		$nonce = rwmb_request()->filter_post( "nonce_{$this->id}" );

		return ! $this->saved
			&& ( ! defined( 'DOING_AUTOSAVE' ) || $this->autosave )
			&& wp_verify_nonce( $nonce, "rwmb-save-{$this->id}" );
	}

	public static function normalize( $meta_box ) {
		$default_title = __( 'Meta Box Title', 'meta-box' );
		$meta_box      = wp_parse_args( $meta_box, [
			'title'          => $default_title,
			'id'             => ! empty( $meta_box['title'] ) ? sanitize_title( $meta_box['title'] ) : sanitize_title( $default_title ),
			'context'        => 'normal',
			'priority'       => 'high',
			'post_types'     => 'post',
			'autosave'       => false,
			'default_hidden' => false,
			'style'          => 'default',
			'class'          => '',
			'fields'         => [],
		] );

		/**
		 * Use 'post_types' for better understanding and fallback to 'pages' for previous versions.
		 * @since 4.4.1
		 */
		Arr::change_key( $meta_box, 'pages', 'post_types' );

		// Make sure the post type is an array and is sanitized.
		$meta_box['post_types'] = array_filter( array_map( 'sanitize_key', Arr::from_csv( $meta_box['post_types'] ) ) );

		return $meta_box;
	}

	public static function normalize_fields( array $fields, $storage = null ) : array {
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
	 * This helps to save empty value in meta fields (text, check box, etc.) and set the correct default values.
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
				|| ( ! $single && is_array( $value ) && [] !== $value )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if we're on the right edit screen.
	 *
	 * @param ?WP_Screen $screen Screen object.
	 */
	public function is_edit_screen( $screen = null ) {
		if ( ! ( $screen instanceof WP_Screen ) ) {
			$screen = get_current_screen();
		}

		return in_array( $screen->base, [ 'post', 'upload' ], true ) && in_array( $screen->post_type, $this->post_types, true );
	}

	public function __get( string $key ) {
		return $this->meta_box[ $key ] ?? false;
	}

	/**
	 * Set the object ID.
	 *
	 * @param mixed $id Object ID.
	 */
	public function set_object_id( $id = null ) {
		$this->object_id = $id;
	}

	public function get_object_type() : string {
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

		return $parent ?: $object_id;
	}
}

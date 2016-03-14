<?php
/**
 * Media field class which users WordPress media popup to upload and select files.
 */
class RWMB_Media_Field extends RWMB_Field
{
	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	static function admin_enqueue_scripts()
	{
		wp_enqueue_media();
		wp_enqueue_style( 'rwmb-media', RWMB_CSS_URL . 'media.css', array(), RWMB_VER );
		wp_enqueue_script( 'rwmb-media', RWMB_JS_URL . 'media.js', array( 'jquery-ui-sortable', 'underscore', 'backbone' ), RWMB_VER, true );
		wp_localize_script( 'rwmb-media', 'i18nRwmbMedia', array(
			'add'                => apply_filters( 'rwmb_media_add_string', _x( '+ Add Media', 'media', 'meta-box' ) ),
			'single'             => apply_filters( 'rwmb_media_single_files_string', _x( ' file', 'media', 'meta-box' ) ),
			'multiple'           => apply_filters( 'rwmb_media_multiple_files_string', _x( ' files', 'media', 'meta-box' ) ),
			'remove'             => apply_filters( 'rwmb_media_remove_string', _x( 'Remove', 'media', 'meta-box' ) ),
			'edit'               => apply_filters( 'rwmb_media_edit_string', _x( 'Edit', 'media', 'meta-box' ) ),
			'view'               => apply_filters( 'rwmb_media_view_string', _x( 'View', 'media', 'meta-box' ) ),
			'noTitle'            => _x( 'No Title', 'media', 'meta-box' ),
			'loadingUrl'         => RWMB_URL . 'img/loader.gif',
			'extensions'         => self::get_mime_extensions(),
			'select'             => _x( 'Select Files', 'media', 'meta-box' ),
			'uploadInstructions' => _x( 'Drop files here to upload', 'media', 'meta-box' )
		) );
	}

	/**
	 * Add actions
	 *
	 * @return void
	 */
	static function add_actions()
	{
		// Print attachment templates
		add_action( 'print_media_templates', array( __CLASS__, 'print_templates' ) );
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 *
	 * @return string
	 */
	static function html( $meta, $field )
	{
		$meta       = (array) $meta;
		$meta       = implode( ',', $meta );
		$attributes = self::get_attributes( $field, $meta );

		$html = sprintf(
			'<input %s>
			<div class="rwmb-media-view" data-mime-type="%s" data-max-files="%s" data-force-delete="%s"></div>',
			self::render_attributes( $attributes ),
			$field['mime_type'],
			$field['max_file_uploads'],
			$field['force_delete'] ? 'true' : 'false'
		);

		return $html;
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'std'              => array(),
			'mime_type'        => '',
			'max_file_uploads' => 0,
			'force_delete'     => false,
		) );

		$field['multiple'] = true;

		return $field;
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes             = parent::get_attributes( $field, $value );
		$attributes['type']     = 'hidden';
		$attributes['name']    .= ! $field['clone'] && $field['multiple'] ? '[]' : '';
		$attributes['disabled'] = true;
		$attributes['id']       = false;
		$attributes['value']    = $value;

		return $attributes;
	}

	static function get_mime_extensions()
	{
		$mime_types = wp_get_mime_types();
		$extensions = array();
		foreach( $mime_types as $ext => $mime )
		{
			$ext = explode( '|', $ext );
			$extensions[ $mime ] = $ext;

			$mime_parts = explode( '/', $mime );
			if( empty( $extensions[ $mime_parts[0] ] ) )
				$extensions[ $mime_parts[0] ] = array();
			$extensions[ $mime_parts[0] ] = $extensions[ $mime_parts[0] . '/*' ] = array_merge( $extensions[ $mime_parts[0] ], $ext );

		}

		return $extensions;
	}

	/**
	 * Save meta value
	 *
	 * @param $new
	 * @param $old
	 * @param $post_id
	 * @param $field
	 */
	static function save( $new, $old, $post_id, $field )
	{
		delete_post_meta( $post_id, $field['id'] );
		parent::save( $new, array(), $post_id, $field );
	}

	/**
	 * Template for media item
	 * @return void
	 */
	static function print_templates()
	{
		require_once( RWMB_INC_DIR . 'templates/media.php' );
	}
}

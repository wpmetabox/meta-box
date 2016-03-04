<?php
/**
 * File advanced field class which users WordPress media popup to upload and select files.
 */
class RWMB_File_Advanced_Field extends RWMB_Media_Field
{
	/**
	 * Add actions
	 *
	 * @return void
	 */
	static function add_actions()
	{
		parent::add_actions();
		// Print attachment templates
		add_action( 'print_media_templates', array( __CLASS__, 'print_templates' ) );
	}

	/**
	 * Get the field value.
	 * @param array $field
	 * @param array $args
	 * @param null  $post_id
	 * @return mixed
	 */
	static function get_value( $field, $args = array(), $post_id = null )
	{
		return RWMB_File_Field::get_value( $field, $args, $post_id );
	}

	/**
	 * Output the field value.
	 * @param array $field
	 * @param array $args
	 * @param null  $post_id
	 * @return mixed
	 */
	static function the_value( $field, $args = array(), $post_id = null )
	{
		return RWMB_File_Field::the_value( $field, $args, $post_id );
	}

	/**
	 * Get uploaded file information.
	 *
	 * @param int   $file_id Attachment image ID (post ID). Required.
	 * @param array $args    Array of arguments (for size).
	 * @return array|bool False if file not found. Array of image info on success
	 */
	static function file_info( $file_id, $args = array() )
	{
		return RWMB_File_Field::file_info( $file_id, $args );
	}

	static function print_templates()
	{
		$i18n_remove         = apply_filters( 'rwmb_media_remove_string', _x( 'Remove', 'media', 'meta-box' ) );
		$i18n_edit           = apply_filters( 'rwmb_media_edit_string', _x( 'Edit', 'media', 'meta-box' ) );
		$i18n_view           = apply_filters( 'rwmb_media_view_string', _x( 'View', 'media', 'meta-box' ) );
		$i18n_title          = _x( 'No Title', 'media', 'meta-box' );

		?>
		<script id="tmpl-rwmb-media-item" type="text/html">
			<input type="hidden" name="{{{ data.fieldName }}}" value="{{{ data.id }}}" class="rwmb-media-input">
			<div class="rwmb-media-preview">
				<div class="rwmb-media-content">
					<div class="centered">
						<# if ( 'image' === data.type && data.sizes ) { #>
							<# if ( data.sizes.thumbnail ) { #>
								<img src="{{{ data.sizes.thumbnail.url }}}">
							<# } else { #>
								<img src="{{{ data.sizes.full.url }}}">
							<# } #>
						<# } else { #>
							<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
								<img src="{{ data.image.src }}" />
							<# } else { #>
								<img src="{{ data.icon }}" />
							<# } #>
						<# } #>
					</div>
				</div>
			</div>
			<div class="rwmb-media-info">
				<h4>
					<a href="{{{ data.url }}}" target="_blank" title="<?php echo esc_attr( $i18n_view ); ?>">
						<# if( data.title ) { #> {{{ data.title }}}
							<# } else { #> <?php echo esc_attr( $i18n_title ); ?>
						<# } #>
					</a>
				</h4>
				<p>{{{ data.mime }}}</p>
				<p>
					<a class="rwmb-edit-media" title="<?php echo esc_attr( $i18n_edit ); ?>" href="{{{ data.editLink }}}" target="_blank">
						<span class="dashicons dashicons-edit"></span><?php echo esc_attr( $i18n_edit ); ?>
					</a>
					<a href="#" class="rwmb-remove-media" title="<?php echo esc_attr( $i18n_remove ); ?>">
						<span class="dashicons dashicons-no-alt"></span><?php echo esc_attr( $i18n_remove ); ?>
					</a>
				</p>
			</div>
		</script>
		<?php
	}
}

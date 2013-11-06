<?php
if ( !class_exists( 'RWMB_File_Input_Field' ) )
{
	class RWMB_File_Input_Field extends RWMB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			// Make sure scripts for new media uploader in WordPress 3.5 is enqueued
			wp_enqueue_media();
			wp_enqueue_script( 'rwmb-file-input', RWMB_JS_URL . 'file-input.js', array( 'jquery' ), RWMB_VER, true );
			wp_localize_script( 'rwmb-file-input', 'rwmbFileInput', array(
				'frameTitle' => __( 'Select File', 'rwmb' ),
			) );
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			return sprintf(
				'<input type="text" class="rwmb-file-input" name="%s" id="%s" value="%s" placeholder="%s" size="%s">
				<a href="#" class="rwmb-file-input-select button-primary">%s</a>
				<a href="#" class="rwmb-file-input-remove button %s">%s</a>',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['placeholder'],
				$field['size'],
				__( 'Select', 'rwmb' ),
				$meta ? '' : 'hidden',
				__( 'Remove', 'rwmb' )
			);
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'size'        => 30,
				'placeholder' => '',
			) );
			return $field;
		}
	}
}
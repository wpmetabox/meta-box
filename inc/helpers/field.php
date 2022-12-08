<?php
/**
 * Field helper functions.
 */
class RWMB_Helpers_Field {
	/**
	 * Localize a script only once.
	 * @link https://github.com/rilwis/meta-box/issues/850
	 */
	public static function localize_script_once( string $handle, string $name, array $data ) {
		if ( ! wp_scripts()->get_data( $handle, 'data' ) ) {
			wp_localize_script( $handle, $name, $data );
		}
	}

	public static function add_inline_script_once( string $handle, string $text ) {
		if ( ! wp_scripts()->get_data( $handle, 'after' ) ) {
			wp_add_inline_script( $handle, $text );
		}
	}

	public static function get_class( $field ) : string {
		$type  = self::get_type( $field );
		$class = 'RWMB_' . RWMB_Helpers_String::title_case( $type ) . '_Field';
		return class_exists( $class ) ? $class : 'RWMB_Input_Field';
	}

	private static function get_type( $field ) : string {
		$type = $field['type'] ?? 'text';
		$map  = array_merge(
			[
				$type => $type,
			],
			[
				'file_advanced'  => 'media',
				'plupload_image' => 'image_upload',
				'url'            => 'text',
			]
		);

		return $map[ $type ];
	}
}

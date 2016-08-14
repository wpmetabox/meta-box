<?php

/**
 * WPML compatibility class
 */
class RWMB_WPML {

    /**
     * Register hooks.
     */
    public function __construct() {
        add_filter( 'wpml_duplicate_generic_string', array( $this, 'wpml_translate_values' ), 10, 3 );
    }

    /**
     * Translating IDs stored as field values upon WPML post/page duplication.
     *
     * @param $value
     * @param $target_language
     * @param $meta_data
     * @return mixed
     */
    public function wpml_translate_values( $value, $target_language, $meta_data ) {
        $fields = RWMB_Core::get_fields();

        foreach ( $fields as $field ) {
            if ( in_array( $field['type'], array( 'post', 'taxonomy_advanced' ) ) && $field['id'] === $meta_data['key'] ) {
                // Post type needed for WPML filter differs between fields
                $post_type = $field['type'] === 'taxonomy_advanced' ? $field['taxonomy'] : $field['post_type'];

                // Translating values, whether are stored as comma separated strings or not.
                if ( ( strpos( $value, ',' ) === false ) ) {
                    $value = apply_filters( 'wpml_object_id', $value, $post_type, true, $target_language );
                }
                else {
                    // Dealing with IDs stored as comma separated strings
                    $translated_values = array();
                    $values            = explode( ',', $value );

                    foreach ( $values as $v ) {
                        $translated_values[] = apply_filters( 'wpml_object_id', $v, $post_type, true, $target_language );
                    }

                    $value = implode( ',', $translated_values );
                }
            }
        }

        return $value;
    }
}
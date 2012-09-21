<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Number_Field' ) )
{
    class RWMB_Number_Field
    {
        /**
         * Get field HTML
         *
         * @param string $html
         * @param mixed  $meta
         * @param array  $field
         *
         * @return string
         */
        static function html( $html, $meta, $field )
        {
            $name  = " name='{$field['field_name']}'";
            $id    = isset( $field['clone'] ) && $field['clone'] ? '' : " id='{$field['id']}'";
            $value = " value='{$meta}'";
            $step  = " step='{$field['step']}'";
            $min   = " min='{$field['min']}'";

            $html .= "<input type='number' class='rwmb-number'{$name}{$id}{$value}{$step}{$min} />";

            return $html;
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
            if ( empty( $field['step'] ) )
                $field['step'] = 1;
            if ( empty( $field['min'] ) )
                $field['min'] = 0;
            return $field;
        }
    }
}
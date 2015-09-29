<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

require_once RWMB_FIELDS_DIR . 'media.php';
if ( ! class_exists( 'RWMB_File_Advanced_Field' ) )
{
	class RWMB_File_Advanced_Field extends RWMB_Media_Field {}
}

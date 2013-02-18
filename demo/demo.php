<?php
/**
 * Registering meta boxes
 *
 * All the definitions of meta boxes are listed below with comments.
 * Please read them CAREFULLY.
 *
 * You also should read the changelog to know what has been changed before updating.
 *
 * For more information, please visit:
 * @link http://www.deluxeblogtips.com/meta-box/
 */

/********************* META BOX DEFINITIONS ***********************/

/**
 * Prefix of meta keys (optional)
 * Use underscore (_) at the beginning to make keys hidden
 * Alt.: You also can make prefix empty to disable it
 */
// Better has an underscore as last sign
$prefix = 'YOUR_PREFIX_';

global $meta_boxes;

$meta_boxes = array();

// 1st meta box
$meta_boxes[] = array(
	// Meta box id, UNIQUE per meta box. Optional since 4.1.5
	'id' => 'standard',

	// Meta box title - Will appear at the drag and drop handle bar. Required.
	'title' => 'Standard Fields',

	// Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
	'pages' => array( 'post', 'page' ),

	// Where the meta box appear: normal (default), advanced, side. Optional.
	'context' => 'normal',

	// Order of meta box: high (default), low. Optional.
	'priority' => 'high',

	// List of meta fields
	'fields' => array(
		// TEXT
		array(
			// Field name - Will be used as label
			'name'  => 'Text',
			// Field ID, i.e. the meta key
			'id'    => "{$prefix}text",
			// Field description (optional)
			'desc'  => 'Text description',
			'type'  => 'text',
			// Default value (optional)
			'std'   => 'Default text value',
			// CLONES: Add to make the field cloneable (i.e. have multiple value)
			'clone' => true,
		),
		// CHECKBOX
		array(
			'name' => 'Checkbox',
			'id'   => "{$prefix}checkbox",
			'type' => 'checkbox',
			// Value can be 0 or 1
			'std'  => 1,
		),
		// RADIO BUTTONS
		array(
			'name'    => 'Radio',
			'id'      => "{$prefix}radio",
			'type'    => 'radio',
			// Array of 'value' => 'Label' pairs for radio options.
			// Note: the 'value' is stored in meta field, not the 'Label'
			'options' => array(
				'value1' => 'Label1',
				'value2' => 'Label2',
			),
		),
		// SELECT BOX
		array(
			'name'     => 'Select',
			'id'       => "{$prefix}select",
			'type'     => 'select',
			// Array of 'value' => 'Label' pairs for select box
			'options'  => array(
				'value1' => 'Label1',
				'value2' => 'Label2',
			),
			// Select multiple values, optional. Default is false.
			'multiple' => false,
		),
		// HIDDEN
		array(
			'id'   => "{$prefix}hidden",
			'type' => 'hidden',
			// Hidden field must have predefined value
			'std'  => 'Hidden value',
		),
		// PASSWORD
		array(
			'name' => 'Password',
			'id'   => "{$prefix}password",
			'type' => 'password',
		),
		// TEXTAREA
		array(
			'name' => 'Textarea',
			'desc' => 'Textarea description',
			'id'   => "{$prefix}textarea",
			'type' => 'textarea',
			'cols' => '20',
			'rows' => '3',
		),
	),
	'validation' => array(
		'rules' => array(
			"{$prefix}password" => array(
				'required'  => true,
				'minlength' => 7,
			),
		),
		// optional override of default jquery.validate messages
		'messages' => array(
			"{$prefix}password" => array(
				'required'  => 'Password is required',
				'minlength' => 'Password must be at least 7 characters',
			),
		)
	)
);

// 2nd meta box
$meta_boxes[] = array(
	'title' => 'Advanced Fields',

	'fields' => array(
		// NUMBER
		array(
			'name' => 'Number',
			'id'   => "{$prefix}number",
			'type' => 'number',

			'min'  => 0,
			'step' => 5,
		),
		// DATE
		array(
			'name' => 'Date picker',
			'id'   => "{$prefix}date",
			'type' => 'date',

			// jQuery date picker options. See here http://jqueryui.com/demos/datepicker
			'js_options' => array(
				'appendText'      => '(yyyy-mm-dd)',
				'dateFormat'      => 'yy-mm-dd',
				'changeMonth'     => true,
				'changeYear'      => true,
				'showButtonPanel' => true,
			),
		),
		// DATETIME
		array(
			'name' => 'Datetime picker',
			'id'   => $prefix . 'datetime',
			'type' => 'datetime',

			// jQuery datetime picker options. See here http://trentrichardson.com/examples/timepicker/
			'js_options' => array(
				'stepMinute'     => 15,
				'showTimepicker' => true,
			),
		),
		// TIME
		array(
			'name' => 'Time picker',
			'id'   => $prefix . 'time',
			'type' => 'time',

			// jQuery datetime picker options. See here http://trentrichardson.com/examples/timepicker/
			'js_options' => array(
				'stepMinute' => 5,
				'showSecond' => true,
				'stepSecond' => 10,
			),
		),
		// COLOR
		array(
			'name' => 'Color picker',
			'id'   => "{$prefix}color",
			'type' => 'color',
		),
		// CHECKBOX LIST
		array(
			'name' => 'Checkbox list',
			'id'   => "{$prefix}checkbox_list",
			'type' => 'checkbox_list',
			// Options of checkboxes, in format 'value' => 'Label'
			'options' => array(
				'value1' => 'Label1',
				'value2' => 'Label2',
			),
		),
		// TAXONOMY
		array(
			'name'    => 'Taxonomy',
			'id'      => "{$prefix}taxonomy",
			'type'    => 'taxonomy',
			'options' => array(
				// Taxonomy name
				'taxonomy' => 'category',
				// How to show taxonomy: 'checkbox_list' (default) or 'checkbox_tree', 'select_tree' or 'select'. Optional
				'type' => 'select_tree',
				// Additional arguments for get_terms() function. Optional
				'args' => array()
			),
		),
		// WYSIWYG/RICH TEXT EDITOR
		array(
			'name' => 'WYSIWYG / Rich Text Editor',
			'id'   => "{$prefix}wysiwyg",
			'type' => 'wysiwyg',
			'std'  => 'WYSIWYG default value',

			// Editor settings, see wp_editor() function: look4wp.com/wp_editor
			'options' => array(
				'textarea_rows' => 4,
				'teeny'         => true,
				'media_buttons' => false,
			),
		),
		// FILE UPLOAD
		array(
			'name' => 'File Upload',
			'id'   => "{$prefix}file",
			'type' => 'file',
		),
		// FILE UPLOAD Advanced
		array(
			'name' => 'File Upload',
			'id'   => "{$prefix}file_advanced",
			'type' => 'file_advanced',
			'max_file_uploads' => 4,
			'mime_type' => 'application,audio,video'		//Leave blank for all file types
		),
		// IMAGE UPLOAD
		array(
			'name' => 'Image Upload',
			'id'   => "{$prefix}image",
			'type' => 'image',
		),
		// THICKBOX IMAGE UPLOAD (WP 3.3+)
		array(
			'name' => 'Thichbox Image Upload',
			'id'   => "{$prefix}thickbox",
			'type' => 'thickbox_image',
		),
		// PLUPLOAD IMAGE UPLOAD (WP 3.3+)
		array(
			'name'             => 'Plupload Image Upload',
			'id'               => "{$prefix}plupload",
			'type'             => 'plupload_image',
			'max_file_uploads' => 4,
		),
		// IMAGE ADVANCED (WP 3.5+)
		array(
			'name'             => 'Image Advanced Upload',
			'id'               => "{$prefix}imgadv",
			'type'             => 'image_advanced',
			'max_file_uploads' => 4,
		),
	)
);

/********************* META BOX REGISTERING ***********************/

/**
 * Register meta boxes
 *
 * @return void
 */
function YOUR_PREFIX_register_meta_boxes()
{
	// Make sure there's no errors when the plugin is deactivated or during upgrade
	if ( !class_exists( 'RW_Meta_Box' ) )
		return;

	global $meta_boxes;
	foreach ( $meta_boxes as $meta_box )
	{
		new RW_Meta_Box( $meta_box );
	}
}
// Hook to 'admin_init' to make sure the meta box class is loaded before
// (in case using the meta box class in another plugin)
// This is also helpful for some conditionals like checking page template, categories, etc.
add_action( 'admin_init', 'YOUR_PREFIX_register_meta_boxes' );

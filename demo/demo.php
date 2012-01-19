<?php
/**
 * Registering meta boxes
 *
 * In this file, I'll show you how to add more field type (in this case, the 'taxonomy' type)
 * All the definitions of meta boxes are listed below with comments, please read them CAREFULLY
 *
 * You also should read the changelog to know what has been changed
 *
 * For more information, please visit: http://www.deluxeblogtips.com/2010/04/how-to-create-meta-box-wordpress-post.html
 */

/**
 * Add field type: 'taxonomy'
 *
 * NOTE: The class name must be in format "RWMB_{$field_type}_Field"
 */
if ( ! class_exists( 'RWMB_Taxonomy_Field' ) )
{
	class RWMB_Taxonomy_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_print_styles()
		{
			wp_enqueue_style( 'rwmb-taxonomy', RWMB_CSS_URL.'taxonomy.css', RWMB_VER );
			wp_enqueue_script( 'rwmb-taxonomy', RWMB_JS_URL.'taxonomy.js', array( 'jquery', 'wp-ajax-response' ), RWMB_VER, true );
		}

		/**
		 * Add default value for 'taxonomy' field
		 *
		 * @param $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			// Default query arguments for get_terms() function
			$default_args = array(
				'hide_empty' => false
			);

			if ( ! isset( $field['options']['args'] ) )
				$field['options']['args'] = $default_args;
			else
				$field['options']['args'] = wp_parse_args( $field['options']['args'], $default_args );

			// Show field as checkbox list by default
			if ( ! isset( $field['options']['type'] ) )
				$field['options']['type'] = 'checkbox_list';

			// If field is shown as checkbox list, add multiple value
			if ( 'checkbox_list' == $field['options']['type'] || 'checkbox_tree' == $field['options']['type'] )
			{
				$field['multiple'] = true;
				$field['field_name'] = "{$field['field_name']}[]"; 
			}

			if ( 
				'checkbox_tree' === $field['options']['type'] 
				&& !isset( $field['options']['args']['parent'] ) 
			)
				$field['options']['args']['parent'] = 0;

			return $field;
		}

		/**
		 * Get field HTML
		 *
		 * @param $html
		 * @param $field
		 * @param $meta
		 *
		 * @return string
		 */
		static function html( $html, $meta, $field )
		{
			global $post;

			$options = $field['options'];

			$terms = get_terms( $options['taxonomy'], $options['args'] );

			$html = '';
			// Checkbox LIST
			if ( 'checkbox_list' === $options['type'] )
			{
				foreach ( $terms as $term )
				{
					$checked = checked( in_array( $term->slug, $meta ), true, false );
					$html .= "<input type='checkbox' name='{$field['field_name']}' value='{$term->slug}'{$checked} /> {$term->name}<br/>";
				}
			}
			// Checkbox TREE
			elseif ( 'checkbox_tree' === $options['type'] )
			{
				$html .= self::walk_checkbox_tree($meta, $field, true);
			}
			// Select
			else
			{
				$multiple = $field['multiple'] ? " multiple='multiple' style='height: auto;'" : "'";
				$html .= "<select name='{$field['field_name']}'{$multiple}>";
				foreach ( $terms as $term )
				{
					$selected = selected( in_array( $term->slug, $meta ), true, false );
					$html .= "<option value='{$term->slug}'{$selected}>{$term->name}</option>";
				}
				$html .= "</select>";
			}

			return $html;
		}

		/**
		 * Walker for displaying checkboxes in treeformat
		 *
		 * @param $meta
		 * @param $field
		 * @param bool $active
		 *
		 * @return string
		 */
		static function walk_checkbox_tree( $meta, $field, $active = false )
		{
			$options	= $field['options'];
			$terms		= get_terms( $options['taxonomy'], $options['args'] );
			$html		= '';
			$hidden		= ! $active ? ' hidden' : '';

			if ( 0 < count( $terms ) )
			{
				$html = "<ul class='rw-taxonomy-tree{$hidden}'>";
				foreach ( $terms as $term )
				{
					$field['options']['args']['parent'] = $term->term_id;

					$checked	= checked( in_array( $term->slug, $meta ), true, false );
					$disabled	= disabled( $active, false, false );

					$html .= "<li><input type='checkbox' name='{$field['field_name']}' value='{$term->slug}'{$checked}{$disabled} /> {$term->name}";
					$html .= self::walk_checkbox_tree( $meta, $field, ( in_array( $term->slug, $meta ) ) );
					$html .= "</li>";
				}
				$html .= "</ul>";
			}
			return $html;
		}

		/**
		 * Save post taxonomy
		 * @param $post_id
		 * @param $field
		 * @param $old
		 * @param $new
		 */
		static function save( $new, $old, $post_id, $field )
		{
			wp_set_object_terms( $post_id, $new, $field['options']['taxonomy'] );
		}
		
		/**
		 * Standard meta retrieval
		 *
		 * @param mixed 	$meta
		 * @param int		$post_id
		 * @param array  	$field
		 * @param bool  	$saved
		 *
		 * @return mixed
		 */
		static function meta( $meta, $post_id, $saved, $field )
		{
			
			$options	= $field['options'];
			$meta		= wp_get_post_terms( $post_id, $options['taxonomy'] );
			$meta		= is_array( $meta ) ? $meta : (array) $meta;
			$meta		= wp_list_pluck( $meta, 'slug' );
			return $meta;
		}
	}
}

/********************* META BOXES DEFINITION ***********************/

/**
 * Prefix of meta keys (optional)
 * Wse underscore (_) at the beginning to make keys hidden
 * You also can make prefix empty to disable it
 */
// Better has an underscore as last sign
$prefix = 'YOUR_PREFIX_';

global $meta_boxes;

$meta_boxes = array();

// 1st meta box
$meta_boxes[] = array(
	// Meta box id, UNIQUE per meta box
	'id' => 'personal',

	// Meta box title - Will appear at the drag and drop handle bar
	'title' => 'Personal Information',

	// Post types, accept custom post types as well - DEFAULT is array('post'); (optional)
	'pages' => array( 'post', 'slider' ),

	// Where the meta box appear: normal (default), advanced, side; optional
	'context' => 'normal',

	// Order of meta box: high (default), low; optional
	'priority' => 'high',

	// List of meta fields
	'fields' => array(
		array(
			// Field name - Will be used as label
			'name'		=> 'Full name',
			// Field ID, i.e. the meta key
			'id'		=> $prefix . 'fname',
			// Field description (optional)
			'desc'		=> 'Format: First Last',
			// CLONES: Add to make the field cloneable (i.e. have multiple value)
			'clone'		=> true,
			'type'		=> 'text',
			// Default value (optional)
			'std'		=> 'Anh Tran'
		),
		array(
			'name'		=> 'Day of Birth',
			'id'		=> "{$prefix}dob",
			'type'		=> 'date',
			// Date format, default yy-mm-dd. Optional. See: http://goo.gl/po8vf
			'format'	=> 'd MM, yy'
		),
		// RADIO BUTTONS
		array(
			'name'		=> 'Gender',
			'id'		=> "{$prefix}gender",
			'type'		=> 'radio',
			// Array of 'key' => 'value' pairs for radio options.
			// Note: the 'key' is stored in meta field, not the 'value'
			'options'	=> array(
				'm'			=> 'Male',
				'f'			=> 'Female'
			),
			'std'		=> 'm',
			'desc'		=> 'Need an explaination?'
		),
		// TEXTAREA
		array(
			'name'		=> 'Bio',
			'desc'		=> "What's your professions? What have you done so far?",
			'id'		=> "{$prefix}bio",
			'type'		=> 'textarea',
			'std'		=> "I'm a special agent from Vietnam.",
			'cols'		=> "40",
			'rows'		=> "8"
		),
		// File type: select box
		array(
			'name'		=> 'Where do you live?',
			'id'		=> "{$prefix}place",
			'type'		=> 'select',
			// Array of 'key' => 'value' pairs for select box
			'options'	=> array(
				'usa'		=> 'USA',
				'vn'		=> 'Vietnam'
			),
			// Select multiple values, optional. Default is false.
			'multiple'	=> true,
			// Default value, can be string (single value) or array (for both single and multiple values)
			'std'		=> array( 'vn' ),
			'desc'		=> 'Select the current place, not in the past'
		),
		array(
			'name'		=> 'About WordPress',    // File type: checkbox
			'id'		=> "{$prefix}love_wp",
			'type'		=> 'checkbox',
			'desc'		=> 'I love WordPress',
			// Value can be 0 or 1
			'std'		=> 1
		),
		// HIDDEN
		array(
			'id'		=> "{$prefix}invisible",
			'type'		=> 'hidden',
			// Hidden field must have predefined value
			'std'		=> "no, i'm visible"
		),
		// PASSWORD
		array(
			'name'		=> 'Your favorite password',
			'id'		=> "{$prefix}pass",
			'type'		=> 'password'
		),
		// TAXONOMY
		array(
			'name'		=> 'Categories',
			'id'		=> "{$prefix}cats",
			'type'		=> 'taxonomy',
			'options'	=> array(
				// Taxonomy name
				'taxonomy'	=> 'category',
				// How to show taxonomy: 'checkbox_list' (default) or 'select'. Optional
				'type'		=> 'checkbox_list',
				// Additional arguments for get_terms() function
				'args'		=> array()
			),
			'desc'		=> 'Choose One Category'
		)
	)
);

// 2nd meta box
$meta_boxes[] = array(
	'id'		=> 'additional',
	'title'		=> 'Additional Information',
	'pages'		=> array( 'post', 'film', 'slider' ),

	'fields'	=> array(
		// WYSIWYG/RICH TEXT EDITOR
		array(
			'name'	=> 'Your thoughts about Deluxe Blog Tips',
			'id'	=> "{$prefix}thoughts",
			'type'	=> 'wysiwyg',
			'std'	=> sprintf( "%1$sIt's great!", '<b>', '</b>' ),
			'desc'	=> 'Do you think so?'
		),
		// FILE UPLOAD
		array(
			'name'	=> 'Upload your source code',
			'desc'	=> 'Any modified code, or extending code',
			'id'	=> "{$prefix}code",
			'type'	=> 'file'
		),
		// IMAGE UPLOAD
		array(
			'name'	=> 'Screenshots',
			'desc'	=> 'Screenshots of problems, warnings, etc.',
			'id'	=> "{$prefix}screenshot",
			'type'	=> 'image'
		),
		// NEW(!) PLUPLOAD IMAGE UPLOAD (WP 3.3+)
		array(
			'name'	=> 'Screenshots (plupload)',
			'desc'	=> 'Screenshots of problems, warnings, etc.',
			'id'	=> "{$prefix}screenshot2",
			'type'	=> 'plupload_image'
		)
	)
);

// 3rd meta box
$meta_boxes[] = array(
	'id'		=> 'survey',
	'title'		=> 'Survey',
	'pages'		=> array( 'post', 'slider', 'page' ),

	'fields'	=> array(
		// COLOR
		array(
			'name'		=> 'Your favorite color',
			'id'		=> "{$prefix}color",
			'type'		=> 'color'
		),
		// CHECKBOX LIST
		array(
			'name'		=> 'Your hobby',
			'id'		=> "{$prefix}hobby",
			'type'		=> 'checkbox_list',
			// Options of checkboxes, in format 'key' => 'value'
			'options'	=> array(
				'reading'	=> 'Books',
				'sport'		=> 'Gym, Boxing'
			),
			'desc'		=> 'What do you do in free time?'
		),
		// TIME
		array(
			'name'		=> 'When do you get up?',
			'id'		=> "{$prefix}getdown",
			'type'		=> 'time',
			// Time format, default hh:mm. Optional. @link See: http://goo.gl/hXHWz
			'format'	=> 'hh:mm:ss'
		),
		// DATETIME
		array(
			'name'		=> 'When were you born?',
			'id'		=> "{$prefix}born_time",
			'type'		=> 'datetime',
			// Time format, default hh:mm. Optional. @link See: http://goo.gl/hXHWz
			'format'	=> 'hh:mm:ss'
		)
	)
);
// Hook to 'admin_init' to make sure the meta box class is loaded
//  before (in case using the meta box class in another plugin)
// This is also helpful for some conditionals like checking page template, categories, etc.
add_action( 'admin_init', 'YOUR_PREFIX_register_meta_boxes' );


/**
 * Register meta boxes
 *
 * @return void
 */
function YOUR_PREFIX_register_meta_boxes()
{
	global $meta_boxes;

	// Make sure there's no errors when the plugin is deactivated or during upgrade
	if ( class_exists( 'RW_Meta_Box' ) )
	{
		foreach ( $meta_boxes as $meta_box )
		{
			new RW_Meta_Box( $meta_box );
		}
	}
}
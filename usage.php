<?php
/**
 * Registering meta boxes
 *
 * In this file, I'll show you how to extend the class to add more field type (in this case, the 'taxonomy' type)
 * All the definitions of meta boxes are listed below with comments, please read them carefully.
 * Note that each validation method of the Validation Class MUST return value instead of boolean as before
 *
 * You also should read the changelog to know what has been changed
 *
 * For more information, please visit: http://www.deluxeblogtips.com/2010/04/how-to-create-meta-box-wordpress-post.html
 *
 */

/********************* BEGIN EXTENDING CLASS ***********************/

/**
 * Extend RW_Meta_Box class
 * Add field type: 'taxonomy'
 */
class RW_Meta_Box_Taxonomy extends RW_Meta_Box {

	static function normalize( $meta_box ) {
		$meta_box = parent::normalize( $meta_box );

		// add 'multiple' option to taxonomy field with checkbox_list type
		foreach ( $meta_box['fields'] as $key => $field ) {
			if ( 'taxonomy' == $field['type'] && 'checkbox_list' == $field['options']['type'] ) {
				$meta_box['fields'][$key]['multiple'] = true;
			}
		}

		return $meta_box;
	}

	// show taxonomy list
	function show_field_taxonomy( $field, $meta ) {
		global $post;

		if ( !is_array( $meta ) )
			$meta = (array) $meta;

		$this->show_field_begin( $field, $meta );

		$options = $field['options'];
		$terms = get_terms( $options['taxonomy'], $options['args'] );

		// checkbox_list
		if ( 'checkbox_list' == $options['type'] ) {
			foreach ( $terms as $term ) {
				echo "<input type='checkbox' name='{$field['id']}[]' value='$term->slug'" . checked( in_array( $term->slug, $meta ), true, false ) . " /> $term->name<br/>";
			}
		}
			// select
		else {
			echo "<select name='{$field['id']}" . ( $field['multiple'] ? "[]' multiple='multiple' style='height:auto'"
				: "'" ) . ">";

			foreach ( $terms as $term ) {
				echo "<option value='$term->slug'" . selected( in_array( $term->slug, $meta ), true, false ) . ">$term->name</option>";
			}
			echo "</select>";
		}

		$this->show_field_end( $field, $meta );
	}
}

/********************* END EXTENDING CLASS ***********************/

/********************* BEGIN DEFINITION OF META BOXES ***********************/

// prefix of meta keys, optional
// use underscore (_) at the beginning to make keys hidden, for example $prefix = '_rw_';
// you also can make prefix empty to disable it
$prefix = 'rw_';

$meta_boxes = array( );

// first meta box
$meta_boxes[] = array(
	'id' => 'personal', // meta box id, unique per meta box
	'title' => 'Personal Information', // meta box title
	'pages' => array( 'post', 'page', 'slider' ), // post types, accept custom post types as well, default is array('post'); optional
	'context' => 'normal', // where the meta box appear: normal (default), advanced, side; optional
	'priority' => 'high', // order of meta box: high (default), low; optional

	'fields' => array( // list of meta fields
		array(
			'name' => 'Full name', // field name
			'desc' => 'Format: Firstname Lastname', // field description, optional
			'id' => $prefix . 'fname', // field id, i.e. the meta key
			'type' => 'text', // text box
			'std' => 'Anh Tran', // default value, optional
			'style' => 'width: 100px', // custom style for field, added in v3.1
			'validate_func' => 'check_name' // validate function, created below, inside RW_Meta_Box_Validate class
		),
		array(
			'name' => 'DOB',
			'id' => $prefix . 'dob',
			'type' => 'date', // date
			'format' => 'd MM, yy' // date format, default yy-mm-dd. Optional. See more formats here: http://goo.gl/po8vf
		),
		array(
			'name' => 'Gender',
			'id' => $prefix . 'gender',
			'type' => 'radio', // radio box
			'options' => array( // array of key => value pairs for radio options
				'm' => 'Male',
				'f' => 'Female'
			),
			'std' => 'm',
			'desc' => 'Need an explaination?'
		),
		array(
			'name' => 'Bio',
			'desc' => 'What\'s your professions? What you\'ve done?',
			'id' => $prefix . 'bio',
			'type' => 'textarea', // textarea
			'std' => 'I\'m a WP developer and a freelancer from Vietnam.',
			'style' => 'width: 200px; height: 100px'
		),
		array(
			'name' => 'Where do you live?',
			'id' => $prefix . 'place',
			'type' => 'select', // select box
			'options' => array( // array of key => value pairs for select box
				'usa' => 'USA',
				'vn' => 'Vietnam'
			),
			'multiple' => true, // select multiple values, optional. Default is false.
			'std' => array( 'vn' ), // default value, can be string (single value) or array (for both single and multiple values)
			'desc' => 'Select the current place, not in the past'
		),
		array(
			'name' => 'About WordPress', // checkbox
			'id' => $prefix . 'love_wp',
			'type' => 'checkbox',
			'desc' => 'I love WordPress'
		),
		/*
		array(
			'name' => 'Categories',
			'id' => $prefix . 'cats',
			'type' => 'taxonomy', // taxonomy
			'options' => array(
				'taxonomy' => 'category', // taxonomy name
				'type' => 'select', // how to show taxonomy? 'select' (default) or 'checkbox_list'
				'args' => array( ) // arguments to query taxonomy, see http://goo.gl/795Vm
			),
			'desc' => 'Choose One Category'
		)
		*/
	)
);

// second meta box
$meta_boxes[] = array(
	'id' => 'additional',
	'title' => 'Additional Information',
	'pages' => array( 'post', 'film', 'slider' ),

	'fields' => array(
		array(
			'name' => 'Your thoughts about Deluxe Blog Tips',
			'id' => $prefix . 'thoughts',
			'type' => 'wysiwyg', // WYSIWYG editor
			'std' => '<b>It\'s great!</b>',
			'desc' => 'Do you think so?',
			'style' => 'width: 300px; height: 400px'
		),
		array(
			'name' => 'Upload your source code',
			'desc' => 'Any modified code, or extending code',
			'id' => $prefix . 'code',
			'type' => 'file' // file upload
		),
		array(
			'name' => 'Screenshots',
			'desc' => 'Screenshots of problems, warnings, etc.',
			'id' => $prefix . 'screenshot',
			'type' => 'image' // image upload
		),
		array(
			'name' => 'When do you get up?',
			'id' => $prefix . 'getdown',
			'type' => 'time', // time
			'format' => 'hh:mm:ss' // time format, default hh:mm. Optional. See more formats here: http://goo.gl/hXHWz
		)
	)
);

$meta_boxes[] = array(
	'id' => 'survey',
	'title' => 'Survey',
	'pages' => array( 'post', 'slider', 'page' ),

	'fields' => array(
		array(
			'name' => 'Your favorite color',
			'id' => $prefix . 'color',
			'type' => 'color' // color
		),
		array(
			'name' => 'Your hobby',
			'id' => $prefix . 'hobby',
			'type' => 'checkbox_list',           // checkbox list
			'options' => array(                  // options of checkbox, in format key => value
				'reading' => 'Books, Magazines', // remember to call: $checkbox_list = get_post_meta(get_the_ID(), 'meta_name', false);
				'sport' => 'Gym, Boxing'         // and use: if (in_array('reading', $checkbox_list) {// do stuff}
			),
			'desc' => 'What do you do in free time?'
		),
		array(
			'name' => 'When do you get up?',
			'id' => $prefix . 'getup',
			'type' => 'time', // time
			'format' => 'hh:mm:ss' // time format, default hh:mm. Optional. See more formats here: http://goo.gl/hXHWz
		)
	)
);

foreach ( $meta_boxes as $meta_box ) {
	new RW_Meta_Box( $meta_box );
}

/********************* END DEFINITION OF META BOXES ***********************/

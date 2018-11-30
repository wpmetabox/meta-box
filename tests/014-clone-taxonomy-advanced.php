<?php
add_filter( 'rwmb_meta_boxes', function( $meta_boxes ) {
	$meta_boxes[] = [
		'title' => 'No clone',
		'fields' => [
			[
				'type' => 'taxonomy_advanced',
				'id'   => 'ta1',
				'name' => 'TA1',
			],
			[
				'type'     => 'taxonomy_advanced',
				'id'       => 'ta2',
				'name'     => 'TA2 - Multiple',
				'multiple' => true,
			]
		],
	];
	$meta_boxes[] = [
		'title' => 'Clone',
		'fields' => [
			[
				'type'              => 'taxonomy_advanced',
				'id'                => 'ta3',
				'name'              => 'TA3 - Clone',
				'clone'             => true,
				'multiple'          => false,
				'clone_as_multiple' => false,
			],
			[
				'type'              => 'taxonomy_advanced',
				'id'                => 'ta4',
				'name'              => 'TA4 - Clone + Clone as multiple',
				'clone'             => true,
				'multiple'          => false,
				'clone_as_multiple' => true,
			],
			[
				'type'              => 'taxonomy_advanced',
				'id'                => 'ta5',
				'name'              => 'TA5 - Clone + Multiple',
				'clone'             => true,
				'multiple'          => true,
				'clone_as_multiple' => false,
			],
			[
				'type'              => 'taxonomy_advanced',
				'id'                => 'ta6',
				'name'              => 'TA6 - Clone + Multiple + Clone as multiple',
				'clone'             => true,
				'multiple'          => true,
				'clone_as_multiple' => true,
			],
		],
	];
	return $meta_boxes;
} );

add_filter( 'the_content', function( $content ) {
	if ( ! is_single() ) {
		return $content;
	}
	ob_start();
	$fields = range( 1, 6 );
	foreach ( $fields as $field ) {
		$field = "ta$field";
		echo "<h1>Field $field</h1>";

		echo '<h3>rwmb_meta()</h3>';
		$value = rwmb_meta( $field );
		echo '<pre>';
		print_r( $value );
		echo '</pre>';

		echo '<h3>rwmb_get_value()</h3>';
		$value = rwmb_get_value( $field );
		echo '<pre>';
		print_r( $value );
		echo '</pre>';

		echo '<h3>rwmb_the_value()</h3>';
		rwmb_the_value( $field );
	}

	return $content . ob_get_clean();
} );
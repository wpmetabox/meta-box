<?php
/**
 * This file test the helper function rwmb_meta to make sure it return correct value
 * Meta boxes and fields are taken from demo/demo.php
 *
 * Steps:
 * 1. Add this line to 'functions.php' of the theme
 *    include WP_PLUGIN_DIR . '/meta-box/tests/helper.php';
 * 2. Uncomment one of the test cases below
 * 3. Go to Dashboard -> Add New Post -> Enter value for fields
 * 4. Go to the frontend and check if values are displayed correctly
 */

// 1: Register meta boxes on both frontend and backend
//new RWMB_Test_Helper( 'all' );

// 2: Register meta boxes on the backend only
new RWMB_Test_Helper( 'admin' );

/**
 * Test helper class.
 */
class RWMB_Test_Helper
{
	/**
	 * Field ID prefix in the demo/demo.php file
	 * @var string
	 */
	public $prefix = 'your_prefix_';

	/**
	 * List of field IDs in the demo/demo.php file
	 * @var array
	 */
	public $keys;

	/**
	 * Test on both frontend and admin area (all) or admin area only (admin)
	 * @var string
	 */
	public $type;

	/**
	 * Constructor.
	 * @param string $type Test type
	 */
	public function __construct( $type = 'all' )
	{
		$this->type = $type;

		$this->keys = [
			'text',
			'checkbox',
			'radio',
			'select',
			'hidden',
			'password',
			'textarea',
			'slider',
			'number',
			'date',
			'datetime',
			'time',
			'color',
			'checkbox_list',
			'autocomplete',
			'email',
			'range',
			'url',
			'oembed',
			'select_advanced',
			'taxonomy',
			'pages',
			'wysiwyg',
			'file',
			'file_advanced',
			'image',
			'thickbox',
			'plupload',
			'imgadv',
		];
		foreach ( $this->keys as $k => $v )
		{
			$this->keys[$k] = $this->prefix . $v;
		}

		// Run the test
		$this->include_demo();
		add_filter( 'the_content', [ $this, 'the_content' ] );
	}

	/**
	 * Include demo/demo.php file for test
	 */
	public function include_demo()
	{
		if ( 'all' == $this->type )
		{
			include WP_PLUGIN_DIR . '/meta-box/demo/demo.php';
		}
		elseif ( 'admin' == $this->type )
		{
			if ( is_admin() )
			{
				include WP_PLUGIN_DIR . '/meta-box/demo/demo.php';
			}
		}
	}

	/**
	 * Show meta value in the frontend
	 * @param $content
	 * @return string
	 */
	public function the_content( $content )
	{
		$content .= '<h2>Testing <code>rwmb_meta</code></h2>';
		$content .= '<table>
		<tr>
			<th>Key</th>
			<th>Value</th>
		</tr>';
		foreach ( $this->keys as $key )
		{
			$content .= '<tr><td>' . $key . '</td><td>';
			switch ( $this->type )
			{
				case 'all':
					$content .= $this->meta_all( $key );
					break;
				case 'admin':
					$content .= $this->meta_admin( $key );
					break;
			}
			$content .= '</td></tr>';
		}
		$content .= '</table>';
		return $content;
	}

	/**
	 * Display meta value when meta boxes are registered in both frontend and backend
	 * In this case `rwmb_get_value()` and `rwmb_the_value()` will be called
	 * @param $key
	 * @return string
	 */
	public function meta_all( $key )
	{
		$meta = rwmb_meta( $key );
		return is_string( $meta ) ? $meta : '<pre>' . print_r( $meta, true ) . '</pre>';
	}

	/**
	 * Display meta value when meta boxes are registered in backend only
	 * In this case `RWMB_Helper::meta` will be called
	 * @param $key
	 * @return string
	 */
	public function meta_admin( $key )
	{
		switch ( $key )
		{
			case $this->prefix . 'text':
				$meta = rwmb_meta( $key, 'clone=true' );
				$meta = '<pre>' . print_r( $meta, true ) . '</pre>';
				break;
			case $this->prefix . 'checkbox_list':
				$meta = rwmb_meta( $key, 'type=checkbox_list' );
				$meta = '<pre>' . print_r( $meta, true ) . '</pre>';
				break;
			case $this->prefix . 'autocomplete':
				$meta = rwmb_meta( $key, 'type=autocomplete' );
				$meta = '<pre>' . print_r( $meta, true ) . '</pre>';
				break;
			case $this->prefix . 'taxonomy':
				$meta = rwmb_meta( $key, 'type=taxonomy&taxonomy=category' );
				$meta = '<pre>' . print_r( $meta, true ) . '</pre>';
				break;
			case $this->prefix . 'oembed':
				$meta = rwmb_meta( $key, 'type=oembed' );
				break;
			case $this->prefix . 'file':
			case $this->prefix . 'file_advanced':
				$meta = rwmb_meta( $key, 'type=file' );
				$meta = '<pre>' . print_r( $meta, true ) . '</pre>';
				break;
			case $this->prefix . 'image':
			case $this->prefix . 'thickbox':
			case $this->prefix . 'plupload':
			case $this->prefix . 'imgadv':
				$meta = rwmb_meta( $key, 'type=image' );
				$meta = '<pre>' . print_r( $meta, true ) . '</pre>';
				break;
			default:
				$meta = rwmb_meta( $key );
				break;
		}
		return $meta;
	}
}

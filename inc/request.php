<?php
/**
 * A very simple request class that handles form inputs.
 * Based on the code of Symphony framework, (c) Fabien Potencier <fabien@symfony.com>
 *
 * @link https://github.com/laravel/framework/blob/6.x/src/Illuminate/Http/Request.php
 * @link https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/HttpFoundation/ParameterBag.php
 *
 * @package Meta Box
 */

/**
 * A very simple request class that handles form inputs.
 *
 * @package Meta Box
 */
class RWMB_Request {
	/**
	 * GET data.
	 *
	 * @var array
	 */
	private $get_data = array();

	/**
	 * POST data.
	 *
	 * @var array
	 */
	private $post_data = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @codingStandardsIgnoreLine
		$this->get_data  = $_GET;
		// @codingStandardsIgnoreLine
		$this->post_data = $_POST;
	}

	/**
	 * Set GET data.
	 *
	 * @param array $get_data Data.
	 */
	public function set_get_data( $get_data ) {
		$this->get_data = array_merge( $this->get_data, $get_data );
	}

	/**
	 * Set POST data.
	 *
	 * @param array $post_data Data.
	 */
	public function set_post_data( $post_data ) {
		$this->post_data = array_merge( $this->post_data, $post_data );
	}

	/**
	 * Return a GET parameter by name.
	 *
	 * @param  string $name    Parameter name.
	 * @param  mixed  $default Default value.
	 * @return mixed
	 */
	public function get( $name, $default = null ) {
		return isset( $this->get_data[ $name ] ) ? $this->get_data[ $name ] : $default;
	}

	/**
	 * Return a POST parameter by name.
	 *
	 * @param  string $name    Parameter name.
	 * @param  mixed  $default Default value.
	 * @return mixed
	 */
	public function post( $name, $default = null ) {
		return isset( $this->post_data[ $name ] ) ? $this->post_data[ $name ] : $default;
	}

	/**
	 * Filter a GET parameter.
	 *
	 * @param string $name    Parameter name.
	 * @param int    $filter  FILTER_* constant.
	 * @param mixed  $options Filter options.
	 *
	 * @return mixed
	 */
	public function filter_get( $name, $filter = FILTER_DEFAULT, $options = array() ) {
		$value = $this->get( $name );
		return filter_var( $value, $filter, $options );
	}

	/**
	 * Filter a POST parameter.
	 *
	 * @param string $name    Parameter name.
	 * @param int    $filter  FILTER_* constant.
	 * @param mixed  $options Filter options.
	 *
	 * @return mixed
	 */
	public function filter_post( $name, $filter = FILTER_DEFAULT, $options = array() ) {
		$value = $this->post( $name );
		return filter_var( $value, $filter, $options );
	}
}

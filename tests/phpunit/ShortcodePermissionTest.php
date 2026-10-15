<?php
use PHPUnit\Framework\TestCase;

/**
 * Permission checks for the [rwmb_meta] shortcode.
 */
class ShortcodePermissionTest extends TestCase {
	private $shortcode;

	protected function setUp(): void {
		parent::setUp();

		if ( ! class_exists( 'RWMB_Shortcode' ) ) {
			$this->markTestSkipped( 'RWMB_Shortcode is not available.' );
		}

		$this->shortcode = new RWMB_Shortcode();
	}

	/**
	 * @param string $method Method name.
	 * @param mixed  ...$args Method arguments.
	 * @return mixed
	 */
	private function invoke( string $method, ...$args ) {
		$reflection = new ReflectionMethod( RWMB_Shortcode::class, $method );
		$reflection->setAccessible( true );
		return $reflection->invoke( $this->shortcode, ...$args );
	}

	public function testUnknownObjectTypeIsDenied(): void {
		$this->assertFalse( $this->invoke( 'user_can_read_object', 'custom_type', 1 ) );
	}

	public function testNetworkSettingUsesSettingsPageCheck(): void {
		$this->assertFalse( $this->invoke( 'user_can_read_object', 'network_setting', 'missing_option' ) );
	}

	public function testPermissionFilterReceivesFieldId(): void {
		if ( ! function_exists( 'add_filter' ) || ! function_exists( 'remove_all_filters' ) ) {
			$this->markTestSkipped( 'WordPress filters are not available.' );
		}

		$received = null;
		add_filter( 'rwmb_meta_shortcode_permission', function ( $allowed, $object_type, $object_id, $atts, $field_id ) use ( &$received ) {
			$received = [ $allowed, $object_type, $object_id, $atts, $field_id ];
			return false;
		}, 10, 5 );

		$result = $this->invoke( 'check_object_permission', 'secret_note', 1, [ 'object_type' => 'user' ] );

		$this->assertFalse( $result );
		$this->assertNotNull( $received );
		$this->assertSame( 'user', $received[1] );
		$this->assertSame( 1, $received[2] );
		$this->assertSame( 'secret_note', $received[4] );
		$this->assertArrayNotHasKey( 'id', $received[3] );

		remove_all_filters( 'rwmb_meta_shortcode_permission' );
	}

	public function testDeniedPermissionReturnsEmptyString(): void {
		if ( ! function_exists( 'add_filter' ) || ! function_exists( 'wp_kses_post' ) ) {
			$this->markTestSkipped( 'WordPress functions are not available.' );
		}

		add_filter( 'rwmb_meta_shortcode_permission', '__return_false' );

		$value = $this->shortcode->register_shortcode( [
			'id'                => 'secret_note',
			'object_id'         => 1,
			'object_type'       => 'user',
			'render_shortcodes' => 'false',
		] );

		$this->assertSame( '', $value );
		remove_all_filters( 'rwmb_meta_shortcode_permission' );
	}

	public function testAnonymousCannotReadUserMetaViaShortcode(): void {
		if ( ! function_exists( 'wp_set_current_user' ) || ! function_exists( 'update_user_meta' ) || ! function_exists( 'rwmb_get_registry' ) ) {
			$this->markTestSkipped( 'WordPress user APIs are not available.' );
		}

		$user_id = self::create_user( 'administrator' );
		if ( ! $user_id ) {
			$this->markTestSkipped( 'Failed to create user.' );
		}

		update_user_meta( $user_id, 'secret_note', 'top-secret' );
		rwmb_get_registry( 'field' )->add( [
			'id'   => 'secret_note',
			'type' => 'text',
		], 'user', 'user' );

		wp_set_current_user( 0 );

		$value = $this->shortcode->register_shortcode( [
			'id'                => 'secret_note',
			'object_id'         => $user_id,
			'object_type'       => 'user',
			'render_shortcodes' => 'false',
		] );

		$this->assertSame( '', $value );

		self::delete_user( $user_id );
	}

	public function testMissingObjectIdSkipsPermissionCheck(): void {
		if ( ! function_exists( 'add_filter' ) || ! function_exists( 'rwmb_the_value' ) || ! function_exists( 'remove_all_filters' ) ) {
			$this->markTestSkipped( 'WordPress helpers are not available.' );
		}

		add_filter( 'rwmb_meta_shortcode_permission', function () {
			$this->fail( 'Permission check must not run when object_id is missing.' );
		} );

		// object_type=user without object_id falls back to post and skips the permission gate.
		$this->invoke( 'get_value', 'any_field', null, [
			'object_type' => 'user',
			'attribute'   => '',
		] );

		remove_all_filters( 'rwmb_meta_shortcode_permission' );
		$this->addToAssertionCount( 1 );
	}

	public function testSettingUsesPageCapability(): void {
		if ( ! function_exists( 'add_filter' ) || ! function_exists( 'wp_set_current_user' ) ) {
			$this->markTestSkipped( 'WordPress APIs are not available.' );
		}

		add_filter( 'mb_settings_pages', function ( $pages ) {
			$pages[] = [
				'id'          => 'theme-slug',
				'option_name' => 'theme_slug',
				'capability'  => 'manage_options',
			];
			return $pages;
		} );

		$shortcode  = new RWMB_Shortcode();
		$reflection = new ReflectionMethod( RWMB_Shortcode::class, 'user_can_read_setting' );
		$reflection->setAccessible( true );

		wp_set_current_user( 0 );
		$this->assertFalse( $reflection->invoke( $shortcode, 'theme_slug' ) );

		$admin_id = self::create_user( 'administrator' );
		if ( ! $admin_id ) {
			$this->markTestSkipped( 'Failed to create administrator.' );
		}

		wp_set_current_user( $admin_id );
		$this->assertTrue( $reflection->invoke( $shortcode, 'theme_slug' ) );
		self::delete_user( $admin_id );
	}

	private static function create_user( string $role ) {
		if ( ! function_exists( 'wp_insert_user' ) ) {
			return 0;
		}

		$user_id = wp_insert_user( [
			'user_login' => 'shortcode_perm_' . uniqid(),
			'user_email' => 'shortcode_perm_' . uniqid() . '@example.com',
			'user_pass'  => 'password',
			'role'       => $role,
		] );

		return is_wp_error( $user_id ) ? 0 : $user_id;
	}

	private static function delete_user( int $user_id ): void {
		if ( ! function_exists( 'wp_delete_user' ) && defined( 'ABSPATH' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		if ( function_exists( 'wp_delete_user' ) ) {
			wp_delete_user( $user_id );
		}
	}
}

<?php
use PHPUnit\Framework\TestCase;

/**
 * Abilities API integration tests.
 *
 * Runs against a live WordPress install (see tests/bootstrap.php).
 * Requires WordPress 6.9+ for the Abilities API; otherwise skips.
 *
 * Note: abilities must be registered during the `wp_abilities_api_init` action,
 * so the test re-fires that action after wiring the class.
 */
class AbilitiesTest extends TestCase {
	private $user_id;
	private $post_id;
	private $abilities;
	private $filter_added = false;

	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active.' );
		}

		if ( ! function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'Abilities API not available (WordPress < 6.9).' );
		}

		// Register a field so rwmb_get_field_settings() can resolve it.
		if ( ! $this->filter_added ) {
			add_filter( 'rwmb_meta_boxes', [ $this, 'register_test_meta_box' ] );
			$this->filter_added = true;
		}

		$this->abilities = new \MetaBox\Abilities\Abilities();
		$this->abilities->init();

		// Fields register on `init` priority 20; the category/abilities register
		// on their own dedicated actions. Drive both so the test has a populated registry.
		do_action( 'init' );
		do_action( 'wp_abilities_api_categories_init' );
		do_action( 'wp_abilities_api_init' );

		$this->user_id = self::create_editor();
		if ( ! $this->user_id ) {
			$this->markTestSkipped( 'Failed to create test user.' );
		}

		$this->post_id = wp_insert_post( [
			'post_author'  => $this->user_id,
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => 'Abilities Test Post',
			'post_content' => '',
		] );
		if ( is_wp_error( $this->post_id ) ) {
			$this->markTestSkipped( 'Failed to create test post: ' . $this->post_id->get_error_message() );
		}
	}

	protected function tearDown(): void {
		if ( $this->post_id && get_post( $this->post_id ) ) {
			wp_delete_post( $this->post_id, true );
		}
		if ( $this->user_id && get_user_by( 'id', $this->user_id ) ) {
			wp_delete_user( $this->user_id );
		}
		parent::tearDown();
	}

	private static function create_editor() {
		$user_id = wp_insert_user( [
			'user_login' => 'abilities_test_' . uniqid(),
			'user_email' => 'abilities_test_' . uniqid() . '@example.com',
			'user_pass'  => 'password',
			'role'       => 'editor',
		] );
		return is_wp_error( $user_id ) ? 0 : $user_id;
	}

	public function register_test_meta_box( $meta_boxes ) {
		$meta_boxes[] = [
			'title'      => 'Test',
			'post_types' => 'post',
			'fields'     => [
				[
					'id'   => 'test_field',
					'type' => 'text',
					'name' => 'Test Field',
				],
			],
		];
		return $meta_boxes;
	}

	public function testCategoryRegistered() {
		$this->assertNotNull( wp_get_ability_category( 'meta-box' ) );
	}

	public function testAbilitiesRegistered() {
		$this->assertNotNull( wp_get_ability( 'meta-box/get-field-value' ) );
		$this->assertNotNull( wp_get_ability( 'meta-box/update-field-value' ) );
		$this->assertNotNull( wp_get_ability( 'meta-box/delete-field-value' ) );
	}

	public function testPermissionDeniedForLoggedOutUser() {
		wp_set_current_user( 0 );

		$input = [
			'field_id'    => 'test_field',
			'object_id'   => $this->post_id,
			'object_type' => 'post',
		];

		$get = wp_get_ability( 'meta-box/get-field-value' );
		$this->assertFalse( $get->check_permissions( $input ) );

		$update = wp_get_ability( 'meta-box/update-field-value' );
		$this->assertFalse( $update->check_permissions( $input ) );

		$delete = wp_get_ability( 'meta-box/delete-field-value' );
		$this->assertFalse( $delete->check_permissions( $input ) );
	}

	public function testPermissionDeniedForUnregisteredField() {
		wp_set_current_user( $this->user_id );

		$input = [
			'field_id'    => 'does_not_exist',
			'object_id'   => $this->post_id,
			'object_type' => 'post',
		];

		$get = wp_get_ability( 'meta-box/get-field-value' );
		$this->assertFalse( $get->check_permissions( $input ) );
	}

	public function testPermissionGrantedForPostAuthor() {
		wp_set_current_user( $this->user_id );

		$input = [
			'field_id'    => 'test_field',
			'object_id'   => $this->post_id,
			'object_type' => 'post',
		];

		$get = wp_get_ability( 'meta-box/get-field-value' );
		$this->assertTrue( $get->check_permissions( $input ) );

		$update = wp_get_ability( 'meta-box/update-field-value' );
		$this->assertTrue( $update->check_permissions( $input ) );
	}

	public function testUpdateGetDeleteRoundTrip() {
		wp_set_current_user( $this->user_id );

		$input = [
			'field_id'    => 'test_field',
			'object_id'   => $this->post_id,
			'object_type' => 'post',
		];

		// Update (also creates).
		$update = wp_get_ability( 'meta-box/update-field-value' );
		$result = $update->execute( $input + [ 'value' => 'hello' ] );
		$this->assertTrue( $result['success'] );

		// Get.
		$get    = wp_get_ability( 'meta-box/get-field-value' );
		$result = $get->execute( $input );
		$this->assertSame( 'hello', $result['value'] );

		// Delete.
		$delete = wp_get_ability( 'meta-box/delete-field-value' );
		$result = $delete->execute( $input );
		$this->assertTrue( $result['success'] );

		// Confirm gone.
		$result = $get->execute( $input );
		$this->assertEmpty( $result['value'] );
	}
}

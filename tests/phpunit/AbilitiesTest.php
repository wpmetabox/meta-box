<?php

/**
 * Abilities API integration tests.
 *
 * Requires WordPress 6.9+ for the Abilities API; otherwise skips.
 */
class AbilitiesTest extends WP_UnitTestCase {
	private $user_id;
	private $post_id;

	public function set_up(): void {
		parent::set_up();

		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active.' );
		}

		if ( ! function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'Abilities API not available (WordPress < 6.9).' );
		}

		// The plugin has registered its abilities; register the test field the way Meta Box does on `init`.
		add_filter( 'rwmb_meta_boxes', [ $this, 'register_test_meta_box' ] );
		( new RWMB_Core() )->register_meta_boxes();

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

	public function tear_down(): void {
		if ( $this->post_id && get_post( $this->post_id ) ) {
			wp_delete_post( $this->post_id, true );
		}
		if ( $this->user_id && get_user_by( 'id', $this->user_id ) ) {
			wp_delete_user( $this->user_id );
		}
		parent::tear_down();
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

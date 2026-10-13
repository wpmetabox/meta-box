<?php
use MetaBox\Integrations\BlockBindings\Source;
use PHPUnit\Framework\TestCase;
use WP_Block;

/**
 * Exposes format_value for regression tests.
 */
class BlockBindings_Source_Test_Double extends Source {
	protected function name(): string {
		return 'meta-box/test-field';
	}

	protected function label(): string {
		return 'Test';
	}

	protected function contexts(): array {
		return [];
	}

	protected function object_type(): string {
		return 'post';
	}

	protected function context_key(): string {
		return '';
	}

	protected function get_object_id( array $source_args, WP_Block $block_instance ) {
		return 1;
	}

	protected function get_type( array $source_args, WP_Block $block_instance ): string {
		return 'post';
	}

	protected function has_permission( $object_id, WP_Block $block_instance ): bool {
		return true;
	}

	protected function get_fields(): array {
		return [];
	}

	public function expose_format_value( $value, array $field, string $key ) {
		return $this->format_value( $value, $field, $key );
	}

	public function expose_get_value( array $source_args, WP_Block $block_instance, string $attribute_name ) {
		return $this->get_value( $source_args, $block_instance, $attribute_name );
	}
}

class BlockBindingsSourceTest extends TestCase {
	private BlockBindings_Source_Test_Double $source;

	protected function setUp(): void {
		parent::setUp();
		if ( ! class_exists( Source::class ) || ! function_exists( 'get_userdata' ) ) {
			$this->markTestSkipped( 'Block bindings source or WordPress user APIs unavailable' );
		}
		$this->source = new BlockBindings_Source_Test_Double();
	}

	public function testUserFieldRejectsUserPassKey(): void {
		$user_id = self::ensure_test_user();
		$user    = get_userdata( $user_id );
		$this->assertNotEmpty( $user->user_pass );

		$field = [
			'type'     => 'user',
			'id'       => 'bb_user',
			'name'     => 'User',
			'clone'    => false,
			'multiple' => false,
		];

		$result = $this->source->expose_format_value( $user_id, $field, 'user_pass' );

		$this->assertNull( $result, 'user_pass must not render via block bindings' );
	}

	public function testUserFieldRejectsUserEmailKey(): void {
		$user_id = self::ensure_test_user();
		$field   = [
			'type'     => 'user',
			'id'       => 'bb_user',
			'name'     => 'User',
			'clone'    => false,
			'multiple' => false,
		];

		$result = $this->source->expose_format_value( $user_id, $field, 'user_email' );

		$this->assertNull( $result, 'user_email must not render via block bindings' );
	}

	public function testUserFieldAllowsDisplayName(): void {
		$user_id = self::ensure_test_user();
		$user    = get_userdata( $user_id );
		$field   = [
			'type'     => 'user',
			'id'       => 'bb_user',
			'name'     => 'User',
			'clone'    => false,
			'multiple' => false,
		];

		$result = $this->source->expose_format_value( $user_id, $field, 'display_name' );

		$this->assertSame( $user->display_name, $result );
	}

	public function testPostFieldRejectsPostPasswordKey(): void {
		$post_id = self::ensure_test_post();
		$field   = [
			'type'     => 'post',
			'id'       => 'bb_post',
			'name'     => 'Post',
			'clone'    => false,
			'multiple' => false,
		];

		$result = $this->source->expose_format_value( $post_id, $field, 'post_password' );

		$this->assertNull( $result, 'post_password must not render via block bindings' );
		wp_delete_post( $post_id, true );
	}

	public function testArrayValueRejectsUnknownKey(): void {
		$field = [
			'type'     => 'single_image',
			'id'       => 'bb_image',
			'name'     => 'Image',
			'clone'    => false,
			'multiple' => false,
		];
		$value = [
			'url'      => 'https://example.com/a.jpg',
			'full_url' => 'https://example.com/a.jpg',
			'alt'      => 'Alt',
			'secret'   => 'INTERNAL',
		];

		$result = $this->source->expose_format_value( $value, $field, 'secret' );

		$this->assertNull( $result, 'non-allowlisted array keys must not render' );
	}

	public function testArrayValueAllowsUrlKey(): void {
		$field = [
			'type'     => 'single_image',
			'id'       => 'bb_image',
			'name'     => 'Image',
			'clone'    => false,
			'multiple' => false,
		];
		$value = [
			'url'      => 'https://example.com/a.jpg',
			'full_url' => 'https://example.com/full.jpg',
			'alt'      => 'Alt',
		];

		$result = $this->source->expose_format_value( $value, $field, 'url' );

		$this->assertSame( 'https://example.com/full.jpg', $result );
	}

	private static function ensure_test_user(): int {
		$login = 'bb_bindings_test_user';
		$user  = get_user_by( 'login', $login );
		if ( $user ) {
			return (int) $user->ID;
		}

		$id = wp_insert_user( [
			'user_login'   => $login,
			'user_pass'    => wp_generate_password( 24 ),
			'user_email'   => 'bb-bindings-test@example.com',
			'display_name' => 'BB Bindings Test',
			'role'         => 'subscriber',
		] );
		if ( is_wp_error( $id ) ) {
			self::fail( $id->get_error_message() );
		}
		return (int) $id;
	}

	private static function ensure_test_post(): int {
		$id = wp_insert_post( [
			'post_title'    => 'BB Bindings Private',
			'post_status'   => 'private',
			'post_content'  => 'SECRET_BODY',
			'post_password' => 'pw-secret',
			'post_type'     => 'post',
		], true );
		if ( is_wp_error( $id ) ) {
			self::fail( $id->get_error_message() );
		}
		return (int) $id;
	}
}

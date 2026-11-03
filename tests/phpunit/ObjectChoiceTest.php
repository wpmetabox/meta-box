<?php
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Exposes set_ajax_params() and get_ajax_field() for tests without reflection.
 */
class RWMB_Object_Choice_Ajax_Test_Field extends RWMB_Object_Choice_Field {
	public static function query( $meta, array $field ): array {
		return [];
	}

	public static function apply_ajax_params( array &$field ): void {
		parent::set_ajax_params( $field );
	}

	public static function get_field(): array {
		return parent::get_ajax_field();
	}
}

class ObjectChoiceTest extends TestCase {
	/**
	 * Type → default ajax action map in set_ajax_params().
	 * Includes taxonomy_advanced as its own map key (not via normalize inheritance).
	 */
	public static function defaultAjaxActionProvider(): array {
		return [
			'post'              => [ 'post', 'rwmb_get_posts' ],
			'user'              => [ 'user', 'rwmb_get_users' ],
			'taxonomy'          => [ 'taxonomy', 'rwmb_get_terms' ],
			'taxonomy_advanced' => [ 'taxonomy_advanced', 'rwmb_get_terms' ],
		];
	}

	public static function forbiddenAjaxProvider(): array {
		return [
			'tampered payload'  => [ 'tampered', 'rwmb_get_users', true ],
			'mismatched action' => [ 'valid', 'rwmb_get_posts', true ],
			'missing payload'   => [ 'missing', 'rwmb_get_users', true ],
			'invalid nonce'     => [ 'valid', 'rwmb_get_users', false ],
		];
	}

	private function field( array $overrides = [] ): array {
		return array_merge( [
			'id'          => 'field',
			'type'        => 'post',
			'ajax'        => true,
			'ajax_action' => '',
			'query_args'  => [],
			'js_options'  => [],
		], $overrides );
	}

	private function ajax_field( array $field ): array {
		$data = $field['js_options']['ajax_data'];
		return is_string( $data['field'] ) ? json_decode( $data['field'], true ) : $data['field'];
	}

	private function get_ajax_die_status( callable $callback ): int {
		$status        = 0;
		$doing_ajax    = static function () {
			return true;
		};
		$status_filter = static function ( $status_header, $code ) use ( &$status ) {
			$status = (int) $code;
			return $status_header;
		};
		$die_handler   = static function () use ( &$status ) {
			return static function ( $message, $title, $args ) use ( &$status ) {
				if ( is_array( $args ) && isset( $args['response'] ) && null !== $args['response'] ) {
					$status = (int) $args['response'];
				} elseif ( is_int( $title ) ) {
					$status = $title;
				}
				throw new RuntimeException( 'wp_die' );
			};
		};

		add_filter( 'wp_doing_ajax', $doing_ajax );
		add_filter( 'status_header', $status_filter, 10, 2 );
		add_filter( 'wp_die_ajax_handler', $die_handler );

		ob_start();
		try {
			$callback();
		} catch ( RuntimeException $e ) {
			// Stubs carry the HTTP status in the exception code.
			$status = $status ?: (int) $e->getCode();
		} finally {
			ob_end_clean();
			remove_filter( 'wp_doing_ajax', $doing_ajax );
			remove_filter( 'status_header', $status_filter, 10 );
			remove_filter( 'wp_die_ajax_handler', $die_handler );
		}

		return $status;
	}

	private function run_user_ajax_search( array $field, string $term ): array {
		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$data = $field['js_options']['ajax_data'];

		rwmb_request()->set_post_data( [
			'field'  => wp_slash( $data['field'] ),
			'action' => 'rwmb_get_users',
			'term'   => $term,
			'_type'  => 'query',
		] );
		$_REQUEST['_wpnonce'] = $data['_wpnonce'];

		$captured = [];
		$filter   = static function ( $items ) use ( &$captured ) {
			$captured = $items;
			throw new RuntimeException( 'captured' );
		};

		add_filter( 'rwmb_ajax_get_users', $filter );
		try {
			RWMB_User_Field::ajax_get_users();
		} catch ( RuntimeException $e ) {
			// Expected: stop before wp_send_json_success().
		} finally {
			remove_filter( 'rwmb_ajax_get_users', $filter );
			rwmb_request()->set_post_data( [
				'field'  => '',
				'action' => '',
				'term'   => '',
				'_type'  => '',
			] );
			unset( $_REQUEST['_wpnonce'] );
		}

		return $captured;
	}

	#[DataProvider( 'defaultAjaxActionProvider' )]
	public function testDefaultAjaxActionIsDerivedFromFieldType( string $type, string $expected_action ): void {
		$field = $this->field( [
			'id'   => $type,
			'type' => $type,
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$this->assertSame( $expected_action, $this->ajax_field( $field )['ajax_action'] );
	}

	public function testCustomAjaxActionIsIncludedInAjaxData(): void {
		$field = $this->field( [
			'ajax_action' => 'my_custom_action',
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$this->assertSame( 'my_custom_action', $this->ajax_field( $field )['ajax_action'] );
	}

	public function testAjaxActionIsSanitized(): void {
		$field = $this->field( [
			'ajax_action' => 'My Custom/Action!',
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$this->assertSame( 'mycustomaction', $this->ajax_field( $field )['ajax_action'] );
	}

	public function testNonStringAjaxActionFallsBackToTypeDefault(): void {
		$field = $this->field( [
			'type'        => 'user',
			'ajax_action' => [ 'not-a-string' ],
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$this->assertSame( 'rwmb_get_users', $this->ajax_field( $field )['ajax_action'] );
	}

	public function testAjaxDataIsNotCreatedWhenAjaxIsDisabled(): void {
		$field = $this->field( [
			'ajax' => false,
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$this->assertArrayNotHasKey( 'ajax_data', $field['js_options'] );
	}

	public function testUnknownTypeWithoutAjaxActionOmitsAction(): void {
		$field = $this->field( [
			'type' => 'custom_object',
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$this->assertArrayNotHasKey( 'ajax_action', $this->ajax_field( $field ) );
	}

	public function testCoreFieldSendsSignedJsonPayload(): void {
		$field = $this->field( [
			'id'             => 'author',
			'type'           => 'user',
			'query_args'     => [ 'role' => 'subscriber' ],
			'display_field'  => 'display_name',
			'show_thumbnail' => false,
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$data    = $field['js_options']['ajax_data'];
		$decoded = $this->ajax_field( $field );

		$this->assertIsString( $data['field'] );
		$this->assertSame( json_decode( $data['field'], true ), $decoded );
		$this->assertSame( 'author', $decoded['id'] );
		$this->assertSame( 'user', $decoded['type'] );
		$this->assertSame( 'subscriber', $decoded['query_args']['role'] );
		$this->assertSame( 'display_name', $decoded['display_field'] );
		$this->assertFalse( $decoded['show_thumbnail'] );
		$this->assertSame( 'rwmb_get_users', $decoded['ajax_action'] );
		$this->assertArrayNotHasKey( '_payload', $data );
		$this->assertArrayNotHasKey( 'meta_box_id', $decoded );
		$this->assertNotFalse( wp_verify_nonce( $data['_wpnonce'], 'rwmb-query-' . $data['field'] ) );
	}

	public function testTamperedPayloadFailsNonce(): void {
		$field = $this->field( [
			'type'       => 'user',
			'query_args' => [ 'role' => 'subscriber' ],
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$data                          = $field['js_options']['ajax_data'];
		$decoded                       = json_decode( $data['field'], true );
		$decoded['query_args']['role'] = 'administrator';
		$tampered                      = wp_json_encode( $decoded );

		$this->assertNotSame( $data['field'], $tampered );
		$this->assertFalse( wp_verify_nonce( $data['_wpnonce'], 'rwmb-query-' . $tampered ) );
	}

	public function testCustomAjaxActionKeepsLegacyFormat(): void {
		$field = $this->field( [
			'ajax_action' => 'my_custom_action',
			'query_args'  => [ 'post_type' => 'page' ],
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$data = $field['js_options']['ajax_data'];
		$this->assertIsArray( $data['field'] );
		$this->assertSame( 'page', $data['field']['query_args']['post_type'] );
		$this->assertSame( 'my_custom_action', $data['field']['ajax_action'] );
		$this->assertSame( wp_create_nonce( 'query' ), $data['_wpnonce'] );
		$this->assertArrayNotHasKey( '_payload', $data );
	}

	public function testGetAjaxFieldUnslashesSignedPayload(): void {
		if ( ! function_exists( 'rwmb_request' ) ) {
			$this->markTestSkipped( 'Meta Box request helper unavailable' );
		}

		wp_set_current_user( 0 );

		$field = $this->field( [
			'id'         => 'author',
			'type'       => 'user',
			'query_args' => [
				'role'   => 'subscriber',
				'search' => 'O\'Reilly / "test"',
			],
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$data = $field['js_options']['ajax_data'];

		// Mimic jQuery serialization and WordPress magic quotes on POST data.
		$post = [];
		parse_str( http_build_query( [
			'field'  => $data['field'],
			'action' => 'rwmb_get_users',
		] ), $post );
		rwmb_request()->set_post_data( wp_slash( $post ) );
		$_REQUEST['_wpnonce'] = $data['_wpnonce'];

		try {
			$resolved = RWMB_Object_Choice_Ajax_Test_Field::get_field();
		} finally {
			rwmb_request()->set_post_data( [
				'field'    => '',
				'action'   => '',
			] );
			unset( $_REQUEST['_wpnonce'] );
		}

		$this->assertSame( 'subscriber', $resolved['query_args']['role'], 'The signed payload must keep its value through transport encoding.' );
		$this->assertSame( 'O\'Reilly / "test"', $resolved['query_args']['search'] );
		$this->assertSame( 'author', $resolved['_original_id'] );
		$this->assertFalse( $resolved['clone'] );
	}

	#[DataProvider( 'forbiddenAjaxProvider' )]
	public function testInvalidAjaxPayloadReturnsForbidden( string $payload_type, string $action, bool $valid_nonce ): void {
		if ( ! function_exists( 'rwmb_request' ) ) {
			$this->markTestSkipped( 'Meta Box request helper unavailable' );
		}

		wp_set_current_user( 0 );

		$field = $this->field( [
			'id'         => 'author',
			'type'       => 'user',
			'query_args' => [ 'role' => 'subscriber' ],
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$data    = $field['js_options']['ajax_data'];
		$payload = $data['field'];
		if ( 'tampered' === $payload_type ) {
			$decoded                       = json_decode( $payload, true );
			$decoded['query_args']['role'] = 'administrator';
			$payload                       = wp_json_encode( $decoded );
		}
		if ( 'missing' === $payload_type ) {
			$payload = '';
		}

		rwmb_request()->set_post_data( [
			'field'  => wp_slash( $payload ),
			'action' => $action,
		] );
		$_REQUEST['_wpnonce'] = $valid_nonce ? $data['_wpnonce'] : 'wrong-nonce';

		try {
			$status = $this->get_ajax_die_status( static function () {
				RWMB_Object_Choice_Ajax_Test_Field::get_field();
			} );
		} finally {
			rwmb_request()->set_post_data( [
				'field'  => '',
				'action' => '',
			] );
			unset( $_REQUEST['_wpnonce'] );
		}

		$this->assertSame( 403, $status );
	}

	public function testUserQueryDoesNotSearchEmailByDefault(): void {
		if ( ! function_exists( 'wp_insert_user' ) || ! function_exists( 'get_users' ) ) {
			$this->markTestSkipped( 'WordPress user APIs unavailable' );
		}

		$suffix = uniqid();
		$login  = 'rwmb_user_' . $suffix;
		$email  = 'rwmb-secret-' . $suffix . '@example.com';

		$user_id = wp_insert_user( [
			'user_login'   => $login,
			'user_pass'    => wp_generate_password( 24 ),
			'user_email'   => $email,
			'display_name' => $login,
			'role'         => 'subscriber',
		] );
		if ( is_wp_error( $user_id ) ) {
			$this->fail( $user_id->get_error_message() );
		}

		wp_set_current_user( 0 );

		try {
			$field = $this->field( [
				'id'            => 'user',
				'type'          => 'user',
				'display_field' => 'display_name',
				'query_args'    => [ 'number' => 10 ],
			] );

			$options = $this->run_user_ajax_search( $field, $email );
			$this->assertNotContains( (string) $user_id, array_map( 'strval', array_column( $options, 'value' ) ), 'Email must not be searchable by default.' );

			$field['query_args']['search_columns'] = [ 'user_email' ];
			$options                               = $this->run_user_ajax_search( $field, $email );
			$this->assertContains( (string) $user_id, array_map( 'strval', array_column( $options, 'value' ) ), 'Email search must work when explicitly configured.' );

			$field['query_args'] = [ 'number' => 10 ];
			$options             = $this->run_user_ajax_search( $field, $login );
			$this->assertContains( (string) $user_id, array_map( 'strval', array_column( $options, 'value' ) ), 'Login search must still work.' );
		} finally {
			if ( ! function_exists( 'wp_delete_user' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}
			wp_delete_user( $user_id );
		}
	}

	public function testSidebarFieldDoesNotSetAjaxData(): void {
		$field = RWMB_Sidebar_Field::normalize( [
			'id'   => 'sidebar',
			'type' => 'sidebar',
		] );

		$this->assertArrayNotHasKey( 'ajax_data', $field['js_options'] ?? [] );
	}
}

<?php
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Exposes set_ajax_params() for tests without reflection.
 */
class RWMB_Object_Choice_Ajax_Test_Field extends RWMB_Object_Choice_Field {
	public static function query( $meta, array $field ): array {
		return [];
	}

	public static function apply_ajax_params( array &$field ): void {
		parent::set_ajax_params( $field );
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

	#[DataProvider( 'defaultAjaxActionProvider' )]
	public function testDefaultAjaxActionIsDerivedFromFieldType( string $type, string $expected_action ): void {
		$field = $this->field( [
			'id'   => $type,
			'type' => $type,
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$this->assertSame( $expected_action, $field['js_options']['ajax_data']['field']['ajax_action'] );
	}

	public function testCustomAjaxActionIsIncludedInAjaxData(): void {
		$field = $this->field( [
			'ajax_action' => 'my_custom_action',
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$this->assertSame( 'my_custom_action', $field['js_options']['ajax_data']['field']['ajax_action'] );
	}

	public function testAjaxActionIsSanitized(): void {
		$field = $this->field( [
			'ajax_action' => 'My Custom/Action!',
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$this->assertSame( 'mycustomaction', $field['js_options']['ajax_data']['field']['ajax_action'] );
	}

	public function testNonStringAjaxActionFallsBackToTypeDefault(): void {
		$field = $this->field( [
			'type'        => 'user',
			'ajax_action' => [ 'not-a-string' ],
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$this->assertSame( 'rwmb_get_users', $field['js_options']['ajax_data']['field']['ajax_action'] );
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

		$this->assertArrayNotHasKey( 'ajax_action', $field['js_options']['ajax_data']['field'] );
	}

	public function testAjaxPayloadOmitsQuerySettingsAndUsesFieldNonce(): void {
		$field = $this->field( [
			'id'          => 'author',
			'meta_box_id' => 'box',
			'query_args'  => [ 'post_type' => 'page' ],
		] );

		RWMB_Object_Choice_Ajax_Test_Field::apply_ajax_params( $field );

		$payload = $field['js_options']['ajax_data']['field'];
		$this->assertSame( [ 'id', 'type', 'meta_box_id', 'ajax_action' ], array_keys( $payload ) );
		$this->assertSame( 'box', $payload['meta_box_id'] );
		$this->assertArrayNotHasKey( 'query_args', $payload );
		$this->assertSame( 'rwmb-query-3-box-author', RWMB_Object_Choice_Ajax_Test_Field::ajax_nonce_action( $field ) );
		$this->assertNotSame(
			RWMB_Object_Choice_Ajax_Test_Field::ajax_nonce_action( [ 'meta_box_id' => 'my-box', 'id' => 'author' ] ),
			RWMB_Object_Choice_Ajax_Test_Field::ajax_nonce_action( [ 'meta_box_id' => 'my', 'id' => 'box-author' ] )
		);
	}

	public function testBindAjaxFieldsRebuildsNestedNonceWithoutQueryArgs(): void {
		$fields = [
			[
				'id'     => 'group',
				'fields' => [
					[
						'id'         => 'author',
						'js_options' => [
							'ajax_data' => [
								'field'    => [
									'id'            => 'author',
									'query_args'    => [ 'role' => 'subscriber' ],
									'display_field' => 'user_login',
								],
								'_wpnonce' => 'old',
							],
						],
					],
				],
			],
		];

		$bound = RWMB_Object_Choice_Ajax_Test_Field::bind_ajax_fields( $fields, 'box' );
		$child = $bound[0]['fields'][0]['js_options']['ajax_data']['field'];

		$this->assertSame( 'box', $bound[0]['fields'][0]['meta_box_id'] );
		$this->assertSame( 'box', $child['meta_box_id'] );
		$this->assertArrayNotHasKey( 'query_args', $child );
		$this->assertArrayNotHasKey( 'display_field', $child );
		$this->assertNotSame( 'old', $bound[0]['fields'][0]['js_options']['ajax_data']['_wpnonce'] );
	}

	public function testSidebarFieldDoesNotSetAjaxData(): void {
		$field = RWMB_Sidebar_Field::normalize( [
			'id'   => 'sidebar',
			'type' => 'sidebar',
		] );

		$this->assertArrayNotHasKey( 'ajax_data', $field['js_options'] ?? [] );
	}
}

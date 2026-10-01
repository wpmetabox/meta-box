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

	public function testSidebarFieldDoesNotSetAjaxData(): void {
		$field = RWMB_Sidebar_Field::normalize( [
			'id'   => 'sidebar',
			'type' => 'sidebar',
		] );

		$this->assertArrayNotHasKey( 'ajax_data', $field['js_options'] ?? [] );
	}
}

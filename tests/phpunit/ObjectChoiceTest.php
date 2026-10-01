<?php
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ObjectChoiceTest extends TestCase {
	protected function setUp(): void {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}
	}

	/**
	 * Built-in object-choice field types and their ajax actions.
	 * Covers the former JS type→action map, including taxonomy_advanced inheritance.
	 */
	public static function fieldTypeProvider(): array {
		return [
			'post'              => [ RWMB_Post_Field::class, 'post', 'rwmb_get_posts' ],
			'user'              => [ RWMB_User_Field::class, 'user', 'rwmb_get_users' ],
			'taxonomy'          => [ RWMB_Taxonomy_Field::class, 'taxonomy', 'rwmb_get_terms' ],
			'taxonomy_advanced' => [ RWMB_Taxonomy_Advanced_Field::class, 'taxonomy_advanced', 'rwmb_get_terms' ],
		];
	}

	#[DataProvider( 'fieldTypeProvider' )]
	public function testDefaultAjaxActionIsIncludedWhenNotPassed( string $class, string $type, string $expected_action ): void {
		$field = $class::normalize( [
			'id'   => $type,
			'type' => $type,
		] );

		$this->assertSame(
			$expected_action,
			$field['js_options']['ajax_data']['field']['ajax_action']
		);
	}

	#[DataProvider( 'fieldTypeProvider' )]
	public function testCustomAjaxActionIsIncludedInAjaxData( string $class, string $type, string $_expected_action ): void {
		$field = $class::normalize( [
			'id'          => $type,
			'type'        => $type,
			'ajax_action' => 'my_custom_action',
		] );

		$this->assertSame(
			'my_custom_action',
			$field['js_options']['ajax_data']['field']['ajax_action']
		);
	}

	#[DataProvider( 'fieldTypeProvider' )]
	public function testAjaxActionIsSanitized( string $class, string $type, string $_expected_action ): void {
		$field = $class::normalize( [
			'id'          => $type,
			'type'        => $type,
			'ajax_action' => 'My Custom/Action!',
		] );

		$this->assertSame(
			'mycustomaction',
			$field['js_options']['ajax_data']['field']['ajax_action']
		);
	}

	#[DataProvider( 'fieldTypeProvider' )]
	public function testNonStringAjaxActionIsOmitted( string $class, string $type, string $_expected_action ): void {
		$field = $class::normalize( [
			'id'          => $type,
			'type'        => $type,
			'ajax_action' => [ 'not-a-string' ],
		] );

		$this->assertArrayNotHasKey(
			'ajax_action',
			$field['js_options']['ajax_data']['field']
		);
	}

	#[DataProvider( 'fieldTypeProvider' )]
	public function testAjaxDataIsNotCreatedWhenAjaxIsDisabled( string $class, string $type, string $_expected_action ): void {
		$field = $class::normalize( [
			'id'         => $type,
			'type'       => $type,
			'field_type' => 'select',
		] );

		$this->assertFalse( $field['ajax'] );
		$this->assertArrayNotHasKey( 'ajax_data', $field['js_options'] ?? [] );
	}

	public function testMissingAjaxActionDoesNotWarn(): void {
		$field = [
			'id'         => 'post',
			'type'       => 'post',
			'ajax'       => true,
			'query_args' => [],
			'js_options' => [],
		];

		$method = new ReflectionMethod( RWMB_Object_Choice_Field::class, 'set_ajax_params' );
		$method->invokeArgs( null, [ &$field ] );

		$this->assertArrayNotHasKey(
			'ajax_action',
			$field['js_options']['ajax_data']['field']
		);
	}
}

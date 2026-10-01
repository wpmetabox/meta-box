<?php
use PHPUnit\Framework\TestCase;

class ObjectChoiceTest extends TestCase {
	protected function setUp(): void {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}
	}

	public function testCustomAjaxActionIsIncludedInAjaxData(): void {
		$field = RWMB_Post_Field::normalize( [
			'id'          => 'post',
			'type'        => 'post',
			'ajax_action' => 'my_custom_action',
		] );

		$this->assertSame(
			'my_custom_action',
			$field['js_options']['ajax_data']['field']['ajax_action']
		);
	}

	public function testDefaultAjaxActionIsIncludedWhenNotPassed(): void {
		$field = RWMB_Post_Field::normalize( [
			'id'   => 'post',
			'type' => 'post',
		] );

		$this->assertSame(
			'rwmb_get_posts',
			$field['js_options']['ajax_data']['field']['ajax_action']
		);
	}

	public function testEmptyAjaxActionIsOmittedFromAjaxData(): void {
		$field = RWMB_Post_Field::normalize( [
			'id'          => 'post',
			'type'        => 'post',
			'ajax_action' => '',
		] );

		$this->assertArrayNotHasKey(
			'ajax_action',
			$field['js_options']['ajax_data']['field']
		);
	}

	public function testAjaxActionIsSanitized(): void {
		$field = RWMB_Post_Field::normalize( [
			'id'          => 'post',
			'type'        => 'post',
			'ajax_action' => 'My Custom/Action!',
		] );

		$this->assertSame(
			'mycustomaction',
			$field['js_options']['ajax_data']['field']['ajax_action']
		);
	}

	public function testAjaxDataIsNotCreatedWhenAjaxIsDisabled(): void {
		$field = RWMB_Post_Field::normalize( [
			'id'         => 'post',
			'type'       => 'post',
			'field_type' => 'select',
		] );

		$this->assertFalse( $field['ajax'] );
		$this->assertArrayNotHasKey( 'ajax_data', $field['js_options'] ?? [] );
	}
}

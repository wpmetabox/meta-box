<?php
use PHPUnit\Framework\TestCase;

class RegisterMetaTest extends TestCase {
	private $meta;

	public function setUp(): void {
		$this->meta = get_registered_meta_keys( 'post' );
	}

	public function testFieldRegisteredByDefault() {
		$this->assertArrayHasKey( 'rmt_simple_text', $this->meta );
	}

	public function testNotRegisteredFieldShouldNotBeRegistered() {
		$this->assertArrayNotHasKey( 'rmt_text_no_register_meta', $this->meta );
	}

	/**
	 * If we set 'register_meta' to array, it should be merged with the default args
	 */
	public function testOverrideArgs() {
		$this->assertArrayIsEqualToArrayIgnoringListOfKeys( $this->meta['rmt_text_override'], [ 
			'description' => 'This is an overridden description',
			'revisions_enabled' => false,
			'single' => true,
			'show_in_rest' => false,
			'default' => 'This is a default value',
			'type' => 'string',
		], [
            'sanitize_callback',
            'auth_callback',
        ] );
	}

	/**
	 * 1. Revision should be set to inherit from the meta box (false) by default
	 * 2. If field['register_meta']['revisions_enabled'] is set, it should be used
	 */
	public function testRevisionArgument() {
        // This inherits from the meta box
		$this->assertEquals( true, $this->meta['rmt_simple_text']['revisions_enabled'] );

        // This is set to false
		$this->assertEquals( false, $this->meta['rmt_text_override']['revisions_enabled'] );
	}

	/**
	 * 1. $args['single'] should be inversion of $field['multiple']
	 * 2. If $field['register_meta']['multiple'] is set, it should be used
	 */
	public function testSingleArgument() {
		$this->assertEquals( true, $this->meta['rmt_simple_text']['single'] );
	}

	/**
	 * 1. By default, show_in_rest should be set to true
	 * 2. If $field['register_meta']['show_in_rest'] is set, it should be used
	 */
	public function testShowInRestArgument() {
		$this->assertEquals( true, $this->meta['rmt_simple_text']['show_in_rest'] );

        $this->assertEquals( false, $this->meta['rmt_text_override']['show_in_rest'] );
	}

	/**
	 * !IMPORTANT: Need more discussion about this
	 * 
	 * It's ideal and possible to use rwmb_the_value() to get the value of the field for the schema so it matches the prepared value,
	 * match with MB Rest API.
	 * 
	 * HOWEVER, we should not use rwmb_the_value() to get the value of the field because WP uses get_post_meta() to get the value
	 * and it doesn't have any filter to alter with rwmb_the_value()
	 * 
	 * Although, we can use get_{$meta_type}_metadata filter to alter the value, it's still dangerous
	 * 
	 * The same applies to "update_{$meta_type}_metadata", we define schema is an array but the POST value should be a string|number|boolean
	 * Need to test if possible to skip the schema validation on POST request
	 * 
	 * Ref: function _block_bindings_post_meta_get_value( array $source_args, $block_instance ) {
	 * Ref: get_{$meta_type}_metadata 
	 * Ref: update_{$meta_type}_metadata
	 * @return void
	 */
	public function testValidSchema() {
		//$check = rest_validate_value_from_schema( $args['default'], $schema );

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
	}
}
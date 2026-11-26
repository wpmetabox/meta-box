<?php

class IconFieldTest extends WP_UnitTestCase {
	private function field( array $args = [] ): array {
		return RWMB_Icon_Field::normalize( array_merge( [
			'id'   => 'icon',
			'type' => 'icon',
		], $args ) );
	}

	/**
	 * Run the Ajax handler and return the items it gives to the filter.
	 * A status code is returned instead if the request stops with wp_die().
	 *
	 * @return array|int
	 */
	private function ajax( array $post ) {
		rwmb_request()->set_post_data( $post );

		$result = 0;
		$filter = static function ( $items ) use ( &$result ) {
			$result = $items;
			throw new RuntimeException( 'captured' );
		};
		$die    = static function () use ( &$result ) {
			return static function ( $message, $title, $args ) use ( &$result ) {
				$result = (int) ( $args['response'] ?? $title );
				throw new RuntimeException( 'wp_die' );
			};
		};

		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', $die );
		add_filter( 'rwmb_ajax_get_icons', $filter );
		ob_start();
		try {
			RWMB_Icon_Field::ajax_get_icons();
		} catch ( RuntimeException $e ) {
			// Expected: stop before the response is sent.
		} finally {
			ob_end_clean();
			remove_filter( 'wp_doing_ajax', '__return_true' );
			remove_filter( 'wp_die_ajax_handler', $die );
			remove_filter( 'rwmb_ajax_get_icons', $filter );
			// set_post_data() merges, so clear each key.
			rwmb_request()->set_post_data( array_fill_keys( array_keys( $post ), '' ) );
			unset( $_REQUEST['_wpnonce'] );
		}

		return $result;
	}

	private function ajax_post( array $field, array $extra = [] ): array {
		$data                 = $field['js_options']['ajax_data'];
		$_REQUEST['_wpnonce'] = $data['_wpnonce'];

		return array_merge( [
			'field'  => wp_slash( $data['field'] ),
			'action' => 'rwmb_get_icons',
		], $extra );
	}

	public function testAjaxFieldDoesNotOutputAllIcons(): void {
		$field = $this->field();

		$this->assertSame( [], $field['options'] );
		$this->assertArrayHasKey( 'ajax_data', $field['js_options'] );
		$this->assertNotEmpty( $this->field( [ 'ajax' => false ] )['options'] );
	}

	public function testAjaxSearchAndPagination(): void {
		$field = $this->field();

		$items = $this->ajax( $this->ajax_post( $field, [ 'term' => 'circle-user' ] ) );
		$this->assertContains( 'fa-solid fa-circle-user', array_column( $items, 'value' ) );
		$this->assertStringContainsString( '<svg', $items[0]['label'] );

		$page1 = $this->ajax( $this->ajax_post( $field ) );
		$page2 = $this->ajax( $this->ajax_post( $field, [ 'page' => 2 ] ) );
		$this->assertCount( RWMB_Icon_Field::PER_PAGE, $page1 );
		$this->assertNotSame( $page1[0]['value'], $page2[0]['value'] );
	}

	public function testAjaxRejectsOtherFieldPayload(): void {
		$field = $this->field();

		// A valid payload and nonce of another Ajax field type.
		$payload              = wp_json_encode( [ 'id' => 'p', 'type' => 'post', 'ajax_action' => 'rwmb_get_posts' ] );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'rwmb-query-' . $payload );
		$this->assertSame( 403, $this->ajax( [ 'field' => wp_slash( $payload ), 'action' => 'rwmb_get_icons' ] ) );

		// Changed icon path: the nonce does not match.
		$post          = $this->ajax_post( $field );
		$data          = json_decode( wp_unslash( $post['field'] ), true );
		$data['icon_file'] = '/etc/passwd';
		$post['field'] = wp_slash( wp_json_encode( $data ) );
		$this->assertSame( 403, $this->ajax( $post ) );
	}
}

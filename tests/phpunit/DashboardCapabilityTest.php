<?php
use MetaBox\Dashboard\Dashboard;

/**
 * Dashboard AJAX handlers must check user capabilities, not only nonces.
 */
class DashboardCapabilityTest extends WP_UnitTestCase {
	public static function forbiddenRequestProvider(): array {
		return [
			'subscriber installs'        => [ [], 'handle_plugin_action', 'plugin', [ 'mb_plugin' => 'foo', 'mb_action' => 'install' ] ],
			'subscriber activates'       => [ [], 'handle_plugin_action', 'plugin', [ 'mb_plugin' => 'foo', 'mb_action' => 'activate' ] ],
			'install-only user installs' => [ [ 'install_plugins' ], 'handle_plugin_action', 'plugin', [ 'mb_plugin' => 'foo', 'mb_action' => 'install' ] ],
			'subscriber reads feed'      => [ [], 'get_feed', 'feed', [] ],
		];
	}

	/**
	 * @dataProvider forbiddenRequestProvider
	 */
	public function testHandlerRejectsUserWithoutCapability( array $caps, string $method, string $nonce_action, array $get ): void {
		$user = self::factory()->user->create_and_get( [ 'role' => 'subscriber' ] );
		foreach ( $caps as $cap ) {
			$user->add_cap( $cap );
		}
		wp_set_current_user( $user->ID );

		$_GET     = $get;
		$_REQUEST = array_merge( $get, [ '_ajax_nonce' => wp_create_nonce( $nonce_action ) ] );

		$this->assertSame( [ 'success' => false ], $this->call_ajax( $method ) );
	}

	private function call_ajax( string $method ): array {
		// Skip the constructor: it registers hooks and reads update data.
		$dashboard   = ( new ReflectionClass( Dashboard::class ) )->newInstanceWithoutConstructor();
		$doing_ajax  = static function () {
			return true;
		};
		$die_handler = static function () {
			return static function () {
				throw new RuntimeException( 'wp_die' );
			};
		};

		add_filter( 'wp_doing_ajax', $doing_ajax );
		add_filter( 'wp_die_ajax_handler', $die_handler );

		ob_start();
		try {
			$dashboard->$method();
		} catch ( RuntimeException $e ) {
			// Expected: wp_send_json_*() ends the request through wp_die().
		} finally {
			$output = ob_get_clean();
			remove_filter( 'wp_doing_ajax', $doing_ajax );
			remove_filter( 'wp_die_ajax_handler', $die_handler );
			$_GET     = [];
			$_REQUEST = [];
		}

		return json_decode( $output, true ) ?: [];
	}
}

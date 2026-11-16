<?php
use PHPUnit\Framework\TestCase;

/**
 * Dashboard AJAX handlers must check user capabilities, not only nonces.
 *
 * Runs against a live WordPress install (see tests/bootstrap.php).
 */
class DashboardCapabilityTest extends TestCase {
	private $user_id;

	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'RWMB_VER' ) || ! function_exists( 'add_role' ) ) {
			$this->markTestSkipped( 'Needs a live WordPress install with Meta Box active.' );
		}

		// Role with install rights only, to prove activate rights are checked too.
		add_role( 'mb_test_installer', 'MB Test Installer', [ 'read' => true, 'install_plugins' => true ] );
		$this->user_id = wp_insert_user(
			[
				'user_login' => 'mb_dashboard_test_' . wp_generate_password( 6, false ),
				'user_pass'  => wp_generate_password(),
				'role'       => 'subscriber',
			]
		);
	}

	protected function tearDown(): void {
		if ( $this->user_id ) {
			wp_set_current_user( 0 );
			wp_delete_user( $this->user_id );
			remove_role( 'mb_test_installer' );
		}
		$_GET = $_REQUEST = [];
		parent::tearDown();
	}

	public function test_subscriber_cannot_install_plugin(): void {
		$this->login( 'subscriber' );
		$response = $this->call_handler( 'handle_plugin_action', [ 'mb_plugin' => 'foo', 'mb_action' => 'install' ], 'plugin' );

		$this->assertFalse( $response['success'] ?? true );
	}

	public function test_install_only_user_cannot_install_and_activate(): void {
		$this->login( 'mb_test_installer' );
		$response = $this->call_handler( 'handle_plugin_action', [ 'mb_plugin' => 'foo', 'mb_action' => 'install' ], 'plugin' );

		$this->assertFalse( $response['success'] ?? true );
	}

	public function test_subscriber_cannot_read_feed(): void {
		$this->login( 'subscriber' );
		$response = $this->call_handler( 'get_feed', [], 'feed' );

		$this->assertFalse( $response['success'] ?? true );
	}

	private function login( string $role ): void {
		wp_update_user( [ 'ID' => $this->user_id, 'role' => $role ] );
		wp_set_current_user( $this->user_id );
	}

	private function call_handler( string $method, array $get, string $nonce_action ): array {
		$_GET                = $get;
		$_REQUEST            = [ '_ajax_nonce' => wp_create_nonce( $nonce_action ) ];
		$_REQUEST            = array_merge( $_REQUEST, $get );
		$dashboard           = ( new ReflectionClass( \MetaBox\Dashboard\Dashboard::class ) )->newInstanceWithoutConstructor();
		$doing_ajax          = static function () {
			return true;
		};
		$die_handler         = static function () {
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
		}

		return json_decode( $output, true ) ?: [];
	}
}

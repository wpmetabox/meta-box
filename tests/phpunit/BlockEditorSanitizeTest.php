<?php
use PHPUnit\Framework\TestCase;

class BlockEditorSanitizeTest extends TestCase {
	private RWMB_Sanitizer $sanitizer;

	/** @var int[] */
	private array $user_ids = [];

	protected function setUp(): void {
		parent::setUp();
		if ( ! defined( 'RWMB_VER' ) || ! function_exists( 'wp_kses_post' ) || ! function_exists( 'wp_set_current_user' ) ) {
			$this->markTestSkipped( 'Meta Box or WordPress user APIs unavailable' );
		}
		$this->sanitizer = new RWMB_Sanitizer();
	}

	protected function tearDown(): void {
		wp_set_current_user( 0 );
		if ( ! function_exists( 'wp_delete_user' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		foreach ( $this->user_ids as $user_id ) {
			if ( get_user_by( 'id', $user_id ) ) {
				wp_delete_user( $user_id );
			}
		}
		$this->user_ids = [];
		parent::tearDown();
	}

	public function testStripsScriptWithoutUnfilteredHtml(): void {
		wp_set_current_user( $this->create_user( 'subscriber' ) );
		$this->assertFalse( current_user_can( 'unfiltered_html' ) );

		$dirty  = "<!-- wp:html -->\n<script>alert(1)</script>\n<!-- /wp:html -->";
		$result = $this->sanitizer->sanitize( $dirty, [
			'type'              => 'block_editor',
			'sanitize_callback' => null,
		] );

		$this->assertStringNotContainsString( '<script', $result );
		$this->assertStringContainsString( '<!-- wp:html -->', $result );
	}

	public function testKeepsRawMarkupWithUnfilteredHtml(): void {
		wp_set_current_user( $this->create_user( 'administrator' ) );
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			$this->markTestSkipped( 'Admin lacks unfiltered_html (multisite or DISALLOW_UNFILTERED_HTML)' );
		}

		$dirty  = "<!-- wp:html -->\n<script>alert(1)</script>\n<!-- /wp:html -->";
		$result = $this->sanitizer->sanitize( $dirty, [
			'type'              => 'block_editor',
			'sanitize_callback' => null,
		] );

		$this->assertStringContainsString( '<script>alert(1)</script>', $result );
	}

	public function testPreservesSlashedBackslashesForStorage(): void {
		wp_set_current_user( $this->create_user( 'administrator' ) );
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			$this->markTestSkipped( 'Admin lacks unfiltered_html (multisite or DISALLOW_UNFILTERED_HTML)' );
		}

		// Mimic magic-quoted $_POST: one logical backslash arrives as two.
		$slashed = wp_slash( "<!-- wp:code -->\n<pre>path\\to\\file</pre>\n<!-- /wp:code -->" );
		$result  = $this->sanitizer->sanitize( $slashed, [
			'type'              => 'block_editor',
			'sanitize_callback' => null,
		] );

		$this->assertSame( $slashed, $result, 'sanitize must leave slashes for update_metadata()' );
	}

	private function create_user( string $role ): int {
		$id = wp_insert_user( [
			'user_login' => 'bb_sanitize_' . $role . '_' . uniqid(),
			'user_pass'  => wp_generate_password( 24 ),
			'user_email' => 'bb-sanitize-' . $role . '-' . uniqid() . '@example.com',
			'role'       => $role,
		] );
		if ( is_wp_error( $id ) ) {
			$this->fail( $id->get_error_message() );
		}
		$this->user_ids[] = (int) $id;
		return (int) $id;
	}
}

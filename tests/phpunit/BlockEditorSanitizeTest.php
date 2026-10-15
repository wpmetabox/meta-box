<?php
use PHPUnit\Framework\TestCase;

class BlockEditorSanitizeTest extends TestCase {
	private RWMB_Sanitizer $sanitizer;

	protected function setUp(): void {
		parent::setUp();
		if ( ! defined( 'RWMB_VER' ) || ! function_exists( 'wp_kses_post' ) || ! function_exists( 'wp_set_current_user' ) ) {
			$this->markTestSkipped( 'Meta Box or WordPress user APIs unavailable' );
		}
		$this->sanitizer = new RWMB_Sanitizer();
	}

	public function testStripsScriptWithoutUnfilteredHtml(): void {
		$user_id = self::ensure_subscriber();
		wp_set_current_user( $user_id );
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
		$user_id = self::ensure_admin();
		wp_set_current_user( $user_id );
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

	private static function ensure_subscriber(): int {
		$login = 'bb_sanitize_subscriber';
		$user  = get_user_by( 'login', $login );
		if ( $user ) {
			return (int) $user->ID;
		}

		$id = wp_insert_user( [
			'user_login' => $login,
			'user_pass'  => wp_generate_password( 24 ),
			'user_email' => 'bb-sanitize-subscriber@example.com',
			'role'       => 'subscriber',
		] );
		if ( is_wp_error( $id ) ) {
			self::fail( $id->get_error_message() );
		}
		return (int) $id;
	}

	private static function ensure_admin(): int {
		$login = 'bb_sanitize_admin';
		$user  = get_user_by( 'login', $login );
		if ( $user ) {
			return (int) $user->ID;
		}

		$id = wp_insert_user( [
			'user_login' => $login,
			'user_pass'  => wp_generate_password( 24 ),
			'user_email' => 'bb-sanitize-admin@example.com',
			'role'       => 'administrator',
		] );
		if ( is_wp_error( $id ) ) {
			self::fail( $id->get_error_message() );
		}
		return (int) $id;
	}
}

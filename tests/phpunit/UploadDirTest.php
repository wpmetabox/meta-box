<?php
class UploadDirTest extends WP_UnitTestCase {
	private $seen = [];

	public function tear_down(): void {
		$this->clear_upload_dir();
		remove_filter( 'wp_handle_sideload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] );
		remove_filter( 'wp_handle_sideload_prefilter', [ $this, 'read_upload_dir' ], 20 );
		rwmb_request()->set_post_data( [ 'rwmb_upload_subdir' => '', 'rwmb_upload_nonce' => '' ] );
		parent::tear_down();
	}

	public function read_upload_dir( $file ) {
		$this->seen = wp_upload_dir();
		return $file;
	}

	public function testUploadsLandInTheSignedFolder(): void {
		$this->assertNotFalse( has_filter( 'wp_handle_upload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] ) );
		$base = wp_normalize_path( wp_upload_dir()['basedir'] );
		foreach ( [ 'file_upload' => 'note.txt', 'image_upload' => 'pixel.gif' ] as $type => $name ) {
			$class = 'image_upload' === $type ? RWMB_Image_Upload_Field::class : RWMB_File_Upload_Field::class;
			$html  = $class::html( [], $class::normalize( [ 'type' => $type, 'id' => 'docs', 'upload_dir' => "$base/docs/123" ] ) );
			$this->assertStringContainsString( '\/docs\/123', $html );
			$this->assertStringNotContainsString( $base, $html );
			rwmb_request()->set_post_data( [
				'rwmb_upload_subdir' => '/docs/123',
				'rwmb_upload_nonce'  => wp_create_nonce( 'rwmb-upload-dir|/docs/123' ),
			] );
			// is_uploaded_file() rejects a temp file, so the move uses sideload. The same filter_upload runs.
			add_filter( 'wp_handle_sideload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] );
			add_filter( 'wp_handle_sideload_prefilter', [ $this, 'read_upload_dir' ], 20 );
			$tmp    = wp_tempnam( $name );
			$is_txt = 'txt' === pathinfo( $name, PATHINFO_EXTENSION );
			file_put_contents( $tmp, $is_txt ? 'hello' : base64_decode( 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' ) );
			$file   = [ 'name' => $name, 'type' => $is_txt ? 'text/plain' : 'image/gif', 'tmp_name' => $tmp, 'error' => 0, 'size' => filesize( $tmp ) ];
			$result = wp_handle_upload( $file, [ 'test_form' => false, 'action' => 'wp_handle_sideload' ] );
			$this->assertArrayNotHasKey( 'error', $result, $type );
			$this->assertSame( '/docs/123', $this->seen['subdir'] );
			$this->assertSame( $base, wp_normalize_path( $this->seen['basedir'] ) );
			$this->assertStringStartsWith( "$base/docs/123/", wp_normalize_path( $result['file'] ) );
			$this->assertStringContainsString( '/docs/123/', $result['url'] );
			$this->assertSame( '/docs/123', wp_upload_dir()['subdir'] );
			wp_delete_file( $result['file'] );
		}
	}

	public function testRejectsTamperedOrForeignNonce(): void {
		$nonce = wp_create_nonce( 'rwmb-upload-dir|/docs' );
		foreach ( [ '/other', '/docs/../secret' ] as $subdir ) {
			$this->reject( $subdir, $nonce );
		}
		wp_set_current_user( self::factory()->user->create() );
		$this->reject( '/docs', $nonce );
		$original = _wp_upload_dir();
		$this->assertSame( wp_normalize_path( $original['path'] ), wp_normalize_path( apply_filters( 'upload_dir', $original )['path'] ) );
	}

	public function testIgnoresOutsideFolderAndPlainUpload(): void {
		foreach ( [ 'file_upload', 'image_upload' ] as $type ) {
			$class = 'image_upload' === $type ? RWMB_Image_Upload_Field::class : RWMB_File_Upload_Field::class;
			$html  = $class::html( [], $class::normalize( [ 'type' => $type, 'id' => 'private', 'upload_dir' => WP_CONTENT_DIR . '/private' ] ) );
			$this->assertStringNotContainsString( 'uploadSubdir', $html );
		}
		$this->assertSame( [ 'name' => 'a.txt', 'error' => 0 ], RWMB_File_Upload_Field::filter_upload( [ 'name' => 'a.txt', 'error' => 0 ] ) );
	}

	public function testAttachmentIdStaysAnAttachment(): void {
		$id = self::factory()->post->create( [ 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image/jpeg' ] );
		update_post_meta( $id, '_wp_attached_file', '2026/11/photo.jpg' );
		$field = [ 'upload_dir' => wp_upload_dir()['basedir'] . '/docs' ];
		$this->assertSame( $id, RWMB_File_Field::file_info( $id, [], $field )['ID'] );
		$bits  = wp_upload_bits( 'pixel.gif', null, base64_decode( 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' ) );
		$image = self::factory()->attachment->create_upload_object( $bits['file'] );
		$this->assertSame( $image, RWMB_Image_Field::file_info( $image, [], $field )['ID'] );
	}

	private function reject( string $subdir, string $nonce ): void {
		rwmb_request()->set_post_data( [ 'rwmb_upload_subdir' => $subdir, 'rwmb_upload_nonce' => $nonce ] );
		$result = RWMB_File_Upload_Field::filter_upload( [ 'name' => 'a.txt', 'error' => 0 ] );
		$this->assertSame( 'You are not allowed to upload to this folder.', $result['error'] );
	}

	private function clear_upload_dir(): void {
		global $wp_filter;
		if ( empty( $wp_filter['upload_dir'] ) ) {
			return;
		}
		foreach ( $wp_filter['upload_dir']->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( $callback['function'] instanceof Closure ) {
					remove_filter( 'upload_dir', $callback['function'], $priority );
				}
			}
		}
	}
}

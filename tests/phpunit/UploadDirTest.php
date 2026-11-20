<?php
class UploadDirTest extends WP_UnitTestCase {
	private $seen_upload = [];

	public function tear_down(): void {
		$this->remove_upload_filters();
		remove_filter( 'wp_handle_sideload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] );
		remove_filter( 'wp_handle_sideload_prefilter', [ $this, 'read_upload_dir' ], 20 );
		rwmb_request()->set_post_data( [
			'rwmb_upload_subdir' => '',
			'rwmb_upload_nonce'  => '',
		] );

		parent::tear_down();
	}

	public function read_upload_dir( $file ) {
		$this->seen_upload = wp_upload_dir();

		return $file;
	}

	public function testBothFieldsUploadIntoSignedSubdir(): void {
		$this->assertNotFalse( has_filter( 'wp_handle_upload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] ) );
		$base = wp_normalize_path( wp_upload_dir()['basedir'] );

		foreach ( [ 'file_upload' => 'note.txt', 'image_upload' => 'pixel.gif' ] as $type => $name ) {
			$html = $this->field_html( $type, $base . '/docs/123' );
			$this->assertStringContainsString( '\/docs\/123', $html );
			$this->assertStringNotContainsString( $base, $html );

			$this->post_subdir( '/docs/123' );
			// is_uploaded_file() rejects a temp file, so the move uses sideload. The same filter_upload runs.
			add_filter( 'wp_handle_sideload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] );
			add_filter( 'wp_handle_sideload_prefilter', [ $this, 'read_upload_dir' ], 20 );

			$tmp = wp_tempnam( $name );
			file_put_contents( $tmp, 'image_upload' === $type ? base64_decode( 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' ) : 'hello' );
			$file   = [
				'name'     => $name,
				'type'     => 'image_upload' === $type ? 'image/gif' : 'text/plain',
				'tmp_name' => $tmp,
				'error'    => 0,
				'size'     => filesize( $tmp ),
			];
			$result = wp_handle_upload( $file, [
				'test_form' => false,
				'action'    => 'wp_handle_sideload',
			] );

			$this->assertArrayNotHasKey( 'error', $result, $type );
			$this->assertSame( '/docs/123', $this->seen_upload['subdir'] );
			$this->assertSame( $base, wp_normalize_path( $this->seen_upload['basedir'] ) );
			$this->assertStringStartsWith( $base . '/docs/123/', wp_normalize_path( $result['file'] ) );
			$this->assertStringContainsString( '/docs/123/', $result['url'] );
			$this->assertSame( '/docs/123', wp_upload_dir()['subdir'] );

			wp_delete_file( $result['file'] );
			$this->remove_upload_filters();
			remove_filter( 'wp_handle_sideload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] );
			remove_filter( 'wp_handle_sideload_prefilter', [ $this, 'read_upload_dir' ], 20 );
		}
	}

	public function testRejectsChangedFolderTraversalAndOtherUser(): void {
		$nonce = wp_create_nonce( 'rwmb-upload-dir|/docs' );
		foreach ( [ '/other', '/docs/../secret' ] as $subdir ) {
			$this->assertRejected( $subdir, $nonce );
		}

		wp_set_current_user( self::factory()->user->create() );
		$this->assertRejected( '/docs', $nonce );
		$this->assertUploadPathUnchanged();
	}

	public function testOutsideUploadsIsNotSignedAndPlainUploadIsUnchanged(): void {
		foreach ( [ RWMB_File_Upload_Field::class, RWMB_Image_Upload_Field::class ] as $class ) {
			$type = RWMB_Image_Upload_Field::class === $class ? 'image_upload' : 'file_upload';
			$html = $class::html( [], $class::normalize( [
				'type'       => $type,
				'id'         => 'private',
				'upload_dir' => WP_CONTENT_DIR . '/private',
			] ) );
			$this->assertStringNotContainsString( 'uploadSubdir', $html );
		}

		$file = [
			'name'  => 'a.txt',
			'error' => 0,
		];
		$this->assertSame( $file, RWMB_File_Upload_Field::filter_upload( $file ) );
		$this->assertUploadPathUnchanged();
	}

	public function testAttachmentIdIsNotReadAsAPath(): void {
		$id = self::factory()->post->create( [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image/jpeg',
			'post_title'     => 'Photo',
		] );
		update_post_meta( $id, '_wp_attached_file', '2026/11/photo.jpg' );
		$field = [
			'upload_dir' => wp_upload_dir()['basedir'] . '/docs',
		];

		$info = RWMB_File_Field::file_info( $id, [], $field );
		$this->assertSame( $id, $info['ID'] );
		$this->assertStringContainsString( 'photo.jpg', $info['url'] );

		$bits     = wp_upload_bits( 'pixel.gif', null, base64_decode( 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' ) );
		$image_id = self::factory()->attachment->create_upload_object( $bits['file'] );
		$image    = RWMB_Image_Field::file_info( $image_id, [], $field );
		$this->assertSame( $image_id, $image['ID'] );

		$file_html = new ReflectionMethod( RWMB_File_Field::class, 'file_html' );
		if ( PHP_VERSION_ID < 80500 ) {
			$file_html->setAccessible( true );
		}
		$html = $file_html->invoke( null, (string) $id, 0, RWMB_File_Field::normalize( [
			'type'       => 'file',
			'id'         => 'legacy',
			'upload_dir' => $field['upload_dir'],
		] ) );
		$this->assertStringContainsString( 'photo.jpg', $html );
	}

	private function assertRejected( string $subdir, string $nonce ): void {
		rwmb_request()->set_post_data( [
			'rwmb_upload_subdir' => $subdir,
			'rwmb_upload_nonce'  => $nonce,
		] );
		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.txt',
			'error' => 0,
		] );

		$this->assertSame( 'You are not allowed to upload to this folder.', $result['error'] );
	}

	private function field_html( string $type, string $dir ): string {
		$class = 'image_upload' === $type ? RWMB_Image_Upload_Field::class : RWMB_File_Upload_Field::class;

		return $class::html( [], $class::normalize( [
			'type'       => $type,
			'id'         => 'docs',
			'upload_dir' => $dir,
		] ) );
	}

	private function post_subdir( string $subdir ): void {
		rwmb_request()->set_post_data( [
			'rwmb_upload_subdir' => $subdir,
			'rwmb_upload_nonce'  => wp_create_nonce( "rwmb-upload-dir|$subdir" ),
		] );
	}

	private function assertUploadPathUnchanged(): void {
		$original = _wp_upload_dir();
		$filtered = apply_filters( 'upload_dir', $original );

		$this->assertSame( wp_normalize_path( $original['path'] ), wp_normalize_path( $filtered['path'] ) );
	}

	private function remove_upload_filters(): void {
		global $wp_filter;
		if ( empty( $wp_filter['upload_dir'] ) ) {
			return;
		}

		$remove = [];
		foreach ( $wp_filter['upload_dir']->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$function = $callback['function'];
				if ( $function instanceof Closure ) {
					$remove[] = [ $function, $priority ];
				}
			}
		}
		foreach ( $remove as $item ) {
			remove_filter( 'upload_dir', $item[0], $item[1] );
		}
	}
}

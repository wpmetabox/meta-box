<?php
class UploadDirTest extends WP_UnitTestCase {
	public function tear_down(): void {
		remove_filter( 'upload_dir', [ 'RWMB_File_Upload_Field', 'custom_upload_dir' ] );
		remove_filter( 'wp_handle_upload_overrides', [ 'RWMB_File_Upload_Field', 'upload_overrides' ] );
		remove_filter( 'wp_handle_upload', [ 'RWMB_File_Upload_Field', 'finish_upload' ] );
		remove_filter( 'wp_handle_sideload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] );
		remove_filter( 'wp_handle_sideload_overrides', [ 'RWMB_File_Upload_Field', 'upload_overrides' ] );
		remove_filter( 'upload_dir', [ $this, 'filter_media_basedir' ] );
		rwmb_request()->set_post_data( [
			'rwmb_field_id'          => '',
			'rwmb_meta_box_id'       => '',
			'rwmb_upload_dir_nonce'  => '',
		] );

		parent::tear_down();
	}

	public function testFilterKeepsBasedirInsideUploads(): void {
		$uploads  = wp_upload_dir();
		$custom   = $uploads['basedir'] . '/files';
		$filtered = RWMB_File_Field::filter_custom_upload_dir( $uploads, $custom );

		$this->assertSame( wp_normalize_path( $uploads['basedir'] ), wp_normalize_path( $filtered['basedir'] ) );
		$this->assertSame( wp_normalize_path( $custom ), wp_normalize_path( $filtered['path'] ) );
		$this->assertSame( '/files', $filtered['subdir'] );
		$this->assertSame( untrailingslashit( $uploads['baseurl'] ) . '/files', $filtered['url'] );
	}

	public function testFilterReplacesBasedirOutsideUploads(): void {
		$uploads  = wp_upload_dir();
		$custom   = WP_CONTENT_DIR . '/invoices';
		$filtered = RWMB_File_Field::filter_custom_upload_dir( $uploads, $custom );
		$custom   = wp_normalize_path( untrailingslashit( $custom ) );

		$this->assertSame( $custom, wp_normalize_path( $filtered['basedir'] ) );
		$this->assertSame( $custom, wp_normalize_path( $filtered['path'] ) );
		$this->assertSame( '', $filtered['subdir'] );
	}

	public function testPathInsideRejectsTraversal(): void {
		$basedir = wp_upload_dir()['basedir'];

		$this->assertFalse( RWMB_File_Field::is_path_inside( $basedir . '/files/../../wp-config.php', $basedir ) );
		$this->assertFalse( RWMB_File_Field::is_path_inside( $basedir . '-secret', $basedir ) );
		$this->assertTrue( RWMB_File_Field::is_path_inside( $basedir . '/files', $basedir ) );
	}

	public function testPrefilterIgnoresRequestWithoutFieldParams(): void {
		$file = [
			'name'  => 'a.jpg',
			'error' => 0,
		];

		$this->assertSame( $file, RWMB_File_Upload_Field::filter_upload( $file ) );
		$this->assertFalse( has_filter( 'upload_dir', [ 'RWMB_File_Upload_Field', 'custom_upload_dir' ] ) );
	}

	public function testPrefilterRejectsDirOutsideUploads(): void {
		$this->register_box( 'box-outside', 'outside', ABSPATH . 'uploads' );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.jpg',
			'error' => 0,
		] );

		$this->assertIsString( $result['error'] );
		$this->assertFalse( has_filter( 'upload_dir', [ 'RWMB_File_Upload_Field', 'custom_upload_dir' ] ) );
	}

	public function testPrefilterKeepsBasedirForNestedField(): void {
		$custom   = wp_upload_dir()['basedir'] . '/gallery';
		$meta_box = rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'box-nested',
			'title'  => 'Nested',
			'fields' => [
				[
					'id'     => 'group',
					'type'   => 'group',
					'fields' => [
						[
							'type'       => 'image_upload',
							'id'         => 'gallery',
							'upload_dir' => $custom,
						],
					],
				],
			],
		] );
		$this->assertNotEmpty( $meta_box->fields );

		$this->send_upload_request( 'box-nested', 'gallery' );
		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.jpg',
			'error' => 0,
		] );

		$this->assertSame( 0, $result['error'] );
		$original = _wp_upload_dir();
		$filtered = apply_filters( 'upload_dir', $original );
		$this->assertSame( wp_normalize_path( $original['basedir'] ), wp_normalize_path( $filtered['basedir'] ) );
		$this->assertSame( wp_normalize_path( $custom ), wp_normalize_path( $filtered['path'] ) );

		$again = apply_filters( 'upload_dir', $original );
		$this->assertSame( wp_normalize_path( $original['path'] ), wp_normalize_path( $again['path'] ) );
	}

	public function testFileInfoKeepsAttachmentWhenUploadDirIsSet(): void {
		$id = self::factory()->post->create( [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image/jpeg',
			'post_title'     => 'Photo',
		] );
		update_post_meta( $id, '_wp_attached_file', '2026/11/photo.jpg' );

		$field = [
			'upload_dir' => wp_upload_dir()['basedir'] . '/files',
		];
		$info  = RWMB_File_Field::file_info( $id, [], $field );

		$this->assertIsArray( $info );
		$this->assertSame( $id, $info['ID'] );
		$this->assertStringContainsString( 'photo.jpg', $info['url'] );

		$url = RWMB_File_Field::file_info( 'http://example.com/files/a.pdf', [], $field );
		$this->assertSame( 'http://example.com/files/a.pdf', $url['url'] );

		$bits = wp_upload_bits(
			'pixel.gif',
			null,
			base64_decode( 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' )
		);
		$image_id = self::factory()->attachment->create_upload_object( $bits['file'] );
		$image    = RWMB_Image_Field::file_info( $image_id, [], $field );

		$this->assertIsArray( $image );
		$this->assertStringContainsString( basename( get_attached_file( $image_id ) ), $image['full_url'] );

		$file_html = new ReflectionMethod( RWMB_File_Field::class, 'file_html' );
		$file_html->setAccessible( true );
		$html = $file_html->invoke( null, (string) $id, 0, RWMB_File_Field::normalize( [
			'type'       => 'file',
			'id'         => 'legacy',
			'upload_dir' => $field['upload_dir'],
		] ) );
		$this->assertStringContainsString( 'photo.jpg', $html );
		$this->assertStringNotContainsString( 'rwmb-file-title">' . $id, $html );
	}

	public function testPrefilterIgnoresUnknownField(): void {
		$file = [
			'name'  => 'a.jpg',
			'error' => 0,
		];
		rwmb_request()->set_post_data( [
			'rwmb_field_id'    => 'missing',
			'rwmb_meta_box_id' => 'missing-box',
		] );

		$this->assertSame( $file, RWMB_File_Upload_Field::filter_upload( $file ) );
		$this->assertFalse( has_filter( 'upload_dir', [ 'RWMB_File_Upload_Field', 'custom_upload_dir' ] ) );
	}

	public function testPrefilterRejectsMissingNonce(): void {
		$this->register_box( 'box-nonce', 'secret', wp_upload_dir()['basedir'] . '/secret' );
		rwmb_request()->set_post_data( [
			'rwmb_upload_dir_nonce' => 'bad',
		] );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.jpg',
			'error' => 0,
		] );

		$this->assertSame( 'You are not allowed to upload to this folder.', $result['error'] );
		$this->assertFalse( has_filter( 'upload_dir', [ 'RWMB_File_Upload_Field', 'custom_upload_dir' ] ) );
	}

	public function testPrefilterAcceptsDirInsideFilteredBasedir(): void {
		add_filter( 'upload_dir', [ $this, 'filter_media_basedir' ] );
		$this->register_box( 'box-media', 'private_files', WP_CONTENT_DIR . '/media/private' );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.txt',
			'error' => 0,
		] );

		$this->assertSame( 0, $result['error'] );
		$this->assertNotFalse( has_filter( 'upload_dir', [ 'RWMB_File_Upload_Field', 'custom_upload_dir' ] ) );
	}

	public function testHandleUploadMovesFileIntoFieldDir(): void {
		$custom   = wp_upload_dir()['basedir'] . '/custom-docs';
		$filename = 'fixed-' . strtolower( wp_generate_password( 6, false ) );
		$this->register_box( 'box-real', 'docs', $custom, static function ( $dir, $name, $ext ) use ( $filename ) {
			return $filename . $ext;
		} );
		add_filter( 'wp_handle_sideload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] );
		add_filter( 'wp_handle_sideload_overrides', [ 'RWMB_File_Upload_Field', 'upload_overrides' ] );

		$tmp = wp_tempnam( 'note.txt' );
		file_put_contents( $tmp, 'hello' );
		$file = [
			'name'     => 'note.txt',
			'type'     => 'text/plain',
			'tmp_name' => $tmp,
			'error'    => 0,
			'size'     => filesize( $tmp ),
		];
		// Sideload action: is_uploaded_file() rejects a temp file that was not posted.
		$result = wp_handle_upload( $file, [
			'test_form' => false,
			'action'    => 'wp_handle_sideload',
		] );

		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'error', $result );
		$this->assertSame( $filename . '.txt', basename( $result['file'] ) );
		$this->assertStringStartsWith( wp_normalize_path( $custom ), wp_normalize_path( $result['file'] ) );
		$this->assertFileExists( $result['file'] );
		$this->assertFalse( has_filter( 'upload_dir', [ 'RWMB_File_Upload_Field', 'custom_upload_dir' ] ) );

		unlink( $result['file'] );
	}

	public function testEarlyUploadFailureClearsCustomDir(): void {
		$this->register_box( 'box-early', 'early', wp_upload_dir()['basedir'] . '/early' );
		$tmp  = wp_tempnam( 'note.txt' );
		file_put_contents( $tmp, 'hello' );
		$file = [
			'name'     => 'note.txt',
			'type'     => 'text/plain',
			'tmp_name' => $tmp,
			'error'    => 0,
			'size'     => filesize( $tmp ),
		];

		$result = wp_handle_upload( $file, [
			'test_form' => false,
		] );

		$this->assertIsString( $result['error'] );
		$this->assertFalse( has_filter( 'upload_dir', [ 'RWMB_File_Upload_Field', 'custom_upload_dir' ] ) );
		$this->assertFalse( has_filter( 'wp_handle_upload_overrides', [ 'RWMB_File_Upload_Field', 'upload_overrides' ] ) );

		unlink( $tmp );
	}

	public function filter_media_basedir( array $uploads ): array {
		$uploads['basedir'] = WP_CONTENT_DIR . '/media';
		$uploads['baseurl'] = content_url( 'media' );
		$uploads['subdir']  = '';
		$uploads['path']    = $uploads['basedir'];
		$uploads['url']     = $uploads['baseurl'];
		$uploads['error']   = false;

		return $uploads;
	}

	public function testNormalizeExposesUploadDirFlag(): void {
		$field = RWMB_File_Upload_Field::normalize( [
			'type'       => 'file_upload',
			'id'         => 'docs',
			'upload_dir' => wp_upload_dir()['basedir'] . '/docs',
		] );

		$this->assertTrue( $field['js_options']['uploadDir'] );
		$this->assertSame( 'docs', $field['js_options']['fieldId'] );
		$this->assertNotFalse( wp_verify_nonce( $field['js_options']['uploadDirNonce'], 'rwmb-upload-dir_docs' ) );

		$image = RWMB_Image_Upload_Field::normalize( [
			'type' => 'image_upload',
			'id'   => 'gallery',
		] );
		$this->assertArrayNotHasKey( 'uploadDir', $image['js_options'] );
	}

	private function register_box( string $box_id, string $field_id, string $dir, ?callable $callback = null ): void {
		$field = [
			'type'       => 'file_upload',
			'id'         => $field_id,
			'upload_dir' => $dir,
		];
		if ( $callback ) {
			$field['unique_filename_callback'] = $callback;
		}

		rwmb_get_registry( 'meta_box' )->make( [
			'id'     => $box_id,
			'title'  => $box_id,
			'fields' => [ $field ],
		] );
		$this->send_upload_request( $box_id, $field_id );
	}

	private function send_upload_request( string $box_id, string $field_id ): void {
		rwmb_request()->set_post_data( [
			'rwmb_field_id'         => $field_id,
			'rwmb_meta_box_id'      => $box_id,
			'rwmb_upload_dir_nonce' => wp_create_nonce( 'rwmb-upload-dir_' . $field_id ),
		] );
	}
}

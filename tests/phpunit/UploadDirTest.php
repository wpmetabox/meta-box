<?php
class UploadDirTest extends WP_UnitTestCase {
	public function tear_down(): void {
		$this->remove_scoped_closures( 'upload_dir' );
		$this->remove_scoped_closures( 'wp_handle_upload_overrides' );
		$this->remove_scoped_closures( 'wp_handle_sideload_overrides' );
		remove_filter( 'wp_handle_sideload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] );
		remove_filter( 'wp_handle_sideload_prefilter', [ $this, 'touch_upload_dir' ], 20 );
		remove_filter( 'upload_dir', [ $this, 'filter_media_basedir' ] );
		remove_filter( 'wp_handle_upload_prefilter', [ $this, 'touch_upload_dir' ], 20 );
		remove_filter( 'rwmb_normalize_field', [ $this, 'add_late_upload_dir' ] );
		rwmb_request()->set_post_data( [
			'rwmb_field_id'         => '',
			'rwmb_meta_box_id'      => '',
			'rwmb_field_name'       => '',
			'rwmb_upload_dir_nonce' => '',
		] );

		parent::tear_down();
	}

	public function touch_upload_dir( $file ) {
		wp_upload_dir();

		return $file;
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
		$this->assertUploadPathUnchanged();
	}

	public function testPrefilterRejectsDirOutsideUploads(): void {
		$this->register_box( 'box-outside', 'outside', ABSPATH . 'uploads' );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.jpg',
			'error' => 0,
		] );

		$this->assertIsString( $result['error'] );
		$this->assertUploadPathUnchanged();
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

		$this->send_upload_request( 'box-nested', 'gallery', 'group[gallery][]' );
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
		$this->assertSame( wp_normalize_path( $custom ), wp_normalize_path( $again['path'] ) );
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
		if ( PHP_VERSION_ID < 80500 ) {
			$file_html->setAccessible( true );
		}
		$html = $file_html->invoke( null, (string) $id, 0, RWMB_File_Field::normalize( [
			'type'       => 'file',
			'id'         => 'legacy',
			'upload_dir' => $field['upload_dir'],
		] ) );
		$this->assertStringContainsString( 'photo.jpg', $html );
		$this->assertStringNotContainsString( 'rwmb-file-title">' . $id, $html );
	}

	public function testPrefilterRejectsUnknownField(): void {
		rwmb_request()->set_post_data( [
			'rwmb_field_id'    => 'missing',
			'rwmb_meta_box_id' => 'missing-box',
		] );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.jpg',
			'error' => 0,
		] );

		$this->assertSame( 'The field for this upload could not be found.', $result['error'] );
		$this->assertUploadPathUnchanged();
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
		$this->assertUploadPathUnchanged();
	}

	public function testNonceDoesNotWorkForAnotherMetaBox(): void {
		$dir_a = wp_upload_dir()['basedir'] . '/box-a';
		$dir_b = wp_upload_dir()['basedir'] . '/box-b';
		$this->register_box( 'box-a', 'docs', $dir_a );
		$this->register_box( 'box-b', 'docs', $dir_b );
		rwmb_request()->set_post_data( [
			'rwmb_field_id'         => 'docs',
			'rwmb_meta_box_id'      => 'box-b',
			'rwmb_field_name'       => 'docs[]',
			'rwmb_upload_dir_nonce' => wp_create_nonce( RWMB_File_Upload_Field::upload_nonce_action( 'box-a', 'docs', 'docs[]' ) ),
		] );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.jpg',
			'error' => 0,
		] );

		$this->assertSame( 'You are not allowed to upload to this folder.', $result['error'] );
		$this->assertUploadPathUnchanged();
	}

	public function testDuplicateSubFieldUsesFieldName(): void {
		$dir_one = wp_upload_dir()['basedir'] . '/group-one';
		$dir_two = wp_upload_dir()['basedir'] . '/group-two';
		rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'box-dup',
			'title'  => 'Dup',
			'fields' => [
				[
					'id'     => 'group_one',
					'type'   => 'group',
					'fields' => [
						[
							'type'       => 'file_upload',
							'id'         => 'docs',
							'upload_dir' => $dir_one,
						],
					],
				],
				[
					'id'     => 'group_two',
					'type'   => 'group',
					'fields' => [
						[
							'type'       => 'file_upload',
							'id'         => 'docs',
							'upload_dir' => $dir_two,
						],
					],
				],
			],
		] );
		$this->send_upload_request( 'box-dup', 'docs', 'group_two[docs][]' );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.txt',
			'error' => 0,
		] );

		$this->assertSame( 0, $result['error'] );
		$filtered = apply_filters( 'upload_dir', _wp_upload_dir() );
		$this->assertSame( wp_normalize_path( $dir_two ), wp_normalize_path( $filtered['path'] ) );
	}

	public function testNestedNameDoesNotUseAnotherFieldWithTheSameId(): void {
		$dir_one = wp_upload_dir()['basedir'] . '/group-one-miss';
		$dir_two = wp_upload_dir()['basedir'] . '/group-two-miss';
		rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'box-dup-miss',
			'title'  => 'Dup miss',
			'fields' => [
				[
					'id'     => 'group_one',
					'type'   => 'group',
					'fields' => [
						[
							'type'       => 'file_upload',
							'id'         => 'docs',
							'upload_dir' => $dir_one,
						],
					],
				],
				[
					'id'     => 'group_two',
					'type'   => 'group',
					'fields' => [
						[
							'type'       => 'file_upload',
							'id'         => 'docs',
							'upload_dir' => $dir_two,
						],
					],
				],
			],
		] );
		$this->send_upload_request( 'box-dup-miss', 'docs', 'g2[docs][]' );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.txt',
			'error' => 0,
		] );

		$this->assertSame( 'The field for this upload could not be found.', $result['error'] );
		$this->assertUploadPathUnchanged();
	}

	public function testPrefilterAcceptsDirInsideFilteredBasedir(): void {
		add_filter( 'upload_dir', [ $this, 'filter_media_basedir' ] );
		$this->register_box( 'box-media', 'private_files', WP_CONTENT_DIR . '/media/private' );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.txt',
			'error' => 0,
		] );

		$this->assertSame( 0, $result['error'] );
		$filtered = apply_filters( 'upload_dir', _wp_upload_dir() );
		$this->assertSame( wp_normalize_path( WP_CONTENT_DIR . '/media/private' ), wp_normalize_path( $filtered['path'] ) );
	}

	public function testHandleUploadMovesFileIntoFieldDir(): void {
		$custom   = wp_upload_dir()['basedir'] . '/custom-docs';
		$filename = 'fixed-' . strtolower( wp_generate_password( 6, false ) );
		$this->register_box( 'box-real', 'docs', $custom, static function ( $dir, $name, $ext ) use ( $filename ) {
			return $filename . $ext;
		} );
		add_filter( 'wp_handle_sideload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] );
		add_filter( 'wp_handle_sideload_prefilter', [ $this, 'touch_upload_dir' ], 20 );

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

		unlink( $result['file'] );
	}

	public function testEarlyUploadFailureReturnsError(): void {
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

	public function testNormalizeDoesNotExposeUploadDirFlag(): void {
		$field = RWMB_File_Upload_Field::normalize( [
			'type'       => 'file_upload',
			'id'         => 'docs',
			'upload_dir' => wp_upload_dir()['basedir'] . '/docs',
		] );

		$this->assertArrayNotHasKey( 'uploadDir', $field['js_options'] );
		$this->assertArrayNotHasKey( 'uploadDirNonce', $field['js_options'] );
	}

	public function add_late_upload_dir( array $field ): array {
		if ( ( $field['id'] ?? '' ) === 'late' ) {
			$field['upload_dir'] = wp_upload_dir()['basedir'] . '/late';
		}

		return $field;
	}

	public function testShowExposesUploadDirAddedAfterNormalize(): void {
		add_filter( 'rwmb_normalize_field', [ $this, 'add_late_upload_dir' ] );
		$box = rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'box-late',
			'title'  => 'Late',
			'fields' => [
				[
					'type' => 'file_upload',
					'id'   => 'late',
				],
			],
		] );

		ob_start();
		$box->show();
		$html = ob_get_clean();

		$this->assertStringContainsString( '&quot;uploadDir&quot;:true', $html );
		$nonce = wp_create_nonce( RWMB_File_Upload_Field::upload_nonce_action( 'box-late', 'late', 'late[]' ) );
		$this->assertStringContainsString( $nonce, $html );
	}

	public function testShowOutputsNonceForMetaBoxAndField(): void {
		$box = rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'box-show',
			'title'  => 'Show',
			'fields' => [
				[
					'type'       => 'file_upload',
					'id'         => 'docs',
					'upload_dir' => wp_upload_dir()['basedir'] . '/docs',
				],
			],
		] );

		ob_start();
		$box->show();
		$html = ob_get_clean();

		$nonce = wp_create_nonce( RWMB_File_Upload_Field::upload_nonce_action( 'box-show', 'docs', 'docs[]' ) );
		$this->assertStringContainsString( $nonce, $html );
		$this->assertStringContainsString( '&quot;uploadDir&quot;:true', $html );
	}

	public function testHtmlSetsUploadOptionsOutsideMetaBoxShow(): void {
		$dir = wp_upload_dir()['basedir'] . '/modal-docs';
		$this->register_box( 'box-modal', 'docs', $dir );
		$field               = rwmb_get_registry( 'meta_box' )->get( 'box-modal' )->fields[0];
		$field['field_name'] = 'attachments[5][docs]';

		$html  = RWMB_File_Upload_Field::html( '', $field );
		$nonce = wp_create_nonce( RWMB_File_Upload_Field::upload_nonce_action( 'box-modal', 'docs', 'attachments[5][docs][]' ) );
		$this->assertStringContainsString( $nonce, $html );

		$this->send_upload_request( 'box-modal', 'docs', 'attachments[5][docs][]' );
		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.txt',
			'error' => 0,
		] );

		$this->assertSame( 0, $result['error'] );
		$filtered = apply_filters( 'upload_dir', _wp_upload_dir() );
		$this->assertSame( wp_normalize_path( $dir ), wp_normalize_path( $filtered['path'] ) );
	}

	public function testGroupFieldNameMatchesWhenItDiffersFromId(): void {
		$dir = wp_upload_dir()['basedir'] . '/named-group';
		rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'box-named',
			'title'  => 'Named',
			'fields' => [
				[
					'id'         => 'group_one',
					'type'       => 'group',
					'field_name' => 'g',
					'fields'     => [
						[
							'type'       => 'file_upload',
							'id'         => 'docs',
							'upload_dir' => $dir,
						],
					],
				],
			],
		] );
		$this->send_upload_request( 'box-named', 'docs', 'g[docs][]' );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.txt',
			'error' => 0,
		] );

		$this->assertSame( 0, $result['error'] );
		$filtered = apply_filters( 'upload_dir', _wp_upload_dir() );
		$this->assertSame( wp_normalize_path( $dir ), wp_normalize_path( $filtered['path'] ) );
	}

	public function testNonceDoesNotCrossGroupsWithTheSameFieldId(): void {
		$dir_one = wp_upload_dir()['basedir'] . '/nonce-one';
		$dir_two = wp_upload_dir()['basedir'] . '/nonce-two';
		rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'box-nonce-groups',
			'title'  => 'Nonce groups',
			'fields' => [
				[
					'id'     => 'group_one',
					'type'   => 'group',
					'fields' => [
						[
							'type'       => 'file_upload',
							'id'         => 'docs',
							'upload_dir' => $dir_one,
						],
					],
				],
				[
					'id'     => 'group_two',
					'type'   => 'group',
					'fields' => [
						[
							'type'       => 'file_upload',
							'id'         => 'docs',
							'upload_dir' => $dir_two,
						],
					],
				],
			],
		] );
		$this->send_upload_request( 'box-nonce-groups', 'docs', 'group_two[docs][]' );
		rwmb_request()->set_post_data( [
			'rwmb_upload_dir_nonce' => wp_create_nonce( RWMB_File_Upload_Field::upload_nonce_action( 'box-nonce-groups', 'docs', 'group_one[docs][]' ) ),
		] );

		$result = RWMB_File_Upload_Field::filter_upload( [
			'name'  => 'a.txt',
			'error' => 0,
		] );

		$this->assertSame( 'You are not allowed to upload to this folder.', $result['error'] );
		$this->assertUploadPathUnchanged();
		$this->assertNotSame(
			RWMB_File_Upload_Field::upload_nonce_action( 'a_b', 'c', 'c[]' ),
			RWMB_File_Upload_Field::upload_nonce_action( 'a', 'b_c', 'b_c[]' )
		);
	}

	public function testRealPrefilterKeepsCustomDir(): void {
		$this->assertNotFalse( has_filter( 'wp_handle_upload_prefilter', [ 'RWMB_File_Upload_Field', 'filter_upload' ] ) );
		$custom = wp_upload_dir()['basedir'] . '/real-hook';
		$this->register_box( 'box-real-hook', 'docs', $custom );
		add_filter( 'wp_handle_upload_prefilter', [ $this, 'touch_upload_dir' ], 20 );

		$tmp  = wp_tempnam( 'note.txt' );
		file_put_contents( $tmp, 'hello' );
		$file = [
			'name'     => 'note.txt',
			'type'     => 'text/plain',
			'tmp_name' => $tmp,
			'error'    => 0,
			'size'     => filesize( $tmp ),
		];
		$result = wp_handle_upload( $file, [ 'test_form' => false ] );

		$this->assertIsString( $result['error'] );
		$filtered = apply_filters( 'upload_dir', _wp_upload_dir() );
		$this->assertSame( wp_normalize_path( $custom ), wp_normalize_path( $filtered['path'] ) );
		unlink( $tmp );
	}

	public function testImageUploadRealHookUsesFieldDir(): void {
		$custom = wp_upload_dir()['basedir'] . '/gallery';
		rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'box-image',
			'title'  => 'Images',
			'fields' => [
				[
					'type'       => 'image_upload',
					'id'         => 'gallery',
					'upload_dir' => $custom,
				],
			],
		] );
		$this->send_upload_request( 'box-image', 'gallery' );

		$tmp  = wp_tempnam( 'pixel.gif' );
		file_put_contents( $tmp, 'gif' );
		$file = [
			'name'     => 'pixel.gif',
			'type'     => 'image/gif',
			'tmp_name' => $tmp,
			'error'    => 0,
			'size'     => filesize( $tmp ),
		];
		$result = wp_handle_upload( $file, [ 'test_form' => false ] );

		$this->assertIsString( $result['error'] );
		$filtered = apply_filters( 'upload_dir', _wp_upload_dir() );
		$this->assertSame( wp_normalize_path( $custom ), wp_normalize_path( $filtered['path'] ) );
		unlink( $tmp );
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

	private function send_upload_request( string $box_id, string $field_id, string $field_name = '' ): void {
		$field_name = $field_name ?: $field_id . '[]';
		rwmb_request()->set_post_data( [
			'rwmb_field_id'         => $field_id,
			'rwmb_meta_box_id'      => $box_id,
			'rwmb_field_name'       => $field_name,
			'rwmb_upload_dir_nonce' => wp_create_nonce( RWMB_File_Upload_Field::upload_nonce_action( $box_id, $field_id, $field_name ) ),
		] );
	}

	private function assertUploadPathUnchanged(): void {
		$original = _wp_upload_dir();
		$filtered = apply_filters( 'upload_dir', $original );

		$this->assertSame( wp_normalize_path( $original['path'] ), wp_normalize_path( $filtered['path'] ) );
	}

	private function remove_scoped_closures( string $hook ): void {
		global $wp_filter;
		if ( empty( $wp_filter[ $hook ] ) ) {
			return;
		}

		$remove = [];
		foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$function = $callback['function'];
				if ( ! $function instanceof Closure ) {
					continue;
				}
				$scope = ( new ReflectionFunction( $function ) )->getClosureScopeClass();
				if ( $scope && RWMB_File_Upload_Field::class === $scope->getName() ) {
					$remove[] = [ $function, $priority ];
				}
			}
		}
		foreach ( $remove as $item ) {
			remove_filter( $hook, $item[0], $item[1] );
		}
	}
}

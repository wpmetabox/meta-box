<?php
class UploadDirTest extends WP_UnitTestCase {
	public function tear_down(): void {
		remove_filter( 'upload_dir', [ 'RWMB_File_Upload_Field', 'custom_upload_dir' ] );
		remove_filter( 'wp_handle_upload_overrides', [ 'RWMB_File_Upload_Field', 'upload_overrides' ] );
		rwmb_request()->set_post_data( [
			'rwmb_field_id'    => '',
			'rwmb_meta_box_id' => '',
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

		rwmb_request()->set_post_data( [
			'rwmb_field_id'    => 'gallery',
			'rwmb_meta_box_id' => 'box-nested',
		] );
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

		$image = RWMB_Image_Field::file_info( $id, [], $field );
		$image_url = is_array( $image ) ? $image['url'] : null;
		$this->assertNotSame( $id, $image_url );
	}

	public function testNormalizeExposesUploadDirFlag(): void {
		$field = RWMB_File_Upload_Field::normalize( [
			'type'       => 'file_upload',
			'id'         => 'docs',
			'upload_dir' => wp_upload_dir()['basedir'] . '/docs',
		] );

		$this->assertTrue( $field['js_options']['uploadDir'] );
		$this->assertSame( 'docs', $field['js_options']['fieldId'] );

		$image = RWMB_Image_Upload_Field::normalize( [
			'type' => 'image_upload',
			'id'   => 'gallery',
		] );
		$this->assertArrayNotHasKey( 'uploadDir', $image['js_options'] );
	}

	private function register_box( string $box_id, string $field_id, string $dir ): void {
		rwmb_get_registry( 'meta_box' )->make( [
			'id'     => $box_id,
			'title'  => $box_id,
			'fields' => [
				[
					'type'       => 'file_upload',
					'id'         => $field_id,
					'upload_dir' => $dir,
				],
			],
		] );
		rwmb_request()->set_post_data( [
			'rwmb_field_id'    => $field_id,
			'rwmb_meta_box_id' => $box_id,
		] );
	}
}

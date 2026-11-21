<?php
class FileSanitizeTest extends WP_UnitTestCase {
	public function testKeepsAttachmentIdWhenUploadDirIsSet(): void {
		$sanitizer = new RWMB_Sanitizer();
		$field     = [
			'type'              => 'file',
			'upload_dir'        => '/uploads/docs',
			'sanitize_callback' => null,
		];

		$this->assertSame(
			[ 123, 'https://example.com/a.jpg' ],
			$sanitizer->sanitize( [ '123', 'https://example.com/a.jpg', '../secret.jpg' ], $field )
		);

		$field['type'] = 'image';
		$this->assertSame( [ 456 ], $sanitizer->sanitize( [ '456' ], $field ) );
	}
}

<?php
use PHPUnit\Framework\TestCase;
use MetaBox\Support\Arr;

class StdTest extends TestCase {
	public function testTextStd() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$field = [
			'id'   => 'text',
			'type' => 'text',
		];

		$field = RWMB_Field::call( 'normalize', $field );

		// 1. Empty std if no std is set
		$std = RWMB_Field::call( 'get_std', $field );

		$this->assertEquals( '', $std );

		// 2. If we set the std value to 'default', it should return the std value
		$std = RWMB_Field::call( 'get_std', [
			...$field,
			'std' => 'default',
		] );

		$this->assertEquals( 'default', $std );

		// 3. If clone is set, the std value should be an array
		$std = RWMB_Field::call( 'get_std', [
			...$field,
			'clone' => true,
			'std'   => 'default',
		] );

		$this->assertIsArray( $std );
		$this->assertEquals( [ 'default' ], $std );

		// 4. If std is set to an array, it should return the array
		$field = RWMB_Field::call( 'normalize', [
			...$field,
			'clone' => true,
			'std'   => [ 'default' ],
		] );

		$std = RWMB_Field::call( 'get_std', $field );
		$this->assertIsArray( $std );
		$this->assertEquals( [ 'default' ], $std );

		// Multiple values in std
		$field = RWMB_Field::call( 'normalize', [
			...$field,
			'clone' => true,
			'std'   => [ 'default', 'default2' ],
		] );

		$std = RWMB_Field::call( 'get_std', $field );
		$this->assertIsArray( $std );
		$this->assertEquals( [ 'default', 'default2' ], $std );
	}

	public function testStdForCheckboxList() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$std_case_1 = 'a';
		$std_case_2 = [ 'a' ];
		$std_case_3 = [ 'a', 'b' ];
		$std_case_4 = [
			[ 'a', 'b' ],
			[ 'c', 'd' ],
		];

		$field = [
			'id'      => 'checkbox_list',
			'type'    => 'checkbox_list',
			'clone'   => true,
			'options' => [
				'a' => 'A',
				'b' => 'B',
				'c' => 'C',
				'd' => 'D',
			],
			'std'     => $std_case_1,
		];

		$field = RWMB_Field::call( 'normalize', $field );

		$this->assertEquals( [ [ 'a' ] ], RWMB_Field::call( 'get_std', $field ) );

		$field = RWMB_Field::call( 'normalize', [
			...$field,
			'std' => $std_case_2,
		] );

		$this->assertEquals( [ [ 'a' ] ], RWMB_Field::call( 'get_std', $field ) );

		$field = RWMB_Field::call( 'normalize', [
			...$field,
			'std' => $std_case_3,
		] );

		$this->assertEquals( [ [ 'a', 'b' ] ], RWMB_Field::call( 'get_std', $field ) );

		$field = RWMB_Field::call( 'normalize', [
			...$field,
			'std' => $std_case_4,
		] );

		$this->assertEquals( [ [ 'a', 'b' ], [ 'c', 'd' ] ], RWMB_Field::call( 'get_std', $field ) );
	}

	public function testStdForGroup() {
		if ( ! class_exists( 'RWMB_Group' ) ) {
			$this->markTestSkipped( 'Meta Box Group is not active' );
		}

		$group_std = [
			[
				'text'   => 'a',
				'group2' => [
					[ 'text2' => 'b' ],
					[ 'text2' => 'c' ],
				],
			],
			[
				'text'   => 'd',
				'group2' => [
					[ 'text2' => 'e' ],
					[ 'text2' => 'f' ],
				],
			],
		];
		$field     = [
			'type'   => 'group',
			'id'     => 'group',
			'clone'  => true,
			'name'   => 'Group 1',
			'std'    => $group_std,
			'fields' => [
				[
					'id'                => 'text',
					'type'              => 'text',
					'name'              => 'Text 1',
					'clone'             => true,
					'clone_empty_start' => true,
				],
				[
					'id'     => 'group2',
					'type'   => 'group',
					'clone'  => true,
					'name'   => 'Group 2',
					'fields' => [
						[
							'id'                => 'text2',
							'type'              => 'text',
							'name'              => 'Text 2',
							'clone'             => true,
							'clone_empty_start' => true,
						],
					],
				],
			],
		];

		$field = RWMB_Field::call( 'normalize', $field );

		$std = RWMB_Field::call( 'get_std', $field );

		$this->assertEquals( $group_std, $std );
	}
}

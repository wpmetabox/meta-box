<?php
use PHPUnit\Framework\TestCase;

class StdTest extends TestCase {
    public function testTextStd() {
        $field = [
            'id' => 'text',
            'type' => 'text',
        ];
        
        $field = RWMB_Field::call('normalize', $field);
        
        // 1. Empty std if no std is set
        $std = RWMB_Field::call('get_std', $field );
        $this->assertEquals('', $std);

        // 2. If we set the std value to 'default', it should return the std value
        $std = RWMB_Field::call('get_std', [...$field, 
            'std' => 'default',
        ]);

        $this->assertEquals('default', $std);

        // 3. If clone is set, the std value should be an array
        $std = RWMB_Field::call('get_std', [
            ...$field,
            'clone' => true,
            'std' => 'default',
        ]);
        $this->assertIsArray( $std );
        $this->assertEquals('default', $std[0]);

        // 4. If std is set to an array, it should return the array
        $field = RWMB_Field::call('normalize', [
            ...$field,
            'clone'=> true,
            'std' => ['default'],
        ]);

        $std = RWMB_Field::call('get_std', $field );

        $this->assertIsArray( $std );
        $this->assertEquals('default', $std[0]);
    }
}
<?php

namespace tigrov\tests\unit\pgsql;

abstract class AbstractColumnSchemaArrayTest extends AbstractColumnSchemaTest
{
    /**
     * @dataProvider arrayValuesProvider
     */
    public function testArrayDbTypecast($expected, $value)
    {
        $this->assertSame($expected, $this->fixture->dbTypecast($value));
    }

    /**
     * @dataProvider arrayValuesProvider
     */
    public function testArrayPhpTypecast($value, $expected)
    {
        $this->assertSame($expected, $this->fixture->phpTypecast($value));
    }

    public function arrayValuesProvider()
    {
        return [
            ['{}', []],
            ['{NULL}', [null]],
            ['{NULL,NULL,NULL}', [null, null, null]],
        ];
    }
}
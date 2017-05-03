<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaBitsTest extends AbstractColumnSchemaArrayTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'bits',
            'allowNull' => true,
            'type' => 'bit',
            'phpType' => 'integer',
            'dbType' => 'varbit',
            'defaultValue' => NULL,
            'enumValues' => NULL,
            'size' => NULL,
            'precision' => NULL,
            'scale' => NULL,
            'isPrimaryKey' => false,
            'unsigned' => false,
            'comment' => NULL,
            'dimension' => 1,
            'delimiter' => ',',
        ]);
    }

    public function valuesProvider()
    {
        return [
            ['{"0"}', [0]],
            ['{"1"}', [1]],
            ['{"1000","1111",NULL}', [8, 15, null]],
        ];
    }

    public function testAdditionalPhpTypecast()
    {
        $this->assertEquals([0], $this->fixture->phpTypecast('{0}'));
        $this->assertEquals([1], $this->fixture->phpTypecast('{1}'));
        $this->assertEquals([8, 15, null], $this->fixture->phpTypecast('{1000,1111,NULL}'));
    }
}
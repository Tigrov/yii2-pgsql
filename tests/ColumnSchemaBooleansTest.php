<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaBooleansTest extends AbstractColumnSchemaArrayTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'booleans',
            'allowNull' => true,
            'type' => 'boolean',
            'phpType' => 'boolean',
            'dbType' => 'bool',
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
            ['{true}', [true]],
            ['{false}', [false]],
            ['{true,false,NULL}', [true, false, null]],
        ];
    }

    public function testAdditionalPhpTypecast()
    {
        $this->assertSame([true], $this->fixture->phpTypecast('{t}'));
        $this->assertSame([false], $this->fixture->phpTypecast('{f}'));
    }
}
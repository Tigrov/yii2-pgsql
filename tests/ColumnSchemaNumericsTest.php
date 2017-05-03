<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaNumericsTest extends AbstractColumnSchemaArrayTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'numerics',
            'allowNull' => true,
            'type' => 'decimal',
            'phpType' => 'string',
            'dbType' => 'numeric',
            'defaultValue' => NULL,
            'enumValues' => NULL,
            'size' => NULL,
            'precision' => 10,
            'scale' => 2,
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
            ['{"0"}', ['0']],
            ['{"0.00"}', ['0.00']],
            ['{"-1.5"}', ['-1.5']],
            ['{"-1.50"}', ['-1.50']],
            ['{"1.50","-1.50",NULL}', ['1.50', '-1.50', null]],
        ];
    }

    public function testAdditionalPhpTypecast()
    {
        $this->assertEquals(['0'], $this->fixture->phpTypecast('{0}'));
        $this->assertEquals(['0.00'], $this->fixture->phpTypecast('{0.00}'));
        $this->assertEquals(['-1.00'], $this->fixture->phpTypecast('{-1.00}'));
        $this->assertEquals(['-1.50'], $this->fixture->phpTypecast('{-1.50}'));
    }
}
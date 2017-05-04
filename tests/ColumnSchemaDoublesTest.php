<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaDoublesTest extends AbstractColumnSchemaArrayTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'doubles',
            'allowNull' => true,
            'type' => 'double',
            'phpType' => 'double',
            'dbType' => 'float8',
            'defaultValue' => NULL,
            'enumValues' => NULL,
            'size' => NULL,
            'precision' => 53,
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
            ['{0}', [0.0]],
            ['{-1.5}', [-1.5]],
            ['{1.5,-1.5,NULL}', [1.5, -1.5, null]],
        ];
    }
}
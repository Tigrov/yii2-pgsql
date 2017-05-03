<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaBitTest extends AbstractColumnSchemaTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'bit',
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
            'dimension' => 0,
            'delimiter' => ',',
        ]);
    }

    public function valuesProvider()
    {
        return [
            ['0', 0],
            ['1', 1],
            ['1000', 8],
            ['1111', 15],
        ];
    }
}
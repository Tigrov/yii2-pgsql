<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaJsonTest extends AbstractColumnSchemaTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'json',
            'allowNull' => true,
            'type' => 'json',
            'phpType' => 'array',
            'dbType' => 'jsonb',
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
            ['[]', []],
            ['""', ''],
            ['true', true],
            ['false', false],
            ['0', 0],
            ['1.5', 1.5],
            ['-1.5', -1.5],
            ['"string"', 'string'],
            ['[""]', ['']],
            ['["string"]', ['string']],
            ['["string",0,false,null]', ['string',0, false, null]],
            ['{"key":"value"}', ['key' => 'value']],
            ['{"key1":"value1","key2":true,"key3":false,"key4":"","key5":null}', ['key1' => 'value1', 'key2' => true, 'key3' => false, 'key4' => '', 'key5' => null]],
            ['{"key":{"key":{"key":"value"}}}', ['key' => ['key' => ['key' => 'value']]]],
        ];
    }
}
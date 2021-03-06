<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

/**
 * Type Schema::TYPE_DATETIME (datetime) never used, but it has mentioned in yii\db\pgsql\QueryBuilder::$typeMap
 */
class ColumnSchemaDatetimeTest extends ColumnSchemaTimestampTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'datetime',
            'allowNull' => true,
            'type' => 'datetime',
            'phpType' => 'string',
            'dbType' => 'timestamp',
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

        $this->mockApplication();
    }
}
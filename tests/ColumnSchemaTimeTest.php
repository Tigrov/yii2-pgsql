<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaTimeTest extends AbstractColumnSchemaTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'time',
            'allowNull' => true,
            'type' => 'time',
            'phpType' => 'string',
            'dbType' => 'time',
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

    public function valuesProvider()
    {
        return [
            ['00:00:00', new \DateTime('00:00:00'), false],
            ['17:50:32', new \DateTime('17:50:32'), false],
            ['23:59:59', new \DateTime('23:59:59'), false],
        ];
    }

    public function testAdditionalDbTypecast()
    {
        $this->assertEquals('17:50:32', $this->fixture->dbTypecast('17:50:32'));
        $this->assertEquals('17:50:32', $this->fixture->dbTypecast(1493747432));
    }
}
<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaDateTest extends AbstractColumnSchemaTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'date',
            'allowNull' => true,
            'type' => 'date',
            'phpType' => 'string',
            'dbType' => 'date',
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
            ['1901-01-01', new \DateTime('1901-01-01'), false],
            ['2017-05-02', new \DateTime('2017-05-02'), false],
        ];
    }

    public function testAdditionalDbTypecast()
    {
        $this->assertEquals('2017-05-02', $this->fixture->dbTypecast('2017-05-02'));
        $this->assertEquals('2017-05-02', $this->fixture->dbTypecast(1493747432));
    }
}
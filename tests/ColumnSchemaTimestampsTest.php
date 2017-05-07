<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaTimestampsTest extends AbstractColumnSchemaTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'timestamps',
            'allowNull' => true,
            'type' => 'timestamp',
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
            'dimension' => 1,
            'delimiter' => ',',
        ]);

        $this->mockApplication();
    }

    public function valuesProvider()
    {
        return [
            ['{"1901-01-01 00:00:00"}', [new \DateTime('1901-01-01')], false],
            ['{"2017-05-02 17:50:32"}', [new \DateTime('2017-05-02 17:50:32')], false],
            ['{"1901-01-01 00:00:00","2017-05-02 17:50:32"}', [new \DateTime('1901-01-01'), new \DateTime('2017-05-02 17:50:32')], false],
        ];
    }

    public function testAdditionalDbTypecast()
    {
        $this->assertEquals('{"2017-05-02 17:50:32","2017-05-02 17:50:32"}', $this->fixture->dbTypecast(['2017-05-02 17:50:32', 1493747432]));
    }

    public function testAdditionalPhpTypecast()
    {
        $this->assertEquals([new \DateTime('1901-01-01'), new \DateTime('2017-05-02 17:50:32')], $this->fixture->phpTypecast('{1901-01-01 00:00:00,2017-05-02 17:50:32}'));
    }
}
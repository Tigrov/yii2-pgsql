<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

abstract class AbstractColumnSchemaTest extends TestCase
{
    /**
     * @var ColumnSchema
     */
    protected $fixture;

    protected function tearDown()
    {
        parent::tearDown();

        $this->fixture = null;
    }

    public function testNullDbTypecast()
    {
        $this->assertNull($this->fixture->dbTypecast(null));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testDbTypecast($expected, $value, $isSame = true)
    {
        $this->assertSame($expected, $this->fixture->dbTypecast($value));
    }

    public function testNullPhpTypecast()
    {
        $this->assertNull($this->fixture->phpTypecast(null));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testPhpTypecast($value, $expected, $isSame = true)
    {
        $assertMethod = $isSame ? 'assertSame' : 'assertEquals';
        $this->$assertMethod($expected, $this->fixture->phpTypecast($value));
    }

    abstract public function valuesProvider();
}
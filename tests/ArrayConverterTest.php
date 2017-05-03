<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ArrayConverter;

class ArrayConverterTest extends TestCase
{
    /**
     * @var ArrayConverter
     */
    protected $fixture;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ArrayConverter(['delimiter' => ',']);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->fixture = null;
    }

    public function testNullToDb()
    {
        $this->assertNull($this->fixture->toDb(null));
    }

    public function testBooleanToDb()
    {
        $this->assertEquals('{false}', $this->fixture->toDb([false]));
        $this->assertEquals('{true}', $this->fixture->toDb([true]));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testToDb($expected, $value)
    {
        $this->assertEquals($expected, $this->fixture->toDb($value));
    }

    public function testNullToPhp()
    {
        $this->assertNull($this->fixture->toPhp(null));
    }

    public function testBooleanToPhp()
    {
        // Typecasting for boolean type realized in ColumnSchema
        $this->assertEquals(['f'], $this->fixture->toPhp('{f}'));
        $this->assertEquals(['t'], $this->fixture->toPhp('{t}'));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testToPhp($value, $expected)
    {
        $this->assertEquals($expected, $this->fixture->toPhp($value));
    }

    public function testAdditionalToPhp()
    {
        $this->assertEquals(['string'], $this->fixture->toPhp('{string}'));
        $this->assertEquals(['string1', ',', 'string3'], $this->fixture->toPhp('{string1,",",string3}'));
    }

    public function valuesProvider()
    {
        return [
            ['{}', []],
            ['{NULL}', [null]],
            ['{0}', [0]],
            ['{-1}', [-1]],
            ['{1.5}', [1.5]],
            ['{""}', ['']],
            ['{1,2,3}', [1,2,3]],
            ['{"string1","str\\\\in\\"g2","str,ing3"}', ['string1','str\\in"g2','str,ing3']],
            ['{"null","NULL",NULL}', ['null','NULL',null]],
        ];
    }
}
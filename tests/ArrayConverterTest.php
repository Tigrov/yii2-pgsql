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
        $this->assertSame('{false}', $this->fixture->toDb([false]));
        $this->assertSame('{true}', $this->fixture->toDb([true]));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testToDb($expected, $value, $isSame = true)
    {
        $this->assertSame($expected, $this->fixture->toDb($value));
    }

    public function testNullToPhp()
    {
        $this->assertNull($this->fixture->toPhp(null));
    }

    public function testBooleanToPhp()
    {
        // Typecasting for boolean type realized in ColumnSchema
        $this->assertSame(['f'], $this->fixture->toPhp('{f}'));
        $this->assertSame(['t'], $this->fixture->toPhp('{t}'));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testToPhp($value, $expected, $isSame = true)
    {
        $assertMethod = $isSame ? 'assertSame' : 'assertEquals';
        $this->$assertMethod($expected, $this->fixture->toPhp($value));
    }

    public function testAdditionalToDb()
    {
        $this->assertNull($this->fixture->toDb(''));
    }

    public function testAdditionalToPhp()
    {
        $this->assertNull($this->fixture->toPhp(''));
        $this->assertSame(['',''], $this->fixture->toPhp('{,}'));
        $this->assertSame(['','',null], $this->fixture->toPhp('{,,NULL}'));
        $this->assertSame(['.'], $this->fixture->toPhp('{.}'));
        $this->assertSame(['string'], $this->fixture->toPhp('{string}'));
        $this->assertSame(['string1', ',', 'string3'], $this->fixture->toPhp('{string1,",",string3}'));
    }

    public function valuesProvider()
    {
        return [
            ['{}', []],
            ['{NULL}', [null]],
            ['{0}', [0], false],
            ['{-1}', [-1], false],
            ['{1.5}', [1.5], false],
            ['{""}', ['']],
            ['{"",""}', ['', '']],
            ['{"","",NULL}', ['', '', null]],
            ['{1,2,3}', [1,2,3], false],
            ['{"string1","str\\\\in\\"g2","str,ing3"}', ['string1','str\\in"g2','str,ing3']],
            ['{"null","NULL",NULL}', ['null','NULL',null]],

            // Multidimensional arrays
            ['{{NULL}}', [[null]]],
            ['{{0}}', [[0]], false],
            ['{{""}}', [['']]],
            ['{{"",""},{"",""}}', [['', ''], ['', '']]],
            ['{{1,2,3},{4,5,6},{7,8,9}}', [[1,2,3], [4,5,6], [7,8,9]], false],
            ['{{{1},{2},{3}},{{4},{5},{6}},{{7},{8},{9}}}', [[[1],[2],[3]], [[4],[5],[6]], [[7],[8],[9]]], false],
        ];
    }
}
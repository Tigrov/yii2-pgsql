<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ArrayConverter;

class ArrayConverterTest extends TestCase
{
    public function testNullToDb()
    {
        $this->assertNull(ArrayConverter::toDb(null));
    }

    public function testBooleanToDb()
    {
        $this->assertSame('{false}', ArrayConverter::toDb([false]));
        $this->assertSame('{true}', ArrayConverter::toDb([true]));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testToDb($expected, $value, $isSame = true)
    {
        $this->assertSame($expected, ArrayConverter::toDb($value));
    }

    public function testNullToPhp()
    {
        $this->assertNull(ArrayConverter::toPhp(null));
    }

    public function testBooleanToPhp()
    {
        // Typecasting for boolean type realized in ColumnSchema
        $this->assertSame(['f'], ArrayConverter::toPhp('{f}'));
        $this->assertSame(['t'], ArrayConverter::toPhp('{t}'));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testToPhp($value, $expected, $isSame = true)
    {
        $assertMethod = $isSame ? 'assertSame' : 'assertEquals';
        $this->$assertMethod($expected, ArrayConverter::toPhp($value));
    }

    public function testAdditionalToDb()
    {
        $this->assertNull(ArrayConverter::toDb(''));
    }

    public function testAdditionalToPhp()
    {
        $this->assertNull(ArrayConverter::toPhp(''));
        $this->assertSame([null,null], ArrayConverter::toPhp('{,}'));
        $this->assertSame([null,null,null], ArrayConverter::toPhp('{,,NULL}'));
        $this->assertSame(['.'], ArrayConverter::toPhp('{.}'));
        $this->assertSame(['string'], ArrayConverter::toPhp('{string}'));
        $this->assertSame(['string1', ',', 'string3'], ArrayConverter::toPhp('{string1,",",string3}'));
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
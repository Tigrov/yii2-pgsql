<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ArrayConverter;

class CompositeArrayConverterTest extends TestCase
{
    public function testNullCompositeToDb()
    {
        $this->assertNull(ArrayConverter::compositeToDb(null));
    }

    public function testBooleanCompositeToDb()
    {
        $this->assertSame('(false)', ArrayConverter::compositeToDb([false]));
        $this->assertSame('(true)', ArrayConverter::compositeToDb([true]));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testCompositeToDb($expected, $value, $isSame = true)
    {
        $this->assertSame($expected, ArrayConverter::compositeToDb($value));
    }

    public function testAdditionalCompositeToDb()
    {
        $this->assertNull(ArrayConverter::compositeToDb(''));
    }

    public function testNullCompositeToPhp()
    {
        $this->assertNull(ArrayConverter::compositeToPhp(null));
    }

    public function testBooleanCompositeToPhp()
    {
        // Typecasting for boolean type realized in ColumnSchema
        $this->assertSame(['f'], ArrayConverter::compositeToPhp('(f)'));
        $this->assertSame(['t'], ArrayConverter::compositeToPhp('(t)'));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testCompositeToPhp($value, $expected, $isSame = true)
    {
        $assertMethod = $isSame ? 'assertSame' : 'assertEquals';
        $this->$assertMethod($expected, ArrayConverter::compositeToPhp($value));
    }

    public function testAdditionalCompositeToPhp()
    {
        $this->assertNull(ArrayConverter::compositeToPhp(''));
        $this->assertSame([null,null], ArrayConverter::compositeToPhp('(,)'));
        $this->assertSame(['.'], ArrayConverter::compositeToPhp('(.)'));
        $this->assertSame(['string'], ArrayConverter::compositeToPhp('(string)'));
        $this->assertSame(['string1', ',', 'string3'], ArrayConverter::compositeToPhp('(string1,",",string3)'));
        $this->assertSame([['value']], ArrayConverter::compositeToPhp('({value})'));
    }

    public function valuesProvider()
    {
        return [
            ['()', [null]],
            ['(0)', [0], false],
            ['(-1)', [-1], false],
            ['(1.5)', [1.5], false],
            ['("")', ['']],
            ['("","")', ['', '']],
            ['("","",)', ['', '', null]],
            ['(1,2,3)', [1,2,3], false],
            ['("string1","str\\\\in\\"g2","str,ing3")', ['string1','str\\in"g2','str,ing3']],
            ['("null","NULL",)', ['null','NULL',null]],

            // Multidimensional arrays
            ['("{NULL}")', ['{NULL}']],
            ['("(0)")', ['(0)']],
            ['("(\\"\\")")', ['("")']],
            ['("{\\"\\",\\"\\"}","{\\"\\",\\"\\"}")', ['{"",""}', '{"",""}']],
        ];
    }
}
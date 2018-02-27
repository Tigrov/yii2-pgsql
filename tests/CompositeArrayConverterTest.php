<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\CompositeParser;

class CompositeArrayConverterTest extends TestCase
{
    public function testNullCompositeToPhp()
    {
        $parser = new CompositeParser;
        $this->assertNull($parser->parse(null));
    }

    public function testBooleanCompositeToPhp()
    {
        $parser = new CompositeParser;
        // Typecasting for boolean type realized in ColumnSchema
        $this->assertSame(['f'], $parser->parse('(f)'));
        $this->assertSame(['t'], $parser->parse('(t)'));
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testCompositeToPhp($value, $expected, $isSame = true)
    {
        $parser = new CompositeParser;
        $assertMethod = $isSame ? 'assertSame' : 'assertEquals';
        $this->$assertMethod($expected, $parser->parse($value));
    }

    public function testAdditionalCompositeToPhp()
    {
        $parser = new CompositeParser;
        $this->assertSame([null,null], $parser->parse('(,)'));
        $this->assertSame(['.'], $parser->parse('(.)'));
        $this->assertSame(['string'], $parser->parse('(string)'));
        $this->assertSame(['string1', ',', 'string3'], $parser->parse('(string1,",",string3)'));
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
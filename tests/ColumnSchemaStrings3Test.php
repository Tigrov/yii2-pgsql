<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaStrings3Test extends AbstractColumnSchemaArrayTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'strings',
            'allowNull' => true,
            'type' => 'string',
            'phpType' => 'string',
            'dbType' => 'varchar',
            'defaultValue' => NULL,
            'enumValues' => NULL,
            'size' => NULL,
            'precision' => NULL,
            'scale' => NULL,
            'isPrimaryKey' => false,
            'unsigned' => false,
            'comment' => NULL,
            'dimension' => 3,
            'delimiter' => ',',
        ]);
    }

    public function valuesProvider()
    {
        return [
            ['{NULL}', [null]],
            ['{{NULL}}', [[null]]],
            ['{{{NULL}}}', [[[null]]]],
            ['{{{""}}}', [[['']]]],
            ['{{{"",""},{"",""}},{{"",""},{"",""}}}', [[['', ''], ['', '']],[['', ''], ['', '']]]],
            ['{{{"","",NULL}}}', [[['', '', null]]]],
            ['{{{"string1"},{"str\\\\in\\"g2"}},{{"str,ing3"}}}', [[['string1'],['str\\in"g2']],[['str,ing3']]]],
            ['{{{"null"}},{{"NULL"}},{{NULL}}}', [[['null']],[['NULL']],[[null]]]],
        ];
    }

    public function testAdditionalPhpTypecast()
    {
        $this->assertSame([[[null, null]]], $this->fixture->phpTypecast('{{{,}}}'));
        $this->assertSame([[['.']]], $this->fixture->phpTypecast('{{{.}}}'));
        $this->assertSame([[[null, null, null]]], $this->fixture->phpTypecast('{{{,,NULL}}}'));
        $this->assertSame([[['string']]], $this->fixture->phpTypecast('{{{string}}}'));
        $this->assertSame([[['string1', 'string2']], [[',']], [['string3']]], $this->fixture->phpTypecast('{{{string1,string2}},{{","}},{{string3}}}'));
    }
}
<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaCompositeCompositeTest extends AbstractColumnSchemaTest
{
    public $types = [
        'numeric_attr' => [
            'name' => 'numeric_attr',
            'allowNull' => true,
            'type' => 'decimal',
            'phpType' => 'string',
            'dbType' => 'numeric',
            'defaultValue' => NULL,
            'enumValues' => NULL,
            'size' => NULL,
            'precision' => 10,
            'scale' => 2,
            'isPrimaryKey' => false,
            'unsigned' => false,
            'comment' => NULL,
            'dimension' => 0,
            'delimiter' => ',',
        ],
        'string_attr' => [
            'name' => 'string_attr',
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
            'dimension' => 0,
            'delimiter' => ',',
        ],
        'integer_attr' => [
            'name' => 'integer_attr',
            'allowNull' => true,
            'type' => 'integer',
            'phpType' => 'integer',
            'dbType' => 'int4',
            'defaultValue' => NULL,
            'enumValues' => NULL,
            'size' => NULL,
            'precision' => 32,
            'scale' => NULL,
            'isPrimaryKey' => false,
            'unsigned' => false,
            'comment' => NULL,
            'dimension' => 0,
            'delimiter' => ',',
        ],
    ];

    public $compositeType = [
        'name' => 'composite_attr',
        'allowNull' => true,
        'type' => 'composite',
        'phpType' => 'array',
        'dbType' => 'composite',
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
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema($this->compositeType);

        $compositeType = $this->compositeType;
        $compositeType['name'] = 'composite_attr1';
        $compositeColumn = new ColumnSchema($compositeType);
        $compositeColumn->columns['string_attr'] = new ColumnSchema($this->types['string_attr']);
        $compositeColumn->columns['integer_attr'] = new ColumnSchema($this->types['integer_attr']);
        $this->fixture->columns[$compositeColumn->name] = $compositeColumn;

        $compositeType = $this->compositeType;
        $compositeType['name'] = 'composite_attr2';
        $compositeColumn = new ColumnSchema($compositeType);
        $compositeColumn->columns['string_attr'] = new ColumnSchema($this->types['string_attr']);
        $compositeColumn->columns['numeric_attr'] = new ColumnSchema($this->types['numeric_attr']);
        $this->fixture->columns[$compositeColumn->name] = $compositeColumn;

        $this->mockApplication();
    }

    public function valuesProvider()
    {
        return [
            ['(,)', ['composite_attr1' => null, 'composite_attr2' => null]],
            ['("(,)","(,)")', ['composite_attr1' => ['string_attr' => null, 'integer_attr' => null], 'composite_attr2' => ['string_attr' => null, 'numeric_attr' => null]]],
            ['("(\\"\\",0)","(\\"\\",\\"0\\")")', ['composite_attr1' => ['string_attr' => '', 'integer_attr' => 0], 'composite_attr2' => ['string_attr' => '', 'numeric_attr' => '0']]],
            ['("(\\"str value\\",5)","(\\"string2\\",\\"10.50\\")")', ['composite_attr1' => ['string_attr' => 'str value', 'integer_attr' => 5], 'composite_attr2' => ['string_attr' => 'string2', 'numeric_attr' => '10.50']], false],
        ];
    }

    /**
     * @dataProvider valuesArrayProvider
     */
    public function testArrayDbTypecast($expected, $value, $isSame = true)
    {
        foreach ($this->fixture->columns as $column) {
            $column->dimension = 1;
        }

        $this->testDbTypecast($expected, $value, $isSame);
    }

    /**
     * @dataProvider valuesArrayProvider
     */
    public function testArrayPhpTypecast($value, $expected, $isSame = true)
    {
        foreach ($this->fixture->columns as $column) {
            $column->dimension = 1;
        }
        $this->testPhpTypecast($value, $expected, $isSame);
    }

    public function valuesArrayProvider()
    {
        return [
            ['("{NULL}","{NULL}")', ['composite_attr1' => [null], 'composite_attr2' => [null]]],
            ['("{\\"(,)\\"}","{\\"(,)\\"}")', ['composite_attr1' => [['string_attr' => null, 'integer_attr' => null]], 'composite_attr2' => [['string_attr' => null, 'numeric_attr' => null]]]],
            ['("{\\"(\\\\\\"\\\\\\",0)\\"}","{\\"(\\\\\\"\\\\\\",\\\\\\"0\\\\\\")\\"}")', ['composite_attr1' => [['string_attr' => '', 'integer_attr' => 0]], 'composite_attr2' => [['string_attr' => '', 'numeric_attr' => '0']]]],
            ['("{\\"(\\\\\\"str value\\\\\\",5)\\",\\"(\\\\\\"str value2\\\\\\",6)\\"}","{\\"(\\\\\\"string2\\\\\\",\\\\\\"10.50\\\\\\")\\"}")', ['composite_attr1' => [['string_attr' => 'str value', 'integer_attr' => 5], ['string_attr' => 'str value2', 'integer_attr' => 6]], 'composite_attr2' => [['string_attr' => 'string2', 'numeric_attr' => '10.50']]], false],
        ];
    }
}
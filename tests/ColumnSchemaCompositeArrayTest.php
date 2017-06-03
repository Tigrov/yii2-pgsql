<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ColumnSchema;

class ColumnSchemaCompositeArrayTest extends AbstractColumnSchemaTest
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
        'boolean_attr' => [
            'name' => 'boolean_attr',
            'allowNull' => true,
            'type' => 'boolean',
            'phpType' => 'boolean',
            'dbType' => 'bool',
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
        'bit_attr' => [
            'name' => 'bit_attr',
            'allowNull' => true,
            'type' => 'bit',
            'phpType' => 'integer',
            'dbType' => 'varbit',
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
        'datetime_attr' => [
            'name' => 'datetime_attr',
            'allowNull' => true,
            'type' => 'datetime',
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
            'dimension' => 0,
            'delimiter' => ',',
        ],
        'json_attr' => [
            'name' => 'json_attr',
            'allowNull' => true,
            'type' => 'json',
            'phpType' => 'array',
            'dbType' => 'jsonb',
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
        'double_attr' => [
            'name' => 'double_attr',
            'allowNull' => true,
            'type' => 'double',
            'phpType' => 'double',
            'dbType' => 'float8',
            'defaultValue' => NULL,
            'enumValues' => NULL,
            'size' => NULL,
            'precision' => 53,
            'scale' => NULL,
            'isPrimaryKey' => false,
            'unsigned' => false,
            'comment' => NULL,
            'dimension' => 0,
            'delimiter' => ',',
        ],
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new ColumnSchema([
            'name' => 'composite_attr',
            'allowNull' => true,
            'type' => 'composite',
            'phpType' => 'string',
            'dbType' => 'composite',
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

        foreach ($this->types as $type) {
            $this->fixture->columns[$type['name']] = new ColumnSchema($type);
        }

        $this->mockApplication();
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testDbTypecast($expected, $value, $isSame = true)
    {
        foreach ($value as $key => $val) {
            $value[$key] = is_array($val) ? array_combine(array_keys($this->types), $val) : $val;
        }
        parent::testDbTypecast($expected, $value, $isSame);
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testPhpTypecast($value, $expected, $isSame = true)
    {
        foreach ($expected as $key => $val) {
            $expected[$key] = is_array($val) ? array_combine(array_keys($this->types), $val) : $val;
        }
        parent::testPhpTypecast($value, $expected, $isSame);
    }

    public function valuesProvider()
    {
        return [
            ['{NULL}', [null]],
            ['{"(,,,,,,,)"}', [[null, null, null, null, null, null, null, null]]],
            ['{"(\\"0\\",\\"\\",false,0,\\"0\\",,\\"[]\\",0)"}', [['0', '', false, 0, 0, null, [], 0.0]]],
            ['{'
             . '"(\\"10.50\\",\\"str value\\",true,5,\\"100\\",\\"2017-05-02 17:50:32\\",\\"{\\\\\\"a\\\\\\":\\\\\\"b\\\\\\",\\\\\\"c\\\\\\":\\\\\\"d\\\\\\"}\\",3.33)"'
             . ',"(\\"20.50\\",\\"str value3\\",false,7,\\"10001\\",\\"2017-05-02 17:50:55\\",\\"{\\\\\\"e\\\\\\":\\\\\\"f\\\\\\",\\\\\\"g\\\\\\":\\\\\\"h\\\\\\"}\\",6.66)"'
             . '}'
             , [
                 ['10.50', 'str value', true, 5, 4, new \DateTime('@1493747432'), ['a' => 'b', 'c' => 'd'], 3.33],
                 ['20.50', 'str value3', false, 7, 17, new \DateTime('@1493747455'), ['e' => 'f', 'g' => 'h'], 6.66]
             ], false],
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
            ['{"(,,,,,,,)"}', [[null, null, null, null, null, null, null, null]]],
            ['{"(\\"{NULL}\\",\\"{NULL}\\",\\"{NULL}\\",\\"{NULL}\\",\\"{NULL}\\",\\"{NULL}\\",\\"{NULL}\\",\\"{NULL}\\")"}', [[[null], [null], [null], [null], [null], [null], [null], [null]]]],
            ['{"(\\"{\\\\\\"0\\\\\\"}\\",\\"{\\\\\\"\\\\\\"}\\",\\"{false}\\",\\"{0}\\",\\"{\\\\\\"0\\\\\\"}\\",\\"{NULL}\\",\\"{\\\\\\"[]\\\\\\"}\\",\\"{0}\\")"}', [[['0'], [''], [false], [0], [0], [null], [[]], [0.0]]]],
            ['{'
             . '"(\\"{\\\\\\"10.50\\\\\\",\\\\\\"11.50\\\\\\"}\\",\\"{\\\\\\"str value\\\\\\",\\\\\\"str value2\\\\\\"}\\",\\"{true,false}\\",\\"{5,6}\\",\\"{\\\\\\"100\\\\\\",\\\\\\"1000\\\\\\"}\\",\\"{\\\\\\"2017-05-02 17:50:32\\\\\\"}\\",\\"{\\\\\\"{\\\\\\\\\\\\\\"a\\\\\\\\\\\\\\":\\\\\\\\\\\\\\"b\\\\\\\\\\\\\\",\\\\\\\\\\\\\\"c\\\\\\\\\\\\\\":\\\\\\\\\\\\\\"d\\\\\\\\\\\\\\"}\\\\\\"}\\",\\"{3.33}\\")"'
             .',"(\\"{\\\\\\"20.50\\\\\\",\\\\\\"21.50\\\\\\"}\\",\\"{\\\\\\"str value3\\\\\\",\\\\\\"str value4\\\\\\"}\\",\\"{false,true}\\",\\"{7,8}\\",\\"{\\\\\\"10001\\\\\\",\\\\\\"100001\\\\\\"}\\",\\"{\\\\\\"2017-05-02 17:50:55\\\\\\"}\\",\\"{\\\\\\"{\\\\\\\\\\\\\\"e\\\\\\\\\\\\\\":\\\\\\\\\\\\\\"f\\\\\\\\\\\\\\",\\\\\\\\\\\\\\"g\\\\\\\\\\\\\\":\\\\\\\\\\\\\\"h\\\\\\\\\\\\\\"}\\\\\\"}\\",\\"{6.66}\\")"'
             . '}'
             , [
                 [['10.50','11.50'], ['str value', 'str value2'], [true, false], [5, 6], [4, 8], [new \DateTime('@1493747432')], [['a' => 'b', 'c' => 'd']], [3.33]],
                 [['20.50','21.50'], ['str value3', 'str value4'], [false, true], [7, 8], [17, 33], [new \DateTime('@1493747455')], [['e' => 'f', 'g' => 'h']], [6.66]]
             ], false],
        ];
    }
}
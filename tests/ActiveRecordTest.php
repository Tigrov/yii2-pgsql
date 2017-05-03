<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\tests\unit\pgsql\data\Datatypes;
use yii\helpers\ArrayHelper;

class ActiveRecordTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $config = ArrayHelper::merge(
            require(__DIR__ . '/data/config.php'),
            require(__DIR__ . '/data/config.local.php'));

        $this->mockApplication($config);
        $this->createDatatypesTable();
    }

    protected function tearDown()
    {
        $this->dropDatatypesTable();

        parent::tearDown();
    }

    public function testNull()
    {
        $model = new Datatypes;
        foreach ($model->attributes() as $attribute) {
            if ($attribute != 'id') {
                $model->$attribute = null;
            }
        }

        $model->save();

        $newModel = Datatypes::findOne($model->id);
        foreach ($newModel->attributes() as $attribute) {
            if ($attribute != 'id') {
                $this->assertNull($newModel->$attribute);
            }
        }
    }

    /**
     * @dataProvider arrayValuesProvider
     */
    public function testArrayTypes($value)
    {
        $attributes = [
            'strings',
            'integers',
            'numerics',
            'doubles',
            'booleans',
            'bits',
            'datetimes',
        ];

        $model = new Datatypes;
        foreach ($attributes as $attribute) {
            $model->$attribute = $value;
        }

        $model->save();

        $newModel = Datatypes::findOne($model->id);
        foreach ($attributes as $attribute) {
            $this->assertEquals($value, $newModel->$attribute);
        }
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testTypes($attribute, $value)
    {
        $model = new Datatypes;
        $model->$attribute = $value;
        $model->save();

        $this->assertEquals($value, Datatypes::findOne($model->id)->$attribute);
    }

    public function arrayValuesProvider()
    {
        return [
            [[]],
            [[null]],
            [[null, null, null]],
        ];
    }

    public function valuesProvider()
    {
        return [
            ['strings', ['']],
            ['strings', ['string1','str\\in"g2','str,ing3']],
            ['strings', ['null','NULL',null]],
            ['integers', [0]],
            ['integers', [-1]],
            ['integers', [1,2,3]],
            ['numerics', ['0']],
            ['numerics', ['0.00']],
            ['numerics', ['-1.5']],
            ['numerics', ['-1.50']],
            ['numerics', ['1.50', '-1.50', null]],
            ['doubles', [0]],
            ['doubles', [-1.5]],
            ['doubles', [1.5, -1.5, null]],
            ['booleans', [true]],
            ['booleans', [false]],
            ['booleans', [true, false, null]],
            ['bit', 0],
            ['bit', 1],
            ['bit', 8],
            ['bit', 15],
            ['bits', [0]],
            ['bits', [1]],
            ['bits', [8, 15, null]],
            ['datetime', new \DateTime('1901-01-01')],
            ['datetime', new \DateTime('2017-05-02 17:50:32')],
            ['datetimes', [new \DateTime('1901-01-01')]],
            ['datetimes', [new \DateTime('2017-05-02 17:50:32')]],
            ['datetimes', [new \DateTime('1901-01-01'), new \DateTime('2017-05-02 17:50:32')]],
            ['json', []],
            ['json', ''],
            ['json', true],
            ['json', false],
            ['json', 0],
            ['json', 1.5],
            ['json', -1.5],
            ['json', 'string'],
            ['json', ['']],
            ['json', ['string']],
            ['json', ['string',0, false, null]],
            ['json', ['key' => 'value']],
            ['json', ['key1' => 'value1', 'key2' => true, 'key3' => false, 'key4' => '', 'key5' => null]],
            ['json', ['key' => ['key' => ['key' => 'value']]]],
        ];
    }
}
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

        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

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

        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

        foreach ($attributes as $attribute) {
            $this->assertSame($value, $newModel->$attribute);
        }
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testTypes($attribute, $value, $isSame = true)
    {
        $model = new Datatypes;
        $model->$attribute = $value;

        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

        $assertMethod = $isSame ? 'assertSame' : 'assertEquals';
        $this->$assertMethod($value, $newModel->$attribute);
    }

    public function testDefaults()
    {
        $model = new Datatypes;
        $model->loadDefaultValues();
        // For default values see TestCase::createDatatypesTable()
        $now = new \DateTime;
        $this->assertLessThanOrEqual(1, static::convertIntervalToSeconds($now->diff($model->datetime)));
        foreach ($model->datetimes as $datetime) {
            $this->assertLessThanOrEqual(1, static::convertIntervalToSeconds($now->diff($datetime)));
        }
        $this->assertSame(1, $model->bit);
        $this->assertSame(5, $model->varbit);
        $this->assertSame([5], $model->bits);
        $this->assertSame([''], $model->strings);
        $this->assertSame([1, 2, 3], $model->integers);
        $this->assertSame(['1.50', '-1.50'], $model->numerics);
        $this->assertSame([-1.5], $model->doubles);
        $this->assertSame([true, false, null], $model->booleans);
        $this->assertSame([], $model->json);
        $this->assertSame(true, $model->boolean);
        $this->assertSame(1, $model->smallint);
    }

    public function testPhpTypes()
    {
        $json = Datatypes::getTableSchema()->getColumn('json');
        $varbit = Datatypes::getTableSchema()->getColumn('varbit');

        $this->assertSame('array', $json->phpType);
        $this->assertSame('integer', $varbit->phpType);
    }

    public static function convertIntervalToSeconds($interval)
    {
        return (new \DateTime)->add($interval)->getTimestamp() - (new \DateTime)->getTimestamp();
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
            ['strings', ['', '']],
            ['strings', ['', '', null]],
            ['strings', ['string1','str\\in"g2','str,ing3']],
            ['strings', ['null','NULL',null]],
            ['integers', [0]],
            ['integers', [-1]],
            ['integers', [1,2,3]],
            ['numerics', ['0.00']],
            ['numerics', ['-1.50']],
            ['numerics', ['1.50', '-1.50', null]],
            ['doubles', [0.0]],
            ['doubles', [-1.5]],
            ['doubles', [1.5, -1.5, null]],
            ['booleans', [true]],
            ['booleans', [false]],
            ['booleans', [true, false, null]],
            ['bit', 0],
            ['bit', 1],
            ['varbit', 0],
            ['varbit', 1],
            ['varbit', 8],
            ['varbit', 15],
            ['bits', [0]],
            ['bits', [1]],
            ['bits', [8, 15, null]],
            ['datetime', new \DateTime('1901-01-01'), false],
            ['datetime', new \DateTime('2017-05-02 17:50:32'), false],
            ['datetimes', [new \DateTime('1901-01-01')], false],
            ['datetimes', [new \DateTime('2017-05-02 17:50:32')], false],
            ['datetimes', [new \DateTime('1901-01-01'), new \DateTime('2017-05-02 17:50:32')], false],
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
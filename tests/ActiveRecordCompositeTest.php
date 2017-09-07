<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\tests\unit\pgsql\data\Datatypes;
use tigrov\tests\unit\pgsql\data\Money;
use yii\helpers\ArrayHelper;

class ActiveRecordCompositeTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $config = require(__DIR__ . '/data/config.php');
        if (file_exists(__DIR__ . '/data/config.local.php')) {
            $config = ArrayHelper::merge($config, require(__DIR__ . '/data/config.local.php'));
        }
        if (is_array($config['components']['db']['schemaMap']['pgsql'])) {
            $config['components']['db']['schemaMap']['pgsql']['compositeMap']['money'] = '\tigrov\tests\unit\pgsql\data\Money';
        } else {
            $config['components']['db']['schemaMap']['pgsql'] = [
                'class' => $config['components']['db']['schemaMap']['pgsql'],
                'compositeMap' => ['money' => '\tigrov\tests\unit\pgsql\data\Money'],
            ];
        }

        $this->mockApplication($config);
        $this->createDatatypesTable();
    }

    protected function tearDown()
    {
        $this->dropDatatypesTable();

        parent::tearDown();
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testCompositeType($value)
    {
        $model = new Datatypes;
        $model->price = $value;

        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

        $this->assertEquals($value, $newModel->price);
    }

    /**
     * @dataProvider arrayValuesProvider
     */
    public function testArrayCompositeType($values)
    {
        $model = new Datatypes;
        $model->prices = $values;

        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

        $this->assertEquals($values, $newModel->prices);
    }

    public function testDefaults()
    {
        $model = new Datatypes;
        $model->loadDefaultValues();

        $this->assertEquals(new Money(['value' => '1.0000', 'currency_code' => 'USD']), $model->price);
        $this->assertEquals([new Money(['value' => '1.0000', 'currency_code' => 'USD'])], $model->prices);
    }

    public function testWrongOrder()
    {
        $price = ['currency_code' => 'USD', 'value' => '10.0000'];
        $model = new Datatypes;
        $model->price = $price;
        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

        $this->assertEquals(new Money($price), $newModel->price);
    }

    public function testSkippedValues()
    {
        $price = ['currency_code' => 'USD'];
        $model = new Datatypes;
        $model->price = $price;
        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

        $this->assertEquals(new Money($price), $newModel->price);
    }

    public function testDomain()
    {
        $model = new Datatypes;
        $model->price = [1,'USD'];
        $this->assertTrue($model->save(false));

        $model = new Datatypes;
        $model->price = [1,'RRR']; // Unknown currency code

        $this->expectException('\yii\db\IntegrityException');
        $model->save(false);
    }

    public function valuesProvider()
    {
        return [
            [new Money(['value' => null, 'currency_code' => 'EUR'])],
            [new Money(['value' => '10.0000', 'currency_code' => 'USD'])],
        ];
    }

    public function arrayValuesProvider()
    {
        return [
            [[null, null]],
            [[new Money(['value' => '10.0000', 'currency_code' => 'USD']), new Money(['value' => '99.9999', 'currency_code' => 'EUR'])]],
        ];
    }
}
<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\ArrayConverter;
use tigrov\tests\unit\pgsql\data\Datatypes;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class QueryBuilderTest extends TestCase
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

    public function testInsert()
    {
        $integers = [2, 3, 4];

        $model = new Datatypes;
        $model->integers = $integers;
        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

        $this->assertSame($integers, $newModel->integers);
    }

    public function testInsertExpression()
    {
        $integers = [2, 3, 4];

        $model = new Datatypes;
        $model->integers = new Expression(':value', ['value' => (new ArrayConverter)->toDb($integers)]);
        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

        $this->assertSame($integers, $newModel->integers);
    }

    public function testUpdate()
    {
        $integers = [3, 4, 5];

        $model = new Datatypes;
        $this->assertTrue($model->save(false));
        $this->assertFalse($model->getIsNewRecord());

        $model->integers = $integers;
        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

        $this->assertSame($integers, $newModel->integers);
    }

    public function testUpdateExpression()
    {
        $integers = [3, 4, 5];

        $model = new Datatypes;
        $this->assertTrue($model->save(false));
        $this->assertFalse($model->getIsNewRecord());

        $model->integers = new Expression(':value', ['value' => (new ArrayConverter)->toDb($integers)]);
        $this->assertTrue($model->save(false));

        $newModel = Datatypes::findOne($model->id);
        $this->assertNotNull($newModel);

        $this->assertSame($integers, $newModel->integers);
    }

    public function testBatchInsert()
    {
        $values = [
            [['', ',', null], true],
            [null, false]
        ];

        \Yii::$app->getDb()
            ->createCommand()
            ->batchInsert(static::TABLENAME, ['strings', 'boolean'], $values)
            ->execute();

        $models = Datatypes::find()
            ->orderBy(['id' => SORT_DESC])
            ->limit(count($values))
            ->all();

        $this->assertSame(count($values), count($models));

        $models = array_reverse($models);

        foreach ($models as $i => $model) {
            $this->assertSame($values[$i][0], $model->strings);
            $this->assertSame($values[$i][1], $model->boolean);
        }
    }
}
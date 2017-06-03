<?php

namespace tigrov\tests\unit\pgsql;

use yii\di\Container;
use yii\helpers\ArrayHelper;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    const TABLENAME = 'datatypes';

    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
        ], $config));
    }

    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'components' => [
                'request' => [
                    'cookieValidationKey' => 'SDefdsfqdxjfwF8s9oqwefJD',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
            ],
        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        \Yii::$app = null;
        \Yii::$container = new Container();
    }

    /**
     * Setup table for test ActiveRecord
     */
    protected function createDatatypesTable()
    {
        $db = \Yii::$app->getDb();

        $moneyType = $db->schema->defaultSchema . '.money';
        $columns = [
            'id' => 'pk',
            'strings' => 'varchar(100)[] DEFAULT \'{""}\'',
            'integers' => 'integer[] DEFAULT \'{1,2,3}\'',
            'numerics' => 'numeric(10,2)[] DEFAULT \'{"1.50","-1.50"}\'',
            'doubles' => 'float8[] DEFAULT \'{-1.5}\'',
            'booleans' => 'boolean[] DEFAULT \'{true,false,NULL}\'',
            'bit' => 'bit DEFAULT \'B1\'',
            'varbit' => 'varbit DEFAULT \'B101\'',
            'bits' => 'varbit[] DEFAULT \'{101}\'',
            'datetime' => 'timestamp DEFAULT now()',
            'datetimes' => 'timestamp[] DEFAULT \'{now(),now()}\'',
            'json' => 'jsonb DEFAULT \'[]\'',
            'boolean' => 'boolean DEFAULT true',
            'smallint' => 'smallint DEFAULT 1::smallint',
            'timestamp' => 'timestamp DEFAULT NULL',
            'price' => $moneyType . ' DEFAULT \'(1,USD)\'',
            'prices' => $moneyType . '[] DEFAULT \'{"(1,USD)"}\'',
        ];

        if (null === $db->schema->getTableSchema(static::TABLENAME)) {
            $db->createCommand('DROP TYPE IF EXISTS ' . $moneyType)->execute();
            $db->createCommand("CREATE TYPE $moneyType AS (value numeric(19,4), currency_code char(3))")->execute();
            $db->createCommand()->createTable(static::TABLENAME, $columns)->execute();
            $db->getSchema()->refreshTableSchema(static::TABLENAME);
        }
    }

    protected function dropDatatypesTable()
    {
        $db = \Yii::$app->getDb();

        $moneyType = $db->schema->defaultSchema . '.money';
        $db->createCommand()->dropTable(static::TABLENAME)->execute();
        $db->createCommand('DROP TYPE IF EXISTS ' . $moneyType)->execute();
    }
}
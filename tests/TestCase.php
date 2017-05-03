<?php

namespace tigrov\tests\unit\pgsql;

use yii\di\Container;
use yii\helpers\ArrayHelper;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
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

        $table = 'datatypes';
        $columns = [
            'id' => 'pk',
            'strings' => 'varchar[]',
            'integers' => 'integer[]',
            'numerics' => 'numeric(10,2)[]',
            'doubles' => 'float8[]',
            'booleans' => 'boolean[]',
            'bit' => 'varbit',
            'bits' => 'varbit[]',
            'datetime' => 'timestamp',
            'datetimes' => 'timestamp[]',
            'json' => 'jsonb',
        ];

        if (null === $db->schema->getTableSchema($table)) {
            $db->createCommand()->createTable($table, $columns)->execute();
            $db->getSchema()->refreshTableSchema($table);
        }
    }

    protected function dropDatatypesTable()
    {
        $table = 'datatypes';
        \Yii::$app->getDb()->createCommand()->dropTable($table)->execute();
    }
}
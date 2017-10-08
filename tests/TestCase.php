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
        $currencyType = $db->schema->defaultSchema . '.currency_code';
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
            'currency_code' => $currencyType,
            'binary' => 'bytea DEFAULT \'test\'::bytea',
            'binaries' => 'bytea[] DEFAULT \'{test}\'::bytea[]',
        ];

        if (null === $db->schema->getTableSchema(static::TABLENAME)) {
            $db->createCommand('DROP TYPE IF EXISTS ' . $moneyType . ' CASCADE')->execute();
            $db->createCommand('DROP DOMAIN IF EXISTS ' . $currencyType . ' CASCADE')->execute();
            $db->createCommand("CREATE DOMAIN $currencyType AS char(3) NOT NULL DEFAULT 'USD'
	            CHECK (VALUE IN ('AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN','BAM','BBD','BDT','BGN','BHD','BIF','BMD','BND','BOB','BRL','BSD','BTN','BWP','BYR','BZD','CAD','CDF','CHF','CLP','CNY','COP','CRC','CUC','CUP','CVE','CZK','DJF','DKK','DOP','DZD','EGP','ERN','ETB','EUR','FJD','FKP','GBP','GEL','GHS','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','INR','IQD','IRR','ISK','JMD','JOD','JPY','KES','KGS','KHR','KMF','KPW','KRW','KWD','KYD','KZT','LAK','LBP','LKR','LRD','LSL','LYD','MAD','MDL','MGA','MKD','MMK','MNT','MOP','MRO','MUR','MVR','MWK','MXN','MYR','MZN','NAD','NGN','NIO','NOK','NPR','NZD','OMR','PAB','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SBD','SCR','SDG','SEK','SGD','SHP','SLL','SOS','SRD','SSP','STD','SVC','SYP','SZL','THB','TJS','TMT','TND','TOP','TRY','TTD','TWD','TZS','UAH','UGX','USD','UYU','UZS','VEF','VND','VUV','WST','XAF','XCD','XOF','XPF','YER','ZAR','ZMW'))")->execute();
            $db->createCommand("CREATE TYPE $moneyType AS (value numeric(19,4), currency_code $currencyType)")->execute();
            $db->createCommand()->createTable(static::TABLENAME, $columns)->execute();
            $db->getSchema()->refreshTableSchema(static::TABLENAME);
        }
    }

    protected function dropDatatypesTable()
    {
        $db = \Yii::$app->getDb();

        $moneyType = $db->schema->defaultSchema . '.money';
        $currencyType = $db->schema->defaultSchema . '.currency_code';
        $db->createCommand('DROP TABLE IF EXISTS ' . static::TABLENAME . ' CASCADE')->execute();
        $db->createCommand('DROP TYPE IF EXISTS ' . $moneyType . ' CASCADE')->execute();
        $db->createCommand('DROP DOMAIN IF EXISTS ' . $currencyType . ' CASCADE')->execute();
    }
}
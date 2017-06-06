<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\Schema;
use yii\helpers\ArrayHelper;

class SchemaTest extends TestCase
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

    public function testComposite()
    {
        $db = \Yii::$app->getDb();
        $columns = $db->getTableSchema(static::TABLENAME)->columns;
        $this->assertSame(Schema::TYPE_COMPOSITE, $columns['price']->type);
        $this->assertSame('money', $columns['price']->dbType);
        $this->assertSame('\tigrov\tests\unit\pgsql\data\Money', $columns['price']->phpType);

        $this->assertSame(Schema::TYPE_COMPOSITE, $columns['prices']->type);
        $this->assertSame('_money', $columns['prices']->dbType);
        $this->assertSame('\tigrov\tests\unit\pgsql\data\Money', $columns['prices']->phpType);
    }

    public function testDomain()
    {
        $db = \Yii::$app->getDb();
        $columns = $db->getTableSchema(static::TABLENAME)->columns;
        $this->assertSame('char', $columns['currency_code']->type);
        $this->assertSame('currency_code', $columns['currency_code']->dbType);
        $this->assertSame('string', $columns['currency_code']->phpType);
        $this->assertSame('USD', $columns['currency_code']->defaultValue);
        $this->assertFalse($columns['currency_code']->allowNull);

        // inside composite type
        $this->assertSame('char', $columns['price']->columns['currency_code']->type);
        $this->assertSame('currency_code', $columns['price']->columns['currency_code']->dbType);
        $this->assertSame('string', $columns['price']->columns['currency_code']->phpType);
        $this->assertSame('USD', $columns['price']->columns['currency_code']->defaultValue);
        $this->assertFalse($columns['price']->columns['currency_code']->allowNull);

        // inside array of composite type
        $this->assertSame('char', $columns['prices']->columns['currency_code']->type);
        $this->assertSame('currency_code', $columns['prices']->columns['currency_code']->dbType);
        $this->assertSame('string', $columns['prices']->columns['currency_code']->phpType);
        $this->assertSame('USD', $columns['prices']->columns['currency_code']->defaultValue);
        $this->assertFalse($columns['prices']->columns['currency_code']->allowNull);
    }
}
<?php

namespace tigrov\tests\unit\pgsql;

use tigrov\pgsql\Schema;
use yii\helpers\ArrayHelper;

class SchemaViewTest extends TestCase
{
    const VIEWNAME = 'datatypes_view';

    protected function setUp()
    {
        parent::setUp();

        $config = require(__DIR__ . '/data/config.php');
        if (file_exists(__DIR__ . '/data/config.local.php')) {
            $config = ArrayHelper::merge($config, require(__DIR__ . '/data/config.local.php'));
        }

        $this->mockApplication($config);
        $this->createDatatypesTable();

        $db = \Yii::$app->getDb();
        if (null === $db->schema->getTableSchema(static::VIEWNAME)) {
            $db->createCommand('CREATE VIEW ' . static::VIEWNAME . ' AS SELECT * FROM ' . static::TABLENAME)->execute();
            $db->getSchema()->refreshTableSchema(static::VIEWNAME);
        }
    }

    protected function tearDown()
    {
        $db = \Yii::$app->getDb();
        $db->createCommand('DROP VIEW IF EXISTS ' . static::VIEWNAME . ' CASCADE')->execute();

        $this->dropDatatypesTable();

        parent::tearDown();
    }

    public function testComposite()
    {
        $db = \Yii::$app->getDb();
        $columns = $db->getTableSchema(static::VIEWNAME)->columns;
        $this->assertSame(Schema::TYPE_COMPOSITE, $columns['price']->type);
        $this->assertSame('money', $columns['price']->dbType);
        $this->assertSame('\tigrov\tests\unit\pgsql\data\Money', $columns['price']->phpType);

        $this->assertSame(Schema::TYPE_COMPOSITE, $columns['prices']->type);
        $this->assertSame('money', $columns['prices']->dbType);
        $this->assertSame('\tigrov\tests\unit\pgsql\data\Money', $columns['prices']->phpType);
    }

    public function testDomain()
    {
        $db = \Yii::$app->getDb();
        $columns = $db->getTableSchema(static::VIEWNAME)->columns;
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
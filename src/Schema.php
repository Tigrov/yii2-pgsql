<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

use Yii;
use yii\db\TableSchema;

/**
 * Schema is the improved class for retrieving metadata from a PostgreSQL database
 * (version 9.x and above).
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class Schema extends \yii\db\pgsql\Schema
{
    const TYPE_BIT = 'bit';
    const TYPE_COMPOSITE = 'composite';

    /**
     * @var array mapping from composite column types (keys) to PHP types (classes in configuration style).
     * `array` by default, `object` also available as PHP type then a result will be converted to \stdClass.
     * The result will be passed to the class constructor as an array.
     * Example of the class constructor:
     * ```php
     * public function __construct($config = [])
     * {
     *     if (!empty($config)) {
     *         \Yii::configure($this, $config);
     *     }
     * }
     * ```
     */
    public $compositeMap = [];

    public function init()
    {
        $this->typeMap['bit'] = static::TYPE_BIT;
        $this->typeMap['bit varying'] = static::TYPE_BIT;
        $this->typeMap['varbit'] = static::TYPE_BIT;

        parent::init();
    }

    /**
     * @inheritdoc
     *
     * @return QueryBuilder query builder instance
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    /**
     * @inheritdoc
     */
    protected function findColumns($table)
    {
        $tableName = $this->db->quoteValue($table->name);
        $schemaName = $this->db->quoteValue($table->schemaName);
        $sql = <<<SQL
SELECT
    d.nspname AS table_schema,
    c.relname AS table_name,
    a.attname AS column_name,
    COALESCE(td.typname, tb.typname, t.typname) AS data_type,
    COALESCE(td.typtype, tb.typtype, t.typtype) AS type_type,
    a.attlen AS character_maximum_length,
    pg_catalog.col_description(c.oid, a.attnum) AS column_comment,
    COALESCE(NULLIF(a.atttypmod, -1), t.typtypmod) AS modifier,
    NOT (a.attnotnull OR t.typnotnull) AS is_nullable,
    COALESCE(t.typdefault, pg_get_expr(ad.adbin, ad.adrelid)::varchar) AS column_default,
    COALESCE(pg_get_expr(ad.adbin, ad.adrelid) ~ 'nextval', false) AS is_autoinc,
    CASE WHEN COALESCE(td.typtype, tb.typtype, t.typtype) = 'e'::char
        THEN array_to_string((SELECT array_agg(enumlabel) FROM pg_enum WHERE enumtypid = COALESCE(td.oid, tb.oid, a.atttypid))::varchar[], ',')
        ELSE NULL
    END AS enum_values,
    information_schema._pg_char_max_length(information_schema._pg_truetypid(a, t), information_schema._pg_truetypmod(a, t))::numeric AS size,
    a.attnum = ANY (ct.conkey) AS is_pkey,
    COALESCE(NULLIF(a.attndims, 0), NULLIF(t.typndims, 0), (t.typcategory='A')::int) AS dimension,
    CASE WHEN t.typndims > 0 THEN tb.typdelim ELSE t.typdelim END AS delimiter,
    COALESCE(td.oid, tb.oid, a.atttypid) AS type_id,
    t.typname AS attr_type
FROM
    pg_class c
    LEFT JOIN pg_attribute a ON a.attrelid = c.oid
    LEFT JOIN pg_attrdef ad ON a.attrelid = ad.adrelid AND a.attnum = ad.adnum
    LEFT JOIN pg_type t ON a.atttypid = t.oid
    LEFT JOIN pg_type tb ON (a.attndims > 0 OR t.typcategory='A') AND t.typelem > 0 AND t.typelem = tb.oid OR t.typbasetype > 0 AND t.typbasetype = tb.oid
    LEFT JOIN pg_type td ON t.typndims > 0 AND t.typbasetype > 0 AND tb.typelem = td.oid
    LEFT JOIN pg_namespace d ON d.oid = c.relnamespace
    LEFT JOIN pg_constraint ct ON ct.conrelid = c.oid AND ct.contype = 'p'
WHERE
    a.attnum > 0 AND t.typname != ''
    AND c.relname = {$tableName}
    AND d.nspname = {$schemaName}
ORDER BY
    a.attnum;
SQL;

        $columns = $this->db->createCommand($sql)->queryAll();
        if (empty($columns)) {
            return false;
        }
        foreach ($columns as $column) {
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_UPPER) {
                $column = array_change_key_case($column, CASE_LOWER);
            }
            $column = $this->loadColumnSchema($column);
            $table->columns[$column->name] = $column;
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
                if ($table->sequenceName === null && preg_match("/nextval\\('\"?\\w+\"?\.?\"?\\w+\"?'(::regclass)?\\)/", $column->defaultValue) === 1) {
                    $table->sequenceName = preg_replace(['/nextval/', '/::/', '/regclass/', '/\'\)/', '/\(\'/'], '', $column->defaultValue);
                }
                $column->defaultValue = null;
            } elseif ($column->defaultValue) {
                if (in_array($column->type, [static::TYPE_TIMESTAMP, static::TYPE_DATETIME, static::TYPE_DATE, static::TYPE_TIME]) && $column->defaultValue === 'now()') {
                    $column->defaultValue = new \DateTime;
                } elseif ($column->type === static::TYPE_BIT && !$column->dimension) {
                    $column->defaultValue = $column->phpTypecast(trim($column->defaultValue, 'B\''));
                } elseif (preg_match("/^'(.*?)'::/", $column->defaultValue, $matches)) {
                    $column->defaultValue = $column->phpTypecast($matches[1]);
                } elseif (preg_match('/^(\()?(.*?)(?(1)\))(?:::.+)?$/', $column->defaultValue, $matches)) {
                    if ($matches[2] === 'NULL') {
                        $column->defaultValue = null;
                    } else {
                        $column->defaultValue = $column->phpTypecast($matches[2]);
                    }
                } else {
                    $column->defaultValue = $column->phpTypecast($column->defaultValue);
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     *
     * @return ColumnSchema the column schema object
     */
    protected function loadColumnSchema($info)
    {
        list($info['numeric_precision'], $info['numeric_scale']) = $this->getPrecisionScale($info);

        $column = parent::loadColumnSchema($info);
        $column->dbType = ltrim($info['attr_type'], '_');
        if ($column->size === null && $info['modifier'] != -1 && !$column->scale) {
            $column->size = (int) $info['modifier'] - 4;
        }
        $column->delimiter = $info['delimiter'];

        // b for a base type, c for a composite type, e for an enum type, p for a pseudo-type.
        if ($info['type_type'] == 'c') {
            $column->type = self::TYPE_COMPOSITE;
            $column->phpType = 'array';

            $composite = new TableSchema();
            $this->resolveTableNames($composite, $info['data_type']);
            if ($this->findColumns($composite)) {
                $column->columns = $composite->columns;
            }

            if (isset($this->compositeMap[$composite->schemaName . '.' . $composite->name])) {
                $column->phpType = $this->compositeMap[$composite->schemaName . '.' . $composite->name];
            } elseif (isset($this->compositeMap[$composite->name])) {
                $column->phpType = $this->compositeMap[$composite->name];
            }
        }

        return $column;
    }

    /**
     * @return ColumnSchema
     * @throws \yii\base\InvalidConfigException
     */
    protected function createColumnSchema()
    {
        return Yii::createObject(ColumnSchema::className());
    }

    /**
     * @inheritdoc
     */
    protected function getColumnPhpType($column)
    {
        static $typeMap = [
            // abstract type => php type
            self::TYPE_BIT => 'integer',
        ];

        if (isset($typeMap[$column->type])) {
            return $typeMap[$column->type];
        }

        return parent::getColumnPhpType($column);
    }

    protected function getPrecisionScale($info)
    {
        switch ($info['type_id']) {
            case 21: /*int2*/
                return [16, 0];
            case 23: /*int4*/
                return [32, 0];
            case 20: /*int8*/
                return [64, 0];
            case 700: /*float4*/
                return [24, 0]; /*FLT_MANT_DIG*/
            case 701: /*float8*/
                return [53, null]; /*DBL_MANT_DIG*/
            case 1700: /*numeric*/
                return $info['modifier'] = -1
                    ? [null, null]
                    : [(($info['modifier'] - 4) >> 16) & 65535, ($info['modifier'] - 4) & 65535];
        }

        return [null, null];
    }
}
<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

use Yii;

/**
 * Schema is the improved class for retrieving metadata from a PostgreSQL database
 * (version 9.x and above).
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class Schema extends \yii\db\pgsql\Schema
{
    const TYPE_BIT = 'bit';
    const TYPE_JSON = 'json';

    public function init()
    {
        $this->typeMap['bit'] = static::TYPE_BIT;
        $this->typeMap['bit varying'] = static::TYPE_BIT;
        $this->typeMap['varbit'] = static::TYPE_BIT;
        $this->typeMap['json'] = static::TYPE_JSON;
        $this->typeMap['jsonb'] = static::TYPE_JSON;

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
    a.attndims AS array_dimension,
    t.typdelim AS delimiter,
    COALESCE(te.typname, t.typname) AS data_type,
    a.attlen AS character_maximum_length,
    pg_catalog.col_description(c.oid, a.attnum) AS column_comment,
    a.atttypmod AS modifier,
    a.attnotnull = false AS is_nullable,
    a.atttypid,
    CAST(pg_get_expr(ad.adbin, ad.adrelid) AS varchar) AS column_default,
    coalesce(pg_get_expr(ad.adbin, ad.adrelid) ~ 'nextval',false) AS is_autoinc,
    array_to_string((select array_agg(enumlabel) from pg_enum where enumtypid=(CASE WHEN t.typelem > 0 THEN t.typelem ELSE a.atttypid END))::varchar[],',') as enum_values,
    CASE (CASE WHEN t.typelem > 0 THEN t.typelem ELSE a.atttypid END)
         WHEN 21 /*int2*/ THEN 16
         WHEN 23 /*int4*/ THEN 32
         WHEN 20 /*int8*/ THEN 64
         WHEN 1700 /*numeric*/ THEN
              CASE WHEN atttypmod = -1
               THEN null
               ELSE ((atttypmod - 4) >> 16) & 65535
               END
         WHEN 700 /*float4*/ THEN 24 /*FLT_MANT_DIG*/
         WHEN 701 /*float8*/ THEN 53 /*DBL_MANT_DIG*/
         ELSE null
      END   AS numeric_precision,
      CASE (CASE WHEN t.typelem > 0 THEN t.typelem ELSE a.atttypid END)
        WHEN 21 THEN 0
        WHEN 23 THEN 0
        WHEN 20 THEN 0
        WHEN 1700 THEN
        CASE
            WHEN atttypmod = -1 THEN null
            ELSE (atttypmod - 4) & 65535
        END
           ELSE null
      END AS numeric_scale,
    CAST(
             information_schema._pg_char_max_length(information_schema._pg_truetypid(a, t), information_schema._pg_truetypmod(a, t))
             AS numeric
    ) AS size,
    a.attnum = any (ct.conkey) as is_pkey
FROM
    pg_class c
    LEFT JOIN pg_attribute a ON a.attrelid = c.oid
    LEFT JOIN pg_attrdef ad ON a.attrelid = ad.adrelid AND a.attnum = ad.adnum
    LEFT JOIN pg_type t ON a.atttypid = t.oid
    LEFT JOIN pg_type te ON t.typelem != 0 AND t.typelem = te.oid
    LEFT JOIN pg_namespace d ON d.oid = c.relnamespace
    LEFT join pg_constraint ct on ct.conrelid=c.oid and ct.contype='p'
WHERE
    a.attnum > 0 and t.typname != ''
    and c.relname = {$tableName}
    and d.nspname = {$schemaName}
ORDER BY
    a.attnum;
SQL;

        $columns = $this->db->createCommand($sql)->queryAll();
        if (empty($columns)) {
            return false;
        }
        foreach ($columns as $column) {
            $column = $this->loadColumnSchema($column);
            $table->columns[$column->name] = $column;
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
                if ($table->sequenceName === null && preg_match("/nextval\\('\"?\\w+\"?\.?\"?\\w+\"?'(::regclass)?\\)/", $column->defaultValue) === 1) {
                    $table->sequenceName = preg_replace(['/nextval/', '/::/', '/regclass/', '/\'\)/', '/\(\'/'], '', $column->defaultValue);
                }
                $column->defaultValue = null;
            } elseif ($column->defaultValue) {
                if ($column->type === 'timestamp' && $column->defaultValue === 'now()') {
                    $column->defaultValue = new \DateTime;
                } elseif ($column->type === 'boolean') {
                    $column->defaultValue = ($column->defaultValue === 'true');
                } elseif (stripos($column->dbType, 'bit') === 0 || stripos($column->dbType, 'varbit') === 0) {
                    $column->defaultValue = bindec(trim($column->defaultValue, 'B\''));
                } elseif (preg_match("/^'(.*?)'::/", $column->defaultValue, $matches)) {
                    $column->defaultValue = $column->phpTypecast($matches[1]);
                } elseif (preg_match("/^(.*?)::/", $column->defaultValue, $matches)) {
                    $column->defaultValue = $column->phpTypecast($matches[1]);
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
        $column = parent::loadColumnSchema($info);
        if ($column->size === null && $info['modifier'] != -1 && !$column->scale) {
            $column->size = (int) $info['modifier'] - 4;
        }
        $column->dimension = (int) $info['array_dimension'];
        $column->delimiter = $info['delimiter'];

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
            self::TYPE_JSON => 'array',
        ];

        if (isset($typeMap[$column->type])) {
            return $typeMap[$column->type];
        }

        return parent::getColumnPhpType($column);
    }
}
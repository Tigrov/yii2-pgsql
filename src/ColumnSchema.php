<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

use yii\db\ExpressionInterface;
use yii\db\JsonExpression;
use yii\db\PdoValue;

/**
 * ColumnSchema is the improved class which describes the metadata of a column in a PostgreSQL database table
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class ColumnSchema extends \yii\db\pgsql\ColumnSchema
{
    /**
     * @var string the delimiter character to be used between values in arrays made of this type.
     */
    public $delimiter;

    /**
     * @var ColumnSchema[]|null columns of composite type
     */
    public $columns;

    /**
     * @inheritdoc
     */
    public function dbTypecast($value)
    {
        if ($value instanceof ExpressionInterface) {
            return $value;
        }

        if ($this->dimension > 0) {
            if ($value === null) {
                return null;
            }

            return new ArrayExpression($value, $this->dbType, $this->dimension, $this);
        }

        return $this->dbTypecastValue($value);
    }

    /**
     * Converts the input value according to [[type]] and [[dbType]] for use in a db query.
     * @param mixed $value input value
     * @return mixed converted value.
     */
    public function dbTypecastValue($value)
    {
        if ($value === null) {
            return null;
        }

        switch ($this->type) {
            case Schema::TYPE_BIT:
                return decbin($value);
            case Schema::TYPE_BINARY:
                return is_string($value) ? new PdoValue($value, \PDO::PARAM_LOB) : $value;
            case Schema::TYPE_TIMESTAMP:
            case Schema::TYPE_DATETIME:
                return \Yii::$app->formatter->asDatetime($value, 'yyyy-MM-dd HH:mm:ss');
            case Schema::TYPE_DATE:
                return \Yii::$app->formatter->asDate($value, 'yyyy-MM-dd');
            case Schema::TYPE_TIME:
                return \Yii::$app->formatter->asTime($value, 'HH:mm:ss');
            case Schema::TYPE_JSON:
                return new JsonExpression($value, $this->dbType);
            case Schema::TYPE_COMPOSITE:
                return new CompositeExpression($value, $this->dbType, $this);
        }

        return $this->typecast($value);
    }

    /**
     * @inheritdoc
     */
    public function phpTypecast($value)
    {
        if ($this->dimension > 0) {
            if (!is_array($value)) {
                $value = $this->getArrayParser()->parse($value);
            }
            if (is_array($value)) {
                array_walk_recursive($value, function (&$val, $key) {
                    $val = $this->phpTypecastValue($val);
                });
            }

            return $value;
        }

        return $this->phpTypecastValue($value);
    }

    /**
     * Converts the input value according to [[phpType]] after retrieval from the database.
     * @param mixed $value input value
     * @return mixed converted value
     */
    protected function phpTypecastValue($value)
    {
        if ($value === null) {
            return null;
        }

        switch ($this->type) {
            case Schema::TYPE_BOOLEAN:
                switch (strtolower($value)) {
                    case 't':
                    case 'true':
                        return true;
                    case 'f':
                    case 'false':
                        return false;
                }
                return (bool) $value;
            case Schema::TYPE_BIT:
                return bindec($value);
            case Schema::TYPE_BINARY:
                return is_string($value) && strncmp($value, '\\x', 2) === 0 ? pack('H*', substr($value, 2)) : $value;
            case Schema::TYPE_JSON:
                return json_decode($value, true);
            case Schema::TYPE_TIMESTAMP:
            case Schema::TYPE_TIME:
            case Schema::TYPE_DATE:
            case Schema::TYPE_DATETIME:
                return new \DateTime($value);
            case Schema::TYPE_COMPOSITE:
                return $this->phpTypecastComposite($value);
        }

        return $this->typecast($value);
    }

    /**
     * Converts the composite type to PHP
     * @param array|string|object|null $value the value to be converted
     * @return array|object|null Composite object as described in `ColumnSchema::$phpType` (@see `Schema::$compositeMap`) or `null`
     */
    public function phpTypecastComposite($value)
    {
        if (is_string($value)) {
            $value = $this->getCompositeParser()->parse($value);
        }
        if (is_array($value)) {
            $result = [];
            $fields = array_keys($this->columns);
            foreach ($value as $i => $item) {
                $field = is_int($i) ? $fields[$i] : $i;
                if (isset($this->columns[$field])) {
                    $result[$field] = $this->columns[$field]->phpTypecast($item);
                }
            }

            return $this->createCompositeObject($result);
        } elseif (!$value instanceof $this->phpType) {
            return null;
        }

        return $value;
    }

    /**
     * Creates an object for the composite type.
     * @param array $values to be passed to the class constructor
     * @return mixed
     */
    public function createCompositeObject($values)
    {
        switch ($this->phpType) {
            case 'array':
                return $values;
            case 'object':
                return (object)$values;
        }

        return \Yii::createObject($this->phpType, [$values]);
    }

    /**
     * Creates instance of CompositeParser
     *
     * @return CompositeParser
     */
    protected function getCompositeParser()
    {
        static $parser = null;

        if ($parser === null) {
            $parser = new CompositeParser();
        }

        return $parser;
    }
}
<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

/**
 * ColumnSchema is the improved class which describes the metadata of a column in a PostgreSQL database table
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var integer the dimension of an array (the number of indices needed to select an element), 0 if it is not an array.
     */
    public $dimension;

    /**
     * @var string the delimiter character to be used between values in arrays made of this type.
     */
    public $delimiter;

    /**
     * @var ArrayConverter object for converting array values.
     */
    private $_arrayConverter;

    /**
     * @inheritdoc
     */
    public function dbTypecast($value)
    {
        if ($this->dimension > 0) {
            if (is_array($value)) {
                array_walk_recursive($value, function (&$value, $key) {
                    $value = $this->dbTypecastValue($value);
                });
            }

            return $this->getArrayConverter()->toDb($value);
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
            case Schema::TYPE_JSON:
                return json_encode($value);
            case Schema::TYPE_TIMESTAMP:
            case Schema::TYPE_DATETIME:
                return \Yii::$app->formatter->asDatetime($value, 'yyyy-MM-dd HH:mm:ss');
            case Schema::TYPE_DATE:
                return \Yii::$app->formatter->asDate($value, 'yyyy-MM-dd');
            case Schema::TYPE_TIME:
                return \Yii::$app->formatter->asTime($value, 'HH:mm:ss');
        }

        return parent::dbTypecast($value);
    }

    /**
     * @inheritdoc
     */
    public function phpTypecast($value)
    {
        if ($this->dimension > 0) {
            $value = $this->getArrayConverter()->toPhp($value);
            if (is_array($value)) {
                array_walk_recursive($value, function (&$value, $key) {
                    $value = $this->phpTypecastValue($value);
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
    public function phpTypecastValue($value)
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
            case Schema::TYPE_JSON:
                return json_decode($value, true);
            case Schema::TYPE_TIMESTAMP:
            case Schema::TYPE_TIME:
            case Schema::TYPE_DATE:
            case Schema::TYPE_DATETIME:
                return new \DateTime($value);
        }

        return parent::phpTypecast($value);
    }

    /**
     * Get or create an object of `ArrayConverter` class.
     * @return ArrayConverter
     */
    public function getArrayConverter()
    {
        if ($this->_arrayConverter === null) {
            $this->_arrayConverter = \Yii::createObject([
                'class' => ArrayConverter::class,
                'delimiter' => $this->delimiter,
            ]);
        }

        return $this->_arrayConverter;
    }
}
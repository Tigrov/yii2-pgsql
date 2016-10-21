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
     * @var integer the dimension of an array (the number of indices needed to select an element), 0 if not array
     */
    public $dimension;

    /**
     * @var string the delimiter character to be used between values in arrays made of this type.
     */
    public $delimiter;

    /**
     * @var ArrayConverter
     */
    private $_arrayConverter;

    /**
     * @inheritdoc
     */
    public function dbTypecast($value)
    {
        if ($this->dimension > 0) {
            return $this->getArrayConverter()->fromPhp($value);
        }

        switch ($this->type) {
            case Schema::TYPE_BIT:
                return decbin($value);
            case Schema::TYPE_JSON:
                return $value !== null ? json_encode($value) : null;
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
            return $this->getArrayConverter()->toPhp($value);
        }

        switch ($this->type) {
            case Schema::TYPE_BIT:
                return bindec($value);
            case Schema::TYPE_JSON:
                return $value ? json_decode($value, true) : null;
            case Schema::TYPE_TIMESTAMP:
            case Schema::TYPE_TIME:
            case Schema::TYPE_DATE:
            case Schema::TYPE_DATETIME:
                return new \DateTime($value);
        }

        return parent::phpTypecast($value);
    }

    /**
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
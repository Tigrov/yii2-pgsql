<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

/**
 * ArrayExpression is the improved class which represents an array SQL expression.
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class ArrayExpression extends \yii\db\ArrayExpression
{
    /**
     * @var ColumnSchema the metadata of a column in a PostgreSQL database table.
     */
    private $column;

    /**
     * {@inheritdoc}
     * @param ColumnSchema|null $column the metadata of a column in a PostgreSQL database table.
     */
    public function __construct($value, $type = null, $dimension = 1, $column = null)
    {
        parent::__construct($value, $type, $dimension);
        $this->column = $column;
    }

    /**
     * @return null|ColumnSchema
     * @see column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return parent::getType() ?: ($this->column ? $this->column->dbType : null);
    }
}

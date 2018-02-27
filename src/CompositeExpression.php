<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

use yii\db\ExpressionInterface;

/**
 * Class CompositeExpression represents data that should be encoded to composite type SQL expression.
 *
 * For example:
 *
 * ```php
 * new CompositeExpression(['a' => 1, 'b' => 2]); // will be encoded to 'ROW(1,2)'
 * ```
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class CompositeExpression implements ExpressionInterface
{
    /**
     * @var array|mixed the composite type content. Either represented as an array of values or a composite object
     * which corresponds to Schema::compositeMap and could be converted to array.
     */
    protected $value;
    /**
     * @var null|string the type of the composite type. Defaults to `null` which means the type is
     * not explicitly specified.
     *
     * Note that in case when type is not specified explicitly and DBMS can not guess it from the context,
     * SQL error will be raised.
     */
    private $type;
    /**
     * @var ColumnSchema describes the metadata of a column in a PostgreSQL database table
     */
    private $column;


    /**
     * CompositeExpression constructor.
     *
     * @param array|mixed $value the composite type content. Either represented as an array of values or a composite
     * object which corresponds to Schema::compositeMap and could be converted to array.
     * @param ColumnSchema|null $column the metadata of a column in a PostgreSQL database table.
     */
    public function __construct($value, $type = null, $column = null)
    {
        $this->value = $value;
        $this->type = $type;
        $this->column = $column;
    }

    /**
     * @return mixed
     * @see value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return null|string
     * @see type
     */
    public function getType()
    {
        return $this->type ?: ($this->column ? $this->column->dbType : null);
    }

    /**
     * @return ColumnSchema|null
     * @see column
     */
    public function getColumn()
    {
        return $this->column;
    }
}

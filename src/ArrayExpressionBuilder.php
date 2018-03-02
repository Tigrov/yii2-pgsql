<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

use yii\db\ExpressionInterface;

/**
 * ArrayExpressionBuilder is the improved class which builds [[ArrayExpression]] for PostgreSQL DBMS.
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class ArrayExpressionBuilder extends \yii\db\pgsql\ArrayExpressionBuilder
{
    /**
     * {@inheritdoc}
     */
    protected function getTypehint(\yii\db\ArrayExpression $expression)
    {
        $type = $expression->getType();
        if ($type === null) {
            return '';
        }

        if ($expression instanceof ArrayExpression) {
            $column = $expression->getColumn();
            if ($column && $column->type === Schema::TYPE_COMPOSITE && strpos($type, '.') === false) {
                $schema = $this->queryBuilder->db->schema->defaultSchema;
                $type = $schema . '.' . $type;
            }
        }

        $result = '::' . $type;
        $result .= str_repeat('[]', $expression->getDimension());

        return $result;
    }

    /**
     * {@inheritdoc}
     * @return mixed
     */
    protected function typecastValue(\yii\db\ArrayExpression $expression, $value)
    {
        if ($expression instanceof ArrayExpression) {
            $column = $expression->getColumn();
            if ($column !== null) {
                if ($value instanceof ExpressionInterface) {
                    return $value;
                }

                return $column->dbTypecastValue($value);
            }
        }

        return parent::typecastValue($expression, $value);
    }
}

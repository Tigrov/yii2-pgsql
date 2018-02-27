<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

use yii\base\Arrayable;
use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionBuilderTrait;
use yii\db\ExpressionInterface;

/**
 * Class CompositeExpressionBuilder builds [[CompositeExpression]] for PostgreSQL DBMS.
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class CompositeExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;

    /**
     * {@inheritdoc}
     * @param ExpressionInterface|CompositeExpression $expression the expression to be built
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $value = $expression->getValue();
        if ($value === null) {
            return 'NULL';
        }

        $placeholders = $this->buildPlaceholders($expression, $params);
        if (empty($placeholders)) {
            return "'()'";
        }

        return 'ROW(' . implode(', ', $placeholders) . ')' . $this->getTypehint($expression);
    }

    /**
     * Builds placeholders array out of $expression values
     * @param ExpressionInterface|CompositeExpression $expression
     * @param array $params the binding parameters.
     * @return array
     */
    protected function buildPlaceholders(ExpressionInterface $expression, &$params)
    {
        $value = $expression->getValue();
        $value = $this->prepareValue($expression, $value);

        $columns = $expression->getColumn()->columns;
        $fields = array_keys($columns);

        $placeholders = [];
        foreach ($value as $i => $item) {
            $field = is_int($i) ? $fields[$i] : $i;
            if (isset($columns[$field])) {
                $item = $columns[$field]->dbTypecast($item);
                if ($item instanceof ExpressionInterface) {
                    $placeholders[] = $this->queryBuilder->buildExpression($item, $params);
                    continue;
                }

                $placeholders[] = $this->queryBuilder->bindParam($item, $params);
            }
        }

        return $placeholders;
    }

    /**
     * @param CompositeExpression $expression
     * @return string the typecast expression based on [[type]].
     */
    protected function getTypehint(CompositeExpression $expression)
    {
        $type = $expression->getType();
        if ($type === null) {
            return '';
        }

        if (strpos($type, '.') === false) {
            $schema = $this->queryBuilder->db->schema->defaultSchema;
            $type = $schema . '.' . $type;
        }

        return '::' . $type;
    }

    /**
     * Sort a composite value in the order of the columns and append skipped values as default value
     * e.g. if default is (0,USD) and $value is ['value' => 10] or [10]
     * then will be converted as ['value' => 10, 'currency_code' => 'USD']
     * @param CompositeExpression $expression
     * @param array $value the composite value
     * @return array
     */
    protected function prepareValue(CompositeExpression $expression, $value)
    {
        $value = $this->toArray($value);
        $column = $expression->getColumn();
        if ($column) {
            $fields = array_keys($column->columns);
            $keys = array_keys($value);

            if ($fields !== $keys) {
                $defaultValue = $column->defaultValue !== null ? $this->toArray($column->defaultValue) : [];
                if (count(array_filter($keys, 'is_string'))) {
                    $list = [];
                    foreach ($fields as $field) {
                        $list[$field] = array_key_exists($field, $value) ? $value[$field] : (isset($defaultValue[$field]) ? $defaultValue[$field] : null);
                    }

                    return $list;
                } elseif (count($keys) < count($fields)) {
                    $skippedKeys = array_slice($fields, count($keys));
                    foreach ($skippedKeys as $key) {
                        array_push($value, (isset($defaultValue[$key]) ? $defaultValue[$key] : null));
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Converts object to array
     * @param array|object $value the value to be converted
     * @return array
     */
    protected function toArray($value)
    {
        return $value instanceof Arrayable
            ? $value->toArray()
            : (array) $value;
    }
}

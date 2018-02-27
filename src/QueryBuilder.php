<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

use yii\db\Expression;

/**
 * QueryBuilder is the improved query builder for PostgreSQL databases.
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class QueryBuilder extends \yii\db\pgsql\QueryBuilder
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->typeMap[Schema::TYPE_BIT] = 'bit';

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultExpressionBuilders()
    {
        return array_merge(parent::defaultExpressionBuilders(), [
            'tigrov\pgsql\ArrayExpression' => 'tigrov\pgsql\ArrayExpressionBuilder',
            'tigrov\pgsql\CompositeExpression' => 'tigrov\pgsql\CompositeExpressionBuilder',
        ]);
    }
}
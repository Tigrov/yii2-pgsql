<?php

namespace tigrov\pgsql\validators;

use yii\base\Model;
use yii\db\ActiveRecord;
use yii\validators\Validator;

/**
 * CompositeValidator validates the attribute value through the composite model validators.
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
abstract class AbstractCompositeValidator extends Validator
{
    /** @var object Class of the composite value */
    public $compositeClass;

    /**
     * @param Model $model
     * @param string $attribute
     * @param mixed $value
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public function castValue($model, $attribute, $value)
    {
        if (!$value instanceof Model) {
            if ($model instanceof ActiveRecord) {
                /** @var \tigrov\pgsql\ColumnSchema $compositeColumn */
                $compositeColumn = $model::getTableSchema()->getColumn($attribute);
                $value = $compositeColumn->phpTypecastComposite($value ?: $compositeColumn->defaultValue ?: []);
            } elseif ($this->compositeClass) {
                $value = \Yii::createObject($this->compositeClass, [is_array($value) ? $value : []]);
            }
        }

        return $value;
    }
}
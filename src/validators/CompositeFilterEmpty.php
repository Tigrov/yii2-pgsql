<?php

namespace tigrov\pgsql\validators;

use yii\base\Model;
use yii\db\ActiveRecord;
use yii\validators\RequiredValidator;
use yii\validators\Validator;

/**
 * CompositeFilterEmpty filters empty values of composite values.
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class CompositeFilterEmpty extends AbstractCompositeValidator
{
    public $isArray = false;

    /** @var object Class of the composite value */
    public $compositeClass;

    /**
     * @inheritdoc
     * @param ActiveRecord $model the model being validated
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if ($this->isArray) {
            $list = [];
            foreach ($value as $v) {
                if (!$this->checkRequired($model, $attribute, $v)) {
                    $list[] = $v;
                }
            }
            $model->$attribute = $list;
        } elseif ($this->checkRequired($model, $attribute, $value)) {
            $model->$attribute = null;
        }
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param Model|array $value
     * @return bool
     */
    protected function checkRequired($model, $attribute, $value)
    {
        $value = $this->castValue($model, $attribute, $value);

        if ($value instanceof Model) {
            $value->clearErrors();
            if ($value->beforeValidate()) {
                foreach ($value->rules() as $rule) {
                    if ($this->isRequiredValidator($value, $rule)) {
                        $validator = $rule instanceof Validator
                            ? $rule
                            : Validator::createValidator($rule[1], $value, (array)$rule[0], array_slice($rule, 2));
                        $validator->validateAttributes($value, $rule[0]);
                    }
                }
                $value->afterValidate();
            }

            return $value->hasErrors();
        }

        return false;
    }

    /**
     * @param Model $model
     * @param Validator|array $rule
     * @return bool
     */
    protected function isRequiredValidator($model, $rule)
    {
        if ($rule instanceof Validator) {
            return $rule instanceof RequiredValidator;
        } elseif (is_array($rule) && isset($rule[1])) {
            $type = $rule[1];
            if ($type instanceof \Closure) {
                return false;
            }
            if (is_string($type)) {
                if ($type == 'required') {
                    return true;
                }
                if (isset(static::$builtInValidators[$type]) || $model->hasMethod($type)) {
                    return false;
                }
            }

            return is_a(is_array($type) ? $type['class'] : $type, RequiredValidator::class, true);
        }

        return false;
    }
}
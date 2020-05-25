<?php

namespace tigrov\pgsql\validators;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * CompositeValidator validates the attribute value through the composite model validators.
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class CompositeValidator extends AbstractCompositeValidator
{
    /** @var bool Assign value after validation */
    public $assignValue = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * @inheritdoc
     * @param ActiveRecord $model the model being validated
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $this->castValue($model, $attribute, $model->$attribute);
        if ($value instanceof Model) {
            if (!$value->validate()) {
                foreach ($value->getErrors() as $errors) {
                    foreach ($errors as $error) {
                        $model->addError($attribute, $error);
                    }
                }
            }
            if ($this->assignValue) {
                $model->$attribute = $value;
            }
        }
    }
}
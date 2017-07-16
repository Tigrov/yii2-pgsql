<?php

namespace tigrov\tests\unit\pgsql\data;

use yii\base\Model;

class Money extends Model
{
    public $value;

    public $currency_code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['value'], 'required'],
            [['value'], 'number'],
            [['currency_code'], 'in', 'range' => ['USD', 'EUR', 'CNY', 'RUB']],
        ];
    }
}
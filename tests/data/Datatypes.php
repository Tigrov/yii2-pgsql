<?php

namespace tigrov\tests\unit\pgsql\data;

use yii\db\ActiveRecord;

class Datatypes extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'datatypes';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [$this->attributes(), 'safe'],
        ];
    }
}
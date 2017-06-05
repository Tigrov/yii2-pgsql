<?php

namespace tigrov\tests\unit\pgsql\data;

class Money extends \stdClass
{
    public function __construct($config = [])
    {
        if (!empty($config)) {
            \Yii::configure($this, $config);
        }
    }
}
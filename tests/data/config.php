<?php

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=postgres',
            'username' => 'postgres',
            'password' => '',
            'charset' => 'utf8',
            'schemaMap' => [
                'pgsql'=> [
                    'class' => 'tigrov\pgsql\Schema',
                    'compositeMap' => [
                        'money' => '\tigrov\tests\unit\pgsql\data\Money'
                    ],
                ],
            ],
        ],
    ],
];
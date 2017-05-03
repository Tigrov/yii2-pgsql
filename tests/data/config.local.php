<?php

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;port=5434;dbname=country',
            'username' => 'postgres',
            'password' => 'YfGfktdt68',
            'charset' => 'utf8',
            'schemaMap' => [
                'pgsql'=> 'tigrov\pgsql\Schema',
            ],
        ],
    ],
];
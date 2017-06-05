Composite types
===============

Here is example how to use composite types.

Configure your application:

```php
return [
    //...
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;dbname=<database>',
            'username' => 'postgres',
            'password' => '<password>',
            'schemaMap' => [
                'pgsql' => [
                    'class' => 'tigrov\pgsql\Schema',
                    // Mapping from composite column types (keys) to PHP types (classes in configuration style).
                    // `array` by default, `object` also available as PHP type then a result will be converted to \stdClass.
                    // The result will be passed to the class constructor as an array.
                    'compositeMap' => [
                        'currency_money' => Money::className(), // See example at /tests/data/Money.php
                    ],
                ],
            ],
        ],
    ],
];
```

Create a composite type:
```sql
CREATE TYPE currency_money AS
(
  value numeric(19,4),
  currency_code char(3)
);
```

Create a table with the composite type:
```sql
CREATE TABLE public.product (
    id serial NOT NULL,
    price currency_money DEFAULT '(0,USD)',
    CONSTRAINT model_pkey PRIMARY KEY (id)
);
```

Create a model for the table
```php
/**
 * @property array $price
 */
class Product extends ActiveRecord
{
    //...
    public function rules()
    {
        return [
            [['price'], 'safe'],
        ];
    }
}
```

Use the model:
```php
$model = new Product;
$model->price = new Money([
    'value' => 10,
    'currency_code' => 'USD'
]);
// also available
// $model->price = [
//     'value' => 10,
//     'currency_code' => 'USD'
// ];
// or just
// $model->price = [10, 'USD'];
$model->save();

$newModel = Product::findOne($model->id);
$newModel->price; // is new Money(['value' => 10, 'currency_code' => 'USD'])
```

[Back to README](../README.md)
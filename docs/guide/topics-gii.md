Using Gii generator
===================

This extension provides a code generator, which can be integrated with yii 'gii' module. It allows generation of the
Active Record code. In order to enable it, you should adjust your application configuration in following way:

```php
return [
    //....
    'modules' => [
        // ...
        'gii' => [
            'class' => 'yii\gii\Module',
            'generators' => [
                'sphinxModel' => [
                    'class' => 'yii\sphinx\gii\model\Generator'
                ]
            ],
        ],
    ]
];
```

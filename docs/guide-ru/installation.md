Установка
============

## Требования

Требуется, хотя бы, версия Sphinx 2.0. Однако, чтобы использовать все возможности расширения, необходим Sphinx версии 2.2 или выше.

## Получение с помощью Composer

Предпочтительный способ установки расширения через [composer](https://getcomposer.org/download/).

Для этого запустите

```
php composer.phar require --prefer-dist yiisoft/yii2-sphinx
```

или добавьте

```json
"yiisoft/yii2-sphinx": "~2.0.0"
```

в секцию require вашего composer.json.

## Конфигурация

Это расширение взаимодействует с демоном поиска Sphinx, используя протокол MySQL и язык запросов [SphinxQL](https://sphinxsearch.com/docs/current.html#sphinxql).
Чтобы настроить Sphinx "searchd" для поддержки протокола MySQL, необходимо добавить следующую конфигурацию:

```
searchd
{
    listen = localhost:9306:mysql41
    ...
}
```

Для использования расширения, просто добавьте этот код в конфигурацию вашего приложения:

```php
return [
    //....
    'components' => [
        'sphinx' => [
            'class' => 'yii\sphinx\Connection',
            'dsn' => 'mysql:host=127.0.0.1;port=9306;',
            'username' => '',
            'password' => '',
        ],
    ],
];
```

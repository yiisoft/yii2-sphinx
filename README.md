<p align="center">
    <a href="https://sphinxsearch.com" target="_blank" rel="external">
        <img src="https://sphinxsearch.com/images/logo.png" height="55px">
    </a>
    <h1 align="center">Sphinx Extension for Yii 2</h1>
    <br>
</p>

This extension adds [Sphinx](https://sphinxsearch.com/docs) full text search engine extension for the [Yii framework 2.0](https://www.yiiframework.com).
It supports all Sphinx features including [Real-time Indexes](https://sphinxsearch.com/docs/current.html#rt-indexes).

For license information check the [LICENSE](LICENSE.md)-file.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-sphinx/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-sphinx)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-sphinx/downloads.png)](https://packagist.org/packages/yiisoft/yii2-sphinx)
[![Build status](https://github.com/yiisoft/yii2-sphinx/workflows/build/badge.svg)](https://github.com/yiisoft/yii2-sphinx/actions?query=workflow%3Abuild)
[![codecov](https://codecov.io/gh/yiisoft/yii2-sphinx/graph/badge.svg?token=eEgiSUaKxc)](https://codecov.io/gh/yiisoft/yii2-sphinx)


Requirements
------------

- PHP 7.3 or higher.
- At least Sphinx version 2.0 is required. However, in order to use all extension features, Sphinx version 2.2.3 or
higher is required.

Installation
------------

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-sphinx
```

or add

```json
"yiisoft/yii2-sphinx": "~2.0.0"
```

to the require section of your composer.json.


Configuration
-------------

This extension interacts with Sphinx search daemon using MySQL protocol and [SphinxQL](https://sphinxsearch.com/docs/current.html#sphinxql) query language.
In order to setup Sphinx "searchd" to support MySQL protocol following configuration should be added:

```
searchd
{
    listen = localhost:9306:mysql41
    ...
}
```

To use this extension, simply add the following code in your application configuration:

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

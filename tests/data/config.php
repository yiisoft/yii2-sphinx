<?php

/**
 * This is the configuration file for the Yii2 unit tests.
 * You can override configuration values by creating a `config.local.php` file
 * and manipulate the `$config` variable.
 */

$config = [
    'sphinx' => [
        'dsn' => 'mysql:host=127.0.0.1;port=19306;',
        'username' => 'travis',
        'password' => '',
    ],
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=yiitest',
        'username' => 'travis',
        'password' => '',
        'fixture' => __DIR__ . '/source.sql',
    ],
];

if (is_file(__DIR__ . '/config.local.php')) {
    include(__DIR__ . '/config.local.php');
}

return $config;

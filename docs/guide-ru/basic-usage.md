Базовое использование
===========

Поскольку это расширение использует протокол MySQL для доступа к Sphinx, оно разделяет базовый подход и множество кода из регулярного пакета `"yii\db"`. Запуск запросов SphinxQL очень похож на обычные SQL-запросы:

```php
$sql = 'SELECT * FROM idx_item WHERE group_id = :group_id';
$params = [
    'group_id' => 17
];
$rows = Yii::$app->sphinx->createCommand($sql, $params)->queryAll();
```

Вы, также, можете использовать Query Builder:

```php
use yii\sphinx\Query;

$query = new Query();
$rows = $query->select('id, price')
    ->from('idx_item')
    ->andWhere(['group_id' => 1])
    ->all();
```

> Note: Sphinx, по умолчанию, ограничивает количество записей, возвращаемых любым запросом, до 10. Если вам нужно получить больше записей, вам следует явно указать ограничение.
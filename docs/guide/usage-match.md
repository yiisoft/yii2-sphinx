Composing 'MATCH' statement
===========================

Sphinx usage does not make sense unless you are using its fulltext search ability.
In SphinxQL it is provided via 'MATCH' statement. You can always compose it manually as a part of the 'where'
condition, but if you are using `yii\sphinx\Query` you can do it via `yii\sphinx\Query::match()`:

```php
use yii\sphinx\Query;

$query = new Query();
$rows = $query->from('idx_item')
    ->match($_POST['search'])
    ->all();
```

Please note that Sphinx 'MATCH' statement argument uses complex internal syntax for better tuning.
By default `yii\sphinx\Query::match()` will escape all special characters related to this syntax from
its argument. So if you wish to use complex 'MATCH' statement, you should use `yii\db\Expression` for it:

```php
use yii\sphinx\Query;
use yii\db\Expression;

$query = new Query();
$rows = $query->from('idx_item')
    ->match(new Expression(':match', ['match' => '@(content) ' . Yii::$app->sphinx->escapeMatchValue($_POST['search'])]))
    ->all();
```

> Note: if you compose 'MATCH' argument, make sure to use `yii\sphinx\Connection::escapeMatchValue()` to properly
  escape any special characters, which may break the query.

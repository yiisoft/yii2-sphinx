Basic Usage
===========

Since this extension uses MySQL protocol to access Sphinx, it shares base approach and much code from the
regular "yii\db" package. Running SphinxQL queries a very similar to regular SQL ones:

```php
$sql = 'SELECT * FROM idx_item WHERE group_id = :group_id';
$params = [
    'group_id' => 17
];
$rows = Yii::$app->sphinx->createCommand($sql, $params)->queryAll();
```

You can also use a Query Builder:

```php
use yii\sphinx\Query;

$query = new Query;
$rows = $query->select('id, price')
    ->from('idx_item')
    ->andWhere(['group_id' => 1])
    ->all();
```

> Note: Sphinx limits the number of records returned by any query to 10 records by default.
  If you need to get more records you should specify limit explicitly.

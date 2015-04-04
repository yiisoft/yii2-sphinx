基本的な使用方法
================

このエクステンションは Sphinx にアクセスするために MySQL プロトコルを使用しているため、基本的なアプローチと多くのコードを通常の "yii\db" パッケージと共有しています。
SphinxQL のクエリの実行は、通常の SQL クエリの実行と非常に似ています。

```php
$sql = 'SELECT * FROM idx_item WHERE group_id = :group_id';
$params = [
    'group_id' => 17
];
$rows = Yii::$app->sphinx->createCommand($sql, $params)->queryAll();
```

クエリビルダを使用することも出来ます。

```php
use yii\sphinx\Query;

$query = new Query;
$rows = $query->select('id, price')
    ->from('idx_item')
    ->andWhere(['group_id' => 1])
    ->all();
```

> Note|注意: Sphinx では、全てのクエリによって返されるレコードの数がデフォルトでは 10 レコードに制限されています。
  10 より多いレコードを取得する必要がある場合は limit を明示的に指定しなければなりません。

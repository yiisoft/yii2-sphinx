'MATCH' 文を作成する
====================

全文検索の能力を使用するのでなければ、Sphinx を使用しても意味がありません。
SphinxQL では、全文検索の機能は 'MATCH' 文によって提供されています。
'MATCH' 文は、いつでも 'where' 条件の一部として手動で作成することが出来ますが、`yii\sphinx\Query` を使うのであれば `yii\sphinx\Query::match()` によって作成することが出来ます。

```php
use yii\sphinx\Query;

$query = new Query;
$rows = $query->from('idx_item')
    ->match($_POST['search'])
    ->all();
```

Sphinx の 'MATCH' 文の引数は、検索結果をより良く調整するために、複雑な内部的構文を使用することに注意してください。
デフォルトでは、`yii\sphinx\Query::match()` の引数から、この構文に関連する全ての特殊文字がエスケープされます。
従って、複雑な 'MATCH' 文を使用したい場合は、そのために `yii\db\Expression` を使わなければなりません。

```php
use yii\sphinx\Query;
use yii\db\Expression;

$query = new Query;
$rows = $query->from('idx_item')
    ->match(new Expression(':match', ['match' => '@(content) ' . Yii::$app->sphinx->escapeMatchValue($_POST['search'])]))
    ->all();
```

> Note|注意: 'MATCH' の引数を作成する場合は、必ず `yii\sphinx\Connection::escapeMatchValue()` を使って、クエリを破壊する全ての特殊文字を正しくエスケープしてください。

'MATCH' 文を作成する
====================

全文検索の能力を使用するのでなければ、Sphinx を使用しても意味がありません。
SphinxQL では、全文検索の機能は 'MATCH' 文によって提供されています。
'MATCH' 文は、いつでも 'where' 条件の一部として手動で作成することが出来ますが、`yii\sphinx\Query` を使うのであれば `yii\sphinx\Query::match()` によって作成することが出来ます。

```php
use yii\sphinx\Query;

$query = new Query();
$rows = $query->from('idx_item')
    ->match($_POST['search'])
    ->all();
```

Sphinx は、検索のより良いチューニングのために、'MATCH' 文の引数に、複雑な内部的構文を使用することに注意してください。
デフォルトでは、`yii\sphinx\Query::match()` の引数の中の、内部的構文に関わる全ての特殊文字がエスケープされます。
従って、複雑な 'MATCH' 文を使用したい場合は、引数のために `yii\db\Expression` を使わなければなりません。

```php
use yii\sphinx\Query;
use yii\db\Expression;

$query = new Query();
$rows = $query->from('idx_item')
    ->match(new Expression(':match', ['match' => '@(content) ' . Yii::$app->sphinx->escapeMatchValue($_POST['search'])]))
    ->all();
```

> Note: 'MATCH' の引数を作成する場合は、必ず `yii\sphinx\Connection::escapeMatchValue()` を使って全ての特殊文字を適切にエスケープしてください。
  そうしないと、クエリが破壊されます。

バージョン 2.0.6 以降は、'MATCH' 文の作成に [[\yii\sphinx\MatchExpression]] を使うことが出来ます。
これを使うと、パラメータバインディングと同じ方法のプレースホルダを使うことが出来、引数の値が [[\yii\sphinx\Connection::escapeMatchValue()]] を使って自動的にエスケープされるようになります。
例えば、

```php
use yii\sphinx\Query;
use yii\sphinx\MatchExpression;

$rows = (new Query())
    ->match(new MatchExpression('@title :title', ['title' => 'Yii'])) // ':title' の値が自動的にエスケープされる
    ->all();
```

[[match()]]、[[andMatch()]] および [[orMatch()]] を使って、複数の条件を結合することが出来ます。
各条件は、[[\yii\sphinx\Query:where]] で使われているのと同じ配列構文を使って指定することが出来ます。
例えば、

```php
use yii\sphinx\Query;
use yii\sphinx\MatchExpression;

$rows = (new Query())
    ->match(
        // '((@title "Yii") (@author "Paul")) | (@content "Sphinx")' を生成
        (new MatchExpression())
            ->match(['title' => 'Yii'])
            ->andMatch(['author' => 'Paul'])
            ->orMatch(['content' => 'Sphinx'])
    )
    ->all();
```

'MAYBE'、'PROXIMITY' など、特殊な演算子を使って式を作成することも可能です。
例えば、

```php
use yii\sphinx\Query;
use yii\sphinx\MatchExpression;

$rows = (new Query())
    ->match(
        // '@title "Yii" MAYBE "Sphinx"' を生成
        (new MatchExpression())->match([
            'maybe',
            'title',
            'Yii',
            'Sphinx',
        ])
    )
    ->all();

$rows = (new Query())
    ->match(
        // '@title "Yii"~10' を生成
        (new MatchExpression())->match([
            'proximity',
            'title',
            'Yii',
            10,
        ])
    )
    ->all();
```

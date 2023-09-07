クエリのメタ情報を取得する
==========================

Sphinx では、[SHOW META](https://sphinxsearch.com/docs/current.html#sphinxql-show-meta) SphinxQL 文によって、最後に実行されたクエリの統計的な情報を取得することが出来ます。
この情報は、通常、追加の `SELECT COUNT(*) ...` クエリを発行せずにインデックス中の総行数を取得するのに使用されます。
`SHOW META` のクエリは何時でも手動で実行することが出来ますが、`yii\sphinx\Query` を使えば、
余計な労力を費やさずとも、自動でこれを実行することが出来ます。
必要なことは、`yii\sphinx\Query::showMeta` を有効にして、`yii\sphinx\Query::search()` を使って全ての行とメタ情報を取得することだけです。

```php
$query = new Query();
$results = $query->from('idx_item')
    ->match('foo')
    ->showMeta(true) // 自動の 'SHOW META' クエリを有効にする
    ->search(); // 全ての行とメタ情報を取得する

$items = $results['hits'];
$meta = $results['meta'];
$totalItemCount = $results['meta']['total'];
```

> Note: 'meta' から抽出可能な総アイテム数は Sphinx の `max_matches` オプションの値に制限されることに注意してください。
  あなたのインデックスが `max_matches` の値 (通常は 1000 以下) を超えるレコードを含んでいる場合は、
  レコード数を取得するためには、[[Query::options]] によって `max_matches` を引き上げるか、または [[Query::count()]] を使わなければなりません。

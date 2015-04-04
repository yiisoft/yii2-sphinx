ファセット検索
==============

バージョン 2.2.3 以降、Sphinx は `FACET` 句によるファセット検索の機能を提供しています。

```
SELECT * FROM idx_item FACET brand_id FACET categories;
```

`yii\sphinx\Query` はこの `FACET` 句の構築だけでなく、ファセット結果の取得をもサポートしています。
ファセットは `yii\sphinx\Query::facets` によって指定することが出来ます。
ファセットを伴う結果を取得するためには、`yii\sphinx\Query::search()` を使用する必要があります。
例えば、

```php
$query = new Query();
$results = $query->from('idx_item')
    ->facets([
        'brand_id',
        'categories',
    ])
    ->search($connection); // 全ての行とファセットを取得

$items = $results['hits'];
$facets = $results['facets'];

foreach ($results['facets']['brand_id'] as $frame) {
    $brandId = $frame['value'];
    $count = $frame['count'];
    ...
}
```

> Note|注意: ファセット機能を使おうと試みる前に、Sphinx サーバのバージョン 2.2.3 以降を使用していることを確認してください。

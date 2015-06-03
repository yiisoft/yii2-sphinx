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

配列形式を使うと、`select` や `order` など、ファセットのオプションを追加して指定することが出来ます。

```php
use yii\db\Expression;
use yii\sphinx\Query;

$query = new Query();
$results = $query->from('idx_item')
    ->facets([
        'price' => [
            'select' => 'INTERVAL(price,200,400,600,800) AS price', // 関数を使用
            'order' => ['FACET()' => SORT_ASC],
        ],
        'name_in_json' => [
            'select' => [new Expression('json_attr.name AS name_in_json')], // 不必要な引用符号を避けるために `Expression` を使用する必要がある
        ],
    ])
    ->search($connection);
```

> Note|注意: ファセットにカスタムセレクトを指定する場合は、必ずセレクト文の中にファセット名に対応するカラムを含めてください。
  つまり、'my_facet' というファセットを指定した場合は、そのセレクト文は 'my_facet' という属性か、'my_facet' というエイリアスを持つ式 ('expr() AS my_facet') を含んでいなければなりません。

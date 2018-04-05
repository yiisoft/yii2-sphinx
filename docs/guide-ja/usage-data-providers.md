データ・プロバイダを扱う
========================

[[\yii\sphinx\Query]] および [[\yii\sphinx\ActiveQuery]] とともに [[\yii\data\ActiveDataProvider]] を使うことが出来ます。

```php
use yii\data\ActiveDataProvider;
use yii\sphinx\Query;

$query = new Query();
$query->from('yii2_test_article_index')->match('development');
$provider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => [
        'pageSize' => 10,
    ]
]);
$models = $provider->getModels();
```

```php
use yii\data\ActiveDataProvider;
use app\models\Article;

$provider = new ActiveDataProvider([
    'query' => Article::find(),
    'pagination' => [
        'pageSize' => 10,
    ]
]);
$models = $provider->getModels();
```

しかし、[ファセットの機能](usage-facets.md) または [クエリのメタ情報](usage-meta.md) を利用したい場合は、`yii\sphinx\ActiveDataProvider` を使用する必要があります。
これを使えば、クエリのメタ情報を使ってアイテムの総数を準備したり、ファセット検索の結果を取得したりすることが出来ます。

```php
use yii\sphinx\ActiveDataProvider;
use yii\sphinx\Query;

$query = new Query();
$query->from('idx_item')
    ->match('foo')
    ->showMeta(true)
    ->facets([
        'brand_id',
        'categories',
    ]);
$provider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => [
        'pageSize' => 10,
    ]
]);
$models = $provider->getModels();
$facets = $provider->getFacets();
$brandIdFacet = $provider->getFacet('brand_id');
```
> Note: ページネーションのオフセットとリミットが Sphinx の 'max_matches' の境界を超える可能性があるため、データ・プロバイダはそれらの値に基づいて 'max_matches' オプションを自動的に設定します。
ただし、[[Query::showMeta]] が設定されている場合は、総数の計算結果を損なうことになるため、そのような調整は実行されませんので、あなた自身が 'max_matches' の境界を操作しなければなりません。

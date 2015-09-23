Working with data providers
===========================

You can use [[\yii\data\ActiveDataProvider]] with the [[\yii\sphinx\Query]] and [[\yii\sphinx\ActiveQuery]]:

```php
use yii\data\ActiveDataProvider;
use yii\sphinx\Query;

$query = new Query;
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

However, if you want to use ['facet' feature](usage-facets.md) or [query meta information](usage-meta.md) benefit
you need to use `yii\sphinx\ActiveDataProvider`. It allows preparing total item count using query 'meta' information
and fetching of the facet results:

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

> Note: Because pagination offset and limit may exceed Sphinx 'max_matches' bounds, data provider will set 'max_matches'
  option automatically based on those values. However, if [[Query::showMeta]] is set, such adjustment is not performed
  as it will break total count calculation, so you'll have to deal with 'max_matches' bounds on your own.

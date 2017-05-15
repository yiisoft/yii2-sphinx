Работа с провайдерами данных
===========================

Вы можете использовать [[\yii\data\ActiveDataProvider]] с [[\yii\sphinx\Query]] и [[\yii\sphinx\ActiveQuery]]:

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

Однако, если вы хотите использовать преимущества ['facet'](usage-facets.md) или [мета-информацию запроса](usage-meta.md) вам нужно использовать `yii\sphinx\ActiveDataProvider`. Он позволяет подготовить общее количество элементов с помощью "мета" информации запроса и получить результаты фасета:

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

> Note: Поскольку смещение и ограничение нумерации страниц могут превышать границы Sphinx 'max_matches', провайдер данных установит 'max_matches'
  автоматически на основании этих значений. Однако, если [[Query::showMeta]] установлен, такая корректировка не выполняется, так как это нарушит подсчет общего количества, поэтому вам придется иметь делос ограничениями 'max_matches' на свое усмотрение.
Facet search
============

Since version 2.2.3 Sphinx provides ability of the facet searching via `FACET` clause:

```
SELECT * FROM idx_item FACET brand_id FACET categories;
```

`yii\sphinx\Query` supports composition of this clause as well as fetching facet results.
You may specify facets via `yii\sphinx\Query::facets`. In order to fetch results with facets you need
to use `yii\sphinx\Query::search()` method.
For example:

```php
use yii\sphinx\Query;

$query = new Query();
$results = $query->from('idx_item')
    ->facets([
        'brand_id',
        'categories',
    ])
    ->search($connection); // retrieve all rows and facets

$items = $results['hits'];
$facets = $results['facets'];

foreach ($results['facets']['brand_id'] as $frame) {
    $brandId = $frame['value'];
    $count = $frame['count'];
    ...
}
```

> Note: make sure you are using Sphinx server version 2.2.3 or higher before attempting to use facet feature.

You may specify additional facet options like actual selection and order using array format:

```php
use yii\db\Expression;
use yii\sphinx\Query;

$query = new Query();
$results = $query->from('idx_item')
    ->facets([
        'price' => [
            'select' => 'INTERVAL(price,200,400,600,800) AS price', // using function
            'order' => ['FACET()' => SORT_ASC],
        ],
        'name_in_json' => [
            'select' => [new Expression('json_attr.name AS name_in_json')], // have to use `Expression` to avoid unnecessary quoting
        ],
    ])
    ->search($connection);
```

> Note: if you specify custom select for the facet, ensure facet name has corresponding column inside it.
  This means if you have specified facet named 'my_facet', its select statement should contained 'my_facet' attribute or
  expression aliased to 'my_facet' ('expr() AS my_facet').
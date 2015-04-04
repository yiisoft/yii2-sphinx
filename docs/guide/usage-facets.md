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

Note: make sure you are using Sphinx server version 2.2.3 or higher before attempting to use facet feature.
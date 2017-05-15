Извлечение запроса META информации
===============================

Sphinx allows fetching statistical information about last performed query via [SHOW META](http://sphinxsearch.com/docs/current.html#sphinxql-show-meta) SphinxQL statement.
This information is commonly used to get total count of rows in the index without extra `SELECT COUNT(*) ...` query.
Although you can always run such query manually, `yii\sphinx\Query` allows you to do this automatically without extra efforts.
All you need to do is enable `yii\sphinx\Query::showMeta` and use `yii\sphinx\Query::search()` to fetch all rows and
meta information:

```php
$query = new Query();
$results = $query->from('idx_item')
    ->match('foo')
    ->showMeta(true) // enable automatic 'SHOW META' query
    ->search(); // retrieve all rows and META information

$items = $results['hits'];
$meta = $results['meta'];
$totalItemCount = $results['meta']['total'];
```

> Note: Total item count that can be extracted from 'meta' is limited to `max_matches` sphinx option.
  If your index contains more records than `max_matches` value (usually - 1000), you should either raise up
  `max_matches` via [[Query::options]] or use [[Query::count()]] to retrieve records count.

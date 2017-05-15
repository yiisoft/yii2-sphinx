Фасетный поиск
============

Начиная с версии 2.2.3 Sphinx обеспечивает возможность фасетного поиска с помощью `FACET`:

```
SELECT * FROM idx_item FACET brand_id FACET categories;
```

`yii\sphinx\Query` поддерживает композицию этого предложения, а также выборку фасетных результатов. Вы можете указать фасеты через `yii\sphinx\Query::facets`. Чтобы получать результаты с фасетами, вам нужно использовать метод `yii\sphinx\Query::search()`.
Для примера:

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

> Note: убедитесь, что вы используете сервер Sphinx версии 2.2.3 или выше, прежде чем пытаться использовать функцию фасетов.

Вы можете указать дополнительные опции фасета, такие как `select` или `order` используя формат массива:

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

> Note: если вы укажете пользовательский выбор для фасета, убедитесь, что в названии фасета имеется соответствующий столбец внутри оператора select. Например, если вы указали фасет с именем 'my_facet', его оператор select должен содержать атрибут 'my_facet' или выражение связанное как 'my_facet' ('expr() AS my_facet').
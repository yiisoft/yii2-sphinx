Float params binding
====================

There are issue related to float values binding using PDO and SphinxQL.
PDO does not provide a way to bind a float parameter in prepared statement mode, thus float values are passed
with mode `PDO::PARAM_STR` and thus are bound to the statement as quoted strings, e.g. `'9.85'`.
Unfortunally, SphinxQL is unable to recognize float values passed in this way, producing following error:

> syntax error, unexpected QUOTED_STRING, expecting CONST_INT or CONST_FLOAT

In order to bypass this problem any parameter bind to the [[\yii\sphinx\Command]], which PHP type is exactly 'float',
will be inserted to the SphinxQL content as literal instead of being bound.

This feature works only if value is a native PHP float (strings containing floats do not work).
For example:

```php
use yii\sphinx\Query;

// following code works fine:
$rows = (new Query())->from('item_index')
    ->where('price > :price AND price < :priceMax', [
        'price' => 2.1,
        'priceMax' => 2.9,
    ])
    ->all();

// this one produces an error:
$rows = (new Query())->from('item_index')
    ->where('price > :price AND price < :priceMax', [
        'price' => '2.1',
        'priceMax' => '2.9',
    ])
    ->all();
```

However, in case you are using 'hash' conditions over the index fields declared as 'float', the type conversion will be
performed automatically:

```php
use yii\sphinx\Query;

// following code works fine in case 'price' is a float field in 'item_index':
$rows = (new Query())->from('item_index')
    ->where([
        'price' => '2.5'
    ])
    ->all();
```

> Note: it could be by the time you are reading this float param binding is already fixed at Sphinx server side, or there
  are other concerns about this functionality, which make it undesirable. In this case you can disable automatic
  float params conversion via [[\yii\sphinx\Connection::enableFloatConversion]].


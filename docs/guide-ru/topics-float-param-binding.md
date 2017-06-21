Связывание параметров с плавающей точкой
====================

Существуют проблемы, связанные с привязкой значений float используя PDO и SphinxQL.
PDO не обеспечивает способ привязки параметра float в режиме подготовленных операторов, поэтому значения float передаются в режиме `PDO::PARAM_STR` и, таким образом, привязаны к оператору в виде цитируемых строк, например. ` '9.85'`.
К сожалению, SphinxQL не может распознать значения float, переданные таким образом, создавая следующую ошибку:

> syntax error, unexpected QUOTED_STRING, expecting CONST_INT or CONST_FLOAT

Чтобы обойти эту проблему, любой параметр связывается с [[\yii\sphinx\Command]], какой точно PHP тип 'float',
Будет вставляться в содержимое SphinxQL как литерал вместо привязки.

Эта функция работает только в том случае, если значение является оригинальным PHP float (строки, содержащие float, не работают).
Например:

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

Однако, если вы используете условия 'hash' над полями индекса, объявленными как 'float', преобразование типа будет выполняется автоматически:

```php
use yii\sphinx\Query;

// following code works fine in case 'price' is a float field in 'item_index':
$rows = (new Query())->from('item_index')
    ->where([
        'price' => '2.5'
    ])
    ->all();
```

> Note: к тому моменту, когда вы это читаете, эта привязка к плавающей запятой, может быть уже уже исправлена на стороне сервера Sphinx, или есть другие опасения по поводу этой функции, что делает ее нежелательной. В этом случае вы можете отключить автоматическое преобразование параметров float через
[[\yii\sphinx\Connection::enableFloatConversion]].


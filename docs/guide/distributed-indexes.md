Working with distributed indexes
================================

This extension uses `DESCRIBE` query in order to fetch information about Sphinx index structure (field names and types).
However for the [distributed indexes](http://sphinxsearch.com/docs/current.html#distributed) it is not always possible.
Schema of such index can be found only, if its declaration contains at list one available local index.
For example:

```php
index item_distributed
{
    type = distributed

    # local index :
    local = item_local

    # remote indexes :
    agent = 127.0.0.1:9312:remote_item_1
    agent = 127.0.0.1:9313:remote_item_2
    # ...
}
```

It is recommended to have at least one local index in the distributed index declaration. You are not forced to actually
use it - this local index may be empty, it is needed for the schema declaration only.

Still it is allowed to specify distributed index without local one. For such index the default dummy schema will be used.
However in this case automatic typecasting for the index fields will be unavailable and you should perform data typecast
on your own.
For example:

```php
use yii\sphinx\Query;

// distributed index with local
$rows = (new Query())->from('item_distributed_with_local')
    ->where(['category_id' => '12']) // works fine string `'12'` - converted to integer `12`
    ->all();

// distributed index without local
$rows = (new Query())->from('item_distributed_without_local')
    ->where(['category_id' => '12']) // produces SphinxQL error: 'syntax error, unexpected QUOTED_STRING, expecting CONST_INT'
    ->all();

$rows = (new Query())->from('item_distributed_without_local')
    ->where(['category_id' => (int)'12']) // need to perform typecasting
    ->all();
```

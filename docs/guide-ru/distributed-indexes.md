Работа с распределенными индексами
================================

Это расширение использует запрос `DESCRIBE` для получения информации о структуре индекса Sphinx (имена полей и типы).
Однако для [распределенных индексов](http://sphinxsearch.com/docs/current.html#distributed) это не всегда возможно.
Схема такого индекса может быть найдена только, если его объявление содержит в списке один доступный локальный индекс.
Например:

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

Рекомендуется иметь по крайней мере один локальный индекс в объявлении распределенного индекса. Вы не обязаны его фактически использовать - этот локальный индекс может быть пустым, он необходим только для объявления схемы.

Тем не менее, разрешено указывать распределенный индекс без локального. Для такого индекса будет использоваться схема-заглушка по умолчанию.
Однако в этом случае автоматическое преобразование типов для полей индекса будет недоступно, и вы должны выполнить типизацию данных самостоятельно.
Например:

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

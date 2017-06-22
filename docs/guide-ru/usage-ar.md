Использование ActiveRecord
======================

Это расширение предоставляет ActiveRecord решение, подобное [[\yii\db\ActiveRecord]].
Чтобы объявить класс ActiveRecord, вам нужно расширить [[\yii\sphinx\ActiveRecord]] и реализовать метод `indexName`:

```php
use yii\sphinx\ActiveRecord;

class Article extends ActiveRecord
{
    /**
     * @return string the name of the index associated with this ActiveRecord class.
     */
    public static function indexName()
    {
        return 'idx_article';
    }
}
```
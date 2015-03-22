Using the ActiveRecord
======================

This extension provides ActiveRecord solution similar ot the [[\yii\db\ActiveRecord]].
To declare an ActiveRecord class you need to extend [[\yii\sphinx\ActiveRecord]] and
implement the `indexName` method:

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

アクティブレコードを使用する
============================

このエクステンションは [[\yii\db\ActiveRecord]] と類似したアクティブレコードのソリューションを提供しています。
アクティブレコード・クラスを宣言するためには、[[\yii\sphinx\ActiveRecord]] から拡張して、
`indexName` メソッドを実装する必要があります。

```php
use yii\sphinx\ActiveRecord;

class Article extends ActiveRecord
{
    /**
     * @return string このアクティブレコードクラスと関連付けられた index の名前
     */
    public static function indexName()
    {
        return 'idx_article';
    }
}
```

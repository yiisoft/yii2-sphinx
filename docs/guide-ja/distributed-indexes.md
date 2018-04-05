分散型インデックスを扱う
========================

このエクステンションは Sphinx のインデックス構造 (フィールド名と型) を取得するのに `DESCRIBE` クエリを使用します。
しかし、[分散されたインデックス](http://sphinxsearch.com/docs/current.html#distributed) の場合は、何時でもそれが出来るとは限りません。
そのようなインデックスのスキーマは、その宣言に少なくとも一つは利用可能なローカルのインデックスが含まれていなければ、取得できません。
例えば、

```php
index item_distributed
{
    type = distributed

    # ローカルのインデックス
    local = item_local

    # リモートのインデックス
    agent = 127.0.0.1:9312:remote_item_1
    agent = 127.0.0.1:9313:remote_item_2
    # ...
}
```

分散型インデックスの宣言には少なくとも一つのローカル・インデックスを含めることが推奨されます。
実際にそれを使うことは強制されません。このローカル・インデックスは空っぽでも構いません。これはスキーマ宣言のためだけに必要とされます。

ただし、分散型のインデックスをローカル・インデックス無しで指定することは依然として許されています。
そのようなインデックスには、デフォルトのダミー・スキーマが使われます。
しかし、その場合には、インデックス・フィールドの自動的な型キャストが利用できなくなり、データの型キャストを自分自身でやらなければならなくなります。
例えば、

```php
use yii\sphinx\Query;

// ローカル・インデックスが有る分散型インデックス
$rows = (new Query())->from('item_distributed_with_local')
    ->where(['category_id' => '12']) // 動作する - 文字列 `'12'` は整数 `12` に変換される
    ->all();

// ローカル・インデックスの無い分散型インデックス
$rows = (new Query())->from('item_distributed_without_local')
    ->where(['category_id' => '12']) // sphinxQL のエラーになる - 'syntax error, unexpected QUOTED_STRING, expecting CONST_INT'
    ->all();

$rows = (new Query())->from('item_distributed_without_local')
    ->where(['category_id' => (int)'12']) // 型キャストが必要
    ->all();
```

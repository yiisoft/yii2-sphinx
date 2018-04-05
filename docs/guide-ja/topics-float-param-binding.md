float のパラメタ・バインディング
=================================

PDO と SphinxQL を使う場合、浮動小数点数のパラメタ・バインディングに関連する問題があります。
PDO はプリペアド・ステートメントで浮動小数点数のパラメタをバインドする方法を提供していません。
従って、浮動小数点数は `PDO::PARAM_STR` のモードで渡され、引用符で囲まれた文字列 (例えば `'9.85'`) としてステートメントにバインドされます。
不幸なことに、SphinxQL はこのようにして渡された浮動小数点数の値を認識することが出来ず、次のようなエラーを発生させます。

> syntax error, unexpected QUOTED_STRING, expecting CONST_INT or CONST_FLOAT

この問題を回避するために、[[\yii\sphinx\Command]] にバインドされる値の PHP タイプが厳密に `float` である場合は、
SphinxQL のコンテントに、バインドされるのでなく、リテラルとして直接に書き込まれます。

この機能は値が PHP のネイティブな float である場合にだけ働きます。浮動小数点数を表す文字列では動作しません。
例えば、

```php
use yii\sphinx\Query;

// 次のコードは問題なく動作する
$rows = (new Query())->from('item_index')
    ->where('price > :price AND price < :priceMax', [
        'price' => 2.1,
        'priceMax' => 2.9,
    ])
    ->all();

// 次のコードはエラーになる
$rows = (new Query())->from('item_index')
    ->where('price > :price AND price < :priceMax', [
        'price' => '2.1',
        'priceMax' => '2.9',
    ])
    ->all();
```

しかし、'float' と宣言されたインデックス・フィールドに対して 'hash' 形式の条件を使う場合は、
自動的に型の変換が実行されます。

```php
use yii\sphinx\Query;

// 'price' が 'item_index' で float のフィールドであれば、次のコードは動作する
$rows = (new Query())->from('item_index')
    ->where([
        'price' => '2.5'
    ])
    ->all();
```

> Note: あなたがこの文を読んでいる頃までには、float のパラメタ・バインディングの問題は Sphinx のサーバ・サイドで解決されているかもしれません。
  または、他に何か懸念があって、この機能を使いたくないかもしれません。
  その場合は、[[\yii\sphinx\Connection::enableFloatConversion]] によって、自動的な float パラメータの変換を無効にすることが出来ます。


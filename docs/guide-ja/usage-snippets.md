スニペット (抜粋) を構築する
============================

スニペット (抜粋) はインデックスされたソーステキストの断片で、全文検索の条件に従ってハイライトされた言葉を含むものです。
Sphinx はスニペットを構築する強力なメカニズムを内蔵しています。
しかしながら、Sphinx はオリジナルのインデックスされたテキストを保存しないため、クエリ結果に含まれる行に対するスニペットは、独立した別のクエリによって構築されなければなりません。
そのようなクエリを `yii\sphinx\Command::callSnippets()` によって実行することが出来ます。

```php
$sql = "SELECT * FROM idx_item WHERE MATCH('about')";
$rows = Yii::$app->sphinx->createCommand($sql)->queryAll();

$rowSnippetSources = [];
foreach ($rows as $row) {
    $rowSnippetSources[] = file_get_contents('/path/to/index/files/' . $row['id'] . '.txt');
}

$snippets = Yii::$app->sphinx->createCommand($sql)->callSnippets('idx_item', $rowSnippetSources, 'about');
```

このワークフローは、[[yii\sphinx\Query::snippetCallback]] を使って、単純化することが出来ます。
これは、クエリ結果の行の配列を引数として取り、入力された行の順序にしたがってスニペットのソース文字列の配列を返す PHP コールバックです。
例えば、

```php
use yii\sphinx\Query;

$query = new Query();
$rows = $query->from('idx_item')
    ->match($_POST['search'])
    ->snippetCallback(function ($rows) {
        $result = [];
        foreach ($rows as $row) {
            $result[] = file_get_contents('/path/to/index/files/' . $row['id'] . '.txt');
        }
        return $result;
    })
    ->all();

foreach ($rows as $row) {
    echo $row['snippet'];
}
```

アクティブレコードを使う場合は、[[yii\sphinx\ActiveQuery::snippetByModel()]] を使ってスニペットを構築することが出来ます。
このメソッドは、検索結果であるモデルの `getSnippetSource()` メソッドを呼ぶことによって、各行ごとにスニペットのソースを取得します。
必要なことは、あなたのアクティブレコードクラスにおいて、正しい値を返すように `getSnippetSource()` メソッドを実装することだけです。

```php
use yii\sphinx\ActiveRecord;

class Article extends ActiveRecord
{
    public function getSnippetSource()
    {
        return file_get_contents('/path/to/source/files/' . $this->id . '.txt');
    }
}

$articles = Article::find()->snippetByModel()->all();

foreach ($articles as $article) {
    echo $article->snippet;
}
```

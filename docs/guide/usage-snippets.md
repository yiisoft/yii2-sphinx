Building Snippets (Excerpts)
============================

Snippet (excerpt) - is a fragment of the index source text, which contains highlighted words from fulltext search
condition. Sphinx has a powerful build-in mechanism to compose snippets. However, since Sphinx does not store the
original indexed text, the snippets for the rows in query result should be build separately via another query.
Such query may be performed via `yii\sphinx\Command::callSnippets()`:

```php
$sql = "SELECT * FROM idx_item WHERE MATCH('about')";
$rows = Yii::$app->sphinx->createCommand($sql)->queryAll();

$rowSnippetSources = [];
foreach ($rows as $row) {
    $rowSnippetSources[] = file_get_contents('/path/to/index/files/' . $row['id'] . '.txt');
}

$snippets = Yii::$app->sphinx->createCommand($sql)->callSnippets('idx_item', $rowSnippetSources, 'about');
```

You can simplify this workflow using [[yii\sphinx\Query::snippetCallback]].
It is a PHP callback, which receives array of query result rows as an argument and must return the
array of snippet source strings in the order, which match one of incoming rows.
Example:

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

If you are using Active Record, you can [[yii\sphinx\ActiveQuery::snippetByModel()]] to compose a snippets.
This method retrieves snippet source per each row calling `getSnippetSource()` method of the result model.
All you need to do is implement it in your Active Record class, so it return the correct value:

```php
use yii\sphinx\ActiveRecord;

class Article extends ActiveRecord
{
    public function getSnippetSource()
    {
        return file_get_contents('/path/to/source/files/' . $this->id . '.txt');;
    }
}

$articles = Article::find()->snippetByModel()->all();

foreach ($articles as $article) {
    echo $article->snippet;
}
```

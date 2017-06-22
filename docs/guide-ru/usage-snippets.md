Создание снипетов (Выдержки)
============================

Сниппет (выдержка) - фрагмент исходного текста индекса, который содержит выделенные слова изсостояния полнотекстового поиска. Sphinx имеет мощный встроенный механизм для создания фрагментов. Однако, поскольку Sphinx не сохраняет исходный проиндексированный текст, фрагменты для строк в результате запроса должны быть построены отдельно через другой запрос.
Такой запрос может быть выполнен с помощью `yii\sphinx\Command::callSnippets()`:

```php
$sql = "SELECT * FROM idx_item WHERE MATCH('about')";
$rows = Yii::$app->sphinx->createCommand($sql)->queryAll();

$rowSnippetSources = [];
foreach ($rows as $row) {
    $rowSnippetSources[] = file_get_contents('/path/to/index/files/' . $row['id'] . '.txt');
}

$snippets = Yii::$app->sphinx->createCommand($sql)->callSnippets('idx_item', $rowSnippetSources, 'about');
```

Вы можете упростить этот рабочий процесс, используя [[yii\sphinx\Query::snippetCallback]].
Это PHP колбек, который получает массив строк результата запроса в качестве аргумента и должен возвращать массив строк источника фрагмента в порядке, который соответствует одной из входящих строк.
Например:

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

Если вы используете Active Record, вы можете создать фрагменты [[yii\sphinx\ActiveQuery::snippetByModel()]].
Этот метод извлекает источник фрагмента для каждой строки, вызывающий метод `getSnippetSource()` модели результата.
Все, что вам нужно сделать, это реализовать его в классе Active Record, чтобы он вернул правильное значение:

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

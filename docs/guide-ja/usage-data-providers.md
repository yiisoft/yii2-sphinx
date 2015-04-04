データプロバイダを扱う
======================

[[\yii\sphinx\Query]] および [[\yii\sphinx\ActiveQuery]] を使って [[\yii\data\ActiveDataProvider]] を使うことが出来ます。

```php
use yii\data\ActiveDataProvider;
use yii\sphinx\Query;

$query = new Query;
$query->from('yii2_test_article_index')->match('development');
$provider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => [
        'pageSize' => 10,
    ]
]);
$models = $provider->getModels();
```

```php
use yii\data\ActiveDataProvider;
use app\models\Article;

$provider = new ActiveDataProvider([
    'query' => Article::find(),
    'pagination' => [
        'pageSize' => 10,
    ]
]);
$models = $provider->getModels();
```

Составление инструкции 'MATCH'
===========================

Sphinx usage does not make sense unless you are using its fulltext search ability.
In SphinxQL it is provided via 'MATCH' statement. You can always compose it manually as a part of the 'where'
condition, but if you are using `yii\sphinx\Query` you can do it via `yii\sphinx\Query::match()`:

```php
use yii\sphinx\Query;

$query = new Query();
$rows = $query->from('idx_item')
    ->match($_POST['search'])
    ->all();
```

Please note that Sphinx 'MATCH' statement argument uses complex internal syntax for better tuning.
By default `yii\sphinx\Query::match()` will escape all special characters related to this syntax from
its argument. So if you wish to use complex 'MATCH' statement, you should use `yii\db\Expression` for it:

```php
use yii\sphinx\Query;
use yii\db\Expression;

$query = new Query();
$rows = $query->from('idx_item')
    ->match(new Expression(':match', ['match' => '@(content) ' . Yii::$app->sphinx->escapeMatchValue($_POST['search'])]))
    ->all();
```

> Note: if you compose 'MATCH' argument, make sure to use `\yii\sphinx\Connection::escapeMatchValue()` to properly
  escape any special characters, which may break the query.

Since version 2.0.6 you can use [[\yii\sphinx\MatchExpression]] for the 'MATCH' statement composition.
It allows composition of the 'MATCH' expression using placeholders in similar way as bound parameters, which
values will be automatically escaped using [[\yii\sphinx\Connection::escapeMatchValue()]].
For examples:

```php
use yii\sphinx\Query;
use yii\sphinx\MatchExpression;

$rows = (new Query())
    ->match(new MatchExpression('@title :title', ['title' => 'Yii'])) // value of ':title' will be escaped automatically
    ->all();
```

You may use [[match()]], [[andMatch()]] and [[orMatch()]] to combine several conditions.
Each condition can be specified using array syntax similar to the one used for [[\yii\sphinx\Query:where]].
For example:

```php
use yii\sphinx\Query;
use yii\sphinx\MatchExpression;

$rows = (new Query())
    ->match(
        // produces '((@title "Yii") (@author "Paul")) | (@content "Sphinx")' :
        (new MatchExpression())
            ->match(['title' => 'Yii'])
            ->andMatch(['author' => 'Paul'])
            ->orMatch(['content' => 'Sphinx'])
    )
    ->all();
```

You may as well compose expressions with special operators like 'MAYBE', 'PROXIMITY' etc.
For example:

```php
use yii\sphinx\Query;
use yii\sphinx\MatchExpression;

$rows = (new Query())
    ->match(
        // produces '@title "Yii" MAYBE "Sphinx"' :
        (new MatchExpression())->match([
            'maybe',
            'title',
            'Yii',
            'Sphinx',
        ])
    )
    ->all();

$rows = (new Query())
    ->match(
        // produces '@title "Yii"~10' :
        (new MatchExpression())->match([
            'proximity',
            'title',
            'Yii',
            10,
        ])
    )
    ->all();
```

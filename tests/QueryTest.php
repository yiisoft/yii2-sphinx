<?php

namespace yiiunit\extensions\sphinx;

use Yii;
use yii\db\Expression;
use yii\sphinx\MatchExpression;
use yii\sphinx\Query;

/**
 * @group sphinx
 */
class QueryTest extends TestCase
{
    public function testSelect()
    {
        // default
        $query = new Query();
        $query->select('*');
        $this->assertEquals(['*' => '*'], $query->select);
        $this->assertFalse($query->distinct);
        $this->assertEquals(null, $query->selectOption);

        $query = new Query();
        $query->select('id, name', 'something')->distinct(true);
        $this->assertEquals(['id' => 'id', 'name' => 'name'], $query->select);
        $this->assertTrue($query->distinct);
        $this->assertEquals('something', $query->selectOption);
    }

    public function testFrom()
    {
        $query = new Query();
        $query->from('user');
        $this->assertEquals(['user'], $query->from);
    }

    public function testMatch()
    {
        $query = new Query();
        $match = 'test match';
        $query->match($match);
        $this->assertEquals($match, $query->match);

        $command = $query->createCommand($this->getConnection(false));
        $this->assertContains('MATCH(', $command->getSql(), 'No MATCH operator present!');
        $this->assertContains($match, $command->params, 'No match query among params!');
    }

    public function testWhere()
    {
        $query = new Query();
        $query->where('id = :id', [':id' => 1]);
        $this->assertEquals('id = :id', $query->where);
        $this->assertEquals([':id' => 1], $query->params);

        $query->andWhere('name = :name', [':name' => 'something']);
        $this->assertEquals(['and', 'id = :id', 'name = :name'], $query->where);
        $this->assertEquals([':id' => 1, ':name' => 'something'], $query->params);

        $query->orWhere('age = :age', [':age' => '30']);
        $this->assertEquals(['or', ['and', 'id = :id', 'name = :name'], 'age = :age'], $query->where);
        $this->assertEquals([':id' => 1, ':name' => 'something', ':age' => '30'], $query->params);
    }

    /**
     * @depends testWhere
     */
    public function testWhereExpression()
    {
        $query = new Query();
        $expression = new Expression('name = :name', ['name' => 'foo']);
        $query->where($expression);

        $command = $query->createCommand($this->getConnection(false));
        $this->assertContains($expression->expression, $command->getSql());
        $this->assertEquals($expression->params, $command->params);
    }

    public function testFilterWhere()
    {
        // should work with hash format
        $query = new Query();
        $query->filterWhere([
            'id' => 0,
            'title' => '   ',
            'author_ids' => [],
        ]);
        $this->assertEquals(['id' => 0], $query->where);

        $query->andFilterWhere(['status' => null]);
        $this->assertEquals(['id' => 0], $query->where);

        $query->orFilterWhere(['name' => '']);
        $this->assertEquals(['id' => 0], $query->where);

        // should work with operator format
        $query = new Query();
        $condition = ['like', 'name', 'Alex'];
        $query->filterWhere($condition);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['between', 'id', null, null]);
        $this->assertEquals($condition, $query->where);

        $query->orFilterWhere(['not between', 'id', null, null]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['like', 'id', '']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['or like', 'id', '']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not like', 'id', '   ']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['or not like', 'id', null]);
        $this->assertEquals($condition, $query->where);
    }

    public function testFilterWhereRecursively()
    {
        $query = new Query();
        $query->filterWhere(['and', ['like', 'name', ''], ['like', 'title', ''], ['id' => 1], ['not', ['like', 'name', '']]]);
        $this->assertEquals(['and', ['id' => 1]], $query->where);
    }

    public function testGroup()
    {
        $query = new Query();
        $query->groupBy('team');
        $this->assertEquals(['team'], $query->groupBy);

        $query->addGroupBy('company');
        $this->assertEquals(['team', 'company'], $query->groupBy);

        $query->addGroupBy('age');
        $this->assertEquals(['team', 'company', 'age'], $query->groupBy);
    }

    public function testHaving()
    {
        $query = new Query();
        $query->having('id = :id', [':id' => 1]);
        $this->assertEquals('id = :id', $query->having);
        $this->assertEquals([':id' => 1], $query->params);

        $query->andHaving('name = :name', [':name' => 'something']);
        $this->assertEquals(['and', 'id = :id', 'name = :name'], $query->having);
        $this->assertEquals([':id' => 1, ':name' => 'something'], $query->params);

        $query->orHaving('age = :age', [':age' => '30']);
        $this->assertEquals(['or', ['and', 'id = :id', 'name = :name'], 'age = :age'], $query->having);
        $this->assertEquals([':id' => 1, ':name' => 'something', ':age' => '30'], $query->params);
    }

    public function testOrder()
    {
        $query = new Query();
        $query->orderBy('team');
        $this->assertEquals(['team' => SORT_ASC], $query->orderBy);

        $query->addOrderBy('company');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC], $query->orderBy);

        $query->addOrderBy('age');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_ASC], $query->orderBy);

        $query->addOrderBy(['age' => SORT_DESC]);
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_DESC], $query->orderBy);

        $query->addOrderBy('age ASC, company DESC');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_DESC, 'age' => SORT_ASC], $query->orderBy);
    }

    public function testLimitOffset()
    {
        $query = new Query();
        $query->limit(10)->offset(5);
        $this->assertEquals(10, $query->limit);
        $this->assertEquals(5, $query->offset);
    }

    public function testWithin()
    {
        $query = new Query();
        $query->within('team');
        $this->assertEquals(['team' => SORT_ASC], $query->within);

        $query->addWithin('company');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC], $query->within);

        $query->addWithin('age');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_ASC], $query->within);

        $query->addWithin(['age' => SORT_DESC]);
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_DESC], $query->within);

        $query->addWithin('age ASC, company DESC');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_DESC, 'age' => SORT_ASC], $query->within);
    }

    public function testOptions()
    {
        $query = new Query();
        $options = [
            'cutoff' => 50,
            'max_matches' => 50,
        ];
        $query->options($options);
        $this->assertEquals($options, $query->options);

        $newMaxMatches = $options['max_matches'] + 10;
        $query->addOptions(['max_matches' => $newMaxMatches]);
        $this->assertEquals($newMaxMatches, $query->options['max_matches']);
    }

    public function testRun()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $rows = $query->from('yii2_test_article_index')
            ->match('about')
            ->options([
                'cutoff' => 50,
                'field_weights' => [
                    'title' => 10,
                    'content' => 3,
                ],
            ])
            ->all($connection);
        $this->assertNotEmpty($rows);
    }

    /**
     * @depends testRun
     */
    public function testSnippet()
    {
        $connection = $this->getConnection();

        $match = 'about';
        $snippetPrefix = 'snippet#';
        $snippetCallback = function () use ($match, $snippetPrefix) {
            return [
                $snippetPrefix . '1: ' . $match,
                $snippetPrefix . '2: ' . $match,
            ];
        };
        $snippetOptions = [
            'before_match' => '[',
            'after_match' => ']',
        ];

        $rows = (new Query())
            ->from('yii2_test_article_index')
            ->match($match)
            ->snippetCallback($snippetCallback)
            ->snippetOptions($snippetOptions)
            ->all($connection);
        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertContains($snippetPrefix, $row['snippet'], 'Snippet source not present!');
            $this->assertContains($snippetOptions['before_match'] . $match, $row['snippet'] . $snippetOptions['after_match'], 'Options not applied!');
        }

        // @see https://github.com/yiisoft/yii2-sphinx/issues/61
        $rows = (new Query())
            ->from('yii2_test_article_index')
            ->match(new Expression(':match', ['match' => '@(content) ' . $connection->escapeMatchValue('about')]))
            ->snippetCallback($snippetCallback)
            ->snippetOptions($snippetOptions)
            ->all($connection);
        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertContains($snippetPrefix, $row['snippet'], 'Snippet source not present!');
            $this->assertContains($snippetOptions['before_match'] . $match, $row['snippet'] . $snippetOptions['after_match'], 'Options not applied!');
        }

        // @see https://github.com/yiisoft/yii2-sphinx/pull/93
        $rows = (new Query())
            ->from('yii2_test_article_index')
            ->match($match)
            ->snippetCallback(function ($rows) {
                return [
                    123,
                    null,
                ];
            })
            ->all($connection);
        $this->assertNotEmpty($rows);
        $this->assertSame('123', $rows[0]['snippet']);
        $this->assertSame('', $rows[1]['snippet']);
    }

    public function testCount()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $count = $query->from('yii2_test_article_index')
            ->match('about')
            ->count('*', $connection);
        $this->assertEquals(2, $count);
    }

    /**
     * @depends testRun
     *
     * @see https://github.com/yiisoft/yii2-sphinx/issues/9
     */
    public function testRunAndWhere()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $rows = $query->from('yii2_test_item_index')
            ->where([
                'category_id' => 2,
                'id' => 2,
            ])
            ->all($connection);
        $this->assertCount(1, $rows);
    }

    /**
     * @depends testRun
     */
    public function testWhereSpecialCharValue()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $rows = $query->from('yii2_test_article_index')
            ->andWhere(['author_id' => 'some"'])
            ->all($connection);
        $this->assertEmpty($rows);
    }

    /**
     * Data provider for [[testMatchSpecialCharValue()]]
     * @return array test data
     */
    public function dataProviderMatchSpecialCharValue()
    {
        return [
            ["'"],
            ['"'],
            ['@'],
            ['\\'],
            ['()'],
            ['<<<'],
            ['>>>'],
            ["\x00"],
            ["\n"],
            ["\r"],
            ["\x1a"],
            ['\\' . "'"],
            ['\\' . '"'],
        ];
    }

    /**
     * @dataProvider dataProviderMatchSpecialCharValue
     * @depends testRun
     *
     * @param string $char char to be tested
     *
     * @see https://github.com/yiisoft/yii2/issues/3668
     */
    public function testMatchSpecialCharValue($char)
    {
        $connection = $this->getConnection();

        $query = new Query();
        $rows = $query->from('yii2_test_article_index')
            ->match('about' . $char)
            ->all($connection);
        $this->assertTrue(is_array($rows)); // no query error
    }

    /**
     * @depends testMatchSpecialCharValue
     */
    public function testMatchComplex()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $rows = $query->from('yii2_test_article_index')
            ->match(new Expression(':match', ['match' => '@(content) ' . $connection->escapeMatchValue('about\\"')]))
            ->all($connection);
        $this->assertNotEmpty($rows);
    }

    /**
     * @depends testMatchComplex
     */
    public function testMatchExpression()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $rows = $query->from('yii2_test_article_index')
            ->match(new MatchExpression(':match', ['match' => 'cats']))
            ->all($connection);
        $this->assertCount(1, $rows);

        $query = new Query();
        $rows = $query->from('yii2_test_article_index')
            ->match(
                (new MatchExpression())
                    ->andMatch(['title' => 'cats'])
                    ->orMatch(['content' => 'dogs'])
            )
            ->all($connection);
        $this->assertCount(2, $rows);
    }

    /**
     * @depends testRun
     *
     * @see https://github.com/yiisoft/yii2/issues/4375
     */
    public function testRunOnDistributedIndex()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $rows = $query->from('yii2_test_distributed')
            ->match('about')
            ->options([
                'cutoff' => 50,
                'field_weights' => [
                    'title' => 10,
                    'content' => 3,
                ],
            ])
            ->all($connection);
        $this->assertNotEmpty($rows);
    }

    /**
     * @depends testRun
     */
    public function testFacets()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $results = $query->from('yii2_test_article_index')
            ->match('about')
            ->facets([
                'author_id'
            ])
            ->search($connection);
        $this->assertNotEmpty($results['hits'], 'Unable to query with facet');
        $this->assertNotEmpty($results['facets']['author_id'], 'Unable to fill up facet');

        $query = new Query();
        $results = $query->from('yii2_test_article_index')
            ->match('about')
            ->facets([
                'author_id' => [
                    'order' => ['COUNT(*)' => SORT_ASC]
                ],
            ])
            ->search($connection);
        $this->assertNotEmpty($results['hits'], 'Unable to query with complex facet');
        $this->assertNotEmpty($results['facets']['author_id'], 'Unable to fill up complex facet');

        $query = (new Query())
            ->from('yii2_test_article_index')
            ->match('about');

        try {
            // Sphinx ^3
            $results = $query
                ->select(new Expression('INTERVAL(author_id,200,400,600,800) AS range'))
                ->facets([
                    'range' => [
                        'select' => 'range',
                    ],
                    'authorId' => [
                        'select' => [new Expression('author_id AS authorId')],
                    ],
                ])
                ->search($connection);
        } catch (\PDOException $e) {
            // Sphinx ^2
            $results = $query
                ->facets([
                    'range' => [
                        'select' => 'INTERVAL(author_id,200,400,600,800) AS range',
                    ],
                    'authorId' => [
                        'select' => [new Expression('author_id AS authorId')],
                    ],
                ])
                ->search($connection);
        }
        $this->assertNotEmpty($results['hits'], 'Unable to query with facet using custom select');
        $this->assertNotEmpty($results['facets']['range'], 'Unable to fill up facet using function in select');
        $this->assertNotEmpty($results['facets']['authorId'], 'Unable to fill up facet using `Expression` in select');
    }

    /**
     * @depends testRun
     */
    public function testShowMeta()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $results = $query->from('yii2_test_article_index')
            ->match('about')
            ->showMeta(true)
            ->search($connection);
        $this->assertNotEmpty($results['hits'], 'Unable to query with meta');
        $this->assertNotEmpty($results['meta'], 'Unable to fill meta');
        $this->assertEquals(2, $results['meta']['total'], 'Wrong meta "total"');
        $this->assertArrayHasKey('time', $results['meta'], '"time" meta data missing.');

        $query = new Query();
        $results = $query->from('yii2_test_article_index')
            ->match('about')
            ->showMeta('total')
            ->search($connection);
        $this->assertArrayHasKey('total', $results['meta'], '"total" meta data missing.');
        $this->assertArrayNotHasKey('time', $results['meta'], '"time" meta data present.');
    }

    /**
     * @depends testFacets
     * @depends testShowMeta
     */
    public function testShowMetaWithFacet()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $results = $query->from('yii2_test_article_index')
            ->match('about')
            ->showMeta(true)
            ->facets([
                'author_id'
            ])
            ->search($connection);
        $this->assertNotEmpty($results['hits'], 'Unable to query with facet');
        $this->assertNotEmpty($results['meta'], 'Unable to fill meta');
        $this->assertNotEmpty($results['facets']['author_id'], 'Unable to fill up facet');
    }

    /**
     * @see https://github.com/yiisoft/yii2-sphinx/issues/31
     *
     * @depends testRun
     */
    public function testWhereEmptyIn()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $results = $query->from('yii2_test_article_index')
            ->where(['id' => []])
            ->all($connection);

        $this->assertEmpty($results);
    }

    /**
     * @see https://github.com/yiisoft/yii2-sphinx/issues/43
     *
     * @depends testRun
     * @depends testWithin
     */
    public function testRunWithin()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $results = $query->from('yii2_test_article_index')
            ->groupBy(['author_id'])
            ->within(['author_id' => SORT_ASC])
            ->all($connection);

        $this->assertNotEmpty($results);
    }

    /**
     * @see https://github.com/yiisoft/yii2-sphinx/issues/26
     *
     * @depends testRun
     * @depends testGroup
     */
    public function testGroupLimit()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $results = $query->from('yii2_test_article_index')
            ->andWhere(['author_id' => 1])
            ->groupBy(['author_id'])
            ->groupLimit(3)
            ->all($connection);

        $this->assertCount(3, $results);
    }

    /**
     * @depends testRun
     *
     * @see https://github.com/yiisoft/yii2-sphinx/issues/8
     */
    public function testFloatCondition()
    {
        $connection = $this->getConnection();

        $query = new Query();
        $rows = $query->from('yii2_test_item_index')
            ->where('price > :price AND price < :priceMax', [
                'price' => 2.1,
                'priceMax' => 2.9,
            ])
            ->all($connection);
        $this->assertCount(1, $rows);

        $query = new Query();
        $rows = $query->from('yii2_test_item_index')
            ->where([
                'price' => '2.5'
            ])
            ->all($connection);
        $this->assertCount(1, $rows);

        $query = new Query();
        $rows = $query->from('yii2_test_item_index')
            ->where([
                'price' => 25000.956
            ])
            ->all($connection);
        $this->assertCount(0, $rows);

        $query = new Query();
        $rows = $query->from('yii2_test_item_index')
            ->where([
                'price' => 2.5E-10
            ])
            ->all($connection);
        $this->assertCount(0, $rows);
    }

    /**
     * @depends testRun
     */
    public function testColumn()
    {
        $db = $this->getConnection();
        $result = (new Query())
            ->select('category_id')
            ->from('yii2_test_item_index')
            ->orderBy(['id' => SORT_DESC])
            ->column($db);
        $this->assertEquals([2, 1], $result);

        // https://github.com/yiisoft/yii2/issues/7515
        $result = (new Query())
            ->select('category_id')
            ->from('yii2_test_item_index')
            ->orderBy(['id' => SORT_DESC])
            ->indexBy('id')
            ->column($db);
        $this->assertEquals([2 => 2, 1 => 1], $result);

        // https://github.com/yiisoft/yii2/issues/12649
        $result = (new Query())
            ->select(['category_id', 'id'])
            ->from('yii2_test_item_index')
            ->orderBy(['id' => SORT_DESC])
            ->indexBy(function ($row) {
                return $row['id'] * 2;
            })
            ->column($db);
        $this->assertEquals([4 => 2, 2 => 1], $result);
    }

    public function testEmulateExecution()
    {
        $query = new Query();
        if (!$query->hasMethod('emulateExecution')) {
            $this->markTestSkipped('"yii2" version 2.0.11 or higher required');
        }

        $db = $this->getConnection();

        $this->assertGreaterThan(0, $query->from('yii2_test_article_index')->count('*', $db));

        $rows = (new Query())
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->all($db);
        $this->assertSame([], $rows);

        $row = (new Query())
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->one($db);
        $this->assertSame(false, $row);

        $exists = (new Query())
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->exists($db);
        $this->assertSame(false, $exists);

        $count = (new Query())
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->count('*', $db);
        $this->assertSame(0, $count);

        $sum = (new Query())
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->sum('id', $db);
        $this->assertSame(0, $sum);

        $sum = (new Query())
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->average('id', $db);
        $this->assertSame(0, $sum);

        $max = (new Query())
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->max('id', $db);
        $this->assertSame(null, $max);

        $min = (new Query())
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->min('id', $db);
        $this->assertSame(null, $min);

        $scalar = (new Query())
            ->select(['id'])
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->scalar($db);
        $this->assertSame(null, $scalar);

        $column = (new Query())
            ->select(['id'])
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->column($db);
        $this->assertSame([], $column);

        $results = (new Query())
            ->from('yii2_test_article_index')
            ->emulateExecution()
            ->search($db);
        $this->assertSame(['hits' => [], 'facets' => [], 'meta' => []], $results);
    }

    /**
     * @see https://github.com/yiisoft/yii2-sphinx/issues/71
     *
     * @depends testWhere
     */
    public function testWhereCompare()
    {
        $db = $this->getConnection();

        $rows = (new Query())
            ->from('yii2_test_item_index')
            ->andWhere(['>', 'category_id', '1'])
            ->all($db);
        $this->assertCount(1, $rows);

        $rows = (new Query())
            ->from('yii2_test_item_index')
            ->andWhere(['>', 'add_date', time() + 1])
            ->all($db);
        $this->assertCount(0, $rows);

        $rows = (new Query())
            ->from('yii2_test_item_index')
            ->andWhere(['<', 'add_date', time() + 1])
            ->all($db);
        $this->assertCount(2, $rows);
    }

    /**
     * @see https://github.com/yiisoft/yii2-sphinx/issues/69
     *
     * @depends testFilterWhere
     * @depends testWhereCompare
     */
    public function testRunFilterWhere()
    {
        $db = $this->getConnection();

        $rows = (new Query())
            ->from('yii2_test_item_index')
            ->andFilterWhere(['category_id' => '2'])
            ->all($db);
        $this->assertCount(1, $rows);

        $rows = (new Query())
            ->from('yii2_test_item_index')
            ->andFilterWhere(['>', 'category_id', '1'])
            ->all($db);
        $this->assertCount(1, $rows);

        $rows = (new Query())
            ->from('yii2_test_item_index')
            ->andFilterWhere(['>', 'category_id', new Expression('1')])
            ->all($db);
        $this->assertCount(1, $rows);
    }

    /**
     * @depends testRun
     *
     * @see https://github.com/yiisoft/yii2-sphinx/issues/82
     */
    public function testSelectExpression()
    {
        $connection = $this->getConnection();

        $row = (new Query())
            ->select([new Expression('author_id AS author')])
            ->from('yii2_test_article_index')
            ->limit(1)
            ->one($connection);
        $this->assertNotEmpty($row['author']);

        $row = (new Query())
            ->select(['author' => new Expression('author_id')])
            ->from('yii2_test_article_index')
            ->limit(1)
            ->one($connection);
        $this->assertNotEmpty($row['author']);
    }
}

<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\ActiveQuery;
use yiiunit\extensions\sphinx\data\ar\ActiveRecord;
use yiiunit\extensions\sphinx\data\ar\ArticleIndex;
use yiiunit\extensions\sphinx\data\ar\RtIndex;

/**
 * @group sphinx
 */
class ActiveRecordTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
    }

    protected function tearDown()
    {
        $this->truncateIndex('yii2_test_rt_index');
        parent::tearDown();
    }

    // Tests :

    public function testFind()
    {
        // find one
        $result = ArticleIndex::find();
        $this->assertTrue($result instanceof ActiveQuery);
        $article = $result->one();
        $this->assertTrue($article instanceof ArticleIndex);

        // find all
        $articles = ArticleIndex::find()->all();
        $this->assertEquals(20, count($articles));
        $this->assertTrue($articles[0] instanceof ArticleIndex);
        $this->assertTrue($articles[1] instanceof ArticleIndex);

        // find fulltext
        $article = ArticleIndex::findOne(2);
        $this->assertTrue($article instanceof ArticleIndex);
        $this->assertEquals(2, $article->id);

        // find by column values
        $article = ArticleIndex::findOne(['id' => 2, 'author_id' => 2]);
        $this->assertTrue($article instanceof ArticleIndex);
        $this->assertEquals(2, $article->id);
        $this->assertEquals(2, $article->author_id);
        $article = ArticleIndex::findOne(['id' => 2, 'author_id' => 1]);
        $this->assertNull($article);

        // find by attributes
        $article = ArticleIndex::find()->where(['author_id' => 2])->one();
        $this->assertTrue($article instanceof ArticleIndex);
        $this->assertEquals(2, $article->id);

        // find by comparison
        $article = ArticleIndex::find()->where(['>', 'author_id', 1])->one();
        $this->assertTrue($article instanceof ArticleIndex);
        $this->assertEquals(2, $article->id);

        // find custom column
        $article = ArticleIndex::find()->select(['*', '(5*2) AS custom_column'])
            ->where(['author_id' => 1])->one();
        $this->assertEquals(1, $article->id);
        $this->assertEquals(10, $article->custom_column);

        // find count, sum, average, min, max, scalar
        $this->assertEquals(1002, ArticleIndex::find()->count());
        $this->assertEquals(1, ArticleIndex::find()->where('id=1')->count());
        // sum and average https://www.wolframalpha.com/input/?i=arithmetic+sequence+to+1002
        $this->assertEquals(502503, ArticleIndex::find()->sum('id'));
        $this->assertEquals(501.5, ArticleIndex::find()->average('id'));
        $this->assertEquals(1, ArticleIndex::find()->min('id'));
        $this->assertEquals(1002, ArticleIndex::find()->max('id'));
        $this->assertEquals(1002, ArticleIndex::find()->select('COUNT(*)')->scalar());

        // scope
        $this->assertEquals(101, ArticleIndex::find()->favoriteAuthor()->count());

        // asArray
        $article = ArticleIndex::find()->where('id=2')->asArray()->one();
        unset($article['add_date']);
        $this->assertEquals([
            'id' => '2',
            'author_id' => '2',
            'tag' => '3,4',
        ], $article);

        // indexBy
        $articles = ArticleIndex::find()->indexBy('author_id')->orderBy('id DESC')->all();
        $this->assertEquals(10, count($articles));
        $this->assertTrue($articles['1'] instanceof ArticleIndex);
        $this->assertTrue($articles['2'] instanceof ArticleIndex);

        // indexBy callable
        $articles = ArticleIndex::find()->indexBy(function ($article) {
            return $article->id . '-' . $article->author_id;
        })->orderBy('id DESC')->all();
        $this->assertEquals(20, count($articles));
        $this->assertTrue($articles['1001-1'] instanceof ArticleIndex);
        $this->assertTrue($articles['1002-2'] instanceof ArticleIndex);
    }

    public function testFindBySql()
    {
        // find one
        $article = ArticleIndex::findBySql('SELECT * FROM yii2_test_article_index ORDER BY id DESC')->one();
        $this->assertTrue($article instanceof ArticleIndex);
        $this->assertEquals(2, $article->author_id);

        // find all
        $articles = ArticleIndex::findBySql('SELECT * FROM yii2_test_article_index')->all();
        $this->assertEquals(20, count($articles));

        // find with parameter binding
        $article = ArticleIndex::findBySql('SELECT * FROM yii2_test_article_index WHERE id=:id', [':id' => 13])->one();
        $this->assertTrue($article instanceof ArticleIndex);
        $this->assertEquals(3, $article->author_id);
    }

    public function testInsert()
    {
        $record = new RtIndex();
        $record->id = 15;
        $record->title = 'test title';
        $record->content = 'test content';
        $record->type_id = 7;
        $record->category = [1, 2];

        $this->assertTrue($record->isNewRecord);

        $record->save();

        $this->assertEquals(15, $record->id);
        $this->assertFalse($record->isNewRecord);
    }

    /**
     * @depends testInsert
     */
    public function testUpdate()
    {
        $record = new RtIndex();
        $record->id = 2;
        $record->title = 'test title';
        $record->content = 'test content';
        $record->type_id = 7;
        $record->category = [1, 2];
        $record->save();

        // save
        $record = RtIndex::findOne(2);
        $this->assertTrue($record instanceof RtIndex);
        $this->assertEquals(7, $record->type_id);
        $this->assertFalse($record->isNewRecord);

        $record->type_id = 9;
        $record->save();
        $this->assertEquals(9, $record->type_id);
        $this->assertFalse($record->isNewRecord);
        $record2 = RtIndex::findOne(['id' => 2]);
        $this->assertEquals(9, $record2->type_id);

        // replace
        $query = 'replace';
        $rows = RtIndex::find()->match($query)->all();
        $this->assertEmpty($rows);
        $record = RtIndex::findOne(2);
        $record->content = 'Test content with ' . $query;
        $record->save();
        $rows = RtIndex::find()->match($query);
        $this->assertNotEmpty($rows);

        // updateAll
        $pk = ['id' => 2];
        $ret = RtIndex::updateAll(['type_id' => 55], $pk);
        $this->assertEquals(1, $ret);
        $record = RtIndex::findOne($pk);
        $this->assertEquals(55, $record->type_id);
    }

    /**
     * @depends testInsert
     */
    public function testDelete()
    {
        // delete
        $record = new RtIndex();
        $record->id = 2;
        $record->title = 'test title';
        $record->content = 'test content';
        $record->type_id = 7;
        $record->category = [1, 2];
        $record->save();

        $record = RtIndex::findOne(2);
        $record->delete();
        $record = RtIndex::findOne(2);
        $this->assertNull($record);

        // deleteAll
        $record = new RtIndex();
        $record->id = 2;
        $record->title = 'test title';
        $record->content = 'test content';
        $record->type_id = 7;
        $record->category = [1, 2];
        $record->save();

        $ret = RtIndex::deleteAll('id = 2');
        $this->assertEquals(1, $ret);
        $records = RtIndex::find()->all();
        $this->assertEquals(0, count($records));
    }

    /**
     * @depends testInsert
     *
     * @see https://github.com/yiisoft/yii2-sphinx/issues/75
     */
    public function testEmptyMva()
    {
        // delete
        $record = new RtIndex();
        $record->id = 3;
        $record->title = 'test empty MVA';
        $record->category = [];
        $record->save();

        $record = RtIndex::findOne(3);
        $this->assertEquals([], $record->category);
    }

    public function testCallSnippets()
    {
        $query = 'pencil';
        $source = 'Some data sentence about ' . $query;

        $snippet = ArticleIndex::callSnippets($source, $query);
        $this->assertNotEmpty($snippet, 'Unable to call snippets!');
        $this->assertContains('<b>' . $query . '</b>', $snippet, 'Query not present in the snippet!');

        $rows = ArticleIndex::callSnippets([$source], $query);
        $this->assertNotEmpty($rows, 'Unable to call snippets!');
        $this->assertContains('<b>' . $query . '</b>', $rows[0], 'Query not present in the snippet!');
    }

    public function testCallKeywords()
    {
        $text = 'table pencil';
        $rows = ArticleIndex::callKeywords($text);
        $this->assertNotEmpty($rows, 'Unable to call keywords!');
        $this->assertArrayHasKey('tokenized', $rows[0], 'No tokenized keyword!');
        $this->assertArrayHasKey('normalized', $rows[0], 'No normalized keyword!');
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/4830
     *
     * @depends testFind
     */
    public function testFindQueryReuse()
    {
        $result = ArticleIndex::find()->andWhere(['author_id' => 1]);
        $this->assertTrue($result->one() instanceof ArticleIndex);
        $this->assertTrue($result->one() instanceof ArticleIndex);

        $result = ArticleIndex::find()->match('dogs');
        $this->assertTrue($result->one() instanceof ArticleIndex);
        $this->assertTrue($result->one() instanceof ArticleIndex);
    }

    /**
     * @see https://github.com/yiisoft/yii2-sphinx/issues/30
     *
     * @depends testFind
     */
    public function testFindByStringPk()
    {
        $model = ArticleIndex::findOne('1');
        $this->assertTrue($model instanceof ArticleIndex);
    }

    public function testEmulateExecution()
    {
        if (!ArticleIndex::find()->hasMethod('emulateExecution')) {
            $this->markTestSkipped('"yii2" version 2.0.11 or higher required');
        }

        $this->assertGreaterThan(0, ArticleIndex::find()->count());

        $rows = ArticleIndex::find()
            ->emulateExecution()
            ->all();
        $this->assertSame([], $rows);

        $row = ArticleIndex::find()
            ->emulateExecution()
            ->one();
        $this->assertSame(null, $row);

        $exists = ArticleIndex::find()
            ->emulateExecution()
            ->exists();
        $this->assertSame(false, $exists);

        $count = ArticleIndex::find()
            ->emulateExecution()
            ->count();
        $this->assertSame(0, $count);

        $sum = ArticleIndex::find()
            ->emulateExecution()
            ->sum('id');
        $this->assertSame(0, $sum);

        $sum = ArticleIndex::find()
            ->from('customer')
            ->emulateExecution()
            ->average('id');
        $this->assertSame(0, $sum);

        $max = ArticleIndex::find()
            ->emulateExecution()
            ->max('id');
        $this->assertSame(null, $max);

        $min = ArticleIndex::find()
            ->emulateExecution()
            ->min('id');
        $this->assertSame(null, $min);

        $scalar = ArticleIndex::find()
            ->select(['id'])
            ->emulateExecution()
            ->scalar();
        $this->assertSame(null, $scalar);

        $column = ArticleIndex::find()
            ->select(['id'])
            ->emulateExecution()
            ->column();
        $this->assertSame([], $column);

        $rows = ArticleIndex::find()
            ->emulateExecution()
            ->search();
        $this->assertSame(['hits' => [], 'facets' => [], 'meta' => []], $rows);
    }
}
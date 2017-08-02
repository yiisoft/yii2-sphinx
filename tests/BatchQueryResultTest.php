<?php

namespace yiiunit\extensions\sphinx;

use yii\db\BatchQueryResult;
use yii\sphinx\Query;
use yiiunit\extensions\sphinx\data\ar\ActiveRecord;
use yiiunit\extensions\sphinx\data\ar\ActiveRecordDb;
use yiiunit\extensions\sphinx\data\ar\ArticleIndex;

class BatchQueryResultTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
        ActiveRecordDb::$db = $this->getDbConnection();
    }

    public function testQuery()
    {
        $db = $this->getConnection();

        // initialize property test
        $query = (new Query())
            ->from('yii2_test_article_index')
            ->orderBy('id');
        $result = $query->batch(2, $db);
        $this->assertInstanceOf(BatchQueryResult::className(), $result);
        $this->assertEquals(2, $result->batchSize);
        $this->assertSame($result->query, $query);

        // normal query
        $query = (new Query())
            ->from('yii2_test_article_index')
            ->orderBy('id');
        $allRows = [];
        $batch = $query->batch(2, $db);
        foreach ($batch as $rows) {
            $allRows = array_merge($allRows, $rows);
        }
        $this->assertCount(20, $allRows);
        $this->assertEquals(1, $allRows[0]['author_id']);
        $this->assertEquals(2, $allRows[1]['author_id']);
        $this->assertEquals(3, $allRows[2]['author_id']);
        $this->assertEquals(4, $allRows[3]['author_id']);

        // rewind
        $allRows = [];
        foreach ($batch as $rows) {
            $allRows = array_merge($allRows, $rows);
        }
        $this->assertCount(20, $allRows);
        // reset
        $batch->reset();

        // empty query
        $query = (new Query())
            ->from('yii2_test_article_index')
            ->where(['id' => 0]);
        $allRows = [];
        $batch = $query->batch(2, $db);
        foreach ($batch as $rows) {
            $allRows = array_merge($allRows, $rows);
        }
        $this->assertCount(0, $allRows);

        // query with index
        $query = (new Query())
            ->from('yii2_test_article_index')
            ->limit(4)
            ->indexBy('author_id');
        $allRows = [];
        foreach ($query->batch(2, $db) as $rows) {
            foreach ($rows as $key => $value) {
                $allRows[$key] = $value;
            }
        }
        $this->assertCount(4, $allRows);
        $this->assertEquals(1, $allRows[1]['author_id']);
        $this->assertEquals(2, $allRows[2]['author_id']);
        $this->assertEquals(3, $allRows[3]['author_id']);
        $this->assertEquals(4, $allRows[4]['author_id']);

        // each
        $query = (new Query())
            ->from('yii2_test_article_index')
            ->orderBy('id');
        $allRows = [];
        foreach ($query->each(2, $db) as $rows) {
            $allRows[] = $rows;
        }
        $this->assertCount(20, $allRows);
        $this->assertEquals(1, $allRows[0]['author_id']);
        $this->assertEquals(2, $allRows[1]['author_id']);
        $this->assertEquals(3, $allRows[2]['author_id']);
        $this->assertEquals(4, $allRows[3]['author_id']);

        // each with key
        $query = (new Query())
            ->from('yii2_test_article_index')
            ->orderBy('id')
            ->limit(4)
            ->indexBy('author_id');
        $allRows = [];
        foreach ($query->each(2, $db) as $key => $row) {
            $allRows[$key] = $row;
        }
        $this->assertCount(4, $allRows);
        $this->assertEquals(1, $allRows[1]['author_id']);
        $this->assertEquals(2, $allRows[2]['author_id']);
    }

    public function testActiveQuery()
    {
        $db = $this->getConnection();

        $query = ArticleIndex::find()
            ->orderBy('id');
        $allModels = [];
        foreach ($query->batch(2, $db) as $models) {
            $allModels = array_merge($allModels, $models);
        }
        $this->assertCount(20, $allModels);
        $this->assertEquals(1, $allModels[0]->author_id);
        $this->assertEquals(2, $allModels[1]->author_id);
        $this->assertEquals(3, $allModels[2]->author_id);
        $this->assertEquals(4, $allModels[3]->author_id);

        // batch with eager loading
        $query = ArticleIndex::find()
            ->with('tags')
            ->orderBy('id');
        $allModels = [];
        foreach ($query->batch(2, $db) as $models) {
            $allModels = array_merge($allModels, $models);
            foreach ($models as $model) {
                /* @var $model ArticleIndex */
                $this->assertTrue($model->isRelationPopulated('tags'));
            }
        }
        $this->assertCount(20, $allModels);
        $this->assertCount(3, $allModels[0]->tags);
        $this->assertCount(2, $allModels[1]->tags);
        $this->assertCount(0, $allModels[2]->tags);
    }
}
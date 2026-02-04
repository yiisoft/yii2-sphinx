<?php

namespace yiiunit\extensions\sphinx;

use yiiunit\extensions\sphinx\data\ar\ActiveRecord;
use yiiunit\extensions\sphinx\data\ar\ActiveRecordDb;
use yiiunit\extensions\sphinx\data\ar\ArticleIndex;
use yiiunit\extensions\sphinx\data\ar\ArticleDb;

/**
 * @group sphinx
 */
class ActiveRelationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
        ActiveRecordDb::$db = $this->getDbConnection();
    }

    // Tests :

    public function testFindLazy(): void
    {
        /* @var $article ArticleDb */
        $article = ArticleDb::findOne(['id' => 2]);
        $this->assertFalse($article->isRelationPopulated('index'));
        $index = $article->index;
        $this->assertTrue($article->isRelationPopulated('index'));
        $this->assertTrue($index instanceof ArticleIndex);
        $this->assertEquals(1, count($article->relatedRecords));
        $this->assertEquals($article->id, $index->id);
    }

    public function testFindEager(): void
    {
        $articles = ArticleDb::find()->with('index')->all();
        $this->assertEquals(1002, count($articles));
        $this->assertTrue($articles[0]->isRelationPopulated('index'));
        $this->assertTrue($articles[1]->isRelationPopulated('index'));
        $this->assertTrue($articles[0]->index instanceof ArticleIndex);
        $this->assertTrue($articles[1]->index instanceof ArticleIndex);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/4018
     */
    public function testFindCompositeLink(): void
    {
        $articles = ArticleIndex::find()->with('sourceCompositeLink')->all();
        $this->assertEquals(20, count($articles));
        $this->assertTrue($articles[0]->isRelationPopulated('sourceCompositeLink'));
        $this->assertNotEmpty($articles[0]->sourceCompositeLink);
        $this->assertTrue($articles[1]->isRelationPopulated('sourceCompositeLink'));
        $this->assertNotEmpty($articles[1]->sourceCompositeLink);
    }
}

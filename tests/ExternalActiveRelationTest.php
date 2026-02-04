<?php

namespace yiiunit\extensions\sphinx;

use yiiunit\extensions\sphinx\data\ar\ActiveRecord;
use yiiunit\extensions\sphinx\data\ar\ActiveRecordDb;
use yiiunit\extensions\sphinx\data\ar\ArticleIndex;
use yiiunit\extensions\sphinx\data\ar\ArticleDb;
use yiiunit\extensions\sphinx\data\ar\TagDb;

/**
 * @group sphinx
 */
class ExternalActiveRelationTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
        ActiveRecordDb::$db = $this->getDbConnection();
    }

    // Tests :

    public function testFindLazy(): void
    {
        /* @var $article ArticleIndex */
        $article = ArticleIndex::findOne(['id' => 2]);

        // has one :
        $this->assertFalse($article->isRelationPopulated('source'));
        $source = $article->source;
        $this->assertTrue($article->isRelationPopulated('source'));
        $this->assertTrue($source instanceof ArticleDb);
        $this->assertEquals(1, count($article->relatedRecords));

        // has many :
        $this->assertFalse($article->isRelationPopulated('tags'));
        $tags = $article->tags;
        $this->assertTrue($article->isRelationPopulated('tags'));
        $this->assertEquals(count($article->tag), count($tags));
        $this->assertTrue($tags[0] instanceof TagDb);
        foreach ($tags as $tag) {
            $this->assertTrue(in_array($tag->id, $article->tag));
        }
    }

    public function testFindEager(): void
    {
        // has one :
        $articles = ArticleIndex::find()->with('source')->all();
        $this->assertEquals(20, count($articles));
        $this->assertTrue($articles[0]->isRelationPopulated('source'));
        $this->assertTrue($articles[1]->isRelationPopulated('source'));
        $this->assertTrue($articles[0]->source instanceof ArticleDb);
        $this->assertTrue($articles[1]->source instanceof ArticleDb);

        // has many :
        $articles = ArticleIndex::find()->with('tags')->limit(2)->all();
        $this->assertEquals(2, count($articles));
        $this->assertTrue($articles[0]->isRelationPopulated('tags'));
        $this->assertTrue($articles[1]->isRelationPopulated('tags'));
        foreach ($articles as $article) {
            $this->assertTrue($article->isRelationPopulated('tags'));
            $tags = $article->tags;
            $this->assertEquals(count($article->tag), count($tags));
            //var_dump($tags);
            $this->assertTrue($tags[0] instanceof TagDb);
            foreach ($tags as $tag) {
                $this->assertTrue(in_array($tag->id, $article->tag));
            }
        }
    }

    /**
     * @depends testFindEager
     */
    public function testFindWithSnippets(): void
    {
        $articles = ArticleIndex::find()
            ->match('about')
            ->with('source')
            ->snippetByModel()
            ->all();
        $this->assertEquals(2, count($articles));
    }
}

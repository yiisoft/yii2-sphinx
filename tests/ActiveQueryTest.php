<?php

namespace yiiunit\extensions\sphinx;

use yiiunit\extensions\sphinx\data\ar\ArticleIndex;
use yiiunit\extensions\sphinx\data\ar\ActiveRecord;

/**
 * @group sphinx
 */
class ActiveQueryTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
    }

    // Tests :

    public function testFacet()
    {
        $results = ArticleIndex::find()
            ->match('about')
            ->facets([
                'author_id'
            ])
            ->search();
        $this->assertNotEmpty($results['hits'], 'Unable to query with facet');
        $this->assertNotEmpty($results['facets']['author_id'], 'Unable to fill up facet');
        $this->assertTrue($results['hits'][0] instanceof ArticleIndex, 'Unable to populate results as AR object');
    }

    public function testIndexBy() 
    {
        $results = ArticleIndex::find()->indexBy(['id'])->all();
        $result = reset($results);
        $this->assertTrue($result->id == key($results), 'Unable to index results by column');
    }
}

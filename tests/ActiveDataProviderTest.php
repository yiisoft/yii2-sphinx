<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\ActiveDataProvider;
use yii\sphinx\Query;
use Yii;
use yii\web\Request;
use yiiunit\extensions\sphinx\data\ar\ActiveRecord;
use yiiunit\extensions\sphinx\data\ar\ArticleIndex;

/**
 * @group sphinx
 */
class ActiveDataProviderTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
    }

    // Tests :

    public function testQuery()
    {
        $query = new Query();
        $query->from('yii2_test_article_index');

        $provider = new ActiveDataProvider([
            'query' => $query,
            'db' => $this->getConnection(),
        ]);
        $models = $provider->getModels();
        $this->assertEquals(2, count($models));

        $provider = new ActiveDataProvider([
            'query' => $query,
            'db' => $this->getConnection(),
            'pagination' => [
                'pageSize' => 1,
            ]
        ]);
        $models = $provider->getModels();
        $this->assertEquals(1, count($models));
    }

    public function testActiveQuery()
    {
        $provider = new ActiveDataProvider([
            'query' => ArticleIndex::find()->orderBy('id ASC'),
        ]);
        $models = $provider->getModels();
        $this->assertEquals(2, count($models));
        $this->assertTrue($models[0] instanceof ArticleIndex);
        $this->assertTrue($models[1] instanceof ArticleIndex);
        $this->assertEquals([1, 2], $provider->getKeys());

        $provider = new ActiveDataProvider([
            'query' => ArticleIndex::find(),
            'pagination' => [
                'pageSize' => 1,
            ]
        ]);
        $models = $provider->getModels();
        $this->assertEquals(1, count($models));
    }

    /**
     * @depends testQuery
     */
    public function testFacetQuery()
    {
        $query = new Query();
        $query->from('yii2_test_article_index');
        $query->facets([
            'author_id'
        ]);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'db' => $this->getConnection(),
        ]);
        $models = $provider->getModels();
        $this->assertEquals(2, count($models));
        $this->assertEquals(2, count($provider->getFacet('author_id')));
    }

    /**
     * @depends testQuery
     */
    public function testTotalCountFromMeta()
    {
        $query = new Query();
        $query->from('yii2_test_article_index');
        $query->showMeta(true);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'db' => $this->getConnection(),
            'pagination' => [
                'pageSize' => 1,
            ]
        ]);
        $models = $provider->getModels();
        $this->assertEquals(1, count($models));
        $this->assertEquals(2, $provider->getTotalCount());
    }

    /**
     * @depends testTotalCountFromMeta
     *
     * @see https://github.com/yiisoft/yii2-sphinx/issues/11
     */
    public function testAutoAdjustPagination()
    {
        $request = new Request();
        $request->setQueryParams(['page' => 2]);
        Yii::$app->set('request', $request);

        $query = new Query();
        $query->from('yii2_test_article_index');
        $query->orderBy(['id' => SORT_ASC]);
        $query->showMeta(true);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'db' => $this->getConnection(),
            'pagination' => [
                'pageSize' => 1,
            ]
        ]);
        $models = $provider->getModels();
        $this->assertEquals(2, $models[0]['id']);
    }

    /**
     * @depends testAutoAdjustPagination
     *
     * @see https://github.com/yiisoft/yii2-sphinx/issues/12
     */
    public function testAutoAdjustMaxMatches()
    {
        $request = new Request();
        $request->setQueryParams(['page' => 99999]);
        Yii::$app->set('request', $request);

        $query = new Query();
        $query->from('yii2_test_article_index');
        $query->orderBy(['id' => SORT_ASC]);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'db' => $this->getConnection(),
            'pagination' => [
                'pageSize' => 100,
                'validatePage' => false,
            ]
        ]);
        $models = $provider->getModels();
        $this->assertEmpty($models); // no exception
    }
}

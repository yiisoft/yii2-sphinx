<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\ActiveFixture;
use yii\test\FixtureTrait;
use yiiunit\extensions\sphinx\data\ar\ActiveRecord;
use yiiunit\extensions\sphinx\data\ar\RuntimeIndex;

class ActiveFixtureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        \Yii::$app->set('sphinx', $this->getConnection());
        ActiveRecord::$db = $this->getConnection();
    }

    public function testGetData()
    {
        $test = new MySphinxTestCase();
        $test->setUp();
        $fixture = $test->getFixture('runtimeIndex');
        $this->assertEquals(RuntimeIndexFixture::className(), get_class($fixture));
        $this->assertEquals(2, count($fixture));
        $this->assertEquals(1, $fixture['row1']['id']);
        $this->assertEquals('title1', $fixture['row1']['title']);
        $this->assertEquals(2, $fixture['row2']['id']);
        $this->assertEquals('title2', $fixture['row2']['title']);
        $test->tearDown();
    }

    public function testGetModel()
    {
        $test = new MySphinxTestCase();
        $test->setUp();
        $fixture = $test->getFixture('runtimeIndex');
        $this->assertEquals(RuntimeIndex::className(), get_class($fixture->getModel('row1')));
        $this->assertEquals(1, $fixture->getModel('row1')->id);
        $this->assertEquals(1, $fixture->getModel('row1')->type_id);
        $this->assertEquals(2, $fixture->getModel('row2')->id);
        $this->assertEquals(2, $fixture->getModel('row2')->type_id);
        $test->tearDown();
    }
}

class RuntimeIndexFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\extensions\sphinx\data\ar\RuntimeIndex';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->dataFile = __DIR__ . '/data/fixtures/' . RuntimeIndex::indexName() . '.php';
        parent::init();
    }
}

class MySphinxTestCase
{
    use FixtureTrait;

    public function setUp()
    {
        $this->unloadFixtures();
        $this->loadFixtures();
    }

    public function tearDown()
    {
    }

    public function fixtures()
    {
        return [
            'runtimeIndex' => RuntimeIndexFixture::className(),
        ];
    }
}
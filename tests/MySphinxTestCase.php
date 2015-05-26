<?php

namespace yiiunit\extensions\sphinx;

use yii\test\FixtureTrait;
use yiiunit\extensions\sphinx\RuntimeIndexFixture;

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

<?php

namespace yiiunit\extensions\sphinx\data\fixture;

use yii\test\FixtureTrait;
use yiiunit\extensions\sphinx\data\fixture\RtIndexFixture;

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
            'runtimeIndex' => RtIndexFixture::className(),
        ];
    }
}

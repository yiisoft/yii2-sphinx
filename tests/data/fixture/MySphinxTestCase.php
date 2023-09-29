<?php

namespace yiiunit\extensions\sphinx\data\fixture;

use yii\test\FixtureTrait;
use yiiunit\extensions\sphinx\data\fixture\RtIndexFixture;

class MySphinxTestCase extends \yiiunit\extensions\sphinx\TestCase
{
    use FixtureTrait;

    protected function setUp(): void
    {
        $this->unloadFixtures();
        $this->loadFixtures();
    }

    protected function tearDown(): void
    {
    }

    public function fixtures()
    {
        return [
            'runtimeIndex' => RtIndexFixture::className(),
        ];
    }
}

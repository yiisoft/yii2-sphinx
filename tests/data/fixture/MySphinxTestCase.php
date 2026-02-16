<?php

namespace yiiunit\extensions\sphinx\data\fixture;

use yii\test\FixtureTrait;
use yiiunit\extensions\sphinx\TestCase;

class MySphinxTestCase extends TestCase
{
    use FixtureTrait;

    public function setUp(): void
    {
        $this->unloadFixtures();
        $this->loadFixtures();
    }

    public function tearDown(): void
    {
    }

    public function fixtures(): array
    {
        return [
            'runtimeIndex' => RtIndexFixture::class,
        ];
    }
}

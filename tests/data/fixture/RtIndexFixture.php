<?php

namespace yiiunit\extensions\sphinx\data\fixture;

use yii\sphinx\ActiveFixture;
use yiiunit\extensions\sphinx\data\ar\RtIndex;

class RtIndexFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\extensions\sphinx\data\ar\RtIndex';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->dataFile = __DIR__ . '/../fixtures/' . RtIndex::indexName() . '.php';
        parent::init();
    }
}

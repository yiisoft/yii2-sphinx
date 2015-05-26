<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\ActiveFixture;
use yiiunit\extensions\sphinx\data\ar\RuntimeIndex;

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

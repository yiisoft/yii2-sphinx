<?php

namespace yiiunit\extensions\sphinx\data\ar;

class RuntimeIndex extends ActiveRecord
{
    public static function indexName()
    {
        return 'yii2_test_rt_index';
    }
}

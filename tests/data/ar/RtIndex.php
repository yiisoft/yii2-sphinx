<?php

namespace yiiunit\extensions\sphinx\data\ar;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $type_id
 * @property array $category
 */
class RtIndex extends ActiveRecord
{
    public static function indexName()
    {
        return 'yii2_test_rt_index';
    }
}

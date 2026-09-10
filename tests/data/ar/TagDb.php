<?php

namespace yiiunit\extensions\sphinx\data\ar;

/**
 * @property int $id
 * @property string $name
 */
class TagDb extends ActiveRecordDb
{
    public static function tableName()
    {
        return 'yii2_test_tag';
    }
}

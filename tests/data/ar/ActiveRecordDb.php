<?php

namespace yiiunit\extensions\sphinx\data\ar;

/**
 * Test regular DB ActiveRecord class
 */
class ActiveRecordDb extends \yii\db\ActiveRecord
{
    public static $db;

    public static function getDb()
    {
        return self::$db;
    }
} 
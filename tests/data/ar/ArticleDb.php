<?php

namespace yiiunit\extensions\sphinx\data\ar;

use yii\sphinx\ActiveQuery;

class ArticleDb extends ActiveRecordDb
{
    public static function tableName()
    {
        return 'yii2_test_article';
    }

    public function getIndex()
    {
        return new ActiveQuery(ArticleIndex::class, [
            'primaryModel' => $this,
            'link' => ['id' => 'id'],
            'multiple' => false,
        ]);
    }
}

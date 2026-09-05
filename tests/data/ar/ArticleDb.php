<?php

namespace yiiunit\extensions\sphinx\data\ar;

use yii\sphinx\ActiveQuery;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $author_id
 * @property string $create_date
 * @property-read mixed $index
 */
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

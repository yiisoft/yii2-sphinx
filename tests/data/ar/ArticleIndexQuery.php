<?php

namespace yiiunit\extensions\sphinx\data\ar;

use yii\sphinx\ActiveQuery;

/**
 * ArticleIndexQuery
 */
class ArticleIndexQuery extends ActiveQuery
{
    public function favoriteAuthor()
    {
        $this->andWhere('author_id=1');

        return $this;
    }
}

<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\MatchExpression;

class MatchExpressionTest extends TestCase
{
    public function testWhere()
    {
        $match = new MatchExpression();
        $match->match('@(content) :content', [':content' => 'foo']);
        $this->assertEquals('@(content) :content', $match->match);
        $this->assertEquals([':content' => 'foo'], $match->params);

        $match->andMatch('@(name) :name', [':name' => 'something']);
        $this->assertEquals(['and', '@(content) :content', '@(name) :name'], $match->match);
        $this->assertEquals([':content' => 'foo', ':name' => 'something'], $match->params);

        $match->orMatch('@(age) :age', [':age' => '30']);
        $this->assertEquals(['or', ['and', '@(content) :content', '@(name) :name'], '@(age) :age'], $match->match);
        $this->assertEquals([':content' => 'foo', ':name' => 'something', ':age' => '30'], $match->params);
    }
}
<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\MatchExpression;

class MatchExpressionTest extends TestCase
{
    public function testConstructor()
    {
        $match = new MatchExpression('@name :name', ['name' => 'value']);

        $this->assertEquals('@name :name', $match->match);
        $this->assertEquals(['name' => 'value'], $match->params);
    }

    public function testMatch()
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

    public function testParams()
    {
        $match = new MatchExpression();

        $match->params(['name1' => 'value1']);
        $this->assertEquals(['name1' => 'value1'], $match->params);

        $match->addParams(['name2' => 'value2']);
        $this->assertEquals(['name1' => 'value1', 'name2' => 'value2'], $match->params);

        $match->addParams(['name2' => 'override']);
        $this->assertEquals(['name1' => 'value1', 'name2' => 'override'], $match->params);

        $match->params(['new' => 'value']);
        $this->assertEquals(['new' => 'value'], $match->params);
    }
}
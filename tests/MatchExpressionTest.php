<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\MatchExpression;

class MatchExpressionTest extends TestCase
{
    public function testConstructor(): void
    {
        $match = new MatchExpression('@name :name', ['name' => 'value']);

        $this->assertEquals('@name :name', $match->match);
        $this->assertEquals(['name' => 'value'], $match->params);
    }

    public function testMatch(): void
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

    public function testParams(): void
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

    /**
     * @depends testMatch
     */
    public function testFilterMatch(): void
    {
        // should work with hash format
        $match = new MatchExpression();
        $match->filterMatch([
            'id' => 0,
            'title' => '   ',
            'author_ids' => [],
        ]);
        $this->assertEquals(['id' => 0], $match->match);

        $match->andFilterMatch(['status' => null]);
        $this->assertEquals(['id' => 0], $match->match);

        $match->orFilterMatch(['name' => '']);
        $this->assertEquals(['id' => 0], $match->match);

        // should work with operator format
        $match = new MatchExpression();
        $condition = ['and', ['name' => 'Alex']];
        $match->filterMatch($condition);
        $this->assertEquals($condition, $match->match);

        $match->andFilterMatch(['and', ['name' => '']]);
        $this->assertEquals($condition, $match->match);

        $match->orFilterMatch(['and', ['name' => '']]);
        $this->assertEquals($condition, $match->match);

        // @see MatchBuilder::buildMultipleMatch()
        $match = new MatchExpression();
        $match->andFilterMatch(['sentence', 'name', null, '']);
        $this->assertEmpty($match->match);
        $condition = ['sentence', 'name', 'v1', 'v2'];
        $match->andFilterMatch($condition);
        $this->assertEquals($condition, $match->match);

        // @see MatchBuilder::buildZoneMatch()
        $match = new MatchExpression();
        $match->andFilterMatch(['zone', null, '']);
        $this->assertEmpty($match->match);
        $condition = ['zone', 'h1', 'h2'];
        $match->andFilterMatch($condition);
        $this->assertEquals($condition, $match->match);

        // @see MatchBuilder::buildIgnoreMatch()
        $match = new MatchExpression();
        $match->andFilterMatch(['ignore', 'name', '']);
        $this->assertEmpty($match->match);
        $condition = ['ignore', 'name', 'fake'];
        $match->andFilterMatch($condition);
        $this->assertEquals($condition, $match->match);

        // @see MatchBuilder::buildProximityMatch()
        $match = new MatchExpression();
        $match->andFilterMatch(['proximity', 'name', '', 4]);
        $this->assertEmpty($match->match);
        $condition = ['proximity', 'name', 'fake', 4];
        $match->andFilterMatch($condition);
        $this->assertEquals($condition, $match->match);
    }
}

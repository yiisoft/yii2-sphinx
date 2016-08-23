<?php

namespace yiiunit\extensions\sphinx;

use yii\db\Expression;
use yii\sphinx\MatchBuilder;
use yii\sphinx\MatchExpression;

class MatchBuilderTest extends TestCase
{
    /**
     * @return MatchBuilder match builder instance.
     */
    protected function createMatchBuilder()
    {
        return new MatchBuilder($this->getConnection());
    }

    // Tests :

    public function testPlainString()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->match('@name :name')
            ->params(['name' => 'foo']);

        $this->assertEquals('@name "foo"', $builder->build($match));
    }

    public function testHash()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->match(['name' => 'apple']);

        $this->assertEquals('@name "apple"', $builder->build($match));
    }

    /**
     * @depends testHash
     */
    public function testAndMatch()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->andMatch(['tags' => ['fruits', 'berries']])
            ->andMatch(['name,title' => new Expression(':kind', [':kind' => 'red'])]);

        $this->assertEquals('(@tags "fruits" | "berries") (@(name,title) "red")', $builder->build($match));
    }

    /**
     * @depends testHash
     */
    public function testOrMatch()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->match(['name' => 'foo'])
            ->orMatch(['tag' => 'always']);

        $this->assertEquals('(@name "foo") | (@tag "always")', $builder->build($match));
    }

    /**
     * @depends testAndMatch
     */
    public function testEscapeParams()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->andMatch([
                'name' => 'special@char',
                'tag' => 'special"char',
            ]);

        $this->assertEquals('(@name "special\@char") (@tag "special\\"char")', $builder->build($match));
    }

    /**
     * @depends testAndMatch
     */
    public function testNotMatch()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->andMatch([
                'not',
                'name',
                'apple',
            ]);
        $this->assertEquals('@name !"apple"', $builder->build($match));
    }

    /**
     * @depends testAndMatch
     */
    public function testIgnoreMatch()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->andMatch([
                'ignore',
                'name',
                'apple',
            ]);
        $this->assertEquals('@!name "apple"', $builder->build($match));
    }

    /**
     * @depends testAndMatch
     */
    public function testProximityMatch()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->andMatch([
                'proximity',
                'name',
                'apple',
                5
            ]);
        $this->assertEquals('@name "apple"~5', $builder->build($match));
    }

    /**
     * @depends testAndMatch
     */
    public function testMaybeMatch()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->andMatch([
                'maybe',
                'name',
                'apple',
                'banana',
            ]);
        $this->assertEquals('@name "apple" MAYBE "banana"', $builder->build($match));
    }

    /**
     * @depends testAndMatch
     */
    public function testSentenceMatch()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->andMatch([
                'sentence',
                'content',
                'all',
                'words',
                'in one sentence',
            ]);
        $this->assertEquals('@content "all" SENTENCE "words" SENTENCE "in one sentence"', $builder->build($match));
    }

    /**
     * @depends testAndMatch
     */
    public function testParagraphMatch()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->andMatch([
                'paragraph',
                'title',
                'Bill Gates',
                'Steve Jobs',
            ]);
        $this->assertEquals('@title "Bill Gates" PARAGRAPH "Steve Jobs"', $builder->build($match));
    }

    /**
     * @depends testAndMatch
     */
    public function testZoneMatch()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->andMatch([
                'zone',
                'name',
            ]);
        $this->assertEquals('ZONE: (name)', $builder->build($match));

        $match = (new MatchExpression())
            ->andMatch([
                'zone',
                ['h3', 'h4'],
            ]);
        $this->assertEquals('ZONE: (h3,h4)', $builder->build($match));
    }

    /**
     * @depends testAndMatch
     */
    public function testZoneSpanMatch()
    {
        $builder = $this->createMatchBuilder();

        $match = (new MatchExpression())
            ->andMatch([
                'zonespan',
                'name',
            ]);
        $this->assertEquals('ZONESPAN: (name)', $builder->build($match));
    }
}
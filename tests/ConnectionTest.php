<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\Connection;

/**
 * @group sphinx
 */
class ConnectionTest extends TestCase
{
    public function testConstruct()
    {
        $connection = $this->getConnection(false);
        $params = $this->sphinxConfig;

        $this->assertEquals($params['dsn'], $connection->dsn);
        $this->assertEquals($params['username'], $connection->username);
        $this->assertEquals($params['password'], $connection->password);
    }

    public function testOpenClose()
    {
        $connection = $this->getConnection(false, false);

        $this->assertFalse($connection->isActive);
        $this->assertEquals(null, $connection->pdo);

        $connection->open();
        $this->assertTrue($connection->isActive);
        $this->assertTrue($connection->pdo instanceof \PDO);

        $connection->close();
        $this->assertFalse($connection->isActive);
        $this->assertEquals(null, $connection->pdo);

        $connection = new Connection;
        $connection->dsn = 'unknown::memory:';
        $this->setExpectedException('yii\db\Exception');
        $connection->open();
    }

    public function testEscapeMatchValue()
    {
        $connection = new Connection();
        $this->assertEquals("him\\\\me", $connection->escapeMatchValue("him\\me"));
        $this->assertEquals("him\\/me", $connection->escapeMatchValue("him/me"));
        $this->assertEquals('this is \"good\"', $connection->escapeMatchValue('this is "good"'));
        $this->assertEquals('number 5 \(five\)', $connection->escapeMatchValue('number 5 (five)'));
        $this->assertEquals('good\|bad', $connection->escapeMatchValue('good|bad'));
        $this->assertEquals('a \- b', $connection->escapeMatchValue('a - b'));
        $this->assertEquals('great\!', $connection->escapeMatchValue('great!'));
        $this->assertEquals('me\@example.com', $connection->escapeMatchValue('me@example.com'));
        $this->assertEquals('example.com\/\~me', $connection->escapeMatchValue('example.com/~me'));
        $this->assertEquals('him \& me', $connection->escapeMatchValue('him & me'));
        $this->assertEquals('8\^2', $connection->escapeMatchValue('8^2'));
        $this->assertEquals('\$ bill', $connection->escapeMatchValue('$ bill'));
        $this->assertEquals('a \= a', $connection->escapeMatchValue('a = a'));
        $this->assertEquals('\<html\>', $connection->escapeMatchValue('<html>'));
        $this->assertEquals('\\x00', $connection->escapeMatchValue("\x00"));
        $this->assertEquals('good\nday', $connection->escapeMatchValue("good\nday"));
        $this->assertEquals('good\rday', $connection->escapeMatchValue("good\rday"));
        $this->assertEquals('\\x1a', $connection->escapeMatchValue("\x1a"));
        $this->assertEquals('http:\\/\\/example.com\\/me\\/', $connection->escapeMatchValue('http://example.com/me/'));
        $this->assertEquals("cote d\\'azure", $connection->escapeMatchValue("cote d'azure"));
        $this->assertEquals('', $connection->escapeMatchValue(''));
    }
}

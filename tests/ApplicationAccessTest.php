<?php

namespace yiiunit\extensions\sphinx;

use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\ArrayCache;
use yii\console\Application;
use yii\sphinx\ActiveRecord;
use yii\sphinx\Connection;
use yii\sphinx\gii\model\Generator;
use yii\sphinx\Query;
use yii\sphinx\Schema;

class ApplicationAccessTest extends TestCase
{
    protected function tearDown(): void
    {
        \Yii::$app = null;

        parent::tearDown();
    }

    public function testActiveRecordRequiresApplication(): void
    {
        $this->expectException(InvalidConfigException::class);

        ActiveRecord::getDb();
    }

    public function testQueryRequiresApplication(): void
    {
        $this->expectException(InvalidConfigException::class);

        (new Query())->getConnection();
    }

    public function testSchemaCacheLookupRequiresApplication(): void
    {
        $this->expectException(InvalidConfigException::class);

        $schema = new Schema(['db' => $this->createConnection()]);
        $schema->getIndexSchema('yii2_test_article_index');
    }

    public function testSchemaRefreshUsesConfiguredApplication(): void
    {
        $app = $this->mockApplication();
        $app->set('cache', new ArrayCache());

        $schema = new Schema(['db' => $this->createConnection()]);
        $schema->refresh();

        $this->addToAssertionCount(1);
    }

    public function testGeneratorValidationRequiresApplication(): void
    {
        $this->expectException(InvalidConfigException::class);

        (new Generator())->validateDb();
    }

    public function testGeneratorReturnsConfiguredConnection(): void
    {
        $connection = $this->createConnection(false);
        $app = $this->mockApplication();
        $app->set('sphinx', $connection);

        $method = new \ReflectionMethod(Generator::class, 'getDbConnection');
        $method->setAccessible(true);

        $this->assertSame($connection, $method->invoke(new Generator()));
    }

    private function mockApplication(): Application
    {
        return new Application([
            'id' => 'testapp',
            'basePath' => __DIR__,
        ]);
    }

    private function createConnection(bool $enableSchemaCache = true): Connection
    {
        return new Connection([
            'dsn' => 'mysql:host=127.0.0.1;port=9306;',
            'enableSchemaCache' => $enableSchemaCache,
            'schemaCache' => 'cache',
        ]);
    }
}

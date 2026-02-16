<?php

namespace yiiunit\extensions\sphinx;

use yii\db\Expression;
use yii\sphinx\Query;
use yii\sphinx\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    /**
     * @return QueryBuilder query builder instance.
     */
    protected function createQueryBuilder()
    {
        return new QueryBuilder($this->getConnection());
    }

    /**
     * @return array test data.
     */
    public function dataProviderBuildCondition(): array
    {
        $conditions = [
            // empty values
            [['like', 'name', []], '0=1', []],
            [['not like', 'name', []], '', []],
            [['or like', 'name', []], '0=1', []],
            [['or not like', 'name', []], '', []],

            // not
            [['not', 'name'], 'NOT (name)', []],

            // and
            [['and', 'id=1', 'id=2'], '(id=1) AND (id=2)', []],
            [['and', 'type=1', ['or', 'id=1', 'id=2']], '(type=1) AND ((id=1) OR (id=2))', []],
            [['and', 'id=1', new Expression('id=:qp0', [':qp0' => 2])], '(id=1) AND (id=:qp0)', [':qp0' => 2]],

            // or
            [['or', 'id=1', 'id=2'], '(id=1) OR (id=2)', []],
            [['or', 'type=1', ['or', 'id=1', 'id=2']], '(type=1) OR ((id=1) OR (id=2))', []],
            [['or', 'type=1', new Expression('id=:qp0', [':qp0' => 1])], '(type=1) OR (id=:qp0)', [':qp0' => 1]],

            // between
            [['between', 'id', 1, 10], '`id` BETWEEN :qp0 AND :qp1', [':qp0' => 1, ':qp1' => 10]],
            [['not between', 'id', 1, 10], '`id` NOT BETWEEN :qp0 AND :qp1', [':qp0' => 1, ':qp1' => 10]],
            [['between', 'date', new Expression('(NOW() - INTERVAL 1 MONTH)'), new Expression('NOW()')], '`date` BETWEEN (NOW() - INTERVAL 1 MONTH) AND NOW()', []],
            [['between', 'date', new Expression('(NOW() - INTERVAL 1 MONTH)'), 123], '`date` BETWEEN (NOW() - INTERVAL 1 MONTH) AND :qp0', [':qp0' => 123]],
            [['not between', 'date', new Expression('(NOW() - INTERVAL 1 MONTH)'), new Expression('NOW()')], '`date` NOT BETWEEN (NOW() - INTERVAL 1 MONTH) AND NOW()', []],
            [['not between', 'date', new Expression('(NOW() - INTERVAL 1 MONTH)'), 123], '`date` NOT BETWEEN (NOW() - INTERVAL 1 MONTH) AND :qp0', [':qp0' => 123]],

            // in
            [['in', 'id', [1, 2, 3]], '`id` IN (:qp0, :qp1, :qp2)', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3]],
            [['not in', 'id', [1, 2, 3]], '`id` NOT IN (:qp0, :qp1, :qp2)', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3]],

            [['in', 'id', 1],   '`id`=:qp0', [':qp0' => 1]],
            [['in', 'id', [1]], '`id`=:qp0', [':qp0' => 1]],
            [['in', 'id', new \ArrayIterator([1])], '`id`=:qp0', [':qp0' => 1]],
            'composite in' => [
                ['in', ['id', 'name'], [['id' => 1, 'name' => 'oy']]],
                '(`id`, `name`) IN ((:qp0, :qp1))',
                [':qp0' => 1, ':qp1' => 'oy'],
            ],

            // in using array objects.
            [['id' => new \ArrayIterator([1, 2])], '`id` IN (:qp0, :qp1)', [':qp0' => 1, ':qp1' => 2]],

            [['in', 'id', new \ArrayIterator([1, 2, 3])], '`id` IN (:qp0, :qp1, :qp2)', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3]],

            'composite in using array objects' => [
                ['in', new \ArrayIterator(['id', 'name']), new \ArrayIterator([
                    ['id' => 1, 'name' => 'oy'],
                    ['id' => 2, 'name' => 'yo'],
                ])],
                '(`id`, `name`) IN ((:qp0, :qp1), (:qp2, :qp3))',
                [':qp0' => 1, ':qp1' => 'oy', ':qp2' => 2, ':qp3' => 'yo'],
            ],

            // simple conditions
            [['=', 'a', 'b'], '`a` = :qp0', [':qp0' => 'b']],
            [['>', 'a', 1], '`a` > :qp0', [':qp0' => 1]],
            [['>=', 'a', 'b'], '`a` >= :qp0', [':qp0' => 'b']],
            [['<', 'a', 2], '`a` < :qp0', [':qp0' => 2]],
            [['<=', 'a', 'b'], '`a` <= :qp0', [':qp0' => 'b']],
            [['<>', 'a', 3], '`a` <> :qp0', [':qp0' => 3]],
            [['!=', 'a', 'b'], '`a` != :qp0', [':qp0' => 'b']],
            [['>=', 'date', new Expression('DATE_SUB(NOW(), INTERVAL 1 MONTH)')], '`date` >= DATE_SUB(NOW(), INTERVAL 1 MONTH)', []],
            [['>=', 'date', new Expression('DATE_SUB(NOW(), INTERVAL :month MONTH)', [':month' => 2])], '`date` >= DATE_SUB(NOW(), INTERVAL :month MONTH)', [':month' => 2]],

            // hash condition
            [['a' => 1, 'b' => 2], '(`a`=:qp0) AND (`b`=:qp1)', [':qp0' => 1, ':qp1' => 2]],
            [['a' => new Expression('CONCAT(col1, col2)'), 'b' => 2], '(`a`=CONCAT(col1, col2)) AND (`b`=:qp0)', [':qp0' => 2]],

            // direct conditions
            ['a = CONCAT(col1, col2)', 'a = CONCAT(col1, col2)', []],
            [new Expression('a = CONCAT(col1, :param1)', ['param1' => 'value1']), 'a = CONCAT(col1, :param1)', ['param1' => 'value1']],

            // Expression with params as operand of 'not'
            [['not', new Expression('any_expression(:a)', [':a' => 1])], 'NOT (any_expression(:a))', [':a' => 1]],
            [new Expression('NOT (any_expression(:a))', [':a' => 1]), 'NOT (any_expression(:a))', [':a' => 1]],
        ];

        return $conditions;
    }

    /**
     * @dataProvider dataProviderBuildCondition
     *
     * @param array $condition
     * @param string $expected
     * @param array $expectedParams
     */
    public function testBuildCondition($condition, $expected, $expectedParams): void
    {
        $query = (new Query())->where($condition);
        list($sql, $params) = $this->createQueryBuilder()->build($query);
        $this->assertEquals('SELECT *' . (empty($expected) ? '' : ' WHERE ' . $expected), $sql);
        $this->assertEquals($expectedParams, $params);
    }
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\BaseObject;
use yii\db\Expression;

/**
 * MatchExpression represents a MATCH SphinxQL expression.
 * In conjunction with [[MatchBuilder]] this class provides ability to build sophisticated MATCH expressions.
 * Instance of this class can be passed to [[Query::match()]].
 * For example:
 *
 * ```php
 * use yii\sphinx\Query;
 * use yii\sphinx\MatchExpression;
 *
 * $rows = (new Query())
 *     ->match(new MatchExpression('@title :title', ['title' => 'Yii']))
 *     ->all();
 * ```
 *
 * You may use [[match()]], [[andMatch()]] and [[orMatch()]] to combine several conditions.
 * For example:
 *
 * ```php
 * use yii\sphinx\Query;
 * use yii\sphinx\MatchExpression;
 *
 * $rows = (new Query())
 *     ->match(
 *         // produces '((@title "Yii") (@author "Paul")) | (@content "Sphinx")' :
 *         (new MatchExpression())
 *             ->match(['title' => 'Yii'])
 *             ->andMatch(['author' => 'Paul'])
 *             ->orMatch(['content' => 'Sphinx'])
 *     )
 *     ->all();
 * ```
 *
 * You may as well compose expressions with special operators like 'MAYBE', 'PROXIMITY' etc.
 * For example:
 *
 * ```php
 * use yii\sphinx\Query;
 * use yii\sphinx\MatchExpression;
 *
 * $rows = (new Query())
 *     ->match(
 *         // produces '@title "Yii" MAYBE "Sphinx"' :
 *         (new MatchExpression())->match([
 *             'maybe',
 *             'title',
 *             'Yii',
 *             'Sphinx',
 *         ])
 *     )
 *     ->all();
 *
 * $rows = (new Query())
 *     ->match(
 *         // produces '@title "Yii"~10' :
 *         (new MatchExpression())->match([
 *             'proximity',
 *             'title',
 *             'Yii',
 *             10,
 *         ])
 *     )
 *     ->all();
 * ```
 *
 * Note: parameters passed via [[params]] or generated from array conditions will be automatically escaped
 * using [[Connection::escapeMatchValue()]].
 *
 * @see MatchBuilder
 * @see http://sphinxsearch.com/docs/current.html#extended-syntax
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.6
 */
class MatchExpression extends BaseObject
{
    /**
     * @var string|array|Expression MATCH expression.
     * For example: `['title' => 'Yii', 'content' => 'Sphinx']`.
     * Note: being specified as a plain string this value will not be quoted or escaped, do not pass
     * possible unsecured values (like the ones obtained from HTTP request) as a direct value.
     * @see match()
     */
    public $match;
    /**
     * @var array list of match expression parameter values indexed by parameter placeholders.
     * For example, `[':name' => 'Dan', ':age' => 31]`.
     * These parameters will be automatically escaped using [[Connection::escapeMatchValue()]] and inserted into MATCH
     * expression as a quoted strings.
     */
    public $params = [];


    /**
     * Constructor.
     * @param string $match the MATCH expression
     * @param array $params expression parameters.
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($match = null, $params = [], $config = [])
    {
        $this->match = $match;
        $this->params = $params;
        parent::__construct($config);
    }

    /**
     * Sets the MATCH expression.
     *
     * The method requires a `$condition` parameter, and optionally a `$params` parameter
     * specifying the values to be parsed into the expression.
     *
     * The `$condition` parameter should be either a string (e.g. `'@name "John"'`) or an array.
     *
     * @param string|array|Expression $condition the conditions that should be put in the MATCH expression.
     * @param array $params the parameters (name => value) to be parsed into the query.
     * @return $this the expression object itself
     * @see andMatch()
     * @see orMatch()
     */
    public function match($condition, $params = [])
    {
        $this->match = $condition;
        $this->addParams($params);
        return $this;
    }

    /**
     * Adds an additional MATCH condition to the existing one.
     * The new condition and the existing one will be joined using the 'AND' (' ') operator.
     * @param string|array|Expression $condition the new MATCH condition. Please refer to [[match()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be parsed into the query.
     * @return $this the expression object itself
     * @see match()
     * @see orMatch()
     */
    public function andMatch($condition, $params = [])
    {
        if ($this->match === null) {
            $this->match = $condition;
        } else {
            $this->match = ['and', $this->match, $condition];
        }
        $this->addParams($params);
        return $this;
    }

    /**
     * Adds an additional MATCH condition to the existing one.
     * The new condition and the existing one will be joined using the 'OR' ('|') operator.
     * @param string|array|Expression $condition the new WHERE condition. Please refer to [[match()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be parsed into the query.
     * @return $this the expression object itself
     * @see match()
     * @see andMatch()
     */
    public function orMatch($condition, $params = [])
    {
        if ($this->match === null) {
            $this->match = $condition;
        } else {
            $this->match = ['or', $this->match, $condition];
        }
        $this->addParams($params);
        return $this;
    }

    /**
     * Sets the parameters to be parsed into the query.
     * @param array $params list of expression parameter values indexed by parameter placeholders.
     * For example, `[':name' => 'Dan', ':age' => 31]`.
     * @return $this the expression object itself
     * @see addParams()
     */
    public function params($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Adds additional parameters to be parsed into the query.
     * @param array $params list of expression parameter values indexed by parameter placeholders.
     * For example, `[':name' => 'Dan', ':age' => 31]`.
     * @return $this the expression object itself
     * @see params()
     */
    public function addParams($params)
    {
        if (!empty($params)) {
            if (empty($this->params)) {
                $this->params = $params;
            } else {
                foreach ($params as $name => $value) {
                    if (is_int($name)) {
                        $this->params[] = $value;
                    } else {
                        $this->params[$name] = $value;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Sets the MATCH part of the query but ignores [[isEmpty()|empty operands]].
     *
     * This method is similar to [[match()]]. The main difference is that this method will
     * remove [[isEmpty()|empty query operands]]. As a result, this method is best suited
     * for building query conditions based on filter values entered by users.
     *
     * The following code shows the difference between this method and [[match()]]:
     *
     * ```php
     * // MATCH (@title :title)
     * $query->filterMatch(['name' => null, 'title' => 'foo']);
     * // MATCH (@title :title)
     * $query->match(['title' => 20]);
     * // MATCH (@name :name @title :title)
     * $query->match(['name' => null, 'age' => 20]);
     * ```
     *
     * Note that unlike [[match()]], you cannot pass binding parameters to this method.
     *
     * @param array $condition the conditions that should be put in the MATCH part.
     * See [[match()]] on how to specify this parameter.
     * @return $this the query object itself
     * @see where()
     * @see andFilterMatch()
     * @see orFilterMatch()
     * @since 2.0.7
     */
    public function filterMatch(array $condition)
    {
        $condition = $this->filterCondition($condition);
        if ($condition !== []) {
            $this->match($condition);
        }
        return $this;
    }

    /**
     * Adds an additional MATCH condition to the existing one but ignores [[isEmpty()|empty operands]].
     * The new condition and the existing one will be joined using the 'AND' operator.
     *
     * This method is similar to [[andMatch()]]. The main difference is that this method will
     * remove [[isEmpty()|empty query operands]]. As a result, this method is best suited
     * for building query conditions based on filter values entered by users.
     *
     * @param array $condition the new MATCH condition. Please refer to [[match()]]
     * on how to specify this parameter.
     * @return $this the query object itself
     * @see filterMatch()
     * @see orFilterMatch()
     * @since 2.0.7
     */
    public function andFilterMatch(array $condition)
    {
        $condition = $this->filterCondition($condition);
        if ($condition !== []) {
            $this->andMatch($condition);
        }
        return $this;
    }

    /**
     * Adds an additional MATCH condition to the existing one but ignores [[isEmpty()|empty operands]].
     * The new condition and the existing one will be joined using the 'OR' operator.
     *
     * This method is similar to [[orMatch()]]. The main difference is that this method will
     * remove [[isEmpty()|empty query operands]]. As a result, this method is best suited
     * for building query conditions based on filter values entered by users.
     *
     * @param array $condition the new MATCH condition. Please refer to [[match()]]
     * on how to specify this parameter.
     * @return $this the query object itself
     * @see filterMatch()
     * @see andFilterMatch()
     * @since 2.0.7
     */
    public function orFilterMatch(array $condition)
    {
        $condition = $this->filterCondition($condition);
        if ($condition !== []) {
            $this->orMatch($condition);
        }
        return $this;
    }

    /**
     * Removes [[isEmpty()|empty operands]] from the given query condition.
     *
     * @param array $condition the original condition
     * @return array the condition with [[isEmpty()|empty operands]] removed.
     * @since 2.0.7
     */
    protected function filterCondition($condition)
    {
        if (!is_array($condition)) {
            return $condition;
        }

        if (!isset($condition[0])) {
            // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
            foreach ($condition as $name => $value) {
                if ($this->isEmpty($value)) {
                    unset($condition[$name]);
                }
            }
            return $condition;
        }

        // operator format: operator, operand 1, operand 2, ...

        $operator = array_shift($condition);

        switch (strtoupper($operator)) {
            case 'NOT':
            case 'AND':
            case 'OR':
                foreach ($condition as $i => $operand) {
                    $subCondition = $this->filterCondition($operand);
                    if ($this->isEmpty($subCondition)) {
                        unset($condition[$i]);
                    } else {
                        $condition[$i] = $subCondition;
                    }
                }

                if (empty($condition)) {
                    return [];
                }
                break;
            case 'SENTENCE':
            case 'PARAGRAPH':
                $column = array_shift($condition);
                foreach ($condition as $i => $operand) {
                    if ($this->isEmpty($operand)) {
                        unset($condition[$i]);
                    }
                }

                if (empty($condition)) {
                    return [];
                }

                array_unshift($condition, $column);
                break;
            case 'ZONE':
            case 'ZONESPAN':
                foreach ($condition as $i => $operand) {
                    if ($this->isEmpty($operand)) {
                        unset($condition[$i]);
                    }
                }

                if (empty($condition)) {
                    return [];
                }
                break;
            default:
                if (array_key_exists(1, $condition) && $this->isEmpty($condition[1])) {
                    return [];
                }
        }

        array_unshift($condition, $operator);

        return $condition;
    }

    /**
     * Returns a value indicating whether the give value is "empty".
     *
     * The value is considered "empty", if one of the following conditions is satisfied:
     *
     * - it is `null`,
     * - an empty string (`''`),
     * - a string containing only whitespace characters,
     * - or an empty array.
     *
     * @param mixed $value
     * @return bool if the value is empty
     * @since 2.0.7
     */
    protected function isEmpty($value)
    {
        return $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
    }
}
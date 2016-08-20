<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\Object;
use yii\db\Expression;

/**
 * MatchExpression
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.6
 */
class MatchExpression extends Object
{
    /**
     * @var string|array|Expression
     */
    public $match;
    /**
     * @var array list of query parameter values indexed by parameter placeholders.
     * For example, `[':name' => 'Dan', ':age' => 31]`.
     */
    public $params = [];


    /**
     * Sets the MATCH expression.
     *
     * The method requires a `$condition` parameter, and optionally a `$params` parameter
     * specifying the values to be bound to the query.
     *
     * The `$condition` parameter should be either a string (e.g. `'id=1'`) or an array.
     *
     * @param string|array|Expression $condition the conditions that should be put in the WHERE part.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
     * @see andMatch()
     * @see orMatch()
     * @see QueryInterface::match()
     */
    public function match($condition, $params = [])
    {
        $this->match = $condition;
        $this->addParams($params);
        return $this;
    }

    /**
     * Adds an additional MATCH condition to the existing one.
     * The new condition and the existing one will be joined using the 'AND' operator.
     * @param string|array|Expression $condition the new MATCH condition. Please refer to [[match()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
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
     * Adds an additional WHERE condition to the existing one.
     * The new condition and the existing one will be joined using the 'OR' operator.
     * @param string|array|Expression $condition the new WHERE condition. Please refer to [[match()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
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
     * Sets the parameters to be bound to the query.
     * @param array $params list of query parameter values indexed by parameter placeholders.
     * For example, `[':name' => 'Dan', ':age' => 31]`.
     * @return $this the query object itself
     * @see addParams()
     */
    public function params($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Adds additional parameters to be bound to the query.
     * @param array $params list of query parameter values indexed by parameter placeholders.
     * For example, `[':name' => 'Dan', ':age' => 31]`.
     * @return $this the query object itself
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
}
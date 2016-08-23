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
class MatchExpression extends Object
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
}
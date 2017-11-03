<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\InvalidParamException;
use yii\base\BaseObject;
use yii\db\Expression;

/**
 * MatchBuilder builds a MATCH SphinxQL expression based on the specification given as a [[MatchExpression]] object.
 *
 * @see MatchExpression
 * @see http://sphinxsearch.com/docs/current.html#extended-syntax
 *
 * @author Kirichenko Sergey <sa-kirch@yandex.ru>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.6
 */
class MatchBuilder extends BaseObject
{
    /**
     * The prefix for automatically generated query binding parameters.
     */
    const PARAM_PREFIX = ':mp';

    /**
     * @var Connection the Sphinx connection.
     */
    public $db;

    /**
     * @var array map of MATCH keywords to builder methods.
     * These methods are used by [[buildMatch]] to build MATCH expression from array syntax.
     */
    protected $matchBuilders = [
        'AND' => 'buildAndMatch',
        'OR' => 'buildAndMatch',
        'IGNORE' => 'buildIgnoreMatch',
        'PROXIMITY' => 'buildProximityMatch',
        'MAYBE' => 'buildMultipleMatch',
        'SENTENCE' => 'buildMultipleMatch',
        'PARAGRAPH' => 'buildMultipleMatch',
        'ZONE' => 'buildZoneMatch',
        'ZONESPAN' => 'buildZoneMatch',
    ];
    /**
     * @var array map of MATCH operators.
     * These operators are used for replacement of verbose operators.
     */
    protected $matchOperators = [
        'AND' => ' ',
        'OR' => ' | ',
        'NOT' => ' !',
        '=' => ' ',
    ];


    /**
     * Constructor.
     * @param Connection $connection the Sphinx connection.
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($connection, $config = [])
    {
        $this->db = $connection;
        parent::__construct($config);
    }

    /**
     * Generates the MATCH expression from given [[MatchExpression]] object.
     * @param MatchExpression $match the [[MatchExpression]] object from which the MATCH expression will be generated.
     * @return string generated MATCH expression.
     */
    public function build($match)
    {
        $params = $match->params;
        $expression = $this->buildMatch($match->match, $params);
        return $this->parseParams($expression, $params);
    }

    /**
     * Create MATCH expression.
     * @param string|array $match MATCH specification.
     * @param array $params the expression parameters to be populated
     * @return string the MATCH expression
     */
    public function buildMatch($match, &$params)
    {
        if (empty($match)) {
            return '';
        }

        if ($match instanceof Expression) {
            return $this->buildMatchValue($match, $params);
        }

        if (!is_array($match)) {
            return $match;
        }

        if (isset($match[0])) {
            // operator format: operator, operand 1, operand 2, ...
            $operator = strtoupper($match[0]);
            if (isset($this->matchBuilders[$operator])) {
                $method = $this->matchBuilders[$operator];
            } else {
                $method = 'buildSimpleMatch';
            }
            array_shift($match);
            return $this->$method($operator, $match, $params);
        }

        // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
        return $this->buildHashMatch($match, $params);
    }

    /**
     * Creates a MATCH based on column-value pairs.
     * @param array $match the match condition
     * @param array $params the expression parameters to be populated
     * @return string the MATCH expression
     */
    public function buildHashMatch($match, &$params)
    {
        $parts = [];

        foreach ($match as $column => $value) {
            $parts[] = $this->buildMatchColumn($column) . ' ' . $this->buildMatchValue($value, $params);
        }

        return count($parts) === 1 ? $parts[0] : '(' . implode(') (', $parts) . ')';
    }

    /**
     * Connects two or more MATCH expressions with the `AND` or `OR` operator
     * @param string $operator the operator which is used for connecting the given operands
     * @param array $operands the Match expressions to connect
     * @param array $params the expression parameters to be populated
     * @return string the MATCH expression
     */
    public function buildAndMatch($operator, $operands, &$params)
    {
        $parts = [];
        foreach ($operands as $operand) {
            if (is_array($operand) || is_object($operand)) {
                $operand = $this->buildMatch($operand, $params);
            }

            if ($operand !== '') {
                $parts[] = $operand;
            }
        }

        if (empty($parts)) {
            return '';
        }

        return '(' . implode(')' . ($operator === 'OR' ? ' | ' : ' ') . '(', $parts) . ')';
    }

    /**
     * Create MAYBE, SENTENCE or PARAGRAPH expressions.
     * @param string $operator the operator which is used for Create Match expressions
     * @param array $operands the Match expressions
     * @param array &$params the expression parameters to be populated
     * @return string the MATCH expression
     */
    public function buildMultipleMatch($operator, $operands, &$params)
    {
        if (count($operands) < 3) {
            throw new InvalidParamException("Operator '$operator' requires three or more operands.");
        }

        $column = array_shift($operands);

        $phNames = [];

        foreach ($operands as $operand) {
            $phNames[] = $this->buildMatchValue($operand, $params);
        }

        return $this->buildMatchColumn($column) . ' ' . implode(' ' . $operator . ' ', $phNames);
    }

    /**
     * Create MATCH expressions for zones.
     * @param string $operator the operator which is used for Create Match expressions
     * @param array $operands the Match expressions
     * @param array &$params the expression parameters to be populated
     * @return string the MATCH expression
     */
    public function buildZoneMatch($operator, $operands, &$params)
    {
        if (!isset($operands[0])) {
            throw new InvalidParamException("Operator '$operator' requires exactly one operand.");
        }

        $zones = (array)$operands[0];

        return "$operator: (" . implode(',', $zones) . ")";
    }

    /**
     * Create PROXIMITY expressions
     * @param string $operator the operator which is used for Create Match expressions
     * @param array $operands the Match expressions
     * @param array &$params the expression parameters to be populated
     * @return string the MATCH expression
     */
    public function buildProximityMatch($operator, $operands, &$params)
    {
        if (!isset($operands[0], $operands[1], $operands[2])) {
            throw new InvalidParamException("Operator '$operator' requires three operands.");
        }

        list($column, $value, $proximity) = $operands;

        return $this->buildMatchColumn($column) . ' ' . $this->buildMatchValue($value, $params) . '~' . (int) $proximity;
    }

    /**
     * Create ignored MATCH expressions
     * @param string $operator the operator which is used for Create Match expressions
     * @param array $operands the Match expressions
     * @param array &$params the expression parameters to be populated
     * @return string the MATCH expression
     */
    public function buildIgnoreMatch($operator, $operands, &$params)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidParamException("Operator '$operator' requires two operands.");
        }

        list($column, $value) = $operands;

        return $this->buildMatchColumn($column, true) . ' ' . $this->buildMatchValue($value, $params);
    }

    /**
     * Creates an Match expressions like `"column" operator value`.
     * @param string $operator the operator to use. Anything could be used e.g. `>`, `<=`, etc.
     * @param array $operands contains two column names.
     * @param array $params the expression parameters to be populated
     * @return string the MATCH expression
     * @throws InvalidParamException on invalid operands count.
     */
    public function buildSimpleMatch($operator, $operands, &$params)
    {
        if (count($operands) !== 2) {
            throw new InvalidParamException("Operator '$operator' requires two operands.");
        }

        list($column, $value) = $operands;

        if (isset($this->matchOperators[$operator])) {
            $operator = $this->matchOperators[$operator];
        }

        return $this->buildMatchColumn($column) . $operator . $this->buildMatchValue($value, $params);
    }

    /**
     * Create placeholder for expression of MATCH
     * @param string|array|Expression $value
     * @param array $params the expression parameters to be populated
     * @return string the MATCH expression
     */
    protected function buildMatchValue($value, &$params)
    {
        if (empty($value)) {
            return '""';
        }

        if ($value instanceof Expression) {
            $params = array_merge($params, $value->params);
            return $value->expression;
        }

        $parts = [];
        foreach ((array) $value as $v) {
            if ($v instanceof Expression) {
                $params = array_merge($params, $v->params);
                $parts[] = $v->expression;
            } else {
                $phName = self::PARAM_PREFIX . count($params);
                $parts[] = $phName;
                $params[$phName] = $v;
            }
        }

        return implode(' | ', $parts);
    }

    /**
     * Created column as string for expression of MATCH
     * @param string $column column specification.
     * @param bool $ignored whether column should be specified as 'ignored'.
     * @return string the column statement.
     */
    protected function buildMatchColumn($column, $ignored = false)
    {
        if (empty($column)) {
            return '';
        }

        if ($column === '*') {
            return '@*';
        }

        return '@' . ($ignored ? '!' : '') . (strpos($column, ',') === false ? $column : '(' . $column . ')');
    }

    /**
     * Returns the actual MATCH expression by inserting parameter values into the corresponding placeholders.
     * @param string $expression the expression string which is needed to prepare.
     * @param array $params the binding parameters for inserting.
     * @return string parsed expression.
     */
    protected function parseParams($expression, $params)
    {
        if (empty($params)) {
            return $expression;
        }

        foreach ($params as $name => $value) {
            if (strncmp($name, ':', 1) !== 0) {
                $name = ':' . $name;
            }
            // unable to use `str_replace()` because particular param name may be a substring of another param name
            $pattern = "/" . preg_quote($name, '/') . '\b/';
            $value = '"' . $this->db->escapeMatchValue($value) . '"';
            $expression = preg_replace($pattern, $value, $expression, -1, $cnt);
        }

        return $expression;
    }
}
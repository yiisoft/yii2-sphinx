<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\Expression;

/**
 * MatchBuilder
 *
 * @author Kirichenko Sergey <sa-kirch@yandex.ru>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.6
 */
class MatchBuilder extends Object
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
     * @var array map of query match to builder methods
     * These methods are used by [[buildMatch]] to build string inside SQL Match method.
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
     * @var array map of match's operators
     * These operators are used for replacement regular SQL operators
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
     * @param MatchExpression $match the [[MatchExpression]] object from which the MATCH expression will be generated.
     * @param array $params the binding parameters to be populated.
     * @return string generated MATCH expression
     */
    public function build($match, &$params)
    {
        return $this->buildMatch($match->match, $params);
    }

    /**
     * Create Match clause
     * @param string|array $match
     * @param array $params the binding parameters to be populated
     * @return string the MATCH expression
     */
    public function buildMatch($match, &$params)
    {
        if (empty($match)) {
            return '';
        }

        if ($match instanceof Expression || !is_array($match)) {
            return $this->composeMatchValue($match, $params);
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
     * Creates a match based on column-value pairs.
     * @param array $match the match condition
     * @param array $params the binding parameters to be populated
     * @return string the MATCH expression
     */
    public function buildHashMatch($match, &$params)
    {
        $parts = [];

        foreach ($match as $column => $value) {
            $parts[] = $this->buildMatchColumn($column) . ' ' . $this->composeMatchValue($value, $params);
        }

        return count($parts) === 1 ? $parts[0] : '(' . implode(') (', $parts) . ')';
    }

    /**
     * Connects two or more MATCH expressions with the `AND` or `OR` operator
     * @param string $operator the operator which is used for connecting the given operands
     * @param array $operands the Match expressions to connect
     * @param array $params the binding parameters to be populated
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

        if (!empty($parts)) {
            return '(' . implode(") " . ($operator === 'OR' ? '|' : '') . " (", $parts) . ')';
        }

        return '';
    }

    /**
     * Create Maybe, Sentence or Paragraph Match expressions
     * @param  string $operator the operator which is used for Create Match expressions
     * @param  array $operands the Match expressions
     * @param  array &$params the binding parameters to be populated
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
            $phNames[] = $this->composeMatchValue($operand, $params);
        }

        return $this->buildMatchColumn($column) . ' ' . implode(' ' . $operator . ' ', $phNames);
    }

    /**
     * Create Match expressions for zones
     * @param  string $operator the operator which is used for Create Match expressions
     * @param  array $operands the Match expressions
     * @param  array &$params the binding parameters to be populated
     * @return string the MATCH expression
     */
    public function buildZoneMatch($operator, $operands, &$params)
    {
        if (!isset($operands[0])) {
            throw new InvalidParamException("Operator '$operator' requires exactly one operand.");
        }

        $phNames = [];

        foreach ((array)$operands[0] as $zone) {
            $phNames[] = $this->composeMatchValue($zone, $params);
        }

        return "$operator: (" . implode(',', $phNames) . ")";
    }

    /**
     * Create Proximity Match expressions
     * @param  string $operator the operator which is used for Create Match expressions
     * @param  array $operands the Match expressions
     * @param  array &$params the binding parameters to be populated
     * @return string the MATCH expression
     */
    public function buildProximityMatch($operator, $operands, &$params)
    {
        if (!isset($operands[0], $operands[1], $operands[2])) {
            throw new InvalidParamException("Operator '$operator' requires three operands.");
        }

        list($column, $value, $proximity) = $operands;

        return $this->buildMatchColumn($column) . ' ' . $this->composeMatchValue($value, $params) . '~' . (int) $proximity;
    }

    /**
     * Create Ignored Match expressions
     * @param  string $operator the operator which is used for Create Match expressions
     * @param  array $operands the Match expressions
     * @param  array &$params the binding parameters to be populated
     * @return string the MATCH expression
     */
    public function buildIgnoreMatch($operator, $operands, &$params)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidParamException("Operator '$operator' requires two operands.");
        }

        list($column, $value) = $operands;

        return $this->buildMatchColumn($column, true) . ' ' . $this->composeMatchValue($value, $params);
    }

    /**
     * Creates an Match expressions like `"column" operator value`.
     * @param string $operator the operator to use. Anything could be used e.g. `>`, `<=`, etc.
     * @param array $operands contains two column names.
     * @param array $params the binding parameters to be populated
     * @return string the Created SQL expression
     * @throws InvalidParamException if count($operands) is not 2
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

        return $this->buildMatchColumn($column) . $operator . $this->composeMatchValue($value, $params);
    }

    /**
     * Create placeholder for expression of Match
     * @param string|array|Expression $value   [description]
     * @param array &$params the binding parameters to be populated
     * @return string the MATCH expression
     */
    protected function composeMatchValue($value, &$params)
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
                $params[$phName] = $this->db->escapeMatchValue($v);
            }
        }

        return implode(' | ', $parts);
    }

    /**
     * Created column as string for expression of Match
     * @param  string  $column
     * @param  boolean $ignored
     * @return string the column as string
     */
    protected function buildMatchColumn($column, $ignored = false)
    {
        if (empty($column)) {
            return '';
        }

        if ($column === '*') {
            return '@*';
        }

        return '@' . ($ignored ? '!' : '') .
            (strpos($column, ',') === false
                ? $column
                : '(' . $column . ')'
            );
    }
}
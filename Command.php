<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use Yii;
use yii\base\NotSupportedException;
use yii\db\Exception;

/**
 * Command represents a SQL statement to be executed against a Sphinx.
 *
 * A command object is usually created by calling [[Connection::createCommand()]].
 * The SQL statement it represents can be set via the [[sql]] property.
 *
 * To execute a non-query SQL (such as INSERT, REPLACE, DELETE, UPDATE), call [[execute()]].
 * To execute a SQL statement that returns result data set (such as SELECT, CALL SNIPPETS, CALL KEYWORDS),
 * use [[queryAll()]], [[queryOne()]], [[queryColumn()]], [[queryScalar()]], or [[query()]].
 * For example,
 *
 * ```php
 * $articles = $connection->createCommand("SELECT * FROM `idx_article` WHERE MATCH('programming')")->queryAll();
 * ```
 *
 * Command supports SQL statement preparation and parameter binding just as [[\yii\db\Command]] does.
 *
 * Command also supports building SQL statements by providing methods such as [[insert()]],
 * [[update()]], etc. For example,
 *
 * ```php
 * $connection->createCommand()->update('idx_article', [
 *     'genre_id' => 15,
 *     'author_id' => 157,
 * ])->execute();
 * ```
 *
 * To build SELECT SQL statements, please use [[Query]] and [[QueryBuilder]] instead.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Command extends \yii\db\Command
{
    /**
     * @var \yii\sphinx\Connection the Sphinx connection that this command is associated with.
     */
    public $db;


    /**
     * Creates a batch INSERT command.
     * For example,
     *
     * ```php
     * $connection->createCommand()->batchInsert('idx_user', ['name', 'age'], [
     *     ['Tom', 30],
     *     ['Jane', 20],
     *     ['Linda', 25],
     * ])->execute();
     * ```
     *
     * Note that the values in each row must match the corresponding column names.
     *
     * @param string $index the index that new rows will be inserted into.
     * @param array $columns the column names
     * @param array $rows the rows to be batch inserted into the index
     * @return $this the command object itself
     */
    public function batchInsert($index, $columns, $rows)
    {
        $params = [];
        $sql = $this->db->getQueryBuilder()->batchInsert($index, $columns, $rows, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Creates an REPLACE command.
     * For example,
     *
     * ```php
     * $connection->createCommand()->replace('idx_user', [
     *     'name' => 'Sam',
     *     'age' => 30,
     * ])->execute();
     * ```
     *
     * The method will properly escape the column names, and bind the values to be replaced.
     *
     * Note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $index the index that new rows will be replaced into.
     * @param array $columns the column data (name => value) to be replaced into the index.
     * @return $this the command object itself
     */
    public function replace($index, $columns)
    {
        $params = [];
        $sql = $this->db->getQueryBuilder()->replace($index, $columns, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Creates a batch REPLACE command.
     * For example,
     *
     * ```php
     * $connection->createCommand()->batchReplace('idx_user', ['name', 'age'], [
     *     ['Tom', 30],
     *     ['Jane', 20],
     *     ['Linda', 25],
     * ])->execute();
     * ```
     *
     * Note that the values in each row must match the corresponding column names.
     *
     * @param string $index the index that new rows will be replaced.
     * @param array $columns the column names
     * @param array $rows the rows to be batch replaced in the index
     * @return $this the command object itself
     */
    public function batchReplace($index, $columns, $rows)
    {
        $params = [];
        $sql = $this->db->getQueryBuilder()->batchReplace($index, $columns, $rows, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Creates an UPDATE command.
     * For example,
     *
     * ```php
     * $connection->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();
     * ```
     *
     * The method will properly escape the column names and bind the values to be updated.
     *
     * Note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $index the index to be updated.
     * @param array $columns the column data (name => value) to be updated.
     * @param string|array $condition the condition that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify condition.
     * @param array $params the parameters to be bound to the command
     * @param array $options list of options in format: optionName => optionValue
     * @return $this the command object itself
     */
    public function update($index, $columns, $condition = '', $params = [], $options = [])
    {
        $sql = $this->db->getQueryBuilder()->update($index, $columns, $condition, $params, $options);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Creates a SQL command for truncating a runtime index.
     * @param string $index the index to be truncated. The name will be properly quoted by the method.
     * @return $this the command object itself
     */
    public function truncateIndex($index)
    {
        $sql = $this->db->getQueryBuilder()->truncateIndex($index);

        return $this->setSql($sql);
    }

    /**
     * Builds a snippet from provided data and query, using specified index settings.
     * @param string $index name of the index, from which to take the text processing settings.
     * @param string|array $source is the source data to extract a snippet from.
     * It could be either a single string or array of strings.
     * @param string $match the full-text query to build snippets for.
     * @param array $options list of options in format: optionName => optionValue
     * @return $this the command object itself
     */
    public function callSnippets($index, $source, $match, $options = [])
    {
        $params = [];
        $sql = $this->db->getQueryBuilder()->callSnippets($index, $source, $match, $options, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Returns tokenized and normalized forms of the keywords, and, optionally, keyword statistics.
     * @param string $index the name of the index from which to take the text processing settings
     * @param string $text the text to break down to keywords.
     * @param bool $fetchStatistic whether to return document and hit occurrence statistics
     * @return $this the command object itself
     */
    public function callKeywords($index, $text, $fetchStatistic = false)
    {
        $params = [];
        $sql = $this->db->getQueryBuilder()->callKeywords($index, $text, $fetchStatistic, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    // Float handling :

    /**
     * @var array list of 'float' type params, which should be inserted into SQL directly instead of binding.
     * @see Connection::enableFloatConversion
     * @since 2.0.6
     */
    private $floatParams = [];

    /**
     * {@inheritdoc}
     */
    public function bindValue($name, $value, $dataType = null)
    {
        if ($this->db->enableFloatConversion && $dataType === null && is_float($value)) {
            $this->floatParams[$name] = $value;
            return $this;
        }
        return parent::bindValue($name, $value, $dataType);
    }

    /**
     * {@inheritdoc}
     */
    public function bindValues($values)
    {
        if ($this->db->enableFloatConversion) {
            if (empty($values)) {
                return $this;
            }

            foreach ($values as $name => $value) {
                if (is_float($value)) {
                    $this->floatParams[$name] = $value;
                    unset($values[$name]);
                }
            }
        }

        return parent::bindValues($values);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($forRead = null)
    {
        if ($this->pdoStatement && empty($this->floatParams)) {
            $this->bindPendingParams();
            return;
        }

        $sql = $this->getSql();

        if ($this->db->getTransaction()) {
            // master is in a transaction. use the same connection.
            $forRead = false;
        }
        if ($forRead || $forRead === null && $this->db->getSchema()->isReadQuery($sql)) {
            $pdo = $this->db->getSlavePdo();
        } else {
            $pdo = $this->db->getMasterPdo();
        }

        if (!empty($this->floatParams)) {
            $sql = $this->parseFloatParams($sql);
        }

        try {
            $this->pdoStatement = $pdo->prepare($sql);
            $this->bindPendingParams();
        } catch (\Exception $e) {
            $message = $e->getMessage() . "\nFailed to prepare SphinxQL: $sql";
            $errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
            throw new Exception($message, $errorInfo, (int) $e->getCode(), $e);
        }
    }

    /**
     * Parses given SQL replacing bound [[floatParams]] with their values.
     * @param string $sql source SQL.
     * @return string adjusted SQL.
     */
    private function parseFloatParams($sql)
    {
        foreach ($this->floatParams as $name => $value) {
            if (strncmp($name, ':', 1) !== 0) {
                $name = ':' . $name;
            }
            // unable to use `str_replace()` because particular param name may be a substring of another param name
            $sql = preg_replace('/' . preg_quote($name) . '\b/s', $value, $sql);
        };

        return $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawSql()
    {
        return $this->parseFloatParams(parent::getRawSql());
    }

    // Not Supported :

    /**
     * {@inheritdoc}
     */
    public function createTable($table, $columns, $options = null)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function renameTable($table, $newName)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function dropTable($table)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function truncateTable($table)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn($table, $column, $type)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function dropColumn($table, $column)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function renameColumn($table, $oldName, $newName)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function alterColumn($table, $column, $type)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function addPrimaryKey($name, $table, $columns)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function dropPrimaryKey($name, $table)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function dropForeignKey($name, $table)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function dropIndex($name, $table)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function resetSequence($table, $value = null)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }
}

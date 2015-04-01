<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;

/**
 * ActiveDataProvider is an enhanced version of [[\yii\data\ActiveDataProvider]] specific to the Sphinx.
 * It allows to fetch not only rows and total rows count, but also a meta information and facet results.
 *
 * The following is an example of using ActiveDataProvider to provide facet results:
 *
 * ~~~
 * $provider = new ActiveDataProvider([
 *     'query' => Post::find()->facets(['author_id', 'category_id']),
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * // get the posts in the current page
 * $posts = $provider->getModels();
 *
 * // get all facets
 * $facets = $provider->getFacets();
 *
 * // get particular facet
 * $authorFacet = $provider->getFacet('author_id');
 * ~~~
 *
 * In case [[Query::showMeta]] is set ActiveDataProvider will fetch total count value from the query meta information,
 * avoiding extra counting query:
 *
 * ~~~
 * $provider = new ActiveDataProvider([
 *     'query' => Post::find()->showMeta(true),
 *     'pagination' => [
 *         'pageSize' => 20,
 *         'validatePage' => false,
 *     ],
 * ]);
 *
 * $totalCount = $provider->getTotalCount(); // fetched from meta information
 * ~~~
 *
 * Note: when using 'meta' information results total count will be fetched after pagination limit applying,
 * which eliminates ability to verify if requested page number actually exist. You should disable `yii\data\Pagination::validatePage`
 * in case you are using this approach.
 *
 * @property array $meta search query meta info in format: name => value.
 * @property array $facets query facet results.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.4
 */
class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
    /**
     * @var array search query meta info in format: name => value.
     */
    private $_meta;
    /**
     * @var array query facet results.
     */
    private $_facets;

    /**
     * @param array $facets query facet results.
     */
    public function setFacets($facets)
    {
        $this->_facets = $facets;
    }

    /**
     * @return array query facet results.
     */
    public function getFacets()
    {
        if (!is_array($this->_facets)) {
            $this->prepareModels();
        }
        return $this->_facets;
    }

    /**
     * @param array $meta search query meta info
     */
    public function setMeta($meta)
    {
        $this->_meta = $meta;
    }

    /**
     * @return array search query meta info
     */
    public function getMeta()
    {
        if (!is_array($this->_meta)) {
            $this->prepareModels();
        }
        return $this->_meta;
    }

    /**
     * Returns results of the specified facet.
     * @param string $name facet name
     * @throws InvalidCallException if requested facet does not present in results.
     * @return array facet results.
     */
    public function getFacet($name)
    {
        $facets = $this->getFacets();
        if (!isset($facets[$name])) {
            throw new InvalidCallException("Facet '{$name}' does not present.");
        }
        return $facets[$name];
    }

    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        if (!$this->query instanceof Query) {
            throw new InvalidConfigException('The "query" property must be an instance "' . Query::className() . '" or its subclasses.');
        }
        $query = clone $this->query;
        if (($pagination = $this->getPagination()) !== false) {
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }
        if (($sort = $this->getSort()) !== false) {
            $query->addOrderBy($sort->getOrders());
        }

        $results = $query->search($this->db);
        $this->setMeta($results['meta']);
        $this->setFacets($results['facets']);

        if ($pagination !== false) {
            $pagination->totalCount = $this->getTotalCount();
        }

        return $results['hits'];
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        if (!$this->query instanceof Query) {
            throw new InvalidConfigException('The "query" property must be an instance "' . Query::className() . '" or its subclasses.');
        }

        $meta = $this->getMeta();
        if (isset($meta['total'])) {
            return (int) $meta['total'];
        }

        $query = clone $this->query;
        return (int) $query->limit(-1)->offset(-1)->orderBy([])->facets([])->showMeta(false)->count('*', $this->db);
    }
} 
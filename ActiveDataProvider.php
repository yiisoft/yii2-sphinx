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
 * It allows to fetch not only rows and total rows count, but also a facet results.
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
 * @property array $facets query facet results.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.4
 */
class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
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
            $pagination->totalCount = $this->getTotalCount();
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }
        if (($sort = $this->getSort()) !== false) {
            $query->addOrderBy($sort->getOrders());
        }

        if (empty($query->facets)) {
            $this->setFacets([]);
            return $query->all($this->db);
        }

        $results = $query->search($this->db);
        $this->setFacets($results['facets']);
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
        $query = clone $this->query;
        return (int) $query->limit(-1)->offset(-1)->orderBy([])->facets([])->count('*', $this->db);
    }
} 
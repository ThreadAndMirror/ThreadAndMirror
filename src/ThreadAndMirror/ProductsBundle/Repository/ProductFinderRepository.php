<?php

namespace ThreadAndMirror\ProductsBundle\Repository;

use	ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Service\ProductFilter;
use FOS\ElasticaBundle\Repository;
use Elastica\Query;
use Elastica\Query\Match as MatchQuery;
use Elastica\Query\Terms as TermsQuery;
use Elastica\Query\Bool as BoolQuery;
use Elastica\Query\Filtered as FilteredQuery;
use Elastica\Filter\Bool as BoolFilter;
use Elastica\Filter\Term as TermFilter;

class ProductFinderRepository extends Repository
{
    public function findNewIn($filters, $area, $page = 1, $perPage = 100)
    {
        $query = new \Elastica\Query();
        $keywords = $filters->getKeywords();
        // $query->addSort(array('created' => array('order' => 'desc')));

        if (!empty($keywords)) {
            $q = new \Elastica\Query\QueryString($keywords);
        } else {
            $q = new \Elastica\Query\MatchAll();
        }

        $term = new \Elastica\Filter\Term(array('area' => $area));
        $filteredQuery = new \Elastica\Query\Filtered($q, $term);

        $query->setQuery($filteredQuery);

        $results = $this->findPaginated($query);

        // $players = $this->get('fos_elastica.finder.xxx.player')->find($query);

        // $results = $this->finder->findPaginated($query, array('from' => $page, 'size' => $perPage));

        // $query = new BoolQuery();

        // Add the product filters
        // $this->addFilters($query, $filters);

        // Restrict by area
        // $filtered = new Query();
        // $filtered->setFilter(new TermFilter());
        // $filtered->setQuery($query);

        // Pagination
        // $results = $this->findPaginated($filtered);
        $results->setMaxPerPage($perPage);
        $results->setCurrentPage($page);
        

        return $results;
    }

    /**
     * Add any submitted filters to a query
     */
    protected function addFilters(BoolQuery $query, ProductFilter $filters)
    {
        // Keywords
        if ($filters->getKeywords() !== null) {

            $name = new MatchQuery();
            $name->setFieldQuery('name', $filters->getKeywords());
            // $name->setFieldParam('name', 'analyzer', 'snowball');
            $query->addShould($name);

            $description = new MatchQuery();
            $description->setFieldQuery('description', $filters->getKeywords());
            $query->addShould($description);

            $brand = new MatchQuery();
            $brand->setFieldQuery('brand', $filters->getKeywords());
            $query->addShould($brand);
        }
    }
}
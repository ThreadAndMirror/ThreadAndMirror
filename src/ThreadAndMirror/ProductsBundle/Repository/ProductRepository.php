<?php

namespace ThreadAndMirror\ProductsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Service\ProductFilter;

class ProductRepository extends EntityRepository
{
    /**
     * Add the essential components for a product query
     *
     * @param  ProductFilter $filters Requested filters
     * @param  string $area The product area
     * @param  integer $limit A limit to apply to the result set
     *
     * @return QueryBuilder
     */
    protected function startProductQuery(ProductFilter $filters = null, $area = null, $limit = 3000)
    {
        // Select
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->addSelect('product');
        $qb->from('ThreadAndMirrorProductsBundle:Product', 'product');
        $qb->innerJoin('product.shop', 'shop');

        // Ignore deleted and expired
        $qb->where('product.deleted = :deleted');
        $qb->setParameter('deleted', '0');
        $qb->andWhere($qb->expr()->isNull('product.expired'));

        // Limit by area
        if ($area !== null) {
            $qb->andWhere('product.area = :area');
            $qb->setParameter('area', $area);
        }

        // Filter by keywords if the filter allows it
        if ($filters !== null && !$filters->getIgnoreKeywords()) {
            $qb->andWhere($qb->expr()->like('product.name', ':keywords'));
            $qb->setParameter('keywords', '%' . $filters->getKeywords() . '%');
        }

        // Default to ordering newest first
        $qb->orderBy('product.id', 'DESC');

        // Limit results
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * Get a list of values from a scalar query result
     *
     * @param QueryBuilder $qb The query builder
     * @param string $property The property to take the value from
     *
     * @return array An un-indexed array of extracted values
     */
    protected function getList(QueryBuilder $qb, $property)
    {
        $results = $qb->getQuery()->getScalarResult();
        $values = array();

        foreach ($results as $result) {
            $values[] = $result[$property];
        }

        return $values;
    }

    /**
     * Find all products in the specified area
     *
     * @param  ProductFilter $filters Requested filters
     * @param  string $area The product area
     * @return array                        The resulting Products
     */
    public function findProducts(ProductFilter $filters, $area)
    {
        $qb = $this->startProductQuery($filters, $area);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find all new-in products in the specified area
     *
     * @param  ProductFilter $filters Requested filters
     * @param  string $area The product area
     * @return array                        The resulting Products
     */
    public function findNewIn(ProductFilter $filters, $area)
    {
        $qb = $this->startProductQuery($filters, $area);

        // Only flagged as new in
        $qb->andWhere('product.new = :new');
        $qb->setParameter('new', '1');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find all sale products in the specified area
     *
     * @param  ProductFilter $filters Requested filters
     * @param  string $area The product area
     * @return array                        The resulting Products
     */
    public function findSale(ProductFilter $filters, $area)
    {
        $qb = $this->startProductQuery($filters, $area);

        // Only flagged as on sale
        $qb->andWhere($qb->expr()->isNotNull('product.sale'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Find all products in the specified area belonging to the specified shop
     *
     * @param  ProductFilter $filters Requested filters
     * @param  string $area The product area
     * @param  string $shop The slug of the shop to match
     * @return array                        The resulting Products
     */
    public function findShopProducts(ProductFilter $filters, $area, $shop)
    {
        $qb = $this->startProductQuery($filters, $area);

        // Only from the given shop
        $qb->andWhere('shop.slug = :shop');
        $qb->setParameter('shop', $shop);

        return $qb->getQuery()->getResult();
    }

    /**
     * Products that have been out of stock for over a month
     *
     * @return array                        The resulting Products
     */
    public function findExpiringProducts()
    {
        $qb = $this->startProductQuery(null, null, null);

        // Out of stock
        $qb->andWhere('product.available = :available');
        $qb->setParameter('available', '0');

        // Last updated over a month ago
        $expiry = new \DateTime();
        $expiry->modify('-1 month');
        $qb->andWhere('product.updated < :expiry');
        $qb->setParameter('expiry', $expiry);

        return $qb->getQuery()->getResult();
    }

    /**
     * Products that have been marked as new for over 7 days
     *
     * @return array                        The resulting Products
     */
    public function getExpiredLatestAdditions()
    {
        $qb = $this->startProductQuery(null, null, null);

        // Added over a week ago
        $expiry = new \DateTime();
        $expiry->modify('-7 days');
        $qb->andWhere('product.added < :expiry');
        $qb->setParameter('expiry', $expiry);

        // Only if flagged as new
        $qb->andwhere('product.new = :new');
        $qb->setParameter('new', '1');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get all existing product IDs for a given affiliate merchant
     *
     * @return array                        The resulting pids
     */
    public function findExistingPidsByMerchant($merchant)
    {
        $qb = $this->startProductQuery(null, null, null);

        // Filter by merchant
        $qb->andWhere('shop.affiliateId = :affiliateId');
        $qb->setParameter('affiliateId', $merchant);

        return $this->getList($qb, 'product_pid');
    }

    /**
     * Find updatable products for the given shop
     *
     * @param  integer $limit Limit the amount of results
     * @param  string $slug The slug of the shop
     * @return array                        The resulting pids
     */
    public function findUpdateableByShop($limit = 200, $shop)
    {
        $qb = $this->startProductQuery(null, null, $limit);

        // Get for the specified shop
        $qb->andWhere('shop.slug = :slug');
        $qb->setParameter('slug', $shop);

        // Order by most recently added
        $qb->orderBy('product.checked', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find updatable products that have a brand name but no linked brand
     *
     * @param  integer $limit Limit the amount of results
     * @return array                        The resulting products
     */
    public function findUpdateableForBrands($limit = 1000)
    {
        $qb = $this->startProductQuery(null, null, $limit);

        // Get for the specified shop
        $qb->andWhere($qb->expr()->isNotNull('product.brandName'));
        $qb->andWhere($qb->expr()->isNull('product.brand'));

        // Order by most recently added
        $qb->orderBy('product.checked', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
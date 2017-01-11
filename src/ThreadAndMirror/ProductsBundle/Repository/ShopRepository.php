<?php

namespace ThreadAndMirror\ProductsBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ShopRepository extends EntityRepository
{
    /**
     * @todo update to a new field that's different from the base url
     */
    public function getShopFromUrl($url)
    {
        $shops = $this->getEntityManager()->getRepository('ThreadAndMirrorProductsBundle:Shop')->findAll();

        // check if the url can be matched against the shop urls
        foreach ($shops as &$shop) {
            if (stristr($url, $shop->getUrl())) {
                return $shop;
            }
        }

        return false;
    }

    public function getShopSlugFromUrl($url)
    {
        $shops = $this->getEntityManager()->getRepository('ThreadAndMirrorProductsBundle:Shop')->findAll();

        // check if the url can be matched against the shop urls
        foreach ($shops as &$shop) {
            if (stristr($url, $shop->getUrl())) {
                return $shop->getSlug();
            }
        }

        return false;
    }

    /**
     * A cheap and cheerful way of getting a list of shop slugs and ids
     */
    public function getAvailableShopIds()
    {
        // get the shops in array format
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->addSelect('shop');
        $qb->from('ThreadAndMirrorProductsBundle:Shop', 'shop');
        $qb->where('shop.deleted = :deleted');
        $qb->setParameter('deleted', '0');
        $results = $qb->getQuery()->getScalarResult();

        // build an array with slugs as the key and id as the value
        $shops = array();
        foreach ($results as $shop) {
            $shops[$shop['shop_slug']] = $shop['shop_id'];
        }

        // clear the results array from memory
        unset($results);

        return $shops;
    }

    public function getLatestProductIds($shop)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->addSelect('product');
        $qb->from('ThreadAndMirrorProductsBundle:Product', 'product');

        // only search for newly added products
        $qb->where('product.new = :new');
        $qb->andWhere('product.deleted = :deleted');
        $qb->setParameter('new', '1');
        $qb->setParameter('deleted', '0');

        // filter by shops
        $qb->andWhere('product.shop = :shop');
        $qb->setParameter('shop', $shop);

        // get the products in an array to save on memory
        $results = $qb->getQuery()->getScalarResult();

        // build an array with just pids
        $pids = array();
        foreach ($results as $product) {
            $pids[] = $product['product_pid'];
        }

        // clear the results array to save memory further
        unset($results);

        return $pids;
    }

    public function getSaleProductIds($shop)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->addSelect('product');
        $qb->from('ThreadAndMirrorProductsBundle:Product', 'product');

        // only search for newly added products
        $qb->where('product.sale = :sale');
        $qb->andWhere('product.deleted = :deleted');
        $qb->setParameter('sale', '1');
        $qb->setParameter('deleted', '0');

        // filter by shops
        $qb->andWhere('product.shop = :shop');
        $qb->setParameter('shop', $shop);

        // get the products in an array to save on memory
        $results = $qb->getQuery()->getScalarResult();

        // build an array with just pids
        $pids = array();
        foreach ($results as $product) {
            $pids[] = $product['product_pid'];
        }

        // clear the results array to save memory further
        unset($results);

        return $pids;
    }

    public function getExistingProductIds($shop)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->addSelect('product');
        $qb->from('ThreadAndMirrorProductsBundle:Product', 'product');

        // only search for newly added products
        $qb->andWhere('product.deleted = :deleted');
        $qb->andWhere($qb->expr()->isNull('product.expired'));
        $qb->setParameter('deleted', '0');

        // filter by shops
        $qb->andWhere('product.shop = :shop');
        $qb->setParameter('shop', $shop);

        // get the products in an array to save on memory
        $results = $qb->getQuery()->getScalarResult();

        // build an array with just pids
        $pids = array();
        foreach ($results as $product) {
            $pids[] = $product['product_pid'];
        }

        // clear the results array to save memory further
        unset($results);

        return $pids;
    }

    /**
     * Gets the list of shops that belong to a specific affiliate and product area
     *
     * @param  string $affiliate The affiliate name
     * @param  string $area The product area
     * @param  boolean $list If set to true, then only get a list of IDs
     * @return array                    A list of affiliate shops or merchants IDs
     */
    public function getAffiliatesForArea($affiliate, $area, $list = false)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        if ($list) {
            $qb->addSelect('shop.affiliateId');
        } else {
            $qb->addSelect('shop');
        }

        $qb->from('ThreadAndMirrorProductsBundle:Shop', 'shop');
        $qb->where('shop.affiliateName = :affiliate');
        $qb->setParameter('affiliate', $affiliate);

        // For beauty products
        if ($area == 'beauty') {
            $qb->andWhere('shop.hasBeauty = :beauty');
            $qb->setParameter('beauty', true);
        }

        // For fashion products
        if ($area == 'fashion') {
            $qb->andWhere('shop.hasFashion = :fashion');
            $qb->setParameter('fashion', true);
        }

        if ($list) {
            // Get the results as an array of affiliate IDs
            $results = $qb->getQuery()->getScalarResult();

            $ids = array_map(function ($shop) {
                return $shop['affiliateId'];
            }, $results);

            return $ids;
        } else {
            return $qb->getQuery()->getResult();
        }
    }

    /**
     * Gets the list of merchants that belong to a specific affiliate and product area
     *
     * @param  string $affiliate The affiliate name
     * @return array                    A list of affiliated shops
     */
    public function findByAffiliate($affiliate)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->addSelect('shop');
        $qb->from('ThreadAndMirrorProductsBundle:Shop', 'shop');
        $qb->where('shop.affiliateName = :affiliate');
        $qb->setParameter('affiliate', $affiliate);

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    /**
     * Gets the service names for the given shop slug
     *
     * @param  string $slug The shop slug
     * @return array                    The crawler service name
     */
    public function getServiceNames($slug)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->addSelect('shop.serviceName');
        $qb->from('ThreadAndMirrorProductsBundle:Shop', 'shop');
        $qb->where('shop.slug = :slug');
        $qb->setParameter('slug', $slug);

        $result = $qb->getQuery()->getScalarResult();

        return array(
            'updater'   => 'threadandmirror.products.updater.' . $result[0]['serviceName'],
            'formatter' => 'threadandmirror.products.formatter.' . $result[0]['serviceName'],
            'crawler'   => 'threadandmirror.products.crawler.' . $result[0]['serviceName']
        );
    }
}
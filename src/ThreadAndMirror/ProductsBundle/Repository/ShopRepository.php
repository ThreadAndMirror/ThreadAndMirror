<?php

namespace ThreadAndMirror\ProductsBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ShopRepository extends EntityRepository
{
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
}
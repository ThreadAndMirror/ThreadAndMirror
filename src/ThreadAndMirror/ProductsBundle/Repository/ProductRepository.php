<?php

namespace ThreadAndMirror\ProductsBundle\Repository;

use Doctrine\ORM\EntityRepository,
	ThreadAndMirror\ProductsBundle\Entity\Product,
	ThreadAndMirror\ProductsBundle\Service\SaleParser,
	Symfony\Component\DomCrawler\Crawler;

use ThreadAndMirror\ProductsBundle\Service\ProductParser;

class ProductRepository extends EntityRepository
{
	public function getUpdateable($limit, $shop)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('product');
		$qb->from('ThreadAndMirrorProductsBundle:Product', 'product');
		
		// get unexpired products for the specified shop
		$qb->where('product.deleted = :deleted');
		$qb->andWhere('product.shop = :shop');
		$qb->andWhere($qb->expr()->isNull('product.expired'));
		$qb->setParameter('deleted', '0');
		$qb->setParameter('shop', $shop);

		// order by most recently added
		$qb->orderBy('product.checked', 'ASC');	

		return $qb->setMaxResults($limit)->getQuery()->getResult();
	}

	public function getAllFashion($filters=null)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('product');
		$qb->from('ThreadAndMirrorProductsBundle:Product', 'product');
		
		// only search for newly added products
		$qb->where('product.deleted = :deleted');
		$qb->andWhere('product.attire = :attire');
		$qb->andWhere($qb->expr()->isNull('product.expired'));
		$qb->setParameter('deleted', '0');
		$qb->setParameter('attire', '1');

		// filter by shops if the filter allows it
		// if (!$filters->getIgnoreShops()) {
		// 	$qb->andWhere($qb->expr()->in('product.shop', ':shops'));
		// 	$qb->setParameter('shops', $filters->getShops());
		// }
			
		// // filter by categories if the filter allows it
		// if (!$filters->getIgnoreCategories()) {
		// 	$qb->andWhere($qb->expr()->in('product.category', ':categories'));
		// 	$qb->setParameter('categories', $filters->getCategories());
		// }

		// filter by keywords if the filter allows it
		if (!$filters->getIgnoreKeywords()) {
			$qb->andWhere($qb->expr()->like('product.name', ':keywords'));
			$qb->setParameter('keywords', '%'.$filters->getKeywords().'%');
		}

		// order by most recently added
		$qb->orderBy('product.id', 'DESC');	

		// show the most recent 3000 items by default
		return $qb->setMaxResults(3000)->getQuery()->getResult();
	}

	public function getLatestAdditions($filters=null, $type='attire')
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('product');
		$qb->from('ThreadAndMirrorProductsBundle:Product', 'product');
		
		// only search for newly added products
		$qb->where('product.new = :new');
		$qb->andWhere('product.deleted = :deleted');
		$qb->andWhere($qb->expr()->isNull('product.expired'));
		$qb->setParameter('new', '1');
		$qb->setParameter('deleted', '0');

		// pick the product type
		if ($type == 'attire') {
			$qb->andWhere('product.attire = :attire');
			$qb->setParameter('attire', '1');
		} else {
			$qb->andWhere('product.attire = :attire');
			$qb->setParameter('attire', '0');
		}
		if ($type == 'beauty') {
			$qb->andWhere('product.beauty = :beauty');
			$qb->setParameter('beauty', '1');
		} else {
			$qb->andWhere('product.beauty = :beauty');
			$qb->setParameter('beauty', '0');
		}

		// filter by shops if the filter allows it
		// if (!$filters->getIgnoreShops()) {
		// 	$qb->andWhere($qb->expr()->in('product.shop', ':shops'));
		// 	$qb->setParameter('shops', $filters->getShops());
		// }
			
		// // filter by categories if the filter allows it
		// if (!$filters->getIgnoreCategories()) {
		// 	$qb->andWhere($qb->expr()->in('product.category', ':categories'));
		// 	$qb->setParameter('categories', $filters->getCategories());
		// }

		// filter by keywords if the filter allows it
		if (!$filters->getIgnoreKeywords()) {
			$qb->andWhere($qb->expr()->like('product.name', ':keywords'));
			$qb->setParameter('keywords', '%'.$filters->getKeywords().'%');
		}

		// order by most recently added
		$qb->orderBy('product.id', 'DESC');	

		// show the most recent 3000 items by default
		return $qb->setMaxResults(3000)->getQuery()->getResult();
	}

	public function getSaleItems($filters=null)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('product');
		$qb->from('ThreadAndMirrorProductsBundle:Product', 'product');

		// only search for sale products
		$qb->where($qb->expr()->isNotNull('product.sale'));
		$qb->andWhere($qb->expr()->isNull('product.expired'));
		$qb->andWhere('product.attire = :attire');
		$qb->andWhere('product.deleted = :deleted');
		$qb->setParameter('deleted', '0');
		$qb->setParameter('attire', '1');

		// filter by shops if the filter allows it
		// if (!$filters->getIgnoreShops()) {
		// 	$qb->andWhere($qb->expr()->in('product.shop', ':shops'));
		// 	$qb->setParameter('shops', $filters->getShops());
		// }

		// filter by categories if the filter allows it
		// if (!$filters->getIgnoreCategories()) {
		// 	$qb->andWhere($qb->expr()->in('product.category', ':categories'));
		// 	$qb->setParameter('categories', $filters->getCategories());
		// }

		// filter by keywords if the filter allows it
		if (!$filters->getIgnoreKeywords()) {
			$qb->andWhere($qb->expr()->like('product.name', ':keywords'));
			$qb->setParameter('keywords', '%'.$filters->getKeywords().'%');
		}

		// order by most recently added
		$qb->orderBy('product.sale', 'DESC');

		// show the most recent 3000 items by default
		return $qb->setMaxResults(3000)->getQuery()->getResult();
	}

	/**
	 * All in one product query for getting beauty products
	 */
	public function getBeauty($filters=null, $mode='all')
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('product');
		$qb->from('ThreadAndMirrorProductsBundle:Product', 'product');
		
		// only search for valid beauty products
		$qb->where('product.beauty = :beauty');
		$qb->andWhere('product.deleted = :deleted');
		$qb->andWhere($qb->expr()->isNull('product.expired'));
		$qb->setParameter('deleted', '0');
		$qb->setParameter('beauty', '1');

		// new products
		if ($mode == 'new') {
			$qb->andWhere('product.new = :new');
			$qb->setParameter('new', '1');
		}

		// sale products
		if ($mode == 'sale') {
			$qb->andWhere($qb->expr()->isNotNull('product.sale'));
		}

		// filter by keywords if the filter allows it
		if (isset($filters) && !$filters->getIgnoreKeywords()) {
			$qb->andWhere($qb->expr()->like('product.name', ':keywords'));
			$qb->setParameter('keywords', '%'.$filters->getKeywords().'%');
		}

		// order by most recently added
		$qb->orderBy('product.id', 'DESC');	

		// show the most recent 3000 items by default
		return $qb->setMaxResults(3000)->getQuery()->getResult();
	}

	/**
	 * Products that have been marked as new for over 7 days
	 */
	public function getExpiredLatestAdditions()
	{
		// set the expiry date
		$expiry = new \DateTime();
		$expiry->modify('-7 days');

		// run the query
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('product');
		$qb->from('ThreadAndMirrorProductsBundle:Product', 'product');

		$qb->where('product.new = :new');
		$qb->andWhere('product.added < :expiry');
		$qb->setParameter('new', '1');
		$qb->setParameter('expiry', $expiry);

		return $qb->getQuery()->getResult();
	}

	/**
	 * Products that have been out of stock for over a month
	 */
	public function getExpiringProducts()
	{
		// set the expiry date
		$expiry = new \DateTime();
		$expiry->modify('-1 month');

		// run the query
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('product');
		$qb->from('ThreadAndMirrorProductsBundle:Product', 'product');

		$qb->where('product.available = :available');
		$qb->andWhere('product.updated < :expiry');
		$qb->setParameter('available', '0');
		$qb->setParameter('expiry', $expiry);
		
		return $qb->getQuery()->getResult();
	}

	public function getProductFromUrl($url)
	{
		$em = $this->getEntityManager();

		// work out which shop we're attempting to load from
		$shop = $em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getShopFromUrl($url);

		// build the method name for the parser
		$slug = $shop->getSlug();
		$class = 'ThreadAndMirror\\ProductsBundle\\Parser\\'.ucfirst(str_replace('-', '', $slug)).'Parser';

		// load the parser service
		$service = new ProductParser($this->getEntityManager());

		// escape if the specific shop parser doesn't exist
		if (class_exists($class)) {
			$parser = new $class($service);
		} else {
			return null;
		}
		
		$product = $parser->create($url);

		// save it if it doesn't exist already
		$em->persist($product);
		$em->flush();

		return $product;
	}

	public function forceUpdate($product)
	{
		// build the method name for the parser
		$slug = $product->getShop()->getSlug();
		$class = 'ThreadAndMirror\\ProductsBundle\\Parser\\'.ucfirst(str_replace('-', '', $slug)).'Parser';

		// load the parser service
		$service = new ProductParser($this->getEntityManager());

		// escape if the specific shop parser doesn't exist
		if (class_exists($class)) {
			$parser = new $class($service);
		} else {
			return $product;
		}
		
		// parse it and force saving of new version
		$product = $parser->update($product, true);

		// mark as fully parsed and save 
		$product->setFullyParsed(true);
		$product->setChecked(new \DateTime());

		$em = $this->getEntityManager();
		$em->persist($product);
		$em->flush();

		return $product;
	}

	public function findExistingProductIdsByMerchant($merchant) 
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('product');
		$qb->from('ThreadAndMirrorProductsBundle:Product', 'product');
		$qb->innerJoin('product.shop', 'shop');

		// Only search for newly added products
		$qb->andWhere('product.deleted = :deleted');
		$qb->andWhere($qb->expr()->isNull('product.expired'));
		$qb->setParameter('deleted', '0');

		// Filter by shops
		$qb->andWhere('shop.affiliateId = :affiliateId');
		$qb->setParameter('affiliateId', $merchant);
		
		// Get the products in an array to save on memory
		$results = $qb->getQuery()->getScalarResult();

		// Build an array with just pids
		$pids = array();
		foreach ($results as $product) {
			$pids[] = $product['product_pid'];
		}

		// Clear the results array to save memory further
		unset($results);

		return $pids; 
	}
}
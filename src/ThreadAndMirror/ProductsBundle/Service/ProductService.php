<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAware;
use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Repository\ProductRepository;
use ThreadAndMirror\ProductsBundle\Service\Cache\ProductCache;

/**
 * Class ProductService
 * @package ThreadAndMirror\ProductsBundle
 *
 * Container aware, so we can pull in the multitude of updaters in dynamically
 */
class ProductService extends ContainerAware
{
	/** @var EntityManager */
	protected $em;

	/** @var ProductRepository */
	protected $productRepository;

	/** @var ProductCache */
	protected $cache;

	public function __construct(EntityManager $em, ProductRepository $productRepository, ProductCache $cache)
	{
		$this->em                = $em;
		$this->productRepository = $productRepository;
		$this->cache             = $cache;
	}

	/**
	 * Get a product from the url
	 *
	 * @param  string 		$url 		The url of the product page
	 * @return Product
	 */
	public function getProductFromUrl($url)
	{
		// Check which shop the url is from
		$shop = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getShopFromUrl($url);

		if (!$shop) {
			return null;
		}

		// Crawl the product
		if ($shop->isCrawlable()) {

			/** @var Product $product */
			$product = $this->container->get($shop->getUpdaterName())->createProductFromCrawl($url);

			if (!is_object($product)) {
				return null;
			}

			// See if a product already exists with that SKU and shop
			// @todo change this to use cache to find the uid and/or sku (both?)
			$existing = $this->productRepository->findOneBy(['shop' => $shop, 'pid' => $product->getPid()]);

			if ($existing !== null) {
				return $existing;
			}

			$product->setShop($shop);

			return $product;
		}

		return null;
	}

	/**
	 * Update an existing product
	 */
	public function updateProduct($product)
	{
		return $this->getProductFromUrl($product->getUrl());
	}

	/**
	 * Check whether a product already exists
	 *
	 * @param  Product  $product
	 * @return boolean
	 */
	public function checkProductExists(Product $product)
	{
		// Get the cache key specific to the product's shop
		$key = $this->getProductCacheKey($product);


		// Check the cache
		$cached = $this->cache->getData($key);

		return $cached !== false;
	}

	/**
	 * Helper for getting the product cache key
	 *
	 * @param  Product  $product
	 * @return string
	 */
	public function getProductCacheKey(Product $product)
	{
		return $this->container->get($product->getShop()->getUpdaterName())->getProductCacheKey($product);
	}

	/**
	 * Caches the product data
	 *
	 * @param  Product  $product
	 */
	public function cacheProduct(Product $product)
	{
		// Get the cache key specific to the product's shop
		$key = $this->getProductCacheKey($product);

		// Cache the data
		$this->cache->setData($key, $product->getJSON());
	}
}

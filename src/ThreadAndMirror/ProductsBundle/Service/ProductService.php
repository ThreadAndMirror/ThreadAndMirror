<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ThreadAndMirror\ProductsBundle\Entity\Brand;
use ThreadAndMirror\ProductsBundle\Entity\Category;
use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Entity\Shop;
use ThreadAndMirror\ProductsBundle\Event\ProductEvent;
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
	const DEFAULT_LIMIT = 1000;

	/** @var EntityManager */
	protected $em;

	/** @var ProductRepository */
	protected $productRepository;

	/** @var ProductCache */
	protected $cache;

	/** @var BrandService */
	protected $brandService;

	/** @var CategoryService */
	protected $categoryService;

	/** @var EventDispatcherInterface */
	protected $dispatcher;

	public function __construct(
		EntityManager $em,
        ProductRepository $productRepository,
        ProductCache $cache,
        BrandService $brandService,
		CategoryService $categoryService,
		EventDispatcherInterface $dispatcher
	) {
		$this->em                = $em;
		$this->productRepository = $productRepository;
		$this->cache             = $cache;
		$this->brandService      = $brandService;
		$this->categoryService   = $categoryService;
		$this->dispatcher        = $dispatcher;
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
	 * Update a product's brand based on the brand name
	 *
	 * @param  Product 		$product  			The product to update
	 */
	public function updateBrandFromBrandName(Product $product)
	{
		// Get the brand name from the product
		$brandName = $product->getBrandName();
		$brandId   = $this->brandService->getExistingBrandId($brandName);

		// Create a new brand if it doesn't exist already
		if ($brandId !== null) {
			$product->setBrand($this->em->getReference('ThreadAndMirrorProductsBundle:Brand', $brandId));
		} else {
			// Create a new brand
			$brand = new Brand($brandName);
			$this->brandService->createBrand($brand);
			$product->setBrand($brand);
		}
	}

	/**
	 * Update a product brand based on the brand name
	 *
	 * @param  Product 		$product  	The product to update
	 */
	public function updateCategoryFromCategoryName(Product $product)
	{
		// Get the category name from the product
		$name = $product->getCategoryName();
		$id   = $this->categoryService->getExistingCategoryId($name, true);

		// Create a new category if it doesn't exist already
		if ($id === null) {

			// Guess the area if a shop is beauty or fashion only
			$shop = $product->getShop();

			if ($shop->getHasBeauty() && !$shop->getHasFashion()) {
				$area = 'beauty';
			} else if (!$shop->getHasBeauty() && $shop->getHasFashion()) {
				$area = 'fashion';
			} else {
				$area = 'other';
			}

			$category = new Category();
			$category->setName($name);
			$category->setArea($area);
			$this->categoryService->createCategory($category);

		} else {
			$category = $this->categoryService->getCategory('id', $id);
		}

		$product->setCategory($category);
		$product->setArea($category->getArea());
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

	/**
	 * Get products for a specific shop
	 *
	 * @param  Shop         $shop
	 * @param  string       $area
	 * @param  integer      $limit
	 * @return Product[]
	 */
	public function getProductsForShopAndArea(Shop $shop, $area, $limit = self::DEFAULT_LIMIT)
	{
		$this->productRepository->findBy(['shop' => $shop, 'area' => $area], ['added' => 'DESC'], $limit);
	}

	/**
	 * Create a new product
	 *
	 * @param Product     $product
	 */
	public function createProduct(Product $product)
	{
		$this->dispatcher->dispatch(ProductEvent::EVENT_CREATE, new ProductEvent($product));

		$this->em->persist($product);
		$this->em->flush();
	}

	/**
	 * Update a product
	 *
	 * @param Product     $product
	 */
	public function updateProduct(Product $product)
	{
		$this->dispatcher->dispatch(ProductEvent::EVENT_UPDATE, new ProductEvent($product));

		$this->em->persist($product);
		$this->em->flush();
	}
}

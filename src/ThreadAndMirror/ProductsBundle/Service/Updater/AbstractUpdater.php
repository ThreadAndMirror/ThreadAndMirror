<?php

namespace ThreadAndMirror\ProductsBundle\Service\Updater;

use ThreadAndMirror\ProductsBundle\Entity\Category;
use ThreadAndMirror\ProductsBundle\Entity\Shop;
use ThreadAndMirror\ProductsBundle\Event\BrandEvent;
use ThreadAndMirror\ProductsBundle\Event\CategoryEvent;
use ThreadAndMirror\ProductsBundle\Event\ProductNewSizesInStockEvent;
use ThreadAndMirror\ProductsBundle\Event\ProductNowOnSaleEvent;
use ThreadAndMirror\ProductsBundle\Event\ProductFurtherReductionsEvent;
use ThreadAndMirror\ProductsBundle\Exception\ProductParseException;
use ThreadAndMirror\ProductsBundle\Service\CategoryService;
use ThreadAndMirror\ProductsBundle\Service\Crawler\AbstractCrawler;
use ThreadAndMirror\ProductsBundle\Service\Formatter\AbstractFormatter;
use ThreadAndMirror\ProductsBundle\Definition\UpdaterInterface;
use ThreadAndMirror\ProductsBundle\Definition\AffiliateInterface;
use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Entity\Brand;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManager;
use ThreadAndMirror\ProductsBundle\Service\BrandService;
use ThreadAndMirror\ProductsBundle\Service\ProductService;

abstract class AbstractUpdater implements UpdaterInterface
{
	/** @var AbstractCrawler */
	protected $crawler;

	/** @var AbstractFormatter */
	protected $formatter;

	/** @var EventDispatcherInterface */
	protected $dispatcher;

	/** @var AffiliateInterface */
	protected $affiliate;

	/** @var EntityManager */
	protected $em;

	/** @var array */
	protected $cachedCategories = [];

	/** @var ProductService */
	protected $productService;

	/** @var CategoryService */
	protected $categoryService;

	public function __construct(
		AbstractCrawler $crawler,
        AbstractFormatter $formatter,
        EventDispatcherInterface $dispatcher,
        EntityManager $em,
        AffiliateInterface $affiliate,
        ProductService $productService,
		CategoryService $categoryService
	) {
		$this->crawler         = $crawler;
		$this->formatter       = $formatter;
		$this->dispatcher      = $dispatcher;
		$this->affiliate       = $affiliate;
		$this->em 		       = $em;
		$this->productService  = $productService;
		$this->categoryService = $categoryService;
	}

	/**
	 * For updating an existing product by crawling it's url crawled
	 *
	 * @param Product 	$product 	The original product	
	 */
	public function updateProductFromCrawl(Product $product) 
	{ 
		// Run the product crawler
        $new = $this->crawler->crawl($product->getUrl());

        // Don't crawl the product if it has now expired
        if ($new->getExpired() !== null) {
        	$product->setExpired(new \DateTime());
        	return;
        }

        // Tidy up the crawled data
        $this->formatter->cleanupCrawledProduct($new);

		// Static properties
		// $this->updateStyleWith($new, $product);
		$this->updateBrand($new, $product);
		$this->updateCategory($new, $product);
		$this->updateImages($new, $product);
		$this->updatePid($new, $product);
		$this->updateUrl($new, $product);
		$this->updateName($new, $product);
		$this->updateArea($new, $product);

		// Changable properties
		$this->updateSizes($new, $product);
		$this->updatePrices($new, $product);

		// Cleanup
		unset($new);
	}

	/**
	 * For creating a new product from a crawl
	 *
	 * @param  string 	    $url 		The product's url
	 * @throws ProductParseException
	 */
	public function createProductFromCrawl($url) 
	{
		// Find the shop
		$shop = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getShopFromUrl($url);

		if (!$shop) {
			throw new ProductParseException('Shop could not be found for the given url.');
		}

		if (!$shop->isCrawlable()) {
			throw new ProductParseException('Shop is not flagged as crawlable for the given url.');
		}

		// Run the product crawler
		$product = $this->crawler->crawl($url);
		$product->setShop($shop);

		// Tidy up the crawled data
        $this->formatter->cleanupCrawledProduct($product);

		// Check if the product exists after cleanup
		$existing = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->findOneBy(['pid' => $product->getPid(), 'shop' => $shop]);

		if ($existing !== null) {
			return $existing;
		}

        // Add the affiliate url
        $product->setAffiliateUrl($this->affiliate->getAffiliateLink($url));

		// Set the brand and categories from their names
		$this->productService->updateBrandFromBrandName($product);
		$this->productService->updateCategoryFromCategoryName($product);
		$this->productService->createProduct($product);

        return $product;
	}

	/**
	 * For creating a new product from parsed feed data
	 *
	 * @param  array 	$data  		The product data
	 * @param  Shop     $data       The shop the data belongs to
	 */
	public function createProductFromFeed($data, $shop)
	{
		// Instantiate a skeleton product from the data
		$product = new Product();

		$product->setUrl($data['url']);
		$product->setAffiliateUrl($data['affiliate_url']);
		$product->setName($data['name']);
		$product->setBrandName($data['brand']);
		$product->setCategoryName($data['category_name']);
		$product->setPid($data['pid']);
		$product->setDescription($data['description']);
		$product->setShortDescription($data['short_description']);
		$product->setNow($data['now']);
		$product->setWas($data['was']);
		$product->setImages($data['images']);
		$product->setPortraits($data['portraits']);
		$product->setThumbnails($data['thumbnails']);
		$product->setMetaKeywords($data['meta_keywords']);
		$product->setMetaData(json_encode($data['meta_data']));
		$product->setShop($shop);

		// Tidy up the feed data
        $this->formatter->cleanupFeedProduct($product);

		// Set the brand and categories from their names
		$this->productService->updateBrandFromBrandName($product);
		$this->productService->updateCategoryFromCategoryName($product);

		return $product;
	}

	/**
	 * Update sizes for the product
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product	
	 */
	protected function updateSizes(Product $new, Product $product) 
	{ 
		// Update the available sizes if they haven't been already
		if (count($new->getAvailableSizes()) > count($product->getAvailableSizes())) {
			$product->setAvailableSizes($new->getAvailableSizes());
		}

		// Check whether new sizes are in stock
		$sizes = array();

		if (is_array($new->getStockedSizes())) {
			foreach ($new->getStockedSizes() as $size) {
				if (is_array($product->getStockedSizes()) && !in_array($size, $product->getStockedSizes())) {
					$sizes[] = $size;
				} 
			}	
		}

		// Fire an event if new sizes are in stock
		if (!empty($sizes)) {
			$event = new ProductNewSizesInStockEvent($product, $sizes);
			$this->dispatcher->dispatch('product.new_sizes_in_stock', $event);
		}
		
		// Update the stocked sizes
		$product->setStockedSizes($new->getStockedSizes());
	}

	/**
	 * Update the prices and sale status of the product
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product	
	 */
	protected function updatePrices(Product $new, Product $product)
	{
		// Check whether the product is now on sale
		if (!$product->getSale() && $new->getWas() !== null) {
			$event = new ProductNowOnSaleEvent($product);
			$this->dispatcher->dispatch('product.now_on_sale', $event);
			$product->setSale(new \DateTime());
		}

		// Check whether the product sale price has been reduced further
		if ($product->getSale() && $new->getWas() !== null && $new->getNow() !== $product->getNow()) {
			$event = new ProductFurtherReductionsEvent($product, $new);
			$this->dispatcher->dispatch('product.further_reductions', $event);
		}

		// Check whether the product is no longer on sale
		if ($new->getWas() === null) {
			$product->setSale(null);
		}

		// Update the prices
		$product->setWas($new->getWas());
		$product->setNow($new->getNow());
	}

	/**
	 * Update the style recommendations for the product
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product	
	 */
	protected function updateStyleWith(Product $new, Product $product)
	{
		if (count($product->getStyleWith()) != count($new->getStyleWith())) {

			// Cycle through each crawled url to see if we have the products already
			$ids = array();

			foreach ($new->getStyleWith() as $url) {
				$styleWith = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->findOneBy(array('url' => $url));
				if ($styleWith !== null) {
					$ids[] = $styleWith->getId();
				}
			}

			$product->setStyleWith($ids);
		}
	}

	/**
	 * Update the brand for the product
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product	
	 */
	protected function updateBrand(Product $new, Product $product)
	{
		if ($product->getBrand() === null) {
			$product->setBrandName($new->getBrandName());
			$this->productService->updateBrandFromBrandName($product);
		}
	}

	/**
	 * Update the pid for the product
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product
	 */
	protected function updatePid(Product $new, Product $product)
	{
		if ($product->getPid() === null) {
			$product->setPid($new->getPid());
		}
	}

	/**
	 * Update the name of the product
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product
	 */
	protected function updateName(Product $new, Product $product)
	{
		if ($product->getName() === null) {
			$product->setName($new->getName());
		}
	}

	/**
	 * Update the category
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product
	 */
	protected function updateCategory(Product $new, Product $product)
	{
		if ($product->getCategory() === null) {
			$product->setCategoryName($new->getCategoryName());
			$this->productService->updateCategoryFromCategoryName($product);
		}
	}

	/**
	 * Update the area
	 *
	 * @param Product 	$new 		The new product
	 * @param Product 	$product 	The original product
	 */
	protected function updateArea(Product $new, Product $product)
	{
		if ($product->getCategory() === null) {
			$product->setArea($new->getCategory()->getArea());
		}
	}

	/**
	 * Update a product category based on the affiliate category id
	 *
	 * @param  Product 		$product  			The product to update
	 * @param  string 		$affiliateField  	The field of the category id to match
	 *
	 */
	public function updateCategoryFromAffiliateCategoryId(Product $product, $affiliateField)
	{
		// Get the category ID from the product
		$id = $product->getCategoryName();

		if (array_key_exists($id, $this->cachedCategories)) {
			$product->setCategory($this->cachedCategories[$id]);
		}

		$categoryRepository = $this->em->getRepository('ThreadAndMirrorProductsBundle:Category');
		$category = $categoryRepository->findOneBy([$affiliateField => $id]);

		// Create a new category if it doesn't exist already
		if ($category !== null) {
			$this->cachedCategories[$id] = $category;
			$product->setCategory($category);
		} else {
			$category = new Category();
			$setter = 'set'.ucfirst($affiliateField);
			$category->$setter($id);
			$this->em->persist($category);
			$this->em->flush();
			$this->cachedCategories[$id] = $category;
			$product->setCategory($category);
		}
	}

	/**
	 * Update the description for the product
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product	
	 */
	protected function updateDescription(Product $new, Product $product)
	{
		if ($product->getDescription() === null) {
			$product->setDescription($new->getDescription());
		}
	}

	/**
	 * Update the images for the product
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product	
	 */
	protected function updateImages(Product $new, Product $product)
	{
		if ($product->getImages() === null) {
			$product->setImages($new->getImages());
		}

		if ($product->getThumbnails() === null) {
			$product->setThumbnails($new->getThumbnails());
		}
	}

	/**
	 * Update the product url
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product
	 */
	protected function updateUrl(Product $new, Product $product)
	{
		// Replace any affiliate window links
		if (stristr($product->getUrl(), 'awin1.com')) {
			$product->setUrl($new->getUrl());
		}
	}

	/**
	 * Generate the unique cache key for the product, defaulting to shop slug & product id
	 *
	 * @param  Product  $product
	 * @return string
	 */
	public function getProductCacheKey(Product $product)
	{
		return implode('.', [$product->getShop()->getSlug(), $product->getPid()]);
	}
}
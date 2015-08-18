<?php

namespace ThreadAndMirror\ProductsBundle\Service\Updater;

use ThreadAndMirror\ProductsBundle\Entity\Category;
use ThreadAndMirror\ProductsBundle\Event\BrandEvent;
use ThreadAndMirror\ProductsBundle\Event\CategoryEvent;
use ThreadAndMirror\ProductsBundle\Event\ProductNewSizesInStockEvent;
use ThreadAndMirror\ProductsBundle\Event\ProductNowOnSaleEvent;
use ThreadAndMirror\ProductsBundle\Event\ProductFurtherReductionsEvent;
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

	/** @var BrandService */
	protected $brandService;

	/** @var CategoryService */
	protected $categoryService;

	public function __construct(
		AbstractCrawler $crawler,
        AbstractFormatter $formatter,
        EventDispatcherInterface $dispatcher,
        EntityManager $em,
        AffiliateInterface $affiliate,
        BrandService $brandService,
		CategoryService $categoryService
	) {
		$this->crawler         = $crawler;
		$this->formatter       = $formatter;
		$this->dispatcher      = $dispatcher;
		$this->affiliate       = $affiliate;
		$this->em 		       = $em;
		$this->brandService    = $brandService;
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
	 * @param  string 	$url 		The product's url
	 */
	public function createProductFromCrawl($url) 
	{
		// Find the shop
		$shop = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getShopFromUrl($url);

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
		$this->updateBrandFromBrandName($product);
		$this->updateCategoryFromCategoryName($product);

        return $product;
	}

	/**
	 * For creating a new product from parsed feed data
	 *
	 * @param  array 	$data  		The product data
	 */
	public function createProductFromFeed($data)
	{
		// Instantiate a skeleton product from the data
		$new = new Product();

		$new->setUrl($data['url']);
		$new->setAffiliateUrl($data['affiliate_url']);
		$new->setName($data['name']);
		$new->setBrandName($data['brand']);
		$new->setCategoryName($data['category_name']);
		$new->setPid($data['pid']);
		$new->setDescription($data['description']);
		$new->setShortDescription($data['short_description']);
		$new->setNow($data['now']);
		$new->setWas($data['was']);
		$new->setImages($data['images']);
		$new->setPortraits($data['portraits']);
		$new->setThumbnails($data['thumbnails']);
		$new->setMetaKeywords($data['meta_keywords']);

		// Tidy up the feed data
        $this->formatter->cleanupFeedProduct($new);

        // @todo Do some post-processing shit with prices etc. here?

		return $new;
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
			$this->updateBrandFromBrandName($product);
		}
	}

	/**
	 * Update a product brand based on the brand name
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
			$this->em->persist($brand);
			$this->em->flush();
			$product->setBrand($brand);

			$this->dispatcher->dispatch(BrandEvent::EVENT_ADD, new BrandEvent($brand));
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
			$this->updateCategoryFromCategoryName($product);
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
	 * Update a product brand based on the brand name
	 *
	 * @param  Product 		$product  			The product to update
	 */
	public function updateCategoryFromCategoryName(Product $product)
	{
		// Get the category name from the product
		$categoryName = $product->getCategoryName();
		$categoryId   = $this->categoryService->getExistingCategoryId($categoryName);

		// Create a new category if it doesn't exist already
		if ($categoryId !== null) {
			$category = $this->em->getReference('ThreadAndMirrorProductsBundle:Category', $categoryId);
			$product->setCategory($category);
			$product->setArea($category->getArea());
		} else {
			// Create a new category
			$category = new Category($categoryName);

			// Guess the area if a shop is beauty or fashion only
			$shop = $product->getShop();

			if ($shop->getHasBeauty() && !$shop->getHasFashion()) {
				$category->setArea('beauty');
				$product->setArea('beauty');
			} else if (!$shop->getHasBeauty() && $shop->getHasFashion()) {
				$category->setArea('fashion');
				$product->setArea('fashion');
			} else {
				$category->setArea('other');
				$product->setArea('other');
			}

			$this->em->persist($category);
			$this->em->flush();
			$product->setCategory($category);

			$this->dispatcher->dispatch(CategoryEvent::EVENT_ADD, new CategoryEvent($category));
		}
	}

	/**
	 * Update a product category based on the affiliate category id
	 *
	 * @param  Product 		$product  			The product to update
	 * @param  string 		$affiliateField  	The field of the category id to match
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
}
<?php

namespace ThreadAndMirror\ProductsBundle\Service\Affiliate;

use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Entity\Category;
use ThreadAndMirror\ProductsBundle\Definition\AffiliateInterface;
use ThreadAndMirror\ProductsBundle\Service\Api\AffiliateWindowApiService;
use ThreadAndMirror\ProductsBundle\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;

class AffiliateWindowService implements AffiliateInterface 
{
	/**
	 * @var The Product Repository
	 */
	protected $productRepository;

	/**
	 * @var The Affiliate Window Api service
	 */
	protected $api;

	/**
	 * @var The Entity Manager
	 */
	protected $em;

	/**
     * @var The shop to perform actions for
     */
	protected $shop = null;

	public function __construct(AffiliateWindowApiService $api, ProductRepository $productRepository, EntityManager $em)
	{
		$this->productRepository = $productRepository;
		$this->api 				 = $api;
		$this->em 				 = $em;
	}

	/**
	 * Get the affiliate link for a given url
	 *
	 * @param  string 		$merchant 		The merchant ID
	 * @return string  						The affiliate link
	 */
	public function getAffiliateLink($url)
	{
		$this->validateMerchant();

		return 'http://www.awin1.com/awclick.php?mid='.$this->shop->getAffiliateId().'&id=45628&clickref=123456&p='.rawurlencode($url);
	}

	/**
	 * Updates the product listings for the given merchant
	 */
	public function updateProducts() 
	{ 
		$this->validateMerchant();

		// Get the existing product IDs
		$existing = $this->productRepository->findExistingProductIdsByMerchant($this->shop->getAffiliateId());

		// Pull through new unchanged products
		$page    = 0;
		$results = array();

		while ($page < 1) {
			$results = array_merge($results, $this->api->getMerchantProducts($this->shop->getAffiliateId(), $page * 100));
			$page++;
		}

		// Add the new products
		$this->addNewProducts($data, $existing);
	}

	/**
	 * Processes any new products in the given data
	 * 
	 * @param  array 	$data 		Product data from the API
	 * @param  array 	$existing   An list of existing product IDs
	 * @return 						The product IDs that were added
	 */
	public function addNewProducts($data, $existing) 
	{
		// Identify any new products
		$new = array_filter($data, function($product) {
			if (in_array($product->sMerchantProductId, $existing)) {
				return false;
			} else {
				return true;
			}
		});

		// Create product entities and track those that were added
		$added = array();

		foreach ($new as $productData) {
			$productEntity = $this->createProduct($productData);
			$this->em->persist($productEntity);
			$added[] = $productEntity->getPid();
		}

		$this->em->flush();

		return $added;
	}

	/**
	 * Create a new product entity from the given data
	 *
	 * @param  array 	$data  		Data for an individual product
	 * @return Product 				The resulting product entity
	 */
	protected function createProduct($data)
	{
		$product = new Product();

		$product->setShop($shop);
		$product->setCategory($this->getCategory($data['iCategoryId']));
		$product->setPid($data['sMerchantProductId']);
		$product->setName($data['sName']);
		$product->setBrand($data['sBrand']);
		$product->setDescription('<p>'.$data['sDescription'].'</p>');
		$product->setShortDescription($data['sDescription']);
		$product->setUrl($data['sAwDeepLink']);
		$product->setAffiliateUrl($data['sAwDeepLink']);
		$product->setThumbnail($data['sMerchantThumbUrl']);
		$product->setImage($data['sMerchantImageUrl']);
		$product->setNow($data['fPrice']);
		$product->setWas($data['fRrpPrice']);
		$product->setSale(null);
		$product->setLatest(true);

		return $product;
	}

	/**
	 * Get a category entity based on the affiliate category id, adding if necessary
	 *
	 * @param  array 		$id  		Affiliate category id
	 * @return Category 				The resulting category entity
	 */
	protected function getCategory($id)
	{
		// Find the existing category
		$categoryRepository = $this->em->getRepository('ThreadAndMirrorProductsBundle:Category');
		$category           = $categoryRepository->findOneByAffiliateWindowId($id);

		// Create a new category if it doesn't exist already
		if ($category !== null) {
			return $category;
		} else {
			$category = new Category();
			$category->setAffiliateWindowId($id);
			$this->em->persist($category);
			return $category;
		}
	}

	/**
	 * Set the active shop
	 *
	 * @param  integer 		$shop 		The shop entity
	 * @return self 					For chaining
	 */
	public function setShop($shop) 
	{
		$this->shop = $shop;

		return $this;
	}

	/** 
	 * Ensure a vaid merchant is set
	 *
	 * @throws \Exception
	 */
	protected function validateMerchant()
	{
		if ($this->shop === null) {
			throw new \Exception('The shop has not been set for this action.');
		}
	}
}


<?php

namespace ThreadAndMirror\ProductsBundle\Service\Affiliate;

use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Entity\Category;
use ThreadAndMirror\ProductsBundle\Entity\Offer;
use ThreadAndMirror\ProductsBundle\Definition\AffiliateInterface;
use ThreadAndMirror\ProductsBundle\Service\Api\AffiliateWindowApiService;
use ThreadAndMirror\ProductsBundle\Repository\ProductRepository;
use ThreadAndMirror\ProductsBundle\Service\Parser\BaseParser;
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
     * @var For storing loaded parsers
     */
	protected $parsers = array();

	public function __construct(AffiliateWindowApiService $api, ProductRepository $productRepository, EntityManager $em)
	{
		$this->productRepository  = $productRepository;
		$this->api 				  = $api;
		$this->em 				  = $em;
		$this->parsers['default'] = new BaseParser();
	}

	/**
	 * Get the affiliate link for a given url
	 *
	 * @param  string 		$merchant 		The merchant ID
	 * @return string  						The affiliate link
	 */
	public function getAffiliateLink($url)
	{
		return 'http://www.awin1.com/awclick.php?mid='.$shop->getAffiliateId().'&id=45628&clickref=123456&p='.rawurlencode($url);
	}

	/**
	 * Updates the shops using affiliate window
	 */
	public function updateShops() 
	{ 
		// Because they both use the same data
		$this->updateDiscountCodes();
	}

	/**
	 * Updates the discount codes for the given merchant
	 */
	public function updateDiscountCodes() 
	{ 
		$data = $this->api->getMerchantList();

		$shops = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(array('affiliateName' => 'affiliate_window'));

		foreach ($shops as $shop) {
			foreach ($data->oMerchant as $merchant) {
				if ($shop->getAffiliateId() == $merchant->iId) {

					$shop->setLogo($merchant->sLogoUrl);
					$shop->setUrl($merchant->sDisplayUrl);
					$shop->setAffiliateUrl($merchant->sClickThroughUrl);

					if (property_exists($merchant, 'sDescription')) {
						$shop->setDescription($merchant->sDescription);
					}
					if (property_exists($merchant, 'sStrapline')) {
						$shop->setSlogan($merchant->sStrapline);
					}

					$this->em->persist($shop);

					// Update the offers
					if (property_exists($merchant, 'oDiscountCode')) {
						if (is_array($merchant->oDiscountCode)) {
							foreach ($merchant->oDiscountCode as $code) {
								$offer = $this->createOffer($code);
								$offer->setShop($shop);
								$this->em->persist($offer);
							}
						} else {
							$offer = $this->createOffer($merchant->oDiscountCode);
							$offer->setShop($shop);
							$this->em->persist($offer);
						}
					}
				}
			}
		}

		$this->em->flush();
	}

	/**
	 * Updates the product listings for the given merchant
	 *
	 * @param  Shop 	$shop 		The shop to add update products for
	 */
	public function updateProducts($shop) 
	{ 
		// Get the existing product IDs
		$existing = $this->productRepository->findExistingProductIdsByMerchant($shop->getAffiliateId());

		// Pull through new unchanged products
		$page    = 0;
		$results = array();

		while ($page < 1) {
			$data = $this->api->getMerchantProducts($shop->getAffiliateId(), $page * 100);
			$results = array_merge($results, $data->oProduct);
			$page++;
		}

		// Add the new products
		$this->addNewProducts($results, $existing, $shop);
	}

	/**
	 * Processes any new products in the given data
	 * 
	 * @param  array 	$data 		Product data from the API
	 * @param  array 	$existing   An list of existing product IDs
	 * @param  Shop 	$shop 		The shop to add new products to
	 * @return 						The product IDs that were added
	 */
	public function addNewProducts($data, $existing, $shop) 
	{
		// Identify any new products
		foreach ($data as $key => $product) {
			if (in_array($product->sMerchantProductId, $existing)) {
				unset($data[$key]);
			}
		}

		// Create product entities and track those that were added
		$added = array();

		foreach ($data as $product) {
			$entity = $this->createProduct($product, $shop);
			$this->em->persist($entity);
			$added[] = $entity->getPid();
		}

		$this->em->flush();

		return $added;
	}

	/**
	 * Create a new product entity from the given data
	 *
	 * @param  array 	$data  		Data for an individual product
	 * @param  Shop 	$shop 		The shop to add the product to
	 * @return Product 				The resulting product entity
	 */
	protected function createProduct($data, $shop)
	{
		$product = new Product();

		$product->setShop($shop);
		$product->setCategory($this->getCategory($data->iCategoryId));
		$product->setPid($data->sMerchantProductId);
		$product->setName($data->sName);
		$product->setDescription('<p>'.$data->sDescription.'</p>');
		$product->setShortDescription($data->sDescription);
		$product->setUrl($data->sAwDeepLink);
		$product->setAffiliateUrl($data->sAwDeepLink);
		$product->setNow($data->fPrice);
		$product->setWas($data->fRrpPrice);
		$product->setSale(null);
		$product->setNew(true);

		// Details
		if (property_exists($data, 'sBrand')) {
			$product->setBrand($data->sBrand);
		} else {
			$product->setBrand($shop->getName());
		}

		// Images
		if (property_exists($data, 'sMerchantThumbUrl')) {
			$product->setThumbnail($data->sMerchantThumbUrl);
		} else {
			$product->setThumbnail($data->sAwImageUrl);
		}
		if (property_exists($data, 'sMerchantImageUrl')) {
			$product->setImage($data->sMerchantImageUrl);
		} else {
			$product->setImage($data->sAwImageUrl);
		}

		// Cleanup any irregular data
		$this->getParser($shop)->cleanupFeedProduct($product);

		return $product;
	}

	/**
	 * Create a new offer entity from the given data
	 *
	 * @param  array 	$data  		Data for an individual product
	 * @return Offer 				The resulting offer entity
	 */
	protected function createOffer($data)
	{
		$offer = new Offer();

		// Offer code
		if ($data->sCode !== 'N/A') {
			$offer->setCode($data->sCode);
		}

		// Url
		if (property_exists($data, 'sUrl')) {
			$offer->setUrl($data->sUrl);
		}

		$offer->setDescription($data->sDescription);
		$offer->setStart(new \DateTime($data->sStartDate));
		$offer->setEnd(new \DateTime($data->sEndDate));

		return $offer;
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
	 * Gets the parser for the relevant shop
	 *
	 * @param  Shop 	$shop 		The shop to load the parser for  	
	 */
	protected function getParser($shop) 
	{
		if ($shop->getParserClass() !== null) {

			// Check if the parser has already been loaded
			if (array_key_exists($shop->getAffiliateName(), $this->parsers)) {
				return $this->parsers[$shop->getAffiliateName()];
			}
			
			// Otherwise load and store for later use
			$class = 'ThreadAndMirror\\ProductsBundle\\Service\\Parser\\'.$shop->getParserClass();
			$parser = new $class();
			$this->parsers[$shop->getAffiliateName()] = $parser;

			return $parser;

		} else {
			// Default parser if one doesn't exist for the shop
			return $this->parsers['default'];
		}
		
	}
}


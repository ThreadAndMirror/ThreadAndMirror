<?php

namespace ThreadAndMirror\ProductsBundle\Service\Affiliate;

use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Entity\Category;
use ThreadAndMirror\ProductsBundle\Entity\Offer;
use ThreadAndMirror\ProductsBundle\Definition\AffiliateInterface;
use ThreadAndMirror\ProductsBundle\Definition\UpdaterInterface;
use ThreadAndMirror\ProductsBundle\Service\Api\AffiliateWindowApiService;
use ThreadAndMirror\ProductsBundle\Repository\ProductRepository;
use ThreadAndMirror\ProductsBundle\Service\Formatter\AbstractFormatter;
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
     * @var For storing loaded formatters
     */
	protected $formatters = array();

	/**
     * @var For storing product updater
     */
	protected $updater;

	/**
	 * @var The valid product categories for each each
	 */
	protected $categories = array(
		'fashion' => array(595,149,135,163,168,159,169,161,167,170,194,141,205,198,206,203,208,199,204,201,546,547),
		'beauty'  => array(110,111,114)
	);

	/**
	 * @var The available product areas
	 */
	protected $areas = array(
		'fashion',
		'beauty'
	);

	public function __construct(AffiliateWindowApiService $api, ProductRepository $productRepository, EntityManager $em)
	{
		$this->productRepository  = $productRepository;
		$this->api 				  = $api;
		$this->em 				  = $em;
	}

	/**
	 * Download the latest feed files
	 */
	public function downloadFeedFiles()
	{
		// Download feeds for each area
		foreach ($this->areas as $area) {

			// Load the shops that have products for the current area
			$merchants = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getAffiliatesForArea('affiliate_window', $area, true);

			// Get the data
			$data = $this->api->getDataFeed($merchants, $this->categories[$area])->getContent();

			// Decompress
			$data = gzdecode($data);

			// Save CSV data to file
			$csv = fopen(__DIR__.'/../../../../../feeds/affiliate_window/'.$area.'.csv', 'w+');

			fwrite($csv, $data);
			fclose($csv);
		}
	}

	/**
	 * Parse the latest feed files for a shop add new products
	 */
	public function createProductsFromFeed($shop)
	{
		// Download feeds for each area
		foreach ($this->areas as $area) {

			// Load the CSV data from the feed file
			$csv = fopen(__DIR__.'/../../../../../feeds/affiliate_window/'.$area.'.csv', 'r');

			// Get the existing product IDs for the shop
			$existing = $this->productRepository->findExistingProductIdsByMerchant($shop->getAffiliateId());

			// Skip the first line
			$data = fgetcsv($csv, 10000, ',');

			// Start a counter to add new products in chunks
			$count = 0;

			while (($data = fgetcsv($csv, 10000, ',')) !== false) {

				// Only handle if the product belongs to the relevant shop
				if ($data[11] == $shop->getAffiliateId()) {

					// Perform a basic PID check before processing
					if (!in_array($data[1], $existing)) {

						// Prepare homogenized version of the data for parsing
						$data = $this->homogeniseProductData($data);

						// Create and format the product data into an entity
						$product = $this->updater->createProductFromFeed($data);

						// Add the shop and area
						$product->setShop($shop);
						$product->setArea($area);

						// @todo check for duplicates with name etc. here
						// Create uid field?

						$this->em->persist($product);

						$existing[] = $product->getPid();
						$count++;

						echo 'Added product '.$product->getPid().' for shop '.$shop->getName().PHP_EOL;

						if ($count == 1000) {
							$this->em->flush();
							$count = 0;
						}
					} else {
						echo 'Ignoring existing product '.$data[1].PHP_EOL;
					}
				}
			}

			$this->em->flush();

			// Close the file
			fclose($csv);
		}
	}

	/**
	 * Homogenise a product from feed data into a generic format
	 *
	 * @param  array 	$data  		Original row data
	 * @return array 				The homogenised data
	 */
	public function homogeniseProductData($data)
	{
		return array(
			'category_name' 	=> $data[13],
			'pid' 				=> $data[1],
			'name' 				=> $data[7],
			'description' 		=> $data[6],
			'short_description' => $data[6],
			'brand' 			=> $data[24],
			'url' 				=> $data[3],
			'affiliate_url' 	=> $data[3],
			'now' 				=> $data[17],
			'was' 				=> $data[17],
			'meta_keywords' 	=> $data[32],
			'thumbnails' 		=> array($data[26], $data[25]),
			'portraits' 		=> array($data[26], $data[25]),
			'images' 			=> array($data[4], $data[9])
		);
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
	public function updateProductsFromApi($shop) 
	{ 
		// Get the existing product IDs
		$existing = $this->productRepository->findExistingProductIdsByMerchant($shop->getAffiliateId());

		// Pull through new unchanged products
		$page    = 0;
		$results = array();

		while ($page < 1) {
			$data = $this->api->getMerchantProducts($shop->getAffiliateId(), $categories, $page * 100);
			$results = array_merge($results, $data->oProduct);
			$page++;
		}

		// Add the new products
		$this->addNewProductsFromApi($results, $existing, $shop);
	}

	/**
	 * Processes any new products in the given data
	 * 
	 * @param  array 	$data 		Product data from the API
	 * @param  array 	$existing   An list of existing product IDs
	 * @param  Shop 	$shop 		The shop to add new products to
	 * @return 						The product IDs that were added
	 */
	public function addNewProductsFromApi($data, $existing, $shop) 
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
			$entity = $this->createProductFromApi($product, $shop);
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
	protected function createProductFromApi($data, $shop)
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
		$this->getFormatter()->cleanupFeedProduct($product);

		return $product;
	}

// 0 aw_product_id,
// 1 merchant_product_id,
// 2 merchant_category,
// 3 aw_deep_link,
// 4 merchant_image_url,
// 5 search_price,
// 6 description,
// 7 product_name,
// 8 merchant_deep_link,
// 9 aw_image_url,
// 10 merchant_name,
// 11 merchant_id,
// 12 category_name,
// 13 category_id,
// 14 delivery_cost,
// 15 currency,
// 16 store_price,
// 17 display_price,
// 18 data_feed_id,
// 19 colour,
// 20 last_updated,
// 21 stock_quantity,
// 22 in_stock,
// 23 is_for_sale,
// 24 brand_name,
// 25 aw_thumb_url,
// 26 merchant_thumb_url,
// 27 rrp_price,
// 28 specifications,
// 29 product_type,
// 30 model_number,
// 31 parent_product_id,
// 32 keywords,
// 33 reviews,
// 34 number_available,
// 35 large_image,
// 36 average_rating,
// 37 alternate_image

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
		$category           = $categoryRepository->findOneBy(array('affiliateWindowId' => $id));

		// Create a new category if it doesn't exist already
		if ($category !== null) {
			return $category;
		} else {
			$category = new Category();
			$category->setAffiliateWindowId($id);
			$this->em->persist($category);
			$this->em->flush();

			return $category;
		}
	}

	/**
	 * Set the updater service
	 *
	 * @param  UpdaterInterface		$updater 	An updater service
	 */
	public function setUpdater(UpdaterInterface $updater) 
	{
		$this->updater = $updater;
	}
}


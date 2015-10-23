<?php

namespace ThreadAndMirror\ProductsBundle\Service\Affiliate;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Entity\Category;
use ThreadAndMirror\ProductsBundle\Entity\Offer;
use ThreadAndMirror\ProductsBundle\Definition\AffiliateInterface;
use ThreadAndMirror\ProductsBundle\Definition\UpdaterInterface;
use ThreadAndMirror\ProductsBundle\Event\ProductEvent;
use ThreadAndMirror\ProductsBundle\Service\Api\AffiliateWindowApiService;
use ThreadAndMirror\ProductsBundle\Repository\ProductRepository;
use ThreadAndMirror\ProductsBundle\Service\Formatter\AbstractFormatter;
use ThreadAndMirror\ProductsBundle\Service\Updater\AbstractUpdater;
use Doctrine\ORM\EntityManager;
use ThreadAndMirror\ProductsBundle\Service\ProductService;

/**
 * Class AffiliateWindowService
 * @package ThreadAndMirror\ProductsBundle
 *
 */
class AffiliateWindowService implements AffiliateInterface
{
	const KEY_NAME = 'affiliate_window';

	/** @var AffiliateWindowApiService */
	protected $api;

	/** @var EntityManager */
	protected $em;

	/** @var Producer[] */
	protected $producers;

	/** @var Logger */
	protected $logger;

	/** @var ProductService */
	protected $productService;

	/** @var ContainerInterface */
	protected $container;

	/** @var EventDispatcherInterface */
	protected $dispatcher;

	/** @var array */
	protected $categories = [
		'fashion' => [595,149,135,163,168,159,169,161,167,170,194,141,205,198,206,203,208,199,204,201,546,547],
		'beauty'  => [111,114]
	];

	/** @var array */
	protected $areas = ['fashion', 'beauty'];

	/** @var array */
	protected $parameters;

	public function __construct(
		AffiliateWindowApiService $api,
		EntityManager $em,
		Logger $logger,
		ProductService $productService,
		ContainerInterface $container,
		EventDispatcherInterface $dispatcher,
		$producers
	) {
		$this->api 				 = $api;
		$this->em 				 = $em;
		$this->logger            = $logger;
		$this->productService    = $productService;
		$this->container         = $container;
		$this->producers         = $producers;
		$this->dispatcher        = $dispatcher;
		$this->parameters        = ['process_chunk_size' => 100];
	}

	/**
	 * Add feed downloads to the queue
	 */
	public function queueFeedFileDownloads()
	{
		// Download feeds for each category
		foreach ($this->areas as $area) {
			foreach ($this->categories[$area] as $category) {
				$this->producers['download_feed']->publish(json_encode([
					'affiliate' => self::KEY_NAME,
					'category'  => $category,
					'area'      => $area
				]));
			}
		}
	}

	/**
	 * Download a feed file for the given category
	 *
	 * @param  string   $area
	 * @param  string   $category
	 */
	public function downloadFeedFile($area, $category)
	{
		// Load the shops that have products for the current area
		$merchants = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getAffiliatesForArea('affiliate_window', $area, true);

		// Get the data
		$data = $this->api->getDataFeed($merchants, [$category])->getContent();

		// Decompress if there are no errors
		if (stristr($data, '<h1>Error</h1>')) {
			$this->logger->info('Category '.$category.' feed file has no products for affiliate window.');
		} else {
			$data = gzdecode($data);

			// Save CSV data to file
			$csv = fopen(__DIR__.'/../../../../../feeds/affiliate_window/'.$category.'.csv', 'w+');

			fwrite($csv, $data);
			fclose($csv);

			$this->logger->info('Category '.$category.' feed file downloaded for affiliate window.');
		}
	}

	/**
	 * Add feed processing chunks to the queue
	 *
	 * @param  string   $area
	 * @param  string   $category
	 */
	public function queueFeedFileProcessing($area = null, $category = null)
	{
		// Filter categories
		if ($category !== null) {
			$categories = [$category];
		} else if ($area !== null) {
			$categories = $this->categories[$area];
		} else {
			$categories = array_merge($this->categories['fashion'], $this->categories['beauty']);
		}

		// Download feeds for each category
		foreach ($categories as $category) {

			// Load the CSV data from the feed file
			$filename = __DIR__.'/../../../../../feeds/'.self::KEY_NAME.'/'.$category.'.csv';

			if (!file_exists($filename)) {
				continue;
			}

			$csv = fopen($filename, 'r');

			// Skip the first line
			$lineItem = fgetcsv($csv, 10000, ',');

			// Start a counter to queue the product data in chunks
			$count = 0;
			$data  = [];

			while (($lineItem = fgetcsv($csv, 10000, ',')) !== false) {

				// Prepare homogenized version of the data for parsing
				$lineItem = $this->homogeniseProductData($lineItem);

				// Add to the data array
				$data[] = $lineItem;
				$count++;

				if ($count == $this->parameters['process_chunk_size']) {

					$area = in_array($category, $this->categories['fashion']) ? 'fashion' : 'beauty';

					// Add message to the queue for processing
					$this->producers['process_feed']->publish(json_encode([
						'affiliate' => self::KEY_NAME,
						'data'      => $data,
						'category'  => $category,
						'area'      => $area
					]));

					$count = 0;
					$data  = [];
				}
			}

			$area = in_array($category, $this->categories['fashion']) ? 'fashion' : 'beauty';

			// Add message to the queue for processing
			$this->producers['process_feed']->publish(json_encode([
				'affiliate' => self::KEY_NAME,
				'data'      => $data,
				'category'  => $category,
				'area'      => $area
			]));
		}
	}

	/**
	 * Parse the latest feed files for a shop add new products
	 *
	 * @param  string   $area
	 * @param  string   $category
	 * @param  array    $data
	 */
	public function createProductsFromFeedData($data, $area, $category)
	{
		// Get array of shops with merchant ids referencing product ids
		$shops = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(['affiliateName' => 'affiliate_window']);

		foreach ($data as $datum) {

			$owner = null;

			// Get the relevant shop and updater for the product
			foreach ($shops as $shop) {
				if ($shop->getAffiliateId() == $datum['merchant_id']) {
					/** @var AbstractUpdater $updater */
					$updater = $this->container->get($shop->getUpdaterName());
					$owner   = $shop;
					break;
				}
			}

			// No shop found for some reason, so log and skip this product
			if ($owner === null) {
				$this->logger->info('No shop found for product with follow data, skipping: '.json_encode($datum));
				continue;
			}

			// @todo skipper for testing, remove
			if (!in_array($owner->getAffiliateId(), [6009])) {
				echo $owner->getAffiliateId().'.';
				continue;
			} else {
				echo 'Processing: '.memory_get_usage().'B * ';
			}

			// Create the product from the data
			$product = $updater->createProductFromFeed($datum, $owner);

			// Check whether the product already exists
			if ($this->productService->checkProductExists($product)) {
				continue;
			}

			// Set the remaining product data
			$product->setArea($area);

			// Persist if doesn't exist
			$this->em->persist($product);
			$this->logger->info('Product added from feed: '.$this->productService->getProductCacheKey($product));
			$this->dispatcher->dispatch(ProductEvent::EVENT_CREATE, new ProductEvent($product));

			unset($product);
		}

		$this->em->flush();
	}

	/**
	 * Homogenise a product from feed data into a generic format
	 *
	 * @param  array 	$data  		Original row data
	 * @return array 				The homogenised data
	 */
	public function homogeniseProductData($data)
	{
		return [
			'category_name'       => $data[12],
			'pid'                 => $data[1],
			'name'                => $data[7],
			'description'         => $data[6],
			'short_description'   => $data[6],
			'brand'               => $data[24],
			'url'                 => $data[8],
			'affiliate_url'       => $data[3],
			'now'                 => $data[17],
			'was'                 => $data[17],
			'meta_keywords'       => $data[32],
			'thumbnails'          => [$data[26], $data[25]],
			'portraits'           => [$data[26], $data[25]],
			'images'              => [$data[4], $data[9]],
			'merchant_id'         => $data[11],
			'meta_data'           => [
				'sku'   => $data[30]
			]
		];
	}

	/**
	 * Get the affiliate link for a given url
	 *
	 * @param  string 		$merchant 		The merchant ID
	 * @return string  						The affiliate link
	 */
	public function getAffiliateLink($url)
	{
		// Check which shop the url is from
		$shop = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getShopFromUrl($url);
		
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

		$shops = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(['affiliateName' => 'affiliate_window']);

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
	 * Set the updater service
	 *
	 * @param  UpdaterInterface		$updater 	An updater service
	 */
	public function setUpdater(UpdaterInterface $updater) 
	{
		$this->updater = $updater;
	}
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


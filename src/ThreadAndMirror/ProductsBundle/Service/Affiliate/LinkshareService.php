<?php

namespace ThreadAndMirror\ProductsBundle\Service\Affiliate;

use Elastica\AbstractUpdateAction;
use Ijanki\Bundle\FtpBundle\Ftp;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ThreadAndMirror\ProductsBundle\Entity\Shop;
use ThreadAndMirror\ProductsBundle\Definition\AffiliateInterface;
use Doctrine\ORM\EntityManager;
use ThreadAndMirror\ProductsBundle\Event\ProductEvent;
use ThreadAndMirror\ProductsBundle\Service\ProductService;
use ThreadAndMirror\ProductsBundle\Service\Updater\AbstractUpdater;

class LinkshareService implements AffiliateInterface
{
	const KEY_NAME = 'linkshare';

	/** @var Ftp */
	protected $ftp;

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

	/** @var string */
	protected $deepLinkToken = '2B31muHJqQI'; //@todo put in params

	/** @var string */
	protected $siteId = '3146542'; //@todo put in params

	public function __construct(
		Ftp $ftp,
		EntityManager $em,
		Logger $logger,
		ProductService $productService,
		ContainerInterface $container,
		EventDispatcherInterface $dispatcher,
		$producers
	) {
		$this->ftp 				 = $ftp;
		$this->em 				 = $em;
		$this->logger            = $logger;
		$this->productService    = $productService;
		$this->container         = $container;
		$this->producers         = $producers;
		$this->dispatcher        = $dispatcher;
		$this->parameters        = ['process_chunk_size' => 100];
	}

	/**
	 * Get the affiliate link for a given url
	 *
	 * @param  string 		$url
	 * @return string  						The affiliate link
	 */
	public function getAffiliateLink($url)
	{
		// Check which shop the url is from
		$shop = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getShopFromUrl($url);

		return 'http://click.linksynergy.com/deeplink?id='.$this->deepLinkToken.'&mid='.$shop->getAffiliateId().'&murl='.rawurlencode($url);
	}

	/**
	 * Add feed downloads to the queue
	 */
	public function queueFeedFileDownloads()
	{
		// Download feeds for each shop
		$shops = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(['affiliateName' => self::KEY_NAME]);

		foreach ($shops as $shop) {
			$this->producers['download_feed']->publish(json_encode([
				'affiliate'  => self::KEY_NAME,
				'merchantId' => $shop->getAffiliateId()
			]));
		}
	}

	/**
	 * Download a feed file for the given merchant Id
	 *
	 * @param  integer   $merchantId
	 */
	public function downloadFeedFile($merchantId)
	{
		// Connect to linkshare ftp in passive mode
		$this->ftp->connect('aftp.linksynergy.com');
		$this->ftp->login('triciamuldoo', '3ZsRf3yX'); //@todo put in params
		$this->ftp->pasv(true);

		// Construct the filename
		$saveDir  = __DIR__.'/../../../../../feeds/'.self::KEY_NAME.'/';
		$filename = $merchantId.'_'.$this->siteId.'_mp_delta.xml.gz';

		try
		{
			// Attempt to download the feed file
			$this->ftp->get($saveDir.$filename, $filename, FTP_BINARY);

			$buffer = 128000; // read 128kb at a time
			$unzipped = str_replace('.gz', '', $filename);

			// Open our files (in binary mode)
			$gz = gzopen($saveDir.$filename, 'rb');
			$output = fopen($saveDir.$unzipped, 'wb');

			// Keep repeating until the end of the input file
			while (!gzeof($gz)) {
				// Both fwrite and gzread and binary-safe
				fwrite($output, gzread($gz, $buffer));
			}

			fclose($output);
			gzclose($gz);

			$this->logger->info('Linkshare feed file downloaded for merchant id '.$merchantId.'.');

		} catch (\Exception $e) {
			$this->logger->error('Could not download feed file for merchant id '.$merchantId.': '.$e->getMessage());
		}
	}

	/**
	 * Add feed processing chunks to the queue
	 *
	 * @param  integer   $merchantId
	 */
	public function queueFeedFileProcessing($merchantId)
	{
		// Load the relevant xml file
		$xml = simplexml_load_file(__DIR__.'/../../../../../feeds/'.self::KEY_NAME.'/'.$merchantId.'_'.$this->siteId.'_mp_delta.xml');

		$count = 0;
		$data  = [];

		// Parse the product data
		foreach ($xml->product as $lineItem) {

			// Ignore any male products
			if ($lineItem->attributeClass->Gender !== 'Male') {

				// Add the merchant id to the line item for data homogenization
				$lineItem['merchant_id'] = $merchantId;

				// Prepare homogenized version of the data for parsing
				$lineItem = $this->homogeniseProductData($lineItem);

				// Add to the data array
				$data[] = $lineItem;
				$count++;

				if ($count == $this->parameters['process_chunk_size']) {

					// Add message to the queue for processing
					$this->producers['process_feed']->publish(json_encode([
						'affiliate'  => self::KEY_NAME,
						'data'       => $data,
						'merchantId' => $merchantId
					]));

					$count = 0;
					$data  = [];
				}
			}
		}

		// Add message to the queue for processing
		$this->producers['process_feed']->publish(json_encode([
			'affiliate'  => self::KEY_NAME,
			'data'       => $data,
			'merchantId' => $merchantId
		]));
	}

	/**
	 * Parse the latest feed files for a shop add new products
	 *
	 * @param  array    $data
	 * @param  string   $merchantId
	 */
	public function createProductsFromFeedData($data, $merchantId)
	{
		// Get array of shops with merchant ids referencing product ids
		$shop = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findOneBy(['affiliateId' => intval($merchantId)]);

		/** @var AbstractUpdater $updater */
		$updater = $this->container->get($shop->getUpdaterName());

		foreach ($data as $datum) {

			// Create the product from the data
			$product = $updater->createProductFromFeed($datum, $shop);

			// Check whether the product already exists
//			if ($this->productService->checkProductExists($product)) {
//				continue;
//			}

			// Ignore any men's products
			if (in_array($datum['meta_data']['linkshare_primary'], ['MEN', 'SALE-MEN', 'MENS-ACCESSORIES'])) {
				continue;
			}

			// Set the remaining product data
			$product->setArea($product->getCategory()->getArea());

			$this->em->persist($product);
			$this->logger->info('Product added from feed: '.$this->productService->getProductCacheKey($product));
			$this->dispatcher->dispatch(ProductEvent::EVENT_CREATE, new ProductEvent($product));

			unset($product);
		}

		$this->em->flush();
	}

	/**
	 * Homogenise a product from feed data into a generic structure
	 *
	 * @param  array 	$data  		Original row data
	 * @return array 				The homogenised data
	 */
	public function homogeniseProductData($data)
	{
		return [
			'category_name'       => (string) $data->category->secondary,
			'pid'                 => (string) $data['product_id'],
			'name'                => (string) $data['name'],
			'description'         => (string) $data->description->short,
			'short_description'   => (string) $data->description->short,
			'brand'               => (string) $data['manufacturer_name'],
			'url'                 => (string) $data->URL->product,
			'affiliate_url'       => (string) $data->URL->product,
			'now'                 => (string) $data->price->retail,
			'was'                 => (string) $data->price->sale,
			'meta_keywords'       => (string) $data->keywords,
			'thumbnails'          => [(string) $data->URL->productImage],
			'portraits'           => [(string) $data->URL->productImage],
			'images'              => [(string) $data->URL->productImage],
			'merchant_id'         => (string) $data['merchant_id'],
			'meta_data'           => [
				'sku'   => (string) $data['sku_number'],
				'linkshare_primary'   => (string) $data->category->primary
			]
		];
	}
}
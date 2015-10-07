<?php

namespace ThreadAndMirror\ProductsBundle\Service\Affiliate;

use Ijanki\Bundle\FtpBundle\Ftp;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ThreadAndMirror\ProductsBundle\Entity\Shop;
use ThreadAndMirror\ProductsBundle\Definition\AffiliateInterface;
use Doctrine\ORM\EntityManager;
use ThreadAndMirror\ProductsBundle\Service\ProductService;

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
	protected $deepLinkToken = '2B31muHJqQI';

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

		// Download the gzip file for the merchant
		$site      = '3146542'; //@todo put in params
		$saveDir   = __DIR__.'/../../../../../feeds/'.self::KEY_NAME.'/';


		// Construct the filename
		$filename = $merchantId.'_'.$site.'_mp_delta.xml.gz';

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

	public function createProductsFromFeedData($data, $merchantId)
	{

	}

	/**
     * Homogenise data for updaters
     */
	public function homogeniseProductData($data)
	{

	}
}
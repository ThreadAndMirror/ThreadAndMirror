<?php

namespace ThreadAndMirror\ProductsBundle\Service\Affiliate;

use ThreadAndMirror\ProductsBundle\Entity\Shop;
use ThreadAndMirror\ProductsBundle\Definition\AffiliateInterface;
use Doctrine\ORM\EntityManager;
use Buzz\Browser;

class LinkshareService implements AffiliateInterface
{
	/**
	 * @var The Client for making requests
	 */
	protected $client;

	/**
	 * @var The Entity Manager
	 */
	protected $em;

	/**
	 * @var Headers to add to each request
	 */
	protected $headers = array(
		'User-Agent' 		=> 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36',
		'Accept' 			=> 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language' 	=> 'en-gb,en;q=0.5',
		'Accept-Charset' 	=> 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
		'Keep-Alive' 		=> '115',
		'Connection' 		=> 'keep-alive'
	);

	/**
	 * The token for link generator requests
	 */
	protected $deepLinkToken = '2B31muHJqQI';

	public function __construct(Browser $client, EntityManager $em)
	{
		$this->em 	  = $em;
		$this->client = $client;
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
	 * Update products for the active merchant
	 */
	public function createProductsFromFeed($shop)
	{

	}

	/**
     * Homogenise data for updaters
     */
	public function homogeniseProductData($data)
	{

	}
}
<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser,
	Symfony\Component\DomCrawler\Crawler;

class FarfetchParser extends BaseParser
{
	protected $shop = 6;

	protected $categories = array(
		'bags-purses-1' => 3, 
		'shoes-1'       => 2, 
		'jewellery-1'   => 5, 
		'accessories-1' => 4, 
		'clothing-1'    => 1,
	);


	protected $selector = '.listingItemWrap';

	protected $urlSale = 'http://www.farfetch.com/shopping/sale/women/{category}/pv-180/ps-{page}/items.aspx';

	protected $urlLatest = 'http://www.farfetch.com/shopping/newin/women/{category}/pv-60/ps-{page}/items.aspx';
		
	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			$this->product->setUrl($url);
			$this->product->setImage($this->services->getCrawler()->filter('#productZoomImgCarousel > li > img')->eq(0)->attr('src'));

			$designer = ucwords(mb_strtolower($this->services->getCrawler()->filter('#ContentPlaceBody_TemplateBody_hItemTitle')->text(), 'UTF-8'));
			$type = ucwords($this->services->getCrawler()->filter('#productItemDesc .productFriendly')->text());
			$this->product->setName($designer.' '.$type);

			// find the pid
			$pid = explode('.aspx', $this->url);
			$pid = explode('-',reset($pid)); 
			$pid = end($pid);
			$this->product->setPid($pid);

			// only update the thumbnail if we don't have one already
			if ($this->product->getThumbnail() == null) {
				$this->product->setThumbnail($this->product->getImage());
			}
				
			$description = $this->services->getCrawler()->filter('#ContentPlaceBody_TemplateBody_lblDescription')->text();
			$description = explode('Item ID:', $description); 
			$description = reset($description);
			$this->product->setDescription($description);

			// confirm the parsing as successfully
			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Get the stock availability of the product
	 */
	protected function getAvailability()
	{
		try 
		{
			$message = $this->services->getCrawler()->filter('#dvtItemSoldOut')->text();
			$this->product->setAvailable(false);

			return false;
		}
		catch (\Exception $e)
		{
			// if the out of stock message isn't there then it's in stock
			$this->product->setAvailable(true);

			return true;
		}
	}

	/**
	 * Get the sale prices for the product
	 */
	protected function getSalePrices()
	{
		// try parsing the sale prices, otherwise parse as a normal if there are no sale prices nodes
		try 
		{
			$was = str_replace(',', '', $this->services->getCrawler()->filter('#ContentPlaceBody_TemplateBody_lbPrice strike')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $this->services->getCrawler()->filter('#ContentPlaceBody_TemplateBody_lbPrice .saleprice')->text());
			$now = preg_replace('/[^0-9,.]/', '', $now);

			$this->product->setNow($now);
			$this->product->setWas($was);
			$this->product->setSale($this->timestamp);

			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Get the normal price for the product
	 */
	protected function getPrices()
	{
		try
		{
			$now = str_replace(',', '', $this->services->getCrawler()->filter('#ContentPlaceBody_TemplateBody_lbPrice')->text());
			$now = preg_replace('/[^0-9,.]/', '', $now);

			$this->product->setNow($now);
			$this->product->setWas(0);
			$this->product->setSale(null);

			return true;
		}
		catch (\Exception $e) 
		{
			return false;
		}
	}

	/**
	 * Parse the amount of pages
	 */
	protected function getPages()
	{
		try
		{
			// check how many pages need crawling
			$pages = $this->services->getCrawler()->filter('#listingPaging > ul > li'); 
			$pages = count($pages);

			$this->pages = $pages;
		}
		catch (\InvalidArgumentException $e) 
		{
			$this->pages = 1;
		}

		return $this->pages;
	}

	/**
	 * Get the core details of the product if it's being parsed from a list (eg. sales or latest additions)
	 */
	protected function getDetailsList($product, $subcrawler)
	{
		try
		{
			$product->setUrl('http://www.farfetch.com'.$subcrawler->filter('a')->attr('href'));
			$product->setName(ucwords(str_replace('-', '', $subcrawler->filter('.ProductImage')->attr('title'))));
			$product->setThumbnail($subcrawler->filter('.ProductImage')->attr('src'));
			$product->setImage($product->getThumbnail());
					
			// get the pid
			$product->setPid($subcrawler->attr('data-item-id'));

			// confirm the parsing as successfully
			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Get the sale prices for the product if it's being parsed from a list
	 */
	protected function getSalePricesList($product, $subcrawler)
	{
		// try parsing the sale prices, otherwise parse as a normal if there are no sale prices nodes
		try 
		{
			$was = str_replace('£', '', $subcrawler->filter('.listingPrice > strike')->text());
			$was = str_replace(',', '', $was); 
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace('£', '', $subcrawler->filter('.listingPrice > .saleprice')->text());
			$now = str_replace(',', '', $now); 
			$now = preg_replace('/[^0-9,.]/', '', $now);

			$product->setNow($now);
			$product->setWas($was);
			$product->setSale($this->timestamp);

			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}		

	/**
	 * Get the normal price for the product if it's being parsed from a list
	 */
	protected function getPricesList($product, $subcrawler)
	{
		try
		{
			$now = str_replace('£', '', $subcrawler->filter('.listingPrice')->text());
			$now = str_replace(',', '', $now); 
			$now = preg_replace('/[^0-9,.]/', '', $now);

			$product->setNow($now);
			$product->setWas(0);
			$product->setSale(null);

			return true;
		}
		catch (\Exception $e) 
		{
			return false;
		}
	}

	/**
	 * See if we can update the affiliate link for the product
	 */
	protected function updateAffiliateLink($url=null)
	{
		// if a url isn't passed (ie. for indivudal updates) we can get it from the parser
		!$url and $url = $this->url;
		
		$token = 'a252e06b15d22698a35fa865c247924cfc1ef382bda0968e81f273f69562744d';
		$mid = $this->existing->getShop()->getAffiliateId();

		$requestUrl = 'http://getdeeplink.linksynergy.com/createcustomlink.shtml?token='.$token.'&mid='.$mid.'&murl='.$url;
		$crawler = $this->services->crawlCustomUrl($requestUrl);
		$response = $crawler->text();
		
		// triple equals to avoid false results
		if (strpos($response, 'http://') === 0) {
			$this->product->setAffiliateUrl($response);
			return true;
		} 

		return false;
	}
}
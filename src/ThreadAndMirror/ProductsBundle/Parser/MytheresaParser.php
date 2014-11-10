<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser,
	Symfony\Component\DomCrawler\Crawler;

class MytheresaParser extends BaseParser
{
	protected $shop = 11;

	protected $categoriesSale = array(
		'clothing'    => 1, 
		'shoes'       => 2,
		'bags'        => 3,
		'accessories' => 4,
	);	

	protected $selector = 'ul.products-grid > li.item';

	protected $urlSale = 'http://www.mytheresa.com/en-gb/sale/{category}.html?p={page}';

	protected $urlLatest = 'http://www.mytheresa.com/en-gb/new-arrivals/what-s-new-this-week-1.html?category={category}&p={page}&limit=210';
		
	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			// decode the affiliate links 15810
			$this->product->setUrl($this->url);

			// strip the pid from the product url
			$this->product->setPid($this->services->getCrawler()->filter('h3.sku-number')->text()); 
			$this->product->setImage($this->services->getCrawler()->filter('#main-image-image')->attr('src'));

			$designer = $this->services->getCrawler()->filter('.product-main-info .designer-name')->text();
			$type = ucwords(mb_strtolower($this->services->getCrawler()->filter('.product-main-info .product-name')->text(), 'UTF-8'));
			$this->product->setName($designer.' '.$type);

			// only update the thumbnail if we don't have one already
			if ($this->product->getThumbnail() == null) {
				$this->product->setThumbnail(str_replace('image/1000x1000', 'small_image/230x260', $this->product->getImage())); 
			}
				
			$description = $this->services->cleanupString($this->markupDescription($this->services->getCrawler()->filter('#collateral-accordion .overview h5')->text()));
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
	 * Custom function to build html markup into the product description.
	 */
	protected function markupDescription($description)
	{
		// get the list items first
		$bullets = $this->services->getCrawler()->filter('#collateral-accordion .featurepoints > li')->each(function ($node, $i) {
    
			// create a new crawler for the product html and create a new entity for it
			$bullet = new Crawler($node);
			
			return $bullet->text();
		});

		$list = '<ul>';
		foreach ($bullets as $bullet) {
			$list .= '<li>'.$bullet.'</li>';
		}
		$list .= '</ul>';

		// some designers have a biography, handle that if it exists
		$description = '<p>'.$description.'</p>';

		// attach both parts together
		$description = $description.$list;
			
		return $description;
	}

	/**
	 * Get the stock availability of the product
	 */
	protected function getAvailability()
	{
		try 
		{
			$message = $this->services->getCrawler()->filter('#catalog-product-404-hint')->text();
			$this->product->setExpired(new \DateTime());

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
			$was = str_replace(',', '', $this->services->getCrawler()->filter('.price-box > .old-price > .price')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);

			$now = str_replace(',', '', $this->services->getCrawler()->filter('.price-box > .special-price > .price')->text());
			$now = explode('|', $now);
			$now = reset($now);
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
			$now = str_replace(',', '', $this->services->getCrawler()->filter('.price-box > .regular-price > .price')->text());
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
	 * Get the sizes of the product
	 */
	protected function getSizes()
	{
		try 
		{
			// work out all available sizes if we don't have them already
			if (!count($this->product->getAvailableSizes())) {

				$available = $this->services->getCrawler()->filter('ul.sizes > li > a')->each(function ($node, $i) {
    

					// create a new crawler for the size dropdown
					$item = new Crawler($node);
					$size = $item->text();

					// remove availability text
					$size = str_replace(' - add to wishlist', '', $size);

					return $size;
				});

				$this->product->setAvailableSizes($available);
			}
			

			// find only the sizes that are in stock
			$stocked = $this->services->getCrawler()->filter('ul.sizes > li > a')->each(function ($node, $i) {
    
				// create a new crawler for the size dropdown
				$item = new Crawler($node);
				$size = $item->text();

				// only return the size if it doesn't have the unavailable text
				if (!stristr($size, 'wishlist')) {
					return $size;
				}
			});

			$this->product->setStockedSizes($stocked);

			// mark as OOS if there are no sizes left
			if ($this->product->getAvailableSizes() && !$stocked) {
				$this->product->setAvailable(false);
			} else {
				$this->product->setAvailable(true);
			}

			return true;
		}
		catch (\Exception $e)
		{	
			return false;
		}
	}

	/**
	 * Get the style recommendations for the product
	 */
	protected function getStyleWith()
	{
		try 
		{
			// find only the sizes that are in stock
			$styleWith = $this->services->getCrawler()->filter('.product-view-bottom .box-up-sell .item')->each(function ($node, $i) {
    
				// create a new crawler for the size dropdown
				$item = new Crawler($node);
				$product = $item->filter('a.product-image')->attr('href');

				return $product;
			});

			$styleWith = array_filter($styleWith);

			// convert the urls into product
			foreach ($styleWith as $key => $url) {
				
				// store the pid if it exists, or crawl if it doesn't
				$product = $this->services->getManager()->getRepository('ThreadAndMirrorProductsBundle:Product')->findOneByUrl($url);

				if (!$product) {
					$product = $this->services->getManager()->getRepository('ThreadAndMirrorProductsBundle:Product')->getProductFromUrl($url);
				}

				if (is_object($product)) {
					$styleWith[$key] = $product->getId();
				} else { 
					unset($styleWith[$key]);
				}
			}

			$this->product->setStyleWith($styleWith);

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
			$pages = (count($this->services->getCrawler()->filter('.pages > ol > li')) / 2) - 1;

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
			$product->setUrl($subcrawler->filter('a')->attr('href'));
			$product->setName(ucwords(html_entity_decode($subcrawler->filter('h2.designer-name')->text().' '.mb_strtolower($subcrawler->filter('h3.product-name')->text(), 'UTF-8'))));	
			$product->setThumbnail($subcrawler->filter('img.image1st')->attr('src'));
			$product->setImage($product->getThumbnail());
					
			// get the pid from the image name
			try 
			{
				$pid = explode('/', $product->getThumbnail());
				$pid = end($pid);
				$pid = explode('-', $pid);
				$pid = reset($pid);
			}
			catch (\InvalidArgumentException $e) 
			{
				$pid = explode('-', $subcrawler->filter('.price-box > .regular-price')->attr('id'));
				$pid = end($pid);
			}

			$product->setPid($pid);

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
			$was = str_replace(',', '', $subcrawler->filter('.price-box > .old-price > .price')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);


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
			$now = str_replace(',', '', $subcrawler->filter('.price-box > .regular-price > .price')->text());
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
		if ($this->existing) {
			$mid = $this->existing->getShop()->getAffiliateId();
		} else {
			$mid = $this->product->getShop()->getAffiliateId();
		}	
		
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
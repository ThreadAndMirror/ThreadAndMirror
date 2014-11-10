<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser;

class Avenue32Parser extends BaseParser
{
	protected $shop = 14;

	protected $selector = 'div.fnresults > div.item';

	protected $categoriesSale = array(
		'clothing/all-clothing' => 1, 
		'shoes'                 => 2,
		'bags'                  => 3,
		'accessories'           => 4,
		'jewellery'             => 5,
	);	

	protected $urlSale = 'http://www.avenue32.com/sale/all-sale-{category}/?perpageitem=192&listing_page={page}';

	protected $urlLatest = 'http://www.avenue32.com/whats-new?listing_page={page}';
		
	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			// strip the pid from the product url
			$this->product->setUrl($this->url);
			$pid = explode('/', $this->product->getUrl());
			$pid = str_replace('.html', '', end($pid));
			$this->product->setPid($pid);

			$this->product->setImage('http://www.avenue32.com'.$this->services->getCrawler()->filter('.product-image .main-image a')->attr('href'));

			// handle names 
			$designer = $this->services->getCrawler()->filter('.product-info > .brand > a')->text();
			$type = $this->services->getCrawler()->filter('.product-info > h1')->text();
			$this->product->setName($designer.' '.$type);

			// only update the thumbnail if we don't have one already
			if ($this->product->getThumbnail() == null) {
				$this->product->setThumbnail($this->product->getImage());
			}

			$description = $this->markupDescription($this->services->getCrawler()->filter('.product-tabs > div')->eq(0)->text());
			$this->product->setDescription($description);

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
		// strip the sub text
		$description = explode('Email ', $description);
		$description = reset($description);
		$description = explode('Please email ', $description);
		$description = reset($description);

		// add spaces where <br> were present
		$description = preg_replace('/((?<=[A-Za-z0-9])\.(?=[A-Za-z]{2})|(?<=[A-Za-z]{2})\.(?=[A-Za-z0-9]))/', '. ', $description);

		$description = '<p>'.$description.'</p>';
			
		return $description;
	}

	/**
	 * Get the stock availability of the product
	 */
	protected function getAvailability()
	{
		try 
		{
			$message = $this->services->getCrawler()->filter('div#contentcdsfsdfdsfs > h2.heading')->text();
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
			$was = explode('Now', $this->services->getCrawler()->filter('.product-info > p.price')->text());
			$was = str_replace(',', '', reset($was));
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $this->services->getCrawler()->filter('p.price > span.red')->eq(0)->text());
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
			$now = str_replace(',', '', $this->services->getCrawler()->filter('.product-info > p.price')->text());

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
			$elements = count($this->services->getCrawler()->filter('ul.paging > li')) / 2;

			$pages = $this->services->getCrawler()->filter('ul.paging > li')->eq($elements-3)->text();

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
			$product->setUrl('http://www.avenue32.com'.$subcrawler->filter('p.image > a')->attr('href'));
			$product->setThumbnail($subcrawler->filter('p.image > a > img')->eq(0)->attr('data-original'));
			$product->setImage($product->getThumbnail());
					
			// handle names 
			$designer = ucwords(mb_strtolower($subcrawler->filter('h3')->eq(0)->text(), 'UTF-8'));
			$type = $subcrawler->filter('h3')->eq(1)->text();
			$product->setName($designer.' '.$type);

			// get pid
			$pid = explode('/', $product->getUrl());
			$pid = str_replace('.html', '', end($pid));
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
			$was = explode('Now', $subcrawler->filter('p.price')->text());
			$was = str_replace(',', '', reset($was));
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $subcrawler->filter('p.price > span.red')->eq(0)->text());
			$now = preg_replace('/[^0-9,.]/', '', $now);

			$product->setNow($now);
			$product->setWas($was);

			if ($product->getWas()) {
				$product->setSale($this->timestamp);
				return true;
			} else {
				$product->setSale(null);
				return false;
			}	
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
			$now = str_replace(',', '', $subcrawler->filter('p.price')->text());
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
}
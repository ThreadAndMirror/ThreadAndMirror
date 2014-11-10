<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser,
	Symfony\Component\DomCrawler\Crawler;

class ThecornerParser extends BaseParser
{
	protected $shop = 7;

	protected $selector = '.itemThumb';

	protected $urlSale = 'http://www.thecorner.com/gb/women/sale?page={page}';

	protected $urlLatest = 'http://www.thecorner.com/gb/women/new-arrivals?page={page}#ipp=30';
		
	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			$this->product->setUrl($this->url);
			$this->product->setImage($this->services->getCrawler()->filter('.mainImage')->attr('src'));

			$name = ucwords(mb_strtolower($this->services->getCrawler()->filter('.itemData > .title > h2 > a')->text(), 'UTF-8'));
			$name .= ' '.$this->services->getCrawler()->filter('#view_more_category')->text();
			$this->product->setName($name);

			$pid = explode('_cod', $this->url);
			$pid = str_replace('.html', '', end($pid));
			$this->product->setPid($pid); 

			// only update the thumbnail if we don't have one already
			if ($this->product->getThumbnail() == null) {
				$this->product->setThumbnail($this->product->getImage());
			}
			
			// $description = $this->services->getCrawler()->filter('#decrizione')->text();
			$this->product->setDescription($this->product->getName());

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
			$message = $this->services->getCrawler()->filter('div#content > h2.heading')->text();
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
			$was = str_replace(',', '', $this->services->getCrawler()->filter('.itemData .oldprice')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $this->services->getCrawler()->filter('.itemData .hasdiscount')->text());
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
			$now = str_replace(',', '', $this->services->getCrawler()->filter('.itemData span.newprice')->text());
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
	 * Custom function to build html markup into the product description.
	 */
	protected function markupDescription($description)
	{
		// get the list items first
		$bullets = $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_divInvLongDescription > ul > li > ul > li')->each(function ($node, $i) {
    
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
		try 
		{
			$splitter = $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_divInvLongDescription > ul > li > h2')->text();
			$description = explode($splitter, $description);
			$description = '<p>'.end($description).'</p>';
		}
		catch (\Exception $e) 
		{
			$description = '';
		}
		

		// attach both parts together
		$description = $description.$list;
			
		return $description;
	}

	/**
	 * Parse the amount of pages
	 */
	protected function getPages()
	{
		try
		{
			// check how many pages need crawling
			$total = $this->services->getCrawler()->filter('.nbHowMany > b')->text();
			$pages = (($total - ($total % 30)) / 30) + 1;

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
			$product->setName(ucwords(mb_strtolower($subcrawler->filter('.itemBrandAndCat > .brand')->text().' '.$subcrawler->filter('.itemBrandAndCat > .category')->text(), 'UTF-8')));
			$product->setUrl('http://www.thecorner.com'.$subcrawler->filter('a.itemContainer')->attr('href'));
			$product->setThumbnail($subcrawler->filter('a.itemContainer > img')->attr('src'));
			$product->setImage($product->getThumbnail());
					
			// get the pid
			$pid = explode('_cod', $product->getUrl());
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
			$now = preg_replace('/[^0-9,.]/', '', $subcrawler->filter('.newprice')->text());
			$now = str_replace(',', '', $now); 
			$was = preg_replace('/[^0-9,.]/', '', $subcrawler->filter('.oldprice')->text());
			$was = str_replace(',', '', $was); 

			// as the element still exists, check the value to escape procesisng as a sale
			if (!$was) {
				throw new \Exception('Empty previous price field');
			}

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
			$now = preg_replace('/[^0-9,.]/', '', $subcrawler->filter('.newprice')->text());
			$now = str_replace(',', '', $now); 

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
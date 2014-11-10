<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser,
	Symfony\Component\DomCrawler\Crawler;

class LondonboutiquesParser extends BaseParser
{
	protected $shop = 8;

	protected $selector = 'ul.products-grid > li.item';

	protected $urlSale = 'http://www.london-boutiques.com/sale?limit=120&p={page}';

	protected $urlLatest = 'http://www.london-boutiques.com/just-in?limit=120&p={page}';
		
	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			// strip the pid from the product url
			$this->product->setUrl($this->url);
			$this->product->setPid($this->services->getCrawler()->filter('#product_addtocart_form .no-display input')->eq(0)->attr('value'));
			$this->product->setImage($this->services->getCrawler()->filter('#productImageSlides #zoom1')->attr('href'));

			$name = $this->services->getCrawler()->filter('.product-name > h1')->text();
			$name .= ' '.$this->services->getCrawler()->filter('.short-description .std')->eq(0)->text();
			$this->product->setName($name);

			// only update the thumbnail if we don't have one already
			$this->product->setThumbnail($this->product->getImage());

			$this->product->setDescription($this->markupDescription(''));

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
		$bullets = $this->services->getCrawler()->filter('.accordiontab dd')->eq(1)->filter('li')->each(function ($node, $i) {
    
			// create a new crawler for the product html and create a new entity for it
			$bullet = new Crawler($node);
			
			return $bullet->text();
		});

		$list = '<ul>';
		foreach ($bullets as $bullet) {
			$list .= '<li>'.$bullet.'</li>';
		}
		$list .= '</ul>';

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
			$was = str_replace(',', '',$this->services->getCrawler()->filter('.price-box > .old-price > .price')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $this->services->getCrawler()->filter('.price-box > .special-price > .price')->text());
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
			$product->setName(ucwords(html_entity_decode($subcrawler->filter('h2.product-name')->text().' '.$subcrawler->filter('.shortdesc')->text())));
			$product->setThumbnail($subcrawler->filter('.product-image img')->eq(0)->attr('src'));
			$product->setImage($product->getThumbnail());
					
			// sale pid location is slightly different to latest
			try 
			{
				$pid = explode('-', $subcrawler->filter('.price-box > .old-price > .price')->attr('id'));
				$pid = end($pid);
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
			$now = str_replace(',', '', $subcrawler->filter('.price-box > .special-price > .price')->text());
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
}
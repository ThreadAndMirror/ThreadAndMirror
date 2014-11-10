<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser;

class TopshopParser extends BaseParser
{
	protected $shop = 1;

	protected $selector = 'ul.product';

	protected $multiplier = 200;

	protected $urlSale = 'http://www.topshop.com/en/tsuk/category/sale-offers-436/sale-799?pageSize=200&beginIndex={page}';

	protected $urlLatest = 'http://www.topshop.com/en/tsuk/category/new-in-this-week-2169932/new-in-this-week-493?pageSize=200&beginIndex={page}';

	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			// strip the pid from the product url
			$this->product->setPid($this->services->getCrawler()->filter('#catEntryId')->attr('value')); 
			$this->product->setImage($this->services->getCrawler()->filter('#product_view_full > img')->attr('src'));
			$this->product->setName($this->services->getCrawler()->filter('#product_tab_1 > h1')->text());
			$this->product->setUrl($this->url);

			// only update the thumbnail if we don't have one already
			if ($this->product->getThumbnail() == null) {
				$this->product->setThumbnail($this->services->getCrawler()->filter('#product_view_full > img')->attr('src'));
			}
				
			// try and build a description for the page (Asos only has lists, so this may need tweaking)
			try
			{
				$description = $this->services->getCrawler()->filter('#product_tab_1 .product_description')->text();
				$this->product->setDescription($description);
			}
			catch (\Exception $e) 
			{ 
				$this->services->getLogger()->warning('Parsing product description failed for '.$this->url.': '.$e->getMessage());
			}

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
			$stock = $this->services->getCrawler()->filter('#item_out_of_stock')->text();
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
			$was = $this->services->getCrawler()->filter('li.was_price')->text();
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = $this->services->getCrawler()->filter('li.now_price')->text();
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
			$now = $this->services->getCrawler()->filter('li.product_price')->text();
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
			$total = $this->services->getCrawler()->filter('span.product_total')->text();
			$this->pages = (($total - ($total % 200)) / 200) + 1;
		}
		catch (\InvalidArgumentException $e) 
		{
			// if the page node doesn't exist then there's only one page
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
			// populate the new product entity
			$product->setUrl($subcrawler->filter('li.product_image > a')->attr('href'));
			$product->setPid($subcrawler->filter('li.product_image > a')->attr('data-productid'));
			$product->setName(str_replace('**', '', $subcrawler->filter('li.product_description')->text()));

			// some thumbnails don't have the domain prepended, so strip and re-apply to catch all
			$thumbnail = str_replace('http://media.topshop.com', '', $subcrawler->filter('li.product_image > a > img')->attr('src'));
			$product->setThumbnail('http://media.topshop.com'.$thumbnail);
			$product->setImage($product->getThumbnail());

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
			$was = explode('£', $subcrawler->filter('li.was_price')->text());
			$was = $was[1];
			$now = explode('£', $subcrawler->filter('li.now_price')->text());
			$now = $now[1];

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
			$now = $subcrawler->filter('li.product_price')->text();
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
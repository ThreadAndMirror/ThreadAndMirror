<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser,
	Symfony\Component\DomCrawler\Crawler;

class AsosParser extends BaseParser
{
	protected $shop = 4;

	protected $selector = 'ul#items > li';

	protected $urlSale = 'http://www.asos.co.uk/Women/Sale/Designer/Cat/pgecategory.aspx?cid=11625&pge={page}&pgesize=36&sort=-1';

	protected $urlLatest = 'http://www.asos.co.uk/Women/new-in-designer/Cat/pgecategory.aspx?cid=6930&pge={page}&pgesize=36&sort=-1';

	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			// strip the pid from the product url
			$pid = explode('iid=', $this->url);
			$pid = explode('&cid=', end($pid));
			$this->product->setPid(reset($pid));

			$this->product->setImage($this->services->getCrawler()->filter('#ctl00_ContentMainPage_imgMainImage')->attr('src'));
			
			try
			{
				$this->product->setName($this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblProductTitle')->text());
			}
			catch (\Exception $e) 
			{
				$this->product->setName($this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparate1_lblProductTitle')->text());
			}

			$this->product->setUrl($this->url);

			// only update the thumbnail if we don't have one already
			if ($this->product->getThumbnail() == null) {
				$this->product->setThumbnail($this->services->getCrawler()->filter('#ctl00_ContentMainPage_imgMainImage')->attr('src'));
			}

			// try and build a description for the page (Asos only has lists, so this may need tweaking)
			try 
			{
				$description = $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_divInvLongDescription > ul > li')->text();
				$this->product->setDescription($this->markupDescription($description));
			}
			catch (\Exception $e) {}

			// confirm the parsing as successfully
			return true;
		}
		catch (\Exception $e)
		{
			// parsing failed
			$this->services->getLogger()->error('Parsing product details failed for '.$this->url.': '.$e->getMessage());
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
	 * Get the stock availability of the product
	 */
	protected function getAvailability()
	{
		try 
		{
			$stock = $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_pnlOutofStock')->text();
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
			// ASOS has RRP previous price for outlets, so try to handle these first
			try 
			{
				$was = str_replace(',', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblRRP')->text());
				$was = preg_replace('/[^0-9,.]/', '', $was);
				$now = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblProductPrice')->text());
				$now = str_replace(',', '', end($now));	
			}
			catch (\Exception $e)
			{
				// it also has different ids for multi item pages
				try 
				{
					$was = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblProductPreviousPrice')->text());
					$was = preg_replace('/[^0-9,.]/', '', $was);
					$now = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblProductPrice')->text());
					$now = str_replace(',', '', end($now));
				}
				catch (\Exception $e)
				{
					// it also has different ids for multi item pages
					try 
					{
						$was = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblRRP')->text());
						$was = preg_replace('/[^0-9,.]/', '', $was);
						$now = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblProductPrice')->text());
						$now = str_replace(',', '', end($now));
					}
					catch (\Exception $e)
					{
						$was = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparate1_lblProductPreviousPrice')->text());
						$was = preg_replace('/[^0-9,.]/', '', $was);
						$now = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparate1_lblProductPrice')->text());
						$now = str_replace(',', '', end($now));
					}
				}
			}

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
			$now = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblProductPrice')->text());
			$now = str_replace(',', '', end($now));

			// designer items show an RRP price instead, so get that
			if ($now == 0) {
				$now = explode('£', $this->services->getCrawler()->filter('.product_rrp > span')->text());
				$now = str_replace(',', '', end($now));
			}

			$this->product->setNow($now);
			$this->product->setWas(0);
			$this->product->setSale(null);

			return true;
		}
		catch (\Exception $e) 
		{
			// Asos annoyingly has underwear displaying as sets, so we need to handle them differently AGAIN
			try
			{
				$now = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparate1_lblProductPrice')->text());
				$now = str_replace(',', '', end($now));

				$this->product->setNow($now);
				$this->product->setWas(0);
				$this->product->setSale(null);

				return true;
			}
			catch (\Exception $e) 
			{
				// if we couldn't get a price at all then there's a problem - log it an move on
				$this->services->getLogger()->error('Parsing product prices failed for '.$this->url.': '.$e->getMessage());

				return false;
			}
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
			$total = $this->services->getCrawler()->filter('span.total-items')->text();
			$pages = (($total - ($total % 36)) / 36) + 1;

			$this->pages = $pages;
		}
		catch (\InvalidArgumentException $e) 
		{
			// asos needs page set to 0 if there's only one page
			return 0;
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
			$product->setUrl('http://www.asos.com'.$subcrawler->filter('a.productImageLink')->attr('href'));
			$product->setName($subcrawler->filter('img.product-image')->attr('alt'));
			$product->setThumbnail($subcrawler->filter('img.product-image')->attr('src'));
			$product->setImage($product->getThumbnail());

			$pid = explode('iid=', $product->getUrl());
			$pid = explode('&cid=', end($pid));
			$product->setPid(reset($pid));

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
			$was = str_replace(',', '', $subcrawler->filter('.productprice span.price')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $subcrawler->filter('.productprice .previousprice')->text());
			$now = preg_replace('/[^0-9,.]/', '', $now);

			if (!$now) {
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
			$now = str_replace(',', '', $subcrawler->filter('.productprice span.price')->text());
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
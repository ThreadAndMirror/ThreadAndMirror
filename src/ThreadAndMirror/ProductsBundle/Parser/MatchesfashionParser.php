<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser;
use Symfony\Component\DomCrawler\Crawler;

class MatchesfashionParser extends BaseParser
{
	protected $shop = 3;

	protected $selector = 'div.products > div.product';

	protected $urlSale = 'http://www.matchesfashion.com/womens/style-steals?pagesize=60&pagenumber={page}';

	protected $urlLatest = 'http://www.matchesfashion.com/womens/justin/last-7-days?pagesize=60&pagenumber={page}';

	/**
	 * Check the product page exists at all
	 */
	protected function getExpired()
	{
		try 
		{
			$error = $this->services->getCrawler()->filter('.empty-lines')->text();

			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			// strip the pid from the product url
			$this->product->setUrl($this->url);
			$pid = explode('/product/', $this->url);
			$this->product->setPid(end($pid));
			$this->product->setName(ucwords($this->services->getCrawler()->filter('.images img.product-image')->eq(0)->attr('alt')));

			// the big images are in a random order, so find the main one
			$images = $this->services->getCrawler()->filter('a.zoom img')->each(function ($node, $i) {		
    			// get the image url from the node
				$subcrawler = new Crawler($node);
				return $subcrawler->attr('src');
			});
			
			foreach ($images as $image) {
				if (stristr($image, '_1_')) {
					$this->product->setImage($image);
				}
			}

			// only update the thumbnail if we don't have one already
			if (!$this->product->getThumbnail()) {
				$thumbnail = $this->getThumbnailFromImage($this->product->getImage());
				$this->product->setThumbnail($thumbnail);
			}
				
			// try and build a description for the page
			$description = $this->services->getCrawler()->filter('.v-panels .scroll p')->eq(0)->text();
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
			$message = $this->services->getCrawler()->filter('span.soldout')->text();

			if ($message) {
				$this->product->setAvailable(false);
				return false;
			} else {
				$this->product->setAvailable(true);
				return true;
			}
			
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
			$was = explode('£', $this->services->getCrawler()->filter('.pricing')->eq(0)->filter('div.price > span.full')->text());
			$was = str_replace(',', '', end($was));
			$now = explode('£', $this->services->getCrawler()->filter('.pricing')->eq(0)->filter('div.price > span.sale')->text());
			$now = str_replace(',', '', end($now));

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
			$now = str_replace(',', '', $this->services->getCrawler()->filter('.pricing')->eq(0)->filter('div.price')->text());
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

				$available = $this->services->getCrawler()->filter('.buy select.size')->eq(0)->filter('option')->each(function ($node, $i) {
    
					// create a new crawler for the size dropdown
					$item = new Crawler($node);
					$size = explode('-', $item->text());
					$size = reset($size);

					return $size;
				});

				// remove the label field
				foreach ($available as $key => $size) {
					if ($size == 'SELECT SIZE') {
						unset($available[$key]);
					}
				}

				$this->product->setAvailableSizes($available);
			}
			

			// find only the sizes that are in stock
			$stocked = $this->services->getCrawler()->filter('.buy select.size')->eq(0)->filter('option')->each(function ($node, $i) {
    
				// create a new crawler for the size dropdown
				$item = new Crawler($node);
				return $item->text();
			});

			// remove the label field and those out of stock
			foreach ($stocked as $key => $size) {
				if (stristr($size, '-') || $size == 'SELECT SIZE') {
					unset($stocked[$key]);
				}
			}

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
			$styleWith = $this->services->getCrawler()->filter('#styled-with-tab-content-container .product')->each(function ($node, $i) {
    
				// create a new crawler for the size dropdown
				$item = new Crawler($node);
				$product = $item->attr('data-wpc');

				return $product;
			});

			$styleWith = array_filter($styleWith);

			// convert the pids into products
			foreach ($styleWith as $key => $pid) {
				
				// store the pid if it exists, or crawl if it doesn't
				$product = $this->services->getManager()->getRepository('ThreadAndMirrorProductsBundle:Product')->findOneByPid($pid);

				if (!$product) {
					$product = $this->services->getManager()->getRepository('ThreadAndMirrorProductsBundle:Product')->getProductFromUrl('http://www.matchesfashion.com/product/'.$pid);
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
			$pages = $this->services->getCrawler()->filter('div.pager > a');
			$pages = count($pages);
			$this->pages = $this->services->getCrawler()->filter('div.pager > a')->eq($pages-2)->text();
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
			$product->setUrl('http://www.matchesfashion.com'.$subcrawler->filter('div.product > a')->eq(2)->attr('href'));
			$product->setName(ucwords($subcrawler->filter('h4.designer')->text().' '.$subcrawler->filter('div.description')->text()));
			$product->setThumbnail($subcrawler->filter('img.product-image')->attr('src'));
			$product->setImage($product->getThumbnail());

			// strip the pid from the product url
			$pid = explode('/', $product->getUrl());
			$product->setPid(end($pid));

			// confirm the parsing as successful
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
			$was = explode('£', $subcrawler->filter('div.details > div.price > span.full')->text());
			$was = str_replace(',', '', end($was));
			$now = explode('£', $subcrawler->filter('div.details > div.price > span.sale')->text());
			$now = str_replace(',', '', end($now));

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
			$now = str_replace(',', '', $subcrawler->filter('div.details > div.price')->text());
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
	 * calculate the thumbnail image url using the image url
	 */
	public function getThumbnailFromImage($image)
	{
		$thumbnail = str_replace('large', 'medium', $image);
		$thumbnail = str_replace('zoom', 'medium', $thumbnail);

		return $thumbnail;
	}
}
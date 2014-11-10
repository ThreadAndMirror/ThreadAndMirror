<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser;

class WhistlesParser extends BaseParser
{
	protected $shop = 13;

	protected $selector = '#product_list > li';

	protected $urlSale = 'http://www.whistles.co.uk/fcp/categorylist/dept/sale';

	protected $urlLatest = 'http://www.whistles.co.uk/fcp/categorylist/new/in';
	
	/**
	 * Check the product page exists at all
	 */
	protected function getExpired()
	{
		try 
		{
			$error = $this->services->getCrawler()->filter('#productInfo')->text();
			return false;
		}
		catch (\Exception $e)
		{
			return true;

		}
	}

	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			$this->product->setUrl($this->url);

			// strip the pid from the product url
			$pid = explode('/', $this->url);
			$pid = end($pid);
			$this->product->setPid($pid); 

			$image = $this->services->getCrawler()->filter('.currentImage img')->attr('src');
			$image = str_replace('small', 'large', $image);
			$image = 'http://www.whistles.co.uk/'.$image;
			$this->product->setImage($image);

			$designer = '';
			$type = ucwords(mb_strtolower($this->services->getCrawler()->filter('#productInfo > h1')->text(), 'UTF-8'));
			$this->product->setName($designer.' '.$type);

			// only update the thumbnail if we don't have one already
			if ($this->product->getThumbnail() == null) {
				$this->product->setThumbnail($this->services->getCrawler()->filter('#product_view_full > img')->attr('src'));
			}

			$description = $this->services->getCrawler()->filter('#prodTabs_StyleNotes > p')->text();
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
			$was = str_replace(',', '', $this->services->getCrawler()->filter('#')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $this->services->getCrawler()->filter('#product_now_price')->text());
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
			$now = str_replace(',', '', $this->services->getCrawler()->filter('#price')->text());
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
	 * Get the core details of the product if it's being parsed from a list (eg. sales or latest additions)
	 */
	protected function getDetailsList($product, $subcrawler)
	{
		try
		{	
			$product->setUrl($subcrawler->filter('a.image_link')->attr('href'));

			// $thumbnail = str_replace('fr.', 'fco.', $subcrawler->filter('a.image_link img')->attr('src'));
			$thumbnail = $subcrawler->filter('a.image_link img')->attr('src');
			$thumbnail = 'http://www.whistles.co.uk'.$thumbnail;

			$product->setThumbnail($thumbnail);
			$product->setImage($thumbnail);
			$product->setName($subcrawler->filter('a.image_link')->attr('title'));
					
			$pid = str_replace('product_', '', $subcrawler->attr('id'));

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
			$was = str_replace(',', '', $subcrawler->filter('.product_info_price > a > span')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $subcrawler->filter('.product_info_price > a')->text());
			$now = explode('was', $now);
			$now = preg_replace('/[^0-9,.]/', '', reset($now));

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
			$now = str_replace(',', '', $subcrawler->filter('.product_info_price > a')->text());
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
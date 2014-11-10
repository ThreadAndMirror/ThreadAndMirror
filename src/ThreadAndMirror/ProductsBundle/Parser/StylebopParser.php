<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser;

class StylebopParser extends BaseParser
{
	protected $shop = 12;

	protected $selector = '.search_productitem';

	protected $urlSale = 'http://www.stylebop.com/uk/women/{category}/';

	protected $categoriesSale = array(
		'clothing/sale/cashmere'   => 1, 
		'clothing/sale/beachwear'  => 1,
		'clothing/sale/coats'      => 1,
		'clothing/sale/dresses'    => 1,
		'clothing/sale/jackets'    => 1,
		'clothing/sale/jeans'      => 1,
		'clothing/sale/knitwear'   => 1,
		'clothing/sale/pants'      => 1,
		'clothing/sale/skirts'     => 1,
		'clothing/sale/sportswear' => 1,
		'clothing/sale/t_shirts'   => 1,
		'clothing/sale/tops'       => 1,
		'shoes/sale/all'           => 2,
		'bags/sale/all'            => 3,
		'accessories/sale/all'     => 4,
	);

	protected $urlLatest = 'http://www.stylebop.com/uk/women/arrivals/';
		
	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			$this->product->setUrl($this->url);

			// strip the pid from the product url
			$pid = explode('id=', $this->url);
			$pid = end($pid);
			$this->product->setPid($pid);

			// name
			$designer = ucwords(mb_strtolower($this->services->getCrawler()->filter('#productInfo .caption_designer')->text(), 'UTF-8'));
			$type = ucwords($this->services->getCrawler()->filter('#productInfo .Text5')->text());
			$this->product->setName($designer.' '.$type);

			$this->product->setImage('http://www.stylebop.com'.$this->services->getCrawler()->filter('#product_image_front img')->eq(0)->attr('src'));

			// only update the thumbnail if we don't have one already
			if ($this->product->getThumbnail() == null) {
				$this->product->setThumbnail($this->product->getImage());
			}

			// pass am emtpy description because there's only bullets
			$this->product->setDescription($this->markupDescription(''));

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
		// all the descriptions are in table cells, so get the goodness out
		$bullets = array();
		for ($i=0; $i < count($this->services->getCrawler()->filter('#product_details_data .productlisting')); $i++) { 
			$bullets[] = $this->services->getCrawler()->filter('#product_details_data .productlisting')->eq($i)->text();
		}

		// create the list from the elements that aren't empty
		$description = '<ul>';
		foreach ($bullets as $bullet) {
			if ($bullet) {
				$description .= '<li>'.$bullet.'</li>';
			}
		}
		$description .= '</ul>';
			
		return $description;
	}

	/**
	 * Get the stock availability of the product
	 */
	protected function getAvailability()
	{
		try 
		{
			$message = $this->services->getCrawler()->filter('.search_noproducts')->text();
			$this->product->setAvailable(false);

			return false;
		}
		catch (\Exception $e) { }

		// if the out of stock message isn't there then it's in stock
		$this->product->setAvailable(true);

		return true;
	}

	/**
	 * Get the sale prices for the product
	 */
	protected function getSalePrices()
	{
		// try parsing the sale prices, otherwise parse as a normal if there are no sale prices nodes
		try 
		{
			$was = str_replace(',', '', $this->services->getCrawler()->filter('#product_price .old_price')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = explode('(', $this->services->getCrawler()->filter('#product_price .sale_price')->text());
			$now = reset($now);
			$now = str_replace(',', '', $now);
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
			$now = str_replace(',', '', $this->services->getCrawler()->filter('#product_price')->text());
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
			$product->setUrl($subcrawler->filter('a')->attr('href'));
			$product->setThumbnail('http://www.stylebop.com'.$subcrawler->filter('.search_thumb img')->attr('src'));
			$product->setImage($product->getThumbnail());

		 	$name = ucwords(mb_strtolower($subcrawler->filter('.search_designer a')->text(), 'UTF-8')).' ';
			$name .= str_replace('- STYLEBOP.com Exclusive -', '', $subcrawler->filter('.search_article a')->text());
			
			$product->setName($name);
					
			$pid = explode('id=', $product->getUrl());
			$pid = explode('&', end($pid));
			$pid = reset($pid);
			
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
			$was = str_replace(',', '', $subcrawler->filter('.search_price > span')->eq(0)->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $subcrawler->filter('.sale_price')->text());
			$now = explode('(', $now);
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
			$now = str_replace(',', '', $subcrawler->filter('.search_price > span')->text());
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
<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser;

class BrownsParser extends BaseParser
{
	protected $shop = 5;

	protected $categoriesSale = array(
		'clothing/dresses' => 1, 
		'clothing/tops' => 1, 
		'clothing/shorts' => 1, 
		'clothing/coats' => 1, 
		'clothing/skirts' => 1, 
		'clothing/jackets' => 1, 
		'clothing/jeans' => 1, 
		'clothing/knitwear' => 1,
		'clothing/swimwear' => 1,
		'clothing/hosiery_and_leggings' => 1,
		'clothing/t-shirts' => 1,
		'clothing/jumpsuits' => 1,
		'clothing/trousers' => 1,
		'accessories/bags' => 3,
		'accessories/shoes_and_boots' => 2,
		'accessories/jewellery' => 5,
		'accessories/other_accessories' => 4,
	);

	protected $selector = 'a.itm';

	protected $urlSale = 'http://www.brownsfashion.com/products/sale-women/{category}';

	protected $urlLatest = 'http://www.brownsfashion.com/products/whats_new/whats_new/clothing/whats_new_for_women';
		
	/**
	 * Check the product page exists at all
	 */
	protected function getExpired()
	{
		try 
		{
			$error = $this->services->getCrawler()->filter('h1')->text();

			if ($error == 'PRODUCT NO LONGER AVAILABLE') {
				return true;
			} else {
				return false;
			}
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

			$image = $this->services->getCrawler()->filter('.p_ti > img')->eq(0)->attr('src');
			$image = str_replace('160x198', '670x830', $image);
			$this->product->setImage($image);
			
			// handle names 
			$designer = ucwords(mb_strtolower($this->services->getCrawler()->filter('.product-manufacturer-name')->text(), 'UTF-8'));
			$type = $this->services->getCrawler()->filter('.product-title')->text();
			$this->product->setName($designer.' '.$type);

			// strip the pid from the product url
			$pid = explode('/product/', $this->url);
			$pid = explode('/', end($pid));
			$this->product->setPid(reset($pid));

			// we can get the thumbnail using the image url
			if ($this->product->getThumbnail() == null) {
				$this->product->setThumbnail(str_replace('670x830', '338x410', $this->product->getImage()));
			}
				
			$description = $this->services->cleanupString($this->services->getCrawler()->filter('.p_ds')->text());
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
		// wrap the text in a paragraph if it doesn't already contain markup
		$description = str_replace('(continue reading)', '', $description);
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
			$was = str_replace(',', '', $this->services->getCrawler()->filter('span.p_pr > span.currency-value')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $this->services->getCrawler()->filter('span.p_sale > span.currency-value')->text());
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
			$now = str_replace(',', '', $this->services->getCrawler()->filter('.p_p > span.currency-value')->text());
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
			$product->setUrl('http://www.brownsfashion.com'.$subcrawler->attr('href'));
			$product->setName(ucwords(mb_strtolower(htmlspecialchars_decode($subcrawler->filter('h5')->text().' '.$subcrawler->filter('h6')->text(), ENT_QUOTES), 'UTF-8')));
			$product->setThumbnail($subcrawler->filter('img')->attr('src'));
			$product->setImage($product->getThumbnail());

			// strip the pid from the product url
			$pid = explode('/product/', $product->getUrl());
			$pid = explode('/', end($pid));
			$product->setPid(reset($pid));

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
			$was = str_replace(',', '', $subcrawler->filter('span.p_pr > span.currency-value')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $subcrawler->filter('span.p_sale > span.currency-value')->text());
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
			$now = str_replace(',', '', $subcrawler->filter('div.i_p > span.currency-value')->text());
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
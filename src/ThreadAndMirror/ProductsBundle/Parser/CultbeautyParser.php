<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use ThreadAndMirror\ProductsBundle\Parser\BaseParser,
	Symfony\Component\DomCrawler\Crawler;

class CultbeautyParser extends BaseParser
{
	protected $shop = 17;

	/**
	 * Check the product page exists at all
	 */
	protected function getExpired()
	{
		try 
		{
			// $error = $this->services->getCrawler()->filter('.empty-lines')->text();

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
		// try
		// {
		// 	// strip the pid from the product url
		// 	$this->product->setUrl($this->url);
		// 	$pid = explode('/product/', $this->url);
		// 	$this->product->setPid(end($pid));
		// 	$this->product->setName(ucwords($this->services->getCrawler()->filter('.images img.product-image')->eq(0)->attr('alt')));

		// 	// the big images are in a random order, so find the main one
		// 	$images = $this->services->getCrawler()->filter('a.zoom img')->each(function ($node, $i) {		
  //   			// get the image url from the node
		// 		$subcrawler = new Crawler($node);
		// 		return $subcrawler->attr('src');
		// 	});
			
		// 	foreach ($images as $image) {
		// 		if (stristr($image, '_1_')) {
		// 			$this->product->setImage($image);
		// 		}
		// 	}

		// 	// only update the thumbnail if we don't have one already
		// 	if (!$this->product->getThumbnail()) {
		// 		$thumbnail = $this->getThumbnailFromImage($this->product->getImage());
		// 		$this->product->setThumbnail($thumbnail);
		// 	}
				
		// 	// try and build a description for the page
		// 	$description = $this->services->getCrawler()->filter('.v-panels .scroll p')->eq(0)->text();
		// 	$this->product->setDescription($description);

		// 	// confirm the parsing as successfully
		// 	return true;
		// }
		// catch (\Exception $e)
		// {
			return false;
		// }
	}

	/**
	 * Get the stock availability of the product
	 */
	protected function getAvailability()
	{
		// try 
		// {
		// 	$message = $this->services->getCrawler()->filter('span.soldout')->text();

		// 	if ($message) {
		// 		$this->product->setAvailable(false);
		// 		return false;
		// 	} else {
		// 		$this->product->setAvailable(true);
		// 		return true;
		// 	}
			
		// }
		// catch (\Exception $e)
		// {
		// 	// if the out of stock message isn't there then it's in stock
		// 	$this->product->setAvailable(true);
			return true;
		// }
	}

	/**
	 * Get the sale prices for the product
	 */
	protected function getSalePrices()
	{
		// try parsing the sale prices, otherwise parse as a normal if there are no sale prices nodes
		try 
		{
			// $was = explode('£', $this->services->getCrawler()->filter('.pricing > div.price > span.full')->text());
			// $was = str_replace(',', '', end($was));
			// $now = explode('£', $this->services->getCrawler()->filter('.pricing > div.price > span.sale')->text());
			// $now = str_replace(',', '', end($now));

			// $this->product->setNow($now);
			// $this->product->setWas($was);
			// $this->product->setSale($this->timestamp);

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
			// $now = str_replace(',', '', $this->services->getCrawler()->filter('.pricing > div.price')->text());
			// $now = preg_replace('/[^0-9,.]/', '', $now);

			// $this->product->setNow($now);
			// $this->product->setWas(0);
			// $this->product->setSale(null);

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
		$thumbnail = $image;

		return $thumbnail;
	}
}
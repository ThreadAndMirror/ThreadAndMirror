<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class YoungBritishDesignersFormatter extends AbstractFormatter
{
	protected function cleanupCrawledUrl(Product $product) 
	{
		$result = $this->format($product->getUrl())->prepend('http://www.youngbritishdesigners.com')->result();
		$product->setUrl($result);
	}

	protected function cleanupCrawledName(Product $product) 
	{ 
		
	}

	protected function cleanupCrawledBrand(Product $product) 
	{ 
		$result = $this->format($product->getBrandName())->name()->result();
		$product->setBrandName($result);
	}

	protected function cleanupCrawledPid(Product $product)
	{
		$result = $this->format($product->getPid())->sheer('?order=')->sheer('&quant', false)->result();
		$product->setPid($result);
	}

	protected function cleanupCrawledDescription(Product $product) 
	{ 

	}
	
	protected function cleanupCrawledImages(Product $product) 
	{
		$result = $this->format($product->getThumbnails())->prepend('http://www.youngbritishdesigners.com')->replace('_thumb', '_485')->result();
		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this->format($product->getThumbnails())->prepend('http://www.youngbritishdesigners.com')->replace('_thumb', '_main')->result();
		$product->setPortraits($result);
	}

	protected function cleanupCrawledThumbnails(Product $product) 
	{
		$result = $this->format($product->getThumbnails())->prepend('http://www.youngbritishdesigners.com')->result();
		$product->setThumbnails($result);
	}

	protected function cleanupCrawledAvailableSizes(Product $product) 
	{

	}

	protected function cleanupCrawledStockedSizes(Product $product) 
	{ 

	}

	protected function cleanupCrawledStyleWith(Product $product) 
	{ 

	}
}
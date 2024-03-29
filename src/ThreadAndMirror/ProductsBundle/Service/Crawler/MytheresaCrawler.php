<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class MytheresaCrawler extends AbstractCrawler
{
	protected function getExpired(DomCrawler $crawler) 
	{
		// Page not found
		// try {
		// 	if ($this->getTextFromElement($crawler, '#breadcrumb > ul > li > a', 'last') === 'Page Not Found') {
		// 		return true;
		// 	} else {
		// 		return false;
		// 	}
		// } catch (\Exception $e) { }
		
		// // Redirected back to category page
		// try {
		// 	if ($this->getTextFromElement($crawler, '.filteroptions') !== null) {
		// 		return true;
		// 	}
		// } catch (\Exception $e) {
			return false;
		// }
	}

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.product-name span');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.product-designer span a');
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.breadcrumbs li a', 3);
	}

	protected function getPid(DomCrawler $crawler) 
	{
			return $this->getTextFromElement($crawler, '.product-sku .h1');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.product-description');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.price-box .regular-price .price', '.price-box .special-price .price');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.price-box .old-price .price', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.product-image-gallery img');
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.product-image-gallery img');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.product-image-gallery img');
	}

//	protected function getAvailableSizes(DomCrawler $crawler)
//	{
//		return $this->getTextFromList($crawler, 'ul.sizes li a');
//	}
//
//	protected function getStockedSizes(DomCrawler $crawler)
//	{
//		return $this->getTextFromList($crawler, 'ul.sizes li a');
//	}

	protected function getStyleWith(DomCrawler $crawler) 
	{
		return null;
	}
}
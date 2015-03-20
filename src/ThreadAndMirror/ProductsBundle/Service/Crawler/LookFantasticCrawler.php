<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class LookFantasticCrawler extends AbstractCrawler
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

	protected function getUrl(DomCrawler $crawler) 
	{
		return $this->getAttributeFromElement($crawler, '#aspnetForm', 'action');
	}

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h1#product_title');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#product_breadcrumb ul > li > a', -2);
	}

	protected function getPid(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#product_code span');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#product_intro p');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '#product_price ins', '#product_price .product_price');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '#product_price .sale_price .sale', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getHrefFromList($crawler, '#main_image');
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#main_image');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#main_image');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#selSize option');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#selSize option');
	}

	protected function getStyleWith(DomCrawler $crawler) 
	{
		return null;
	}
}
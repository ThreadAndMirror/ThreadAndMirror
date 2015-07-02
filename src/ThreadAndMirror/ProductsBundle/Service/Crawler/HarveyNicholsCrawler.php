<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class HarveyNicholsCrawler extends AbstractCrawler
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
		return $this->getTextFromElement($crawler, '.product-main-info h1 strong');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.product-main-info h1 a');
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.breadcrumbs ul li a.last');
	}

	protected function getPid(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.product-main-info h1 .product-ids');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromList($crawler, '#product-details .std-description ul:first-child li');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '#product-main-info .regular-price .price', '#product_price');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '#product-main-info .sale-price .price', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#imageslider li img');
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#imageslider li img');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#imageslider li img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#product-options-wrapper script:nth-child(3)');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#product-options-wrapper script:nth-child(3)');
	}

	protected function getStyleWith(DomCrawler $crawler)
	{
		return null;
	}
}
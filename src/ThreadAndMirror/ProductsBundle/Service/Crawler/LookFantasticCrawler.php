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

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h1.product-title');
	}

	protected function getBrandName(DomCrawler $crawler)
	{
		return $this->getAttributeFromElement($crawler, '.product-brand-logo img', 'alt');
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#product_breadcrumb ul > li > a', -2);
	}

	protected function getPid(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'input[name=prodId]');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.product-info p:first-child');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.product-price .price');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.saving-percent span', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.main-product-image a img');
	}
}
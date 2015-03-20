<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class FarfetchCrawler extends AbstractCrawler
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
		return null;
	}

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h2.detail-name');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h1.detail-brand a');
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getPid(DomCrawler $crawler) 	
	{
		return $this->getValueFromElement($crawler, '#ProductId');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.productDetailModule-accordion-content p', 0);
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.price .listing-sale', '.price .listing-price');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.price .strike span', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.bx-pager-thumb a img');
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.bx-pager-thumb a img');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.bx-pager-thumb a img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '.sizedropdown .js-product-selecSize-dropdown .productDetailModule-dropdown-numberItems');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '.sizedropdown .js-product-selecSize-dropdown .productDetailModule-dropdown-numberItems');
	}

	protected function getStyleWith(DomCrawler $crawler) 
	{
		return null;
	}
}
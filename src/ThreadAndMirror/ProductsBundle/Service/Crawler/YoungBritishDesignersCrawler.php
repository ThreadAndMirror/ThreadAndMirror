<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class YoungBritishDesignersCrawler extends AbstractCrawler
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
		return $this->getHrefFromElement($crawler, '.breadcrumbCenter li:last-child a');
	}

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.productTitle h1');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.productTitle p a');
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.breadcrumbCenter li a', 2);
	}

	protected function getPid(DomCrawler $crawler)
	{
		return $this->getHrefFromElement($crawler, '.cssBuyButton .middleText h2 a');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.productWeThink p');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.productPrices h1');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.productPrices h2', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.thumbnailImages ul li a img');
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.thumbnailImages ul li a img');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.thumbnailImages ul li a img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '.productSizes li a');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '.productSizes li a');
	}

	protected function getStyleWith(DomCrawler $crawler) 
	{
		return null;
	}
}
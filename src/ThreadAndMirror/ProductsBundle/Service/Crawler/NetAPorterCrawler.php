<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class NetAPorterCrawler extends AbstractCrawler
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
		return $this->getTextFromElement($crawler, '#product-details h1');
	}

	protected function getBrandName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#product-details h2 a');
	}

	protected function getCategoryName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#viewMoreCategory');
	}

	protected function getPid(DomCrawler $crawler)
	{
		return $this->getDataFromElement($crawler, '#product-details', 'pid');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#editors-notes-content p', 0);
	}

	protected function getNow(DomCrawler $crawler)
	{
		return $this->getTextFromAlternatingElements($crawler, '#product-details .price .now', '#product-details .price > span');
	}

	protected function getWas(DomCrawler $crawler)
	{
		return $this->getTextFromAlternatingElements($crawler, '#product-details .price .was', null);
	}

	protected function getImages(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '#thumbnails-container img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#sku option');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#sku option');
	}

	protected function getStyleWith(DomCrawler $crawler)
	{
		return null;
	}
}
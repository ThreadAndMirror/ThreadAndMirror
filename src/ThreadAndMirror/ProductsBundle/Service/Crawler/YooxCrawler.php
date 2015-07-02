<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class YooxCrawler extends AbstractCrawler
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
		return $this->getTextFromElement($crawler, '#itemTitle h1 a span');
	}

	protected function getBrandName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#itemTitle h2 a span');
	}

	protected function getCategoryName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#breadcrumbs a', 2);
	}

	protected function getPid(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#itemInfoCod10');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#ItemDescription');
	}

	protected function getNow(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#itemPrice .priceCurrency');
	}

	protected function getWas(DomCrawler $crawler)
	{
		return $this->getTextFromAlternatingElements($crawler, '#itemPrice .oldprice', null);
	}

	protected function getImages(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '#itemThumbs li img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#itemSizes .colorsizelist li');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#itemSizes .colorsizelist li');
	}

	protected function getStyleWith(DomCrawler $crawler)
	{
		return null;
	}
}
<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class TheOutnetCrawler extends AbstractCrawler
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
		return $this->getTextFromElement($crawler, '#product-heading h1 span');
	}

	protected function getBrandName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#product-heading h1 a');
	}

	protected function getCategoryName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#breadcrumbs ul li:nth-child(3) a');
	}

	protected function getPid(DomCrawler $crawler)
	{
		return $this->getValueFromElement($crawler, '#productId');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return null;
	}

	protected function getNow(DomCrawler $crawler)
	{
		return $this->getTextFromAlternatingElements($crawler, '.prices-all .price-now .exact-price', null);
	}

	protected function getWas(DomCrawler $crawler)
	{
		return $this->getTextFromAlternatingElements($crawler, '.prices-all .price-info .price-original', null);
	}

	protected function getImages(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '#gallerySlider ul li a img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#sizes ul li label span');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#sizes ul li label:not(.sold-out) span');
	}

	protected function getStyleWith(DomCrawler $crawler)
	{
		return null;
	}
}
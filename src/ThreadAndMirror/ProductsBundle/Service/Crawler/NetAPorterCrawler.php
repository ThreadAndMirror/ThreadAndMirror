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
		return $this->getTextFromElement($crawler, '#main-product h1');
	}

	protected function getBrandName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#main-product h2.product-name');
	}

	protected function getCategoryName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.view-more ul li a', 1);
	}

	protected function getPid(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#main-product .product-code span');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.editors-notes .wrapper p', 0);
	}

	protected function getNow(DomCrawler $crawler)
	{
		return $this->getAttributeFromElement($crawler, '#main-product nap-product-price', 'price');
	}

	protected function getWas(DomCrawler $crawler)
	{
		return $this->getAttributeFromElement($crawler, '#main-product nap-product-price', 'price');
	}

	protected function getImages(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '#swiper-slide .thumbnail-image');
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
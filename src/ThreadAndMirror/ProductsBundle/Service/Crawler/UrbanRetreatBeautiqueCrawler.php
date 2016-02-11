<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class UrbanRetreatBeautiqueCrawler extends AbstractCrawler
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
		return $this->getTextFromElement($crawler, 'h1.brand-product');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h2.brand a');
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getPid(DomCrawler $crawler) 
	{
		return $this->getValueFromElement($crawler, '#UR_Product_hProductId');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromList($crawler, '#UR_Product_lblProductDescription p');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, 'h3.price .integer', 'h3.price .price');
	}

	protected function getWas(DomCrawler $crawler) 
	{
//		return $this->getTextFromAlternatingElements($crawler, '#product_price .sale_price .sale', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#imgMain');
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#imgMain');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#imgMain');
	}
}
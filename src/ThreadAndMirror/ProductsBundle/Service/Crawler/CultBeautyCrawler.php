<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class CultBeautyCrawler extends AbstractCrawler
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
		return $this->getTextFromElement($crawler, '.productTitle');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.productBrandTitle');
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return 'beauty';
	}

	protected function getPid(DomCrawler $crawler) 
	{
		return $this->getDataFromElement($crawler, '.discontinuedMsg', 'sku');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.productInfo .expandInfo .itemContent', 1);
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.productPrice', '.productPrice');
	}

	protected function getWas(DomCrawler $crawler) 
	{
//		return $this->getTextFromAlternatingElements($crawler, '#product_price .sale_price .sale', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.bigImage img');
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.bigImage img');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.bigImage img');
	}
}
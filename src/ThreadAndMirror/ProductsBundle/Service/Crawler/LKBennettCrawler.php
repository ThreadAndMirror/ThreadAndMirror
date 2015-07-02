<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class LKBennettCrawler extends AbstractCrawler
{
	protected function getExpired(DomCrawler $crawler) 
	{
		// Page not found
		try {
			if ($this->getTextFromElement($crawler, '#breadcrumb > ul > li > a', 'last') === 'Page Not Found') {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) { }
		
		// Redirected back to category page
		try {
			if ($this->getTextFromElement($crawler, '.filteroptions') !== null) {
				return true;
			}
		} catch (\Exception $e) {
			return false;
		}
	}

	protected function getUrl(DomCrawler $crawler) 
	{
		return $this->getHrefFromElement($crawler, '.breadcrumb > ul > li > a', 'last');
	}

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h1.product-title');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getPid(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.prod-detail-accordian-item');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.productdetails .price', '.productdetails .price', 1, 0);
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.productdetails .wasPrice strike', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getAttributeFromElement($crawler, 'meta[property="og:image"]', 'content');
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getAttributeFromElement($crawler, 'meta[property="og:image"]', 'content');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getAttributeFromElement($crawler, 'meta[property="og:image"]', 'content');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#Size option');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#Size option');
	}

	protected function getStyleWith(DomCrawler $crawler) 
	{
		return $this->getHrefFromList($crawler, '#producthcarousel > li a');
	}
}
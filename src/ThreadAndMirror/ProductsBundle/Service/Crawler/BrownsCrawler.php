<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class BrownsCrawler extends AbstractCrawler
{
	protected function getExpired(DomCrawler $crawler) 
	{
		try {
			if ($this->getTextFromElement($crawler, '#layout-content h1') === 'PRODUCT NO LONGER AVAILABLE') {
				return true;
			}
		} catch (\Exception $e) {
			return false;
		}
	}

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.prodInfo .product-title');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.prodInfo .product-manufacturer-name a');
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.p_sc > a:first-child');
	}

	protected function getPid(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.prodInfo > div > div:nth-child(2) > span');
	}

	protected function getDescription(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.prodInfo > div > div', 2);
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.prodInfo .p_p > .p_sale .currency-value', '.prodInfo .p_p > .currency-value');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.prodInfo .p_p > .p_pr .currency-value', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.p_ti > img');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.p_ti > img');
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.p_ti > img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '.p_oos');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getDataFromList($crawler, '.p_sr', 'size');
	}

	protected function getStyleWith(DomCrawler $crawler) 
	{
		return $this->getHrefFromList($crawler, '#lstRelatedComplete > li > a');
	}
}
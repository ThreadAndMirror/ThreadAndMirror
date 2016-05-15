<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class StylebopCrawler extends AbstractCrawler
{
	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.productInfo span');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.productInfo .caption_designer');
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#sitepath a', 2);
	}

	protected function getPid(DomCrawler $crawler)
	{
		return $this->getValueFromElement($crawler, '.productcode span');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromList($crawler, '.product_details_table .productlisting');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '#product_price .old_price', '#product_price');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '#product_price .sale_price', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#jcarousel_top ul li a img');
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#jcarousel_top ul li a img');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#jcarousel_top ul li a img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#product_size .size-item');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#product_size .size-item');
	}

	protected function getStyleWith(DomCrawler $crawler) 
	{
		return null;
	}
}
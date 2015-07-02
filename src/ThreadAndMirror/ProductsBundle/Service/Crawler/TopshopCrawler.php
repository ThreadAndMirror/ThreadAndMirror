<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class TopshopCrawler extends AbstractCrawler
{

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h1');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return 'Topshop';
	}

	protected function getCategoryName(DomCrawler $crawler)
	{
		return null;
	}

	protected function getPid(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#product_tab_1 .product_code span');
	}

	protected function getDescription(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#product_tab_1 .product_description');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, 'li.now_price > span', 'li.product_price');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, 'li.was_price > span', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#product_view_full > img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '.wrapper_product_size #product_size_full option');
	}

	protected function getStockedSizes(DomCrawler $crawler)
	{
		return $this->getTextFromList($crawler, '.wrapper_product_size #product_size_full option:not(.stock_zero)');
	}

	protected function getStyleWith(DomCrawler $crawler) 
	{
		return null;
	}
}
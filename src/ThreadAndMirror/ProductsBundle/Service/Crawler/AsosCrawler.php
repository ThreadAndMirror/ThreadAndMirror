<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class AsosCrawler extends AbstractCrawler
{

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h1');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getCategory(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getPid(DomCrawler $crawler) 
	{
		return null;
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

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.product_viewer ul.menu_nav_hor > img');
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.product_viewer ul.menu_nav_hor > img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, 'ul.product_size_grid > li > a');
	}

	protected function getStyleWith(DomCrawler $crawler) 
	{
		return null;
	}
}
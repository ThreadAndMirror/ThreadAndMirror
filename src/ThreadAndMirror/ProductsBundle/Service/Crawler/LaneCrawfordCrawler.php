<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class LaneCrawfordCrawler extends AbstractCrawler
{

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h1 .lc-product-short-description');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h1 .lc-product-brand');
	}

	protected function getCategoryName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.lc-product-link-list li:nth-child(3) a');
	}

	protected function getPid(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.lc-product-code code');
	}

	protected function getDescription(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.lc-product-long-description');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '#product-price', '#product-price');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '#price-original', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.lc-product-thumbs table tr td a img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '.lc-size-swatch');
	}

	protected function getStockedSizes(DomCrawler $crawler)
	{
		return $this->getTextFromList($crawler, '.lc-size-swatch:not(.lc-size-outofstock)');
	}
}
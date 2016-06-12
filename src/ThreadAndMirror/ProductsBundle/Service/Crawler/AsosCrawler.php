<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class AsosCrawler extends AbstractCrawler
{
	/**
	 * {@inheritdoc}
	 */
	protected function getPid(DomCrawler $crawler)
	{
		return $this->getValueFromElement($crawler, '#CatwalkInventoryId');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, 'h1');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#ctl00_ContentMainPage_productInfoPanel > a > strong', 1);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getCategory(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#ctl00_ContentMainPage_productInfoPanel a strong', 0);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDescription(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#ctl00_ContentMainPage_productInfoPanel li');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.outlet-current-price', '.product_price span');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.product_rrp span', null);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.productThumbnails img');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.productThumbnails img');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.productThumbnails img');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getStyleWith(DomCrawler $crawler) 
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getCustomHeaders()
	{
		return ['Cookie' => 'asos=currencyid=1'];
	}
}
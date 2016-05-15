<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class Avenue32Crawler extends AbstractCrawler
{
	/**
	 * {@inheritdoc}
	 */
	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h1.product-name small');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, 'h1.product-name span');
	}

	/**
     * {@inheritdoc}
	 */
	protected function getCategoryName(DomCrawler $crawler) 
	{
		return null;
	}

	/**
     * {@inheritdoc}
	 */
	protected function getPid(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.product-number span');
	}

	/**
     * {@inheritdoc}
	 */
	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#tab1');
	}

	/**
     * {@inheritdoc}
	 */
	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.product-price .price-sales');
	}

	/**
     * {@inheritdoc}
	 */
	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.product-price .price-standard', null);
	}

	/**
     * {@inheritdoc}
	 */
	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, 'img.productthumbnail');
	}

	/**
     * {@inheritdoc}
	 */
	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, 'img.productthumbnail');
	}

	/**
     * {@inheritdoc}
	 */
	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, 'img.productthumbnail');
	}

	/**
     * {@inheritdoc}
	 */
	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#va-size option');
	}

	/**
     * {@inheritdoc}
	 */
	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#va-size option');
	}
}
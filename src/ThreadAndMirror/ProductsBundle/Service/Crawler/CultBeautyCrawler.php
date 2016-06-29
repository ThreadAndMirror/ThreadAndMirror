<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class CultBeautyCrawler extends AbstractCrawler
{
	/**
	 * {@inheritdoc}
	 */
	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.productTitle');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '.productBrandTitle');
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
		return $this->getDataFromElement($crawler, '.discontinuedMsg', 'sku');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.productInfo .expandInfo .itemContent', 1);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.productPrice', '.productPrice');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getWas(DomCrawler $crawler) 
	{
//		return $this->getTextFromAlternatingElements($crawler, '#product_price .sale_price .sale', null);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.bigImage img');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.bigImage img');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.bigImage img');
	}
}
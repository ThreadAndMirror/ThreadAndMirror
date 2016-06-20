<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class SpaceNKCrawler extends AbstractCrawler
{
	/**
	 * {@inheritdoc}
	 */
	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#pdpMain .product-name');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#pdpMain .product-name');
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
		return $this->getTextFromElement($crawler, '#pdpMain .product-number span');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromAlternatingElements($crawler, '#pdp-accordion .pdp-accordion-text', '#pdp-accordion .pdp-accordion-description');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '#pdpMain .product-price .price-sales', null);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getWas(DomCrawler $crawler)
	{
		return null;
//		return $this->getTextFromAlternatingElements($crawler, '#product_price .sale_price .sale', null);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.primary-image');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.primary-image');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '.primary-image');
	}
}
<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class UrbanRetreatBeautiqueCrawler extends AbstractCrawler
{
	/**
	 * {@inheritdoc}
	 */
	protected function getName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, 'h1.product-title');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getBrandName(DomCrawler $crawler)
	{
		return $this->getAttributeFromElement($crawler, '.product-logo-img', 'title');
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
		return $this->getValueFromElement($crawler, 'input[name=prodId]');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.product-details-wrapper .product-info');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNow(DomCrawler $crawler)
	{
		return $this->getTextFromAlternatingElements($crawler, '.product-price .price', '.product-price .price');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getWas(DomCrawler $crawler)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getImages(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '.main-product-image img');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getPortraits(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '.main-product-image img');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getThumbnails(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '.main-product-image img');
	}
}
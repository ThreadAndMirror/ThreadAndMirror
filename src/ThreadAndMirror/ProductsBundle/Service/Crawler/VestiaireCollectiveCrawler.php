<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class VestiaireCollectiveCrawler extends AbstractCrawler
{
	/**
	 * {@inheritdoc}
	 */
	protected function getName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#prd-name');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getBrandName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#prd-brand');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getCategoryName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#breadcrumb a', 3);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getPid(DomCrawler $crawler)
	{
		return $this->getValueFromElement($crawler, 'input[name="productID"]');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#sellerDescription');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNow(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#actual_price_old');
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
	protected function getThumbnails(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '#prd_gallery img');
	}
}